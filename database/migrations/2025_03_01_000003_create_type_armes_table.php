<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('type_armes', function (Blueprint $table) {
            $table->id('id_TArmes');
            $table->string('libelle_TArme', 50);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('type_armes');
    }
};
