<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('personnage_arme_recommandee', function (Blueprint $table) {
            $table->unsignedInteger('fid_perso');
            $table->unsignedInteger('fid_arme');
            $table->tinyInteger('position');
            $table->enum('origine', ['tirage', 'evenement', 'craft', 'achat'])->nullable();
            $table->tinyInteger('starter')->default(0);

            $table->primary(['fid_perso', 'fid_arme']);
            $table->unique(['fid_perso', 'position'], 'uk_arme_position');

            $table->foreign('fid_perso')
                ->references('id_perso')
                ->on('personnage')
                ->onDelete('cascade');

            $table->foreign('fid_arme')
                ->references('id_arme')
                ->on('armes')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('personnage_arme_recommandee');
    }
};
