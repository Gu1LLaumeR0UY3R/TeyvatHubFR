# TeyvatHub — Instructions GitHub Copilot

## Contexte du projet

TeyvatHub est un site fan encyclopédique pour Genshin Impact développé avec Laravel 12, Blade, Alpine.js, MySQL et Tailwind CSS.

---

## Stack technique

- **Backend** : Laravel 12, PHP 8.2+
- **Frontend** : Blade, Alpine.js, Tailwind CSS
- **Base de données** : MySQL 8.0+, InnoDB, utf8mb4
- **Authentification joueur** : Laravel Breeze (Blade)
- **Authentification admin** : Session manuelle séparée (table Admin isolée)
- **Upload fichiers** : Laravel Storage (storage/app/public/)
- **HTTP client** : Laravel Http facade
- **Tests** : PHPUnit (intégré Laravel)

---

## Structure des branches Git

```
main          ← production uniquement — merge depuis develop après validation complète
develop       ← intégration — toutes les features convergent ici

feat/encyclopedie   ← issues #1 à #30  (pages publiques encyclopédie)
feat/profil         ← issues #31 à #41 (profil joueur)
feat/outils         ← issues #42 à #48 (outils & social)
feat/admin          ← issues #49 à #56 (dashboard admin)
```

### Règles Git absolues

- Ne jamais commiter directement sur main ou develop
- Chaque issue = un commit sur la branche feature correspondante
- Quand une branche feature est terminée → Pull Request vers develop
- La PR nécessite que tous les tests PHPUnit passent
- Le merge sur main est manuel après validation complète sur develop

### Messages de commit

```
feat: issue #1 — Layout principal et header avec navigation
feat: issue #2 — Footer global
fix: issue #3 — Correction requête HomeController
test: issue #1 — Tests PHPUnit header et navigation
```

---

## Workflow par issue — étapes obligatoires

```
1.  Lire l'issue (titre, route, composant, corps, critères)
2.  Créer/modifier la migration si nécessaire
3.  Créer/modifier le(s) modèle(s) Eloquent avec toutes les relations
4.  Créer le contrôleur avec les méthodes requises
5.  Créer les vues Blade
6.  Déclarer les routes dans routes/web.php
7.  Écrire les tests PHPUnit couvrant TOUS les critères
8.  Lancer : php artisan test --filter=IssueXxxTest
9.  Si échec → corriger et relancer (max 3 tentatives)
10. Si 3 échecs → git checkout -- . puis recommencer depuis l'étape 1
11. Si succès → commit : feat: issue #X — titre
12. Demander à Amazon Q de lancer les tests de l'issue
13. Si Amazon Q confirme 0 échec → passer à l'issue suivante
14. Si Amazon Q signale des échecs → corriger et revenir à l'étape 8 (compte dans les 3 tentatives)
```

### Règle des 3 tentatives

- Tentative 1 : coder l'issue normalement
- Tentative 2 : analyser les erreurs et corriger
- Tentative 3 : réécrire complètement la partie qui échoue
- Après 3 échecs : `git checkout -- .` pour annuler tout, recommencer avec une approche différente

---

## Vérification par Amazon Q

Après chaque commit d'issue, Amazon Q (disponible dans VS Code) a un rôle unique : **lancer les tests et confirmer que tout passe**.

### Commande à exécuter

```bash
php artisan test --filter=IssueXxxTest
```

> Remplacer `IssueXxx` par le numéro de l'issue concernée (ex: `Issue7`).

### Ce qu'Amazon Q doit vérifier

- Tous les tests passent (0 échec, 0 erreur)
- Le nombre de tests correspond au nombre de critères d'acceptance de l'issue
- Les cas limites sont couverts (404, validation, accès non autorisé)

### Résultat

- **0 échec** → issue validée, passer à la suivante
- **1 échec ou plus** → signaler les tests qui échouent avec le message d'erreur, corriger et relancer (compte dans les 3 tentatives)

---

## Conventions BDD absolues

### Slugs

Toutes les entités principales ont un slug : Personnage, Armes, Ennemi, Animaux, Plat, Ingrédient, Materiaux, Région, Sous_Region.

