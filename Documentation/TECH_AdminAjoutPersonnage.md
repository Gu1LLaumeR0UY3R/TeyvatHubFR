# Documentation technique — Éditeur admin personnage (ajout & blocs AJAX)

> **Pour qui ?** Développeur qui reprend le projet ou développeur junior PHP/Laravel.  
> **Difficulté** : ⭐⭐⭐ Avancé — Route Model Binding, AJAX JSON, upload + redimensionnement, JSON en base.

---

## 1. Vue d'ensemble des fichiers impliqués

| Fichier | Rôle |
|---------|------|
| `app/Http/Controllers/Admin/PersonnageController.php` | CRUD principal : index, create (brouillon), edit, update, destroy |
| `app/Http/Controllers/Admin/PersonnageBlockController.php` | 16 routes AJAX pour les blocs de l'éditeur |
| `app/Models/Personnage.php` | Modèle Eloquent, relations, slug auto |
| `resources/views/admin/personnages/edit.blade.php` | Vue de l'éditeur, Alpine.js |
| `routes/web.php` | Déclaration des 16+ routes de l'éditeur |

---

## 2. Routes complètes

```php
// Toutes sous middleware: ['admin', '2fa.admin', 'admin.can:encyclopedie']

// CRUD standard
Route::resource('/personnages', Admin\PersonnageController::class);
Route::patch('/personnages/bulk-update', [Admin\PersonnageController::class, 'bulkUpdate']);

// Blocs AJAX — préfixe : /admin/personnages/{personnage:slug}/block/
Route::put('main-zone',                  [PersonnageBlockController::class, 'updateMainZone']);
Route::post('main-zone/upload-image',    [PersonnageBlockController::class, 'uploadImage']);
Route::get('main-zone/backgrounds',      [PersonnageBlockController::class, 'getBackgroundsByNation']);
Route::put('armes-recommandees',         [PersonnageBlockController::class, 'updateArmesRecommandees']);
Route::delete('armes-recommandees/{id_arme}', [PersonnageBlockController::class, 'deleteArmeRecommandee']);
Route::put('artefacts-recommandes',      [PersonnageBlockController::class, 'updateArtefactsRecommandees']);
Route::delete('artefacts-recommandes/{id_build}', [PersonnageBlockController::class, 'deleteArtefactRecommande']);
Route::put('constellations',             [PersonnageBlockController::class, 'updateConstellations']);
Route::post('constellations/upload-image',[PersonnageBlockController::class, 'uploadConstellationImage']);
Route::post('constellation-map',         [PersonnageBlockController::class, 'updateConstellationMap']);
Route::put('competences',                [PersonnageBlockController::class, 'updateCompetences']);
Route::post('competences/upload-image',  [PersonnageBlockController::class, 'uploadAptitudeImage']);
Route::put('histoires',                  [PersonnageBlockController::class, 'updateHistoires']);
Route::post('teams',                     [PersonnageBlockController::class, 'storeTeam']);
Route::put('teams/{id_team}',            [PersonnageBlockController::class, 'updateTeam']);
Route::delete('teams/{id_team}',         [PersonnageBlockController::class, 'deleteTeam']);
Route::patch('order',                    [PersonnageBlockController::class, 'updateBlockOrder']);
```

> **Note** : le paramètre `{personnage:slug}` utilise le **Route Model Binding par slug**. Laravel résout automatiquement l'objet `Personnage` depuis la colonne `slug` (définie dans `getRouteKeyName()`).

---

## 3. `PersonnageController::create()` — création du brouillon

```php
public function create(): RedirectResponse
{
    // Cherche les IDs minimaux pour créer un personnage valide
    $fidElement  = Elements::query()->value('id_element');
    $fidEtoile   = Etoile::query()->value('id_etoile');
    $fidTypeArme = TypeArme::query()->value('id_TArmes');
    $fidTypePerso= TypePerso::query()->value('id_TP');

    // Si une référence manque → erreur, retour à la liste
    if (!$fidElement || !$fidEtoile || !$fidTypeArme || !$fidTypePerso) {
        return redirect()->route('admin.personnages.index')
            ->with('error', 'Références manquantes (element, etoile, type arme ou type perso).');
    }

    // Crée le personnage brouillon en base
    $draft = Personnage::create([
        'nom_perso' => 'Brouillon ' . uniqid(),  // nom temporaire unique
        'fid_etoile'  => $fidEtoile,
        'fid_element' => $fidElement,
        'fid_TArmes'  => $fidTypeArme,
        'fid_TP'      => $fidTypePerso,
    ]);

    // Redirige vers l'éditeur avec le flag ?fresh=1
    return redirect()->route('admin.personnages.edit', ['personnage' => $draft, 'fresh' => 1]);
}
```

