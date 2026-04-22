@php
    $isEdit = $article !== null;
    $slugBases = ($slugPresets ?? collect())->pluck('slug_base')->values()->all();
@endphp

@if ($errors->any())
    <div class="mb-4 rounded border border-red-300 bg-red-50 p-3 text-sm text-red-700">
        <ul class="list-disc pl-5">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

@php
    $currentArticle = $article ?? null;
    $featuredPhotos = $article?->photos?->where('type', 'featured')->values() ?? collect();
    $inlinePhotos   = $article?->photos?->where('type', 'inline')->values() ?? collect();
    $inlineLibrary  = $inlinePhotos->map(fn($photo) => [
        'id'    => (int) $photo->id_photo,
        'url'   => $currentArticle ? $currentArticle->resolvePhotoUrl($photo) : null,
        'label' => 'Image #' . $photo->id_photo,
    ])->values()->all();

    $oldLayoutJson = old('layout_json');
    $decodedOldLayout = is_string($oldLayoutJson) && trim($oldLayoutJson) !== '' ? json_decode($oldLayoutJson, true) : null;
    $rawLayout = is_array($decodedOldLayout) ? $decodedOldLayout : ($article?->layout_json ?? null);

    // Normalize block to new vertical schema (drop x/y/w/h; add level/size/caption)
    $normalizeLayoutBlock = function (array $block): array {
        $type = (string) ($block['type'] ?? 'text');
        $normalized = [
            'id'      => $block['id'] ?? ('block-' . uniqid()),
            'type'    => $type,
            'text'    => (string) ($block['text'] ?? ''),
            'align'   => in_array(($block['align'] ?? 'left'), ['left', 'center', 'right']) ? $block['align'] : 'left',
            'level'   => 2,
            'photoId' => null,
            'url'     => (string) ($block['url'] ?? ''),
            'imageInputMode' => in_array(($block['imageInputMode'] ?? 'url'), ['url', 'drive', 'file'], true)
                ? (($block['imageInputMode'] ?? 'url') === 'file' ? 'drive' : (string) $block['imageInputMode'])
                : 'url',
            'size'    => 'medium',
            'caption' => (string) ($block['caption'] ?? ''),
            'weight'  => in_array(($block['weight'] ?? 'normal'), ['normal', 'medium', 'bold']) ? $block['weight'] : 'normal',
            'tone'    => in_array(($block['tone'] ?? 'default'), ['default', 'muted', 'accent']) ? $block['tone'] : 'default',
            'spacing' => in_array(($block['spacing'] ?? 'normal'), ['compact', 'normal', 'relaxed']) ? $block['spacing'] : 'normal',
            'columns' => in_array((int) ($block['columns'] ?? 2), [2, 3], true) ? (int) ($block['columns'] ?? 2) : 2,
            'col1'    => (string) ($block['col1'] ?? ''),
            'col2'    => (string) ($block['col2'] ?? ''),
            'col3'    => (string) ($block['col3'] ?? ''),
            'italic'  => (bool) ($block['italic'] ?? false),
        ];
        if ($type === 'heading') {
            $fontSize = (int) ($block['fontSize'] ?? 32);
            $normalized['level'] = (int) ($block['level'] ?? ($fontSize >= 28 ? 2 : ($fontSize >= 22 ? 3 : 4)));
        }
        if ($type === 'image') {
            $normalized['photoId'] = isset($block['photoId']) && $block['photoId'] ? (int) $block['photoId'] : null;
            $w = (int) ($block['w'] ?? 0);
            $normalized['size'] = $block['size'] ?? ($w >= 800 ? 'full' : ($w >= 600 ? 'large' : ($w >= 300 ? 'medium' : 'small')));
        }
        return $normalized;
    };

    if (is_array($rawLayout) && isset($rawLayout['blocks']) && is_array($rawLayout['blocks'])) {
        $initialLayout = ['blocks' => array_values(array_map($normalizeLayoutBlock, $rawLayout['blocks']))];
    } else {
        $initialLayout = ['blocks' => []];
    }

    $googleDriveConfig = [
        'browseUrl' => route('admin.google-drive.browse'),
        'folderId' => (string) config('services.google_drive.folder_id', ''),
        'folderUrl' => (string) config('services.google_drive.folder_url', ''),
    ];
@endphp

<form method="POST" action="{{ $action }}" class="space-y-5" enctype="multipart/form-data"
    x-data="blogArticleForm(@js($slugBases), @js(route('admin.blog.slugs.store')), @js(old('slug', $article?->slug)), @js($initialLayout), @js($inlineLibrary), @js(old('titre_article', $article?->titre_article)), @js($googleDriveConfig))"
