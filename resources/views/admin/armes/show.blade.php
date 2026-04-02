<x-admin-layout>
    <x-slot name="title">{{ $arme->nom_arme }} — Admin</x-slot>
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-hub-gold">{{ $arme->nom_arme }}</h1>
        <a href="{{ route('admin.armes.index') }}" class="px-4 py-2 border border-hub-border rounded text-hub-text hover:bg-hub-surface">Retour</a>
    </div>

    <div class="bg-hub-surface rounded-lg p-6 text-sm space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <p><span class="text-hub-text-sec">Slug :</span> {{ $arme->slug }}</p>
            <p><span class="text-hub-text-sec">Type :</span> {{ $arme->typeArme?->libelle_TArme ?? 'N/A' }}</p>
            <p><span class="text-hub-text-sec">Rareté :</span> {{ $arme->etoile?->libelle ?? 'N/A' }}</p>
            <p><span class="text-hub-text-sec">Description :</span> {{ $arme->descr_arme ?: 'Aucune description' }}</p>
        </div>

        <div>
            <h2 class="text-base font-semibold text-hub-gold mb-2">Stats par niveau</h2>
            @if($arme->statsNiveaux->isNotEmpty())
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="text-left border-b border-hub-border text-hub-text-sec">
                                <th class="py-2">Niveau</th>
                                <th class="py-2">Main stat</th>
                                <th class="py-2">Sub stat</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($arme->statsNiveaux->sortBy('lvl_ASN') as $stat)
                                <tr class="border-b border-hub-border/50">
                                    <td class="py-2">{{ $stat->lvl_ASN }}</td>
                                    <td class="py-2">{{ $stat->main_stat }}</td>
                                    <td class="py-2">{{ $stat->subs_stats }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-hub-text-sec">Aucune stat de niveau.</p>
            @endif
        </div>

        <div>
            <h2 class="text-base font-semibold text-hub-gold mb-2">Effets par rang</h2>
            @if($arme->statsRangs->isNotEmpty())
                <div class="space-y-2">
                    @foreach($arme->statsRangs->sortBy('rang_ASR') as $rang)
                        <div class="border border-hub-border rounded p-3">
                            <p class="font-semibold text-hub-gold">Rang {{ $rang->rang_ASR }}</p>
                            <p class="text-hub-text mt-1">{{ $rang->descri_ASR }}</p>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-hub-text-sec">Aucun effet de rang.</p>
            @endif
        </div>
    </div>
</x-admin-layout>
