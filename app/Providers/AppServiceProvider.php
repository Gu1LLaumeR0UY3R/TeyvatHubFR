<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Eloquent\Relations\Relation;
use App\Models\{
    Personnage, Nation, Plat, Materiaux, Ennemi, Ingredient,
    SousRegion, Elements, TypeArme, Arme, Evenement, Animal,
    Etoile, Chronologie
};

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // MorphMap pour la table photo (relation polymorphique)
        Relation::morphMap([
            'personnage'   => Personnage::class,
            'arme'         => Arme::class,
            'ennemi'       => Ennemi::class,
            'nation'       => Nation::class,
            'animal'       => Animal::class,
            'plat'         => Plat::class,
            'ingredient'   => Ingredient::class,
            'materiaux'    => Materiaux::class,
            'elements'     => Elements::class,
            'typearme'     => TypeArme::class,
            'sous_region'  => SousRegion::class,
            'etoile'       => Etoile::class,
            'evenement'    => Evenement::class,
            'chronologie'  => Chronologie::class,
        ]);
    }
}
