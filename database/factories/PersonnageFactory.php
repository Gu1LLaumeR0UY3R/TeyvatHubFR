<?php

namespace Database\Factories;

use App\Models\Personnage;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class PersonnageFactory extends Factory
{
    protected $model = Personnage::class;

    public function definition(): array
    {
        $nom = $this->faker->unique()->firstName() . ' ' . $this->faker->lastName();
        return [
            'nom_perso'     => $nom,
            'slug'          => Str::slug($nom),
            'affinite_perso'=> 0,
            'fid_TP'        => null,
            'fid_etoile'    => null,
            'fid_element'   => null,
            'fid_TArmes'    => null,
        ];
    }
}
