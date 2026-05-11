<?php

namespace App\Services;

use App\Models\Personnage;
use App\Models\Snapshot;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

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

        if ($oldValues === [] || $newValues === []) {
            return null;
        }

        return DB::transaction(function () use ($personnage, $oldValues, $newValues, $adminId): Snapshot {
            $snapshot = Snapshot::create([
                'fid_perso' => $personnage->getKey(),
                'fid_admin' => $adminId,
                'action_type' => 'update',
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

    public function createForDelete(Personnage $personnage, array $oldValues, array $newValues, ?int $adminId = null): ?Snapshot
    {
        if (!self::$recordingEnabled) {
            return null;
        }

        return DB::transaction(function () use ($personnage, $oldValues, $newValues, $adminId): Snapshot {
            $snapshot = Snapshot::create([
                'fid_perso' => $personnage->getKey(),
                'fid_admin' => $adminId,
                'action_type' => 'delete',
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
                foreach ($snapshot->modifications as $modification) {
                    $oldValues = Arr::wrap($modification->old_values);
                    foreach ($oldValues as $field => $value) {
                        if (in_array($field, ['deleted_at', 'deleted_by'], true)) {
                            continue;
                        }
                        $payload[$field] = $value;
                    }
                }

                if ($payload !== []) {
                    $personnage->updateQuietly($payload);
                }
            });

            return $personnage;
        });
    }
}
