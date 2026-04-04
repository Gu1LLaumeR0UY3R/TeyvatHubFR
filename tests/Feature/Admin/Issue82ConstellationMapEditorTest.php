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

    private function makePersonnageWithConstellation(): array
    {
        $element = Elements::firstOrCreate(['libelle_element' => 'Hydro']);
        $etoile = Etoile::firstOrCreate(['libelle' => '5★']);
        $typeArme = TypeArme::firstOrCreate(['libelle_TArme' => 'Epee']);
        $typePerso = TypePerso::firstOrCreate(['libelle_TP' => 'jouable']);

        $personnage = Personnage::factory()->create([
            'nom_perso' => 'Furina',
            'fid_element' => $element->id_element,
            'fid_etoile' => $etoile->id_etoile,
            'fid_TArmes' => $typeArme->id_TArmes,
            'fid_TP' => $typePerso->id_TP,
        ]);

        $constellation = Constellation::create([
            'fid_perso' => $personnage->id_perso,
            'titre_const' => 'C1',
            'descri_const' => 'Description',
        ]);

        return [$personnage, $constellation];
    }

    private function updatePayload(Personnage $personnage): array
    {
        return [
            'nom_perso' => $personnage->nom_perso,
            'fid_element' => $personnage->fid_element,
            'fid_etoile' => $personnage->fid_etoile,
            'fid_TArmes' => $personnage->fid_TArmes,
            'fid_TP' => $personnage->fid_TP,
        ];
    }

    public function test_update_stocke_les_positions_constellation_en_pourcentage(): void
    {
        [$personnage, $constellation] = $this->makePersonnageWithConstellation();

        $positions = [
            '1' => ['x' => 42.56, 'y' => 18.34],
            '2' => ['x' => 101.7, 'y' => -4.8],
            '3' => ['x' => 55.2, 'y' => 55.1],
        ];

        $this->withSession($this->adminSession())
            ->put(route('admin.personnages.update', $personnage), [
                ...$this->updatePayload($personnage),
                'positions_const' => json_encode($positions, JSON_THROW_ON_ERROR),
            ])
            ->assertRedirect(route('admin.personnages.index'));

        $constellation->refresh();

        $this->assertEquals([
            '1' => ['x' => 42.6, 'y' => 18.3],
            '2' => ['x' => 100.0, 'y' => 0.0],
            '3' => ['x' => 55.2, 'y' => 55.1],
        ], $constellation->positions_const);
    }

    public function test_update_stocke_image_de_carte_dans_photo_polymorphique(): void
    {
        [$personnage, $constellation] = $this->makePersonnageWithConstellation();

        $this->withSession($this->adminSession())
            ->put(route('admin.personnages.update', $personnage), [
                ...$this->updatePayload($personnage),
                'constellation_map_image_url' => 'https://cdn.example.com/maps/furina-map.png',
            ])
            ->assertRedirect(route('admin.personnages.index'));

        $this->assertDatabaseHas('photo', [
            'photoable_type' => 'constellation',
            'photoable_id' => $constellation->id_const,
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
            ->assertSee('Prochain point a placer', false)
            ->assertSee('name="positions_const"', false);
    }
}
