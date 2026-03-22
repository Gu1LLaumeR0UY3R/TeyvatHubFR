<?php

namespace Tests\Feature;

use App\Models\Nation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Issue66NationsListingTest extends TestCase
{
    use RefreshDatabase;

    // Critère 1 : La page /nations s'affiche (200)
    public function test_route_index_retourne_200(): void
    {
        $this->get('/nations')->assertStatus(200);
    }

    // Critère 2 : La page /nations/{slug} affiche la bonne nation
    public function test_route_show_retourne_200(): void
    {
        $nation = Nation::create(['nom_region' => 'Mondstadt', 'descri_region' => 'Ville du vent.']);
        $this->get('/nations/' . $nation->slug)->assertStatus(200)->assertSee('Mondstadt');
    }

    // Critère 3 : 404 sur slug inexistant
    public function test_404_sur_slug_inexistant(): void
    {
        $this->get('/nations/slug-inexistant')->assertStatus(404);
    }

    // Critère 4 : La liste affiche le nom des nations
    public function test_liste_affiche_nom_des_nations(): void
    {
        Nation::create(['nom_region' => 'Liyue', 'descri_region' => 'Nation du commerce.']);
        Nation::create(['nom_region' => 'Inazuma', 'descri_region' => 'Nation de l\'Éternité.']);
        $this->get('/nations')->assertSee('Liyue')->assertSee('Inazuma');
    }

    // Critère 5 : Composant card-nation utilisé dans la vue
    public function test_vue_utilise_composant_card_nation(): void
    {
        $view = file_get_contents(resource_path('views/nations/index.blade.php'));
        $this->assertStringContainsString('card-nation', $view);
    }

    // Critère 6 : Le slug est auto-généré depuis le nom_region
    public function test_slug_genere_automatiquement(): void
    {
        $nation = Nation::create(['nom_region' => 'Fontaine', 'descri_region' => 'Nation de la justice.']);
        $this->assertEquals('fontaine', $nation->slug);
        $this->get('/nations/fontaine')->assertStatus(200);
    }

    // Critère 7 : Ancienne route /histoire/nations redirige vers /nations
    public function test_ancienne_route_redirige(): void
    {
        $this->get('/histoire/nations')->assertRedirect('/nations');
    }
}
