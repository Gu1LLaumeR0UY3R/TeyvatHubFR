<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class Issue43ImportControllerTest extends TestCase
{
    use RefreshDatabase;

    private function makeUser(): User
    {
        return User::factory()->create();
    }

    // Critère 1 : un invité est redirigé
    public function test_invites_sont_redirigers(): void
    {
        $this->post(route('profil.import-uid'), ['uid' => '123456789'])
             ->assertRedirect(route('login'));
    }

    // Critère 2 : validation uid doit être 9 chiffres
    public function test_validation_uid_requis_9_chiffres(): void
    {
        $user = $this->makeUser();
        $this->actingAs($user)
             ->post(route('profil.import-uid'), ['uid' => '12345'])
             ->assertSessionHasErrors('uid');
    }

    // Critère 3 : validation uid ne peut être non-numérique
    public function test_validation_uid_doit_etre_numerique(): void
    {
        $user = $this->makeUser();
        $this->actingAs($user)
             ->post(route('profil.import-uid'), ['uid' => 'abcdefghi'])
             ->assertSessionHasErrors('uid');
    }

    // Critère 4 : erreur si enka.network échoue
    public function test_erreur_si_enka_echoue(): void
    {
        Http::fake([
            'enka.network/*' => Http::response([], 503),
        ]);

        $user = $this->makeUser();
        $this->actingAs($user)
             ->post(route('profil.import-uid'), ['uid' => '123456789'])
             ->assertRedirect(route('profil.parametres'))
             ->assertSessionHas('import_error');
    }

    // Critère 5 : message d'erreur si pas de showcase
    public function test_erreur_si_pas_de_showcase(): void
    {
        Http::fake([
            'enka.network/*' => Http::response(['playerInfo' => []], 200),
        ]);

        $user = $this->makeUser();
        $this->actingAs($user)
             ->post(route('profil.import-uid'), ['uid' => '123456789'])
             ->assertRedirect(route('profil.parametres'))
             ->assertSessionHas('import_error');
    }

    // Critère 6 : import réussi avec avatarInfoList
    public function test_import_reussi_avec_avatar_list(): void
    {
        Http::fake([
            'enka.network/*' => Http::response([
                'avatarInfoList' => [
                    ['avatarId' => 10000046, 'propMap' => ['4001' => ['val' => 90]], 'equipList' => []],
                    ['avatarId' => 10000002, 'propMap' => ['4001' => ['val' => 80]], 'equipList' => []],
                ],
            ], 200),
        ]);

        $user = $this->makeUser();
        $this->actingAs($user)
             ->post(route('profil.import-uid'), ['uid' => '123456789'])
             ->assertRedirect(route('profil.parametres'))
             ->assertSessionHas('import_success');
    }

    // Critère 7 : l'uid est sauvegardé dans le profil
    public function test_uid_sauvegarde_dans_profil(): void
    {
        Http::fake([
            'enka.network/*' => Http::response([
                'avatarInfoList' => [['avatarId' => 10000046, 'propMap' => ['4001' => ['val' => 60]], 'equipList' => []]],
            ], 200),
        ]);

        $user = $this->makeUser();
        $this->actingAs($user)->post(route('profil.import-uid'), ['uid' => '987654321']);
        $this->assertDatabaseHas('users', ['id' => $user->id, 'uid_genshin' => '987654321']);
    }
}
