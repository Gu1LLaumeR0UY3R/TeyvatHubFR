# Documentation technique — Jeu Motus

> **Pour qui ?** Développeur qui reprend le projet ou développeur junior PHP/Laravel.  
> **Difficulté Laravel** : ⭐⭐ Intermédiaire — logique métier dans un Service, réponse JSON.

---

## 1. Rôle de cette fonctionnalité

Motus est un mini-jeu de devinette de mots, accessible **sans connexion**. Le principe :
- Chaque jour, un mot est tiré de la base de données (parmi les noms de personnages, armes, ennemis, nations).
- Le joueur propose des mots lettre par lettre.
- Le serveur répond pour chaque lettre si elle est **correcte** (bonne lettre, bonne position), **présente** (bonne lettre, mauvaise position) ou **absente**.

---

## 2. Routes

```php
Route::get('/jeux/motus', [MotusController::class, 'index'])->name('jeux.motus');
Route::post('/jeux/motus/valider', [MotusController::class, 'valider'])->name('jeux.motus.valider');
```

---

## 3. Architecture — pattern Service

La logique métier est isolée dans `app/Services/MotusService.php` (pas dans le controller). Pourquoi ?
- **Séparation des responsabilités** : le controller reste mince (reçoit la requête, retourne la réponse), la logique complexe est dans le Service.
- **Testabilité** : on peut tester `MotusService` sans simuler une requête HTTP.

```php
// app/Http/Controllers/MotusController.php
class MotusController extends Controller
{
    // Injection de dépendance : Laravel instancie automatiquement MotusService
    public function __construct(private MotusService $motusService) {}

    public function index(): View
    {
        $dailyWord = $this->motusService->getDailyWord();
        $wordLength = mb_strlen($dailyWord);
        return view('jeux.motus', compact('dailyWord', 'wordLength'));
    }

    public function valider(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'guess' => ['required', 'string', 'max:100'],
            'word'  => ['required', 'string', 'max:100'],
        ]);
        $result = $this->motusService->validateGuess($validated['guess'], $validated['word']);
        $won = collect($result)->every(fn($r) => $r['status'] === 'correct');
        return response()->json(['result' => $result, 'won' => $won]);
    }
}
```

---

## 4. Service — `app/Services/MotusService.php`

### 4.1 Pool de mots

```php
public function getWordPool(): Collection
{
    return collect()
        ->merge(Personnage::pluck('nom_perso'))   // Tous les noms de personnages
        ->merge(Arme::pluck('nom_arme'))           // Tous les noms d'armes
        ->merge(Ennemi::pluck('nom_ennemi'))       // Tous les noms d'ennemis
        ->merge(Nation::pluck('nom_region'))       // Tous les noms de nations
        ->filter(fn(string $w) => mb_strlen($w) >= 3)  // Minimum 3 lettres
        ->unique()
        ->values();
}
```

**`pluck('nom_perso')`** : requête SQL qui récupère uniquement la colonne `nom_perso` (pas tout l'objet). Efficace.

### 4.2 Mot du jour

```php
public function getDailyWord(): string
{
    $pool = $this->getWordPool();
    $sorted = $pool->sort()->values()->toArray();
    // crc32(date) = un hash numérique déterministe de la date
    // abs() pour éviter un nombre négatif
    // % count = index dans le tableau
    $index = abs(crc32(date('Y-m-d'))) % count($sorted);
    return $sorted[$index];
}
```

Le mot est **le même pour tous les joueurs toute la journée**, et change automatiquement à minuit. Aucune table en base nécessaire.

### 4.3 Validation d'un essai (algorithme BM25-like)

```php
public function validateGuess(string $guess, string $word): array
{
    // Passe 1 : lettres exactes (bonne lettre, bonne position)
    foreach ($guessChars as $i => $char) {
        if (isset($wordChars[$i]) && $char === $wordChars[$i]) {
            $result[$i] = 'correct';
            $wordUsed[$i] = true;
        }
    }
    // Passe 2 : lettres présentes (bonne lettre, mauvaise position)
    foreach ($guessChars as $i => $char) {
        if ($result[$i] !== null) continue;  // déjà traité
        foreach ($wordChars as $j => $wChar) {
            if (!$wordUsed[$j] && $char === $wChar) {
                $result[$i] = 'present';
                $wordUsed[$j] = true;
                break;
            }
        }
        if (!$result[$i]) $result[$i] = 'absent';
    }
    // Retourne [{letter: 'H', status: 'correct'}, ...]
}
```

**Réponse JSON exemple** :
```json
{
  "result": [
    {"letter": "H", "status": "correct"},
    {"letter": "U", "status": "absent"},
    {"letter": "T", "status": "present"}
  ],
  "won": false
}
```

---

## 5. Modèles impliqués

`Personnage`, `Arme`, `Ennemi`, `Nation` — uniquement lus pour construire le pool de mots. Aucune écriture.

---

## 6. Vue

```
resources/views/jeux/motus.blade.php
```

L'interface est entièrement en JavaScript côté client (Alpine.js ou JS vanilla). Elle envoie des requêtes POST vers `/jeux/motus/valider` et affiche les résultats colorés.

---

## 7. Tests

```php
public function test_motus_page_retourne_200(): void
{
    $this->get('/jeux/motus')->assertStatus(200);
}

public function test_motus_valider_retourne_json(): void
{
    $this->postJson('/jeux/motus/valider', [
        'guess' => 'Hutao',
        'word'  => 'Hutao',
    ])->assertJson(['won' => true]);
}

public function test_motus_valider_sans_champs_retourne_422(): void
{
    $this->postJson('/jeux/motus/valider', [])->assertStatus(422);
}
```

---

## 8. Points à surveiller

- Si la base de données est vide (aucun personnage, arme, ennemi, nation), le mot de secours est `'Mondstadt'`.
- L'algorithme normalise les accents avant comparaison (ex : `É` = `E`). Vérifier `normalize()` dans `MotusService` si des mots français accentués posent problème.
