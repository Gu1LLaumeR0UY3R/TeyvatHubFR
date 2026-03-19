<x-app-layout>
<x-slot name="title">Paramètres du profil</x-slot>
<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    <nav class="flex gap-4 mb-8 border-b border-hub-border pb-2">
        <a href="{{ route('profil.index') }}" class="text-hub-text-sec hover:text-hub-text pb-2 text-sm">Vue d'ensemble</a>
        <a href="{{ route('profil.personnages') }}" class="text-hub-text-sec hover:text-hub-text pb-2 text-sm">Personnages</a>
        <a href="{{ route('profil.armes') }}" class="text-hub-text-sec hover:text-hub-text pb-2 text-sm">Armes</a>
        <a href="{{ route('profil.parametres') }}" class="text-hub-primary border-b-2 border-hub-primary pb-2 font-medium text-sm">Paramètres</a>
        <a href="{{ route('profil.amis') }}" class="text-hub-text-sec hover:text-hub-text pb-2 text-sm">Amis</a>
    </nav>

    <h1 class="text-2xl font-bold text-hub-text mb-6">Paramètres du profil</h1>

    {{-- Import UID --}}
    <div class="bg-hub-surface border border-hub-border rounded-2xl p-6 mb-6">
        <h2 class="text-lg font-bold text-hub-text mb-2">Import UID Genshin</h2>
        <p class="text-hub-text-sec text-sm mb-4">Synchronisez vos données depuis Enka.Network (showcase doit être activé en jeu).</p>
        <form method="POST" action="{{ route('profil.import-uid') }}">
            @csrf
            <div class="flex gap-3">
                <input type="text"
                       name="uid"
                       value="{{ $user->uid_genshin }}"
                       placeholder="Votre UID Genshin (9 chiffres)"
                       class="flex-1 bg-hub-surface-hover border border-hub-border rounded-xl px-4 py-2 text-hub-text focus:outline-none focus:border-hub-primary">
                <button type="submit" class="px-5 py-2 bg-hub-primary text-white rounded-xl hover:bg-opacity-90 font-medium">
                    Importer
                </button>
            </div>
            @error('uid')
                <p class="text-red-400 text-sm mt-2">{{ $message }}</p>
            @enderror
        </form>
        @if(session('import_success'))
            <p class="text-green-400 text-sm mt-3">{{ session('import_success') }}</p>
        @endif
        @if(session('import_error'))
            <p class="text-red-400 text-sm mt-3">{{ session('import_error') }}</p>
        @endif
    </div>

    {{-- Infos du compte --}}
    <div class="bg-hub-surface border border-hub-border rounded-2xl p-6">
        <h2 class="text-lg font-bold text-hub-text mb-4">Informations du compte</h2>
        <dl class="space-y-3">
            <div class="flex justify-between">
                <dt class="text-hub-text-sec text-sm">Pseudo</dt>
                <dd class="text-hub-text text-sm font-medium">{{ $user->pseudo ?? $user->name }}</dd>
            </div>
            <div class="flex justify-between">
                <dt class="text-hub-text-sec text-sm">E-mail</dt>
                <dd class="text-hub-text text-sm font-medium">{{ $user->email }}</dd>
            </div>
            @if($user->uid_genshin)
                <div class="flex justify-between">
                    <dt class="text-hub-text-sec text-sm">UID Genshin</dt>
                    <dd class="text-hub-text text-sm font-medium">{{ $user->uid_genshin }}</dd>
                </div>
            @endif
        </dl>
    </div>

</div>
</x-app-layout>
