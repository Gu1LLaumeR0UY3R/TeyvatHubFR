<x-admin-layout>
    <x-slot name="title">Modifier un article — Blog Admin</x-slot>

    <h1 class="text-2xl font-bold text-hub-gold mb-6">Modifier l'article</h1>

    @include('admin.blog.partials.form', [
        'article' => $article,
        'action' => route('admin.blog.update', $article),
        'method' => 'PUT',
        'submitLabel' => 'Mettre à jour',
    ])
</x-admin-layout>
