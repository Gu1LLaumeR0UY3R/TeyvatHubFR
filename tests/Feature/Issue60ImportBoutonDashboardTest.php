<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class Issue60ImportBoutonDashboardTest extends TestCase
{
    use RefreshDatabase;

    private function loginAdmin(): void
    {
        $admin = \App\Models\Admin::create([
            'pseudo_admin'       => 'admindash',
            'email_admin'        => 'admindash@test.com',
            'mot_de_passe_admin' => Hash::make('password'),
        ]);
        session(['admin_id' => $admin->id_admin]);
    }

    // Critère 1: Dashboard admin contient un bouton d'import
    public function test_dashboard_contient_bouton_import(): void
    {
        $this->loginAdmin();

        $response = $this->get('/admin');
        $response->assertStatus(200);
        $response->assertSee('Importer depuis teyvat-dev');
    }

    // Critère 2: Dashboard contient le composant Alpine.js importGenshin
    public function test_dashboard_contient_composant_alpine(): void
    {
        $this->loginAdmin();

        $response = $this->get('/admin');
        $response->assertStatus(200);
        $response->assertSee('importGenshin()', false);
    }

    // Critère 3: Dashboard contient la modale de confirmation
    public function test_dashboard_contient_modale_confirmation(): void
    {
        $this->loginAdmin();

        $response = $this->get('/admin');
        $response->assertStatus(200);
        $response->assertSee('Confirmer l\'import', false);
    }

    // Critère 4: Dashboard contient l'indicateur de chargement
    public function test_dashboard_contient_loader(): void
    {
        $this->loginAdmin();

        $response = $this->get('/admin');
        $response->assertStatus(200);
        $response->assertSee('animate-spin', false);
    }

    // Critère 5: Dashboard contient l'appel AJAX correct
    public function test_dashboard_contient_appel_ajax(): void
    {
        $this->loginAdmin();

        $response = $this->get('/admin');
        $response->assertStatus(200);
        $response->assertSee('runImport', false);
    }

    // Critère 6: Dashboard affiche les stats nations (pas régions)
    public function test_dashboard_affiche_stat_nations(): void
    {
        $this->loginAdmin();

        $response = $this->get('/admin');
        $response->assertStatus(200);
        $response->assertSee('Nations');
    }
}
