<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PersonnageController;
use App\Http\Controllers\EnnemiController;
use App\Http\Controllers\ArmeController;
use App\Http\Controllers\MateriauxController;
use App\Http\Controllers\PlatController;
use App\Http\Controllers\AnimalController;
use App\Http\Controllers\IngredientController;
use App\Http\Controllers\HistoireController;
use App\Http\Controllers\NationController;
use App\Http\Controllers\ProfilController;
use App\Http\Controllers\ImportController;
use App\Http\Controllers\OutilsController;
use App\Http\Controllers\AmiController;
use App\Http\Controllers\RoulettePersonnageController;
use App\Http\Controllers\RouletteTeamController;
use App\Http\Controllers\MotusController;
use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Admin\AdminController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');

// Routes publiques encyclopédie
Route::get('/personnages', [PersonnageController::class, 'index'])->name('personnages.index');
Route::get('/personnages/{personnage}', [PersonnageController::class, 'show'])->name('personnages.show');
Route::get('/armes', [ArmeController::class, 'index'])->name('armes.index');
Route::get('/armes/{arme}', [ArmeController::class, 'show'])->name('armes.show');
Route::get('/ennemis', [EnnemiController::class, 'index'])->name('ennemis.index');
Route::get('/ennemis/{ennemi}', [EnnemiController::class, 'show'])->name('ennemis.show');
Route::get('/animaux', [AnimalController::class, 'index'])->name('animaux.index');
Route::get('/animaux/{animal}', [AnimalController::class, 'show'])->name('animaux.show');
Route::get('/cuisine', [PlatController::class, 'index'])->name('cuisine.index');
Route::get('/cuisine/{plat}', [PlatController::class, 'show'])->name('cuisine.show');
Route::get('/materiaux', [MateriauxController::class, 'index'])->name('materiaux.index');
Route::get('/materiaux/{materiaux}', [MateriauxController::class, 'show'])->name('materiaux.show');
Route::get('/ingredients', [IngredientController::class, 'index'])->name('ingredients.index');
Route::get('/ingredients/{ingredient}', [IngredientController::class, 'show'])->name('ingredients.show');
Route::get('/histoire', [HistoireController::class, 'index'])->name('histoire.index');
Route::get('/nations', [NationController::class, 'index'])->name('nations.index');
Route::get('/nations/{nation}', [NationController::class, 'show'])->name('nations.show');
// Alias legacy
Route::get('/histoire/nations', fn() => redirect()->route('nations.index'));
Route::get('/histoire/nations/{nation}', fn(string $nation) => redirect()->route('nations.show', $nation));

// Jeux publics
Route::get('/jeux/motus', [MotusController::class, 'index'])->name('jeux.motus');
Route::post('/jeux/motus/valider', [MotusController::class, 'valider'])->name('jeux.motus.valider');

// Outils publics
Route::get('/outils/personnage-du-jour', [OutilsController::class, 'personnageDuJour'])->name('outils.personnage-du-jour');
Route::get('/outils/quiz', [OutilsController::class, 'quiz'])->name('outils.quiz');
Route::post('/outils/quiz/resultat', [OutilsController::class, 'quizResultat'])->name('outils.quiz.resultat');

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Routes profil joueur
Route::middleware('auth')->prefix('profil')->group(function () {
    Route::get('/', [ProfilController::class, 'index'])->name('profil.index');
    Route::get('/personnages', [ProfilController::class, 'personnages'])->name('profil.personnages');
    Route::get('/armes', [ProfilController::class, 'armes'])->name('profil.armes');
    Route::get('/parametres', [ProfilController::class, 'parametres'])->name('profil.parametres');
    Route::post('/import-uid', [ImportController::class, 'importUID'])->name('profil.import-uid');
    Route::get('/amis', [AmiController::class, 'index'])->name('profil.amis');
    Route::post('/amis', [AmiController::class, 'store'])->name('profil.amis.store');
    Route::patch('/amis/{amitie}', [AmiController::class, 'update'])->name('profil.amis.update');
    Route::delete('/amis/{amitie}', [AmiController::class, 'destroy'])->name('profil.amis.destroy');
});

// Outils protégés joueur
Route::middleware('auth')->prefix('outils')->group(function () {
    Route::get('/roulette', [OutilsController::class, 'roulette'])->name('outils.roulette');
    Route::patch('/roulette/sauvegarder', [OutilsController::class, 'rouletteSauvegarder'])->name('outils.roulette.sauvegarder');
    Route::get('/roulette-personnage', [RoulettePersonnageController::class, 'index'])->name('outils.roulette-personnage');
    Route::post('/roulette-personnage/confirmer', [RoulettePersonnageController::class, 'confirmer'])->name('outils.roulette-personnage.confirmer');
    Route::get('/roulette-team', [RouletteTeamController::class, 'index'])->name('outils.roulette-team');
    Route::post('/roulette-team/generer', [RouletteTeamController::class, 'generer'])->name('outils.roulette-team.generer');
    Route::get('/team', [OutilsController::class, 'team'])->name('outils.team');
    Route::post('/team/generer', [OutilsController::class, 'teamGenerer'])->name('outils.team.generer');
    Route::get('/comparateur', [OutilsController::class, 'comparateur'])->name('outils.comparateur');
    Route::post('/comparateur/comparer', [OutilsController::class, 'comparateurComparer'])->name('outils.comparateur.comparer');
});

