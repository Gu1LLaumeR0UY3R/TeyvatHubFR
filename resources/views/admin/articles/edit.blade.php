{{-- Handles both create (?article = null) and edit ($article set) --}}
<x-admin-layout>
@php
    $isEdit      = isset($article) && $article !== null;
    $actionUrl   = $isEdit ? route('admin.articles.update', $article) : route('admin.articles.store');
    $autosaveUrl = $isEdit ? route('admin.articles.autosave', $article) : '';
    $uploadUrl   = route('admin.upload.image');
    $initialContent = $isEdit && $article->content ? json_encode($article->content) : 'null';
    $currentType    = old('type', $isEdit ? $article->type : 'annonce');
    $currentStatus  = old('status', $isEdit ? $article->status : 'draft');
    $initialChangelog = $isEdit && $article->patchNoteMeta
        ? ($article->patchNoteMeta->changelog ?? ['added' => [], 'fixed' => [], 'removed' => []])
        : ['added' => [], 'fixed' => [], 'removed' => []];
    $initialQuestions = $isEdit && $article->survey
        ? $article->survey->questions->sortBy('position')->map(fn($q) => [
            'id' => $q->id,
            'type' => $q->type,
            'label' => $q->label,
            'options' => $q->options ?? [],
        ])
        : [];
@endphp

<script>
document.addEventListener('alpine:init', function () {
    window._articleEditorConfig = {
        articleId:             {{ $isEdit ? $article->id : 'null' }},
        autosaveUrl:           @json($autosaveUrl),
        uploadUrl:             @json($uploadUrl),
        initialContent:        {!! $initialContent !!},
        initialType:           @json($currentType),
        initialChangelog:      @json($initialChangelog),
        initialQuestions:      @json($initialQuestions),
        initialPinned:         {{ $isEdit && $article->is_pinned ? 'true' : 'false' }},
        initialPinnedUntil:    @json(old('pinned_until',   $isEdit ? $article->pinned_until?->format('Y-m-d\TH:i')   : '')),
        initialScheduledAt:    @json(old('scheduled_at',   $isEdit ? $article->scheduled_at?->format('Y-m-d\TH:i')   : '')),
        initialVersion:        @json(old('version',        $isEdit ? $article->patchNoteMeta?->version               : '')),
        initialReleaseDate:    @json(old('release_date',   $isEdit ? $article->patchNoteMeta?->release_date?->format('Y-m-d') : '')),
        initialPlanningStatus: @json(old('planning_status',$isEdit ? $article->improvementMeta?->planning_status     : 'prevu')),
        initialNotifEmail:     @json(old('notification_email', $isEdit ? $article->survey?->notification_email       : '')),
        initialClosesAt:       @json(old('closes_at',      $isEdit ? $article->survey?->closes_at?->format('Y-m-d\TH:i') : '')),
    };
});
</script>

<div
    x-data="articleEditor(window._articleEditorConfig)"
    @destroy="destroy()"
    class="relative"
