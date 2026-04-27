# Documentation fonctionnelle — Outils joueur (protégés)

> **Pour qui ?** Développeur reprenant le projet ou développeur junior.  
> **Niveau** : ⭐⭐ Intermédiaire — authentification, tables pivot, aléatoire.

---

## 1. Objectif

Les outils protégés sont réservés aux **joueurs connectés** (et ayant validé la 2FA si activée). Ils offrent des fonctionnalités plus poussées liées à la collection personnelle du joueur.

| Outil | URL | Fonctionnalité |
|-------|-----|-----------------|
| Roulette personnage | `/outils/roulette-personnage` | Tire un personnage aléatoire depuis **ma** collection |
| Roulette team | `/outils/roulette-team` | Génère une équipe de 4 depuis **tous** les personnages |
| Générateur team | `/outils/team` | Génère une équipe avec un personnage fixé optionnel |
| Comparateur | `/outils/comparateur` | *(en cours)* Compare deux personnages |

---

## 2. En tant que joueur connecté…

> *Je veux tirer aléatoirement un personnage parmi ma collection pour décider lequel améliorer.*

> *Je veux générer une équipe surprise de 4 personnages pour pimenter mes sessions.*

> *Je veux générer une équipe en fixant un personnage que je veux jouer absolument.*

---

## 3. Outil 1 — Roulette personnage

### Parcours

1. Le joueur accède à `/outils/roulette-personnage`.
2. La page liste **ses** personnages (ceux liés à son compte dans la table `joueur_personnage`).
3. Il peut lancer la roulette — un personnage est mis en avant aléatoirement.
4. Il peut **confirmer** ce personnage pour le marquer "\u00e0 améliorer" en base de données.
5. Le serveur **vérifie** que le personnage appartient bien au joueur avant tout enregistrement.

> ⚠️ **Sécurité** : même si un joueur modifie manuellement le formulaire pour envoyer un ID qui ne lui appartient pas, le serveur refuse (vérification de propriété + erreur 403).

---

## 4. Outil 2 — Roulette team

### Parcours

1. Le joueur accède à `/outils/roulette-team`.
2. Il lance la génération : **4 personnages** sont tirés aléatoirement depuis **TOUS** les personnages de la base (pas uniquement sa collection).
3. L'équipe est affichée à l'écran.
4. **Aucune sauvegarde** : l'équipe est éphémère (disparaît au rechargement).

---

## 5. Outil 3 — Générateur team (`/outils/team`)

### Parcours

1. Le joueur accède à `/outils/team`.
2. Il peut optionnellement **fixer un personnage** (sélectionné depuis un menu déroulant).
3. Il soumet pour générer une équipe :
   - Si un personnage est fixé : il est inclus + 3 aléatoires parmi les autres.
   - Si aucun personnage fixé : 4 aléatoires.
4. L'équipe est affichée. **Aucune sauvegarde**.

---

## 6. Outil 4 — Comparateur *(en cours de développement)*

- Accessible à `/outils/comparateur`.
- L'interface permet de sélectionner deux personnages.
- La comparaison est actuellement **côté client uniquement** (JavaScript).
- Aucune route serveur de comparaison persistée n'est prévue à ce stade.

---

## 7. Règles métier

| Règle | Détail |
|-------|--------|
| Accès | Middleware `auth` + `2fa.user` sur toutes ces routes |
| Roulette personnage | Uniquement les personnages du joueur (jointure `joueur_personnage`) |
| Confirmation roulette | Vérification serveur de propriété — refus 403 si non possédé |
| Roulette team / Team | Utilise TOUS les personnages de la base, pas la collection du joueur |
| Sauvegarde | Roulette team et générateur team : **aucune persistance** |

---

## 8. Messages & cas limites

| Situation | Comportement |
|-----------|-------------|
| Joueur sans personnages (roulette perso) | Liste vide, message informatif |
| Personnage non possédé soumis en POST | Erreur 403 Forbidden |
| Base de personnages vide (roulette team) | Équipe vide, message informatif |
| Utilisateur non connecté | Redirigé vers `/login` automatiquement |

---

## 9. Dépendances techniques

- **`RoulettePersonnageController`** : index + confirmer.
- **`RouletteTeamController`** : index + generer.
- **`OutilsController`** : roulette (alias), team, teamGenerer, comparateur.
- **Middleware** : `auth` + `2fa.user`.
- **Table pivot** `joueur_personnage` : lien entre un `User` et ses `Personnage` avec les champs `niveau`, `perso_amelioration`.
