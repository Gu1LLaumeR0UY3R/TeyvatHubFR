<?php

namespace Tests\Feature;

use App\Models\Elements;
use App\Models\Etoile;
use App\Models\Personnage;
use App\Models\TypeArme;
use App\Models\TypePerso;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Issue44OutilsControllerTest extends TestCase
{
    use RefreshDatabase;

    private function makePersonnage(string $nom = 'Fischl'): Personnage
    {
        $etoile   = Etoile::firstOrCreate(['libelle' => '4★']);
        $element  = Elements::firstOrCreate(['libelle_element' => 'Electro']);
        $typeArme = TypeArme::firstOrCreate(['libelle_TArme' => 'Arc']);
        $typePerso= TypePerso::firstOrCreate(['libelle_TP' => 'Normal']);

        return Personnage::create([
            'nom_perso'  => $nom,
            'fid_etoile' => $etoile->id_etoile,
            'fid_element'=> $element->id_element,
            'fid_TArmes' => $typeArme->id_TArmes,
            'fid_TP'     => $typePerso->id_TP,
        ]);
    }

    // Critère 1 : personnage-du-jour accessible sans connexion
    public function test_personnage_du_jour_accessible_sans_connexion(): void
    {
        $this->get(route('outils.personnage-du-jour'))->assertStatus(200);
    }

    // Critère 2 : personnage-du-jour affiche un personnage
    public function test_personnage_du_jour_affiche_un_personnage(): void
    {
        $this->makePersonnage('Klee');
        $this->get(route('outils.personnage-du-jour'))->assertStatus(200)->assertSee('Personnage du jour');
    }

    // Critère 3 : quiz accessible sans connexion
    public function test_quiz_accessible_sans_connexion(): void
    {
        $this->get(route('outils.quiz'))->assertStatus(200);
    }

    // Critère 4 : quiz avec 4 personnages affiche les choix
    public function test_quiz_avec_personnages_affiche_les_choix(): void
    {
        foreach (['Fischl', 'Klee', 'Diluc', 'Venti'] as $nom) {
            $this->makePersonnage($nom);
        }
        $this->get(route('outils.quiz'))->assertStatus(200);
    }

    // Critère 5 : quiz résultat correct
    public function test_quiz_resultat_correct(): void
    {
        $this->post(route('outils.quiz.resultat'), [
            'reponse' => 'Fischl',
            'correct' => 'Fischl',
        ])->assertStatus(200)->assertSee('Bonne réponse');
    }

    // Critère 6 : quiz résultat incorrect
    public function test_quiz_resultat_incorrect(): void
    {
        $this->post(route('outils.quiz.resultat'), [
            'reponse' => 'Klee',
            'correct' => 'Fischl',
        ])->assertStatus(200)->assertSee('Mauvaise réponse');
    }

    // Critère 7 : quiz résultat validation échoue sans paramètre
    public function test_quiz_resultat_validation_echoue_sans_parametre(): void
    {
        $this->post(route('outils.quiz.resultat'), [])->assertSessionHasErrors();
    }

    // Critère 8 : roulette nécessite connexion
    public function test_roulette_necessite_connexion(): void
    {
        $this->get(route('outils.roulette'))->assertRedirect(route('login'));
    }

    // Critère 9 : roulette accessible avec connexion
    public function test_roulette_accessible_avec_connexion(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->get(route('outils.roulette'))->assertStatus(200);
    }

    // Critère 10 : team nécessite connexion
    public function test_team_necessite_connexion(): void
    {
        $this->get(route('outils.team'))->assertRedirect(route('login'));
    }

    // Critère 11 : team accessible avec connexion
    public function test_team_accessible_avec_connexion(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->get(route('outils.team'))->assertStatus(200);
    }

    // Critère 12 : comparateur nécessite connexion
    public function test_comparateur_necessite_connexion(): void
    {
        $this->get(route('outils.comparateur'))->assertRedirect(route('login'));
    }

    // Critère 13 : comparateur accessible avec connexion
    public function test_comparateur_accessible_avec_connexion(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->get(route('outils.comparateur'))->assertStatus(200);
    }

    // Critère 14 : team generer retourne 200
    public function test_team_generer_retourne_200(): void
    {
        $user = User::factory()->create();
        foreach (['A', 'B', 'C', 'D'] as $nom) {
            $this->makePersonnage($nom);
        }
        $this->actingAs($user)
             ->post(route('outils.team.generer'), [])
             ->assertStatus(200);
    }
}
