<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Aptitude;
use App\Models\Arme;
use App\Models\Constellation;
use App\Models\PersonnageArtefactRecommandee;
use App\Models\PersonnageArmeRecommandee;
use App\Models\PersonnageStatsRecommandee;
use App\Models\PersonnageHistoire;
use App\Models\PersonnageVideo;
use App\Models\Nation;
use App\Models\TeamComposition;
use App\Models\TeamCompositionMembre;
use App\Models\TeamSlotRemplacant;
use App\Models\TypeArme;
use Illuminate\Http\Request;
use App\Models\Personnage;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;
use App\Services\SnapshotService;

class PersonnageBlockController extends Controller
{
    public function __construct(private readonly SnapshotService $snapshotService)
    {
    }

    private const ARTEFACT_MAIN_STATS_SABLIER = [
        'ATK%',
        'HP%',
        'DEF%',
        'Recharge d\'energie%',
        'Maitrise elementaire',
    ];

    private const ARTEFACT_MAIN_STATS_GOBELET = [
        'ATK%',
        'HP%',
        'DEF%',
        'Maitrise elementaire',
        'Bonus DGT Pyro%',
        'Bonus DGT Hydro%',
        'Bonus DGT Electro%',
        'Bonus DGT Cryo%',
        'Bonus DGT Anemo%',
        'Bonus DGT Geo%',
        'Bonus DGT Dendro%',
        'Bonus DGT Physiques%',
    ];

    private const ARTEFACT_MAIN_STATS_COURONNE = [
        'ATK%',
        'HP%',
        'DEF%',
        'Maitrise elementaire',
        'Taux CRIT%',
        'DGT CRIT%',
        'Bonus de soin%',
    ];

    private const ARTEFACT_SUB_STATS = [
        'ATK%',
        'HP%',
        'DEF%',
        'ATK',
        'HP',
        'DEF',
        'Taux CRIT%',
        'DGT CRIT%',
        'Recharge d\'energie%',
        'Maitrise elementaire',
    ];

