<?php

namespace Tests\Feature;

use App\Models\Ingredient;
use App\Models\Plat;
use App\Models\Rarete;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Issue30IngredientControllerTest extends TestCase
{
    use RefreshDatabase;

    private function makeIngredient(array $override = []): Ingredient
    {
        return Ingredient::create(array_merge([
            'nom_ingre' => 'Carotte',
            'slug'      => 'carotte',
        ], $override));
    }

    public function test_liste_ingredients_retourne_200(): void
    {
        $this->get('/ingredients')->assertStatus(200);
    }

    public function test_liste_ingredients_accessible_sans_connexion(): void
    {
        $this->get('/ingredients')->assertStatus(200);
    }

    public function test_liste_ingredients_affiche_tous(): void
    {
        $this->makeIngredient(['nom_ingre' => 'Carotte', 'slug' => 'carotte']);
        $this->get('/ingredients')->assertSee('Carotte');
    }

    public function test_liste_ingredients_recherche(): void
    {
        Ingredient::create(['nom_ingre' => 'Carotte', 'slug' => 'carotte']);
        Ingredient::create(['nom_ingre' => 'Pomme', 'slug' => 'pomme']);

        $this->get('/ingredients?search=Carotte')
            ->assertSee('Carotte')
            ->assertDontSee('Pomme');
    }

    public function test_liste_ingredients_aucun_ingredient(): void
    {
        $this->get('/ingredients')->assertSee('Aucun ingrédient trouvé');
    }

    public function test_detail_ingredient_retourne_200(): void
    {
        $this->makeIngredient(['nom_ingre' => 'Carotte', 'slug' => 'carotte']);
        $this->get('/ingredients/carotte')->assertStatus(200);
    }

    public function test_detail_ingredient_affiche_nom(): void
    {
        $this->makeIngredient(['nom_ingre' => 'Fraise rouge', 'slug' => 'fraise-rouge']);
        $this->get('/ingredients/fraise-rouge')->assertSee('Fraise rouge');
    }

    public function test_detail_ingredient_slug_inexistant_retourne_404(): void
    {
        $this->get('/ingredients/slug-inexistant')->assertStatus(404);
    }

    public function test_detail_ingredient_acces_par_id_retourne_404(): void
    {
        $this->makeIngredient();
        $this->get('/ingredients/1')->assertStatus(404);
    }

    public function test_detail_ingredient_affiche_aucun_plat(): void
    {
        $this->makeIngredient(['nom_ingre' => 'Sel', 'slug' => 'sel']);
        $this->get('/ingredients/sel')->assertSee('Aucun plat connu');
    }

    public function test_detail_ingredient_affiche_plats_associes(): void
    {
        $ingre  = $this->makeIngredient(['nom_ingre' => 'Oeuf', 'slug' => 'oeuf']);
        $rarete = Rarete::create(['libelle_rareté' => '1★']);
        $plat   = Plat::create(['nom_plat' => 'Omelette', 'slug' => 'omelette', 'fid_rareté' => $rarete->{'id_rareté'}]);
        $plat->ingredients()->attach($ingre->id_ingredient, ['quantite' => 2]);
        $this->get('/ingredients/oeuf')->assertSee('Omelette');
    }

    public function test_detail_ingredient_bouton_retour(): void
    {
        $this->makeIngredient(['nom_ingre' => 'Miel', 'slug' => 'miel']);
        $this->get('/ingredients/miel')->assertSee(route('ingredients.index'));
    }
}
