<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('materiaux', function (Blueprint $table) {
            $table->increments('id_materiaux');
            $table->string('nom_mat', 100);
            $table->string('slug', 100)->unique();
            $table->text('descri_mat')->nullable();
            $table->unsignedInteger('fid_typeM');
            $table->unsignedInteger('fid_rareté');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('materiaux');
    }
};
