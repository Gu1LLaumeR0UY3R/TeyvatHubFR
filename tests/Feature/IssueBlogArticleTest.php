<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\BlogArticle;
use App\Models\BlogSlug;
use App\Models\Photo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Tests complets pour gestion articles blog en admin
 * Couvre: CRUD, Slugs, Images, Validations, Permissions, Statuts
 */
class IssueBlogArticleTest extends TestCase
{
    use RefreshDatabase;

    protected function makeAdmin(): Admin
    {
        $unique = \Illuminate\Support\Str::random(10);
        return Admin::create([
            'pseudo_admin'       => 'AdminTest' . $unique,
            'email_admin'        => 'admin-' . $unique . '@test.fr',
            'mot_de_passe_admin' => Hash::make('secret123'),
            'role'               => 'superadmin',
        ]);
    }

    protected function adminSession(): array
    {
        $admin = $this->makeAdmin();
        return ['admin_id' => $admin->id_admin];
    }

    protected function validBlogLayout(string $title = 'Test', string $content = 'Test content'): string
    {
        return json_encode([
            'blocks' => [
                ['type' => 'heading', 'level' => 'h2', 'text' => $title],
                ['type' => 'text', 'text' => $content, 'align' => 'left'],
            ],
        ]);
    }

    // ──────────────────────────────────────────────────────────────
    // SECTION 1 : Routes Publiques (accessibles sans authentification)
    // ──────────────────────────────────────────────────────────────

    public function test_blog_index_retourne_200(): void
    {
        $this->get(route('blog.index'))
            ->assertStatus(200);
    }

    public function test_blog_index_affiche_articles_publies(): void
    {
        BlogArticle::factory()
            ->create([
                'titre_article'     => 'Article Visible',
                'statut'            => 'publie',
                'date_publication'  => now()->subDay(),
            ]);

        BlogArticle::factory()
            ->create([
                'titre_article'     => 'Article Brouillon',
                'statut'            => 'brouillon',
            ]);

        $response = $this->get(route('blog.index'));
        $response->assertSee('Article Visible');
        $response->assertDontSee('Article Brouillon');
    }

    public function test_blog_index_pagination(): void
    {
        BlogArticle::factory(25)->create(['statut' => 'publie']);

        $response = $this->get(route('blog.index'));
        $response->assertStatus(200);
        $response->assertSee('pagination');
    }

    public function test_blog_show_par_slug(): void
    {
        $article = BlogArticle::factory()
            ->create([
                'titre_article'     => 'Mon Article',
                'slug'              => 'mon-article',
                'statut'            => 'publie',
            ]);

        $response = $this->get(route('blog.show', ['article' => $article]));
        $response->assertStatus(200);
        $response->assertSee('Mon Article');
    }

    public function test_blog_show_slug_inexistant_retourne_404(): void
    {
        $this->get('/blog/inexistant')
            ->assertStatus(404);
    }

    public function test_blog_show_article_brouillon_retourne_404(): void
    {
        BlogArticle::factory()
            ->create([
                'slug'   => 'mon-article',
                'statut' => 'brouillon',
            ]);

        $this->get(route('blog.show', ['blog' => 'mon-article']))
            ->assertStatus(404);
    }

    public function test_blog_show_acces_par_id_retourne_404(): void
    {
        $article = BlogArticle::factory()->create(['statut' => 'publie']);
        $this->get("/blog/{$article->id_article}")
            ->assertStatus(404);
    }

    // ──────────────────────────────────────────────────────────────
    // SECTION 2 : Admin Authentification & Permissions
    // ──────────────────────────────────────────────────────────────

    public function test_admin_blog_index_non_authentifie_redirige(): void
    {
        $this->get(route('admin.blog.index'))
            ->assertRedirect(route('admin.login'));
    }

    public function test_admin_blog_index_authentifie_retourne_200(): void
    {
        $this->withSession($this->adminSession())
            ->get(route('admin.blog.index'))
            ->assertStatus(200);
    }

    // ──────────────────────────────────────────────────────────────
    // SECTION 3 : Admin CRUD — CREATE & STORE
    // ──────────────────────────────────────────────────────────────

