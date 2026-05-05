{{--
    block-renderer.blade.php
    Usage: @include('partials.block-renderer', ['node' => $article->content])
    Converts TipTap JSON doc structure to HTML.
    $node is an array (TipTap doc or any node).
--}}
@php
use Illuminate\Support\Str;

/**
 * Apply marks (bold, italic, etc.) to a text string.
 */
function thApplyMarks(string $text, array $marks): string
{
    foreach ($marks as $mark) {
        $type = $mark['type'] ?? '';
        $attrs = $mark['attrs'] ?? [];
        $text = match ($type) {
            'bold'      => "<strong>{$text}</strong>",
            'italic'    => "<em>{$text}</em>",
            'strike'    => "<s>{$text}</s>",
            'underline' => "<u>{$text}</u>",
            'code'      => "<code>{$text}</code>",
            'link'      => (function() use ($text, $attrs) {
                $href = htmlspecialchars($attrs['href'] ?? '#', ENT_QUOTES, 'UTF-8');
                $target = !empty($attrs['target']) ? ' target="'.htmlspecialchars($attrs['target'], ENT_QUOTES)."\"" : '';
                return "<a href=\"{$href}\"{$target} rel=\"noopener\">{$text}</a>";
            })(),
            'textStyle' => (function() use ($text, $attrs) {
                $style = '';
                if (!empty($attrs['color'])) $style .= 'color:'.htmlspecialchars($attrs['color'], ENT_QUOTES).';';
                return $style ? "<span style=\"{$style}\">{$text}</span>" : $text;
            })(),
            default => $text,
        };
    }
    return $text;
}

/**
 * Render a TipTap inline or block node recursively.
 */
function thRenderNode(array|null $node): string
{
    if (!$node || empty($node['type'])) return '';

    $type    = $node['type'];
    $attrs   = $node['attrs'] ?? [];
    $content = $node['content'] ?? [];

    // Inline text
    if ($type === 'text') {
        $text = htmlspecialchars($node['text'] ?? '', ENT_QUOTES, 'UTF-8');
        $marks = $node['marks'] ?? [];
        return $marks ? thApplyMarks($text, $marks) : $text;
    }

    // Render all children first
    $inner = implode('', array_map('thRenderNode', $content));

    return match ($type) {
        'doc' => $inner,

        'paragraph' => $inner !== '' ? "<p>{$inner}</p>" : '<p>&nbsp;</p>',

        'heading' => (function() use ($attrs, $inner) {
            $level = min(max((int)($attrs['level'] ?? 2), 1), 6);
            $align = !empty($attrs['textAlign']) ? " style=\"text-align:{$attrs['textAlign']}\"" : '';
            return "<h{$level}{$align}>{$inner}</h{$level}>";
        })(),

        'bulletList'  => "<ul>{$inner}</ul>",
        'orderedList' => "<ol>{$inner}</ol>",
        'listItem'    => "<li>{$inner}</li>",

        'blockquote' => "<blockquote>{$inner}</blockquote>",

        'codeBlock' => (function() use ($attrs, $inner, $content) {
            // codeBlock wraps a text node
            $rawText = '';
            foreach ($content as $child) {
                if ($child['type'] === 'text') $rawText .= $child['text'] ?? '';
            }
            $lang = !empty($attrs['language']) ? ' class="language-'.htmlspecialchars($attrs['language'], ENT_QUOTES).'"' : '';
            $escaped = htmlspecialchars($rawText, ENT_QUOTES, 'UTF-8');
            return "<pre><code{$lang}>{$escaped}</code></pre>";
        })(),

        'horizontalRule' => '<hr>',

        'hardBreak' => '<br>',

        // customImage
        'customImage' => (function() use ($attrs) {
            $src       = htmlspecialchars($attrs['src'] ?? '', ENT_QUOTES, 'UTF-8');
            $alt       = htmlspecialchars($attrs['alt'] ?? '', ENT_QUOTES, 'UTF-8');
            $alignment = htmlspecialchars($attrs['alignment'] ?? 'center', ENT_QUOTES, 'UTF-8');
            $caption   = htmlspecialchars($attrs['caption'] ?? '', ENT_QUOTES, 'UTF-8');
            $figClass  = "th-image th-image-{$alignment}";
            $captionHtml = $caption ? "<figcaption>{$caption}</figcaption>" : '';
            return "<figure class=\"{$figClass}\"><img src=\"{$src}\" alt=\"{$alt}\" loading=\"lazy\">{$captionHtml}</figure>";
        })(),

        // Generic image (TipTap built-in, fallback)
        'image' => (function() use ($attrs) {
            $src = htmlspecialchars($attrs['src'] ?? '', ENT_QUOTES, 'UTF-8');
            $alt = htmlspecialchars($attrs['alt'] ?? '', ENT_QUOTES, 'UTF-8');
            return "<figure class=\"th-image th-image-center\"><img src=\"{$src}\" alt=\"{$alt}\" loading=\"lazy\"></figure>";
        })(),

        // Table
        'table'       => "<div class=\"th-table-wrap\"><table>{$inner}</table></div>",
        'tableRow'    => "<tr>{$inner}</tr>",
        'tableHeader' => (function() use ($attrs, $inner) {
            $align = !empty($attrs['textAlign']) ? " style=\"text-align:{$attrs['textAlign']}\"" : '';
            return "<th{$align}>{$inner}</th>";
        })(),
        'tableCell'   => (function() use ($attrs, $inner) {
            $align = !empty($attrs['textAlign']) ? " style=\"text-align:{$attrs['textAlign']}\"" : '';
            return "<td{$align}>{$inner}</td>";
        })(),

        // Columns layout
        'columns' => "<div class=\"th-columns\">{$inner}</div>",
        'column'  => "<div class=\"th-column\">{$inner}</div>",

        default => $inner ?: '',
    };
}
@endphp

@if(!empty($node))
    {!! thRenderNode($node) !!}
@endif
