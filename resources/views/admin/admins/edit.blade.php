@php
    $permLabels = [
        'encyclopedie' => ['label' => 'Encyclopedie', 'desc' => 'Personnages, Armes, Artefacts, Ennemis, Animaux, Cuisine, Nations'],
        'articles'     => ['label' => 'Articles', 'desc' => 'Rédiger et publier des articles'],
        'evenements' => ['label' => 'Evenements', 'desc' => 'Evenements et chronologie'],
        'utilisateurs' => ['label' => 'Utilisateurs', 'desc' => 'Gerer les comptes joueurs'],
        'admins' => ['label' => 'Admins', 'desc' => 'Creer et gerer d\'autres admins'],
        'import' => ['label' => 'Import API', 'desc' => 'Importer les donnees depuis Genshin API'],
    ];
    $checked = old('permissions', $adminUser->getPermissionNames()->toArray());
    if (!is_array($checked) || $checked === []) {
        $checked = is_array($adminUser->legacy_permissions ?? null) ? $adminUser->legacy_permissions : [];
    }
    $photoSrc = null;
    if (!empty($adminUser->photo_profil)) {
        $photoSrc = filter_var($adminUser->photo_profil, FILTER_VALIDATE_URL)
            ? $adminUser->photo_profil
            : asset('storage/' . $adminUser->photo_profil);
    }
    $bannerSrc = null;
    if (!empty($adminUser->banniere_admin)) {
        $bannerSrc = filter_var($adminUser->banniere_admin, FILTER_VALIDATE_URL)
            ? $adminUser->banniere_admin
            : asset('storage/' . $adminUser->banniere_admin);
    }
    $photoUrlValue = old('photo_profil_url', filter_var($adminUser->photo_profil, FILTER_VALIDATE_URL) ? $adminUser->photo_profil : '');
    $bannerUrlValue = old('banniere_admin_url', filter_var($adminUser->banniere_admin, FILTER_VALIDATE_URL) ? $adminUser->banniere_admin : '');
@endphp

