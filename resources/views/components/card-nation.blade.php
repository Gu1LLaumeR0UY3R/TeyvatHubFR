@props(['nation'])

<a href="{{ route('nations.show', $nation->slug) }}"
   class="group block card-entity bg-hub-surface rounded-xl overflow-hidden">
    <div class="aspect-square overflow-hidden bg-hub-surface-hover p-4">
        <img src="{{ $nation->icone_url }}"
             alt="{{ $nation->nom_region }}"
             class="w-full h-full object-contain group-hover:scale-105 transition-transform duration-200"
             loading="lazy">
    </div>
    <div class="p-3 text-center">
        <p class="font-semibold text-hub-text text-sm">{{ $nation->nom_region }}</p>
    </div>
</a>
