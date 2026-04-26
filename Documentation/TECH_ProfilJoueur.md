# Documentation technique - ProfilJoueur

## Objectif technique
Implementer et maintenir la fonctionnalite ProfilJoueur de maniere robuste, testable et securisee.

## Routes principales
GET /profil, /profil/personnages, /profil/armes, /profil/parametres, /profil/amis + POST/PATCH/DELETE amis + POST import-uid

## Controllers
ProfilController, ImportController, AmiController

## Modeles/entites
User, Amitie, Joueur*

## Vues / UI
resources/views/profil/*

## Middleware et securite
auth, 2fa.user

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