```php
protected static function booted(): void
{
    static::creating(function ($model) {
        $model->slug = Str::slug($model->nom_xxx);
    });
}

public function getRouteKeyName(): string
{
    return 'slug';
}
```

Ne jamais utiliser l'id dans les routes publiques — toujours le slug.

### Photos polymorphiques

Une seule table Photo pour toutes les entités. Ne jamais créer de table Photo_xxx dédiée.

- Colonne `chemin_photo` : chemin local ou URL directe
- Colonne `source_url` : URL originale HoYoverse (VARCHAR 500)

```php
public function photos(): MorphMany
{
    return $this->morphMany(Photo::class, 'photoable');
}
```

```blade
<img src="{{ $entity->photos->first()?->source_url
         ?? $entity->photos->first()?->chemin_photo
         ?? asset('images/placeholder.webp') }}"
     alt="{{ $entity->nom_xxx }}">
```

### Passwords

```php
// Toujours
'mot_de_passe' => Hash::make($request->password)
Hash::check($request->password, $user->mot_de_passe)

// Jamais
bcrypt(), md5(), sha1(), password_hash()
```

### Clés étrangères

Nommage `fid_xxx` (ex: `fid_perso`, `fid_arme`, `fid_region`). Toujours déclarer avec CONSTRAINT nommées explicitement.

---

## Conventions routes

### Routes publiques

```php
Route::get('/personnages', [PersonnageController::class, 'index'])->name('personnages.index');
Route::get('/personnages/{slug}', [PersonnageController::class, 'show'])->name('personnages.show');
Route::get('/armes', [ArmeController::class, 'index'])->name('armes.index');
Route::get('/armes/{slug}', [ArmeController::class, 'show'])->name('armes.show');
Route::get('/ennemis', [EnnemiController::class, 'index'])->name('ennemis.index');
Route::get('/ennemis/{slug}', [EnnemiController::class, 'show'])->name('ennemis.show');
Route::get('/animaux', [AnimalController::class, 'index'])->name('animaux.index');
Route::get('/animaux/{slug}', [AnimalController::class, 'show'])->name('animaux.show');
Route::get('/cuisine', [PlatController::class, 'index'])->name('cuisine.index');
Route::get('/cuisine/{slug}', [PlatController::class, 'show'])->name('cuisine.show');
Route::get('/materiaux', [MateriauxController::class, 'index'])->name('materiaux.index');
Route::get('/materiaux/{slug}', [MateriauxController::class, 'show'])->name('materiaux.show');
Route::get('/ingredients', [IngredientController::class, 'index'])->name('ingredients.index');
Route::get('/ingredients/{slug}', [IngredientController::class, 'show'])->name('ingredients.show');
Route::get('/histoire', [HistoireController::class, 'index'])->name('histoire.index');
Route::get('/histoire/regions', [RegionController::class, 'index'])->name('regions.index');
Route::get('/histoire/regions/{slug}', [RegionController::class, 'show'])->name('regions.show');
Route::get('/outils/personnage-du-jour', [OutilsController::class, 'personnageDuJour'])->name('outils.personnage-du-jour');
Route::get('/outils/quiz', [OutilsController::class, 'quiz'])->name('outils.quiz');
Route::post('/outils/quiz/resultat', [OutilsController::class, 'quizResultat'])->name('outils.quiz.resultat');
```

### Routes protégées joueur

```php
Route::middleware('auth')->prefix('profil')->group(function () {
    Route::get('/', [ProfilController::class, 'index'])->name('profil.index');
    Route::get('/personnages', [ProfilController::class, 'personnages'])->name('profil.personnages');
    Route::get('/armes', [ProfilController::class, 'armes'])->name('profil.armes');
    Route::get('/parametres', [ProfilController::class, 'parametres'])->name('profil.parametres');
    Route::post('/import-uid', [ImportController::class, 'importUID'])->name('profil.import-uid');
    Route::get('/amis', [AmiController::class, 'index'])->name('profil.amis');
});

Route::middleware('auth')->prefix('outils')->group(function () {
    Route::get('/roulette', [OutilsController::class, 'roulette'])->name('outils.roulette');
    Route::patch('/roulette/sauvegarder', [OutilsController::class, 'rouletteSauvegarder'])->name('outils.roulette.sauvegarder');
    Route::get('/team', [OutilsController::class, 'team'])->name('outils.team');
    Route::post('/team/generer', [OutilsController::class, 'teamGenerer'])->name('outils.team.generer');
    Route::get('/comparateur', [OutilsController::class, 'comparateur'])->name('outils.comparateur');
});
```