>
    {{-- ── Top bar ─────────────────────────────────────────────────── --}}
    <div class="flex items-center justify-between mb-4 sticky top-0 z-40 bg-hub-surface border-b border-hub-border px-6 py-3 -mx-8">
        <a href="{{ route('admin.articles.index') }}"
           class="text-hub-text-sec hover:text-hub-text text-sm flex items-center gap-1 transition">
            ← Retour
        </a>

        <div class="flex items-center gap-3">
            <span x-show="autosaveStatus" x-text="autosaveStatus" class="text-xs text-hub-text-sec italic"></span>

            <select id="status-select" name="status_val"
                    class="bg-hub-surface-hover border border-hub-border text-hub-text text-sm rounded-lg px-3 py-1.5 focus:outline-none focus:ring-1 focus:ring-hub-primary">
                <option value="draft"     {{ $currentStatus === 'draft'     ? 'selected' : '' }}>Brouillon</option>
                <option value="published" {{ $currentStatus === 'published' ? 'selected' : '' }}>Publié</option>
                <option value="archived"  {{ $currentStatus === 'archived'  ? 'selected' : '' }}>Archivé</option>
            </select>

            <button type="button"
                    @click="
                        document.getElementById('status-input').value = document.getElementById('status-select').value;
                        syncContentInput(editor);
                        document.getElementById('article-form').submit();
                    "
                    class="px-4 py-1.5 bg-hub-primary hover:bg-hub-primary-hover text-white text-sm font-medium rounded-lg transition">
                {{ $isEdit ? 'Enregistrer' : 'Créer' }}
            </button>
        </div>
    </div>

    {{-- ── Hidden form ─────────────────────────────────────────────── --}}
    <form id="article-form" method="POST" action="{{ $actionUrl }}" enctype="multipart/form-data">
        @csrf
        @if($isEdit) @method('PUT') @endif

        {{-- Champs communs cachés --}}
        <input type="hidden" name="title"   id="title-input"   value="{{ old('title', $isEdit ? $article->title : '') }}">
        <input type="hidden" name="status"  id="status-input"  value="{{ $currentStatus }}">
        <input type="hidden" name="type"    id="type-input"    value="{{ $currentType }}">
        <input type="hidden" name="content" id="content-input">
        <input type="hidden" name="is_pinned" id="is-pinned-input" value="0">
        <input type="hidden" name="pinned_until" id="pinned-until-input" value="{{ old('pinned_until', $isEdit ? $article->pinned_until?->format('Y-m-d\TH:i') : '') }}">
        <input type="hidden" name="scheduled_at" id="scheduled-at-input" value="{{ old('scheduled_at', $isEdit ? $article->scheduled_at?->format('Y-m-d\TH:i') : '') }}">

        {{-- Satellite : patch_note --}}
        <input type="hidden" name="version"      id="version-input"      value="{{ old('version', $isEdit ? $article->patchNoteMeta?->version : '') }}">
        <input type="hidden" name="release_date" id="release-date-input" value="{{ old('release_date', $isEdit ? $article->patchNoteMeta?->release_date?->format('Y-m-d') : '') }}">
        {{-- changelog_added/fixed/removed seront injectés via JS --}}

        {{-- Satellite : amelioration --}}
        <input type="hidden" name="planning_status" id="planning-status-input" value="{{ old('planning_status', $isEdit ? $article->improvementMeta?->planning_status : 'prevu') }}">

        {{-- Satellite : questionnaire --}}
        <input type="hidden" name="notification_email" id="notif-email-input" value="{{ old('notification_email', $isEdit ? $article->survey?->notification_email : '') }}">
        <input type="hidden" name="closes_at" id="closes-at-input" value="{{ old('closes_at', $isEdit ? $article->survey?->closes_at?->format('Y-m-d\TH:i') : '') }}">
        {{-- questions injectées via JS --}}
    </form>

    {{-- ── Zone d'édition principale ──────────────────────────────── --}}
    <div class="max-w-4xl mx-auto mt-4">

        {{-- Erreurs de validation --}}
        @if($errors->any())
            <div class="mb-6 p-4 bg-red-900/30 border border-red-700 rounded-xl text-red-300 text-sm">
                <ul class="list-disc list-inside space-y-1">
                    @foreach($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Title --}}
        <input type="text" id="title-field" placeholder="Titre de l'article…"
               value="{{ old('title', $isEdit ? $article->title : '') }}"
               @input="document.getElementById('title-input').value = $event.target.value"
               class="w-full bg-transparent text-hub-text text-4xl font-bold placeholder-hub-text-sec border-0 outline-none mb-4 focus:ring-0 px-0">

        {{-- ── Panneau options (Type / Épinglage / Programmation) ──── --}}
        <div class="mb-6 p-4 bg-hub-surface-hover rounded-xl border border-hub-border space-y-4">
            <button type="button" @click="showOptions = !showOptions"
                    class="flex items-center gap-2 text-sm text-hub-text-sec hover:text-hub-text transition w-full">
                <svg class="w-4 h-4 transition-transform" :class="showOptions ? 'rotate-90' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                Options avancées
            </button>

            <div x-show="showOptions" x-transition class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-2">
                {{-- Type --}}
                <div>
                    <label class="block text-xs text-hub-text-sec mb-1">Type d'article</label>
                    <select x-model="articleType" @change="syncHidden()"
                            class="w-full bg-hub-bg border border-hub-border text-hub-text text-sm rounded-lg px-3 py-2 focus:ring-hub-primary focus:border-hub-primary outline-none">
                        <option value="annonce">Annonce</option>
                        <option value="patch_note">Patch Note</option>
                        <option value="amelioration">Amélioration</option>
                        <option value="questionnaire">Questionnaire</option>
                    </select>
                </div>

                {{-- Programmation --}}
                <div>
                    <label class="block text-xs text-hub-text-sec mb-1">Publication programmée</label>
                    <input type="datetime-local" x-model="scheduledAt" @change="syncHidden()"
                           class="w-full bg-hub-bg border border-hub-border text-hub-text text-sm rounded-lg px-3 py-2 focus:ring-hub-primary focus:border-hub-primary outline-none">
                </div>

                {{-- Épinglage --}}
                <div class="flex items-center gap-3">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" x-model="isPinned" @change="syncHidden()" class="text-hub-primary rounded">
                        <span class="text-sm text-hub-text-sec">Épingler cet article</span>
                    </label>
                </div>

                <div x-show="isPinned" x-transition>
                    <label class="block text-xs text-hub-text-sec mb-1">Épinglé jusqu'au</label>
                    <input type="datetime-local" x-model="pinnedUntil" @change="syncHidden()"
                           class="w-full bg-hub-bg border border-hub-border text-hub-text text-sm rounded-lg px-3 py-2 focus:ring-hub-primary focus:border-hub-primary outline-none">
                </div>
            </div>
        </div>

        {{-- ── PATCH NOTE — champs spécifiques ─────────────────────── --}}
        <div x-show="articleType === 'patch_note'" x-transition class="mb-6 space-y-4 p-4 bg-blue-900/10 border border-blue-700/30 rounded-xl">
            <h3 class="text-sm font-semibold text-blue-300">Informations Patch Note</h3>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs text-hub-text-sec mb-1">Version (x.y.z)</label>
                    <input type="text" x-model="version" @input="syncHidden()" placeholder="4.6.0"
                           class="w-full bg-hub-bg border border-hub-border text-hub-text text-sm rounded-lg px-3 py-2 focus:ring-hub-primary focus:border-hub-primary outline-none font-mono">
                </div>
                <div>
                    <label class="block text-xs text-hub-text-sec mb-1">Date de sortie</label>
                    <input type="date" x-model="releaseDate" @change="syncHidden()"
                           class="w-full bg-hub-bg border border-hub-border text-hub-text text-sm rounded-lg px-3 py-2 focus:ring-hub-primary focus:border-hub-primary outline-none">
                </div>
            </div>

            {{-- Changelog --}}
            @foreach(['added' => ['Ajouté', 'text-green-300'], 'fixed' => ['Corrigé', 'text-orange-300'], 'removed' => ['Supprimé', 'text-red-300']] as $key => [$label, $color])
            <div>
                <label class="block text-xs {{ $color }} mb-2">{{ $label }}</label>
                <ul class="space-y-1 mb-2">
                    <template x-for="(item, idx) in changelog['{{ $key }}']" :key="idx">
                        <li class="flex items-center gap-2">
                            <span class="text-sm text-hub-text flex-1" x-text="item"></span>
                            <button type="button" @click="removeChangelogItem('{{ $key }}', idx); syncHidden()" class="text-red-400 hover:text-red-300 text-xs">✕</button>
                        </li>
                    </template>
                </ul>
                <div class="flex gap-2">
                    <input type="text" x-model="newChangelog['{{ $key }}']"
                           @keydown.enter.prevent="addChangelogItem('{{ $key }}'); syncHidden()"
                           placeholder="Ajouter un élément…"
                           class="flex-1 bg-hub-bg border border-hub-border text-hub-text text-sm rounded-lg px-3 py-1.5 focus:ring-hub-primary focus:border-hub-primary outline-none">
                    <button type="button" @click="addChangelogItem('{{ $key }}'); syncHidden()"
                            class="px-3 py-1.5 bg-hub-surface border border-hub-border text-hub-text-sec hover:text-hub-text text-sm rounded-lg transition">+</button>
                </div>
            </div>
            @endforeach
        </div>

        {{-- ── AMÉLIORATION — champs spécifiques ───────────────────── --}}
        <div x-show="articleType === 'amelioration'" x-transition class="mb-6 p-4 bg-green-900/10 border border-green-700/30 rounded-xl">
            <h3 class="text-sm font-semibold text-green-300 mb-3">Statut planning</h3>
            <select x-model="planningStatus" @change="syncHidden()"
                    class="w-full bg-hub-bg border border-hub-border text-hub-text text-sm rounded-lg px-3 py-2 focus:ring-hub-primary focus:border-hub-primary outline-none">
                <option value="prevu">À venir</option>
                <option value="en_cours">En cours</option>
                <option value="annule">Annulé</option>
                <option value="livre">Livré ✓</option>
            </select>
        </div>

        {{-- ── QUESTIONNAIRE — champs spécifiques ─────────────────── --}}
        <div x-show="articleType === 'questionnaire'" x-transition class="mb-6 p-4 bg-purple-900/10 border border-purple-700/30 rounded-xl space-y-4">
            <h3 class="text-sm font-semibold text-purple-300">Paramètres du questionnaire</h3>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs text-hub-text-sec mb-1">Email de notification</label>
                    <input type="email" x-model="notifEmail" @input="syncHidden()" placeholder="admin@example.com"
                           class="w-full bg-hub-bg border border-hub-border text-hub-text text-sm rounded-lg px-3 py-2 focus:ring-hub-primary focus:border-hub-primary outline-none">
                </div>
                <div>
                    <label class="block text-xs text-hub-text-sec mb-1">Fermeture</label>
                    <input type="datetime-local" x-model="closesAt" @change="syncHidden()"
                           class="w-full bg-hub-bg border border-hub-border text-hub-text text-sm rounded-lg px-3 py-2 focus:ring-hub-primary focus:border-hub-primary outline-none">
                </div>
            </div>

            {{-- Questions existantes --}}
            <div class="space-y-3">
                <template x-for="(q, qi) in questions" :key="qi">
                    <div class="flex items-start gap-2 p-3 bg-hub-bg rounded-lg border border-hub-border">
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 mb-1">
                                <span class="text-xs text-purple-300 font-mono uppercase" x-text="q.type"></span>
                                <span class="text-sm text-hub-text" x-text="q.label"></span>
                            </div>
                            <template x-if="q.options && q.options.length">
                                <ul class="flex flex-wrap gap-1 mt-1">
                                    <template x-for="opt in q.options" :key="opt">
                                        <li class="text-xs bg-hub-surface px-2 py-0.5 rounded" x-text="opt"></li>
                                    </template>
                                </ul>
                            </template>
                        </div>
                        <button type="button" @click="removeQuestion(qi); syncHidden()" class="text-red-400 hover:text-red-300 text-xs flex-shrink-0">✕</button>
                    </div>
                </template>
            </div>

            {{-- Ajouter une question --}}
            <div class="p-3 bg-hub-bg rounded-lg border border-hub-border space-y-3">
                <p class="text-xs text-hub-text-sec font-medium">Nouvelle question</p>
                <div class="grid grid-cols-2 gap-3">
                    <select x-model="newQuestion.type"
                            class="bg-hub-surface border border-hub-border text-hub-text text-sm rounded-lg px-3 py-1.5 outline-none">
                        <option value="qcm">QCM (une réponse)</option>
                        <option value="checkbox">Cases à cocher</option>
                        <option value="text">Texte libre</option>
                        <option value="rating">Note (1–5)</option>
                        <option value="boolean">Oui / Non</option>
                    </select>
                    <input type="text" x-model="newQuestion.label" placeholder="Libellé de la question…"
                           class="bg-hub-surface border border-hub-border text-hub-text text-sm rounded-lg px-3 py-1.5 outline-none">
                </div>

                {{-- Options (QCM/checkbox) --}}
                <div x-show="newQuestion.type === 'qcm' || newQuestion.type === 'checkbox'" x-transition class="space-y-2">
                    <ul class="flex flex-wrap gap-1">
                        <template x-for="(opt, oi) in newQuestion.options" :key="oi">
                            <li class="flex items-center gap-1 text-xs bg-hub-surface px-2 py-0.5 rounded border border-hub-border">
                                <span x-text="opt"></span>
                                <button type="button" @click="removeOption(oi)" class="text-red-400 hover:text-red-300 ml-1">✕</button>
                            </li>
                        </template>
                    </ul>
                    <div class="flex gap-2">
                        <input type="text" x-model="newOption" @keydown.enter.prevent="addOption()" placeholder="Ajouter une option…"
                               class="flex-1 bg-hub-surface border border-hub-border text-hub-text text-sm rounded-lg px-3 py-1 outline-none">
                        <button type="button" @click="addOption()" class="px-3 py-1 text-sm bg-hub-surface border border-hub-border text-hub-text-sec rounded-lg hover:text-hub-text transition">+</button>
                    </div>
                </div>

                <button type="button" @click="addQuestion(); syncHidden()"
                        class="px-4 py-1.5 bg-purple-900/50 border border-purple-700/50 text-purple-300 text-sm rounded-lg hover:bg-purple-900/70 transition">
                    + Ajouter la question
                </button>
            </div>
        </div>

        <hr class="border-hub-border mb-6">

        {{-- TipTap canvas --}}
        <div
            x-ref="editorEl"
            class="bg-white rounded-xl p-8 min-h-[540px] shadow-sm border border-gray-200 text-gray-900"
        ></div>
    </div>{{-- fin zone d'édition --}}

    {{-- ── Slash command menu ──────────────────────────────────────── --}}
    <div
        x-show="slashMenu.open && slashMenu.items && slashMenu.items.length > 0"
        x-transition
        :style="`position:fixed; left:${slashMenu.x}px; top:${slashMenu.y}px; z-index:9999;`"
        class="bg-white border border-gray-200 rounded-xl shadow-2xl w-72 py-2 overflow-hidden"
        @click.outside="slashMenu.open = false"
    >
        <template x-for="(cmd, idx) in slashMenu.items" :key="cmd.id">
            <button
                type="button"
                @mouseenter="slashMenu.selectedIndex = idx"
                @mousedown.prevent="selectSlashItem(idx)"
                :class="idx === slashMenu.selectedIndex ? 'bg-gray-100' : 'hover:bg-gray-50'"
                class="w-full flex items-center gap-3 px-4 py-2 text-left transition"
            >
                <span class="w-8 h-8 flex items-center justify-center bg-gray-100 rounded-lg text-sm font-bold text-gray-600 flex-shrink-0"
                      x-text="cmd.icon"></span>
                <div>
                    <div class="text-sm font-medium text-gray-800" x-text="cmd.label"></div>
                    <div class="text-xs text-gray-400" x-text="cmd.desc"></div>
                </div>
            </button>
        </template>
    </div>

    {{-- ── Image alignment toolbar ─────────────────────────────────── --}}
    <div
        x-show="imageToolbar.open"
        x-transition
        :style="`position:fixed; left:${imageToolbar.x}px; top:${imageToolbar.y}px; z-index:9998;`"
        class="bg-white border border-gray-200 rounded-xl shadow-xl px-3 py-2 flex items-center gap-2"
    >
        <span class="text-xs text-gray-400 mr-1">Alignement :</span>
        <template x-for="al in ['left','center','right','full']">
            <button
                type="button"
                @mousedown.prevent="setImageAlignment(al)"
                :class="imageToolbar.alignment === al ? 'bg-gray-200 text-gray-900' : 'text-gray-500 hover:bg-gray-100'"
                class="px-2 py-1 rounded text-xs font-medium transition capitalize"
                x-text="al"
            ></button>
        </template>
        <span class="mx-2 h-4 border-r border-gray-200"></span>
        <input
            type="text"
            placeholder="Légende…"
            :value="imageToolbar.caption"
            @mousedown="imageToolbar.editing = true"
            @blur="imageToolbar.editing = false"
            @input="setImageCaption($event.target.value)"
            class="border border-gray-200 rounded px-2 py-0.5 text-xs w-32 focus:outline-none focus:ring-1 focus:ring-gray-300"
        >
    </div>

    {{-- ── Image insert modal ──────────────────────────────────────── --}}
    <div
        x-show="imageModal.open"
        x-transition
        class="fixed inset-0 z-50 flex items-center justify-center"
        @click.self="imageModal.open = false"
    >
        <div class="absolute inset-0 bg-black/50"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md p-6 z-10">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Insérer une image</h3>

            <div class="flex gap-2 mb-4 border-b border-gray-200">
                <button type="button"
                        @click="imageModal.mode = 'url'"
                        :class="imageModal.mode === 'url' ? 'border-b-2 border-gray-800 text-gray-800 font-medium' : 'text-gray-400'"
                        class="px-3 pb-2 text-sm transition">URL</button>
                <button type="button"
                        @click="imageModal.mode = 'upload'"
                        :class="imageModal.mode === 'upload' ? 'border-b-2 border-gray-800 text-gray-800 font-medium' : 'text-gray-400'"
                        class="px-3 pb-2 text-sm transition">Upload</button>
            </div>

            <div x-show="imageModal.mode === 'url'" class="space-y-3">
                <input type="text"
                       x-model="imageModal.url"
                       placeholder="https://example.com/image.jpg"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-400">
                <div class="flex justify-end gap-2">
                    <button type="button" @click="imageModal.open = false"
                            class="px-4 py-2 text-sm text-gray-500 hover:text-gray-700 transition">Annuler</button>
                    <button type="button" @click="insertImageByUrl(imageModal.url)"
                            class="px-4 py-2 bg-gray-800 hover:bg-gray-700 text-white text-sm rounded-lg transition">Insérer</button>
                </div>
            </div>

            <div x-show="imageModal.mode === 'upload'" class="space-y-3">
                <label class="block border-2 border-dashed border-gray-300 rounded-lg p-6 text-center cursor-pointer hover:border-gray-400 transition">
                    <span class="text-gray-500 text-sm">Cliquez pour choisir un fichier image</span>
                    <input type="file" accept="image/*"
                           class="hidden"
                           @change="insertImageByFile($event.target.files[0])">
                </label>
                <div class="flex justify-end">
                    <button type="button" @click="imageModal.open = false"
                            class="px-4 py-2 text-sm text-gray-500 hover:text-gray-700 transition">Annuler</button>
                </div>
            </div>
        </div>
    </div>

</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const titleField = document.getElementById('title-field');
    const titleInput = document.getElementById('title-input');
    if (titleField && titleInput) titleInput.value = titleField.value;
});
</script>
</x-admin-layout>
