<?php

namespace Tests\Feature;

use App\Models\Admin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class Issue50AdminAuthTest extends TestCase
{
    use RefreshDatabase;

    private function makeAdmin(array $attrs = []): Admin
    {
        return Admin::create(array_merge([
            'pseudo_admin'       => 'SuperAdmin',
            'email_admin'        => 'admin@teyvathub.fr',
            'mot_de_passe_admin'  => Hash::make('secret123'),
            'role'               => 'superadmin',
        ], $attrs));
    }

    // Critère 1 : page login admin retourne 200
    public function test_page_login_admin_retourne_200(): void
    {
        $this->get(route('admin.login'))->assertStatus(200);
    }

    // Critère 2 : login invalide retourne erreur
    public function test_login_invalide_retourne_erreur(): void
    {
        $this->makeAdmin();
        $this->post(route('admin.authenticate'), [
            'email'    => 'admin@teyvathub.fr',
            'password' => 'mauvais_mdp',
        ])->assertSessionHasErrors('email');
    }

    // Régression : un hash legacy/non-Bcrypt ne doit pas provoquer une 500
    public function test_login_admin_legacy_hash_ne_provoque_pas_500(): void
    {
        DB::table('admin')->insert([
            'pseudo_admin' => 'LegacyAdmin',
            'email_admin' => 'legacy@teyvathub.fr',
            'mot_de_passe_admin' => 'legacy_plaintext_password',
            'role' => 'superadmin',
        ]);

        $this->post(route('admin.authenticate'), [
            'email' => 'legacy@teyvathub.fr',
            'password' => 'legacy_plaintext_password',
        ])
            ->assertStatus(302)
            ->assertSessionHasErrors('email');
    }

    // Critère 3 : login valide redirige vers dashboard
    public function test_login_valide_redirige_vers_dashboard(): void
    {
        $this->makeAdmin();
        $this->post(route('admin.authenticate'), [
            'email'    => 'admin@teyvathub.fr',
            'password' => 'secret123',
        ])->assertRedirect(route('admin.dashboard'));
    }

    // Critère 4 : session admin est définie après connexion
    public function test_session_admin_definie_apres_connexion(): void
    {
        $this->makeAdmin();
        $this->post(route('admin.authenticate'), [
            'email'    => 'admin@teyvathub.fr',
            'password' => 'secret123',
        ]);
        $this->assertNotEmpty(session('admin_id'));
    }

    // Critère 5 : dashboard redirige sans session
    public function test_dashboard_redirige_sans_session(): void
    {
        $this->get(route('admin.dashboard'))->assertRedirect(route('admin.login'));
    }

    // Critère 6 : dashboard accessible avec session
    public function test_dashboard_accessible_avec_session(): void
    {
        $this->withSession(['admin_id' => 1, 'admin_pseudo' => 'Admin'])
             ->get(route('admin.dashboard'))
             ->assertStatus(200);
    }

    // Critère 7 : déconnexion supprime la session
    public function test_deconnexion_supprime_la_session(): void
    {
        $this->withSession(['admin_id' => 1])
             ->post(route('admin.logout'))
             ->assertRedirect(route('admin.login'));
        $this->assertEmpty(session('admin_id'));
    }

    // Critère 8 : validation email requis
    public function test_validation_email_requis(): void
    {
        $this->post(route('admin.authenticate'), ['password' => 'test'])
             ->assertSessionHasErrors('email');
    }

    // Critère 9 : validation password requis
    public function test_validation_password_requis(): void
    {
        $this->post(route('admin.authenticate'), ['email' => 'admin@test.com'])
             ->assertSessionHasErrors('password');
    }

    // Critère 10 : admin connecté redirigé depuis login vers dashboard
    public function test_admin_connecte_redirige_depuis_login(): void
    {
        $this->withSession(['admin_id' => 1, 'admin_pseudo' => 'Admin'])
             ->get(route('admin.login'))
             ->assertRedirect(route('admin.dashboard'));
    }
}
