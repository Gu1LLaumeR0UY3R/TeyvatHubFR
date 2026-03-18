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
            $table->string('libelle_spe', 100);
            $table->text('descri_spe')->nullable();
            $table->unsignedInteger('fid_plat')->unique();
            $table->unsignedInteger('fid_perso');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('spécialité');
    }
};
