<?php

namespace Tests\Feature\Admin;

use App\Models\Admin;
use App\Models\Constellation;
use App\Models\Elements;
use App\Models\Etoile;
use App\Models\Personnage;
use App\Models\TypeArme;
use App\Models\TypePerso;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class Issue82ConstellationMapEditorTest extends TestCase
{
    use RefreshDatabase;

    private function makeAdmin(): Admin
    {
        return Admin::create([
            'pseudo_admin' => 'AdminTest',
            'email_admin' => 'admin82@test.fr',
            'mot_de_passe_admin' => Hash::make('secret123'),
            'role' => 'admin',
        ]);
    }

    private function adminSession(): array
    {
        $admin = $this->makeAdmin();

        return ['admin_id' => $admin->id_admin];
    }

    private function makePersonnage(): Personnage
    {
        $element  = Elements::firstOrCreate(['libelle_element' => 'Hydro']);
        $etoile   = Etoile::firstOrCreate(['libelle' => '5★']);
        $typeArme = TypeArme::firstOrCreate(['libelle_TArme' => 'Epee']);
        $typePerso = TypePerso::firstOrCreate(['libelle_TP' => 'jouable']);

        return Personnage::factory()->create([
            'nom_perso'   => 'Furina',
            'fid_element' => $element->id_element,
            'fid_etoile'  => $etoile->id_etoile,
            'fid_TArmes'  => $typeArme->id_TArmes,
            'fid_TP'      => $typePerso->id_TP,
        ]);
    }

    private function makePersonnageWithConstellation(): array
    {
        $personnage = $this->makePersonnage();

        $constellation = Constellation::create([
            'fid_perso'   => $personnage->id_perso,
            'titre_const' => 'C1',
            'descri_const' => 'Description',
        ]);

        return [$personnage, $constellation];
    }

    public function test_update_stocke_les_positions_constellation_en_pourcentage(): void
    {
        // Pas besoin de constellation pré-existante : le contrôleur fait un firstOrCreate
        $personnage = $this->makePersonnage();

        $positions = [
            'points' => [
                '1' => ['x' => 42.56, 'y' => 18.34],
                '2' => ['x' => 101.7, 'y' => -4.8],
                '3' => ['x' => 55.2, 'y' => 55.1],
            ],
            'lines' => [
                ['from' => 1, 'to' => 2],
                ['from' => 2, 'to' => 1],
                ['from' => 2, 'to' => 3],
                ['from' => 2, 'to' => 8],
            ],
        ];

        $this->withSession($this->adminSession())
            ->post(route('admin.personnage.block.constellation-map.update', $personnage), [
                'positions_const' => json_encode($positions, JSON_THROW_ON_ERROR),
            ])
            ->assertOk()
            ->assertJson(['success' => true]);

        $constCarte = Constellation::where('fid_perso', $personnage->id_perso)->first();
        $this->assertNotNull($constCarte);

        $this->assertEquals([
            'points' => [
                '1' => ['x' => 42.6, 'y' => 18.3],
                '2' => ['x' => 100.0, 'y' => 0.0],
                '3' => ['x' => 55.2, 'y' => 55.1],
            ],
            'lines' => [
                ['from' => 1, 'to' => 2],
                ['from' => 2, 'to' => 3],
            ],
        ], $constCarte->positions_const);
    }

    public function test_update_stocke_image_de_carte_dans_photo_polymorphique(): void
    {
        // Fonctionne même sans constellation pré-existante
        $personnage = $this->makePersonnage();

        $this->withSession($this->adminSession())
            ->post(route('admin.personnage.block.constellation-map.update', $personnage), [
                'constellation_map_image_url' => 'https://cdn.example.com/maps/furina-map.png',
            ])
            ->assertOk()
            ->assertJson(['success' => true]);

        $constCarte = Constellation::where('fid_perso', $personnage->id_perso)->first();
        $this->assertNotNull($constCarte);

        $this->assertDatabaseHas('photo', [
            'photoable_type' => 'constellation',
            'photoable_id' => $constCarte->id_const,
            'chemin_photo' => 'https://cdn.example.com/maps/furina-map.png',
            'source_url' => 'https://cdn.example.com/maps/furina-map.png',
        ]);
    }

    public function test_edit_affiche_le_bloc_constellation_map(): void
    {
        [$personnage] = $this->makePersonnageWithConstellation();

        $this->withSession($this->adminSession())
            ->get(route('admin.personnages.edit', $personnage))
            ->assertStatus(200)
            ->assertSee('id="constellation-map"', false)
            ->assertSee('name="positions_const"', false)
            ->assertSee('Mise en place des points', false)
            ->assertSee('Mode ligne', false)
            ->assertSee('Apercu statique dans la sidebar', false);
    }
}
