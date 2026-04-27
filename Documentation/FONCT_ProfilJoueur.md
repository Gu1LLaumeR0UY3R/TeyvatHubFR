# Documentation fonctionnelle — Profil joueur

> **Pour qui ?** Développeur reprenant le projet ou développeur junior.  
> **Niveau** : ⭐⭐ Intermédiaire — authentification, API externe, amitiés.

---

## 1. Objectif

La section profil regroupe toutes les fonctionnalités **personnelles** d'un joueur connecté :

- Consulter son **tableau de bord** (statistiques de collection).
- Voir la liste de ses **personnages** et **armes**.
- Gérer ses **paramètres** (pseudo, avatar, bio, UID Genshin).
- **Importer** ses personnages depuis Genshin Impact via son UID.
- Gérer ses **amis** (envoyer, accepter, refuser, supprimer une amitié).

Toutes ces pages nécessitent d'être **connecté** (et d'avoir validé la 2FA si activée).

---

## 2. En tant que joueur connecté…

> *Je veux voir en un coup d'œil combien de personnages et armes j'ai en collection.*

> *Je veux importer mes personnages depuis le jeu en entrant mon UID Genshin.*

> *Je veux ajouter d'autres joueurs TeyvatHub en ami pour suivre leur progression.*

---

## 3. Page tableau de bord (`/profil`)

1. Le joueur accède à `/profil`.
2. Il voit ses **statistiques** :
   - Nombre de personnages en collection.
   - Nombre d'armes en collection.
   - Nombre total de constellations débloquées.
   - Nombre de personnages à C6 (constellation maximale — tous les 6 niveaux débloqués).
3. Son nom d'utilisateur, son avatar et sa bio sont affichés.

---

## 4. Page personnages (`/profil/personnages`)

1. Le joueur voit la liste de **ses** personnages (ceux de sa collection, pas tous les personnages du jeu).
2. Triés par **niveau décroissant** (le plus haut niveau en premier).
3. Paginés à **20 par page**.

---

## 5. Page armes (`/profil/armes`)

- Même logique que les personnages, mais pour les armes.
- Triées par niveau décroissant, paginées à 20 par page.

---

## 6. Import UID Genshin

### Qu'est-ce que l'UID Genshin ?

L'UID est un **identifiant unique à 9 chiffres** attribué à chaque compte Genshin Impact. Il est visible en bas à droite de l'écran en jeu.

### Parcours

1. Le joueur va dans `/profil/parametres`.
2. Il saisit son **UID** (9 chiffres exactement).
3. Il soumet le formulaire.
4. Le serveur contacte l'API publique **Enka.Network** (`https://enka.network/api/uid/{uid}`).
5. L'API retourne les personnages présents dans le **showcase** du joueur (maximum 8 personnages).
6. Les données sont enregistrées en base (personnages, niveaux…).
7. Un message de succès indique le nombre de personnages importés.

> ⚠️ **Pré-requis côté jeu** : le joueur doit avoir activé l'option **"Afficher les détails des personnages"** dans les paramètres Genshin Impact. Sans ça, l'API ne renvoie rien.

---

## 7. Gestion des amis

### États d'une relation d'amitié

```
Non envoyée  →  en_attente  →  accepte
                             →  supprimée
```

| Action | Route | Méthode | Détail |
|--------|-------|---------|--------|
| Voir amis & demandes | `/profil/amis` | GET | Amis acceptés + demandes reçues + demandes envoyées |
| Envoyer une demande | `/profil/amis` | POST | Crée une relation `en_attente` |
| Accepter/refuser | `/profil/amis/{id}` | PATCH | Change le statut |
| Supprimer | `/profil/amis/{id}` | DELETE | Supprime la relation |

---

## 8. Règles métier

| Règle | Détail |
|-------|--------|
| UID | Exactement 9 chiffres |
| Enka.Network | Timeout 10 secondes. Si l'API ne répond pas → message d'erreur |
| Showcase | Limité à 8 personnages par l'API Genshin |
| Statut amitié | `en_attente` → `accepte` ou supprimé |
| Doublon | Impossible d'envoyer deux demandes au même joueur |
| Middleware | `auth` + `2fa.user` sur toutes les routes `/profil/*` |

---

## 9. Messages & cas limites

| Situation | Comportement |
|-----------|-------------|
| UID invalide (format) | Erreur "L'UID doit être composé de 9 chiffres" |
| Enka.Network indisponible | Message "Impossible de contacter l'API" |
| Showcase vide (option désactivée en jeu) | Message "Activez les détails dans Genshin Impact" |
| Ami déjà ajouté | Géré côté serveur (contrainte de duplication) |
| Utilisateur non connecté | Redirigé vers `/login` |

---

## 10. Dépendances techniques

- **`ProfilController`** : index, personnages, armes, paramètres.
- **`ImportController`** : `importUID()` — appel HTTP vers Enka.Network.
- **`AmiController`** : index, store, update, destroy.
- **Modèles** : `User`, `Amitie`, + tables pivot `joueur_personnage`, `joueur_arme`, `joueur_constellation`.
- **`Http` facade** : appel HTTP vers `enka.network`.
- **Middleware** : `auth` + `2fa.user`.
