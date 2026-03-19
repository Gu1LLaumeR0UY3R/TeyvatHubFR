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
            $table->foreign('fid_animal', 'fk_ar_animal')
                ->references('id_animal')->on('animaux')
                ->onDelete('cascade');
            $table->foreign('fid_region', 'fk_ar_region')
                ->references('id_region')->on('région')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('animal_region');
    }
};
