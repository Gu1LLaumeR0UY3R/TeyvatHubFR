<x-app-layout>
    <x-slot name="title">{{ $arme->nom_arme }}</x-slot>

    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

        <a href="{{ route('armes.index') }}"
           class="inline-flex items-center gap-2 text-hub-text-sec hover:text-hub-text mb-6 transition-colors">
            ← Retour aux armes
        </a>

        {{-- En-tête --}}
        <div class="bg-hub-surface border border-hub-border rounded-2xl p-6 flex flex-col sm:flex-row gap-6 mb-6">
            <img src="{{ $arme->photos->first()?->source_url ?? $arme->photos->first()?->chemin_photo ?? asset('images/placeholder.webp') }}"
                 alt="{{ $arme->nom_arme }}"
                 class="w-36 h-36 object-cover rounded-xl self-start">
            <div class="flex-1">
                <h1 class="text-3xl font-bold text-hub-primary mb-2">{{ $arme->nom_arme }}</h1>
                <div class="flex flex-wrap gap-3 text-sm mb-3">
                    <span class="px-3 py-1 bg-hub-surface-hover rounded-full text-hub-text-sec">
                        {{ $arme->typeArme?->libelle_TArme ?? '—' }}
                    </span>
                    <span class="px-3 py-1 bg-hub-surface-hover rounded-full text-hub-gold">
                        {{ $arme->etoile?->libelle ?? '—' }}
                    </span>
                    @if($arme->main_stat_type)
                        <span class="px-3 py-1 bg-hub-surface-hover rounded-full text-hub-text-sec">
                            Stat principale : {{ $arme->main_stat_type }}
                        </span>
                    @endif
                    @if($arme->sub_stat_type)
                        <span class="px-3 py-1 bg-hub-surface-hover rounded-full text-hub-text-sec">
                            Stat secondaire : {{ $arme->sub_stat_type }}
                        </span>
                    @endif
                </div>
                @if($arme->descr_arme)
                    <p class="text-hub-text-sec leading-relaxed text-sm">{{ $arme->descr_arme }}</p>
                @endif
            </div>
        </div>

        {{-- Compétence passive --}}
        @if($arme->nom_competence || $arme->statsRangs->isNotEmpty())
            <div class="bg-hub-surface border border-hub-border rounded-2xl p-6 mb-6" x-data="{ rang: 1 }">
                <h2 class="text-xl font-semibold text-hub-text mb-4">
                    Compétence passive{{ $arme->nom_competence ? ' — ' . $arme->nom_competence : '' }}
                </h2>
                @if($arme->statsRangs->isNotEmpty())
                    <div class="flex gap-2 mb-4">
                        @foreach($arme->statsRangs as $sr)
                            <button @click="rang = {{ $sr->rang_ASR }}"
                                    :class="rang === {{ $sr->rang_ASR }} ? 'bg-hub-primary text-white' : 'bg-hub-surface-hover text-hub-text-sec'"
                                    class="w-8 h-8 rounded-full text-sm font-bold transition-colors">
                                R{{ $sr->rang_ASR }}
                            </button>
                        @endforeach
                    </div>
                    @foreach($arme->statsRangs as $sr)
                        <div x-show="rang === {{ $sr->rang_ASR }}" x-cloak>
                            <p class="text-hub-text leading-relaxed">{{ $sr->descri_ASR }}</p>
                        </div>
                    @endforeach
                @endif
            </div>
        @endif

        {{-- Stats par niveau --}}
        @if($arme->statsNiveaux->isNotEmpty())
            <div class="bg-hub-surface border border-hub-border rounded-2xl p-6 mb-6"
                 x-data="{ niveau: {{ $arme->statsNiveaux->first()->lvl_ASN }} }">
                <h2 class="text-xl font-semibold text-hub-text mb-4">Statistiques par niveau</h2>
                <div class="mb-4">
                    <label class="text-hub-text-sec text-sm mb-1 block">
                        Niveau : <span class="text-hub-primary font-bold" x-text="niveau"></span>
                    </label>
                    <input type="range"
                           min="{{ $arme->statsNiveaux->first()->lvl_ASN }}"
                           max="{{ $arme->statsNiveaux->last()->lvl_ASN }}"
                           x-model="niveau"
                           class="w-full accent-hub-primary">
                </div>
                @foreach($arme->statsNiveaux as $sn)
                    <div x-show="niveau == {{ $sn->lvl_ASN }}" x-cloak class="grid grid-cols-2 gap-4">
                        <div class="bg-hub-surface-hover rounded-xl p-3 text-center">
                            <p class="text-xs text-hub-text-sec mb-1">{{ $arme->main_stat_type ?? 'ATQ' }}</p>
                            <p class="text-xl font-bold text-hub-primary">{{ number_format($sn->main_stat, 0) }}</p>
                        </div>
                        @if($arme->sub_stat_type)
                            <div class="bg-hub-surface-hover rounded-xl p-3 text-center">
                                <p class="text-xs text-hub-text-sec mb-1">{{ $arme->sub_stat_type }}</p>
                                <p class="text-xl font-bold text-hub-gold">{{ number_format($sn->subs_stats, 1) }}</p>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif

    </div>
</x-app-layout>
