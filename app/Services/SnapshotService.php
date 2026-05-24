<?php

namespace App\Services;

use App\Models\Personnage;
use App\Models\Snapshot;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SnapshotService
{
    private static bool $recordingEnabled = true;

    public static function isRecordingEnabled(): bool
    {
        return self::$recordingEnabled;
    }

    public static function withoutRecording(callable $callback): mixed
    {
        $previous = self::$recordingEnabled;
        self::$recordingEnabled = false;

        try {
            return $callback();
        } finally {
            self::$recordingEnabled = $previous;
        }
    }

    public function createForUpdate(Personnage $personnage, array $oldValues, array $newValues, ?int $adminId = null): ?Snapshot
    {
        if (!self::$recordingEnabled) {
            return null;
        }

        if ($oldValues === [] || $newValues === [] || $oldValues === $newValues) {
            return null;
        }

        return $this->storeSnapshot($personnage, 'update', $oldValues, $newValues, $adminId);
    }

    public function createManualUpdate(Personnage $personnage, array $oldValues, array $newValues, ?int $adminId = null): ?Snapshot
    {
        if ($oldValues === [] || $newValues === [] || $oldValues === $newValues) {
            return null;
        }

        return $this->storeSnapshot($personnage, 'update', $oldValues, $newValues, $adminId);
    }

    public function captureMainZoneState(Personnage $personnage): array
    {
        $relations = ['videos', 'photos'];
        $canUseNations = Schema::hasTable('personnage_nation')
            && (Schema::hasTable('nation') || Schema::hasTable('région'));

        if ($canUseNations) {
            $relations[] = 'nations';
        }

        $personnage->loadMissing($relations);

        $state = [
            'nom_perso' => $personnage->nom_perso,
            'fid_element' => $personnage->fid_element,
            'fid_etoile' => $personnage->fid_etoile,
            'fid_TArmes' => $personnage->fid_TArmes,
            'fid_TP' => $personnage->fid_TP,
            'videos' => $personnage->videos->map(fn ($video) => [
                'url_video' => $video->url_video,
                'ordre' => (int) $video->ordre,
            ])->values()->all(),
            'photos' => $personnage->photos->map(fn ($photo) => [
                'type' => $photo->type,
                'chemin_photo' => $photo->chemin_photo,
                'source_url' => $photo->source_url,
            ])->sortBy(fn (array $photo) => ($photo['type'] ?? '') . '|' . ($photo['chemin_photo'] ?? ''))->values()->all(),
        ];

        if (Schema::hasColumn('personnage', 'arme_icon')) {
            $state['arme_icon'] = $personnage->arme_icon;
        }

        if (Schema::hasColumn('personnage', 'background_actif')) {
            $state['background_actif'] = $personnage->background_actif;
        }

        if ($canUseNations) {
            $state['fid_nations'] = $personnage->nations->pluck('id_region')->map(fn ($id) => (int) $id)->sort()->values()->all();
        }

        return $state;
    }

    public function captureRecommendedWeaponsState(Personnage $personnage): array
    {
        $personnage->loadMissing('armesRecommandees');

        return $personnage->armesRecommandees->map(fn ($arme) => [
            'fid_arme' => (int) $arme->fid_arme,
            'position' => (int) $arme->position,
            'origine' => $arme->origine,
            'starter' => (bool) $arme->starter,
        ])->values()->all();
    }

    public function captureRecommendedArtefactsState(Personnage $personnage): array
    {
        $personnage->loadMissing('artefactsRecommandees');

        return $personnage->artefactsRecommandees->map(fn ($build) => [
            'fid_artefact_1' => (int) $build->fid_artefact_1,
            'pieces_1' => $build->pieces_1,
            'fid_artefact_2' => $build->fid_artefact_2 ? (int) $build->fid_artefact_2 : null,
            'pieces_2' => $build->pieces_2,
            'main_stat_sablier' => $build->main_stat_sablier,
            'main_stat_gobelet' => $build->main_stat_gobelet,
            'main_stat_couronne' => $build->main_stat_couronne,
            'sub_stats' => $build->sub_stats,
            'position' => (int) $build->position,
        ])->values()->all();
    }

    public function captureConstellationsState(Personnage $personnage): array
    {
        $personnage->loadMissing('constellations.photo');

        return $personnage->constellations->map(function ($constellation): array {
            $photo = $constellation->photo;

            return [
                'titre_const' => $constellation->titre_const,
                'descri_const' => $constellation->descri_const,
                'positions_const' => $constellation->positions_const,
                'photo' => $photo ? [
                    'type' => $photo->type,
                    'chemin_photo' => $photo->chemin_photo,
                    'source_url' => $photo->source_url,
                ] : null,
            ];
        })->values()->all();
    }

    public function captureCompetencesState(Personnage $personnage): array
    {
        $personnage->loadMissing('aptitudes.photos');

        return $personnage->aptitudes->map(function ($aptitude): array {
            return [
                'titre_apti' => $aptitude->titre_apti,
                'descri_apti' => $aptitude->descri_apti,
                'lvl_apt' => (int) $aptitude->lvl_apt,
                'sub_Apt' => $aptitude->sub_Apt,
                'fid_TypeApti' => (int) $aptitude->fid_TypeApti,
                'photos' => $aptitude->photos->map(fn ($photo) => [
                    'type' => $photo->type,
                    'chemin_photo' => $photo->chemin_photo,
                    'source_url' => $photo->source_url,
                ])->values()->all(),
            ];
        })->values()->all();
    }

    public function captureHistoiresState(Personnage $personnage): array
    {
        $personnage->loadMissing('histoires');

        return $personnage->histoires->map(fn ($histoire) => [
            'titre_histoire' => $histoire->titre_histoire,
            'histoire' => $histoire->histoire,
            'ordre' => (int) $histoire->ordre,
        ])->values()->all();
    }

    public function createForDelete(Personnage $personnage, array $oldValues, array $newValues, ?int $adminId = null): ?Snapshot
    {
        if (!self::$recordingEnabled) {
            return null;
        }

        return $this->storeSnapshot($personnage, 'delete', $oldValues, $newValues, $adminId);
    }

    private function storeSnapshot(Personnage $personnage, string $actionType, array $oldValues, array $newValues, ?int $adminId = null): Snapshot
    {
        return DB::transaction(function () use ($personnage, $actionType, $oldValues, $newValues, $adminId): Snapshot {
            $snapshot = Snapshot::create([
                'fid_perso' => $personnage->getKey(),
                'fid_admin' => $adminId,
                'action_type' => $actionType,
                'action_at' => now(),
            ]);

            $snapshot->modifications()->create([
                'sub_sequence' => 1,
                'old_values' => $oldValues,
                'new_values' => $newValues,
            ]);

            return $snapshot;
        });
    }

    public function restore(Snapshot $snapshot): Personnage
    {
        return DB::transaction(function () use ($snapshot): Personnage {
            $personnage = Personnage::withTrashed()->findOrFail($snapshot->fid_perso);

            self::withoutRecording(function () use ($personnage, $snapshot): void {
                if ($snapshot->action_type === 'delete') {
                    $personnage->deleted_at = null;
                    $personnage->deleted_by = null;
                    $personnage->saveQuietly();
                    return;
                }

                $payload = [];
                $photos = null;
                $videos = null;
                $nations = null;
                $recommendedWeapons = null;
                $recommendedArtefacts = null;
                $constellations = null;
                $competences = null;
                $histoires = null;

                foreach ($snapshot->modifications as $modification) {
                    $oldValues = Arr::wrap($modification->old_values);
                    foreach ($oldValues as $field => $value) {
                        if (in_array($field, ['deleted_at', 'deleted_by'], true)) {
                            continue;
                        }

                        if ($field === 'photos') {
                            $photos = is_array($value) ? $value : [];
                            continue;
                        }

                        if ($field === 'videos') {
                            $videos = is_array($value) ? $value : [];
                            continue;
                        }

                        if ($field === 'fid_nations') {
                            $nations = is_array($value) ? $value : [];
                            continue;
                        }

                        if ($field === 'armes_recommandees') {
                            $recommendedWeapons = is_array($value) ? $value : [];
                            continue;
                        }

                        if ($field === 'artefacts_recommandes') {
                            $recommendedArtefacts = is_array($value) ? $value : [];
                            continue;
                        }

                        if ($field === 'constellations_data') {
                            $constellations = is_array($value) ? $value : [];
                            continue;
                        }

                        if ($field === 'competences_data') {
                            $competences = is_array($value) ? $value : [];
                            continue;
                        }

                        if ($field === 'histoires_data') {
                            $histoires = is_array($value) ? $value : [];
                            continue;
                        }

                        if (Schema::hasColumn('personnage', $field)) {
                            $payload[$field] = $value;
                        }
                    }
                }

                if ($payload !== []) {
                    $personnage->updateQuietly($payload);
                }

                if ($nations !== null && Schema::hasTable('personnage_nation')) {
                    $personnage->nations()->sync(array_map('intval', $nations));
                }

                if ($videos !== null) {
                    $personnage->videos()->delete();
                    foreach ($videos as $index => $video) {
                        $personnage->videos()->create([
                            'url_video' => $video['url_video'] ?? null,
                            'ordre' => (int) ($video['ordre'] ?? ($index + 1)),
                        ]);
                    }
                }

                if ($photos !== null) {
                    $personnage->photos()->delete();
                    foreach ($photos as $photo) {
                        $personnage->photos()->create([
                            'type' => $photo['type'] ?? null,
                            'chemin_photo' => $photo['chemin_photo'] ?? null,
                            'source_url' => $photo['source_url'] ?? null,
                        ]);
                    }
                }

                if ($recommendedWeapons !== null) {
                    $personnage->armesRecommandees()->delete();
                    foreach ($recommendedWeapons as $weapon) {
                        $personnage->armesRecommandees()->create([
                            'fid_arme' => (int) ($weapon['fid_arme'] ?? 0),
                            'position' => (int) ($weapon['position'] ?? 1),
                            'origine' => $weapon['origine'] ?? null,
                            'starter' => !empty($weapon['starter']),
                        ]);
                    }
                }

                if ($recommendedArtefacts !== null) {
                    $personnage->artefactsRecommandees()->delete();
                    foreach ($recommendedArtefacts as $artefact) {
                        $personnage->artefactsRecommandees()->create([
                            'fid_artefact_1' => (int) ($artefact['fid_artefact_1'] ?? 0),
                            'pieces_1' => $artefact['pieces_1'] ?? null,
                            'fid_artefact_2' => isset($artefact['fid_artefact_2']) ? (int) $artefact['fid_artefact_2'] : null,
                            'pieces_2' => $artefact['pieces_2'] ?? null,
                            'main_stat_sablier' => $artefact['main_stat_sablier'] ?? null,
                            'main_stat_gobelet' => $artefact['main_stat_gobelet'] ?? null,
                            'main_stat_couronne' => $artefact['main_stat_couronne'] ?? null,
                            'sub_stats' => $artefact['sub_stats'] ?? null,
                            'position' => (int) ($artefact['position'] ?? 1),
                        ]);
                    }
                }

                if ($constellations !== null) {
                    $personnage->constellations()->get()->each(function (\App\Models\Constellation $constellation): void {
                        if ($constellation->photo) {
                            $constellation->photo->delete();
                        }
                    });
                    $personnage->constellations()->delete();

                    foreach ($constellations as $row) {
                        $constellation = $personnage->constellations()->create([
                            'titre_const' => $row['titre_const'] ?? null,
                            'descri_const' => $row['descri_const'] ?? null,
                            'positions_const' => $row['positions_const'] ?? null,
                        ]);

                        if (isset($row['photo']) && is_array($row['photo'])) {
                            $constellation->photo()->create([
                                'type' => $row['photo']['type'] ?? null,
                                'chemin_photo' => $row['photo']['chemin_photo'] ?? null,
                                'source_url' => $row['photo']['source_url'] ?? null,
                            ]);
                        }
                    }
                }

                if ($competences !== null) {
                    $personnage->aptitudes()->get()->each(function (\App\Models\Aptitude $aptitude): void {
                        $aptitude->photos()->delete();
                    });
                    $personnage->aptitudes()->delete();

                    foreach ($competences as $competence) {
                        $aptitude = $personnage->aptitudes()->create([
                            'titre_apti' => $competence['titre_apti'] ?? null,
                            'descri_apti' => $competence['descri_apti'] ?? null,
                            'lvl_apt' => (int) ($competence['lvl_apt'] ?? 1),
                            'sub_Apt' => $competence['sub_Apt'] ?? null,
                            'fid_TypeApti' => (int) ($competence['fid_TypeApti'] ?? 0),
                        ]);

                        foreach (($competence['photos'] ?? []) as $photo) {
                            if (!is_array($photo)) {
                                continue;
                            }

                            $aptitude->photos()->create([
                                'type' => $photo['type'] ?? null,
                                'chemin_photo' => $photo['chemin_photo'] ?? null,
                                'source_url' => $photo['source_url'] ?? null,
                            ]);
                        }
                    }
                }

                if ($histoires !== null) {
                    $personnage->histoires()->delete();

                    foreach ($histoires as $histoire) {
                        $personnage->histoires()->create([
                            'titre_histoire' => $histoire['titre_histoire'] ?? null,
                            'histoire' => $histoire['histoire'] ?? null,
                            'ordre' => (int) ($histoire['ordre'] ?? 1),
                        ]);
                    }
                }
            });

            // After restoring a point-in-time state, discard newer snapshots
            // for this personnage to keep history coherent from that restored point.
            Snapshot::query()
                ->where('fid_perso', $snapshot->fid_perso)
                ->where(function ($query) use ($snapshot): void {
                    $query
                        ->where('action_at', '>', $snapshot->action_at)
                        ->orWhere(function ($subQuery) use ($snapshot): void {
                            $subQuery
                                ->where('action_at', '=', $snapshot->action_at)
                                ->where('id_snapshot', '>', $snapshot->id_snapshot);
                        });
                })
                ->delete();

            return $personnage;
        });
    }
}
