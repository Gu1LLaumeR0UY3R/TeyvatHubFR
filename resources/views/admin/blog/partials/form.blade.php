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
    $featuredPhotos = $article?->photos?->where('type', 'featured')->values() ?? collect();
    $inlinePhotos = $article?->photos?->where('type', 'inline')->values() ?? collect();
@endphp

<form method="POST" action="{{ $action }}" class="space-y-5" enctype="multipart/form-data"
    x-data="blogArticleForm(@js($slugBases), @js(route('admin.blog.slugs.store')), @js(old('slug', $article?->slug)))"
>
    @csrf
    @if($method !== 'POST')
        @method($method)
    @endif

    <div>
        <label for="titre_article" class="block text-sm font-medium text-slate-700 mb-1">Titre</label>
        <input id="titre_article" name="titre_article" type="text" required
               value="{{ old('titre_article', $article?->titre_article) }}"
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

    <div>
        <label for="contenu_article" class="block text-sm font-medium text-slate-700 mb-1">Contenu</label>
        <textarea id="contenu_article" name="contenu_article" rows="14" required x-ref="contentTextarea"
                  class="w-full rounded border border-slate-300 px-3 py-2 font-mono text-sm focus:outline-none focus:ring-2 focus:ring-hub-gold">{{ old('contenu_article', $article?->contenu_article) }}</textarea>
        <p class="mt-1 text-xs text-slate-500">L'extrait est généré automatiquement à partir du début du contenu.</p>
    </div>

    <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
        <div class="mb-3">
            <h2 class="text-sm font-bold text-slate-800">Images mises en avant</h2>
            <p class="text-xs text-slate-500">Elles servent uniquement à l'affichage public de l'article et des cartes du blog.</p>
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

    <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
        <div class="mb-3">
            <h2 class="text-sm font-bold text-slate-800">Images dans l'article</h2>
            <p class="text-xs text-slate-500">Upload puis insertion dans le contenu avec une taille S, M, L ou XL.</p>
        </div>
        <input type="file" name="inline_images[]" accept="image/*" multiple
               class="block w-full rounded border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 file:mr-3 file:rounded file:border-0 file:bg-hub-gold file:px-3 file:py-2 file:text-sm file:font-medium file:text-hub-bg" />

        @if($article)
            <div class="mt-4 space-y-3">
                @forelse($inlinePhotos as $photo)
                    <div class="rounded-xl border border-slate-200 bg-white p-3">
                        <div class="flex flex-col gap-3 lg:flex-row lg:items-center">
                            <img src="{{ $article->resolvePhotoUrl($photo) }}" alt="Image article" class="h-28 w-full rounded-lg object-cover lg:w-48" />
                            <div class="flex-1 space-y-2">
                                <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">Insérer dans le contenu</div>
                                <div class="flex flex-wrap gap-2">
                                    @foreach(['small' => 'S', 'medium' => 'M', 'large' => 'L', 'full' => 'XL'] as $size => $label)
                                        <button type="button"
                                                @click="insertInlineImageToken({{ (int) $photo->id_photo }}, '{{ $size }}')"
                                                class="rounded border border-slate-300 px-2 py-1 text-xs text-slate-700 hover:bg-slate-100">
                                            {{ $label }}
                                        </button>
                                    @endforeach
                                </div>
                                <div class="text-[11px] text-slate-500">Tag généré: <span class="font-mono">[[image:{{ (int) $photo->id_photo }}|medium]]</span></div>
                            </div>
                            <form method="POST" action="{{ route('admin.blog.images.destroy', [$article, $photo]) }}" onsubmit="return confirm('Supprimer cette image ?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="rounded border border-red-300 px-2 py-1 text-xs text-red-700 hover:bg-red-50">Supprimer</button>
                            </form>
                        </div>
                    </div>
                @empty
                    <div class="text-sm italic text-slate-500">Aucune image inline. Enregistre d'abord l'article si tu veux ensuite les insérer dans le contenu.</div>
                @endforelse
            </div>
        @else
            <p class="mt-3 text-sm italic text-slate-500">Après la première création, tu pourras insérer les images inline dans le contenu avec les boutons de taille.</p>
        @endif
    </div>

    <div class="grid gap-4 md:grid-cols-2">
        <div>
            <label for="statut" class="block text-sm font-medium text-slate-700 mb-1">Statut</label>
            <select id="statut" name="statut" class="w-full rounded border border-slate-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-hub-gold">
                @php $statut = old('statut', $article?->statut ?? 'brouillon'); @endphp
                <option value="brouillon" @selected($statut === 'brouillon')>Brouillon</option>
                <option value="publie" @selected($statut === 'publie')>Publié</option>
            </select>
        </div>

        <div>
            <label for="date_publication" class="block text-sm font-medium text-slate-700 mb-1">Date de publication</label>
            <input id="date_publication" name="date_publication" type="datetime-local"
                   value="{{ old('date_publication', optional($article?->date_publication)->format('Y-m-d\TH:i')) }}"
                   class="w-full rounded border border-slate-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-hub-gold" />
        </div>
    </div>

    <div class="flex items-center gap-3 pt-2">
        <button type="submit" class="px-4 py-2 bg-hub-gold text-hub-bg rounded hover:opacity-90">{{ $submitLabel }}</button>
        <a href="{{ route('admin.blog.index') }}" class="px-4 py-2 rounded border border-slate-300 text-slate-700 hover:bg-slate-100">Annuler</a>
    </div>
</form>

<script>
    function blogArticleForm(initialSlugs, storeSlugUrl, initialSlugValue) {
        return {
            availableSlugs: Array.isArray(initialSlugs) ? initialSlugs : [],
            slugInputValue: initialSlugValue || '',
            slugFeedback: '',
            slugFeedbackError: false,
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
                    if (part && candidate.includes(part)) {
                        score += 10;
                    }
                }

                return score;
            },
            useExistingSlug(slug) {
                this.slugInputValue = slug;
                this.$refs.slugInput.value = slug;
                this.slugFeedback = '';
                this.slugFeedbackError = false;
            },
            insertInlineImageToken(photoId, size) {
                const textarea = this.$refs.contentTextarea;
                if (!textarea || !photoId) {
                    return;
                }

                const token = `[[image:${photoId}|${size}]]`;
                const start = textarea.selectionStart ?? textarea.value.length;
                const end = textarea.selectionEnd ?? textarea.value.length;
                const before = textarea.value.slice(0, start);
                const after = textarea.value.slice(end);
                const paddedBefore = before && !before.endsWith('\n') ? `${before}\n` : before;
                const paddedAfter = after && !after.startsWith('\n') ? `\n${after}` : after;

                textarea.value = `${paddedBefore}${token}${paddedAfter}`;
                this.$refs.contentTextarea.dispatchEvent(new Event('input', { bubbles: true }));
                const cursor = paddedBefore.length + token.length;
                textarea.focus();
                textarea.setSelectionRange(cursor, cursor);
            },
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
                } catch (error) {
                    this.slugFeedback = 'Erreur réseau pendant l\'ajout du slug.';
                    this.slugFeedbackError = true;
                }
            },
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
        };
    }
</script>