    public function updateMainZone(Request $request, Personnage $personnage): JsonResponse
    {
        $oldSnapshotState = $this->snapshotService->captureMainZoneState($personnage->fresh());

        $nationTable = Schema::hasTable('nation')
            ? 'nation'
            : (Schema::hasTable('région') ? 'région' : null);

        $nationRules = ['sometimes', 'array'];
        $nationItemRules = ['integer'];

        if ($nationTable) {
            $nationItemRules[] = Rule::exists($nationTable, 'id_region');
        }

        $data = $request->validate([
        'nom_perso' => ['sometimes', 'string', 'max:100'],
        'voix_va' => ['sometimes', 'nullable', 'string', 'max:150'],
        'voix_vj' => ['sometimes', 'nullable', 'string', 'max:150'],
        'voix_vc' => ['sometimes', 'nullable', 'string', 'max:150'],
        'fid_element' => ['sometimes', 'integer', 'exists:elements,id_element'],
        'fid_etoile' => ['sometimes', 'integer', 'exists:etoile,id_etoile'],
        'fid_TArmes' => ['nullable', 'integer', 'exists:type_armes,id_TArmes'],
        'fid_TP' => ['nullable', 'integer', 'exists:type_perso,id_TP'],
        'versatilite' => ['sometimes', 'nullable', 'integer', 'min:0', 'max:10'],
        'background_actif' => ['nullable', 'string', 'max:255'],
        'fid_nations' => $nationRules,
        'fid_nations.*' => $nationItemRules,
        'videos' => ['sometimes', 'array'],
        'videos.*.url_video' => ['sometimes', 'url', 'max:255'],
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

        $updatePayload = [];

        // Ne mettre à jour que les champs qui ont été envoyés
        if (array_key_exists('nom_perso', $data)) {
            $updatePayload['nom_perso'] = $data['nom_perso'];
        }
        foreach (['voix_va', 'voix_vj', 'voix_vc'] as $voiceField) {
            if (array_key_exists($voiceField, $data)) {
                $updatePayload[$voiceField] = trim((string) ($data[$voiceField] ?? '')) ?: null;
            }
        }

        if (array_key_exists('fid_element', $data)) {
            $updatePayload['fid_element'] = $data['fid_element'];
        }

        if (array_key_exists('fid_etoile', $data)) {
            $updatePayload['fid_etoile'] = $data['fid_etoile'];
        }

        if (array_key_exists('fid_TArmes', $data)) {
            $updatePayload['fid_TArmes'] = $data['fid_TArmes'] ?? null;
        }

        if (array_key_exists('fid_TP', $data)) {
            $updatePayload['fid_TP'] = $data['fid_TP'] ?? null;
        }

        if (array_key_exists('fid_TP', $data)) {
            $updatePayload['fid_TP'] = $data['fid_TP'] ?? null;
        }

        if (array_key_exists('versatilite', $data)) {
            $updatePayload['versatilite'] = $data['versatilite'] ?? null;
        }

        if (array_key_exists('fid_TArmes', $data) && $armeIcon) {
            if (Schema::hasColumn('personnage', 'arme_icon')) {
                $updatePayload['arme_icon'] = $armeIcon;
            }
        }

        if (array_key_exists('background_actif', $data)) {
            if (Schema::hasColumn('personnage', 'background_actif')) {
                $updatePayload['background_actif'] = $data['background_actif'] ?? null;
            }
        }

        SnapshotService::withoutRecording(function () use ($personnage, $updatePayload, $data, $nationTable): void {
            if (!empty($updatePayload)) {
                $personnage->update($updatePayload);
            }

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
        });

        $newSnapshotState = $this->snapshotService->captureMainZoneState($personnage->fresh());
        $this->snapshotService->createManualUpdate(
            $personnage,
            $oldSnapshotState,
            $newSnapshotState,
            $this->resolveAdminId(),
        );

        return response()->json([
            'success' => true,
            'message' => 'Zone principale mise à jour.',
        ]);
    }

    public function uploadImage(Request $request, Personnage $personnage): JsonResponse
    {
        $oldSnapshotState = ['photos' => $this->snapshotService->captureMainZoneState($personnage->fresh())['photos']];

        $data = $request->validate([
            'image_type' => ['required', 'string', Rule::in(['icone', 'portrait', 'full'])],
            'image' => ['required', 'image', 'max:4096'],
        ]);

        $imageType = $data['image_type'];
        $storageType = $imageType === 'full' ? 'portrait' : $imageType;
        $dir = $storageType === 'portrait'
            ? 'photos/personnages/personnage_full'
            : 'photos/personnages/icones_personnage';

        SnapshotService::withoutRecording(function () use ($personnage, $storageType, $request, $dir, $imageType): void {
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
        });

        $newSnapshotState = ['photos' => $this->snapshotService->captureMainZoneState($personnage->fresh())['photos']];
        $this->snapshotService->createManualUpdate(
            $personnage,
            $oldSnapshotState,
            $newSnapshotState,
            $this->resolveAdminId(),
        );

        $path = $personnage->fresh('photos')->photos->where('type', $storageType)->first()?->chemin_photo;

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

    private function resolveAdminId(): ?int
    {
        $adminId = session('admin_id');

        return $adminId ? (int) $adminId : null;
    }

    public function updateArtefactsRecommandees(Request $request, Personnage $personnage): JsonResponse
    {
        $oldSnapshotState = ['artefacts_recommandes' => $this->snapshotService->captureRecommendedArtefactsState($personnage->fresh())];

        $data = $request->validate([
            'nom_build' => ['sometimes', 'nullable', 'string', 'max:100'],
            'builds' => ['sometimes', 'array', 'max:4'],
            'builds.*.artefact1_id' => ['sometimes', 'integer', 'exists:artefact,id_artefact'],
            'builds.*.pieces_1' => ['sometimes', 'integer', Rule::in([2, 4])],
            'builds.*.artefact2_id' => ['nullable', 'integer', 'exists:artefact,id_artefact'],
            'builds.*.pieces_2' => ['nullable', 'integer', Rule::in([2])],
            'builds.*.main_stat_sablier' => ['nullable', Rule::in(self::ARTEFACT_MAIN_STATS_SABLIER)],
            'builds.*.main_stat_gobelet' => ['nullable', Rule::in(self::ARTEFACT_MAIN_STATS_GOBELET)],
            'builds.*.main_stat_couronne' => ['nullable', Rule::in(self::ARTEFACT_MAIN_STATS_COURONNE)],
            'builds.*.sub_stats' => ['nullable', 'array', 'max:4'],
            'builds.*.sub_stats.*' => ['string', Rule::in(self::ARTEFACT_SUB_STATS)],
            'builds.*.commentaire' => ['nullable', 'string', 'max:500'],
        ]);

        $nomBuild = trim((string) ($data['nom_build'] ?? ''));

        if (!array_key_exists('builds', $data) || empty($data['builds'])) {
            SnapshotService::withoutRecording(function () use ($personnage, $nomBuild): void {
                PersonnageArtefactRecommandee::query()
                    ->where('fid_perso', $personnage->id_perso)
                    ->where('nom_build', $nomBuild)
                    ->delete();
            });

            $this->snapshotService->createManualUpdate(
                $personnage,
                $oldSnapshotState,
                ['artefacts_recommandes' => []],
                $this->resolveAdminId(),
            );

            return response()->json(['success' => true]);
        }

        $builds = collect($data['builds'])->values();

        foreach ($builds as $index => $build) {
            $pieces1 = (int) $build['pieces_1'];
            $artefact2Id = $build['artefact2_id'] ?? null;
            $pieces2 = $build['pieces_2'] ?? null;
            $subStats = collect($build['sub_stats'] ?? [])
                ->map(fn ($stat) => trim((string) $stat))
                ->filter()
                ->values();

            if ($pieces1 === 2 && (!$artefact2Id || (int) $pieces2 !== 2)) {
                throw ValidationException::withMessages([
                    'builds.' . $index . '.artefact2_id' => 'Un build 2P+2P requiert un second set en 2P.',
                ]);
            }

            if ($pieces1 === 2 && (int) $build['artefact1_id'] === (int) $artefact2Id) {
                throw ValidationException::withMessages([
                    'builds.' . $index . '.artefact2_id' => 'Un build 2P+2P doit utiliser deux sets différents.',
                ]);
            }

            if ($subStats->count() > 4) {
                throw ValidationException::withMessages([
                    'builds.' . $index . '.sub_stats' => 'Maximum 4 sous-stats recommandees par build.',
                ]);
            }

            if ($subStats->duplicates()->isNotEmpty()) {
                throw ValidationException::withMessages([
                    'builds.' . $index . '.sub_stats' => 'Les sous-stats recommandees doivent etre uniques.',
                ]);
            }
        }

        SnapshotService::withoutRecording(function () use ($personnage, $builds, $nomBuild): void {
            PersonnageArtefactRecommandee::query()
                ->where('fid_perso', $personnage->id_perso)
                ->where('nom_build', $nomBuild)
                ->delete();

            foreach ($builds as $index => $build) {
                $pieces1 = (int) $build['pieces_1'];
                $subStats = collect($build['sub_stats'] ?? [])
                    ->map(fn ($stat) => trim((string) $stat))
                    ->filter()
                    ->values();

                PersonnageArtefactRecommandee::query()->create([
                    'fid_perso' => $personnage->id_perso,
                    'nom_build' => $nomBuild,
                    'fid_artefact_1' => (int) $build['artefact1_id'],
                    'pieces_1' => $pieces1 === 4 ? '4p' : '2p',
                    'fid_artefact_2' => $pieces1 === 2 ? (int) $build['artefact2_id'] : null,
                    'pieces_2' => $pieces1 === 2 ? '2p' : null,
                    'main_stat_sablier' => $build['main_stat_sablier'] ?? null,
                    'main_stat_gobelet' => $build['main_stat_gobelet'] ?? null,
                    'main_stat_couronne' => $build['main_stat_couronne'] ?? null,
                    'sub_stats' => $subStats->isNotEmpty() ? $subStats->implode(', ') : null,
                    'commentaire' => isset($build['commentaire']) ? trim((string) $build['commentaire']) ?: null : null,
                    'position' => $index + 1,
                ]);
            }
        });

        $newSnapshotState = ['artefacts_recommandes' => $this->snapshotService->captureRecommendedArtefactsState($personnage->fresh())];
        $this->snapshotService->createManualUpdate(
            $personnage,
            $oldSnapshotState,
            $newSnapshotState,
            $this->resolveAdminId(),
        );

        return response()->json(['success' => true]);
    }

    public function deleteArtefactRecommande(Request $request, Personnage $personnage, int $id_build): JsonResponse
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

    public function updateArmesRecommandees(Request $request, Personnage $personnage): JsonResponse
    {
        $data = $request->validate([
            'nom_build' => ['sometimes', 'nullable', 'string', 'max:100'],
            'armes' => ['sometimes', 'array', 'max:6'],
            'armes.*.id_arme' => ['required', 'integer', 'exists:armes,id_arme'],
            'armes.*.rang' => ['nullable', 'integer', 'min:1', 'max:5'],
            'armes.*.is_starter' => ['nullable', 'boolean'],
            'armes.*.origine' => ['nullable', Rule::in(['tirage', 'pull', 'evenement', 'craft', 'achat'])],
            'armes.*.commentaire' => ['nullable', 'string', 'max:500'],
        ]);

        $nomBuild = trim((string) ($data['nom_build'] ?? ''));

        if (!array_key_exists('armes', $data) || empty($data['armes'])) {
            $oldSnapshotState = ['armes_recommandees' => $this->snapshotService->captureRecommendedWeaponsState($personnage->fresh('armesRecommandees'))];

            SnapshotService::withoutRecording(function () use ($personnage, $nomBuild): void {
                PersonnageArmeRecommandee::query()
                    ->where('fid_perso', $personnage->id_perso)
                    ->where('nom_build', $nomBuild)
                    ->delete();
            });

            $this->snapshotService->createManualUpdate(
                $personnage,
                $oldSnapshotState,
                ['armes_recommandees' => []],
                $this->resolveAdminId(),
            );

            return response()->json([
                'success' => true,
                'armes' => [],
            ]);
        }

        $armes = collect($data['armes'])->values();

        $expectedTypeId = $personnage->fid_TArmes;
        if ($expectedTypeId) {
            foreach ($armes as $index => $armeData) {
                $armeModel = Arme::find($armeData['id_arme']);
                if (!$armeModel || $armeModel->fid_TArmes !== $expectedTypeId) {
                    throw ValidationException::withMessages([
                        'armes.' . $index . '.id_arme' => 'Cette arme n\'est pas compatible avec le type d\'arme du personnage.',
                    ]);
                }
            }
        }

        $oldSnapshotState = ['armes_recommandees' => $this->snapshotService->captureRecommendedWeaponsState($personnage->fresh('armesRecommandees'))];

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
                'origine' => (($arme['origine'] ?? null) === 'pull') ? 'tirage' : ($arme['origine'] ?? null),
                'commentaire' => isset($arme['commentaire']) ? trim((string) $arme['commentaire']) ?: null : null,
            ];
        })->values();

        SnapshotService::withoutRecording(function () use ($personnage, $armes, $nomBuild): void {
            PersonnageArmeRecommandee::query()
                ->where('fid_perso', $personnage->id_perso)
                ->where('nom_build', $nomBuild)
                ->delete();

            foreach ($armes as $index => $arme) {
                PersonnageArmeRecommandee::query()->create([
                    'fid_perso' => $personnage->id_perso,
                    'nom_build' => $nomBuild,
                    'fid_arme' => $arme['id_arme'],
                    'position' => $index + 1,
                    'origine' => $arme['origine'],
                    'starter' => $arme['is_starter'] ? 1 : 0,
                    'commentaire' => $arme['commentaire'] ?? null,
                ]);
            }
        });

        $newSnapshotState = ['armes_recommandees' => $this->snapshotService->captureRecommendedWeaponsState($personnage->fresh('armesRecommandees'))];
        $this->snapshotService->createManualUpdate(
            $personnage,
            $oldSnapshotState,
            $newSnapshotState,
            $this->resolveAdminId(),
        );

        return response()->json([
            'success' => true,
            'armes' => $armes,
        ]);
    }


