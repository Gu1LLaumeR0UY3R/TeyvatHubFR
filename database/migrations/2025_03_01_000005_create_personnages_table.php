<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('personnages', function (Blueprint $table) {
            $table->id('id_perso');
            $table->string('nom_perso', 100);
            $table->string('slug', 100)->unique();
            $table->integer('affinite_perso')->default(0);
            $table->unsignedBigInteger('fid_TP')->nullable();
            $table->unsignedBigInteger('fid_etoile')->nullable();
            $table->unsignedBigInteger('fid_element')->nullable();
            $table->unsignedBigInteger('fid_TArmes')->nullable();
            $table->timestamps();

            $table->foreign('fid_TP', 'fk_perso_TP')
                ->references('id_TP')->on('type_perso')->onDelete('set null');
            $table->foreign('fid_etoile', 'fk_perso_etoile')
                ->references('id_etoile')->on('etoile')->onDelete('set null');
            $table->foreign('fid_element', 'fk_perso_element')
                ->references('id_element')->on('elements')->onDelete('set null');
            $table->foreign('fid_TArmes', 'fk_perso_TArmes')
                ->references('id_TArmes')->on('type_armes')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('personnages');
    }
};
