<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
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

        return view('admin.references.index', [
            'type'   => $type,
            'cfg'    => $cfg,
            'items'  => $items,
            'allTypes' => self::config(),
        ]);
    }

    public function store(Request $request, string $type): JsonResponse
    {
        $cfg = $this->resolve($type);

        $request->validate([
            'libelle' => ['required', 'string', 'max:120'],
            'icon_url' => ['nullable', 'string', 'max:500'],
        ]);

        $data = [$cfg['field'] => $request->input('libelle')];

        if ($type === 'reactions') {
            $data['slug'] = Str::slug($request->input('libelle'));
        }

        $item = $cfg['model']::create($data);
        $this->syncIcon($item, $request->input('icon_url'));

        return response()->json([
            'success' => true,
            'item'    => [
                'id' => $item->getKey(),
                'libelle' => $item->{$cfg['field']},
                'icon_url' => $this->extractIcon($item),
            ],
        ]);
    }

    public function update(Request $request, string $type, int $id): JsonResponse
    {
        $cfg = $this->resolve($type);

        $request->validate([
            'libelle' => ['required', 'string', 'max:120'],
            'icon_url' => ['nullable', 'string', 'max:500'],
        ]);

        $item = $cfg['model']::findOrFail($id);
        $item->{$cfg['field']} = $request->input('libelle');

        if ($type === 'reactions') {
            $item->slug = Str::slug($request->input('libelle'));
        }

        $item->save();
        $this->syncIcon($item, $request->input('icon_url'));

        return response()->json([
            'success' => true,
            'item'    => [
                'id' => $item->getKey(),
                'libelle' => $item->{$cfg['field']},
                'icon_url' => $this->extractIcon($item),
            ],
        ]);
    }

    public function destroy(string $type, int $id): JsonResponse
    {
        $cfg  = $this->resolve($type);
        $item = $cfg['model']::findOrFail($id);

        try {
            $item->delete();
            return response()->json(['success' => true]);
        } catch (\Illuminate\Database\QueryException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Impossible de supprimer : cet élément est utilisé ailleurs.',
            ], 422);
        }
    }

    private function extractIcon(object $item): ?string
    {
        $photo = $item->photos()->first();
        return $photo?->source_url ?: $photo?->chemin_photo;
    }

    private function syncIcon(object $item, ?string $iconUrl): void
    {
        $value = trim((string) $iconUrl);

        if ($value === '') {
            $item->photos()->delete();
            return;
        }

        $item->photos()->updateOrCreate(
            [
                'photoable_type' => get_class($item),
                'photoable_id' => $item->getKey(),
            ],
            [
                'chemin_photo' => $value,
                'source_url' => filter_var($value, FILTER_VALIDATE_URL) ? $value : null,
                'type' => 'icon',
            ]
        );
    }
}
