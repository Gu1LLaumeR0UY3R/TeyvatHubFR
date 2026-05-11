<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Personnage;
use App\Models\Snapshot;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SnapshotTraceabilityTest extends TestCase
{
    use RefreshDatabase;

    private function makeAdmin(string $role = 'super_admin'): Admin
    {
        return Admin::create([
            'pseudo_admin' => 'trace-admin-' . $role,
            'email_admin' => $role . '@test.local',
            'mot_de_passe_admin' => Hash::make('secret123'),
            'role' => $role,
            'two_factor_enabled' => false,
        ]);
    }

    private function adminSession(Admin $admin): array
    {
        return [
            'admin_id' => $admin->id_admin,
            'admin_role' => $admin->role,
            'admin_2fa_passed' => true,
        ];
    }

    public function test_update_cree_snapshot_avec_modifications(): void
    {
        $admin = $this->makeAdmin('super_admin');
        $personnage = Personnage::factory()->create(['nom_perso' => 'Amber']);

        $this->withSession($this->adminSession($admin))
            ->patch(route('admin.personnages.update', $personnage), [
                'nom_perso' => 'Amber Prime',
                'fid_etoile' => $personnage->fid_etoile,
                'fid_element' => $personnage->fid_element,
                'fid_TArmes' => $personnage->fid_TArmes,
                'fid_TP' => $personnage->fid_TP,
            ])
            ->assertRedirect(route('admin.personnages.index'));

        $snapshot = Snapshot::query()
            ->where('fid_perso', $personnage->id_perso)
            ->where('action_type', 'update')
            ->latest('id_snapshot')
            ->first();

        $this->assertNotNull($snapshot);
        $this->assertSame($admin->id_admin, $snapshot->fid_admin);

        $modification = $snapshot->modifications()->first();
        $this->assertNotNull($modification);
        $this->assertSame('Amber', $modification->old_values['nom_perso'] ?? null);
        $this->assertSame('Amber Prime', $modification->new_values['nom_perso'] ?? null);
    }

    public function test_delete_fait_un_soft_delete_et_cree_snapshot(): void
    {
        $admin = $this->makeAdmin('super_admin');
        $personnage = Personnage::factory()->create();

        $this->withSession($this->adminSession($admin))
            ->delete(route('admin.personnages.destroy', $personnage))
            ->assertRedirect(route('admin.personnages.index'));

        $this->assertSoftDeleted('personnage', ['id_perso' => $personnage->id_perso]);

        $snapshot = Snapshot::query()
            ->where('fid_perso', $personnage->id_perso)
            ->where('action_type', 'delete')
            ->latest('id_snapshot')
            ->first();

        $this->assertNotNull($snapshot);
        $this->assertSame($admin->id_admin, $snapshot->fid_admin);

        $this->assertDatabaseHas('personnage', [
            'id_perso' => $personnage->id_perso,
            'deleted_by' => $admin->id_admin,
        ]);
    }

    public function test_restoration_est_bloquee_pour_non_super_admin(): void
    {
        $superAdmin = $this->makeAdmin('super_admin');
        $moderateur = $this->makeAdmin('moderateur');
        $personnage = Personnage::factory()->create();

        $this->withSession($this->adminSession($superAdmin))
            ->delete(route('admin.personnages.destroy', $personnage));

        $snapshot = Snapshot::query()->latest('id_snapshot')->firstOrFail();

        $this->withSession($this->adminSession($moderateur))
            ->post(route('admin.snapshots.restore', $snapshot))
            ->assertStatus(403);
    }

    public function test_super_admin_peut_restaurer_un_snapshot_de_delete(): void
    {
        $admin = $this->makeAdmin('super_admin');
        $personnage = Personnage::factory()->create();

        $this->withSession($this->adminSession($admin))
            ->delete(route('admin.personnages.destroy', $personnage));

        $snapshot = Snapshot::query()
            ->where('fid_perso', $personnage->id_perso)
            ->where('action_type', 'delete')
            ->latest('id_snapshot')
            ->firstOrFail();

        $this->withSession($this->adminSession($admin))
            ->post(route('admin.snapshots.restore', $snapshot))
            ->assertRedirect(route('admin.snapshots.show', $snapshot));

        $this->assertDatabaseHas('personnage', [
            'id_perso' => $personnage->id_perso,
            'deleted_at' => null,
            'deleted_by' => null,
        ]);
    }

    public function test_super_admin_peut_consulter_liste_et_detail_snapshots(): void
    {
        $admin = $this->makeAdmin('super_admin');
        $personnage = Personnage::factory()->create(['nom_perso' => 'Navia']);

        $this->withSession($this->adminSession($admin))
            ->patch(route('admin.personnages.update', $personnage), [
                'nom_perso' => 'Navia Prime',
                'fid_etoile' => $personnage->fid_etoile,
                'fid_element' => $personnage->fid_element,
                'fid_TArmes' => $personnage->fid_TArmes,
                'fid_TP' => $personnage->fid_TP,
            ]);

        $snapshot = Snapshot::query()->where('fid_perso', $personnage->id_perso)->latest('id_snapshot')->firstOrFail();

        $this->withSession($this->adminSession($admin))
            ->get(route('admin.personnages.snapshots.index', $personnage))
            ->assertStatus(200)
            ->assertSeeText('Historique des snapshots')
            ->assertSeeText((string) $snapshot->id_snapshot);

        $this->withSession($this->adminSession($admin))
            ->get(route('admin.snapshots.show', $snapshot))
            ->assertStatus(200)
            ->assertSeeText('Diff détaillé')
            ->assertSeeText('nom_perso');
    }

    public function test_super_admin_peut_consulter_restauration_globale_groupee(): void
    {
        $admin = $this->makeAdmin('super_admin');
        $p1 = Personnage::factory()->create(['nom_perso' => 'Albedo']);
        $p2 = Personnage::factory()->create(['nom_perso' => 'Xiao']);

        $this->withSession($this->adminSession($admin))
            ->patch(route('admin.personnages.update', $p1), [
                'nom_perso' => 'Albedo Prime',
                'fid_etoile' => $p1->fid_etoile,
                'fid_element' => $p1->fid_element,
                'fid_TArmes' => $p1->fid_TArmes,
                'fid_TP' => $p1->fid_TP,
            ]);

        $this->withSession($this->adminSession($admin))
            ->patch(route('admin.personnages.update', $p2), [
                'nom_perso' => 'Xiao Prime',
                'fid_etoile' => $p2->fid_etoile,
                'fid_element' => $p2->fid_element,
                'fid_TArmes' => $p2->fid_TArmes,
                'fid_TP' => $p2->fid_TP,
            ]);

        $this->withSession($this->adminSession($admin))
            ->get(route('admin.snapshots.index'))
            ->assertStatus(200)
            ->assertSeeText('Restauration globale des snapshots')
            ->assertSeeText('Albedo Prime')
            ->assertSeeText('Xiao Prime');
    }

    public function test_restauration_globale_bloquee_pour_non_super_admin(): void
    {
        $moderateur = $this->makeAdmin('moderateur');

        $this->withSession($this->adminSession($moderateur))
            ->get(route('admin.snapshots.index'))
            ->assertStatus(403);
    }
}
