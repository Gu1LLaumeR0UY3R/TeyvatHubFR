# Documentation fonctionnelle — Gestion des utilisateurs (Admin)

> **Pour qui ?** Développeur reprenant le projet ou développeur junior.  
> **Niveau** : ⭐ Débutant — CRUD simple avec logique de ban/déban.

---

## 1. Objectif

Ce module permet aux administrateurs avec la **permission `utilisateurs`** de gérer les comptes joueurs :

- Consulter la liste de tous les comptes.
- Voir le détail d'un compte (informations, UID Genshin, date d'inscription…).
- Créer manuellement un compte joueur.
- Modifier les informations d'un joueur.
- **Bannir** ou **débannir** un joueur.
- Supprimer un compte.

---

## 2. En tant qu'admin avec la permission "utilisateurs"…

> *Je veux voir la liste de tous les joueurs inscrits.*

> *Je veux bannir un joueur abusif pour l'empêcher d'accéder au site.*

> *Je veux débannir un joueur après examen de sa situation.*

---

## 3. Parcours — Liste des utilisateurs

1. L'admin accède à `/admin/utilisateurs`.
2. Il voit un tableau paginé (20 par page) de tous les comptes joueurs.
3. Il peut **trier** par : nom, email, statut (banni / actif).
4. Chaque ligne affiche : nom, email, date d'inscription, statut banni/actif.

---

## 4. Parcours — Bannir un joueur

1. L'admin accède à la fiche du joueur (ex: `/admin/utilisateurs/42`).
2. Il clique sur **"Bannir"**.
3. Il peut saisir un **motif de ban** (texte libre, visible uniquement de l'admin).
4. Le joueur est banni :
   - La colonne `banni_le` est renseignée avec la date et l'heure.
   - La colonne `motif_ban` est renseignée avec le motif.
5. À sa prochaine tentative de connexion, le joueur est **bloqué**.

---

## 5. Parcours — Débannir un joueur

1. L'admin accède à la fiche du joueur banni.
2. Il clique sur **"Débannir"**.
3. Les champs `banni_le` et `motif_ban` sont remis à `null`.
4. Le joueur peut se reconnecter normalement.

---

## 6. Parcours — Création manuelle d'un compte joueur

1. L'admin accède à `/admin/utilisateurs/create`.
2. Il saisit : **nom**, **email**, **mot de passe**.
3. Le compte est créé avec le mot de passe hashé en Bcrypt.
4. Le joueur peut se connecter avec ces identifiants sur `/login`.

> Utile pour créer des comptes tests ou des comptes pour des utilisateurs n'ayant pas accès à l'email d'inscription.

---

## 7. Règles métier

| Règle | Détail |
|-------|--------|
| Permission | `admin.can:utilisateurs` |
| Bannissement | Champs `banni_le` (datetime) et `motif_ban` (texte) dans la table `users` |
| Détection du ban | Le middleware auth vérifie si `banni_le` est renseigné à chaque connexion |
| Mot de passe | Hashé avec Bcrypt via `Hash::make()`, jamais stocké en clair |
| Email | Unique dans la table `users` |
| Suppression | Définitive, supprime le compte et ses données liées |

---

## 8. Messages & cas limites

| Situation | Comportement |
|-----------|-------------|
| Email déjà utilisé (création) | Erreur de validation |
| Joueur déjà banni | Le bouton "Débannir" est affiché à la place de "Bannir" |
| Admin sans permission `utilisateurs` | Erreur 403 |
| Suppression d'un compte avec données | Les tables pivot (joueur_personnage, joueur_arme) sont nettoyées en cascade |

---

## 9. Dépendances techniques

- **`UtilisateurController`** (Admin) : index, create, store, show, edit, update, destroy.
- **Modèle `User`** : champs `banni_le`, `motif_ban`, `password` (hashage automatique via `casts`).
- **Middleware** : `admin`, `2fa.admin`, `admin.can:utilisateurs`.
