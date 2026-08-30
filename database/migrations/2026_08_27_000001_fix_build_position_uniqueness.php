<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $indexExists = static fn (string $table, string $index): bool => DB::table('information_schema.statistics')
            ->where('TABLE_SCHEMA', DB::getDatabaseName())
            ->where('TABLE_NAME', $table)
            ->where('INDEX_NAME', $index)
            ->exists();

        $originalSqlMode = DB::selectOne('SELECT @@SESSION.sql_mode AS mode')->mode;
        DB::statement("SET SESSION sql_mode = ''");

        DB::statement("UPDATE personnage_arme_recommandee SET nom_build = '' WHERE nom_build IS NULL");
        DB::statement("ALTER TABLE personnage_arme_recommandee MODIFY nom_build VARCHAR(100) NOT NULL DEFAULT ''");

        DB::statement("UPDATE personnage_artefact_recommandee SET nom_build = '' WHERE nom_build IS NULL");
        DB::statement("ALTER TABLE personnage_artefact_recommandee MODIFY nom_build VARCHAR(100) NOT NULL DEFAULT ''");

        DB::statement("SET SESSION sql_mode = '{$originalSqlMode}'");

        if (!$indexExists('personnage_arme_recommandee', 'uk_arme_build_position')) {
            Schema::table('personnage_arme_recommandee', function (Blueprint $table) {
                $table->unique(['fid_perso', 'nom_build', 'position'], 'uk_arme_build_position');
            });
        }
        if ($indexExists('personnage_arme_recommandee', 'uk_arme_position')) {
            Schema::table('personnage_arme_recommandee', function (Blueprint $table) {
                $table->dropUnique('uk_arme_position');
            });
        }

        if (!$indexExists('personnage_artefact_recommandee', 'uk_artefact_build_position')) {
            Schema::table('personnage_artefact_recommandee', function (Blueprint $table) {
                $table->unique(['fid_perso', 'nom_build', 'position'], 'uk_artefact_build_position');
            });
        }
        if ($indexExists('personnage_artefact_recommandee', 'uk_build_position')) {
            Schema::table('personnage_artefact_recommandee', function (Blueprint $table) {
                $table->dropUnique('uk_build_position');
            });
        }
    }

    public function down(): void
    {
        $indexExists = static fn (string $table, string $index): bool => DB::table('information_schema.statistics')
            ->where('TABLE_SCHEMA', DB::getDatabaseName())
            ->where('TABLE_NAME', $table)
            ->where('INDEX_NAME', $index)
            ->exists();

        if (!$indexExists('personnage_arme_recommandee', 'uk_arme_position')) {
            Schema::table('personnage_arme_recommandee', function (Blueprint $table) {
                $table->unique(['fid_perso', 'position'], 'uk_arme_position');
            });
        }
        if ($indexExists('personnage_arme_recommandee', 'uk_arme_build_position')) {
            Schema::table('personnage_arme_recommandee', function (Blueprint $table) {
                $table->dropUnique('uk_arme_build_position');
            });
        }

        if (!$indexExists('personnage_artefact_recommandee', 'uk_build_position')) {
            Schema::table('personnage_artefact_recommandee', function (Blueprint $table) {
                $table->unique(['fid_perso', 'position'], 'uk_build_position');
            });
        }
        if ($indexExists('personnage_artefact_recommandee', 'uk_artefact_build_position')) {
            Schema::table('personnage_artefact_recommandee', function (Blueprint $table) {
                $table->dropUnique('uk_artefact_build_position');
            });
        }

        DB::statement("ALTER TABLE personnage_arme_recommandee MODIFY nom_build VARCHAR(100) NULL");
        DB::statement("ALTER TABLE personnage_artefact_recommandee MODIFY nom_build VARCHAR(100) NULL");
    }
};