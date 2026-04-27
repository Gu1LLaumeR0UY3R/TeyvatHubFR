# Documentation fonctionnelle — Blog public

> **Pour qui ?** Développeur reprenant le projet ou développeur junior.  
> **Niveau** : ⭐ Débutant — lecture seule, aucune authentification.

---

## 1. Objectif

La section blog permet aux visiteurs de lire les **articles publiés** par l'équipe de TeyvatHub. Ces articles peuvent contenir des guides, des analyses ou des actualités Genshin Impact.

L'accès est **totalement public** : aucune connexion n'est requise.

---

## 2. En tant que visiteur…

> *Je veux consulter les articles du blog pour me tenir informé des actualités et guides Genshin Impact.*

> *Je veux rechercher un article par mot-clé pour trouver rapidement ce qui m'intéresse.*

---

## 3. Parcours — Liste des articles

1. L'utilisateur navigue vers `/blog`.
2. Il voit une liste d'articles **triés par date de publication** (les plus récents en premier).
3. Chaque carte affiche : **titre**, **extrait**, **photo de couverture**, **date**.
4. La liste est **paginée** (12 articles par page).
5. Un champ de recherche permet de filtrer par mot-clé (titre, extrait ou contenu).
6. La pagination conserve le filtre de recherche actif (`?search=...` reste dans l'URL).

---

## 4. Parcours — Lire un article

1. L'utilisateur clique sur un article.
2. Il est redirigé vers `/blog/{slug}` où `{slug}` est l'identifiant textuel unique.
3. La page affiche : **titre**, **contenu complet**, **photo(s)**, **date de publication**.
4. Si l'article n'est **pas publié** (`statut !== 'publie'`) → erreur **404** (même si l'URL est connue).

> **Pourquoi 404 et non 403 ?** Pour ne pas révéler l'existence d'un brouillon à un visiteur anonyme.

---

## 5. Règles métier

| Règle | Détail |
|-------|--------|
| Visibilité | Seuls les articles avec `statut = 'publie'` sont visibles publiquement |
| Slug | Identifiant textuel unique dans l'URL (ex: `guide-hu-tao-v3`). Généré depuis le titre. |
| Recherche | Filtre SQL `LIKE` sur `titre_article`, `extrait` et `contenu_article` |
| Pagination | 12 articles par page, filtre de recherche conservé entre les pages |
| Tri | Par `date_publication` décroissante, puis par `created_at` décroissant |
| Photos | Via la table polymorphique `Photo` (une image peut être rattachée à n'importe quelle entité) |

---

## 6. Messages & cas limites

| Situation | Comportement |
|-----------|-------------|
| Slug inconnu dans l'URL | Erreur 404 |
| Article en brouillon (URL connue) | Erreur 404 |
| Aucun article publié en base | Liste vide, message "Aucun article disponible" |
| Recherche sans résultat | Liste vide, message "Aucun résultat pour cette recherche" |
| Photo de couverture absente | Placeholder générique affiché |

---

## 7. Dépendances techniques

- **`BlogController`** : méthodes `index()` (liste + recherche + pagination) et `show()` (fiche article + vérification statut).
- **Modèle `BlogArticle`** : colonnes `titre_article`, `slug`, `contenu_article` (JSON Gutenberg), `statut`, `date_publication`, `extrait`.
- **Modèle `BlogSlug`** : slugs alternatifs pour la rétrocompatibilité des URLs.
- **Modèle `Photo`** (polymorphique) : images de couverture rattachées aux articles.
- **Aucun middleware** : routes entièrement publiques.

---

## 8. Glossaire

| Terme | Explication |
|-------|-------------|
| Slug | Identifiant textuel dans une URL (ex: `guide-hu-tao-v3`). Plus lisible qu'un ID numérique. |
| Statut `publie` | L'article est visible publiquement |
| Statut `brouillon` | L'article est en cours de rédaction, invisible du public |
| Pagination | Division des résultats en pages pour ne pas charger 1000 articles d'un coup |
| Polymorphique | Une table (ici `Photo`) peut être liée à plusieurs types d'entités différentes |
