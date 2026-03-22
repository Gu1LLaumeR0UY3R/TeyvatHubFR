<?php

namespace Tests\Feature;

use App\Models\Elements;
use App\Models\Etoile;
use App\Models\Personnage;
use App\Models\TypeArme;
use App\Models\TypePerso;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class Issue68RoulettePersonnageTest extends TestCase
{
    use RefreshDatabase;

    private function makePersonnage(string $nom = 'Hu Tao'): Personnage
    {
        return Personnage::factory()->create(['nom_perso' => $nom]);
    }

    private function createUser(): User
    {
        return User::factory()->create();
    }

    // Critère 1 : Accès non authentifié redirige vers login
    public function test_acces_non_authentifie_redirige(): void
    {
        $this->get(route('outils.roulette-personnage'))->assertRedirect();
    }

    // Critère 2 : Page rendue pour un joueur connecté
    public function test_page_rendue_pour_joueur_connecte(): void
    {
        $user = $this->createUser();
        $this->actingAs($user)
            ->get(route('outils.roulette-personnage'))
            ->assertStatus(200);
    }

    // Critère 3 : Confirmer marque perso_amelioration à true
    public function test_confirmation_met_amelioration_a_true(): void
    {
        $user = $this->createUser();
        $perso = $this->makePersonnage('Raiden Shogun');

        DB::table('joueur_personnage')->insert([
            'fid_joueur'         => $user->id,
            'fid_perso'          => $perso->id_perso,
            'niveau'             => 80,
            'affinite'           => 5,
            'perso_amelioration' => false,
        ]);

        $this->actingAs($user)
            ->post(route('outils.roulette-personnage.confirmer'), [
                'fid_perso' => $perso->id_perso,
            ])
            ->assertRedirect(route('outils.roulette-personnage'));

        $this->assertDatabaseHas('joueur_personnage', [
            'fid_joueur'         => $user->id,
            'fid_perso'          => $perso->id_perso,
            'perso_amelioration' => true,
        ]);
    }

    // Critère 4 : Confirmer avec le personnage d'un autre joueur retourne 403
    public function test_personnage_autre_joueur_interdit(): void
    {
        $user1 = $this->createUser();
        $user2 = $this->createUser();
        $perso = $this->makePersonnage('Ayaka');

        DB::table('joueur_personnage')->insert([
            'fid_joueur'         => $user1->id,
            'fid_perso'          => $perso->id_perso,
            'niveau'             => 70,
            'affinite'           => 1,
            'perso_amelioration' => false,
        ]);

        // user2 tries to confirm a character belonging to user1
        $this->actingAs($user2)
            ->post(route('outils.roulette-personnage.confirmer'), [
                'fid_perso' => $perso->id_perso,
            ])
            ->assertStatus(403);
    }

    // Critère 5 : Un fid_perso inexistant retourne 422 (validation failed for JSON request)
    public function test_perso_inexistant_retourne_erreur_validation(): void
    {
        $user = $this->createUser();
        $this->actingAs($user)
            ->postJson(route('outils.roulette-personnage.confirmer'), [
                'fid_perso' => 99999,
            ])
            ->assertStatus(422);
    }
}
