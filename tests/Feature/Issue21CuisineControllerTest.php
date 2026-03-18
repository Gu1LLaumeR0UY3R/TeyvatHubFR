<?php

namespace Tests\Feature;

use App\Models\Ingredient;
use App\Models\Plat;
use App\Models\Rarete;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Issue21CuisineControllerTest extends TestCase
{
    use RefreshDatabase;

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    private function makeRarete(): Rarete
    {
        return Rarete::create(['libelle_rareté' => '2★']);
    }

    private function makePlat(array $override = []): Plat
    {
        $rarete = $this->makeRarete();
        return Plat::create(array_merge([
            'nom_plat'   => 'Poulet grillé',
            'slug'       => 'poulet-grille',
            'fid_rareté' => $rarete->{'id_rareté'},
        ], $override));
    }

    // =========================================================================
    // Issue #21 — Modèle Plat + Ingredient
    // =========================================================================

    public function test_plat_slug_genere_automatiquement(): void
    {
        $rarete = $this->makeRarete();
        $plat = Plat::create(['nom_plat' => 'Riz sauté épicé', 'fid_rareté' => $rarete->{'id_rareté'}]);
        $this->assertEquals('riz-saute-epice', $plat->slug);
    }

    public function test_plat_route_key_est_slug(): void
    {
        $plat = $this->makePlat();
        $this->assertEquals('slug', $plat->getRouteKeyName());
    }

    public function test_plat_relation_rarete(): void
    {
        $plat = $this->makePlat();
        $this->assertInstanceOf(Rarete::class, $plat->rarete);
    }

    public function test_plat_ingredients_vide(): void
    {
        $plat = $this->makePlat();
        $this->assertCount(0, $plat->ingredients);
    }

    public function test_plat_ingredients_relation(): void
    {
        $plat  = $this->makePlat();
        $ingre = Ingredient::create(['nom_ingre' => 'Poulet', 'slug' => 'poulet']);
        $plat->ingredients()->attach($ingre->id_ingredient, ['quantite' => 2]);
        $this->assertCount(1, $plat->fresh()->ingredients);
        $this->assertEquals(2, $plat->fresh()->ingredients->first()->pivot->quantite);
    }

    public function test_ingredient_slug_genere_automatiquement(): void
    {
        $ingre = Ingredient::create(['nom_ingre' => 'Carotte sucrée']);
        $this->assertEquals('carotte-sucree', $ingre->slug);
    }

    public function test_ingredient_route_key_est_slug(): void
    {
        $ingre = Ingredient::create(['nom_ingre' => 'Fraise', 'slug' => 'fraise']);
        $this->assertEquals('slug', $ingre->getRouteKeyName());
    }

    // =========================================================================
    // Issue #22 — PlatController — liste
    // =========================================================================

    public function test_liste_plats_retourne_200(): void
    {
        $this->get('/cuisine')->assertStatus(200);
    }

    public function test_liste_plats_accessible_sans_connexion(): void
    {
        $this->get('/cuisine')->assertStatus(200);
    }

    public function test_liste_plats_affiche_les_plats(): void
    {
        $this->makePlat(['nom_plat' => 'Poulet grillé', 'slug' => 'poulet-grille2']);
        $this->get('/cuisine')->assertSee('Poulet grillé');
    }

    public function test_liste_plats_recherche_filtre(): void
    {
        $rarete = $this->makeRarete();
        Plat::create(['nom_plat' => 'Poulet grillé', 'slug' => 'poulet-grille', 'fid_rareté' => $rarete->{'id_rareté'}]);
        Plat::create(['nom_plat' => 'Champignons frits', 'slug' => 'champignons-frits', 'fid_rareté' => $rarete->{'id_rareté'}]);

        $this->get('/cuisine?search=Poulet')
            ->assertSee('Poulet grillé')
            ->assertDontSee('Champignons frits');
    }

    public function test_liste_plats_filtre_par_rarete(): void
    {
        $r1 = $this->makeRarete();
        $r2 = Rarete::create(['libelle_rareté' => '3★']);
        Plat::create(['nom_plat' => 'Plat commun', 'slug' => 'plat-commun', 'fid_rareté' => $r1->{'id_rareté'}]);
        Plat::create(['nom_plat' => 'Plat rare', 'slug' => 'plat-rare', 'fid_rareté' => $r2->{'id_rareté'}]);

        $this->get('/cuisine?rarete=' . $r1->{'id_rareté'})
            ->assertSee('Plat commun')
            ->assertDontSee('Plat rare');
    }

    public function test_liste_plats_conserve_filtres_pagination(): void
    {
        $this->get('/cuisine?search=test&sort=rarete_desc&page=1')->assertStatus(200);
    }

    // =========================================================================
    // Issue #23 — Vue liste cuisine
    // =========================================================================

    public function test_vue_liste_aucun_plat(): void
    {
        $this->get('/cuisine')->assertSee('Aucun plat trouvé');
    }

    public function test_vue_liste_affiche_switch_vue(): void
    {
        $this->get('/cuisine')->assertSee('view=grid')->assertSee('view=list');
    }

    // =========================================================================
    // Issue #24 — Vue détail plat
    // =========================================================================

    public function test_detail_plat_retourne_200(): void
    {
        $this->makePlat(['nom_plat' => 'Poulet grillé', 'slug' => 'poulet-grille']);
        $this->get('/cuisine/poulet-grille')->assertStatus(200);
    }

    public function test_detail_plat_affiche_nom(): void
    {
        $this->makePlat(['nom_plat' => 'Donut miel', 'slug' => 'donut-miel']);
        $this->get('/cuisine/donut-miel')->assertSee('Donut miel');
    }

    public function test_detail_plat_slug_inexistant_retourne_404(): void
    {
        $this->get('/cuisine/slug-inexistant')->assertStatus(404);
    }

    public function test_detail_plat_acces_par_id_retourne_404(): void
    {
        $this->makePlat();
        $this->get('/cuisine/1')->assertStatus(404);
    }

    public function test_detail_plat_affiche_aucun_ingredient(): void
    {
        $this->makePlat(['nom_plat' => 'Plat simple', 'slug' => 'plat-simple']);
        $this->get('/cuisine/plat-simple')->assertSee('Aucun ingrédient connu');
    }

    public function test_detail_plat_affiche_ingredients(): void
    {
        $plat  = $this->makePlat(['nom_plat' => 'Plat complet', 'slug' => 'plat-complet']);
        $ingre = Ingredient::create(['nom_ingre' => 'Carotte', 'slug' => 'carotte']);
        $plat->ingredients()->attach($ingre->id_ingredient, ['quantite' => 3]);
        $this->get('/cuisine/plat-complet')
            ->assertSee('Carotte')
            ->assertSee('3');
    }

    public function test_detail_plat_bouton_retour_vers_liste(): void
    {
        $this->makePlat(['nom_plat' => 'Poulet', 'slug' => 'poulet']);
        $this->get('/cuisine/poulet')->assertSee(route('cuisine.index'));
    }
}
