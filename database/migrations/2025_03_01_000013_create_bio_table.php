<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bio', function (Blueprint $table) {
            $table->increments('id_bio');
            $table->string('titre_bio', 100);
            $table->text('descri_bio');
            $table->unsignedInteger('fid_perso')->unique();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bio');
    }
};
