<?php

namespace Tests\Feature;

use App\Models\Constellation;
use App\Models\Personnage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Issue88ConstellationMapPopupTest extends TestCase
{
    use RefreshDatabase;

    public function test_page_personnage_affiche_icone_constellation_aux_positions_json(): void
    {
        $personnage = Personnage::factory()->create();
        Constellation::create([
            'fid_perso' => $personnage->id_perso,
            'titre_const' => 'C1',
            'descri_const' => 'Description C1',
            'positions_const' => [
                'points' => [
                    '1' => ['x' => 10.5, 'y' => 20.5],
                ],
                'lines' => [],
            ],
        ]);

        $response = $this->get(route('personnages.show', $personnage));

        $response->assertStatus(200);
        $response->assertSee('csh-constellation-map-point', false);
        $response->assertSee('openConstellationPopup(index)', false);
    }

    public function test_constellation_recommandee_porte_la_classe_is_recommended_dans_le_html(): void
    {
        $personnage = Personnage::factory()->create();
        Constellation::create([
            'fid_perso' => $personnage->id_perso,
            'titre_const' => 'C1 Prioritaire',
            'descri_const' => 'Description prioritaire',
            'recommandee' => true,
            'positions_const' => [
                'points' => ['1' => ['x' => 15, 'y' => 25]],
                'lines' => [],
            ],
        ]);

        $response = $this->get(route('personnages.show', $personnage));

        $response->assertStatus(200);
        $response->assertSee('constellationPointRecommended(index)', false);
        $response->assertSee('csh-constellation-map-point-star', false);
        $response->assertSee('"recommandee":true', false);
    }

    public function test_constellation_non_recommandee_najoute_pas_le_badge_etoile_par_defaut(): void
    {
        $personnage = Personnage::factory()->create();
        Constellation::create([
            'fid_perso' => $personnage->id_perso,
            'titre_const' => 'C1 Standard',
            'descri_const' => 'Description standard',
            'positions_const' => [
                'points' => ['1' => ['x' => 15, 'y' => 25]],
                'lines' => [],
            ],
        ]);

        $response = $this->get(route('personnages.show', $personnage));

        $response->assertStatus(200);
        $response->assertSee('"recommandee":false', false);
    }

    public function test_popup_modal_est_presente_dans_le_dom_avec_fermeture_au_clavier(): void
    {
        $personnage = Personnage::factory()->create();
        Constellation::create([
            'fid_perso' => $personnage->id_perso,
            'titre_const' => 'C1',
            'descri_const' => 'Description C1',
        ]);

        $response = $this->get(route('personnages.show', $personnage));

        $response->assertStatus(200);
        $response->assertSee('csh-constellation-modal-bg', false);
        $response->assertSee('closeConstellationPopup()', false);
        $response->assertSee('@keydown.window.escape', false);
    }

    public function test_aucune_constellation_naffiche_pas_la_carte(): void
    {
        $personnage = Personnage::factory()->create();

        $response = $this->get(route('personnages.show', $personnage));

        $response->assertStatus(200);
        $response->assertSee('Aucune constellation disponible pour ce personnage.');
    }
}
