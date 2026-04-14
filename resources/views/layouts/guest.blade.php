<!DOCTYPE html>
<html lang="fr" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? config('app.name', 'TeyvatHub') }} — TeyvatHub</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-hub-bg text-hub-text min-h-screen flex flex-col">

    {{-- Barre de navigation --}}
    <header class="border-b border-hub-border bg-hub-surface/80 backdrop-blur sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-14 flex items-center justify-between">
            <a href="{{ route('home') }}" class="flex items-center gap-2 group">
                <span class="text-xl font-bold text-hub-primary group-hover:text-hub-accent transition-colors duration-200">TeyvatHub</span>
                <span class="text-hub-gold text-xs tracking-widest font-semibold hidden sm:block">FR</span>
            </a>
            <a href="{{ route('home') }}"
               class="flex items-center gap-1.5 text-sm text-hub-text-sec hover:text-hub-text transition-colors py-1.5 px-3 rounded-lg hover:bg-hub-surface-hover">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
                Retour à l'encyclopédie
            </a>
        </div>
    </header>

    {{-- Contenu central --}}
    <div class="flex-1 flex items-center justify-center px-4 py-12">
        <div class="w-full max-w-md">

            {{-- Logo / Titre --}}
            <div class="text-center mb-8">
                <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-hub-primary/15 border border-hub-primary/30 mb-4">
                    <svg class="w-7 h-7 text-hub-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/>
                    </svg>
                </div>
                <h1 class="text-2xl font-bold text-hub-text">TeyvatHub</h1>
                <p class="text-hub-text-sec text-sm mt-1">Encyclopédie Genshin Impact</p>
            </div>

            {{-- Carte formulaire --}}
            <div class="bg-hub-surface border border-hub-border rounded-2xl shadow-2xl shadow-black/40 p-8">
                {{ $slot }}
            </div>

        </div>
    </div>

</body>
</html>
