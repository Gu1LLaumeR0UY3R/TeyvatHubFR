<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('personnage_stats_recommandee')) {
            return;
        }

        Schema::create('personnage_stats_recommandee', function (Blueprint $table) {
            $table->id('id_stats');
            $table->unsignedInteger('fid_perso');
            $table->string('nom_build', 100)->nullable();
            $table->string('pv', 20)->nullable();
            $table->string('atq', 20)->nullable();
            $table->string('def', 20)->nullable();
            $table->string('taux_crit', 20)->nullable();
            $table->string('degats_crit', 20)->nullable();
            $table->string('maitrise_elementaire', 20)->nullable();
            $table->string('recharge_energetique', 20)->nullable();
            $table->string('commentaire', 500)->nullable();
            $table->tinyInteger('position')->default(1);

            $table->unique(['fid_perso', 'nom_build'], 'uk_stats_perso_build');

            $table->foreign('fid_perso')
                ->references('id_perso')
                ->on('personnage')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('personnage_stats_recommandee');
    }
};
