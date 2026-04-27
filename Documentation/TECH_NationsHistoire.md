# Documentation technique — Nations et Histoire

> **Pour qui ?** Développeur qui reprend le projet ou développeur junior PHP/Laravel.  
> **Difficulté Laravel** : ⭐ Débutant — consultation simple, relations 1-N.

---

## 1. Rôle de cette fonctionnalité

Deux sections publiques :
- **Histoire** (`/histoire`) : timeline narrative de Teyvat, triée par `ordre`.
- **Nations** (`/nations`, `/nations/{slug}`) : liste des nations (régions) du jeu avec leurs sous-régions, ennemis et animaux associés.

---

## 2. Routes

```php
Route::get('/histoire', [HistoireController::class, 'index'])->name('histoire.index');
Route::get('/nations', [NationController::class, 'index'])->name('nations.index');
Route::get('/nations/{nation}', [NationController::class, 'show'])->name('nations.show');

// Alias legacy (anciennes URLs conservées pour compatibilité)
Route::get('/histoire/nations', fn() => redirect()->route('nations.index'));
Route::get('/histoire/nations/{nation}', fn(string $nation) => redirect()->route('nations.show', $nation));
```

**Alias legacy** : si quelqu'un a un ancien lien `/histoire/nations/mondstadt`, il est redirigé vers `/nations/mondstadt`. C'est une bonne pratique SEO.

---

## 3. Controllers

### 3.1 `HistoireController@index`

```php
public function index(): View
{
    // Chronologie triée par ordre (colonne 'ordre' de type INT)
    $chronologie = Chronologie::with(['nation', 'photos'])->orderBy('ordre')->get();
    $nations = Nation::orderBy('nom_region')->get();
    return view('histoire.index', compact('chronologie', 'nations'));
}
```

La `Chronologie` est une entrée narrative (ex : "La Chute de Khaenri'ah") avec un titre, un résumé, une période et un ordre d'affichage.

### 3.2 `NationController@index`

```php
public function index(): View
{
    $nations = Nation::with('photos')->orderBy('nom_region')->get();
    return view('nations.index', compact('nations'));
}
```

### 3.3 `NationController@show`

```php
public function show(Nation $nation): View
{
    $nation->load([
        'photos',
        'sousRegions.photos',  // sous-zones géographiques (Mond, Liue, etc.)
        'ennemis.photos',      // ennemis liés à cette nation
        'animaux.photos',      // animaux présents dans cette nation
        'produits',            // spécialités / produits locaux
    ]);
    return view('nations.show', compact('nation'));
}
```

---

## 4. Modèles impliqués

| Modèle | Table SQL | Clé primaire | Colonnes importantes |
|--------|-----------|--------------|----------------------|
| `Nation` | `région` | `id_region` | `nom_region`, `slug`, `description_region` |
| `SousRegion` | `sous_region` | `id_sous_region` | `nom_sous_region`, `fid_region` |
| `Chronologie` | `chronologie` | `id_chrono` | `titre`, `resume`, `periode`, `ordre`, `fid_region` |
| `Evenement` | `evenement` | `id_evenement` | `nom_evenement`, `date_debut`, `date_fin` |
| `Produits` | `produits` | `id_produit` | `nom_produit`, `fid_region` |

**Note** : la table SQL s'appelle `région` (avec accent), ce qui peut poser problème sur certains OS. Toujours passer par Eloquent pour l'éviter.

---

## 5. Sécurité

Aucun middleware — routes publiques.

---

## 6. Vues

```
resources/views/
  histoire/
    index.blade.php    ← timeline narrative
  nations/
    index.blade.php    ← grille des nations
    show.blade.php     ← détail d'une nation
```

---

## 7. Tests

```php
public function test_histoire_retourne_200(): void
{
    $this->get('/histoire')->assertStatus(200);
}

public function test_nations_retourne_200(): void
{
    $this->get('/nations')->assertStatus(200);
}

public function test_detail_nation_par_slug(): void
{
    Nation::factory()->create(['nom_region' => 'Mondstadt', 'slug' => 'mondstadt']);
    $this->get('/nations/mondstadt')->assertStatus(200)->assertSee('Mondstadt');
}

public function test_ancien_lien_histoire_nations_redirige(): void
{
    $this->get('/histoire/nations')->assertRedirect('/nations');
}
```
