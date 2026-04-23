<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin — Vérification 2FA</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-hub-bg flex items-center justify-center">

<div class="w-full max-w-md px-4">
    <div class="text-center mb-8">
        <h1 class="text-3xl font-bold text-hub-text">TeyvatHub</h1>
        <p class="text-hub-text-sec mt-1">Vérification admin</p>
    </div>

    <div class="bg-hub-surface border border-hub-border rounded-2xl p-8">
        <h2 class="text-xl font-bold text-hub-text mb-3">Code 2FA</h2>
        <p class="text-sm text-hub-text-sec mb-6">Entre le code à 6 chiffres de ton application.</p>

        @if($errors->any())
            <div class="mb-4 p-3 bg-red-900/30 border border-red-700 rounded-xl">
                @foreach($errors->all() as $error)
                    <p class="text-red-400 text-sm">{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('admin.twofactor.challenge.verify') }}">
            @csrf
            <div class="mb-6">
                <label class="block text-hub-text-sec text-sm mb-1">Code</label>
                <input type="text" name="code" maxlength="6" inputmode="numeric" pattern="[0-9]*" required autofocus
                       class="w-full bg-hub-surface-hover border border-hub-border rounded-xl px-4 py-2.5 text-hub-text focus:outline-none focus:border-hub-primary">
            </div>
            <button type="submit"
                    class="w-full py-2.5 bg-hub-primary text-white rounded-xl font-bold hover:bg-opacity-90 transition-colors">
                Vérifier
            </button>
        </form>
    </div>
</div>

</body>
</html>
