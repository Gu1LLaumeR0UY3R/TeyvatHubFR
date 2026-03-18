<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('role', function (Blueprint $table) {
            $table->increments('id_role');
            $table->string('libelle_role', 50);
            $table->text('descri_role')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('role');
    }
};