### Routes admin

```php
Route::prefix('admin')->group(function () {
    Route::get('/login', [AdminAuthController::class, 'login'])->name('admin.login');
    Route::post('/login', [AdminAuthController::class, 'authenticate'])->name('admin.authenticate');
    Route::middleware('admin')->group(function () {
        Route::get('/', [AdminController::class, 'dashboard'])->name('admin.dashboard');
        Route::post('/logout', [AdminAuthController::class, 'logout'])->name('admin.logout');
        Route::post('/import-genshin', [AdminController::class, 'importGenshin'])->name('admin.import-genshin');
        Route::resource('/personnages', Admin\PersonnageController::class);
        Route::resource('/armes', Admin\ArmeController::class);
        Route::resource('/ennemis', Admin\EnnemiController::class);
        Route::resource('/animaux', Admin\AnimalController::class);
        Route::resource('/cuisine', Admin\CuisineController::class);
        Route::resource('/regions', Admin\RegionController::class);
        Route::resource('/evenements', Admin\EvenementController::class);
        Route::resource('/chronologie', Admin\ChronologieController::class);
        Route::resource('/roles', Admin\RoleController::class);
        Route::resource('/references', Admin\ReferenceController::class);
        Route::resource('/utilisateurs', Admin\UtilisateurController::class);
    });
});
```

---

## Conventions modèles Eloquent

```php
class Personnage extends Model
{
    protected $primaryKey = 'id_perso';
    public $timestamps = true;
    protected $fillable = ['nom_perso', 'slug', 'affinite_perso', 'fid_TP', 'fid_etoile', 'fid_element', 'fid_TArmes'];

    protected static function booted(): void
    {
        static::creating(fn($m) => $m->slug = Str::slug($m->nom_perso));
    }

    public function getRouteKeyName(): string { return 'slug'; }

    public function element(): BelongsTo      { return $this->belongsTo(Elements::class, 'fid_element', 'id_element'); }
    public function etoile(): BelongsTo       { return $this->belongsTo(Etoile::class, 'fid_etoile', 'id_etoile'); }
    public function typePerso(): BelongsTo    { return $this->belongsTo(TypePerso::class, 'fid_TP', 'id_TP'); }
    public function typeArme(): BelongsTo     { return $this->belongsTo(TypeArme::class, 'fid_TArmes', 'id_TArmes'); }
    public function bio(): HasOne             { return $this->hasOne(Bio::class, 'fid_perso', 'id_perso'); }
    public function aptitudes(): HasMany      { return $this->hasMany(Aptitude::class, 'fid_perso', 'id_perso'); }
    public function constellations(): HasMany { return $this->hasMany(Constellation::class, 'fid_perso', 'id_perso')->orderBy('id_const'); }
    public function specialite(): HasOne      { return $this->hasOne(Specialite::class, 'fid_perso', 'id_perso'); }
    public function roles(): BelongsToMany   { return $this->belongsToMany(Role::class, 'personnage_role', 'fid_perso', 'fid_role'); }
    public function photos(): MorphMany      { return $this->morphMany(Photo::class, 'photoable'); }
}
```

### Eager loading — obligatoire

```php
// BIEN — 2 requêtes
Personnage::with(['element', 'etoile', 'photos'])->paginate(20);

// MAL — N+1 (61 requêtes pour 20 personnages)
Personnage::paginate(20);
```

---

## Conventions controllers

### Index avec filtres et pagination

