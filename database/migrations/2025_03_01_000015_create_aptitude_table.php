<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('aptitude', function (Blueprint $table) {
            $table->increments('id_aptitude');
            $table->string('titre_apti', 100);
            $table->text('descri_apti');
            $table->unsignedTinyInteger('lvl_apt')->default(1)->nullable();
            $table->text('sub_Apt')->nullable();
            $table->unsignedInteger('fid_TypeApti');
            $table->unsignedInteger('fid_perso');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('aptitude');
    }
};
