<?php

namespace Tests\Feature\Admin;

use App\Models\Admin;
use App\Models\Arme;
use App\Models\Elements;
use App\Models\Etoile;
use App\Models\Personnage;
use App\Models\PersonnageArmeRecommandee;
use App\Models\TypeArme;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class Issue76PersonnageEditDataTest extends TestCase
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

    public function test_edit_preremplit_nom_personnage(): void
    {
        $personnage = Personnage::factory()->create(['nom_perso' => 'Furina']);

        $this->withSession($this->adminSession())
            ->get(route('admin.personnages.edit', $personnage))
            ->assertStatus(200)
            ->assertSee('value="Furina"', false);
    }

    public function test_edit_preremplit_select_element(): void
    {
        $hydro = Elements::firstOrCreate(['libelle_element' => 'Hydro']);
        $personnage = Personnage::factory()->create(['fid_element' => $hydro->id_element]);

        $this->withSession($this->adminSession())
            ->get(route('admin.personnages.edit', $personnage))
            ->assertStatus(200)
            ->assertSee('option value="' . $hydro->id_element . '" selected', false);
    }

    public function test_edit_preremplit_select_type_arme(): void
    {
        $arc = TypeArme::firstOrCreate(['libelle_TArme' => 'Arc']);

        $personnage = Personnage::factory()->create([
            'fid_TArmes' => $arc->id_TArmes,
        ]);

        $this->withSession($this->adminSession())
            ->get(route('admin.personnages.edit', $personnage))
            ->assertStatus(200)
            ->assertSee('option value="' . $arc->id_TArmes . '" selected', false);
    }

    public function test_edit_preremplit_videos(): void
    {
        $personnage = Personnage::factory()->create();

        DB::table('personnage_video')->insert([
            'fid_perso' => $personnage->id_perso,
            'url_video' => 'https://www.youtube.com/watch?v=video_one',
            'ordre' => 1,
            'created_at' => now(),
        ]);
        DB::table('personnage_video')->insert([
            'fid_perso' => $personnage->id_perso,
            'url_video' => 'https://www.youtube.com/watch?v=video_two',
            'ordre' => 2,
            'created_at' => now(),
        ]);

        $this->withSession($this->adminSession())
            ->get(route('admin.personnages.edit', $personnage))
            ->assertStatus(200)
            ->assertSee('https://www.youtube.com/watch?v=video_one')
            ->assertSee('https://www.youtube.com/watch?v=video_two');
    }

    public function test_edit_preremplit_armes_recommandees(): void
    {
        $etoile4 = Etoile::firstOrCreate(['libelle' => '4★']);
        $typeArme = TypeArme::firstOrCreate(['libelle_TArme' => 'Arc']);
        $personnage = Personnage::factory()->create();

        $arme1 = Arme::factory()->create([
            'nom_arme' => 'Aqua Simulacra',
            'fid_etoile' => $etoile4->id_etoile,
            'fid_TArmes' => $typeArme->id_TArmes,
        ]);
        $arme2 = Arme::factory()->create([
            'nom_arme' => 'Fleuve Cendre Ferryman',
            'fid_etoile' => $etoile4->id_etoile,
            'fid_TArmes' => $typeArme->id_TArmes,
        ]);

        PersonnageArmeRecommandee::create([
            'fid_perso' => $personnage->id_perso,
            'fid_arme' => $arme1->id_arme,
            'position' => 1,
            'origine' => 'tirage',
            'starter' => 0,
        ]);
        PersonnageArmeRecommandee::create([
            'fid_perso' => $personnage->id_perso,
            'fid_arme' => $arme2->id_arme,
            'position' => 2,
            'origine' => 'craft',
            'starter' => 1,
        ]);

        $this->withSession($this->adminSession())
            ->get(route('admin.personnages.edit', $personnage))
            ->assertStatus(200)
            ->assertSee('Aqua Simulacra')
            ->assertSee('Fleuve Cendre Ferryman');
    }
}
