<x-app-layout>
<x-slot name="title">Comparateur de personnages</x-slot>
<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8"
     x-data="{
         personnages: {{ json_encode($personnages->map(fn($p) => [
             'id'       => $p->getKey(),
             'nom'      => $p->nom_perso,
             'slug'     => $p->slug,
             'element'  => $p->element?->libelle_element ?? '',
             'etoile'   => $p->etoile?->libelle ?? '',
             'photo'    => $p->photos->first()?->source_url ?? $p->photos->first()?->chemin_photo ?? '',
         ])->values()) }},
         perso1: null,
         perso2: null,
         find(id) { return this.personnages.find(p => p.id == id); }
     }">

    <h1 class="text-3xl font-bold text-hub-text mb-2">Comparateur</h1>
    <p class="text-hub-text-sec mb-8">Comparez deux personnages côte à côte.</p>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 mb-8">
        {{-- Sélecteur 1 --}}
        <div>
            <label class="block text-hub-text-sec text-sm mb-1">Personnage 1</label>
            <select x-model="perso1"
                    class="w-full bg-hub-surface border border-hub-border rounded-xl px-4 py-2 text-hub-text focus:outline-none focus:border-hub-primary">
                <option value="">Choisir...</option>
                @foreach($personnages as $p)
                    <option value="{{ $p->getKey() }}">{{ $p->nom_perso }}</option>
                @endforeach
            </select>
        </div>
        {{-- Sélecteur 2 --}}
        <div>
            <label class="block text-hub-text-sec text-sm mb-1">Personnage 2</label>
            <select x-model="perso2"
                    class="w-full bg-hub-surface border border-hub-border rounded-xl px-4 py-2 text-hub-text focus:outline-none focus:border-hub-primary">
                <option value="">Choisir...</option>
                @foreach($personnages as $p)
                    <option value="{{ $p->getKey() }}">{{ $p->nom_perso }}</option>
                @endforeach
            </select>
        </div>
    </div>

    {{-- Comparaison côte à côte --}}
    <template x-if="perso1 || perso2">
        <div class="grid grid-cols-2 gap-6">
            <template x-if="perso1">
                <div class="bg-hub-surface border border-hub-border rounded-2xl p-6 flex flex-col items-center gap-3">
                    <img :src="find(perso1)?.photo || '{{ asset('images/placeholder.svg') }}'"
                         :alt="find(perso1)?.nom"
                         class="w-32 h-32 object-contain rounded-xl">
                    <p class="text-hub-text font-bold text-lg text-center" x-text="find(perso1)?.nom"></p>
                    <p class="text-hub-text-sec text-sm" x-text="find(perso1)?.element"></p>
                    <p class="text-hub-gold text-sm" x-text="find(perso1)?.etoile"></p>
                    <a :href="'/personnages/' + find(perso1)?.slug"
                       class="text-hub-primary text-sm hover:underline">Voir la fiche →</a>
                </div>
            </template>

            <template x-if="perso2">
                <div class="bg-hub-surface border border-hub-border rounded-2xl p-6 flex flex-col items-center gap-3">
                    <img :src="find(perso2)?.photo || '{{ asset('images/placeholder.svg') }}'"
                         :alt="find(perso2)?.nom"
                         class="w-32 h-32 object-contain rounded-xl">
                    <p class="text-hub-text font-bold text-lg text-center" x-text="find(perso2)?.nom"></p>
                    <p class="text-hub-text-sec text-sm" x-text="find(perso2)?.element"></p>
                    <p class="text-hub-gold text-sm" x-text="find(perso2)?.etoile"></p>
                    <a :href="'/personnages/' + find(perso2)?.slug"
                       class="text-hub-primary text-sm hover:underline">Voir la fiche →</a>
                </div>
            </template>
        </div>
    </template>

</div>
</x-app-layout>
