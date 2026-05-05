<x-app-layout>
    <x-slot name="title">Articles</x-slot>

    @php
        $typeLabels = [
            'patch_note'    => ['label' => 'Patch Note',    'color' => 'bg-blue-900/50 text-blue-300 border-blue-700/50'],
            'annonce'       => ['label' => 'Annonce',       'color' => 'bg-hub-primary/20 text-hub-gold border-hub-primary/30'],
            'amelioration'  => ['label' => 'Amélioration',  'color' => 'bg-green-900/50 text-green-300 border-green-700/50'],
            'questionnaire' => ['label' => 'Questionnaire', 'color' => 'bg-purple-900/50 text-purple-300 border-purple-700/50'],
        ];
    @endphp

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12" x-data="{ activeType: '{{ request('type', '') }}' }">

        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-8 gap-4">
            <div>
                <h1 class="text-3xl font-bold text-hub-text">Articles</h1>
                <p class="text-hub-text-sec mt-1">Guides, analyses et actualités sur Genshin Impact</p>
            </div>

            {{-- Filtre par type --}}
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('articles.index') }}"
                   class="px-3 py-1.5 text-xs rounded-full border transition {{ !request('type') ? 'bg-hub-primary text-white border-hub-primary' : 'border-hub-border text-hub-text-sec hover:border-hub-primary/50' }}">
                    Tous
                </a>
                @foreach($typeLabels as $type => $cfg)
                    <a href="{{ route('articles.index', ['type' => $type]) }}"
                       class="px-3 py-1.5 text-xs rounded-full border transition {{ request('type') === $type ? 'bg-hub-primary text-white border-hub-primary' : 'border-hub-border text-hub-text-sec hover:border-hub-primary/50' }}">
                        {{ $cfg['label'] }}
                    </a>
                @endforeach
            </div>
        </div>

        @if($articles->isEmpty())
            <div class="text-center py-24">
                <p class="text-hub-text-sec text-lg">Aucun article publié pour l'instant.</p>
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($articles as $article)
                    @php $tc = $typeLabels[$article->type] ?? ['label' => $article->type, 'color' => 'bg-hub-surface-hover text-hub-text-sec border-hub-border']; @endphp
                    <a href="{{ route('articles.show', $article) }}"
                       class="group block bg-hub-surface border border-hub-border rounded-2xl overflow-hidden hover:border-hub-primary/50 transition-all hover:-translate-y-1 hover:shadow-lg">

                        <div class="p-5">
                            {{-- Type + Épinglé --}}
                            <div class="flex items-center gap-2 mb-3">
                                <span class="px-2 py-0.5 text-xs border rounded-full {{ $tc['color'] }}">
                                    {{ $tc['label'] }}
                                </span>
                                @if($article->isPinnedActive())
                                    <span class="px-2 py-0.5 text-xs bg-hub-gold/20 text-hub-gold border border-hub-gold/30 rounded-full">
                                        📌 Épinglé
                                    </span>
                                @endif
                            </div>

                            <h2 class="text-hub-text font-semibold text-lg leading-snug group-hover:text-hub-primary transition mb-2">
                                {{ $article->title }}
                            </h2>

                            <div class="flex items-center justify-between text-xs text-hub-text-sec mt-4">
                                <span>{{ $article->admin?->pseudo ?? 'TeyvatHub' }}</span>
                                <span>{{ ($article->published_at ?? $article->created_at)->format('d/m/Y') }}</span>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>

            <div class="mt-8">
                {{ $articles->links() }}
            </div>
        @endif
    </div>
</x-app-layout>
