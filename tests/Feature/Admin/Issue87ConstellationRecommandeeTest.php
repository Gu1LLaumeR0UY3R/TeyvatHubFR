<?php

namespace Tests\Feature\Admin;

use App\Models\Admin;
use App\Models\Constellation;
use App\Models\Personnage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class Issue87ConstellationRecommandeeTest extends TestCase
{
    use RefreshDatabase;

    private function makeAdmin(): Admin
    {
        return Admin::create([
            'pseudo_admin' => 'AdminTest',
            'email_admin' => 'admin87@test.fr',
            'mot_de_passe_admin' => Hash::make('secret123'),
            'role' => 'super_admin',
            'two_factor_enabled' => false,
        ]);
    }

    private function adminSession(): array
    {
        $admin = $this->makeAdmin();

        return [
            'admin_id' => $admin->id_admin,
            'admin_role' => $admin->role,
            'admin_2fa_passed' => true,
        ];
    }

    public function test_admin_peut_marquer_une_constellation_comme_recommandee(): void
    {
        $personnage = Personnage::factory()->create();
        $const1 = Constellation::create([
            'fid_perso' => $personnage->id_perso,
            'titre_const' => 'C1',
            'descri_const' => 'Description C1',
        ]);

        $this->withSession($this->adminSession())
            ->putJson(route('admin.personnage.block.constellations.update', $personnage), [
                'constellations' => [
                    [
                        'id_const' => $const1->id_const,
                        'titre_const' => 'C1',
                        'descri_const' => 'Description C1',
                        'recommandee' => true,
                    ],
                ],
            ])
            ->assertStatus(200)
            ->assertJson(['success' => true])
            ->assertJsonPath('constellations.0.recommandee', true);

        $this->assertDatabaseHas('constellation', [
            'id_const' => $const1->id_const,
            'recommandee' => true,
        ]);
    }

    public function test_recommandee_est_false_par_defaut(): void
    {
        $personnage = Personnage::factory()->create();
        $const1 = Constellation::create([
            'fid_perso' => $personnage->id_perso,
            'titre_const' => 'C1',
            'descri_const' => 'Description C1',
        ]);

        $this->assertDatabaseHas('constellation', [
            'id_const' => $const1->id_const,
            'recommandee' => false,
        ]);
    }

    public function test_plusieurs_constellations_peuvent_etre_recommandees_simultanement(): void
    {
        $personnage = Personnage::factory()->create();
        $const1 = Constellation::create([
            'fid_perso' => $personnage->id_perso,
            'titre_const' => 'C1',
        ]);
        $const2 = Constellation::create([
            'fid_perso' => $personnage->id_perso,
            'titre_const' => 'C2',
        ]);
        $const3 = Constellation::create([
            'fid_perso' => $personnage->id_perso,
            'titre_const' => 'C3',
        ]);

        $this->withSession($this->adminSession())
            ->putJson(route('admin.personnage.block.constellations.update', $personnage), [
                'constellations' => [
                    ['id_const' => $const1->id_const, 'titre_const' => 'C1', 'recommandee' => true],
                    ['id_const' => $const2->id_const, 'titre_const' => 'C2', 'recommandee' => false],
                    ['id_const' => $const3->id_const, 'titre_const' => 'C3', 'recommandee' => true],
                ],
            ])
            ->assertStatus(200);

        $this->assertDatabaseHas('constellation', ['id_const' => $const1->id_const, 'recommandee' => true]);
        $this->assertDatabaseHas('constellation', ['id_const' => $const2->id_const, 'recommandee' => false]);
        $this->assertDatabaseHas('constellation', ['id_const' => $const3->id_const, 'recommandee' => true]);
    }

    public function test_fiche_personnage_publique_expose_le_flag_recommandee(): void
    {
        $personnage = Personnage::factory()->create();
        Constellation::create([
            'fid_perso' => $personnage->id_perso,
            'titre_const' => 'C1 Prioritaire',
            'descri_const' => 'Description prioritaire',
            'recommandee' => true,
        ]);

        $this->get(route('personnages.show', $personnage))
            ->assertStatus(200)
            ->assertSee('C1 Prioritaire');
    }
}
