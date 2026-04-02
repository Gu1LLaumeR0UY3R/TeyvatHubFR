<x-app-layout>
<x-slot name="title">Cuisine</x-slot>
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    <div class="mb-8">
        <h1 class="text-3xl font-bold text-hub-text mb-2">Cuisine</h1>
        <p class="text-hub-text-sec">{{ $plats->total() }} plat(s) trouvé(s)</p>
    </div>

    <form method="GET" action="{{ route('cuisine.index') }}" class="mb-8 flex flex-wrap gap-3">
        <input type="text" name="search" value="{{ request('search') }}"
               placeholder="Rechercher un plat..."
               class="flex-1 min-w-48 px-4 py-2 bg-hub-surface border border-hub-border rounded-lg text-hub-text placeholder-hub-text-sec focus:outline-none focus:ring-2 focus:ring-hub-primary">

        <select name="rarete" class="px-4 py-2 bg-hub-surface border border-hub-border rounded-lg text-hub-text focus:outline-none focus:ring-2 focus:ring-hub-primary">
            <option value="">Toutes les raretés</option>
            @foreach($raretés as $r)
                <option value="{{ $r->{'id_rareté'} }}" @selected(request('rarete') == $r->{'id_rareté'})>
                    {{ $r->{'libelle_rareté'} }}
                </option>
            @endforeach
        </select>

        <select name="sort" class="px-4 py-2 bg-hub-surface border border-hub-border rounded-lg text-hub-text focus:outline-none focus:ring-2 focus:ring-hub-primary">
            <option value="nom_asc" @selected(!request('sort') || request('sort') === 'nom_asc')>Nom A-Z</option>
            <option value="nom_desc" @selected(request('sort') === 'nom_desc')>Nom Z-A</option>
            <option value="rarete_desc" @selected(request('sort') === 'rarete_desc')>Rareté ↑</option>
            <option value="rarete_asc" @selected(request('sort') === 'rarete_asc')>Rareté ↓</option>
        </select>

        <button type="submit"
                class="px-6 py-2 bg-hub-primary hover:bg-hub-primary-hover text-white rounded-lg font-medium transition-colors">
            Filtrer
        </button>
    </form>

    @if($plats->isEmpty())
        <div class="text-center py-16 text-hub-text-sec">
            <p class="text-lg">Aucun plat trouvé.</p>
        </div>
    @else
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
            @foreach($plats as $plat)
                <a href="{{ route('cuisine.show', $plat->slug) }}"
                   class="group bg-hub-surface border border-hub-border rounded-xl overflow-hidden hover:border-hub-primary hover:shadow-lg transition-all duration-200">
                    <div class="aspect-square bg-hub-surface-hover overflow-hidden p-4">
                        <img src="{{ $plat->photos->first()?->source_url ?? $plat->photos->first()?->chemin_photo ?? asset('images/placeholder.svg') }}"
                             alt="{{ $plat->nom_plat }}"
                             class="w-full h-full object-contain group-hover:scale-105 transition-transform duration-200">
                    </div>
                    <div class="p-3">
                        <p class="font-semibold text-hub-text text-sm truncate">{{ $plat->nom_plat }}</p>
                        @if($plat->rarete)
                            <p class="text-hub-gold text-xs mt-1">{{ $plat->rarete->{'libelle_rareté'} }}</p>
                        @endif
                        @if($plat->specialite)
                            <span class="inline-block mt-1 px-2 py-0.5 bg-hub-primary/20 text-hub-primary text-xs rounded">
                                Spécialité
                            </span>
                        @endif
                    </div>
                </a>
            @endforeach
        </div>

        <div class="mt-8">
            {{ $plats->links() }}
        </div>
    @endif

</div>
</x-app-layout>
