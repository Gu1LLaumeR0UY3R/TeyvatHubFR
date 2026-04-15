<x-app-layout>
<x-slot name="title">Blog</x-slot>

<div class="max-w-[120rem] mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-hub-text mb-2">Blog</h1>
        <p class="text-hub-text-sec">Actualités, guides et articles autour de Teyvat.</p>
        <div class="mt-4">
            <a href="{{ route('admin.blog.index') }}" class="inline-flex items-center rounded-lg border border-hub-border px-3 py-2 text-sm text-hub-text-sec hover:text-hub-primary hover:border-hub-primary transition-colors">
                Gérer les articles (admin)
            </a>
        </div>
    </div>

    <form method="GET" action="{{ route('blog.index') }}" class="mb-6">
        <input type="text" name="search" value="{{ request('search') }}"
               placeholder="Rechercher un article..."
               class="w-full px-4 py-2 bg-hub-surface border border-hub-border rounded-lg text-hub-text placeholder-hub-text-sec focus:outline-none focus:ring-2 focus:ring-hub-primary">
    </form>

    @if($articles->count())
        <div class="grid gap-5 sm:grid-cols-2 xl:grid-cols-3">
            @foreach($articles as $article)
                @php $featuredPhoto = $article->photos->firstWhere('type', 'featured'); @endphp
                <article class="rounded-2xl border border-hub-border bg-hub-surface p-5">
                    @if($featuredPhoto)
                        <a href="{{ route('blog.show', $article) }}" class="mb-4 block overflow-hidden rounded-xl">
                            <img src="{{ $article->resolvePhotoUrl($featuredPhoto) }}" alt="{{ $article->titre_article }}" class="h-52 w-full object-cover transition-transform duration-300 hover:scale-[1.02]">
                        </a>
                    @endif
                    <div class="text-xs text-hub-text-sec mb-2">
                        {{ optional($article->date_publication)->format('d/m/Y H:i') ?? optional($article->created_at)->format('d/m/Y H:i') }}
                    </div>
                    <h2 class="text-xl font-bold text-hub-text mb-2 leading-tight">
                        <a href="{{ route('blog.show', $article) }}" class="hover:text-hub-primary transition-colors">
                            {{ $article->titre_article }}
                        </a>
                    </h2>
                    <p class="text-hub-text-sec text-sm leading-relaxed line-clamp-4">
                        {{ $article->extrait ?: \Illuminate\Support\Str::limit(strip_tags($article->contenu_article), 170) }}
                    </p>
                    <a href="{{ route('blog.show', $article) }}" class="inline-flex mt-4 text-sm font-semibold text-hub-primary hover:text-hub-accent">
                        Lire l'article →
                    </a>
                </article>
            @endforeach
        </div>

        <div class="mt-8">
            {{ $articles->links() }}
        </div>
    @else
        <div class="rounded-2xl border border-dashed border-hub-border p-10 text-center text-hub-text-sec">
            Aucun article publié pour le moment.
        </div>
    @endif
</div>
</x-app-layout>
