<x-app-layout>
    <x-slot name="title">{{ $ingredient->nom_ingre }}</x-slot>

    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

        <a href="{{ route('ingredients.index') }}"
           class="inline-flex items-center gap-2 text-hub-text-sec hover:text-hub-text mb-6 transition-colors">
            ← Retour aux ingrédients
        </a>

        {{-- En-tête --}}
        <div class="bg-hub-surface border border-hub-border rounded-2xl p-6 flex flex-col sm:flex-row gap-6 mb-6">
            <img src="{{ $ingredient->photos->first()?->source_url ?? $ingredient->photos->first()?->chemin_photo ?? asset('images/placeholder.webp') }}"
                 alt="{{ $ingredient->nom_ingre }}"
                 class="w-32 h-32 object-cover rounded-xl self-start">
            <div class="flex-1">
                <h1 class="text-3xl font-bold text-hub-primary mb-2">{{ $ingredient->nom_ingre }}</h1>
            </div>
        </div>

        {{-- Plats utilisant cet ingrédient --}}
        <div class="bg-hub-surface border border-hub-border rounded-2xl p-6 mb-6">
            <h2 class="text-xl font-semibold text-hub-text mb-4">Plats utilisant cet ingrédient</h2>
            @if($ingredient->plats->isEmpty())
                <p class="text-hub-text-sec">Aucun plat connu.</p>
            @else
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4">
                    @foreach($ingredient->plats as $plat)
                        <a href="{{ route('cuisine.show', $plat->slug) }}"
                           class="bg-hub-surface-hover rounded-xl p-3 hover:ring-1 hover:ring-hub-primary transition-all text-center">
                            <img src="{{ $plat->photos->first()?->source_url ?? $plat->photos->first()?->chemin_photo ?? asset('images/placeholder.webp') }}"
                                 alt="{{ $plat->nom_plat }}"
                                 class="w-16 h-16 object-cover rounded-lg mx-auto mb-2">
                            <p class="text-hub-text text-sm font-medium truncate">{{ $plat->nom_plat }}</p>
                        </a>
                    @endforeach
                </div>
            @endif
        </div>

    </div>
</x-app-layout>
