<?php

namespace Tests\Feature\Admin;

use App\Models\Admin;
use App\Models\Arme;
use App\Models\Personnage;
use App\Models\PersonnageArmeRecommandee;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class Issue78ArmesRecommandeesTest extends TestCase
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

    public function test_update_armes_recommandees_insere_en_base(): void
    {
        $personnage = Personnage::factory()->create();
        $arme1 = Arme::factory()->create();
        $arme2 = Arme::factory()->create();

        $this->withSession($this->adminSession())
            ->putJson(route('admin.personnage.block.armes.update', $personnage), [
                'armes' => [
                    ['id_arme' => $arme1->id_arme, 'rang' => 1, 'is_starter' => true, 'origine' => 'tirage'],
                    ['id_arme' => $arme2->id_arme, 'rang' => 3, 'is_starter' => false, 'origine' => 'craft'],
                ],
            ])
            ->assertStatus(200)
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('personnage_arme_recommandee', [
            'fid_perso' => $personnage->id_perso,
            'fid_arme' => $arme1->id_arme,
            'position' => 1,
            'starter' => 1,
            'origine' => 'tirage',
        ]);

        $this->assertDatabaseHas('personnage_arme_recommandee', [
            'fid_perso' => $personnage->id_perso,
            'fid_arme' => $arme2->id_arme,
            'position' => 2,
            'starter' => 0,
            'origine' => 'craft',
        ]);
    }

    public function test_starter_weapon_unique_par_personnage(): void
    {
        $personnage = Personnage::factory()->create();
        $arme1 = Arme::factory()->create();
        $arme2 = Arme::factory()->create();

        $this->withSession($this->adminSession())
            ->putJson(route('admin.personnage.block.armes.update', $personnage), [
                'armes' => [
                    ['id_arme' => $arme1->id_arme, 'rang' => 1, 'is_starter' => true],
                    ['id_arme' => $arme2->id_arme, 'rang' => 2, 'is_starter' => true],
                ],
            ])
            ->assertStatus(200);

        $starterCount = PersonnageArmeRecommandee::query()
            ->where('fid_perso', $personnage->id_perso)
            ->where('starter', 1)
            ->count();

        $this->assertSame(1, $starterCount);
    }

    public function test_maximum_6_armes_respecte(): void
    {
        $personnage = Personnage::factory()->create();
        $armes = collect(range(1, 7))->map(function (int $i) {
            return Arme::factory()->create(['nom_arme' => 'Weapon ' . $i]);
        });

        $payload = [
            'armes' => $armes->values()->map(fn (Arme $arme) => [
                'id_arme' => $arme->id_arme,
                'rang' => 1,
                'is_starter' => false,
            ])->all(),
        ];

        $this->withSession($this->adminSession())
            ->putJson(route('admin.personnage.block.armes.update', $personnage), $payload)
            ->assertStatus(422);
    }

    public function test_suppression_arme_recommandee(): void
    {
        $personnage = Personnage::factory()->create();
        $arme = Arme::factory()->create();

        PersonnageArmeRecommandee::create([
            'fid_perso' => $personnage->id_perso,
            'fid_arme' => $arme->id_arme,
            'position' => 1,
            'starter' => 0,
            'origine' => 'tirage',
        ]);

        $this->withSession($this->adminSession())
            ->deleteJson(route('admin.personnage.block.armes.delete', ['personnage' => $personnage, 'id_arme' => $arme->id_arme]))
            ->assertStatus(200)
            ->assertJson(['success' => true]);

        $this->assertDatabaseMissing('personnage_arme_recommandee', [
            'fid_perso' => $personnage->id_perso,
            'fid_arme' => $arme->id_arme,
        ]);
    }
}
