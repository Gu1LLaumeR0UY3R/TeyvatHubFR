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
            'constellations.photo',
            'specialite.plat.photos',
            'roles',
            'photos',
            'videos',
            'histoires',
            'nations',
            'typeArme',
            'teamCompositions.membres.personnage.element',
            'teamCompositions.membres.personnage.photos',
            'teamCompositions.membres.personnage.aptitudes.typeApti',
            'teamCompositions.membres.personnage.aptitudes.photos',
            'armesRecommandees.arme.typeArme.photos',
            'armesRecommandees.arme.statsNiveaux',
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

        $teamsRotationJson = $personnage->teamCompositions
            ->whereIn('tag', ['recommended', 'f2p'])
            ->sortBy('id_team')
            ->groupBy('tag')
            ->map(function ($group) use ($resolvePhotoUrl) {
                $team = $group->first();

                $rotationSequence = [];
                if ($team->rotation) {
                    $decoded = json_decode($team->rotation, true);
                    if (is_array($decoded)) {
                        $rotationSequence = $decoded;
                    }
                }

                return [
                    'id_team' => (int) $team->id_team,
                    'tag' => (string) $team->tag,
                    'type_reaction' => (string) $team->type_reaction,
                    'rotationSequence' => $rotationSequence,
                    'membres' => $team->membres
                        ->sortBy('slot')
                        ->values()
                        ->map(function ($membre) use ($resolvePhotoUrl) {
                            $perso = $membre->personnage;

                            $aptitudes = collect();
                            if ($membre->slot <= 4 && $perso) {
                                $aptitudes = $perso->aptitudes
                                    ->filter(fn($a) => in_array($a->fid_TypeApti, [6, 7, 8]))
                                    ->sortBy('fid_TypeApti')
                                    ->values()
                                    ->map(fn($a) => [
                                        'titre' => (string) $a->titre_apti,
                                        'type'  => (string) ($a->typeApti?->libelle_Apti ?? ''),
                                        'icon'  => $resolvePhotoUrl($a->photos?->first()),
                                    ]);
                            }

                            return [
                                'slot'      => (int) $membre->slot,
                                'nom'       => (string) ($perso?->nom_perso ?? ''),
                                'element'   => (string) ($perso?->element?->libelle_element ?? ''),
                                'icon'      => $resolvePhotoUrl($perso?->photos?->first()),
                                'aptitudes' => $aptitudes->values()->all(),
                            ];
                        })
                        ->all(),
                ];
            })
            ->values();

        return view('personnages.show', compact('personnage', 'aptitudesJson', 'storyReferences', 'teamsRotationJson'));
    }
}
