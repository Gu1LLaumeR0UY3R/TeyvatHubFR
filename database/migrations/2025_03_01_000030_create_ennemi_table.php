<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ennemi', function (Blueprint $table) {
            $table->increments('id_ennemi');
            $table->string('nom_ennemi', 150);
            $table->string('slug', 100)->unique();
            $table->text('descri_enn')->nullable();
            $table->unsignedInteger('fid_typeEnne');
            $table->unsignedInteger('fid_element')->nullable();

            $table->foreign('fid_typeEnne', 'fk_enn_type')
                ->references('id_typeEnnemi')->on('type_ennemi');
            $table->foreign('fid_element', 'fk_enn_element')
                ->references('id_element')->on('elements');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ennemi');
    }
};
