# Documentation fonctionnelle — Nations & Histoire de Teyvat

> **Pour qui ?** Développeur reprenant le projet ou développeur junior.  
> **Niveau** : ⭐ Débutant — lecture seule, aucune authentification.

---

## 1. Objectif

Cette section présente l'**univers narratif** de Genshin Impact sous deux angles :

- **Page Histoire** (`/histoire`) : une **chronologie** des événements majeurs du lore, ordonnée manuellement par les admins.
- **Page Nations** (`/nations`) : liste de toutes les nations de Teyvat.
- **Fiche Nation** (`/nations/{slug}`) : détail d'une nation avec ses sous-régions, ennemis natifs, animaux et produits locaux.

Toutes ces pages sont **publiques** — aucune connexion requise.

---

## 2. En tant que visiteur…

> *En tant que fan de Genshin, je veux consulter la chronologie du lore pour mieux comprendre l'histoire du jeu.*

> *En tant que visiteur, je veux explorer les nations de Teyvat pour découvrir leurs caractéristiques.*

> *Je veux voir quels ennemis et animaux sont présents dans une nation pour préparer mon exploration.*

---

## 3. Parcours — Page Histoire (`/histoire`)

1. L'utilisateur navigue vers `/histoire`.
2. Il voit une **timeline chronologique** des événements narratifs majeurs de l'univers Genshin.
3. Chaque entrée affiche : **titre**, **période** (ex: "500 ans avant l'Ère actuelle"), **résumé**, **nation associée** (si applicable).
4. Les entrées sont triées par le champ `ordre` (entier défini manuellement par les admins).
5. La liste de toutes les nations est également affichée sur cette page.

---

## 4. Parcours — Page Nations (`/nations`)

1. L'utilisateur navigue vers `/nations`.
2. Il voit une liste de toutes les nations (Mondstadt, Liyue, Inazuma, Sumeru, Fontaine…) avec leurs photos.
3. Il clique sur une nation pour accéder à sa fiche.

---

## 5. Parcours — Fiche Nation (`/nations/{slug}`)

1. L'utilisateur clique sur une nation ou saisit son URL.
2. La fiche affiche :
   - **Nom** et **photo(s)** de la nation.
   - **Sous-régions** : zones géographiques internes (ex: Mondstadt → Plaines de Windrise, Forêt de Stormterror…).
   - **Ennemis** présents dans cette nation (avec photos).
   - **Animaux** présents dans cette nation (avec photos).
   - **Produits** locaux : spécialités et ressources caractéristiques.

---

## 6. Redirections legacy

Les anciennes URLs `/histoire/nations` et `/histoire/nations/{nation}` redirigent automatiquement vers les nouvelles URLs `/nations` et `/nations/{nation}` (redirection 301 permanente).

Cela évite les liens cassés si des pages extérieures pointaient vers les anciennes URLs.

---

## 7. Règles métier

| Règle | Détail |
|-------|--------|
| URLs | Basées sur le slug de la nation (ex: `/nations/mondstadt`) |
| Slug inconnu | Erreur 404 |
| Ordre de la chronologie | Champ entier `ordre`, trié croissant ; défini manuellement par les admins |
| Photos | Via la relation polymorphique `photos()` |
| Relations chargées | Toutes en une fois (`->load([...])`) pour éviter le N+1 |

---

## 8. Messages & cas limites

| Situation | Comportement |
|-----------|-------------|
| Nation introuvable (slug inconnu) | 404 |
| Chronologie vide (aucune entrée en base) | Page affichée sans entrées, message informatif |
| Nation sans ennemis ni animaux | Sections correspondantes masquées proprement |
| Ancienne URL `/histoire/nations` | Redirigée automatiquement vers `/nations` |

---

## 9. Dépendances techniques

- **`HistoireController`** : méthode `index()` charge la chronologie + liste des nations.
- **`NationController`** : méthodes `index()` (liste) et `show()` (fiche détaillée).
- **Modèles** : `Nation`, `SousRegion`, `Chronologie`, `Ennemi`, `Animal`, `Produits`, `Photo`.
- **Aucun middleware** : routes entièrement publiques.
