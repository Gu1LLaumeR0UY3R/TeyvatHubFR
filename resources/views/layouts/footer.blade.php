<footer class="bg-hub-surface border-t border-hub-border mt-auto">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-6 mb-8">

            {{-- Branding --}}
            <div class="col-span-2 sm:col-span-3 lg:col-span-2">
                <a href="{{ route('home') }}" class="text-xl font-bold text-hub-primary hover:text-hub-accent transition-colors">
                    TeyvatHub <span class="text-hub-gold text-sm">FR</span>
                </a>
                <p class="mt-2 text-sm text-hub-muted max-w-xs">
                    Encyclopédie fan Genshin Impact — personnages, armes, ennemis, cuisine et bien plus.
                </p>
            </div>

            {{-- Encyclopédie --}}
            <div>
                <h3 class="text-xs font-semibold text-hub-text uppercase tracking-wider mb-3">Encyclopédie</h3>
                <ul class="space-y-2">
                    <li><a href="{{ route('personnages.index') }}" class="text-sm text-hub-muted hover:text-hub-text transition-colors">Personnages</a></li>
                    <li><a href="{{ route('armes.index') }}" class="text-sm text-hub-muted hover:text-hub-text transition-colors">Armes</a></li>
                    <li><a href="{{ route('cuisine.index') }}" class="text-sm text-hub-muted hover:text-hub-text transition-colors">Cuisine</a></li>
                </ul>
            </div>

            {{-- Monde --}}
            <div>
                <h3 class="text-xs font-semibold text-hub-text uppercase tracking-wider mb-3">Monde</h3>
                <ul class="space-y-2">
                    <li><a href="{{ route('ennemis.index') }}" class="text-sm text-hub-muted hover:text-hub-text transition-colors">Ennemis</a></li>
                    <li><a href="{{ route('animaux.index') }}" class="text-sm text-hub-muted hover:text-hub-text transition-colors">Animaux</a></li>
                    <li><a href="{{ route('histoire.index') }}" class="text-sm text-hub-muted hover:text-hub-text transition-colors">Histoire</a></li>
                    <li><a href="{{ route('regions.index') }}" class="text-sm text-hub-muted hover:text-hub-text transition-colors">Régions</a></li>
                </ul>
            </div>

            {{-- Ressources --}}
            <div>
                <h3 class="text-xs font-semibold text-hub-text uppercase tracking-wider mb-3">Ressources</h3>
                <ul class="space-y-2">
                    <li><a href="{{ route('materiaux.index') }}" class="text-sm text-hub-muted hover:text-hub-text transition-colors">Matériaux</a></li>
                    <li><a href="{{ route('ingredients.index') }}" class="text-sm text-hub-muted hover:text-hub-text transition-colors">Ingrédients</a></li>
                </ul>
            </div>
        </div>

        <div class="border-t border-hub-border pt-6 flex flex-col sm:flex-row items-center justify-between gap-2">
            <p class="text-xs text-hub-muted">
                Site fan non officiel — Genshin Impact © HoYoverse. Tous droits réservés à leurs propriétaires respectifs.
            </p>
            <p class="text-xs text-hub-muted">
                © {{ date('Y') }} TeyvatHub
            </p>
        </div>
    </div>
</footer>
