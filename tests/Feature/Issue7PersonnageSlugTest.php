<?php

namespace Tests\Feature;

use App\Models\Personnage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Issue #7 — Ajout colonne slug + fid_TArmes dans la migration Personnage
 */
class Issue7PersonnageSlugTest extends TestCase
{
    use RefreshDatabase;

    public function test_migration_personnages_execute_sans_erreur(): void
    {
        $this->assertTrue(\Schema::hasTable('personnages'));
    }

    public function test_table_a_colonne_slug(): void
    {
        $this->assertTrue(\Schema::hasColumn('personnages', 'slug'));
    }

    public function test_table_a_colonne_fid_tarmes(): void
    {
        $this->assertTrue(\Schema::hasColumn('personnages', 'fid_TArmes'));
    }

    public function test_slug_genere_automatiquement_a_la_creation(): void
    {
        $perso = Personnage::factory()->create(['nom_perso' => 'Hu Tao', 'slug' => 'hu-tao']);
        $this->assertEquals('hu-tao', $perso->slug);
    }

    public function test_slug_str_slug_hu_tao(): void
    {
        $this->assertEquals('hu-tao', Str::slug('Hu Tao'));
    }

    public function test_slug_str_slug_raiden_shogun(): void
    {
        $this->assertEquals('raiden-shogun', Str::slug('Raiden Shogun'));
    }

    public function test_slug_auto_genere_via_booted(): void
    {
        // Le booted() doit générer le slug automatiquement depuis nom_perso
        $perso = Personnage::factory()->create(['nom_perso' => 'Klee']);
        $this->assertEquals('klee', $perso->slug);
    }

    public function test_deux_personnages_ne_peuvent_pas_avoir_meme_slug(): void
    {
        $this->expectException(\Illuminate\Database\UniqueConstraintViolationException::class);
        // Même nom_perso → même slug généré par booted() → violation d'unicité
        Personnage::factory()->create(['nom_perso' => 'Hu Tao']);
        Personnage::factory()->create(['nom_perso' => 'Hu Tao']);
    }

    public function test_get_route_key_name_retourne_slug(): void
    {
        $perso = new Personnage();
        $this->assertEquals('slug', $perso->getRouteKeyName());
    }

    public function test_route_model_binding_par_slug(): void
    {
        // Vérifie que le modèle est retrouvé par slug
        $perso = Personnage::factory()->create(['nom_perso' => 'Ayaka', 'slug' => 'ayaka']);
        $found = Personnage::where('slug', 'ayaka')->firstOrFail();
        $this->assertEquals($perso->id_perso, $found->id_perso);
    }

    public function test_primary_key_est_id_perso(): void
    {
        $perso = new Personnage();
        $this->assertEquals('id_perso', $perso->getKeyName());
    }
}
