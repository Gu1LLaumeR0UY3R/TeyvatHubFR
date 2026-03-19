<?php

namespace App\Console\Commands;

use App\Models\Arme;
use App\Models\Elements;
use App\Models\Etoile;
use App\Models\Personnage;
use App\Models\TypeArme;
use App\Models\TypePerso;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ImportGenshin extends Command
{
    protected $signature   = 'import:genshin';
    protected $description = 'Importe les données Genshin Impact depuis l\'API teyvat-dev';

    public function handle(): int
    {
        $base = 'https://teyvat-dev.vercel.app/api';

        // 1. Éléments
        $this->info('Import des éléments…');
        $elements = Http::timeout(15)->get("{$base}/elements")->json();
        foreach ($elements ?? [] as $e) {
            $el = Elements::updateOrCreate(
                ['libelle_element' => $e['name']],
                ['libelle_element' => $e['name']]
            );
            if (!empty($e['icon_url'])) {
                $el->photos()->updateOrCreate(
                    ['photoable_type' => Elements::class, 'photoable_id' => $el->id_element],
                    ['chemin_photo' => $e['icon_url'], 'source_url' => $e['icon_url']]
                );
            }
        }
        $this->info(count($elements ?? []) . ' éléments importés.');

        // 2. Types d'armes + Armes
        $this->info('Import des armes…');
        $weapons = Http::timeout(15)->get("{$base}/weapons")->json();
        foreach ($weapons ?? [] as $w) {
            $typeName = $w['weapon_type']['name'] ?? $w['type'] ?? 'Inconnu';
            $ta = TypeArme::updateOrCreate(['libelle_TArme' => $typeName]);
            if (!empty($w['weapon_type']['icon_url'])) {
                $ta->photos()->updateOrCreate(
                    ['photoable_type' => TypeArme::class, 'photoable_id' => $ta->id_TArmes],
                    ['chemin_photo' => $w['weapon_type']['icon_url'], 'source_url' => $w['weapon_type']['icon_url']]
                );
            }
            $etoile = Etoile::firstOrCreate(['libelle' => ($w['rarity'] ?? 3) . '★']);
            $arme = Arme::updateOrCreate(
                ['slug' => Str::slug($w['name'])],
                [
                    'nom_arme'   => $w['name'],
                    'fid_etoile' => $etoile->id_etoile,
                    'fid_TArmes' => $ta->id_TArmes,
                ]
            );
            if (!empty($w['icon_url'])) {
                $arme->photos()->updateOrCreate(
                    ['photoable_type' => Arme::class, 'photoable_id' => $arme->id_arme],
                    ['chemin_photo' => $w['icon_url'], 'source_url' => $w['icon_url']]
                );
            }
        }
        $this->info(count($weapons ?? []) . ' armes importées.');

        // 3. Personnages
        $this->info('Import des personnages…');
        $characters = Http::timeout(15)->get("{$base}/characters")->json();
        $typePerso  = TypePerso::firstOrCreate(['libelle_TP' => 'Normal']);
        foreach ($characters ?? [] as $p) {
            $etoile   = Etoile::firstOrCreate(['libelle' => ($p['rarity'] ?? 4) . '★']);
            $element  = Elements::where('libelle_element', $p['element']['name'] ?? '')->first();
            $typeArme = TypeArme::where('libelle_TArme', $p['weapon_type']['name'] ?? '')->first();

            $perso = Personnage::updateOrCreate(
                ['slug' => Str::slug($p['name'])],
                [
                    'nom_perso'   => $p['name'],
                    'fid_etoile'  => $etoile->id_etoile,
                    'fid_element' => $element?->id_element,
                    'fid_TArmes'  => $typeArme?->id_TArmes,
                    'fid_TP'      => $typePerso->id_TP,
                ]
            );
            if (!empty($p['icon_url'])) {
                $perso->photos()->updateOrCreate(
                    ['photoable_type' => Personnage::class, 'photoable_id' => $perso->id_perso],
                    ['chemin_photo' => $p['icon_url'], 'source_url' => $p['icon_url']]
                );
            }
        }
        $this->info(count($characters ?? []) . ' personnages importés.');

        $this->info('Import Genshin terminé avec succès.');
        return Command::SUCCESS;
    }
}
