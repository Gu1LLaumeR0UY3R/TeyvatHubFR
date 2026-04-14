<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('histoire', function (Blueprint $table): void {
            $table->increments('id_histoire');
            $table->unsignedInteger('fid_perso');
            $table->text('histoire');
            $table->unsignedTinyInteger('ordre')->default(1);

            $table->foreign('fid_perso', 'fk_histoire_perso')
                ->references('id_perso')
                ->on('personnage')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('histoire', function (Blueprint $table): void {
            $table->dropForeign('fk_histoire_perso');
        });

        Schema::dropIfExists('histoire');
    }
};
