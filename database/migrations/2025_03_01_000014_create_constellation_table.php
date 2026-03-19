<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('constellation', function (Blueprint $table) {
            $table->increments('id_const');
            $table->string('titre_const', 200);
            $table->text('descri_const')->nullable();
            $table->unsignedInteger('fid_perso');

            $table->foreign('fid_perso', 'fk_const_perso')
                ->references('id_perso')->on('personnage')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('constellation');
    }
};
