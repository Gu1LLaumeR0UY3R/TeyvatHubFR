<?php

namespace Tests\Feature;

use App\Models\Ingredient;
use App\Models\Animal;
use App\Models\TypeAnimal;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Issue30IngredientControllerTest extends TestCase
{
    use RefreshDatabase;

    private function makeIngredient(array $attrs = []): Ingredient
    {
        return Ingredient::create(array_merge([
            'nom_ingre' => 'Pomme',
        ], $attrs));
    }

    // Critère 1 : liste retourne 200
    public function test_liste_retourne_200(): void
    {
        $this->get(route('ingredients.index'))->assertStatus(200);
    }

    // Critère 2 : la liste affiche les ingrédients
    public function test_liste_affiche_les_ingredients(): void
    {
        $this->makeIngredient(['nom_ingre' => 'Radis floral']);
        $this->get(route('ingredients.index'))->assertSee('Radis floral');
    }

    // Critère 3 : page de détail retourne 200
    public function test_detail_retourne_200(): void
    {
        $i = $this->makeIngredient();
        $this->get(route('ingredients.show', $i->slug))->assertStatus(200);
    }

    // Critère 4 : page de détail affiche le nom
    public function test_detail_affiche_le_nom(): void
    {
        $i = $this->makeIngredient(['nom_ingre' => 'Œuf de perdrix']);
        $this->get(route('ingredients.show', $i->slug))->assertSee('Œuf de perdrix');
    }

    // Critère 5 : slug inexistant retourne 404
    public function test_slug_inexistant_retourne_404(): void
    {
        $this->get(route('ingredients.show', 'introuvable'))->assertStatus(404);
    }

    // Critère 6 : accès par id retourne 404
    public function test_acces_par_id_retourne_404(): void
    {
        $i = $this->makeIngredient();
        $this->get('/ingredients/' . $i->id_ingredient)->assertStatus(404);
    }

    // Critère 7 : filtre search fonctionne
    public function test_filtre_search_fonctionne(): void
    {
        $this->makeIngredient(['nom_ingre' => 'Carotte dorée']);
        $this->makeIngredient(['nom_ingre' => 'Pierre de sel']);

        $this->get(route('ingredients.index', ['search' => 'Carotte']))
             ->assertSee('Carotte dorée')
             ->assertDontSee('Pierre de sel');
    }

    // Critère 8 : liste accessible sans connexion
    public function test_liste_accessible_sans_connexion(): void
    {
        $this->get(route('ingredients.index'))->assertStatus(200);
    }

    // Critère 9 : liste vide ne génère pas d'erreur
    public function test_liste_vide_ne_genere_pas_d_erreur(): void
    {
        $this->get(route('ingredients.index'))->assertStatus(200);
    }

    // Critère 10 : slug généré automatiquement
    public function test_slug_genere_automatiquement(): void
    {
        $i = $this->makeIngredient(['nom_ingre' => 'Herbe aromatique']);
        $this->assertEquals('herbe-aromatique', $i->slug);
    }
}
