<?php

namespace App\Providers;

use App\Models\Admin;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Eloquent\Relations\Relation;
use App\Models\{
    Personnage, Nation, Plat, Materiaux, Ennemi, Ingredient,
    SousRegion, Elements, TypeArme, Arme, Evenement, Animal, Constellation,
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
            'constellation'=> Constellation::class,
        ]);

        // @adminCan('permission') … @endAdminCan
        Blade::if('adminCan', function (string $permission): bool {
            $adminId = session('admin_id');
            if (!$adminId) {
                return false;
            }
            if (session('admin_role') === 'super_admin') {
                return true;
            }
            $admin = Admin::find($adminId);
            return $admin ? $admin->can($permission) : false;
        });

        // Gate write_articles — compatible auth session manuelle
        // Le callback reçoit null quand aucun joueur n'est connecté via Laravel Auth.
        // On ignore le $user et on vérifie uniquement la session admin.
        Gate::define('write_articles', function ($user = null): bool {
            $adminId = session('admin_id');
            if (!$adminId) {
                return false;
            }
            if (session('admin_role') === 'super_admin') {
                return true;
            }
            $admin = Admin::find($adminId);
            return $admin !== null && $admin->can('articles');
        });
    }
}
