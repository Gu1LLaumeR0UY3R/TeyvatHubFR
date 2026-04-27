# Documentation technique — Profil Joueur

> **Pour qui ?** Développeur qui reprend le projet ou développeur junior PHP/Laravel.  
> **Difficulté Laravel** : ⭐⭐ Intermédiaire — middleware auth, relations pivot, import HTTP externe.

---

## 1. Rôle de cette fonctionnalité

Le profil joueur rassemble tout ce qui est **personnel à un joueur connecté** :
- **Dashboard profil** : statistiques (personnages possédés, armes, constellations).
- **Mes personnages** : liste des personnages importés depuis Genshin Impact.
- **Mes armes** : liste des armes importées.
- **Paramètres** : import UID Genshin, informations du compte.
- **Amis** : système de demandes d'amitié entre joueurs.

---

## 2. Routes (toutes protégées par `auth` + `2fa.user`)

```php
Route::middleware(['auth', '2fa.user'])->prefix('profil')->group(function () {
    Route::get('/',             [ProfilController::class, 'index'])->name('profil.index');
    Route::get('/personnages',  [ProfilController::class, 'personnages'])->name('profil.personnages');
    Route::get('/armes',        [ProfilController::class, 'armes'])->name('profil.armes');
    Route::get('/parametres',   [ProfilController::class, 'parametres'])->name('profil.parametres');
    Route::post('/import-uid',  [ImportController::class, 'importUID'])->name('profil.import-uid');
    Route::get('/amis',         [AmiController::class, 'index'])->name('profil.amis');
    Route::post('/amis',        [AmiController::class, 'store'])->name('profil.amis.store');
    Route::patch('/amis/{amitie}', [AmiController::class, 'update'])->name('profil.amis.update');
    Route::delete('/amis/{amitie}', [AmiController::class, 'destroy'])->name('profil.amis.destroy');
});
```

**`prefix('profil')`** : toutes les routes dans ce groupe ont l'URL prefixée par `/profil`.

---

## 3. ProfilController — statistiques

```php
public function index(): View
{
    $user = Auth::user();

    // Nombre de personnages enregistrés (table pivot joueur_personnage)
    $persos_possedes = $user->personnages()->count();

    // Nombre de constellations débloquées (via DB::table car pas de Model dédié)
    $constellations_debloquees = DB::table('joueur_constellation')
        ->where('fid_joueur', $user->id)
        ->where('debloquee', true)
        ->count();

    // Nombre de personnages montés à C6 (6 constellations toutes débloquées)
    $persos_c6 = DB::table('joueur_constellation')
        ->where('fid_joueur', $user->id)
        ->where('debloquee', true)
        ->selectRaw('fid_perso, COUNT(*) as cnt')
        ->groupBy('fid_perso')
        ->havingRaw('cnt >= 6')  // au moins 6 constellations débloquées = C6
        ->count();

    return view('profil.index', compact('user', 'stats'));
}
```

**Relations pivot** : la table `joueur_personnage` est une **table de jointure** entre `users` et `personnage`. Elle stocke les personnages possédés par chaque joueur avec des infos supplémentaires (`niveau`, `perso_amelioration`).

---

## 4. Import UID Genshin Impact — `ImportController`

```php
public function importUID(Request $request): RedirectResponse
{
    $request->validate([
        'uid' => ['required', 'digits:9'],  // UID = exactement 9 chiffres
    ]);

    // Appel HTTP vers l'API externe Enka.Network
    $response = Http::timeout(10)->get("https://enka.network/api/uid/{$uid}");

    if ($response->failed()) {
        return redirect()->route('profil.parametres')
            ->with('import_error', 'Impossible de contacter Enka.Network.');
    }

    $data = $response->json();

    if (!isset($data['avatarInfoList'])) {
        // L'API retourne une liste vide si le joueur n'a pas activé
        // "Afficher les détails des personnages" dans Genshin Impact
        return redirect()->route('profil.parametres')
            ->with('import_error', 'Aucun personnage trouvé. Activez "Afficher les détails" dans le jeu.');
    }

    // Sauvegarde l'UID dans le profil
    $user->uid_genshin = $uid;
    $user->save();

    // Parcourt chaque personnage du showcase (max 8 dans Genshin Impact)
    $imported = 0;
    foreach ($data['avatarInfoList'] as $avatar) {
        $niveau = $avatar['propMap']['4001']['val'] ?? 1;
        $imported++;
    }

    return redirect()->route('profil.parametres')
        ->with('import_success', "{$imported} personnages trouvés dans votre showcase.");
}
```

**Enka.Network** : API publique non officielle qui lit le profil Genshin Impact d'un joueur depuis les serveurs HoYoverse. Le joueur doit avoir activé l'affichage public de son showcase en jeu.

---

## 5. Système d'amis — `AmiController`

```php
public function index(): View
{
    $user = auth()->user();

    // Amis acceptés (dans les deux sens : A a invité B, ou B a invité A)
    $amis = Amitie::with(['demandeur', 'receveur'])
        ->where(function ($q) use ($user) {
            $q->where('fid_demandeur', $user->id)
              ->orWhere('fid_receveur', $user->id);
        })
        ->where('statut', 'accepte')
        ->get()
        ->map(function ($a) use ($user) {
            // Retourne l'AUTRE joueur (pas soi-même)
            return $a->fid_demandeur === $user->id ? $a->receveur : $a->demandeur;
        });

    $demandesRecues  = Amitie::with('demandeur')->where('fid_receveur', $user->id)->where('statut', 'en_attente')->get();
    $demandesEnvoyees = Amitie::with('receveur')->where('fid_demandeur', $user->id)->where('statut', 'en_attente')->get();

    return view('profil.amis', compact('amis', 'demandesRecues', 'demandesEnvoyees'));
}
```

**Modèle `Amitie`** (table `amitie`) : `fid_demandeur` (celui qui invite), `fid_receveur` (celui qui reçoit), `statut` (`en_attente` / `accepte` / `refuse`).

---

## 6. Tests

```php
public function test_profil_redirige_si_non_connecte(): void
{
    $this->get('/profil')->assertRedirect('/login');
}

public function test_profil_retourne_200_si_connecte(): void
{
    $user = User::factory()->create();
    $this->actingAs($user)->get('/profil')->assertStatus(200);
}

public function test_import_uid_invalide_retourne_erreur(): void
{
    $user = User::factory()->create();
    $this->actingAs($user)
         ->post('/profil/import-uid', ['uid' => '123'])
         ->assertSessionHasErrors('uid');
}
```

---

## 7. Points de vigilance

- **`Http::timeout(10)`** : l'import s'arrête au bout de 10 secondes si Enka.Network ne répond pas. Sans ce timeout, une API lente bloquerait le serveur indirectement.
- **Import partiel** : le matching entre `avatarId` Genshin et les personnages TeyvatHub n'est pas encore complet (voir commentaire dans le code `ImportController`).
