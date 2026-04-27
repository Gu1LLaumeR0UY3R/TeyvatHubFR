# Documentation fonctionnelle — Événements & Chronologie (Admin)

> **Pour qui ?** Développeur reprenant le projet ou développeur junior.  
> **Niveau** : ⭐ Débutant — CRUD simple, pas de logique métier complexe.

---

## 1. Objectif

Ce module permet aux administrateurs avec la **permission `evenements`** de gérer deux types de contenus distincts :

1. Les **événements Genshin Impact** (bannières, événements limités…) affichés sur la page d'accueil.
2. La **chronologie narrative** du lore de Genshin, affichée sur la page `/histoire`.

> **À ne pas confondre** :
> - Événement = actualité en jeu (bannière de Hu Tao, fête des lanternes…)
> - Chronologie = événement **narratif du lore** (la catastrophe de Khaeenri'ah, la guerre des dieux…)

---

## 2. En tant qu'admin avec la permission "evenements"…

> *Je veux ajouter un événement Genshin avec ses dates pour qu'il apparaisse sur la page d'accueil.*

> *Je veux ajouter une entrée à la chronologie du lore et définir sa position dans la timeline.*

> *Je veux réordonner les entrées de la chronologie.*

---

## 3. Gestion des événements

### Parcours — Créer un événement

1. L'admin accède à `/admin/evenements/create`.
2. Il saisit : **titre**, **date de début**, **date de fin**, **description** (optionnelle).
3. Il soumet. L'événement est créé.
4. Sur la page d'accueil, les événements dont `date_fin >= aujourd'hui` apparaissent automatiquement.

### Affichage sur la page d'accueil

La page d'accueil affiche les **4 prochains événements** dont la date de fin est dans le futur, triés par date de début croissante.

Si un événement est passé (date_fin < aujourd'hui), il disparaît automatiquement de l'accueil **sans intervention admin**.

---

## 4. Gestion de la chronologie du lore

### Qu'est-ce que la chronologie ?

La chronologie est une liste ordonnée d'événements **narratifs** de l'univers Genshin. Chaque entrée a un numéro d'ordre (`ordre`) qui détermine sa position dans la timeline affichée sur `/histoire`.

### Parcours — Créer une entrée chronologique

1. L'admin accède à `/admin/chronologie/create`.
2. Il saisit :
   - **Titre** (obligatoire)
   - **Résumé** (texte libre, optionnel)
   - **Période** (ex: "500 ans avant l'Ère actuelle", optionnel)
   - **Ordre** (entier, obligatoire) — détermine la position dans la timeline
   - **Nation associée** (optionnel)
3. Il soumet. L'entrée apparaît sur `/histoire` à la position définie par `ordre`.

### Réordonner la chronologie

- L'admin modifie le champ `ordre` dans le formulaire d'édition.
- Les entrées sont toujours affichées en **ordre croissant** du champ `ordre` sur la page publique.
- Deux entrées peuvent avoir le même `ordre` (affichage dans l'ordre d'insertion dans ce cas).

---

## 5. Règles métier

| Règle | Détail |
|-------|--------|
| Permission | `admin.can:evenements` |
| Événements accueil | Uniquement ceux dont `date_fin >= date du jour` |
| Nombre affichés sur l'accueil | 4 maximum |
| Ordre chronologie | Champ entier, trié croissant sur la page publique |
| Nation chronologie | Lien optionnel vers une nation (clé étrangère) |

---

## 6. Messages & cas limites

| Situation | Comportement |
|-----------|-------------|
| Date de fin passée | Événement non affiché à l'accueil (mais conservé en base) |
| Ordre dupliqué | Affichage dans l'ordre d'insertion de la base |
| Nation liée inexistante | Erreur de validation |
| Admin sans permission `evenements` | Erreur 403 |

---

## 7. Dépendances techniques

- **`EvenementController`** (Admin) : CRUD des événements.
- **`ChronologieController`** (Admin) : CRUD de la chronologie.
- **Modèles** : `Evenement`, `Chronologie`, `Nation`.
- **`HomeController`** : utilise `Evenement` pour afficher les prochains événements sur l'accueil.
- **Middleware** : `admin`, `2fa.admin`, `admin.can:evenements`.
