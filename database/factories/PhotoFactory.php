<?php

namespace Database\Factories;

use App\Models\Photo;
use Illuminate\Database\Eloquent\Factories\Factory;

class PhotoFactory extends Factory
{
    protected $model = Photo::class;

    public function definition(): array
    {
        return [
            'chemin_photo'  => 'photos/blog/featured/' . $this->faker->uuid() . '.jpg',
            'source_url'    => $this->faker->url(),
            'type'          => $this->faker->randomElement(['featured', 'inline']),
            'photoable_type' => null,
            'photoable_id'   => null,
        ];
    }
}
