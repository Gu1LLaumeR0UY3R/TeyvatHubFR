<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('type_ennemi', function (Blueprint $table) {
            $table->increments('id_typeEnnemi');
            $table->string('libelle_Type', 50);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('type_ennemi');
    }
};
