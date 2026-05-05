{{-- Bloc commentaires à inclure dans la vue show d'un article --}}
{{-- Usage : @include('articles.partials.comments', ['article' => $article]) --}}

<section class="mt-12" id="commentaires">
    <h2 class="text-xl font-bold text-hub-text mb-6">
        Commentaires
        @if($approvedCount = $article->comments()->approved()->count())
            <span class="text-hub-text-sec font-normal text-base">({{ $approvedCount }})</span>
        @endif
    </h2>

    {{-- Formulaire --}}
    <div class="mb-8 bg-hub-surface border border-hub-border rounded-xl p-5">
        @auth
            <form method="POST" action="{{ route('articles.comment.store', $article) }}">
                @csrf
                <label class="block text-sm text-hub-text-sec mb-2">Laisser un commentaire</label>
                <textarea
                    name="content"
                    rows="4"
                    maxlength="1000"
                    placeholder="Votre commentaire (max 1000 caractères)…"
                    class="w-full bg-hub-bg border border-hub-border rounded-lg px-3 py-2 text-hub-text text-sm resize-y focus:outline-none focus:ring-1 focus:ring-hub-accent @error('content') border-red-500 @enderror"
                >{{ old('content') }}</textarea>
                @error('content')
                    <p class="mt-1 text-red-400 text-xs">{{ $message }}</p>
                @enderror
                @error('comment')
                    <p class="mt-1 text-red-400 text-xs">{{ $message }}</p>
                @enderror
                @if(session('success'))
                    <p class="mt-1 text-green-400 text-xs">{{ session('success') }}</p>
                @endif
                <div class="flex justify-end mt-3">
                    <button type="submit"
                            class="px-5 py-2 bg-hub-accent text-white rounded-lg text-sm font-medium hover:opacity-90 transition">
                        Publier
                    </button>
                </div>
            </form>
        @else
            <p class="text-hub-text-sec text-sm">
                <a href="{{ route('login') }}" class="text-hub-accent hover:underline">Connectez-vous</a>
                pour laisser un commentaire.
            </p>
        @endauth
    </div>

    {{-- Liste des commentaires approuvés --}}
    @php
        $comments = $article->comments()->approved()->with('user:id,pseudo,avatar')->latest()->get();
    @endphp

    <div class="space-y-4">
        @forelse($comments as $comment)
            <div class="flex gap-4">
                {{-- Avatar --}}
                <div class="shrink-0">
                    @if($comment->user?->avatar)
                        <img src="{{ $comment->user->avatar }}" alt="{{ $comment->display_name }}"
                             class="w-9 h-9 rounded-full object-cover border border-hub-border">
                    @else
                        <div class="w-9 h-9 rounded-full bg-hub-surface border border-hub-border flex items-center justify-center text-hub-text-sec text-xs font-bold">
                            {{ strtoupper(mb_substr($comment->display_name, 0, 1)) }}
                        </div>
                    @endif
                </div>

                {{-- Contenu --}}
                <div class="flex-1 bg-hub-surface border border-hub-border rounded-xl px-4 py-3">
                    <div class="flex items-center gap-2 mb-1">
                        <span class="text-hub-text text-sm font-semibold">{{ $comment->display_name }}</span>
                        <span class="text-hub-text-sec text-xs">{{ $comment->created_at->diffForHumans() }}</span>
                    </div>
                    {{-- Blade échappe automatiquement — contenu déjà sanitisé à l'enregistrement --}}
                    <p class="text-hub-text text-sm leading-relaxed">{{ $comment->content }}</p>
                </div>
            </div>
        @empty
            <p class="text-hub-text-sec text-sm text-center py-8">
                Aucun commentaire pour le moment. Soyez le premier !
            </p>
        @endforelse
    </div>
</section>
