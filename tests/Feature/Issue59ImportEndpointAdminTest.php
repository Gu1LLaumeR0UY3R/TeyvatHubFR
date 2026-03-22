<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class Issue59ImportEndpointAdminTest extends TestCase
{
    use RefreshDatabase;

    private function getAdminToken(): void
    {
        $admin = \App\Models\Admin::create([
            'pseudo_admin'       => 'admintest',
            'email_admin'        => 'admintest@test.com',
            'mot_de_passe_admin' => \Illuminate\Support\Facades\Hash::make('password'),
        ]);
        session(['admin_id' => $admin->id_admin]);
    }

    // Critère 1: Route POST /admin/import-genshin protégée par middleware admin
    public function test_route_protegee_par_middleware_admin(): void
    {
        $response = $this->post('/admin/import-genshin', [], [
            'Accept' => 'application/json',
        ]);
        $response->assertStatus(302); // Redirige vers login admin si non authentifié
    }

    // Critère 2: Retourne JSON si Accept: application/json
    public function test_retourne_json_si_accept_json(): void
    {
        $this->getAdminToken();

        Artisan::shouldReceive('call')->with('import:genshin')->once()->andReturn(0);
        Artisan::shouldReceive('output')->once()->andReturn("✅ Import terminé\n");

        $response = $this->post('/admin/import-genshin', [], [
            'Accept' => 'application/json',
        ]);
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        $response->assertJsonStructure(['success', 'message', 'output']);
    }

    // Critère 3: Retourne success: true quand import réussit
    public function test_retourne_success_true_quand_import_reussit(): void
    {
        $this->getAdminToken();

        Artisan::shouldReceive('call')->with('import:genshin')->once()->andReturn(0);
        Artisan::shouldReceive('output')->once()->andReturn("✅ Résumé de l'import");

        $response = $this->postJson('/admin/import-genshin');
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
    }

    // Critère 4: Retourne success: false et 500 quand import échoue
    public function test_retourne_500_quand_import_echoue(): void
    {
        $this->getAdminToken();

        Artisan::shouldReceive('call')->with('import:genshin')->once()->andReturn(1);
        Artisan::shouldReceive('output')->once()->andReturn("Erreur lors de l'import");

        $response = $this->postJson('/admin/import-genshin');
        $response->assertStatus(500);
        $response->assertJson(['success' => false]);
    }

    // Critère 5: Délègue l'import à la commande Artisan import:genshin
    public function test_delegue_a_artisan_import_genshin(): void
    {
        $this->getAdminToken();

        Artisan::shouldReceive('call')->with('import:genshin')->once()->andReturn(0);
        Artisan::shouldReceive('output')->once()->andReturn('OK');

        $this->postJson('/admin/import-genshin');
        // Si on arrive ici sans exception, la commande a été bien appelée
        $this->assertTrue(true);
    }

    // Critère 6: Redirige avec message si requête normale (pas JSON)
    public function test_redirige_avec_message_si_requete_normale(): void
    {
        $this->getAdminToken();

        Artisan::shouldReceive('call')->with('import:genshin')->once()->andReturn(0);
        Artisan::shouldReceive('output')->once()->andReturn('OK');

        $response = $this->post('/admin/import-genshin');
        $response->assertRedirect();
        $response->assertSessionHas('import_success');
    }
}
