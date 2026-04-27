# Documentation technique — Accueil et Navigation

> **Pour qui ?** Développeur qui reprend le projet ou développeur junior PHP/Laravel.  
> **Difficulté Laravel** : ⭐ Débutant — c'est la page d'entrée la plus simple.

---

## 1. Rôle de cette fonctionnalité

La page d'accueil (`/`) est la **vitrine du site**. Elle présente :
- Les 6 derniers personnages ajoutés dans la base de données.
- Les prochains événements Genshin Impact (dont la date de fin est dans le futur).
- Un compteur général (nombre total de personnages indexés).

Elle ne nécessite **aucune connexion**. N'importe quel visiteur peut la consulter.

---

## 2. Route

```php
// routes/web.php
Route::get('/', [HomeController::class, 'index'])->name('home');
```

**Lecture rapide** : quand quelqu'un visite `https://teyvathub.fr/`, Laravel appelle la méthode `index()` du `HomeController`. La route s'appelle `home` (utilisable dans les vues avec `route('home')`).

---

## 3. Controller — `app/Http/Controllers/HomeController.php`

```php
public function index(): View
{
    // Récupère les 6 derniers personnages avec leurs relations (élément, rareté, photos)
    $derniers_personnages = Personnage::with(['element', 'etoile', 'photos'])
        ->latest('id_perso')   // tri décroissant sur la clé primaire
        ->take(6)
        ->get();

    // Récupère les événements dont la date de fin n'est pas encore passée
    $prochains_evenements = Evenement::where('date_fin', '>=', now()->toDateString())
        ->orderBy('date_debut')
        ->take(4)
        ->get();

    // Compteur simple (affiché sur la page d'accueil)
    $compteurs = [
        'personnages' => Personnage::count(),
    ];

    return view('home', compact('derniers_personnages', 'prochains_evenements', 'compteurs'));
}
```

**Pourquoi `with([...])`?** Sans ce mot-clé, Laravel ferait une requête SQL par personnage pour charger l'élément, la rareté, la photo → 18+ requêtes pour 6 personnages. Avec `with()`, il fait **2 requêtes** (chargement en masse = *eager loading*). C'est une règle absolue du projet.

---

## 4. Modèles impliqués

| Modèle | Table SQL | Rôle dans cette page |
|--------|-----------|----------------------|
| `Personnage` | `personnage` | Fournit les derniers personnages ajoutés |
| `Elements` | `elements` | Chargé via `element()` pour afficher l'icône d'élément |
| `Etoile` | `etoile` | Chargé via `etoile()` pour afficher la rareté |
| `Photo` | `photo` | Polymorphique — chargé via `photos()` pour l'image du personnage |
| `Evenement` | `evenement` | Fournit les prochains événements Genshin |

---

## 5. Vue

**Fichier** : `resources/views/home.blade.php`

C'est un template Blade (moteur de template intégré à Laravel). La vue reçoit les variables `$derniers_personnages`, `$prochains_evenements`, `$compteurs` et les affiche.

Pour afficher une image de personnage en Blade :
```blade
<img src="{{ $perso->photos->first()?->source_url ?? asset('images/placeholder.webp') }}"
     alt="{{ $perso->nom_perso }}">
```
Le `?->` est l'opérateur *nullsafe* — il évite les erreurs si la photo n'existe pas.

---

## 6. Middleware et sécurité

Aucun middleware n'est appliqué. La route est **entièrement publique**.

---

## 7. Points d'extension connus

- Le `$compteurs` ne contient actuellement que `personnages`. Il est prévu d'y ajouter armes, ennemis, etc.
- Les événements sont triés par date de début mais ne sont pas paginés.

---

## 8. Tests à écrire

```php
// Test minimum attendu
public function test_page_accueil_retourne_200(): void
{
    $this->get('/')->assertStatus(200);
}

public function test_page_accueil_affiche_personnages_recents(): void
{
    Personnage::factory()->count(3)->create();
    $this->get('/')->assertStatus(200);
}

public function test_page_accueil_sans_evenements_ne_plante_pas(): void
{
    // Aucun événement en base → la page doit quand même s'afficher
    $this->get('/')->assertStatus(200);
}
```

---

## 9. Erreurs fréquentes

| Erreur | Cause | Solution |
|--------|-------|----------|
| `N+1 queries detected` | Oubli du `with()` | Toujours ajouter `with(['element','etoile','photos'])` |
| Page vide | Aucun personnage en base | Normal en dev — utiliser les seeders |
| Date événement incorrecte | Timezone PHP/MySQL décalée | Vérifier `config/app.php` → `timezone` = `'Europe/Paris'` |
