<x-admin-layout>
    <x-slot name="title">Créer un article — Blog Admin</x-slot>

    <h1 class="text-2xl font-bold text-hub-gold mb-6">Créer un article</h1>

    @include('admin.blog.partials.form', [
        'article' => null,
        'action' => route('admin.blog.store'),
        'method' => 'POST',
        'submitLabel' => 'Créer l\'article',
    ])
</x-admin-layout>
