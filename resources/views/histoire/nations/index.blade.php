<x-app-layout>
<x-slot name="title">Nations de Teyvat</x-slot>
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    <nav class="mb-6 text-sm text-hub-text-sec">
        <a href="{{ route('histoire.index') }}" class="hover:text-hub-primary">Histoire</a>
        <span class="mx-2">/</span>
        <span class="text-hub-text">Régions</span>
    </nav>

    <h1 class="text-3xl font-bold text-hub-text mb-8">Nations de Teyvat</h1>

    @if($nations->isEmpty())
        <p class="text-hub-text-sec text-center py-12">Aucune nation disponible.</p>
    @else
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
            @foreach($nations as $nation)
                <a href="{{ route('nations.show', $nation->slug) }}"
                   class="bg-hub-surface border border-hub-border rounded-2xl p-5 hover:border-hub-primary hover:bg-hub-surface-hover transition-all flex flex-col items-center gap-3">
                    <img src="{{ $nation->photos->first()?->source_url ?? $nation->photos->first()?->chemin_photo ?? asset('images/placeholder.svg') }}"
                         alt="{{ $nation->nom_region }}"
                         class="w-24 h-24 object-contain">
                    <span class="text-hub-text font-semibold text-center">{{ $nation->nom_region }}</span>
                    @if($nation->descri_region)
                        <p class="text-hub-text-sec text-xs text-center line-clamp-2">{{ $nation->descri_region }}</p>
                    @endif
                </a>
            @endforeach
        </div>
    @endif

</div>
</x-app-layout>
