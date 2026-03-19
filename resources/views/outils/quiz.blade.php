<x-app-layout>
<x-slot name="title">Quiz Genshin Impact</x-slot>
<div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-12">

    <h1 class="text-3xl font-bold text-hub-text mb-2 text-center">Quiz</h1>
    <p class="text-hub-text-sec mb-8 text-center">Reconnaissez-vous ce personnage ?</p>

    @if(isset($correct) && $correct)
        <div class="bg-hub-surface border border-hub-border rounded-2xl p-8">
            <div class="flex justify-center mb-6">
                <div class="relative">
                    <img src="{{ $correct->photos->first()?->source_url ?? $correct->photos->first()?->chemin_photo ?? asset('images/placeholder.webp') }}"
                         alt="Personnage mystère"
                         class="w-40 h-40 rounded-2xl object-contain filter brightness-0">
                </div>
            </div>

            <form method="POST" action="{{ route('outils.quiz.resultat') }}">
                @csrf
                <input type="hidden" name="correct" value="{{ $correct->nom_perso }}">

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    @foreach($choices->shuffle() as $choice)
                        <button type="submit"
                                name="reponse"
                                value="{{ $choice->nom_perso }}"
                                class="px-4 py-3 bg-hub-surface-hover border border-hub-border rounded-xl text-hub-text hover:border-hub-primary hover:bg-hub-surface transition-all font-medium">
                            {{ $choice->nom_perso }}
                        </button>
                    @endforeach
                </div>
            </form>
        </div>
    @else
        <p class="text-hub-text-sec text-center">Pas assez de personnages pour le quiz. Importez des données d'abord.</p>
    @endif

</div>
</x-app-layout>
