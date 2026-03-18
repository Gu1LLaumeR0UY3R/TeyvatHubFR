<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('type_apti', function (Blueprint $table) {
            $table->increments('id_TypeApti');
            $table->string('libelle_Apti', 50);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('type_apti');
    }
};
