# Documentation fonctionnelle — Encyclopédie CRUD (Admin)

> **Pour qui ?** Développeur reprenant le projet ou développeur junior.  
> **Niveau** : ⭐⭐⭐ Avancé — CRUD multi-entités, éditeur AJAX par blocs, Alpine.js.

---

## 1. Objectif

Le module encyclopédie admin permet aux administrateurs avec la **permission `encyclopedie`** de gérer tout le contenu de TeyvatHub :

| Entité | Routes admin |
|---------|-------------|
| Personnages | `/admin/personnages` |
| Armes | `/admin/armes` |
| Artefacts | `/admin/artefacts` |
| Ennemis | `/admin/ennemis` |
| Animaux | `/admin/animaux` |
| Cuisine | `/admin/cuisine` |
| Nations / Régions | `/admin/nations` |
| Rôles | `/admin/roles` |
| Références (types, éléments, étoiles…) | `/admin/references/{type}` |

---

## 2. En tant qu'admin avec la permission "encyclopedie"…

> *Je veux créer un personnage et remplir toutes ses informations via un éditeur avancé.*

> *Je veux modifier les armes recommandées sans recharger toute la page.*

> *Je veux supprimer ou corriger des entrées en masse.*

---

## 3. CRUD standard (Armes, Ennemis, Animaux, Cuisine, Nations…)

Chaque entité propose les opérations classiques :

| Action | URL (exemple armes) | Méthode HTTP |
|--------|---------------------|---------------|
| Liste | `/admin/armes` | GET |
| Formulaire de création | `/admin/armes/create` | GET |
| Enregistrer | `/admin/armes` | POST |
| Voir le détail | `/admin/armes/{slug}` | GET |
| Formulaire de modification | `/admin/armes/{slug}/edit` | GET |
| Mettre à jour | `/admin/armes/{slug}` | PUT/PATCH |
| Supprimer | `/admin/armes/{slug}` | DELETE |
| Mise à jour en masse | `/admin/armes/bulk-update` | PATCH |

La **mise à jour en masse** (bulk update) permet de modifier plusieurs entrées en une seule action depuis la liste (ex: changer l'élément de 5 personnages d'un coup).

---

## 4. Éditeur avancé Personnages (par blocs)

C'est le module **le plus complexe** du site. L'éditeur de personnages fonctionne avec des **sauvegardes AJAX indépendantes** : chaque section est enregistrée sans recharger toute la page.

### Blocs disponibles

| Bloc | Description |
|------|-------------|
| Zone principale | Nom, élément, rareté, type d'arme, nation, photo icône, photo portrait |
| Armes recommandées | Meilleures armes pour ce personnage, avec notes |
| Artefacts recommandés | Configurations de sets d'artefacts conseillées |
| Constellations | Les 6 niveaux avec titres, descriptions et icônes |
| Carte de constellations | Image de la carte + placement des points et lignes par glisser-déposer |
| Compétences | Attaque normale, compétences élémentaires (E, Q), passives |
| Histoires | Textes de lore (biographie, anecdotes, journal) |
| Équipes | Compositions d'équipes recommandées avec rôles |

### Comment ça marche (sans jargon)

1. L'admin ouvre la fiche d'un personnage → la page complète se charge.
2. Il modifie un bloc (ex: description d'une constellation).
3. Il clique "Sauvegarder ce bloc".
4. Une requête **AJAX** est envoyée vers `/admin/personnages/{slug}/block/constellations`.
5. Le serveur valide et enregistre **uniquement ce bloc** en base.
6. L'interface affiche "Sauvegardé ✓" sans recharger la page.

> ℹ️ **AJAX** = requête HTTP envoye en arrière-plan par JavaScript, sans rechargement de la page.

---

## 5. Création d'un nouveau personnage

> **Important** : l'éditeur avancé nécessite un **slug existant** pour adresser les routes AJAX. On ne peut pas créer un personnage vierge dans le navigateur puis sauvegarder les blocs un par un sans avoir d'abord un enregistrement en base.

Flux de création :

1. L'admin clique sur **"Ajouter un personnage"**.
2. Le système crée automatiquement un **brouillon en base** avec les valeurs minimales (premier élément disponible, première rareté…).
3. L'admin est **immédiatement redirigé** vers l'éditeur avancé de ce brouillon.
4. Il complète les blocs un par un.
5. Si les références manquent (aucun élément en base) → message d'erreur et redirection vers la liste.

---

## 6. Données de référence

Les références sont des **listes de valeurs partagées** gérées via `/admin/references/{type}` :

| Type | Exemples |
|------|----------|
| `elements` | Pyro, Hydro, Electro, Cryo, Anemo, Geo, Dendro |
| `types-armes` | Épée, Claymore, Catalyseur, Arc, Lance |
| `etoiles` | 4★, 5★ |
| `reactions` | Vaporiser, Fondre, Électrocharger… |

---

## 7. Règles métier

| Règle | Détail |
|-------|--------|
| Permission | `admin.can:encyclopedie` obligatoire sur toutes les routes |
| Slug | Auto-généré depuis le nom à la création |
| Images | Stockées dans `storage/app/public/photos/` |
| Création personnage | Crée un brouillon en base puis redirige vers l'éditeur |
| Blocs AJAX | Chaque bloc est validé et sauvegardé indépendamment |

---

## 8. Messages & cas limites

| Situation | Comportement |
|-----------|-------------|
| Nom déjà existant | Erreur "Ce nom est déjà utilisé" |
| Références manquantes à la création | Erreur + redirection vers la liste |
| Image invalide | Erreur de validation |
| Bloc AJAX avec données invalides | Code 422 + message JSON d'erreur |
| Admin sans permission `encyclopedie` | Erreur 403 |

---

## 9. Dépendances techniques

- **`PersonnageController`** (Admin) : CRUD + création de brouillon.
- **`PersonnageBlockController`** : toutes les routes AJAX de l'éditeur par blocs.
- **Controllers** : `ArmeController`, `EnnemiController`, `AnimalController`, `CuisineController`, `NationController`, `RoleController`, `ReferenceController`, `ArtefactController`.
- **Alpine.js** : gestion de l'interface de l'éditeur avancé côté client.
- **Middleware** : `admin`, `2fa.admin`, `admin.can:encyclopedie`.