Le `uniqid()` génère un suffixe hexadécimal unique (ex: `6638a2b4f1c3e`), ce qui garantit que le slug du brouillon (`brouillon-6638a2b4f1c3e`) ne collisionne pas.

Le flag `?fresh=1` dans l'URL permet à la vue `edit.blade.php` de détecter un nouveau brouillon et d'afficher un message contextuel ("Complétez le nom du personnage pour commencer").

---

## 4. `PersonnageController::edit()` — chargement de l'éditeur

```php
public function edit(Personnage $personnage): View
{
    // Charge toutes les relations nécessaires à l'éditeur en une seule opération
    $personnage->load([
        'element', 'etoile', 'typeArme', 'typePerso',
        'photos', 'videos', 'histoires', 'nations',
        'armesRecommandees.arme',
        'artefactsRecommandees.artefact1.photos',
        'artefactsRecommandees.artefact1.rareté',
        'artefactsRecommandees.artefact2.photos',
        'artefactsRecommandees.artefact2.rareté',
        'constellations.photo',
        'aptitudes.typeApti',
        'aptitudes.photos',
    ]);

    // Données de référence passées à la vue (listes déroulantes)
    $elements   = Elements::all();
    $etoiles    = Etoile::all();
    $typesArme  = TypeArme::all();
    $typesPerso = TypePerso::all();
    $roles      = Role::all();
    $nations    = Nation::all();
    $typesApti  = TypeApti::orderBy('id_TypeApti')->get();
    $armesDisponibles    = Arme::with('typeArme')->orderBy('nom_arme')->get();
    $artefactsDisponibles= Artefact::with(['photos', 'rareté'])->orderBy('nom_artefact')->get();
    $reactions  = Reaction::orderBy('nom_reaction')->get();

    return view('admin.personnages.edit', compact(/* toutes les variables */));
}
```

> **Eager loading** : le `->load([...])` charge toutes les relations en **un nombre limité de requêtes SQL**, évitant le problème N+1 (qui génèrerait des centaines de requêtes pour une fiche complète).

---

## 5. `PersonnageBlockController::updateMainZone()` — bloc zone principale

**Route** : `PUT /admin/personnages/{slug}/block/main-zone`  
**Réponse** : JSON `{ success: true, message: "Zone principale mise à jour." }`

Ce bloc met à jour les champs identitaires du personnage. Particularité : il calcule aussi automatiquement l'icône de l'arme associée.

```php
// Après validation, mise à jour du personnage
$personnage->update([
    'nom_perso'   => $data['nom_perso'],
    'fid_element' => $data['fid_element'],
    'fid_etoile'  => $data['fid_etoile'],
    'fid_TArmes'  => $data['fid_TArmes'],
    'fid_TP'      => $data['fid_TP'],
    'arme_icon'   => $armeIcon,         // URL de l'icône de l'arme type (calculée)
    'background_actif' => $data['background_actif'],
]);

// Sync des nations (relation many-to-many)
$personnage->nations()->sync($data['fid_nations']);

// Gestion des vidéos (suppression + recréation)
$personnage->videos()->delete();
foreach ($data['videos'] as $index => $video) {
    PersonnageVideo::create([
        'fid_perso' => $personnage->id_perso,
        'url_video' => $video['url_video'],
        'ordre'     => $index + 1,
    ]);
}
```

> `sync()` : méthode Eloquent many-to-many qui **synchronise** les nations. Elle ajoute les nouvelles, supprime celles retirées, sans toucher aux existantes.

---

## 6. `PersonnageBlockController::uploadImage()` — upload photo

