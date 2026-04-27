# Documentation fonctionnelle — Accueil & Navigation

---

## 1. Objectif

La page d'accueil est la **première page** que voit un visiteur arrivant sur TeyvatHub. Elle doit :
- Présenter le site de manière attractive.
- Montrer rapidement les derniers personnages ajoutés.
- Afficher les prochains événements Genshin Impact.
- Permettre d'accéder à toutes les sections via le menu de navigation.

---

## 2. En tant que visiteur…

> *En tant que visiteur anonyme, je veux voir les dernières actualités du site dès l'accueil pour savoir si le contenu est à jour.*

> *En tant que joueur connecté, je veux que la navigation affiche mon avatar et un lien vers mon profil.*

---

## 3. Parcours utilisateur

1. L'utilisateur arrive sur `https://teyvathub.fr/`.
2. Il voit un **hero visuel** (bannière Genshin Impact) avec le slogan du site.
3. Une section affiche les **6 derniers personnages** ajoutés (photo, nom, élément, rareté).
4. Une section affiche les **4 prochains événements** Genshin (titre, date de début).
5. Le menu de navigation en haut propose les liens vers toutes les sections.
6. S'il n'est pas connecté, le menu affiche "Connexion" et "Inscription".
7. S'il est connecté, le menu affiche son pseudo et un lien vers son profil.

---

## 4. Règles métier

- Les 6 personnages affichés sont les 6 derniers enregistrés en base (triés par date d'insertion décroissante).
- Les événements affichés sont ceux dont la `date_debut` est dans le futur (ou en cours).
- Si aucun événement n'est trouvé, la section est masquée (pas de message d'erreur).
- Si aucun personnage n'est en base, la section personnages est vide (pas de crashdu site).

---

## 5. Navigation — structure du menu

| Lien | URL | Visible |
|------|-----|---------|
| Encyclopédie → Personnages | `/personnages` | Toujours |
| Encyclopédie → Armes | `/armes` | Toujours |
| Encyclopédie → Ennemis | `/ennemis` | Toujours |
| Encyclopédie → Animaux | `/animaux` | Toujours |
| Cuisine | `/cuisine` | Toujours |
| Matériaux | `/materiaux` | Toujours |
| Histoire | `/histoire` | Toujours |
| Blog | `/blog` | Toujours |
| Outils | `/outils/quiz` | Toujours |
| Mon profil | `/profil` | Joueur connecté uniquement |
| Connexion | `/login` | Visiteur non connecté |
| Inscription | `/register` | Visiteur non connecté |
| Admin | `/admin` | Admin connecté uniquement |

---

## 6. Messages & cas limites

| Situation | Comportement |
|-----------|-------------|
| Base de données vide (0 personnages) | La section personnages est vide, aucune erreur |
| Aucun événement à venir | La section événements est masquée |
| Utilisateur banni | Déconnecté automatiquement, message d'erreur affiché |
| Site en maintenance | Page 503 standard Laravel |

---

## 7. Accessibilité

- Le menu de navigation est accessible au clavier (tabulation).
- Les images de personnages ont un attribut `alt` avec le nom du personnage.
- Les liens du menu ont des libellés explicites (pas de "Cliquez ici").