    public function deleteArmeRecommandee(Request $request, Personnage $personnage, int $id_arme): JsonResponse
    {
        $nomBuild = trim((string) $request->query('nom_build', ''));

        PersonnageArmeRecommandee::query()
            ->where('fid_perso', $personnage->id_perso)
            ->where('nom_build', $nomBuild)
            ->where('fid_arme', $id_arme)
            ->delete();

        return response()->json([
            'success' => true,
            'id_arme' => $id_arme,
        ]);
    }

    public function updateStatsRecommandees(Request $request, Personnage $personnage): JsonResponse
    {
        $data = $request->validate([
            'builds' => ['sometimes', 'array', 'max:4'],
            'builds.*.nom_build' => ['nullable', 'string', 'max:100'],
            'builds.*.pv' => ['nullable', 'string', 'max:20'],
            'builds.*.atq' => ['nullable', 'string', 'max:20'],
            'builds.*.def' => ['nullable', 'string', 'max:20'],
            'builds.*.taux_crit' => ['nullable', 'string', 'max:20'],
            'builds.*.degats_crit' => ['nullable', 'string', 'max:20'],
            'builds.*.maitrise_elementaire' => ['nullable', 'string', 'max:20'],
            'builds.*.recharge_energetique' => ['nullable', 'string', 'max:20'],
            'builds.*.commentaire' => ['nullable', 'string', 'max:500'],
        ]);

        $builds = collect($data['builds'] ?? [])->values();

        DB::transaction(function () use ($personnage, $builds): void {
            PersonnageStatsRecommandee::query()
                ->where('fid_perso', $personnage->id_perso)
                ->delete();

            foreach ($builds as $index => $build) {
                PersonnageStatsRecommandee::query()->create([
                    'fid_perso' => $personnage->id_perso,
                    'nom_build' => trim((string) ($build['nom_build'] ?? '')) ?: null,
                    'pv' => $build['pv'] ?? null,
                    'atq' => $build['atq'] ?? null,
                    'def' => $build['def'] ?? null,
                    'taux_crit' => $build['taux_crit'] ?? null,
                    'degats_crit' => $build['degats_crit'] ?? null,
                    'maitrise_elementaire' => $build['maitrise_elementaire'] ?? null,
                    'recharge_energetique' => $build['recharge_energetique'] ?? null,
                    'commentaire' => isset($build['commentaire']) ? trim((string) $build['commentaire']) ?: null : null,
                    'position' => $index + 1,
                ]);
            }
        });

        return response()->json([
            'success' => true,
            'stats' => $personnage->statsRecommandees()->get(),
        ]);
    }

