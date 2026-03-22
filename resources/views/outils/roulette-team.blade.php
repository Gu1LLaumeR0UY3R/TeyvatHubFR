<x-app-layout>
<x-slot name="title">Roulette Team</x-slot>
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8"
     x-data="{
         mode: 'aleatoire',
         filtre: '',
         team: [],
         loading: false,
         message: '',
         async generer() {
             this.loading = true;
             this.message = '';
             this.team = [];
             const res = await fetch('{{ route('outils.roulette-team.generer') }}', {
                 method: 'POST',
                 headers: {
                     'Content-Type': 'application/json',
                     'Accept': 'application/json',
                     'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
                 },
                 body: JSON.stringify({ mode: this.mode, filtre: this.filtre })
             });
             const data = await res.json();
             this.loading = false;
             if (data.success) {
                 this.team = data.team;
             } else {
                 this.message = data.message;
             }
         }
     }">

    <div class="mb-8">
        <h1 class="text-3xl font-bold text-hub-text mb-2">Roulette Team</h1>
        <p class="text-hub-text-sec">Génère une équipe de 4 personnages depuis ton roster</p>
    </div>

    @if($personnages->isEmpty())
        <div class="text-center py-16 bg-hub-surface border border-hub-border rounded-2xl">
            <p class="text-hub-text-sec text-lg">Aucun personnage dans votre roster.</p>
        </div>
    @else
        {{-- Modes --}}
        <div class="bg-hub-surface border border-hub-border rounded-2xl p-6 mb-6">
            <h2 class="text-lg font-bold text-hub-text mb-4">Mode de génération</h2>
            <div class="flex flex-wrap gap-3 mb-4">
                <button @click="mode = 'aleatoire'; filtre = ''"
                        :class="mode === 'aleatoire' ? 'bg-hub-primary text-white' : 'bg-hub-surface-hover text-hub-text border border-hub-border'"
                        class="px-4 py-2 rounded-lg font-medium transition-colors">
                    Aléatoire pur
                </button>
                <button @click="mode = 'reaction'"
                        :class="mode === 'reaction' ? 'bg-hub-primary text-white' : 'bg-hub-surface-hover text-hub-text border border-hub-border'"
                        class="px-4 py-2 rounded-lg font-medium transition-colors">
                    Par réaction
                </button>
                <button @click="mode = 'element'"
                        :class="mode === 'element' ? 'bg-hub-primary text-white' : 'bg-hub-surface-hover text-hub-text border border-hub-border'"
                        class="px-4 py-2 rounded-lg font-medium transition-colors">
                    Mono-élément
                </button>
            </div>

            {{-- Filtre réaction --}}
            <div x-show="mode === 'reaction'" class="mb-4">
                <label class="text-hub-text-sec text-sm block mb-2">Réaction élémentaire</label>
                <select x-model="filtre"
                        class="px-4 py-2 bg-hub-surface-hover border border-hub-border rounded-lg text-hub-text focus:outline-none focus:ring-2 focus:ring-hub-primary">
                    <option value="">Choisir une réaction</option>
                    @foreach($reactions as $reaction)
                        <option value="{{ $reaction }}">{{ $reaction }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Filtre élément --}}
            <div x-show="mode === 'element'" class="mb-4">
                <label class="text-hub-text-sec text-sm block mb-2">Élément</label>
                <select x-model="filtre"
                        class="px-4 py-2 bg-hub-surface-hover border border-hub-border rounded-lg text-hub-text focus:outline-none focus:ring-2 focus:ring-hub-primary">
                    <option value="">Choisir un élément</option>
                    @foreach(['Pyro','Hydro','Cryo','Electro','Dendro','Anemo','Geo'] as $el)
                        <option value="{{ $el }}">{{ $el }}</option>
                    @endforeach
                </select>
            </div>

            <button @click="generer()"
                    :disabled="loading"
                    class="px-8 py-3 bg-hub-primary hover:bg-hub-primary-hover text-white rounded-xl font-semibold transition-colors disabled:opacity-50">
                <span x-show="!loading">Générer la team</span>
                <span x-show="loading">Génération...</span>
            </button>
        </div>

        {{-- Message erreur --}}
        <div x-show="message" class="bg-red-900/50 border border-red-700 text-red-300 rounded-xl p-4 mb-6">
            <p x-text="message"></p>
        </div>

        {{-- Résultat team --}}
        <div x-show="team.length > 0" class="bg-hub-surface border border-hub-border rounded-2xl p-6">
            <h2 class="text-lg font-bold text-hub-text mb-4">Votre team</h2>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                <template x-for="perso in team" :key="perso.id">
                    <div class="text-center bg-hub-surface-hover border border-hub-border rounded-xl p-4">
                        <img :src="perso.icone_url" :alt="perso.nom"
                             class="w-20 h-20 mx-auto object-contain mb-2 rounded-lg">
                        <p class="font-semibold text-hub-text text-sm" x-text="perso.nom"></p>
                        <p class="text-hub-text-sec text-xs" x-text="perso.element ?? ''"></p>
                    </div>
                </template>
            </div>
        </div>
    @endif

</div>
</x-app-layout>
