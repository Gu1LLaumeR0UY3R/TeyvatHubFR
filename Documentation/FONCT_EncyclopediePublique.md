# Documentation fonctionnelle — Encyclopédie publique

> **Pour qui ?** Développeur reprenant le projet ou développeur junior.  
> **Niveau** : ⭐⭐ Intermédiaire — filtres, relations Eloquent, slugs.

---

## 1. Objectif

L'encyclopédie est le **cœur du site**. Elle regroupe toutes les données de Genshin Impact organisées en sections. Chaque section propose une liste filtrable et des fiches détaillées.

| Section | URL | Contenu |
|---------|-----|---------|
| Personnages | `/personnages` | Personnages jouables, stats, compétences, constellations… |
| Armes | `/armes` | Toutes les armes du jeu |
| Ennemis | `/ennemis` | Monstres et boss |
| Animaux | `/animaux` | Faune de Teyvat |
| Cuisine | `/cuisine` | Plats cuisinables et effets |
| Matériaux | `/materiaux` | Ressources de craft et d'ascension |
| Ingrédients | `/ingredients` | Ingrédients de cuisine |

Toutes ces pages sont **publiques** — aucune connexion requise.

---

## 2. En tant que visiteur…

> *Je veux consulter la liste des personnages et filtrer par élément pour trouver rapidement ceux qui m'intéressent.*

> *Je veux lire la fiche détaillée d'un personnage pour voir ses compétences, constellations et armes recommandées.*

> *Je veux parcourir les armes pour choisir la meilleure pour mon personnage.*

---

## 3. Parcours — Liste (exemple Personnages)

1. L'utilisateur navigue vers `/personnages`.
2. Il voit une **grille** de personnages (photo, nom, élément, rareté en étoiles).
3. Il peut **filtrer** par :
   - **Nom** (champ de recherche texte)
   - **Élément** (Pyro, Hydro, Electro, Cryo, Anemo, Geo, Dendro)
   - **Rareté** (4★ ou 5★)
4. Il peut **trier** par : nom A→Z, nom Z→A, rareté, élément.
5. Les filtres actifs sont **conservés dans l'URL** (`?search=hu&element=2&sort=rarete_desc`) — ainsi, copier-coller l'URL partage les mêmes filtres.

---

## 4. Parcours — Fiche détaillée Personnage

1. L'utilisateur clique sur un personnage dans la liste.
2. Il est redirigé vers `/personnages/{slug}` (ex: `/personnages/hu-tao`).
3. La fiche affiche :
   - **Informations générales** : nom, élément, rareté, type d'arme, nation(s).
   - **Biographie** : texte de présentation du personnage.
   - **Compétences** : attaque normale, compétence E (élémentaire), compétence Q (ultime), passives.
   - **Constellations** : les 6 niveaux avec icône et description.
   - **Armes recommandées** : sélection d'armes adaptées, avec photos.
   - **Artefacts recommandés** : combinaisons de sets conseillées.
   - **Équipes** : compositions d'équipe recommandées.
   - **Histoires** : textes de lore (journal, anecdotes).
   - **Vidéos** : liens YouTube intégrés.

---

## 5. Règles métier

| Règle | Détail |
|-------|--------|
| URLs | Toujours basées sur le **slug** textuel — jamais un ID numérique |
| Slug inconnu | Erreur 404 |
| Accès par ID (ex: `/personnages/42`) | Erreur 404 (le slug ne peut pas être un entier pur) |
| Photos | Via la table polymorphique `Photo` ; si absente → image placeholder |
| Filtres | Transmis en query string et persistés entre les pages de pagination |
| Chargement des relations | Toutes chargées d'un coup avec `->with([...])` — jamais en boucle (pas de problème N+1) |

> **C'est quoi le N+1 ?** Si on charge 20 personnages puis qu'on fait une requête par personnage pour chercher son élément, on fait 21 requêtes au lieu de 2. Le chargement eager avec `->with()` évite ça.

---

## 6. Messages & cas limites

| Situation | Comportement |
|-----------|-------------|
| Slug inconnu | 404 |
| Aucune donnée en base | Grille vide, message informatif |
| Recherche sans résultat | Grille vide, message "Aucun résultat" |
| Photo manquante | Image placeholder affichée |
| Relations manquantes (bio, constellations) | Sections masquées proprement |

---

## 7. Dépendances techniques

- **Controllers** : `PersonnageController`, `ArmeController`, `EnnemiController`, `AnimalController`, `PlatController`, `MateriauxController`, `IngredientController`.
- **Modèles principaux** : `Personnage`, `Arme`, `Ennemi`, `Animal`, `Plat`, `Materiaux`, `Ingredient`.
- **Modèles liés** : `Elements`, `Etoile`, `TypeArme`, `Bio`, `Aptitude`, `Constellation`, `Photo`, `Nation`.
- **Aucun middleware** : routes entièrement publiques.

---

## 8. Glossaire

| Terme | Explication |
|-------|-------------|
| Slug | Identifiant textuel dans une URL (ex: `hu-tao` plutôt que `42`) |
| Route Model Binding | Laravel trouve automatiquement l'entité en base depuis le slug dans l'URL |
| Eager Loading | Chargement anticipé de toutes les relations en 2 requêtes au lieu de N+1 |
| Query string | Paramètres dans l'URL après `?` (ex: `?search=hu&element=2`) |
