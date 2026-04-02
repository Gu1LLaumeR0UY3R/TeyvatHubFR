<x-admin-layout>
    <x-slot name="title">Modifier {{ $personnage->nom_perso }} - Admin</x-slot>

    {{-- â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
         CSS Ã‰DITEUR GUTENBERG
    â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• --}}
    <style>
        /* â”€â”€ Couleurs de raretÃ© â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ */
        .rarity-1  { background: #78716c; }   /* 1â˜… gris   */
        .rarity-2  { background: #22c55e; }   /* 2â˜… vert   */
        .rarity-3  { background: #3b82f6; }   /* 3â˜… bleu   */
        .rarity-4  { background: #8b5cf6; }   /* 4â˜… violet */
        .rarity-5  { background: #f59e0b; }   /* 5â˜… ambre  */

        /* â”€â”€ Grille 2 colonnes â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ */
        #editor-blocks {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
            background: #e5e7eb;
            padding: 1.25rem;
        }
        #editor-blocks > [data-block-id="main_zone"],
        #editor-blocks > [data-block-id="constellations"],
        #editor-blocks > [data-block-id="competences"] {
            grid-column: span 2;
        }

        /* â”€â”€ Drag ghost â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ */
        .editor-block.is-dragging { opacity: .4; }

        /* â”€â”€ Scrollbar fine dans le sÃ©lecteur d'armes â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ */
        .weapon-grid-scroll::-webkit-scrollbar { width: 5px; }
        .weapon-grid-scroll::-webkit-scrollbar-thumb { background: #9ca3af; border-radius: 3px; }

        /* â”€â”€ Modal backdrop â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ */
        .modal-backdrop {
            position: fixed; inset: 0;
            background: rgba(0,0,0,.55);
            z-index: 40;
            display: flex; align-items: center; justify-content: center;
        }
        .modal-panel {
            background: #e5e7eb;
            border: 1px solid #9ca3af;
            border-radius: .75rem;
            padding: 1.25rem;
            width: 320px;
            position: relative;
            z-index: 50;
        }
        /* Panel dÃ©tail constellation (overlay interne au bloc) */
        .const-detail-overlay {
            position: absolute; inset: 0;
            background: rgba(229,231,235,.97);
            border-radius: .5rem;
            padding: 1rem;
            z-index: 10;
        }
        /* SÃ©lecteur d'armes (modal centrÃ©) */
        .weapon-picker {
            background: #e5e7eb;
            border: 1px solid #9ca3af;
            border-radius: .75rem;
            padding: 1rem;
            width: 430px;
            display: flex; flex-direction: column;
            position: relative;
            z-index: 50;
        }
    </style>

    @php
        $iconePhoto    = $personnage->photos->where('type', 'icone')->first()
                      ?? $personnage->photos->first();
        $portraitPhoto = $personnage->photos->where('type', 'portrait')->first()
                      ?? $personnage->photos->first();
        $constellationImage = $personnage->constellations
            ->filter(fn($c) => !empty($c->photo_url))->first()?->photo_url
            ?? asset('images/placeholder.svg');
        $mainNationId  = (string) ($personnage->nations->first()?->id_region ?? '');

        $mainZoneJson = json_encode([
            'nom_perso'       => $personnage->nom_perso,
            'fid_element'     => (string) $personnage->fid_element,
            'fid_etoile'      => (string) $personnage->fid_etoile,
            'fid_TArmes'      => $personnage->fid_TArmes ? (string) $personnage->fid_TArmes : '',
            'fid_TP'          => $personnage->fid_TP ? (string) $personnage->fid_TP : '',
            'fid_nations'     => $personnage->nations->pluck('id_region')->map(fn($id) => (string)$id)->values(),
            'background_actif'=> $personnage->background_actif,
            'videos'          => $personnage->videos->map(fn($v) => ['url_video' => $v->url_video])->values(),
        ]);
        $validBlocks = ['main_zone', 'armes', 'artefacts', 'constellations', 'competences'];
        $savedBlockOrder = array_values(array_filter(explode(',', (string) $personnage->block_order)));
        $savedBlockOrder = array_values(array_filter($savedBlockOrder, fn($id) => in_array($id, $validBlocks, true)));
        $blockOrder = count($savedBlockOrder) === count($validBlocks) ? $savedBlockOrder : $validBlocks;

        $armesJson = json_encode($armesDisponibles->map(fn($a) => [
            'id'     => $a->id_arme,
            'nom'    => $a->nom_arme,
            'rarity' => (int) preg_replace('/[^\d]/', '', $a->etoile?->libelle ?? '3'),
            'icon'   => $a->photos->first()?->source_url ?? $a->photos->first()?->chemin_photo ?? '',
        ])->values());

        $constellationsJson = json_encode($personnage->constellations->map(fn($c) => [
            'id'     => $c->id_const,
            'titre'  => $c->titre_const,
            'descri' => $c->descri_const ?? '',
        ])->values());

        $aptitudesJson = json_encode($personnage->aptitudes->map(fn($a) => [
            'id'           => $a->id_aptitude,
            'titre'        => $a->titre_apti ?? '',
            'descri'       => $a->descri_apti ?? '',
            'lvl'          => (int) ($a->lvl_apt ?? 1),
            'fid_TypeApti' => (string) ($a->fid_TypeApti ?? ''),
            'type_label'   => $a->typeApti?->libelle_Apti ?? '',
            'icon'         => '',
        ])->values());
    @endphp

    {{-- Config hidden div â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ --}}
    <div id="personnage-editor-config"
         data-main-zone="{{ e($mainZoneJson) }}"
         data-block-order="{{ e(json_encode($blockOrder)) }}"
         data-armes="{{ e($armesJson) }}"
         data-existing-armes="{{ e(json_encode($personnage->armesRecommandees->pluck('arme.id_arme')->filter()->values()->all())) }}"
         data-constellations="{{ e($constellationsJson) }}"
         data-aptitudes="{{ e($aptitudesJson) }}"
         data-save-main-zone-url="{{ route('admin.personnage.block.main-zone.update', $personnage) }}"
         data-save-block-order-url="{{ route('admin.personnage.block.order', $personnage) }}"
         data-upload-image-url="{{ route('admin.personnage.block.main-zone.upload', $personnage) }}"
         data-save-armes-url="{{ route('admin.personnage.block.armes.update', $personnage) }}"
         data-save-competences-url="{{ route('admin.personnage.block.competences.update', $personnage) }}"
         data-icone-preview="{{ $iconePhoto ? ($iconePhoto->source_url ?? asset('storage/'.$iconePhoto->chemin_photo)) : '' }}"
         data-portrait-preview="{{ $portraitPhoto ? ($portraitPhoto->source_url ?? asset('storage/'.$portraitPhoto->chemin_photo)) : '' }}"
         data-csrf="{{ csrf_token() }}"
         class="hidden"></div>

    {{-- â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
         Ã‰DITEUR PRINCIPAL
    â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• --}}
    <div class="max-w-6xl mx-auto space-y-4" x-data="personnageEditorLayout()">

        {{-- Titre + retour â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ --}}
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-bold text-hub-gold">admin/personnage/modification</h1>
            <a href="{{ route('admin.personnages.index') }}"
               class="px-4 py-2 border border-hub-border rounded text-hub-text text-sm hover:bg-hub-surface">
               ← Retour
            </a>
        </div>

        <div class="bg-neutral-200 border border-neutral-400 rounded-xl overflow-hidden">
            <div id="editor-blocks" x-init="initBlockOrder()">

                {{-- â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
                     BLOC â€” MAIN ZONE  (col-span-2)
                â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• --}}
                <section data-block-id="main_zone" draggable="true"
                         @dragstart="startDrag" @dragover.prevent @drop="dropOn"
                         class="editor-block border border-neutral-400 bg-neutral-100 rounded-lg p-4 cursor-move">
                    <div class="space-y-5 max-w-4xl mx-auto">

                        <div class="character-show-hero" data-element="{{ strtolower($personnage->element->libelle_element ?? 'geo') }}">
                            <section class="csh-portrait" aria-label="Portrait de {{ $personnage->nom_perso }}"
                                     @dragover.prevent
                                     @drop.prevent="uploadDroppedImage($event,'portrait')">
                                <template x-if="portraitPreview">
                                    <img :src="portraitPreview" alt="Portrait du personnage">
                                </template>
                                <template x-if="!portraitPreview">
                                    <div class="w-full h-full flex items-center justify-center text-center px-4">
                                        <div>
                                            <div class="text-3xl mb-1">🖼</div>
                                            <div class="text-xs text-neutral-200">Drop ou clic pour changer</div>
                                        </div>
                                    </div>
                                </template>
                                <input type="file" class="hidden" accept="image/*" @change="uploadImage($event,'portrait')">
                            </section>

                            <header class="csh-hero">

                                <h1 class="csh-name">
                                    <input type="text" x-model="mainZone.nom_perso"
                                           value="{{ $personnage->nom_perso }}"
                                           class="w-full bg-transparent text-4xl font-bold leading-tight border-none focus:outline-none focus:ring-0"
                                           placeholder="Nom personnage">
                                </h1>
                                <p class="csh-role">{{ $personnage->typePerso->libelle_TP ?? 'Aucun type' }}</p>
                            </header>


                        </div>

                        <div class="flex flex-wrap justify-center gap-2">
                            <template x-for="(video, index) in mainZone.videos" :key="index">
                                <div class="inline-flex items-center gap-1 text-xs px-3 py-1 rounded-full font-medium transition-colors"
                                     :class="activeVideoIndex === index
                                        ? 'bg-neutral-700 text-white'
                                        : 'bg-neutral-300 text-neutral-700 hover:bg-neutral-400'">
                                    <button type="button" @click="setActiveVideo(index)" x-text="`vidéo ${index + 1}`"></button>
                                    <button type="button" @click="removeVideo(index)" class="ml-1 font-bold text-[10px] opacity-60 hover:opacity-100">✕</button>
                                </div>
                            </template>
                            <button type="button" @click="addVideo()"
                                    class="w-6 h-6 rounded-full bg-neutral-300 border border-neutral-500 text-neutral-700 text-sm font-bold hover:bg-neutral-400 leading-none">+</button>
                        </div>

                        {{-- Vidéo (iframe large comme la référence) --}}
                        <div class="space-y-2">
                            <div class="w-full aspect-video rounded-lg border border-neutral-400 bg-neutral-800 overflow-hidden">
                                <template x-if="activeEmbedUrl">
                                    <iframe class="w-full h-full" :src="activeEmbedUrl"
                                            frameborder="0"
                                            referrerpolicy="strict-origin-when-cross-origin"
                                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                                            allowfullscreen></iframe>
                                </template>
                                <template x-if="!activeEmbedUrl">
                                    <div class="h-full flex items-center justify-center text-xs text-neutral-400">
                                        aperçu vidéo
                                    </div>
                                </template>
                            </div>

                            <div class="rounded-lg border border-neutral-400 bg-neutral-200 p-2 overflow-y-auto space-y-1 max-h-28">
                                <template x-for="(video, idx) in mainZone.videos" :key="`vi-${idx}`">
                                    <input type="url" x-model="video.url_video"
                                           @focus="setActiveVideo(idx)"
                                           class="w-full text-xs px-2 py-1 rounded border border-neutral-400 bg-neutral-100"
                                           :placeholder="`URL YouTube vidéo ${idx + 1}`">
                                </template>
                                <template x-if="mainZone.videos.length === 0">
                                    <div class="h-full flex items-center justify-center text-xs text-neutral-500">
                                        vidéos personnage (généralement 2)
                                    </div>
                                </template>
                            </div>
                        </div>

                        {{-- Attributs --}}
                        <div class="grid grid-cols-2 lg:grid-cols-4 gap-2">
                                <div class="relative">
                                    <select x-model="mainZone.fid_TArmes"
                                            class="w-full px-3 py-2 bg-neutral-200 border border-neutral-400
                                                   rounded-lg text-sm font-semibold appearance-none pr-8">
                                        <option value="">Type d'armes</option>
                                        @foreach($typesArme as $type)
                                            <option value="{{ $type->id_TArmes }}" @selected((int)$personnage->fid_TArmes === (int)$type->id_TArmes)>{{ $type->libelle_TArme }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <span class="pointer-events-none absolute right-2 top-1/2 -translate-y-1/2 text-neutral-500">&#9662;</span>
                                </div>
                                <div class="relative">
                                    <select x-model="mainZone.fid_element"
                                            class="w-full px-3 py-2 bg-neutral-200 border border-neutral-400
                                                   rounded-lg text-sm font-semibold appearance-none pr-8">
                                        <option value="">&#201;l&#233;ment</option>
                                        @foreach($elements as $element)
                                            <option value="{{ $element->id_element }}" @selected((int)$personnage->fid_element === (int)$element->id_element)>{{ $element->libelle_element }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <span class="pointer-events-none absolute right-2 top-1/2 -translate-y-1/2 text-neutral-500">&#9662;</span>
                                </div>
                                <div class="relative">
                                    <select x-model="mainZone.fid_etoile"
                                            class="w-full px-3 py-2 bg-neutral-200 border border-neutral-400
                                                   rounded-lg text-sm font-semibold appearance-none pr-8">
                                        <option value="">&#201;toile</option>
                                        @foreach($etoiles as $etoile)
                                            <option value="{{ $etoile->id_etoile }}" @selected((int)$personnage->fid_etoile === (int)$etoile->id_etoile)>{{ $etoile->libelle }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <span class="pointer-events-none absolute right-2 top-1/2 -translate-y-1/2 text-neutral-500">&#9662;</span>
                                </div>
                                <div class="relative">
                                    <select x-model="activeNation" @change="syncNationAndBackgrounds"
                                            class="w-full px-3 py-2 bg-neutral-200 border border-neutral-400
                                                   rounded-lg text-sm font-semibold appearance-none pr-8">
                                        <option value="">R&#233;gion</option>
                                        @foreach($nations as $nation)
                                            <option value="{{ $nation->id_region }}" @selected($mainNationId !== '' && (int)$mainNationId === (int)$nation->id_region)>{{ $nation->nom_region }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <span class="pointer-events-none absolute right-2 top-1/2 -translate-y-1/2 text-neutral-500">&#9662;</span>
                                </div>

                        </div>

                        <div class="flex items-center gap-3">
                            <button type="button" @click="saveMainZone"
                                    class="px-5 py-2 rounded-lg bg-neutral-700 text-white text-sm font-medium hover:bg-neutral-800 transition-colors">
                                Enregistrer les attributs
                            </button>
                            <span class="text-sm" :class="saveState === 'ok' ? 'text-green-700' : 'text-red-600'" x-text="saveMessage"></span>
                        </div>

                        <div class="hidden" aria-hidden="true">
                            @foreach($personnage->videos as $video)
                                <span>{{ $video->url_video }}</span>
                            @endforeach
                        </div>
                    </div>
                </section>

                {{-- â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
                     BLOC â€” ARMES RECOMMANDÃ‰ES  (col-span-1)
                â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• --}}
                <section data-block-id="armes" draggable="true"
                         @dragstart="startDrag" @dragover.prevent @drop="dropOn"
                         class="editor-block border border-neutral-400 bg-neutral-100 rounded-lg p-4 cursor-move">

                    <h2 class="text-xs font-bold text-neutral-500 uppercase tracking-widest mb-3">Armes recommandées</h2>

                    <div class="space-y-2">
                        @forelse($personnage->armesRecommandees as $armeRec)
                            @php
                                $rl   = (int) preg_replace('/[^\d]/', '', $armeRec->arme?->etoile?->libelle ?? '3');
                                $icon = $armeRec->arme?->photos->first()?->source_url
                                     ?? $armeRec->arme?->photos->first()?->chemin_photo ?? '';
                            @endphp
                            <div class="flex items-center gap-2 bg-neutral-200 border border-neutral-300 rounded-lg p-2">
                                <div class="w-10 h-10 rounded-lg rarity-{{ $rl }} flex-shrink-0 overflow-hidden
                                            flex items-center justify-center">
                                    @if($icon)
                                        <img src="{{ $icon }}" alt="{{ $armeRec->arme?->nom_arme }}"
                                             class="w-full h-full object-cover">
                                    @else
                                        <span class="text-white text-[10px] font-bold">{{ $rl }}★</span>
                                    @endif
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="text-sm font-semibold text-neutral-800 truncate">
                                        {{ $armeRec->arme?->nom_arme ?? 'nom_armes' }}
                                    </div>
                                    @if($armeRec->starter)
                                        <div class="flex items-center gap-1 mt-0.5">
                                            <span class="w-3 h-3 rounded-sm border border-neutral-400 bg-neutral-300 inline-block"></span>
                                            <span class="text-[10px] text-neutral-500">Starter Weapon</span>
                                        </div>
                                    @endif
                                </div>
                                <div class="w-7 h-7 rounded-full border-2 border-neutral-400 bg-neutral-300
                                            flex items-center justify-center text-xs flex-shrink-0">•</div>
                            </div>
                        @empty
                            @foreach([5,4,3] as $rl)
                                <div class="flex items-center gap-2 bg-neutral-200 border border-neutral-300 rounded-lg p-2">
                                    <div class="w-10 h-10 rounded-lg rarity-{{ $rl }} flex-shrink-0 flex items-center justify-center">
                                        <span class="text-white text-[9px] font-bold">{{ $rl }}★</span>
                                    </div>
                                    <div class="flex-1 text-sm font-semibold text-neutral-500">nom_armes</div>
                                    <div class="w-7 h-7 rounded-full border-2 border-neutral-400 bg-neutral-300 flex items-center justify-center text-xs">•</div>
                                </div>
                            @endforeach
                        @endforelse

                        <button type="button" @click="openWeaponSelector()"
                                class="w-12 h-12 rounded-lg bg-neutral-200 border-2 border-dashed border-neutral-400
                                       text-2xl text-neutral-500 font-bold leading-none
                                       hover:bg-neutral-300 hover:border-neutral-600 transition-colors">+</button>
                    </div>
                </section>

                {{-- â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
                     BLOC â€” ARTEFACTS RECOMMANDÃ‰S  (col-span-1)
                â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• --}}
                <section data-block-id="artefacts" draggable="true"
                         @dragstart="startDrag" @dragover.prevent @drop="dropOn"
                         class="editor-block border border-neutral-400 bg-neutral-100 rounded-lg p-4 cursor-move">

                    <h2 class="text-xs font-bold text-neutral-500 uppercase tracking-widest mb-3">Artefacts recommandés</h2>

                    <div class="space-y-2">
                        @forelse($personnage->artefactsRecommandees as $build)
                            @php
                                $icon1 = $build->artefact1?->photos->first()?->source_url
                                      ?? $build->artefact1?->photos->first()?->chemin_photo ?? '';
                                $icon2 = $build->artefact2?->photos->first()?->source_url
                                      ?? $build->artefact2?->photos->first()?->chemin_photo ?? '';
                                $is4p  = !$build->fid_artefact_2;
                            @endphp
                            <div class="flex items-center gap-2 bg-neutral-200 border border-neutral-300 rounded-lg p-2">
                                <div class="flex gap-1 flex-shrink-0">
                                    <div class="w-10 h-10 rounded-lg bg-amber-500 overflow-hidden">
                                        @if($icon1)<img src="{{ $icon1 }}" class="w-full h-full object-cover">
                                        @else<div class="w-full h-full flex items-center justify-center text-[9px] font-bold text-white">SET</div>
                                        @endif
                                    </div>
                                    @if(!$is4p)
                                        <div class="w-10 h-10 rounded-lg bg-amber-400 overflow-hidden">
                                            @if($icon2)<img src="{{ $icon2 }}" class="w-full h-full object-cover">
                                            @else<div class="w-full h-full flex items-center justify-center text-[9px] font-bold text-neutral-700">SET</div>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="text-sm font-semibold text-neutral-800 truncate">
                                        {{ $build->artefact1?->nom_artefact ?? 'nom set artefact' }}
                                    </div>
                                    <div class="text-[11px] text-neutral-500">
                                        {{ $is4p ? '4 pièces' : '2P + 2P' }}
                                        @if(!$is4p && $build->artefact2)
                                            · {{ $build->artefact2->nom_artefact }}
                                        @endif
                                    </div>
                                </div>
                                <div class="w-7 h-7 rounded-full border-2 border-neutral-400 bg-neutral-300
                                            flex items-center justify-center text-xs flex-shrink-0">•</div>
                            </div>
                        @empty
                            @foreach(['Set Gladiateur','Embleme du Feu-Roi','Sablier Dore'] as $nom)
                                <div class="flex items-center gap-2 bg-neutral-200 border border-neutral-300 rounded-lg p-2">
                                    <div class="w-10 h-10 rounded-lg bg-amber-500 flex items-center justify-center flex-shrink-0">
                                        <span class="text-white text-[9px] font-bold">SET</span>
                                    </div>
                                    <div class="flex-1">
                                        <div class="text-sm font-semibold text-neutral-500">{{ $nom }}</div>
                                        <div class="text-[11px] text-neutral-400">[4p]</div>
                                    </div>
                                    <div class="w-7 h-7 rounded-full border-2 border-neutral-400 bg-neutral-300 flex items-center justify-center text-xs">•</div>
                                </div>
                            @endforeach
                        @endforelse

                        <button type="button"
                                class="w-12 h-12 rounded-lg bg-neutral-200 border-2 border-dashed border-neutral-400
                                       text-2xl text-neutral-500 font-bold leading-none
                                       hover:bg-neutral-300 hover:border-neutral-600 transition-colors">+</button>
                    </div>
                </section>

                {{-- â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
                     BLOC â€” CONSTELLATIONS  (col-span-2)
                â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• --}}
                <section data-block-id="constellations" draggable="true"
                         @dragstart="startDrag" @dragover.prevent @drop="dropOn"
                         class="editor-block border border-neutral-400 bg-neutral-100 rounded-lg p-4 cursor-move relative">

                    <h2 class="text-xs font-bold text-neutral-500 uppercase tracking-widest mb-3">Constellations</h2>

                    <div class="grid grid-cols-12 gap-4">
                        {{-- Image constellation â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ --}}
                        <div class="col-span-6">
                            <div class="h-52 rounded-lg overflow-hidden border border-neutral-400 bg-neutral-800">
                                <img src="{{ $constellationImage }}" alt="constellation"
                                     class="w-full h-full object-cover">
                            </div>
                        </div>
                        {{-- Liste des 6 constellations â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ --}}
                        <div class="col-span-6 space-y-1.5">
                            @forelse($personnage->constellations as $constellation)
                                <button type="button"
                                        @click="openConstellationDetail(constellations[{{ $loop->index }}])"
                                        class="w-full flex items-center gap-3 px-3 py-2 rounded-lg
                                               bg-neutral-200 border border-neutral-300 hover:bg-neutral-300
                                               transition-colors text-left">
                                    <span class="w-6 h-6 rounded-full bg-neutral-500 flex-shrink-0"></span>
                                    <span class="text-sm font-semibold text-neutral-800">{{ $constellation->titre_const }}</span>
                                </button>
                            @empty
                                @for($i = 0; $i < 6; $i++)
                                    <button type="button"
                                            @click="openConstellationDetail(constellations[{{ $i }}] ?? null)"
                                            class="w-full flex items-center gap-3 px-3 py-2 rounded-lg
                                                   bg-neutral-200 border border-neutral-300 hover:bg-neutral-300
                                                   transition-colors text-left">
                                        <span class="w-6 h-6 rounded-full bg-neutral-500 flex-shrink-0"></span>
                                        <span class="text-sm font-semibold text-neutral-400">nom Constellation {{ $i+1 }}</span>
                                    </button>
                                @endfor
                            @endforelse
                        </div>
                    </div>

                    {{-- Panel dÃ©tail constellation â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ --}}
                    <div x-show="constellationDetailOpen" x-cloak
                         class="const-detail-overlay">
                        <div class="flex items-start justify-between mb-3">
                            <h3 class="font-bold text-neutral-800 text-sm"
                                x-text="selectedConstellation?.titre ?? 'Constellation'"></h3>
                            <button type="button" @click="closeConstellationDetail()"
                                     class="w-6 h-6 rounded-full bg-neutral-400 text-neutral-700
                                         text-xs font-bold leading-none hover:bg-neutral-500">✕</button>
                        </div>

                        {{-- Points C1â€“C6 --}}
                        <div class="flex gap-1.5 mb-3">
                            <template x-for="i in 6" :key="i">
                                <div class="w-5 h-5 rounded-full border-2 transition-colors"
                                     :class="selectedConstellation && i <= (constellations.indexOf(selectedConstellation)+1)
                                        ? 'bg-yellow-400 border-yellow-500'
                                        : 'bg-neutral-300 border-neutral-400'"></div>
                            </template>
                        </div>

                        <p class="text-xs text-neutral-600 leading-relaxed mb-4"
                           x-text="selectedConstellation?.descri ?? ''"></p>

                        <button type="button" @click="nextConstellation()"
                                class="text-xs text-neutral-500 hover:text-neutral-800 font-medium">
                            constellation suivante →
                        </button>
                    </div>
                </section>

                {{-- â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
                     BLOC â€” COMPÃ‰TENCES  (col-span-2)
                â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• --}}
                <section data-block-id="competences" draggable="true"
                         @dragstart="startDrag" @dragover.prevent @drop="dropOn"
                         class="editor-block border border-neutral-400 bg-neutral-100 rounded-lg p-4 cursor-move">

                    <h2 class="text-xs font-bold text-neutral-500 uppercase tracking-widest mb-3">Compétences</h2>

                    <div class="space-y-3">
                        <template x-for="(apt, idx) in aptitudes" :key="idx">
                            <div class="flex items-center justify-between gap-3
                                        bg-neutral-200 border border-neutral-300 rounded-xl p-3">
                                <div class="flex items-center gap-3">
                                    <div class="w-14 h-14 rounded-full bg-neutral-500 flex-shrink-0 overflow-hidden
                                                flex items-center justify-center">
                                        <template x-if="apt.icon">
                                            <img :src="apt.icon" :alt="apt.titre" class="w-full h-full object-cover">
                                        </template>
                                        <template x-if="!apt.icon">
                                            <span class="text-neutral-300 text-2xl">✦</span>
                                        </template>
                                    </div>
                                    <div>
                                        <div class="font-semibold text-neutral-800 text-sm"
                                             x-text="apt.titre || 'nom Compétence'"></div>
                                        <div class="text-xs text-neutral-500" x-text="apt.type_label"></div>
                                    </div>
                                </div>
                                <button type="button" @click="openCompetenceModal(apt)"
                                        class="w-9 h-9 rounded-lg bg-neutral-300 border border-neutral-400
                                               flex items-center justify-center text-neutral-600 text-base
                                               hover:bg-neutral-400 transition-colors flex-shrink-0">✎</button>
                            </div>
                        </template>

                        {{-- Placeholder si aucune competence chargee --}}
                        <template x-if="aptitudes.length === 0">
                            <div>
                                @for($i = 0; $i < 3; $i++)
                                    <div class="flex items-center justify-between gap-3 bg-neutral-200 border border-neutral-300 rounded-xl p-3 mb-3">
                                        <div class="flex items-center gap-3">
                                            <div class="w-14 h-14 rounded-full bg-neutral-400 flex-shrink-0 flex items-center justify-center">
                                                <span class="text-neutral-200 text-2xl">✦</span>
                                            </div>
                                            <span class="font-semibold text-neutral-400 text-sm">nom Compétence</span>
                                        </div>
                                        <div class="w-9 h-9 rounded-lg bg-neutral-300 border border-neutral-400 flex items-center justify-center text-sm text-neutral-500">✎</div>
                                    </div>
                                @endfor
                            </div>
                        </template>

                        <button type="button" @click="openCompetenceModal(null)"
                                class="w-14 h-14 rounded-full bg-neutral-300 border-2 border-dashed border-neutral-400
                                       text-3xl text-neutral-500 font-bold leading-none
                                       hover:bg-neutral-400 transition-colors">+</button>
                    </div>
                </section>

            </div>{{-- /#editor-blocks --}}
        </div>

        {{-- â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
             MODAL â€” SÃ‰LECTEUR D'ARMES
        â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• --}}
        <div x-show="weaponSelectorOpen" x-cloak class="modal-backdrop" @click.self="closeWeaponSelector()">
            <div class="weapon-picker">
                <div class="flex items-center justify-between mb-3">
                        <h3 class="font-bold text-neutral-800 text-sm">Choisir une arme</h3>
                    <button type="button" @click="closeWeaponSelector()"
                            class="w-7 h-7 rounded-full bg-neutral-400 text-neutral-700 font-bold text-sm hover:bg-neutral-500">✕</button>
                </div>

                <input type="text" x-model="weaponSearch" placeholder="Rechercher une arme..."
                       class="w-full px-3 py-1.5 text-sm border border-neutral-400 rounded-lg bg-white mb-2">

                <label class="flex items-center gap-2 mb-3 cursor-pointer select-none">
                    <input type="checkbox" x-model="isStarterWeapon" class="rounded">
                    <span class="text-sm text-neutral-700">Starter Weapon</span>
                </label>

                <div class="weapon-grid-scroll overflow-y-auto grid grid-cols-4 gap-2" style="max-height:300px">
                    <template x-for="arme in filteredArmes" :key="arme.id">
                        <button type="button" @click="selectArme(arme)"
                                class="flex flex-col items-center rounded-lg p-1 border border-transparent
                                       hover:scale-105 transition-transform overflow-hidden"
                                :class="`rarity-${arme.rarity}`"
                                :title="arme.nom">
                            <div class="w-full aspect-square rounded overflow-hidden bg-black/20 mb-0.5">
                                <img x-show="arme.icon" :src="arme.icon" :alt="arme.nom"
                                     class="w-full h-full object-cover">
                                <div x-show="!arme.icon"
                                     class="w-full h-full flex items-center justify-center text-white text-[8px] font-bold"
                                     x-text="arme.rarity + '★'"></div>
                            </div>
                            <span class="text-white text-[8px] font-semibold text-center leading-tight line-clamp-2"
                                  x-text="arme.nom"></span>
                        </button>
                    </template>
                </div>
            </div>
        </div>

        {{-- â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
             MODAL â€” Ã‰DITION COMPÃ‰TENCE
        â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• --}}
        <div x-show="competenceModalOpen" x-cloak class="modal-backdrop" @click.self="closeCompetenceModal()">
            <div class="modal-panel">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-bold text-neutral-800 text-sm"
                        x-text="competenceForm.id ? 'Modifier la compétence' : 'Ajouter une compétence'"></h3>
                    <button type="button" @click="closeCompetenceModal()"
                            class="w-6 h-6 rounded-full bg-neutral-400 text-neutral-700 text-xs font-bold hover:bg-neutral-500">✕</button>
                </div>

                <div class="space-y-3">
                    <div>
                        <label class="text-xs font-semibold text-neutral-600 block mb-1">nom Compétence</label>
                        <input type="text" x-model="competenceForm.titre"
                               class="w-full px-3 py-2 text-sm border border-neutral-400 rounded-lg bg-white">
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-neutral-600 block mb-1">description de la compétence</label>
                        <textarea x-model="competenceForm.descri" rows="3"
                                  class="w-full px-3 py-2 text-sm border border-neutral-400 rounded-lg bg-white resize-none"></textarea>
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-neutral-600 block mb-1">type_compétence ▼</label>
                        <select x-model="competenceForm.fid_TypeApti"
                                class="w-full px-3 py-2 text-sm border border-neutral-400 rounded-lg bg-white">
                            <option value="">- Selectionner -</option>
                            @foreach($typesApti as $ta)
                                <option value="{{ $ta->id_TypeApti }}">{{ $ta->libelle_Apti }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex items-center gap-3">
                        <label class="text-xs font-semibold text-neutral-600">Niveau</label>
                        <button type="button"
                                @click="competenceForm.lvl = Math.max(1, competenceForm.lvl - 1)"
                                class="w-7 h-7 rounded-full bg-neutral-300 border border-neutral-400
                                       font-bold text-neutral-700 hover:bg-neutral-400">−</button>
                        <span class="text-sm font-bold text-neutral-800 w-5 text-center" x-text="competenceForm.lvl"></span>
                        <button type="button"
                                @click="competenceForm.lvl = Math.min(15, competenceForm.lvl + 1)"
                                class="w-7 h-7 rounded-full bg-neutral-300 border border-neutral-400
                                       font-bold text-neutral-700 hover:bg-neutral-400">+</button>
                    </div>
                    <div class="rounded-lg bg-neutral-200 border border-neutral-300 p-3
                                text-xs text-neutral-500 text-center min-h-[48px] flex items-center justify-center">
                        stats de la compétence en fonction du niveau
                    </div>
                </div>

                <div class="mt-4 flex items-center justify-between">
                    <button type="button" @click="saveCompetenceFromModal()"
                            class="px-4 py-2 rounded-lg bg-neutral-700 text-white text-sm font-medium
                                   hover:bg-neutral-800 transition-colors">
                        Enregistrer
                    </button>
                    <button type="button" @click="nextConstellation()"
                            class="text-xs text-neutral-500 hover:text-neutral-700">
                        constellation suivante →
                    </button>
                </div>
            </div>
        </div>

    </div>{{-- /x-data --}}

    <script>
    function personnageEditorLayout() {
        const cfg = document.getElementById('personnage-editor-config');

        const safeJsonParse = (raw, fallback) => {
            if (!raw || typeof raw !== 'string') return fallback;

            const decodeEntities = (value) => value
                .replace(/&quot;/g, '"')
                .replace(/&#34;/g, '"')
                .replace(/&#039;/g, "'")
                .replace(/&#39;/g, "'")
                .replace(/&amp;/g, '&')
                .trim();

            const attempts = [raw, decodeEntities(raw)];
            for (const candidate of attempts) {
                try {
                    return JSON.parse(candidate);
                } catch (e) {
                    // Try next decoding strategy.
                }
            }
            return fallback;
        };

        const dataset = cfg?.dataset ?? {};
        const mzRaw     = safeJsonParse(dataset.mainZone, {});
        const boRaw     = safeJsonParse(dataset.blockOrder, []);
        const armesAll  = safeJsonParse(dataset.armes, []);
        const constsAll = safeJsonParse(dataset.constellations, []);
        const aptsAll   = safeJsonParse(dataset.aptitudes, []);

        return {
            /* â”€â”€ Main zone â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ */
            mainZone: {
                nom_perso:       mzRaw.nom_perso       || '',
                fid_element:     mzRaw.fid_element     || '',
                fid_etoile:      mzRaw.fid_etoile      || '',
                fid_TArmes:      mzRaw.fid_TArmes      || '',
                fid_TP:          mzRaw.fid_TP          || '',
                fid_nations:     Array.isArray(mzRaw.fid_nations) ? mzRaw.fid_nations : [],
                background_actif:mzRaw.background_actif || '',
                videos:          Array.isArray(mzRaw.videos) ? mzRaw.videos : [],
            },
            activeNation:     (Array.isArray(mzRaw.fid_nations) && mzRaw.fid_nations.length) ? mzRaw.fid_nations[0] : '',
            activeVideoIndex: 0,
            saveState:        '',
            saveMessage:      '',
            iconePreview:     dataset.iconePreview    || '',
            portraitPreview:  dataset.portraitPreview || '',

            /* â”€â”€ Drag & drop â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ */
            blockOrder:      Array.isArray(boRaw) ? boRaw : [],
            draggingBlockId: '',

            /* â”€â”€ Constellations â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ */
            constellations:          constsAll,
            constellationDetailOpen: false,
            selectedConstellation:   null,

            /* â”€â”€ SÃ©lecteur d'armes â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ */
            weaponSelectorOpen: false,
            weaponSearch:       '',
            isStarterWeapon:    false,
            armesAll:           armesAll,

            /* â”€â”€ CompÃ©tences â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ */
            aptitudes:           aptsAll,
            competenceModalOpen: false,
            competenceForm: { id: null, titre: '', descri: '', lvl: 1, fid_TypeApti: '', icon: '' },

            /* â”€â”€ Computed â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ */
            get activeEmbedUrl() {
                const v = this.mainZone.videos[this.activeVideoIndex];
                return this.toEmbedUrl(v?.url_video || '');
            },
            get filteredArmes() {
                if (!this.weaponSearch.trim()) return this.armesAll;
                const q = this.weaponSearch.toLowerCase();
                return this.armesAll.filter(a => a.nom.toLowerCase().includes(q));
            },

            /* â”€â”€ Init drag â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ */
            initBlockOrder() {
                const container = this.$el.querySelector('#editor-blocks');
                if (!container) return;
                this.blockOrder.forEach(id => {
                    const sec = container.querySelector(`[data-block-id="${id}"]`);
                    if (sec) container.appendChild(sec);
                });
            },

            /* â”€â”€ Drag & drop â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ */
            startDrag(event) {
                this.draggingBlockId = event.currentTarget.dataset.blockId || '';
                event.currentTarget.classList.add('is-dragging');
            },
            dropOn(event) {
                const target    = event.currentTarget;
                const container = this.$el.querySelector('#editor-blocks');
                if (!container || !this.draggingBlockId) return;
                const dragged = container.querySelector(`[data-block-id="${this.draggingBlockId}"]`);
                if (!dragged || dragged === target) { this.draggingBlockId = ''; return; }
                dragged.classList.remove('is-dragging');
                const rect       = target.getBoundingClientRect();
                const insertAfter= (event.clientY - rect.top) > (rect.height / 2);
                container.insertBefore(dragged, insertAfter ? target.nextSibling : target);
                this.draggingBlockId = '';
                this.captureAndSaveBlockOrder();
            },
            async captureAndSaveBlockOrder() {
                const container = this.$el.querySelector('#editor-blocks');
                if (!container) return;
                const order = Array.from(container.querySelectorAll('[data-block-id]'))
                    .map(s => s.dataset.blockId).filter(Boolean);
                this.blockOrder = order;
                try {
                    await fetch(dataset.saveBlockOrderUrl, {
                        method: 'PATCH',
                        headers: { 'Content-Type':'application/json', 'X-CSRF-TOKEN': dataset.csrf, 'Accept':'application/json' },
                        body: JSON.stringify({ block_order: order }),
                    });
                } catch(e) {}
            },

            /* â”€â”€ VidÃ©os â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ */
            addVideo()        { this.mainZone.videos.push({ url_video: '' }); this.activeVideoIndex = this.mainZone.videos.length - 1; },
            removeVideo(i)    { this.mainZone.videos.splice(i, 1); if (this.activeVideoIndex >= this.mainZone.videos.length) this.activeVideoIndex = Math.max(0, this.mainZone.videos.length - 1); },
            setActiveVideo(i) { this.activeVideoIndex = i; },
            syncNationAndBackgrounds() { this.mainZone.fid_nations = this.activeNation ? [this.activeNation] : []; },
            toEmbedUrl(raw) {
                if (!raw) return '';
                try {
                    const url = new URL(raw);
                    const buildEmbedUrl = (videoId) => {
                        if (!videoId) return '';
                        const params = 'rel=0&modestbranding=1&playsinline=1&iv_load_policy=3';
                        return `https://www.youtube-nocookie.com/embed/${videoId}?${params}`;
                    };
                    if (url.hostname.includes('youtu.be')) {
                        return buildEmbedUrl(url.pathname.slice(1));
                    }
                    if (url.hostname.includes('youtube.com')) {
                        const v = url.searchParams.get('v');
                        return buildEmbedUrl(v);
                    }
                } catch(e) {}
                return '';
            },

            /* â”€â”€ Enregistrer main zone â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ */
            async saveMainZone() {
                this.saveState = ''; this.saveMessage = '';
                const payload = {
                    nom_perso:       this.mainZone.nom_perso,
                    fid_element:     this.mainZone.fid_element,
                    fid_etoile:      this.mainZone.fid_etoile,
                    fid_TArmes:      this.mainZone.fid_TArmes  || null,
                    fid_TP:          this.mainZone.fid_TP      || null,
                    fid_nations:     this.mainZone.fid_nations,
                    background_actif:this.mainZone.background_actif || null,
                    videos:          this.mainZone.videos.filter(v => v.url_video?.trim()),
                };
                try {
                    const resp = await fetch(dataset.saveMainZoneUrl, {
                        method: 'PUT',
                        headers: { 'Content-Type':'application/json', 'X-CSRF-TOKEN': dataset.csrf, 'Accept':'application/json' },
                        body: JSON.stringify(payload),
                    });
                    const data = await resp.json();
                    if (resp.ok && (data.success !== false)) {
                        this.saveState   = 'ok';
                        this.saveMessage = '✓ Enregistré';
                        setTimeout(() => this.saveMessage = '', 3000);
                    } else {
                        this.saveState = 'error'; this.saveMessage = data.message || 'Erreur';
                    }
                } catch(e) { this.saveState = 'error'; this.saveMessage = 'Erreur réseau'; }
            },

            /* â”€â”€ Upload portrait/icone â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ */
            async uploadImage(event, type) {
                const file = event.target.files?.[0];
                if (!file) return;
                await this.uploadImageFile(file, type);
                event.target.value = '';
            },
            async uploadDroppedImage(event, type) {
                const file = event.dataTransfer?.files?.[0];
                if (!file) return;
                await this.uploadImageFile(file, type);
            },
            async uploadImageFile(file, type) {
                const fd = new FormData();
                fd.append('image', file);
                fd.append('image_type', type);
                fd.append('_token', dataset.csrf);
                try {
                    const resp = await fetch(dataset.uploadImageUrl, {
                        method: 'POST',
                        headers: { 'Accept': 'application/json' },
                        body: fd,
                    });

                    const data = await resp.json().catch(() => ({}));
                    if (!resp.ok) {
                        this.saveState = 'error';
                        this.saveMessage = data.message || 'Upload image impossible';
                        return;
                    }

                    if (data.url) {
                        if (type === 'portrait') this.portraitPreview = data.url;
                        else this.iconePreview = data.url;
                    }
                    this.saveState = 'ok';
                    this.saveMessage = '✓ Image mise à jour';
                    setTimeout(() => {
                        if (this.saveMessage === '✓ Image mise à jour') this.saveMessage = '';
                    }, 2000);
                } catch (e) {
                    this.saveState = 'error';
                    this.saveMessage = 'Erreur réseau';
                }
            },

            /* â”€â”€ SÃ©lecteur d'armes â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ */
            openWeaponSelector()  { this.weaponSelectorOpen = true; this.weaponSearch = ''; this.isStarterWeapon = false; },
            closeWeaponSelector() { this.weaponSelectorOpen = false; },
            async selectArme(arme) {
                const existing = safeJsonParse(dataset.existingArmes, []);
                const armes = [...existing, arme.id];
                try {
                    const resp = await fetch(dataset.saveArmesUrl, {
                        method: 'PUT',
                        headers: { 'Content-Type':'application/json', 'X-CSRF-TOKEN': dataset.csrf, 'Accept':'application/json' },
                        body: JSON.stringify({ armes: armes.map((id, i) => ({
                            fid_arme: id,
                            starter:  this.isStarterWeapon && i === armes.length - 1,
                            position: i + 1,
                            origine:  'pull',
                        })) }),
                    });
                    if (resp.ok) { this.closeWeaponSelector(); window.location.reload(); }
                } catch(e) {}
            },

            /* â”€â”€ Constellations â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ */
            openConstellationDetail(c)  { if (!c) return; this.selectedConstellation = c; this.constellationDetailOpen = true; },
            closeConstellationDetail()  { this.constellationDetailOpen = false; this.selectedConstellation = null; },
            nextConstellation() {
                const idx = this.constellations.indexOf(this.selectedConstellation);
                if (idx >= 0 && idx < this.constellations.length - 1)
                    this.selectedConstellation = this.constellations[idx + 1];
            },

            /* â”€â”€ CompÃ©tences â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ */
            openCompetenceModal(apt) {
                this.competenceForm = apt
                    ? { id: apt.id, titre: apt.titre, descri: apt.descri, lvl: apt.lvl, fid_TypeApti: apt.fid_TypeApti, icon: apt.icon }
                    : { id: null, titre: '', descri: '', lvl: 1, fid_TypeApti: '', icon: '' };
                this.competenceModalOpen = true;
            },
            closeCompetenceModal() { this.competenceModalOpen = false; },
            async saveCompetenceFromModal() {
                const form = this.competenceForm;
                const idx  = this.aptitudes.findIndex(a => a.id === form.id && form.id !== null);
                if (idx >= 0) Object.assign(this.aptitudes[idx], form);
                else          this.aptitudes.push({ ...form });
                await this.persistCompetences();
                this.closeCompetenceModal();
            },
            async persistCompetences() {
                try {
                    await fetch(dataset.saveCompetencesUrl, {
                        method: 'PUT',
                        headers: { 'Content-Type':'application/json', 'X-CSRF-TOKEN': dataset.csrf, 'Accept':'application/json' },
                        body: JSON.stringify({ competences: this.aptitudes.map(a => ({
                            id_aptitude:  a.id,
                            titre_apti:   a.titre,
                            descri_apti:  a.descri,
                            lvl_apt:      a.lvl,
                            fid_TypeApti: a.fid_TypeApti,
                        })) }),
                    });
                } catch(e) {}
            },
        };
    }
    </script>
</x-admin-layout>
