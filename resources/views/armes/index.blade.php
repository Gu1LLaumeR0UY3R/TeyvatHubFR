<x-app-layout>
    <x-slot name="title">Armes</x-slot>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

        <h1 class="text-3xl font-bold text-hub-primary mb-6">Armes</h1>

        <form method="GET" action="{{ route('armes.index') }}" class="mb-6 flex flex-wrap gap-3 items-end">
            <div class="flex-1 min-w-48">
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Rechercher une arme..."
                       class="w-full rounded-lg bg-hub-surface border border-hub-border px-4 py-2 text-hub-text placeholder-hub-text-sec focus:outline-none focus:ring-2 focus:ring-hub-primary">
            </div>

            <select name="type" class="rounded-lg bg-hub-surface border border-hub-border px-3 py-2 text-hub-text focus:outline-none focus:ring-2 focus:ring-hub-primary">
                <option value="">Tous les types</option>
                @foreach($types as $type)
                    <option value="{{ $type->id_TArmes }}" {{ request('type') == $type->id_TArmes ? 'selected' : '' }}>
                        {{ $type->libelle_TArme }}
                    </option>
                @endforeach
            </select>

            <select name="etoile" class="rounded-lg bg-hub-surface border border-hub-border px-3 py-2 text-hub-text focus:outline-none focus:ring-2 focus:ring-hub-primary">
                <option value="">Toutes les raretés</option>
                @foreach($etoiles as $etoile)
                    <option value="{{ $etoile->id_etoile }}" {{ request('etoile') == $etoile->id_etoile ? 'selected' : '' }}>
                        {{ $etoile->libelle }}
                    </option>
                @endforeach
            </select>

            <select name="sort" class="rounded-lg bg-hub-surface border border-hub-border px-3 py-2 text-hub-text focus:outline-none focus:ring-2 focus:ring-hub-primary">
                <option value="nom_asc"    {{ request('sort', 'nom_asc') === 'nom_asc'    ? 'selected' : '' }}>Nom A→Z</option>
                <option value="nom_desc"   {{ request('sort') === 'nom_desc'   ? 'selected' : '' }}>Nom Z→A</option>
                <option value="rarete_asc" {{ request('sort') === 'rarete_asc' ? 'selected' : '' }}>Rareté ↑</option>
                <option value="rarete_desc"{{ request('sort') === 'rarete_desc'? 'selected' : '' }}>Rareté ↓</option>
            </select>

            <div class="flex gap-1">
                <a href="{{ request()->fullUrlWithQuery(['view' => 'grid']) }}"
                   class="px-3 py-2 rounded-lg border {{ request('view', 'grid') === 'grid' ? 'bg-hub-primary text-white border-hub-primary' : 'bg-hub-surface border-hub-border text-hub-text-sec' }}">⊞</a>
                <a href="{{ request()->fullUrlWithQuery(['view' => 'list']) }}"
                   class="px-3 py-2 rounded-lg border {{ request('view') === 'list' ? 'bg-hub-primary text-white border-hub-primary' : 'bg-hub-surface border-hub-border text-hub-text-sec' }}">☰</a>
            </div>

            <button type="submit" class="px-4 py-2 bg-hub-primary hover:bg-hub-accent text-white rounded-lg font-medium transition-colors">Filtrer</button>
        </form>

        @if($armes->isEmpty())
            <div class="text-center py-16 text-hub-text-sec">
                <p class="text-xl">Aucune arme trouvée.</p>
            </div>
        @else
            @if(request('view') === 'list')
                <div class="space-y-3">
                    @foreach($armes as $arme)
                        <a href="{{ route('armes.show', $arme->slug) }}"
                           class="flex items-center gap-4 bg-hub-surface border border-hub-border rounded-xl p-3 hover:border-hub-primary transition-colors">
                            <img src="{{ $arme->photos->first()?->source_url ?? $arme->photos->first()?->chemin_photo ?? asset('images/placeholder.webp') }}"
                                 alt="{{ $arme->nom_arme }}" class="w-12 h-12 rounded-full object-cover">
                            <div class="flex-1 min-w-0">
                                <p class="font-semibold text-hub-text truncate">{{ $arme->nom_arme }}</p>
                                <p class="text-sm text-hub-text-sec">{{ $arme->typeArme?->libelle_TArme ?? '—' }} · {{ $arme->etoile?->libelle ?? '—' }}</p>
                            </div>
                        </a>
                    @endforeach
                </div>
            @else
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
                    @foreach($armes as $arme)
                        <a href="{{ route('armes.show', $arme->slug) }}"
                           class="bg-hub-surface border border-hub-border rounded-2xl overflow-hidden hover:border-hub-primary hover:-translate-y-1 transition-all">
                            <img src="{{ $arme->photos->first()?->source_url ?? $arme->photos->first()?->chemin_photo ?? asset('images/placeholder.webp') }}"
                                 alt="{{ $arme->nom_arme }}"
                                 class="w-full aspect-square object-cover">
                            <div class="p-3">
                                <p class="font-semibold text-hub-text text-sm truncate">{{ $arme->nom_arme }}</p>
                                <p class="text-xs text-hub-text-sec truncate">{{ $arme->typeArme?->libelle_TArme ?? '—' }}</p>
                                <p class="text-xs text-hub-gold">{{ $arme->etoile?->libelle ?? '—' }}</p>
                            </div>
                        </a>
                    @endforeach
                </div>
            @endif

            <div class="mt-6">
                {{ $armes->links() }}
            </div>
        @endif

    </div>
</x-app-layout>
