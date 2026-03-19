<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Issue36ProfilControllerTest extends TestCase
{
    use RefreshDatabase;

    private function makeUser(array $attrs = []): User
    {
        return User::factory()->create(array_merge([
            'pseudo'    => 'TravelerX',
            'bio_joueur'=> 'Un aventurier de Teyvat.',
        ], $attrs));
    }

    // Critère 1 : profil accessible aux utilisateurs connectés
    public function test_profil_accessible_aux_utilisateurs_connectes(): void
    {
        $user = $this->makeUser();
        $this->actingAs($user)->get(route('profil.index'))->assertStatus(200);
    }

    // Critère 2 : profil redirige les invités vers login
    public function test_profil_redirige_les_invites(): void
    {
        $this->get(route('profil.index'))->assertRedirect(route('login'));
    }

    // Critère 3 : profil affiche le pseudo
    public function test_profil_affiche_le_pseudo(): void
    {
        $user = $this->makeUser(['pseudo' => 'Lumine']);
        $this->actingAs($user)->get(route('profil.index'))->assertSee('Lumine');
    }

    // Critère 4 : profil affiche les statistiques
    public function test_profil_affiche_les_statistiques(): void
    {
        $user = $this->makeUser();
        $this->actingAs($user)
             ->get(route('profil.index'))
             ->assertStatus(200)
             ->assertSee('Personnages');
    }

    // Critère 5 : page personnages du profil accessible
    public function test_page_personnages_profil_accessible(): void
    {
        $user = $this->makeUser();
        $this->actingAs($user)->get(route('profil.personnages'))->assertStatus(200);
    }

    // Critère 6 : page armes du profil accessible
    public function test_page_armes_profil_accessible(): void
    {
        $user = $this->makeUser();
        $this->actingAs($user)->get(route('profil.armes'))->assertStatus(200);
    }

    // Critère 7 : page paramètres accessible
    public function test_page_parametres_accessible(): void
    {
        $user = $this->makeUser();
        $this->actingAs($user)->get(route('profil.parametres'))->assertStatus(200);
    }

    // Critère 8 : page personnages redirige les invités
    public function test_page_personnages_redirige_les_invites(): void
    {
        $this->get(route('profil.personnages'))->assertRedirect(route('login'));
    }

    // Critère 9 : page armes redirige les invités
    public function test_page_armes_redirige_les_invites(): void
    {
        $this->get(route('profil.armes'))->assertRedirect(route('login'));
    }

    // Critère 10 : page paramètres redirige les invités
    public function test_page_parametres_redirige_les_invites(): void
    {
        $this->get(route('profil.parametres'))->assertRedirect(route('login'));
    }
}
