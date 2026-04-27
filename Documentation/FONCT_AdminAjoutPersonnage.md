# Documentation fonctionnelle — Ajouter un personnage en admin

> **Pour qui ?** Développeur ou administrateur reprenant le projet.  
> **Niveau** : ⭐⭐⭐ Avancé — brouillon automatique, éditeur par blocs, AJAX.

---

## 1. Objectif

Cette page explique **dans le détail complet** comment fonctionne l'ajout et la modification d'un personnage dans l'interface admin de TeyvatHub. C'est la fonctionnalité la plus complexe du site.

L'éditeur de personnage **n'est pas un simple formulaire**. C'est un éditeur multi-blocs où chaque section est sauvegardée indépendamment, sans rechargement de page.

---

## 2. Pourquoi ce système est particulier

Un formulaire classique envoie **tout d'un coup** au serveur. Ici, ce n'est pas le cas.

**Problème** : la fiche d'un personnage peut contenir des dizaines de champs (6 constellations × titre + description + image, des compétences, des armes recommandées, des builds d'artefacts, du lore…). Un seul formulaire d'une telle taille serait ingérable.

**Solution choisie** : l'éditeur est découpé en **blocs indépendants**. Chaque bloc se sauvegarde séparément via une requête AJAX. L'administrateur peut fermer son navigateur après avoir sauvegardé la zone principale, rouvrir plus tard et compléter les constellations.

---

## 3. Étape 1 — Cliquer sur "Ajouter un personnage"

Quand l'admin clique sur **"Ajouter un personnage"**, il n'arrive **pas** sur un formulaire vide.

À la place, le serveur fait ceci automatiquement :

1. Il cherche le **premier élément** disponible en base (ex: Pyro).
2. Il cherche la **première étoile** disponible en base (ex: 4★).
3. Il cherche le **premier type d'arme** disponible en base (ex: Épée).
4. Il cherche le **premier type de personnage** disponible en base.
5. Si l'une de ces valeurs est manquante → message d'erreur, retour à la liste. **L'admin doit d'abord créer les données de référence.**
6. Sinon → un personnage est **créé immédiatement en base** avec le nom `"Brouillon XXXXX"` (XXXXX = identifiant unique aléatoire).
7. L'admin est redirigé vers l'éditeur de ce personnage brouillon.

> **Pourquoi créer un brouillon immédiatement ?**  
> Les blocs AJAX ont besoin d'un slug dans l'URL (ex: `/admin/personnages/brouillon-abc123/block/constellations`). Sans enregistrement préalable en base, il n'y a pas de slug, donc pas d'URL, donc impossible de sauvegarder quoi que ce soit.

---

## 4. Étape 2 — L'éditeur multi-blocs

Une fois redirigé, l'admin voit l'éditeur complet. Il contient **8 blocs** :

| # | Bloc | Ce qu'il permet de renseigner |
|---|------|-------------------------------|
| 1 | **Zone principale** | Nom, élément, rareté, type d'arme, nation(s), vidéos YouTube, fond d'écran |
| 2 | **Images** | Photo icône (512px max), photo portrait/full art (1600px max) |
| 3 | **Armes recommandées** | Jusqu'à 6 armes, avec rang de raffinement, origine, arme starter |
| 4 | **Artefacts recommandés** | Jusqu'à 6 builds : set 4P ou 2P+2P, stats principales sablier/gobelet/couronne, sous-stats |
| 5 | **Constellations** | 6 niveaux : titre, description, image icône individuelle |
| 6 | **Carte des constellations** | Image de la carte + placement des 6 points par glisser-déposer + lignes de connexion |
| 7 | **Compétences** | Attaque normale, compétence E, ultime Q, passives — avec icône pour chacune |
| 8 | **Histoires (lore)** | Textes de biographie, journaux, anecdotes (titre + corps de texte, ordre libre) |
| 9 | **Équipes** | Compositions de 4 personnages recommandées, avec réaction principale et remplaçants par slot |

---

## 5. Comment fonctionne une sauvegarde de bloc

Chaque bloc fonctionne de la même manière :

```
Admin modifie le bloc
        ↓
Admin clique "Sauvegarder ce bloc"
        ↓
JavaScript envoie une requête HTTP en arrière-plan (AJAX)
vers /admin/personnages/{slug}/block/{nom-du-bloc}
        ↓
Le serveur valide les données reçues
        ↓
    Si OK → enregistre en base + renvoie { success: true }
    Si erreur → renvoie { errors: [...] } avec code HTTP 422
        ↓
L'interface affiche "✓ Sauvegardé" ou les erreurs
(sans rechargement de page)
```

