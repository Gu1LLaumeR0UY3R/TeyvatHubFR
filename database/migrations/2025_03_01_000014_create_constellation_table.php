<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('constellation', function (Blueprint $table) {
            $table->increments('id_const');
            $table->string('titre_const', 100);
            $table->text('descri_const');
            $table->unsignedInteger('fid_perso');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('constellation');
    }
};
