<x-admin-layout>
    <x-slot name="title">Sécurité 2FA</x-slot>

    <div class="max-w-2xl">
        <h1 class="text-2xl font-bold text-hub-gold mb-2">Double authentification admin</h1>
        <p class="text-sm text-slate-500 mb-6">Recommandé pour les comptes à privilèges.</p>

        @if(session('success'))
            <div class="mb-4 rounded border border-emerald-300 bg-emerald-50 px-3 py-2 text-sm text-emerald-700">{{ session('success') }}</div>
        @endif

        @if(!$adminUser->two_factor_enabled)
            <div class="rounded-xl border border-slate-200 bg-white p-5">
                <p class="text-sm text-slate-700 mb-3">1. Scanne le QR code dans ton application d'authentification.</p>
                @if(!empty($qrCodeSvg))
                    <div class="inline-block rounded border bg-white p-2">{!! $qrCodeSvg !!}</div>
                @endif
                <p class="mt-2 text-xs text-slate-500">Code manuel: <span class="font-mono">{{ $manualSecret }}</span></p>

                <form method="POST" action="{{ route('admin.twofactor.enable') }}" class="mt-4 space-y-3">
                    @csrf
                    <div>
                        <label class="block text-sm text-slate-700 mb-1">2. Entre le code 6 chiffres</label>
                        <input type="text" name="code" maxlength="6" inputmode="numeric" pattern="[0-9]*" required
                               class="w-full max-w-xs rounded border border-slate-300 px-3 py-2">
                        @error('code')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
                    </div>
                    <button type="submit" class="px-4 py-2 bg-hub-gold text-hub-bg rounded hover:opacity-90">Activer la 2FA</button>
                </form>
            </div>
        @else
            <div class="rounded-xl border border-emerald-300 bg-emerald-50 p-5">
                <p class="text-sm text-emerald-800">2FA active depuis le {{ optional($adminUser->two_factor_confirmed_at)->format('d/m/Y H:i') }}.</p>

                <form method="POST" action="{{ route('admin.twofactor.disable') }}" class="mt-4 space-y-3">
                    @csrf
                    <div>
                        <label class="block text-sm text-slate-700 mb-1">Mot de passe admin</label>
                        <input type="password" name="password" required class="w-full max-w-xs rounded border border-slate-300 px-3 py-2">
                        @error('password')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
                    </div>
                    <button type="submit" class="px-4 py-2 rounded border border-red-300 text-red-700 hover:bg-red-50">Désactiver la 2FA</button>
                </form>
            </div>
        @endif
    </div>
</x-admin-layout>
