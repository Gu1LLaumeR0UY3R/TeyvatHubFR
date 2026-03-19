<?php

namespace Tests\Feature;

use App\Models\Materiaux;
use App\Models\TypeMateriaux;
use App\Models\Rarete;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Issue #16 — MateriauxController (index + show)
 */
class Issue16MateriauxControllerTest extends TestCase
{
    use RefreshDatabase;

    private function makeMat(array $attrs = []): Materiaux
    {
        $type = TypeMateriaux::firstOrCreate(['libelle_TypeM' => 'Boss']);
        $rarete = Rarete::firstOrCreate(['libelle_rareté' => '3★']);
        return Materiaux::create(array_merge([
            'nom_mat'      => 'Gemme de Pyro',
            'slug'         => 'gemme-de-pyro',
            'fid_typeM'    => $type->id_typeM,
            'fid_rareté'   => $rarete->{'id_rareté'},
        ], $attrs));
    }

    public function test_liste_materiaux_retourne_200(): void
    {
        $this->get('/materiaux')->assertStatus(200);
    }

    public function test_liste_affiche_nom_materiau(): void
    {
        $this->makeMat();
        $this->get('/materiaux')->assertSee('Gemme de Pyro');
    }

    public function test_detail_par_slug_retourne_200(): void
    {
        $this->makeMat();
        $this->get('/materiaux/gemme-de-pyro')->assertStatus(200);
    }

    public function test_detail_affiche_nom(): void
    {
        $this->makeMat();
        $this->get('/materiaux/gemme-de-pyro')->assertSee('Gemme de Pyro');
    }

    public function test_slug_inexistant_retourne_404(): void
    {
        $this->get('/materiaux/inexistant')->assertStatus(404);
    }

    public function test_acces_par_id_retourne_404(): void
    {
        $this->makeMat();
        $this->get('/materiaux/1')->assertStatus(404);
    }

    public function test_recherche_filtre_par_nom(): void
    {
        $type = TypeMateriaux::firstOrCreate(['libelle_TypeM' => 'Boss']);
        $rarete = Rarete::firstOrCreate(['libelle_rareté' => '3★']);
        Materiaux::create(['nom_mat' => 'Gemme de Pyro', 'slug' => 'gemme-de-pyro', 'fid_typeM' => $type->id_typeM, 'fid_rareté' => $rarete->{'id_rareté'}]);
        Materiaux::create(['nom_mat' => 'Cristal de glace', 'slug' => 'cristal-de-glace', 'fid_typeM' => $type->id_typeM, 'fid_rareté' => $rarete->{'id_rareté'}]);

        $this->get('/materiaux?search=Gemme')->assertSee('Gemme de Pyro')->assertDontSee('Cristal de glace');
    }

    public function test_filtre_par_type(): void
    {
        $type1 = TypeMateriaux::firstOrCreate(['libelle_TypeM' => 'Boss']);
        $type2 = TypeMateriaux::firstOrCreate(['libelle_TypeM' => 'Élite']);
        $rarete = Rarete::firstOrCreate(['libelle_rareté' => '3★']);
        Materiaux::create(['nom_mat' => 'Gemme de Pyro', 'slug' => 'gemme-de-pyro', 'fid_typeM' => $type1->id_typeM, 'fid_rareté' => $rarete->{'id_rareté'}]);
        Materiaux::create(['nom_mat' => 'Masque de Fatui', 'slug' => 'masque-de-fatui', 'fid_typeM' => $type2->id_typeM, 'fid_rareté' => $rarete->{'id_rareté'}]);

        $this->get('/materiaux?type=' . $type1->id_typeM)
            ->assertSee('Gemme de Pyro')
            ->assertDontSee('Masque de Fatui');
    }

    public function test_liste_accessible_sans_connexion(): void
    {
        $this->get('/materiaux')->assertStatus(200);
    }

    public function test_liste_vide_retourne_200(): void
    {
        $this->get('/materiaux')->assertStatus(200);
    }

    public function test_slug_genere_automatiquement(): void
    {
        $type = TypeMateriaux::firstOrCreate(['libelle_TypeM' => 'Boss']);
        $rarete = Rarete::firstOrCreate(['libelle_rareté' => '3★']);
        $mat = Materiaux::create([
            'nom_mat'    => 'Cristal brillant',
            'fid_typeM'  => $type->id_typeM,
            'fid_rareté' => $rarete->{'id_rareté'},
        ]);
        $this->assertEquals('cristal-brillant', $mat->slug);
    }
}
