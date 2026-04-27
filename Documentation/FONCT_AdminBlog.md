# Documentation fonctionnelle — Gestion du blog (Admin)

> **Pour qui ?** Développeur reprenant le projet ou développeur junior.  
> **Niveau** : ⭐⭐ Intermédiaire — CRUD, upload, Gutenberg JSON, slugs.

---

## 1. Objectif

Le module blog admin permet aux administrateurs avec la **permission `blog`** de rédiger, modifier et publier des articles visibles sur la section publique `/blog`.

---

## 2. En tant qu'admin avec la permission "blog"…

> *Je veux créer un article avec un éditeur de blocs, choisir son statut et téléverser des images.*

> *Je veux gérer les slugs pour avoir des URLs propres et rétrocompatibles.*

> *Je veux publier un article immédiatement ou le laisser en brouillon.*

---

## 3. Parcours — Créer un article

1. L'admin accède à `/admin/blog/create`.
2. Il saisit :
   - **Titre** (obligatoire, max 180 caractères).
   - **Contenu** via un éditeur de blocs (Gutenberg-style). Le contenu est stocké en **JSON**.
   - **Statut** : `brouillon` ou `publie`.
   - **Date de publication** (optionnelle — remplie automatiquement si laissée vide lors de la publication).
   - **Slug** (optionnel — généré depuis le titre si absent).
3. Il peut téléverser des **images de couverture** (format image, max 5 Mo chacune).
4. Il peut choisir un **slug prédéfini** depuis la liste `BlogSlug`.
5. Il soumet. L'article est créé.
6. Il est redirigé vers la page d'édition de l'article créé.

---

## 4. Parcours — Modifier un article

1. L'admin accède à `/admin/blog/{id}/edit`.
2. Il peut modifier tous les champs.
3. Il peut **ajouter ou supprimer des images** de couverture.
4. Il peut gérer les **slugs alternatifs** (alias) pour maintenir d'anciennes URLs valides.
5. Il sauvegarde.

---

## 5. Statuts d'un article

| Statut | Visible publiquement | Détail |
|--------|---------------------|--------|
| `brouillon` | Non | Travail en cours, invisible du public |
| `publie` | Oui | Visible sur `/blog`, trié par date |

Passage en `publie` : si `date_publication` est vide → remplie automatiquement avec la date actuelle.

---

## 6. Gestion des slugs

- Le **slug principal** est généré automatiquement depuis le titre (ex: "Guide Hu Tao V3" → `guide-hu-tao-v3`).
- Des **slugs alternatifs** (`BlogSlug`) permettent d'avoir des alias pour la rétrocompatibilité.
- Chaque slug est **unique** en base.
- Les `BlogSlug` servent aussi de **suggestions prédéfinies** dans le formulaire de création.

> **Pourquoi des slugs alternatifs ?** Si un article a été publié sous `/blog/guide-hu-tao` puis migré vers un nouveau slug, l'ancien lien continue de fonctionner.

---

## 7. Règles métier

| Règle | Détail |
|-------|--------|
| Permission | Seuls les admins avec `blog` accèdent à ces routes |
| Slug | Unique, généré depuis le titre si non spécifié |
| Contenu | Stocké en JSON (format compatible blocs Gutenberg) |
| Images | Via la table polymorphique `Photo`, type `featured`, max 5 Mo |
| Publication auto | Si `statut = publie` et date vide → date = maintenant |

---

## 8. Messages & cas limites

| Situation | Comportement |
|-----------|-------------|
| Slug déjà utilisé | Erreur de validation "Le slug est déjà pris" |
| Titre manquant | Erreur de validation |
| Image trop lourde (> 5 Mo) | Erreur de validation |
| Contenu JSON invalide | Erreur de validation "Contenu invalide" |
| Admin sans permission `blog` | Erreur 403 Forbidden |

---

## 9. Dépendances techniques

- **`BlogArticleController`** (Admin) : index, create, store, edit, update, destroy, storeSlug, destroySlug, destroyImage.
- **Modèles** : `BlogArticle`, `BlogSlug`, `Photo`.
- **Middleware** : `admin`, `2fa.admin`, `admin.can:blog`.
- **Storage** : fichiers téléversés dans `storage/app/public/photos/blog/`.
