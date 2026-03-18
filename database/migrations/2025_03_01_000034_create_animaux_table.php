<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('animaux', function (Blueprint $table) {
            $table->increments('id_animal');
            $table->string('nom_animal', 100);
            $table->string('slug', 100)->unique();
            $table->text('descri_animal')->nullable();
            $table->unsignedInteger('fid_TAnimal');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('animaux');
    }
};
