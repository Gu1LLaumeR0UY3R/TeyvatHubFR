<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Aptitude;
use App\Models\Arme;
use App\Models\Constellation;
use App\Models\PersonnageArtefactRecommandee;
use App\Models\PersonnageArmeRecommandee;
use App\Models\PersonnageVideo;
use App\Models\Nation;
use App\Models\TypeArme;
use Illuminate\Http\Request;
use App\Models\Personnage;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;

class PersonnageBlockController extends Controller
{
    public function updateMainZone(Request $request, Personnage $personnage): JsonResponse
    {
        $nationTable = Schema::hasTable('nation')
            ? 'nation'
            : (Schema::hasTable('région') ? 'région' : null);

        $nationRules = ['sometimes', 'array'];
        $nationItemRules = ['integer'];

        if ($nationTable) {
            $nationItemRules[] = Rule::exists($nationTable, 'id_region');
        }

        $data = $request->validate([
            'nom_perso' => ['required', 'string', 'max:100'],
            'fid_element' => ['required', 'integer', 'exists:elements,id_element'],
            'fid_etoile' => ['required', 'integer', 'exists:etoile,id_etoile'],
            'fid_TArmes' => ['nullable', 'integer', 'exists:type_armes,id_TArmes'],
            'fid_TP' => ['nullable', 'integer', 'exists:type_perso,id_TP'],
            'background_actif' => ['nullable', 'string', 'max:255'],
            'fid_nations' => $nationRules,
            'fid_nations.*' => $nationItemRules,
            'videos' => ['sometimes', 'array'],
            'videos.*.url_video' => ['required', 'url', 'max:255'],
        ]);

        $armeIcon = null;
        if (!empty($data['fid_TArmes'])) {
            $arme = Arme::where('fid_TArmes', $data['fid_TArmes'])->first();
            if ($arme) {
                $photo = $arme->photos->first();
                if ($photo) {
                    if ($photo->source_url) {
                        $armeIcon = $photo->source_url;
                    } elseif (filter_var($photo->chemin_photo, FILTER_VALIDATE_URL)) {
                        $armeIcon = $photo->chemin_photo;
                    } else {
                        $armeIcon = Storage::url($photo->chemin_photo);
                    }
                } else {
                    $weaponFile = public_path('storage/photos/armes/icones_armes/' . $arme->slug . '.png');
                    if (file_exists($weaponFile)) {
                        $armeIcon = asset('storage/photos/armes/icones_armes/' . $arme->slug . '.png');
                    }
                }
            }

            if (!$armeIcon) {
                $weaponType = TypeArme::find($data['fid_TArmes']);
                $photo = $weaponType?->photos->first();
                if ($photo) {
                    if ($photo->source_url) {
                        $armeIcon = $photo->source_url;
                    } elseif (filter_var($photo->chemin_photo, FILTER_VALIDATE_URL)) {
                        $armeIcon = $photo->chemin_photo;
                    } else {
                        $armeIcon = Storage::url($photo->chemin_photo);
                    }
                }
            }
        }

        $updatePayload = [
            'nom_perso' => $data['nom_perso'],
            'fid_element' => $data['fid_element'],
            'fid_etoile' => $data['fid_etoile'],
            'fid_TArmes' => $data['fid_TArmes'] ?? null,
            'fid_TP' => $data['fid_TP'] ?? $personnage->fid_TP,
        ];

        if (Schema::hasColumn('personnage', 'arme_icon')) {
            $updatePayload['arme_icon'] = $armeIcon;
        }

        if (Schema::hasColumn('personnage', 'background_actif')) {
            $updatePayload['background_actif'] = $data['background_actif'] ?? null;
        }

        $personnage->update($updatePayload);

        if (
            array_key_exists('fid_nations', $data)
            && $nationTable
            && Schema::hasTable('personnage_nation')
        ) {
            $personnage->nations()->sync($data['fid_nations']);
        }

        if (array_key_exists('videos', $data)) {
            $personnage->videos()->delete();
            foreach ($data['videos'] as $index => $video) {
                PersonnageVideo::query()->create([
                    'fid_perso' => $personnage->id_perso,
                    'url_video' => $video['url_video'],
                    'ordre' => $index + 1,
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Zone principale mise à jour.',
        ]);
    }

    public function uploadImage(Request $request, Personnage $personnage): JsonResponse
    {
        $data = $request->validate([
            'image_type' => ['required', 'string', Rule::in(['icone', 'portrait', 'full'])],
            'image' => ['required', 'image', 'max:4096'],
        ]);

        $imageType = $data['image_type'];
        $storageType = $imageType === 'full' ? 'portrait' : $imageType;
        $dir = $storageType === 'portrait'
            ? 'photos/personnages/personnage_full'
            : 'photos/personnages/icones_personnage';

        $oldPath = $personnage->photos()->where('type', $storageType)->value('chemin_photo');
        if ($oldPath && !filter_var($oldPath, FILTER_VALIDATE_URL)) {
            Storage::disk('public')->delete($oldPath);
        }
        $personnage->photos()->where('type', $storageType)->delete();

        $extension = strtolower($request->file('image')->getClientOriginalExtension() ?: $request->file('image')->extension() ?: 'png');
        $filename = $storageType === 'portrait'
            ? $personnage->slug . '-full.' . $extension
            : $personnage->slug . '-icon.' . $extension;

        $path = $this->storeResizedImage(
            $request->file('image'),
            $dir,
            $filename,
            $imageType === 'portrait' ? 1600 : 512
        );
        $personnage->photos()->create([
            'chemin_photo' => $path,
            'source_url' => null,
            'type' => $storageType,
        ]);

        return response()->json([
            'success' => true,
            'path' => $path,
            'url' => asset('storage/' . $path),
            'image_type' => $imageType,
        ]);
    }

    /**
     * Resize uploaded images when they are larger than the configured max side.
     */
    private function storeResizedImage($uploadedFile, string $dir, string $filename, int $maxSide): string
    {
        $path = trim($dir, '/') . '/' . $filename;

        try {
            $sourcePath = $uploadedFile->getRealPath();
            $imageInfo = @getimagesize($sourcePath);

            if (!$imageInfo) {
                return $uploadedFile->storeAs($dir, $filename, 'public');
            }

            [$width, $height] = $imageInfo;
            $mime = $imageInfo['mime'] ?? null;

            if ($width <= $maxSide && $height <= $maxSide) {
                return $uploadedFile->storeAs($dir, $filename, 'public');
            }

            $ratio = min($maxSide / $width, $maxSide / $height);
            $newWidth = max(1, (int) round($width * $ratio));
            $newHeight = max(1, (int) round($height * $ratio));

            $sourceData = file_get_contents($sourcePath);
            if ($sourceData === false) {
                return $uploadedFile->storeAs($dir, $filename, 'public');
            }

            $src = @imagecreatefromstring($sourceData);
            if (!$src) {
                return $uploadedFile->storeAs($dir, $filename, 'public');
            }

            $dst = imagecreatetruecolor($newWidth, $newHeight);

            if (in_array($mime, ['image/png', 'image/webp'], true)) {
                imagealphablending($dst, false);
                imagesavealpha($dst, true);
                $transparent = imagecolorallocatealpha($dst, 0, 0, 0, 127);
                imagefill($dst, 0, 0, $transparent);
            }

            imagecopyresampled($dst, $src, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

            ob_start();
            if ($mime === 'image/png') {
                imagepng($dst, null, 6);
            } elseif ($mime === 'image/webp' && function_exists('imagewebp')) {
                imagewebp($dst, null, 85);
            } else {
                imagejpeg($dst, null, 85);
            }
            $binary = ob_get_clean();

            imagedestroy($src);
            imagedestroy($dst);

            if ($binary === false || $binary === null) {
                return $uploadedFile->storeAs($dir, $filename, 'public');
            }

            Storage::disk('public')->put($path, $binary);

            return $path;
        } catch (\Throwable $exception) {
            return $uploadedFile->storeAs($dir, $filename, 'public');
        }
    }

    public function getBackgroundsByNation(Request $request, Personnage $personnage): JsonResponse
    {
        $nationTable = Schema::hasTable('nation') ? 'nation' : 'région';

        $data = $request->validate([
            'fid_nation' => ['required', 'integer', Rule::exists($nationTable, 'id_region')],
        ]);

        $nation = Nation::findOrFail($data['fid_nation']);
        $galleryDir = 'regions/gallery_' . $nation->nom_region;

        if (!Storage::disk('public')->exists($galleryDir)) {
            return response()->json(['backgrounds' => []]);
        }

        $backgrounds = collect(Storage::disk('public')->files($galleryDir))
            ->filter(function (string $path): bool {
                return Str::endsWith(strtolower($path), ['.jpg', '.jpeg', '.png', '.webp']);
            })
            ->values()
            ->map(function (string $path): array {
                return [
                    'id' => basename($path),
                    'path' => $path,
                    'url' => asset('storage/' . $path),
                ];
            });

        return response()->json(['backgrounds' => $backgrounds]);
    }

    public function updateArmesRecommandees(Request $request, Personnage $personnage): JsonResponse
    {
        $data = $request->validate([
            'armes' => ['required', 'array', 'min:1', 'max:6'],
            'armes.*.id_arme' => ['required', 'integer', 'exists:armes,id_arme'],
            'armes.*.rang' => ['nullable', 'integer', 'min:1', 'max:5'],
            'armes.*.is_starter' => ['nullable', 'boolean'],
            'armes.*.origine' => ['nullable', Rule::in(['tirage', 'evenement', 'craft', 'achat'])],
        ]);

        $armes = collect($data['armes'])->values();

        // Verifie le type d'arme du personnage si défini
        $expectedTypeId = $personnage->fid_TArmes;
        if ($expectedTypeId) {
            foreach ($armes as $index => $armeData) {
                $armeModel = \App\Models\Arme::find($armeData['id_arme']);
                if (!$armeModel || $armeModel->fid_TArmes !== $expectedTypeId) {
                    throw ValidationException::withMessages([
                        'armes.' . $index . '.id_arme' => 'Cette arme n\'est pas compatible avec le type d\'arme du personnage.',
                    ]);
                }
            }
        }

        // Keep only the first starter weapon as starter=true.
        $starterAssigned = false;
        $armes = $armes->map(function (array $arme) use (&$starterAssigned): array {
            $isStarter = (bool) ($arme['is_starter'] ?? false);
            if ($isStarter && $starterAssigned) {
                $isStarter = false;
            }
            if ($isStarter) {
                $starterAssigned = true;
            }

            return [
                'id_arme' => (int) $arme['id_arme'],
                'rang' => (int) ($arme['rang'] ?? 1),
                'is_starter' => $isStarter,
                'origine' => $arme['origine'] ?? null,
            ];
        });

        if (!$armes->contains(fn (array $arme): bool => $arme['is_starter'] === true)) {
            throw ValidationException::withMessages([
                'armes' => 'Une arme starter est obligatoire.',
            ]);
        }

        // Force le starter en derniere position quel que soit l'ordre saisi.
        $starterWeapon = $armes->first(fn (array $arme): bool => $arme['is_starter'] === true);
        $nonStarterWeapons = $armes->filter(fn (array $arme): bool => $arme['is_starter'] === false)->values();
        $armes = $starterWeapon ? $nonStarterWeapons->push($starterWeapon)->values() : $nonStarterWeapons;

        PersonnageArmeRecommandee::query()->where('fid_perso', $personnage->id_perso)->delete();

        foreach ($armes as $index => $arme) {
            PersonnageArmeRecommandee::query()->create([
                'fid_perso' => $personnage->id_perso,
                'fid_arme' => $arme['id_arme'],
                'position' => $index + 1,
                'origine' => $arme['origine'],
                'starter' => $arme['is_starter'] ? 1 : 0,
            ]);
        }

        return response()->json([
            'success' => true,
            'armes' => $armes,
        ]);
    }

    public function deleteArmeRecommandee(Personnage $personnage, int $id_arme): JsonResponse
    {
        PersonnageArmeRecommandee::query()
            ->where('fid_perso', $personnage->id_perso)
            ->where('fid_arme', $id_arme)
            ->delete();

        return response()->json([
            'success' => true,
            'id_arme' => $id_arme,
        ]);
    }

    public function updateArtefactsRecommandees(Request $request, Personnage $personnage): JsonResponse
    {
        $data = $request->validate([
            'builds' => ['required', 'array', 'min:1'],
            'builds.*.artefact1_id' => ['required', 'integer', 'exists:artefact,id_artefact'],
            'builds.*.pieces_1' => ['required', 'integer', Rule::in([2, 4])],
            'builds.*.artefact2_id' => ['nullable', 'integer', 'exists:artefact,id_artefact'],
            'builds.*.pieces_2' => ['nullable', 'integer', Rule::in([2])],
        ]);

        $builds = collect($data['builds'])->values();

        foreach ($builds as $index => $build) {
            $pieces1 = (int) $build['pieces_1'];
            $artefact2Id = $build['artefact2_id'] ?? null;
            $pieces2 = $build['pieces_2'] ?? null;

            if ($pieces1 === 2 && (!$artefact2Id || (int) $pieces2 !== 2)) {
                throw ValidationException::withMessages([
                    'builds.' . $index . '.artefact2_id' => 'Un build 2P+2P requiert un second set en 2P.',
                ]);
            }
        }

        PersonnageArtefactRecommandee::query()->where('fid_perso', $personnage->id_perso)->delete();

        foreach ($builds as $index => $build) {
            $pieces1 = (int) $build['pieces_1'];

            PersonnageArtefactRecommandee::query()->create([
                'fid_perso' => $personnage->id_perso,
                'fid_artefact_1' => (int) $build['artefact1_id'],
                'pieces_1' => $pieces1 === 4 ? '4p' : '2p',
                'fid_artefact_2' => $pieces1 === 2 ? (int) $build['artefact2_id'] : null,
                'pieces_2' => $pieces1 === 2 ? '2p' : null,
                'position' => $index + 1,
            ]);
        }

        return response()->json(['success' => true]);
    }

    public function deleteArtefactRecommande(Personnage $personnage, int $id_build): JsonResponse
    {
        PersonnageArtefactRecommandee::query()
            ->where('fid_perso', $personnage->id_perso)
            ->where('id_build', $id_build)
            ->delete();

        return response()->json([
            'success' => true,
            'id_build' => $id_build,
        ]);
    }

    public function updateConstellations(Request $request, Personnage $personnage): JsonResponse
    {
        $data = $request->validate([
            'constellations' => ['required', 'array', 'min:1'],
            'constellations.*.id_const' => ['required', 'integer', 'exists:constellation,id_const'],
            'constellations.*.titre_const' => ['required', 'string', 'max:200'],
            'constellations.*.descri_const' => ['nullable', 'string'],
        ]);

        foreach ($data['constellations'] as $payload) {
            Constellation::query()
                ->where('id_const', (int) $payload['id_const'])
                ->where('fid_perso', $personnage->id_perso)
                ->update([
                    'titre_const' => $payload['titre_const'],
                    'descri_const' => $payload['descri_const'] ?? null,
                ]);
        }

        return response()->json(['success' => true]);
    }

    public function uploadConstellationImage(Request $request, Personnage $personnage): JsonResponse
    {
        $data = $request->validate([
            'image' => ['required', 'image', 'max:4096'],
            'constellation_index' => ['required', 'integer', 'min:1', 'max:6'],
        ]);

        $index = (int) $data['constellation_index'];
        $dir = 'photos/personnages/constellations';
        $extension = strtolower($request->file('image')->getClientOriginalExtension() ?: $request->file('image')->extension() ?: 'png');
        $filename = $personnage->slug . '-c' . $index . '.' . $extension;

        $existingFiles = Storage::disk('public')->files($dir);
        foreach ($existingFiles as $file) {
            if (preg_match('/^' . preg_quote($dir, '/') . '\\/' . preg_quote($personnage->slug, '/') . '-c' . $index . '\\./', $file)) {
                Storage::disk('public')->delete($file);
            }
        }

        $path = $request->file('image')->storeAs($dir, $filename, 'public');

        return response()->json([
            'success' => true,
            'path' => $path,
            'url' => asset('storage/' . $path),
            'constellation_index' => $index,
        ]);
    }

    public function updateConstellationMap(Request $request, Personnage $personnage): JsonResponse
    {
        $request->validate([
            'positions_const'             => ['nullable', 'json'],
            'constellation_map_image'     => ['nullable', 'image', 'max:5120'],
            'constellation_map_image_url' => ['nullable', 'url', 'max:500'],
        ]);

        // Ensure at least one constellation record exists to store map data
        $constCarte = Constellation::where('fid_perso', $personnage->id_perso)
            ->orderBy('id_const')
            ->first();

        if (!$constCarte) {
            $constCarte = Constellation::create([
                'fid_perso'   => $personnage->id_perso,
                'titre_const' => 'Carte',
                'descri_const' => null,
            ]);
        }

        // Save positions + lines JSON
        if ($request->filled('positions_const') && Schema::hasColumn('constellation', 'positions_const')) {
            $decoded = json_decode((string) $request->input('positions_const'), true);

            if (is_array($decoded)) {
                $rawPoints = is_array($decoded['points'] ?? null) ? $decoded['points'] : $decoded;

                $normalizedPoints = [];
                for ($i = 1; $i <= 6; $i++) {
                    $pt = $rawPoints[(string) $i] ?? $rawPoints[$i] ?? null;
                    if (!is_array($pt) || !isset($pt['x'], $pt['y'])) {
                        continue;
                    }
                    $normalizedPoints[(string) $i] = [
                        'x' => round(max(0, min(100, (float) $pt['x'])), 1),
                        'y' => round(max(0, min(100, (float) $pt['y'])), 1),
                    ];
                }

                $normalizedLines = [];
                $seen = [];
                foreach (is_array($decoded['lines'] ?? null) ? $decoded['lines'] : [] as $line) {
                    if (!is_array($line)) {
                        continue;
                    }
                    $from = isset($line['from']) ? (int) $line['from'] : null;
                    $to   = isset($line['to'])   ? (int) $line['to']   : null;
                    if (!$from || !$to || $from === $to || $from < 1 || $from > 6 || $to < 1 || $to > 6) {
                        continue;
                    }
                    if (!isset($normalizedPoints[(string) $from]) || !isset($normalizedPoints[(string) $to])) {
                        continue;
                    }
                    $a = min($from, $to);
                    $b = max($from, $to);
                    $pair = "{$a}-{$b}";
                    if (isset($seen[$pair])) {
                        continue;
                    }
                    $seen[$pair] = true;
                    $normalizedLines[] = ['from' => $a, 'to' => $b];
                }

                $constCarte->positions_const = ['points' => $normalizedPoints, 'lines' => $normalizedLines];
                $constCarte->save();
            }
        }

        // Save photo via URL
        if ($request->filled('constellation_map_image_url')) {
            $url = (string) $request->input('constellation_map_image_url');
            $constCarte->photo()->updateOrCreate([], ['chemin_photo' => $url, 'source_url' => $url]);
        }

        // Save photo via file upload
        if ($request->hasFile('constellation_map_image')) {
            $oldPhoto = $constCarte->photo;
            if ($oldPhoto && !filter_var((string) $oldPhoto->chemin_photo, FILTER_VALIDATE_URL)) {
                Storage::disk('public')->delete($oldPhoto->chemin_photo);
            }
            $path = $request->file('constellation_map_image')
                ->store('photos/personnages/constellations/maps', 'public');
            $constCarte->photo()->updateOrCreate([], ['chemin_photo' => $path, 'source_url' => null]);
        }

        // Build image URL for the response
        $photo    = $constCarte->fresh()->load('photo')->photo;
        $imageUrl = null;
        if ($photo) {
            if ($photo->source_url) {
                $imageUrl = $photo->source_url;
            } elseif (filter_var((string) $photo->chemin_photo, FILTER_VALIDATE_URL)) {
                $imageUrl = $photo->chemin_photo;
            } else {
                $imageUrl = asset('storage/' . ltrim($photo->chemin_photo, '/'));
            }
        }

        return response()->json(['success' => true, 'image_url' => $imageUrl]);
    }

    public function updateCompetences(Request $request, Personnage $personnage): JsonResponse
    {
        $data = $request->validate([
            'competences' => ['required', 'array', 'min:1'],
            'competences.*.id_aptitude' => ['nullable', 'integer', 'exists:aptitude,id_aptitude'],
            'competences.*.titre_apti' => ['required', 'string', 'max:200'],
            'competences.*.descri_apti' => ['nullable', 'string'],
            'competences.*.lvl_apt' => ['nullable', 'integer', 'min:1', 'max:15'],
            'competences.*.sub_Apt' => ['nullable', 'string'],
            'competences.*.fid_TypeApti' => ['required', 'integer', 'exists:type_apti,id_TypeApti'],
        ]);

        $keptIds = [];

        foreach ($data['competences'] as $payload) {
            $attributes = [
                'titre_apti' => $payload['titre_apti'],
                'descri_apti' => $payload['descri_apti'] ?? null,
                'lvl_apt' => (int) ($payload['lvl_apt'] ?? 1),
                'sub_Apt' => $payload['sub_Apt'] ?? null,
                'fid_TypeApti' => (int) $payload['fid_TypeApti'],
                'fid_perso' => $personnage->id_perso,
            ];

            if (!empty($payload['id_aptitude'])) {
                $aptitude = Aptitude::query()
                    ->where('id_aptitude', (int) $payload['id_aptitude'])
                    ->where('fid_perso', $personnage->id_perso)
                    ->first();

                if ($aptitude) {
                    $aptitude->update($attributes);
                    $keptIds[] = $aptitude->id_aptitude;
                    continue;
                }
            }

            $created = Aptitude::query()->create($attributes);
            $keptIds[] = $created->id_aptitude;
        }

        Aptitude::query()
            ->where('fid_perso', $personnage->id_perso)
            ->whereNotIn('id_aptitude', $keptIds)
            ->delete();

        return response()->json([
            'success'          => true,
            'competences_ids'  => $keptIds,
            'competences_count'=> count($keptIds),
        ]);
    }

    public function uploadAptitudeImage(Request $request, Personnage $personnage): JsonResponse
    {
        $data = $request->validate([
            'image'        => ['required', 'image', 'max:4096'],
            'id_aptitude'  => ['required', 'integer', 'exists:aptitude,id_aptitude'],
        ]);

        $aptitude = Aptitude::query()
            ->where('id_aptitude', (int) $data['id_aptitude'])
            ->where('fid_perso', $personnage->id_perso)
            ->firstOrFail();

        $dir       = 'photos/personnages/aptitudes';
        $extension = strtolower($request->file('image')->getClientOriginalExtension() ?: $request->file('image')->extension() ?: 'png');
        $filename  = $personnage->slug . '-apt-' . $aptitude->id_aptitude . '.' . $extension;

        if ($old = $aptitude->photos()->first()) {
            if (!filter_var($old->chemin_photo, FILTER_VALIDATE_URL)) {
                Storage::disk('public')->delete($old->chemin_photo);
            }
            $aptitude->photos()->delete();
        }

        $path = $request->file('image')->storeAs($dir, $filename, 'public');

        $aptitude->photos()->create([
            'chemin_photo' => $path,
            'source_url'   => null,
        ]);

        return response()->json([
            'success'      => true,
            'path'         => $path,
            'url'          => asset('storage/' . $path),
            'id_aptitude'  => $aptitude->id_aptitude,
        ]);
    }

    public function updateBlockOrder(Request $request, Personnage $personnage): JsonResponse
    {
        $data = $request->validate([
            'block_order' => ['required', 'array', 'min:1'],
            'block_order.*' => ['required', 'string', 'in:main_zone,armes,artefacts,constellations,competences'],
        ]);

        $personnage->update([
            'block_order' => implode(',', $data['block_order']),
        ]);

        return response()->json([
            'success' => true,
            'block_order' => $data['block_order'],
        ]);
    }
}
