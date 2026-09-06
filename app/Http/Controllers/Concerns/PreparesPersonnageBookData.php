<?php

namespace App\Http\Controllers\Concerns;

use App\Models\Arme;
use App\Models\Ennemi;
use App\Models\Personnage;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Prépare toutes les variables nécessaires au rendu du "dossier livre"
 * d'un personnage (resources/views/personnages/partials/book.blade.php).
 *
 * Utilisé par PersonnageController (vue publique) et
 * Admin\PersonnageController (aperçu admin) pour éviter de dupliquer
 * cette logique dans deux endroits.
 *
 * IMPORTANT : $personnage doit déjà avoir les relations suivantes chargées
 * (voir self::eagerLoadRelations()) avant d'appeler prepare().
 */
trait PreparesPersonnageBookData
{
    /**
     * Liste des relations à charger sur $personnage avant d'appeler prepare().
     * Utiliser : $personnage->load(PreparesPersonnageBookData::eagerLoadRelations());
     */
    public static function eagerLoadRelations(): array
    {
        return [
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
            'armesRecommandees.arme.etoile',
            'artefactsRecommandees.artefact1',
            'artefactsRecommandees.artefact2',
            'statsRecommandees',
        ];
    }

    /**
     * @return array<string, mixed> à fusionner dans compact('personnage', ...)
     */
    protected function preparePersonnageBookData(Personnage $personnage): array
    {
        $photoUrl = function ($photo) {
            if (!$photo) {
                return null;
            }
            if ($photo->source_url) {
                return $photo->source_url;
            }
            if (filter_var((string) $photo->chemin_photo, FILTER_VALIDATE_URL)) {
                return $photo->chemin_photo;
            }
            return asset('storage/' . ltrim((string) $photo->chemin_photo, '/'));
        };

        $resolvePhotoUrl = static function ($photo) use ($photoUrl): string {
            return $photoUrl($photo) ?? asset('images/placeholder.svg');
        };

        $iconPhoto = $personnage->photos->where('type', 'icone')->first() ?? $personnage->photos->first();
        $portraitPhoto = $personnage->photos->where('type', 'portrait')->first() ?? $personnage->photos->first();
        $splashPhoto = $personnage->photos->where('type', 'splash')->first() ?? $portraitPhoto;

        $iconeUrl = $resolvePhotoUrl($iconPhoto);
        $portraitUrl = $resolvePhotoUrl($portraitPhoto);
        $splashUrl = $resolvePhotoUrl($splashPhoto);

        $elementIcon = asset('images/placeholder.svg');
        if ($personnage->element) {
            $elPhoto = $personnage->element->photos->first();
            if ($elPhoto) {
                $elementIcon = $photoUrl($elPhoto) ?? $elementIcon;
            } else {
                $file = public_path('storage/photos/elements/icones/' . strtolower($personnage->element->libelle_element) . '.png');
                if (file_exists($file)) {
                    $elementIcon = asset('storage/photos/elements/icones/' . strtolower($personnage->element->libelle_element) . '.png');
                }
            }
        }

        $nation = $personnage->nations->first();
        $nationIcon = asset('images/placeholder.svg');
        if ($nation) {
            $slug = $nation->slug ?? Str::slug($nation->nom_region);
            $file = public_path('storage/photos/regions/icones/' . $slug . '.png');
            if (file_exists($file)) {
                $nationIcon = asset('storage/photos/regions/icones/' . $slug . '.png');
            } elseif (isset($nation->icone_url)) {
                $nationIcon = $nation->icone_url;
            }
        }

        $weaponTypeIcon = asset('images/placeholder.svg');
        if ($personnage->typeArme) {
            $weaponName = strtolower(trim((string) $personnage->typeArme->libelle_TArme));
            $weaponIconMap = [
                'bow' => 'Icon_Bow.webp',
                'arc' => 'Icon_Bow.webp',
                'catalyst' => 'Icon_Catalyst.webp',
                'catalyseur' => 'Icon_Catalyst.webp',
                'claymore' => 'Icon_Claymore.webp',
                'espadon' => 'Icon_Claymore.webp',
                'polearm' => 'Icon_Polearm.webp',
                'lance' => 'Icon_Polearm.webp',
                'sword' => 'Icon_Sword.webp',
                'épée' => 'Icon_Sword.webp',
                'epee' => 'Icon_Sword.webp',
            ];

            if (isset($weaponIconMap[$weaponName])) {
                $path = public_path('storage/photos/armes/' . $weaponIconMap[$weaponName]);
                if (file_exists($path)) {
                    $weaponTypeIcon = asset('storage/photos/armes/' . $weaponIconMap[$weaponName]);
                }
            }

            $typePhoto = $personnage->typeArme->photos->first();
            if ($weaponTypeIcon === asset('images/placeholder.svg') && $typePhoto) {
                $weaponTypeIcon = $photoUrl($typePhoto) ?? $weaponTypeIcon;
            }
        }

        $videoUrls = $personnage->videos->pluck('url_video')->filter()->values();

        $heroBackgroundUrl = null;
        if (!empty($personnage->background_actif)) {
            $rawBackground = (string) $personnage->background_actif;
            $heroBackgroundUrl = filter_var($rawBackground, FILTER_VALIDATE_URL)
                ? $rawBackground
                : asset('storage/' . ltrim($rawBackground, '/'));
        }

        $constellationImageFor = function ($constellation, string $slug, int $index) use ($photoUrl): string {
            if ($constellation?->photo) {
                return $photoUrl($constellation->photo) ?? asset('images/placeholder.svg');
            }

            $base = 'photos/personnages/constellations/' . $slug . '-c' . $index;
            foreach (['webp', 'png', 'jpg', 'jpeg'] as $ext) {
                $path = $base . '.' . $ext;
                if (Storage::disk('public')->exists($path)) {
                    return asset('storage/' . $path);
                }
            }

            return asset('images/placeholder.svg');
        };

        $constellations = $personnage->constellations
            ->sortBy('id_const')
            ->values()
            ->map(function ($constellation, $idx) use ($personnage, $constellationImageFor) {
                return [
                    'label' => 'C' . ($idx + 1),
                    'titre_const' => $constellation->titre_const,
                    'descri_const' => $constellation->descri_const,
                    'image_url' => $constellationImageFor($constellation, $personnage->slug, $idx + 1),
                    'recommandee' => (bool) $constellation->recommandee,
                ];
            })
            ->values();

        $constCarte = $personnage->constellations->sortBy('id_const')->first();
        $constellationMapPositions = [];
        $constellationMapLines = [];
        $constellationMapImage = '';

        if ($constCarte && is_array($constCarte->positions_const)) {
            $rawMapPayload = $constCarte->positions_const;
            $rawPoints = is_array($rawMapPayload['points'] ?? null) ? $rawMapPayload['points'] : $rawMapPayload;

            foreach ($rawPoints as $k => $point) {
                if (!is_array($point) || !isset($point['x']) || !isset($point['y'])) {
                    continue;
                }
                $key = (string) $k;
                if (!in_array($key, ['1', '2', '3', '4', '5', '6'], true)) {
                    continue;
                }
                $constellationMapPositions[$key] = [
                    'x' => round((float) $point['x'], 1),
                    'y' => round((float) $point['y'], 1),
                ];
            }

            $rawLines = is_array($rawMapPayload['lines'] ?? null) ? $rawMapPayload['lines'] : [];
            foreach ($rawLines as $line) {
                if (!is_array($line)) {
                    continue;
                }
                $from = isset($line['from']) ? (int) $line['from'] : null;
                $to = isset($line['to']) ? (int) $line['to'] : null;
                if (!$from || !$to || $from === $to) {
                    continue;
                }
                if ($from < 1 || $from > 6 || $to < 1 || $to > 6) {
                    continue;
                }
                $constellationMapLines[] = ['from' => $from, 'to' => $to];
            }
        }

        if ($constCarte?->photo) {
            $constellationMapImage = $photoUrl($constCarte->photo) ?? '';
        }

        $rotationTeams = $personnage->teamCompositions
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
                                        'type' => (string) ($a->typeApti?->libelle_Apti ?? ''),
                                        'icon' => $resolvePhotoUrl($a->photos?->first()),
                                    ]);
                            }

                            return [
                                'slot' => (int) $membre->slot,
                                'nom' => (string) ($perso?->nom_perso ?? ''),
                                'element' => (string) ($perso?->element?->libelle_element ?? ''),
                                'icon' => $resolvePhotoUrl($perso?->photos?->first()),
                                'aptitudes' => $aptitudes->values()->all(),
                            ];
                        })
                        ->all(),
                ];
            })
            ->values();

        $aptitudesJson = $personnage->aptitudes
            ->sortBy('id_aptitude')
            ->values()
            ->map(fn($a) => [
                'id_aptitude' => (int) $a->id_aptitude,
                'titre_apti' => $a->titre_apti,
                'descri_apti' => $a->descri_apti ?? '',
                'fid_TypeApti' => (int) $a->fid_TypeApti,
                'type_libelle' => (string) ($a->typeApti?->libelle_Apti ?? ''),
                'image_url' => $resolvePhotoUrl($a->photos->first()),
            ]);

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

        $weaponOrderIndex = array_flip([1, 4, 2, 5, 3, 6]);
        $orderedWeaponRecommendations = $personnage->armesRecommandees
            ->sortBy(function ($item) use ($weaponOrderIndex) {
                $position = (int) ($item->position ?? 0);
                return $weaponOrderIndex[$position] ?? (100 + $position);
            })
            ->values();

        return [
            'photoUrl' => $photoUrl,
            'iconeUrl' => $iconeUrl,
            'portraitUrl' => $portraitUrl,
            'splashUrl' => $splashUrl,
            'elementIcon' => $elementIcon,
            'nation' => $nation,
            'nationIcon' => $nationIcon,
            'weaponTypeIcon' => $weaponTypeIcon,
            'videoUrls' => $videoUrls,
            'heroBackgroundUrl' => $heroBackgroundUrl,
            'constellations' => $constellations,
            'constellationMapPositions' => $constellationMapPositions,
            'constellationMapLines' => $constellationMapLines,
            'constellationMapImage' => $constellationMapImage,
            'rotationTeams' => $rotationTeams,
            'aptitudesJson' => $aptitudesJson,
            'storyReferences' => $storyReferences,
            'orderedWeaponRecommendations' => $orderedWeaponRecommendations,
        ];
    }
}
