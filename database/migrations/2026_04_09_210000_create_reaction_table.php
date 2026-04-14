<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reaction', function (Blueprint $table) {
            $table->unsignedSmallInteger('id_reaction')->autoIncrement();
            $table->string('nom_reaction', 80)->unique();
            $table->string('slug', 80)->unique();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reaction');
    }
};