Les autres blocs **ne sont pas affectés**. L'admin peut sauvegarder la zone principale, puis les constellations, puis revenir sur la zone principale — chaque sauvegarde est indépendante.

---

## 6. Bloc Zone principale — règles en détail

C'est le premier bloc à remplir. Il contient les données identitaires du personnage.

| Champ | Obligatoire | Règle |
|-------|-------------|-------|
| Nom | ✅ | Max 100 caractères. Le slug est **recalculé** à chaque modification. |
| Élément | ✅ | Doit exister dans la table `elements` |
| Rareté | ✅ | Doit exister dans la table `etoile` |
| Type d'arme | ✅ | Doit exister dans `type_armes`. Détermine quelles armes sont proposées dans le bloc "Armes recommandées" |
| Nation(s) | ❌ | Tableau d'IDs, plusieurs nations possibles |
| Fond d'écran | ❌ | Sélectionné parmi les images de la galerie de la nation choisie |
| Vidéos YouTube | ❌ | URLs valides, ordre libre |

> ⚠️ **Le slug change si le nom change.** Toutes les routes AJAX utilisent le slug. Si on renomme "Brouillon-abc" en "Hu Tao", l'URL passe de `.../brouillon-abc/block/...` à `.../hu-tao/block/...`. La page se met à jour automatiquement.

---

## 7. Bloc Images — règles en détail

Deux types d'images séparées :

| Type | Nom interne | Dossier de stockage | Taille max redimensionnée |
|------|-------------|---------------------|--------------------------|
| Icône (buste) | `icone` | `photos/personnages/icones_personnage/` | 512×512 px |
| Portrait / Full art | `portrait` | `photos/personnages/personnage_full/` | 1600×1600 px |

**Redimensionnement automatique** : si l'image importée est plus grande que la limite, le serveur la redimensionne **en conservant les proportions** avant de la stocker. Aucune déformation. Les formats PNG et WebP gardent leur transparence.

Nom de fichier automatique : `{slug}-icon.{ext}` ou `{slug}-full.{ext}`.

---

## 8. Bloc Armes recommandées — règles en détail

