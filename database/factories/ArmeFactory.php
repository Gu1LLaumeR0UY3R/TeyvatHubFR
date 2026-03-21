<?php

namespace Database\Factories;

use App\Models\Arme;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ArmeFactory extends Factory
{
    protected $model = Arme::class;

    public function definition(): array
    {
        $nom = $this->faker->word() . ' Weapon';
        return [
            'nom_arme'      => $nom,
            'slug'          => Str::slug($nom),
            'fid_TArmes'    => \App\Models\TypeArme::firstOrCreate(['libelle_TArme' => 'Épée'])->id_TArmes,
            'fid_etoile'    => \App\Models\Etoile::firstOrCreate(['libelle' => '4★'])->id_etoile,
            'descr_arme'    => $this->faker->sentence(),
        ];
    }
}
