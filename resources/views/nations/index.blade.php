<x-app-layout>
<x-slot name="title">Nations de Teyvat</x-slot>
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    <div class="mb-8">
        <h1 class="text-3xl font-bold text-hub-text mb-2">Nations de Teyvat</h1>
        <p class="text-hub-text-sec">{{ $nations->count() }} nation(s)</p>
    </div>

    @if($nations->isEmpty())
        <p class="text-hub-text-sec text-center py-12">Aucune nation disponible.</p>
    @else
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-6">
            @foreach($nations as $nation)
                <x-card-nation :nation="$nation" />
            @endforeach
        </div>
    @endif

</div>
</x-app-layout>
