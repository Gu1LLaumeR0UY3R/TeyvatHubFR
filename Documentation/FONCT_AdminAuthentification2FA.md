# Documentation fonctionnelle — Authentification admin & 2FA

> **Pour qui ?** Développeur reprenant le projet ou développeur junior.  
> **Niveau** : ⭐⭐⭐ Avancé — session manuelle, 2FA TOTP, séparation complète de l'auth joueur.

---

## 1. Objectif

L'authentification admin est **complètement séparée** de l'authentification joueur. Elle utilise :
- Une **table dédiée** : `admin` (pas la table `users`).
- Un système de **session PHP manuelle** (pas Laravel Sanctum ni Breeze).
- Un **deuxième facteur d'authentification (2FA)** basé sur TOTP.

> **Pourquoi séparé ?** Pour qu'un compte joueur compromis ne puisse jamais donner accès au panel admin. Les deux systèmes n'ont aucun point commun en base de données ni en session.

---

## 2. En tant qu'administrateur…

> *Je veux me connecter au panel admin avec mon email et mot de passe.*

> *Si j'ai activé la 2FA, je veux saisir mon code mobile pour compléter la connexion.*

> *Je veux activer ou désactiver la 2FA depuis mes paramètres de sécurité.*

---

## 3. Parcours — Connexion sans 2FA

1. L'admin visite `/admin/login`.
2. Il saisit son **email** et **mot de passe**.
3. Le serveur vérifie les identifiants dans la table `admin` (avec `Hash::check()`).
4. Si valides et 2FA désactivée : les variables de session sont créées.
5. Redirection vers `/admin` (tableau de bord).

### Variables de session créées

| Variable | Valeur | Rôle |
|----------|--------|-------|
| `admin_id` | ID de l'admin | Identifie l'admin connecté |
| `admin_pseudo` | Pseudo | Affiché dans l'interface |
| `admin_role` | Rôle (`super_admin`, `admin`) | Détermine les droits |
| `admin_2fa_passed` | `true` | Confirmé que la 2FA est passée (ou qu'elle est désactivée) |

---

## 4. Parcours — Connexion avec 2FA activée

1. Même étapes 1 à 3.
2. Après validation du mot de passe, la session stocke **uniquement** `admin_2fa_pending_id` (temporaire).
3. L'admin est redirigé vers `/admin/two-factor/challenge`.
4. Il ouvre Google Authenticator ou Authy et saisit le **code à 6 chiffres**.
5. Le serveur vérifie via `pragmarx/google2fa`.
6. Si valide : les variables de session complètes sont créées (`admin_id`, `admin_2fa_passed = true`).
7. Redirection vers `/admin`.

---

## 5. Parcours — Activer la 2FA

1. L'admin va dans `/admin/security/two-factor`.
2. Un **QR code SVG** est généré et affiché (bibliothèque `BaconQrCode`).
3. L'admin scanne le QR code avec son application mobile.
4. Il saisit le code à 6 chiffres pour confirmer.
5. Si valide : le secret TOTP est stocké **chiffré** en base avec `Crypt::encryptString()`.
6. La 2FA est active. Les prochaines connexions demanderont ce code.

---

## 6. Parcours — Désactiver la 2FA

1. L'admin va dans `/admin/security/two-factor`.
2. Il saisit son **mot de passe actuel** pour confirmer la désactivation.
3. Le secret TOTP est effacé, `two_factor_enabled` passe à `false`.

---

## 7. Rôle des middlewares

| Middleware | Vérification effectuée |
|------------|------------------------|
| `admin` | Vérifie que `admin_id` est en session. Sinon redirige vers `/admin/login`. |
| `2fa.admin` | Vérifie que `admin_2fa_passed = true` est en session. Sinon redirige vers le challenge 2FA. |
| `admin.can:xxx` | Vérifie que l'admin a la permission `xxx` (ex: `encyclopedie`, `blog`). Sinon 403. |

---

## 8. Règles métier

| Règle | Détail |
|-------|--------|
| Table | `admin` (séparée de `users`) |
| Mot de passe | Hashé Bcrypt, vérifié avec `Hash::check()` |
| Secret 2FA | Stocké chiffré (`Crypt::encryptString()`) dans `two_factor_secret` |
| Session | Variables PHP manuelles (pas de guard Laravel) |
| Sécurité session | `session()->regenerate()` après authentification (prévention fixation de session) |

---

## 9. Messages & cas limites

| Situation | Comportement |
|-----------|-------------|
| Identifiants incorrects | Erreur générique "Identifiants incorrects" |
| Code 2FA invalide | Message "Code 2FA invalide", réessai possible |
| Session 2FA expirée | Retour à `/admin/login` |
| Admin déjà connecté accedant à `/admin/login` | Redirigé vers `/admin` |
| Ancien hash non Bcrypt | Connexion refusée proprement (pas de crash serveur) |

---

## 10. Dépendances techniques

- **`AdminAuthController`** : `login()`, `authenticate()`, `logout()`.
- **`AdminTwoFactorChallengeController`** : challenge 2FA (`show()`, `verify()`).
- **`AdminTwoFactorController`** : paramètres 2FA (`edit()`, `enable()`, `disable()`).
- **Modèle `Admin`** : table `admin`, champs `two_factor_secret`, `two_factor_enabled`, `role`, `permissions`.
- **`pragmarx/google2fa`** : génération et vérification TOTP.
- **`BaconQrCode`** : génération du QR code SVG.
