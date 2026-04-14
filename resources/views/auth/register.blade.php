<x-guest-layout>
    <x-slot name="title">Inscription</x-slot>

    <h2 class="text-xl font-bold text-hub-text mb-6">Créer un compte</h2>

    <form method="POST" action="{{ route('register') }}" class="space-y-5">
        @csrf

        {{-- Pseudo --}}
        <div>
            <label for="name" class="block text-sm font-medium text-hub-text-sec mb-1.5">Pseudo</label>
            <input id="name" type="text" name="name" value="{{ old('name') }}"
                   required autofocus autocomplete="name"
                   class="block w-full rounded-xl bg-hub-bg border border-hub-border text-hub-text placeholder-hub-text-sec px-4 py-2.5 text-sm focus:outline-none focus:border-hub-primary focus:ring-1 focus:ring-hub-primary transition-colors" />
            <x-input-error :messages="$errors->get('name')" class="mt-1.5" />
        </div>

        {{-- Email --}}
        <div>
            <label for="email" class="block text-sm font-medium text-hub-text-sec mb-1.5">Adresse e-mail</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}"
                   required autocomplete="username"
                   class="block w-full rounded-xl bg-hub-bg border border-hub-border text-hub-text placeholder-hub-text-sec px-4 py-2.5 text-sm focus:outline-none focus:border-hub-primary focus:ring-1 focus:ring-hub-primary transition-colors" />
            <x-input-error :messages="$errors->get('email')" class="mt-1.5" />
        </div>

        {{-- Mot de passe --}}
        <div>
            <label for="password" class="block text-sm font-medium text-hub-text-sec mb-1.5">Mot de passe</label>
            <input id="password" type="password" name="password"
                   required autocomplete="new-password"
                   class="block w-full rounded-xl bg-hub-bg border border-hub-border text-hub-text px-4 py-2.5 text-sm focus:outline-none focus:border-hub-primary focus:ring-1 focus:ring-hub-primary transition-colors" />
            <x-input-error :messages="$errors->get('password')" class="mt-1.5" />
        </div>

        {{-- Confirmation --}}
        <div>
            <label for="password_confirmation" class="block text-sm font-medium text-hub-text-sec mb-1.5">Confirmer le mot de passe</label>
            <input id="password_confirmation" type="password" name="password_confirmation"
                   required autocomplete="new-password"
                   class="block w-full rounded-xl bg-hub-bg border border-hub-border text-hub-text px-4 py-2.5 text-sm focus:outline-none focus:border-hub-primary focus:ring-1 focus:ring-hub-primary transition-colors" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-1.5" />
        </div>

        {{-- Bouton --}}
        <button type="submit"
                class="w-full py-2.5 px-4 bg-hub-primary hover:bg-hub-primary-hover text-hub-bg font-semibold text-sm rounded-xl transition-colors duration-150">
            Créer mon compte
        </button>
    </form>

    {{-- Lien connexion --}}
    <p class="mt-6 text-center text-sm text-hub-text-sec">
        Déjà inscrit ?
        <a href="{{ route('login') }}" class="text-hub-primary hover:text-hub-accent font-medium transition-colors">
            Se connecter
        </a>
    </p>
</x-guest-layout>
