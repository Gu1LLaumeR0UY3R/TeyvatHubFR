<x-admin-layout>
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-hub-text mb-2">Logs d'activité</h1>
        <p class="text-hub-text-sec text-sm">Tableau de bord centralisé des logs d'administration et publics</p>
    </div>

    {{-- ADMIN LOGS --}}
    @if(isset($scopes['admin']) && !empty($scopes['admin']))
        <div class="mb-8">
            <div class="flex items-center gap-2 mb-4">
                <span class="text-xl">🛡️</span>
                <h2 class="text-xl font-bold text-hub-text">ADMIN</h2>
                <span class="px-2 py-0.5 bg-violet-600/30 border border-violet-500/40 rounded text-xs text-violet-200 font-medium">
                    {{ count($scopes['admin']) }} catégorie(s)
                </span>
            </div>
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4">
                @foreach($scopes['admin'] as $cat => $data)
                    @if($data['hasLogs'])
                        <a href="{{ route('admin.logs.show', ['scope' => 'admin', 'category' => $cat]) }}"
                           class="group relative p-4 bg-hub-surface border-2 rounded-lg hover:border-violet-500/60 hover:shadow-lg transition-all cursor-pointer
                                  @if($data['highestLevel'] === 'critical') border-red-700
                                  @elseif($data['highestLevel'] === 'error') border-red-600/60
                                  @elseif($data['highestLevel'] === 'warning') border-orange-600/50
                                  @elseif($data['highestLevel'] === 'success') border-green-600/50
                                  @else border-hub-border
                                  @endif">
                            <div class="text-2xl mb-2">{{ $data['icon'] }}</div>
                            <div class="font-medium text-hub-text text-sm mb-1">{{ $data['label'] }}</div>

                            @if(!in_array($data['highestLevel'], ['info', 'debug']))
                                <div class="mb-2">
                                    <span class="px-2 py-0.5 text-xs font-bold rounded
                                          @if($data['highestLevel'] === 'critical') bg-red-900 text-red-100
                                          @elseif($data['highestLevel'] === 'error') bg-red-800/50 text-red-200
                                          @elseif($data['highestLevel'] === 'warning') bg-orange-800/50 text-orange-200
                                          @elseif($data['highestLevel'] === 'success') bg-green-800/50 text-green-200
                                          @else bg-hub-surface text-hub-text-sec
                                          @endif">
                                        {{ strtoupper($data['highestLevel']) }}
                                    </span>
                                </div>
                            @endif
                            
                            <div class="text-xs text-hub-text-sec space-y-0.5">
                                <div>📅 {{ $data['lastDate'] ?? '—' }}</div>
                                <div>📝 {{ $data['lastCount'] }} ligne(s)</div>
                                <div>📄 {{ count($data['dates']) }} fichier(s)</div>
                            </div>
                        </a>
                    @else
                        <div class="p-4 bg-hub-surface border border-hub-border rounded-lg opacity-50 cursor-default">
                            <div class="text-2xl mb-2">{{ $data['icon'] }}</div>
                            <div class="font-medium text-hub-text-sec text-sm mb-3">{{ $data['label'] }}</div>
                            <div class="text-xs text-hub-text-sec">Aucun log</div>
                        </div>
                    @endif
                @endforeach
            </div>
        </div>
    @endif

    {{-- PUBLIC LOGS --}}
    @if(isset($scopes['public']) && !empty($scopes['public']))
        <div>
            <div class="flex items-center gap-2 mb-4">
                <span class="text-xl">🌐</span>
                <h2 class="text-xl font-bold text-hub-text">PUBLIC</h2>
                <span class="px-2 py-0.5 bg-sky-600/30 border border-sky-500/40 rounded text-xs text-sky-200 font-medium">
                    {{ count($scopes['public']) }} catégorie(s)
                </span>
            </div>
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4">
                @foreach($scopes['public'] as $cat => $data)
                    @if($data['hasLogs'])
                        <a href="{{ route('admin.logs.show', ['scope' => 'public', 'category' => $cat]) }}"
                           class="group relative p-4 bg-hub-surface border-2 rounded-lg hover:border-sky-500/60 hover:shadow-lg transition-all cursor-pointer
                                  @if($data['highestLevel'] === 'critical') border-red-700
                                  @elseif($data['highestLevel'] === 'error') border-red-600/60
                                  @elseif($data['highestLevel'] === 'warning') border-orange-600/50
                                  @elseif($data['highestLevel'] === 'success') border-green-600/50
                                  @else border-hub-border
                                  @endif">
                            <div class="text-2xl mb-2">{{ $data['icon'] }}</div>
                            <div class="font-medium text-hub-text text-sm mb-1">{{ $data['label'] }}</div>

                            @if(!in_array($data['highestLevel'], ['info', 'debug']))
                                <div class="mb-2">
                                    <span class="px-2 py-0.5 text-xs font-bold rounded
                                          @if($data['highestLevel'] === 'critical') bg-red-900 text-red-100
                                          @elseif($data['highestLevel'] === 'error') bg-red-800/50 text-red-200
                                          @elseif($data['highestLevel'] === 'warning') bg-orange-800/50 text-orange-200
                                          @elseif($data['highestLevel'] === 'success') bg-green-800/50 text-green-200
                                          @else bg-hub-surface text-hub-text-sec
                                          @endif">
                                        {{ strtoupper($data['highestLevel']) }}
                                    </span>
                                </div>
                            @endif
                            
                            <div class="text-xs text-hub-text-sec space-y-0.5">
                                <div>📅 {{ $data['lastDate'] ?? '—' }}</div>
                                <div>📝 {{ $data['lastCount'] }} ligne(s)</div>
                                <div>📄 {{ count($data['dates']) }} fichier(s)</div>
                            </div>
                        </a>
                    @else
                        <div class="p-4 bg-hub-surface border border-hub-border rounded-lg opacity-50 cursor-default">
                            <div class="text-2xl mb-2">{{ $data['icon'] }}</div>
                            <div class="font-medium text-hub-text-sec text-sm mb-3">{{ $data['label'] }}</div>
                            <div class="text-xs text-hub-text-sec">Aucun log</div>
                        </div>
                    @endif
                @endforeach
            </div>
        </div>
    @endif

    @if(!isset($scopes['admin']) || empty($scopes['admin']) && !isset($scopes['public']) || empty($scopes['public']))
        <div class="text-center py-12">
            <p class="text-hub-text-sec">Aucune catégorie de logs disponible. Les logs s'afficheront au fur et à mesure que des activités seront enregistrées.</p>
        </div>
    @endif
</x-admin-layout>
