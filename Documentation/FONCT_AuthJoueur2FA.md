# Documentation fonctionnelle — Authentification joueur & 2FA

> **Pour qui ?** Développeur reprenant le projet ou développeur junior PHP/Laravel.  
> **Niveau** : ⭐⭐ Intermédiaire — implique Breeze, sessions et TOTP.

---

## 1. Objectif

Cette fonctionnalité couvre tout le cycle de vie de l'identité d'un joueur sur TeyvatHub :

- **Inscription** : créer un compte avec email et mot de passe.
- **Connexion** : se connecter avec email/mot de passe (+ code 2FA si activé).
- **2FA (double facteur)** : ajouter une couche de sécurité supplémentaire via une application mobile (Google Authenticator, Authy…).
- **Gestion du profil** : modifier son nom, e-mail ou mot de passe depuis la page `/profile`.
- **Déconnexion** : invalider la session.

---

## 2. En tant que joueur…

> *En tant que nouveau visiteur, je veux créer un compte pour accéder aux fonctionnalités personnalisées.*

> *En tant que joueur avec un compte, je veux me connecter et sécuriser mon compte avec la 2FA.*

> *En tant que joueur connecté, je veux modifier mes informations personnelles depuis mon profil.*

---

## 3. Parcours — Inscription

1. Le visiteur clique sur **"Inscription"** dans le menu.
2. Il accède au formulaire `/register` (fourni par **Laravel Breeze**).
3. Il saisit : **nom**, **e-mail**, **mot de passe**, **confirmation du mot de passe**.
4. Il soumet le formulaire.
5. Laravel valide les données, crée le compte (mot de passe hashé), connecte le joueur automatiquement.
6. Le joueur est redirigé vers `/dashboard`.

---

## 4. Parcours — Connexion sans 2FA

1. Le joueur clique sur **"Connexion"**.
2. Il saisit son **e-mail** et **mot de passe** sur `/login`.
3. Si les identifiants sont corrects et que la 2FA est **désactivée**, il est connecté.
4. Il est redirigé vers `/dashboard`.

---

## 5. Parcours — Connexion avec 2FA activée

1. Même étapes 1 à 3.
2. Après validation du mot de passe, Laravel détecte que la 2FA est activée.
3. Le joueur est redirigé vers la page de challenge 2FA (middleware `2fa.user`).
4. Il ouvre son application d'authentification et saisit le **code à 6 chiffres**.
5. Si le code est valide → il accède au site.
6. Si le code est expiré ou invalide → message d'erreur, il peut réessayer.

---

## 6. Parcours — Activer la 2FA

1. Le joueur va dans **Profil → Paramètres → Sécurité**.
2. Il clique sur **"Activer la double authentification"**.
3. Un **QR code SVG** s'affiche (généré par `pragmarx/google2fa` + `BaconQrCode`).
4. Il scanne le QR code avec son application mobile.
5. Il saisit le code à 6 chiffres pour confirmer l'activation.
6. La 2FA est active. Le secret TOTP est stocké **chiffré** en base.

---

## 7. Règles métier

| Règle | Détail |
|-------|--------|
| Mot de passe | Minimum 8 caractères, hashé en Bcrypt automatiquement |
| E-mail | Unique en base, format valide |
| 2FA | Standard TOTP — RFC 6238 (même standard que Google Authenticator) |
| Secret 2FA | Stocké chiffré avec `Crypt::encryptString()` dans `two_factor_secret` |
| Session 2FA | Une fois validée, stockée en session PHP pour toute la durée de connexion |
| Middleware | Toutes les routes protégées joueur exigent `auth` + `2fa.user` |

---

## 8. Messages & cas limites

| Situation | Comportement |
|-----------|-------------|
| Email déjà utilisé à l'inscription | Erreur "Cet e-mail est déjà pris" |
| Mot de passe incorrect | Erreur générique sans préciser quel champ est faux (sécurité) |
| Code 2FA invalide ou expiré | Message "Code 2FA invalide", réessai possible |
| Session expirée en cours de challenge 2FA | Retour à `/login` |
| Compte banni | Middleware détecte le ban → déconnexion automatique |

---

## 9. Dépendances techniques

- **Laravel Breeze** : vues et controllers d'inscription/connexion/profil de base.
- **`pragmarx/google2fa`** : génération et vérification des codes TOTP.
- **`BaconQrCode`** : génération du QR code SVG affiché à l'activation.
- **Middleware `2fa.user`** : vérifie que le challenge 2FA est passé avant d'accéder aux routes protégées.
- **`TwoFactorController`** (`Auth/`) : endpoints enable/disable de la 2FA joueur.
- **`ProfileController`** : modification du profil de base (nom, email, mot de passe).

---

## 10. Glossaire

| Terme | Explication |
|-------|-------------|
| Breeze | Starter kit Laravel qui fournit l'auth de base (login, register, reset password) |
| TOTP | "Time-based One-Time Password" — code qui change toutes les 30 secondes |
| 2FA | Authentification à deux facteurs : mot de passe + code temporaire |
| Hash Bcrypt | Algorithme de hachage de mot de passe irréversible (standard Laravel) |
