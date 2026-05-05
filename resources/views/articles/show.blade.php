<x-app-layout>
    <x-slot name="title">{{ $article->title }}</x-slot>

    @php
        $typeLabels = [
            'patch_note'    => ['label' => 'Patch Note',    'color' => 'bg-blue-900/50 text-blue-300 border-blue-700/50'],
            'annonce'       => ['label' => 'Annonce',       'color' => 'bg-hub-primary/20 text-hub-gold border-hub-primary/30'],
            'amelioration'  => ['label' => 'Amélioration',  'color' => 'bg-green-900/50 text-green-300 border-green-700/50'],
            'questionnaire' => ['label' => 'Questionnaire', 'color' => 'bg-purple-900/50 text-purple-300 border-purple-700/50'],
        ];
        $tc = $typeLabels[$article->type] ?? ['label' => $article->type, 'color' => 'bg-hub-surface-hover text-hub-text-sec border-hub-border'];
    @endphp

    <article class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">

        {{-- Meta + Badges --}}
        <div class="flex flex-wrap items-center gap-3 text-hub-text-sec text-sm mb-4">
            <span class="px-2 py-0.5 text-xs border rounded-full {{ $tc['color'] }}">{{ $tc['label'] }}</span>
            @if($article->isPinnedActive())
                <span class="px-2 py-0.5 text-xs bg-hub-gold/20 text-hub-gold border border-hub-gold/30 rounded-full">📌 Épinglé</span>
            @endif
            <span class="ml-auto">{{ $article->admin?->pseudo ?? 'TeyvatHub' }}</span>
            <span>·</span>
            <span>{{ ($article->published_at ?? $article->created_at)->translatedFormat('d F Y') }}</span>
        </div>

        {{-- Title --}}
        <h1 class="text-4xl font-bold text-hub-text leading-tight mb-8">{{ $article->title }}</h1>

        {{-- ══ PATCH NOTE ══ --}}
        @if($article->type === 'patch_note' && $article->patchNoteMeta)
            @php $meta = $article->patchNoteMeta; @endphp
            <div class="flex flex-wrap items-center gap-4 mb-8 p-4 bg-blue-900/20 rounded-xl border border-blue-700/30">
                <span class="text-sm font-mono font-bold text-blue-300">v{{ $meta->version }}</span>
                <span class="text-sm text-hub-text-sec">{{ $meta->release_date?->format('d/m/Y') }}</span>
            </div>

            @php $changelog = $meta->changelog ?? []; @endphp
            @foreach(['added' => ['Ajouté', 'text-green-300', 'bg-green-900/20 border-green-700/30'], 'fixed' => ['Corrigé', 'text-orange-300', 'bg-orange-900/20 border-orange-700/30'], 'removed' => ['Supprimé', 'text-red-300', 'bg-red-900/20 border-red-700/30']] as $key => [$label, $textColor, $bgBorder])
                @if(!empty($changelog[$key]))
                    <div class="mb-6 p-4 rounded-xl border {{ $bgBorder }}">
                        <h3 class="font-semibold {{ $textColor }} mb-3">{{ $label }}</h3>
                        <ul class="space-y-1.5">
                            @foreach($changelog[$key] as $item)
                                <li class="flex gap-2 text-sm text-hub-text">
                                    <span class="{{ $textColor }}">•</span>
                                    <span>{!! $item !!}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            @endforeach
        @endif

        {{-- ══ ANNONCE (contenu TipTap) ══ --}}
        @if($article->type === 'annonce')
            <div class="th-article-content bg-white rounded-2xl p-8 shadow-sm border border-gray-200">
                @include('partials.block-renderer', ['node' => $article->content])
            </div>
        @endif

        {{-- ══ AMÉLIORATION ══ --}}
        @if($article->type === 'amelioration')
            @php
                $meta = $article->improvementMeta;
                $statusLabels = ['prevu' => ['À venir', 'bg-blue-900/50 text-blue-300 border-blue-700/50'], 'en_cours' => ['En cours', 'bg-yellow-900/50 text-yellow-300 border-yellow-700/50'], 'annule' => ['Annulé', 'bg-red-900/50 text-red-300 border-red-700/50'], 'livre' => ['Livré ✓', 'bg-green-900/50 text-green-300 border-green-700/50']];
                $sc = $statusLabels[$meta?->planning_status ?? ''] ?? ['Inconnu', 'bg-hub-surface-hover text-hub-text-sec border-hub-border'];
            @endphp

            {{-- Statut planning --}}
            @if($meta)
                <div class="flex items-center gap-3 mb-6 p-4 bg-hub-surface rounded-xl border border-hub-border">
                    <span class="text-sm text-hub-text-sec">Statut :</span>
                    <span class="px-2 py-0.5 text-xs border rounded-full {{ $sc[1] }}">{{ $sc[0] }}</span>
                </div>
            @endif

            {{-- Contenu TipTap --}}
            <div class="th-article-content bg-white rounded-2xl p-8 shadow-sm border border-gray-200 mb-8">
                @include('partials.block-renderer', ['node' => $article->content])
            </div>

            {{-- Bouton vote --}}
            @auth
                @if($meta)
                <div x-data="{
                        voted: {{ auth()->user() ? ($meta->votes->where('user_id', auth()->id())->isNotEmpty() ? 'true' : 'false') : 'false' }},
                        count: {{ $meta->upvotes_count }},
                        loading: false,
                        async toggle() {
                            if (this.loading) return;
                            this.loading = true;
                            try {
                                const res = await fetch('{{ route('improvement.vote', $meta->id) }}', {
                                    method: 'POST',
                                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }
                                });
                                const data = await res.json();
                                this.voted = data.voted;
                                this.count = data.count;
                            } finally {
                                this.loading = false;
                            }
                        }
                    }" class="flex items-center gap-4 mt-4">
                    <button @click="toggle()" :disabled="loading"
                            :class="voted ? 'bg-green-700 border-green-500 text-white' : 'border-hub-border text-hub-text-sec hover:border-green-500 hover:text-green-300'"
                            class="flex items-center gap-2 px-4 py-2 border rounded-lg transition text-sm">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M2 10.5a1.5 1.5 0 113 0v6a1.5 1.5 0 01-3 0v-6zM6 10.333v5.43a2 2 0 001.106 1.79l.05.025A4 4 0 008.943 18h5.416a2 2 0 001.962-1.608l1.2-6A2 2 0 0015.56 8H12V4a2 2 0 00-2-2 1 1 0 00-1 1v.667a4 4 0 01-.8 2.4L6.8 7.933a4 4 0 00-.8 2.4z"/></svg>
                        <span x-text="voted ? 'Voter annulé' : 'Soutenir'"></span>
                        <span class="font-bold" x-text="count"></span>
                    </button>
                </div>
                @endif
            @else
                <p class="text-sm text-hub-text-sec mt-4">
                    <a href="{{ route('login') }}" class="text-hub-primary hover:underline">Connectez-vous</a> pour soutenir cette amélioration.
                </p>
            @endauth
        @endif

        {{-- ══ QUESTIONNAIRE ══ --}}
        @if($article->type === 'questionnaire' && $article->survey)
            @php $survey = $article->survey; @endphp

            @if($survey->isClosed())
                <div class="p-4 bg-red-900/20 border border-red-700/30 rounded-xl text-red-300 text-sm mb-6">
                    Ce questionnaire est maintenant fermé. Merci à tous les participants.
                </div>
            @elseif(auth()->check() && \App\Models\SurveyResponse::where('survey_id', $survey->id)->where('user_id', auth()->id())->exists())
                <div class="p-4 bg-green-900/20 border border-green-700/30 rounded-xl text-green-300 text-sm mb-6">
                    Vous avez déjà répondu à ce questionnaire. Merci !
                </div>
            @else
                @auth
                    @if($survey->closes_at)
                        <p class="text-sm text-hub-text-sec mb-4">Fermeture le {{ $survey->closes_at->format('d/m/Y à H:i') }}</p>
                    @endif

                    <form method="POST" action="{{ route('survey.respond', $survey) }}" class="space-y-6">
                        @csrf
                        @foreach($survey->questions->sortBy('position') as $question)
                            <fieldset class="p-5 bg-hub-surface border border-hub-border rounded-xl">
                                <legend class="font-medium text-hub-text mb-4">{{ $question->label }}</legend>

                                @if($question->type === 'qcm')
                                    <div class="space-y-2">
                                        @foreach($question->options ?? [] as $option)
                                            <label class="flex items-center gap-3 cursor-pointer text-hub-text-sec hover:text-hub-text">
                                                <input type="radio" name="answers[q{{ $question->id }}]" value="{{ $option }}" required class="text-hub-primary">
                                                {{ $option }}
                                            </label>
                                        @endforeach
                                    </div>

                                @elseif($question->type === 'checkbox')
                                    <div class="space-y-2">
                                        @foreach($question->options ?? [] as $option)
                                            <label class="flex items-center gap-3 cursor-pointer text-hub-text-sec hover:text-hub-text">
                                                <input type="checkbox" name="answers[q{{ $question->id }}][]" value="{{ $option }}" class="text-hub-primary">
                                                {{ $option }}
                                            </label>
                                        @endforeach
                                    </div>

                                @elseif($question->type === 'text')
                                    <textarea name="answers[q{{ $question->id }}]" rows="3" required maxlength="2000"
                                              class="w-full bg-hub-bg border border-hub-border rounded-lg px-3 py-2 text-hub-text text-sm focus:ring-2 focus:ring-hub-primary/50 focus:border-hub-primary outline-none resize-none"
                                              placeholder="Votre réponse…"></textarea>

                                @elseif($question->type === 'rating')
                                    <div class="flex gap-2" x-data="{ rating: 0 }">
                                        @for($i = 1; $i <= 5; $i++)
                                            <label class="cursor-pointer">
                                                <input type="radio" name="answers[q{{ $question->id }}]" value="{{ $i }}" class="sr-only" required @click="rating = {{ $i }}">
                                                <span @click="rating = {{ $i }}" :class="rating >= {{ $i }} ? 'text-yellow-400' : 'text-hub-border'" class="text-2xl hover:text-yellow-300 transition">★</span>
                                            </label>
                                        @endfor
                                    </div>

                                @elseif($question->type === 'boolean')
                                    <div class="flex gap-4">
                                        <label class="flex items-center gap-2 cursor-pointer text-hub-text-sec hover:text-hub-text">
                                            <input type="radio" name="answers[q{{ $question->id }}]" value="1" required class="text-hub-primary">
                                            Oui
                                        </label>
                                        <label class="flex items-center gap-2 cursor-pointer text-hub-text-sec hover:text-hub-text">
                                            <input type="radio" name="answers[q{{ $question->id }}]" value="0" class="text-hub-primary">
                                            Non
                                        </label>
                                    </div>
                                @endif
                            </fieldset>
                        @endforeach

                        @error('answers.*') <p class="text-red-400 text-sm">{{ $message }}</p> @enderror

                        <div class="flex justify-end">
                            <button type="submit"
                                    class="px-6 py-2 bg-hub-primary text-white rounded-lg hover:bg-hub-primary/80 transition font-medium">
                                Envoyer mes réponses
                            </button>
                        </div>
                    </form>
                @else
                    <div class="p-4 bg-hub-surface border border-hub-border rounded-xl text-hub-text-sec text-sm">
                        <a href="{{ route('login') }}" class="text-hub-primary hover:underline">Connectez-vous</a> pour participer à ce questionnaire.
                    </div>
                @endauth
            @endif
        @endif

        {{-- Back --}}
        <div class="mt-10">
            <a href="{{ route('articles.index') }}" class="text-hub-text-sec hover:text-hub-primary transition text-sm">
                ← Retour aux articles
            </a>
        </div>

    </article>
</x-app-layout>

