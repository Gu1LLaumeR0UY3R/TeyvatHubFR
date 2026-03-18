<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('personnage', function (Blueprint $table) {
            $table->increments('id_perso');
            $table->string('nom_perso', 100);
            $table->string('slug', 100)->unique();
            $table->string('affinite_perso', 50)->nullable();
            $table->unsignedInteger('fid_TP');
            $table->unsignedInteger('fid_etoile');
            $table->unsignedInteger('fid_element');
            $table->unsignedInteger('fid_TArmes');

            $table->foreign('fid_TP', 'fk_perso_type')
                ->references('id_TP')->on('type_perso');
            $table->foreign('fid_etoile', 'fk_perso_etoile')
                ->references('id_etoile')->on('etoile');
            $table->foreign('fid_element', 'fk_perso_element')
                ->references('id_element')->on('elements');
            $table->foreign('fid_TArmes', 'fk_perso_tarmes')
                ->references('id_TArmes')->on('type_armes');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('personnage');
    }
};
