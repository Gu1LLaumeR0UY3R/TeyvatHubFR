<?php

namespace Tests\Feature;

use App\Models\Ennemi;
use App\Models\Elements;
use App\Models\TypeEnnemi;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Issue #13 — EnnemiController (index + show)
 */
class Issue13EnnemiControllerTest extends TestCase
{
    use RefreshDatabase;

    private function makeEnnemi(array $attrs = []): Ennemi
    {
        $type = TypeEnnemi::firstOrCreate(['libelle_Type' => 'Élite']);
        return Ennemi::create(array_merge([
            'nom_ennemi'  => 'Fatui Agent',
            'slug'        => 'fatui-agent',
            'fid_typeEnne' => $type->id_typeEnnemi,
        ], $attrs));
    }

    public function test_liste_ennemis_retourne_200(): void
    {
        $this->get('/ennemis')->assertStatus(200);
    }

    public function test_liste_affiche_nom_ennemi(): void
    {
        $this->makeEnnemi();
        $this->get('/ennemis')->assertSee('Fatui Agent');
    }

    public function test_detail_par_slug_retourne_200(): void
    {
        $this->makeEnnemi();
        $this->get('/ennemis/fatui-agent')->assertStatus(200);
    }

    public function test_detail_affiche_nom(): void
    {
        $this->makeEnnemi();
        $this->get('/ennemis/fatui-agent')->assertSee('Fatui Agent');
    }

    public function test_slug_inexistant_retourne_404(): void
    {
        $this->get('/ennemis/inexistant-slug')->assertStatus(404);
    }

    public function test_acces_par_id_retourne_404(): void
    {
        $this->makeEnnemi();
        $this->get('/ennemis/1')->assertStatus(404);
    }

    public function test_recherche_filtre_par_nom(): void
    {
        $type = TypeEnnemi::firstOrCreate(['libelle_Type' => 'Élite']);
        Ennemi::create(['nom_ennemi' => 'Fatui Agent', 'slug' => 'fatui-agent', 'fid_typeEnne' => $type->id_typeEnnemi]);
        Ennemi::create(['nom_ennemi' => 'Slime Pyro', 'slug' => 'slime-pyro', 'fid_typeEnne' => $type->id_typeEnnemi]);

        $this->get('/ennemis?search=Fatui')->assertSee('Fatui Agent')->assertDontSee('Slime Pyro');
    }

    public function test_filtre_par_type(): void
    {
        $type1 = TypeEnnemi::firstOrCreate(['libelle_Type' => 'Élite']);
        $type2 = TypeEnnemi::firstOrCreate(['libelle_Type' => 'Commun']);
        Ennemi::create(['nom_ennemi' => 'Fatui Agent', 'slug' => 'fatui-agent', 'fid_typeEnne' => $type1->id_typeEnnemi]);
        Ennemi::create(['nom_ennemi' => 'Slime Pyro', 'slug' => 'slime-pyro', 'fid_typeEnne' => $type2->id_typeEnnemi]);

        $this->get('/ennemis?type=' . $type1->id_typeEnnemi)
            ->assertSee('Fatui Agent')
            ->assertDontSee('Slime Pyro');
    }

    public function test_element_null_affiche_neutre(): void
    {
        $this->makeEnnemi(['fid_element' => null]);
        $response = $this->get('/ennemis');
        $response->assertStatus(200);
    }

    public function test_liste_accessible_sans_connexion(): void
    {
        $this->get('/ennemis')->assertStatus(200);
    }

    public function test_liste_vide_retourne_200(): void
    {
        $this->get('/ennemis')->assertStatus(200);
    }

    public function test_pagination_conserve_filtres(): void
    {
        $this->get('/ennemis?search=slime&page=1')->assertStatus(200);
    }
}
