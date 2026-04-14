<!DOCTYPE html>
<html lang="fr" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'TeyvatHub' }} — Encyclopédie Genshin Impact</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-hub-bg text-hub-text h-full flex flex-col">

    {{-- HEADER --}}
    <header class="bg-white/95 backdrop-blur border-b border-slate-200 shadow-sm sticky top-0 z-50" x-data="{ mobileOpen: false }">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">

                {{-- Logo --}}
                <a href="{{ route('home') }}" class="flex items-center gap-2 group">
                    <span class="text-2xl font-bold text-hub-primary group-hover:text-hub-accent transition-colors duration-200">
                        TeyvatHub
                    </span>
                    <span class="text-hub-gold text-xs tracking-widest font-semibold hidden sm:block">FR</span>
                </a>

                {{-- Navigation Desktop --}}
                <nav class="hidden lg:flex items-center gap-1">

                    {{-- Personnages --}}
                    <a href="{{ route('personnages.index') }}"
                       class="px-3 py-2 text-sm font-medium text-slate-700 hover:text-slate-950 hover:bg-slate-100 rounded-lg transition-colors duration-150">
                        Personnages
                    </a>

                    {{-- Armes --}}
                    <a href="{{ route('armes.index') }}"
                       class="px-3 py-2 text-sm font-medium text-slate-700 hover:text-slate-950 hover:bg-slate-100 rounded-lg transition-colors duration-150">
                        Armes
                    </a>

                    {{-- Ennemis + sous-menu --}}
                    <div class="relative" x-data="{ open: false }" @mouseenter="open = true" @mouseleave="open = false">
                        <button class="flex items-center gap-1 px-3 py-2 text-sm font-medium text-slate-700 hover:text-slate-950 hover:bg-slate-100 rounded-lg transition-colors duration-150">
                            Ennemis
                            <svg class="w-3 h-3 transition-transform" :class="{ 'rotate-180': open }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>
                        <div x-show="open" x-transition
                             class="absolute top-full left-0 mt-1 w-44 bg-white border border-slate-200 rounded-xl shadow-xl py-1 z-50">
                            <a href="{{ route('ennemis.index') }}"
                               class="block px-4 py-2 text-sm text-slate-700 hover:text-slate-950 hover:bg-slate-100 transition-colors">
                                Tous les ennemis
                            </a>
                            <a href="{{ route('materiaux.index') }}"
                               class="block px-4 py-2 text-sm text-slate-700 hover:text-slate-950 hover:bg-slate-100 transition-colors">
                                Matériaux
                            </a>
                        </div>
                    </div>

                    {{-- Animaux + sous-menu --}}
                    <div class="relative" x-data="{ open: false }" @mouseenter="open = true" @mouseleave="open = false">
                        <button class="flex items-center gap-1 px-3 py-2 text-sm font-medium text-slate-700 hover:text-slate-950 hover:bg-slate-100 rounded-lg transition-colors duration-150">
                            Animaux
                            <svg class="w-3 h-3 transition-transform" :class="{ 'rotate-180': open }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>
                        <div x-show="open" x-transition
                             class="absolute top-full left-0 mt-1 w-44 bg-white border border-slate-200 rounded-xl shadow-xl py-1 z-50">
                            <a href="{{ route('animaux.index') }}"
                               class="block px-4 py-2 text-sm text-slate-700 hover:text-slate-950 hover:bg-slate-100 transition-colors">
                                Tous les animaux
                            </a>
                            <a href="{{ route('ingredients.index') }}"
                               class="block px-4 py-2 text-sm text-slate-700 hover:text-slate-950 hover:bg-slate-100 transition-colors">
                                Ingrédients
                            </a>
                        </div>
                    </div>

                    {{-- Cuisine --}}
                    <a href="{{ route('cuisine.index') }}"
                       class="px-3 py-2 text-sm font-medium text-slate-700 hover:text-slate-950 hover:bg-slate-100 rounded-lg transition-colors duration-150">
                        Cuisine
                    </a>

                    {{-- Histoire + sous-menu régions --}}
                    <div class="relative" x-data="{ open: false }" @mouseenter="open = true" @mouseleave="open = false">
                        <button class="flex items-center gap-1 px-3 py-2 text-sm font-medium text-slate-700 hover:text-slate-950 hover:bg-slate-100 rounded-lg transition-colors duration-150">
                            Histoire
                            <svg class="w-3 h-3 transition-transform" :class="{ 'rotate-180': open }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>
                        <div x-show="open" x-transition
                             class="absolute top-full left-0 mt-1 w-52 bg-white border border-slate-200 rounded-xl shadow-xl py-1 z-50">
                            <a href="{{ route('histoire.index') }}"
                               class="block px-4 py-2 text-sm text-slate-700 hover:text-slate-950 hover:bg-slate-100 transition-colors">
                                Histoire de Teyvat
                            </a>
                            <div class="border-t border-slate-200 my-1"></div>
                            <p class="px-4 py-1 text-xs text-slate-500 uppercase tracking-wider">Régions</p>
                            @foreach([
                                'mondstadt' => 'Mondstadt',
                                'liyue'     => 'Liyue',
                                'inazuma'   => 'Inazuma',
                                'sumeru'    => 'Sumeru',
                                'fontaine'  => 'Fontaine',
                                'natlan'    => 'Natlan',
                                'nod-krai'  => 'Nod-Krai',
                            ] as $slug => $nom)
                                <a href="{{ route('nations.show', $slug) }}"
                                   class="block px-4 py-2 text-sm text-slate-700 hover:text-slate-950 hover:bg-slate-100 transition-colors">
                                    {{ $nom }}
                                </a>
                            @endforeach
                        </div>
                    </div>

                    {{-- Outils --}}
                    <div class="relative" x-data="{ open: false }" @mouseenter="open = true" @mouseleave="open = false">
                        <button class="flex items-center gap-1 px-3 py-2 text-sm font-medium text-slate-700 hover:text-slate-950 hover:bg-slate-100 rounded-lg transition-colors duration-150">
                            Outils
                            <svg class="w-3 h-3 transition-transform" :class="{ 'rotate-180': open }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>
                        <div x-show="open" x-transition
                             class="absolute top-full left-0 mt-1 w-52 bg-white border border-slate-200 rounded-xl shadow-xl py-1 z-50">
                            <a href="{{ route('outils.personnage-du-jour') }}"
                               class="block px-4 py-2 text-sm text-slate-700 hover:text-slate-950 hover:bg-slate-100 transition-colors">
                                Personnage du jour
                            </a>
                            <a href="{{ route('outils.quiz') }}"
                               class="block px-4 py-2 text-sm text-slate-700 hover:text-slate-950 hover:bg-slate-100 transition-colors">
                                Quiz
                            </a>
                            @auth
                            <div class="border-t border-slate-200 my-1"></div>
                            <a href="{{ route('outils.roulette') }}"
                               class="block px-4 py-2 text-sm text-slate-700 hover:text-slate-950 hover:bg-slate-100 transition-colors">
                                Roulette personnage
                            </a>
                            <a href="{{ route('outils.team') }}"
                               class="block px-4 py-2 text-sm text-slate-700 hover:text-slate-950 hover:bg-slate-100 transition-colors">
                                Générateur d'équipe
                            </a>
                            <a href="{{ route('outils.comparateur') }}"
                               class="block px-4 py-2 text-sm text-slate-700 hover:text-slate-950 hover:bg-slate-100 transition-colors">
                                Comparateur
                            </a>
                            @endauth
                        </div>
                    </div>

                    {{-- Jeux --}}
                    <a href="{{ route('jeux.motus') }}"
                       class="px-3 py-2 text-sm font-medium text-slate-700 hover:text-slate-950 hover:bg-slate-100 rounded-lg transition-colors duration-150">
                        Jeux
                    </a>

                </nav>

                {{-- Auth Desktop --}}
                <div class="hidden lg:flex items-center gap-3">
                    @guest
                        <a href="{{ route('login') }}"
                           class="text-sm text-slate-700 hover:text-slate-950 transition-colors px-3 py-2 rounded-lg hover:bg-slate-100">
                            Connexion
                        </a>
                        <a href="{{ route('register') }}"
                           class="text-sm font-semibold bg-hub-primary hover:bg-hub-primary/80 text-white px-4 py-2 rounded-lg transition-colors duration-150">
                            Inscription
                        </a>
                    @else
                        <div class="relative" x-data="{ open: false }" @click.outside="open = false">
                            <button @click="open = !open"
                                    class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-hub-surface-hover transition-colors">
                                <span class="w-7 h-7 rounded-full bg-hub-primary flex items-center justify-center text-white text-sm font-bold">
                                    {{ mb_strtoupper(mb_substr(Auth::user()->name, 0, 1)) }}
                                </span>
                                <span class="text-sm text-slate-700">{{ Auth::user()->name }}</span>
                            </button>
                            <div x-show="open" x-transition
                                 class="absolute right-0 mt-1 w-52 bg-white border border-slate-200 rounded-xl shadow-xl py-1 z-50">
                                <a href="{{ route('profil.index') }}"
                                   class="block px-4 py-2 text-sm text-slate-700 hover:text-slate-950 hover:bg-slate-100 transition-colors">
                                    Mon profil
                                </a>
                                <a href="{{ route('profil.personnages') }}"
                                   class="block px-4 py-2 text-sm text-slate-700 hover:text-slate-950 hover:bg-slate-100 transition-colors">
                                    Mes personnages
                                </a>
                                <a href="{{ route('profil.armes') }}"
                                   class="block px-4 py-2 text-sm text-slate-700 hover:text-slate-950 hover:bg-slate-100 transition-colors">
                                    Mes armes
                                </a>
                                <a href="{{ route('profil.amis') }}"
                                   class="block px-4 py-2 text-sm text-slate-700 hover:text-slate-950 hover:bg-slate-100 transition-colors">
                                    Mes amis
                                </a>
                                <a href="{{ route('profil.parametres') }}"
                                   class="block px-4 py-2 text-sm text-slate-700 hover:text-slate-950 hover:bg-slate-100 transition-colors">
                                    Paramètres
                                </a>
                                <div class="border-t border-slate-200 my-1"></div>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit"
                                            class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-slate-100 transition-colors">
                                        Déconnexion
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endguest
                </div>

                {{-- Burger Mobile --}}
                <button @click="mobileOpen = !mobileOpen"
                        class="lg:hidden p-2 rounded-lg hover:bg-slate-100 transition-colors text-slate-700">
                    <svg x-show="!mobileOpen" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                    <svg x-show="mobileOpen" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>

        {{-- Menu Mobile --}}
        <div x-show="mobileOpen" x-transition class="lg:hidden border-t border-slate-200 bg-white">
            <nav class="px-4 py-3 space-y-1">
                <a href="{{ route('personnages.index') }}" class="block py-2 text-sm text-slate-700 hover:text-slate-950">Personnages</a>
                <a href="{{ route('armes.index') }}" class="block py-2 text-sm text-slate-700 hover:text-slate-950">Armes</a>
                <a href="{{ route('ennemis.index') }}" class="block py-2 text-sm text-slate-700 hover:text-slate-950">Ennemis</a>
                <a href="{{ route('materiaux.index') }}" class="block py-2 text-sm text-slate-500 hover:text-slate-950 pl-4">↳ Matériaux</a>
                <a href="{{ route('animaux.index') }}" class="block py-2 text-sm text-slate-700 hover:text-slate-950">Animaux</a>
                <a href="{{ route('ingredients.index') }}" class="block py-2 text-sm text-slate-500 hover:text-slate-950 pl-4">↳ Ingrédients</a>
                <a href="{{ route('cuisine.index') }}" class="block py-2 text-sm text-slate-700 hover:text-slate-950">Cuisine</a>
                <a href="{{ route('histoire.index') }}" class="block py-2 text-sm text-slate-700 hover:text-slate-950">Histoire</a>
                <a href="{{ route('nations.index') }}" class="block py-2 text-sm text-slate-500 hover:text-slate-950 pl-4">↳ Nations</a>
                <div class="border-t border-slate-200 my-1"></div>
                <p class="py-1 text-xs text-slate-500 uppercase tracking-wider">Outils</p>
                <a href="{{ route('outils.personnage-du-jour') }}" class="block py-2 text-sm text-slate-700 hover:text-slate-950">Personnage du jour</a>
                <a href="{{ route('outils.quiz') }}" class="block py-2 text-sm text-slate-700 hover:text-slate-950">Quiz</a>
                <a href="{{ route('jeux.motus') }}" class="block py-2 text-sm text-slate-700 hover:text-slate-950">Motus</a>
                @auth
                <a href="{{ route('outils.roulette') }}" class="block py-2 text-sm text-slate-700 hover:text-slate-950">Roulette</a>
                <a href="{{ route('outils.team') }}" class="block py-2 text-sm text-slate-700 hover:text-slate-950">Équipe</a>
                @endauth
            </nav>
            <div class="px-4 py-3 border-t border-slate-200">
                @guest
                    <a href="{{ route('login') }}" class="block py-2 text-sm font-medium text-hub-primary">Connexion</a>
                    <a href="{{ route('register') }}" class="block py-2 text-sm font-semibold text-hub-primary">Inscription</a>
                @else
                    <p class="text-xs text-slate-500 uppercase tracking-wider mb-1">{{ Auth::user()->name }}</p>
                    <a href="{{ route('profil.index') }}" class="block py-2 text-sm text-slate-700 hover:text-slate-950">Mon profil</a>
                    <a href="{{ route('profil.personnages') }}" class="block py-2 text-sm text-slate-700 hover:text-slate-950">Mes personnages</a>
                    <a href="{{ route('profil.amis') }}" class="block py-2 text-sm text-slate-700 hover:text-slate-950">Mes amis</a>
                    <a href="{{ route('profil.parametres') }}" class="block py-2 text-sm text-slate-700 hover:text-slate-950">Paramètres</a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="block py-2 text-sm text-red-600">Déconnexion</button>
                    </form>
                @endguest
            </div>
        </div>
    </header>

    {{-- CONTENU PRINCIPAL --}}
    <main class="flex-1">
        {{ $slot }}
    </main>

    {{-- FOOTER --}}
    @include('layouts.footer')

</body>
</html>
