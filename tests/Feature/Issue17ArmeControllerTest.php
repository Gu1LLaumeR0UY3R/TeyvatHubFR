<?php

namespace Tests\Feature;

use App\Models\Arme;
use App\Models\ArmStatsNiveau;
use App\Models\ArmStatsRang;
use App\Models\Etoile;
use App\Models\TypeArme;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Issue17ArmeControllerTest extends TestCase
{
    use RefreshDatabase;

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    private function makeTypeArme(): TypeArme
    {
        return TypeArme::create(['libelle_TArme' => 'Épée']);
    }

    private function makeEtoile(): Etoile
    {
        return Etoile::create(['libelle' => '5★']);
    }

    private function makeArme(array $override = []): Arme
    {
        $type   = $this->makeTypeArme();
        $etoile = $this->makeEtoile();
        return Arme::create(array_merge([
            'nom_arme'   => 'Mistsplitter',
            'slug'       => 'mistsplitter',
            'fid_TArmes' => $type->id_TArmes,
            'fid_etoile' => $etoile->id_etoile,
        ], $override));
    }

    // =========================================================================
    // Issue #17 — Modèle Arme
    // =========================================================================

    public function test_arme_slug_genere_automatiquement(): void
    {
        $type   = $this->makeTypeArme();
        $etoile = $this->makeEtoile();
        $arme = Arme::create(['nom_arme' => 'Wolf Gravestone', 'fid_TArmes' => $type->id_TArmes, 'fid_etoile' => $etoile->id_etoile]);
        $this->assertEquals('wolf-gravestone', $arme->slug);
    }

    public function test_arme_route_key_est_slug(): void
    {
        $arme = $this->makeArme();
        $this->assertEquals('slug', $arme->getRouteKeyName());
    }

    public function test_arme_relation_typeArme(): void
    {
        $arme = $this->makeArme();
        $this->assertInstanceOf(TypeArme::class, $arme->typeArme);
    }

    public function test_arme_relation_etoile(): void
    {
        $arme = $this->makeArme();
        $this->assertInstanceOf(Etoile::class, $arme->etoile);
    }

    public function test_arme_stats_niveaux_vide(): void
    {
        $arme = $this->makeArme();
        $this->assertCount(0, $arme->statsNiveaux);
    }

    public function test_arme_stats_rangs_vide(): void
    {
        $arme = $this->makeArme();
        $this->assertCount(0, $arme->statsRangs);
    }

    public function test_arme_stats_niveaux_ordonnes_par_niveau(): void
    {
        $arme = $this->makeArme();
        ArmStatsNiveau::create(['lvl_ASN' => 90, 'main_stat' => 674, 'subs_stats' => 0, 'fid_arme' => $arme->id_arme]);
        ArmStatsNiveau::create(['lvl_ASN' => 1, 'main_stat' => 48, 'subs_stats' => 0, 'fid_arme' => $arme->id_arme]);
        $niveaux = $arme->fresh()->statsNiveaux;
        $this->assertEquals(1, $niveaux->first()->lvl_ASN);
        $this->assertEquals(90, $niveaux->last()->lvl_ASN);
    }

    public function test_arme_stats_rangs_ordonnes_par_rang(): void
    {
        $arme = $this->makeArme();
        ArmStatsRang::create(['rang_ASR' => 5, 'descri_ASR' => 'R5 desc', 'fid_arme' => $arme->id_arme]);
        ArmStatsRang::create(['rang_ASR' => 1, 'descri_ASR' => 'R1 desc', 'fid_arme' => $arme->id_arme]);
        $rangs = $arme->fresh()->statsRangs;
        $this->assertEquals(1, $rangs->first()->rang_ASR);
        $this->assertEquals(5, $rangs->last()->rang_ASR);
    }

    // =========================================================================
    // Issue #18 — ArmeController — liste
    // =========================================================================

    public function test_liste_armes_retourne_200(): void
    {
        $this->get('/armes')->assertStatus(200);
    }

    public function test_liste_armes_accessible_sans_connexion(): void
    {
        $this->get('/armes')->assertStatus(200);
    }

    public function test_liste_armes_affiche_les_armes(): void
    {
        $this->makeArme(['nom_arme' => 'Mistsplitter', 'slug' => 'mistsplitter']);
        $this->get('/armes')->assertSee('Mistsplitter');
    }

    public function test_liste_armes_recherche_filtre(): void
    {
        $type   = $this->makeTypeArme();
        $etoile = $this->makeEtoile();
        Arme::create(['nom_arme' => 'Mistsplitter', 'slug' => 'mistsplitter', 'fid_TArmes' => $type->id_TArmes, 'fid_etoile' => $etoile->id_etoile]);
        Arme::create(['nom_arme' => 'Skyward Blade', 'slug' => 'skyward-blade', 'fid_TArmes' => $type->id_TArmes, 'fid_etoile' => $etoile->id_etoile]);

        $this->get('/armes?search=Mist')
            ->assertSee('Mistsplitter')
            ->assertDontSee('Skyward Blade');
    }

    public function test_liste_armes_filtre_par_type(): void
    {
        $type1  = $this->makeTypeArme();
        $type2  = TypeArme::create(['libelle_TArme' => 'Arc']);
        $etoile = $this->makeEtoile();
        Arme::create(['nom_arme' => 'Mistsplitter', 'slug' => 'mistsplitter', 'fid_TArmes' => $type1->id_TArmes, 'fid_etoile' => $etoile->id_etoile]);
        Arme::create(['nom_arme' => 'Elegy Arc', 'slug' => 'elegy-arc', 'fid_TArmes' => $type2->id_TArmes, 'fid_etoile' => $etoile->id_etoile]);

        $this->get('/armes?type=' . $type1->id_TArmes)
            ->assertSee('Mistsplitter')
            ->assertDontSee('Elegy Arc');
    }

    public function test_liste_armes_conserve_filtres_pagination(): void
    {
        $this->get('/armes?search=test&sort=rarete_desc&page=1')->assertStatus(200);
    }

    // =========================================================================
    // Issue #19 — Vue liste armes
    // =========================================================================

    public function test_vue_liste_armes_aucune_arme(): void
    {
        $this->get('/armes')->assertSee('Aucune arme trouvée');
    }

    public function test_vue_liste_affiche_barre_recherche(): void
    {
        $this->get('/armes')->assertSee('search');
    }

    public function test_vue_liste_affiche_switch_vue(): void
    {
        $this->get('/armes')->assertSee('view=grid')->assertSee('view=list');
    }

    // =========================================================================
    // Issue #20 — Vue détail arme avec Alpine.js
    // =========================================================================

    public function test_detail_arme_retourne_200(): void
    {
        $this->makeArme(['nom_arme' => 'Mistsplitter', 'slug' => 'mistsplitter']);
        $this->get('/armes/mistsplitter')->assertStatus(200);
    }

    public function test_detail_arme_affiche_nom(): void
    {
        $this->makeArme(['nom_arme' => 'Wolf Grave', 'slug' => 'wolf-grave']);
        $this->get('/armes/wolf-grave')->assertSee('Wolf Grave');
    }

    public function test_detail_arme_slug_inexistant_retourne_404(): void
    {
        $this->get('/armes/slug-inexistant')->assertStatus(404);
    }

    public function test_detail_arme_acces_par_id_retourne_404(): void
    {
        $this->makeArme();
        $this->get('/armes/1')->assertStatus(404);
    }

    public function test_detail_arme_affiche_stats_niveaux(): void
    {
        $arme = $this->makeArme(['nom_arme' => 'Test Arme', 'slug' => 'test-arme']);
        ArmStatsNiveau::create(['lvl_ASN' => 1, 'main_stat' => 48, 'subs_stats' => 0, 'fid_arme' => $arme->id_arme]);
        $this->get('/armes/test-arme')->assertSee('48');
    }

    public function test_detail_arme_affiche_rangs_competence(): void
    {
        $arme = $this->makeArme(['nom_arme' => 'Test Arme2', 'slug' => 'test-arme2', 'nom_competence' => 'Tranchant Vif']);
        ArmStatsRang::create(['rang_ASR' => 1, 'descri_ASR' => 'Description R1', 'fid_arme' => $arme->id_arme]);
        $this->get('/armes/test-arme2')->assertSee('Tranchant Vif')->assertSee('Description R1');
    }

    public function test_detail_arme_bouton_retour_vers_liste(): void
    {
        $this->makeArme(['nom_arme' => 'Claymore', 'slug' => 'claymore']);
        $this->get('/armes/claymore')->assertSee(route('armes.index'));
    }
}
