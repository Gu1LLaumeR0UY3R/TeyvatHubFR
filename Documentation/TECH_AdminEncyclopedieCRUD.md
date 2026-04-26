# Documentation technique - AdminEncyclopedieCRUD

## Objectif technique
Implementer et maintenir la fonctionnalite AdminEncyclopedieCRUD de maniere robuste, testable et securisee.

## Routes principales
Resources admin personnages/armes/artefacts/ennemis/animaux/cuisine/nations/regions/roles/references + routes block AJAX personnage

## Controllers
Admin\\PersonnageController + PersonnageBlockController + autres controllers CRUD admin

## Modeles/entites
Personnage, Arme, Ennemi, Animal, Plat, Nation, Role, Reference, Photo

## Vues / UI
resources/views/admin/personnages/* et autres dossiers admin/*

## Middleware et securite
admin, 2fa.admin, admin.can:encyclopedie

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