```php
public function index(Request $request): View
{
    $personnages = Personnage::with(['element', 'etoile', 'photos'])
        ->when($request->search, fn($q) => $q->where('nom_perso', 'LIKE', '%'.$request->search.'%'))
        ->when($request->element, fn($q) => $q->where('fid_element', $request->element))
        ->when($request->sort === 'rarete_desc', fn($q) => $q->orderBy('fid_etoile', 'desc'))
        ->orderBy('nom_perso')
        ->paginate(20)
        ->withQueryString(); // obligatoire pour conserver les filtres

    return view('personnages.index', compact('personnages'));
}
```

### Show avec Route Model Binding

```php
public function show(Personnage $personnage): View
{
    $personnage->load(['element', 'etoile', 'bio', 'aptitudes.typeApti', 'constellations', 'photos']);
    return view('personnages.show', compact('personnage'));
}
```

### Upload photo

```php
if ($request->hasFile('photo')) {
    if ($old = $entity->photos->first()) {
        if (!filter_var($old->chemin_photo, FILTER_VALIDATE_URL)) {
            Storage::delete($old->chemin_photo);
        }
        $entity->photos()->delete();
    }
    $path = $request->file('photo')->store('photos/personnages', 'public');
    $entity->photos()->create(['chemin_photo' => $path, 'source_url' => null]);
}
```

### Import depuis API externe

```php
$entity->photos()->updateOrCreate(
    ['photoable_type' => get_class($entity), 'photoable_id' => $entity->getKey()],
    ['chemin_photo' => $data['icon_url'], 'source_url' => $data['icon_url']]
);
```

---

## Tests PHPUnit — conventions

### Ce que chaque test doit couvrir

Pour chaque issue, écrire un test pour chaque critère d'acceptance, plus :

- Test HTTP (status 200 sur les routes qui doivent fonctionner)
- Test 404 (slug inexistant, accès par id)
- Test authentification (middleware auth et admin)
- Test validation (champs requis, formats)
- Test BDD (slug auto-généré, relations)
- Test cas limites (liste vide, données manquantes)

```php
class Issue7PersonnageControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_liste_retourne_200(): void
    {
        $this->get('/personnages')->assertStatus(200);
    }

    public function test_detail_par_slug(): void
    {
        Personnage::factory()->create(['slug' => 'hu-tao', 'nom_perso' => 'Hu Tao']);
        $this->get('/personnages/hu-tao')->assertStatus(200)->assertSee('Hu Tao');
    }

    public function test_slug_inexistant_retourne_404(): void
    {
        $this->get('/personnages/inexistant')->assertStatus(404);
    }

    public function test_acces_par_id_retourne_404(): void
    {
        $this->get('/personnages/1')->assertStatus(404);
    }

    public function test_slug_genere_automatiquement(): void
    {
        $perso = Personnage::factory()->create(['nom_perso' => 'Raiden Shogun']);
        $this->assertEquals('raiden-shogun', $perso->slug);
    }

    public function test_recherche_filtre_resultats(): void
    {
        Personnage::factory()->create(['nom_perso' => 'Hu Tao']);
        Personnage::factory()->create(['nom_perso' => 'Ayaka']);
        $this->get('/personnages?search=Hu')->assertSee('Hu Tao')->assertDontSee('Ayaka');
    }

    public function test_pagination_conserve_les_filtres(): void
    {
        $response = $this->get('/personnages?search=hu&sort=rarete_desc&page=2');
        $response->assertStatus(200);
        $response->assertSee('search=hu');
    }

    public function test_page_accessible_sans_connexion(): void
    {
        $this->get('/personnages')->assertStatus(200);
    }
}
```

### Lancer les tests

```bash
php artisan test                          # tous les tests
php artisan test --filter=Issue7          # une issue
php artisan test --filter=Personnage      # une fonctionnalité
php artisan test --filter=Issue7 --verbose
```

---

## Import de données externes

### teyvat-dev API — ordre d'import impératif

