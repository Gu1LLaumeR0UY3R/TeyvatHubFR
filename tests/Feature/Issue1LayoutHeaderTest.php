<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Issue #1 — Layout principal et header avec navigation
 * Issue #2 — Footer global
 * Issue #6 — Intégration Tailwind CSS et styles de base
 */
class Issue1LayoutHeaderTest extends TestCase
{
    use RefreshDatabase;

    // ─── Issue #1 : Header ────────────────────────────────────────

    public function test_page_accueil_retourne_200(): void
    {
        $this->get('/')->assertStatus(200);
    }

    public function test_header_contient_logo_teyvathub(): void
    {
        $this->get('/')->assertSee('TeyvatHub');
    }

    public function test_header_contient_lien_personnages(): void
    {
        $this->get('/')->assertSee('Personnages');
    }

    public function test_header_contient_lien_armes(): void
    {
        $this->get('/')->assertSee('Armes');
    }

    public function test_header_contient_lien_ennemis(): void
    {
        $this->get('/')->assertSee('Ennemis');
    }

    public function test_header_contient_lien_cuisine(): void
    {
        $this->get('/')->assertSee('Cuisine');
    }

    public function test_header_contient_lien_histoire(): void
    {
        $this->get('/')->assertSee('Histoire');
    }

    public function test_header_affiche_connexion_pour_guest(): void
    {
        $this->get('/')->assertSee('Connexion');
    }

    public function test_header_affiche_inscription_pour_guest(): void
    {
        $this->get('/')->assertSee('Inscription');
    }

    public function test_header_affiche_profil_pour_utilisateur_connecte(): void
    {
        $user = \App\Models\User::factory()->create();
        $this->actingAs($user)->get('/')->assertSee($user->name);
    }

    public function test_header_masque_bouton_inscription_si_connecte(): void
    {
        $user = \App\Models\User::factory()->create();
        $this->actingAs($user)->get('/')->assertDontSee('Inscription');
    }

    public function test_logo_redirige_vers_accueil(): void
    {
        $response = $this->get('/');
        $response->assertSee('href="' . route('home') . '"', false);
    }

    public function test_header_contient_sous_menus_regions(): void
    {
        $this->get('/')->assertSee('Mondstadt');
    }

    public function test_header_contient_lien_materiaux_sous_ennemis(): void
    {
        $this->get('/')->assertSee('Matériaux');
    }

    public function test_header_contient_lien_ingredients_sous_animaux(): void
    {
        $this->get('/')->assertSee('Ingrédients');
    }

    // ─── Issue #2 : Footer ────────────────────────────────────────

    public function test_footer_contient_mention_legale(): void
    {
        $this->get('/')->assertSee('Site fan non officiel');
    }

    public function test_footer_contient_hoYoverse(): void
    {
        $this->get('/')->assertSee('HoYoverse');
    }

    public function test_footer_annee_dynamique(): void
    {
        $this->get('/')->assertSee(date('Y'));
    }

    public function test_footer_contient_lien_personnages(): void
    {
        $response = $this->get('/');
        // Le lien personnages apparaît au moins deux fois (header + footer)
        $response->assertSee(route('personnages.index'), false);
    }

    public function test_footer_contient_lien_armes(): void
    {
        $this->get('/')->assertSee(route('armes.index'), false);
    }

    public function test_footer_contient_lien_histoire(): void
    {
        $this->get('/')->assertSee(route('histoire.index'), false);
    }

    // ─── Issue #6 : Tailwind & assets ─────────────────────────────

    public function test_vite_assets_inclus(): void
    {
        // Vite génère un nom de fichier haché en production (ex: app-XXXXXXXX.css)
        $this->get('/')->assertSee('/build/assets/app-', false);
    }

    public function test_page_responsive_viewport_meta(): void
    {
        $this->get('/')->assertSee('viewport');
    }
}