**Route** : `POST /admin/personnages/{slug}/block/main-zone/upload-image`  
**Paramètre** : `image_type` = `icone` | `portrait` | `full`

```php
// Stockage par type
'icone'   → dossier: 'photos/personnages/icones_personnage/', max: 512px
'portrait'→ dossier: 'photos/personnages/personnage_full/',   max: 1600px
'full'    → même que portrait

// Nom de fichier automatique
'icone'   → '{slug}-icon.{ext}'
'portrait'→ '{slug}-full.{ext}'
```

**Redimensionnement avec `storeResizedImage()`** :

```php
// Si l'image est plus grande que maxSide:
$ratio = min($maxSide / $width, $maxSide / $height);  // conserve les proportions
$newWidth  = (int) round($width  * $ratio);
$newHeight = (int) round($height * $ratio);

// Recréation de l'image avec imagecopyresampled() (PHP GD)
// PNG/WebP → transparence préservée via imagealphablending + imagesavealpha
// JPEG → qualité 85%
// WebP → qualité 85% (si imagewebp() disponible)
```

Si le redimensionnement échoue pour quelque raison que ce soit (mémoire insuffisante, format non reconnu), le fichier original est stocké **sans transformation**. Pas de crash.

---

## 7. `PersonnageBlockController::updateArmesRecommandees()` — armes recommandées

**Route** : `PUT /admin/personnages/{slug}/block/armes-recommandees`

Logique de validation spécifique :

```php
// 1. Vérification de compatibilité type d'arme
foreach ($armes as $armeData) {
    $armeModel = Arme::find($armeData['id_arme']);
    if ($armeModel->fid_TArmes !== $personnage->fid_TArmes) {
        // Erreur 422 : arme incompatible
    }
}

// 2. Un seul starter autorisé
// Si plusieurs armes marquées starter → seule la première est conservée

// 3. Le starter est toujours placé en dernière position
$nonStarterWeapons = $armes->filter(fn($a) => !$a['is_starter'])->values();
$armes = $nonStarterWeapons->push($starterWeapon)->values();

// 4. Suppression totale puis recréation
PersonnageArmeRecommandee::where('fid_perso', $personnage->id_perso)->delete();
foreach ($armes as $index => $arme) {
    PersonnageArmeRecommandee::create([...]);
}
```

---

## 8. `PersonnageBlockController::updateConstellationMap()` — carte des constellations

**Route** : `POST /admin/personnages/{slug}/block/constellation-map`

Stockage des coordonnées dans la colonne `positions_const` (JSON) de la **première** constellation du personnage :

```php
// Normalisation des coordonnées : clamp entre 0 et 100, arrondi à 1 décimale
$x = round(max(0, min(100, (float) $pt['x'])), 1);
$y = round(max(0, min(100, (float) $pt['y'])), 1);

// Déduplication des lignes (1→2 === 2→1)
$a = min($from, $to);
$b = max($from, $to);
$pair = "{$a}-{$b}";
if (isset($seen[$pair])) continue;  // doublon ignoré

// Structure JSON finale
$constCarte->positions_const = [
    'points' => ['1' => ['x' => 45.2, 'y' => 30.0], ...],
    'lines'  => [['from' => 1, 'to' => 2], ...],
];
```

Si aucune constellation n'existe encore en base → une constellation vide est **créée automatiquement** pour stocker la carte.

---

## 9. `PersonnageBlockController::updateCompetences()` — gestion des compétences

**Route** : `PUT /admin/personnages/{slug}/block/competences`

Stratégie **update-or-create avec nettoyage** :

```php
$keptIds = [];

foreach ($data['competences'] as $payload) {
    if (!empty($payload['id_aptitude'])) {
        // Aptitude existante → mise à jour
        $aptitude = Aptitude::where('id_aptitude', $payload['id_aptitude'])
                            ->where('fid_perso', $personnage->id_perso) // sécurité
                            ->first();
        if ($aptitude) {
            $aptitude->update($attributes);
            $keptIds[] = $aptitude->id_aptitude;
            continue;
        }
    }
    // Nouvelle aptitude → création
    $created = Aptitude::create($attributes);
    $keptIds[] = $created->id_aptitude;
}

// Supprime les aptitudes absentes de la liste soumise
Aptitude::where('fid_perso', $personnage->id_perso)
         ->whereNotIn('id_aptitude', $keptIds)
         ->delete();
```

