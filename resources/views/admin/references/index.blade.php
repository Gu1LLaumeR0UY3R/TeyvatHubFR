<x-admin-layout>
<x-slot name="title">{{ $cfg['plural'] }}</x-slot>

<div class="mb-6 flex items-center justify-between">
    <div>
        <div class="text-xs text-hub-text-sec mb-1">
            <a href="{{ route('admin.dashboard') }}" class="hover:text-hub-primary">Dashboard</a>
            <span class="mx-1">/</span>
            Références
        </div>
        <h1 class="text-2xl font-bold text-hub-text">{{ $cfg['plural'] }}</h1>
    </div>
    <div class="flex items-center gap-2">
        <a href="{{ route('admin.references.index', ['type' => $type, 'create' => 1]) }}"
           class="px-4 py-2 bg-hub-primary text-white rounded-xl text-sm font-medium hover:bg-opacity-90 transition-colors">
            + Ajouter
        </a>
        <a href="{{ route('admin.dashboard') }}"
           class="px-4 py-2 border border-hub-border text-hub-text-sec rounded-xl hover:border-hub-primary text-sm transition-colors">
            ← Dashboard
        </a>
    </div>
</div>

{{-- Navigation entre types --}}
<div class="flex flex-wrap gap-2 mb-6">
    @foreach($allTypes as $slug => $c)
        <a href="{{ route('admin.references.index', $slug) }}"
           class="px-3 py-1.5 rounded-lg text-sm font-medium transition-colors {{ $slug === $type ? 'bg-hub-primary text-white' : 'bg-hub-surface border border-hub-border text-hub-text-sec hover:text-hub-primary hover:border-hub-primary' }}">
            {{ $c['plural'] }}
        </a>
    @endforeach
</div>

