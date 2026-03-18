<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sous_region', function (Blueprint $table) {
            $table->increments('id_sous_region');
            $table->string('nom_sous_region', 100);
            $table->string('slug', 100)->unique();
            $table->text('description')->nullable();
            $table->unsignedInteger('fid_region');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sous_region');
    }
};
