<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\PersonnageController;
use App\Http\Controllers\EnnemiController;
use App\Http\Controllers\MateriauxController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');

// Routes publiques encyclopédie
Route::get('/personnages', [PersonnageController::class, 'index'])->name('personnages.index');
Route::get('/personnages/{personnage:slug}', [PersonnageController::class, 'show'])->name('personnages.show');
Route::get('/armes', fn() => abort(404))->name('armes.index');
Route::get('/armes/{slug}', fn() => abort(404))->name('armes.show');
Route::get('/ennemis', [EnnemiController::class, 'index'])->name('ennemis.index');
Route::get('/ennemis/{ennemi:slug}', [EnnemiController::class, 'show'])->name('ennemis.show');
Route::get('/animaux', fn() => abort(404))->name('animaux.index');
Route::get('/animaux/{slug}', fn() => abort(404))->name('animaux.show');
Route::get('/cuisine', fn() => abort(404))->name('cuisine.index');
Route::get('/cuisine/{slug}', fn() => abort(404))->name('cuisine.show');
Route::get('/materiaux', [MateriauxController::class, 'index'])->name('materiaux.index');
Route::get('/materiaux/{materiaux:slug}', [MateriauxController::class, 'show'])->name('materiaux.show');
Route::get('/ingredients', fn() => abort(404))->name('ingredients.index');
Route::get('/ingredients/{slug}', fn() => abort(404))->name('ingredients.show');
Route::get('/histoire', fn() => abort(404))->name('histoire.index');
Route::get('/histoire/regions', fn() => abort(404))->name('regions.index');
Route::get('/histoire/regions/{slug}', fn() => abort(404))->name('regions.show');

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Routes profil joueur (stubs — seront implémentées dans les issues #31+)
Route::middleware('auth')->prefix('profil')->group(function () {
    Route::get('/', fn() => abort(404))->name('profil.index');
    Route::get('/personnages', fn() => abort(404))->name('profil.personnages');
    Route::get('/armes', fn() => abort(404))->name('profil.armes');
    Route::get('/parametres', fn() => abort(404))->name('profil.parametres');
    Route::get('/amis', fn() => abort(404))->name('profil.amis');
});

require __DIR__.'/auth.php';
