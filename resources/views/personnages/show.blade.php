<x-app-layout>
    <div class="max-w-[120rem] mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <nav class="mb-6 text-sm text-hub-text-sec">
            <a href="{{ route('personnages.index') }}" class="hover:text-hub-primary">Personnages</a>
            <span class="mx-2">/</span>
            <span class="text-hub-text">{{ $personnage->nom_perso }}</span>
        </nav>

        @include('personnages.partials.book')
    </div>
</x-app-layout>
