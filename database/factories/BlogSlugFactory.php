<?php

namespace Database\Factories;

use App\Models\BlogSlug;
use Illuminate\Database\Eloquent\Factories\Factory;

class BlogSlugFactory extends Factory
{
    protected $model = BlogSlug::class;

    public function definition(): array
    {
        return [
            'slug_base' => $this->faker->unique()->word(),
        ];
    }
}
