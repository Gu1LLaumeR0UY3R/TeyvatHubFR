<x-app-layout>
<x-slot name="title">Mes Amis</x-slot>
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    <nav class="flex gap-4 mb-8 border-b border-hub-border pb-2">
        <a href="{{ route('profil.index') }}" class="text-hub-text-sec hover:text-hub-text pb-2 text-sm">Vue d'ensemble</a>
        <a href="{{ route('profil.personnages') }}" class="text-hub-text-sec hover:text-hub-text pb-2 text-sm">Personnages</a>
        <a href="{{ route('profil.armes') }}" class="text-hub-text-sec hover:text-hub-text pb-2 text-sm">Armes</a>
        <a href="{{ route('profil.parametres') }}" class="text-hub-text-sec hover:text-hub-text pb-2 text-sm">Paramètres</a>
        <a href="{{ route('profil.amis') }}" class="text-hub-primary border-b-2 border-hub-primary pb-2 font-medium text-sm">Amis</a>
    </nav>

    <h1 class="text-2xl font-bold text-hub-text mb-6">Mes Amis ({{ $amis->count() }})</h1>

    {{-- Demandes reçues --}}
    @if($demandesRecues->count() > 0)
        <div class="bg-hub-surface border border-hub-border rounded-2xl p-5 mb-6">
            <h2 class="text-lg font-bold text-hub-text mb-3">Demandes reçues ({{ $demandesRecues->count() }})</h2>
            <div class="space-y-2">
                @foreach($demandesRecues as $demande)
                    <div class="flex items-center justify-between p-3 bg-hub-surface-hover rounded-xl">
                        <div class="flex items-center gap-3">
                            @if($demande->demandeur?->avatar)
                                <img src="{{ $demande->demandeur->avatar }}"
                                     alt="{{ $demande->demandeur->pseudo ?? $demande->demandeur->name }}"
                                     class="w-10 h-10 rounded-full object-cover">
                            @else
                                <div class="w-10 h-10 rounded-full bg-hub-surface flex items-center justify-center text-hub-text font-bold">
                                    {{ substr($demande->demandeur->pseudo ?? $demande->demandeur->name ?? '?', 0, 1) }}
                                </div>
                            @endif
                            <span class="text-hub-text font-medium">{{ $demande->demandeur->pseudo ?? $demande->demandeur->name }}</span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Liste d'amis --}}
    @if($amis->isEmpty())
        <div class="bg-hub-surface border border-hub-border rounded-2xl p-8 text-center">
            <p class="text-hub-text-sec">Aucun ami pour l'instant.</p>
        </div>
    @else
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
            @foreach($amis as $ami)
                @if($ami)
                    <div class="bg-hub-surface border border-hub-border rounded-xl p-4 flex items-center gap-3">
                        @if($ami->avatar)
                            <img src="{{ $ami->avatar }}"
                                 alt="{{ $ami->pseudo ?? $ami->name }}"
                                 class="w-12 h-12 rounded-full object-cover">
                        @else
                            <div class="w-12 h-12 rounded-full bg-hub-surface-hover flex items-center justify-center text-hub-text font-bold text-lg">
                                {{ substr($ami->pseudo ?? $ami->name ?? '?', 0, 1) }}
                            </div>
                        @endif
                        <div>
                            <p class="text-hub-text font-medium">{{ $ami->pseudo ?? $ami->name }}</p>
                            @if($ami->uid_genshin)
                                <p class="text-hub-text-sec text-xs">UID: {{ $ami->uid_genshin }}</p>
                            @endif
                        </div>
                    </div>
                @endif
            @endforeach
        </div>
    @endif

</div>
</x-app-layout>
