<?php

use Illuminate\Support\Facades\Route;
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
use App\Http\Controllers\Admin\AdminTwoFactorChallengeController;
use App\Http\Controllers\Admin\AdminTwoFactorController;
use App\Http\Controllers\ArticleController;
use App\Http\Controllers\Admin\ArticleController as AdminArticleController;
use App\Http\Controllers\ImprovementVoteController;
use App\Http\Controllers\SurveyResponseController;
use App\Http\Controllers\Auth\TwoFactorController;
use App\Http\Controllers\Admin\ActivityLogController;
use App\Http\Controllers\Admin\SnapshotRestoreController;

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
// Articles publics
Route::get('/articles', [ArticleController::class, 'index'])->name('articles.index');
Route::get('/articles/{article}', [ArticleController::class, 'show'])->name('articles.show');

// Alias legacy
Route::get('/histoire/nations', fn() => redirect()->route('nations.index'));
Route::get('/histoire/nations/{nation}', fn(string $nation) => redirect()->route('nations.show', $nation));

// Routes joueur : votes + questionnaires
Route::middleware(['auth', '2fa.user'])->group(function () {
    Route::post('/amelioration/{improvement}/vote', [ImprovementVoteController::class, 'toggle'])
         ->name('improvement.vote');
    Route::post('/surveys/{survey}/respond', [SurveyResponseController::class, 'store'])
         ->name('survey.respond');
});

// Jeux publics
Route::get('/jeux/motus', [MotusController::class, 'index'])->name('jeux.motus');
Route::post('/jeux/motus/valider', [MotusController::class, 'valider'])->name('jeux.motus.valider');

// Outils publics
Route::get('/outils/personnage-du-jour', [OutilsController::class, 'personnageDuJour'])->name('outils.personnage-du-jour');
Route::get('/outils/quiz', [OutilsController::class, 'quiz'])->name('outils.quiz');
Route::post('/outils/quiz/resultat', [OutilsController::class, 'quizResultat'])->name('outils.quiz.resultat');

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified', '2fa.user'])->name('dashboard');

Route::middleware(['auth', '2fa.user'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::post('/profile/two-factor/enable', [TwoFactorController::class, 'enable'])->name('twofactor.enable');
    Route::post('/profile/two-factor/disable', [TwoFactorController::class, 'disable'])->name('twofactor.disable');
});

