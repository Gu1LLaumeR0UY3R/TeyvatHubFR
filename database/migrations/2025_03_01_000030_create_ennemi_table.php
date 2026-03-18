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
            $table->string('nom_ennemi', 100);
            $table->string('slug', 100)->unique();
            $table->text('descri_enn')->nullable();
            $table->unsignedInteger('fid_typeEnne');
            $table->unsignedInteger('fid_element')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ennemi');
    }
};
