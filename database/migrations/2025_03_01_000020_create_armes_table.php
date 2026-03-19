<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('armes', function (Blueprint $table) {
            $table->increments('id_arme');
            $table->string('nom_arme', 150);
            $table->string('slug', 100)->unique();
            $table->text('descr_arme')->nullable();
            $table->string('nom_competence', 200)->nullable();
            $table->string('main_stat_type', 100)->nullable();
            $table->string('sub_stat_type', 100)->nullable();
            $table->unsignedInteger('fid_TArmes');
            $table->unsignedInteger('fid_etoile');

            $table->foreign('fid_TArmes', 'fk_arme_type')
                ->references('id_TArmes')->on('type_armes');
            $table->foreign('fid_etoile', 'fk_arme_etoile')
                ->references('id_etoile')->on('etoile');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('armes');
    }
};
