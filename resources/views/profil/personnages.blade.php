<x-app-layout>
<x-slot name="title">Mes Personnages</x-slot>
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    <nav class="flex gap-4 mb-8 border-b border-hub-border pb-2">
        <a href="{{ route('profil.index') }}" class="text-hub-text-sec hover:text-hub-text pb-2 text-sm">Vue d'ensemble</a>
        <a href="{{ route('profil.personnages') }}" class="text-hub-primary border-b-2 border-hub-primary pb-2 font-medium text-sm">Personnages</a>
        <a href="{{ route('profil.armes') }}" class="text-hub-text-sec hover:text-hub-text pb-2 text-sm">Armes</a>
        <a href="{{ route('profil.parametres') }}" class="text-hub-text-sec hover:text-hub-text pb-2 text-sm">Paramètres</a>
        <a href="{{ route('profil.amis') }}" class="text-hub-text-sec hover:text-hub-text pb-2 text-sm">Amis</a>
    </nav>

    <h1 class="text-2xl font-bold text-hub-text mb-6">Mes Personnages ({{ $personnages->total() }})</h1>

    @if($personnages->isEmpty())
        <div class="text-center py-12">
            <p class="text-hub-text-sec mb-4">Aucun personnage. Importez votre UID Genshin pour synchroniser.</p>
            <a href="{{ route('profil.parametres') }}" class="px-4 py-2 bg-hub-primary text-white rounded-xl">Importer mon UID</a>
        </div>
    @else
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4 mb-6">
            @foreach($personnages as $personnage)
                <a href="{{ route('personnages.show', $personnage->slug) }}"
                   class="bg-hub-surface border border-hub-border rounded-xl p-3 hover:border-hub-primary transition-all flex flex-col items-center gap-2">
                    <img src="{{ $personnage->photos->first()?->source_url ?? $personnage->photos->first()?->chemin_photo ?? asset('images/placeholder.webp') }}"
                         alt="{{ $personnage->nom_perso }}"
                         class="w-16 h-16 rounded-lg object-contain">
                    <span class="text-hub-text text-xs font-medium text-center">{{ $personnage->nom_perso }}</span>
                    @if($personnage->element)
                        <span class="px-2 py-0.5 bg-hub-surface-hover rounded text-xs text-hub-text-sec">{{ $personnage->element->libelle_element }}</span>
                    @endif
                    <span class="text-hub-text-sec text-xs">Niv. {{ $personnage->pivot->niveau }}</span>
                </a>
            @endforeach
        </div>

        {{ $personnages->links() }}
    @endif

</div>
</x-app-layout>