// Routes profil joueur
Route::middleware(['auth', '2fa.user'])->prefix('profil')->group(function () {
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
Route::middleware(['auth', '2fa.user'])->prefix('outils')->group(function () {
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
    Route::get('/two-factor/challenge', [AdminTwoFactorChallengeController::class, 'show'])->name('admin.twofactor.challenge');
    Route::post('/two-factor/challenge', [AdminTwoFactorChallengeController::class, 'verify'])->name('admin.twofactor.challenge.verify');

    Route::middleware(['admin', '2fa.admin'])->group(function () {
        // ─── Accès libre à tout admin connecté ───────────────────────────
        Route::get('/', [AdminController::class, 'dashboard'])->name('admin.dashboard');
        Route::post('/logout', [AdminAuthController::class, 'logout'])->name('admin.logout');
        Route::get('/security/two-factor', [AdminTwoFactorController::class, 'edit'])->name('admin.twofactor.settings');
        Route::post('/security/two-factor/enable', [AdminTwoFactorController::class, 'enable'])->name('admin.twofactor.enable');
        Route::post('/security/two-factor/disable', [AdminTwoFactorController::class, 'disable'])->name('admin.twofactor.disable');
        Route::get('/google-drive/browse', [\App\Http\Controllers\Admin\GoogleDriveController::class, 'browse'])->name('admin.google-drive.browse');

        // ─── Import API (permission: import) ─────────────────────────────
        Route::middleware('admin.can:import')->group(function () {
            Route::post('/import-genshin', [AdminController::class, 'importGenshin'])->name('admin.import-genshin');
        });

        // ─── Encyclopédie (permission: encyclopedie) ──────────────────────
        Route::middleware('admin.can:encyclopedie')->group(function () {
            Route::patch('/personnages/bulk-update', [\App\Http\Controllers\Admin\PersonnageController::class, 'bulkUpdate'])->name('admin.personnages.bulk-update');
            Route::resource('/personnages', \App\Http\Controllers\Admin\PersonnageController::class)->names([
                'index'   => 'admin.personnages.index',
                'create'  => 'admin.personnages.create',
                'store'   => 'admin.personnages.store',
                'show'    => 'admin.personnages.show',
                'edit'    => 'admin.personnages.edit',
                'update'  => 'admin.personnages.update',
                'destroy' => 'admin.personnages.destroy',
            ]);
            // Routes AJAX blocs personnage
            Route::prefix('personnages/{personnage:slug}/block')->group(function () {
                Route::put('main-zone', [\App\Http\Controllers\Admin\PersonnageBlockController::class, 'updateMainZone'])->name('admin.personnage.block.main-zone.update');
                Route::post('main-zone/upload-image', [\App\Http\Controllers\Admin\PersonnageBlockController::class, 'uploadImage'])->name('admin.personnage.block.main-zone.upload');
                Route::get('main-zone/backgrounds', [\App\Http\Controllers\Admin\PersonnageBlockController::class, 'getBackgroundsByNation'])->name('admin.personnage.block.main-zone.backgrounds');
                Route::put('armes-recommandees', [\App\Http\Controllers\Admin\PersonnageBlockController::class, 'updateArmesRecommandees'])->name('admin.personnage.block.armes.update');
                Route::delete('armes-recommandees/{id_arme}', [\App\Http\Controllers\Admin\PersonnageBlockController::class, 'deleteArmeRecommandee'])->name('admin.personnage.block.armes.delete');
                Route::put('artefacts-recommandes', [\App\Http\Controllers\Admin\PersonnageBlockController::class, 'updateArtefactsRecommandees'])->name('admin.personnage.block.artefacts.update');
                Route::delete('artefacts-recommandes/{id_build}', [\App\Http\Controllers\Admin\PersonnageBlockController::class, 'deleteArtefactRecommande'])->name('admin.personnage.block.artefacts.delete');
                Route::put('stats-recommandees', [\App\Http\Controllers\Admin\PersonnageBlockController::class, 'updateStatsRecommandees'])->name('admin.personnage.block.stats.update');
                Route::put('constellations', [\App\Http\Controllers\Admin\PersonnageBlockController::class, 'updateConstellations'])->name('admin.personnage.block.constellations.update');
                Route::post('constellations/upload-image', [\App\Http\Controllers\Admin\PersonnageBlockController::class, 'uploadConstellationImage'])->name('admin.personnage.block.constellations.upload');
                Route::post('constellation-map', [\App\Http\Controllers\Admin\PersonnageBlockController::class, 'updateConstellationMap'])->name('admin.personnage.block.constellation-map.update');
                Route::put('competences', [\App\Http\Controllers\Admin\PersonnageBlockController::class, 'updateCompetences'])->name('admin.personnage.block.competences.update');
                Route::put('histoires', [\App\Http\Controllers\Admin\PersonnageBlockController::class, 'updateHistoires'])->name('admin.personnage.block.histoires.update');
                Route::post('competences/upload-image', [\App\Http\Controllers\Admin\PersonnageBlockController::class, 'uploadAptitudeImage'])->name('admin.personnage.block.competences.upload');
                Route::post('teams', [\App\Http\Controllers\Admin\PersonnageBlockController::class, 'storeTeam'])->name('admin.personnage.block.teams.store');
                Route::put('teams/{id_team}', [\App\Http\Controllers\Admin\PersonnageBlockController::class, 'updateTeam'])->name('admin.personnage.block.teams.update');
                Route::get('teams/{id_team}/aptitudes', [\App\Http\Controllers\Admin\PersonnageBlockController::class, 'getTeamAptitudes'])->name('admin.personnage.block.teams.aptitudes');
                Route::patch('teams/{id_team}/rotation', [\App\Http\Controllers\Admin\PersonnageBlockController::class, 'updateTeamRotation'])->name('admin.personnage.block.teams.rotation.update');
                Route::delete('teams/{id_team}', [\App\Http\Controllers\Admin\PersonnageBlockController::class, 'deleteTeam'])->name('admin.personnage.block.teams.delete');
                Route::patch('order', [\App\Http\Controllers\Admin\PersonnageBlockController::class, 'updateBlockOrder'])->name('admin.personnage.block.order');
            });
            Route::patch('/armes/bulk-update', [\App\Http\Controllers\Admin\ArmeController::class, 'bulkUpdate'])->name('admin.armes.bulk-update');
            Route::resource('/armes', \App\Http\Controllers\Admin\ArmeController::class)->names([
                'index'   => 'admin.armes.index',
                'create'  => 'admin.armes.create',
                'store'   => 'admin.armes.store',
                'show'    => 'admin.armes.show',
                'edit'    => 'admin.armes.edit',
                'update'  => 'admin.armes.update',
                'destroy' => 'admin.armes.destroy',
            ]);
            Route::patch('/artefacts/bulk-update', [\App\Http\Controllers\Admin\ArtefactController::class, 'bulkUpdate'])->name('admin.artefacts.bulk-update');
            Route::resource('/artefacts', \App\Http\Controllers\Admin\ArtefactController::class)->except(['show'])->names([
                'index'   => 'admin.artefacts.index',
                'create'  => 'admin.artefacts.create',
                'store'   => 'admin.artefacts.store',
                'edit'    => 'admin.artefacts.edit',
                'update'  => 'admin.artefacts.update',
                'destroy' => 'admin.artefacts.destroy',
            ]);
            Route::patch('/ennemis/bulk-update', [\App\Http\Controllers\Admin\EnnemiController::class, 'bulkUpdate'])->name('admin.ennemis.bulk-update');
            Route::resource('/ennemis', \App\Http\Controllers\Admin\EnnemiController::class)->names([
                'index'   => 'admin.ennemis.index',
                'create'  => 'admin.ennemis.create',
                'store'   => 'admin.ennemis.store',
                'show'    => 'admin.ennemis.show',
                'edit'    => 'admin.ennemis.edit',
                'update'  => 'admin.ennemis.update',
                'destroy' => 'admin.ennemis.destroy',
            ]);
            Route::patch('/animaux/bulk-update', [\App\Http\Controllers\Admin\AnimalController::class, 'bulkUpdate'])->name('admin.animaux.bulk-update');
            Route::resource('/animaux', \App\Http\Controllers\Admin\AnimalController::class)->names([
                'index'   => 'admin.animaux.index',
                'create'  => 'admin.animaux.create',
                'store'   => 'admin.animaux.store',
                'show'    => 'admin.animaux.show',
                'edit'    => 'admin.animaux.edit',
                'update'  => 'admin.animaux.update',
                'destroy' => 'admin.animaux.destroy',
            ]);
            Route::patch('/cuisine/bulk-update', [\App\Http\Controllers\Admin\CuisineController::class, 'bulkUpdate'])->name('admin.cuisine.bulk-update');
            Route::resource('/cuisine', \App\Http\Controllers\Admin\CuisineController::class)->names([
                'index'   => 'admin.cuisine.index',
                'create'  => 'admin.cuisine.create',
                'store'   => 'admin.cuisine.store',
                'show'    => 'admin.cuisine.show',
                'edit'    => 'admin.cuisine.edit',
                'update'  => 'admin.cuisine.update',
                'destroy' => 'admin.cuisine.destroy',
            ]);
            Route::patch('/nations/bulk-update', [\App\Http\Controllers\Admin\NationController::class, 'bulkUpdate'])->name('admin.nations.bulk-update');
            Route::resource('/nations', \App\Http\Controllers\Admin\NationController::class)->names([
                'index'   => 'admin.nations.index',
                'create'  => 'admin.nations.create',
                'store'   => 'admin.nations.store',
                'show'    => 'admin.nations.show',
                'edit'    => 'admin.nations.edit',
                'update'  => 'admin.nations.update',
                'destroy' => 'admin.nations.destroy',
            ]);
            // Alias legacy: conserver les anciennes routes admin.regions.*
            Route::patch('/regions/bulk-update', [\App\Http\Controllers\Admin\NationController::class, 'bulkUpdate'])->name('admin.regions.bulk-update');
            Route::resource('/regions', \App\Http\Controllers\Admin\NationController::class)->names([
                'index'   => 'admin.regions.index',
                'create'  => 'admin.regions.create',
                'store'   => 'admin.regions.store',
                'show'    => 'admin.regions.show',
                'edit'    => 'admin.regions.edit',
                'update'  => 'admin.regions.update',
                'destroy' => 'admin.regions.destroy',
            ]);
            Route::patch('/roles/bulk-update', [\App\Http\Controllers\Admin\RoleController::class, 'bulkUpdate'])->name('admin.roles.bulk-update');
            Route::resource('/roles', \App\Http\Controllers\Admin\RoleController::class)->names([
                'index'   => 'admin.roles.index',
                'create'  => 'admin.roles.create',
                'store'   => 'admin.roles.store',
                'show'    => 'admin.roles.show',
                'edit'    => 'admin.roles.edit',
                'update'  => 'admin.roles.update',
                'destroy' => 'admin.roles.destroy',
            ]);
            // Références (éléments, types, étoiles, réactions…)
            Route::get('/references/{type}', [\App\Http\Controllers\Admin\ReferenceController::class, 'index'])->name('admin.references.index');
            Route::post('/references/{type}', [\App\Http\Controllers\Admin\ReferenceController::class, 'store'])->name('admin.references.store');
            Route::patch('/references/{type}/{id}', [\App\Http\Controllers\Admin\ReferenceController::class, 'update'])->name('admin.references.update');
            Route::delete('/references/{type}/{id}', [\App\Http\Controllers\Admin\ReferenceController::class, 'destroy'])->name('admin.references.destroy');
        });

        // ─── Articles (permission: articles) ────────────────────────────
        Route::middleware('admin.can:articles')->group(function () {
            Route::post('/upload/image', [AdminArticleController::class, 'uploadImage'])->name('admin.upload.image');
            Route::post('/articles/{article}/autosave', [AdminArticleController::class, 'autosave'])->name('admin.articles.autosave');
            Route::get('/articles/calendar', [AdminArticleController::class, 'calendar'])->name('admin.articles.calendar');
            Route::resource('/articles', AdminArticleController::class)->except(['show'])->names([
                'index'   => 'admin.articles.index',
                'create'  => 'admin.articles.create',
                'store'   => 'admin.articles.store',
                'edit'    => 'admin.articles.edit',
                'update'  => 'admin.articles.update',
                'destroy' => 'admin.articles.destroy',
            ]);
        });

        // ─── Événements & Chronologie (permission: evenements) ────────────
        Route::middleware('admin.can:evenements')->group(function () {
            Route::resource('/evenements', \App\Http\Controllers\Admin\EvenementController::class)->names([
                'index'   => 'admin.evenements.index',
                'create'  => 'admin.evenements.create',
                'store'   => 'admin.evenements.store',
                'show'    => 'admin.evenements.show',
                'edit'    => 'admin.evenements.edit',
                'update'  => 'admin.evenements.update',
                'destroy' => 'admin.evenements.destroy',
            ]);
            Route::patch('/chronologie/bulk-update', [\App\Http\Controllers\Admin\ChronologieController::class, 'bulkUpdate'])->name('admin.chronologie.bulk-update');
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
        });

        // ─── Utilisateurs (permission: utilisateurs) ──────────────────────
        Route::middleware('admin.can:utilisateurs')->group(function () {
            Route::patch('/utilisateurs/bulk-update', [\App\Http\Controllers\Admin\UtilisateurController::class, 'bulkUpdate'])->name('admin.utilisateurs.bulk-update');
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

        // ─── Gestion des admins (permission: admins) ──────────────────────
        Route::middleware('admin.can:admins')->prefix('admins')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\AdminManageController::class, 'index'])->name('admin.admins.index');
            Route::get('/create', [\App\Http\Controllers\Admin\AdminManageController::class, 'create'])->name('admin.admins.create');
            Route::post('/', [\App\Http\Controllers\Admin\AdminManageController::class, 'store'])->name('admin.admins.store');
            Route::get('/{admin}/edit', [\App\Http\Controllers\Admin\AdminManageController::class, 'edit'])->name('admin.admins.edit');
            Route::put('/{admin}', [\App\Http\Controllers\Admin\AdminManageController::class, 'update'])->name('admin.admins.update');
            Route::delete('/{admin}', [\App\Http\Controllers\Admin\AdminManageController::class, 'destroy'])->name('admin.admins.destroy');
        });

        // ─── Résultats questionnaires (permission: articles) ─────────────
        Route::middleware('admin.can:articles')->group(function () {
            Route::get('/articles/{article}/survey-results', [AdminArticleController::class, 'surveyResults'])
                 ->name('admin.articles.survey-results');
        });

        // ─── Logs d'activité (permission: manage_logs — super_admin uniquement via Spatie) ───
        Route::middleware('admin.can:manage_logs')->group(function () {
            Route::get('/logs', [ActivityLogController::class, 'index'])->name('admin.logs.index');
            Route::get('/logs/{scope}/{category}', [ActivityLogController::class, 'show'])->name('admin.logs.show');
        });

        // ─── Restauration snapshots (super_admin uniquement) ─────────────
        Route::middleware('admin.super')->group(function () {
            Route::get('/snapshots', [SnapshotRestoreController::class, 'globalIndex'])
                ->name('admin.snapshots.index');
            Route::get('/personnages/{personnage:slug}/snapshots', [SnapshotRestoreController::class, 'index'])
                ->name('admin.personnages.snapshots.index');
            Route::get('/snapshots/{snapshot}', [SnapshotRestoreController::class, 'show'])
                ->name('admin.snapshots.show');
            Route::post('/snapshots/{snapshot}/restore', [SnapshotRestoreController::class, 'restore'])
                ->name('admin.snapshots.restore');
        });
    });
});

require __DIR__.'/auth.php';
