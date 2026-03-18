<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('etoile', function (Blueprint $table) {
            $table->id('id_etoile');
            $table->string('libelle', 10);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('etoile');
    }
};
