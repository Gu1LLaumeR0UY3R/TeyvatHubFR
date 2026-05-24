<?php

namespace Tests\Feature\Admin;

use App\Models\Admin;
use App\Models\Artefact;
use App\Models\Personnage;
use App\Models\PersonnageArtefactRecommandee;
use App\Models\Rarete;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class Issue79ArtefactsRecommandesTest extends TestCase
{
    use RefreshDatabase;

    private function makeAdmin(): Admin
    {
        return Admin::create([
            'pseudo_admin' => 'AdminTest',
            'email_admin' => 'admin@test.fr',
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

    private function makeArtefact(string $name): Artefact
    {
        $rarete = Rarete::firstOrCreate(['libelle_rareté' => '5★']);

        return Artefact::create([
            'nom_artefact' => $name,
            'fid_rareté' => $rarete->id_rareté,
        ]);
    }

    public function test_build_4p_insere_correctement(): void
    {
        $personnage = Personnage::factory()->create();
        $set1 = $this->makeArtefact('Marechaussee Hunter');

        $this->withSession($this->adminSession())
            ->putJson(route('admin.personnage.block.artefacts.update', $personnage), [
                'builds' => [
                    [
                        'artefact1_id' => $set1->id_artefact,
                        'pieces_1' => 4,
                    ],
                ],
            ])
            ->assertStatus(200)
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('personnage_artefact_recommandee', [
            'fid_perso' => $personnage->id_perso,
            'fid_artefact_1' => $set1->id_artefact,
            'pieces_1' => '4p',
            'fid_artefact_2' => null,
            'pieces_2' => null,
            'position' => 1,
        ]);
    }

    public function test_build_2p_2p_requiert_deux_sets(): void
    {
        $personnage = Personnage::factory()->create();
        $set1 = $this->makeArtefact('Golden Troupe');

        $this->withSession($this->adminSession())
            ->putJson(route('admin.personnage.block.artefacts.update', $personnage), [
                'builds' => [
                    [
                        'artefact1_id' => $set1->id_artefact,
                        'pieces_1' => 2,
                    ],
                ],
            ])
            ->assertStatus(422);
    }

    public function test_build_2p_2p_insere_correctement(): void
    {
        $personnage = Personnage::factory()->create();
        $set1 = $this->makeArtefact('Golden Troupe');
        $set2 = $this->makeArtefact('Noblesse Oblige');

        $this->withSession($this->adminSession())
            ->putJson(route('admin.personnage.block.artefacts.update', $personnage), [
                'builds' => [
                    [
                        'artefact1_id' => $set1->id_artefact,
                        'pieces_1' => 2,
                        'artefact2_id' => $set2->id_artefact,
                        'pieces_2' => 2,
                    ],
                ],
            ])
            ->assertStatus(200)
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('personnage_artefact_recommandee', [
            'fid_perso' => $personnage->id_perso,
            'fid_artefact_1' => $set1->id_artefact,
            'pieces_1' => '2p',
            'fid_artefact_2' => $set2->id_artefact,
            'pieces_2' => '2p',
            'position' => 1,
        ]);
    }

    public function test_suppression_build_artefact(): void
    {
        $personnage = Personnage::factory()->create();
        $set1 = $this->makeArtefact('Marechaussee Hunter');

        $build = PersonnageArtefactRecommandee::create([
            'fid_perso' => $personnage->id_perso,
            'fid_artefact_1' => $set1->id_artefact,
            'pieces_1' => '4p',
            'position' => 1,
        ]);

        $this->withSession($this->adminSession())
            ->deleteJson(route('admin.personnage.block.artefacts.delete', ['personnage' => $personnage, 'id_build' => $build->id_build]))
            ->assertStatus(200)
            ->assertJson(['success' => true]);

        $this->assertDatabaseMissing('personnage_artefact_recommandee', [
            'id_build' => $build->id_build,
        ]);
    }
}
