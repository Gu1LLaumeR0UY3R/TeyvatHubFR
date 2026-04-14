<x-admin-layout>

<div class="mb-6">
    <h1 class="text-2xl font-bold text-hub-text">Dashboard</h1>
    <p class="text-hub-text-sec mt-1">Vue d'ensemble de TeyvatHub</p>
</div>

@if(session('import_success'))
    <div class="mb-4 p-4 bg-green-900 bg-opacity-30 border border-green-700 rounded-xl">
        <p class="text-green-400">{{ session('import_success') }}</p>
    </div>
@endif

@if(session('import_error'))
    <div class="mb-4 p-4 bg-red-900 bg-opacity-30 border border-red-700 rounded-xl">
        <p class="text-red-400">{{ session('import_error') }}</p>
    </div>
@endif

{{-- Statistiques --}}
<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
    @foreach([
        ['label' => 'Personnages', 'count' => $stats['personnages'], 'route' => 'admin.personnages.index'],
        ['label' => 'Armes', 'count' => $stats['armes'], 'route' => 'admin.armes.index'],
        ['label' => 'Artefacts', 'count' => $stats['artefacts'], 'route' => 'admin.artefacts.index'],
        ['label' => 'Ennemis', 'count' => $stats['ennemis'], 'route' => 'admin.ennemis.index'],
        ['label' => 'Animaux', 'count' => $stats['animaux'], 'route' => 'admin.animaux.index'],
        ['label' => 'Plats', 'count' => $stats['plats'], 'route' => 'admin.cuisine.index'],
        ['label' => 'Ingrédients', 'count' => $stats['ingredients'], 'route' => 'admin.cuisine.index'],
        ['label' => 'Nations', 'count' => $stats['nations'], 'route' => 'admin.nations.index'],
        ['label' => 'Utilisateurs', 'count' => $stats['utilisateurs'], 'route' => 'admin.utilisateurs.index'],
    ] as $stat)
        <a href="{{ route($stat['route']) }}"
           class="bg-hub-surface border border-hub-border rounded-xl p-4 hover:border-hub-primary transition-colors">
            <p class="text-2xl font-bold text-hub-primary">{{ $stat['count'] }}</p>
            <p class="text-hub-text-sec text-sm mt-1">{{ $stat['label'] }}</p>
        </a>
    @endforeach
</div>

{{-- Données de référence --}}
<div class="mb-8">
    <h2 class="text-lg font-bold text-hub-text mb-3">Données de référence</h2>
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3">
        @foreach(\App\Http\Controllers\Admin\ReferenceController::allTypes() as $slug => $cfg)
            @php $count = $cfg['model']::count(); @endphp
            <div class="bg-hub-surface border border-hub-border rounded-xl p-4 hover:border-hub-primary transition-colors group">
                <a href="{{ route('admin.references.index', $slug) }}" class="block">
                    <p class="text-xl font-bold text-hub-primary">{{ $count }}</p>
                    <p class="text-hub-text text-sm font-medium mt-0.5 group-hover:text-hub-primary transition-colors">{{ $cfg['plural'] }}</p>
                </a>
                <a href="{{ route('admin.references.index', ['type' => $slug, 'create' => 1]) }}"
                   class="mt-3 inline-flex items-center rounded-lg border border-hub-border px-2.5 py-1 text-xs text-hub-text-sec hover:text-hub-primary hover:border-hub-primary transition-colors">
                    + Ajouter
                </a>
            </div>
        @endforeach
    </div>
</div>

{{-- Import Genshin --}}
<div class="bg-hub-surface border border-hub-border rounded-2xl p-6"
     x-data="importGenshin()">
    <h2 class="text-lg font-bold text-hub-text mb-2">Import Genshin Impact</h2>
    <p class="text-hub-text-sec text-sm mb-4">Importe les personnages, armes et éléments depuis teyvat-dev API.</p>

    <button @click="openModal = true"
            class="px-5 py-2.5 bg-hub-primary text-white rounded-xl hover:bg-opacity-90 font-medium transition-colors">
        Importer depuis teyvat-dev
    </button>

    {{-- Modale de confirmation --}}
    <div x-show="openModal"
         x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-60">
        <div class="bg-hub-surface border border-hub-border rounded-2xl p-6 w-full max-w-md shadow-2xl">
            <h3 class="text-lg font-bold text-hub-text mb-2">Confirmer l'import</h3>
            <p class="text-hub-text-sec text-sm mb-6">
                Cette action va importer ou mettre à jour les personnages, armes, éléments et nations
                depuis l'API teyvat-dev. Les données existantes ne seront pas supprimées.
            </p>

            {{-- Résultat --}}
            <div x-show="result" class="mb-4 p-3 rounded-lg"
                 :class="success ? 'bg-green-900 bg-opacity-30 border border-green-700' : 'bg-red-900 bg-opacity-30 border border-red-700'">
                <p class="text-sm" :class="success ? 'text-green-400' : 'text-red-400'" x-text="result"></p>
            </div>

            <div class="flex gap-3 justify-end">
                <button @click="openModal = false; result = ''"
                        :disabled="loading"
                        class="px-4 py-2 text-hub-text-sec hover:text-hub-text border border-hub-border rounded-xl transition-colors disabled:opacity-50">
                    Annuler
                </button>
                <button @click="runImport()"
                        :disabled="loading"
                        class="px-5 py-2 bg-hub-primary text-white rounded-xl hover:bg-opacity-90 font-medium transition-colors disabled:opacity-50 flex items-center gap-2">
                    <svg x-show="loading" class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                    </svg>
                    <span x-text="loading ? 'Import en cours...' : \"Lancer l'import\""></span>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function importGenshin() {
    return {
        openModal: false,
        loading: false,
        result: '',
        success: false,

        async runImport() {
            this.loading = true;
            this.result = '';

            try {
                const response = await fetch('{{ route("admin.import-genshin") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                });

                const data = await response.json();
                this.success = data.success;
                this.result = data.message;
            } catch (e) {
                this.success = false;
                this.result = 'Erreur réseau. Veuillez réessayer.';
            } finally {
                this.loading = false;
            }
        }
    };
}
</script>

</x-admin-layout>
