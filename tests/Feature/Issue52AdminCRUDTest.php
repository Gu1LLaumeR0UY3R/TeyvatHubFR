<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Animal;
use App\Models\Arme;
use App\Models\Chronologie;
use App\Models\Elements;
use App\Models\Ennemi;
use App\Models\Etoile;
use App\Models\Personnage;
use App\Models\Plat;
use App\Models\Rarete;
use App\Models\Region;
use App\Models\Role;
use App\Models\TypeAnimal;
use App\Models\TypeArme;
use App\Models\TypeEnnemi;
use App\Models\TypePerso;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Issues #52-56 — Admin CRUD routes
 */
class Issue52AdminCRUDTest extends TestCase
{
    use RefreshDatabase;

    private function makeAdmin(): Admin
    {
        return Admin::create([
            'pseudo_admin'      => 'AdminTest',
            'email_admin'       => 'admin@test.fr',
            'mot_de_passe_admin' => Hash::make('secret123'),
            'role'              => 'superadmin',
        ]);
    }

    private function adminSession(): array
    {
        $admin = $this->makeAdmin();
        return ['admin_id' => $admin->id_admin];
    }

    // ──────────────────────────────────────────────
    // Critère 1 : admin dashboard est protégé par middleware
    // ──────────────────────────────────────────────
    public function test_admin_dashboard_non_authentifie_redirige(): void
    {
        $this->get(route('admin.dashboard'))->assertRedirect(route('admin.login'));
    }

    // ──────────────────────────────────────────────
    // Critère 2 : liste personnages retourne 200 pour admin connecté
    // ──────────────────────────────────────────────
    public function test_admin_personnages_index_retourne_200(): void
    {
        $this->withSession($this->adminSession())
            ->get(route('admin.personnages.index'))
            ->assertStatus(200);
    }

    // ──────────────────────────────────────────────
    // Critère 3 : création ouvre le formulaire sans créer de brouillon
    // ──────────────────────────────────────────────
    public function test_admin_personnages_create_affiche_formulaire_sans_brouillon(): void
    {
        Etoile::create(['libelle' => '5★']);
        TypePerso::create(['libelle_TP' => 'Standard']);
        Elements::create(['libelle_element' => 'Dendro']);
        TypeArme::create(['libelle_TArme' => 'Arc']);

        $this->withSession($this->adminSession())
            ->get(route('admin.personnages.create'))
            ->assertStatus(200)
            ->assertSeeText('Nouveau personnage');

        $this->assertDatabaseCount('personnage', 0);
    }

    // ──────────────────────────────────────────────
    // Critère 4 : création personnage redirige avec succès
    // ──────────────────────────────────────────────
    public function test_admin_personnages_store_cree_et_redirige(): void
    {
        $etoile   = Etoile::create(['libelle' => '5★']);
        $tp       = TypePerso::create(['libelle_TP' => 'Standard']);
        $element  = Elements::create(['libelle_element' => 'Dendro']);
        $typeArme = TypeArme::create(['libelle_TArme' => 'Arc']);
        $session  = $this->adminSession();

        $response = $this->withSession($session)->post(route('admin.personnages.store'), [
            'nom_perso'   => 'Tighnari',
            'fid_etoile'  => $etoile->id_etoile,
            'fid_TP'      => $tp->id_TP,
            'fid_element' => $element->id_element,
            'fid_TArmes'  => $typeArme->id_TArmes,
        ]);

        $perso = Personnage::where('nom_perso', 'Tighnari')->firstOrFail();

        $response->assertRedirect(route('admin.personnages.edit', $perso));
        $this->assertDatabaseHas('personnage', ['nom_perso' => 'Tighnari']);
    }

    // ──────────────────────────────────────────────
    // Critère 5 : modification personnage retourne 200
    // ──────────────────────────────────────────────
    public function test_admin_personnages_edit_retourne_200(): void
    {
        $etoile   = Etoile::create(['libelle' => '4★']);
        $tp       = TypePerso::create(['libelle_TP' => 'Standard']);
        $element  = Elements::create(['libelle_element' => 'Électro']);
        $typeArme = TypeArme::create(['libelle_TArme' => 'Arc']);
        $perso    = Personnage::create([
            'nom_perso'   => 'Fischl',
            'slug'        => 'fischl',
            'fid_etoile'  => $etoile->id_etoile,
            'fid_TP'      => $tp->id_TP,
            'fid_element' => $element->id_element,
            'fid_TArmes'  => $typeArme->id_TArmes,
        ]);

        $this->withSession($this->adminSession())
            ->get(route('admin.personnages.edit', $perso))
            ->assertStatus(200);
    }

