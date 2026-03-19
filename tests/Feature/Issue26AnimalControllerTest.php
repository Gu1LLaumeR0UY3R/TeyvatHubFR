<?php

namespace Tests\Feature;

use App\Models\Animal;
use App\Models\TypeAnimal;
use App\Models\Region;
use App\Models\Ingredient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Issue26AnimalControllerTest extends TestCase
{
    use RefreshDatabase;

    private function makeAnimal(array $attrs = []): Animal
    {
        $type = TypeAnimal::firstOrCreate(['libelle_TAnimal' => 'Oiseau']);
        return Animal::create(array_merge([
            'nom_animal'   => 'Pigeon',
            'descri_animal'=> 'Un oiseau commun.',
            'fid_TAnimal'  => $type->id_TAnimal,
        ], $attrs));
    }

    // Critère 1 : liste retourne 200
    public function test_liste_retourne_200(): void
    {
        $this->get(route('animaux.index'))->assertStatus(200);
    }

    // Critère 2 : la liste affiche les noms des animaux
    public function test_liste_affiche_les_animaux(): void
    {
        $this->makeAnimal(['nom_animal' => 'Renard']);
        $this->get(route('animaux.index'))->assertSee('Renard');
    }

    // Critère 3 : page de détail retourne 200
    public function test_detail_retourne_200(): void
    {
        $animal = $this->makeAnimal();
        $this->get(route('animaux.show', $animal->slug))->assertStatus(200);
    }

    // Critère 4 : la page de détail affiche le nom
    public function test_detail_affiche_le_nom(): void
    {
        $animal = $this->makeAnimal(['nom_animal' => 'Lynx blanc']);
        $this->get(route('animaux.show', $animal->slug))->assertSee('Lynx blanc');
    }

    // Critère 5 : slug inexistant retourne 404
    public function test_slug_inexistant_retourne_404(): void
    {
        $this->get(route('animaux.show', 'introuvable'))->assertStatus(404);
    }

    // Critère 6 : accès par id retourne 404
    public function test_acces_par_id_retourne_404(): void
    {
        $animal = $this->makeAnimal();
        $this->get('/animaux/' . $animal->id_animal)->assertStatus(404);
    }

    // Critère 7 : filtre search fonctionne
    public function test_filtre_search_fonctionne(): void
    {
        $this->makeAnimal(['nom_animal' => 'Renard roux']);
        $type = TypeAnimal::firstOrCreate(['libelle_TAnimal' => 'Félin']);
        Animal::create(['nom_animal' => 'Chat sauvage', 'fid_TAnimal' => $type->id_TAnimal]);

        $this->get(route('animaux.index', ['search' => 'Renard']))
             ->assertSee('Renard roux')
             ->assertDontSee('Chat sauvage');
    }

    // Critère 8 : filtre type fonctionne
    public function test_filtre_type_fonctionne(): void
    {
        $type1 = TypeAnimal::firstOrCreate(['libelle_TAnimal' => 'Oiseau']);
        $type2 = TypeAnimal::firstOrCreate(['libelle_TAnimal' => 'Reptile']);
        Animal::create(['nom_animal' => 'Aigle', 'fid_TAnimal' => $type1->id_TAnimal]);
        Animal::create(['nom_animal' => 'Gecko', 'fid_TAnimal' => $type2->id_TAnimal]);

        $this->get(route('animaux.index', ['type' => $type1->id_TAnimal]))
             ->assertSee('Aigle')
             ->assertDontSee('Gecko');
    }

    // Critère 9 : liste accessible sans connexion
    public function test_liste_accessible_sans_connexion(): void
    {
        $this->get(route('animaux.index'))->assertStatus(200);
    }

    // Critère 10 : liste vide ne génère pas d'erreur
    public function test_liste_vide_ne_genere_pas_d_erreur(): void
    {
        $this->get(route('animaux.index'))->assertStatus(200);
    }

    // Critère 11 : slug généré automatiquement
    public function test_slug_genere_automatiquement(): void
    {
        $animal = $this->makeAnimal(['nom_animal' => 'Aigle royal']);
        $this->assertEquals('aigle-royal', $animal->slug);
    }
}