```php
$base = 'https://teyvat-dev.vercel.app/api';

// 1. Éléments en premier
foreach (Http::get("{$base}/elements")->json() as $e) {
    $el = Elements::updateOrCreate(['libelle_element' => $e['name']]);
    $el->photos()->updateOrCreate(
        ['photoable_type' => Elements::class, 'photoable_id' => $el->id_element],
        ['chemin_photo' => $e['icon_url'], 'source_url' => $e['icon_url']]
    );
}

// 2. Types d'armes
foreach (Http::get("{$base}/weapons")->json() as $w) {
    $type = $w['weapon_type']['name'] ?? $w['type'];
    $ta = TypeArme::updateOrCreate(['libelle_TArme' => $type]);
    if (!empty($w['weapon_type']['icon_url'])) {
        $ta->photos()->updateOrCreate(
            ['photoable_type' => TypeArme::class, 'photoable_id' => $ta->id_TArmes],
            ['chemin_photo' => $w['weapon_type']['icon_url'], 'source_url' => $w['weapon_type']['icon_url']]
        );
    }
}

// 3. Personnages
foreach (Http::get("{$base}/characters")->json() as $p) {
    $perso = Personnage::updateOrCreate(['slug' => Str::slug($p['name'])], [
        'nom_perso'  => $p['name'],
        'fid_etoile' => Etoile::firstOrCreate(['libelle' => $p['rarity'].'★'])->id_etoile,
        'fid_element'=> Elements::where('libelle_element', $p['element']['name'])->first()?->id_element,
        'fid_TArmes' => TypeArme::where('libelle_TArme', $p['weapon_type']['name'])->first()?->id_TArmes,
        'fid_TP'     => TypePerso::first()?->id_TP,
    ]);
    $perso->photos()->updateOrCreate(
        ['photoable_type' => Personnage::class, 'photoable_id' => $perso->id_perso],
        ['chemin_photo' => $p['icon_url'], 'source_url' => $p['icon_url']]
    );
}

// 4. Armes, Matériaux, Nations dans le même ordre
```

### Enka.Network — import showcase joueur

```php
$response = Http::get("https://enka.network/api/uid/{$uid}");
// Retourne avatarInfoList — max 8 personnages du showcase
// Le joueur doit avoir activé "Afficher les détails des personnages" en jeu
```

---

## Migrations — ordre impératif

```
1.  Etoile, Rareté, Elements
2.  Type_Armes, Type_Perso, Type_Apti, Type_Ennemi, Type_Animal, Type_Materiaux
3.  Joueur, Admin
4.  Personnage
5.  Bio, Constellation, Aptitude
6.  Role, Personnage_Role
7.  Armes, Arm_Stats_Rang, Arm_Stats_Niveau
8.  Joueur_Arme, Joueur_Personnage, Joueur_Constellation
9.  Amitie
10. Région, Sous_Region, Produits
11. Ennemi, Ennemi_Region
12. Materiaux, Mate_Ennemi
13. Animaux, Animal_Region
14. Ingrédient, Animal_Ingredient
15. Plat, Spécialité, Plat_Ingredient
16. Chronologie, Evenement
17. Photo (en dernier — polymorphique, aucune FK)
```

---

## Ne jamais faire

- Utiliser des ids dans les URLs publiques
- Stocker des passwords en clair ou avec md5/sha1/bcrypt() direct
- Créer des tables Photo_xxx spécifiques
- Oublier withQueryString() sur les paginations avec filtres
- Faire des requêtes dans les boucles Blade
- Commiter directement sur main ou develop
- Passer à l'issue suivante si les tests échouent
- Ignorer un échec signalé par Amazon Q
- Importer les personnages avant les éléments et types d'armes

## Toujours faire

- Générer le slug dans static::creating()
- Utiliser findOrFail() ou Route Model Binding
- Valider avec $request->validate()
- Utiliser updateOrCreate() pour les imports
- Ajouter ->withQueryString() après ->paginate()
- Vérifier l'existence d'une photo avec ?-> avant affichage
- Supprimer l'ancien fichier local avant upload d'un nouveau
- Stocker source_url pour toutes les images venant de l'API externe
- Écrire un test pour chaque critère d'acceptance de l'issue
- Demander à Amazon Q de lancer `php artisan test --filter=IssueXxxTest` après chaque commit
