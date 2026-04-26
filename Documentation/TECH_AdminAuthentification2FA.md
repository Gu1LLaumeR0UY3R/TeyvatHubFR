# Documentation technique - AdminAuthentification2FA

## Objectif technique
Implementer et maintenir la fonctionnalite AdminAuthentification2FA de maniere robuste, testable et securisee.

## Routes principales
GET/POST /admin/login ; GET/POST /admin/two-factor/challenge ; GET/POST /admin/security/two-factor/* ; POST /admin/logout

## Controllers
AdminAuthController, AdminTwoFactorChallengeController, AdminTwoFactorController

## Modeles/entites
Admin

## Vues / UI
resources/views/admin/login.blade.php + vues security

## Middleware et securite
admin, 2fa.admin

## Flux technique standard
1. Route HTTP recue.
2. Middleware applique (authentification, 2FA, permission).
3. Controller execute la logique metier et les validations.
4. Modeles Eloquent lisent/ecrivent en base.
5. Reponse renvoyee: vue Blade, JSON ou redirection.

## Tests recommandes
- Test route (200, 302, 403, 404 selon cas).
- Test validation des champs requis.
- Test autorisation par role/permission.
- Test persistance en base (create/update/delete).
