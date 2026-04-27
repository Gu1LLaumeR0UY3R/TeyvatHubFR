# Documentation technique — Blog Public

> **Pour qui ?** Développeur qui reprend le projet ou développeur junior PHP/Laravel.  
> **Difficulté Laravel** : ⭐ Débutant — consultation simple avec pagination.

---

## 1. Rôle de cette fonctionnalité

Le blog public permet à tout visiteur de consulter les articles publiés. Il y a deux pages :
- **Liste** (`/blog`) : liste paginable (12 articles/page), avec recherche textuelle.
- **Détail** (`/blog/{slug}`) : article complet, accessible uniquement si le statut est `publie`.

Les articles en statut `brouillon` sont **invisibles** sur le site public — ils ne sont accessibles que via le panel admin.

---

## 2. Routes

```php
// routes/web.php
Route::get('/blog', [BlogController::class, 'index'])->name('blog.index');
Route::get('/blog/{article:slug}', [BlogController::class, 'show'])->name('blog.show');
```

Notice `{article:slug}` : la syntaxe `:slug` dit à Laravel d'utiliser la colonne `slug` de la table `blog_article` pour trouver l'article (au lieu de l'ID par défaut).

---

## 3. Controller — `app/Http/Controllers/BlogController.php`

### 3.1 Méthode `index` — liste

```php
public function index(Request $request): View
{
    $articles = BlogArticle::query()
        ->with('photos')
        ->where('statut', 'publie')   // UNIQUEMENT les articles publiés
        ->when($request->filled('search'), function ($query) use ($request) {
            // Recherche dans le titre, l'extrait et le contenu
            $term = (string) $request->input('search');
            $query->where(function ($q) use ($term) {
                $q->where('titre_article', 'like', '%' . $term . '%')
                  ->orWhere('extrait', 'like', '%' . $term . '%')
                  ->orWhere('contenu_article', 'like', '%' . $term . '%');
            });
        })
        ->orderByDesc('date_publication')  // Plus récent en premier
        ->orderByDesc('created_at')        // Tri secondaire
        ->paginate(12)
        ->withQueryString();  // Conserve les paramètres (?search=...) sur les liens de pagination

    return view('blog.index', compact('articles'));
}
```

**`when(condition, callback)`** : exécute le callback uniquement si la condition est vraie. Évite d'écrire des `if/else` en dehors de la query.

**`withQueryString()`** : nécessaire pour que les liens de pagination (/blog?page=2) conservent le paramètre `search` (/blog?search=hu&page=2).

### 3.2 Méthode `show` — détail

```php
public function show(BlogArticle $article): View
{
    // Si quelqu'un accède directement à un brouillon par son slug, on renvoie 404
    abort_unless($article->statut === 'publie', 404);

    $article->load('photos');
    return view('blog.show', compact('article'));
}
```

**`abort_unless(condition, code)`** : si la condition est fausse, Laravel interrompt la requête et renvoie une page d'erreur avec le code HTTP donné (404 = "Non trouvé").

---

## 4. Modèles impliqués

| Modèle | Table SQL | Colonnes clés |
|--------|-----------|---------------|
| `BlogArticle` | `blog_article` | `titre_article`, `slug`, `statut` (`brouillon`/`publie`), `extrait`, `contenu_article`, `date_publication` |
| `BlogSlug` | `blog_slug` | `slug_base` — pré-configurations de slugs |
| `Photo` | `photo` | `chemin_photo`, `source_url` — photo de couverture de l'article |

**Slugs** : le slug d'un article est généré à partir du titre lors de la création. La table `blog_slug` contient des "gabarits" de slugs prédéfinis (ex : pour des slugs saisonniers récurrents).

---

## 5. Sécurité

Aucun middleware. La seule protection côté public est :
- La clause `where('statut', 'publie')` qui exclut les brouillons de la liste.
- Le `abort_unless($article->statut === 'publie', 404)` qui protège la page de détail.

---

## 6. Vues

```
resources/views/blog/
  index.blade.php   ← liste paginable avec barre de recherche
  show.blade.php    ← article complet (titre, photo, contenu en HTML/Gutenberg)
```

---

## 7. Tests

```php
public function test_blog_liste_retourne_200(): void
{
    $this->get('/blog')->assertStatus(200);
}

public function test_blog_liste_naffiche_que_publie(): void
{
    BlogArticle::factory()->create(['statut' => 'brouillon', 'slug' => 'brouillon-test']);
    BlogArticle::factory()->create(['statut' => 'publie',   'slug' => 'publie-test',
                                    'titre_article' => 'Article publié']);
    $this->get('/blog')
         ->assertSee('Article publié')
         ->assertDontSee('brouillon-test');
}

public function test_blog_detail_brouillon_retourne_404(): void
{
    BlogArticle::factory()->create(['statut' => 'brouillon', 'slug' => 'secret']);
    $this->get('/blog/secret')->assertStatus(404);
}

public function test_blog_recherche_fonctionne(): void
{
    BlogArticle::factory()->create(['statut' => 'publie', 'titre_article' => 'Guide Hu Tao', 'slug' => 'guide-hu-tao']);
    BlogArticle::factory()->create(['statut' => 'publie', 'titre_article' => 'Guide Ayaka',  'slug' => 'guide-ayaka']);
    $this->get('/blog?search=Hu Tao')->assertSee('Guide Hu Tao')->assertDontSee('Guide Ayaka');
}
```
