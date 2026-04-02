<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('personnage_video', function (Blueprint $table) {
            $table->id('id_video');
            $table->unsignedInteger('fid_perso');
            $table->string('url_video', 255);
            $table->tinyInteger('ordre')->default(1);
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('fid_perso')
                ->references('id_perso')
                ->on('personnage')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('personnage_video');
    }
};
