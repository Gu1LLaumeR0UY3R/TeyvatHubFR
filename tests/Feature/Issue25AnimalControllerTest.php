<?php

namespace Tests\Feature;

use App\Models\Animal;
use App\Models\Ingredient;
use App\Models\TypeAnimal;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Issue25AnimalControllerTest extends TestCase
{
    use RefreshDatabase;

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    private function makeTypeAnimal(): TypeAnimal
    {
        return TypeAnimal::create(['libelle_TAnimal' => 'Oiseau']);
    }

    private function makeAnimal(array $override = []): Animal
    {
        $type = $this->makeTypeAnimal();
        return Animal::create(array_merge([
            'nom_animal'  => 'Pigeon vert',
            'slug'        => 'pigeon-vert',
            'fid_TAnimal' => $type->id_TAnimal,
        ], $override));
    }

    // =========================================================================
    // Issue #25 — Modèle Animal
    // =========================================================================

    public function test_animal_slug_genere_automatiquement(): void
    {
        $type = $this->makeTypeAnimal();
        $animal = Animal::create(['nom_animal' => 'Lapin blanc', 'fid_TAnimal' => $type->id_TAnimal]);
        $this->assertEquals('lapin-blanc', $animal->slug);
    }

    public function test_animal_route_key_est_slug(): void
    {
        $animal = $this->makeAnimal();
        $this->assertEquals('slug', $animal->getRouteKeyName());
    }

    public function test_animal_relation_typeAnimal(): void
    {
        $animal = $this->makeAnimal();
        $this->assertInstanceOf(TypeAnimal::class, $animal->typeAnimal);
    }

    public function test_animal_regions_vide(): void
    {
        $animal = $this->makeAnimal();
        $this->assertCount(0, $animal->regions);
    }

    public function test_animal_ingredients_vide(): void
    {
        $animal = $this->makeAnimal();
        $this->assertCount(0, $animal->ingredients);
    }

    public function test_animal_ingredients_relation(): void
    {
        $animal = $this->makeAnimal();
        $ingre  = Ingredient::create(['nom_ingre' => 'Plume', 'slug' => 'plume']);
        $animal->ingredients()->attach($ingre->id_ingredient);
        $this->assertCount(1, $animal->fresh()->ingredients);
    }

    // =========================================================================
    // Issue #26 — AnimalController — liste
    // =========================================================================

    public function test_liste_animaux_retourne_200(): void
    {
        $this->get('/animaux')->assertStatus(200);
    }

    public function test_liste_animaux_accessible_sans_connexion(): void
    {
        $this->get('/animaux')->assertStatus(200);
    }

    public function test_liste_animaux_affiche_les_animaux(): void
    {
        $this->makeAnimal(['nom_animal' => 'Pigeon vert', 'slug' => 'pigeon-vert']);
        $this->get('/animaux')->assertSee('Pigeon vert');
    }

    public function test_liste_animaux_recherche_filtre(): void
    {
        $type = $this->makeTypeAnimal();
        Animal::create(['nom_animal' => 'Pigeon vert', 'slug' => 'pigeon-vert', 'fid_TAnimal' => $type->id_TAnimal]);
        Animal::create(['nom_animal' => 'Lapin blanc', 'slug' => 'lapin-blanc', 'fid_TAnimal' => $type->id_TAnimal]);

        $this->get('/animaux?search=Pigeon')
            ->assertSee('Pigeon vert')
            ->assertDontSee('Lapin blanc');
    }

    public function test_liste_animaux_filtre_par_type(): void
    {
        $type1 = $this->makeTypeAnimal();
        $type2 = TypeAnimal::create(['libelle_TAnimal' => 'Reptile']);
        Animal::create(['nom_animal' => 'Pigeon vert', 'slug' => 'pigeon-vert', 'fid_TAnimal' => $type1->id_TAnimal]);
        Animal::create(['nom_animal' => 'Lézard rouge', 'slug' => 'lezard-rouge', 'fid_TAnimal' => $type2->id_TAnimal]);

        $this->get('/animaux?type=' . $type1->id_TAnimal)
            ->assertSee('Pigeon vert')
            ->assertDontSee('Lézard rouge');
    }

    public function test_liste_animaux_conserve_filtres_pagination(): void
    {
        $this->get('/animaux?search=test&sort=type_asc&page=1')->assertStatus(200);
    }

    // =========================================================================
    // Issue #27/#28 — Vue liste animaux
    // =========================================================================

    public function test_vue_liste_aucun_animal(): void
    {
        $this->get('/animaux')->assertSee('Aucun animal trouvé');
    }

    public function test_vue_liste_affiche_switch_vue(): void
    {
        $this->get('/animaux')->assertSee('view=grid')->assertSee('view=list');
    }

    // =========================================================================
    // Issue #29 — Vue détail animal
    // =========================================================================

    public function test_detail_animal_retourne_200(): void
    {
        $this->makeAnimal(['nom_animal' => 'Pigeon vert', 'slug' => 'pigeon-vert']);
        $this->get('/animaux/pigeon-vert')->assertStatus(200);
    }

    public function test_detail_animal_affiche_nom(): void
    {
        $this->makeAnimal(['nom_animal' => 'Aiglon doré', 'slug' => 'aiglon-dore']);
        $this->get('/animaux/aiglon-dore')->assertSee('Aiglon doré');
    }

    public function test_detail_animal_slug_inexistant_retourne_404(): void
    {
        $this->get('/animaux/slug-inexistant')->assertStatus(404);
    }

    public function test_detail_animal_acces_par_id_retourne_404(): void
    {
        $this->makeAnimal();
        $this->get('/animaux/1')->assertStatus(404);
    }

    public function test_detail_animal_affiche_aucune_region(): void
    {
        $this->makeAnimal(['nom_animal' => 'Pigeon vert', 'slug' => 'pigeon-vert2']);
        $this->get('/animaux/pigeon-vert2')->assertSee('Aucune région connue');
    }

    public function test_detail_animal_affiche_aucun_ingredient(): void
    {
        $this->makeAnimal(['nom_animal' => 'Lapin', 'slug' => 'lapin']);
        $this->get('/animaux/lapin')->assertSee('Aucun ingrédient connu');
    }

    public function test_detail_animal_bouton_retour(): void
    {
        $this->makeAnimal(['nom_animal' => 'Renard', 'slug' => 'renard']);
        $this->get('/animaux/renard')->assertSee(route('animaux.index'));
    }
}
