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
            $table->string('titre', 150);
            $table->text('resume');
            $table->string('periode', 100)->nullable();
            $table->unsignedInteger('ordre')->default(0);
            $table->unsignedInteger('fid_region')->nullable();
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->nullable()->useCurrentOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chronologie');
    }
};
