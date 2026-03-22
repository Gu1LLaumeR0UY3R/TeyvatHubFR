<?php

namespace App\Services;

use App\Models\Personnage;
use Illuminate\Support\Collection;

class TeamCompositionService
{
    const REACTIONS = [
        'Vaporize'      => ['Pyro', 'Hydro'],
        'Melt'          => ['Pyro', 'Cryo'],
        'Overload'      => ['Pyro', 'Electro'],
        'Freeze'        => ['Hydro', 'Cryo'],
        'Superconduct'  => ['Cryo', 'Electro'],
        'Bloom'         => ['Hydro', 'Dendro'],
        'Hyperbloom'    => ['Hydro', 'Dendro'],
        'Burgeon'       => ['Hydro', 'Dendro'],
        'Quicken'       => ['Electro', 'Dendro'],
        'Aggravate'     => ['Electro', 'Dendro'],
        'Spread'        => ['Electro', 'Dendro'],
        'Swirl'         => ['Anemo'],
        'Crystallize'   => ['Geo'],
    ];

    public function buildRandom(Collection $personnages): Collection
    {
        return $personnages->shuffle()->take(4);
    }

    public function buildByReaction(Collection $personnages, string $reaction): Collection
    {
        $elements = self::REACTIONS[$reaction] ?? [];

        if (empty($elements)) {
            return $this->buildRandom($personnages);
        }

        if (count($elements) === 1) {
            // Swirl / Crystallize : 1 du bon élément + autres au hasard
            $with    = $personnages->filter(fn($p) => $p->element?->libelle_element === $elements[0]);
            $without = $personnages->filter(fn($p) => $p->element?->libelle_element !== $elements[0]);
            return $with->shuffle()->take(1)->merge($without->shuffle()->take(3))->take(4);
        }

        // Deux éléments requis
        $el1 = $personnages->filter(fn($p) => in_array($p->element?->libelle_element, $elements));
        $others = $personnages->filter(fn($p) => !in_array($p->element?->libelle_element, $elements));
        return $el1->shuffle()->take(2)->merge($others->shuffle()->take(2))->take(4);
    }

    public function buildByElement(Collection $personnages, string $element): Collection
    {
        return $personnages->filter(
            fn($p) => strcasecmp($p->element?->libelle_element ?? '', $element) === 0
        )->shuffle()->take(4);
    }

    public function hasEnoughPersonnages(Collection $team, int $required = 1): bool
    {
        return $team->count() >= $required;
    }
}
