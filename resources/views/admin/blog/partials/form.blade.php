@php
    $isEdit = $article !== null;
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

<form method="POST" action="{{ $action }}" class="space-y-5">
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
        <input id="slug" name="slug" type="text"
               value="{{ old('slug', $article?->slug) }}"
               class="w-full rounded border border-slate-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-hub-gold" />
    </div>

    <div>
        <label for="extrait" class="block text-sm font-medium text-slate-700 mb-1">Extrait (optionnel)</label>
        <textarea id="extrait" name="extrait" rows="3"
                  class="w-full rounded border border-slate-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-hub-gold">{{ old('extrait', $article?->extrait) }}</textarea>
    </div>

    <div>
        <label for="contenu_article" class="block text-sm font-medium text-slate-700 mb-1">Contenu</label>
        <textarea id="contenu_article" name="contenu_article" rows="14" required
                  class="w-full rounded border border-slate-300 px-3 py-2 font-mono text-sm focus:outline-none focus:ring-2 focus:ring-hub-gold">{{ old('contenu_article', $article?->contenu_article) }}</textarea>
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
