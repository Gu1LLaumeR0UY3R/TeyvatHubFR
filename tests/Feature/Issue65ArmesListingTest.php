<?php

namespace Tests\Feature;

use App\Models\Arme;
use App\Models\Elements;
use App\Models\Etoile;
use App\Models\TypeArme;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Issue65ArmesListingTest extends TestCase
{
    use RefreshDatabase;

    // Critère 1 : La page /armes s'affiche (200)
    public function test_route_index_retourne_200(): void
    {
        $this->get('/armes')->assertStatus(200);
    }

    // Critère 2 : La page /armes/{slug} affiche la bonne arme
    public function test_route_show_retourne_200(): void
    {
        Arme::factory()->create(['nom_arme' => 'Epée de test', 'slug' => 'epee-de-test']);
        $this->get('/armes/epee-de-test')->assertStatus(200)->assertSee('Epée de test');
    }

    // Critère 3 : 404 sur slug inexistant
    public function test_404_sur_slug_inexistant(): void
    {
        $this->get('/armes/inexistant-slug')->assertStatus(404);
    }

    // Critère 4 : 404 sur accès par ID
    public function test_acces_par_id_retourne_404(): void
    {
        Arme::factory()->create();
        $this->get('/armes/1')->assertStatus(404);
    }

    // Critère 5 : Filtres Alpine.js présents dans la vue
    public function test_filtres_alpinejs_presents(): void
    {
        $response = $this->get('/armes');
        $response->assertStatus(200);
        $response->assertSee('x-data', false);
        $response->assertSee('x-show', false);
        $response->assertSee('typeFilter', false);
    }

    // Critère 6 : Composant card-arme utilisé dans la vue
    public function test_vue_utilise_composant_card_arme(): void
    {
        $view = file_get_contents(resource_path('views/armes/index.blade.php'));
        $this->assertStringContainsString('card-arme', $view);
    }
}
