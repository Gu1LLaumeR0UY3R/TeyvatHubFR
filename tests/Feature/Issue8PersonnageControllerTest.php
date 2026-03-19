<?php

namespace Tests\Feature;

use App\Models\Elements;
use App\Models\Etoile;
use App\Models\Personnage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Issue #8 — PersonnageController (index + show)
 */
class Issue8PersonnageControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_liste_personnages_retourne_200(): void
    {
        $this->get('/personnages')->assertStatus(200);
    }

    public function test_liste_affiche_nom_personnage(): void
    {
        Personnage::factory()->create(['nom_perso' => 'Hu Tao']);
        $this->get('/personnages')->assertSee('Hu Tao');
    }

    public function test_detail_par_slug_retourne_200(): void
    {
        Personnage::factory()->create(['nom_perso' => 'Hu Tao', 'slug' => 'hu-tao']);
        $this->get('/personnages/hu-tao')->assertStatus(200);
    }

    public function test_detail_affiche_nom_personnage(): void
    {
        Personnage::factory()->create(['nom_perso' => 'Hu Tao', 'slug' => 'hu-tao']);
        $this->get('/personnages/hu-tao')->assertSee('Hu Tao');
    }

    public function test_slug_inexistant_retourne_404(): void
    {
        $this->get('/personnages/inexistant-slug')->assertStatus(404);
    }

    public function test_acces_par_id_retourne_404(): void
    {
        Personnage::factory()->create();
        $this->get('/personnages/1')->assertStatus(404);
    }

    public function test_recherche_filtre_par_nom(): void
    {
        Personnage::factory()->create(['nom_perso' => 'Hu Tao']);
        Personnage::factory()->create(['nom_perso' => 'Ayaka']);
        $this->get('/personnages?search=Hu')->assertSee('Hu Tao')->assertDontSee('Ayaka');
    }

    public function test_filtre_par_element(): void
    {
        $pyro = Elements::firstOrCreate(['libelle_element' => 'Pyro']);
        $cryo = Elements::firstOrCreate(['libelle_element' => 'Cryo']);
        $etoile = Etoile::firstOrCreate(['libelle' => '4★']);
        $tp = \App\Models\TypePerso::firstOrCreate(['libelle_TP' => 'Personnage jouable']);
        $ta = \App\Models\TypeArme::firstOrCreate(['libelle_TArme' => 'Épée']);

        Personnage::factory()->create(['nom_perso' => 'Hu Tao', 'fid_element' => $pyro->id_element]);
        Personnage::factory()->create(['nom_perso' => 'Ayaka', 'fid_element' => $cryo->id_element]);

        $this->get('/personnages?element=' . $pyro->id_element)
            ->assertSee('Hu Tao')
            ->assertDontSee('Ayaka');
    }

    public function test_pagination_conserve_les_filtres(): void
    {
        $this->get('/personnages?search=hu&page=1')->assertStatus(200);
    }

    public function test_liste_accessible_sans_connexion(): void
    {
        $this->get('/personnages')->assertStatus(200);
    }

    public function test_detail_accessible_sans_connexion(): void
    {
        Personnage::factory()->create(['nom_perso' => 'Klee', 'slug' => 'klee']);
        $this->get('/personnages/klee')->assertStatus(200);
    }

    public function test_tri_par_nom_par_defaut(): void
    {
        Personnage::factory()->create(['nom_perso' => 'Zhongli']);
        Personnage::factory()->create(['nom_perso' => 'Albedo']);
        $response = $this->get('/personnages');
        $response->assertStatus(200);
        $content = $response->getContent();
        $this->assertLessThan(strpos($content, 'Zhongli'), strpos($content, 'Albedo'));
    }

    public function test_liste_vide_retourne_200(): void
    {
        $this->get('/personnages')->assertStatus(200);
    }
}
