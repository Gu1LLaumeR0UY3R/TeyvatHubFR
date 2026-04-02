<x-app-layout>
<x-slot name="title">Matériaux</x-slot>
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    <div class="mb-8">
        <h1 class="text-3xl font-bold text-hub-text mb-2">Matériaux</h1>
        <p class="text-hub-text-sec">{{ $materiaux->total() }} matériau(x) trouvé(s)</p>
    </div>

    <form method="GET" action="{{ route('materiaux.index') }}" class="mb-8 flex flex-wrap gap-3">
        <input type="text" name="search" value="{{ request('search') }}"
               placeholder="Rechercher un matériau..."
               class="flex-1 min-w-48 px-4 py-2 bg-hub-surface border border-hub-border rounded-lg text-hub-text placeholder-hub-text-sec focus:outline-none focus:ring-2 focus:ring-hub-primary">

        <select name="type" class="px-4 py-2 bg-hub-surface border border-hub-border rounded-lg text-hub-text focus:outline-none focus:ring-2 focus:ring-hub-primary">
            <option value="">Tous les types</option>
            @foreach($types as $type)
                <option value="{{ $type->id_typeM }}" @selected(request('type') == $type->id_typeM)>
                    {{ $type->libelle_TypeM }}
                </option>
            @endforeach
        </select>

        <button type="submit"
                class="px-6 py-2 bg-hub-primary hover:bg-hub-primary-hover text-white rounded-lg font-medium transition-colors">
            Filtrer
        </button>
    </form>

    @if($materiaux->isEmpty())
        <div class="text-center py-16 text-hub-text-sec">
            <p class="text-lg">Aucun matériau trouvé.</p>
        </div>
    @else
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-4">
            @foreach($materiaux as $mat)
                <a href="{{ route('materiaux.show', $mat->slug) }}"
                   class="group bg-hub-surface border border-hub-border rounded-xl overflow-hidden hover:border-hub-primary hover:shadow-lg transition-all duration-200">
                    <div class="aspect-square bg-hub-surface-hover overflow-hidden p-4">
                        <img src="{{ $mat->photos->first()?->source_url ?? $mat->photos->first()?->chemin_photo ?? asset('images/placeholder.svg') }}"
                             alt="{{ $mat->nom_mat }}"
                             class="w-full h-full object-contain group-hover:scale-105 transition-transform duration-200">
                    </div>
                    <div class="p-3">
                        <p class="font-semibold text-hub-text text-sm truncate">{{ $mat->nom_mat }}</p>
                        <p class="text-hub-text-sec text-xs mt-1">{{ $mat->typeMateriaux?->libelle_TypeM ?? '—' }}</p>
                        @if($mat->rarete)
                            <p class="text-hub-gold text-xs">{{ $mat->rarete->libelle_rareté }}</p>
                        @endif
                    </div>
                </a>
            @endforeach
        </div>

        <div class="mt-8">
            {{ $materiaux->links() }}
        </div>
    @endif

</div>
</x-app-layout>
