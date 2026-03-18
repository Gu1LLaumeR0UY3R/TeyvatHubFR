<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('elements', function (Blueprint $table) {
            $table->increments('id_element');
            $table->string('libelle_element', 30);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('elements');
    }
};
