<x-app-layout>
<x-slot name="title">{{ $article->titre_article }}</x-slot>

@php
    $featuredPhotos = $article->photos->where('type', 'featured')->values();
    $inlinePhotos = $article->photos->where('type', 'inline')->keyBy('id_photo');

    $renderInlineImages = function (?string $content) use ($article, $inlinePhotos) {
        $raw = (string) ($content ?? '');
        if ($raw === '') {
            return '';
        }

        $sizeClasses = [
            'small' => 'max-w-sm',
            'medium' => 'max-w-2xl',
            'large' => 'max-w-4xl',
            'full' => 'max-w-full',
        ];

        $pattern = '/\[\[image:(\d+)\|(small|medium|large|full)\]\]/i';
        $offset = 0;
        $chunks = [];

        preg_match_all($pattern, $raw, $matches, PREG_OFFSET_CAPTURE);

        foreach ($matches[0] as $idx => $fullMatch) {
            $matchedText = $fullMatch[0];
            $start = $fullMatch[1];

            if ($start > $offset) {
                $chunks[] = nl2br(e(substr($raw, $offset, $start - $offset)));
            }

            $photoId = (int) ($matches[1][$idx][0] ?? 0);
            $size = strtolower((string) ($matches[2][$idx][0] ?? 'medium'));
            $photo = $inlinePhotos->get($photoId);

            if (!$photo) {
                $chunks[] = e($matchedText);
                $offset = $start + strlen($matchedText);
                continue;
            }

            $sizeClass = $sizeClasses[$size] ?? $sizeClasses['medium'];
            $url = e((string) $article->resolvePhotoUrl($photo));
            $alt = e($article->titre_article);
            $chunks[] = '<figure class="my-6 w-full ' . $sizeClass . '"><img src="' . $url . '" alt="' . $alt . '" class="w-full rounded-2xl border border-white/10 object-cover shadow-xl"></figure>';

            $offset = $start + strlen($matchedText);
        }

        if ($offset < strlen($raw)) {
            $chunks[] = nl2br(e(substr($raw, $offset)));
        }

        return implode('', $chunks);
    };
@endphp

<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <a href="{{ route('blog.index') }}" class="inline-flex mb-6 text-sm text-hub-text-sec hover:text-hub-primary">← Retour au blog</a>

    <article class="rounded-2xl border border-hub-border bg-hub-surface p-6 sm:p-8">
        @if($featuredPhotos->count())
            <div class="mb-6 grid gap-4 {{ $featuredPhotos->count() > 1 ? 'md:grid-cols-2' : '' }}">
                @foreach($featuredPhotos as $featuredPhoto)
                    <div class="overflow-hidden rounded-2xl border border-white/10">
                        <img src="{{ $article->resolvePhotoUrl($featuredPhoto) }}" alt="{{ $article->titre_article }}" class="h-72 w-full object-cover">
                    </div>
                @endforeach
            </div>
        @endif

        <div class="text-xs text-hub-text-sec mb-2">
            {{ optional($article->date_publication)->format('d/m/Y H:i') ?? optional($article->created_at)->format('d/m/Y H:i') }}
        </div>
        <h1 class="text-3xl font-bold text-hub-text leading-tight mb-4">{{ $article->titre_article }}</h1>

        @if($article->extrait)
            <p class="text-hub-text-sec leading-relaxed mb-6 text-lg">{{ $article->extrait }}</p>
        @endif

        <div class="prose prose-invert max-w-none text-hub-text-sec leading-relaxed">
            {!! $renderInlineImages($article->contenu_article) !!}
        </div>
    </article>
</div>
</x-app-layout>
