<?php

namespace Tests\Feature\Admin;

use App\Models\Admin;
use App\Models\Aptitude;
use App\Models\Personnage;
use App\Models\TypeApti;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class Issue81CompetencesTest extends TestCase
{
    use RefreshDatabase;

    private function makeAdmin(): Admin
    {
        return Admin::create([
            'pseudo_admin' => 'AdminTest',
            'email_admin' => 'admin@test.fr',
            'mot_de_passe_admin' => Hash::make('secret123'),
            'role' => 'admin',
        ]);
    }

    private function adminSession(): array
    {
        $admin = $this->makeAdmin();

        return ['admin_id' => $admin->id_admin];
    }

    public function test_update_competences_modifie_les_donnees_existantes(): void
    {
        $personnage = Personnage::factory()->create();
        $typeNormal = TypeApti::create(['libelle_Apti' => 'Attaque normale']);

        $aptitude = Aptitude::create([
            'titre_apti' => 'Attaque normale',
            'descri_apti' => 'Ancienne description',
            'lvl_apt' => 1,
            'sub_Apt' => null,
            'fid_TypeApti' => $typeNormal->id_TypeApti,
            'fid_perso' => $personnage->id_perso,
        ]);

        $this->withSession($this->adminSession())
            ->putJson(route('admin.personnage.block.competences.update', $personnage), [
                'competences' => [
                    [
                        'id_aptitude' => $aptitude->id_aptitude,
                        'titre_apti' => 'Attaque normale améliorée',
                        'descri_apti' => 'Nouvelle description',
                        'lvl_apt' => 6,
                        'fid_TypeApti' => $typeNormal->id_TypeApti,
                    ],
                ],
            ])
            ->assertStatus(200)
            ->assertJson(['success' => true, 'competences_count' => 1]);

        $this->assertDatabaseHas('aptitude', [
            'id_aptitude' => $aptitude->id_aptitude,
            'fid_perso' => $personnage->id_perso,
            'titre_apti' => 'Attaque normale améliorée',
            'descri_apti' => 'Nouvelle description',
            'lvl_apt' => 6,
        ]);
    }

    public function test_update_competences_cree_une_competence_si_id_absent(): void
    {
        $personnage = Personnage::factory()->create();
        $typeBurst = TypeApti::create(['libelle_Apti' => 'Déchaînement']);

        $this->withSession($this->adminSession())
            ->putJson(route('admin.personnage.block.competences.update', $personnage), [
                'competences' => [
                    [
                        'titre_apti' => 'Déferlante abyssale',
                        'descri_apti' => 'Inflige des dégâts Hydro.',
                        'lvl_apt' => 8,
                        'sub_Apt' => 'Hydro',
                        'fid_TypeApti' => $typeBurst->id_TypeApti,
                    ],
                ],
            ])
            ->assertStatus(200)
            ->assertJson(['success' => true, 'competences_count' => 1]);

        $this->assertDatabaseHas('aptitude', [
            'fid_perso' => $personnage->id_perso,
            'titre_apti' => 'Déferlante abyssale',
            'fid_TypeApti' => $typeBurst->id_TypeApti,
        ]);
    }

    public function test_update_competences_supprime_les_competences_non_soumises(): void
    {
        $personnage = Personnage::factory()->create();
        $typeSkill = TypeApti::create(['libelle_Apti' => 'Compétence élémentaire']);

        $kept = Aptitude::create([
            'titre_apti' => 'Compétence gardée',
            'descri_apti' => null,
            'lvl_apt' => 1,
            'sub_Apt' => null,
            'fid_TypeApti' => $typeSkill->id_TypeApti,
            'fid_perso' => $personnage->id_perso,
        ]);

        $toDelete = Aptitude::create([
            'titre_apti' => 'Compétence supprimée',
            'descri_apti' => null,
            'lvl_apt' => 1,
            'sub_Apt' => null,
            'fid_TypeApti' => $typeSkill->id_TypeApti,
            'fid_perso' => $personnage->id_perso,
        ]);

        $this->withSession($this->adminSession())
            ->putJson(route('admin.personnage.block.competences.update', $personnage), [
                'competences' => [
                    [
                        'id_aptitude' => $kept->id_aptitude,
                        'titre_apti' => 'Compétence gardée',
                        'descri_apti' => 'Toujours là',
                        'lvl_apt' => 2,
                        'fid_TypeApti' => $typeSkill->id_TypeApti,
                    ],
                ],
            ])
            ->assertStatus(200)
            ->assertJson(['success' => true, 'competences_count' => 1]);

        $this->assertDatabaseHas('aptitude', [
            'id_aptitude' => $kept->id_aptitude,
            'fid_perso' => $personnage->id_perso,
        ]);

        $this->assertDatabaseMissing('aptitude', [
            'id_aptitude' => $toDelete->id_aptitude,
            'fid_perso' => $personnage->id_perso,
        ]);
    }
}
