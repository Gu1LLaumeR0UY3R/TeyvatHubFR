<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Elements;
use App\Models\Etoile;
use App\Models\Personnage;
use App\Models\TypeArme;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class Issue71BulkUpdateTest extends TestCase
{
    use RefreshDatabase;

    private function makeAdmin(): Admin
    {
        return Admin::create([
            'pseudo_admin' => 'AdminTest',
            'email_admin' => 'admin@test.fr',
            'mot_de_passe_admin' => Hash::make('secret123'),
            'role' => 'admin',
        ]);
    }

    private function adminSession(): array
    {
        $admin = $this->makeAdmin();
        return ['admin_id' => $admin->id_admin];
    }

    public function test_bulk_update_route_exists(): void
    {
        $this->assertTrue(route('admin.personnages.bulk-update', [], false) !== null);
    }

    public function test_bulk_update_requires_admin_auth(): void
    {
        $this->patch(route('admin.personnages.bulk-update'), [
            'ids' => [1],
        ])->assertRedirect(route('admin.login'));
    }

    public function test_bulk_update_changes_element(): void
    {
        $element1 = Elements::firstOrCreate(['libelle_element' => 'Pyro']);
        $element2 = Elements::firstOrCreate(['libelle_element' => 'Hydro']);

        $perso1 = Personnage::factory()->create(['fid_element' => $element1->id_element]);
        $perso2 = Personnage::factory()->create(['fid_element' => $element1->id_element]);

        $this->withSession($this->adminSession())
            ->patch(route('admin.personnages.bulk-update'), [
                'ids' => [$perso1->id_perso, $perso2->id_perso],
                'fid_element' => $element2->id_element,
            ])
            ->assertSessionHas('success');

        $this->assertEquals($element2->id_element, $perso1->fresh()->fid_element);
        $this->assertEquals($element2->id_element, $perso2->fresh()->fid_element);
    }

    public function test_bulk_update_changes_rarity(): void
    {
        $etoile4 = Etoile::firstOrCreate(['libelle' => '4★']);
        $etoile5 = Etoile::firstOrCreate(['libelle' => '5★']);

        $perso1 = Personnage::factory()->create(['fid_etoile' => $etoile4->id_etoile]);
        $perso2 = Personnage::factory()->create(['fid_etoile' => $etoile4->id_etoile]);

        $this->withSession($this->adminSession())
            ->patch(route('admin.personnages.bulk-update'), [
                'ids' => [$perso1->id_perso, $perso2->id_perso],
                'fid_etoile' => $etoile5->id_etoile,
            ])
            ->assertSessionHas('success');

        $this->assertEquals($etoile5->id_etoile, $perso1->fresh()->fid_etoile);
        $this->assertEquals($etoile5->id_etoile, $perso2->fresh()->fid_etoile);
    }

    public function test_bulk_update_changes_weapon_type(): void
    {
        $typeArme1 = TypeArme::firstOrCreate(['libelle_TArme' => 'Épée']);
        $typeArme2 = TypeArme::firstOrCreate(['libelle_TArme' => 'Arc']);

        $perso1 = Personnage::factory()->create(['fid_TArmes' => $typeArme1->id_TArmes]);
        $perso2 = Personnage::factory()->create(['fid_TArmes' => $typeArme1->id_TArmes]);

        $this->withSession($this->adminSession())
            ->patch(route('admin.personnages.bulk-update'), [
                'ids' => [$perso1->id_perso, $perso2->id_perso],
                'fid_TArmes' => $typeArme2->id_TArmes,
            ])
            ->assertSessionHas('success');

        $this->assertEquals($typeArme2->id_TArmes, $perso1->fresh()->fid_TArmes);
        $this->assertEquals($typeArme2->id_TArmes, $perso2->fresh()->fid_TArmes);
    }

    public function test_bulk_update_with_multiple_fields(): void
    {
        $element = Elements::firstOrCreate(['libelle_element' => 'Electro']);
        $etoile5 = Etoile::firstOrCreate(['libelle' => '5★']);
        $typeArme = TypeArme::firstOrCreate(['libelle_TArme' => 'Catalyst']);

        $perso1 = Personnage::factory()->create();
        $perso2 = Personnage::factory()->create();

        $this->withSession($this->adminSession())
            ->patch(route('admin.personnages.bulk-update'), [
                'ids' => [$perso1->id_perso, $perso2->id_perso],
                'fid_element' => $element->id_element,
                'fid_etoile' => $etoile5->id_etoile,
                'fid_TArmes' => $typeArme->id_TArmes,
            ])
            ->assertSessionHas('success');

        $perso1Refreshed = $perso1->fresh();
        $perso2Refreshed = $perso2->fresh();
        $this->assertEquals($element->id_element, $perso1Refreshed->fid_element);
        $this->assertEquals($etoile5->id_etoile, $perso1Refreshed->fid_etoile);
        $this->assertEquals($typeArme->id_TArmes, $perso1Refreshed->fid_TArmes);
        $this->assertEquals($element->id_element, $perso2Refreshed->fid_element);
        $this->assertEquals($etoile5->id_etoile, $perso2Refreshed->fid_etoile);
        $this->assertEquals($typeArme->id_TArmes, $perso2Refreshed->fid_TArmes);
    }

    public function test_bulk_update_empty_ids_returns_error(): void
    {
        $this->withSession($this->adminSession())
            ->patch(route('admin.personnages.bulk-update'), [
                'ids' => [],
            ])
            ->assertSessionHas('error');
    }

    public function test_bulk_update_no_data_returns_error(): void
    {
        $perso = Personnage::factory()->create();

        $this->withSession($this->adminSession())
            ->patch(route('admin.personnages.bulk-update'), [
                'ids' => [$perso->id_perso],
            ])
            ->assertSessionHas('error');
    }

    public function test_bulk_update_index_shows_bulk_section(): void
    {
        Personnage::factory(3)->create();

        $this->withSession($this->adminSession())
            ->get(route('admin.personnages.index'))
            ->assertStatus(200)
            ->assertSeeText('Modification en masse');
    }

    public function test_bulk_update_index_shows_weapon_type_column(): void
    {
        $typeArme = TypeArme::firstOrCreate(['libelle_TArme' => 'Arc']);
        Personnage::factory()->create(['fid_TArmes' => $typeArme->id_TArmes]);

        $this->withSession($this->adminSession())
            ->get(route('admin.personnages.index'))
            ->assertStatus(200)
            ->assertSeeText("Type d'arme", false)
            ->assertSeeText('Arc');
    }

    public function test_bulk_update_index_supports_sort_query(): void
    {
        Personnage::factory()->create(['nom_perso' => 'Amber']);
        Personnage::factory()->create(['nom_perso' => 'Zhongli']);

        $this->withSession($this->adminSession())
            ->get(route('admin.personnages.index', ['sort' => 'nom_desc']))
            ->assertStatus(200)
            ->assertSeeInOrder(['Zhongli', 'Amber']);
    }

    public function test_bulk_update_index_supports_search_filter(): void
    {
        Personnage::factory()->create(['nom_perso' => 'Furina']);
        Personnage::factory()->create(['nom_perso' => 'Neuvillette']);

        $this->withSession($this->adminSession())
            ->get(route('admin.personnages.index', ['search' => 'Furi']))
            ->assertStatus(200)
            ->assertSeeText('Furina')
            ->assertDontSeeText('Neuvillette');
    }

    public function test_bulk_update_index_supports_element_filter(): void
    {
        $pyro = Elements::firstOrCreate(['libelle_element' => 'Pyro']);
        $hydro = Elements::firstOrCreate(['libelle_element' => 'Hydro']);

        Personnage::factory()->create(['nom_perso' => 'Diluc', 'fid_element' => $pyro->id_element]);
        Personnage::factory()->create(['nom_perso' => 'Mona', 'fid_element' => $hydro->id_element]);

        $this->withSession($this->adminSession())
            ->get(route('admin.personnages.index', ['element' => $pyro->id_element]))
            ->assertStatus(200)
            ->assertSeeText('Diluc')
            ->assertDontSeeText('Mona');
    }
}