| Règle | Détail |
|-------|--------|
| Nombre | 1 à 6 armes |
| Compatibilité | Chaque arme doit avoir le même type que le personnage (ex: si le personnage utilise une Épée, seules les Épées sont acceptées) |
| Arme starter | **Exactement une** arme doit être marquée "starter" (l'arme gratuite de référence, ex: l'Épée des Pèlerins). Elle est toujours affichée en dernier. |
| Rang | 1 à 5 (niveau de raffinement de l'arme) |
| Origine | `tirage`, `evenement`, `craft` ou `achat` |

Si aucune arme starter n'est définie → erreur de validation.

---

## 9. Bloc Artefacts recommandés — règles en détail

Un build d'artefacts est une **configuration de sets** avec les stats prioritaires.

| Règle | Détail |
|-------|--------|
| Nombre de builds | 1 à 6 |
| Composition | Set en 4 pièces (4P) **ou** deux sets en 2 pièces (2P+2P) |
| Build 2P+2P | Les deux sets doivent être **différents** |
| Stat sablier | Parmi : ATK%, HP%, DEF%, Recharge d'énergie%, Maîtrise élémentaire |
| Stat gobelet | Parmi : ATK%, HP%, DEF%, Maîtrise élémentaire, Bonus DGT Pyro/Hydro/Electro/Cryo/Anemo/Geo/Dendro/Physiques% |
| Stat couronne | Parmi : ATK%, HP%, DEF%, Maîtrise élémentaire, Taux CRIT%, DGT CRIT%, Bonus de soin% |
| Sous-stats | Max 4, **sans doublon**, parmi une liste fixe |

---

## 10. Bloc Constellations — règles en détail

Un personnage Genshin a exactement **6 constellations** (niveaux de déblocage via duplicatas).

| Règle | Détail |
|-------|--------|
| Nombre | Toujours 6 |
| Titre | Optionnel (défaut : "Constellation C1"…"C6") |
| Description | Optionnel, texte libre |
| Image icône | Upload individuel par constellation, stockée dans `photos/personnages/constellations/` |
| Nom de fichier | `{slug}-c1.{ext}` à `{slug}-c6.{ext}` |

Si une constellation n'existe pas encore en base au moment d'un upload d'image, elle est **créée automatiquement** pour accueillir l'image.

---

## 11. Bloc Carte des constellations — règles en détail

La carte est une **image de fond** sur laquelle l'admin place les 6 points de constellation par **glisser-déposer** dans l'interface.

| Élément | Détail |
|---------|--------|
| Image de fond | Upload ou URL externe |
| Points | 6 points (un par constellation), coordonnées en % de la taille de l'image (0–100) |
| Lignes | Connexions entre points, gérées par l'interface. Doublons supprimés automatiquement. Les deux sens (1→3 et 3→1) comptent comme un seul lien. |
| Stockage | JSON dans la colonne `positions_const` de la première ligne `constellation` du personnage |

Format du JSON stocké :
```json
{
  "points": {
    "1": { "x": 45.2, "y": 30.0 },
    "2": { "x": 60.5, "y": 45.1 },
    ...
  },
  "lines": [
    { "from": 1, "to": 2 },
    { "from": 2, "to": 3 }
  ]
}
```

---

## 12. Bloc Compétences — règles en détail

| Champ | Obligatoire | Détail |
|-------|-------------|--------|
| Titre | ✅ | Max 200 caractères |
| Type (TypeApti) | ✅ | Ex: Attaque normale, Compétence élémentaire, Ultime, Passive 1, Passive 2 |
| Description | ❌ | Texte libre |
| Niveau max | ❌ | Entier 1–15 |
| Image icône | ❌ | Upload séparé via le bloc, stockée dans `photos/personnages/aptitudes/` |

Les compétences supprimées (absentes de la liste envoyée) sont **effacées de la base**. Celles présentes avec un `id_aptitude` existant sont mises à jour. Celles sans ID sont créées.

---

## 13. Bloc Histoires (lore) — règles en détail

| Champ | Obligatoire | Détail |
|-------|-------------|--------|
| Titre | ✅ | Max 200 caractères |
| Texte | ✅ | Corps du texte, longueur libre |
| Ordre | Auto | Défini par la position dans le tableau envoyé (index 0, 1, 2…) |

Même logique que les compétences : les histoires supprimées sont effacées, les existantes mises à jour, les nouvelles créées.

---

## 14. Bloc Équipes — règles en détail

| Règle | Détail |
|-------|--------|
| Taille de l'équipe | Exactement **4 membres** |
| Slots | 1 à 4, pas de doublon de slot |
| Personnage principal | Le personnage de la fiche doit occuper le **slot 1** |
| Réaction principale | Texte libre (ex: "Vaporiser", "Électrocharger") |
| Tag | `recommended` ou `f2p` (optionnel) |
| Remplaçants | Par slot, liste optionnelle de personnages alternatifs |

On peut avoir plusieurs compositions d'équipes pour un même personnage (une F2P, une optimale, une pour un autre style de jeu).

---

## 15. Ordre des blocs

L'admin peut **réordonner les blocs** dans l'éditeur par glisser-déposer. L'ordre est sauvegardé dans la colonne `block_order` du personnage (chaîne de caractères, ex: `"main_zone,armes,constellations,competences,artefacts,histoires"`).

---

## 16. Supprimer un personnage

La suppression d'un personnage via `/admin/personnages/{slug}` (méthode DELETE) supprime l'enregistrement. Les données liées (constellations, aptitudes, photos, histoires, armes recommandées, artefacts recommandés, équipes) sont supprimées par cascade grâce aux contraintes de clé étrangère en base.

---

## 17. Dépendances techniques

- **`PersonnageController`** (Admin) : `create()` (brouillon), `edit()` (chargement de l'éditeur), `update()`, `destroy()`.
- **`PersonnageBlockController`** (Admin) : toutes les routes AJAX (16 routes).
- **Modèles** : `Personnage`, `Constellation`, `Aptitude`, `PersonnageHistoire`, `PersonnageArmeRecommandee`, `PersonnageArtefactRecommandee`, `TeamComposition`, `TeamCompositionMembre`, `TeamSlotRemplacant`, `Photo`.
- **Alpine.js** : interface de l'éditeur côté navigateur (glisser-déposer, prévisualisation d'images, appels AJAX).
- **GD Library (PHP)** : redimensionnement des images côté serveur.
- **Middleware** : `admin`, `2fa.admin`, `admin.can:encyclopedie`.
