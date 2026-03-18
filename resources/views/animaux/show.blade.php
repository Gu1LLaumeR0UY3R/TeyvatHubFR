<x-app-layout>
    <x-slot name="title">{{ $animal->nom_animal }}</x-slot>

    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

        <a href="{{ route('animaux.index') }}"
           class="inline-flex items-center gap-2 text-hub-text-sec hover:text-hub-text mb-6 transition-colors">
            ← Retour aux animaux
        </a>

        {{-- En-tête --}}
        <div class="bg-hub-surface border border-hub-border rounded-2xl p-6 flex flex-col sm:flex-row gap-6 mb-6">
            <img src="{{ $animal->photos->first()?->source_url ?? $animal->photos->first()?->chemin_photo ?? asset('images/placeholder.webp') }}"
                 alt="{{ $animal->nom_animal }}"
                 class="w-32 h-32 object-cover rounded-xl self-start">
            <div class="flex-1">
                <h1 class="text-3xl font-bold text-hub-primary mb-2">{{ $animal->nom_animal }}</h1>
                <span class="px-3 py-1 bg-hub-surface-hover rounded-full text-hub-text-sec text-sm">
                    {{ $animal->typeAnimal?->libelle_TAnimal ?? '—' }}
                </span>
                @if($animal->descri_animal)
                    <p class="text-hub-text-sec mt-3 leading-relaxed">{{ $animal->descri_animal }}</p>
                @endif
            </div>
        </div>

        {{-- Régions --}}
        <div class="bg-hub-surface border border-hub-border rounded-2xl p-6 mb-6">
            <h2 class="text-xl font-semibold text-hub-text mb-4">Régions</h2>
            @if($animal->regions->isEmpty())
                <p class="text-hub-text-sec">Aucune région connue.</p>
            @else
                <div class="flex flex-wrap gap-3">
                    @foreach($animal->regions as $region)
                        <a href="{{ route('regions.show', $region->slug) }}"
                           class="px-4 py-2 bg-hub-surface-hover rounded-full text-hub-text hover:ring-1 hover:ring-hub-primary transition-all">
                            {{ $region->nom_region }}
                        </a>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Ingrédients obtenus --}}
        <div class="bg-hub-surface border border-hub-border rounded-2xl p-6 mb-6">
            <h2 class="text-xl font-semibold text-hub-text mb-4">Ingrédients obtenus</h2>
            @if($animal->ingredients->isEmpty())
                <p class="text-hub-text-sec">Aucun ingrédient connu.</p>
            @else
                <div class="flex flex-wrap gap-3">
                    @foreach($animal->ingredients as $ingredient)
                        <span class="px-4 py-2 bg-hub-surface-hover rounded-full text-hub-text">
                            {{ $ingredient->nom_ingre }}
                        </span>
                    @endforeach
                </div>
            @endif
        </div>

    </div>
</x-app-layout>
