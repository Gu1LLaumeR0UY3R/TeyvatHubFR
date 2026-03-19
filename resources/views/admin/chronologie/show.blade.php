<x-admin-layout>
    <x-slot name="title">{{ $chronologie->titre }} — Admin</x-slot>
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-hub-gold">{{ $chronologie->titre }}</h1>
        <a href="{{ route('admin.chronologie.index') }}" class="px-4 py-2 border border-hub-border rounded text-hub-text hover:bg-hub-surface">Retour</a>
    </div>
    <div class="bg-hub-surface rounded-lg p-6 text-sm">
        <p><span class="text-hub-text-sec">Ordre :</span> {{ $chronologie->ordre }}</p>
        <p class="mt-2"><span class="text-hub-text-sec">Résumé :</span> {{ $chronologie->resume }}</p>
    </div>
</x-admin-layout>
