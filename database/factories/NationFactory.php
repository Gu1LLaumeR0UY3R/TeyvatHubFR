<?php

namespace Database\Factories;

use App\Models\Nation;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class NationFactory extends Factory
{
    protected $model = Nation::class;

    public function definition(): array
    {
        $nom = $this->faker->unique()->country();
        return [
            'nom_region'    => $nom,
            'slug'          => Str::slug($nom),
            'descri_region' => $this->faker->paragraph(),
        ];
    }
}
