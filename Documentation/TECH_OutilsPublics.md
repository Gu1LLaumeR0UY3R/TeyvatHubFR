# Documentation technique — Outils Publics (Personnage du Jour & Quiz)

> **Pour qui ?** Développeur qui reprend le projet ou développeur junior PHP/Laravel.  
> **Difficulté Laravel** : ⭐ Débutant — logique déterministe simple.

---

## 1. Rôle de cette fonctionnalité

Deux outils consultables **sans connexion** :

### Personnage du Jour
Un personnage tiré de la base est affiché pendant **toute la journée** (le même pour tous les visiteurs). Le personnage change à minuit automatiquement.

### Quiz
Un personnage aléatoire est choisi comme "bonne réponse". 4 personnages aléatoires sont proposés comme choix. Le visiteur sélectionne sa réponse et un résultat est affiché.

---

## 2. Routes

```php
Route::get('/outils/personnage-du-jour', [OutilsController::class, 'personnageDuJour'])->name('outils.personnage-du-jour');
Route::get('/outils/quiz', [OutilsController::class, 'quiz'])->name('outils.quiz');
Route::post('/outils/quiz/resultat', [OutilsController::class, 'quizResultat'])->name('outils.quiz.resultat');
```

---

## 3. Controller — `app/Http/Controllers/OutilsController.php`

### 3.1 Personnage du Jour

```php
public function personnageDuJour(): View
{
    $count = Personnage::count();
    if ($count === 0) {
        $personnage = null;
    } else {
        // crc32(date) donne un hash numérique de la date du jour
        // abs() garantit un positif, % $count donne un index valide
        $seed  = crc32(now()->format('Y-m-d'));
        $index = abs($seed) % $count;
        $personnage = Personnage::with(['element', 'etoile', 'photos'])
            ->skip($index)   // saute $index enregistrements
            ->first();
    }
    return view('outils.personnage-du-jour', compact('personnage'));
}
```

**Mécanisme déterministe** : `crc32('2026-04-26')` retourne toujours le même nombre pour la même date. Tous les visiteurs voient donc le même personnage. Aucune table de cache nécessaire.

**Limite connue** : si des personnages sont ajoutés ou supprimés, l'index peut se décaler. Le personnage du jour peut changer sans qu'on soit en minuit.

### 3.2 Quiz

```php
public function quiz(): View
{
    $personnages = Personnage::with(['element', 'etoile', 'photos'])->get();

    if ($personnages->count() < 4) {
        // Pas assez de personnages pour faire un quiz à 4 choix
        return view('outils.quiz', ['question' => null, 'choices' => []]);
    }

    $shuffled = $personnages->shuffle();   // mélange aléatoire
    $correct  = $shuffled->first();        // premier = bonne réponse
    $choices  = $shuffled->take(4);        // 4 choix (inclut le correct)

    return view('outils.quiz', compact('correct', 'choices'));
}
```

### 3.3 Résultat du Quiz

```php
public function quizResultat(Request $request): View
{
    $request->validate([
        'reponse' => ['required', 'string'],
        'correct' => ['required', 'string'],
    ]);

    $estCorrect = $request->reponse === $request->correct;

    return view('outils.quiz-resultat', [
        'reponse'    => $request->reponse,
        'correct'    => $request->correct,
        'estCorrect' => $estCorrect,
    ]);
}
```

**Validation côté serveur** : même si le formulaire est soumis manuellement (sans passer par l'interface), le serveur valide que `reponse` et `correct` sont bien présents.

---

## 4. Modèles impliqués

`Personnage`, `Elements`, `Etoile`, `Photo` — lecture seule.

---

## 5. Vues

```
resources/views/outils/
  personnage-du-jour.blade.php
  quiz.blade.php
  quiz-resultat.blade.php
```

---

## 6. Tests

```php
public function test_personnage_du_jour_retourne_200(): void
{
    $this->get('/outils/personnage-du-jour')->assertStatus(200);
}

public function test_personnage_du_jour_sans_personnages_ne_plante_pas(): void
{
    // Table vide → $personnage = null → la vue doit gérer ce cas
    $this->get('/outils/personnage-du-jour')->assertStatus(200);
}

public function test_quiz_retourne_200(): void
{
    Personnage::factory()->count(6)->create();
    $this->get('/outils/quiz')->assertStatus(200);
}

public function test_quiz_resultat_correct(): void
{
    $this->post('/outils/quiz/resultat', ['reponse' => 'Hu Tao', 'correct' => 'Hu Tao'])
         ->assertStatus(200);
}

public function test_quiz_resultat_sans_champs_retourne_422(): void
{
    $this->post('/outils/quiz/resultat', [])->assertStatus(422);
}
```
