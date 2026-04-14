<x-app-layout>
<x-slot name="title">{{ $article->titre_article }}</x-slot>

<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <a href="{{ route('blog.index') }}" class="inline-flex mb-6 text-sm text-hub-text-sec hover:text-hub-primary">← Retour au blog</a>

    <article class="rounded-2xl border border-hub-border bg-hub-surface p-6 sm:p-8">
        <div class="text-xs text-hub-text-sec mb-2">
            {{ optional($article->date_publication)->format('d/m/Y H:i') ?? optional($article->created_at)->format('d/m/Y H:i') }}
        </div>
        <h1 class="text-3xl font-bold text-hub-text leading-tight mb-4">{{ $article->titre_article }}</h1>

        @if($article->extrait)
            <p class="text-hub-text-sec leading-relaxed mb-6 text-lg">{{ $article->extrait }}</p>
        @endif

        <div class="prose prose-invert max-w-none text-hub-text-sec leading-relaxed whitespace-pre-line">
            {{ $article->contenu_article }}
        </div>
    </article>
</div>
</x-app-layout>
