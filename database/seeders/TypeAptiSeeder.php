<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TypeAptiSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            'Attaque normale',
            'Compétence élémentaire',
            'Burst élémentaire',
            'Technique de sprint',
            'Descente',
        ];

        foreach ($types as $libelle) {
            DB::table('type_apti')->insertOrIgnore(['libelle_Apti' => $libelle]);
        }
    }
}
