<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Issue #87 — Marquer une constellation comme recommandée ("priority node").
     * Pilote l'affichage du halo vert + badge étoile côté fiche personnage (issue #88).
     */
    public function up(): void
    {
        if (!Schema::hasColumn('constellation', 'recommandee')) {
            Schema::table('constellation', function (Blueprint $table): void {
                $table->boolean('recommandee')->default(false)->after('positions_const');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('constellation', 'recommandee')) {
            Schema::table('constellation', function (Blueprint $table): void {
                $table->dropColumn('recommandee');
            });
        }
    }
};
