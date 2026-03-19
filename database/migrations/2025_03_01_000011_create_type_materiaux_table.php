<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('type_materiaux', function (Blueprint $table) {
            $table->increments('id_typeM');
            $table->string('libelle_TypeM', 50);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('type_materiaux');
    }
};
