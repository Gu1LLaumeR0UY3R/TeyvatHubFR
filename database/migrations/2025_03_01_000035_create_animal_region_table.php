<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('animal_region', function (Blueprint $table) {
            $table->unsignedInteger('fid_animal');
            $table->unsignedInteger('fid_region');
            $table->primary(['fid_animal', 'fid_region']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('animal_region');
    }
};