    public function updateConstellations(Request $request, Personnage $personnage): JsonResponse
    {
        $oldSnapshotState = ['constellations_data' => $this->snapshotService->captureConstellationsState($personnage->fresh())];

        $data = $request->validate([
            'constellations' => ['sometimes', 'array'],
            'constellations.*.id_const' => ['nullable', 'integer', 'exists:constellation,id_const'],
            'constellations.*.index' => ['nullable', 'integer', 'min:1', 'max:6'],
            'constellations.*.titre_const' => ['nullable', 'string', 'max:200'],
            'constellations.*.descri_const' => ['nullable', 'string'],
            'constellations.*.recommandee' => ['nullable', 'boolean'],
        ]);

        // Si aucune constellation n'est envoyée, retourner succès directement
        if (!array_key_exists('constellations', $data) || empty($data['constellations'])) {
            return response()->json(['success' => true, 'constellations' => []]);
        }

        SnapshotService::withoutRecording(function () use ($data, $personnage): void {
            foreach ($data['constellations'] as $rowIndex => $payload) {
                $title = trim((string) ($payload['titre_const'] ?? ''));
                $desc = trim((string) ($payload['descri_const'] ?? ''));
                $idConst = isset($payload['id_const']) ? (int) $payload['id_const'] : null;
                $index = isset($payload['index']) ? (int) $payload['index'] : ((int) $rowIndex + 1);
                $recommandee = array_key_exists('recommandee', $payload) ? (bool) $payload['recommandee'] : false;

                if ($idConst) {
                    Constellation::query()
                        ->where('id_const', $idConst)
                        ->where('fid_perso', $personnage->id_perso)
                        ->update([
                            'titre_const' => $title !== '' ? $title : ('Constellation C' . $index),
                            'descri_const' => $desc !== '' ? $desc : null,
                            'recommandee' => $recommandee,
                        ]);
                    continue;
                }

                if ($title === '' && $desc === '') {
                    continue;
                }

                Constellation::create([
                    'fid_perso' => $personnage->id_perso,
                    'titre_const' => $title !== '' ? $title : ('Constellation C' . $index),
                    'descri_const' => $desc !== '' ? $desc : null,
                    'recommandee' => $recommandee,
                ]);
            }
        });

        $newSnapshotState = ['constellations_data' => $this->snapshotService->captureConstellationsState($personnage->fresh())];
        $this->snapshotService->createManualUpdate(
            $personnage,
            $oldSnapshotState,
            $newSnapshotState,
            $this->resolveAdminId(),
        );

        $constellationImageFor = function (string $slug, int $index): string {
            $base = 'photos/personnages/constellations/' . $slug . '-c' . $index;
            foreach (['webp', 'png', 'jpg', 'jpeg'] as $ext) {
                $path = $base . '.' . $ext;
                if (Storage::disk('public')->exists($path)) {
                    return asset('storage/' . $path);
                }
            }

            return asset('images/placeholder.svg');
        };

        $constellations = $personnage->fresh()->constellations
            ->sortBy('id_const')
            ->values()
            ->map(function ($constellation, $idx) use ($personnage, $constellationImageFor) {
                $index = $idx + 1;
                return [
                    'id_const' => (int) $constellation->id_const,
                    'index' => $index,
                    'label' => 'C' . $index,
                    'titre_const' => $constellation->titre_const,
                    'descri_const' => $constellation->descri_const,
                    'image_url' => $constellationImageFor($personnage->slug, $index),
                    'recommandee' => (bool) $constellation->recommandee,
                ];
            })
            ->values();

        return response()->json(['success' => true, 'constellations' => $constellations]);
    }

