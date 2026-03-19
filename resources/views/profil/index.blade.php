<x-app-layout>
<x-slot name="title">Mon Profil</x-slot>
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    {{-- Bannière + Avatar --}}
    <div class="relative mb-8">
        <div class="w-full h-40 rounded-2xl overflow-hidden bg-hub-surface border border-hub-border">
            @if($user->banniere)
                <img src="{{ $user->banniere }}" alt="Bannière" class="w-full h-full object-cover">
            @else
                <div class="w-full h-full bg-gradient-to-r from-hub-surface to-hub-surface-hover"></div>
            @endif
        </div>
        <div class="absolute -bottom-8 left-6">
            <div class="w-20 h-20 rounded-2xl border-4 border-hub-surface overflow-hidden bg-hub-surface-hover">
                @if($user->avatar)
                    <img src="{{ $user->avatar }}" alt="{{ $user->pseudo ?? $user->name }}" class="w-full h-full object-cover">
                @else
                    <div class="w-full h-full flex items-center justify-center text-hub-text-sec text-3xl">
                        {{ substr($user->pseudo ?? $user->name, 0, 1) }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Infos joueur --}}
    <div class="mt-10 mb-6">
        <h1 class="text-2xl font-bold text-hub-text">{{ $user->pseudo ?? $user->name }}</h1>
        @if($user->bio_joueur)
            <p class="text-hub-text-sec mt-1">{{ $user->bio_joueur }}</p>
        @endif
        <p class="text-hub-text-sec text-sm mt-1">Membre depuis {{ $user->date_inscription ? \Carbon\Carbon::parse($user->date_inscription)->format('d/m/Y') : \Carbon\Carbon::parse($user->created_at)->format('d/m/Y') }}</p>
    </div>

    {{-- Navigation profil --}}
    <nav class="flex gap-4 mb-8 border-b border-hub-border pb-2">
        <a href="{{ route('profil.index') }}" class="text-hub-primary border-b-2 border-hub-primary pb-2 font-medium text-sm">Vue d'ensemble</a>
        <a href="{{ route('profil.personnages') }}" class="text-hub-text-sec hover:text-hub-text pb-2 text-sm">Personnages</a>
        <a href="{{ route('profil.armes') }}" class="text-hub-text-sec hover:text-hub-text pb-2 text-sm">Armes</a>
        <a href="{{ route('profil.parametres') }}" class="text-hub-text-sec hover:text-hub-text pb-2 text-sm">Paramètres</a>
        <a href="{{ route('profil.amis') }}" class="text-hub-text-sec hover:text-hub-text pb-2 text-sm">Amis</a>
    </nav>

    {{-- Statistiques --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
        <div class="bg-hub-surface border border-hub-border rounded-xl p-4 text-center">
            <p class="text-2xl font-bold text-hub-primary">{{ $stats['persos_possedes'] }}</p>
            <p class="text-hub-text-sec text-sm mt-1">Personnages</p>
        </div>
        <div class="bg-hub-surface border border-hub-border rounded-xl p-4 text-center">
            <p class="text-2xl font-bold text-hub-primary">{{ $stats['armes_possedees'] }}</p>
            <p class="text-hub-text-sec text-sm mt-1">Armes</p>
        </div>
        <div class="bg-hub-surface border border-hub-border rounded-xl p-4 text-center">
            <p class="text-2xl font-bold text-hub-primary">{{ $stats['constellations_debloquees'] }}</p>
            <p class="text-hub-text-sec text-sm mt-1">Constellations</p>
        </div>
        <div class="bg-hub-surface border border-hub-border rounded-xl p-4 text-center">
            <p class="text-2xl font-bold text-hub-primary">{{ $stats['persos_c6'] }}</p>
            <p class="text-hub-text-sec text-sm mt-1">Persos C6</p>
        </div>
    </div>

</div>
</x-app-layout>