<x-admin-layout>
    <x-slot name="title">Modifier {{ $adminUser->pseudo_admin }}</x-slot>

    <h1 class="text-2xl font-bold text-hub-gold mb-6">Modifier : {{ $adminUser->pseudo_admin }}</h1>

    @if($errors->any())
        <div class="mb-4 rounded border border-red-300 bg-red-50 p-3 text-sm text-red-700">
            <ul class="list-disc pl-5">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.admins.update', $adminUser) }}" class="max-w-lg space-y-5" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Pseudo</label>
            <input name="pseudo_admin" type="text" required value="{{ old('pseudo_admin', $adminUser->pseudo_admin) }}" class="w-full rounded border border-slate-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-hub-gold">
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Email</label>
            <input name="email_admin" type="email" required value="{{ old('email_admin', $adminUser->email_admin) }}" class="w-full rounded border border-slate-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-hub-gold">
        </div>

        <div x-data="PhotoCropper()" class="space-y-4">
            <label class="block text-sm font-medium text-slate-700 mb-1">Photo de profil <span class="text-xs text-slate-400">(optionnel)</span></label>
            @if($photoSrc && !old('photo_profil'))
                <div class="mb-2">
                    <img src="{{ $photoSrc }}" alt="Photo profil" class="h-20 w-20 rounded-full object-cover border-2 border-hub-gold">
                    <p class="mt-1 text-xs text-slate-500">Photo actuelle</p>
                </div>
            @endif
            <div @dragover.prevent="dragActive = true" @dragleave="dragActive = false" @drop.prevent="dragActive = false; handleDrop($event)" :class="dragActive ? 'bg-hub-gold/10 border-hub-gold' : 'bg-slate-50 border-slate-300'" class="relative w-full rounded border-2 border-dashed px-6 py-8 transition-colors cursor-pointer text-center">
                <input type="file" accept="image/*" @change="handleSelect($event)" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer">
                <div x-show="!finalImage" class="text-center pointer-events-none">
                    <p class="mt-2 text-sm text-slate-600">Deposez votre image ici ou cliquez pour changer</p>
                </div>
                <div x-show="finalImage" class="pointer-events-none">
                    <img :src="finalImage" class="h-20 w-20 mx-auto rounded-full object-cover border-2 border-hub-gold">
                    <p class="mt-2 text-xs text-hub-gold">Pret</p>
                    <button type="button" @click="reset()" class="mt-2 text-xs text-slate-500 hover:text-slate-700 pointer-events-auto">Annuler</button>
                </div>
            </div>
            <div x-show="showModal" class="fixed inset-0 bg-black/60 z-50 flex items-center justify-center p-4">
                <div class="bg-white rounded-2xl shadow-2xl max-w-2xl w-full max-h-[85vh] overflow-hidden flex flex-col">
                    <div class="bg-gradient-to-r from-hub-gold/10 to-transparent border-b border-slate-200 px-6 py-4 flex items-center justify-between">
                        <h3 class="text-lg font-bold text-slate-900">Recadrer</h3>
                        <button type="button" @click="closeModal()" class="text-slate-400 hover:text-slate-600">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>
                    <div class="flex-1 p-6 bg-slate-50 flex items-center justify-center">
                        <img id="cropper_photo" :src="imageSrc" style="max-width: 100%; max-height: 400px;">
                    </div>
                    <div class="border-t border-slate-200 bg-white px-6 py-4 flex gap-3 justify-end">
                        <button type="button" @click="closeModal()" class="px-4 py-2 border border-slate-300 rounded-lg text-slate-700 font-medium hover:bg-slate-50">Annuler</button>
                        <button type="button" @click="finalize()" class="px-6 py-2 bg-hub-gold text-hub-bg rounded-lg font-medium hover:opacity-90">Recadrer</button>
                    </div>
                </div>
            </div>
            <input type="hidden" name="photo_profil" :value="finalImage || ''">

            <div>
                <label class="block text-xs font-medium text-slate-500 mb-1">Ou coller un lien d'image</label>
                <input name="photo_profil_url" type="url" value="{{ $photoUrlValue }}" placeholder="https://..." class="w-full rounded border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-hub-gold">
            </div>
        </div>

        <div x-data="BannerCropper()" class="space-y-4">
            <label class="block text-sm font-medium text-slate-700 mb-1">Banniere <span class="text-xs text-slate-400">(optionnel)</span></label>
            @if($bannerSrc && !old('banniere_admin'))
                <div class="mb-2">
                    <img src="{{ $bannerSrc }}" alt="Banniere" class="h-20 w-full rounded-lg object-cover border-2 border-hub-gold">
                    <p class="mt-1 text-xs text-slate-500">Banniere actuelle</p>
                </div>
            @endif
            <div @dragover.prevent="dragActive = true" @dragleave="dragActive = false" @drop.prevent="dragActive = false; handleDrop($event)" :class="dragActive ? 'bg-hub-gold/10 border-hub-gold' : 'bg-slate-50 border-slate-300'" class="relative w-full rounded border-2 border-dashed px-6 py-8 transition-colors cursor-pointer text-center">
                <input type="file" accept="image/*" @change="handleSelect($event)" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer">
                <div x-show="!finalImage" class="text-center pointer-events-none">
                    <p class="mt-2 text-sm text-slate-600">Deposez votre image ici ou cliquez pour changer</p>
                </div>
                <div x-show="finalImage" class="pointer-events-none">
                    <img :src="finalImage" class="h-20 w-full rounded-lg object-cover border-2 border-hub-gold">
                    <p class="mt-2 text-xs text-hub-gold">Pret</p>
                    <button type="button" @click="reset()" class="mt-2 text-xs text-slate-500 hover:text-slate-700 pointer-events-auto">Annuler</button>
                </div>
            </div>
            <div x-show="showModal" class="fixed inset-0 bg-black/60 z-50 flex items-center justify-center p-4">
                <div class="bg-white rounded-2xl shadow-2xl max-w-3xl w-full max-h-[85vh] overflow-hidden flex flex-col">
                    <div class="bg-gradient-to-r from-hub-gold/10 to-transparent border-b border-slate-200 px-6 py-4 flex items-center justify-between">
                        <h3 class="text-lg font-bold text-slate-900">Recadrer</h3>
                        <button type="button" @click="closeModal()" class="text-slate-400 hover:text-slate-600">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>
                    <div class="flex-1 p-6 bg-slate-50 flex items-center justify-center">
                        <img id="cropper_banner" :src="imageSrc" style="max-width: 100%; max-height: 400px;">
                    </div>
                    <div class="border-t border-slate-200 bg-white px-6 py-4 flex gap-3 justify-end">
                        <button type="button" @click="closeModal()" class="px-4 py-2 border border-slate-300 rounded-lg text-slate-700 font-medium hover:bg-slate-50">Annuler</button>
                        <button type="button" @click="finalize()" class="px-6 py-2 bg-hub-gold text-hub-bg rounded-lg font-medium hover:opacity-90">Recadrer</button>
                    </div>
                </div>
            </div>
            <input type="hidden" name="banniere_admin" :value="finalImage || ''">

            <div>
                <label class="block text-xs font-medium text-slate-500 mb-1">Ou coller un lien d'image</label>
                <input name="banniere_admin_url" type="url" value="{{ $bannerUrlValue }}" placeholder="https://..." class="w-full rounded border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-hub-gold">
            </div>
        </div>

        <div x-data="{ showPassword: false }" class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-2">Nouveau mot de passe <span class="text-slate-400 font-normal">(laisser vide = ne pas changer)</span></label>
                <div class="flex gap-2">
                    <input :type="showPassword ? 'text' : 'password'" name="mot_de_passe_admin" autocomplete="new-password" class="flex-1 rounded border border-slate-300 px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-hub-gold">
                    <button type="button" @click="showPassword = !showPassword" :class="showPassword ? 'bg-hub-gold text-hub-bg' : 'bg-slate-100 text-slate-600'" class="px-4 py-2.5 rounded font-medium transition-all duration-200 hover:shadow-md">
                        <span x-show="!showPassword">Afficher</span>
                        <span x-show="showPassword">Masquer</span>
                    </button>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-2">Confirmer</label>
                <div class="flex gap-2" x-data="{ show: false }">
                    <input :type="show ? 'text' : 'password'" name="mot_de_passe_admin_confirmation" autocomplete="new-password" class="flex-1 rounded border border-slate-300 px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-hub-gold">
                    <button type="button" @click="show = !show" :class="show ? 'bg-hub-gold text-hub-bg' : 'bg-slate-100 text-slate-600'" class="px-4 py-2.5 rounded font-medium transition-all duration-200 hover:shadow-md">
                        <span x-show="!show">Afficher</span>
                        <span x-show="show">Masquer</span>
                    </button>
                </div>
            </div>
        </div>

        <div x-data="{ isSuperAdmin: {{ old('role', $adminUser->role) === 'super_admin' ? 'true' : 'false' }} }" class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-2">Role</label>
                <div class="flex gap-4">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="radio" name="role" value="super_admin" {{ old('role', $adminUser->role) === 'super_admin' ? 'checked' : '' }} @change="isSuperAdmin = true" class="accent-hub-gold">
                        <span class="text-sm text-slate-700">Super Admin <span class="text-xs text-slate-400">(tout)</span></span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="radio" name="role" value="moderateur" {{ old('role', $adminUser->role) === 'moderateur' ? 'checked' : '' }} @change="isSuperAdmin = false" class="accent-hub-gold">
                        <span class="text-sm text-slate-700">Moderateur</span>
                    </label>
                </div>
            </div>

            <div x-show="!isSuperAdmin" class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                <div class="mb-3 text-sm font-semibold text-slate-700">Permissions</div>
                <div class="space-y-3">
                    @foreach($allPermissions as $perm)
                        <label class="flex items-start gap-3 cursor-pointer">
                            <input type="checkbox" name="permissions[]" value="{{ $perm }}" {{ in_array($perm, $checked) ? 'checked' : '' }} class="mt-0.5 h-4 w-4 accent-hub-gold">
                            <span>
                                <span class="text-sm font-medium text-slate-800">{{ $permLabels[$perm]['label'] ?? $perm }}</span>
                                <span class="block text-xs text-slate-500">{{ $permLabels[$perm]['desc'] ?? '' }}</span>
                            </span>
                        </label>
                    @endforeach
                </div>
            </div>
            <p x-show="isSuperAdmin" class="text-sm text-slate-500 italic">Super Admin = acces complet.</p>
        </div>

        <div class="flex gap-3 pt-2">
            <button type="submit" class="px-4 py-2 bg-hub-gold text-hub-bg rounded hover:opacity-90">Enregistrer</button>
            <a href="{{ route('admin.admins.index') }}" class="px-4 py-2 rounded border border-slate-300 text-slate-700 hover:bg-slate-100">Annuler</a>
        </div>
    </form>
