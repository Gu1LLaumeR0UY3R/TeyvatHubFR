<?php

namespace App\Console\Commands;

use App\Models\Arme;
use App\Models\Etoile;
use App\Models\Photo;
use App\Models\TypeArme;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class RepairWeaponData extends Command
{
    protected $signature = 'repair:weapons-data';

    protected $description = 'Rejoue les corrections BDD: mapping armes, photos manquantes, deduplication et conversion URL vers fichiers locaux';

    public function handle(): int
    {
        $this->info('Demarrage de la reparation des donnees armes...');

        $apiWeapons = $this->fetchWeaponsFromApi();
        if (empty($apiWeapons)) {
            $this->error('Aucune donnee arme recuperable depuis l\'API. Arret.');
            return self::FAILURE;
        }

        $stats = [
            'mapped_type' => 0,
            'mapped_rarity' => 0,
            'photos_created_or_updated' => 0,
            'weapon_duplicates_removed' => 0,
            'remote_paths_downloaded' => 0,
            'remote_paths_failed' => 0,
        ];

        DB::transaction(function () use ($apiWeapons, &$stats): void {
            $typeCache = $this->buildTypeCache();

            foreach (Arme::query()->get() as $arme) {
                if (!$arme instanceof Arme) {
                    continue;
                }

                $payload = $apiWeapons[$arme->slug] ?? null;
                if (!$payload) {
                    continue;
                }

                $dirty = false;

                $typeId = $this->resolveTypeId($payload, $typeCache);
                if ($typeId && $arme->fid_TArmes !== $typeId) {
                    $arme->fid_TArmes = $typeId;
                    $stats['mapped_type']++;
                    $dirty = true;
                }

                $rarity = (int) ($payload['rarity'] ?? 0);
                if ($rarity >= 1 && $rarity <= 5) {
                    $etoile = Etoile::firstOrCreate(['libelle' => $rarity.'★']);
                    if ($arme->fid_etoile !== $etoile->id_etoile) {
                        $arme->fid_etoile = $etoile->id_etoile;
                        $stats['mapped_rarity']++;
                        $dirty = true;
                    }
                }

                if ($dirty) {
                    $arme->save();
                }

                $iconUrl = $payload['icon_url'] ?? null;
                if (!is_string($iconUrl) || trim($iconUrl) === '') {
                    continue;
                }

                $photo = Photo::query()
                    ->where('photoable_id', $arme->id_arme)
                    ->whereIn('photoable_type', ['arme', 'armes', Arme::class])
                    ->orderBy('id_photo')
                    ->first();

                if (!$photo) {
                    Photo::query()->create([
                        'photoable_type' => 'arme',
                        'photoable_id' => $arme->id_arme,
                        'chemin_photo' => $iconUrl,
                        'source_url' => $iconUrl,
                    ]);
                    $stats['photos_created_or_updated']++;
                    continue;
                }

                $photoDirty = false;
                if (empty($photo->source_url)) {
                    $photo->source_url = $iconUrl;
                    $photoDirty = true;
                }
                if (empty($photo->chemin_photo)) {
                    $photo->chemin_photo = $iconUrl;
                    $photoDirty = true;
                }
                if ($photo->photoable_type !== 'arme') {
                    $photo->photoable_type = 'arme';
                    $photoDirty = true;
                }

                if ($photoDirty) {
                    $photo->save();
                    $stats['photos_created_or_updated']++;
                }

                Photo::query()
                    ->where('photoable_id', $arme->id_arme)
                    ->whereIn('photoable_type', ['arme', 'armes', Arme::class])
                    ->where('id_photo', '!=', $photo->id_photo)
                    ->delete();
            }

            $stats['weapon_duplicates_removed'] = $this->deduplicateWeaponsBySlug();
        });

        $downloadStats = $this->downloadRemotePhotosToLocal();
        $stats['remote_paths_downloaded'] = $downloadStats['downloaded'];
        $stats['remote_paths_failed'] = $downloadStats['failed'];

        $this->line('---');
        $this->info('Reparation terminee.');
        foreach ($stats as $key => $value) {
            $this->line($key.': '.$value);
        }

        return self::SUCCESS;
    }

    private function fetchWeaponsFromApi(): array
    {
        $base = 'https://teyvat-dev.vercel.app/api';
        $response = Http::timeout(30)->retry(2, 500)->get("{$base}/weapons");

        if (!$response->successful()) {
            return [];
        }

        $payload = $response->json();
        if (!is_array($payload)) {
            return [];
        }

        $bySlug = [];
        foreach ($payload as $weapon) {
            if (!is_array($weapon) || empty($weapon['name'])) {
                continue;
            }
            $slug = Str::slug((string) $weapon['name']);
            $bySlug[$slug] = $weapon;
        }

        return $bySlug;
    }

    private function buildTypeCache(): array
    {
        $cache = [];
        foreach (TypeArme::query()->get() as $type) {
            $cache[Str::lower(trim((string) $type->libelle_TArme))] = $type->id_TArmes;
        }

        return $cache;
    }

    private function resolveTypeId(array $payload, array $typeCache): ?int
    {
        $raw = $payload['type']['name']
            ?? $payload['weapon_type']['name']
            ?? $payload['type']
            ?? $payload['weapon_type']
            ?? null;

        if (!is_string($raw) || trim($raw) === '') {
            return null;
        }

        $normalized = Str::lower(trim($raw));
        if (isset($typeCache[$normalized])) {
            return $typeCache[$normalized];
        }

        $aliases = [
            'one-handed sword' => ['sword', 'epee'],
            'sword' => ['sword', 'epee'],
            'claymore' => ['claymore', 'espadon'],
            'polearm' => ['polearm', 'lance'],
            'catalyst' => ['catalyst', 'catalyseur'],
            'bow' => ['bow', 'arc'],
        ];

        foreach ($aliases as $apiName => $candidates) {
            if ($normalized !== $apiName) {
                continue;
            }
            foreach ($candidates as $candidate) {
                if (isset($typeCache[$candidate])) {
                    return $typeCache[$candidate];
                }
            }
        }

        foreach ($typeCache as $label => $id) {
            if (str_contains($label, $normalized) || str_contains($normalized, $label)) {
                return $id;
            }
        }

        return null;
    }

    private function deduplicateWeaponsBySlug(): int
    {
        $duplicates = DB::table('armes')
            ->select('slug')
            ->whereNotNull('slug')
            ->groupBy('slug')
            ->havingRaw('COUNT(*) > 1')
            ->pluck('slug');

        if ($duplicates->isEmpty()) {
            return 0;
        }

        $updated = 0;
        $refTables = collect(DB::select(
            "SELECT TABLE_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND COLUMN_NAME = 'fid_arme'"
        ))->pluck('TABLE_NAME')->filter(fn (string $t) => $t !== 'armes')->values();

        foreach ($duplicates as $slug) {
            $ids = DB::table('armes')->where('slug', $slug)->orderBy('id_arme')->pluck('id_arme')->values();
            $keepId = (int) $ids->first();
            $dropIds = $ids->slice(1)->map(fn ($v) => (int) $v)->values();

            if ($dropIds->isEmpty()) {
                continue;
            }

            foreach ($refTables as $table) {
                DB::table($table)->whereIn('fid_arme', $dropIds)->update(['fid_arme' => $keepId]);
            }

            DB::table('photo')
                ->whereIn('photoable_id', $dropIds)
                ->whereIn('photoable_type', ['arme', 'armes', Arme::class])
                ->update([
                    'photoable_id' => $keepId,
                    'photoable_type' => 'arme',
                ]);

            DB::table('armes')->whereIn('id_arme', $dropIds)->delete();
            $updated += $dropIds->count();
        }

        return $updated;
    }

    private function downloadRemotePhotosToLocal(): array
    {
        $downloaded = 0;
        $failed = 0;

        Photo::query()
            ->whereNotNull('chemin_photo')
            ->where('chemin_photo', 'like', 'http%')
            ->orderBy('id_photo')
            ->chunkById(50, function ($photos) use (&$downloaded, &$failed): void {
                foreach ($photos as $photo) {
                    if (!$photo instanceof Photo) {
                        continue;
                    }

                    $url = trim((string) $photo->chemin_photo);
                    if ($url === '') {
                        continue;
                    }

                    try {
                        $response = Http::timeout(30)->retry(2, 500)->get($url);
                        if (!$response->successful()) {
                            $failed++;
                            continue;
                        }

                        $path = parse_url($url, PHP_URL_PATH) ?: '';
                        $ext = pathinfo($path, PATHINFO_EXTENSION);
                        if (!$ext) {
                            $ext = 'webp';
                        }

                        $localPath = 'photos/imports/'.$photo->id_photo.'-'.Str::random(8).'.'.$ext;
                        Storage::disk('public')->put($localPath, $response->body());

                        if (empty($photo->source_url)) {
                            $photo->source_url = $url;
                        }
                        $photo->chemin_photo = $localPath;
                        $photo->save();
                        $downloaded++;
                    } catch (\Throwable $e) {
                        $failed++;
                    }
                }
            }, 'id_photo');

        return ['downloaded' => $downloaded, 'failed' => $failed];
    }
}
