<?php

namespace App\Http\Controllers;

use App\Models\Elements;
use App\Models\Etoile;
use App\Models\Arme;
use App\Models\Ennemi;
use App\Models\Personnage;
use App\Models\TypeArme;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PersonnageController extends Controller
{
    public function index(Request $request): View
    {
        $query = Personnage::with(['element', 'etoile', 'typeArme', 'photos']);

        if ($request->search) {
            $query->where('nom_perso', 'LIKE', '%' . $request->search . '%');
        }

        if ($request->element) {
            $query->where('fid_element', $request->element);
        }

        if ($request->rarete) {
            $query->where('fid_etoile', $request->rarete);
        }

        switch ($request->sort) {
            case 'rarete_desc':
                $query->orderBy('fid_etoile', 'desc');
                break;
            case 'rarete_asc':
                $query->orderBy('fid_etoile', 'asc');
                break;
            case 'nom_desc':
                $query->orderBy('nom_perso', 'desc');
                break;
            case 'element':
                $query->orderBy('fid_element');
                break;
            default:
                $query->orderBy('nom_perso');
        }

        $personnages = $query->get();
        $elements    = Elements::orderBy('libelle_element')->get();
        $etoiles     = Etoile::query()
            ->select('libelle')
            ->whereIn('libelle', ['4★', '5★'])
            ->distinct()
            ->orderBy('libelle')
            ->get();
        $typeArmes   = TypeArme::orderBy('libelle_TArme')->get();

        return view('personnages.index', compact('personnages', 'elements', 'etoiles', 'typeArmes'));
    }

    public function show(Personnage $personnage): View
    {
        $personnage->load([
            'element',
            'etoile',
            'bio',
            'aptitudes.typeApti',
            'aptitudes.photos',
            'constellations',
            'specialite.plat.photos',
            'roles',
            'photos',
            'videos',
            'histoires',
            'nations',
            'typeArme',
            'armesRecommandees.arme.typeArme.photos',
            'artefactsRecommandees.artefact1',
            'artefactsRecommandees.artefact2',
        ]);

        $aptitudesJson = $personnage->aptitudes
            ->sortBy('id_aptitude')
            ->values()
            ->map(fn($a) => [
                'id_aptitude'  => (int) $a->id_aptitude,
                'titre_apti'   => $a->titre_apti,
                'descri_apti'  => $a->descri_apti ?? '',
                'fid_TypeApti' => (int) $a->fid_TypeApti,
                'image_url'    => $a->photos->first()?->source_url
                               ?? ($a->photos->first()?->chemin_photo
                                   ? asset('storage/' . $a->photos->first()->chemin_photo)
                                   : null),
            ]);

        $resolvePhotoUrl = static function ($photo): string {
            if (!$photo) {
                return asset('images/placeholder.svg');
            }
            if ($photo->source_url) {
                return $photo->source_url;
            }
            if (filter_var((string) $photo->chemin_photo, FILTER_VALIDATE_URL)) {
                return $photo->chemin_photo;
            }

            return asset('storage/' . ltrim((string) $photo->chemin_photo, '/'));
        };

        $referencesAptitudes = $personnage->aptitudes
            ->sortBy('id_aptitude')
            ->values()
            ->mapWithKeys(fn($aptitude) => [
                (string) $aptitude->id_aptitude => [
                    'label' => $aptitude->titre_apti,
                    'image' => $resolvePhotoUrl($aptitude->photos->first()),
                    'url' => '#aptitude-' . $aptitude->id_aptitude,
                ],
            ])
            ->all();

        $referencesArmes = Arme::with('photos')
            ->orderBy('nom_arme')
            ->get(['id_arme', 'nom_arme', 'slug'])
            ->mapWithKeys(fn($arme) => [
                $arme->slug => [
                    'label' => $arme->nom_arme,
                    'image' => $resolvePhotoUrl($arme->photos->first()),
                    'url' => route('armes.show', $arme),
                ],
            ])
            ->all();

        $ennemis = Ennemi::with(['photos', 'typeEnnemi'])
            ->orderBy('nom_ennemi')
            ->get(['id_ennemi', 'nom_ennemi', 'slug', 'fid_typeEnne']);

        $referencesMonstres = $ennemis
            ->mapWithKeys(fn($ennemi) => [
                $ennemi->slug => [
                    'label' => $ennemi->nom_ennemi,
                    'image' => $resolvePhotoUrl($ennemi->photos->first()),
                    'url' => route('ennemis.show', $ennemi),
                    'is_boss' => str_contains(strtolower((string) ($ennemi->typeEnnemi?->libelle_Type ?? '')), 'boss'),
                ],
            ])
            ->all();

        $referencesBoss = collect($referencesMonstres)
            ->filter(fn($entry) => !empty($entry['is_boss']))
            ->all();

        $storyReferences = [
            'aptitude' => $referencesAptitudes,
            'arme' => $referencesArmes,
            'monstre' => $referencesMonstres,
            'boss' => $referencesBoss,
        ];

        return view('personnages.show', compact('personnage', 'aptitudesJson', 'storyReferences'));
    }
}
