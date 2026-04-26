# TeyvatHub

TeyvatHub est un site fan encyclopedique Genshin Impact base sur Laravel 12 (Blade + Alpine + Tailwind + MySQL).

## Documentation
Le dossier Documentation contient 2 documents par fonctionnalite:
- TECH_<Fonctionnalite>.md: vue technique (routes, controllers, modeles, securite, tests)
- FONCT_<Fonctionnalite>.md: vue fonctionnelle (objectif metier, parcours utilisateur, regles)

## Fonctionnalites documentees
- AccueilNavigation
- BlogPublic
- EncyclopediePublique
- NationsHistoire
- JeuxMotus
- OutilsPublics
- AuthJoueur2FA
- ProfilJoueur
- OutilsJoueurProteges
- AdminAuthentification2FA
- AdminImportGenshin
- AdminEncyclopedieCRUD
- AdminBlog
- AdminEvenementsChronologie
- AdminUtilisateurs
- AdminGestionAdmins

## Sitemap technique
- Documentation/TECH_SitemapArchitecture.md

## Stack technique
- Backend: Laravel 12, PHP 8.2+
- Frontend: Blade, Alpine.js, Tailwind CSS
- Base de donnees: MySQL
- Tests: PHPUnit

## Lancer le projet
1. composer install
2. npm install
3. copier .env.example vers .env
4. php artisan key:generate
5. php artisan migrate
6. php artisan serve
7. npm run dev

## Tests
- Tous les tests: php artisan test
- Filtre: php artisan test --filter=NomDuTest