</x-admin-layout>

<link rel="stylesheet" href="https://unpkg.com/cropperjs@1.5.13/dist/cropper.css">
<script src="https://unpkg.com/cropperjs@1.5.13/dist/cropper.js"></script>

<script>
const MAX_ADMIN_IMAGE_BYTES = 500 * 1024;

function dataUrlSize(dataUrl) {
    const base64 = (dataUrl.split(',')[1] || '');
    return Math.ceil((base64.length * 3) / 4);
}

function exportCanvasUnderLimit(canvas, maxBytes) {
    let quality = 0.9;
    let dataUrl = canvas.toDataURL('image/jpeg', quality);

    while (dataUrlSize(dataUrl) > maxBytes && quality > 0.4) {
        quality -= 0.1;
        dataUrl = canvas.toDataURL('image/jpeg', quality);
    }

    return dataUrlSize(dataUrl) <= maxBytes ? dataUrl : null;
}

window.PhotoCropper = () => ({
    dragActive: false,
    imageSrc: null,
    showModal: false,
    cropperInstance: null,
    finalImage: null,
    handleSelect(e) {
        const f = e.target.files[0];
        if (f) this.load(f);
    },
    handleDrop(e) {
        const f = e.dataTransfer.files[0];
        if (f && f.type.startsWith('image/')) this.load(f);
    },
    load(f) {
        const r = new FileReader();
        r.onload = (e) => {
            this.imageSrc = e.target.result;
            this.showModal = true;
            this.$nextTick(() => this.init());
        };
        r.readAsDataURL(f);
    },
    init() {
        if (this.cropperInstance) this.cropperInstance.destroy();
        const i = document.getElementById('cropper_photo');
        if (i) {
            this.cropperInstance = new Cropper(i, {
                aspectRatio: 1,
                viewMode: 1,
                autoCropArea: 0.8,
                responsive: true,
                guides: true,
                cropBoxMovable: true,
                cropBoxResizable: true,
            });
        }
    },
    finalize() {
        if (!this.cropperInstance) return;
        const c = this.cropperInstance.getCroppedCanvas({
            maxWidth: 1024,
            maxHeight: 1024,
            imageSmoothingEnabled: true,
        });
        const dataUrl = exportCanvasUnderLimit(c, MAX_ADMIN_IMAGE_BYTES);
        if (!dataUrl) {
            alert('Image trop lourde: maximum 500 Ko.');
            return;
        }
        this.finalImage = dataUrl;
        this.closeModal();
    },
    closeModal() {
        this.showModal = false;
        if (this.cropperInstance) {
            this.cropperInstance.destroy();
            this.cropperInstance = null;
        }
    },
    reset() {
        this.finalImage = null;
        this.imageSrc = null;
    },
});

