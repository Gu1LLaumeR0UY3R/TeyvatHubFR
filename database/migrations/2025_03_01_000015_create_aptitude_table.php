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
            $table->string('titre_apti', 200);
            $table->text('descri_apti')->nullable();
            $table->tinyInteger('lvl_apt')->default(1);
            $table->text('sub_Apt')->nullable();
            $table->unsignedInteger('fid_TypeApti');
            $table->unsignedInteger('fid_perso');

            $table->foreign('fid_TypeApti', 'fk_apti_type')
                ->references('id_TypeApti')->on('type_apti');
            $table->foreign('fid_perso', 'fk_apti_perso')
                ->references('id_perso')->on('personnage')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('aptitude');
    }
};
