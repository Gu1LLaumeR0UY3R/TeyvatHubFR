<?php

namespace Tests\Feature;

use App\Models\Elements;
use App\Models\Ennemi;
use App\Models\Materiaux;
use App\Models\Rarete;
use App\Models\TypeEnnemi;
use App\Models\TypeMateriaux;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Issue12EnnemiMateriauxTest extends TestCase
{
    use RefreshDatabase;

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    private function makeTypeEnnemi(): TypeEnnemi
    {
        return TypeEnnemi::create(['libelle_Type' => 'Élite']);
    }

    private function makeElements(): Elements
    {
        return Elements::create(['libelle_element' => 'Pyro']);
    }

    private function makeTypeMateriaux(): TypeMateriaux
    {
        return TypeMateriaux::create(['libelle_TypeM' => 'Gemme']);
    }

    private function makeRarete(): Rarete
    {
        return Rarete::create(['libelle_rareté' => '3★']);
    }

    private function makeEnnemi(array $override = []): Ennemi
    {
        $type = $this->makeTypeEnnemi();
        return Ennemi::create(array_merge([
            'nom_ennemi'   => 'Hilichurl',
            'slug'         => 'hilichurl',
            'fid_typeEnne' => $type->id_typeEnnemi,
        ], $override));
    }

    private function makeMateriaux(array $override = []): Materiaux
    {
        $type   = $this->makeTypeMateriaux();
        $rarete = $this->makeRarete();
        return Materiaux::create(array_merge([
            'nom_mat'   => 'Fragment de cristal',
            'slug'      => 'fragment-de-cristal',
            'fid_typeM' => $type->id_typeM,
            'fid_rareté' => $rarete->{'id_rareté'},
        ], $override));
    }

    // =========================================================================
    // Issue #12 — Modèle Ennemi
    // =========================================================================

    public function test_ennemi_slug_genere_automatiquement(): void
    {
        $type = $this->makeTypeEnnemi();
        $ennemi = Ennemi::create(['nom_ennemi' => 'Ruin Guard', 'fid_typeEnne' => $type->id_typeEnnemi]);
        $this->assertEquals('ruin-guard', $ennemi->slug);
    }

    public function test_ennemi_route_key_est_slug(): void
    {
        $ennemi = $this->makeEnnemi();
        $this->assertEquals('slug', $ennemi->getRouteKeyName());
    }

    public function test_ennemi_relation_typeEnnemi(): void
    {
        $ennemi = $this->makeEnnemi();
        $this->assertInstanceOf(TypeEnnemi::class, $ennemi->typeEnnemi);
    }

    public function test_ennemi_relation_element_nullable(): void
    {
        $ennemi = $this->makeEnnemi(['fid_element' => null]);
        $this->assertNull($ennemi->element);
    }

    public function test_ennemi_relation_element_avec_valeur(): void
    {
        $el = $this->makeElements();
        $ennemi = $this->makeEnnemi(['fid_element' => $el->id_element]);
        $this->assertInstanceOf(Elements::class, $ennemi->element);
    }

    // =========================================================================
    // Issue #13 — EnnemiController — liste
    // =========================================================================

    public function test_liste_ennemis_retourne_200(): void
    {
        $this->get('/ennemis')->assertStatus(200);
    }

    public function test_liste_ennemis_accessible_sans_connexion(): void
    {
        $this->get('/ennemis')->assertStatus(200);
    }

    public function test_liste_ennemis_affiche_les_ennemis(): void
    {
        $this->makeEnnemi(['nom_ennemi' => 'Fatui Agent', 'slug' => 'fatui-agent']);
        $this->get('/ennemis')->assertSee('Fatui Agent');
    }

    public function test_liste_ennemis_recherche_filtre(): void
    {
        $this->makeEnnemi(['nom_ennemi' => 'Hilichurl', 'slug' => 'hilichurl']);

        $type2 = TypeEnnemi::create(['libelle_Type' => 'Boss']);
        Ennemi::create(['nom_ennemi' => 'Dvalin', 'slug' => 'dvalin', 'fid_typeEnne' => $type2->id_typeEnnemi]);

        $this->get('/ennemis?search=Hili')
            ->assertSee('Hilichurl')
            ->assertDontSee('Dvalin');
    }

    public function test_liste_ennemis_filtre_par_type(): void
    {
        $type1 = TypeEnnemi::create(['libelle_Type' => 'Commun']);
        $type2 = TypeEnnemi::create(['libelle_Type' => 'Boss']);
        Ennemi::create(['nom_ennemi' => 'Hilichurl', 'slug' => 'hilichurl', 'fid_typeEnne' => $type1->id_typeEnnemi]);
        Ennemi::create(['nom_ennemi' => 'Dvalin', 'slug' => 'dvalin', 'fid_typeEnne' => $type2->id_typeEnnemi]);

        $this->get('/ennemis?type=' . $type1->id_typeEnnemi)
            ->assertSee('Hilichurl')
            ->assertDontSee('Dvalin');
    }

    public function test_liste_ennemis_conserve_filtres_pagination(): void
    {
        $this->get('/ennemis?search=test&sort=nom_desc&page=1')
            ->assertStatus(200);
    }

    // =========================================================================
    // Issue #14 — Vue liste ennemis
    // =========================================================================

    public function test_vue_liste_ennemis_affiche_neutre_si_element_null(): void
    {
        $this->makeEnnemi(['nom_ennemi' => 'NoElement', 'slug' => 'no-element', 'fid_element' => null]);
        $this->get('/ennemis')->assertSee('Neutre');
    }

    public function test_vue_liste_ennemis_liste_vide(): void
    {
        $this->get('/ennemis')->assertStatus(200);
    }

    // =========================================================================
    // Issue #15 — Vue détail ennemi
    // =========================================================================

    public function test_detail_ennemi_retourne_200(): void
    {
        $this->makeEnnemi(['nom_ennemi' => 'Hilichurl', 'slug' => 'hilichurl']);
        $this->get('/ennemis/hilichurl')->assertStatus(200);
    }

    public function test_detail_ennemi_affiche_nom(): void
    {
        $this->makeEnnemi(['nom_ennemi' => 'Ruin Grader', 'slug' => 'ruin-grader']);
        $this->get('/ennemis/ruin-grader')->assertSee('Ruin Grader');
    }

    public function test_detail_ennemi_slug_inexistant_retourne_404(): void
    {
        $this->get('/ennemis/slug-inexistant')->assertStatus(404);
    }

    public function test_detail_ennemi_acces_par_id_retourne_404(): void
    {
        $this->makeEnnemi();
        $this->get('/ennemis/1')->assertStatus(404);
    }

    public function test_detail_ennemi_affiche_aucune_region_connue(): void
    {
        $this->makeEnnemi(['nom_ennemi' => 'SansRegion', 'slug' => 'sans-region']);
        $this->get('/ennemis/sans-region')->assertSee('Aucune région connue');
    }

    public function test_detail_ennemi_affiche_aucun_materiau_connu(): void
    {
        $this->makeEnnemi(['nom_ennemi' => 'SansRegion', 'slug' => 'sans-region2']);
        $this->get('/ennemis/sans-region2')->assertSee('Aucun matériau connu');
    }

    // =========================================================================
    // Issue #16 — MateriauxController + vues
    // =========================================================================

    public function test_liste_materiaux_retourne_200(): void
    {
        $this->get('/materiaux')->assertStatus(200);
    }

    public function test_liste_materiaux_accessible_sans_connexion(): void
    {
        $this->get('/materiaux')->assertStatus(200);
    }

    public function test_liste_materiaux_affiche_les_materiaux(): void
    {
        $this->makeMateriaux(['nom_mat' => 'Fragment de shiver', 'slug' => 'fragment-shiver']);
        $this->get('/materiaux')->assertSee('Fragment de shiver');
    }

    public function test_liste_materiaux_recherche_filtre(): void
    {
        $type = $this->makeTypeMateriaux();
        $rarete = $this->makeRarete();
        Materiaux::create(['nom_mat' => 'Gemme Pyro', 'slug' => 'gemme-pyro', 'fid_typeM' => $type->id_typeM, 'fid_rareté' => $rarete->{'id_rareté'}]);

        $type2 = TypeMateriaux::create(['libelle_TypeM' => 'OS']);
        Materiaux::create(['nom_mat' => 'Os de dragon', 'slug' => 'os-dragon', 'fid_typeM' => $type2->id_typeM, 'fid_rareté' => $rarete->{'id_rareté'}]);

        $this->get('/materiaux?search=Gemme')
            ->assertSee('Gemme Pyro')
            ->assertDontSee('Os de dragon');
    }

    public function test_liste_materiaux_filtre_par_type(): void
    {
        $type1 = $this->makeTypeMateriaux();
        $type2 = TypeMateriaux::create(['libelle_TypeM' => 'OS']);
        $rarete = $this->makeRarete();
        Materiaux::create(['nom_mat' => 'Gemme Pyro', 'slug' => 'gemme-pyro', 'fid_typeM' => $type1->id_typeM, 'fid_rareté' => $rarete->{'id_rareté'}]);
        Materiaux::create(['nom_mat' => 'Os de dragon', 'slug' => 'os-dragon', 'fid_typeM' => $type2->id_typeM, 'fid_rareté' => $rarete->{'id_rareté'}]);

        $this->get('/materiaux?type=' . $type1->id_typeM)
            ->assertSee('Gemme Pyro')
            ->assertDontSee('Os de dragon');
    }

    public function test_detail_materiaux_retourne_200(): void
    {
        $this->makeMateriaux(['nom_mat' => 'Fragment de cristal', 'slug' => 'fragment-cristal-1']);
        $this->get('/materiaux/fragment-cristal-1')->assertStatus(200);
    }

    public function test_detail_materiaux_affiche_nom(): void
    {
        $this->makeMateriaux(['nom_mat' => 'Fragment de cristal', 'slug' => 'fragment-cristal-2']);
        $this->get('/materiaux/fragment-cristal-2')->assertSee('Fragment de cristal');
    }

    public function test_detail_materiaux_slug_inexistant_retourne_404(): void
    {
        $this->get('/materiaux/slug-inexistant')->assertStatus(404);
    }

    public function test_detail_materiaux_acces_par_id_retourne_404(): void
    {
        $this->makeMateriaux();
        $this->get('/materiaux/1')->assertStatus(404);
    }

    public function test_detail_materiaux_affiche_aucun_ennemi_source(): void
    {
        $this->makeMateriaux(['nom_mat' => 'SansEnnemi', 'slug' => 'sans-ennemi']);
        $this->get('/materiaux/sans-ennemi')->assertSee('Aucun ennemi source connu');
    }

    public function test_materiaux_slug_genere_automatiquement(): void
    {
        $type   = $this->makeTypeMateriaux();
        $rarete = $this->makeRarete();
        $mat = Materiaux::create([
            'nom_mat'    => 'Éclat de cristal',
            'fid_typeM'  => $type->id_typeM,
            'fid_rareté' => $rarete->{'id_rareté'},
        ]);
        $this->assertEquals('eclat-de-cristal', $mat->slug);
    }

    public function test_materiaux_route_key_est_slug(): void
    {
        $mat = $this->makeMateriaux();
        $this->assertEquals('slug', $mat->getRouteKeyName());
    }

    public function test_materiaux_relation_typeMateriaux(): void
    {
        $mat = $this->makeMateriaux();
        $this->assertInstanceOf(TypeMateriaux::class, $mat->typeMateriaux);
    }

    public function test_materiaux_relation_rarete(): void
    {
        $mat = $this->makeMateriaux();
        $this->assertInstanceOf(Rarete::class, $mat->rarete);
    }

    public function test_liste_materiaux_conserve_filtres_pagination(): void
    {
        $this->get('/materiaux?search=test&page=1')->assertStatus(200);
    }
}
