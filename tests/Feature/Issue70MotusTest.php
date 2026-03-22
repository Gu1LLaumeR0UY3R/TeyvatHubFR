<?php

namespace Tests\Feature;

use App\Models\Personnage;
use App\Services\MotusService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Issue70MotusTest extends TestCase
{
    use RefreshDatabase;

    // Critère 1 : La route /jeux/motus est publique (200)
    public function test_route_accessible_sans_connexion(): void
    {
        // Pool must not be empty — seed a personnage
        Personnage::factory()->create(['nom_perso' => 'Mondstadt']);

        $this->get(route('jeux.motus'))->assertStatus(200);
    }

    // Critère 2 : getDailyWord() retourne le même mot deux fois le même jour
    public function test_seed_journaliere_retourne_meme_mot(): void
    {
        Personnage::factory()->create(['nom_perso' => 'Fontaine']);
        $service = new MotusService();
        $word1 = $service->getDailyWord();
        $word2 = $service->getDailyWord();
        $this->assertEquals($word1, $word2);
    }

    // Critère 3 : validateGuess avec chaque lettre correcte retourne tout 'correct'
    public function test_validation_lettre_bonne_position(): void
    {
        $service = new MotusService();
        $result = $service->validateGuess('Pyro', 'Pyro');
        foreach ($result as $r) {
            $this->assertEquals('correct', $r['status']);
        }
    }

    // Critère 4 : validateGuess détecte les lettres en mauvaise position
    public function test_validation_lettre_mauvaise_position(): void
    {
        $service = new MotusService();
        // 'oryP' — all letters of 'Pyro' but shuffled
        $result = $service->validateGuess('oryP', 'Pyro');
        $statuses = array_column($result, 'status');
        // At least one 'present' (wrong position) expected
        $this->assertContains('present', $statuses);
    }

    // Critère 5 : normalize() supprime les accents
    public function test_normalisation_des_accents(): void
    {
        $service = new MotusService();
        $this->assertEquals('elee', $service->normalize('élée'));
        $this->assertEquals('aaaaaa', $service->normalize('àáâãäå'));
        $this->assertEquals('eeee', $service->normalize('èéêë'));
    }

    // Critère 6 : POST /jeux/motus/valider retourne JSON avec result et won
    public function test_endpoint_valider_retourne_json(): void
    {
        $response = $this->postJson(route('jeux.motus.valider'), [
            'guess' => 'Pyro',
            'word'  => 'Pyro',
        ]);
        $response->assertStatus(200)
                 ->assertJsonStructure(['result', 'won'])
                 ->assertJsonPath('won', true);
    }

    // Critère 7 : Mots de moins de 3 caractères exclus du pool
    public function test_mots_courts_exclus_du_pool(): void
    {
        // 'A' is < 3 chars, 'Hu' is < 3 chars
        Personnage::factory()->create(['nom_perso' => 'A']);
        Personnage::factory()->create(['nom_perso' => 'Hu']);
        Personnage::factory()->create(['nom_perso' => 'Fischl']);

        $service = new MotusService();
        $pool = $service->getWordPool();

        $this->assertNotContains('A', $pool->toArray());
        $this->assertNotContains('Hu', $pool->toArray());
        $this->assertContains('Fischl', $pool->toArray());
    }
}
