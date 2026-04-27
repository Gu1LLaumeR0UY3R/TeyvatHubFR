# Documentation fonctionnelle — Gestion des comptes admin

> **Pour qui ?** Développeur reprenant le projet ou développeur junior.  
> **Niveau** : ⭐⭐ Intermédiaire — gestion de rôles, permissions JSON, sécurité accès.

---

## 1. Objectif

Ce module permet aux **super-administrateurs** (ou admins avec la permission `admins`) de :

- Créer des comptes administrateurs.
- Modifier leurs informations et leurs permissions.
- Supprimer des comptes.

> ⚠️ Cette fonctionnalité est très sensible : elle permet de donner ou retirer des droits d'administration complets.

---

## 2. En tant que super-administrateur…

> *Je veux créer un compte admin pour un nouveau membre de l'équipe et lui assigner seulement les permissions dont il a besoin.*

> *Je veux modifier les permissions d'un admin existant sans changer son mot de passe.*

> *Je veux supprimer un compte admin qui n'est plus utilisé.*

---

## 3. Système de rôles et permissions

### Rôles

| Rôle | Détail |
|------|--------|
| `super_admin` | Accès à **toutes** les fonctionnalités, sans restriction |
| `admin` | Accès uniquement aux fonctionnalités pour lesquelles il a une permission |

### Permissions disponibles

| Permission | Ce que ça permet |
|------------|-------------------|
| `encyclopedie` | CRUD personnages, armes, ennemis, animaux, cuisine, nations, rôles, références |
| `blog` | CRUD articles de blog, slugs, images |
| `evenements` | CRUD événements et chronologie |
| `utilisateurs` | Consulter et modérer les comptes joueurs |
| `admins` | Créer/modifier/supprimer des comptes admin |
| `import` | Lancer l'import des données depuis l'API Genshin |

> **Note technique** : les permissions sont actuellement stockées en **JSON** dans la colonne `permissions` de la table `admin`. Cette approche est fonctionnelle mais une refonte est prévue avec une bibliothèque dédiée (ex: Spatie Laravel Permission) pour une gestion plus propre et plus testable.

---

## 4. Parcours — Créer un admin

1. Le super-admin accède à `/admin/admins/create`.
2. Il saisit : **pseudo**, **email**, **mot de passe**, **rôle**, **permissions** (cases à cocher).
3. Il soumet. Le compte est créé avec le mot de passe hashé en Bcrypt.
4. Le nouvel admin peut immédiatement se connecter sur `/admin/login`.

---

## 5. Parcours — Modifier un admin

1. Depuis la liste `/admin/admins`, cliquer sur "Modifier".
2. L'admin peut changer : **pseudo**, **email**, **permissions**, **photo de profil**.
3. Le **mot de passe** ne peut être changé que si un nouveau est saisi explicitement.
4. Il sauvegarde.

---

## 6. Règles métier

| Règle | Détail |
|-------|--------|
| Accès | Permission `admin.can:admins` (ou `super_admin`) |
| Super admin | A toutes les permissions automatiquement |
| Mot de passe | Hashé Bcrypt, jamais stocké en clair |
| Permissions vides | Si `permissions = []`, l'admin a accès à tout par règle legacy (comportement hérité) |
| Auto-suppression | Un admin ne peut pas supprimer son propre compte |
| Email | Unique dans la table `admin` |

---

## 7. Messages & cas limites

| Situation | Comportement |
|-----------|-------------|
| Email déjà utilisé | Erreur "Cet email est déjà pris" |
| Admin sans permission `admins` | Erreur 403 |
| Suppression de son propre compte | Bloqué côté serveur |

---

## 8. Dépendances techniques

- **`AdminManageController`** : CRUD des comptes admin.
- **Modèle `Admin`** : champs `role`, `permissions` (JSON casté), `two_factor_enabled`.
- **Méthode `Admin::can(string $permission)`** : retourne `true` si l'admin a la permission demandée (ou s'il est super_admin).
- **Middleware** : `admin`, `2fa.admin`, `admin.can:admins`.
