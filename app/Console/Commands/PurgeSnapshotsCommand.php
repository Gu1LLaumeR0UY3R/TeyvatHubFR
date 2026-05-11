<?php

namespace App\Console\Commands;

use App\Models\Snapshot;
use Illuminate\Console\Command;

class PurgeSnapshotsCommand extends Command
{
    protected $signature = 'snapshots:purge {--keep=}';

    protected $description = 'Purge les snapshots les plus anciens en conservant N versions par personnage';

    public function handle(): int
    {
        $keepOption = $this->option('keep');
        $keep = $keepOption === null
            ? (int) config('traceability.keep_snapshots_per_entity', 30)
            : (int) $keepOption;
        $keep = max(1, $keep);

        $personnageIds = Snapshot::query()
            ->select('fid_perso')
            ->distinct()
            ->pluck('fid_perso');

        $deleted = 0;

        foreach ($personnageIds as $personnageId) {
            $snapshotIds = Snapshot::query()
                ->where('fid_perso', $personnageId)
                ->orderByDesc('id_snapshot')
                ->pluck('id_snapshot');

            $snapshotIdsToDelete = $snapshotIds->slice($keep)->values();

            if ($snapshotIdsToDelete->isEmpty()) {
                continue;
            }

            $deleted += Snapshot::query()
                ->whereIn('id_snapshot', $snapshotIdsToDelete->all())
                ->delete();
        }

        $this->info("Snapshots purgés: {$deleted}");

        return self::SUCCESS;
    }
}
