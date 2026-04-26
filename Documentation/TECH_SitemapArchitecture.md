# Sitemap technique - Architecture et flux fichiers

## Vue globale des dossiers
- app/Http/Controllers: orchestration des requetes HTTP.
- app/Models: acces aux donnees metier via Eloquent.
- app/Services: logique transverse (GoogleDriveBrowserService, MotusService).
- resources/views: rendu Blade (public, profil, admin).
- routes: declaration des endpoints web et auth.
- database/migrations: schema de base de donnees.
- database/factories et seeders: generation de donnees de test.
- tests/Feature et tests/Unit: verification fonctionnelle et technique.
- public et storage: assets publics et fichiers uploades.

## Flux principal (HTTP vers DB vers UI)
1. routes/web.php mappe URL vers Controller@action.
2. Middleware (auth, 2FA, permissions admin) filtre la requete.
3. Controller valide la requete puis appelle Modeles/Services.
4. Modeles Eloquent executent les requetes SQL.
5. Controller renvoie une vue Blade ou une reponse JSON.
6. Vue Blade affiche les donnees et declenche eventuellement des appels AJAX.

## Flux AJAX admin personnage (edition par blocs)
1. La vue admin personnage charge des attributs data-* de configuration.
2. JS/Alpine envoie des requetes vers /admin/personnages/{slug}/block/*.
3. PersonnageBlockController valide chaque payload de bloc.
4. Les modeles associes sont mis a jour (videos, constellations, aptitudes, teams, etc.).
5. Reponse JSON met a jour l etat de l interface sans rechargement complet.

## Interactions entre fichiers
- routes/web.php -> app/Http/Controllers/*
- app/Http/Controllers/* -> app/Models/* et app/Services/*
- app/Models/* -> database/migrations/* (structure) et tables SQL
- app/Http/Controllers/* -> resources/views/* (rendu)
- resources/views/* -> routes nommees (liens, formulaires, actions)
- tests/Feature/* -> routes + controllers + base de donnees

## Carte des actions inter-dossiers
- Public web: routes/web.php -> Controllers public -> Models -> Views public
- Profil joueur: routes/web.php (groupe auth) -> Controllers profil -> Models joueur/amitie -> Views profil
- Admin web: routes/web.php (groupe admin + permissions) -> Controllers admin -> Models admin/metier -> Views admin
- Donnees: migrations definissent le schema ; models appliquent les relations ; factories alimentent les tests
- Tests: tests/Feature valident les parcours complets, tests/Unit valident la logique isolee
