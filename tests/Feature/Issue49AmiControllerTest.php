<?php

namespace Tests\Feature;

use App\Models\Amitie;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Issue49AmiControllerTest extends TestCase
{
    use RefreshDatabase;

    // Critère 1 : liste amis redirige les invités
    public function test_liste_amis_redirige_les_invites(): void
    {
        $this->get(route('profil.amis'))->assertRedirect(route('login'));
    }

    // Critère 2 : liste amis accessible avec connexion
    public function test_liste_amis_accessible_avec_connexion(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->get(route('profil.amis'))->assertStatus(200);
    }

    // Critère 3 : affiche les amis acceptés
    public function test_affiche_les_amis_acceptes(): void
    {
        $user = User::factory()->create(['pseudo' => 'Lumine']);
        $ami  = User::factory()->create(['pseudo' => 'Aether']);

        Amitie::create([
            'fid_demandeur' => $user->id,
            'fid_receveur'  => $ami->id,
            'statut'        => 'accepte',
        ]);

        $this->actingAs($user)
             ->get(route('profil.amis'))
             ->assertSee('Aether');
    }

    // Critère 4 : affiche les demandes reçues en attente
    public function test_affiche_les_demandes_recues(): void
    {
        $user     = User::factory()->create(['pseudo' => 'Lumine']);
        $demandeur = User::factory()->create(['pseudo' => 'Bennet']);

        Amitie::create([
            'fid_demandeur' => $demandeur->id,
            'fid_receveur'  => $user->id,
            'statut'        => 'en_attente',
        ]);

        $this->actingAs($user)
             ->get(route('profil.amis'))
             ->assertSee('Demandes reçues');
    }

    // Critère 5 : liste vide ne génère pas d'erreur
    public function test_liste_vide_ne_genere_pas_d_erreur(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->get(route('profil.amis'))->assertStatus(200);
    }
}