<div class="bg-hub-surface border border-hub-border rounded-2xl overflow-hidden"
     x-data="referenceManager()"
     x-init="init()">

    {{-- En-tête --}}
    <div class="flex items-center justify-between px-6 py-4 border-b border-hub-border">
        <div>
            <h2 class="font-semibold text-hub-text" x-text="`${items.length} entrée${items.length > 1 ? 's' : ''}`"></h2>
        </div>
        <button type="button"
                @click="openAdd()"
                class="flex items-center gap-2 px-4 py-2 bg-hub-primary text-white rounded-xl text-sm font-medium hover:bg-opacity-90 transition-colors">
            <span class="text-lg leading-none">+</span> Ajouter
        </button>
    </div>

    {{-- Alerte erreur --}}
    <div x-show="error"
         x-cloak
         class="mx-6 mt-4 px-4 py-3 rounded-lg bg-red-900/30 border border-red-700 text-red-400 text-sm"
         x-text="error"></div>

    {{-- Table --}}
    <div class="divide-y divide-hub-border">
        <template x-if="items.length === 0">
            <div class="px-6 py-8 text-center text-hub-text-sec text-sm italic">Aucune entrée.</div>
        </template>

        <template x-for="item in items" :key="item.id">
            <div class="flex items-center gap-4 px-6 py-3 hover:bg-hub-surface-hover transition-colors">

                <img :src="item.icon_url || '{{ asset('images/placeholder.webp') }}'"
                     alt=""
                     class="h-9 w-9 rounded-lg border border-hub-border object-cover flex-shrink-0">

                {{-- Mode lecture --}}
                <template x-if="editingId !== item.id">
                    <span class="flex-1 text-hub-text text-sm" x-text="item.libelle"></span>
                </template>

                {{-- Mode édition inline --}}
                <template x-if="editingId === item.id">
                      <div class="flex-1 grid grid-cols-1 md:grid-cols-2 gap-2">
                       <input type="text"
                           x-model="editValue"
                           @keydown.enter="saveEdit(item.id)"
                           @keydown.escape="cancelEdit()"
                           class="rounded-lg border border-hub-primary bg-hub-surface px-3 py-1.5 text-sm text-hub-text focus:outline-none focus:ring-1 focus:ring-hub-primary"
                           x-ref="editInput"
                           placeholder="Nom" />
                       <input type="text"
                           x-model="editIconValue"
                           @keydown.enter="saveEdit(item.id)"
                           @keydown.escape="cancelEdit()"
                           class="rounded-lg border border-hub-border bg-hub-surface px-3 py-1.5 text-sm text-hub-text focus:outline-none focus:ring-1 focus:ring-hub-primary"
                           placeholder="URL icône (optionnel)" />
                      </div>
                </template>

                <div class="flex items-center gap-2 flex-shrink-0">
                    <template x-if="editingId !== item.id">
                        <button type="button"
                                @click="startEdit(item)"
                                class="px-3 py-1 rounded-lg border border-hub-border text-xs text-hub-text-sec hover:text-hub-primary hover:border-hub-primary transition-colors">
                            Modifier
                        </button>
                    </template>

                    <template x-if="editingId === item.id">
                        <button type="button"
                                @click="saveEdit(item.id)"
                                :disabled="saving"
                                class="px-3 py-1 rounded-lg bg-hub-primary text-white text-xs font-medium disabled:opacity-50 hover:bg-opacity-90 transition-colors">
                            Enregistrer
                        </button>
                    </template>

                    <template x-if="editingId === item.id">
                        <button type="button"
                                @click="cancelEdit()"
                                class="px-3 py-1 rounded-lg border border-hub-border text-xs text-hub-text-sec hover:text-hub-text transition-colors">
                            Annuler
                        </button>
                    </template>

                    <template x-if="editingId !== item.id">
                        <button type="button"
                                @click="confirmDelete(item)"
                                class="px-3 py-1 rounded-lg border border-red-800 text-xs text-red-400 hover:bg-red-900/20 hover:border-red-600 transition-colors">
                            Supprimer
                        </button>
                    </template>
                </div>
            </div>
        </template>
    </div>

    {{-- Formulaire d'ajout --}}
    <div x-show="addOpen"
         x-cloak
         class="border-t border-hub-border px-6 py-4 bg-hub-surface-hover">
        <div class="flex items-center gap-3">
            <input type="text"
                   x-model="addValue"
                   @keydown.enter="saveAdd()"
                   @keydown.escape="addOpen = false; addValue = ''"
                   placeholder="Nouveau nom…"
                   class="flex-1 rounded-lg border border-hub-border bg-hub-surface px-3 py-2 text-sm text-hub-text focus:outline-none focus:border-hub-primary focus:ring-1 focus:ring-hub-primary"
                   x-ref="addInput" />
                 <input type="text"
                     x-model="addIconValue"
                     @keydown.enter="saveAdd()"
                     @keydown.escape="addOpen = false; addValue = ''; addIconValue = ''"
                     placeholder="URL icône (optionnel)"
                     class="flex-1 rounded-lg border border-hub-border bg-hub-surface px-3 py-2 text-sm text-hub-text focus:outline-none focus:border-hub-primary focus:ring-1 focus:ring-hub-primary" />
            <button type="button"
                    @click="saveAdd()"
                    :disabled="saving || !addValue.trim()"
                    class="px-4 py-2 bg-hub-primary text-white rounded-xl text-sm font-medium disabled:opacity-50 hover:bg-opacity-90 transition-colors">
                Ajouter
            </button>
            <button type="button"
                    @click="addOpen = false; addValue = ''; addIconValue = ''"
                    class="px-3 py-2 border border-hub-border text-hub-text-sec rounded-xl text-sm hover:text-hub-text transition-colors">
                Annuler
            </button>
        </div>
    </div>

    {{-- Modale confirmation suppression --}}
    <div x-show="deleteTarget"
         x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center bg-black/60"
         @keydown.escape.window="deleteTarget = null">
        <div class="bg-white border border-slate-200 rounded-xl p-4 w-full max-w-xs shadow-2xl">
            <h3 class="font-bold text-slate-900 mb-2">Confirmer la suppression</h3>
            <p class="text-slate-600 text-sm mb-4">
                Supprimer <strong x-text="deleteTarget?.libelle" class="text-slate-900"></strong> ?
                Cette action est irréversible.
            </p>
            <div class="flex gap-3 justify-end">
                <button type="button"
                        @click="deleteTarget = null"
                        class="px-3 py-1.5 border border-slate-300 text-slate-600 rounded-lg text-sm hover:text-slate-900 transition-colors">
                    Annuler
                </button>
                <button type="button"
                        @click="executeDelete()"
                        :disabled="saving"
                        class="px-3 py-1.5 bg-red-600 text-white rounded-lg text-sm font-medium disabled:opacity-50 hover:bg-red-500 transition-colors">
                    Supprimer
                </button>
            </div>
        </div>
    </div>

    <button type="button"
            @click="openAdd()"
            class="fixed bottom-6 right-6 z-40 inline-flex items-center gap-2 rounded-full bg-hub-primary px-5 py-3 text-sm font-semibold text-black shadow-xl hover:bg-opacity-90 transition-colors">
        <span class="text-lg leading-none">+</span>
        Ajouter
    </button>
</div>

<script>
function referenceManager() {
    return {
        autoOpenAdd: @json(request()->boolean('create')),
        items: @json($items->map(fn($i) => ['id' => $i->getKey(), 'libelle' => $i->{$cfg['field']}, 'icon_url' => optional($i->photos->first())->source_url ?: optional($i->photos->first())->chemin_photo])->values()),
        addOpen: false,
        addValue: '',
        addIconValue: '',
        editingId: null,
        editValue: '',
        editIconValue: '',
        deleteTarget: null,
        saving: false,
        error: '',

        init() {
            this.$watch('addOpen', v => { if (v) this.$nextTick(() => this.$refs.addInput?.focus()); });
            this.$watch('editingId', v => { if (v) this.$nextTick(() => this.$refs.editInput?.focus()); });
            if (this.autoOpenAdd) {
                this.openAdd();
            }
        },

        openAdd() {
            this.addOpen = true;
            this.addValue = '';
            this.addIconValue = '';
            this.cancelEdit();
        },

        startEdit(item) {
            this.addOpen = false;
            this.editingId = item.id;
            this.editValue = item.libelle;
            this.editIconValue = item.icon_url || '';
        },

        cancelEdit() {
            this.editingId = null;
            this.editValue = '';
            this.editIconValue = '';
        },

        confirmDelete(item) {
            this.deleteTarget = item;
            this.cancelEdit();
        },

        async saveAdd() {
            if (!this.addValue.trim() || this.saving) return;
            this.saving = true;
            this.error = '';
            try {
                const r = await fetch('{{ route('admin.references.store', $type) }}', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                    body: JSON.stringify({
                        libelle: this.addValue.trim(),
                        icon_url: this.addIconValue.trim() || null,
                    }),
                });
                const d = await r.json();
                if (d.success) {
                    this.items.push(d.item);
                    this.items.sort((a, b) => a.libelle.localeCompare(b.libelle));
                    this.addValue = '';
                    this.addIconValue = '';
                    this.addOpen = false;
                } else {
                    this.error = d.message || 'Erreur lors de la création.';
                }
            } catch { this.error = 'Erreur réseau.'; }
            this.saving = false;
        },

        async saveEdit(id) {
            if (!this.editValue.trim() || this.saving) return;
            this.saving = true;
            this.error = '';
            try {
                const r = await fetch(`{{ url('admin/references/' . $type) }}/${id}`, {
                    method: 'PATCH',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                    body: JSON.stringify({
                        libelle: this.editValue.trim(),
                        icon_url: this.editIconValue.trim() || null,
                    }),
                });
                const d = await r.json();
                if (d.success) {
                    const idx = this.items.findIndex(i => i.id === id);
                    if (idx !== -1) {
                        this.items[idx].libelle = d.item.libelle;
                        this.items[idx].icon_url = d.item.icon_url || null;
                    }
                    this.items.sort((a, b) => a.libelle.localeCompare(b.libelle));
                    this.cancelEdit();
                } else {
                    this.error = d.message || 'Erreur lors de la modification.';
                }
            } catch { this.error = 'Erreur réseau.'; }
            this.saving = false;
        },

        async executeDelete() {
            if (!this.deleteTarget || this.saving) return;
            this.saving = true;
            this.error = '';
            const id = this.deleteTarget.id;
            try {
                const r = await fetch(`{{ url('admin/references/' . $type) }}/${id}`, {
                    method: 'DELETE',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                });
                const d = await r.json();
                if (d.success) {
                    this.items = this.items.filter(i => i.id !== id);
                    this.deleteTarget = null;
                } else {
                    this.error = d.message || 'Impossible de supprimer.';
                    this.deleteTarget = null;
                }
            } catch { this.error = 'Erreur réseau.'; }
            this.saving = false;
        },
    };
}
</script>

</x-admin-layout>
