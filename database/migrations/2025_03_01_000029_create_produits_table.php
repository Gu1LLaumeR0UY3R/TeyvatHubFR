<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('produits', function (Blueprint $table) {
            $table->increments('id_produit');
            $table->string('libelle_produit', 100);
            $table->text('descri_produit')->nullable();
            $table->unsignedInteger('fid_region');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('produits');
    }
};
