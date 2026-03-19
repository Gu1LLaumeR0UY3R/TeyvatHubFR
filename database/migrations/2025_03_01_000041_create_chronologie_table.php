<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chronologie', function (Blueprint $table) {
            $table->increments('id_chrono');
            $table->string('titre', 200);
            $table->text('resume')->nullable();
            $table->string('periode', 100)->nullable();
            $table->integer('ordre')->default(0);
            $table->unsignedInteger('fid_region')->nullable();
            $table->timestamps();

            $table->foreign('fid_region', 'fk_chrono_region')
                ->references('id_region')->on('région')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chronologie');
    }
};
