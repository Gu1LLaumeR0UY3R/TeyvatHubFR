<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bio', function (Blueprint $table) {
            $table->increments('id_bio');
            $table->string('titre_bio', 200)->nullable();
            $table->text('descri_bio')->nullable();
            $table->unsignedInteger('fid_perso');

            $table->unique('fid_perso', 'uk_bio_perso');
            $table->foreign('fid_perso', 'fk_bio_perso')
                ->references('id_perso')->on('personnage')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bio');
    }
};
