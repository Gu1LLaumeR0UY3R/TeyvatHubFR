<x-admin-layout>
    <x-slot name="title">Gestion des admins</x-slot>

    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-hub-gold">Comptes administrateurs</h1>
        <a href="{{ route('admin.admins.create') }}" class="px-4 py-2 bg-hub-gold text-hub-bg rounded hover:opacity-90">+ Nouvel admin</a>
    </div>

    @if(session('success'))
        <div class="mb-4 rounded border border-emerald-300 bg-emerald-50 px-3 py-2 text-sm text-emerald-700">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="mb-4 rounded border border-red-300 bg-red-50 px-3 py-2 text-sm text-red-700">{{ session('error') }}</div>
    @endif

    @php
        $permLabels = [
            'encyclopedie' => 'Encyclopédie',
            'articles'     => 'Articles',
            'evenements'   => 'Événements',
            'utilisateurs' => 'Utilisateurs',
            'admins'       => 'Admins',
            'import'       => 'Import API',
        ];
    @endphp

    <div class="mx-auto max-w-6xl space-y-4">
        @forelse($admins as $adminUser)
            @php
                $avatarSrc = null;
                if (!empty($adminUser->photo_profil)) {
                    $avatarSrc = filter_var($adminUser->photo_profil, FILTER_VALIDATE_URL)
                        ? $adminUser->photo_profil
                        : asset('storage/' . $adminUser->photo_profil);
                }

                $bannerSrc = null;
                if (!empty($adminUser->banniere_admin)) {
                    $bannerSrc = filter_var($adminUser->banniere_admin, FILTER_VALIDATE_URL)
                        ? $adminUser->banniere_admin
                        : asset('storage/' . $adminUser->banniere_admin);
                }

                $permissions = (array) ($adminUser->legacy_permissions ?? []);
                $isCurrentAdmin = $adminUser->id_admin === session('admin_id');
            @endphp

            <article class="relative overflow-hidden rounded-3xl border border-slate-200 bg-slate-900 text-white shadow-sm">
                @if($bannerSrc)
                    <div class="absolute inset-0 overflow-hidden">
                        <img src="{{ $bannerSrc }}" alt="Banniere de {{ $adminUser->pseudo_admin }}" class="h-full w-full object-cover object-center">
                    </div>
                @else
                    <div class="absolute inset-0 bg-cover bg-center" style="background: radial-gradient(circle at 85% 20%, rgba(126, 247, 214, 0.22), transparent 22%), linear-gradient(90deg, #2b2d31 0%, #22353b 42%, #25515a 100%);"></div>
                @endif
                <div class="absolute inset-0 bg-gradient-to-r from-slate-950/55 via-slate-900/35 to-slate-900/10"></div>

                <div class="relative flex min-h-[156px] flex-col gap-4 px-4 py-5 sm:flex-row sm:items-center sm:justify-between sm:px-5">
                    <div class="flex min-w-0 items-center gap-3 sm:gap-4">
                        <div class="relative shrink-0">
                            @if($avatarSrc)
                                <img src="{{ $avatarSrc }}" alt="{{ $adminUser->pseudo_admin }}" class="h-14 w-14 rounded-full border-[3px] border-slate-900/80 bg-slate-100 object-cover shadow-lg sm:h-16 sm:w-16">
                            @else
                                <div class="flex h-14 w-14 items-center justify-center rounded-full border-[3px] border-slate-900/80 bg-hub-gold/25 text-lg font-bold text-hub-gold shadow-lg sm:h-16 sm:w-16">
                                    {{ substr($adminUser->pseudo_admin, 0, 1) }}
                                </div>
                            @endif

                            <span class="absolute bottom-0 right-0 h-4 w-4 rounded-full border-2 border-slate-900 {{ $adminUser->role === 'super_admin' ? 'bg-emerald-400' : 'bg-slate-400' }}"></span>
                        </div>

                        <div class="min-w-0">
                            <div class="flex flex-wrap items-center gap-2">
                                <h2 class="truncate text-2xl font-semibold leading-none text-white">{{ $adminUser->pseudo_admin }}</h2>
                                @if($isCurrentAdmin)
                                    <span class="rounded-full bg-white/12 px-2 py-0.5 text-[11px] font-medium text-white/85">Vous</span>
                                @endif
                            </div>

                            <div class="mt-1 flex flex-wrap items-center gap-x-3 gap-y-1 text-sm text-white/78">
                                <span>{{ $adminUser->role === 'super_admin' ? 'Super Admin' : 'Moderateur' }}</span>
                                <span class="hidden h-1 w-1 rounded-full bg-white/40 sm:inline-block"></span>
                                <span class="truncate">{{ $adminUser->email_admin }}</span>
                            </div>

                            <div class="mt-2 flex flex-wrap gap-2">
                                @if($adminUser->role === 'super_admin')
                                    <span class="rounded-full bg-white/10 px-2.5 py-1 text-xs font-medium text-white/90">Acces complet</span>
                                @elseif(count($permissions) > 0)
                                    @foreach(array_slice($permissions, 0, 3) as $perm)
                                        <span class="rounded-full bg-white/10 px-2.5 py-1 text-xs font-medium text-white/90">{{ $permLabels[$perm] ?? $perm }}</span>
                                    @endforeach
                                    @if(count($permissions) > 3)
                                        <span class="rounded-full bg-white/10 px-2.5 py-1 text-xs font-medium text-white/75">+{{ count($permissions) - 3 }}</span>
                                    @endif
                                @else
                                    <span class="rounded-full bg-red-500/15 px-2.5 py-1 text-xs font-medium text-red-100">Aucune permission</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="flex shrink-0 items-center gap-2 pl-[4.5rem] sm:pl-0">
                        <a href="{{ route('admin.admins.edit', $adminUser) }}" class="inline-flex items-center rounded-xl border border-white/15 bg-white/10 px-3 py-2 text-sm font-medium text-white transition hover:bg-white/16">
                            Modifier
                        </a>
                        @if(!$isCurrentAdmin)
                            <form method="POST" action="{{ route('admin.admins.destroy', $adminUser) }}" onsubmit="return confirm('Supprimer cet admin ?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="inline-flex items-center rounded-xl border border-red-300/35 bg-red-500/10 px-3 py-2 text-sm font-medium text-red-100 transition hover:bg-red-500/18">
                                    Supprimer
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </article>
        @empty
            <div class="rounded-3xl border border-dashed border-slate-300 bg-white px-6 py-12 text-center text-sm text-slate-500">
                Aucun administrateur trouve.
            </div>
        @endforelse
    </div>
</x-admin-layout>
