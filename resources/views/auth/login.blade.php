<x-guest-layout>
    <x-slot name="title">Connexion</x-slot>

    <h2 class="text-xl font-bold text-hub-text mb-6">Connexion</h2>

    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="space-y-5">
        @csrf

        {{-- Email --}}
        <div>
            <label for="email" class="block text-sm font-medium text-hub-text-sec mb-1.5">Adresse e-mail</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}"
                   required autofocus autocomplete="username"
                   class="block w-full rounded-xl bg-hub-bg border border-hub-border text-hub-text placeholder-hub-text-sec px-4 py-2.5 text-sm focus:outline-none focus:border-hub-primary focus:ring-1 focus:ring-hub-primary transition-colors" />
            <x-input-error :messages="$errors->get('email')" class="mt-1.5" />
        </div>

        {{-- Mot de passe --}}
        <div>
            <div class="flex items-center justify-between mb-1.5">
                <label for="password" class="text-sm font-medium text-hub-text-sec">Mot de passe</label>
                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}"
                       class="text-xs text-hub-primary hover:text-hub-accent transition-colors">
                        Mot de passe oublié ?
                    </a>
                @endif
            </div>
            <input id="password" type="password" name="password"
                   required autocomplete="current-password"
                   class="block w-full rounded-xl bg-hub-bg border border-hub-border text-hub-text px-4 py-2.5 text-sm focus:outline-none focus:border-hub-primary focus:ring-1 focus:ring-hub-primary transition-colors" />
            <x-input-error :messages="$errors->get('password')" class="mt-1.5" />
        </div>

        {{-- Se souvenir de moi --}}
        <div class="flex items-center gap-2">
            <input id="remember_me" type="checkbox" name="remember"
                   class="rounded border-hub-border bg-hub-bg text-hub-primary focus:ring-hub-primary" />
            <label for="remember_me" class="text-sm text-hub-text-sec">Se souvenir de moi</label>
        </div>

        {{-- Bouton --}}
        <button type="submit"
                class="w-full py-2.5 px-4 bg-hub-primary hover:bg-hub-primary-hover text-hub-bg font-semibold text-sm rounded-xl transition-colors duration-150">
            Se connecter
        </button>
    </form>

    {{-- Lien inscription --}}
    <p class="mt-6 text-center text-sm text-hub-text-sec">
        Pas encore inscrit ?
        <a href="{{ route('register') }}" class="text-hub-primary hover:text-hub-accent font-medium transition-colors">
            Créer un compte
        </a>
    </p>
</x-guest-layout>
