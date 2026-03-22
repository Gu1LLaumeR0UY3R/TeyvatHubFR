<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Issue67DesignSystemTest extends TestCase
{
    use RefreshDatabase;

    // Critère 1 : Toutes les variables CSS sont centralisées dans app.css
    public function test_variables_css_border_rarete_presentes(): void
    {
        $css = file_get_contents(resource_path('css/app.css'));
        $this->assertStringContainsString('--border-5star', $css);
        $this->assertStringContainsString('--border-4star', $css);
        $this->assertStringContainsString('--border-3star', $css);
    }

    public function test_variables_css_couleurs_elements_presentes(): void
    {
        $css = file_get_contents(resource_path('css/app.css'));
        $this->assertStringContainsString('--color-pyro', $css);
        $this->assertStringContainsString('--color-hydro', $css);
        $this->assertStringContainsString('--color-cryo', $css);
        $this->assertStringContainsString('--color-electro', $css);
        $this->assertStringContainsString('--color-dendro', $css);
        $this->assertStringContainsString('--color-anemo', $css);
        $this->assertStringContainsString('--color-geo', $css);
    }

    // Critère 2 : Le composant x-card existe et contient card-entity
    public function test_composant_card_existe(): void
    {
        $this->assertFileExists(resource_path('views/components/card.blade.php'));
    }

    public function test_composant_card_personnage_existe(): void
    {
        $this->assertFileExists(resource_path('views/components/card-personnage.blade.php'));
        $content = file_get_contents(resource_path('views/components/card-personnage.blade.php'));
        $this->assertStringContainsString('card-entity', $content);
        $this->assertStringContainsString('rarity-', $content);
        $this->assertStringContainsString('element-', $content);
    }

    // Critère 3 : Hover uniforme défini dans app.css
    public function test_hover_uniforme_dans_app_css(): void
    {
        $css = file_get_contents(resource_path('css/app.css'));
        $this->assertStringContainsString('.card-entity:hover', $css);
        $this->assertStringContainsString('translateY', $css);
    }

    // Critère 4 : Aucune couleur hex en dur dans la vue index personnages
    public function test_aucune_couleur_hex_en_dur_dans_vue_index(): void
    {
        $view = file_get_contents(resource_path('views/personnages/index.blade.php'));
        // Pas de couleur hex hardcodée (ex: #ff4d1c) dans la vue index
        $this->assertStringNotContainsString('#ff4d1c', $view);
        $this->assertStringNotContainsString('#1c96ff', $view);
    }
}
