<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('amitie', function (Blueprint $table) {
            $table->increments('id_amitie');
            $table->unsignedInteger('fid_demandeur');
            $table->unsignedInteger('fid_receveur');
            $table->string('statut', 20)->default('en_attente');
            $table->dateTime('created_at')->useCurrent();
            $table->unique(['fid_demandeur', 'fid_receveur'], 'uq_amitie');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('amitie');
    }
};
