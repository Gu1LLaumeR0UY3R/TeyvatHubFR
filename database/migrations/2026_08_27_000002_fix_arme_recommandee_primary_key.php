<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement(
            'ALTER TABLE personnage_arme_recommandee DROP PRIMARY KEY, ADD PRIMARY KEY (fid_perso, nom_build, fid_arme)'
        );
    }

    public function down(): void
    {
        DB::statement(
            'ALTER TABLE personnage_arme_recommandee DROP PRIMARY KEY, ADD PRIMARY KEY (fid_perso, fid_arme)'
        );
    }
};