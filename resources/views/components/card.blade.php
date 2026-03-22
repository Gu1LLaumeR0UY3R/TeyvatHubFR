@props(['rarete' => null, 'element' => null])

@php
    $stars     = $rarete  ? preg_replace('/[^0-9]/', '', (string) $rarete)  : '';
    $elemSlug  = $element ? strtolower($element) : '';
    $extra     = trim(($stars ? "rarity-{$stars}" : '') . ' ' . ($elemSlug ? "element-{$elemSlug}" : ''));
@endphp

<div {{ $attributes->merge(['class' => "card-entity bg-hub-surface rounded-xl overflow-hidden {$extra}"]) }}>
    {{ $slot }}
</div>
