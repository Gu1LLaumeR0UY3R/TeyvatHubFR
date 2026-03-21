<x-app-layout>
<x-slot name="title">Histoire de Teyvat</x-slot>
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    <h1 class="text-3xl font-bold text-hub-text mb-2">Histoire de Teyvat</h1>
    <p class="text-hub-text-sec mb-8">Explorez la chronologie et les nations du monde de Teyvat.</p>

    {{-- Chronologie --}}
    @if($chronologie->count())
        <section class="mb-10">
            <h2 class="text-2xl font-bold text-hub-text mb-6">Chronologie</h2>
            <div x-data="{ selected: 0 }" class="overflow-hidden">
                {{-- Barre horizontale de navigation --}}
                <div class="flex gap-2 overflow-x-auto pb-2 mb-6 scrollbar-thin scrollbar-thumb-hub-border">
                    @foreach($chronologie as $index => $event)
                        <button @click="selected = {{ $index }}"
                                :class="selected === {{ $index }} ? 'border-hub-primary bg-hub-surface-hover text-hub-primary' : 'border-hub-border text-hub-text-sec'"
                                class="flex-shrink-0 px-4 py-2 border rounded-xl text-sm font-medium transition-colors hover:border-hub-primary hover:text-hub-primary whitespace-nowrap">
                            {{ $event->periode ?? 'Ère ' . ($index + 1) }}
                        </button>
                    @endforeach
                </div>

                {{-- Contenu de l'événement sélectionné --}}
                @foreach($chronologie as $index => $event)
                    <div x-show="selected === {{ $index }}"
                         class="bg-hub-surface border border-hub-border rounded-2xl p-6">
                        <div class="flex flex-col sm:flex-row gap-4">
                            @if($event->photos->first())
                                <img src="{{ $event->photos->first()->source_url ?? $event->photos->first()->chemin_photo }}"
                                     alt="{{ $event->titre }}"
                                     class="w-full sm:w-48 h-32 object-cover rounded-xl">
                            @endif
                            <div class="flex-1">
                                <div class="flex items-center gap-3 mb-2">
                                    @if($event->periode)
                                        <span class="px-2 py-0.5 bg-hub-primary bg-opacity-20 text-hub-primary rounded text-xs font-medium">{{ $event->periode }}</span>
                                    @endif
                                    @if($event->nation)
                                        <span class="text-hub-text-sec text-xs">{{ $event->nation->nom_region }}</span>
                                    @endif
                                </div>
                                <h3 class="text-xl font-bold text-hub-text mb-2">{{ $event->titre }}</h3>
                                @if($event->resume)
                                    <p class="text-hub-text-sec leading-relaxed">{{ $event->resume }}</p>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>
    @endif

    {{-- Nations / Régions --}}
    <section>
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-2xl font-bold text-hub-text">Nations de Teyvat</h2>
            <a href="{{ route('nations.index') }}" class="text-hub-primary hover:underline text-sm">Voir toutes →</a>
        </div>

        @if($nations->count())
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                @foreach($nations as $nation)
                    <a href="{{ route('nations.show', $nation->slug) }}"
                       class="bg-hub-surface border border-hub-border rounded-2xl p-4 hover:border-hub-primary hover:bg-hub-surface-hover transition-all flex flex-col items-center gap-3">
                        <img src="{{ $nation->photos->first()?->source_url ?? $nation->photos->first()?->chemin_photo ?? asset('images/placeholder.webp') }}"
                             alt="{{ $nation->nom_region }}"
                             class="w-20 h-20 object-contain">
                        <span class="text-hub-text font-semibold text-center">{{ $nation->nom_region }}</span>
                    </a>
                @endforeach
            </div>
        @else
            <p class="text-hub-text-sec text-center py-12">Aucune région disponible.</p>
        @endif
    </section>

</div>
</x-app-layout>