>
    @csrf
    @if($method !== 'POST')
        @method($method)
    @endif

    <div>
        <label for="titre_article" class="block text-sm font-medium text-slate-700 mb-1">Titre</label>
        <input id="titre_article" name="titre_article" type="text" required
               value="{{ old('titre_article', $article?->titre_article) }}" x-model="articleTitle"
               class="w-full rounded border border-slate-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-hub-gold" />
    </div>

    <div>
        <label for="slug" class="block text-sm font-medium text-slate-700 mb-1">Slug (optionnel)</label>
        <input id="slug" name="slug" type="text" x-ref="slugInput" x-model="slugInputValue"
               @input="handleSlugInput()"
               value="{{ old('slug', $article?->slug) }}"
               class="w-full rounded border border-slate-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-hub-gold" />
        <div class="mt-2 space-y-2" x-show="normalizedSlugValue">
            <template x-if="exactSlugMatch">
                <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-xs text-emerald-700">
                    Slug existant détecté: <span class="font-semibold" x-text="exactSlugMatch"></span>
                </div>
            </template>

            <template x-if="!exactSlugMatch && similarSlugs.length">
                <div class="rounded-lg border border-slate-200 bg-slate-50 px-3 py-2">
                    <div class="mb-2 text-xs font-semibold uppercase tracking-wide text-slate-500">Slugs proches</div>
                    <div class="flex flex-wrap gap-2">
                        <template x-for="slug in similarSlugs" :key="`slug-similar-${slug}`">
                            <button type="button" @click="useExistingSlug(slug)" class="rounded border border-slate-300 px-2 py-1 text-xs text-slate-700 hover:bg-slate-100">
                                <span x-text="slug"></span>
                            </button>
                        </template>
                    </div>
                </div>
            </template>

            <template x-if="canAddCurrentSlug">
                <div class="rounded-lg border border-amber-200 bg-amber-50 px-3 py-2">
                    <div class="flex items-center justify-between gap-3">
                        <p class="text-xs text-amber-800">
                            Aucun slug équivalent trouvé pour <span class="font-semibold" x-text="normalizedSlugValue"></span>.
                        </p>
                        <button type="button" @click="addSlugPreset()" class="shrink-0 rounded border border-amber-300 px-3 py-1.5 text-xs font-medium text-amber-900 hover:bg-amber-100">
                            Ajouter ce slug
                        </button>
                    </div>
                </div>
            </template>
        </div>
        <p class="mt-1 text-xs" :class="slugFeedbackError ? 'text-red-600' : 'text-emerald-600'" x-text="slugFeedback" x-show="slugFeedback"></p>
        <p class="mt-1 text-xs text-slate-500">Astuce: laisse vide pour auto-générer depuis le titre à l'enregistrement.</p>
    </div>

    <input type="hidden" name="layout_json" :value="serializedLayout">

    <div class="rounded-2xl border border-slate-200 bg-white/80 p-2 xl:hidden">
        <div class="grid grid-cols-2 gap-2">
            <button type="button" @click="activePanel = 'editor'"
                    class="rounded-xl px-4 py-2 text-sm font-medium transition"
                    :class="activePanel === 'editor' ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'">
                Éditeur
            </button>
            <button type="button" @click="activePanel = 'preview'"
                    class="rounded-xl px-4 py-2 text-sm font-medium transition"
                    :class="activePanel === 'preview' ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'">
                Prévisualisation
            </button>
        </div>
    </div>

    <div class="grid gap-6 xl:grid-cols-12 xl:items-start">
        <div class="space-y-5 xl:col-span-7" x-show="activePanel === 'editor' || isDesktop">
            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                <div class="mb-4 flex items-start justify-between gap-3">
                    <div>
                        <h2 class="text-base font-bold text-slate-800">Contenu de l'article</h2>
                        <p class="text-xs text-slate-500">Compose les blocs à gauche, garde l’aperçu à droite en référence pendant l’écriture.</p>
                    </div>
                    <button type="button" class="hidden rounded-lg border border-slate-300 px-3 py-2 text-xs font-medium text-slate-700 hover:bg-slate-100 xl:block"
                            @click="selectedBlockId = blocks[0]?.id || null">
                        Revenir en haut
                    </button>
                </div>

                <div class="mb-4 rounded-xl border border-slate-200 bg-white p-3">
                    <div class="mb-2 text-xs font-semibold uppercase tracking-wide text-slate-500">Ajout rapide (illimité)</div>
                    <div class="flex flex-wrap gap-2">
                        <button type="button" @click="appendBlock('heading')" class="rounded border border-slate-300 bg-white px-3 py-1.5 text-xs text-slate-700 hover:bg-slate-100">+ Titre</button>
                        <button type="button" @click="appendBlock('text')" class="rounded border border-slate-300 bg-white px-3 py-1.5 text-xs text-slate-700 hover:bg-slate-100">+ Texte</button>
                        <button type="button" @click="appendBlock('quote')" class="rounded border border-slate-300 bg-white px-3 py-1.5 text-xs text-slate-700 hover:bg-slate-100">+ Citation</button>
                        <button type="button" @click="appendBlock('columns')" class="rounded border border-slate-300 bg-white px-3 py-1.5 text-xs text-slate-700 hover:bg-slate-100">+ Colonnes</button>
                        <button type="button" @click="appendBlock('image')" class="rounded border border-slate-300 bg-white px-3 py-1.5 text-xs text-slate-700 hover:bg-slate-100">+ Image</button>
                        <button type="button" @click="appendBlock('divider')" class="rounded border border-slate-300 bg-white px-3 py-1.5 text-xs text-slate-700 hover:bg-slate-100">+ Séparateur</button>
                    </div>
                    <p class="mt-2 text-xs text-slate-500">Astuce: dans un bloc texte/titre/citation, tape <span class="font-semibold">/</span> pour ouvrir les commandes rapides.</p>
                </div>

                <div x-show="!blocks.length" class="rounded-xl border-2 border-dashed border-slate-300 bg-white px-4 py-12 text-center">
                    <p class="mb-4 text-sm font-medium text-slate-500">Aucun contenu — ajoute ton premier module</p>
                    <div class="flex flex-wrap justify-center gap-2">
                        <button type="button" @click="addBlockAt(0, 'heading')" class="rounded border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 hover:bg-slate-100">＋ Titre</button>
                        <button type="button" @click="addBlockAt(0, 'text')" class="rounded border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 hover:bg-slate-100">＋ Texte</button>
                        <button type="button" @click="addBlockAt(0, 'columns')" class="rounded border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 hover:bg-slate-100">＋ Colonnes</button>
                        <button type="button" @click="addBlockAt(0, 'image')" class="rounded border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 hover:bg-slate-100">＋ Image</button>
                        <button type="button" @click="addBlockAt(0, 'divider')" class="rounded border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 hover:bg-slate-100">＋ Séparateur</button>
                        <button type="button" @click="addBlockAt(0, 'quote')" class="rounded border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 hover:bg-slate-100">＋ Citation</button>
                    </div>
                </div>

                <div x-show="blocks.length">
                    <div class="mb-1 flex items-center py-1">
                        <div x-show="addPickerAt !== 0" class="flex w-full items-center gap-2">
                            <div class="h-px flex-1 bg-slate-200"></div>
                            <button type="button" @click="addPickerAt = 0"
                                    class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full border border-slate-300 bg-white text-xs text-slate-500 transition hover:border-hub-gold hover:bg-hub-gold hover:text-hub-bg">+</button>
                            <div class="h-px flex-1 bg-slate-200"></div>
                        </div>
                        <div x-show="addPickerAt === 0"
                             class="flex w-full flex-wrap items-center gap-2 rounded-xl border border-hub-gold/50 bg-hub-gold/5 px-3 py-2">
                            <span class="text-xs font-semibold text-slate-600">Insérer :</span>
                            <button type="button" @click="addBlockAt(addPickerAt, 'heading')" class="rounded border border-slate-300 bg-white px-3 py-1 text-xs text-slate-700 hover:bg-slate-100">Titre</button>
                            <button type="button" @click="addBlockAt(addPickerAt, 'text')" class="rounded border border-slate-300 bg-white px-3 py-1 text-xs text-slate-700 hover:bg-slate-100">Texte</button>
                            <button type="button" @click="addBlockAt(addPickerAt, 'columns')" class="rounded border border-slate-300 bg-white px-3 py-1 text-xs text-slate-700 hover:bg-slate-100">Colonnes</button>
                            <button type="button" @click="addBlockAt(addPickerAt, 'image')" class="rounded border border-slate-300 bg-white px-3 py-1 text-xs text-slate-700 hover:bg-slate-100">Image</button>
                            <button type="button" @click="addBlockAt(addPickerAt, 'divider')" class="rounded border border-slate-300 bg-white px-3 py-1 text-xs text-slate-700 hover:bg-slate-100">Séparateur</button>
                            <button type="button" @click="addBlockAt(addPickerAt, 'quote')" class="rounded border border-slate-300 bg-white px-3 py-1 text-xs text-slate-700 hover:bg-slate-100">Citation</button>
                            <button type="button" @click="addPickerAt = null" class="ml-auto rounded px-2 py-1 text-xs text-slate-400 hover:text-slate-600">✕</button>
                        </div>
                    </div>

                    <template x-for="(block, index) in blocks" :key="block.id">
                        <div>
                            <div class="mb-1 rounded-xl border border-slate-200 bg-white transition relative"
                                 :class="selectedBlockId === block.id ? 'border-hub-gold ring-2 ring-hub-gold/20' : 'hover:border-slate-300'"
                                 @click="selectedBlockId = block.id">
                                <div class="flex flex-wrap items-center gap-2 border-b border-slate-100 px-4 py-2">
                                    <span class="rounded bg-slate-100 px-2 py-0.5 text-xs font-semibold text-slate-500"
                                          x-text="blockTypeLabel(block.type)"></span>

                                    <select x-show="block.type === 'heading'" x-model.number="block.level" @click.stop
                                            class="rounded border border-slate-200 px-2 py-0.5 text-xs text-slate-600 focus:outline-none focus:ring-1 focus:ring-hub-gold">
                                        <option value="2">H2</option>
                                        <option value="3">H3</option>
                                        <option value="4">H4</option>
                                    </select>

                                    <div x-show="block.type === 'image'" class="flex gap-1">
                                        <template x-for="sz in [{v:'small',l:'S'},{v:'medium',l:'M'},{v:'large',l:'L'},{v:'full',l:'XL'}]" :key="`sz-${sz.v}`">
                                            <button type="button" @click.stop="block.size = sz.v"
                                                    class="rounded border px-2 py-0.5 text-xs transition"
                                                    :class="(block.size||'medium') === sz.v ? 'border-hub-gold bg-hub-gold/10 font-semibold text-slate-900' : 'border-slate-300 text-slate-500 hover:bg-slate-50'"
                                                    x-text="sz.l"></button>
                                        </template>
                                    </div>

                                    <div x-show="['heading','text','quote'].includes(block.type)" class="flex gap-1">
                                        <template x-for="al in [{v:'left',l:'Gauche'},{v:'center',l:'Centré'},{v:'right',l:'Droite'}]" :key="`al-${al.v}`">
                                            <button type="button" @click.stop="block.align = al.v"
                                                    class="rounded border px-2 py-0.5 text-xs transition"
                                                    :class="(block.align||'left') === al.v ? 'border-hub-gold bg-hub-gold/10 text-slate-900' : 'border-slate-300 text-slate-400 hover:bg-slate-50'"
                                                    x-text="al.l"></button>
                                        </template>
                                    </div>

                                    <select x-show="block.type === 'columns'" x-model.number="block.columns" @click.stop
                                            class="rounded border border-slate-200 px-2 py-0.5 text-xs text-slate-600 focus:outline-none focus:ring-1 focus:ring-hub-gold">
                                        <option value="2">2 colonnes</option>
                                        <option value="3">3 colonnes</option>
                                    </select>

                                    <button type="button"
                                            x-show="['heading','text','quote'].includes(block.type)"
                                            @click.stop="block.italic = !block.italic"
                                            class="rounded border px-2 py-0.5 text-xs font-italic transition"
                                            :class="block.italic ? 'border-hub-gold bg-hub-gold/10 font-semibold italic text-slate-900' : 'border-slate-300 text-slate-400 hover:bg-slate-50'"
                                            title="Italique">I</button>

                                    <select x-show="['heading','text','quote'].includes(block.type)" x-model="block.weight" @click.stop
                                            class="rounded border border-slate-200 px-2 py-0.5 text-xs text-slate-600 focus:outline-none focus:ring-1 focus:ring-hub-gold">
                                        <option value="normal">Normal</option>
                                        <option value="medium">Semi-gras</option>
                                        <option value="bold">Gras</option>
                                    </select>

                                    <select x-show="['heading','text','quote'].includes(block.type)" x-model="block.tone" @click.stop
                                            class="rounded border border-slate-200 px-2 py-0.5 text-xs text-slate-600 focus:outline-none focus:ring-1 focus:ring-hub-gold">
                                        <option value="default">Couleur normale</option>
                                        <option value="muted">Couleur discrète</option>
                                        <option value="accent">Couleur accent</option>
                                    </select>

                                    <select x-show="['text','quote'].includes(block.type)" x-model="block.spacing" @click.stop
                                            class="rounded border border-slate-200 px-2 py-0.5 text-xs text-slate-600 focus:outline-none focus:ring-1 focus:ring-hub-gold">
                                        <option value="compact">Compact</option>
                                        <option value="normal">Normal</option>
                                        <option value="relaxed">Large</option>
                                    </select>

                                    <div class="flex-1"></div>

                                    <button type="button" :disabled="index === 0" @click.stop="moveUp(block.id)"
                                            class="rounded border border-slate-200 px-2 py-0.5 text-xs text-slate-500 hover:bg-slate-100 disabled:opacity-30">↑</button>
                                    <button type="button" :disabled="index === blocks.length - 1" @click.stop="moveDown(block.id)"
                                            class="rounded border border-slate-200 px-2 py-0.5 text-xs text-slate-500 hover:bg-slate-100 disabled:opacity-30">↓</button>
                                    <button type="button" @click.stop="removeBlock(block.id)"
                                            class="rounded border border-red-200 px-2 py-0.5 text-xs text-red-600 hover:bg-red-50">✕</button>
                                </div>

                                <div class="px-4 py-3">
                                    <textarea x-show="block.type === 'heading'" x-model="block.text" rows="2"
                                              placeholder="Titre..." @click.stop
                                              @input="handleSlashInput(block, index, $event)"
                                              @keydown="onSlashKeydown(block, index, $event)"
                                              :style="`font-size:${headingFontSize(block.level||2)}px;font-weight:700;text-align:${block.align||'left'};`"
                                              :class="`${editorToneClass(block.tone)} ${editorWeightClass(block.weight)} ${block.italic ? 'italic' : ''}`"
                                              class="w-full resize-none border-0 bg-transparent p-0 placeholder:text-slate-300 focus:outline-none focus:ring-0"></textarea>

                                    <textarea x-show="block.type === 'text'" x-model="block.text" rows="6"
                                              placeholder="Écris ton paragraphe ici..." @click.stop
                                              @input="handleSlashInput(block, index, $event)"
                                              @keydown="onSlashKeydown(block, index, $event)"
                                              :style="`text-align:${block.align||'left'};`"
                                              :class="`${editorToneClass(block.tone)} ${editorWeightClass(block.weight)} ${editorSpacingClass(block.spacing)} ${block.italic ? 'italic' : ''}`"
                                              class="w-full resize-none border-0 bg-transparent p-0 placeholder:text-slate-300 focus:outline-none focus:ring-0"></textarea>

                                    <div x-show="block.type === 'quote'" class="border-l-4 border-hub-gold pl-4">
                                        <textarea x-model="block.text" rows="3" placeholder="Citation..." @click.stop
                                                  @input="handleSlashInput(block, index, $event)"
                                                  @keydown="onSlashKeydown(block, index, $event)"
                                                  :style="`text-align:${block.align||'left'};`"
                                                  :class="`${editorToneClass(block.tone)} ${editorWeightClass(block.weight)} ${editorSpacingClass(block.spacing)} ${block.italic ? 'italic' : ''}`"
                                                  class="w-full resize-none border-0 bg-transparent p-0 italic placeholder:text-slate-300 focus:outline-none focus:ring-0"></textarea>
                                    </div>

                                    <div x-show="block.type === 'columns'" class="space-y-3">
                                        <div class="grid gap-3" :class="Number(block.columns) === 3 ? 'md:grid-cols-3' : 'md:grid-cols-2'">
                                            <textarea x-model="block.col1" rows="6" placeholder="Colonne 1..." @click.stop class="w-full rounded border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 placeholder:text-slate-300 focus:outline-none focus:ring-2 focus:ring-hub-gold"></textarea>
                                            <textarea x-model="block.col2" rows="6" placeholder="Colonne 2..." @click.stop class="w-full rounded border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 placeholder:text-slate-300 focus:outline-none focus:ring-2 focus:ring-hub-gold"></textarea>
                                            <textarea x-show="Number(block.columns) === 3" x-model="block.col3" rows="6" placeholder="Colonne 3..." @click.stop class="w-full rounded border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 placeholder:text-slate-300 focus:outline-none focus:ring-2 focus:ring-hub-gold"></textarea>
                                        </div>
                                    </div>

                                    <div x-show="slashMenu.open && slashMenu.blockId === block.id"
                                         x-cloak
                                         class="absolute right-2 top-12 z-20 w-56 rounded-xl border border-slate-200 bg-white p-2 shadow-lg">
                                        <div class="mb-2 flex items-center justify-between">
                                            <span class="text-[11px] uppercase tracking-wide text-slate-400">Commandes rapides</span>
                                            <button type="button" @click.stop="closeSlashMenu()" class="text-[11px] text-slate-400 hover:text-slate-600">✕</button>
                                        </div>
                                        <template x-for="(command, commandIndex) in filteredSlashCommands" :key="`slash-${command.id}`">
                                            <button type="button" @click="applySlashCommand(block, index, command)"
                                                    class="flex w-full items-center justify-between rounded-lg px-2 py-1.5 text-left text-xs transition"
                                                    :class="slashMenu.selectedIndex === commandIndex ? 'bg-slate-900 text-white' : 'text-slate-700 hover:bg-slate-100'">
                                                <span x-text="command.label"></span>
                                                <span class="text-[11px] opacity-70" x-text="command.hint"></span>
                                            </button>
                                        </template>
                                    </div>

                                    <div x-show="block.type === 'divider'" class="py-1">
                                        <hr class="border-slate-300">
                                    </div>

                                    <div x-show="block.type === 'image'">
                                        <div class="mt-1 rounded-xl border border-slate-200 bg-slate-50 p-3">
                                            <div class="mb-3 inline-flex rounded-lg border border-slate-300 bg-white p-1 text-xs">
                                                <button type="button" @click="block.imageInputMode = 'url'; block.photoId = null"
                                                        class="rounded px-3 py-1.5"
                                                        :class="block.imageInputMode === 'url' ? 'bg-slate-900 text-white' : 'text-slate-600 hover:bg-slate-100'">
                                                    URL
                                                </button>
                                                <button type="button" @click="block.imageInputMode = 'drive'; block.photoId = null"
                                                        class="rounded px-3 py-1.5"
                                                        :class="block.imageInputMode === 'drive' ? 'bg-slate-900 text-white' : 'text-slate-600 hover:bg-slate-100'">
                                                    Parcourir
                                                </button>
                                            </div>

                                            <input x-show="block.imageInputMode === 'url'" x-model="block.url" @input="setImageUrl(block)" type="url"
                                                   placeholder="https://..."
                                                   class="w-full rounded border border-slate-300 px-3 py-2 text-sm text-slate-700 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-hub-gold">

                                            <div x-show="block.imageInputMode === 'drive'" class="rounded-lg border border-dashed border-slate-300 bg-white px-4 py-4">
                                                <button type="button" @click="openDriveBrowserForBlock(block)"
                                                        class="inline-flex items-center rounded border border-slate-300 bg-slate-50 px-3 py-2 text-xs font-medium text-slate-700 hover:bg-slate-100">
                                                    Ouvrir Google Drive
                                                </button>
                                                <p class="mt-2 text-xs text-slate-500">Choisis une image depuis ton Drive connecté.</p>
                                            </div>
                                        </div>
                                        <div x-show="imagePreviewForBlock(block)" class="mt-3 flex justify-center">
                                            <img :src="imagePreviewForBlock(block)?.url" :alt="imagePreviewForBlock(block)?.label"
                                                 :class="imageSizeClass(block.size||'medium')"
                                                 class="rounded-xl object-cover shadow-md">
                                        </div>
                                        <input x-show="imagePreviewForBlock(block)" x-model="block.caption" type="text"
                                               placeholder="Légende (optionnelle)..." @click.stop
                                               class="mt-2 w-full rounded border border-slate-200 px-3 py-1.5 text-sm text-slate-600 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-hub-gold">
                                        <div class="mt-2 flex justify-end">
                                            <button type="button" @click="clearImageSource(block)" class="rounded border border-slate-300 px-2 py-1 text-xs text-slate-600 hover:bg-slate-100">
                                                Effacer l'image
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-1 flex items-center py-1">
                                <div x-show="addPickerAt !== index + 1" class="flex w-full items-center gap-2">
                                    <div class="h-px flex-1 bg-slate-200"></div>
                                    <button type="button" @click="addPickerAt = index + 1"
                                            class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full border border-slate-300 bg-white text-xs text-slate-500 transition hover:border-hub-gold hover:bg-hub-gold hover:text-hub-bg">+</button>
                                    <div class="h-px flex-1 bg-slate-200"></div>
                                </div>
                                <div x-show="addPickerAt === index + 1"
                                     class="flex w-full flex-wrap items-center gap-2 rounded-xl border border-hub-gold/50 bg-hub-gold/5 px-3 py-2">
                                    <span class="text-xs font-semibold text-slate-600">Insérer :</span>
                                    <button type="button" @click="addBlockAt(addPickerAt, 'heading')" class="rounded border border-slate-300 bg-white px-3 py-1 text-xs text-slate-700 hover:bg-slate-100">Titre</button>
                                    <button type="button" @click="addBlockAt(addPickerAt, 'text')" class="rounded border border-slate-300 bg-white px-3 py-1 text-xs text-slate-700 hover:bg-slate-100">Texte</button>
                                    <button type="button" @click="addBlockAt(addPickerAt, 'columns')" class="rounded border border-slate-300 bg-white px-3 py-1 text-xs text-slate-700 hover:bg-slate-100">Colonnes</button>
                                    <button type="button" @click="addBlockAt(addPickerAt, 'image')" class="rounded border border-slate-300 bg-white px-3 py-1 text-xs text-slate-700 hover:bg-slate-100">Image</button>
                                    <button type="button" @click="addBlockAt(addPickerAt, 'divider')" class="rounded border border-slate-300 bg-white px-3 py-1 text-xs text-slate-700 hover:bg-slate-100">Séparateur</button>
                                    <button type="button" @click="addBlockAt(addPickerAt, 'quote')" class="rounded border border-slate-300 bg-white px-3 py-1 text-xs text-slate-700 hover:bg-slate-100">Citation</button>
                                    <button type="button" @click="addPickerAt = null" class="ml-auto rounded px-2 py-1 text-xs text-slate-400 hover:text-slate-600">✕</button>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                <div class="mb-3">
                    <h2 class="text-sm font-bold text-slate-800">Images mises en avant</h2>
                    <p class="text-xs text-slate-500">Elles servent à la carte du blog et à l’en-tête public.</p>
                </div>
                <input type="file" name="featured_images[]" accept="image/*" multiple
                       class="block w-full rounded border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 file:mr-3 file:rounded file:border-0 file:bg-hub-gold file:px-3 file:py-2 file:text-sm file:font-medium file:text-hub-bg" />

                @if($featuredPhotos->count())
                    <div class="mt-4 grid gap-3 sm:grid-cols-2 xl:grid-cols-3">
                        @foreach($featuredPhotos as $photo)
                            <div class="overflow-hidden rounded-xl border border-slate-200 bg-white">
                                <img src="{{ $article->resolvePhotoUrl($photo) }}" alt="Image mise en avant" class="h-40 w-full object-cover" />
                                <div class="flex items-center justify-end p-3">
                                    <form method="POST" action="{{ route('admin.blog.images.destroy', [$article, $photo]) }}" onsubmit="return confirm('Supprimer cette image ?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="rounded border border-red-300 px-2 py-1 text-xs text-red-700 hover:bg-red-50">Supprimer</button>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="grid gap-4 md:grid-cols-2">
                <div>
                    <label for="statut" class="mb-1 block text-sm font-medium text-slate-700">Statut</label>
                    <select id="statut" name="statut" x-ref="statusInput" class="w-full rounded border border-slate-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-hub-gold">
                        @php $statut = old('statut', $article?->statut ?? 'brouillon'); @endphp
                        <option value="brouillon" @selected($statut === 'brouillon')>Brouillon</option>
                        <option value="publie" @selected($statut === 'publie')>Publié</option>
                    </select>
                </div>

                <div>
                    <label for="date_publication" class="mb-1 block text-sm font-medium text-slate-700">Date de publication</label>
                    <input id="date_publication" name="date_publication" type="datetime-local" x-ref="dateInput"
                           value="{{ old('date_publication', optional($article?->date_publication)->format('Y-m-d\TH:i') ?? now()->format('Y-m-d\TH:i')) }}"
                           class="w-full rounded border border-slate-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-hub-gold" />
                </div>
            </div>

            <div class="flex items-center gap-3 pt-2">
                <button type="submit" class="rounded bg-hub-gold px-4 py-2 text-hub-bg hover:opacity-90">{{ $submitLabel }}</button>
                <a href="{{ route('admin.blog.index') }}" class="rounded border border-slate-300 px-4 py-2 text-slate-700 hover:bg-slate-100">Annuler</a>
            </div>
        </div>

        <aside :class="previewCollapsed && isDesktop ? 'xl:col-span-1' : 'xl:col-span-5'" class="xl:col-span-5" x-show="activePanel === 'preview' || isDesktop">
            <div class="xl:sticky xl:top-6">
                <div class="relative overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <button type="button" x-show="isDesktop"
                            @click="previewCollapsed = !previewCollapsed"
                            class="absolute left-0 top-6 z-10 hidden -translate-x-1/2 rounded-full border border-slate-300 bg-white p-2 text-xs text-slate-600 shadow-sm transition hover:bg-slate-100 xl:flex"
                            :title="previewCollapsed ? 'Afficher la prévisualisation' : 'Masquer la prévisualisation'">
                        <span x-text="previewCollapsed ? '>' : '<'"></span>
                    </button>

                    <div x-show="previewCollapsed && isDesktop" class="p-4 text-center">
                        <div class="rounded-xl border border-dashed border-slate-300 bg-slate-50 px-3 py-5">
                            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Preview</p>
                            <p class="mt-1 text-xs text-slate-400">Clique sur &gt; pour ouvrir</p>
                        </div>
                    </div>

                    <div x-show="!previewCollapsed || !isDesktop">
                    <div class="border-b border-slate-200 bg-slate-50 px-4 py-3">
                        <div class="flex items-center justify-between gap-3">
                            <div>
                                <h2 class="text-sm font-bold text-slate-800">Prévisualisation</h2>
                                <p class="text-xs text-slate-500">Même ordre de lecture que la page publique, sans te forcer à descendre sous l’éditeur.</p>
                            </div>
                            <span class="rounded-full px-2.5 py-1 text-[11px] font-semibold"
                                  :class="$refs.statusInput?.value === 'publie' ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700'"
                                  x-text="$refs.statusInput?.value === 'publie' ? 'Publié' : 'Brouillon'"></span>
                        </div>
                    </div>

                    <div class="max-h-[calc(100vh-8rem)] overflow-y-auto p-5">
                        @if($featuredPhotos->count())
                            <div class="mb-5 grid gap-3 {{ $featuredPhotos->count() > 1 ? 'sm:grid-cols-2' : '' }}">
                                @foreach($featuredPhotos as $photo)
                                    <img src="{{ $article->resolvePhotoUrl($photo) }}" alt="Image mise en avant" class="h-40 w-full rounded-2xl object-cover" />
                                @endforeach
                            </div>
                        @endif

                        <div class="mb-2 flex items-center gap-2 text-xs text-slate-500">
                            <span x-text="$refs.dateInput?.value ? formatPreviewDate($refs.dateInput.value) : 'Date non définie'"></span>
                        </div>

                        <h1 class="mb-5 text-3xl font-bold leading-tight text-slate-900" x-text="articleTitle || 'Titre de l\'article'"></h1>

                        <div x-show="!blocks.length" class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 px-4 py-10 text-center text-sm text-slate-500">
                            La prévisualisation apparaîtra ici dès qu’un premier bloc sera ajouté.
                        </div>

                        <div x-show="blocks.length" class="space-y-3">
                            <template x-for="block in blocks" :key="`preview-${block.id}`">
                                <div>
                                    <template x-if="block.type === 'heading'">
                                        <div :class="`${previewHeadingClass(block.level, block.align)} ${previewToneClass(block.tone)} ${previewWeightClass(block.weight)} ${block.italic ? 'italic' : ''}`" x-html="renderMarkdownInline(block.text || 'Titre sans texte')"></div>
                                    </template>

                                    <template x-if="block.type === 'text'">
                                        <div :class="`${previewTextClass(block.align)} ${previewToneClass(block.tone)} ${previewWeightClass(block.weight)} ${previewSpacingClass(block.spacing)} ${block.italic ? 'italic' : ''}`" x-html="renderMarkdown(block.text || 'Paragraphe vide')"></div>
                                    </template>

                                    <template x-if="block.type === 'quote'">
                                        <blockquote class="my-4 border-l-4 border-hub-gold py-1 pl-5 italic" :class="`${previewAlignClass(block.align)} ${previewToneClass(block.tone)} ${previewWeightClass(block.weight)} ${previewSpacingClass(block.spacing)}`">
                                            <span x-html="renderMarkdown(block.text || 'Citation vide')"></span>
                                        </blockquote>
                                    </template>

                                    <template x-if="block.type === 'columns'">
                                        <div class="my-4 grid gap-3" :class="Number(block.columns) === 3 ? 'md:grid-cols-3' : 'md:grid-cols-2'">
                                            <div class="rounded-xl border border-slate-200 bg-slate-50 p-3 text-sm text-slate-700" x-html="renderMarkdown(block.col1 || 'Colonne 1 vide')"></div>
                                            <div class="rounded-xl border border-slate-200 bg-slate-50 p-3 text-sm text-slate-700" x-html="renderMarkdown(block.col2 || 'Colonne 2 vide')"></div>
                                            <div x-show="Number(block.columns) === 3" class="rounded-xl border border-slate-200 bg-slate-50 p-3 text-sm text-slate-700" x-html="renderMarkdown(block.col3 || 'Colonne 3 vide')"></div>
                                        </div>
                                    </template>

                                    <template x-if="block.type === 'divider'">
                                        <hr class="my-6 border-slate-200">
                                    </template>

                                    <template x-if="block.type === 'image'">
                                        <div class="my-6">
                                            <template x-if="imagePreviewForBlock(block)">
                                                <figure :class="previewImageWrapperClass(block.size)">
                                                    <img :src="imagePreviewForBlock(block)?.url" :alt="block.caption || articleTitle || 'Image article'" class="w-full rounded-2xl object-cover shadow-sm">
                                                    <figcaption x-show="block.caption" class="mt-2 text-center text-xs text-slate-500" x-html="renderMarkdownInline(block.caption)"></figcaption>
                                                </figure>
                                            </template>
                                            <template x-if="!imagePreviewForBlock(block)">
                                                <div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 px-4 py-8 text-center text-sm text-slate-500">
                                                    Image inline non sélectionnée.
                                                </div>
                                            </template>
                                        </div>
                                    </template>
                                </div>
                            </template>
                        </div>
                    </div>
                    </div>
                </div>
            </div>
        </aside>
    </div>

    <div x-show="driveBrowser.open" x-cloak class="fixed inset-0 z-[70] flex items-center justify-center bg-slate-900/60 p-4" @keydown.escape.window="closeDriveBrowser()">
        <div class="w-full max-w-6xl rounded-2xl border border-slate-200 bg-white p-4 shadow-2xl">
            <div class="flex items-center justify-between gap-3 border-b border-slate-200 pb-3">
                <div>
                    <h3 class="text-base font-bold text-slate-900">Google Drive</h3>
                    <p class="text-xs text-slate-500">Parcours les dossiers puis clique une image pour l'utiliser dans le bloc.</p>
                </div>
                <button type="button" @click="closeDriveBrowser()" class="rounded border border-slate-300 bg-white px-3 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-100">Fermer</button>
            </div>

            <div class="mt-3 flex flex-wrap gap-2">
                <template x-for="(crumb, idx) in driveBrowser.breadcrumbs" :key="`crumb-${crumb.id}-${idx}`">
                    <button type="button" @click="goToDriveBreadcrumb(idx)" class="rounded border border-slate-300 bg-slate-50 px-2 py-1 text-xs text-slate-700 hover:bg-slate-100" x-text="crumb.name || 'Dossier'"></button>
                </template>
            </div>

            <div x-show="driveBrowser.error" class="mt-3 rounded border border-red-300 bg-red-50 px-3 py-2 text-xs text-red-700" x-text="driveBrowser.error"></div>
            <div x-show="driveBrowser.loading" class="mt-3 rounded border border-slate-200 bg-slate-50 px-3 py-2 text-xs text-slate-600">Chargement...</div>

            <div class="mt-4 grid grid-cols-1 gap-4 lg:grid-cols-12" x-show="!driveBrowser.loading">
                <div class="rounded-xl border border-slate-200 bg-slate-50 p-3 lg:col-span-4">
                    <div class="mb-2 text-xs font-semibold uppercase tracking-wide text-slate-600">Dossiers</div>
                    <template x-if="!driveBrowser.folders.length">
                        <div class="rounded border border-dashed border-slate-300 px-3 py-5 text-center text-xs text-slate-500">Aucun sous-dossier.</div>
                    </template>
                    <div class="max-h-[50vh] space-y-2 overflow-y-auto">
                        <template x-for="folder in driveBrowser.folders" :key="folder.id">
                            <button type="button" @click="openDriveSubFolder(folder)" class="flex w-full items-center gap-2 rounded border border-slate-200 bg-white px-3 py-2 text-left text-sm text-slate-700 hover:border-hub-gold hover:bg-hub-gold/10">
                                <span>📁</span>
                                <span class="truncate" x-text="folder.name"></span>
                            </button>
                        </template>
                    </div>
                </div>

                <div class="rounded-xl border border-slate-200 bg-slate-50 p-3 lg:col-span-8">
                    <div class="mb-2 text-xs font-semibold uppercase tracking-wide text-slate-600">Images</div>
                    <template x-if="!driveBrowser.images.length">
                        <div class="rounded border border-dashed border-slate-300 px-3 py-5 text-center text-xs text-slate-500">Aucune image dans ce dossier.</div>
                    </template>
                    <div class="grid max-h-[60vh] grid-cols-2 gap-2 overflow-y-auto pr-1 sm:grid-cols-3 lg:grid-cols-4">
                        <template x-for="image in driveBrowser.images" :key="image.id">
                            <button type="button" @click="selectDriveImage(image)" class="rounded border border-slate-200 bg-white p-2 text-left hover:border-hub-gold hover:bg-hub-gold/10">
                                <img :src="image.thumbnail_url || image.direct_url" :alt="image.name" class="h-24 w-full rounded object-cover" />
                                <div class="mt-1 truncate text-[11px] text-slate-700" x-text="image.name"></div>
                            </button>
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

