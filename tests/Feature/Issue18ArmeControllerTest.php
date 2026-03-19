<?php

namespace Tests\Feature;

use App\Models\Arme;
use App\Models\Etoile;
use App\Models\TypeArme;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Issue #18 — ArmeController (index + show)
 */
class Issue18ArmeControllerTest extends TestCase
{
    use RefreshDatabase;

    private function makeArme(array $attrs = []): Arme
    {
        $type = TypeArme::firstOrCreate(['libelle_TArme' => 'Épée']);
        $etoile = Etoile::firstOrCreate(['libelle' => '4★']);
        return Arme::create(array_merge([
            'nom_arme'  => 'Épée du vent',
            'slug'      => 'epee-du-vent',
            'fid_TArmes' => $type->id_TArmes,
            'fid_etoile' => $etoile->id_etoile,
        ], $attrs));
    }

    public function test_liste_armes_retourne_200(): void
    {
        $this->get('/armes')->assertStatus(200);
    }

    public function test_liste_affiche_nom_arme(): void
    {
        $this->makeArme();
        $this->get('/armes')->assertSee('Épée du vent');
    }

    public function test_detail_par_slug_retourne_200(): void
    {
        $this->makeArme();
        $this->get('/armes/epee-du-vent')->assertStatus(200);
    }

    public function test_detail_affiche_nom(): void
    {
        $this->makeArme();
        $this->get('/armes/epee-du-vent')->assertSee('Épée du vent');
    }

    public function test_slug_inexistant_retourne_404(): void
    {
        $this->get('/armes/inexistant')->assertStatus(404);
    }

    public function test_acces_par_id_retourne_404(): void
    {
        $this->makeArme();
        $this->get('/armes/1')->assertStatus(404);
    }

    public function test_recherche_filtre_par_nom(): void
    {
        $type = TypeArme::firstOrCreate(['libelle_TArme' => 'Épée']);
        $etoile = Etoile::firstOrCreate(['libelle' => '4★']);
        Arme::create(['nom_arme' => 'Épée du vent', 'slug' => 'epee-du-vent', 'fid_TArmes' => $type->id_TArmes, 'fid_etoile' => $etoile->id_etoile]);
        Arme::create(['nom_arme' => 'Arc des étoiles', 'slug' => 'arc-des-etoiles', 'fid_TArmes' => $type->id_TArmes, 'fid_etoile' => $etoile->id_etoile]);

        $this->get('/armes?search=Épée')->assertSee('Épée du vent')->assertDontSee('Arc des étoiles');
    }

    public function test_filtre_par_type(): void
    {
        $epee = TypeArme::firstOrCreate(['libelle_TArme' => 'Épée']);
        $arc = TypeArme::firstOrCreate(['libelle_TArme' => 'Arc']);
        $etoile = Etoile::firstOrCreate(['libelle' => '4★']);
        Arme::create(['nom_arme' => 'Épée du vent', 'slug' => 'epee-du-vent', 'fid_TArmes' => $epee->id_TArmes, 'fid_etoile' => $etoile->id_etoile]);
        Arme::create(['nom_arme' => 'Arc des étoiles', 'slug' => 'arc-des-etoiles', 'fid_TArmes' => $arc->id_TArmes, 'fid_etoile' => $etoile->id_etoile]);

        $this->get('/armes?type=' . $epee->id_TArmes)
            ->assertSee('Épée du vent')
            ->assertDontSee('Arc des étoiles');
    }

    public function test_liste_accessible_sans_connexion(): void
    {
        $this->get('/armes')->assertStatus(200);
    }

    public function test_liste_vide_retourne_200(): void
    {
        $this->get('/armes')->assertStatus(200);
    }

    public function test_slug_genere_automatiquement(): void
    {
        $type = TypeArme::firstOrCreate(['libelle_TArme' => 'Épée']);
        $etoile = Etoile::firstOrCreate(['libelle' => '4★']);
        $arme = Arme::create(['nom_arme' => 'Lame d\'argent', 'fid_TArmes' => $type->id_TArmes, 'fid_etoile' => $etoile->id_etoile]);
        $this->assertEquals('lame-dargent', $arme->slug);
    }
}