    // ──────────────────────────────────────────────
    // Critère 6 : suppression personnage redirige
    // ──────────────────────────────────────────────
    public function test_admin_personnages_destroy_supprime(): void
    {
        $etoile   = Etoile::create(['libelle' => '4★']);
        $tp       = TypePerso::create(['libelle_TP' => 'Standard']);
        $element  = Elements::create(['libelle_element' => 'Électro']);
        $typeArme = TypeArme::create(['libelle_TArme' => 'Arc']);
        $perso    = Personnage::create([
            'nom_perso'   => 'A supprimer',
            'slug'        => 'a-supprimer',
            'fid_etoile'  => $etoile->id_etoile,
            'fid_TP'      => $tp->id_TP,
            'fid_element' => $element->id_element,
            'fid_TArmes'  => $typeArme->id_TArmes,
        ]);

        $this->withSession($this->adminSession())
            ->delete(route('admin.personnages.destroy', $perso))
            ->assertRedirect(route('admin.personnages.index'));

        $this->assertDatabaseMissing('personnage', ['slug' => 'a-supprimer']);
    }

    // ──────────────────────────────────────────────
    // Critère 7 : liste armes retourne 200
    // ──────────────────────────────────────────────
    public function test_admin_armes_index_retourne_200(): void
    {
        $this->withSession($this->adminSession())
            ->get(route('admin.armes.index'))
            ->assertStatus(200);
    }

    // ──────────────────────────────────────────────
    // Critère 8 : création arme redirige avec succès
    // ──────────────────────────────────────────────
    public function test_admin_armes_store_cree_et_redirige(): void
    {
        $etoile   = Etoile::create(['libelle' => '5★']);
        $typeArme = TypeArme::create(['libelle_TArme' => 'Épée']);
        $session  = $this->adminSession();

        $this->withSession($session)->post(route('admin.armes.store'), [
            'nom_arme'   => 'Haran Geppaku Futsu',
            'fid_etoile' => $etoile->id_etoile,
            'fid_TArmes' => $typeArme->id_TArmes,
        ])->assertRedirect(route('admin.armes.index'));

        $this->assertDatabaseHas('armes', ['nom_arme' => 'Haran Geppaku Futsu']);
    }

    // ──────────────────────────────────────────────
    // Critère 9 : liste ennemis retourne 200
    // ──────────────────────────────────────────────
    public function test_admin_ennemis_index_retourne_200(): void
    {
        $this->withSession($this->adminSession())
            ->get(route('admin.ennemis.index'))
            ->assertStatus(200);
    }

    // ──────────────────────────────────────────────
    // Critère 10 : liste animaux retourne 200
    // ──────────────────────────────────────────────
    public function test_admin_animaux_index_retourne_200(): void
    {
        $this->withSession($this->adminSession())
            ->get(route('admin.animaux.index'))
            ->assertStatus(200);
    }

    // ──────────────────────────────────────────────
    // Critère 11 : liste cuisine retourne 200
    // ──────────────────────────────────────────────
    public function test_admin_cuisine_index_retourne_200(): void
    {
        $this->withSession($this->adminSession())
            ->get(route('admin.cuisine.index'))
            ->assertStatus(200);
    }

    // ──────────────────────────────────────────────
    // Critère 12 : liste régions retourne 200
    // ──────────────────────────────────────────────
    public function test_admin_regions_index_retourne_200(): void
    {
        $this->withSession($this->adminSession())
            ->get(route('admin.regions.index'))
            ->assertStatus(200);
    }

    // ──────────────────────────────────────────────
    // Critère 13 : création région redirige avec succès
    // ──────────────────────────────────────────────
    public function test_admin_regions_store_cree_et_redirige(): void
    {
        $this->withSession($this->adminSession())
            ->post(route('admin.regions.store'), [
                'nom_region'    => 'Mondstadt',
                'descri_region' => 'La cité du vent.',
            ])
            ->assertRedirect(route('admin.regions.index'));

        $this->assertDatabaseHas('région', ['nom_region' => 'Mondstadt']);
    }

    // ──────────────────────────────────────────────
    // Critère 14 : liste chronologie retourne 200
    // ──────────────────────────────────────────────
    public function test_admin_chronologie_index_retourne_200(): void
    {
        $this->withSession($this->adminSession())
            ->get(route('admin.chronologie.index'))
            ->assertStatus(200);
    }

    // ──────────────────────────────────────────────
    // Critère 15 : création chronologie redirige avec succès
    // ──────────────────────────────────────────────
    public function test_admin_chronologie_store_cree_et_redirige(): void
    {
        $this->withSession($this->adminSession())
            ->post(route('admin.chronologie.store'), [
                'titre'  => 'Ère de Kaeya',
                'ordre'  => 1,
                'resume' => 'Résumé test.',
                'periode'=> 'Temps anciens',
            ])
            ->assertRedirect(route('admin.chronologie.index'));

        $this->assertDatabaseHas('chronologie', ['titre' => 'Ère de Kaeya']);
    }

