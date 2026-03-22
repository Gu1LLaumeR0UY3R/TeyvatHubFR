<?php

namespace Tests\Feature;

use App\Models\Arme;
use App\Models\Etoile;
use App\Models\Nation;
use App\Models\Personnage;
use App\Models\TypeArme;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Issue63ImageAccessorsTest extends TestCase
{
    use RefreshDatabase;

    // Critère 1 : icone_url retourne source_url si présente
    public function test_icone_url_retourne_source_url_si_presente(): void
    {
        $perso = Personnage::factory()->create();
        $perso->photos()->create([
            'chemin_photo' => 'photos/personnages/test.webp',
            'source_url'   => 'https://api.example.com/icon.webp',
        ]);
        $perso->load('photos');
        $this->assertEquals('https://api.example.com/icon.webp', $perso->icone_url);
    }

    // Critère 2 : icone_url retourne placeholder si aucune photo en BDD
    public function test_icone_url_retourne_placeholder_si_aucune_photo(): void
    {
        $perso = Personnage::factory()->create();
        $perso->load('photos');
        $this->assertStringContainsString('placeholder', $perso->icone_url);
    }

    // Critère 3 : full_image_url retourne la même URL que icone_url
    public function test_full_image_url_retourne_source_url(): void
    {
        $perso = Personnage::factory()->create();
        $perso->photos()->create([
            'chemin_photo' => 'photos/personnages/full.webp',
            'source_url'   => 'https://api.example.com/full.webp',
        ]);
        $perso->load('photos');
        $this->assertEquals('https://api.example.com/full.webp', $perso->full_image_url);
    }

    // Critère 4 : accessors disponibles sur Arme
    public function test_icone_url_sur_arme(): void
    {
        $arme = Arme::factory()->create();
        $arme->photos()->create([
            'chemin_photo' => 'test.webp',
            'source_url'   => 'https://api.example.com/arme.webp',
        ]);
        $arme->load('photos');
        $this->assertEquals('https://api.example.com/arme.webp', $arme->icone_url);
    }

    // Critère 5 : icone_url utilise Storage::url pour un chemin local
    public function test_icone_url_retourne_storage_url_pour_chemin_local(): void
    {
        $perso = Personnage::factory()->create();
        $perso->photos()->create([
            'chemin_photo' => 'photos/personnages/icones_personnage/test.webp',
            'source_url'   => null,
        ]);
        $perso->load('photos');
        $this->assertStringContainsString('test.webp', $perso->icone_url);
    }

    // Critère 6 : accessors disponibles sur Nation
    public function test_icone_url_sur_nation(): void
    {
        $nation = Nation::factory()->create();
        $nation->photos()->create([
            'chemin_photo' => 'regions/icones/mondstadt.webp',
            'source_url'   => 'https://api.example.com/mondstadt.webp',
        ]);
        $nation->load('photos');
        $this->assertEquals('https://api.example.com/mondstadt.webp', $nation->icone_url);
    }
}
