<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ReferenceController extends Controller
{
    /**
     * Configuration de chaque type de référence.
     * slug => [model, champ_libelle, label_singulier, label_pluriel, pk]
     */
    private static function config(): array
    {
        return [
            'elements'         => ['model' => \App\Models\Elements::class,    'field' => 'libelle_element',  'pk' => 'id_element',     'label' => 'Élément',         'plural' => 'Éléments'],
            'types-armes'      => ['model' => \App\Models\TypeArme::class,     'field' => 'libelle_TArme',    'pk' => 'id_TArmes',      'label' => 'Type d\'arme',    'plural' => 'Types d\'armes'],
            'types-perso'      => ['model' => \App\Models\TypePerso::class,    'field' => 'libelle_TP',       'pk' => 'id_TP',          'label' => 'Type de perso',   'plural' => 'Types de perso'],
            'types-aptitudes'  => ['model' => \App\Models\TypeApti::class,     'field' => 'libelle_Apti',     'pk' => 'id_TypeApti',    'label' => 'Type d\'aptitude','plural' => 'Types d\'aptitudes'],
            'types-ennemis'    => ['model' => \App\Models\TypeEnnemi::class,   'field' => 'libelle_Type',     'pk' => 'id_typeEnnemi',  'label' => 'Type d\'ennemi',  'plural' => 'Types d\'ennemis'],
            'types-animaux'    => ['model' => \App\Models\TypeAnimal::class,   'field' => 'libelle_TAnimal',  'pk' => 'id_TAnimal',     'label' => 'Type d\'animal',  'plural' => 'Types d\'animaux'],
            'types-materiaux'  => ['model' => \App\Models\TypeMateriaux::class,'field' => 'libelle_TypeM',    'pk' => 'id_typeM',       'label' => 'Type de matériau','plural' => 'Types de matériaux'],
            'etoiles'          => ['model' => \App\Models\Etoile::class,       'field' => 'libelle',          'pk' => 'id_etoile',      'label' => 'Étoile',          'plural' => 'Étoiles'],
            'reactions'        => ['model' => \App\Models\Reaction::class,     'field' => 'nom_reaction',     'pk' => 'id_reaction',    'label' => 'Réaction',        'plural' => 'Réactions'],
        ];
    }

    private function resolve(string $type): array
    {
        $config = self::config();
        if (!isset($config[$type])) {
            abort(404, "Type de référence inconnu : {$type}");
        }
        return $config[$type];
    }

    public static function allTypes(): array
    {
        return self::config();
    }

    public function index(string $type): View
    {
        $cfg   = $this->resolve($type);
        $items = $cfg['model']::with('photos')->orderBy($cfg['field'])->get();
        $serializedItems = $items->map(fn($item) => $this->serializeItem($item, $cfg))->values();

        return view('admin.references.index', [
            'type'   => $type,
            'cfg'    => $cfg,
            'items'  => $items,
            'serializedItems' => $serializedItems,
            'allTypes' => self::config(),
        ]);
    }

    public function store(Request $request, string $type): JsonResponse
    {
        $cfg = $this->resolve($type);

        $request->validate([
            'libelle' => ['required', 'string', 'max:120'],
            'icon_url' => ['sometimes', 'nullable', 'string', 'max:500'],
            'icon_file' => ['sometimes', 'nullable', 'image', 'max:4096'],
        ]);

        $data = [$cfg['field'] => $request->input('libelle')];

        if ($type === 'reactions') {
            $data['slug'] = Str::slug($request->input('libelle'));
        }

        $item = $cfg['model']::create($data);
        $this->syncIcon(
            $item,
            $request->input('icon_url'),
            $request->file('icon_file'),
            true
        );

        return response()->json([
            'success' => true,
            'item'    => $this->serializeItem($item->fresh('photos'), $cfg),
        ]);
    }

    public function update(Request $request, string $type, int $id): JsonResponse
    {
        $cfg = $this->resolve($type);

        $request->validate([
            'libelle' => ['required', 'string', 'max:120'],
            'icon_url' => ['sometimes', 'nullable', 'string', 'max:500'],
            'icon_file' => ['sometimes', 'nullable', 'image', 'max:4096'],
        ]);

        $item = $cfg['model']::findOrFail($id);
        $item->{$cfg['field']} = $request->input('libelle');

        if ($type === 'reactions') {
            $item->slug = Str::slug($request->input('libelle'));
        }

        $item->save();
        $shouldSyncIcon = $request->has('icon_url') || $request->hasFile('icon_file');
        $this->syncIcon(
            $item,
            $request->input('icon_url'),
            $request->file('icon_file'),
            $shouldSyncIcon
        );

        return response()->json([
            'success' => true,
            'item'    => $this->serializeItem($item->fresh('photos'), $cfg),
        ]);
    }

    public function destroy(string $type, int $id): JsonResponse
    {
        $cfg  = $this->resolve($type);
        $item = $cfg['model']::findOrFail($id);

        try {
            $this->deleteLocalIconFile($item->photos()->first());
            $item->photos()->delete();
            $item->delete();
            return response()->json(['success' => true]);
        } catch (\Illuminate\Database\QueryException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Impossible de supprimer : cet élément est utilisé ailleurs.',
            ], 422);
        }
    }

    private function serializeItem(object $item, array $cfg): array
    {
        $photo = $item->photos()->first();

        return [
            'id' => $item->getKey(),
            'libelle' => $item->{$cfg['field']},
            'icon_url' => $this->resolveIconUrl($photo),
            'icon_raw' => $this->resolveRawIconValue($photo),
        ];
    }

    private function syncIcon(object $item, ?string $iconUrl, ?UploadedFile $iconFile, bool $shouldSync): void
    {
        if (!$shouldSync) {
            return;
        }

        $photo = $item->photos()->first();

        if ($iconFile) {
            $this->deleteLocalIconFile($photo);
            $storedPath = $iconFile->store('references/icons', 'public');

            $item->photos()->updateOrCreate(
                [
                    'photoable_type' => get_class($item),
                    'photoable_id' => $item->getKey(),
                ],
                [
                    'chemin_photo' => $storedPath,
                    'source_url' => null,
                    'type' => 'icon',
                ]
            );

            return;
        }

        $value = trim((string) $iconUrl);

        if ($value === '') {
            $this->deleteLocalIconFile($photo);
            $item->photos()->delete();
            return;
        }

        $localPath = $this->extractLocalPathFromInput($value);
        $cheminPhoto = $localPath ?? $value;
        $sourceUrl = ($localPath === null && filter_var($value, FILTER_VALIDATE_URL)) ? $value : null;

        if ($photo && $photo->chemin_photo !== $cheminPhoto) {
            $this->deleteLocalIconFile($photo);
        }

        $item->photos()->updateOrCreate(
            [
                'photoable_type' => get_class($item),
                'photoable_id' => $item->getKey(),
            ],
            [
                'chemin_photo' => $cheminPhoto,
                'source_url' => $sourceUrl,
                'type' => 'icon',
            ]
        );
    }

    private function resolveIconUrl(object|null $photo): ?string
    {
        if (!$photo) {
            return null;
        }

        if (!empty($photo->source_url)) {
            return $photo->source_url;
        }

        $path = trim((string) $photo->chemin_photo);
        if ($path === '') {
            return null;
        }

        if (filter_var($path, FILTER_VALIDATE_URL)) {
            return $path;
        }

        if (Str::startsWith($path, ['/storage/', 'storage/'])) {
            return asset(ltrim($path, '/'));
        }

        return $this->publicStorageUrl($path);
    }

    private function resolveRawIconValue(object|null $photo): ?string
    {
        if (!$photo) {
            return null;
        }

        if (!empty($photo->source_url)) {
            return $photo->source_url;
        }

        $path = trim((string) $photo->chemin_photo);
        if ($path === '') {
            return null;
        }

        if (filter_var($path, FILTER_VALIDATE_URL)) {
            return $path;
        }

        if (Str::startsWith($path, ['/storage/', 'storage/'])) {
            return asset(ltrim($path, '/'));
        }

        return $this->publicStorageUrl($path);
    }

    private function extractLocalPathFromInput(string $value): ?string
    {
        if (Str::startsWith($value, '/storage/')) {
            return ltrim(substr($value, strlen('/storage/')), '/');
        }

        if (Str::startsWith($value, 'storage/')) {
            return ltrim(substr($value, strlen('storage/')), '/');
        }

        if (filter_var($value, FILTER_VALIDATE_URL)) {
            $path = parse_url($value, PHP_URL_PATH);
            if (is_string($path) && Str::startsWith($path, '/storage/')) {
                return ltrim(substr($path, strlen('/storage/')), '/');
            }
        }

        return null;
    }

    private function deleteLocalIconFile(object|null $photo): void
    {
        if (!$photo) {
            return;
        }

        $path = trim((string) $photo->chemin_photo);
        if ($path === '' || filter_var($path, FILTER_VALIDATE_URL)) {
            return;
        }

        $localPath = $this->extractLocalPathFromInput($path) ?? $path;
        Storage::disk('public')->delete($localPath);
    }

    private function publicStorageUrl(string $path): string
    {
        return asset('storage/' . ltrim($path, '/'));
    }
}
