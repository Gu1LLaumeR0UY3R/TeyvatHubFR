<?php

namespace Tests\Feature;

use App\Models\Elements;
use App\Models\Etoile;
use App\Models\Personnage;
use App\Models\TypeArme;
use App\Models\TypePerso;
use App\Models\User;
use App\Services\TeamCompositionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class Issue69RouletteTeamTest extends TestCase
{
    use RefreshDatabase;

    private function createUserWithPersonnages(int $count = 5): array
    {
        $user = User::factory()->create();
        $persos = Personnage::factory()->count($count)->create();

        foreach ($persos as $perso) {
            DB::table('joueur_personnage')->insert([
                'fid_joueur'         => $user->id,
                'fid_perso'          => $perso->id_perso,
                'niveau'             => 80,
                'affinite'           => 10,
                'perso_amelioration' => false,
            ]);
        }

        return [$user, $persos];
    }

    // Critère 1 : Accès non authentifié redirige
    public function test_acces_non_authentifie_redirige(): void
    {
        $this->get(route('outils.roulette-team'))->assertRedirect();
    }

    // Critère 2 : Page rendue pour un joueur connecté
    public function test_page_rendue_pour_joueur_connecte(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)
            ->get(route('outils.roulette-team'))
            ->assertStatus(200);
    }

    // Critère 3 : Génération d'équipe aléatoire retourne JSON success=true avec 4 personnages
    public function test_generation_equipe_aleatoire(): void
    {
        [$user] = $this->createUserWithPersonnages(6);

        $response = $this->actingAs($user)
            ->postJson(route('outils.roulette-team.generer'), [
                'mode' => 'aleatoire',
            ]);

        $response->assertStatus(200)
                 ->assertJsonPath('success', true)
                 ->assertJsonCount(4, 'team');
    }

    // Critère 4 : Roster vide retourne message d'erreur
    public function test_roster_vide_retourne_message(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->postJson(route('outils.roulette-team.generer'), [
                'mode' => 'aleatoire',
            ]);

        $response->assertStatus(200)
                 ->assertJsonPath('success', false);
    }

    // Critère 5 : Service buildRandom retourne 4 personnages
    public function test_service_build_random_retourne_4_personnages(): void
    {
        $persos = Personnage::factory()->count(6)->create();
        $service = new TeamCompositionService();
        $team = $service->buildRandom($persos);
        $this->assertCount(4, $team);
    }

    // Critère 6 : Validation du mode interdit
    public function test_mode_invalide_retourne_erreur(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)
            ->postJson(route('outils.roulette-team.generer'), [
                'mode' => 'invalide',
            ])
            ->assertStatus(422);
    }
}
