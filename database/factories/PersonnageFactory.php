<?php

namespace Database\Factories;

use App\Models\Personnage;
use Illuminate\Database\Eloquent\Factories\Factory;

class PersonnageFactory extends Factory
{
    protected $model = Personnage::class;

    public function definition(): array
    {
        $nom = $this->faker->unique()->firstName() . ' ' . $this->faker->lastName();
        return [
            'nom_perso'      => $nom,
            'affinite_perso' => null,
            'fid_TP'         => \App\Models\TypePerso::firstOrCreate(['libelle_TP'      => 'Personnage jouable'])->id_TP,
            'fid_etoile'     => \App\Models\Etoile::firstOrCreate(['libelle'            => '4★'])->id_etoile,
            'fid_element'    => \App\Models\Elements::firstOrCreate(['libelle_element'  => 'Pyro'])->id_element,
            'fid_TArmes'     => \App\Models\TypeArme::firstOrCreate(['libelle_TArme'    => 'Épée'])->id_TArmes,
        ];
    }
}
