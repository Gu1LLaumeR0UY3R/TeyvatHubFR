<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('personnage_arme_recommandee', 'commentaire')) {
            Schema::table('personnage_arme_recommandee', function (Blueprint $table) {
                $table->string('commentaire', 500)->nullable()->after('starter');
            });
        }

        if (!Schema::hasColumn('personnage_artefact_recommandee', 'commentaire')) {
            Schema::table('personnage_artefact_recommandee', function (Blueprint $table) {
                $table->string('commentaire', 500)->nullable()->after('sub_stats');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('personnage_arme_recommandee', 'commentaire')) {
            Schema::table('personnage_arme_recommandee', function (Blueprint $table) {
                $table->dropColumn('commentaire');
            });
        }

        if (Schema::hasColumn('personnage_artefact_recommandee', 'commentaire')) {
            Schema::table('personnage_artefact_recommandee', function (Blueprint $table) {
                $table->dropColumn('commentaire');
            });
        }
    }
};
