<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin — TeyvatHub</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-hub-bg flex items-center justify-center">

<div class="w-full max-w-md px-4">
    <div class="text-center mb-8">
        <h1 class="text-3xl font-bold text-hub-text">TeyvatHub</h1>
        <p class="text-hub-text-sec mt-1">Administration</p>
    </div>

    <div class="bg-hub-surface border border-hub-border rounded-2xl p-8">
        <h2 class="text-xl font-bold text-hub-text mb-6">Connexion Admin</h2>

        @if($errors->any())
            <div class="mb-4 p-3 bg-red-900 bg-opacity-30 border border-red-700 rounded-xl">
                @foreach($errors->all() as $error)
                    <p class="text-red-400 text-sm">{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('admin.authenticate') }}">
            @csrf
            <div class="mb-4">
                <label class="block text-hub-text-sec text-sm mb-1">E-mail</label>
                <input type="email"
                       name="email"
                       value="{{ old('email') }}"
                       required
                       class="w-full bg-hub-surface-hover border border-hub-border rounded-xl px-4 py-2.5 text-hub-text focus:outline-none focus:border-hub-primary">
            </div>
            <div class="mb-6">
                <label class="block text-hub-text-sec text-sm mb-1">Mot de passe</label>
                <input type="password"
                       name="password"
                       required
                       class="w-full bg-hub-surface-hover border border-hub-border rounded-xl px-4 py-2.5 text-hub-text focus:outline-none focus:border-hub-primary">
            </div>
            <button type="submit"
                    class="w-full py-2.5 bg-hub-primary text-white rounded-xl font-bold hover:bg-opacity-90 transition-colors">
                Se connecter
            </button>
        </form>
    </div>
</div>

</body>
</html>
