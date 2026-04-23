<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900">Double authentification (2FA)</h2>
        <p class="mt-1 text-sm text-gray-600">Sécurise ton compte avec un code temporaire (Google Authenticator, Authy...).</p>
    </header>

    @if (session('status') === 'twofactor-enabled')
        <div class="mt-3 rounded border border-emerald-300 bg-emerald-50 px-3 py-2 text-sm text-emerald-700">2FA activée.</div>
    @endif

    @if (session('status') === 'twofactor-disabled')
        <div class="mt-3 rounded border border-amber-300 bg-amber-50 px-3 py-2 text-sm text-amber-700">2FA désactivée.</div>
    @endif

    @if(!$user->two_factor_enabled)
        <div class="mt-4 rounded-lg border border-gray-200 p-4">
            <p class="text-sm text-gray-700 mb-3">1. Scanne ce QR code dans ton app d'authentification.</p>
            @if(!empty($qrCodeSvg))
                <div class="inline-block bg-white p-2 rounded border">{!! $qrCodeSvg !!}</div>
            @endif
            <p class="text-xs text-gray-500 mt-2">Code manuel: <span class="font-mono">{{ $manualSecret }}</span></p>

            <form method="POST" action="{{ route('twofactor.enable') }}" class="mt-4 space-y-3">
                @csrf
                <div>
                    <label class="block text-sm text-gray-700 mb-1">2. Entre le code 6 chiffres pour confirmer</label>
                    <input type="text" name="code" maxlength="6" inputmode="numeric" pattern="[0-9]*" required
                           class="w-full max-w-xs rounded border-gray-300">
                    @error('code')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
                </div>
                <x-primary-button>Activer la 2FA</x-primary-button>
            </form>
        </div>
    @else
        <div class="mt-4 rounded-lg border border-emerald-300 bg-emerald-50 p-4">
            <p class="text-sm text-emerald-800">2FA active depuis le {{ optional($user->two_factor_confirmed_at)->format('d/m/Y H:i') }}.</p>
            <form method="POST" action="{{ route('twofactor.disable') }}" class="mt-3 space-y-3">
                @csrf
                <div>
                    <label class="block text-sm text-gray-700 mb-1">Mot de passe pour désactiver</label>
                    <input type="password" name="password" required class="w-full max-w-xs rounded border-gray-300">
                    @error('password')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
                </div>
                <x-danger-button>Désactiver la 2FA</x-danger-button>
            </form>
        </div>
    @endif
</section>
