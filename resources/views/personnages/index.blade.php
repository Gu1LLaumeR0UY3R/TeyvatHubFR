<x-app-layout>
    <x-slot name="title">Personnages</x-slot>
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    {{-- En-tête --}}
    <h1 class="text-3xl font-bold text-hub-primary mb-6">Personnages</h1>

    {{-- Filtres --}}
    <form method="GET" action="{{ route('personnages.index') }}" class="mb-6 flex flex-wrap gap-3 items-end">
        {{-- Recherche --}}
        <div class="flex-1 min-w-48">
            <input type="text"
                   name="search"
                   value="{{ request('search') }}"
                   placeholder="Rechercher un personnage..."
                   class="w-full rounded-lg bg-hub-surface border border-hub-border px-4 py-2 text-hub-text placeholder-hub-text-sec focus:outline-none focus:ring-2 focus:ring-hub-primary">
        </div>

        {{-- Filtre élément --}}
        <div>
            <select name="element"
                    class="rounded-lg bg-hub-surface border border-hub-border px-3 py-2 text-hub-text focus:outline-none focus:ring-2 focus:ring-hub-primary">
                <option value="">Tous les éléments</option>
                @foreach($elements as $el)
                    <option value="{{ $el->id_element }}" {{ request('element') == $el->id_element ? 'selected' : '' }}>
                        {{ $el->libelle_element }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- Tri --}}
        <div>
            <select name="sort"
                    class="rounded-lg bg-hub-surface border border-hub-border px-3 py-2 text-hub-text focus:outline-none focus:ring-2 focus:ring-hub-primary">
                <option value="nom_asc"    {{ request('sort', 'nom_asc') === 'nom_asc'    ? 'selected' : '' }}>Nom A→Z</option>
                <option value="nom_desc"   {{ request('sort') === 'nom_desc'   ? 'selected' : '' }}>Nom Z→A</option>
                <option value="rarete_asc" {{ request('sort') === 'rarete_asc' ? 'selected' : '' }}>Rareté ↑</option>
                <option value="rarete_desc"{{ request('sort') === 'rarete_desc'? 'selected' : '' }}>Rareté ↓</option>
            </select>
        </div>

        {{-- Switch grille / liste --}}
        <div class="flex gap-1">
            <a href="{{ request()->fullUrlWithQuery(['view' => 'grid']) }}"
               class="px-3 py-2 rounded-lg border {{ request('view', 'grid') === 'grid' ? 'bg-hub-primary text-white border-hub-primary' : 'bg-hub-surface border-hub-border text-hub-text-sec hover:text-hub-text' }}">
                ⊞
            </a>
            <a href="{{ request()->fullUrlWithQuery(['view' => 'list']) }}"
               class="px-3 py-2 rounded-lg border {{ request('view') === 'list' ? 'bg-hub-primary text-white border-hub-primary' : 'bg-hub-surface border-hub-border text-hub-text-sec hover:text-hub-text' }}">
                ☰
            </a>
        </div>

        <button type="submit"
                class="px-4 py-2 bg-hub-primary hover:bg-hub-accent text-white rounded-lg font-medium transition-colors">
            Filtrer
        </button>
    </form>

    {{-- Résultats --}}
    @if($personnages->isEmpty())
        <div class="text-center py-16 text-hub-text-sec">
            <p class="text-xl">Aucun personnage trouvé.</p>
        </div>
    @else
        @if(request('view') === 'list')
            {{-- Vue liste --}}
            <div class="space-y-3">
                @foreach($personnages as $perso)
                    <a href="{{ route('personnages.show', $perso->slug) }}"
                       class="flex items-center gap-4 bg-hub-surface border border-hub-border rounded-xl p-3 hover:border-hub-primary transition-colors">
                        <img src="{{ $perso->photos->first()?->source_url ?? $perso->photos->first()?->chemin_photo ?? asset('images/placeholder.webp') }}"
                             alt="{{ $perso->nom_perso }}"
                             class="w-12 h-12 rounded-full object-cover">
                        <div class="flex-1">
                            <span class="font-semibold text-hub-text">{{ $perso->nom_perso }}</span>
                        </div>
                        <span class="text-hub-text-sec text-sm">{{ $perso->element?->libelle_element ?? '—' }}</span>
                        <span class="text-hub-gold text-sm">{{ $perso->etoile?->libelle ?? '—' }}</span>
                        <span class="text-hub-text-sec text-sm">{{ $perso->typeArme?->libelle_TArme ?? '—' }}</span>
                    </a>
                @endforeach
            </div>
        @else
            {{-- Vue grille (défaut) --}}
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
                @foreach($personnages as $perso)
                    <a href="{{ route('personnages.show', $perso->slug) }}"
                       class="bg-hub-surface border border-hub-border rounded-xl overflow-hidden hover:border-hub-primary transition-colors group">
                        <div class="aspect-square overflow-hidden bg-hub-surface-hover">
                            <img src="{{ $perso->photos->first()?->source_url ?? $perso->photos->first()?->chemin_photo ?? asset('images/placeholder.webp') }}"
                                 alt="{{ $perso->nom_perso }}"
                                 class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-200">
                        </div>
                        <div class="p-3">
                            <h3 class="font-semibold text-hub-text text-sm truncate">{{ $perso->nom_perso }}</h3>
                            <p class="text-hub-text-sec text-xs mt-1">{{ $perso->element?->libelle_element ?? '—' }}</p>
                            <div class="flex items-center justify-between mt-1">
                                <span class="text-hub-gold text-xs">{{ $perso->etoile?->libelle ?? '—' }}</span>
                                <span class="text-hub-text-sec text-xs">{{ $perso->typeArme?->libelle_TArme ?? '—' }}</span>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
        @endif

        {{-- Pagination --}}
        <div class="mt-8">
            {{ $personnages->links() }}
        </div>
    @endif

</div>
</x-app-layout>
