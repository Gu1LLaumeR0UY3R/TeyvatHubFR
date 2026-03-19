<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('spécialité', function (Blueprint $table) {
            $table->increments('id_specialite');
            $table->string('libelle_spe', 150)->nullable();
            $table->text('descri_spe')->nullable();
            $table->unsignedInteger('fid_plat');
            $table->unsignedInteger('fid_perso');

            $table->unique('fid_plat', 'uk_spe_plat');
            $table->foreign('fid_plat', 'fk_spe_plat')
                ->references('id_plat')->on('plat')
                ->onDelete('cascade');
            $table->foreign('fid_perso', 'fk_spe_perso')
                ->references('id_perso')->on('personnage')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('spécialité');
    }
};
