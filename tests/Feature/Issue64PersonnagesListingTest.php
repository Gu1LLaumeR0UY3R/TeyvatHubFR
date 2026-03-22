<?php

namespace Tests\Feature;

use App\Models\Elements;
use App\Models\Etoile;
use App\Models\Personnage;
use App\Models\TypeArme;
use App\Models\TypePerso;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Issue64PersonnagesListingTest extends TestCase
{
    use RefreshDatabase;

    // Critère 1 : La page /personnages s'affiche (200)
    public function test_route_index_retourne_200(): void
    {
        $this->get('/personnages')->assertStatus(200);
    }

    // Critère 2 : La page /personnages/{slug} affiche le bon personnage
    public function test_route_show_retourne_200(): void
    {
        Personnage::factory()->create(['nom_perso' => 'Hu Tao', 'slug' => 'hu-tao']);
        $this->get('/personnages/hu-tao')->assertStatus(200)->assertSee('Hu Tao');
    }

    // Critère 3 : 404 sur slug inexistant
    public function test_404_sur_slug_inexistant(): void
    {
        $this->get('/personnages/inexistant-slug')->assertStatus(404);
    }

    // Critère 4 : 404 sur accès par ID
    public function test_acces_par_id_retourne_404(): void
    {
        Personnage::factory()->create();
        $this->get('/personnages/1')->assertStatus(404);
    }

    // Critère 5 : Filtres combinés fonctionnent sans rechargement (Alpine.js présent)
    public function test_filtres_combinés_sans_rechargement(): void
    {
        $response = $this->get('/personnages');
        $response->assertStatus(200);
        $response->assertSee('x-data', false);
        $response->assertSee('x-show', false);
        $response->assertSee('elementFilter', false);
    }

    // Critère 6 : Recherche server-side filtre les résultats
    public function test_recherche_filtre_resultats(): void
    {
        Personnage::factory()->create(['nom_perso' => 'Hu Tao']);
        Personnage::factory()->create(['nom_perso' => 'Ayaka']);
        $this->get('/personnages?search=Hu')
            ->assertSee('Hu Tao')
            ->assertDontSee('Ayaka');
    }

    // Critère 7 : Filtre par élément server-side
    public function test_filtre_par_element(): void
    {
        $pyro  = Elements::firstOrCreate(['libelle_element' => 'Pyro']);
        $cryo  = Elements::firstOrCreate(['libelle_element' => 'Cryo']);
        $etoile = Etoile::firstOrCreate(['libelle' => '4★']);
        $tp    = TypePerso::firstOrCreate(['libelle_TP' => 'Jouable']);
        $ta    = TypeArme::firstOrCreate(['libelle_TArme' => 'Épée']);

        Personnage::factory()->create(['nom_perso' => 'Hu Tao',  'fid_element' => $pyro->id_element]);
        Personnage::factory()->create(['nom_perso' => 'Ayaka',   'fid_element' => $cryo->id_element]);

        $this->get('/personnages?element=' . $pyro->id_element)
            ->assertSee('Hu Tao')
            ->assertDontSee('Ayaka');
    }

    // Critère 8 : Composant card-personnage utilisé dans la vue
    public function test_vue_utilise_composant_card_personnage(): void
    {
        $view = file_get_contents(resource_path('views/personnages/index.blade.php'));
        $this->assertStringContainsString('card-personnage', $view);
    }
}
