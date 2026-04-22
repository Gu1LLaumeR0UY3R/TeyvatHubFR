<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('personnage_artefact_recommandee')) {
            return;
        }

        Schema::table('personnage_artefact_recommandee', function (Blueprint $table) {
            if (!Schema::hasColumn('personnage_artefact_recommandee', 'main_stat_sablier')) {
                $table->string('main_stat_sablier', 120)->nullable()->after('pieces_2');
            }
            if (!Schema::hasColumn('personnage_artefact_recommandee', 'main_stat_gobelet')) {
                $table->string('main_stat_gobelet', 120)->nullable()->after('main_stat_sablier');
            }
            if (!Schema::hasColumn('personnage_artefact_recommandee', 'main_stat_couronne')) {
                $table->string('main_stat_couronne', 120)->nullable()->after('main_stat_gobelet');
            }
            if (!Schema::hasColumn('personnage_artefact_recommandee', 'sub_stats')) {
                $table->string('sub_stats', 255)->nullable()->after('main_stat_couronne');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('personnage_artefact_recommandee')) {
            return;
        }

        Schema::table('personnage_artefact_recommandee', function (Blueprint $table) {
            if (Schema::hasColumn('personnage_artefact_recommandee', 'sub_stats')) {
                $table->dropColumn('sub_stats');
            }
            if (Schema::hasColumn('personnage_artefact_recommandee', 'main_stat_couronne')) {
                $table->dropColumn('main_stat_couronne');
            }
            if (Schema::hasColumn('personnage_artefact_recommandee', 'main_stat_gobelet')) {
                $table->dropColumn('main_stat_gobelet');
            }
            if (Schema::hasColumn('personnage_artefact_recommandee', 'main_stat_sablier')) {
                $table->dropColumn('main_stat_sablier');
            }
        });
    }
};
