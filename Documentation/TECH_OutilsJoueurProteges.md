# Documentation technique - OutilsJoueurProteges

## Objectif technique
Implementer et maintenir la fonctionnalite OutilsJoueurProteges de maniere robuste, testable et securisee.

## Routes principales
GET/PATCH /outils/roulette ; GET/POST roulette-personnage ; GET/POST roulette-team ; GET/POST team ; GET/POST comparateur

## Controllers
OutilsController, RoulettePersonnageController, RouletteTeamController

## Modeles/entites
Personnage, Arme, TeamComposition, Reaction

## Vues / UI
resources/views/outils/*

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
