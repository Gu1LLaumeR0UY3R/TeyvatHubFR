<x-guest-layout>
    <x-slot name="title">Vérification 2FA</x-slot>

    <h2 class="text-xl font-bold text-hub-text mb-6">Double authentification</h2>
    <p class="text-sm text-hub-text-sec mb-5">
        Entre le code à 6 chiffres généré par ton application d'authentification.
    </p>

    @if($errors->any())
        <div class="mb-4 p-3 bg-red-900/30 border border-red-700 rounded-xl">
            @foreach($errors->all() as $error)
                <p class="text-red-400 text-sm">{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <form method="POST" action="{{ route('twofactor.challenge.verify') }}" class="space-y-5">
        @csrf
        <div>
            <label for="code" class="block text-sm font-medium text-hub-text-sec mb-1.5">Code 2FA</label>
            <input id="code" name="code" type="text" inputmode="numeric" pattern="[0-9]*" maxlength="6" required autofocus
                   class="block w-full rounded-xl bg-hub-bg border border-hub-border text-hub-text px-4 py-2.5 text-sm focus:outline-none focus:border-hub-primary focus:ring-1 focus:ring-hub-primary" />
        </div>

        <button type="submit"
                class="w-full py-2.5 px-4 bg-hub-primary hover:bg-hub-primary-hover text-hub-bg font-semibold text-sm rounded-xl transition-colors duration-150">
            Vérifier
        </button>
    </form>
</x-guest-layout>
