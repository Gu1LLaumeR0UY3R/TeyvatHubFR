<?php

namespace Tests\Feature;

use App\Models\Evenement;
use App\Models\Personnage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Issue #3 — HomeController et route d'accueil
 * Issue #4 — Vue Blade de la page d'accueil
 */
class Issue3HomeControllerTest extends TestCase
{
    use RefreshDatabase;

    // ─── Issue #3 : HomeController ────────────────────────────────

    public function test_route_accueil_retourne_200(): void
    {
        $this->get('/')->assertStatus(200);
    }

    public function test_route_accueil_accessible_sans_connexion(): void
    {
        $this->get('/')->assertStatus(200);
    }

    public function test_controller_passe_derniers_personnages_a_la_vue(): void
    {
        Personnage::factory()->count(3)->create();
        $this->get('/')
            ->assertStatus(200)
            ->assertViewHas('derniers_personnages');
    }

    public function test_controller_passe_prochains_evenements_a_la_vue(): void
    {
        $this->get('/')
            ->assertStatus(200)
            ->assertViewHas('prochains_evenements');
    }

    public function test_controller_passe_compteurs_a_la_vue(): void
    {
        $this->get('/')
            ->assertStatus(200)
            ->assertViewHas('compteurs');
    }

    public function test_controller_retourne_6_derniers_personnages_maximum(): void
    {
        Personnage::factory()->count(10)->create();
        $response = $this->get('/');
        $this->assertLessThanOrEqual(6, $response->viewData('derniers_personnages')->count());
    }

    public function test_controller_retourne_4_evenements_maximum(): void
    {
        Evenement::factory()->count(6)->create(['date_fin' => now()->addDays(10)->toDateString()]);
        $response = $this->get('/');
        $this->assertLessThanOrEqual(4, $response->viewData('prochains_evenements')->count());
    }

    public function test_evenements_expires_exclus(): void
    {
        Evenement::factory()->create([
            'titre'    => 'Evenement expire',
            'date_fin' => now()->subDay()->toDateString(),
        ]);
        $this->get('/')
            ->assertStatus(200)
            ->assertDontSee('Evenement expire');
    }

    public function test_compteurs_contient_personnages(): void
    {
        Personnage::factory()->count(3)->create();
        $response = $this->get('/');
        $compteurs = $response->viewData('compteurs');
        $this->assertEquals(3, $compteurs['personnages']);
    }

    // ─── Issue #4 : Vue home ──────────────────────────────────────

    public function test_hero_banner_affiche(): void
    {
        $this->get('/')->assertSee('Explorer');
    }

    public function test_cta_creer_compte_visible_pour_guest(): void
    {
        $this->get('/')->assertSee('Créer un compte');
    }

    public function test_cta_creer_compte_masque_si_connecte(): void
    {
        $user = \App\Models\User::factory()->create();
        $this->actingAs($user)->get('/')->assertDontSee('Créer un compte');
    }

    public function test_section_encyclopedie_affiche_compteurs(): void
    {
        Personnage::factory()->count(5)->create();
        $this->get('/')->assertSee('5');
    }

    public function test_section_personnages_affiche_noms(): void
    {
        Personnage::factory()->create(['nom_perso' => 'Hu Tao', 'slug' => 'hu-tao']);
        $this->get('/')->assertSee('Hu Tao');
    }

    public function test_badge_en_cours_pour_evenement_actif(): void
    {
        Evenement::factory()->create([
            'titre'      => 'Fête du Printemps',
            'date_debut' => now()->subDay()->toDateString(),
            'date_fin'   => now()->addDays(5)->toDateString(),
        ]);
        $this->get('/')->assertSee('En cours');
    }

    public function test_badge_a_venir_pour_evenement_futur(): void
    {
        Evenement::factory()->create([
            'titre'      => 'Prochain Event',
            'date_debut' => now()->addDays(3)->toDateString(),
            'date_fin'   => now()->addDays(10)->toDateString(),
        ]);
        $this->get('/')->assertSee('À venir');
    }

    public function test_page_accueil_affiche_layout(): void
    {
        $this->get('/')->assertSee('TeyvatHub');
    }

    public function test_vue_utilise_layout_app(): void
    {
        $this->get('/')->assertSee('Encyclopédie');
    }
}
