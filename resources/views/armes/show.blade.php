<x-app-layout>
<x-slot name="title">{{ $arme->nom_arme }}</x-slot>
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8" x-data="{
    niveau: 1,
    rang: 1,
    statsNiveaux: {{ json_encode($arme->statsNiveaux->keyBy('lvl_ASN')) }},
    statsRangs: {{ json_encode($arme->statsRangs->keyBy('rang_ASR')) }},
    get statNiveau() {
        return this.statsNiveaux[this.niveau] ?? null;
    },
    get statRang() {
        return this.statsRangs[this.rang] ?? null;
    }
}">

    <nav class="mb-6 text-sm text-hub-text-sec">
        <a href="{{ route('armes.index') }}" class="hover:text-hub-primary">Armes</a>
        <span class="mx-2">/</span>
        <span class="text-hub-text">{{ $arme->nom_arme }}</span>
    </nav>

    {{-- Bloc 1 : Header --}}
    <div class="bg-hub-surface border border-hub-border rounded-2xl p-6 mb-6">
        <div class="flex flex-col sm:flex-row gap-6">
            <div class="flex-shrink-0">
                <img src="{{ $arme->photos->first()?->source_url ?? $arme->photos->first()?->chemin_photo ?? asset('images/placeholder.svg') }}"
                     alt="{{ $arme->nom_arme }}"
                     class="w-32 h-32 rounded-xl object-contain border-2 border-hub-border p-2 bg-hub-surface-hover">
            </div>
            <div class="flex-1">
                <h1 class="text-3xl font-bold text-hub-text mb-2">{{ $arme->nom_arme }}</h1>
                <div class="flex flex-wrap gap-3 mb-4">
                    @if($arme->typeArme)
                        <span class="px-3 py-1 bg-hub-surface-hover rounded-full text-sm text-hub-text">
                            {{ $arme->typeArme->libelle_TArme }}
                        </span>
                    @endif
                    @if($arme->etoile)
                        <span class="px-3 py-1 bg-hub-surface-hover rounded-full text-sm text-hub-gold">
                            {{ $arme->etoile->libelle }}
                        </span>
                    @endif
                </div>
                @if($arme->nom_competence)
                    <p class="text-hub-text font-semibold">{{ $arme->nom_competence }}</p>
                @endif
                @if($arme->descr_arme)
                    <p class="text-hub-text-sec text-sm mt-2">{{ $arme->descr_arme }}</p>
                @endif
            </div>
        </div>
    </div>

    {{-- Bloc 2 : Stats par niveau --}}
    @if($arme->statsNiveaux->count())
        <div class="bg-hub-surface border border-hub-border rounded-2xl p-6 mb-6">
            <h2 class="text-xl font-bold text-hub-text mb-4">Statistiques par niveau</h2>
            <div class="flex items-center gap-4 mb-4">
                <button @click="niveau = Math.max(1, niveau - 1)"
                        class="w-8 h-8 flex items-center justify-center rounded-lg bg-hub-surface-hover border border-hub-border hover:border-hub-primary text-hub-text transition-colors">
                    −
                </button>
                <span class="text-hub-text font-semibold min-w-16 text-center">Niv. <span x-text="niveau"></span></span>
                <button @click="niveau = Math.min(90, niveau + 1)"
                        class="w-8 h-8 flex items-center justify-center rounded-lg bg-hub-surface-hover border border-hub-border hover:border-hub-primary text-hub-text transition-colors">
                    +
                </button>
            </div>
            <template x-if="statNiveau">
                <div class="grid grid-cols-2 gap-4">
                    <div class="p-4 bg-hub-surface-hover rounded-xl">
                        <p class="text-hub-text-sec text-sm">{{ $arme->main_stat_type ?? 'Stat principale' }}</p>
                        <p class="text-hub-text font-bold text-lg" x-text="statNiveau.main_stat"></p>
                    </div>
                    <template x-if="statNiveau.subs_stats">
                        <div class="p-4 bg-hub-surface-hover rounded-xl">
                            <p class="text-hub-text-sec text-sm">{{ $arme->sub_stat_type ?? 'Stat secondaire' }}</p>
                            <p class="text-hub-text font-bold text-lg" x-text="statNiveau.subs_stats"></p>
                        </div>
                    </template>
                </div>
            </template>
            <template x-if="!statNiveau">
                <p class="text-hub-text-sec">Aucune donnée pour ce niveau.</p>
            </template>
        </div>
    @endif

    {{-- Bloc 3 : Stats par rang --}}
    @if($arme->statsRangs->count())
        <div class="bg-hub-surface border border-hub-border rounded-2xl p-6 mb-6">
            <h2 class="text-xl font-bold text-hub-text mb-4">Compétence par rang</h2>
            <div class="flex gap-2 mb-4">
                @foreach(['R1','R2','R3','R4','R5'] as $r)
                    <button @click="rang = {{ $loop->iteration }}"
                            :class="rang === {{ $loop->iteration }} ? 'bg-hub-primary text-white' : 'bg-hub-surface-hover text-hub-text'"
                            class="px-4 py-2 rounded-lg border border-hub-border font-semibold text-sm transition-colors">
                        {{ $r }}
                    </button>
                @endforeach
            </div>
            <template x-if="statRang">
                <div class="p-4 bg-hub-surface-hover rounded-xl">
                    <p class="text-hub-text-sec text-sm leading-relaxed" x-text="statRang.descri_ASR"></p>
                </div>
            </template>
            <template x-if="!statRang">
                <p class="text-hub-text-sec">Aucune description pour ce rang.</p>
            </template>
        </div>
    @endif

</div>
</x-app-layout>