    public function uploadConstellationImage(Request $request, Personnage $personnage): JsonResponse
    {
        $oldSnapshotState = ['constellations_data' => $this->snapshotService->captureConstellationsState($personnage->fresh())];

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

        SnapshotService::withoutRecording(function () use ($personnage, $index, $path): void {
            $constellations = $personnage->constellations()
                ->orderBy('id_const')
                ->get()
                ->values();

            while ($constellations->count() < $index) {
                $nextIndex = $constellations->count() + 1;
                $constellations->push(Constellation::create([
                    'fid_perso' => $personnage->id_perso,
                    'titre_const' => 'Constellation C' . $nextIndex,
                    'descri_const' => null,
                ]));
            }

            $constellation = $constellations->get($index - 1);

            $oldPhoto = $constellation->photo;
            if ($oldPhoto && $oldPhoto->chemin_photo !== $path && !filter_var((string) $oldPhoto->chemin_photo, FILTER_VALIDATE_URL)) {
                Storage::disk('public')->delete($oldPhoto->chemin_photo);
            }

            $constellation->photo()->updateOrCreate([], [
                'chemin_photo' => $path,
                'source_url' => null,
                'type' => 'icon',
            ]);
        });

        $newSnapshotState = ['constellations_data' => $this->snapshotService->captureConstellationsState($personnage->fresh())];
        $this->snapshotService->createManualUpdate(
            $personnage,
            $oldSnapshotState,
            $newSnapshotState,
            $this->resolveAdminId(),
        );

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
        $oldSnapshotState = ['competences_data' => $this->snapshotService->captureCompetencesState($personnage->fresh())];

        $data = $request->validate([
            'competences' => ['sometimes', 'array'],
            'competences.*.id_aptitude' => ['nullable', 'integer', 'exists:aptitude,id_aptitude'],
            'competences.*.titre_apti' => ['sometimes', 'string', 'max:200'],
            'competences.*.descri_apti' => ['nullable', 'string'],
            'competences.*.lvl_apt' => ['nullable', 'integer', 'min:1', 'max:15'],
            'competences.*.sub_Apt' => ['nullable', 'string'],
            'competences.*.fid_TypeApti' => ['sometimes', 'integer', 'exists:type_apti,id_TypeApti'],
        ]);

        // Si aucune compétence n'est envoyée, supprimer les existantes et enregistrer un snapshot.
        if (!array_key_exists('competences', $data) || empty($data['competences'])) {
            SnapshotService::withoutRecording(function () use ($personnage): void {
                Aptitude::query()
                    ->where('fid_perso', $personnage->id_perso)
                    ->delete();
            });

            $this->snapshotService->createManualUpdate(
                $personnage,
                $oldSnapshotState,
                ['competences_data' => []],
                $this->resolveAdminId(),
            );

            return response()->json([
                'success'          => true,
                'competences_ids'  => [],
                'competences_count'=> 0,
            ]);
        }

        $keptIds = [];

        SnapshotService::withoutRecording(function () use ($data, $personnage, &$keptIds): void {
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
        });

        $newSnapshotState = ['competences_data' => $this->snapshotService->captureCompetencesState($personnage->fresh())];
        $this->snapshotService->createManualUpdate(
            $personnage,
            $oldSnapshotState,
            $newSnapshotState,
            $this->resolveAdminId(),
        );

        return response()->json([
            'success'          => true,
            'competences_ids'  => $keptIds,
            'competences_count'=> count($keptIds),
        ]);
    }