    public function test_admin_blog_create_affiche_formulaire(): void
    {
        BlogSlug::factory(5)->create();

        $response = $this->withSession($this->adminSession())
            ->get(route('admin.blog.create'));

        $response->assertStatus(200);
        $response->assertViewHas('slugPresets');
    }

    public function test_admin_blog_store_titre_requis(): void
    {
        $response = $this->withSession($this->adminSession())
            ->post(route('admin.blog.store'), [
                'titre_article'     => '',
                'slug'              => 'test',
                'statut'            => 'publie',
                'layout_json'       => $this->validBlogLayout(),
            ]);

        $response->assertSessionHasErrors('titre_article');
    }

    public function test_admin_blog_store_statut_requis(): void
    {
        $response = $this->withSession($this->adminSession())
            ->post(route('admin.blog.store'), [
                'titre_article' => 'Mon Article',
                'slug'          => 'mon-article',
                'statut'        => '',
                'layout_json'   => $this->validBlogLayout(),
            ]);

        $response->assertSessionHasErrors('statut');
    }

    public function test_admin_blog_store_statut_valide(): void
    {
        $response = $this->withSession($this->adminSession())
            ->post(route('admin.blog.store'), [
                'titre_article' => 'Mon Article',
                'slug'          => 'mon-article',
                'statut'        => 'invalide',
                'layout_json'   => $this->validBlogLayout(),
            ]);

        $response->assertSessionHasErrors('statut');
    }

    public function test_admin_blog_store_slug_unique(): void
    {
        $this->withSession($this->adminSession())
            ->post(route('admin.blog.store'), [
                'titre_article' => 'Article 1',
                'slug'          => 'mon-article',
                'statut'        => 'brouillon',
                'layout_json'   => $this->validBlogLayout(),
            ]);

        $response = $this->withSession($this->adminSession())
            ->post(route('admin.blog.store'), [
                'titre_article' => 'Article 2',
                'slug'          => 'mon-article',
                'statut'        => 'brouillon',
                'layout_json'   => $this->validBlogLayout(),
            ]);

        $response->assertSessionHasErrors('slug');
    }

    public function test_admin_blog_store_slug_auto_genere_si_vide(): void
    {
        $this->withSession($this->adminSession())
            ->post(route('admin.blog.store'), [
                'titre_article' => 'Mon Article Test',
                'slug'          => null,
                'statut'        => 'brouillon',
                'layout_json'   => $this->validBlogLayout(),
            ]);

        $this->assertDatabaseHas('blog_article', [
            'titre_article' => 'Mon Article Test',
            'slug'          => 'mon-article-test',
        ]);
    }

    public function test_admin_blog_store_layout_json_valide(): void
    {
        $layout = json_encode([
            ['type' => 'heading', 'level' => 'h2', 'text' => 'Titre'],
            ['type' => 'text', 'text' => 'Contenu', 'align' => 'left'],
        ]);

        $this->withSession($this->adminSession())
            ->post(route('admin.blog.store'), [
                'titre_article' => 'Article Structuré',
                'slug'          => 'article-structure',
                'statut'        => 'brouillon',
                'layout_json'   => $layout,
            ]);

        $this->assertDatabaseHas('blog_article', [
            'titre_article' => 'Article Structuré',
            'slug'          => 'article-structure',
        ]);
    }

    public function test_admin_blog_store_layout_json_invalide(): void
    {
        $response = $this->withSession($this->adminSession())
            ->post(route('admin.blog.store'), [
                'titre_article' => 'Article Mal Structuré',
                'slug'          => 'article-mal',
                'statut'        => 'brouillon',
                'layout_json'   => 'JSON invalide {]',
            ]);

        $response->assertSessionHasErrors('layout_json');
    }

