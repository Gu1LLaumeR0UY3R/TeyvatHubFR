<?php

namespace Database\Factories;

use App\Models\BlogArticle;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class BlogArticleFactory extends Factory
{
    protected $model = BlogArticle::class;

    public function definition(): array
    {
        $title = $this->faker->unique()->sentence(4);
        
        $layout = [
            'blocks' => [
                [
                    'type' => 'heading',
                    'level' => 'h2',
                    'text' => $title,
                ],
                [
                    'type' => 'text',
                    'text' => $this->faker->paragraphs(3, true),
                    'align' => 'left',
                ],
            ],
        ];
        
        return [
            'titre_article'     => $title,
            'slug'              => Str::slug($title),
            'extrait'           => $this->faker->text(100),
            'layout_json'       => json_encode($layout),
            'statut'            => $this->faker->randomElement(['brouillon', 'publie']),
            'date_publication'  => $this->faker->dateTime(),
        ];
    }

    public function brouillon(): self
    {
        return $this->state(fn (array $attributes) => [
            'statut' => 'brouillon',
            'date_publication' => null,
        ]);
    }

    public function publie(): self
    {
        return $this->state(fn (array $attributes) => [
            'statut' => 'publie',
            'date_publication' => now(),
        ]);
    }
}
