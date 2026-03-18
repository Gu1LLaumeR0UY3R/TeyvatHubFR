<x-app-layout>
    <x-slot name="title">Ennemis</x-slot>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

        <h1 class="text-3xl font-bold text-hub-primary mb-6">Ennemis</h1>

        <form method="GET" action="{{ route('ennemis.index') }}" class="mb-6 flex flex-wrap gap-3 items-end">
            <div class="flex-1 min-w-48">
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Rechercher un ennemi..."
                       class="w-full rounded-lg bg-hub-surface border border-hub-border px-4 py-2 text-hub-text placeholder-hub-text-sec focus:outline-none focus:ring-2 focus:ring-hub-primary">
            </div>

            <select name="type" class="rounded-lg bg-hub-surface border border-hub-border px-3 py-2 text-hub-text focus:outline-none focus:ring-2 focus:ring-hub-primary">
                <option value="">Tous les types</option>
                @foreach($types as $type)
                    <option value="{{ $type->id_typeEnnemi }}" {{ request('type') == $type->id_typeEnnemi ? 'selected' : '' }}>
                        {{ $type->libelle_Type }}
                    </option>
                @endforeach
            </select>

            <select name="element" class="rounded-lg bg-hub-surface border border-hub-border px-3 py-2 text-hub-text focus:outline-none focus:ring-2 focus:ring-hub-primary">
                <option value="">Tous les éléments</option>
                @foreach($elements as $el)
                    <option value="{{ $el->id_element }}" {{ request('element') == $el->id_element ? 'selected' : '' }}>
                        {{ $el->libelle_element }}
                    </option>
                @endforeach
            </select>

            <select name="sort" class="rounded-lg bg-hub-surface border border-hub-border px-3 py-2 text-hub-text focus:outline-none focus:ring-2 focus:ring-hub-primary">
                <option value="nom_asc"  {{ request('sort', 'nom_asc') === 'nom_asc'  ? 'selected' : '' }}>Nom A→Z</option>
                <option value="nom_desc" {{ request('sort') === 'nom_desc' ? 'selected' : '' }}>Nom Z→A</option>
                <option value="type_asc" {{ request('sort') === 'type_asc' ? 'selected' : '' }}>Par type</option>
            </select>

            <div class="flex gap-1">
                <a href="{{ request()->fullUrlWithQuery(['view' => 'grid']) }}"
                   class="px-3 py-2 rounded-lg border {{ request('view', 'grid') === 'grid' ? 'bg-hub-primary text-white border-hub-primary' : 'bg-hub-surface border-hub-border text-hub-text-sec' }}">⊞</a>
                <a href="{{ request()->fullUrlWithQuery(['view' => 'list']) }}"
                   class="px-3 py-2 rounded-lg border {{ request('view') === 'list' ? 'bg-hub-primary text-white border-hub-primary' : 'bg-hub-surface border-hub-border text-hub-text-sec' }}">☰</a>
            </div>

            <button type="submit" class="px-4 py-2 bg-hub-primary hover:bg-hub-accent text-white rounded-lg font-medium transition-colors">Filtrer</button>
        </form>

        @if($ennemis->isEmpty())
            <div class="text-center py-16 text-hub-text-sec">
                <p class="text-xl">Aucun ennemi trouvé.</p>
            </div>
        @else
            @if(request('view') === 'list')
                <div class="space-y-3">
                    @foreach($ennemis as $ennemi)
                        <a href="{{ route('ennemis.show', $ennemi->slug) }}"
                           class="flex items-center gap-4 bg-hub-surface border border-hub-border rounded-xl p-3 hover:border-hub-primary transition-colors">
                            <img src="{{ $ennemi->photos->first()?->source_url ?? $ennemi->photos->first()?->chemin_photo ?? asset('images/placeholder.webp') }}"
                                 alt="{{ $ennemi->nom_ennemi }}" class="w-12 h-12 rounded-full object-cover">
                            <div class="flex-1">
                                <span class="font-semibold text-hub-text">{{ $ennemi->nom_ennemi }}</span>
                            </div>
                            <span class="text-hub-text-sec text-sm">{{ $ennemi->element?->libelle_element ?? 'Neutre' }}</span>
                            <span class="text-hub-muted text-sm">{{ $ennemi->typeEnnemi?->libelle_Type ?? '—' }}</span>
                        </a>
                    @endforeach
                </div>
            @else
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
                    @foreach($ennemis as $ennemi)
                        <a href="{{ route('ennemis.show', $ennemi->slug) }}"
                           class="bg-hub-surface border border-hub-border rounded-xl overflow-hidden hover:border-hub-primary transition-colors group">
                            <div class="aspect-square overflow-hidden bg-hub-surface-hover">
                                <img src="{{ $ennemi->photos->first()?->source_url ?? $ennemi->photos->first()?->chemin_photo ?? asset('images/placeholder.webp') }}"
                                     alt="{{ $ennemi->nom_ennemi }}"
                                     class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-200">
                            </div>
                            <div class="p-3">
                                <h3 class="font-semibold text-hub-text text-sm truncate">{{ $ennemi->nom_ennemi }}</h3>
                                <p class="text-hub-text-sec text-xs mt-1">{{ $ennemi->element?->libelle_element ?? 'Neutre' }}</p>
                                <p class="text-hub-muted text-xs">{{ $ennemi->typeEnnemi?->libelle_Type ?? '—' }}</p>
                            </div>
                        </a>
                    @endforeach
                </div>
            @endif

            <div class="mt-8">{{ $ennemis->links() }}</div>
        @endif

    </div>
</x-app-layout>
