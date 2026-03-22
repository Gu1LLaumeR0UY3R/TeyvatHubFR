<?php

namespace Tests\Feature;

use App\Models\Chronologie;
use App\Models\Nation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Issue33HistoireNationControllerTest extends TestCase
{
    use RefreshDatabase;

    private function makeNation(array $attrs = []): Nation
    {
        return Nation::create(array_merge([
            'nom_region'    => 'Mondstadt',
            'descri_region' => 'Nation de la liberté.',
        ], $attrs));
    }

    // Critère 1 : page histoire retourne 200
    public function test_page_histoire_retourne_200(): void
    {
        $this->get(route('histoire.index'))->assertStatus(200);
    }

    // Critère 2 : page histoire affiche les nations
    public function test_page_histoire_affiche_les_nations(): void
    {
        $this->makeNation(['nom_region' => 'Liyue']);
        $this->get(route('histoire.index'))->assertSee('Liyue');
    }

    // Critère 3 : page histoire affiche la chronologie
    public function test_page_histoire_affiche_la_chronologie(): void
    {
        $nation = $this->makeNation();
        Chronologie::create([
            'titre'      => 'Première guerre archonte',
            'resume'     => 'Les archontes se battent.',
            'ordre'      => 1,
            'fid_region' => $nation->id_region,
        ]);
        $this->get(route('histoire.index'))->assertSee('Première guerre archonte');
    }

    // Critère 4 : liste nations retourne 200
    public function test_liste_nations_retourne_200(): void
    {
        $this->get(route('nations.index'))->assertStatus(200);
    }

    // Critère 5 : liste nations affiche les nations
    public function test_liste_nations_affiche_les_nations(): void
    {
        $this->makeNation(['nom_region' => 'Inazuma']);
        $this->get(route('nations.index'))->assertSee('Inazuma');
    }

    // Critère 6 : détail nation par slug retourne 200
    public function test_detail_nation_par_slug_retourne_200(): void
    {
        $nation = $this->makeNation();
        $this->get(route('nations.show', $nation->slug))->assertStatus(200);
    }

    // Critère 7 : détail nation affiche le nom
    public function test_detail_nation_affiche_le_nom(): void
    {
        $nation = $this->makeNation(['nom_region' => 'Sumeru']);
        $this->get(route('nations.show', $nation->slug))->assertSee('Sumeru');
    }

    // Critère 8 : slug inexistant retourne 404
    public function test_slug_inexistant_retourne_404(): void
    {
        $this->get(route('nations.show', 'introuvable'))->assertStatus(404);
    }

    // Critère 9 : accès par id retourne 404 (en suivant la redirection)
    public function test_acces_par_id_retourne_404(): void
    {
        $nation = $this->makeNation();
        $this->followingRedirects()
             ->get('/histoire/nations/' . $nation->id_region)
             ->assertStatus(404);
    }

    // Critère 10 : pages accessibles sans connexion
    public function test_pages_accessibles_sans_connexion(): void
    {
        $this->get(route('histoire.index'))->assertStatus(200);
        $this->get(route('nations.index'))->assertStatus(200);
    }

    // Critère 11 : slug généré automatiquement
    public function test_slug_genere_automatiquement(): void
    {
        $nation = $this->makeNation(['nom_region' => 'Fontaine']);
        $this->assertEquals('fontaine', $nation->slug);
    }
}
