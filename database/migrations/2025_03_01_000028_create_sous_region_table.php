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
            $table->string('nom_sous_region', 150);
            $table->string('slug', 100)->unique();
            $table->text('description')->nullable();
            $table->unsignedInteger('fid_region');
            $table->timestamps();

            $table->foreign('fid_region', 'fk_sr_region')
                ->references('id_region')->on('région')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sous_region');
    }
};
