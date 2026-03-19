<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Animal;
use App\Models\Arme;
use App\Models\Ennemi;
use App\Models\Ingredient;
use App\Models\Personnage;
use App\Models\Plat;
use App\Models\Region;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AdminController extends Controller
{
    public function dashboard(): View
    {
        $stats = [
            'personnages'  => Personnage::count(),
            'armes'        => Arme::count(),
            'ennemis'      => Ennemi::count(),
            'animaux'      => Animal::count(),
            'plats'        => Plat::count(),
            'ingredients'  => Ingredient::count(),
            'regions'      => Region::count(),
            'utilisateurs' => User::count(),
        ];

        return view('admin.dashboard', compact('stats'));
    }

    public function importGenshin(): RedirectResponse
    {
        try {
            $base = 'https://teyvat-dev.vercel.app/api';

            // Éléments
            $elements = Http::timeout(15)->get("{$base}/elements")->json();
            foreach ($elements ?? [] as $e) {
                $el = \App\Models\Elements::updateOrCreate(
                    ['libelle_element' => $e['name']],
                    ['libelle_element' => $e['name']]
                );
                if (!empty($e['icon_url'])) {
                    $el->photos()->updateOrCreate(
                        ['photoable_type' => \App\Models\Elements::class, 'photoable_id' => $el->id_element],
                        ['chemin_photo' => $e['icon_url'], 'source_url' => $e['icon_url']]
                    );
                }
            }

            // Types d'armes + Armes
            $weapons = Http::timeout(15)->get("{$base}/weapons")->json();
            foreach ($weapons ?? [] as $w) {
                $typeName = $w['weapon_type']['name'] ?? $w['type'] ?? 'Inconnu';
                $ta = \App\Models\TypeArme::updateOrCreate(['libelle_TArme' => $typeName]);
                if (!empty($w['weapon_type']['icon_url'])) {
                    $ta->photos()->updateOrCreate(
                        ['photoable_type' => \App\Models\TypeArme::class, 'photoable_id' => $ta->id_TArmes],
                        ['chemin_photo' => $w['weapon_type']['icon_url'], 'source_url' => $w['weapon_type']['icon_url']]
                    );
                }
                $etoile = \App\Models\Etoile::firstOrCreate(['libelle' => ($w['rarity'] ?? 3) . '★']);
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

            // Personnages
            $characters = Http::timeout(15)->get("{$base}/characters")->json();
            $typePerso = \App\Models\TypePerso::firstOrCreate(['libelle_TP' => 'Normal']);
            foreach ($characters ?? [] as $p) {
                $etoile   = \App\Models\Etoile::firstOrCreate(['libelle' => ($p['rarity'] ?? 4) . '★']);
                $element  = \App\Models\Elements::where('libelle_element', $p['element']['name'] ?? '')->first();
                $typeArme = \App\Models\TypeArme::where('libelle_TArme', $p['weapon_type']['name'] ?? '')->first();

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

            return redirect()->route('admin.dashboard')
                ->with('import_success', 'Import Genshin terminé avec succès.');
        } catch (\Throwable $e) {
            Log::error('Import Genshin échoué: ' . $e->getMessage());
            return redirect()->route('admin.dashboard')
                ->with('import_error', 'Erreur lors de l\'import: ' . $e->getMessage());
        }
    }
}
