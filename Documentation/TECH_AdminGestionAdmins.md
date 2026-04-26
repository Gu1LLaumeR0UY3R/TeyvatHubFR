# Documentation technique - AdminGestionAdmins

## Objectif technique
Implementer et maintenir la fonctionnalite AdminGestionAdmins de maniere robuste, testable et securisee.

## Routes principales
GET/POST /admin/admins ; GET/PUT/DELETE /admin/admins/{admin}

## Controllers
Admin\\AdminManageController

## Modeles/entites
Admin

## Vues / UI
resources/views/admin/admins/*

## Middleware et securite
admin, 2fa.admin, admin.can:admins

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
