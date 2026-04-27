# Documentation fonctionnelle — Outils publics

> **Pour qui ?** Développeur reprenant le projet ou développeur junior.  
> **Niveau** : ⭐ Débutant — lecture seule, aucune authentification.

---

## 1. Objectif

Les outils publics sont des fonctionnalités interactives accessibles **sans connexion**. Ils permettent aux visiteurs de s'amuser avec les personnages de Genshin Impact.

Deux outils sont disponibles :
1. **Personnage du jour** — un personnage mis en avant chaque jour, le même pour tout le monde.
2. **Quiz** — une question à choix multiples : deviner un personnage à partir de sa photo.

---

## 2. En tant que visiteur…

> *Je veux découvrir le personnage Genshin mis en avant aujourd'hui sans avoir à m'inscrire.*

> *Je veux tester mes connaissances en devinant un personnage depuis sa photo.*

---

## 3. Outil 1 — Personnage du jour (`/outils/personnage-du-jour`)

### Parcours utilisateur

1. L'utilisateur navigue vers `/outils/personnage-du-jour`.
2. Un personnage s'affiche avec sa **photo, son nom, son élément et sa rareté**.
3. Ce personnage est **identique pour tous les visiteurs** le même jour.
4. Le lendemain, un autre personnage est sélectionné automatiquement.

### Comment le personnage est-il sélectionné ?

Le même mécanisme que le Motus : un **seed déterministe par date**.

```
index = abs(crc32("2026-04-27")) % nombre_total_personnages
personnage = Personnage en base à l'index calculé
```

→ Pas de table "personnage du jour". Tout est calculé à la volée.  
→ Le résultat est constant toute la journée.

---

## 4. Outil 2 — Quiz (`/outils/quiz`)

### Parcours utilisateur

1. L'utilisateur navigue vers `/outils/quiz`.
2. Il voit la **photo** d'un personnage aléatoire + **4 noms** proposés (dont le bon).
3. Il clique sur le nom qu'il croit correct.
4. Le formulaire est soumis à `POST /outils/quiz/resultat`.
5. La page résultat lui indique s'il avait raison, et affiche le **nom correct**.

### Comment les choix sont-ils générés ?

1. Tous les personnages sont récupérés depuis la base.
2. Ils sont mélangés aléatoirement (`->shuffle()`).
3. Le **premier** est désigné comme la bonne réponse.
4. Les **4 premiers** (y compris la bonne réponse) sont proposés comme choix.

→ Aléatoire à chaque chargement de page (pas de seed fixe pour le quiz).

---

## 5. Règles métier

| Règle | Détail |
|-------|--------|
| Personnage du jour | Même résultat pour tous, toute la journée. Change à minuit. |
| Quiz | Aléatoire à chaque chargement. Différent pour chaque visiteur. |
| Minimum pour le quiz | 4 personnages en base requis. En-dessous : quiz désactivé. |
| Validation quiz | Côté serveur : `reponse` et `correct` sont obligatoires (string) |
| Aucune persistance | Ni le score du quiz, ni la vue du personnage du jour ne sont sauvegardés |

---

## 6. Messages & cas limites

| Situation | Comportement |
|-----------|-------------|
| Aucun personnage en base (personnage du jour) | Message "Aucun personnage disponible" |
| Moins de 4 personnages (quiz) | `question` passé à null, vue affiche un message "Quiz indisponible" |
| Paramètres quiz manquants au POST | Erreur de validation 422 |

---

## 7. Dépendances techniques

- **`OutilsController`** : `personnageDuJour()`, `quiz()`, `quizResultat()`.
- **Modèle `Personnage`** avec ses relations `element`, `etoile`, `photos`.
- **Aucun middleware** : routes entièrement publiques.
- **Vues** : `resources/views/outils/personnage-du-jour.blade.php`, `outils/quiz.blade.php`, `outils/quiz-resultat.blade.php`.
