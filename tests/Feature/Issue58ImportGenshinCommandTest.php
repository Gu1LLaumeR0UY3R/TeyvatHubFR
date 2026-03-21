<?php

namespace Tests\Feature;

use App\Models\Arme;
use App\Models\Elements;
use App\Models\Etoile;
use App\Models\Personnage;
use App\Models\Photo;
use App\Models\TypeArme;
use App\Models\TypePerso;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class Issue58ImportGenshinCommandTest extends TestCase
{
    use RefreshDatabase;

    private function mockApiResponses($options = []): void
    {
        $defaults = [
            'elements' => [],
            'regions' => [],
            'weapons' => [],
            'characters' => [],
            'materials' => [],
        ];
        
        $responses = array_merge($defaults, $options);
        
        Http::fake([
            'teyvat-dev.vercel.app/api/elements' => Http::response($responses['elements'], 200),
            'teyvat-dev.vercel.app/api/regions' => Http::response($responses['regions'], 200),
            'teyvat-dev.vercel.app/api/weapons' => Http::response($responses['weapons'], 200),
            'teyvat-dev.vercel.app/api/characters' => Http::response($responses['characters'], 200),
            'teyvat-dev.vercel.app/api/materials' => Http::response($responses['materials'], 200),
        ]);
    }

    // Critère 1: Commande import:genshin existe et s'exécute
    public function test_commande_import_genshin_accessible(): void
    {
        $this->mockApiResponses();

        $this->artisan('import:genshin')
            ->expectsOutput('🔄 Import des éléments…')
            ->assertExitCode(0);
    }

    // Critère 2: Importe les éléments depuis l'API
    public function test_importe_elements_depuis_api(): void
    {
        $this->mockApiResponses([
            'elements' => [
                ['id' => 1, 'name' => 'Pyro', 'icon_url' => 'https://example.com/pyro.png'],
                ['id' => 2, 'name' => 'Hydro', 'icon_url' => 'https://example.com/hydro.png'],
            ],
        ]);

        $this->artisan('import:genshin')->assertExitCode(0);
        
        $this->assertGreaterThan(0, Elements::count(), 'Au moins 1 élément doit être importé');
    }

    // Critère 3: Importe les types d'armes
    public function test_importe_types_armes(): void
    {
        $this->mockApiResponses([
            'weapons' => [
                ['id' => 1, 'name' => 'Sword', 'type' => ['id' => 1, 'name' => 'Sword', 'icon_url' => ''], 'rarity' => 3, 'icon_url' => ''],
                ['id' => 2, 'name' => 'Claymore', 'type' => ['id' => 2, 'name' => 'Claymore', 'icon_url' => ''], 'rarity' => 3, 'icon_url' => ''],
            ],
        ]);

        $this->artisan('import:genshin')->assertExitCode(0);
        
        $this->assertGreaterThan(0, TypeArme::count(), 'Au moins 1 type d\'arme doit être importé');
    }

    // Critère 4: Importe les armes avec relations correctes
    public function test_importe_armes_avec_relations(): void
    {
        $this->mockApiResponses([
            'weapons' => [
                ['id' => 1, 'name' => 'Jade Cutter', 'type' => ['id' => 1, 'name' => 'Sword', 'icon_url' => ''], 'rarity' => 5, 'icon_url' => 'https://example.com/sword.png'],
            ],
        ]);

        $this->artisan('import:genshin')->assertExitCode(0);
        
        $armes = Arme::with('typeArme', 'etoile')->get();
        $this->assertGreaterThan(0, $armes->count(), 'Au moins 1 arme doit être importée');
        
        $armeWithRelations = $armes->filter(fn($a) => $a->typeArme && $a->etoile)->first();
        $this->assertNotNull($armeWithRelations, 'Les armes doivent avoir type d\'arme et rareté');
    }

    // Critère 5: Importe les personnages
    public function test_importe_personnages(): void
    {
        $this->mockApiResponses([
            'elements' => [
                ['id' => 1, 'name' => 'Pyro', 'icon_url' => ''],
            ],
            'weapons' => [
                ['id' => 1, 'name' => 'Sword 1', 'type' => ['id' => 1, 'name' => 'Sword', 'icon_url' => ''], 'rarity' => 3, 'icon_url' => ''],
            ],
            'characters' => [
                ['id' => 1, 'name' => 'Hu Tao', 'rarity' => 5, 'element' => ['id' => 1, 'name' => 'Pyro', 'icon_url' => ''], 'icon_url' => 'https://example.com/hutao.png'],
            ],
        ]);

        $this->artisan('import:genshin')->assertExitCode(0);
        
        $persos = Personnage::count();
        $this->assertGreaterThan(0, $persos, 'Au moins 1 personnage doit être importé');
    }

    // Critère 6: Slugs sont générés automatiquement
    public function test_slugs_generes_automatiquement(): void
    {
        $this->mockApiResponses([
            'weapons' => [
                ['id' => 1, 'name' => 'Skyward Blade', 'type' => ['id' => 3, 'name' => 'Sword', 'icon_url' => ''], 'rarity' => 5, 'icon_url' => ''],
            ],
        ]);

        $this->artisan('import:genshin')->assertExitCode(0);
        
        $arme = Arme::first();
        $this->assertNotNull($arme);
        $this->assertNotEmpty($arme->slug, 'Le slug doit être rempli');
        $this->assertTrue(str_contains($arme->slug, strtolower(substr($arme->nom_arme, 0, 3))), 'Le slug doit dériver du nom');
    }

    // Critère 7: Photos stockées avec morphMap (photoable_type utilise clé courte)
    public function test_photos_utilisent_morphmap(): void
    {
        $this->mockApiResponses([
            'elements' => [
                ['id' => 1, 'name' => 'Pyro', 'icon_url' => 'https://example.com/pyro.png'],
            ],
        ]);

        $this->artisan('import:genshin')->assertExitCode(0);
        
        $photosWithNamespace = Photo::where('photoable_type', 'like', 'App\\%')->get();
        $this->assertCount(0, $photosWithNamespace, 'Aucune photo ne doit avoir App\\ dans photoable_type (doit utiliser morphMap)');
    }

    // Critère 8: Utilise firstOrNew + isDirty() (pas de création de doublons)
    public function test_deuxieme_run_ne_cree_pas_doublons(): void
    {
        $this->mockApiResponses([
            'weapons' => [
                ['id' => 1, 'name' => 'Test Weapon', 'type' => ['id' => 1, 'name' => 'Sword', 'icon_url' => ''], 'rarity' => 3, 'icon_url' => ''],
            ],
        ]);

        $this->artisan('import:genshin')->assertExitCode(0);
        $count1 = Arme::count();
        
        $this->artisan('import:genshin')->assertExitCode(0);
        $count2 = Arme::count();
        
        $this->assertEquals($count1, $count2, 'Le nombre d\'armes ne doit pas changer au 2e import');
    }

    // Critère 9: Les éléments sont liés aux personnages et armes
    public function test_relations_elements_personnages_armes(): void
    {
        $this->mockApiResponses([
            'elements' => [
                ['id' => 1, 'name' => 'Pyro', 'icon_url' => 'https://example.com/pyro.png'],
            ],
            'weapons' => [
                ['id' => 1, 'name' => 'Sword', 'type' => ['id' => 1, 'name' => 'Sword', 'icon_url' => ''], 'rarity' => 3, 'icon_url' => ''],
                ['id' => 2, 'name' => 'Polearm2', 'type' => ['id' => 4, 'name' => 'Polearm', 'icon_url' => ''], 'rarity' => 3, 'icon_url' => ''],
            ],
            'characters' => [
                ['id' => 1, 'name' => 'Test Char', 'rarity' => 5, 'element' => ['id' => 1, 'name' => 'Pyro', 'icon_url' => ''], 'icon_url' => ''],
            ],
        ]);

        $this->artisan('import:genshin')->assertExitCode(0);
        
        $perso = Personnage::with('element', 'etoile', 'typeArme', 'typePerso')->first();
        $this->assertNotNull($perso);
        $this->assertNotNull($perso->element, 'Personnage doit avoir un élément');
        $this->assertNotNull($perso->etoile, 'Personnage doit avoir une rareté');
    }

    // Critère 10: Résumé affiche les statistiques d'import
    public function test_summary_output_statistics(): void
    {
        $this->mockApiResponses([
            'elements' => [
                ['id' => 1, 'name' => 'Test', 'icon_url' => ''],
            ],
        ]);

        $this->artisan('import:genshin')
            ->expectsOutput('📊 Résumé de l\'import')
            ->assertExitCode(0);
    }
}
