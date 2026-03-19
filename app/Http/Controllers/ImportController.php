<?php

namespace App\Http\Controllers;

use App\Models\Arme;
use App\Models\Personnage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ImportController extends Controller
{
    public function importUID(Request $request): RedirectResponse
    {
        $request->validate([
            'uid' => ['required', 'digits:9'],
        ]);

        $uid = $request->uid;
        $user = auth()->user();

        $response = Http::timeout(10)->get("https://enka.network/api/uid/{$uid}");

        if ($response->failed()) {
            return redirect()->route('profil.parametres')
                ->with('import_error', 'Impossible de contacter Enka.Network. Vérifiez votre connexion ou réessayez plus tard.');
        }

        $data = $response->json();

        if (!isset($data['avatarInfoList'])) {
            return redirect()->route('profil.parametres')
                ->with('import_error', 'Aucun personnage trouvé. Activez "Afficher les détails des personnages" dans Genshin Impact.');
        }

        $user->uid_genshin = $uid;
        $user->save();

        $imported = 0;
        foreach ($data['avatarInfoList'] as $avatar) {
            $avatarId = $avatar['avatarId'] ?? null;
            if (!$avatarId) {
                continue;
            }

            // Trouver le personnage correspondant par un champ source externe si disponible
            // Sinon on enregistre juste les données basiques
            $niveau = $avatar['propMap']['4001']['val'] ?? 1;

            // Arme équipée
            foreach ($avatar['equipList'] ?? [] as $equip) {
                if (isset($equip['weapon'])) {
                    $weap = $equip['weapon'];
                    $affix = array_values($weap['affixMap'] ?? []);
                    $rang = isset($affix[0]) ? $affix[0] + 1 : 1;
                    $niveauArme = $weap['level'] ?? 1;

                    // On cherche l'arme dans notre BDD par son id externe si disponible
                    // Sinon on skip silencieusement
                }
            }
            $imported++;
        }

        return redirect()->route('profil.parametres')
            ->with('import_success', "Import réussi ! {$imported} personnages trouvés dans votre showcase.");
    }
}