    // ──────────────────────────────────────────────
    // Critère 16 : updateOrdre chronologie redirige
    // ──────────────────────────────────────────────
    public function test_admin_chronologie_update_ordre_redirige(): void
    {
        $chrono = Chronologie::create(['titre' => 'Test', 'ordre' => 1]);

        $this->withSession($this->adminSession())
            ->patch(route('admin.chronologie.ordre', $chrono), ['ordre' => 5])
            ->assertRedirect(route('admin.chronologie.index'));

        $this->assertDatabaseHas('chronologie', ['id_chrono' => $chrono->id_chrono, 'ordre' => 5]);
    }

    // ──────────────────────────────────────────────
    // Critère 17 : liste rôles retourne 200
    // ──────────────────────────────────────────────
    public function test_admin_roles_index_retourne_200(): void
    {
        $this->withSession($this->adminSession())
            ->get(route('admin.roles.index'))
            ->assertStatus(200);
    }

    // ──────────────────────────────────────────────
    // Critère 18 : création rôle redirige avec succès
    // ──────────────────────────────────────────────
    public function test_admin_roles_store_cree_et_redirige(): void
    {
        $this->withSession($this->adminSession())
            ->post(route('admin.roles.store'), [
                'libelle_role' => 'DPS',
                'descri_role'  => 'Personnage de dommages.',
            ])
            ->assertRedirect(route('admin.roles.index'));

        $this->assertDatabaseHas('role', ['libelle_role' => 'DPS']);
    }

    // ──────────────────────────────────────────────
    // Critère 19 : liste utilisateurs retourne 200
    // ──────────────────────────────────────────────
    public function test_admin_utilisateurs_index_retourne_200(): void
    {
        $this->withSession($this->adminSession())
            ->get(route('admin.utilisateurs.index'))
            ->assertStatus(200);
    }

    // ──────────────────────────────────────────────
    // Critère 20 : bannir un utilisateur fonctionne
    // ──────────────────────────────────────────────
    public function test_admin_bannir_utilisateur(): void
    {
        $user = User::create([
            'name'     => 'Joueur Test',
            'email'    => 'joueur@test.fr',
            'password' => Hash::make('password'),
        ]);

        $this->withSession($this->adminSession())
            ->post(route('admin.utilisateurs.bannir', $user))
            ->assertRedirect(route('admin.utilisateurs.index'));

        $this->assertNotNull($user->fresh()->banni_le);
    }

    // ──────────────────────────────────────────────
    // Critère 21 : débannir un utilisateur fonctionne
    // ──────────────────────────────────────────────
    public function test_admin_debannir_utilisateur(): void
    {
        $user = User::create([
            'name'     => 'Joueur Banni',
            'email'    => 'banni@test.fr',
            'password' => Hash::make('password'),
            'banni_le' => now(),
        ]);

        $this->withSession($this->adminSession())
            ->post(route('admin.utilisateurs.debannir', $user))
            ->assertRedirect(route('admin.utilisateurs.index'));

        $this->assertNull($user->fresh()->banni_le);
    }

    // ──────────────────────────────────────────────
    // Critère 22 : accès CRUD sans session redirige vers login
    // ──────────────────────────────────────────────
    public function test_admin_crud_sans_session_redirige(): void
    {
        $this->get(route('admin.personnages.index'))
            ->assertRedirect(route('admin.login'));

        $this->get(route('admin.armes.index'))
            ->assertRedirect(route('admin.login'));

        $this->get(route('admin.ennemis.index'))
            ->assertRedirect(route('admin.login'));

        $this->get(route('admin.utilisateurs.index'))
            ->assertRedirect(route('admin.login'));
    }

    // ──────────────────────────────────────────────
    // Critère 23 : événements index retourne 200
    // ──────────────────────────────────────────────
    public function test_admin_evenements_index_retourne_200(): void
    {
        $this->withSession($this->adminSession())
            ->get(route('admin.evenements.index'))
            ->assertStatus(200);
    }

    // ──────────────────────────────────────────────
    // Critère 24 : validation échoue si nom manquant (personnage)
    // ──────────────────────────────────────────────
    public function test_admin_personnages_store_validation_echoue_sans_nom(): void
    {
        $etoile = Etoile::create(['libelle' => '5★']);
        $tp     = TypePerso::create(['libelle_TP' => 'Standard']);

        $this->withSession($this->adminSession())
            ->post(route('admin.personnages.store'), [
                'nom_perso'  => '',
                'fid_etoile' => $etoile->id_etoile,
                'fid_TP'     => $tp->id_TP,
            ])
            ->assertSessionHasErrors('nom_perso');
    }
}