    public function updateHistoires(Request $request, Personnage $personnage): JsonResponse
    {
        $oldSnapshotState = ['histoires_data' => $this->snapshotService->captureHistoiresState($personnage->fresh())];

        $data = $request->validate([
            'histoires' => ['sometimes', 'array'],
            'histoires.*.id_histoire' => ['nullable', 'integer', 'exists:histoire,id_histoire'],
            'histoires.*.titre_histoire' => ['sometimes', 'string', 'max:200'],
            'histoires.*.histoire' => ['sometimes', 'string'],
        ]);

        // Si aucune histoire n'est envoyée, supprimer les existantes et enregistrer un snapshot.
        if (!array_key_exists('histoires', $data) || empty($data['histoires'])) {
            SnapshotService::withoutRecording(function () use ($personnage): void {
                PersonnageHistoire::query()
                    ->where('fid_perso', $personnage->id_perso)
                    ->delete();
            });

            $this->snapshotService->createManualUpdate(
                $personnage,
                $oldSnapshotState,
                ['histoires_data' => []],
                $this->resolveAdminId(),
            );

            return response()->json([
                'success' => true,
                'histoires_ids' => [],
                'histoires_count' => 0,
            ]);
        }

        $keptIds = [];

        SnapshotService::withoutRecording(function () use ($data, $personnage, &$keptIds): void {
            foreach ($data['histoires'] as $index => $payload) {
                $attributes = [
                    'fid_perso' => $personnage->id_perso,
                    'titre_histoire' => trim((string) $payload['titre_histoire']),
                    'histoire' => trim((string) $payload['histoire']),
                    'ordre' => $index + 1,
                ];

                if (!empty($payload['id_histoire'])) {
                    $histoire = PersonnageHistoire::query()
                        ->where('id_histoire', (int) $payload['id_histoire'])
                        ->where('fid_perso', $personnage->id_perso)
                        ->first();

                    if ($histoire) {
                        $histoire->update($attributes);
                        $keptIds[] = (int) $histoire->id_histoire;
                        continue;
                    }
                }

                $created = PersonnageHistoire::query()->create($attributes);
                $keptIds[] = (int) $created->id_histoire;
            }

            PersonnageHistoire::query()
                ->where('fid_perso', $personnage->id_perso)
                ->whereNotIn('id_histoire', $keptIds)
                ->delete();
        });

        $newSnapshotState = ['histoires_data' => $this->snapshotService->captureHistoiresState($personnage->fresh())];
        $this->snapshotService->createManualUpdate(
            $personnage,
            $oldSnapshotState,
            $newSnapshotState,
            $this->resolveAdminId(),
        );

        return response()->json([
            'success' => true,
            'histoires_ids' => $keptIds,
            'histoires_count' => count($keptIds),
        ]);
    }

