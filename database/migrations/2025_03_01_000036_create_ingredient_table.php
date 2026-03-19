<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ingrédient', function (Blueprint $table) {
            $table->increments('id_ingredient');
            $table->string('nom_ingre', 150);
            $table->string('slug', 100)->unique();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ingrédient');
    }
};
