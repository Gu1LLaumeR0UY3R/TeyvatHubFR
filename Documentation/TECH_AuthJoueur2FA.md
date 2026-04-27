# Documentation technique — Authentification Joueur & 2FA

> **Pour qui ?** Développeur qui reprend le projet ou développeur junior PHP/Laravel.  
> **Difficulté Laravel** : ⭐⭐⭐ Avancé — Breeze, middleware personnalisé, TOTP Google2FA.

---

## 1. Rôle de cette fonctionnalité

Permet à un joueur de :
1. **Créer un compte** (inscription via Laravel Breeze).
2. **Se connecter** (login email/password).
3. **Activer la double authentification (2FA)** via une application TOTP (Google Authenticator, Authy…).
4. **Gérer son profil de base** (nom, email, password).

---

## 2. Qu'est-ce que Laravel Breeze ?

Laravel Breeze est un **kit de démarrage d'authentification** fourni par Laravel. Il génère automatiquement :
- Les routes `/login`, `/register`, `/forgot-password`, `/logout`.
- Les vues Blade associées (`resources/views/auth/`).
- Les controllers d'authentification (`app/Http/Controllers/Auth/`).

On ne réécrit pas ces fichiers — on les utilise tels quels.

---

## 3. Routes

```php
// Injecté par routes/auth.php (généré par Breeze)
// /login, /register, /forgot-password, /reset-password, /logout

// Route dashboard après connexion
Route::get('/dashboard', ...)->middleware(['auth', 'verified', '2fa.user']);

// Routes profil + gestion 2FA joueur
Route::middleware(['auth', '2fa.user'])->group(function () {
    Route::get('/profile',  [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile',[ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile',[ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::post('/profile/two-factor/enable',  [TwoFactorController::class, 'enable'])->name('twofactor.enable');
    Route::post('/profile/two-factor/disable', [TwoFactorController::class, 'disable'])->name('twofactor.disable');
});
```

---

## 4. Les middlewares d'authentification

Les **middlewares** sont des filtres appliqués avant que la requête atteigne le controller.

| Middleware | Rôle |
|------------|------|
| `auth` | Vérifie que l'utilisateur est connecté via Breeze. Redirige vers `/login` sinon. |
| `verified` | Vérifie que l'email a été vérifié. |
| `2fa.user` | Middleware personnalisé : vérifie que le joueur a passé le challenge 2FA si activé. |

**Middleware `2fa.user`** : déclaré dans `bootstrap/app.php`. Il vérifie :
- Si `two_factor_enabled = true` pour l'utilisateur.
- Si `session('2fa_passed')` est défini (il a validé son code TOTP ce jour).
- Sinon → redirection vers une page de challenge 2FA.

---

## 5. Modèle `User` (table `users`)

```php
protected $fillable = [
    'name', 'email', 'password',
    'two_factor_secret',       // secret chiffré (Crypt::encryptString)
    'two_factor_enabled',      // booléen
    'two_factor_confirmed_at', // date de confirmation
    'pseudo', 'avatar', 'banniere', 'bio_joueur',
    'uid_genshin',             // UID du compte Genshin Impact
    'banni_le', 'motif_ban',   // modération
];

protected function casts(): array
{
    return [
        'password'             => 'hashed',    // hash automatique BCrypt
        'two_factor_enabled'   => 'boolean',
    ];
}
```

**`password` casté `hashed`** : depuis Laravel 10+, le cast `hashed` hash automatiquement la valeur assignée. Ne jamais utiliser `bcrypt()` ou `md5()` manuellement.

---

## 6. Flux complet de connexion avec 2FA

```
1. Joueur soumet /login (email + password)
2. Breeze vérifie les identifiants
3. Si two_factor_enabled = false → connexion directe, redirect dashboard
4. Si two_factor_enabled = true :
   a. Session temporaire stockée
   b. Redirect vers /two-factor-challenge
   c. Joueur entre le code TOTP (6 chiffres depuis son appli)
   d. Serveur vérifie avec Google2FA::verifyKey()
   e. Si valide → session('2fa_passed') = true, redirect dashboard
   f. Si invalide → erreur, retour formulaire
```

---

## 7. Activation du 2FA

```php
public function enable(Request $request): RedirectResponse
{
    $request->validate(['code' => ['required', 'digits:6']]);
    $user   = auth()->user();
    $secret = $request->session()->get('user_2fa_secret_temp');

    if (!app(Google2FA::class)->verifyKey($secret, $request->input('code'))) {
        return back()->withErrors(['code' => 'Code 2FA invalide.']);
    }

    $user->update([
        'two_factor_secret'       => Crypt::encryptString($secret),
        'two_factor_enabled'      => true,
        'two_factor_confirmed_at' => now(),
    ]);
    session()->forget('user_2fa_secret_temp');
    return redirect()->route('profile.edit')->with('success', '2FA activée.');
}
```

**Chiffrement du secret** : le secret TOTP n'est **jamais stocké en clair**. `Crypt::encryptString()` utilise la clé `APP_KEY` du `.env`. Si `APP_KEY` change, tous les secrets 2FA sont invalidés.

---

## 8. Tests

```php
public function test_login_page_retourne_200(): void
{
    $this->get('/login')->assertStatus(200);
}

public function test_connexion_valide_redirige_dashboard(): void
{
    $user = User::factory()->create(['password' => Hash::make('password123')]);
    $this->post('/login', ['email' => $user->email, 'password' => 'password123'])
         ->assertRedirect('/dashboard');
}

public function test_acces_dashboard_sans_connexion_redirige_login(): void
{
    $this->get('/dashboard')->assertRedirect('/login');
}

public function test_acces_profil_sans_connexion_redirige_login(): void
{
    $this->get('/profile')->assertRedirect('/login');
}
```

---

## 9. Sécurité — points importants

- Passwords hashés avec BCrypt via le cast `hashed` (Laravel).
- Jamais de `md5()`, `sha1()` ou `bcrypt()` direct dans le code.
- Secret 2FA chiffré avec `Crypt::encryptString()` avant stockage.
- `session()->regenerate()` appelé après le login pour prévenir les attaques de fixation de session.
