<?php

namespace Database\Factories;

use App\Models\Evenement;
use Illuminate\Database\Eloquent\Factories\Factory;

class EvenementFactory extends Factory
{
    protected $model = Evenement::class;

    public function definition(): array
    {
        $debut = $this->faker->dateTimeBetween('-7 days', '+30 days');
        $fin   = $this->faker->dateTimeBetween($debut, '+60 days');

        return [
            'titre'        => $this->faker->sentence(4),
            'descri_courte'=> $this->faker->sentence(10),
            'description'  => $this->faker->paragraph(),
            'date_debut'   => $debut->format('Y-m-d'),
            'date_fin'     => $fin->format('Y-m-d'),
        ];
    }
}
