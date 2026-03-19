<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('animal_ingredient', function (Blueprint $table) {
            $table->unsignedInteger('fid_animal');
            $table->unsignedInteger('fid_ingredient');

            $table->primary(['fid_animal', 'fid_ingredient']);
            $table->foreign('fid_animal', 'fk_ai_animal')
                ->references('id_animal')->on('animaux')
                ->onDelete('cascade');
            $table->foreign('fid_ingredient', 'fk_ai_ingre')
                ->references('id_ingredient')->on('ingrédient')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('animal_ingredient');
    }
};
