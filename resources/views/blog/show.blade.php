<x-app-layout>
<x-slot name="title">{{ $article->titre_article }}</x-slot>

@php
    $featuredPhotos = $article->photos->where('type', 'featured')->values();
    $inlinePhotos   = $article->photos->where('type', 'inline')->keyBy('id_photo');
    $layout         = is_array($article->layout_json ?? null) ? $article->layout_json : null;
    $layoutBlocks   = collect($layout['blocks'] ?? [])
        ->filter(fn($block) => is_array($block) && filled($block['type'] ?? null))
        ->values();

    $renderInlineImages = function (?string $content) use ($article, $inlinePhotos) {
        $raw = (string) ($content ?? '');
        if ($raw === '') return '';

        $sizeClasses = [
            'small'  => 'max-w-sm',
            'medium' => 'max-w-2xl',
            'large'  => 'max-w-4xl',
            'full'   => 'max-w-full',
        ];

        $pattern = '/\[\[image:(\d+)\|(small|medium|large|full)\]\]/i';
        $offset  = 0;
        $chunks  = [];

        preg_match_all($pattern, $raw, $matches, PREG_OFFSET_CAPTURE);

        foreach ($matches[0] as $idx => $fullMatch) {
            $matchedText = $fullMatch[0];
            $start       = $fullMatch[1];

            if ($start > $offset) {
                $chunks[] = nl2br(e(substr($raw, $offset, $start - $offset)));
            }

            $photoId = (int) ($matches[1][$idx][0] ?? 0);
            $size    = strtolower((string) ($matches[2][$idx][0] ?? 'medium'));
            $photo   = $inlinePhotos->get($photoId);

            if (!$photo) {
                $chunks[] = e($matchedText);
                $offset   = $start + strlen($matchedText);
                continue;
            }

            $sizeClass = $sizeClasses[$size] ?? $sizeClasses['medium'];
            $url       = e((string) $article->resolvePhotoUrl($photo));
            $alt       = e($article->titre_article);
            $chunks[]  = '<figure class="my-6 w-full ' . $sizeClass . '"><img src="' . $url . '" alt="' . $alt . '" class="w-full rounded-2xl border border-white/10 object-cover shadow-xl"></figure>';

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

        @if($layoutBlocks->count())
            <div class="space-y-2">
                @foreach($layoutBlocks as $block)
                    @php
                        $type  = (string) ($block['type'] ?? 'text');
                        $text  = (string) ($block['text'] ?? '');
                        $align = in_array(($block['align'] ?? 'left'), ['left', 'center', 'right']) ? $block['align'] : 'left';
                        $alignClass = match($align) { 'center' => 'text-center', 'right' => 'text-right', default => 'text-left' };
                    @endphp

                    @if($type === 'heading')
                        @php
                            $level      = max(2, min(4, (int) ($block['level'] ?? 2)));
                            $levelClass = match($level) {
                                2       => 'text-2xl font-bold mt-8 mb-3',
                                3       => 'text-xl font-bold mt-6 mb-2',
                                default => 'text-lg font-semibold mt-5 mb-2',
                            };
                            $headingMarkdown = \Illuminate\Support\Str::markdown($text, ['html_input' => 'strip', 'allow_unsafe_links' => false]);
                            $headingHtml = trim((string) preg_replace('/^<p>(.*)<\/p>$/s', '$1', trim($headingMarkdown)));
                        @endphp
                        <h{{ $level }} class="{{ $levelClass }} {{ $alignClass }} text-hub-text">{!! $headingHtml !!}</h{{ $level }}>

                    @elseif($type === 'text')
                        <div class="mb-2 leading-relaxed text-hub-text-sec {{ $alignClass }} prose max-w-none prose-headings:text-hub-text prose-p:text-hub-text-sec prose-strong:text-hub-text prose-a:text-hub-primary">
                            {!! \Illuminate\Support\Str::markdown($text, ['html_input' => 'strip', 'allow_unsafe_links' => false]) !!}
                        </div>

                    @elseif($type === 'quote')
                        <blockquote class="my-4 border-l-4 border-hub-gold py-1 pl-5 italic text-hub-text-sec {{ $alignClass }}">
                            {!! \Illuminate\Support\Str::markdown($text, ['html_input' => 'strip', 'allow_unsafe_links' => false]) !!}
                        </blockquote>

                    @elseif($type === 'columns')
                        @php
                            $columns = in_array((int) ($block['columns'] ?? 2), [2, 3], true) ? (int) ($block['columns'] ?? 2) : 2;
                            $col1 = (string) ($block['col1'] ?? '');
                            $col2 = (string) ($block['col2'] ?? '');
                            $col3 = (string) ($block['col3'] ?? '');
                        @endphp
                        <div class="my-6 grid gap-4 {{ $columns === 3 ? 'md:grid-cols-3' : 'md:grid-cols-2' }}">
                            <div class="rounded-xl border border-white/10 bg-black/10 p-4 prose max-w-none prose-headings:text-hub-text prose-p:text-hub-text-sec prose-strong:text-hub-text prose-a:text-hub-primary">
                                {!! \Illuminate\Support\Str::markdown($col1 ?: 'Colonne 1 vide', ['html_input' => 'strip', 'allow_unsafe_links' => false]) !!}
                            </div>
                            <div class="rounded-xl border border-white/10 bg-black/10 p-4 prose max-w-none prose-headings:text-hub-text prose-p:text-hub-text-sec prose-strong:text-hub-text prose-a:text-hub-primary">
                                {!! \Illuminate\Support\Str::markdown($col2 ?: 'Colonne 2 vide', ['html_input' => 'strip', 'allow_unsafe_links' => false]) !!}
                            </div>
                            @if($columns === 3)
                                <div class="rounded-xl border border-white/10 bg-black/10 p-4 prose max-w-none prose-headings:text-hub-text prose-p:text-hub-text-sec prose-strong:text-hub-text prose-a:text-hub-primary">
                                    {!! \Illuminate\Support\Str::markdown($col3 ?: 'Colonne 3 vide', ['html_input' => 'strip', 'allow_unsafe_links' => false]) !!}
                                </div>
                            @endif
                        </div>

                    @elseif($type === 'divider')
                        <hr class="my-6 border-hub-border">

                    @elseif($type === 'image')
                        @php
                            $photoId    = (int) ($block['photoId'] ?? 0);
                            $photo      = $inlinePhotos->get($photoId);
                            $fallbackUrl = (string) ($block['url'] ?? '');
                            $isSafeFallback = str_starts_with($fallbackUrl, 'data:image/')
                                || filter_var($fallbackUrl, FILTER_VALIDATE_URL);
                            $photoUrl   = $photo ? $article->resolvePhotoUrl($photo) : ($isSafeFallback ? $fallbackUrl : null);
                            $caption    = (string) ($block['caption'] ?? '');
                            $sizeClass  = match($block['size'] ?? 'medium') {
                                'small'  => 'max-w-sm',
                                'large'  => 'max-w-3xl',
                                'full'   => 'w-full max-w-none',
                                default  => 'max-w-xl',
                            };
                        @endphp
                        @if($photoUrl)
                            <figure class="my-6 {{ $sizeClass }} mx-auto">
                                <img src="{{ $photoUrl }}"
                                     alt="{{ $caption ?: $article->titre_article }}"
                                     class="w-full rounded-2xl border border-white/10 object-cover shadow-xl">
                                @if($caption)
                                    <figcaption class="mt-2 text-center text-xs text-hub-text-sec prose max-w-none prose-p:m-0 prose-strong:text-hub-text prose-em:text-hub-text-sec prose-a:text-hub-primary">
                                        {!! \Illuminate\Support\Str::markdown($caption, ['html_input' => 'strip', 'allow_unsafe_links' => false]) !!}
                                    </figcaption>
                                @endif
                            </figure>
                        @endif
                    @endif
                @endforeach
            </div>
        @else
            <div class="prose prose-invert max-w-none text-hub-text-sec leading-relaxed">
                {!! $renderInlineImages($article->contenu_article) !!}
            </div>
        @endif
    </article>
</div>
</x-app-layout>
