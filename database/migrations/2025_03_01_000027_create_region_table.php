<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('région', function (Blueprint $table) {
            $table->increments('id_region');
            $table->string('nom_region', 50);
            $table->string('slug', 100)->unique();
            $table->text('descri_region')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('région');
    }
};
