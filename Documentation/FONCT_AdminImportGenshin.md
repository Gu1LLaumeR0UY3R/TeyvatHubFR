# Documentation fonctionnelle — Import données Genshin (Admin)

> **Pour qui ?** Développeur reprenant le projet ou développeur junior.  
> **Niveau** : ⭐⭐ Intermédiaire — commande Artisan, API externe, ordre d'import.

---

## 1. Objectif

L'import Genshin permet de **synchroniser automatiquement** les données de base du jeu (personnages, armes, éléments, types d'armes…) depuis une **API externe** vers la base de données TeyvatHub.

Sans cet import, toutes les données doivent être saisies manuellement via les formulaires CRUD.

> **Qui peut l'utiliser ?** Uniquement les admins avec la permission `import`.

---

## 2. En tant qu'admin avec la permission "import"…

> *Je veux lancer l'import depuis le tableau de bord pour peupler automatiquement la base avec les personnages et armes Genshin.*

> *Je veux que l'import n'écrase pas mes données personnalisées existantes.*

> *Je veux être informé si l'import échoue.*

---

## 3. Parcours utilisateur

1. L'admin accède au tableau de bord `/admin`.
2. Il clique sur le bouton **"Importer les données Genshin"**.
3. Le système lance la commande Artisan `import:genshin` en arrière-plan.
4. L'admin reçoit un **message de succès** ou un **message d'erreur** selon le résultat.

---

## 4. Que fait l'import concrètement ?

L'import appelle une API externe et enregistre les données en base dans cet **ordre précis** (l'ordre est obligatoire pour respecter les dépendances entre tables) :

| Étape | Données importées | Pourquoi en premier |
|-------|-------------------|-----------------------|
| 1 | Éléments (Pyro, Hydro…) | Les personnages dépendent des éléments |
| 2 | Types d'armes (Épée, Arc…) | Les personnages et armes dépendent des types |
| 3 | Personnages | Dépend de : éléments + types d'armes |
| 4 | Armes | Dépend de : types d'armes |
| 5 | Matériaux | Indépendant |
| 6 | Nations | Indépendant |

> **Pourquoi cet ordre est-il obligatoire ?** Si on importait les personnages avant les éléments, la clé étrangère `fid_element` ne trouverait rien et l'import échouerait.

### Principe `updateOrCreate`

Chaque entité est créée **ou mise à jour** si elle existe déjà :
- Si le personnage "Hu Tao" existe déjà → ses champs sont mis à jour depuis l'API.
- S'il n'existe pas → il est créé.
- **Aucun doublon** n'est généré.

### Stockage des images

Les images ne sont **pas téléchargées** localement. L'URL externe est stockée directement dans le champ `source_url` de la table `Photo`.

> **Pourquoi ?** Pour éviter de remplir l'espace disque du serveur avec des centaines d'images. L'image s'affiche depuis le CDN de l'API externe.

---

## 5. Règles métier

| Règle | Détail |
|-------|--------|
| Permission | `admin.can:import` |
| Idémpotent | Peut être lancé plusieurs fois sans créer de doublons |
| Images | Stockées avec l'URL externe (`source_url`), pas téléchargées localement |
| Ordre d'import | Éléments → Types d'armes → Personnages → Armes → … (strict) |
| Erreur API | Logée dans `storage/logs/laravel.log` + message d'erreur affiché à l'admin |

---

## 6. Messages & cas limites

| Situation | Comportement |
|-----------|-------------|
| API externe indisponible | Message d'erreur affiché, log enregistré, base non affectée |
| Import partiel (erreur en cours d'import) | Les données déjà importées restent en base |
| Double import | Aucun doublon grâce à `updateOrCreate()` |
| Admin sans permission `import` | Erreur 403 |

---

## 7. Dépendances techniques

- **`AdminController@importGenshin`** : déclenche la commande Artisan et retourne le résultat.
- **Commande Artisan `import:genshin`** : logique d'import complète (dans `app/Console/Commands/`).
- **Modèles** : `Elements`, `TypeArme`, `Personnage`, `Arme`, `Materiaux`, `Nation`, `Photo`.
- **`Http` facade Laravel** : appels HTTP vers l'API externe avec timeout configurable.
- **`Log` facade** : enregistrement des erreurs dans les logs applicatifs.
- **Middleware** : `admin`, `2fa.admin`, `admin.can:import`.
