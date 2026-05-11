<?php

namespace Tests\Feature;

use App\Models\Personnage;
use App\Models\Snapshot;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PurgeSnapshotsCommandTest extends TestCase
{
    use RefreshDatabase;

    private function createSnapshots(Personnage $personnage, int $count): void
    {
        for ($i = 1; $i <= $count; $i++) {
            $snapshot = Snapshot::create([
                'fid_perso' => $personnage->id_perso,
                'fid_admin' => null,
                'action_type' => $i % 2 === 0 ? 'update' : 'delete',
                'action_at' => now()->subMinutes($count - $i),
            ]);

            $snapshot->modifications()->create([
                'sub_sequence' => 1,
                'old_values' => ['nom_perso' => 'old-' . $i],
                'new_values' => ['nom_perso' => 'new-' . $i],
            ]);
        }
    }

    public function test_commande_purge_conserve_n_snapshots_par_personnage(): void
    {
        $p1 = Personnage::factory()->create(['nom_perso' => 'Purge One']);
        $p2 = Personnage::factory()->create(['nom_perso' => 'Purge Two']);

        $this->createSnapshots($p1, 5);
        $this->createSnapshots($p2, 3);

        $this->artisan('snapshots:purge --keep=2')
            ->expectsOutput('Snapshots purgés: 4')
            ->assertExitCode(0);

        $this->assertSame(2, Snapshot::where('fid_perso', $p1->id_perso)->count());
        $this->assertSame(2, Snapshot::where('fid_perso', $p2->id_perso)->count());
    }

    public function test_commande_purge_utilise_config_quand_option_absente(): void
    {
        config(['traceability.keep_snapshots_per_entity' => 3]);

        $p1 = Personnage::factory()->create(['nom_perso' => 'Purge Config']);
        $this->createSnapshots($p1, 5);

        $this->artisan('snapshots:purge')
            ->expectsOutput('Snapshots purgés: 2')
            ->assertExitCode(0);

        $this->assertSame(3, Snapshot::where('fid_perso', $p1->id_perso)->count());
    }

    public function test_commande_purge_force_keep_minimum_a_un(): void
    {
        $p1 = Personnage::factory()->create(['nom_perso' => 'Purge Min']);
        $this->createSnapshots($p1, 4);

        $this->artisan('snapshots:purge --keep=0')
            ->expectsOutput('Snapshots purgés: 3')
            ->assertExitCode(0);

        $this->assertSame(1, Snapshot::where('fid_perso', $p1->id_perso)->count());
    }
}
