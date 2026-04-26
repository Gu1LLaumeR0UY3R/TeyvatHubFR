# Documentation technique - BlogPublic

## Objectif technique
Implementer et maintenir la fonctionnalite BlogPublic de maniere robuste, testable et securisee.

## Routes principales
GET /blog ; GET /blog/{article:slug}

## Controllers
BlogController@index, BlogController@show

## Modeles/entites
BlogArticle, Photo, BlogSlug

## Vues / UI
resources/views/blog/index.blade.php ; resources/views/blog/show.blade.php

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
