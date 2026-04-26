# Documentation technique - AuthJoueur2FA

## Objectif technique
Implementer et maintenir la fonctionnalite AuthJoueur2FA de maniere robuste, testable et securisee.

## Routes principales
Routes Breeze + /dashboard + /profile + endpoints 2FA user

## Controllers
Auth controllers Breeze, ProfileController, Auth\\TwoFactorController

## Modeles/entites
User

## Vues / UI
resources/views/auth/* ; resources/views/profile/*

## Middleware et securite
auth, verified, 2fa.user

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
