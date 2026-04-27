# Documentation technique — Encyclopédie Publique

> **Pour qui ?** Développeur qui reprend le projet ou développeur junior PHP/Laravel.  
> **Difficulté Laravel** : ⭐⭐ Intermédiaire — maîtriser Route Model Binding, slugs, eager loading, filtres.

---

## 1. Rôle de cette fonctionnalité

L'encyclopédie publique est le cœur du site. Elle expose **7 sections** lisibles par n'importe quel visiteur, sans connexion :

| Section | URL index | URL détail |
|---------|-----------|------------|
| Personnages | `/personnages` | `/personnages/{slug}` |
| Armes | `/armes` | `/armes/{slug}` |
| Ennemis | `/ennemis` | `/ennemis/{slug}` |
| Animaux | `/animaux` | `/animaux/{slug}` |
| Cuisine (plats) | `/cuisine` | `/cuisine/{slug}` |
| Matériaux | `/materiaux` | `/materiaux/{slug}` |
| Ingrédients | `/ingredients` | `/ingredients/{slug}` |

---

## 2. Principe du slug (important)

Dans les URLs publiques, on **n'utilise jamais l'ID** (`/personnages/42`). On utilise un **slug** : une version URL-safe du nom (`Hu Tao` → `hu-tao`).

**Pourquoi ?**
- Les URLs sont lisibles et mémorisables.
- L'ID interne est une donnée technique qui ne doit pas fuiter dans l'URL.

**Comment est généré le slug ?** Automatiquement dans le modèle, au moment de la création :
```php
// app/Models/Personnage.php
protected static function booted(): void
{
    static::creating(function ($model) {
        $model->slug = Str::slug($model->nom_perso); // 'Hu Tao' → 'hu-tao'
    });
}

public function getRouteKeyName(): string
{
    return 'slug'; // Laravel sait qu'il doit chercher par 'slug', pas par 'id'
}
```

---

## 3. Routes

```php
// routes/web.php
Route::get('/personnages', [PersonnageController::class, 'index'])->name('personnages.index');
Route::get('/personnages/{personnage}', [PersonnageController::class, 'show'])->name('personnages.show');
// (même pattern pour armes, ennemis, animaux, cuisine, materiaux, ingredients)
```

**Route Model Binding** : quand Laravel voit `{personnage}` dans l'URL, et que `PersonnageController@show` reçoit un paramètre typé `Personnage $personnage`, Laravel fait automatiquement `SELECT * FROM personnage WHERE slug = 'hu-tao'`. Si le résultat est vide → **404 automatique**.

---

## 4. Controllers

### 4.1 `PersonnageController@index` — liste avec filtres

```php
public function index(Request $request): View
{
    $query = Personnage::with(['element', 'etoile', 'typeArme', 'photos']);

    // Filtre par nom (recherche textuelle)
    if ($request->search) {
        $query->where('nom_perso', 'LIKE', '%' . $request->search . '%');
    }
    // Filtre par élément
    if ($request->element) {
        $query->where('fid_element', $request->element);
    }
    // Filtre par rareté (4★ ou 5★)
    if ($request->rarete) {
        $query->where('fid_etoile', $request->rarete);
    }
    // Tri
    switch ($request->sort) {
        case 'rarete_desc': $query->orderBy('fid_etoile', 'desc'); break;
        case 'nom_desc':    $query->orderBy('nom_perso', 'desc'); break;
        default:            $query->orderBy('nom_perso');
    }

    $personnages = $query->get(); // Pas de pagination ici
    // ...
    return view('personnages.index', compact('personnages', 'elements', 'etoiles', 'typeArmes'));
}
```

### 4.2 `PersonnageController@show` — détail

```php
public function show(Personnage $personnage): View
{
    // On charge TOUTES les relations nécessaires à la page de détail
    $personnage->load([
        'element', 'etoile', 'bio',
        'aptitudes.typeApti', 'aptitudes.photos',
        'constellations.photo',
        'specialite.plat.photos',
        'roles', 'photos', 'videos', 'histoires', 'nations',
        'typeArme',
        'armesRecommandees.arme.typeArme.photos',
        'artefactsRecommandees.artefact1',
        'artefactsRecommandees.artefact2',
    ]);
    return view('personnages.show', compact('personnage'));
}
```

**`->load()` vs `->with()`** : `with()` s'utilise sur une *Query* (avant le `get()`), `load()` s'utilise sur une *instance déjà récupérée*. Même résultat, usage différent.

---

## 5. Modèles impliqués

| Modèle | Table | Clé primaire | Relations importantes |
|--------|-------|--------------|------------------------|
| `Personnage` | `personnage` | `id_perso` | element, etoile, typeArme, bio, aptitudes, constellations, photos, roles |
| `Arme` | `armes` | `id_arme` | typeArme, etoile, photos |
| `Ennemi` | `ennemi` | `id_ennemi` | typeEnnemi, photos |
| `Animal` | `animaux` | `id_animal` | typeAnimal, photos |
| `Plat` | `plat` | `id_plat` | ingredient, photos |
| `Materiaux` | `materiaux` | `id_mat` | typeMateriaux, photos |
| `Ingredient` | `ingredient` | `id_ingredient` | photos |
| `Photo` | `photo` | `id_photo` | Polymorphique — liée à n'importe quelle entité |

**Photo polymorphique** : une seule table `photo` pour toutes les entités. Les colonnes `photoable_type` et `photoable_id` indiquent à quelle entité la photo appartient. Exemple : `photoable_type = 'App\Models\Personnage'`, `photoable_id = 5`.

---

## 6. Middleware et sécurité

Aucun middleware — toutes ces routes sont **publiques**. Pas besoin d'être connecté.

---

## 7. Vues

```
resources/views/
  personnages/
    index.blade.php   ← liste avec filtres (formulaire GET)
    show.blade.php    ← fiche détaillée d'un personnage
  armes/
    index.blade.php
    show.blade.php
  ... (même structure pour chaque section)
```

---

## 8. Tests à écrire

```php
// Test liste
public function test_liste_personnages_retourne_200(): void
{
    $this->get('/personnages')->assertStatus(200);
}

// Test détail par slug
public function test_detail_personnage_par_slug(): void
{
    $p = Personnage::factory()->create(['nom_perso' => 'Hu Tao']);
    $this->get('/personnages/hu-tao')->assertStatus(200)->assertSee('Hu Tao');
}

// Test 404 pour slug inexistant
public function test_slug_inexistant_retourne_404(): void
{
    $this->get('/personnages/inexistant-xyz')->assertStatus(404);
}

// Test 404 si on accède par ID
public function test_acces_par_id_retourne_404(): void
{
    $this->get('/personnages/1')->assertStatus(404);
}

// Test filtre recherche
public function test_filtre_recherche_personnage(): void
{
    Personnage::factory()->create(['nom_perso' => 'Hu Tao']);
    Personnage::factory()->create(['nom_perso' => 'Ayaka']);
    $this->get('/personnages?search=Hu')->assertSee('Hu Tao')->assertDontSee('Ayaka');
}
```

---

## 9. Erreurs fréquentes

| Erreur | Cause | Solution |
|--------|-------|----------|
| 404 sur une fiche existante | Slug mal généré (caractères spéciaux) | Vérifier `Str::slug()` sur le nom |
| Images cassées | `source_url` et `chemin_photo` vides | Vérifier l'import ou l'upload admin |
| N+1 queries | Relations non chargées | Ajouter les relations dans `with()` / `load()` |
