# Documentation fonctionnelle — Jeu Motus

> **Pour qui ?** Développeur reprenant le projet ou développeur junior.  
> **Niveau** : ⭐⭐ Intermédiaire — AJAX, seed déterministe, normalisation de chaînes.

---

## 1. Objectif

Le Motus est un **mini-jeu de devinette** inspiré du jeu télévisé français, adapté à l'univers Genshin Impact. Le joueur doit deviner un mot lié au jeu (nom de personnage, arme, ennemi ou nation) lettre par lettre.

Ce jeu est **public** — aucune connexion n'est requise pour jouer.

---

## 2. En tant que visiteur…

> *Je veux jouer au Motus pour m'amuser et tester mes connaissances sur Genshin Impact.*

> *Je veux comprendre pourquoi chaque lettre est colorée pour affiner mes propositions.*

---

## 3. Mécanisme du jeu

### Règles de base

1. Chaque jour, un **mot secret** est tiré automatiquement de la base de données.
2. Le **même mot** est utilisé pour tous les joueurs toute la journée (seed déterministe par date).
3. Le joueur saisit un mot de la longueur indiquée et valide.
4. Chaque lettre reçoit un code couleur :

| Couleur | Signification |
|---------|---------------|
| 🟩 **Vert** (`correct`) | La lettre est à la **bonne position** |
| 🟨 **Orange** (`present`) | La lettre est dans le mot, mais **pas à cette position** |
| ⬛ **Gris** (`absent`) | La lettre **n'est pas** dans le mot |

5. Le joueur peut faire **plusieurs tentatives** jusqu'à trouver ou s'arrêter.

### Exemple concret

Mot secret : **MONDSTADT** (10 lettres)  
Proposition : **MONDRAGONS**  
Résultat : M🟩 O🟩 N🟩 D🟩 R⬛ A🟨 G⬛ O⬛ N🟨 S⬛

---

## 4. Comment le mot du jour est-il sélectionné ?

- Le service `MotusService` récupère **tous les noms** de personnages, armes, ennemis et nations.
- Il filtre les noms de **moins de 3 caractères** (trop courts pour jouer).
- Il trie la liste alphabétiquement, puis calcule `crc32(date_du_jour)` pour obtenir un index fixe.
- Le mot à cet index est le **mot du jour**.

→ Même calcul pour tout le monde → même mot pour tout le monde le même jour.  
→ Le lendemain, la date change → index différent → nouveau mot.

---

## 5. Parcours utilisateur

1. L'utilisateur navigue vers `/jeux/motus`.
2. La page affiche : longueur du mot, grille de saisie vide.
3. Il saisit un mot et clique "Valider".
4. Une **requête AJAX** est envoyée à `POST /jeux/motus/valider` (sans rechargement de page).
5. Le serveur retourne un tableau JSON avec le statut de chaque lettre.
6. Les cases se colorent automatiquement.
7. Le joueur continue jusqu'à trouver ou épuiser ses essais.

---

## 6. Règles métier

| Règle | Détail |
|-------|--------|
| Sélection du mot | Déterministe par date — même mot toute la journée pour tous les joueurs |
| Sources des mots | Personnages + Armes + Ennemis + Nations (longueur ≥ 3 caractères) |
| Comparaison | Insensible à la casse, accents normalisés (é → e, è → e…) |
| Validation | Côté **serveur** via `POST /jeux/motus/valider`, réponse JSON |
| Victoire | Toutes les lettres avec statut `correct` |
| Persistance | **Aucune** — le score n'est pas sauvegardé, pas de compte requis |

---

## 7. Messages & cas limites

| Situation | Comportement |
|-----------|-------------|
| Mot de longueur différente | Erreur de validation (422) côté serveur |
| Proposition vide | Refusée côté serveur |
| Base de données vide (aucun mot) | Mot par défaut `"Mondstadt"` utilisé |

---

## 8. Dépendances techniques

- **`MotusService`** (`app/Services/MotusService.php`) : pool de mots, sélection du mot du jour, validation d'une proposition.
- **`MotusController`** : `index()` (affichage) et `valider()` (AJAX JSON).
- **Modèles** : `Personnage`, `Arme`, `Ennemi`, `Nation` (pour alimenter le pool).
- **Aucun middleware** : jeu entièrement public.

---

## 9. Glossaire

| Terme | Explication |
|-------|-------------|
| AJAX | Requête HTTP faite en JavaScript sans recharger la page |
| crc32 | Fonction de hachage rapide — donne un entier à partir d'une chaîne (ici la date) |
| Seed déterministe | Même entrée → même résultat. `crc32("2026-04-27")` donne toujours le même nombre. |
| TOTP | Ici non pertinent — voir le jeu Motus uniquement |