> **Sécurité** : la vérification `->where('fid_perso', $personnage->id_perso)` empêche qu'un admin modifie les aptitudes d'un autre personnage en manipulant les IDs dans la requête.

La même stratégie s'applique à `updateHistoires()`.

---

## 10. `PersonnageBlockController::updateBlockOrder()` — réordonner les blocs

**Route** : `PATCH /admin/personnages/{slug}/block/order`

```php
// Valeurs acceptées (les 6 blocs de l'éditeur)
Rule::in(['main_zone','armes','artefacts','constellations','competences','histoires'])

// Stockage dans la colonne block_order du personnage (string CSV)
$personnage->update([
    'block_order' => implode(',', $data['block_order'])
    // Exemple : "main_zone,constellations,competences,armes,artefacts,histoires"
]);
```

La vue `edit.blade.php` lit cette colonne pour afficher les blocs dans l'ordre sauvegardé.

---

## 11. Schéma des tables touchées par l'éditeur

```
personnage (id_perso, nom_perso, slug, fid_element, fid_etoile, fid_TArmes,
            fid_TP, arme_icon, background_actif, block_order)
    │
    ├── photo (polymorphique, type = 'icone' | 'portrait')
    ├── personnage_video (url_video, ordre)
    ├── personnage_nation (pivot many-to-many)
    ├── constellation (id_const, fid_perso, titre_const, descri_const,
    │   │              positions_const JSON)
    │   └── photo (polymorphique, type = 'icon')
    ├── aptitude (id_aptitude, fid_perso, titre_apti, descri_apti,
    │   │         lvl_apt, fid_TypeApti)
    │   └── photo (polymorphique)
    ├── personnage_arme_recommandee (fid_perso, fid_arme, position, starter, origine)
    ├── personnage_artefact_recommandee (fid_perso, fid_artefact_1, pieces_1,
    │                                    fid_artefact_2, pieces_2,
    │                                    main_stat_*, sub_stats, position)
    ├── histoire (id_histoire, fid_perso, titre_histoire, histoire, ordre)
    └── team_composition (id_team, fid_perso, type_reaction, tag)
        ├── team_composition_membre (fid_team, id_perso, slot, role_override)
        └── team_slot_remplacant (fid_team, slot, id_perso, role_override)
```

---

## 12. Format des réponses AJAX

Tous les blocs retournent du JSON :

**Succès (HTTP 200)** :
```json
{
  "success": true,
  "message": "Zone principale mise à jour."
}
```

**Erreur de validation (HTTP 422)** :
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "armes.0.id_arme": ["Cette arme n'est pas compatible avec le type d'arme du personnage."],
    "armes": ["Une arme starter est obligatoire."]
  }
}
```

**Erreur serveur (HTTP 500)** : réponse standard Laravel (log dans `storage/logs/laravel.log`).

---

## 13. Checklist — créer un personnage complet

```
1. [ ] Vérifier que les données de référence existent
       (elements, etoile, type_armes, type_perso, type_apti, artefacts, armes)

2. [ ] Cliquer "Ajouter un personnage"
       → Brouillon créé, redirigé vers l'éditeur

3. [ ] Bloc Zone principale : renseigner le vrai nom, élément, rareté, type d'arme
       → Le slug est mis à jour (l'URL des blocs change)

4. [ ] Bloc Images : uploader l'icône et le portrait

5. [ ] Bloc Constellations : remplir les 6 niveaux + uploader les icônes C1-C6

6. [ ] Bloc Carte : uploader l'image de la carte, placer les 6 points, tracer les lignes

7. [ ] Bloc Compétences : créer chaque compétence avec son type et son icône

8. [ ] Bloc Armes recommandées : sélectionner les armes, marquer une arme starter

9. [ ] Bloc Artefacts recommandés : créer les builds avec stats principales et sous-stats

10. [ ] Bloc Histoires : saisir les textes de lore

11. [ ] Bloc Équipes : créer les compositions recommandées
```
