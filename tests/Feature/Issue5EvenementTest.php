<?php

namespace Tests\Feature;

use App\Models\Evenement;
use App\Models\Photo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Issue #5 — Migration et modèle Evenement
 */
class Issue5EvenementTest extends TestCase
{
    use RefreshDatabase;

    public function test_migration_evenements_execute_sans_erreur(): void
    {
        // Si RefreshDatabase passe, la migration s'est exécutée
        $this->assertTrue(Schema::hasTable('evenement'));
    }

    public function test_modele_evenement_existe(): void
    {
        $this->assertTrue(class_exists(Evenement::class));
    }

    public function test_creation_evenement(): void
    {
        $evt = Evenement::factory()->create([
            'titre'      => 'Test Event',
            'date_debut' => '2026-03-01',
            'date_fin'   => '2026-03-31',
        ]);
        $this->assertDatabaseHas('evenement', ['titre' => 'Test Event']);
    }

    public function test_dates_castees_en_carbon(): void
    {
        $evt = Evenement::factory()->create([
            'date_debut' => '2026-03-01',
            'date_fin'   => '2026-03-31',
        ]);
        $evt->refresh();
        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $evt->date_debut);
        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $evt->date_fin);
    }

    public function test_relation_photos_morphmany(): void
    {
        $evt = Evenement::factory()->create();
        $evt->photos()->create([
            'chemin_photo' => 'photos/test.jpg',
            'source_url'   => null,
        ]);
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $evt->photos);
        $this->assertEquals(1, $evt->photos->count());
    }

    public function test_photo_photoable_type_est_evenement(): void
    {
        $evt = Evenement::factory()->create();
        $evt->photos()->create(['chemin_photo' => 'test.jpg', 'source_url' => null]);
        $photo = Photo::first();
        $this->assertEquals('evenement', $photo->photoable_type);
    }

    public function test_fillable_colonnes(): void
    {
        $evt = new Evenement();
        $this->assertContains('titre', $evt->getFillable());
        $this->assertContains('date_debut', $evt->getFillable());
        $this->assertContains('date_fin', $evt->getFillable());
    }

    public function test_table_a_colonnes_requises(): void
    {
        $this->assertTrue(Schema::hasColumn('evenement', 'titre'));
        $this->assertTrue(Schema::hasColumn('evenement', 'descri_courte'));
        $this->assertTrue(Schema::hasColumn('evenement', 'description'));
        $this->assertTrue(Schema::hasColumn('evenement', 'date_debut'));
        $this->assertTrue(Schema::hasColumn('evenement', 'date_fin'));
    }
}
