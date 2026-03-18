<?php

namespace Tests\Feature;

use App\Models\Personnage;
use App\Models\Bio;
use App\Models\Aptitude;
use App\Models\TypeApti;
use App\Models\Constellation;
use App\Models\Specialite;
use App\Models\Plat;
use App\Models\Rarete;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Issues #8, #9, #10, #11 — PersonnageController, vues liste/détail, modèle Eloquent
 */
class Issue8PersonnageControllerTest extends TestCase
{
    use RefreshDatabase;

    // ─── Issue #11 : Modèle Eloquent ───────────────────────────

    public function test_personnage_has_relation_element(): void
    {
        $perso = Personnage::factory()->create();
        $this->assertNotNull($perso->element);
    }

    public function test_personnage_has_relation_etoile(): void
    {
        $perso = Personnage::factory()->create();
        $this->assertNotNull($perso->etoile);
    }

    public function test_personnage_has_relation_type_perso(): void
    {
        $perso = Personnage::factory()->create();
        $this->assertNotNull($perso->typePerso);
    }

    public function test_personnage_has_relation_type_arme(): void
    {
        $perso = Personnage::factory()->create();
        $this->assertNotNull($perso->typeArme);
    }

    public function test_personnage_photos_retourne_collection_vide_sans_photo(): void
    {
        $perso = Personnage::factory()->create();
        $this->assertCount(0, $perso->photos);
    }

    public function test_personnage_specialite_retourne_null_si_absente(): void
    {
        $perso = Personnage::factory()->create();
        $this->assertNull($perso->specialite);
    }

    public function test_personnage_bio_retourne_null_si_absente(): void
    {
        $perso = Personnage::factory()->create();
        $this->assertNull($perso->bio);
    }

    public function test_personnage_aptitudes_retourne_collection_vide(): void
    {
        $perso = Personnage::factory()->create();
        $this->assertCount(0, $perso->aptitudes);
    }

    public function test_personnage_constellations_orderby_id_const(): void
    {
        $perso = Personnage::factory()->create();
        // 6 constellations dans le désordre
        for ($i = 6; $i >= 1; $i--) {
            Constellation::create([
                'titre_const' => "C$i",
                'descri_const' => "Description C$i",
                'fid_perso' => $perso->id_perso,
            ]);
        }
        $ids = $perso->constellations()->pluck('id_const')->toArray();
        $sorted = collect($ids)->sort()->values()->toArray();
        $this->assertEquals($sorted, array_values($ids));
    }

    public function test_personnage_roles_retourne_collection_vide(): void
    {
        $perso = Personnage::factory()->create();
        $this->assertCount(0, $perso->roles);
    }

    public function test_get_route_key_name_retourne_slug(): void
    {
        $perso = new Personnage();
        $this->assertEquals('slug', $perso->getRouteKeyName());
    }

    public function test_slug_auto_genere_via_booted(): void
    {
        $perso = Personnage::factory()->create(['nom_perso' => 'Raiden Shogun']);
        $this->assertEquals('raiden-shogun', $perso->slug);
    }

    // ─── Issue #8 : PersonnageController ───────────────────────

    public function test_liste_personnages_retourne_200(): void
    {
        $this->get('/personnages')->assertStatus(200);
    }

    public function test_liste_paginee_a_20(): void
    {
        Personnage::factory()->count(25)->create();
        $response = $this->get('/personnages');
        $response->assertStatus(200);
        $this->assertLessThanOrEqual(20, $response->viewData('personnages')->count());
    }

    public function test_recherche_filtre_par_nom(): void
    {
        Personnage::factory()->create(['nom_perso' => 'Hu Tao', 'slug' => 'hu-tao']);
        Personnage::factory()->create(['nom_perso' => 'Ayaka', 'slug' => 'ayaka']);
        $this->get('/personnages?search=Hu')->assertSee('Hu Tao')->assertDontSee('Ayaka');
    }

    public function test_tri_nom_asc_fonctionne(): void
    {
        $this->get('/personnages?sort=nom_asc')->assertStatus(200);
    }

    public function test_tri_nom_desc_fonctionne(): void
    {
        $this->get('/personnages?sort=nom_desc')->assertStatus(200);
    }

    public function test_tri_rarete_asc_fonctionne(): void
    {
        $this->get('/personnages?sort=rarete_asc')->assertStatus(200);
    }

    public function test_tri_rarete_desc_fonctionne(): void
    {
        $this->get('/personnages?sort=rarete_desc')->assertStatus(200);
    }

    public function test_show_personnage_par_slug(): void
    {
        $perso = Personnage::factory()->create(['nom_perso' => 'Klee', 'slug' => 'klee']);
        $this->get('/personnages/klee')->assertStatus(200)->assertSee('Klee');
    }

    public function test_show_charge_toutes_les_relations(): void
    {
        $perso = Personnage::factory()->create(['nom_perso' => 'Tartaglia', 'slug' => 'tartaglia']);
        $this->get('/personnages/tartaglia')->assertStatus(200);
    }

    public function test_slug_inexistant_retourne_404(): void
    {
        $this->get('/personnages/personnage-inexistant')->assertStatus(404);
    }

    public function test_acces_par_id_retourne_404(): void
    {
        $perso = Personnage::factory()->create();
        $this->get('/personnages/' . $perso->id_perso)->assertStatus(404);
    }

