<x-app-layout>
<x-slot name="title">Roulette Personnage</x-slot>
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8"
     x-data="{
         selected: null,
         animating: false,
         currentIndex: 0,
         intervalId: null,
         personalityList: {{ Js::from($personnages->map(fn($p) => ['id' => $p->getKey(), 'nom' => $p->nom_perso, 'arme_icon' => $p->arme_icon])->values()) }},
         spin() {
             if (this.personalityList.length === 0) return;
             this.animating = true;
             this.selected = null;
             let count = 0;
             const max = 20 + Math.floor(Math.random() * 15);
             this.intervalId = setInterval(() => {
                 this.currentIndex = Math.floor(Math.random() * this.personalityList.length);
                 count++;
                 if (count >= max) {
                     clearInterval(this.intervalId);
                     this.selected = this.personalityList[this.currentIndex];
                     this.animating = false;
                 }
             }, 80);
         }
     }">

    <div class="mb-8">
        <h1 class="text-3xl font-bold text-hub-text mb-2">Roulette Personnage</h1>
        <p class="text-hub-text-sec">Tire un personnage aléatoire de ton roster à monter en priorité</p>
    </div>

    @if(session('success'))
        <div class="bg-green-900/50 border border-green-700 text-green-300 rounded-xl p-4 mb-6">
            {{ session('success') }}
        </div>
    @endif

    @if($personnages->isEmpty())
        <div class="text-center py-16 bg-hub-surface border border-hub-border rounded-2xl">
            <p class="text-hub-text-sec text-lg">Aucun personnage dans votre roster.</p>
            <p class="text-hub-text-sec text-sm mt-2">Importez votre UID depuis votre profil.</p>
        </div>
    @else
        {{-- Slot machine --}}
        <div class="bg-hub-surface border border-hub-border rounded-2xl p-8 mb-6 text-center">
            <div class="w-40 h-40 mx-auto mb-6 rounded-2xl overflow-hidden bg-hub-surface-hover border-2 border-hub-border"
                 :class="animating ? 'animate-pulse' : ''">
                <img :src="animating ? personalityList[currentIndex]?.icone_url : (selected?.icone_url ?? '{{ asset('images/placeholder.svg') }}')"
                     :alt="animating ? '' : (selected?.nom ?? 'Clique sur Lancer')"
                     class="w-full h-full object-contain">
            </div>

            <p class="text-xl font-bold text-hub-text mb-6" x-text="animating ? '...' : (selected?.nom ?? 'Prêt ?')">Prêt ?</p>

            <button @click="spin()"
                    :disabled="animating"
                    class="px-8 py-3 bg-hub-primary hover:bg-hub-primary-hover text-white rounded-xl font-semibold text-lg transition-colors disabled:opacity-50">
                Lancer !
            </button>
        </div>

        {{-- Confirmation --}}
        <div x-show="selected !== null && !animating" x-cloak
             class="bg-hub-surface border border-hub-border rounded-2xl p-6 text-center">
            <p class="text-hub-text mb-4">
                Confirmer <strong x-text="selected?.nom"></strong> comme personnage à monter ?
            </p>
            <form method="POST" action="{{ route('outils.roulette-personnage.confirmer') }}">
                @csrf
                <input type="hidden" name="fid_perso" :value="selected?.id">
                <div class="flex justify-center gap-4">
                    <button type="submit"
                            class="px-6 py-2 bg-hub-primary hover:bg-hub-primary-hover text-white rounded-lg font-medium transition-colors">
                        Confirmer
                    </button>
                    <button type="button" @click="selected = null; spin()"
                            class="px-6 py-2 bg-hub-surface-hover border border-hub-border text-hub-text rounded-lg font-medium hover:bg-hub-border transition-colors">
                        Relancer
                    </button>
                </div>
            </form>
        </div>

        {{-- Roster --}}
        <div class="mt-8">
            <h2 class="text-lg font-bold text-hub-text mb-4">Mon roster ({{ $personnages->count() }})</h2>
            <div class="grid grid-cols-4 sm:grid-cols-6 md:grid-cols-8 gap-3">
                @foreach($personnages as $perso)
                    <div class="text-center">
                        <img src="{{ $perso->icone_url }}"
                             alt="{{ $perso->nom_perso }}"
                             class="w-full aspect-square object-contain rounded-lg bg-hub-surface-hover border border-hub-border p-1">
                        <p class="text-hub-text-sec text-xs mt-1 truncate">{{ $perso->nom_perso }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

</div>
</x-app-layout>
