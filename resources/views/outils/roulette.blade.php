<x-app-layout>
<x-slot name="title">Roulette de personnages</x-slot>
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8" x-data="{
    spinning: false,
    result: null,
    personnages: {{ json_encode($personnages->map(fn($p) => ['nom' => $p->nom_perso, 'slug' => $p->slug, 'photo' => $p->photos->first()?->source_url ?? $p->photos->first()?->chemin_photo ?? ''])->values()) }},
    spin() {
        this.spinning = true;
        this.result = null;
        setTimeout(() => {
            this.result = this.personnages[Math.floor(Math.random() * this.personnages.length)];
            this.spinning = false;
        }, 1500);
    }
}">

    <h1 class="text-3xl font-bold text-hub-text mb-2 text-center">Roulette</h1>
    <p class="text-hub-text-sec mb-8 text-center">Quel personnage allez-vous jouer ?</p>

    {{-- Résultat --}}
    <div class="text-center mb-8">
        <div class="inline-flex items-center justify-center w-48 h-48 rounded-2xl border-2 border-hub-border bg-hub-surface mb-4"
             :class="spinning ? 'animate-pulse border-hub-primary' : ''">
            <template x-if="result && !spinning">
                <img :src="result.photo || '{{ asset('images/placeholder.svg') }}'"
                     :alt="result.nom"
                     class="w-44 h-44 object-contain rounded-2xl">
            </template>
            <template x-if="!result && !spinning">
                <span class="text-hub-text-sec text-6xl">?</span>
            </template>
            <template x-if="spinning">
                <span class="text-hub-primary text-4xl">⟳</span>
            </template>
        </div>

        <div x-show="result && !spinning" class="mb-4">
            <p class="text-2xl font-bold text-hub-text" x-text="result?.nom"></p>
            <a :href="'/personnages/' + result?.slug"
               class="text-hub-primary hover:underline text-sm mt-1 inline-block">
                Voir la fiche →
            </a>
        </div>

        <button @click="spin()"
                :disabled="spinning"
                class="px-8 py-3 bg-hub-primary text-white rounded-xl font-bold hover:bg-opacity-90 transition-all disabled:opacity-50">
            <span x-show="!spinning">Tourner !</span>
            <span x-show="spinning">En cours...</span>
        </button>
    </div>

    {{-- Sauvegarder préférence --}}
    <div class="bg-hub-surface border border-hub-border rounded-2xl p-6">
        <h2 class="text-lg font-bold text-hub-text mb-3">Sauvegarder une préférence</h2>
        <form method="POST" action="{{ route('outils.roulette.sauvegarder') }}" class="flex gap-3">
            @csrf
            @method('PATCH')
            <input type="text"
                   name="preference"
                   value=""
                   placeholder="Ma préférence (ex: Pyro uniquement)"
                   class="flex-1 bg-hub-surface-hover border border-hub-border rounded-xl px-4 py-2 text-hub-text focus:outline-none focus:border-hub-primary">
            <button type="submit" class="px-4 py-2 bg-hub-primary text-white rounded-xl">Sauvegarder</button>
        </form>
        @if(session('success'))
            <p class="text-green-400 text-sm mt-2">{{ session('success') }}</p>
        @endif
    </div>

</div>
</x-app-layout>
