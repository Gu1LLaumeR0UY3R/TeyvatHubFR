<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('photo', function (Blueprint $table) {
            $table->increments('id_photo');
            $table->string('chemin_photo', 255);
            $table->string('photoable_type', 100);
            $table->unsignedInteger('photoable_id');
            $table->timestamp('created_at')->useCurrent();
            $table->string('source_url', 500)->nullable();

            $table->index(['photoable_type', 'photoable_id'], 'idx_photoable');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('photo');
    }
};
