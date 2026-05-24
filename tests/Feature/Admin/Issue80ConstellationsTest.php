<?php

namespace Tests\Feature\Admin;

use App\Models\Admin;
use App\Models\Constellation;
use App\Models\Personnage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class Issue80ConstellationsTest extends TestCase
{
    use RefreshDatabase;

    private function makeAdmin(): Admin
    {
        return Admin::create([
            'pseudo_admin' => 'AdminTest',
            'email_admin' => 'admin@test.fr',
            'mot_de_passe_admin' => Hash::make('secret123'),
            'role' => 'super_admin',
            'two_factor_enabled' => false,
        ]);
    }

    private function adminSession(): array
    {
        $admin = $this->makeAdmin();

        return [
            'admin_id' => $admin->id_admin,
            'admin_role' => $admin->role,
            'admin_2fa_passed' => true,
        ];
    }

    private function tinyPngContent(): string
    {
        return base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO1XWZ0AAAAASUVORK5CYII=');
    }

    public function test_update_constellation_titre_et_description(): void
    {
        $personnage = Personnage::factory()->create();
        $const1 = Constellation::create([
            'fid_perso' => $personnage->id_perso,
            'titre_const' => 'C1 Ancien titre',
            'descri_const' => 'Ancienne description',
        ]);

        $this->withSession($this->adminSession())
            ->putJson(route('admin.personnage.block.constellations.update', $personnage), [
                'constellations' => [
                    [
                        'id_const' => $const1->id_const,
                        'titre_const' => 'C1 Nouveau titre',
                        'descri_const' => 'Nouvelle description',
                    ],
                ],
            ])
            ->assertStatus(200)
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('constellation', [
            'id_const' => $const1->id_const,
            'fid_perso' => $personnage->id_perso,
            'titre_const' => 'C1 Nouveau titre',
            'descri_const' => 'Nouvelle description',
        ]);
    }

    public function test_upload_image_constellation_stocke_fichier(): void
    {
        Storage::fake('public');

        $personnage = Personnage::factory()->create(['nom_perso' => 'Furina']);

        $response = $this->withSession($this->adminSession())
            ->post(route('admin.personnage.block.constellations.upload', $personnage), [
                'constellation_index' => 3,
                'image' => UploadedFile::fake()->createWithContent('constellation.png', $this->tinyPngContent()),
            ])
            ->assertStatus(200)
            ->assertJson(['success' => true]);

        $storedPath = $response->json('path');
        $this->assertStringStartsWith('photos/personnages/constellations/' . $personnage->slug . '-c3.', $storedPath);
        $this->assertTrue(Storage::disk('public')->exists($storedPath));
    }

    public function test_constellations_chargees_au_chargement_editeur(): void
    {
        $personnage = Personnage::factory()->create(['nom_perso' => 'Furina']);

        for ($i = 1; $i <= 6; $i++) {
            Constellation::create([
                'fid_perso' => $personnage->id_perso,
                'titre_const' => 'Constellation ' . $i,
                'descri_const' => 'Description ' . $i,
            ]);
        }

        $this->withSession($this->adminSession())
            ->get(route('admin.personnages.edit', $personnage))
            ->assertStatus(200)
            ->assertSee('Constellation 1')
            ->assertSee('Constellation 2')
            ->assertSee('Constellation 3')
            ->assertSee('Constellation 4')
            ->assertSee('Constellation 5')
            ->assertSee('Constellation 6');
    }
}