    public function uploadAptitudeImage(Request $request, Personnage $personnage): JsonResponse
    {
        $oldSnapshotState = ['competences_data' => $this->snapshotService->captureCompetencesState($personnage->fresh())];

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

        $newSnapshotState = ['competences_data' => $this->snapshotService->captureCompetencesState($personnage->fresh())];
        $this->snapshotService->createManualUpdate(
            $personnage,
            $oldSnapshotState,
            $newSnapshotState,
            $this->resolveAdminId(),
        );

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
            'block_order' => ['sometimes', 'array'],
            'block_order.*' => ['sometimes', 'string', 'in:main_zone,armes,artefacts,constellations,competences,histoires'],
        ]);

        // Si block_order est envoyé, le mettre à jour
        if (array_key_exists('block_order', $data) && !empty($data['block_order'])) {
            $personnage->update([
                'block_order' => implode(',', $data['block_order']),
            ]);
        }

        return response()->json([
            'success' => true,
            'block_order' => $data['block_order'] ?? [],
        ]);
    }

    public function storeTeam(Request $request, Personnage $personnage): JsonResponse
    {
        $data = $request->validate([
            'type_reaction' => ['required', 'string', 'max:80'],
            'tag' => ['required', Rule::in(['recommended', 'f2p'])],
            'rotation' => ['nullable', 'string', 'max:5000'],
            'membres' => ['required', 'array', 'size:4'],
            'membres.*.id_perso' => ['required', 'integer', 'exists:personnage,id_perso'],
            'membres.*.slot' => ['required', 'integer', 'min:1', 'max:4'],
            'membres.*.role_override' => ['nullable', 'string', 'max:100'],
            'remplacants' => ['nullable', 'array'],
            'remplacants.*.slot' => ['required', 'integer', 'min:1', 'max:4'],
            'remplacants.*.id_perso' => ['required', 'integer', 'exists:personnage,id_perso'],
            'remplacants.*.role_override' => ['nullable', 'string', 'max:100'],
        ]);

        $this->validateTeamPayload($personnage, $data, null);

        $team = DB::transaction(function () use ($personnage, $data) {
            $team = TeamComposition::create([
                'fid_perso' => $personnage->id_perso,
                'type_reaction' => trim((string) $data['type_reaction']),
                'tag' => $data['tag'],
                'rotation' => trim((string) $data['rotation']),
            ]);

            foreach ($data['membres'] as $membre) {
                TeamCompositionMembre::create([
                    'fid_team' => $team->id_team,
                    'fid_perso' => (int) $membre['id_perso'],
                    'slot' => (int) $membre['slot'],
                    'role_override' => $membre['role_override'] ?? null,
                ]);
            }

            foreach ($data['remplacants'] ?? [] as $remplacant) {
                TeamSlotRemplacant::create([
                    'fid_team' => $team->id_team,
                    'slot' => (int) $remplacant['slot'],
                    'fid_perso_remplacant' => (int) $remplacant['id_perso'],
                    'role_override' => $remplacant['role_override'] ?? null,
                ]);
            }

            return $team;
        });

        $team->load([
            'membres.personnage.element',
            'membres.personnage.photos',
            'membres.personnage.roles',
            'alternatives.personnage.element',
            'alternatives.personnage.photos',
            'alternatives.personnage.roles',
        ]);

        return response()->json([
            'success' => true,
            'team' => $this->formatTeam($team),
        ]);
    }

    public function updateTeam(Request $request, Personnage $personnage, int $id_team): JsonResponse
    {
        $team = TeamComposition::where('id_team', $id_team)
            ->where('fid_perso', $personnage->id_perso)
            ->firstOrFail();

        $data = $request->validate([
            'type_reaction' => ['required', 'string', 'max:80'],
            'tag' => ['required', Rule::in(['recommended', 'f2p'])],
            'rotation' => ['nullable', 'string', 'max:5000'],
            'membres' => ['required', 'array', 'size:4'],
            'membres.*.id_perso' => ['required', 'integer', 'exists:personnage,id_perso'],
            'membres.*.slot' => ['required', 'integer', 'min:1', 'max:4'],
            'membres.*.role_override' => ['nullable', 'string', 'max:100'],
            'remplacants' => ['nullable', 'array'],
            'remplacants.*.slot' => ['required', 'integer', 'min:1', 'max:4'],
            'remplacants.*.id_perso' => ['required', 'integer', 'exists:personnage,id_perso'],
            'remplacants.*.role_override' => ['nullable', 'string', 'max:100'],
        ]);

        $this->validateTeamPayload($personnage, $data, $team->id_team);

        DB::transaction(function () use ($team, $data) {
            $team->update([
                'type_reaction' => trim((string) $data['type_reaction']),
                'tag' => $data['tag'],
                'rotation' => trim((string) $data['rotation']),
            ]);

            $team->membres()->delete();
            $team->alternatives()->delete();

            foreach ($data['membres'] as $membre) {
                TeamCompositionMembre::create([
                    'fid_team' => $team->id_team,
                    'fid_perso' => (int) $membre['id_perso'],
                    'slot' => (int) $membre['slot'],
                    'role_override' => $membre['role_override'] ?? null,
                ]);
            }

            foreach ($data['remplacants'] ?? [] as $remplacant) {
                TeamSlotRemplacant::create([
                    'fid_team' => $team->id_team,
                    'slot' => (int) $remplacant['slot'],
                    'fid_perso_remplacant' => (int) $remplacant['id_perso'],
                    'role_override' => $remplacant['role_override'] ?? null,
                ]);
            }
        });

        $team->load([
            'membres.personnage.element',
            'membres.personnage.photos',
            'membres.personnage.roles',
            'alternatives.personnage.element',
            'alternatives.personnage.photos',
            'alternatives.personnage.roles',
        ]);

        return response()->json([
            'success' => true,
            'team' => $this->formatTeam($team),
        ]);
    }

    public function deleteTeam(Personnage $personnage, int $id_team): JsonResponse
    {
        TeamComposition::where('id_team', $id_team)
            ->where('fid_perso', $personnage->id_perso)
            ->firstOrFail()
            ->delete();

        return response()->json(['success' => true]);
    }

    public function getTeamAptitudes(Personnage $personnage, int $id_team): JsonResponse
    {
        $team = TeamComposition::where('id_team', $id_team)
            ->where('fid_perso', $personnage->id_perso)
            ->with('membres.personnage.aptitudes.typeApti', 'membres.personnage.aptitudes.photos')
            ->firstOrFail();

        $aptitudesByMember = [];

        foreach ($team->membres->where('slot', '<=', 4) as $membre) {
            $perso = $membre->personnage;
            if (!$perso) {
                continue;
            }

            $memberApts = $perso->aptitudes
                ->filter(fn($a) => in_array($a->fid_TypeApti, [6, 7, 8]))
                ->sortBy('fid_TypeApti')
                ->values()
                ->map(fn($a) => [
                    'id_aptitude' => (int) $a->id_aptitude,
                    'fid_perso' => (int) $perso->id_perso,
                    'nom_perso' => (string) $perso->nom_perso,
                    'titre' => (string) $a->titre_apti,
                    'type' => (string) ($a->typeApti?->libelle_Apti ?? ''),
                    'icon' => $this->getPhotoUrl($a->photos->first()),
                ])
                ->all();

            if (!empty($memberApts)) {
                $aptitudesByMember[] = [
                    'slot' => (int) $membre->slot,
                    'nom_perso' => (string) $perso->nom_perso,
                    'icon_perso' => $this->getPhotoUrl($perso->photos->first()),
                    'aptitudes' => $memberApts,
                ];
            }
        }

        return response()->json([
            'success' => true,
            'members' => $aptitudesByMember,
        ]);
    }

    public function updateTeamRotation(Request $request, Personnage $personnage, int $id_team): JsonResponse
    {
        $team = TeamComposition::where('id_team', $id_team)
            ->where('fid_perso', $personnage->id_perso)
            ->firstOrFail();

        $data = $request->validate([
            'rotation' => ['nullable', 'json'],
        ]);

        $rotation = $data['rotation'] ? json_decode($data['rotation'], true) : [];

        if (!is_array($rotation)) {
            return response()->json([
                'success' => false,
                'message' => 'La rotation doit être un JSON valide.',
            ], 422);
        }

        $team->update([
            'rotation' => $rotation ? json_encode($rotation) : null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Rotation sauvegardée avec succès.',
        ]);
    }

    private function getPhotoUrl($photo): string
    {
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
    }

    private function validateTeamPayload(Personnage $personnage, array $data, ?int $ignoreTeamId): void
    {
        $slots = array_map(static fn(array $m) => (int) $m['slot'], $data['membres']);
        sort($slots);
        if ($slots !== [1, 2, 3, 4]) {
            throw ValidationException::withMessages([
                'membres' => 'Les membres doivent couvrir exactement les slots 1 à 4.',
            ]);
        }

        $memberIds = array_map(static fn(array $m) => (int) $m['id_perso'], $data['membres']);
        if (count(array_unique($memberIds)) !== 4) {
            throw ValidationException::withMessages([
                'membres' => 'Un personnage ne peut apparaître qu\'une fois parmi les 4 membres.',
            ]);
        }

        if (!in_array((int) $personnage->id_perso, $memberIds, true)) {
            throw ValidationException::withMessages([
                'membres' => 'Le personnage principal doit être présent dans la team.',
            ]);
        }

        $replacementKeys = [];
        foreach ($data['remplacants'] ?? [] as $idx => $remplacant) {
            $slot = (int) $remplacant['slot'];
            $idPerso = (int) $remplacant['id_perso'];

            $memberForSlot = collect($data['membres'])->firstWhere('slot', $slot);
            if ($memberForSlot && (int) $memberForSlot['id_perso'] === $idPerso) {
                throw ValidationException::withMessages([
                    "remplacants.$idx.id_perso" => 'Un remplaçant ne peut pas être identique au titulaire du même slot.',
                ]);
            }

            if (in_array($idPerso, $memberIds, true)) {
                throw ValidationException::withMessages([
                    "remplacants.$idx.id_perso" => 'Un remplaçant ne peut pas déjà être titulaire de la team.',
                ]);
            }

            $key = $slot . ':' . $idPerso;
            if (isset($replacementKeys[$key])) {
                throw ValidationException::withMessages([
                    "remplacants.$idx.id_perso" => 'Remplaçant dupliqué pour ce slot.',
                ]);
            }
            $replacementKeys[$key] = true;
        }

        $typeReaction = trim((string) $data['type_reaction']);

        $existingTeamsQuery = TeamComposition::query()
            ->where('fid_perso', $personnage->id_perso)
            ->where('type_reaction', $typeReaction);
        if ($ignoreTeamId !== null) {
            $existingTeamsQuery->where('id_team', '!=', $ignoreTeamId);
        }

        if ($existingTeamsQuery->count() >= 2) {
            throw ValidationException::withMessages([
                'tag' => 'Maximum 2 équipes par réaction: Recommended et F2P.',
            ]);
        }

        $conflictTagQuery = TeamComposition::query()
            ->where('fid_perso', $personnage->id_perso)
            ->where('type_reaction', $typeReaction)
            ->where('tag', (string) $data['tag']);

        if ($ignoreTeamId !== null) {
            $conflictTagQuery->where('id_team', '!=', $ignoreTeamId);
        }

        if ($conflictTagQuery->exists()) {
            throw ValidationException::withMessages([
                'tag' => 'Cette étiquette existe déjà pour cette réaction sur ce personnage.',
            ]);
        }
    }

    private function formatTeam(TeamComposition $team): array
    {
        return [
            'id_team' => (int) $team->id_team,
            'type_reaction' => $team->type_reaction,
            'tag' => $team->tag,
            'rotation' => $team->rotation,
            'membres' => $team->membres
                ->sortBy('slot')
                ->values()
                ->map(function (TeamCompositionMembre $membre) {
                    $perso = $membre->personnage;
                    $photo = $perso?->photos?->first();
                    $defaultRole = $perso?->roles?->first()?->libelle_role;

                    return [
                        'slot' => (int) $membre->slot,
                        'id_perso' => (int) $membre->fid_perso,
                        'nom' => $perso?->nom_perso ?? '',
                        'element' => $perso?->element?->libelle_element ?? '',
                        'icon' => $photo?->source_url
                            ?? ($photo?->chemin_photo
                                ? (filter_var($photo->chemin_photo, FILTER_VALIDATE_URL)
                                    ? $photo->chemin_photo
                                    : asset('storage/' . ltrim((string) $photo->chemin_photo, '/')))
                                : null),
                        'default_role' => $defaultRole,
                        'role_override' => $membre->role_override,
                        'role' => $membre->role_override ?: $defaultRole,
                    ];
                })
                ->all(),
            'remplacants' => $team->alternatives
                ->sortBy('id_rpl')
                ->values()
                ->map(function (TeamSlotRemplacant $remplacant) {
                    $perso = $remplacant->personnage;
                    $photo = $perso?->photos?->first();
                    $defaultRole = $perso?->roles?->first()?->libelle_role;

                    return [
                        'id' => (int) ($remplacant->id_rpl ?? 0),
                        'slot' => (int) $remplacant->slot,
                        'id_perso' => (int) $remplacant->fid_perso_remplacant,
                        'nom' => $perso?->nom_perso ?? '',
                        'element' => $perso?->element?->libelle_element ?? '',
                        'icon' => $photo?->source_url
                            ?? ($photo?->chemin_photo
                                ? (filter_var($photo->chemin_photo, FILTER_VALIDATE_URL)
                                    ? $photo->chemin_photo
                                    : asset('storage/' . ltrim((string) $photo->chemin_photo, '/')))
                                : null),
                        'default_role' => $defaultRole,
                        'role_override' => $remplacant->role_override,
                        'role' => $remplacant->role_override ?: $defaultRole,
                    ];
                })
                ->all(),
        ];
    }

}