<x-app-layout>
<x-slot name="title">Résultat du Quiz</x-slot>
<div class="max-w-md mx-auto px-4 sm:px-6 lg:px-8 py-12 text-center">

    <h1 class="text-3xl font-bold text-hub-text mb-6">Résultat</h1>

    @if($estCorrect)
        <div class="bg-green-900 bg-opacity-30 border border-green-700 rounded-2xl p-8 mb-6">
            <p class="text-green-400 text-2xl font-bold mb-2">✓ Bonne réponse !</p>
            <p class="text-hub-text">C'était bien <strong>{{ $correct }}</strong>.</p>
        </div>
    @else
        <div class="bg-red-900 bg-opacity-30 border border-red-700 rounded-2xl p-8 mb-6">
            <p class="text-red-400 text-2xl font-bold mb-2">✗ Mauvaise réponse</p>
            <p class="text-hub-text">Vous avez répondu <strong>{{ $reponse }}</strong>.</p>
            <p class="text-hub-text-sec mt-1">La bonne réponse était <strong class="text-hub-gold">{{ $correct }}</strong>.</p>
        </div>
    @endif

    <a href="{{ route('outils.quiz') }}"
       class="px-6 py-3 bg-hub-primary text-white rounded-xl hover:bg-opacity-90 font-medium">
        Nouvelle question →
    </a>

</div>
</x-app-layout>