window.BannerCropper = () => ({
    dragActive: false,
    imageSrc: null,
    showModal: false,
    cropperInstance: null,
    finalImage: null,
    handleSelect(e) {
        const f = e.target.files[0];
        if (f) this.load(f);
    },
    handleDrop(e) {
        const f = e.dataTransfer.files[0];
        if (f && f.type.startsWith('image/')) this.load(f);
    },
    load(f) {
        const r = new FileReader();
        r.onload = (e) => {
            this.imageSrc = e.target.result;
            this.showModal = true;
            this.$nextTick(() => this.init());
        };
        r.readAsDataURL(f);
    },
    init() {
        if (this.cropperInstance) this.cropperInstance.destroy();
        const i = document.getElementById('cropper_banner');
        if (i) {
            this.cropperInstance = new Cropper(i, {
                aspectRatio: 16 / 9,
                viewMode: 1,
                autoCropArea: 0.8,
                responsive: true,
                guides: true,
                cropBoxMovable: true,
                cropBoxResizable: true,
            });
        }
    },
    finalize() {
        if (!this.cropperInstance) return;
        const c = this.cropperInstance.getCroppedCanvas({
            maxWidth: 1920,
            maxHeight: 1080,
            imageSmoothingEnabled: true,
        });
        const dataUrl = exportCanvasUnderLimit(c, MAX_ADMIN_IMAGE_BYTES);
        if (!dataUrl) {
            alert('Image trop lourde: maximum 500 Ko.');
            return;
        }
        this.finalImage = dataUrl;
        this.closeModal();
    },
    closeModal() {
        this.showModal = false;
        if (this.cropperInstance) {
            this.cropperInstance.destroy();
            this.cropperInstance = null;
        }
    },
    reset() {
        this.finalImage = null;
        this.imageSrc = null;
    },
});
</script>