// Routes admin
Route::prefix('admin')->group(function () {
    Route::get('/login', [AdminAuthController::class, 'login'])->name('admin.login');
    Route::post('/login', [AdminAuthController::class, 'authenticate'])->name('admin.authenticate');
    Route::middleware('admin')->group(function () {
        Route::get('/', [AdminController::class, 'dashboard'])->name('admin.dashboard');
        Route::post('/logout', [AdminAuthController::class, 'logout'])->name('admin.logout');
        Route::post('/import-genshin', [AdminController::class, 'importGenshin'])->name('admin.import-genshin');
        Route::resource('/personnages', \App\Http\Controllers\Admin\PersonnageController::class)->names([
            'index'   => 'admin.personnages.index',
            'create'  => 'admin.personnages.create',
            'store'   => 'admin.personnages.store',
            'show'    => 'admin.personnages.show',
            'edit'    => 'admin.personnages.edit',
            'update'  => 'admin.personnages.update',
            'destroy' => 'admin.personnages.destroy',
        ]);
        Route::resource('/armes', \App\Http\Controllers\Admin\ArmeController::class)->names([
            'index'   => 'admin.armes.index',
            'create'  => 'admin.armes.create',
            'store'   => 'admin.armes.store',
            'show'    => 'admin.armes.show',
            'edit'    => 'admin.armes.edit',
            'update'  => 'admin.armes.update',
            'destroy' => 'admin.armes.destroy',
        ]);
        Route::resource('/ennemis', \App\Http\Controllers\Admin\EnnemiController::class)->names([
            'index'   => 'admin.ennemis.index',
            'create'  => 'admin.ennemis.create',
            'store'   => 'admin.ennemis.store',
            'show'    => 'admin.ennemis.show',
            'edit'    => 'admin.ennemis.edit',
            'update'  => 'admin.ennemis.update',
            'destroy' => 'admin.ennemis.destroy',
        ]);
        Route::resource('/animaux', \App\Http\Controllers\Admin\AnimalController::class)->names([
            'index'   => 'admin.animaux.index',
            'create'  => 'admin.animaux.create',
            'store'   => 'admin.animaux.store',
            'show'    => 'admin.animaux.show',
            'edit'    => 'admin.animaux.edit',
            'update'  => 'admin.animaux.update',
            'destroy' => 'admin.animaux.destroy',
        ]);
        Route::resource('/cuisine', \App\Http\Controllers\Admin\CuisineController::class)->names([
            'index'   => 'admin.cuisine.index',
            'create'  => 'admin.cuisine.create',
            'store'   => 'admin.cuisine.store',
            'show'    => 'admin.cuisine.show',
            'edit'    => 'admin.cuisine.edit',
            'update'  => 'admin.cuisine.update',
            'destroy' => 'admin.cuisine.destroy',
        ]);
        Route::resource('/nations', \App\Http\Controllers\Admin\NationController::class)->names([
            'index'   => 'admin.nations.index',
            'create'  => 'admin.nations.create',
            'store'   => 'admin.nations.store',
            'show'    => 'admin.nations.show',
            'edit'    => 'admin.nations.edit',
            'update'  => 'admin.nations.update',
            'destroy' => 'admin.nations.destroy',
        ]);
        Route::resource('/evenements', \App\Http\Controllers\Admin\EvenementController::class)->names([
            'index'   => 'admin.evenements.index',
            'create'  => 'admin.evenements.create',
            'store'   => 'admin.evenements.store',
            'show'    => 'admin.evenements.show',
            'edit'    => 'admin.evenements.edit',
            'update'  => 'admin.evenements.update',
            'destroy' => 'admin.evenements.destroy',
        ]);
        Route::resource('/chronologie', \App\Http\Controllers\Admin\ChronologieController::class)->names([
            'index'   => 'admin.chronologie.index',
            'create'  => 'admin.chronologie.create',
            'store'   => 'admin.chronologie.store',
            'show'    => 'admin.chronologie.show',
            'edit'    => 'admin.chronologie.edit',
            'update'  => 'admin.chronologie.update',
            'destroy' => 'admin.chronologie.destroy',
        ]);
        Route::patch('/chronologie/{chronologie}/ordre', [\App\Http\Controllers\Admin\ChronologieController::class, 'updateOrdre'])->name('admin.chronologie.ordre');
        Route::resource('/roles', \App\Http\Controllers\Admin\RoleController::class)->names([
            'index'   => 'admin.roles.index',
            'create'  => 'admin.roles.create',
            'store'   => 'admin.roles.store',
            'show'    => 'admin.roles.show',
            'edit'    => 'admin.roles.edit',
            'update'  => 'admin.roles.update',
            'destroy' => 'admin.roles.destroy',
        ]);
        Route::resource('/utilisateurs', \App\Http\Controllers\Admin\UtilisateurController::class)->names([
            'index'   => 'admin.utilisateurs.index',
            'create'  => 'admin.utilisateurs.create',
            'store'   => 'admin.utilisateurs.store',
            'show'    => 'admin.utilisateurs.show',
            'edit'    => 'admin.utilisateurs.edit',
            'update'  => 'admin.utilisateurs.update',
            'destroy' => 'admin.utilisateurs.destroy',
        ]);
        Route::post('/utilisateurs/{utilisateur}/bannir', [\App\Http\Controllers\Admin\UtilisateurController::class, 'bannir'])->name('admin.utilisateurs.bannir');
        Route::post('/utilisateurs/{utilisateur}/debannir', [\App\Http\Controllers\Admin\UtilisateurController::class, 'debannir'])->name('admin.utilisateurs.debannir');
    });
});

require __DIR__.'/auth.php';
