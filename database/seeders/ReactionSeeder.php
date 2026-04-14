<?php

namespace Database\Seeders;

use App\Models\Reaction;
use Illuminate\Database\Seeder;

class ReactionSeeder extends Seeder
{
    public function run(): void
    {
        $reactions = [
            'Vaporize',
            'Melt',
            'Freeze',
            'Shattered',
            'Superconduct',
            'Overloaded',
            'Electro-Charged',
            'Swirl',
            'Crystallize',
            'Burning',
            'Quicken',
            'Aggravate',
            'Spread',
            'Bloom',
            'Hyperbloom',
            'Burgeon',
        ];

        foreach ($reactions as $nom) {
            Reaction::firstOrCreate(
                ['nom_reaction' => $nom],
                ['slug' => \Illuminate\Support\Str::slug($nom)]
            );
        }
    }
}
