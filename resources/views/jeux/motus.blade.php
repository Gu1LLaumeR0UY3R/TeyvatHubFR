<x-app-layout>
<x-slot name="title">Motus Genshin</x-slot>
<div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-8"
     x-data="{
         word: '{{ $dailyWord }}',
         wordLength: {{ $wordLength }},
         maxTries: 6,
         guesses: [],
         currentGuess: '',
         gameOver: false,
         won: false,
         message: '',
         async submitGuess() {
             if (this.currentGuess.length !== this.wordLength || this.gameOver) return;
             const res = await fetch('{{ route('jeux.motus.valider') }}', {
                 method: 'POST',
                 headers: {
                     'Content-Type': 'application/json',
                     'Accept': 'application/json',
                     'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
                 },
                 body: JSON.stringify({ guess: this.currentGuess, word: this.word })
             });
             const data = await res.json();
             this.guesses.push(data.result);
             this.currentGuess = '';
             if (data.won) {
                 this.won = true;
                 this.gameOver = true;
                 this.message = '🎉 Bravo ! Tu as trouvé le mot en ' + this.guesses.length + ' essai(s) !';
             } else if (this.guesses.length >= this.maxTries) {
                 this.gameOver = true;
                 this.message = '❌ Perdu ! Le mot était : ' + this.word;
             }
         },
         keyPress(key) {
             if (this.gameOver) return;
             if (key === 'Backspace') {
                 this.currentGuess = this.currentGuess.slice(0, -1);
             } else if (key === 'Enter') {
                 this.submitGuess();
             } else if (this.currentGuess.length < this.wordLength) {
                 this.currentGuess += key;
             }
         },
         statusColor(status) {
             if (status === 'correct') return 'bg-green-700 text-white border-green-600';
             if (status === 'present') return 'bg-yellow-600 text-white border-yellow-500';
             return 'bg-hub-surface-hover text-hub-text-sec border-hub-border';
         }
     }"
     @keydown.window="keyPress($event.key)">

    <div class="mb-8 text-center">
        <h1 class="text-3xl font-bold text-hub-text mb-2">Motus Genshin</h1>
        <p class="text-hub-text-sec">Devinez le mot Genshin du jour en {{ $maxTries ?? 6 }} essais</p>
        <p class="text-hub-text-sec text-sm mt-1">Le mot a {{ $wordLength }} lettres</p>
    </div>

    {{-- Message victoire/défaite --}}
    <div x-show="message" class="text-center mb-6">
        <p class="text-xl font-bold" :class="won ? 'text-green-400' : 'text-red-400'" x-text="message"></p>
    </div>

    {{-- Grille des essais --}}
    <div class="mb-6 space-y-2">
        {{-- Essais passés --}}
        <template x-for="(guess, gi) in guesses" :key="gi">
            <div class="flex justify-center gap-1">
                <template x-for="(cell, ci) in guess" :key="ci">
                    <div class="w-12 h-12 flex items-center justify-center text-xl font-bold uppercase rounded-lg border-2 transition-colors"
                         :class="statusColor(cell.status)"
                         x-text="cell.letter">
                    </div>
                </template>
            </div>
        </template>

        {{-- Ligne courante --}}
        <template x-if="!gameOver && guesses.length < 6">
            <div class="flex justify-center gap-1">
                <template x-for="i in wordLength" :key="i">
                    <div class="w-12 h-12 flex items-center justify-center text-xl font-bold uppercase rounded-lg border-2 border-hub-border bg-hub-surface text-hub-text"
                         x-text="currentGuess[i-1] ?? ''">
                    </div>
                </template>
            </div>
        </template>

        {{-- Lignes vides restantes --}}
        <template x-for="j in Math.max(0, 6 - guesses.length - (gameOver ? 0 : 1))" :key="j">
            <div class="flex justify-center gap-1">
                <template x-for="k in wordLength" :key="k">
                    <div class="w-12 h-12 rounded-lg border-2 border-hub-border bg-hub-surface opacity-30"></div>
                </template>
            </div>
        </template>
    </div>

    {{-- Clavier virtuel --}}
    @php
        $rows = [
            ['A','Z','E','R','T','Y','U','I','O','P'],
            ['Q','S','D','F','G','H','J','K','L','M'],
            ['⌫','W','X','C','V','B','N','↵'],
        ];
    @endphp
    <div class="space-y-2">
        @foreach($rows as $row)
        <div class="flex justify-center gap-1">
            @foreach($row as $key)
            <button @click="keyPress(@js($key === '⌫' ? 'Backspace' : ($key === '↵' ? 'Enter' : $key)))"
                    :disabled="gameOver"
                    class="px-3 py-3 min-w-[2.5rem] bg-hub-surface-hover border border-hub-border text-hub-text font-semibold rounded-lg text-sm hover:bg-hub-border transition-colors disabled:opacity-40">
                {{ $key }}
            </button>
            @endforeach
        </div>
        @endforeach
    </div>

    {{-- Mode libre --}}
    <div class="mt-8 text-center">
        <a href="{{ route('jeux.motus') }}"
           class="text-hub-text-sec hover:text-hub-primary text-sm underline">
            Nouvelle partie (mot aléatoire)
        </a>
    </div>

</div>
</x-app-layout>