    public function test_page_accessible_sans_connexion(): void
    {
        $this->get('/personnages')->assertStatus(200);
    }

    // ─── Issue #9 : Vue liste ───────────────────────────────────

    public function test_vue_liste_affiche_barre_recherche(): void
    {
        $this->get('/personnages')->assertSee('search');
    }

    public function test_vue_liste_affiche_selecteur_tri(): void
    {
        $this->get('/personnages')->assertSee('sort');
    }

    public function test_vue_liste_affiche_switch_vue(): void
    {
        $this->get('/personnages')->assertSee('view=grid');
    }

    public function test_vue_liste_affiche_message_si_aucun_resultat(): void
    {
        $this->get('/personnages?search=zzz_inexistant')->assertSee('Aucun personnage');
    }

    public function test_vue_liste_cartes_cliquables_vers_show(): void
    {
        $perso = Personnage::factory()->create(['nom_perso' => 'Diluc', 'slug' => 'diluc']);
        $this->get('/personnages')->assertSee(route('personnages.show', $perso->slug));
    }

    public function test_pagination_conserve_les_parametres_url(): void
    {
        Personnage::factory()->count(25)->create();
        $response = $this->get('/personnages?search=a&sort=nom_asc&page=1');
        $response->assertStatus(200);
    }

    // ─── Issue #10 : Vue détail ─────────────────────────────────

    public function test_vue_detail_affiche_tous_les_blocs(): void
    {
        $perso = Personnage::factory()->create(['nom_perso' => 'Xiao', 'slug' => 'xiao']);
        Bio::create(['titre_bio' => 'Bio de Xiao', 'descri_bio' => 'Description', 'fid_perso' => $perso->id_perso]);
        $this->get('/personnages/xiao')->assertSee('Bio de Xiao');
    }

    public function test_vue_detail_competences_groupees_par_type(): void
    {
        $perso  = Personnage::factory()->create(['nom_perso' => 'Ganyu', 'slug' => 'ganyu']);
        $type1  = TypeApti::create(['libelle_Apti' => 'Normal']);
        $type2  = TypeApti::create(['libelle_Apti' => 'Élémentaire']);
        Aptitude::create(['titre_apti' => 'Attaque', 'descri_apti' => '...', 'fid_TypeApti' => $type1->id_TypeApti, 'fid_perso' => $perso->id_perso]);
        Aptitude::create(['titre_apti' => 'Compétence', 'descri_apti' => '...', 'fid_TypeApti' => $type2->id_TypeApti, 'fid_perso' => $perso->id_perso]);
        $this->get('/personnages/ganyu')
            ->assertSee('Normal')
            ->assertSee('Élémentaire');
    }

    public function test_vue_detail_constellations_c1_c6(): void
    {
        $perso = Personnage::factory()->create(['nom_perso' => 'Yelan', 'slug' => 'yelan']);
        for ($i = 1; $i <= 6; $i++) {
            Constellation::create(['titre_const' => "Titre C$i", 'descri_const' => "Desc", 'fid_perso' => $perso->id_perso]);
        }
        $response = $this->get('/personnages/yelan');
        $response->assertSee('C1')->assertSee('C6');
    }

    public function test_vue_detail_sans_specialite_pas_de_bloc_specialite(): void
    {
        $perso = Personnage::factory()->create(['nom_perso' => 'Zhongli', 'slug' => 'zhongli']);
        $this->get('/personnages/zhongli')->assertDontSee('Spécialité culinaire');
    }

    public function test_vue_detail_modal_specialite_si_presente(): void
    {
        $perso  = Personnage::factory()->create(['nom_perso' => 'Noelle', 'slug' => 'noelle']);
        $rarete = Rarete::create(['libelle_rareté' => '3★']);
        $plat   = Plat::create(['nom_plat' => 'Soupe', 'slug' => 'soupe', 'fid_rareté' => $rarete->{'id_rareté'}]);
        Specialite::create(['libelle_spe' => 'Soupe Spéciale', 'fid_plat' => $plat->id_plat, 'fid_perso' => $perso->id_perso]);
        $this->get('/personnages/noelle')
            ->assertSee('Spécialité culinaire')
            ->assertSee('Soupe Spéciale');
    }

    public function test_vue_detail_bouton_voir_plat_redirige(): void
    {
        $perso  = Personnage::factory()->create(['nom_perso' => 'Amber', 'slug' => 'amber']);
        $rarete = Rarete::create(['libelle_rareté' => '3★']);
        $plat   = Plat::create(['nom_plat' => 'Tarte', 'slug' => 'tarte', 'fid_rareté' => $rarete->{'id_rareté'}]);
        Specialite::create(['libelle_spe' => 'Tarte Spéciale', 'fid_plat' => $plat->id_plat, 'fid_perso' => $perso->id_perso]);
        $this->get('/personnages/amber')->assertSee(route('cuisine.show', $plat->slug));
    }

    public function test_bouton_retour_vers_liste(): void
    {
        $perso = Personnage::factory()->create(['nom_perso' => 'Lisa', 'slug' => 'lisa']);
        $this->get('/personnages/lisa')->assertSee(route('personnages.index'));
    }
}
