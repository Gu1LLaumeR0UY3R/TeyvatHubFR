<?php

namespace App\Console\Commands;

use App\Models\Artefact;
use App\Models\Photo;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DownloadArtefactImages extends Command
{
    protected $signature = 'artefacts:download-images
                            {--skip-existing : Ne pas re-télécharger les fichiers déjà présents}
                            {--no-db : Ne pas mettre à jour la base de données}';

    protected $description = 'Télécharge les icônes et images des sets d\'artefacts depuis l\'API teyvat-dev';

    private const API_URL = 'https://teyvat-dev.vercel.app/api/artifacts';

    private const PIECE_SHORT = [
        'flower_of_life'    => 'flower',
        'sands_of_eon'      => 'sands',
        'plume_of_death'    => 'plume',
        'circlet_of_logos'  => 'circlet',
        'goblet_of_eonothem'=> 'goblet',
    ];

    private int $downloaded = 0;
    private int $skipped    = 0;
    private int $failed     = 0;

    public function handle(): int
    {
        $this->info('📥 Récupération des artefacts depuis l\'API…');

        $response = Http::timeout(30)->get(self::API_URL);

        if ($response->failed()) {
            $this->error('Impossible de joindre l\'API : ' . $response->status());
            return Command::FAILURE;
        }

        $artifacts = $response->json();

        if (empty($artifacts) || !is_array($artifacts)) {
            $this->error('Réponse API invalide ou vide.');
            return Command::FAILURE;
        }

        $this->info('✅ ' . count($artifacts) . ' sets récupérés.');
        $this->newLine();

        // Créer les dossiers si nécessaire
        Storage::disk('public')->makeDirectory('photos/artefacts/icones_arte');
        Storage::disk('public')->makeDirectory('photos/artefacts/arte_ful');

        $skipExisting = $this->option('skip-existing');
        $noDB         = $this->option('no-db');

        $bar = $this->output->createProgressBar(count($artifacts));
        $bar->setFormat(' %current%/%max% [%bar%] %percent:3s%% — %message%');
        $bar->start();

        foreach ($artifacts as $artifact) {
            $name = $artifact['name'] ?? 'unknown';
            $slug = Str::slug($name);

            $bar->setMessage($name);

            // --- Icône principale (icones_arte) ---
            $iconUrl = $artifact['icon_url'] ?? null;
            if (!empty($iconUrl)) {
                $iconPath = "photos/artefacts/icones_arte/{$slug}.png";
                $iconSaved = $this->downloadFile($iconUrl, $iconPath, $skipExisting);
            } else {
                $iconSaved = false;
            }

            // --- Pièces du set (arte_ful) ---
            $setIcons = $artifact['set_icons'] ?? [];
            $firstPiecePath = null;

            foreach (self::PIECE_SHORT as $apiKey => $short) {
                $pieceUrl = $setIcons[$apiKey] ?? null;
                if (empty($pieceUrl)) {
                    continue;
                }

                $ext       = $this->guessExtension($pieceUrl);
                $piecePath = "photos/artefacts/arte_ful/{$slug}-{$short}.{$ext}";

                $saved = $this->downloadFile($pieceUrl, $piecePath, $skipExisting);

                if ($saved && $firstPiecePath === null) {
                    $firstPiecePath = $piecePath;
                }
            }

            // --- Mise à jour DB ---
            if (!$noDB) {
                $this->syncDatabase($artifact, $slug, $iconUrl, $iconPath ?? null);
            }

            $bar->advance();
        }

        $bar->setMessage('Terminé');
        $bar->finish();
        $this->newLine(2);

        $this->table(
            ['Téléchargés', 'Ignorés (déjà présents)', 'Échecs'],
            [[$this->downloaded, $this->skipped, $this->failed]]
        );

        if ($this->failed > 0) {
            $this->warn("{$this->failed} fichier(s) n'ont pas pu être téléchargés (URLs invalides ou expirées).");
        }

        return Command::SUCCESS;
    }

    // -------------------------------------------------------------------------

    private function downloadFile(string $url, string $storagePath, bool $skipExisting): bool
    {
        if ($skipExisting && Storage::disk('public')->exists($storagePath)) {
            $this->skipped++;
            return false;
        }

        try {
            $response = Http::timeout(20)->withoutVerifying()->get($url);

            if ($response->failed() || empty($response->body())) {
                $this->failed++;
                return false;
            }

            $contentType = $response->header('Content-Type');

            // Vérification basique que c'est bien une image
            if ($contentType && !str_starts_with($contentType, 'image/')) {
                $this->failed++;
                return false;
            }

            Storage::disk('public')->put($storagePath, $response->body());
            $this->downloaded++;
            return true;

        } catch (\Exception) {
            $this->failed++;
            return false;
        }
    }

    private function guessExtension(string $url): string
    {
        $path = parse_url($url, PHP_URL_PATH);
        $ext  = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        return in_array($ext, ['png', 'jpg', 'jpeg', 'webp', 'gif']) ? $ext : 'png';
    }

    private function syncDatabase(array $artifact, string $slug, ?string $iconUrl, ?string $localIconPath): void
    {
        // On tente de trouver ou créer l'artefact par slug
        $artefact = Artefact::firstOrNew(['slug' => $slug]);

        $setEffects = $artifact['set_effects'] ?? [];

        $artefact->nom_artefact = $artifact['name'];
        $artefact->slug         = $slug;
        $artefact->bonus_2p     = $setEffects['2'] ?? null;
        $artefact->bonus_4p     = $setEffects['4'] ?? null;

        // fid_rareté : non fourni par l'API, on ne l'écrase pas s'il existe déjà
        if (!$artefact->exists) {
            // Valeur par défaut 5★ si table rareté disponible
            $artefact->fid_rareté = \App\Models\Rarete::where('nb_etoile', 5)
                ->orWhere('libelle', 'like', '5%')
                ->value('id_rareté') ?? 1;
        }

        $artefact->save();

        // Photo polymorphique : 1 photo = l'icône principale
        if ($localIconPath && Storage::disk('public')->exists($localIconPath)) {
            $artefact->photos()->updateOrCreate(
                [
                    'photoable_type' => Artefact::class,
                    'photoable_id'   => $artefact->id_artefact,
                    'type'           => 'icon',
                ],
                [
                    'chemin_photo' => $localIconPath,
                    'source_url'   => $iconUrl,
                ]
            );
        } elseif ($iconUrl) {
            // Pas encore téléchargée localement → stocker l'URL directe
            $artefact->photos()->updateOrCreate(
                [
                    'photoable_type' => Artefact::class,
                    'photoable_id'   => $artefact->id_artefact,
                    'type'           => 'icon',
                ],
                [
                    'chemin_photo' => $iconUrl,
                    'source_url'   => $iconUrl,
                ]
            );
        }
    }
}
