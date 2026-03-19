<?php

namespace Tests\Feature;

use App\Models\Plat;
use App\Models\Rarete;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Issue #22 — PlatController (cuisine index + show)
 */
class Issue22PlatControllerTest extends TestCase
{
    use RefreshDatabase;

    private function makePlat(array $attrs = []): Plat
    {
        $rarete = Rarete::firstOrCreate(['libelle_rareté' => '2★']);
        return Plat::create(array_merge([
            'nom_plat'  => 'Poulet grillé',
            'slug'      => 'poulet-grille',
            'fid_rareté' => $rarete->{'id_rareté'},
        ], $attrs));
    }

    public function test_liste_cuisine_retourne_200(): void
    {
        $this->get('/cuisine')->assertStatus(200);
    }

    public function test_liste_affiche_nom_plat(): void
    {
        $this->makePlat();
        $this->get('/cuisine')->assertSee('Poulet grillé');
    }

    public function test_detail_par_slug_retourne_200(): void
    {
        $this->makePlat();
        $this->get('/cuisine/poulet-grille')->assertStatus(200);
    }

    public function test_detail_affiche_nom(): void
    {
        $this->makePlat();
        $this->get('/cuisine/poulet-grille')->assertSee('Poulet grillé');
    }

    public function test_slug_inexistant_retourne_404(): void
    {
        $this->get('/cuisine/inexistant')->assertStatus(404);
    }

    public function test_acces_par_id_retourne_404(): void
    {
        $this->makePlat();
        $this->get('/cuisine/1')->assertStatus(404);
    }

    public function test_recherche_filtre_par_nom(): void
    {
        $rarete = Rarete::firstOrCreate(['libelle_rareté' => '2★']);
        Plat::create(['nom_plat' => 'Poulet grillé', 'slug' => 'poulet-grille', 'fid_rareté' => $rarete->{'id_rareté'}]);
        Plat::create(['nom_plat' => 'Soupe de légumes', 'slug' => 'soupe-de-legumes', 'fid_rareté' => $rarete->{'id_rareté'}]);

        $this->get('/cuisine?search=Poulet')->assertSee('Poulet grillé')->assertDontSee('Soupe de légumes');
    }

    public function test_filtre_par_rarete(): void
    {
        $r2 = Rarete::firstOrCreate(['libelle_rareté' => '2★']);
        $r3 = Rarete::firstOrCreate(['libelle_rareté' => '3★']);
        Plat::create(['nom_plat' => 'Poulet grillé', 'slug' => 'poulet-grille', 'fid_rareté' => $r2->{'id_rareté'}]);
        Plat::create(['nom_plat' => 'Poisson doré', 'slug' => 'poisson-dore', 'fid_rareté' => $r3->{'id_rareté'}]);

        $this->get('/cuisine?rarete=' . $r2->{'id_rareté'})
            ->assertSee('Poulet grillé')
            ->assertDontSee('Poisson doré');
    }

    public function test_liste_accessible_sans_connexion(): void
    {
        $this->get('/cuisine')->assertStatus(200);
    }

    public function test_liste_vide_retourne_200(): void
    {
        $this->get('/cuisine')->assertStatus(200);
    }

    public function test_slug_genere_automatiquement(): void
    {
        $rarete = Rarete::firstOrCreate(['libelle_rareté' => '2★']);
        $plat = Plat::create(['nom_plat' => 'Riz au cari', 'fid_rareté' => $rarete->{'id_rareté'}]);
        $this->assertEquals('riz-au-cari', $plat->slug);
    }
}