<script>
    function blogArticleForm(initialSlugs, storeSlugUrl, initialSlugValue, initialLayout, inlineLibrary, initialTitle, driveConfig) {
        return {
            availableSlugs: Array.isArray(initialSlugs) ? initialSlugs : [],
            slugInputValue: initialSlugValue || '',
            slugFeedback: '',
            slugFeedbackError: false,
            articleTitle: initialTitle || '',
            activePanel: 'editor',
            isDesktop: window.innerWidth >= 1280,
            previewCollapsed: false,
            slashMenu: {
                open: false,
                blockId: null,
                blockIndex: -1,
                query: '',
                selectedIndex: 0,
            },
            slashCommands: [
                { id: 'h2', label: 'Titre H2', hint: '/h2', type: 'heading', level: 2, keywords: ['titre', 'heading'] },
                { id: 'h3', label: 'Titre H3', hint: '/h3', type: 'heading', level: 3, keywords: ['titre', 'heading'] },
                { id: 'h4', label: 'Titre H4', hint: '/h4', type: 'heading', level: 4, keywords: ['titre', 'heading'] },
                { id: 'text', label: 'Paragraphe', hint: '/text', type: 'text', keywords: ['markdown', 'md', 'paragraphe'] },
                { id: 'quote', label: 'Citation', hint: '/quote', type: 'quote', keywords: ['citation', 'blockquote'] },
                { id: 'columns-2', label: 'Colonnes x2', hint: '/column', type: 'columns', columns: 2, keywords: ['column', 'columns', 'colonne', 'colonnes', 'col2'] },
                { id: 'columns-3', label: 'Colonnes x3', hint: '/columns3', type: 'columns', columns: 3, keywords: ['column3', 'columns3', 'col3', 'colonnes3'] },
                { id: 'divider', label: 'Séparateur', hint: '/divider', type: 'divider', keywords: ['ligne', 'separateur', 'hr'] },
                { id: 'image', label: 'Image', hint: '/image', type: 'image', keywords: ['media', 'photo'] },
                { id: 'add-text', label: 'Ajouter bloc texte dessous', hint: '/+text', action: 'add-below', insertType: 'text', keywords: ['ajouter', 'dessous'] },
                { id: 'add-image', label: 'Ajouter bloc image dessous', hint: '/+image', action: 'add-below', insertType: 'image', keywords: ['ajouter', 'dessous'] },
                { id: 'add-columns', label: 'Ajouter bloc colonnes dessous', hint: '/+column', action: 'add-below', insertType: 'columns', keywords: ['ajouter', 'column', 'colonne'] },
            ],
            blocks: Array.isArray(initialLayout?.blocks) ? initialLayout.blocks.map((block, index) => ({
                id: block.id || `block-${Date.now()}-${index}`,
                type: block.type || 'text',
                text: block.text || '',
                align: block.align || 'left',
                level: block.level ? Number(block.level) : 2,
                photoId: block.photoId ? Number(block.photoId) : null,
                url: block.url || '',
                imageInputMode: block.imageInputMode === 'file'
                    ? 'drive'
                    : (['url', 'drive'].includes(block.imageInputMode) ? block.imageInputMode : 'url'),
                size: block.size || 'medium',
                caption: block.caption || '',
                weight: block.weight || 'normal',
                tone: block.tone || 'default',
                spacing: block.spacing || 'normal',
                columns: [2, 3].includes(Number(block.columns)) ? Number(block.columns) : 2,
                col1: block.col1 || '',
                col2: block.col2 || '',
                col3: block.col3 || '',
                italic: block.italic || false,
            })) : [],
            selectedBlockId: null,
            addPickerAt: null,
            inlineLibrary: Array.isArray(inlineLibrary) ? inlineLibrary : [],
            driveBrowser: {
                open: false,
                loading: false,
                error: '',
                currentFolderId: '',
                folders: [],
                images: [],
                breadcrumbs: [],
                selectedBlockId: null,
            },
            googleDrive: {
                browseUrl: driveConfig?.browseUrl || '',
                folderId: driveConfig?.folderId || '',
                folderUrl: driveConfig?.folderUrl || '',
            },

            init() {
                const syncViewport = () => {
                    this.isDesktop = window.innerWidth >= 1280;
                    if (!this.isDesktop) {
                        this.previewCollapsed = false;
                    }
                };

                syncViewport();
                window.addEventListener('resize', syncViewport);
            },

            // ─── Slug helpers ───────────────────────────────────────────────
            normalize(value) {
                return (value || '')
                    .toString()
                    .toLowerCase()
                    .normalize('NFD')
                    .replace(/[\u0300-\u036f]/g, '')
                    .replace(/[^a-z0-9]+/g, '-')
                    .replace(/^-+|-+$/g, '')
                    .replace(/-{2,}/g, '-');
            },
            handleSlugInput() {
                this.slugInputValue = this.normalize(this.slugInputValue);
                this.$refs.slugInput.value = this.slugInputValue;
                this.slugFeedback = '';
            },
            similarityScore(candidate, query) {
                if (!candidate || !query) return 0;
                if (candidate === query) return 100;
                if (candidate.startsWith(query)) return 80;
                if (candidate.includes(query)) return 60;
                if (query.startsWith(candidate)) return 40;
                let score = 0;
                for (const part of query.split('-')) {
                    if (part && candidate.includes(part)) score += 10;
                }
                return score;
            },
            useExistingSlug(slug) {
                this.slugInputValue = slug;
                this.$refs.slugInput.value = slug;
                this.slugFeedback = '';
                this.slugFeedbackError = false;
            },

            // ─── Block management ───────────────────────────────────────────
            addBlockAt(position, type) {
                const defaults = {
                    heading: { text: 'Titre', level: 2, align: 'left', weight: 'bold', tone: 'default', spacing: 'normal', italic: false },
                    text:    { text: '', align: 'left', weight: 'normal', tone: 'default', spacing: 'normal', italic: false },
                    quote:   { text: '', align: 'left', weight: 'medium', tone: 'muted', spacing: 'normal', italic: false },
                    divider: {},
                    image:   { photoId: null, url: '', imageInputMode: 'url', size: 'medium', caption: '', weight: 'normal', tone: 'default', spacing: 'normal', italic: false },
                    columns: { columns: 2, col1: '', col2: '', col3: '', weight: 'normal', tone: 'default', spacing: 'normal', italic: false },
                };
                const block = {
                    id: `block-${Date.now()}-${Math.random().toString(36).slice(2, 7)}`,
                    type,
                    text: '',
                    align: 'left',
                    level: 2,
                    photoId: null,
                    url: '',
                    imageInputMode: 'url',
                    size: 'medium',
                    caption: '',
                    weight: 'normal',
                    tone: 'default',
                    spacing: 'normal',
                    columns: 2,
                    col1: '',
                    col2: '',
                    col3: '',
                    italic:  false,
                    ...(defaults[type] || {}),
                };
                const safePosition = Number.isInteger(position) && position >= 0 ? Math.min(position, this.blocks.length) : this.blocks.length;
                this.blocks.splice(safePosition, 0, block);
                this.selectedBlockId = block.id;
                this.addPickerAt = null;
            },
            appendBlock(type) {
                this.addBlockAt(this.blocks.length, type);
            },
            addImageBlock(photoId, size) {
                this.addBlockAt(this.blocks.length, 'image');
                const last = this.blocks[this.blocks.length - 1];
                if (last) {
                    last.photoId = Number(photoId);
                    last.url = '';
                    last.imageInputMode = 'url';
                    last.size = size || 'medium';
                }
            },
            removeBlock(id) {
                const idx = this.blocks.findIndex((b) => b.id === id);
                if (idx !== -1) this.blocks.splice(idx, 1);
                if (this.selectedBlockId === id) {
                    this.selectedBlockId = this.blocks[0]?.id || null;
                }
            },
            moveUp(id) {
                const i = this.blocks.findIndex((b) => b.id === id);
                if (i > 0) {
                    const [block] = this.blocks.splice(i, 1);
                    this.blocks.splice(i - 1, 0, block);
                }
            },
            moveDown(id) {
                const i = this.blocks.findIndex((b) => b.id === id);
                if (i >= 0 && i < this.blocks.length - 1) {
                    const [block] = this.blocks.splice(i, 1);
                    this.blocks.splice(i + 1, 0, block);
                }
            },

            // ─── Block display helpers ──────────────────────────────────────
            blockTypeLabel(type) {
                const labels = { heading: 'Titre', text: 'Texte', image: 'Image', divider: 'Séparateur', quote: 'Citation', columns: 'Colonnes' };
                return labels[type] || type;
            },
            headingFontSize(level) {
                return { 2: 28, 3: 22, 4: 18 }[Number(level)] || 24;
            },
            imageForBlock(block) {
                if (!block.photoId) return null;
                return this.inlineLibrary.find((p) => Number(p.id) === Number(block.photoId)) || null;
            },
            imagePreviewForBlock(block) {
                const fromLibrary = this.imageForBlock(block);
                if (fromLibrary) return fromLibrary;

                if (this.isSafeImageUrl(block.url)) {
                    return {
                        id: `url-${block.id}`,
                        url: block.url,
                        label: 'Image personnalisée',
                    };
                }

                return null;
            },
            isSafeImageUrl(url) {
                const value = (url || '').trim();
                if (!value) return false;

                if (value.startsWith('data:image/')) {
                    return true;
                }

                try {
                    const parsed = new URL(value);
                    return parsed.protocol === 'http:' || parsed.protocol === 'https:';
                } catch {
                    return false;
                }
            },
            setImageUrl(block) {
                if (!block) return;
                if (block.url && this.isSafeImageUrl(block.url)) {
                    block.photoId = null;
                    block.imageInputMode = 'url';
                }
            },
            clearImageSource(block) {
                if (!block) return;
                block.photoId = null;
                block.url = '';
                block.imageInputMode = 'url';
            },
            extractGoogleDriveFolderId(value) {
                const source = (value || '').trim();
                if (!source) return '';
                const fromUrl = source.match(/\/folders\/([a-zA-Z0-9_-]+)/);
                if (fromUrl?.[1]) return fromUrl[1];
                if (/^[a-zA-Z0-9_-]{20,}$/.test(source)) return source;
                return '';
            },
            async openDriveBrowserForBlock(block) {
                if (!block) return;
                const rootFolderId = this.extractGoogleDriveFolderId(this.googleDrive.folderId || this.googleDrive.folderUrl);
                if (!this.googleDrive.browseUrl || !rootFolderId) {
                    this.driveBrowser.error = 'Configuration Drive manquante.';
                    this.driveBrowser.open = true;
                    return;
                }

                this.driveBrowser.selectedBlockId = block.id;
                this.driveBrowser.open = true;
                this.driveBrowser.error = '';
                await this.loadDriveFolder(rootFolderId, [{ id: rootFolderId, name: 'Racine Drive' }]);
            },
            closeDriveBrowser() {
                this.driveBrowser.open = false;
                this.driveBrowser.error = '';
            },
            async loadDriveFolder(folderId, breadcrumbs = null) {
                if (!this.googleDrive.browseUrl || !folderId) return;

                this.driveBrowser.loading = true;
                this.driveBrowser.error = '';

                try {
                    const url = `${this.googleDrive.browseUrl}?folder_id=${encodeURIComponent(folderId)}`;
                    const response = await fetch(url, { headers: { Accept: 'application/json' } });
                    const payload = await response.json();

                    if (!response.ok || !payload?.ok) {
                        this.driveBrowser.error = payload?.message || 'Impossible de lire ce dossier Drive.';
                        return;
                    }

                    this.driveBrowser.currentFolderId = payload.folder_id || folderId;
                    this.driveBrowser.folders = Array.isArray(payload.folders) ? payload.folders : [];
                    this.driveBrowser.images = Array.isArray(payload.images) ? payload.images : [];
                    if (Array.isArray(breadcrumbs)) {
                        this.driveBrowser.breadcrumbs = breadcrumbs;
                    }
                } catch {
                    this.driveBrowser.error = 'Erreur réseau en lisant Google Drive.';
                } finally {
                    this.driveBrowser.loading = false;
                }
            },
            async openDriveSubFolder(folder) {
                if (!folder?.id) return;
                const crumbs = Array.isArray(this.driveBrowser.breadcrumbs)
                    ? [...this.driveBrowser.breadcrumbs, { id: folder.id, name: folder.name || 'Dossier' }]
                    : [{ id: folder.id, name: folder.name || 'Dossier' }];
                await this.loadDriveFolder(folder.id, crumbs);
            },
            async goToDriveBreadcrumb(index) {
                const crumbs = this.driveBrowser.breadcrumbs || [];
                if (index < 0 || index >= crumbs.length) return;
                const target = crumbs[index];
                await this.loadDriveFolder(target.id, crumbs.slice(0, index + 1));
            },
            selectDriveImage(image) {
                const blockId = this.driveBrowser.selectedBlockId;
                const block = this.blocks.find((item) => item.id === blockId);
                if (!block) return;

                const source = image?.direct_url || image?.background_url || image?.thumbnail_url || '';
                if (!source || !this.isSafeImageUrl(source)) return;

                block.photoId = null;
                block.url = source;
                block.imageInputMode = 'drive';
                this.closeDriveBrowser();
            },
            imageSizeClass(size) {
                return { small: 'max-w-xs', medium: 'max-w-md', large: 'max-w-2xl', full: 'w-full' }[size] || 'max-w-md';
            },
            editorWeightClass(weight) {
                return {
                    normal: 'font-normal',
                    medium: 'font-medium',
                    bold: 'font-bold',
                }[weight || 'normal'] || 'font-normal';
            },
            editorToneClass(tone) {
                return {
                    default: 'text-slate-700',
                    muted: 'text-slate-500',
                    accent: 'text-amber-700',
                }[tone || 'default'] || 'text-slate-700';
            },
            editorSpacingClass(spacing) {
                return {
                    compact: 'leading-snug',
                    normal: 'leading-relaxed',
                    relaxed: 'leading-8',
                }[spacing || 'normal'] || 'leading-relaxed';
            },
            previewAlignClass(align) {
                return { center: 'text-center', right: 'text-right', left: 'text-left' }[align || 'left'] || 'text-left';
            },
            previewTextClass(align) {
                return `leading-relaxed text-slate-600 ${this.previewAlignClass(align)}`;
            },
            previewToneClass(tone) {
                return {
                    default: 'text-slate-700',
                    muted: 'text-slate-500',
                    accent: 'text-amber-700',
                }[tone || 'default'] || 'text-slate-700';
            },
            previewWeightClass(weight) {
                return {
                    normal: 'font-normal',
                    medium: 'font-medium',
                    bold: 'font-bold',
                }[weight || 'normal'] || 'font-normal';
            },
            previewSpacingClass(spacing) {
                return {
                    compact: 'leading-snug',
                    normal: 'leading-relaxed',
                    relaxed: 'leading-8',
                }[spacing || 'normal'] || 'leading-relaxed';
            },
            previewHeadingClass(level, align) {
                const sizes = {
                    2: 'mb-3 mt-8 text-2xl font-bold',
                    3: 'mb-2 mt-6 text-xl font-bold',
                    4: 'mb-2 mt-5 text-lg font-semibold',
                };
                return `${sizes[Number(level) || 2] || sizes[2]} text-slate-900 ${this.previewAlignClass(align)}`;
            },
            previewImageWrapperClass(size) {
                return {
                    small: 'mx-auto max-w-sm',
                    medium: 'mx-auto max-w-xl',
                    large: 'mx-auto max-w-3xl',
                    full: 'w-full max-w-none',
                }[size || 'medium'] || 'mx-auto max-w-xl';
            },
            formatPreviewDate(value) {
                const date = new Date(value);
                if (Number.isNaN(date.getTime())) {
                    return 'Date non définie';
                }

                return new Intl.DateTimeFormat('fr-FR', {
                    day: '2-digit',
                    month: '2-digit',
                    year: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit',
                }).format(date);
            },
            escapeHtml(value) {
                return (value || '')
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;')
                    .replace(/'/g, '&#039;');
            },
            renderMarkdown(raw) {
                const escaped = this.escapeHtml((raw || '').trim());
                if (!escaped) return '';

                const linked = this.renderMarkdownInline(escaped, true);

                const lines = linked.split(/\r?\n/);
                const chunks = [];
                let inList = false;

                for (const line of lines) {
                    const trimmed = line.trim();

                    if (trimmed.startsWith('- ')) {
                        if (!inList) {
                            chunks.push('<ul class="list-disc pl-5 space-y-1">');
                            inList = true;
                        }
                        chunks.push(`<li>${trimmed.slice(2)}</li>`);
                        continue;
                    }

                    if (inList) {
                        chunks.push('</ul>');
                        inList = false;
                    }

                    if (!trimmed) {
                        chunks.push('<div class="h-2"></div>');
                        continue;
                    }

                    if (trimmed.startsWith('### ')) {
                        chunks.push(`<h4 class="mt-4 mb-2 text-base font-semibold">${trimmed.slice(4)}</h4>`);
                        continue;
                    }

                    if (trimmed.startsWith('## ')) {
                        chunks.push(`<h3 class="mt-5 mb-2 text-lg font-semibold">${trimmed.slice(3)}</h3>`);
                        continue;
                    }

                    if (trimmed.startsWith('# ')) {
                        chunks.push(`<h2 class="mt-6 mb-3 text-xl font-bold">${trimmed.slice(2)}</h2>`);
                        continue;
                    }

                    chunks.push(`<p>${trimmed}</p>`);
                }

                if (inList) {
                    chunks.push('</ul>');
                }

                return chunks.join('');
            },
            renderMarkdownInline(raw, alreadyEscaped = false) {
                const source = alreadyEscaped ? (raw || '') : this.escapeHtml((raw || '').trim());
                if (!source) return '';

                return source
                    .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
                    .replace(/\*(.*?)\*/g, '<em>$1</em>')
                    .replace(/`([^`]+)`/g, '<code class="rounded bg-slate-100 px-1 py-0.5 text-xs">$1</code>')
                    .replace(/\[(.*?)\]\((https?:\/\/[^\s)]+)\)/g, '<a href="$2" target="_blank" rel="noopener" class="text-hub-primary underline">$1</a>')
                    .replace(/\r?\n/g, '<br>');
            },

            // ─── Slash commands ───────────────────────────────────────────
            handleSlashInput(block, index, event) {
                const value = event?.target?.value || '';
                const caret = event?.target?.selectionStart ?? value.length;
                const beforeCaret = value.slice(0, caret);
                const currentLine = beforeCaret.split('\n').pop() || '';
                const trimmedLine = currentLine.trimStart();

                if (!trimmedLine.startsWith('/')) {
                    if (this.slashMenu.blockId === block.id) {
                        this.closeSlashMenu();
                    }
                    return;
                }

                this.slashMenu.open = true;
                this.slashMenu.blockId = block.id;
                this.slashMenu.blockIndex = index;
                this.slashMenu.query = trimmedLine.slice(1).trim().toLowerCase();
                this.slashMenu.selectedIndex = 0;
            },
            onSlashKeydown(block, index, event) {
                if (!this.slashMenu.open || this.slashMenu.blockId !== block.id) return;

                const commands = this.filteredSlashCommands;
                if (!commands.length) return;

                if (event.key === 'ArrowDown') {
                    event.preventDefault();
                    this.slashMenu.selectedIndex = Math.min(this.slashMenu.selectedIndex + 1, commands.length - 1);
                    return;
                }

                if (event.key === 'ArrowUp') {
                    event.preventDefault();
                    this.slashMenu.selectedIndex = Math.max(this.slashMenu.selectedIndex - 1, 0);
                    return;
                }

                if (event.key === 'Enter' || event.key === 'Tab') {
                    event.preventDefault();
                    const selected = commands[this.slashMenu.selectedIndex] || commands[0];
                    this.applySlashCommand(block, index, selected);
                    return;
                }

                if (event.key === 'Escape') {
                    event.preventDefault();
                    this.closeSlashMenu();
                }
            },
            applySlashCommand(block, index, command) {
                if (!command) return;

                if (command.action === 'add-below') {
                    this.addBlockAt(index + 1, command.insertType);
                    this.closeSlashMenu();
                    return;
                }

                block.type = command.type;
                if (command.type === 'heading') {
                    block.level = command.level || 2;
                }
                if (command.type === 'divider') {
                    block.text = '';
                }
                if (command.type === 'image') {
                    block.photoId = block.photoId || null;
                    block.url = block.url || '';
                    block.imageInputMode = ['url', 'drive'].includes(block.imageInputMode) ? block.imageInputMode : 'url';
                    block.size = block.size || 'medium';
                    block.caption = block.caption || '';
                }
                if (command.type === 'columns') {
                    block.columns = [2, 3].includes(Number(command.columns)) ? Number(command.columns) : (block.columns || 2);
                    block.col1 = block.col1 || '';
                    block.col2 = block.col2 || '';
                    block.col3 = block.col3 || '';
                }

                block.text = this.cleanSlashLine(block.text);
                this.closeSlashMenu();
            },
            cleanSlashLine(text) {
                const lines = (text || '').split('\n');
                if (!lines.length) return '';
                const first = lines[0].trim();
                if (first.startsWith('/')) {
                    lines.shift();
                }
                return lines.join('\n').trimStart();
            },
            closeSlashMenu() {
                this.slashMenu.open = false;
                this.slashMenu.blockId = null;
                this.slashMenu.blockIndex = -1;
                this.slashMenu.query = '';
                this.slashMenu.selectedIndex = 0;
            },

            // ─── Slug preset (async) ────────────────────────────────────────
            async addSlugPreset() {
                const value = this.normalizedSlugValue;
                if (!value) {
                    this.slugFeedback = 'Renseigne un slug avant de l\'ajouter.';
                    this.slugFeedbackError = true;
                    return;
                }
                try {
                    const response = await fetch(storeSlugUrl, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                        },
                        body: JSON.stringify({ slug_base: value }),
                    });
                    const data = await response.json();
                    if (!response.ok || !data.success) {
                        this.slugFeedback = data.message || 'Impossible d\'ajouter ce slug.';
                        this.slugFeedbackError = true;
                        return;
                    }
                    if (!this.availableSlugs.includes(data.slug.slug_base)) {
                        this.availableSlugs.push(data.slug.slug_base);
                        this.availableSlugs.sort((a, b) => a.localeCompare(b));
                    }
                    this.useExistingSlug(data.slug.slug_base);
                    this.slugFeedback = data.message || 'Slug ajouté.';
                    this.slugFeedbackError = false;
                } catch {
                    this.slugFeedback = 'Erreur réseau pendant l\'ajout du slug.';
                    this.slugFeedbackError = true;
                }
            },

            // ─── Computed ───────────────────────────────────────────────────
            get normalizedSlugValue() {
                return this.normalize(this.slugInputValue);
            },
            get exactSlugMatch() {
                const query = this.normalizedSlugValue;
                if (!query) return null;
                return this.availableSlugs.find((slug) => this.normalize(slug) === query) || null;
            },
            get similarSlugs() {
                const query = this.normalizedSlugValue;
                if (!query) return [];
                return this.availableSlugs
                    .map((slug) => ({ slug, score: this.similarityScore(this.normalize(slug), query) }))
                    .filter((entry) => entry.score > 0 && this.normalize(entry.slug) !== query)
                    .sort((a, b) => b.score - a.score || a.slug.localeCompare(b.slug))
                    .slice(0, 5)
                    .map((entry) => entry.slug);
            },
            get canAddCurrentSlug() {
                return Boolean(this.normalizedSlugValue) && !this.exactSlugMatch;
            },
            get selectedBlock() {
                return this.blocks.find((block) => block.id === this.selectedBlockId) || null;
            },
            get filteredSlashCommands() {
                const query = (this.slashMenu.query || '').toLowerCase();
                if (!query) return this.slashCommands;
                return this.slashCommands.filter((cmd) => {
                    return cmd.label.toLowerCase().includes(query)
                        || cmd.hint.toLowerCase().includes(query)
                        || (cmd.type && cmd.type.toLowerCase().includes(query))
                        || (Array.isArray(cmd.keywords) && cmd.keywords.some((keyword) => keyword.toLowerCase().includes(query)));
                });
            },
            get serializedLayout() {
                return JSON.stringify({
                    blocks: this.blocks.map((block) => ({
                        id:      block.id,
                        type:    block.type,
                        text:    block.text || '',
                        align:   block.align || 'left',
                        level:   Number(block.level || 2),
                        photoId: block.photoId ? Number(block.photoId) : null,
                        url:     block.url || '',
                        imageInputMode: ['url', 'drive'].includes(block.imageInputMode) ? block.imageInputMode : 'url',
                        size:    block.size || 'medium',
                        caption: block.caption || '',
                        weight:  block.weight || 'normal',
                        tone:    block.tone || 'default',
                        spacing: block.spacing || 'normal',
                        columns: [2, 3].includes(Number(block.columns)) ? Number(block.columns) : 2,
                        col1:    block.col1 || '',
                        col2:    block.col2 || '',
                        col3:    block.col3 || '',
                        italic:  block.italic || false,
                    })),
                });
            },
        };
    }
</script>
