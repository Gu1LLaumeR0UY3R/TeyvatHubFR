<?php

namespace App\Console\Commands;

use App\Models\Arme;
use App\Models\Elements;
use App\Models\Etoile;
use App\Models\Materiaux;
use App\Models\Nation;
use App\Models\Personnage;
use App\Models\Photo;
use App\Models\Rarete;
use App\Models\TypeArme;
use App\Models\TypeMateriaux;
use App\Models\TypePerso;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ImportGenshin extends Command
{
    protected $signature   = 'import:genshin';
    protected $description = 'Importe les données Genshin Impact depuis l\'API teyvat-dev';
    private array $stats = ['created' => 0, 'updated' => 0, 'unchanged' => 0];

    public function handle(): int
    {
        $base = 'https://teyvat-dev.vercel.app/api';
        $this->resetStats();

        try {
            // 1. Éléments
            $this->import_elements($base);
            
            // 2. Nations (Régions)
            $this->import_nations($base);
            
            // 3. Types d'armes
            $this->import_weapon_types($base);
            
            // 4. Armes
            $this->import_weapons($base);
            
            // 5. Types de personnages + Personnages
            $this->import_characters($base);
            
            // 6. Types de matériaux + Matériaux
            $this->import_materials($base);

            $this->print_summary();
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Erreur lors de l\'import: ' . $e->getMessage());
            Log::error('ImportGenshin error', ['exception' => $e]);
            return Command::FAILURE;
        }
    }

    private function resetStats(): void
    {
        $this->stats = ['created' => 0, 'updated' => 0, 'unchanged' => 0];
    }

    private function import_elements(string $base): void
    {
        $this->info('🔄 Import des éléments…');
        $elements = Http::timeout(15)->get("{$base}/elements")->json() ?? [];
        
        foreach ($elements as $e) {
            $el = Elements::firstOrNew(['libelle_element' => $e['name']]);
            
            if ($el->isDirty()) {
                $el->save();
                $this->stats['created']++;
            } else {
                $this->stats['unchanged']++;
            }

            if (!empty($e['icon_url'])) {
                $photoType = Relation::getMorphAlias(Elements::class);
                $el->photos()->updateOrCreate(
                    ['photoable_type' => $photoType, 'photoable_id' => $el->id_element],
                    ['chemin_photo' => $e['icon_url'], 'source_url' => $e['icon_url']]
                );
            }
        }
        
        $this->info("✅ Éléments: " . count($elements) . " traités");
    }

    private function import_nations(string $base): void
    {
        $this->info('🔄 Import des nations…');
        $nations = Http::timeout(15)->get("{$base}/regions")->json() ?? [];
        
        foreach ($nations as $n) {
            $nation = Nation::firstOrNew(['slug' => Str::slug($n['name'])]);
            
            if ($nation->isDirty()) {
                $nation->fill([
                    'nom_region' => $n['name'],
                    'slug' => Str::slug($n['name']),
                ]);
                $nation->save();
                $this->stats['created']++;
            } else {
                $this->stats['unchanged']++;
            }

            if (!empty($n['icon_url'])) {
                $photoType = Relation::getMorphAlias(Nation::class);
                $nation->photos()->updateOrCreate(
                    ['photoable_type' => $photoType, 'photoable_id' => $nation->id_region],
                    ['chemin_photo' => $n['icon_url'], 'source_url' => $n['icon_url']]
                );
            }
        }
        
        $this->info("✅ Nations: " . count($nations) . " traitées");
    }

    private function import_weapon_types(string $base): void
    {
        $this->info('🔄 Import des types d\'armes…');
        $weapons = Http::timeout(15)->get("{$base}/weapons")->json() ?? [];
        $types = collect($weapons)
            ->map(fn($w) => $w['weapon_type']['name'] ?? $w['type']['name'] ?? null)
            ->filter(fn($t) => !empty($t))
            ->unique()
            ->values();
        
        foreach ($types as $typeName) {
            $ta = TypeArme::firstOrNew(['libelle_TArme' => $typeName]);
            
            if ($ta->isDirty()) {
                $ta->save();
                $this->stats['created']++;
            } else {
                $this->stats['unchanged']++;
            }
        }
        
        $this->info("✅ Types d'armes: " . count($types) . " traités");
    }

    private function import_weapons(string $base): void
    {
        $this->info('🔄 Import des armes…');
        $weapons = Http::timeout(15)->get("{$base}/weapons")->json() ?? [];
        
        foreach ($weapons as $w) {
            $arme = Arme::firstOrNew(['slug' => Str::slug($w['name'])]);
            
            $typeName = $w['weapon_type']['name'] ?? $w['type']['name'] ?? null;
            if (empty($typeName)) {
                $this->warn("⚠️  Arme '{$w['name']}' n'a pas de type d'arme, ignorée");
                continue;
            }
            
            $typeArme = TypeArme::where('libelle_TArme', $typeName)->first();
            if (!$typeArme) {
                $this->warn("⚠️  Type d'arme '{$typeName}' non trouvé pour '{$w['name']}'");
                continue;
            }
            
            $etoile = Etoile::firstOrCreate(['libelle' => ($w['rarity'] ?? 3) . '★']);

            if ($arme->isDirty()) {
                $arme->fill([
                    'nom_arme' => $w['name'],
                    'fid_etoile' => $etoile->id_etoile,
                    'fid_TArmes' => $typeArme->id_TArmes,
                ]);
                $arme->save();
                $this->stats['created']++;
            } else {
                $this->stats['unchanged']++;
            }

            if (!empty($w['icon_url'])) {
                $photoType = Relation::getMorphAlias(Arme::class);
                $arme->photos()->updateOrCreate(
                    ['photoable_type' => $photoType, 'photoable_id' => $arme->id_arme],
                    ['chemin_photo' => $w['icon_url'], 'source_url' => $w['icon_url']]
                );
            }
        }
        
        $this->info("✅ Armes: " . count($weapons) . " traitées");
    }

    private function import_characters(string $base): void
    {
        $this->info('🔄 Import des personnages…');
        $characters = Http::timeout(15)->get("{$base}/characters")->json() ?? [];
        
        $typePerso = TypePerso::firstOrCreate(['libelle_TP' => 'Personnage jouable']);
        
        foreach ($characters as $p) {
            $perso = Personnage::firstOrNew(['slug' => Str::slug($p['name'])]);
            
            $etoile = Etoile::firstOrCreate(['libelle' => ($p['rarity'] ?? 4) . '★']);
            $element = Elements::where('libelle_element', $p['element']['name'] ?? '')->first();
            $typeArme = TypeArme::where('libelle_TArme', $p['weapon_type']['name'] ?? '')->first();

            if ($perso->isDirty()) {
                $perso->fill([
                    'nom_perso' => $p['name'],
                    'fid_etoile' => $etoile->id_etoile,
                    'fid_element' => $element?->id_element,
                    'fid_TArmes' => $typeArme?->id_TArmes,
                    'fid_TP' => $typePerso->id_TP,
                ]);
                $perso->save();
                $this->stats['created']++;
            } else {
                $this->stats['unchanged']++;
            }

            if (!empty($p['icon_url'])) {
                $photoType = Relation::getMorphAlias(Personnage::class);
                $perso->photos()->updateOrCreate(
                    ['photoable_type' => $photoType, 'photoable_id' => $perso->id_perso],
                    ['chemin_photo' => $p['icon_url'], 'source_url' => $p['icon_url']]
                );
            }
        }
        
        $this->info("✅ Personnages: " . count($characters) . " traités");
    }

    private function import_materials(string $base): void
    {
        $this->info('🔄 Import des matériaux…');
        $materials = Http::timeout(15)->get("{$base}/materials")->json() ?? [];
        
        foreach ($materials as $m) {
            $mat = Materiaux::firstOrNew(['slug' => Str::slug($m['name'])]);
            
            $typeName = $m['category'] ?? 'Autre';
            $typeMat = TypeMateriaux::firstOrCreate(['libelle_typeM' => $typeName]);
            
            $rareté = Rarete::firstOrCreate(['libelle_rarete' => ($m['rarity'] ?? 1) . '★']);

            if ($mat->isDirty()) {
                $mat->fill([
                    'nom_mat' => $m['name'],
                    'fid_typeM' => $typeMat->id_typeM,
                    'fid_rareté' => $rareté->id_rareté,
                ]);
                $mat->save();
                $this->stats['created']++;
            } else {
                $this->stats['unchanged']++;
            }

            if (!empty($m['icon_url'])) {
                $photoType = Relation::getMorphAlias(Materiaux::class);
                $mat->photos()->updateOrCreate(
                    ['photoable_type' => $photoType, 'photoable_id' => $mat->id_materiaux],
                    ['chemin_photo' => $m['icon_url'], 'source_url' => $m['icon_url']]
                );
            }
        }
        
        $this->info("✅ Matériaux: " . count($materials) . " traités");
    }

    private function print_summary(): void
    {
        $this->info("\n" . str_repeat('=', 60));
        $this->line('<info>📊 Résumé de l\'import</info>');
        $this->line('  ✨ Créés:       ' . $this->stats['created']);
        $this->line('  ♻️  Mis à jour:  ' . $this->stats['updated']);
        $this->line('  ✅ Inchangés:   ' . $this->stats['unchanged']);
        $this->line('  ━━━━━━━━━━━━━━━━━━━');
        $total = array_sum($this->stats);
        $this->line('  📦 Total:       ' . $total);
        $this->info(str_repeat('=', 60));
    }
}

