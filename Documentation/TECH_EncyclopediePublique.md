# Documentation technique - EncyclopediePublique

## Objectif technique
Implementer et maintenir la fonctionnalite EncyclopediePublique de maniere robuste, testable et securisee.

## Routes principales
GET /personnages, /armes, /ennemis, /animaux, /cuisine, /materiaux, /ingredients + routes show

## Controllers
PersonnageController, ArmeController, EnnemiController, AnimalController, PlatController, MateriauxController, IngredientController

## Modeles/entites
Personnage, Arme, Ennemi, Animal, Plat, Materiaux, Ingredient, Photo

## Vues / UI
resources/views/personnages/*, armes/*, ennemis/*, animaux/*, cuisine/*, materiaux/*, ingredients/*

## Middleware et securite
Aucun (public)

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