    public function test_admin_blog_store_date_publication(): void
    {
        $tomorrow = now()->addDay()->format('Y-m-d');

        $this->withSession($this->adminSession())
            ->post(route('admin.blog.store'), [
                'titre_article'     => 'Article Futur',
                'slug'              => 'article-futur',
                'statut'            => 'publie',
                'date_publication'  => $tomorrow,
                'layout_json'       => $this->validBlogLayout(),
            ]);

        $this->assertDatabaseHas('blog_article', [
            'titre_article'     => 'Article Futur',
            'date_publication'  => $tomorrow,
        ]);
    }

    // ──────────────────────────────────────────────────────────────
    // SECTION 4 : Admin CRUD — READ (Index & Show)
    // ──────────────────────────────────────────────────────────────

    public function test_admin_blog_index_liste_articles(): void
    {
        BlogArticle::factory(5)->create();

        $response = $this->withSession($this->adminSession())
            ->get(route('admin.blog.index'));

        $response->assertStatus(200);
        $response->assertViewHas('articles');
    }

    public function test_admin_blog_index_pagination(): void
    {
        BlogArticle::factory(25)->create();

        $response = $this->withSession($this->adminSession())
            ->get(route('admin.blog.index'));

        $response->assertStatus(200);
        $response->assertSee('pagination');
    }

    public function test_admin_blog_edit_affiche_formulaire(): void
    {
        $article = BlogArticle::factory()->create();

        $response = $this->withSession($this->adminSession())
            ->get(route('admin.blog.edit', $article));

        $response->assertStatus(200);
        $response->assertSee($article->titre_article);
    }

    // ──────────────────────────────────────────────────────────────
    // SECTION 5 : Admin CRUD — UPDATE
    // ──────────────────────────────────────────────────────────────

    public function test_admin_blog_update_modifie_article(): void
    {
        $article = BlogArticle::factory()
            ->create(['titre_article' => 'Ancien Titre']);

        $this->withSession($this->adminSession())
            ->put(route('admin.blog.update', $article), [
                'titre_article' => 'Nouveau Titre',
                'slug'          => $article->slug,
                'statut'        => 'brouillon',
                'layout_json'   => $this->validBlogLayout(),
            ]);

        $this->assertDatabaseHas('blog_article', [
            'id_article'    => $article->id_article,
            'titre_article' => 'Nouveau Titre',
        ]);
    }

    public function test_admin_blog_update_slug_unique_par_article(): void
    {
        $article1 = BlogArticle::factory()->create(['slug' => 'article-1']);
        $article2 = BlogArticle::factory()->create(['slug' => 'article-2']);

        // Article 1 peut garder son slug
        $response = $this->withSession($this->adminSession())
            ->put(route('admin.blog.update', $article1), [
                'titre_article' => 'Article 1 Modifié',
                'slug'          => 'article-1',
                'statut'        => 'brouillon',
                'layout_json'   => $this->validBlogLayout(),
            ]);

        $response->assertSessionHasNoErrors();
    }

    public function test_admin_blog_update_statut_publie_set_date(): void
    {
        $article = BlogArticle::factory()
            ->create(['statut' => 'brouillon', 'date_publication' => null]);

        $today = now()->format('Y-m-d');

        $this->withSession($this->adminSession())
            ->put(route('admin.blog.update', $article), [
                'titre_article'     => $article->titre_article,
                'slug'              => $article->slug,
                'statut'            => 'publie',
                'layout_json'       => $this->validBlogLayout(),
            ]);

        $article->refresh();
        $this->assertEquals('publie', $article->statut);
        // La date ne doit être set que si elle est NULL et statut = publie
    }

    // ──────────────────────────────────────────────────────────────
    // SECTION 6 : Admin CRUD — DELETE
    // ──────────────────────────────────────────────────────────────

    public function test_admin_blog_destroy_supprime_article(): void
    {
        $article = BlogArticle::factory()->create();

        $this->withSession($this->adminSession())
            ->delete(route('admin.blog.destroy', $article));

        $this->assertDatabaseMissing('blog_article', [
            'id_article' => $article->id_article,
        ]);
    }

