<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Admin Dashboard — TeyvatHub</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-hub-bg">

    {{-- Header --}}
    <header class="bg-hub-surface border-b border-hub-border px-6 py-4 flex items-center justify-between">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.dashboard') }}" class="text-xl font-bold text-hub-text">TeyvatHub Admin</a>
        </div>
        <div class="flex items-center gap-4">
            <span class="text-hub-text-sec text-sm">{{ session('admin_pseudo') }}</span>
            <form method="POST" action="{{ route('admin.logout') }}">
                @csrf
                <button type="submit" class="text-hub-text-sec hover:text-hub-text text-sm">Déconnexion</button>
            </form>
        </div>
    </header>

    <div class="flex">
        {{-- Sidebar --}}
        <aside class="w-56 min-h-screen bg-hub-surface border-r border-hub-border p-4">
            <nav class="space-y-1">
                <a href="{{ route('admin.dashboard') }}" class="block px-3 py-2 rounded-lg text-hub-text hover:bg-hub-surface-hover text-sm font-medium">Dashboard</a>
                <a href="{{ route('admin.personnages.index') }}" class="block px-3 py-2 rounded-lg text-hub-text-sec hover:bg-hub-surface-hover text-sm">Personnages</a>
                <a href="{{ route('admin.armes.index') }}" class="block px-3 py-2 rounded-lg text-hub-text-sec hover:bg-hub-surface-hover text-sm">Armes</a>
                <a href="{{ route('admin.artefacts.index') }}" class="block px-3 py-2 rounded-lg text-hub-text-sec hover:bg-hub-surface-hover text-sm">Artefacts</a>
                <a href="{{ route('admin.ennemis.index') }}" class="block px-3 py-2 rounded-lg text-hub-text-sec hover:bg-hub-surface-hover text-sm">Ennemis</a>
                <a href="{{ route('admin.animaux.index') }}" class="block px-3 py-2 rounded-lg text-hub-text-sec hover:bg-hub-surface-hover text-sm">Animaux</a>
                <a href="{{ route('admin.cuisine.index') }}" class="block px-3 py-2 rounded-lg text-hub-text-sec hover:bg-hub-surface-hover text-sm">Cuisine</a>
                <a href="{{ route('admin.nations.index') }}" class="block px-3 py-2 rounded-lg text-hub-text-sec hover:bg-hub-surface-hover text-sm">Nations</a>
                <a href="{{ route('admin.references.index', 'elements') }}" class="block px-3 py-2 rounded-lg text-hub-text-sec hover:bg-hub-surface-hover text-sm">Références</a>
                <a href="{{ route('admin.utilisateurs.index') }}" class="block px-3 py-2 rounded-lg text-hub-text-sec hover:bg-hub-surface-hover text-sm">Utilisateurs</a>
                <a href="{{ route('admin.admins.index') }}" class="block px-3 py-2 rounded-lg text-hub-text-sec hover:bg-hub-surface-hover text-sm">Admins</a>
                <a href="{{ route('admin.articles.index') }}" class="block px-3 py-2 rounded-lg text-hub-text-sec hover:bg-hub-surface-hover text-sm">Articles</a>
                @if(in_array(session('admin_role'), ['super_admin', 'superadmin']))
                    <a href="{{ route('admin.snapshots.index') }}" class="block px-3 py-2 rounded-lg text-hub-text-sec hover:bg-hub-surface-hover text-sm">Restauration</a>
                    <a href="{{ route('admin.logs.index') }}" class="block px-3 py-2 rounded-lg text-hub-text-sec hover:bg-hub-surface-hover text-sm">Logs activité</a>
                @endif
            </nav>
        </aside>

        {{-- Content --}}
        <main class="flex-1 p-8">
            {{ $slot }}
        </main>
    </div>

</body>
</html>
