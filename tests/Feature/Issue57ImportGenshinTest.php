<?php

namespace Tests\Feature;

use App\Models\Arme;
use App\Models\Elements;
use App\Models\Etoile;
use App\Models\Personnage;
use App\Models\TypeArme;
use App\Models\TypePerso;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * Issue #57 — Commande Artisan import:genshin
 */
class Issue57ImportGenshinTest extends TestCase
{
    use RefreshDatabase;

    /** Fake API responses réutilisées dans plusieurs tests */
    private function fakeApiResponses(): void
    {
        Http::fake([
            'https://teyvat-dev.vercel.app/api/elements*' => Http::response([
                ['name' => 'Pyro', 'icon_url' => 'https://cdn.example.com/pyro.webp'],
                ['name' => 'Électro', 'icon_url' => 'https://cdn.example.com/electro.webp'],
            ], 200),
            'https://teyvat-dev.vercel.app/api/regions*' => Http::response([], 200),
            'https://teyvat-dev.vercel.app/api/weapons*' => Http::response([
                [
                    'name'        => 'Aquila Favonia',
                    'rarity'      => 5,
                    'icon_url'    => 'https://cdn.example.com/aquila.webp',
                    'type'        => ['name' => 'Épée', 'icon_url' => 'https://cdn.example.com/sword.webp'],
                ],
            ], 200),
            'https://teyvat-dev.vercel.app/api/characters*' => Http::response([
                [
                    'name'        => 'Hu Tao',
                    'rarity'      => 5,
                    'icon_url'    => 'https://cdn.example.com/hutao.webp',
                    'element'     => ['name' => 'Pyro'],
                    'weapon_type' => ['name' => 'Épée'],
                ],
            ], 200),
            'https://teyvat-dev.vercel.app/api/materials*' => Http::response([], 200),
        ]);
    }

    // ──────────────────────────────────────────────
    // Critère 1 : la commande existe et retourne SUCCESS
    // ──────────────────────────────────────────────
    public function test_commande_existe_et_retourne_success(): void
    {
        $this->fakeApiResponses();
        $exitCode = Artisan::call('import:genshin');
        $this->assertSame(0, $exitCode);
    }

    // ──────────────────────────────────────────────
    // Critère 2 : les éléments sont importés en BDD
    // ──────────────────────────────────────────────
    public function test_elements_importes_en_bdd(): void
    {
        $this->fakeApiResponses();
        Artisan::call('import:genshin');
        $this->assertDatabaseHas('elements', ['libelle_element' => 'Pyro']);
        $this->assertDatabaseHas('elements', ['libelle_element' => 'Électro']);
    }

    // ──────────────────────────────────────────────
    // Critère 3 : les armes sont importées en BDD
    // ──────────────────────────────────────────────
    public function test_armes_importees_en_bdd(): void
    {
        $this->fakeApiResponses();
        Artisan::call('import:genshin');
        $this->assertDatabaseHas('armes', ['slug' => 'aquila-favonia']);
    }

    // ──────────────────────────────────────────────
    // Critère 4 : les personnages sont importés en BDD
    // ──────────────────────────────────────────────
    public function test_personnages_importes_en_bdd(): void
    {
        $this->fakeApiResponses();
        Artisan::call('import:genshin');
        $this->assertDatabaseHas('personnage', ['slug' => 'hu-tao']);
    }

    // ──────────────────────────────────────────────
    // Critère 5 : les photos (source_url) sont associées aux éléments
    // ──────────────────────────────────────────────
    public function test_photos_elements_enregistrees(): void
    {
        $this->fakeApiResponses();
        Artisan::call('import:genshin');

        $el = Elements::where('libelle_element', 'Pyro')->first();
        $this->assertNotNull($el);
        $this->assertNotNull($el->photos()->first());
        $this->assertEquals('https://cdn.example.com/pyro.webp', $el->photos()->first()->source_url);
    }

    // ──────────────────────────────────────────────
    // Critère 6 : les photos sont associées aux armes
    // ──────────────────────────────────────────────
    public function test_photos_armes_enregistrees(): void
    {
        $this->fakeApiResponses();
        Artisan::call('import:genshin');

        $arme = Arme::where('slug', 'aquila-favonia')->first();
        $this->assertNotNull($arme);
        $this->assertNotNull($arme->photos()->first());
        $this->assertEquals('https://cdn.example.com/aquila.webp', $arme->photos()->first()->source_url);
    }

    // ──────────────────────────────────────────────
    // Critère 7 : les photos sont associées aux personnages
    // ──────────────────────────────────────────────
    public function test_photos_personnages_enregistrees(): void
    {
        $this->fakeApiResponses();
        Artisan::call('import:genshin');

        $perso = Personnage::where('slug', 'hu-tao')->first();
        $this->assertNotNull($perso);
        $this->assertNotNull($perso->photos()->first());
        $this->assertEquals('https://cdn.example.com/hutao.webp', $perso->photos()->first()->source_url);
    }

    // ──────────────────────────────────────────────
    // Critère 8 : import idempotent (updateOrCreate — pas de doublon)
    // ──────────────────────────────────────────────
    public function test_import_idempotent_pas_de_doublon(): void
    {
        $this->fakeApiResponses();
        Artisan::call('import:genshin');
        // Fake a second time (Http::fake resets after each test, so re-fake)
        $this->fakeApiResponses();
        Artisan::call('import:genshin');

        $this->assertSame(1, Elements::where('libelle_element', 'Pyro')->count());
        $this->assertSame(1, Arme::where('slug', 'aquila-favonia')->count());
        $this->assertSame(1, Personnage::where('slug', 'hu-tao')->count());
    }

    // ──────────────────────────────────────────────
    // Critère 9 : l'arme est liée à l'étoile correcte
    // ──────────────────────────────────────────────
    public function test_arme_liee_a_bonne_rarete(): void
    {
        $this->fakeApiResponses();
        Artisan::call('import:genshin');

        $arme = Arme::with('etoile')->where('slug', 'aquila-favonia')->first();
        $this->assertNotNull($arme->etoile);
        $this->assertEquals('5★', $arme->etoile->libelle);
    }

    // ──────────────────────────────────────────────
    // Critère 10 : le personnage est lié à son élément
    // ──────────────────────────────────────────────
    public function test_personnage_lie_a_son_element(): void
    {
        $this->fakeApiResponses();
        Artisan::call('import:genshin');

        $perso = Personnage::with('element')->where('slug', 'hu-tao')->first();
        $this->assertNotNull($perso->element);
        $this->assertEquals('Pyro', $perso->element->libelle_element);
    }
}
