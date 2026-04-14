<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            [
                'libelle_role' => 'DPS',
                'descri_role'  => 'Personnage principal infligeant des dégâts élevés en première ligne.',
            ],
            [
                'libelle_role' => 'Sub-DPS',
                'descri_role'  => 'Personnage secondaire apportant des dégâts hors terrain grâce à ses compétences ou burst.',
            ],
            [
                'libelle_role' => 'Support',
                'descri_role'  => 'Personnage fournissant des soins, boucliers, buffs ou debuffs pour renforcer l\'équipe.',
            ],
            [
                'libelle_role' => 'Healer',
                'descri_role'  => 'Personnage spécialisé dans les soins pour maintenir la survie de l\'équipe.',
            ],
            [
                'libelle_role' => 'Shielder',
                'descri_role'  => 'Personnage générant des boucliers pour absorber les dégâts reçus.',
            ],
            [
                'libelle_role' => 'Buffer',
                'descri_role'  => 'Personnage augmentant les statistiques ou les dégâts des alliés.',
            ],
            [
                'libelle_role' => 'Debuffer',
                'descri_role'  => 'Personnage réduisant les résistances ou les statistiques des ennemis.',
            ],
            [
                'libelle_role' => 'Enabler',
                'descri_role'  => 'Personnage appliquant des éléments pour déclencher des réactions élémentaires.',
            ],
            [
                'libelle_role' => 'Burst DPS',
                'descri_role'  => 'Personnage entrant ponctuellement sur le terrain pour lancer un burst dévastateur.',
            ],
            [
                'libelle_role' => 'Exploration',
                'descri_role'  => 'Personnage dont les capacités facilitent l\'exploration du monde ouvert.',
            ],
        ];

        foreach ($roles as $role) {
            DB::table('role')->insertOrIgnore($role);
        }
    }
}
