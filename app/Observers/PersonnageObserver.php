<?php

namespace App\Observers;

use App\Models\Personnage;
use App\Services\SnapshotService;

class PersonnageObserver
{
    /** @var array<int, array{old: array<string, mixed>, new: array<string, mixed>, admin_id: int|null}> */
    private static array $pendingUpdates = [];

    /** @var array<int, array{old: array<string, mixed>, new: array<string, mixed>, admin_id: int|null}> */
    private static array $pendingDeletes = [];

    public function __construct(private readonly SnapshotService $snapshotService)
    {
    }

    public function updating(Personnage $personnage): void
    {
        if (!SnapshotService::isRecordingEnabled()) {
            return;
        }

        $dirty = $personnage->getDirty();
        unset($dirty['deleted_at'], $dirty['deleted_by']);

        if ($dirty === []) {
            return;
        }

        $oldValues = [];
        $newValues = [];

        foreach ($dirty as $field => $newValue) {
            $oldValues[$field] = $personnage->getOriginal($field);
            $newValues[$field] = $newValue;
        }

        self::$pendingUpdates[spl_object_id($personnage)] = [
            'old' => $oldValues,
            'new' => $newValues,
            'admin_id' => $this->resolveAdminId(),
        ];
    }

    public function updated(Personnage $personnage): void
    {
        $key = spl_object_id($personnage);
        $pending = self::$pendingUpdates[$key] ?? null;

        if (!$pending) {
            return;
        }

        unset(self::$pendingUpdates[$key]);

        $this->snapshotService->createForUpdate(
            personnage: $personnage,
            oldValues: $pending['old'],
            newValues: $pending['new'],
            adminId: $pending['admin_id'],
        );
    }

    public function deleting(Personnage $personnage): void
    {
        if (!SnapshotService::isRecordingEnabled() || $personnage->isForceDeleting()) {
            return;
        }

        $adminId = $this->resolveAdminId();

        self::$pendingDeletes[spl_object_id($personnage)] = [
            'old' => [
                'deleted_at' => $personnage->deleted_at,
                'deleted_by' => $personnage->deleted_by,
            ],
            'new' => [
                'deleted_at' => now()->toDateTimeString(),
                'deleted_by' => $adminId,
            ],
            'admin_id' => $adminId,
        ];
    }

    public function deleted(Personnage $personnage): void
    {
        if ($personnage->isForceDeleting()) {
            return;
        }

        $key = spl_object_id($personnage);
        $pending = self::$pendingDeletes[$key] ?? null;

        if (!$pending) {
            return;
        }

        unset(self::$pendingDeletes[$key]);

        if ($pending['admin_id']) {
            Personnage::withoutEvents(function () use ($personnage, $pending): void {
                Personnage::withTrashed()
                    ->where('id_perso', $personnage->getKey())
                    ->update(['deleted_by' => $pending['admin_id']]);
            });
        }

        $this->snapshotService->createForDelete(
            personnage: $personnage,
            oldValues: $pending['old'],
            newValues: $pending['new'],
            adminId: $pending['admin_id'],
        );
    }

    private function resolveAdminId(): ?int
    {
        if (!app()->bound('session')) {
            return null;
        }

        $adminId = session('admin_id');
        return $adminId ? (int) $adminId : null;
    }
}
