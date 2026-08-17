<?php

namespace Tests\Feature;

use App\Models\Personnage;
use App\Models\Nation;
use App\Models\Arme;
use App\Models\Photo;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Issue62MorphMapTest extends TestCase
{
    use RefreshDatabase;

    // Critère 1 : MorphMap est configuré dans AppServiceProvider
    public function test_morphmap_configuré(): void
    {
        $morphMap = Relation::morphMap();
        
        $this->assertNotEmpty($morphMap, 'Le morphMap ne doit pas être vide');
        $this->assertArrayHasKey('personnage', $morphMap);
        $this->assertArrayHasKey('nation', $morphMap);
        $this->assertArrayHasKey('arme', $morphMap);
        $this->assertArrayHasKey('ennemi', $morphMap);
        $this->assertArrayHasKey('animal', $morphMap);
    }

    // Critère 2 : $personnage->photos retourne bien les photos liées
    public function test_personnage_photos_retourne_photos(): void
    {
        $perso = Personnage::factory()->create(['nom_perso' => 'Hu Tao']);
        
        // Créer une photo directement avec le morphMap
        $photo = Photo::create([
            'chemin_photo'  => 'photos/personnages/hutao.png',
            'source_url'    => 'https://example.com/hutao.png',
            'photoable_type' => Relation::getMorphAlias(Personnage::class),
            'photoable_id'   => $perso->id_perso,
        ]);

        $this->assertCount(1, $perso->photos);
        $this->assertEquals('hutao.png', basename($perso->photos->first()->chemin_photo));
    }

    // Critère 3 : Nation->photos fonctionne
    public function test_nation_photos_phonctionnel(): void
    {
        $nation = Nation::create(['nom_region' => 'Mondstadt']);
        
        Photo::create([
            'chemin_photo'   => 'photos/nations/mondstadt.png',
            'source_url'     => 'https://example.com/mondstadt.png',
            'photoable_type' => Relation::getMorphAlias(Nation::class),
            'photoable_id'   => $nation->id_region,
        ]);

        $this->assertCount(1, $nation->photos);
    }

    // Critère 4 : Arme->photos fonctionne
    public function test_arme_photos_fonctionnel(): void
    {
        $arme = Arme::factory()->create(['nom_arme' => 'Windblume Ode']);
        
        Photo::create([
            'chemin_photo'   => 'photos/armes/windblume.png',
            'source_url'     => 'https://example.com/windblume.png',
            'photoable_type' => Relation::getMorphAlias(Arme::class),
            'photoable_id'   => $arme->id_arme,
        ]);

        $this->assertCount(1, $arme->photos);
    }

    // Critère 5 : photoable_type ne contient jamais un namespace complet (App\Models\...)
    public function test_photoable_type_ne_contient_pas_namespace(): void
    {
        $perso = Personnage::factory()->create(['nom_perso' => 'Raiden Shogun']);
        
        Photo::create([
            'chemin_photo'   => 'photos/perso/raiden.png',
            'source_url'     => 'https://example.com/raiden.png',
            'photoable_type' => Relation::getMorphAlias(Personnage::class),
            'photoable_id'   => $perso->id_perso,
        ]);

        $photo = Photo::first();
        
        // Vérifier que le photoable_type n'est pas le namespace complet
        $this->assertStringNotContainsString('App\\Models', $photo->photoable_type);
        $this->assertStringNotContainsString('App\\', $photo->photoable_type);
        // Doit être la clé courte du morphMap (ex: 'personnage')
        $this->assertEquals('personnage', $photo->photoable_type);
    }

    // Critère 6 : Chaque type de modèle peut récupérer ses photos
    public function test_toutes_les_relations_polymorphiques(): void
    {
        // Personnage
        $perso = Personnage::factory()->create(['nom_perso' => 'Test']);
        Photo::create([
            'chemin_photo' => 'test.png',
            'photoable_type' => 'personnage',
            'photoable_id' => $perso->id_perso,
        ]);
        $this->assertCount(1, $perso->fresh()->photos);

        // Nation
        $nation = Nation::create(['nom_region' => 'Test']);
        Photo::create([
            'chemin_photo' => 'test.png',
            'photoable_type' => 'nation',
            'photoable_id' => $nation->id_region,
        ]);
        $this->assertCount(1, $nation->fresh()->photos);

        // Arme
        $arme = Arme::factory()->create(['nom_arme' => 'Test']);
        Photo::create([
            'chemin_photo' => 'test.png',
            'photoable_type' => 'arme',
            'photoable_id' => $arme->id_arme,
        ]);
        $this->assertCount(1, $arme->fresh()->photos);
    }

    public function test_photo_normalise_les_types_morph_legacy_vers_alias(): void
    {
        $this->assertSame('personnage', Photo::normalizeMorphType(Personnage::class));
        $this->assertSame('nation', Photo::normalizeMorphType(Nation::class));
        $this->assertSame('arme', Photo::normalizeMorphType('arme'));
        $this->assertSame('personnage', Photo::normalizeMorphType('App\\Models\\Personnage'));
        $this->assertSame('nation', Photo::normalizeMorphType('App\\Models\\Nation'));
    }
}
