<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mate_ennemi', function (Blueprint $table) {
            $table->unsignedInteger('fid_materiaux');
            $table->unsignedInteger('fid_ennemi');

            $table->primary(['fid_materiaux', 'fid_ennemi']);
            $table->foreign('fid_materiaux', 'fk_me_mat')
                ->references('id_materiaux')->on('materiaux')
                ->onDelete('cascade');
            $table->foreign('fid_ennemi', 'fk_me_ennemi')
                ->references('id_ennemi')->on('ennemi')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mate_ennemi');
    }
};
