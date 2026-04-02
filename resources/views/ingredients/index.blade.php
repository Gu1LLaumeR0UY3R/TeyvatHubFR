<x-app-layout>
<x-slot name="title">Ingrédients</x-slot>
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    <h1 class="text-3xl font-bold text-hub-text mb-6">Ingrédients</h1>

    {{-- Filtres --}}
    <form method="GET" action="{{ route('ingredients.index') }}" class="mb-6">
        <div class="flex flex-wrap gap-3">
            <input type="text"
                   name="search"
                   value="{{ request('search') }}"
                   placeholder="Rechercher un ingrédient..."
                   class="flex-1 min-w-48 bg-hub-surface border border-hub-border rounded-xl px-4 py-2 text-hub-text placeholder-hub-text-sec focus:outline-none focus:border-hub-primary">
            <button type="submit"
                    class="px-5 py-2 bg-hub-primary text-white rounded-xl hover:bg-opacity-90 transition-colors font-medium">
                Rechercher
            </button>
            @if(request()->hasAny(['search']))
                <a href="{{ route('ingredients.index') }}"
                   class="px-5 py-2 bg-hub-surface border border-hub-border rounded-xl text-hub-text hover:bg-hub-surface-hover transition-colors">
                    Réinitialiser
                </a>
            @endif
        </div>
    </form>

    {{-- Grille --}}
    @if($ingredients->isEmpty())
        <p class="text-hub-text-sec text-center py-12">Aucun ingrédient trouvé.</p>
    @else
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-4 mb-6">
            @foreach($ingredients as $ingredient)
                <a href="{{ route('ingredients.show', $ingredient->slug) }}"
                   class="bg-hub-surface border border-hub-border rounded-xl p-3 hover:border-hub-primary hover:bg-hub-surface-hover transition-all flex flex-col items-center gap-2">
                    <img src="{{ $ingredient->photos->first()?->source_url ?? $ingredient->photos->first()?->chemin_photo ?? asset('images/placeholder.svg') }}"
                         alt="{{ $ingredient->nom_ingre }}"
                         class="w-16 h-16 object-contain">
                    <span class="text-hub-text text-xs font-medium text-center leading-tight">{{ $ingredient->nom_ingre }}</span>
                </a>
            @endforeach
        </div>

        {{ $ingredients->links() }}
    @endif

</div>
</x-app-layout>
