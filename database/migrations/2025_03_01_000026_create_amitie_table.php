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
            $table->unsignedBigInteger('fid_demandeur');
            $table->unsignedBigInteger('fid_receveur');
            $table->string('statut', 20)->default('en_attente'); // en_attente | accepte | refuse
            $table->dateTime('created_at')->useCurrent();

            $table->foreign('fid_demandeur', 'fk_ami_demandeur')
                ->references('id')->on('users')
                ->onDelete('cascade');
            $table->foreign('fid_receveur', 'fk_ami_receveur')
                ->references('id')->on('users')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('amitie');
    }
};
