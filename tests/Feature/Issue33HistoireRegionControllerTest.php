<?php

namespace Tests\Feature;

use App\Models\Chronologie;
use App\Models\Region;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Issue33HistoireRegionControllerTest extends TestCase
{
    use RefreshDatabase;

    private function makeRegion(array $attrs = []): Region
    {
        return Region::create(array_merge([
            'nom_region'    => 'Mondstadt',
            'descri_region' => 'Nation de la liberté.',
        ], $attrs));
    }

    // Critère 1 : page histoire retourne 200
    public function test_page_histoire_retourne_200(): void
    {
        $this->get(route('histoire.index'))->assertStatus(200);
    }

    // Critère 2 : page histoire affiche les régions
    public function test_page_histoire_affiche_les_regions(): void
    {
        $this->makeRegion(['nom_region' => 'Liyue']);
        $this->get(route('histoire.index'))->assertSee('Liyue');
    }

    // Critère 3 : page histoire affiche la chronologie
    public function test_page_histoire_affiche_la_chronologie(): void
    {
        $region = $this->makeRegion();
        Chronologie::create([
            'titre'      => 'Première guerre archonte',
            'resume'     => 'Les archontes se battent.',
            'ordre'      => 1,
            'fid_region' => $region->id_region,
        ]);
        $this->get(route('histoire.index'))->assertSee('Première guerre archonte');
    }

    // Critère 4 : liste régions retourne 200
    public function test_liste_regions_retourne_200(): void
    {
        $this->get(route('regions.index'))->assertStatus(200);
    }

    // Critère 5 : liste régions affiche les régions
    public function test_liste_regions_affiche_les_regions(): void
    {
        $this->makeRegion(['nom_region' => 'Inazuma']);
        $this->get(route('regions.index'))->assertSee('Inazuma');
    }

    // Critère 6 : détail région par slug retourne 200
    public function test_detail_region_par_slug_retourne_200(): void
    {
        $region = $this->makeRegion();
        $this->get(route('regions.show', $region->slug))->assertStatus(200);
    }

    // Critère 7 : détail région affiche le nom
    public function test_detail_region_affiche_le_nom(): void
    {
        $region = $this->makeRegion(['nom_region' => 'Sumeru']);
        $this->get(route('regions.show', $region->slug))->assertSee('Sumeru');
    }

    // Critère 8 : slug inexistant retourne 404
    public function test_slug_inexistant_retourne_404(): void
    {
        $this->get(route('regions.show', 'introuvable'))->assertStatus(404);
    }

    // Critère 9 : accès par id retourne 404
    public function test_acces_par_id_retourne_404(): void
    {
        $region = $this->makeRegion();
        $this->get('/histoire/regions/' . $region->id_region)->assertStatus(404);
    }

    // Critère 10 : pages accessibles sans connexion
    public function test_pages_accessibles_sans_connexion(): void
    {
        $this->get(route('histoire.index'))->assertStatus(200);
        $this->get(route('regions.index'))->assertStatus(200);
    }

    // Critère 11 : slug généré automatiquement
    public function test_slug_genere_automatiquement(): void
    {
        $region = $this->makeRegion(['nom_region' => 'Fontaine']);
        $this->assertEquals('fontaine', $region->slug);
    }
}
