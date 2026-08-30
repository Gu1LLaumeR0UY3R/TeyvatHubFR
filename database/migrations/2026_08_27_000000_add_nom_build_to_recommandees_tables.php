<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('personnage_arme_recommandee', function (Blueprint $table) {
            $table->string('nom_build', 100)->nullable()->after('fid_perso');
        });

        Schema::table('personnage_artefact_recommandee', function (Blueprint $table) {
            $table->string('nom_build', 100)->nullable()->after('fid_perso');
        });
    }

    public function down(): void
    {
        Schema::table('personnage_arme_recommandee', function (Blueprint $table) {
            $table->dropColumn('nom_build');
        });

        Schema::table('personnage_artefact_recommandee', function (Blueprint $table) {
            $table->dropColumn('nom_build');
        });
    }
};
