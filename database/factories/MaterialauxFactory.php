<?php

namespace Database\Factories;

use App\Models\Materiaux;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class MaterialauxFactory extends Factory
{
    protected $model = Materiaux::class;

    public function definition(): array
    {
        $nom = $this->faker->unique()->word() . ' Material';
        return [
            'nom_mat'   => $nom,
            'slug'      => Str::slug($nom),
            'fid_typeM' => \App\Models\TypeMateriaux::firstOrCreate(['libelle_typeM' => 'Ascension'])->id_typeM,
            'fid_rareté' => \App\Models\Rarete::firstOrCreate(['libelle_rarete' => '3★'])->id_rareté,
            'descri_mat' => $this->faker->sentence(),
        ];
    }
}
