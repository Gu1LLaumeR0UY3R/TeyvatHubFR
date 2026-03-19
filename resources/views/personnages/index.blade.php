<x-app-layout>
<x-slot name="title">Personnages</x-slot>
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    {{-- Header --}}
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-hub-text mb-2">Personnages</h1>
        <p class="text-hub-text-sec">{{ $personnages->total() }} personnage(s) trouvé(s)</p>
    </div>

    {{-- Filtres --}}
    <form method="GET" action="{{ route('personnages.index') }}" class="mb-8 flex flex-wrap gap-3">
        <input type="text" name="search" value="{{ request('search') }}"
               placeholder="Rechercher un personnage..."
               class="flex-1 min-w-48 px-4 py-2 bg-hub-surface border border-hub-border rounded-lg text-hub-text placeholder-hub-text-sec focus:outline-none focus:ring-2 focus:ring-hub-primary">

        <select name="element" class="px-4 py-2 bg-hub-surface border border-hub-border rounded-lg text-hub-text focus:outline-none focus:ring-2 focus:ring-hub-primary">
            <option value="">Tous les éléments</option>
            @foreach($elements as $el)
                <option value="{{ $el->id_element }}" @selected(request('element') == $el->id_element)>
                    {{ $el->libelle_element }}
                </option>
            @endforeach
        </select>

        <select name="rarete" class="px-4 py-2 bg-hub-surface border border-hub-border rounded-lg text-hub-text focus:outline-none focus:ring-2 focus:ring-hub-primary">
            <option value="">Toutes les raretés</option>
            @foreach($etoiles as $e)
                <option value="{{ $e->id_etoile }}" @selected(request('rarete') == $e->id_etoile)>
                    {{ $e->libelle }}
                </option>
            @endforeach
        </select>

        <select name="sort" class="px-4 py-2 bg-hub-surface border border-hub-border rounded-lg text-hub-text focus:outline-none focus:ring-2 focus:ring-hub-primary">
            <option value="nom_asc" @selected(request('sort') === 'nom_asc' || !request('sort'))>Nom A-Z</option>
            <option value="nom_desc" @selected(request('sort') === 'nom_desc')>Nom Z-A</option>
            <option value="rarete_desc" @selected(request('sort') === 'rarete_desc')>Rareté ↑</option>
            <option value="rarete_asc" @selected(request('sort') === 'rarete_asc')>Rareté ↓</option>
            <option value="element" @selected(request('sort') === 'element')>Élément</option>
        </select>

        <button type="submit"
                class="px-6 py-2 bg-hub-primary hover:bg-hub-primary-hover text-white rounded-lg font-medium transition-colors">
            Filtrer
        </button>
    </form>

    {{-- Grille personnages --}}
    @if($personnages->isEmpty())
        <div class="text-center py-16 text-hub-text-sec">
            <p class="text-lg">Aucun personnage trouvé.</p>
        </div>
    @else
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-4">
            @foreach($personnages as $perso)
                <a href="{{ route('personnages.show', $perso->slug) }}"
                   class="group bg-hub-surface border border-hub-border rounded-xl overflow-hidden hover:border-hub-primary hover:shadow-lg transition-all duration-200">
                    <div class="aspect-square bg-hub-surface-hover overflow-hidden">
                        <img src="{{ $perso->photos->first()?->source_url ?? $perso->photos->first()?->chemin_photo ?? asset('images/placeholder.webp') }}"
                             alt="{{ $perso->nom_perso }}"
                             class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-200">
                    </div>
                    <div class="p-3">
                        <p class="font-semibold text-hub-text text-sm truncate">{{ $perso->nom_perso }}</p>
                        <p class="text-hub-text-sec text-xs mt-1">{{ $perso->element?->libelle_element ?? '—' }}</p>
                        <p class="text-hub-gold text-xs">{{ $perso->etoile?->libelle ?? '' }}</p>
                    </div>
                </a>
            @endforeach
        </div>

        {{-- Pagination --}}
        <div class="mt-8">
            {{ $personnages->links() }}
        </div>
    @endif

</div>
</x-app-layout>
