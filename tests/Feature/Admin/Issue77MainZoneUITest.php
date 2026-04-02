<?php

namespace Tests\Feature\Admin;

use App\Models\Admin;
use App\Models\Elements;
use App\Models\Etoile;
use App\Models\Personnage;
use App\Models\Photo;
use App\Models\TypeArme;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class Issue77MainZoneUITest extends TestCase
{
    use RefreshDatabase;

    private function tinyPngContent(): string
    {
        return base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO1XWZ0AAAAASUVORK5CYII=');
    }

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

    public function test_upload_portrait_stocke_fichier(): void
    {
        Storage::fake('public');

        $personnage = Personnage::factory()->create(['nom_perso' => 'Furina']);

        $response = $this->withSession($this->adminSession())
            ->post(route('admin.personnage.block.main-zone.upload', $personnage), [
                'image_type' => 'portrait',
                'image' => UploadedFile::fake()->createWithContent('portrait.jpg', $this->tinyPngContent()),
            ])
            ->assertStatus(200)
            ->assertJson(['success' => true]);

        $storedPath = $response->json('path');
        $this->assertStringStartsWith('photos/personnages/personnage_full/' . $personnage->slug . '-full.', $storedPath);
        $this->assertTrue(Storage::disk('public')->exists($storedPath));
        $this->assertDatabaseHas('photo', [
            'photoable_type' => 'personnage',
            'photoable_id' => $personnage->id_perso,
            'type' => 'portrait',
            'chemin_photo' => $storedPath,
        ]);
    }

    public function test_upload_icone_stocke_fichier(): void
    {
        Storage::fake('public');

        $personnage = Personnage::factory()->create(['nom_perso' => 'Furina']);

        $response = $this->withSession($this->adminSession())
            ->post(route('admin.personnage.block.main-zone.upload', $personnage), [
                'image_type' => 'icone',
                'image' => UploadedFile::fake()->createWithContent('icon.png', $this->tinyPngContent()),
            ])
            ->assertStatus(200)
            ->assertJson(['success' => true]);

        $storedPath = $response->json('path');
        $this->assertStringStartsWith('photos/personnages/icones_personnage/' . $personnage->slug . '-icon.', $storedPath);
        $this->assertTrue(Storage::disk('public')->exists($storedPath));
        $this->assertDatabaseHas('photo', [
            'photoable_type' => 'personnage',
            'photoable_id' => $personnage->id_perso,
            'type' => 'icone',
            'chemin_photo' => $storedPath,
        ]);
    }

    public function test_update_main_zone_sauvegarde_videos(): void
    {
        $element = Elements::firstOrCreate(['libelle_element' => 'Hydro']);
        $etoile = Etoile::firstOrCreate(['libelle' => '5★']);
        $typeArme = TypeArme::firstOrCreate(['libelle_TArme' => 'Epee']);

        $personnage = Personnage::factory()->create([
            'nom_perso' => 'Furina',
            'fid_element' => $element->id_element,
            'fid_etoile' => $etoile->id_etoile,
            'fid_TArmes' => $typeArme->id_TArmes,
        ]);

        $this->withSession($this->adminSession())
            ->putJson(route('admin.personnage.block.main-zone.update', $personnage), [
                'nom_perso' => 'Furina',
                'fid_element' => $element->id_element,
                'fid_etoile' => $etoile->id_etoile,
                'fid_TArmes' => $typeArme->id_TArmes,
                'videos' => [
                    ['url_video' => 'https://www.youtube.com/watch?v=aaa111bbb22'],
                    ['url_video' => 'https://www.youtube.com/watch?v=ccc333ddd44'],
                ],
            ])
            ->assertStatus(200)
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('personnage_video', [
            'fid_perso' => $personnage->id_perso,
            'url_video' => 'https://www.youtube.com/watch?v=aaa111bbb22',
            'ordre' => 1,
        ]);
        $this->assertDatabaseHas('personnage_video', [
            'fid_perso' => $personnage->id_perso,
            'url_video' => 'https://www.youtube.com/watch?v=ccc333ddd44',
            'ordre' => 2,
        ]);
    }

    public function test_main_zone_retourne_urls_images_existantes(): void
    {
        $personnage = Personnage::factory()->create(['slug' => 'furina']);

        Photo::create([
            'photoable_type' => 'personnage',
            'photoable_id' => $personnage->id_perso,
            'type' => 'icone',
            'chemin_photo' => 'https://cdn.example.com/furina-icon.png',
            'source_url' => 'https://cdn.example.com/furina-icon.png',
        ]);

        Photo::create([
            'photoable_type' => 'personnage',
            'photoable_id' => $personnage->id_perso,
            'type' => 'portrait',
            'chemin_photo' => 'https://cdn.example.com/furina-full.jpg',
            'source_url' => 'https://cdn.example.com/furina-full.jpg',
        ]);

        $this->withSession($this->adminSession())
            ->get(route('admin.personnages.edit', $personnage))
            ->assertStatus(200)
            ->assertSee('https://cdn.example.com/furina-icon.png')
            ->assertSee('https://cdn.example.com/furina-full.jpg');
    }
}