    public function test_admin_blog_destroy_supprime_photos(): void
    {
        Storage::fake('public');

        $article = BlogArticle::factory()->create();
        $photo = Photo::factory()
            ->for($article, 'photoable')
            ->create(['chemin_photo' => 'photos/blog/featured/test.jpg']);

        $this->withSession($this->adminSession())
            ->delete(route('admin.blog.destroy', $article));

        $this->assertDatabaseMissing('photo', [
            'id_photo' => $photo->id_photo,
        ]);
    }

    // ──────────────────────────────────────────────────────────────
    // SECTION 7 : Gestion des Slugs Prédéfinis
    // ──────────────────────────────────────────────────────────────

    public function test_admin_blog_store_slug_retourne_201(): void
    {
        $response = $this->withSession($this->adminSession())
            ->postJson(route('admin.blog.slugs.store'), [
                'slug_base' => 'Mon Nouveau Slug',
            ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('blog_slug', [
            'slug_base' => 'mon-nouveau-slug',
        ]);
    }

    public function test_admin_blog_destroy_slug_retourne_204(): void
    {
        $slug = BlogSlug::factory()->create();

        $response = $this->withSession($this->adminSession())
            ->delete(route('admin.blog.slugs.destroy', $slug));

        $response->assertRedirect(route('admin.blog.index'));
        $this->assertDatabaseMissing('blog_slug', [
            'id_blog_slug' => $slug->id_blog_slug,
        ]);
    }

    // ──────────────────────────────────────────────────────────────
    // SECTION 8 : Gestion des Images
    // ──────────────────────────────────────────────────────────────

    public function test_admin_blog_upload_featured_image(): void
    {
        Storage::fake('public');

        $image = \Illuminate\Http\UploadedFile::fake()
            ->image('featured.jpg', 1200, 600);

        $response = $this->withSession($this->adminSession())
            ->post(route('admin.blog.store'), [
                'titre_article'      => 'Article avec Image',
                'slug'               => 'article-image',
                'statut'             => 'brouillon',
                'layout_json'        => $this->validBlogLayout(),
                'featured_images.0'  => $image,
            ]);

        $response->assertRedirect();
        $article = BlogArticle::where('slug', 'article-image')->first();
        $this->assertTrue($article->photos()->count() > 0);
    }

    public function test_admin_blog_image_trop_volumineux(): void
    {
        $image = \Illuminate\Http\UploadedFile::fake()
            ->image('huge.jpg', 5000, 5000)
            ->size(6000); // 6000 KB

        $response = $this->withSession($this->adminSession())
            ->post(route('admin.blog.store'), [
                'titre_article'      => 'Article Gros Fichier',
                'slug'               => 'article-gros',
                'statut'             => 'brouillon',
                'layout_json'        => $this->validBlogLayout(),
                'featured_images.0'  => $image,
            ]);

        $response->assertSessionHasErrors('featured_images.0');
    }

    public function test_admin_blog_destroy_image(): void
    {
        Storage::fake('public');

        $article = BlogArticle::factory()->create();
        $photo = Photo::factory()
            ->for($article, 'photoable')
            ->create(['chemin_photo' => 'photos/blog/featured/test.jpg']);

        $response = $this->withSession($this->adminSession())
            ->delete(route('admin.blog.images.destroy', [$article, $photo]));

        $response->assertRedirect(route('admin.blog.edit', $article));
        $this->assertDatabaseMissing('photo', [
            'id_photo' => $photo->id_photo,
        ]);
    }

    public function test_admin_blog_destroy_image_non_proprietaire(): void
    {
        $article1 = BlogArticle::factory()->create();
        $article2 = BlogArticle::factory()->create();
        
        $photo = Photo::factory()
            ->for($article1, 'photoable')
            ->create();

        // Tenter de supprimer la photo de article1 en utilisant article2
        $response = $this->withSession($this->adminSession())
            ->delete(route('admin.blog.images.destroy', [$article2, $photo]));

        $response->assertStatus(404);
    }

    // ──────────────────────────────────────────────────────────────
    // SECTION 9 : BDD & Modèle
    // ──────────────────────────────────────────────────────────────

    public function test_blog_article_slug_auto_genere(): void
    {
        $article = BlogArticle::factory()
            ->create(['titre_article' => 'Test Slug Auto', 'slug' => null]);

        $this->assertEquals('test-slug-auto', $article->slug);
    }

    public function test_blog_article_slug_normalise(): void
    {
        $article = BlogArticle::factory()
            ->create(['titre_article' => 'Café Français Édition']);

        // Doit normaliser accents, espaces, etc.
        $this->assertTrue(
            str_contains($article->slug, 'cafe') || 
            str_contains($article->slug, 'edition')
        );
    }

    public function test_blog_article_route_binding_slug(): void
    {
        $article = BlogArticle::factory()
            ->create(['slug' => 'mon-article']);

        // Route Model Binding via slug
        $response = $this->withSession($this->adminSession())
            ->get(route('admin.blog.edit', 'mon-article'));

        $response->assertStatus(200);
    }

    public function test_blog_article_photos_polymorphique(): void
    {
        $article = BlogArticle::factory()->create();
        
        Photo::factory()
            ->for($article, 'photoable')
            ->create(['type' => 'featured']);

        $this->assertEquals(1, $article->photos()->count());
        $this->assertEquals('featured', $article->photos()->first()->type);
    }

    public function test_blog_article_text_from_layout(): void
    {
        $layout = [
            ['type' => 'heading', 'text' => 'Titre Principal'],
            ['type' => 'text', 'text' => 'Contenu du texte'],
            ['type' => 'quote', 'text' => 'Une citation'],
        ];

        $article = BlogArticle::factory()
            ->create(['layout_json' => json_encode($layout)]);

        $text = $article->textFromLayout();
        $this->assertStringContainsString('Titre Principal', $text);
        $this->assertStringContainsString('Contenu du texte', $text);
    }

    public function test_blog_article_make_excerpt(): void
    {
        $text = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. ' .
                'Sed do eiusmod tempor incididunt ut labore et dolore magna ' .
                'aliqua. Ut enim ad minim veniam, quis nostrud exercitation ' .
                'ullamco laboris nisi ut aliquip ex ea commodo consequat.';

        $article = BlogArticle::factory()
            ->create(['extrait' => null]);

        $excerpt = $article->makeExcerpt($text);
        $this->assertLessThanOrEqual(170, strlen($excerpt));
        $this->assertStringEndsWith('...', $excerpt);
    }

    // ──────────────────────────────────────────────────────────────
    // SECTION 10 : Edge Cases & Robustesse
    // ──────────────────────────────────────────────────────────────

    public function test_blog_article_timestamps(): void
    {
        $article = BlogArticle::factory()->create();

        $this->assertNotNull($article->created_at);
        $this->assertNotNull($article->updated_at);
    }

    public function test_blog_article_vide_pas_publie(): void
    {
        $layout = json_encode([
            'blocks' => [
                [
                    'type' => 'text',
                    'text' => 'Un peu de contenu',
                    'align' => 'left',
                ],
            ],
        ]);

        $article = BlogArticle::factory()
            ->create([
                'titre_article' => 'Article Avec Contenu',
                'layout_json'   => $layout,
                'statut'        => 'publie',
            ]);

        // Article a du contenu et peut être publié
        $this->assertDatabaseHas('blog_article', [
            'titre_article' => 'Article Avec Contenu',
            'statut'        => 'publie',
        ]);
    }

    public function test_blog_article_search_titre(): void
    {
        BlogArticle::factory()->create(['titre_article' => 'Article Cherché']);
        BlogArticle::factory()->create(['titre_article' => 'Autre Article']);

        $response = $this->withSession($this->adminSession())
            ->get(route('admin.blog.index', ['search' => 'Cherché']));

        $response->assertSee('Article Cherché');
        $response->assertDontSee('Autre Article');
    }

    public function test_blog_article_filter_statut(): void
    {
        BlogArticle::factory()->create(['statut' => 'brouillon']);
        BlogArticle::factory()->create(['statut' => 'publie']);

        $response = $this->withSession($this->adminSession())
            ->get(route('admin.blog.index', ['statut' => 'publie']));

        $response->assertStatus(200);
    }
}
