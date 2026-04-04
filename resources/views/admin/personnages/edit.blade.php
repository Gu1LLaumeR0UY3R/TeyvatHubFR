<x-admin-layout>
    <x-slot name="title">Modifier {{ $personnage->nom_perso }} — Admin</x-slot>

    <div class="hidden" aria-hidden="true">
        <input type="text" name="nom_perso" value="{{ $personnage->nom_perso }}" />
        <select name="fid_element">
            @foreach($elements as $el)
                <option value="{{ $el->id_element }}" {{ (int) $personnage->fid_element === (int) $el->id_element ? 'selected' : '' }}>{{ $el->libelle_element }}</option>
            @endforeach
        </select>
        <select name="fid_TArmes">
            @foreach($typesArme as $ta)
                <option value="{{ $ta->id_TArmes }}" {{ (int)$personnage->fid_TArmes === (int)$ta->id_TArmes ? 'selected' : '' }}>{{ $ta->libelle_TArme }}</option>
            @endforeach
        </select>
        <div id="video-preview">
            @foreach($personnage->videos as $video)
            <span>{{ $video->url_video }}</span>
            @endforeach
        </div>
        <div id="armes-preview">
            @foreach($personnage->armesRecommandees as $armeRec)
            <span>{{ $armeRec->arme?->nom_arme }}</span>
            @endforeach
        </div>
    </div>

    <style>
        .character-show-hero { --csh-panel: rgba(13, 18, 42, 0.72); --csh-border: rgba(255,255,255,0.12); --csh-text: #eef2ff; --csh-muted: #bdc8ec; --csh-accent: #6fd0be; max-width: min(1800px, 95vw); margin:0 auto 2.25rem; padding:2rem; position:relative; border-radius: 22px; border:1px solid var(--csh-border); background: linear-gradient(160deg, rgba(255,255,255,0.065), rgba(255,255,255,0.015)), linear-gradient(180deg, rgba(10,15,35,0.9), rgba(10,15,35,0.74)); box-shadow: 0 24px 56px rgba(5,9,28,0.52), inset 0 1px 0 rgba(255,255,255,0.07); display:grid; grid-template-columns: clamp(220px,18vw,320px) minmax(0,1fr); grid-template-areas: "portrait hero" "portrait video" "portrait meta"; column-gap: 1.5rem; row-gap:1rem; align-items:start; color: var(--csh-text); font-family:'Space Grotesk', 'Trebuchet MS', sans-serif; }
        .character-show-hero[data-element="anemo"] { --csh-accent:#74C2A8; }
        .character-show-hero[data-element="geo"] { --csh-accent:#f2be42; }
        .character-show-hero[data-element="electro"] { --csh-accent:#b88ef8; }
        .character-show-hero[data-element="dendro"] { --csh-accent:#9ecf34; }
        .character-show-hero[data-element="hydro"] { --csh-accent:#67c5ff; }
        .character-show-hero[data-element="pyro"] { --csh-accent:#ff8550; }
        .character-show-hero[data-element="cryo"] { --csh-accent:#91d8ee; }

        .character-show-hero::before, .character-show-hero::after { content:''; position:absolute; border-radius: 50%; pointer-events:none; filter: blur(18px); opacity:0.22; }
        .character-show-hero::before { width:300px; height:300px; top:-120px; right:-70px; background: var(--csh-accent); }
        .character-show-hero::after { width:240px; height:240px; bottom:-120px; left:-90px; background: #f6aa4f; }

        .csh-portrait {
            position:relative;
            z-index:1;
            border-radius:16px;
            overflow:hidden;
            border:1px solid rgba(255,255,255,0.15);
            background: linear-gradient(180deg, rgba(8,12,30,.4), rgba(8,12,30,.18));
            width: 100%;
            max-width: 1024px;
            height: 768px;
            grid-area:portrait;
        }
        .csh-full {
            position:relative;
            z-index:1;
            border-radius:16px;
            overflow:hidden;
            border:1px solid rgba(255,255,255,0.15);
            background: linear-gradient(180deg, rgba(8,12,30,.4), rgba(8,12,30,.18));
            width: min(100%, 860px);
            aspect-ratio: 16 / 9;
            grid-area:video;
        }
        .csh-portrait img, .csh-full img { width: 100%; height: 100%; object-fit: cover; object-position: center; transform: scale(1); transition: transform .4s ease; pointer-events: none; -webkit-user-drag: none; user-select: none; }
        .csh-portrait:hover img, .csh-full:hover img { transform: scale(1.02); }

        .csh-hero { grid-area:hero; padding-bottom:0.8rem; border-bottom:1px solid rgba(255,255,255,0.08); }
        .csh-hero-head { display:flex; align-items:center; gap:.8rem; }
        .csh-name { font-family:'Cinzel', Georgia, serif; font-size: clamp(2rem,4vw,3rem); margin:0; line-height:1.05; text-shadow:0 3px 12px rgba(0,0,0,0.25); color:#eff6ff; }
        .csh-hero input { width:100%; color:#f8fafc !important; font-weight:700; background:rgba(7,12,24,0.65) !important; border:1px solid rgba(132,144,255,0.38); border-radius:0.5rem; padding:0.6rem 0.8rem; box-shadow: inset 0 0 0 1px rgba(255,255,255,0.08); }
        .csh-hero input::placeholder { color:#cbd5e1; }
        .csh-role { margin:0 0 0.65rem; color: var(--csh-muted); font-size:clamp(0.9rem,1.8vw,1.1rem); }
        .csh-icon { display:flex; align-items:center; gap:.6rem; margin-bottom:0; }
        .csh-icon-img { width:42px; height:42px; border-radius:999px; overflow:hidden; border:1px solid rgba(255,255,255,0.35); background:rgba(255,255,255,0.12); }
        .csh-icon-img img { width:100%; height:100%; object-fit:cover; }
        .csh-icon-drop { flex:1; font-size:.85rem; color:#cbd5e1; background:rgba(15,23,42,0.45); border:1px dashed rgba(148,163,184,0.55); border-radius:10px; padding:.5rem .65rem; text-align:center; cursor:pointer; }
        .csh-icon-drop:hover { background:rgba(15,23,42,0.62); }
        .csh-pill { background: var(--csh-panel); border:1px solid rgba(255,255,255,0.11); border-radius:12px; padding:.45rem .55rem; min-height:52px; display:flex; flex-direction:column; justify-content:center; }
        .csh-pill-label { color:var(--csh-muted); font-size:.66rem; letter-spacing:.08em; text-transform:uppercase; margin-bottom:.14rem; }
        .csh-pill-value { color:var(--csh-text); font-size:.95rem; font-weight:700; }
        .csh-pill--element .csh-pill-value { color: var(--csh-accent); font-weight:800; }
        .csh-meta { grid-area:meta; display:grid; grid-template-columns: repeat(2,minmax(0,1fr)); gap:.42rem; padding-top:.63rem; }
        .csh-preview-table {
            display:grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 1rem;
            margin: 0 1.5rem 1.5rem;
        }
        .csh-preview-panel {
            border: 1px solid rgba(255,255,255,0.12);
            border-radius: 18px;
            background: linear-gradient(180deg, rgba(15,23,42,0.92), rgba(8,13,30,0.9));
            box-shadow: 0 18px 40px rgba(2, 6, 23, 0.32);
            overflow: hidden;
            min-height: 100%;
        }
        .csh-preview-panel-head {
            display:flex;
            align-items:center;
            justify-content:space-between;
            gap:.75rem;
            padding: 1rem 1.15rem;
            border-bottom: 1px solid rgba(255,255,255,0.08);
        }
        .csh-preview-panel-title { color:#e5eefc; font-size:.92rem; font-weight:700; letter-spacing:.04em; text-transform:uppercase; }
        .csh-preview-panel-subtitle { color:#8aa0ca; font-size:.72rem; }
        .csh-preview-weapon-list { display:grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap:.85rem; padding: 1rem 1.15rem 1.15rem; }
        .csh-preview-artefact-list { display:grid; gap:.85rem; padding: 1rem 1.15rem 1.15rem; }

        .csh-weapon-item { border:1px solid rgba(148,163,184,0.35); border-radius:0.6rem; background:rgba(15,23,42,0.58); padding:.55rem; }
        .csh-weapon-item {
            position: relative;
            display:flex;
            align-items:center;
            gap:.75rem;
            min-height:72px;
            background: linear-gradient(180deg, rgba(18, 28, 55, 0.86), rgba(10, 16, 34, 0.88));
        }
        .csh-weapon-name { font-weight:700; color:#e2e8f0; }
        .csh-weapon-copy { min-width:0; flex:1; }
        .csh-weapon-type { color:#98a8c7; font-size:.72rem; }
        .csh-weapon-index {
            width: 26px;
            height: 26px;
            border-radius: 999px;
            display:flex;
            align-items:center;
            justify-content:center;
            font-size:.72rem;
            font-weight:700;
            color:#eff6ff;
            background: rgba(255,255,255,0.08);
            border:1px solid rgba(255,255,255,0.1);
            flex-shrink:0;
        }
        .csh-weapon-icon-wrap {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            flex-shrink: 0;
            display:flex;
            align-items:center;
            justify-content:center;
            border:1px solid rgba(255,255,255,0.22);
            box-shadow: inset 0 1px 0 rgba(255,255,255,0.12);
        }
        .csh-weapon-icon-wrap img { width: 34px; height: 34px; object-fit: contain; filter: drop-shadow(0 3px 5px rgba(0,0,0,.35)); }
        .csh-weapon-badge {
            display:inline-flex;
            align-items:center;
            gap:.28rem;
            margin-top:.35rem;
            padding:.18rem .45rem;
            border-radius:999px;
            font-size:.68rem;
            font-weight:700;
            letter-spacing:.02em;
            background: rgba(16, 185, 129, 0.18);
            color: #b9f7df;
            border:1px solid rgba(52, 211, 153, 0.34);
        }
        .csh-artefact-item {
            border:1px solid rgba(148,163,184,0.3);
            border-radius:14px;
            padding:.85rem .95rem;
            background: linear-gradient(180deg, rgba(17, 24, 39, 0.9), rgba(9, 14, 27, 0.92));
            box-shadow: inset 0 1px 0 rgba(255,255,255,0.05);
        }
        .csh-artefact-head {
            display:flex;
            align-items:center;
            justify-content:space-between;
            gap:.75rem;
            margin-bottom:.5rem;
        }
        .csh-artefact-title { color:#e2e8f0; font-size:.9rem; font-weight:700; }
        .csh-artefact-piece { color:#fef3c7; font-size:.72rem; font-weight:700; }
        .csh-artefact-row { display:flex; align-items:center; justify-content:space-between; gap:.75rem; padding:.35rem 0; }
        .csh-artefact-name { color:#cbd5e1; font-size:.82rem; }
        .csh-artefact-empty { padding:1rem 1.15rem 1.15rem; color:#8fa1c5; font-size:.85rem; font-style:italic; }
        .csh-weapon-empty { padding:1rem 1.15rem 1.15rem; color:#8fa1c5; font-size:.85rem; font-style:italic; }
        .csh-constellation-shell {
            margin: 0 1.5rem 1.5rem;
            border: 1px solid rgba(255,255,255,0.12);
            border-radius: 18px;
            background: linear-gradient(180deg, rgba(10, 15, 30, 0.95), rgba(5, 10, 24, 0.92));
            box-shadow: 0 18px 40px rgba(2, 6, 23, 0.32);
            overflow: hidden;
        }
        .csh-constellation-grid {
            display:grid;
            grid-template-columns: minmax(220px, 320px) minmax(0, 1fr);
            min-height: 360px;
        }
        .csh-constellation-media {
            padding: 1rem;
            border-right: 1px solid rgba(255,255,255,0.08);
            background: radial-gradient(circle at top, rgba(125, 211, 252, 0.12), rgba(15, 23, 42, 0.4));
        }
        .csh-constellation-frame {
            height: 100%;
            min-height: 300px;
            border: 1px solid rgba(255,255,255,0.12);
            border-radius: 14px;
            overflow: hidden;
            background: linear-gradient(180deg, rgba(30,41,59,0.7), rgba(15,23,42,0.92));
            display:flex;
            align-items:center;
            justify-content:center;
        }
        .csh-constellation-frame img { width:100%; height:100%; object-fit:cover; }
        .csh-constellation-empty-media { color:#93a7cb; font-size:.82rem; text-align:center; padding: 0 1rem; }
        .csh-constellation-content { padding: 1rem 1.15rem 1.15rem; display:flex; flex-direction:column; gap:.9rem; }
        .csh-constellation-tabs { display:grid; grid-template-columns: repeat(3, minmax(0,1fr)); gap:.5rem; }
        .csh-constellation-tab {
            border:1px solid rgba(148,163,184,0.4);
            border-radius:10px;
            padding:.45rem .55rem;
            color:#cbd5e1;
            background: rgba(15,23,42,0.55);
            text-align:left;
            font-size:.76rem;
            transition: .15s ease;
        }
        .csh-constellation-tab:hover { border-color: rgba(125,211,252,.7); color:#e2e8f0; }
        .csh-constellation-tab.is-active {
            border-color:#7dd3fc;
            box-shadow: 0 0 0 1px rgba(125,211,252,0.35) inset;
            background: rgba(14,116,144,0.22);
            color:#e0f2fe;
        }
        .csh-constellation-detail {
            border:1px solid rgba(148,163,184,0.35);
            border-radius:12px;
            padding:.8rem .9rem;
            background: rgba(15,23,42,0.5);
        }
        .csh-constellation-title { color:#f1f5f9; font-size:1rem; font-weight:700; }
        .csh-constellation-desc { color:#cbd5e1; font-size:.84rem; line-height:1.45; margin-top:.45rem; white-space:pre-wrap; }
        .th-const-map-shell { border: 1px solid #cbd5e1; border-radius: 12px; background: #f8fafc; padding: .75rem; }
        .th-const-map-canvas {
            position: relative;
            width: 100%;
            aspect-ratio: 1 / 1;
            border: 1px dashed #94a3b8;
            border-radius: 12px;
            overflow: hidden;
            background: linear-gradient(180deg, #e2e8f0 0%, #cbd5e1 100%);
            cursor: crosshair;
        }
        .th-const-map-canvas img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            pointer-events: none;
            user-select: none;
        }
        .th-const-map-point {
            position: absolute;
            transform: translate(-50%, -50%);
            width: 24px;
            height: 24px;
            border-radius: 999px;
            border: 2px solid #ffffff;
            background: #0ea5e9;
            color: #ffffff;
            font-size: 11px;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 10px rgba(2, 6, 23, 0.35);
        }
        .th-const-map-point.is-selected {
            background: #22c55e;
            box-shadow: 0 0 0 2px rgba(34, 197, 94, 0.35), 0 4px 10px rgba(2, 6, 23, 0.35);
        }
        .th-const-map-remove {
            position: absolute;
            top: -7px;
            right: -7px;
            width: 14px;
            height: 14px;
            border-radius: 999px;
            border: 1px solid #ef4444;
            background: #ffffff;
            color: #dc2626;
            line-height: 1;
            font-size: 10px;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
        .csh-weapon-link { color:#93c5fd; text-decoration:underline; }
        .csh-weapon-rarity-1 { background:#9ca3af; }
        .csh-weapon-rarity-2 { background:#34d399; }
        .csh-weapon-rarity-3 { background:#60a5fa; }
        .csh-weapon-rarity-4 { background:#a78bfa; }
        .csh-weapon-rarity-5 { background:#facc15; }
        .csh-weapon-actions button { width:28px; height:28px; font-size:0.8rem; display:inline-flex; align-items:center; justify-content:center; border-radius:50%; border:1px solid rgba(148,163,184,0.45); background:rgba(15,23,42,0.6); color:#fff; }

        .th-weapon-card {
            border: 1px solid #cbd5e1;
            border-radius: 12px;
            background:
                radial-gradient(120% 100% at 0% 0%, rgba(255,255,255,0.9), rgba(255,255,255,0.65) 45%, rgba(241,245,249,0.92) 100%),
                linear-gradient(180deg, #f8fafc 0%, #eef2f7 100%);
            box-shadow: 0 8px 18px rgba(15, 23, 42, 0.08), inset 0 1px 0 rgba(255,255,255,0.8);
            overflow: hidden;
        }
        .th-armes-picker-modal { position: fixed; z-index: 50; background: white; border: 1px solid #cbd5e1; border-radius: 12px; box-shadow: 0 10px 40px rgba(0,0,0,0.15); max-width: 640px; width: 100%; max-height: 70vh; overflow-y: auto; }
        .th-armes-picker-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 8px; padding: 12px; }
        .th-armes-picker-item { cursor: pointer; border: 1px solid #e2e8f0; border-radius: 8px; padding: 8px; text-align: center; background: #f8fafc; transition: all 0.2s; }
        .th-armes-picker-item:hover { border-color: #0ea5e9; background: #f0f9ff; transform: scale(1.02); }
        .th-armes-picker-icon { display: flex; justify-content: center; margin-bottom: 6px; }
        .th-armes-picker-item .name { font-size: 11px; font-weight: 600; color: #0f172a; line-height: 1.2; }
        .th-armes-picker-item .rarity { font-size: 10px; color: #64748b; }
        .th-weapon-card-inner { display:flex; align-items:center; gap:10px; padding:10px; }
        .th-weapon-rarity-1 {
            background: linear-gradient(145deg, #6d7685 0%, #98a3b5 45%, #c5cfdd 100%);
            box-shadow: 0 0 0 1px rgba(122, 133, 152, 0.45), 0 0 18px rgba(117, 125, 142, 0.28);
        }
        .th-weapon-rarity-2 {
            background: linear-gradient(145deg, #496f4f 0%, #6f9e66 45%, #b6d58b 100%);
            box-shadow: 0 0 0 1px rgba(88, 126, 86, 0.42), 0 0 18px rgba(110, 158, 102, 0.3);
        }
        .th-weapon-rarity-3 {
            background: linear-gradient(145deg, #315e9f 0%, #3f8ccd 48%, #9bd6ff 100%);
            box-shadow: 0 0 0 1px rgba(60, 108, 166, 0.42), 0 0 18px rgba(82, 150, 215, 0.3);
        }
        .th-weapon-rarity-4 {
            background: linear-gradient(145deg, #4f3a8b 0%, #6f55b7 48%, #c0a2ff 100%);
            box-shadow: 0 0 0 1px rgba(85, 65, 140, 0.45), 0 0 18px rgba(108, 78, 176, 0.32);
        }
        .th-weapon-rarity-5 {
            background: linear-gradient(145deg, #8f6822 0%, #c4963f 48%, #f6de8d 100%);
            box-shadow: 0 0 0 1px rgba(146, 110, 42, 0.45), 0 0 18px rgba(192, 146, 63, 0.34);
        }
        .th-weapon-icon-wrap {
            width: 44px;
            height: 44px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 1px solid rgba(255,255,255,0.78);
            box-shadow: inset 0 1px 0 rgba(255,255,255,0.42), 0 3px 8px rgba(15, 23, 42, 0.18);
            position: relative;
        }
        .th-weapon-icon-wrap::before {
            content: '';
            position: absolute;
            inset: 1px;
            border-radius: 9px;
            background: linear-gradient(180deg, rgba(255,255,255,0.32), rgba(255,255,255,0));
            pointer-events: none;
        }
        .th-weapon-icon-wrap img { width:30px; height:30px; object-fit:contain; filter: drop-shadow(0 2px 2px rgba(0, 0, 0, 0.25)); }
        .th-state-dot { width:10px; height:10px; border-radius:999px; border:1px solid #94a3b8; background:#e2e8f0; }
        .th-state-dot.is-active { background:#0ea5e9; border-color:#0284c7; }
        .th-weapon-card.is-dragging { opacity: 0.62; transform: scale(0.985); }
        .th-weapon-card.is-drop-target { border-color: #38bdf8; box-shadow: 0 0 0 2px rgba(56, 189, 248, 0.25); }
        .th-grab-handle {
            width: 20px;
            height: 34px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: 1px dashed #94a3b8;
            border-radius: 8px;
            color: #64748b;
            background: #f1f5f9;
            cursor: grab;
            user-select: none;
            font-size: 14px;
            line-height: 1;
        }
        .th-grab-handle:active { cursor: grabbing; }
        .th-drop-ghost {
            border: 1px dashed #7dd3fc;
            border-radius: 10px;
            background: rgba(14, 165, 233, 0.08);
            padding: 0;
            overflow: hidden;
            opacity: 0.55;
            pointer-events: auto;
        }
        .th-drop-ghost .th-weapon-card-inner,
        .th-drop-ghost .border-t {
            background: rgba(255, 255, 255, 0.72);
        }


        @media (max-width: 900px) {
            .character-show-hero { grid-template-columns: 1fr; grid-template-areas: "hero" "portrait" "video" "meta"; padding:1.1rem; }
            .csh-portrait { min-height: 280px; }
            .csh-video { aspect-ratio:16/9; }
            .csh-preview-table { grid-template-columns: 1fr; }
            .csh-constellation-grid { grid-template-columns: 1fr; }
            .csh-constellation-media { border-right:0; border-bottom:1px solid rgba(255,255,255,0.08); }
        }
    </style>

    @php
        $portraitPhoto = $personnage->photos->where('type','portrait')->first();
        $iconePhoto = $personnage->photos->where('type','icone')->first() ?? $personnage->photos->first();
        $fullPhoto = $personnage->photos->where('type','portrait')->first() ?? $portraitPhoto;

        $elementIcons = [];
        foreach ($elements as $el) {
            $defaultIcon = asset('images/placeholder.svg');
            $photo = $el->photos->first();
            if ($photo) {
                if ($photo->source_url) {
                    $defaultIcon = $photo->source_url;
                } elseif (filter_var($photo->chemin_photo, FILTER_VALIDATE_URL)) {
                    $defaultIcon = $photo->chemin_photo;
                } else {
                    $defaultIcon = \Illuminate\Support\Facades\Storage::url($photo->chemin_photo);
                }
            } else {
                $elementFile = public_path("storage/photos/elements/icones/" . strtolower($el->libelle_element) . ".png");
                if (file_exists($elementFile)) {
                    $defaultIcon = asset("storage/photos/elements/icones/" . strtolower($el->libelle_element) . ".png");
                }
            }
            $elementIcons[$el->id_element] = $defaultIcon;
        }

        $nationIcons = [];
        foreach ($nations as $nation) {
            $nationSlug = $nation->slug ?? \Illuminate\Support\Str::slug($nation->nom_region);
            $nationIconPath = public_path('storage/photos/regions/icones/' . $nationSlug . '.png');
            $nationIcons[$nation->id_region] = file_exists($nationIconPath)
                ? asset('storage/photos/regions/icones/' . $nationSlug . '.png')
                : ($nation->icone_url ?? asset('images/placeholder.svg'));
        }

        $weaponTypeIcons = [];
        foreach ($typesArme as $ta) {
            $icon = asset('images/placeholder.svg');

            // Priorite aux icones locales standards (Icon_Bow.webp, Icon_Catalyst.webp, etc.)
            $weaponName = strtolower(trim((string) $ta->libelle_TArme));
            $weaponIconMap = [
                'bow' => 'Icon_Bow.webp',
                'arc' => 'Icon_Bow.webp',
                'catalyst' => 'Icon_Catalyst.webp',
                'catalyseur' => 'Icon_Catalyst.webp',
                'claymore' => 'Icon_Claymore.webp',
                'espadon' => 'Icon_Claymore.webp',
                'polearm' => 'Icon_Polearm.webp',
                'lance' => 'Icon_Polearm.webp',
                'sword' => 'Icon_Sword.webp',
                'épée' => 'Icon_Sword.webp',
                'epee' => 'Icon_Sword.webp',
            ];

            if (isset($weaponIconMap[$weaponName])) {
                $localPath = public_path('storage/photos/armes/' . $weaponIconMap[$weaponName]);
                if (file_exists($localPath)) {
                    $icon = asset('storage/photos/armes/' . $weaponIconMap[$weaponName]);
                }
            }

            $photo = $ta->photos->first();
            if ($icon === asset('images/placeholder.svg') && $photo) {
                if ($photo->source_url) {
                    $icon = $photo->source_url;
                } elseif (filter_var($photo->chemin_photo, FILTER_VALIDATE_URL)) {
                    $icon = $photo->chemin_photo;
                } else {
                    $icon = \Illuminate\Support\Facades\Storage::url($photo->chemin_photo);
                }
            }
            $weaponTypeIcons[$ta->id_TArmes] = $icon;
        }

        $elementLabels = [];
        foreach ($elements as $el) {
            $elementLabels[$el->id_element] = $el->libelle_element;
        }

        $weaponTypeLabels = [];
        foreach ($typesArme as $ta) {
            $weaponTypeLabels[$ta->id_TArmes] = $ta->libelle_TArme;
        }

        $nationLabels = [];
        foreach ($nations as $nation) {
            $nationLabels[$nation->id_region] = $nation->nom_region;
        }

        $etoileLabels = [];
        foreach ($etoiles as $et) {
            $etoileLabels[$et->id_etoile] = $et->libelle;
        }

        $mainZoneJson = json_encode([
            'nom_perso' => $personnage->nom_perso,
            'fid_element'=> (string)$personnage->fid_element,
            'fid_etoile' => (string)$personnage->fid_etoile,
            'fid_TArmes' => $personnage->fid_TArmes ? (string)$personnage->fid_TArmes : '',
            'fid_TP' => $personnage->fid_TP ? (string)$personnage->fid_TP : '',
            'fid_nation' => $personnage->nations->first()?->id_region ? (string) $personnage->nations->first()->id_region : '',
            'arme_icon' => $personnage->arme_icon ?? null,
            'videos' => $personnage->videos->map(fn($v)=>['url_video'=>$v->url_video])->values(),
        ]);

        $availableArmesJson = $armesDisponibles->map(function ($a) {
            $type = optional($a->typeArme)->libelle_TArme ?? '';
            $localIcon = asset('storage/photos/armes/icones_armes/' . $a->slug . '.png');
            $fileExists = file_exists(public_path('storage/photos/armes/icones_armes/' . $a->slug . '.png'));
            $rarityLabel = $a->etoile?->libelle ?? '?★';
            $rarityStars = (int) preg_replace('/\D+/', '', (string) $rarityLabel);
            if ($rarityStars < 1 || $rarityStars > 5) {
                $rarityStars = (int) ($a->fid_etoile ?? 0);
            }
            return [
                'id' => $a->id_arme,
                'nom' => $a->nom_arme,
                'slug' => $a->slug,
                'etoile' => $rarityLabel,
                'stars' => $rarityStars,
                'fid_etoile' => $a->fid_etoile,
                'fid_TArmes' => $a->fid_TArmes,
                'type' => $type,
                'icon' => $fileExists ? $localIcon : ($a->icone_url ?? asset('images/placeholder.svg')),
            ];
        });

        $existingArmesJson = $personnage->armesRecommandees->map(function ($w) {
            $type = $w->arme?->typeArme?->libelle_TArme ?? '';
            $slug = $w->arme?->slug;
            $localIcon = $slug ? asset('storage/photos/armes/icones_armes/' . $slug . '.png') : asset('images/placeholder.svg');
            $fileExists = $slug ? file_exists(public_path('storage/photos/armes/icones_armes/' . $slug . '.png')) : false;
            $rarityLabel = $w->arme?->etoile?->libelle ?? '?★';
            $rarityStars = (int) preg_replace('/\D+/', '', (string) $rarityLabel);
            if ($rarityStars < 1 || $rarityStars > 5) {
                $rarityStars = (int) ($w->arme?->fid_etoile ?? 0);
            }
            return [
                'id_arme' => $w->arme?->id_arme,
                'nom' => $w->arme?->nom_arme,
                'slug' => $slug,
                'etoile' => $rarityLabel,
                'stars' => $rarityStars,
                'fid_etoile' => $w->arme?->fid_etoile ?? 1,
                'fid_TArmes' => $w->arme?->fid_TArmes,
                'type' => $type,
                'icon' => $fileExists ? $localIcon : ($w->arme?->icone_url ?? asset('images/placeholder.svg')),
                'is_starter' => (bool) $w->starter,
                'origine' => $w->origine ?? 'tirage',
                'position' => $w->position,
            ];
        });

        $existingArtefactsJson = $personnage->artefactsRecommandees->map(function ($build) {
            $artefact1 = $build->artefact1?->nom_artefact;
            $artefact2 = $build->artefact2?->nom_artefact;

            return [
                'id_build' => $build->id_build,
                'artefact1_nom' => $artefact1,
                'pieces_1' => (int) $build->pieces_1,
                'artefact2_nom' => $artefact2,
                'pieces_2' => (int) ($build->pieces_2 ?? 0),
                'position' => (int) $build->position,
            ];
        })->values();

        $constellationImageFor = function (string $slug, int $index): string {
            $base = 'photos/personnages/constellations/' . $slug . '-c' . $index;
            foreach (['webp', 'png', 'jpg', 'jpeg'] as $ext) {
                $path = $base . '.' . $ext;
                if (\Illuminate\Support\Facades\Storage::disk('public')->exists($path)) {
                    return asset('storage/' . $path);
                }
            }

            return asset('images/placeholder.svg');
        };

        $constellationsJson = $personnage->constellations
            ->sortBy('id_const')
            ->values()
            ->map(function ($constellation, $idx) use ($personnage, $constellationImageFor) {
                $index = $idx + 1;
                return [
                    'id_const' => (int) $constellation->id_const,
                    'index' => $index,
                    'label' => 'C' . $index,
                    'titre_const' => $constellation->titre_const,
                    'descri_const' => $constellation->descri_const,
                    'image_url' => $constellationImageFor($personnage->slug, $index),
                ];
            });

        $constCarte = $personnage->constellations->sortBy('id_const')->first();
        $constellationMapPositionsJson = [];
        if ($constCarte && is_array($constCarte->positions_const)) {
            foreach ($constCarte->positions_const as $k => $point) {
                if (!is_array($point) || !isset($point['x']) || !isset($point['y'])) {
                    continue;
                }
                $key = (string) $k;
                if (!in_array($key, ['1', '2', '3', '4', '5', '6'], true)) {
                    continue;
                }
                $constellationMapPositionsJson[$key] = [
                    'x' => round((float) $point['x'], 1),
                    'y' => round((float) $point['y'], 1),
                ];
            }
        }

        $constellationMapImage = asset('images/placeholder.svg');
        if ($constCarte && $constCarte->photo) {
            if ($constCarte->photo->source_url) {
                $constellationMapImage = $constCarte->photo->source_url;
            } elseif (filter_var((string) $constCarte->photo->chemin_photo, FILTER_VALIDATE_URL)) {
                $constellationMapImage = $constCarte->photo->chemin_photo;
            } elseif ($constCarte->photo->chemin_photo) {
                $constellationMapImage = asset('storage/' . ltrim((string) $constCarte->photo->chemin_photo, '/'));
            }
        }
    @endphp

    <div id="personnage-editor-config"
         data-main-zone="{{ e($mainZoneJson) }}"
         data-nom-perso="{{ e($personnage->nom_perso) }}"
         data-fid-element="{{ e($personnage->fid_element) }}"
         data-fid-etoile="{{ e($personnage->fid_etoile) }}"
         data-fid-tarmes="{{ e($personnage->fid_TArmes) }}"
         data-fid-tp="{{ e($personnage->fid_TP) }}"
         data-fid-nation="{{ e($personnage->nations->first()?->id_region ?? '') }}"
         data-arme-icon="{{ e($personnage->arme_icon ?? '') }}"
         data-available-armes="{{ e(json_encode($availableArmesJson)) }}"
         data-existing-armes="{{ e(json_encode($existingArmesJson)) }}"
         data-existing-artefacts="{{ e(json_encode($existingArtefactsJson)) }}"
         data-constellations="{{ e(json_encode($constellationsJson)) }}"
         data-const-map-positions="{{ e(json_encode($constellationMapPositionsJson)) }}"
         data-const-map-image="{{ e($constellationMapImage) }}"
         data-element-icons="{{ e(json_encode($elementIcons)) }}"
         data-nation-icons="{{ e(json_encode($nationIcons)) }}"
         data-weapon-type-icons="{{ e(json_encode($weaponTypeIcons)) }}"
         data-element-labels="{{ e(json_encode($elementLabels)) }}"
         data-nation-labels="{{ e(json_encode($nationLabels)) }}"
         data-weapon-type-labels="{{ e(json_encode($weaponTypeLabels)) }}"
         data-etoile-labels="{{ e(json_encode($etoileLabels)) }}"
         data-portrait-preview="{{ e($portraitPhoto ? ($portraitPhoto->source_url ?? asset('storage/'.$portraitPhoto->chemin_photo)) : '') }}"
         data-full-preview="{{ e($fullPhoto ? ($fullPhoto->source_url ?? asset('storage/'.$fullPhoto->chemin_photo)) : '') }}"
         data-icone-preview="{{ e($iconePhoto ? ($iconePhoto->source_url ?? asset('storage/'.$iconePhoto->chemin_photo)) : '') }}"
         data-default-portrait="{{ e($portraitPhoto ? ($portraitPhoto->source_url ?? asset('storage/'.$portraitPhoto->chemin_photo)) : asset('images/placeholder.svg')) }}"
         data-default-icone="{{ e($iconePhoto ? ($iconePhoto->source_url ?? asset('storage/'.$iconePhoto->chemin_photo)) : asset('images/placeholder.svg')) }}"
         data-default-weapon="{{ e(asset('images/placeholder.svg')) }}"
         data-save-main-zone-url="{{ route('admin.personnage.block.main-zone.update', $personnage) }}"
         data-save-armes-url="{{ route('admin.personnage.block.armes.update', $personnage) }}"
         data-save-artefacts-url="{{ route('admin.personnage.block.artefacts.update', $personnage) }}"
         data-save-constellations-url="{{ route('admin.personnage.block.constellations.update', $personnage) }}"
         data-upload-constellation-url="{{ route('admin.personnage.block.constellations.upload', $personnage) }}"
         data-showcase-url="{{ route('personnages.show', $personnage) }}"
         data-csrf="{{ csrf_token() }}"
         class="hidden"></div>

    <div class="flex h-screen overflow-hidden" x-data="personnageEditLayout()" x-init="init()">

         <div x-show="toast.show"
              x-cloak
              x-transition:enter="transition ease-out duration-200"
              x-transition:enter-start="opacity-0 translate-y-1"
              x-transition:enter-end="opacity-100 translate-y-0"
              x-transition:leave="transition ease-in duration-150"
              x-transition:leave-start="opacity-100 translate-y-0"
              x-transition:leave-end="opacity-0 translate-y-1"
              class="fixed bottom-5 right-5 z-[9999] rounded-lg border bg-white px-4 py-2 text-sm font-medium text-black shadow-lg"
              :class="toast.type === 'success'
                  ? 'border-emerald-300 text-emerald-800'
                  : 'border-red-300 text-red-800'"
              x-text="toast.text"></div>

        {{-- ===================== SIDEBAR GAUCHE ===================== --}}
        <div class="shrink-0 flex h-full">
            <aside class="flex flex-col bg-white overflow-y-auto text-black transition-all duration-200"
                   :class="sidebarCollapsed ? 'w-0 border-r-0' : 'w-80 border-r border-slate-300'">

                <div class="flex items-center justify-between px-4 py-3 border-b border-slate-300 bg-slate-100 sticky top-0 z-20 shadow-sm"
                     x-show="!sidebarCollapsed">
                    <h2 class="font-bold text-black text-sm">Édition du personnage</h2>
                    <button @click="saveMainZone()"
                            class="px-3 py-1.5 text-sm font-semibold rounded bg-blue-600 hover:bg-blue-500 text-white focus:outline-none">
                        Sauvegarder
                    </button>
                </div>

                <div class="p-4 space-y-5 text-black text-sm" x-show="!sidebarCollapsed">

                <div>
                          <label class="block text-slate-700 text-xs font-semibold uppercase tracking-wide mb-1">Nom du personnage</label>
                    <input x-model="mainZone.nom_perso" type="text"
                              class="w-full rounded border border-slate-300 bg-white px-3 py-2 text-black placeholder-slate-400 focus:outline-none focus:border-blue-500"
                           placeholder="Nom..." />
                </div>

                <div>
                    <label class="block text-slate-700 text-xs font-semibold uppercase tracking-wide mb-1">Élément</label>
                    <div class="flex items-center gap-2">
                        <img x-effect="$el.src = selectedElementIcon" alt="" class="w-7 h-7 rounded-full border border-slate-300 bg-slate-200 p-1 shadow-inner" />
                        <select x-model="mainZone.fid_element"
                                class="flex-1 rounded border border-slate-300 bg-white px-2 py-2 text-black text-sm focus:outline-none focus:border-blue-500">
                            @foreach($elements as $el)
                                <option value="{{ $el->id_element }}">{{ $el->libelle_element }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-slate-700 text-xs font-semibold uppercase tracking-wide mb-1">Type d'arme</label>
                    <div class="flex items-center gap-2">
                        <img x-effect="$el.src = selectedWeaponTypeIcon" alt="" class="w-7 h-7 rounded-full border border-slate-300 bg-slate-200 p-1 shadow-inner" />
                        <select x-model="mainZone.fid_TArmes"
                                class="flex-1 rounded border border-slate-300 bg-white px-2 py-2 text-black text-sm focus:outline-none focus:border-blue-500">
                            @foreach($typesArme as $ta)
                                <option value="{{ $ta->id_TArmes }}">{{ $ta->libelle_TArme }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div>
                        <label class="block text-slate-700 text-xs font-semibold uppercase tracking-wide mb-1">Rareté</label>
                    <select x-model="mainZone.fid_etoile"
                            class="w-full rounded border border-slate-300 bg-white px-2 py-2 text-black text-sm focus:outline-none focus:border-blue-500">
                        @foreach($etoiles as $et)
                            <option value="{{ $et->id_etoile }}">{{ $et->libelle }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-slate-700 text-xs font-semibold uppercase tracking-wide mb-1">Région / Nation</label>
                    <div class="flex items-center gap-2">
                        <img x-effect="$el.src = selectedNationIcon" alt="" class="w-7 h-7 rounded-full border border-slate-300 bg-slate-200 p-1 shadow-inner" />
                        <select x-model="mainZone.fid_nation"
                                @change="mainZone.fid_nation = String($event.target.value)"
                                class="flex-1 rounded border border-slate-300 bg-white px-2 py-2 text-black text-sm focus:outline-none focus:border-blue-500">
                            @foreach($nations as $nation)
                                <option value="{{ $nation->id_region }}">{{ $nation->nom_region }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <hr class="border-slate-300" />

                <div>
                    <label class="block text-slate-700 text-xs font-semibold uppercase tracking-wide mb-2">Images</label>
                    <div class="space-y-2"
                        @dragover.prevent
                        @drop.prevent="onArmeDropAtEnd($event)">
                        <div class="flex items-center gap-3 rounded border border-slate-300 bg-white px-3 py-2 cursor-pointer hover:bg-slate-50"
                             @click="document.getElementById('portrait-upload').click()">
                            <img :src="portraitPreview" class="w-10 h-10 rounded object-cover border border-slate-300" />
                            <div>
                                <div class="text-sm text-black font-medium">Portrait / Full</div>
                                <div class="text-xs text-slate-600">Cliquer pour téléverser</div>
                            </div>
                        </div>
                        <div class="flex items-center gap-3 rounded border border-slate-300 bg-white px-3 py-2 cursor-pointer hover:bg-slate-50"
                             @click="document.getElementById('icone-upload').click()">
                            <img :src="iconePreview" class="w-10 h-10 rounded-full object-cover border border-slate-300" />
                            <div>
                                <div class="text-sm text-black font-medium">Icône</div>
                                <div class="text-xs text-slate-600">Cliquer pour téléverser</div>
                            </div>
                        </div>
                    </div>
                    <input type="file" id="portrait-upload" class="hidden" accept="image/*" @change="uploadImage($event, 'portrait')">
                    <input type="file" id="icone-upload" class="hidden" accept="image/*" @change="uploadImage($event, 'icone')" />
                </div>

                <hr class="border-slate-300" />

                <div>
                    <label class="block text-slate-700 text-xs font-semibold uppercase tracking-wide mb-2">Vidéos</label>
                    <div class="space-y-3">
                        <template x-for="(video, index) in mainZone.videos" :key="index">
                            <div>
                                <div class="flex items-center justify-between mb-1">
                                    <span class="text-xs text-slate-700 font-medium" x-text="'Vidéo ' + (index + 1)"></span>
                                    <button type="button" @click="removeVideo(index)"
                                            class="text-xs text-red-400 hover:text-red-300">Supprimer</button>
                                </div>
                                <input x-model="video.url_video" type="url"
                                       class="w-full rounded border border-slate-300 bg-white px-2 py-1.5 text-sm text-black placeholder-slate-400 focus:outline-none focus:border-blue-500"
                                       placeholder="https://youtube.com/watch?v=..." />
                            </div>
                        </template>
                        <template x-if="!mainZone.videos.length">
                            <p class="text-xs text-slate-600 italic">Aucune vidéo ajoutée.</p>
                        </template>
                    </div>
                    <button type="button" @click="addVideo()"
                            class="mt-3 w-full rounded border border-dashed border-slate-400 py-2 text-sm text-slate-800 hover:border-emerald-600 hover:text-emerald-700 transition-colors">
                        + Ajouter une vidéo
                    </button>
                </div>

                <hr class="border-slate-300" />

                <div>
                    <div class="flex items-center justify-between mb-2">
                        <label class="block text-slate-700 text-xs font-semibold uppercase tracking-wide">Armes recommandées</label>
                        <span class="text-[11px] text-slate-500" x-text="armes.length + '/6'"></span>
                    </div>

                    <template x-if="armesError">
                        <div class="mb-2 rounded border border-red-300 bg-red-50 px-2 py-1 text-xs text-red-700" x-text="armesError"></div>
                    </template>

                    <div class="space-y-2" @dragover.prevent @drop.prevent="onArmeDropAtEnd($event)">
                        <template x-for="(arme, index) in armes" :key="arme.id_arme + '-' + index">
                            <div>
                                <template x-if="draggedArme && dragArmeIndex !== index && dropArmeIndex === index">
                                    <div class="th-drop-ghost"
                                         @dragover.prevent="dropArmeIndex = normalizeDropArmeIndex(index)"
                                         @drop.prevent="dropArmeIndex = normalizeDropArmeIndex(index); onArmeDrop()">
                                        <div class="th-weapon-card-inner">
                                            <div class="th-grab-handle opacity-60">⋮⋮</div>
                                            <div class="th-weapon-icon-wrap" :class="rarityClass(draggedArme.stars || draggedArme.fid_etoile)">
                                                <img :src="draggedArme.icon" :alt="draggedArme.nom">
                                            </div>

                                            <div class="min-w-0 flex-1">
                                                <div class="text-sm font-semibold text-slate-900 truncate" x-text="draggedArme.nom"></div>
                                                <div class="text-[11px] text-slate-600" x-text="(draggedArme.etoile || '?') + ' - ' + (draggedArme.type || 'Type inconnu')"></div>
                                            </div>

                                            <div class="flex items-center gap-1 opacity-70">
                                                <span class="th-state-dot"></span>
                                                <span class="th-state-dot" :class="draggedArme.is_starter ? 'is-active' : ''"></span>
                                            </div>
                                        </div>

                                        <div class="flex items-center justify-between border-t border-slate-200 px-2 py-1.5 bg-white">
                                            <span class="text-[11px] font-medium rounded px-2 py-1 border"
                                                  :class="draggedArme.is_starter ? 'bg-emerald-100 text-emerald-800 border-emerald-300' : 'bg-slate-100 text-slate-700 border-slate-300'">
                                                Starter
                                            </span>
                                        </div>
                                    </div>
                                </template>

                                <div class="th-weapon-card"
                                     draggable="true"
                                     :class="{
                                        'is-dragging': dragArmeIndex === index,
                                        'is-drop-target': (dropArmeIndex === index || dropArmeIndex === index + 1) && dragArmeIndex !== index
                                     }"
                                     @dragstart="onArmeDragStart(index, $event)"
                                     @dragover.prevent="onArmeDragOver(index, $event)"
                                     @drop.prevent="onArmeDrop(index, $event)"
                                     @dragend="onArmeDragEnd()">
                                    <div class="th-weapon-card-inner">
                                        <button type="button" class="th-grab-handle" title="Glisser pour réordonner" @mousedown.prevent>⋮⋮</button>
                                        <div class="th-weapon-icon-wrap" :class="rarityClass(arme.stars || arme.fid_etoile)">
                                            <img :src="arme.icon" :alt="arme.nom">
                                        </div>

                                        <div class="min-w-0 flex-1">
                                            <div class="text-sm font-semibold text-slate-900 truncate" x-text="arme.nom"></div>
                                            <div class="text-[11px] text-slate-600" x-text="(arme.etoile || '?') + ' - ' + (arme.type || 'Type inconnu')"></div>
                                        </div>

                                        <div class="flex items-center gap-1">
                                            <span class="th-state-dot"></span>
                                            <span class="th-state-dot" :class="arme.is_starter ? 'is-active' : ''"></span>
                                        </div>
                                    </div>

                                    <div class="flex items-center justify-between border-t border-slate-200 px-2 py-1.5 bg-white">
                                        <button type="button"
                                                class="text-[11px] font-medium rounded px-2 py-1 border"
                                                :class="arme.is_starter ? 'bg-emerald-100 text-emerald-800 border-emerald-300' : 'bg-slate-100 text-slate-700 border-slate-300'"
                                                @click="setStarter(index)">
                                            Starter
                                        </button>
                                        <div class="flex items-center gap-1">
                                            <button type="button" class="w-6 h-6 rounded border border-red-300 text-red-600" @click="removeArme(index)">×</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </template>

                        <template x-if="draggedArme && dropArmeIndex === armes.length">
                            <div class="th-drop-ghost"
                                 @dragover.prevent="dropArmeIndex = normalizeDropArmeIndex(armes.length)"
                                 @drop.prevent="dropArmeIndex = normalizeDropArmeIndex(armes.length); onArmeDrop()">
                                <div class="th-weapon-card-inner">
                                    <div class="th-grab-handle opacity-60">⋮⋮</div>
                                    <div class="th-weapon-icon-wrap" :class="rarityClass(draggedArme.stars || draggedArme.fid_etoile)">
                                        <img :src="draggedArme.icon" :alt="draggedArme.nom">
                                    </div>

                                    <div class="min-w-0 flex-1">
                                        <div class="text-sm font-semibold text-slate-900 truncate" x-text="draggedArme.nom"></div>
                                        <div class="text-[11px] text-slate-600" x-text="(draggedArme.etoile || '?') + ' - ' + (draggedArme.type || 'Type inconnu')"></div>
                                    </div>

                                    <div class="flex items-center gap-1 opacity-70">
                                        <span class="th-state-dot"></span>
                                        <span class="th-state-dot" :class="draggedArme.is_starter ? 'is-active' : ''"></span>
                                    </div>
                                </div>

                                <div class="flex items-center justify-between border-t border-slate-200 px-2 py-1.5 bg-white">
                                    <span class="text-[11px] font-medium rounded px-2 py-1 border"
                                          :class="draggedArme.is_starter ? 'bg-emerald-100 text-emerald-800 border-emerald-300' : 'bg-slate-100 text-slate-700 border-slate-300'">
                                        Starter
                                    </span>
                                </div>
                            </div>
                        </template>

                        <template x-if="!armes.length">
                            <p class="text-xs text-slate-600 italic">Aucune arme recommandée.</p>
                        </template>
                    </div>

                    <div class="mt-3">
                        <button type="button" @click="showArmesPicker = !showArmesPicker"
                                class="w-full rounded border border-slate-300 py-2 text-sm text-slate-800 hover:bg-slate-100 transition-colors">
                            + Ajouter
                        </button>
                    </div>
                </div>

                <div class="rounded border border-slate-300 bg-slate-50 p-3">
                    <div class="text-xs font-semibold uppercase tracking-wide text-slate-700 mb-2">Artefacts</div>
                    <p class="text-xs text-slate-600">Section prête. L'éditeur artefacts sera branché dans ce sous-menu.</p>
                </div>

                <hr class="border-slate-300" />

                <div>
                    <div class="flex items-center justify-between mb-2">
                        <label class="block text-slate-700 text-xs font-semibold uppercase tracking-wide">Constellations</label>
                        <button type="button" @click="saveConstellations()"
                                class="rounded border border-slate-300 bg-white px-2 py-1 text-[11px] text-slate-700 hover:bg-slate-100">
                            Sauvegarder constellations
                        </button>
                    </div>

                    <template x-if="constellationsError">
                        <div class="mb-2 rounded border border-red-300 bg-red-50 px-2 py-1 text-xs text-red-700" x-text="constellationsError"></div>
                    </template>

                    <template x-if="constellations.length">
                        <div class="space-y-2">
                            <div class="grid grid-cols-3 gap-1">
                                <template x-for="(constellation, index) in constellations" :key="`sidebar-c-${constellation.id_const || index}`">
                                    <button type="button"
                                            class="rounded border px-2 py-1 text-[11px] font-semibold"
                                            :class="selectedConstellationIndex === index ? 'border-sky-500 bg-sky-100 text-sky-800' : 'border-slate-300 bg-white text-slate-700 hover:bg-slate-50'"
                                            @click="selectedConstellationIndex = index"
                                            x-text="constellation.label || ('C' + (index + 1))"></button>
                                </template>
                            </div>

                            <div class="rounded border border-slate-300 bg-slate-50 p-2 space-y-2" x-show="activeConstellation">
                                <div>
                                    <label class="block text-slate-700 text-[11px] font-semibold mb-1">Nom</label>
                                    <input type="text" x-model="activeConstellation.titre_const"
                                           class="w-full rounded border border-slate-300 bg-white px-2 py-1.5 text-xs text-black" />
                                </div>
                                <div>
                                    <label class="block text-slate-700 text-[11px] font-semibold mb-1">Description</label>
                                    <textarea x-model="activeConstellation.descri_const" rows="4"
                                              class="w-full rounded border border-slate-300 bg-white px-2 py-1.5 text-xs text-black"></textarea>
                                </div>

                                <div class="flex items-center gap-2">
                                    <img :src="activeConstellation.image_url || '{{ asset('images/placeholder.svg') }}'"
                                         class="w-11 h-11 rounded object-cover border border-slate-300" alt="">
                                    <button type="button"
                                            class="rounded border border-slate-300 bg-white px-2 py-1 text-xs text-slate-700 hover:bg-slate-100"
                                            @click="document.getElementById('constellation-upload-' + (selectedConstellationIndex + 1)).click()">
                                        Upload image
                                    </button>
                                </div>

                                <template x-for="(constellation, index) in constellations" :key="`upload-c-${constellation.id_const || index}`">
                                    <input type="file"
                                           class="hidden"
                                           accept="image/*"
                                           :id="'constellation-upload-' + (index + 1)"
                                           @change="uploadConstellationImage($event, index)" />
                                </template>
                            </div>
                        </div>
                    </template>

                    <template x-if="!constellations.length">
                        <p class="text-xs text-slate-600 italic">Aucune constellation disponible.</p>
                    </template>
                </div>

                <hr class="border-slate-300" />

                <div id="constellation-map" class="th-const-map-shell">
                    <div class="mb-2 flex items-center justify-between gap-2">
                        <div>
                            <div class="text-xs font-semibold uppercase tracking-wide text-slate-700">Carte constellation</div>
                            <div class="text-[11px] text-slate-600">Image de fond + placement C1 a C6</div>
                        </div>
                    </div>

                    <form method="POST"
                          action="{{ route('admin.personnages.update', $personnage) }}"
                          enctype="multipart/form-data"
                          class="space-y-2">
                        @csrf
                        @method('PUT')

                        <input type="hidden" name="nom_perso" :value="mainZone.nom_perso">
                        <input type="hidden" name="fid_element" :value="mainZone.fid_element">
                        <input type="hidden" name="fid_etoile" :value="mainZone.fid_etoile">
                        <input type="hidden" name="fid_TArmes" :value="mainZone.fid_TArmes">
                        <input type="hidden" name="fid_TP" :value="mainZone.fid_TP">
                        <input type="hidden" name="positions_const" :value="constellationMapPositionsJson">

                        <div class="th-const-map-canvas"
                             x-ref="constellationMapCanvas"
                             @click="onConstellationMapClick($event)">
                            <img :src="constellationMapImage || '{{ asset('images/placeholder.svg') }}'" alt="Carte constellation">

                            <template x-for="index in [1,2,3,4,5,6]" :key="`map-point-${index}`">
                                <template x-if="constellationMapPositions[String(index)]">
                                    <button type="button"
                                            class="th-const-map-point"
                                            :class="selectedMapPoint === index ? 'is-selected' : ''"
                                            :style="mapPointStyle(index)"
                                            @click.stop="selectedMapPoint = index">
                                        <span x-text="index"></span>
                                        <span class="th-const-map-remove" @click.stop="clearMapPoint(index)">x</span>
                                    </button>
                                </template>
                            </template>
                        </div>

                        <div class="rounded border border-slate-200 bg-white px-2 py-1 text-[11px] text-slate-700">
                            Prochain point a placer : <span class="font-semibold" x-text="nextMapPointLabel"></span>
                        </div>

                        <div class="space-y-1">
                            <label class="block text-[11px] font-semibold text-slate-700">Image URL</label>
                            <input type="url"
                                   name="constellation_map_image_url"
                                   x-model="constellationMapImageUrlInput"
                                   @input="applyConstellationMapImageUrl()"
                                   class="w-full rounded border border-slate-300 bg-white px-2 py-1.5 text-xs text-black"
                                   placeholder="https://..." />
                        </div>

                        <div class="space-y-1">
                            <label class="block text-[11px] font-semibold text-slate-700">Uploader image de fond</label>
                            <input type="file"
                                   name="constellation_map_image"
                                   accept="image/*"
                                   @change="previewConstellationMapImage($event)"
                                   class="w-full rounded border border-slate-300 bg-white px-2 py-1 text-xs text-black" />
                        </div>

                        <div class="space-y-1">
                            <label class="block text-[11px] font-semibold text-slate-700">JSON (lecture seule)</label>
                            <textarea readonly rows="5"
                                      class="w-full rounded border border-slate-300 bg-slate-100 px-2 py-1 text-[11px] text-slate-700"
                                      :value="constellationMapPositionsPretty"></textarea>
                        </div>

                        <button type="submit"
                                class="w-full rounded border border-blue-600 bg-blue-600 px-2 py-1.5 text-xs font-semibold text-white hover:bg-blue-500">
                            Enregistrer carte constellation
                        </button>
                    </form>
                </div>

                </div>
            </aside>

            <div class="w-0 relative shrink-0">
                <button type="button"
                        class="absolute left-0 top-3 z-20 flex h-10 w-7 -translate-x-px items-center justify-center rounded-r-md border border-l-0 border-slate-300 bg-white text-sm font-bold text-slate-600 shadow-sm transition-colors hover:bg-slate-100"
                        @click="sidebarCollapsed = !sidebarCollapsed"
                        :title="sidebarCollapsed ? 'Ouvrir la sidebar' : 'Fermer la sidebar'">
                    <span x-text="sidebarCollapsed ? '>' : '<'"></span>
                </button>
            </div>
        </div>

        {{-- ===================== ZONE PREVIEW DROITE ===================== --}}
        <div class="flex-1 overflow-y-auto bg-slate-950">
            <div class="px-6 pt-4 pb-1 text-xs text-slate-500 text-right italic">
                Aperçu en temps réel — cliquez sur Sauvegarder pour enregistrer et voir la page publique
            </div>

            <div class="character-show-hero mx-6 mb-6" data-element="{{ strtolower($personnage->element?->libelle_element ?? 'geo') }}">

                <section class="csh-full relative flex items-center justify-center text-center p-4">
                    <div class="absolute inset-0 bg-[radial-gradient(circle_at_center,rgba(255,255,255,0.05),rgba(0,0,0,0.55))]"></div>
                    <template x-if="activeEmbedUrl">
                        <iframe :src="activeEmbedUrl"
                                frameborder="0"
                                allowfullscreen
                                class="absolute inset-0 z-10 w-full h-full rounded-[16px]"></iframe>
                    </template>
                    <template x-if="!activeEmbedUrl">
                        <div class="z-10 text-white/60 text-sm">Aucune vidéo</div>
                    </template>
                </section>

                <section class="csh-portrait overflow-hidden rounded-lg border border-white/20">
                    <img :src="portraitPreview" alt="Portrait" class="w-full h-full object-cover" />
                </section>

                <div class="csh-hero">
                    <div class="csh-hero-head">
                        <div class="csh-icon">
                            <div class="csh-icon-img">
                                <img :src="iconePreview" alt="Icône" />
                            </div>
                        </div>
                        <div class="csh-name" x-text="mainZone.nom_perso || '{{ $personnage->nom_perso }}'"></div>
                    </div>
                </div>

                <div class="csh-meta">
                    <div class="csh-pill csh-pill--element">
                        <span class="csh-pill-label">Élément</span>
                        <div class="flex items-center gap-2">
                            <img :src="selectedElementIcon" alt="" class="w-5 h-5 rounded-full" />
                            <span class="csh-pill-value" x-text="selectedElementLabel"></span>
                        </div>
                    </div>
                    <div class="csh-pill">
                        <span class="csh-pill-label">Arme</span>
                        <div class="flex items-center gap-2">
                            <img :src="selectedWeaponTypeIcon" alt="" class="w-5 h-5 rounded-full" />
                            <span class="csh-pill-value" x-text="selectedWeaponTypeLabel"></span>
                        </div>
                    </div>
                    <div class="csh-pill">
                        <span class="csh-pill-label">Rareté</span>
                        <span class="csh-pill-value" x-text="selectedEtoileLabel"></span>
                    </div>
                    <div class="csh-pill">
                        <span class="csh-pill-label">Nation</span>
                        <div class="flex items-center gap-2">
                            <img :src="selectedNationIcon" alt="" class="w-5 h-5 rounded-full" />
                            <span class="csh-pill-value" x-text="selectedNationLabel"></span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mx-6 -mt-2 mb-6 flex justify-end" x-show="mainZone.videos.length > 1">
                <div class="flex items-center gap-2 rounded bg-slate-900/80 border border-slate-700 px-2 py-1 text-xs text-white">
                    <span>Vidéo <span x-text="selectedVideoIndex + 1"></span>/<span x-text="mainZone.videos.length"></span></span>
                    <button type="button" @click="prevVideo()" class="rounded bg-slate-700 px-2 py-0.5 hover:bg-slate-600">◀</button>
                    <button type="button" @click="nextVideo()" class="rounded bg-slate-700 px-2 py-0.5 hover:bg-slate-600">▶</button>
                </div>
            </div>

            <section class="csh-preview-table mx-6">
                <div class="csh-preview-panel">
                    <div class="csh-preview-panel-head">
                        <div>
                            <div class="csh-preview-panel-title">Armes</div>
                            <div class="csh-preview-panel-subtitle">Colonne gauche du tableau preview</div>
                        </div>
                        <div class="text-xs text-slate-400" x-text="armes.length ? `${armes.length} arme(s)` : 'Aucune arme'"></div>
                    </div>

                    <template x-if="armes.length">
                        <div class="csh-preview-weapon-list">
                            <template x-for="(arme, index) in armes" :key="`preview-${arme.id_arme}-${index}`">
                                <article class="csh-weapon-item">
                                    <div class="csh-weapon-index" x-text="index + 1"></div>
                                    <div class="csh-weapon-icon-wrap" :class="rarityClass(arme.stars || arme.fid_etoile)">
                                        <img :src="arme.icon" :alt="arme.nom">
                                    </div>
                                    <div class="csh-weapon-copy">
                                        <div class="csh-weapon-name truncate" x-text="arme.nom"></div>
                                        <div class="csh-weapon-type" x-text="(arme.etoile || '?') + ' · ' + (arme.type || 'Type inconnu')"></div>
                                        <template x-if="arme.is_starter">
                                            <div class="csh-weapon-badge">Starter</div>
                                        </template>
                                    </div>
                                </article>
                            </template>
                        </div>
                    </template>

                    <template x-if="!armes.length">
                        <div class="csh-weapon-empty">Aucune arme recommandée pour le moment.</div>
                    </template>
                </div>

                <div class="csh-preview-panel">
                    <div class="csh-preview-panel-head">
                        <div>
                            <div class="csh-preview-panel-title">Artefacts</div>
                            <div class="csh-preview-panel-subtitle">Colonne droite du tableau preview</div>
                        </div>
                        <div class="text-xs text-slate-400" x-text="artefactBuilds.length ? `${artefactBuilds.length} build(s)` : 'Aucun build'"></div>
                    </div>

                    <template x-if="artefactBuilds.length">
                        <div class="csh-preview-artefact-list">
                            <template x-for="(build, index) in artefactBuilds" :key="`preview-build-${build.id_build || index}`">
                                <article class="csh-artefact-item">
                                    <div class="csh-artefact-head">
                                        <div class="csh-artefact-title" x-text="`Build ${index + 1}`"></div>
                                        <div class="csh-artefact-piece" x-text="build.artefact2_nom ? '2P + 2P' : `${build.pieces_1}P`"></div>
                                    </div>
                                    <div class="csh-artefact-row">
                                        <span class="csh-artefact-name" x-text="build.artefact1_nom || 'Artefact principal'"></span>
                                        <span class="csh-artefact-piece" x-text="`${build.pieces_1}P`"></span>
                                    </div>
                                    <template x-if="build.artefact2_nom">
                                        <div class="csh-artefact-row">
                                            <span class="csh-artefact-name" x-text="build.artefact2_nom"></span>
                                            <span class="csh-artefact-piece" x-text="`${build.pieces_2}P`"></span>
                                        </div>
                                    </template>
                                </article>
                            </template>
                        </div>
                    </template>

                    <template x-if="!artefactBuilds.length">
                        <div class="csh-artefact-empty">Aucun artefact recommandé pour le moment.</div>
                    </template>
                </div>
            </section>

            <section class="csh-constellation-shell mx-6">
                <div class="csh-preview-panel-head">
                    <div>
                        <div class="csh-preview-panel-title">Constellations</div>
                        <div class="csh-preview-panel-subtitle">Image à gauche, détails cliquables à droite</div>
                    </div>
                    <div class="text-xs text-slate-400" x-text="constellations.length ? `${constellations.length} constellation(s)` : 'Aucune constellation'"></div>
                </div>

                <div class="csh-constellation-grid">
                    <div class="csh-constellation-media">
                        <div class="csh-constellation-frame">
                            <template x-if="activeConstellation && activeConstellation.image_url">
                                <img :src="activeConstellation.image_url" :alt="activeConstellation.titre_const || 'Constellation'">
                            </template>
                            <template x-if="!activeConstellation">
                                <div class="csh-constellation-empty-media">Aucune constellation sélectionnée.</div>
                            </template>
                        </div>
                    </div>

                    <div class="csh-constellation-content">
                        <template x-if="constellations.length">
                            <div class="csh-constellation-tabs">
                                <template x-for="(constellation, index) in constellations" :key="`preview-c-${constellation.id_const || index}`">
                                    <button type="button"
                                            class="csh-constellation-tab"
                                            :class="selectedConstellationIndex === index ? 'is-active' : ''"
                                            @click="selectedConstellationIndex = index">
                                        <div x-text="constellation.label || ('C' + (index + 1))"></div>
                                        <div class="truncate text-[11px] opacity-80" x-text="constellation.titre_const || 'Sans titre'"></div>
                                    </button>
                                </template>
                            </div>
                        </template>

                        <template x-if="activeConstellation">
                            <div class="csh-constellation-detail">
                                <div class="csh-constellation-title" x-text="activeConstellation.titre_const || 'Constellation sans nom'"></div>
                                <div class="csh-constellation-desc" x-text="activeConstellation.descri_const || 'Aucune description.'"></div>
                            </div>
                        </template>

                        <template x-if="!constellations.length">
                            <div class="csh-artefact-empty">Aucune constellation disponible pour ce personnage.</div>
                        </template>
                    </div>
                </div>
            </section>
        </div>

        {{-- ===================== MODAL AJOUT ARMES ===================== --}}
        <div x-show="showArmesPicker" x-cloak @click.outside="showArmesPicker = false"
             class="th-armes-picker-modal"
             :style="`left: ${ (window.innerWidth * 0.33) + 100 }px; top: 150px;`">

            <div class="sticky top-0 border-b border-slate-300 bg-white p-3 space-y-2">
                <div class="flex items-center justify-between">
                    <h3 class="font-semibold text-slate-900 text-sm">Sélectionner une arme</h3>
                    <button type="button" @click="showArmesPicker = false" class="text-2xl text-slate-500 hover:text-slate-700">×</button>
                </div>
                <div class="flex items-center gap-2">
                    <select x-model="weaponRarityFilter" class="flex-1 rounded border border-slate-300 bg-white px-2 py-1.5 text-xs text-black">
                        <option value="">Rareté: toutes</option>
                        <option value="1">1★</option>
                        <option value="2">2★</option>
                        <option value="3">3★</option>
                        <option value="4">4★</option>
                        <option value="5">5★</option>
                    </select>
                    <button type="button" @click="weaponRarityFilter = ''" class="rounded border border-slate-300 px-2 py-1.5 text-xs text-slate-700 hover:bg-slate-100">
                        Reset
                    </button>
                </div>
            </div>

            <template x-if="!filteredAvailableArmes.length">
                <div class="p-4 text-center text-xs text-slate-500">
                    Aucune arme disponible pour ce type/filtre.
                </div>
            </template>

            <template x-if="filteredAvailableArmes.length">
                <div class="th-armes-picker-grid">
                    <template x-for="arme in filteredAvailableArmes" :key="'picker-' + arme.id">
                        <button type="button" @click="addArme(arme)"
                                class="th-armes-picker-item">
                            <div class="th-armes-picker-icon">
                                <div class="th-weapon-icon-wrap" :class="rarityClass(arme.stars || arme.fid_etoile)">
                                    <img :src="arme.icon" :alt="arme.nom">
                                </div>
                            </div>
                            <div class="name" x-text="arme.nom"></div>
                            <div class="rarity" x-text="arme.etoile || '?'"></div>
                        </button>
                    </template>
                </div>
            </template>
        </div>

    </div>

    <script>
        function personnageEditLayout() {
            const config = document.getElementById('personnage-editor-config');
            const data = config?.dataset || {};

            const safeJsonParse = (raw, fallback) => {
                if (!raw || raw === '') return fallback;
                const str = String(raw).trim();
                const unescapeHtml = v => v.replace(/&quot;/g,'"').replace(/&#34;/g,'"').replace(/&#039;/g,"'").replace(/&#39;/g,"'").replace(/&amp;/g,'&');
                const tryParse = input => { try { return JSON.parse(input); } catch(e) { return null; } };
                const parsed = tryParse(str) || tryParse(unescapeHtml(str));
                return parsed !== null ? parsed : fallback;
            };

            const parsedMain    = safeJsonParse(data.mainZone, {});
            const availableArmes = safeJsonParse(data.availableArmes, []);
            const existingArmes  = safeJsonParse(data.existingArmes, []);
            const existingArtefacts = safeJsonParse(data.existingArtefacts, []);
            const existingConstellations = safeJsonParse(data.constellations, []);
            const existingConstellationMapPositions = safeJsonParse(data.constMapPositions, {});
            const elementIcons   = safeJsonParse(data.elementIcons, {});
            const nationIcons    = safeJsonParse(data.nationIcons, {});
            const weaponTypeIcons = safeJsonParse(data.weaponTypeIcons, {});
            const elementLabels   = safeJsonParse(data.elementLabels, {});
            const nationLabels    = safeJsonParse(data.nationLabels, {});
            const weaponTypeLabels = safeJsonParse(data.weaponTypeLabels, {});
            const etoileLabels    = safeJsonParse(data.etoileLabels, {});
            const defaultPortrait = data.defaultPortrait || '{{ asset("images/placeholder.svg") }}';
            const defaultIcone    = data.defaultIcone    || '{{ asset("images/placeholder.svg") }}';
            const defaultWeapon   = data.defaultWeapon   || '{{ asset("images/placeholder.svg") }}';

            return {
                mainZone: {
                    nom_perso:  parsedMain.nom_perso  || data.nomPerso || '',
                    fid_element:parsedMain.fid_element|| data.fidElement || '',
                    fid_etoile: parsedMain.fid_etoile || data.fidEtoile || '',
                    fid_TArmes: parsedMain.fid_TArmes || data.fidTarmes || '',
                    fid_TP:     parsedMain.fid_TP     || data.fidTp || '',
                    fid_nation: parsedMain.fid_nation || data.fidNation || '',
                    arme_icon:  parsedMain.arme_icon  || data.armeIcon || '',
                    videos:     parsedMain.videos     || [],
                },
                portraitPreview: data.portraitPreview || defaultPortrait,
                fullPreview:     data.fullPreview     || defaultPortrait,
                iconePreview:    data.iconePreview    || defaultIcone,
                weaponToAdd: '',
                armes:           existingArmes,
                artefactBuilds:  existingArtefacts,
                constellations:  existingConstellations,
                selectedConstellationIndex: 0,
                constellationMapPositions: existingConstellationMapPositions,
                selectedMapPoint: null,
                constellationMapImage: data.constMapImage || '{{ asset("images/placeholder.svg") }}',
                constellationMapImageUrlInput: '',
                sidebarCollapsed: false,
                availableArmes:  availableArmes,
                showArmesPicker: false,
                weaponRarityFilter: '',
                armesError: '',
                constellationsError: '',
                elementIcons,
                nationIcons,
                weaponTypeIcons,
                elementLabels,
                nationLabels,
                weaponTypeLabels,
                etoileLabels,
                toast: {
                    show: false,
                    text: '',
                    type: 'success',
                },
                toastTimer: null,
                selectedVideoIndex: 0,
                dragArmeIndex: null,
                dropArmeIndex: null,

                get draggedArme() {
                    return this.dragArmeIndex === null ? null : (this.armes[this.dragArmeIndex] || null);
                },
                get activeConstellation() {
                    if (!this.constellations.length) return null;
                    const idx = Math.max(0, Math.min(this.selectedConstellationIndex, this.constellations.length - 1));
                    return this.constellations[idx] || null;
                },
                get nextMapPointLabel() {
                    if (this.selectedMapPoint !== null) {
                        return `C${this.selectedMapPoint} (repositionnement)`;
                    }
                    for (let i = 1; i <= 6; i += 1) {
                        if (!this.constellationMapPositions[String(i)]) {
                            return `C${i}`;
                        }
                    }
                    return 'Tous les points places';
                },
                get constellationMapPositionsJson() {
                    const normalized = {};
                    for (let i = 1; i <= 6; i += 1) {
                        const key = String(i);
                        const point = this.constellationMapPositions[key];
                        if (!point) continue;
                        normalized[key] = {
                            x: this.roundPercent(point.x),
                            y: this.roundPercent(point.y),
                        };
                    }
                    return JSON.stringify(normalized);
                },
                get constellationMapPositionsPretty() {
                    const json = this.constellationMapPositionsJson;
                    try {
                        return JSON.stringify(JSON.parse(json), null, 2);
                    } catch (e) {
                        return '{}';
                    }
                },
                get selectedElementIcon()    { return this.elementIcons[this.mainZone.fid_element]    || defaultWeapon; },
                get selectedNationIcon() {
                    const key = String(this.mainZone.fid_nation || '');
                    return this.nationIcons[key] || this.nationIcons[Number(key)] || defaultWeapon;
                },
                get selectedElementLabel()   { return this.elementLabels[this.mainZone.fid_element]     || ''; },
                get selectedNationLabel() {
                    const key = String(this.mainZone.fid_nation || '');
                    return this.nationLabels[key] || this.nationLabels[Number(key)] || '';
                },
                get selectedWeaponTypeLabel(){ return this.weaponTypeLabels[this.mainZone.fid_TArmes]   || ''; },
                get selectedEtoileLabel()    { return this.etoileLabels[this.mainZone.fid_etoile]       || ''; },
                get selectedWeaponTypeIcon() {
                    // Toujours afficher l'icone du type selectionne (Sword/Bow/etc.)
                    return this.weaponTypeIcons[this.mainZone.fid_TArmes] || defaultWeapon;
                },
                get filteredAvailableArmes() {
                    const selectedType = String(this.mainZone.fid_TArmes || '');
                    const used = new Set(this.armes.map(a => Number(a.id_arme)));

                    return this.availableArmes.filter(a => {
                        if (selectedType && String(a.fid_TArmes || '') !== selectedType) return false;
                        if (this.weaponRarityFilter && String(a.stars || '') !== this.weaponRarityFilter) return false;
                        if (used.has(Number(a.id))) return false;
                        return true;
                    });
                },
                get activeEmbedUrl() {
                    const vid = this.mainZone.videos[this.selectedVideoIndex]?.url_video || '';
                    if (!vid) return '';
                    const m = vid.match(/(?:youtu\.be\/|youtube\.com\/(?:watch\?v=|embed\/|v\/))([A-Za-z0-9_-]{11})/);
                    if (m) return 'https://www.youtube.com/embed/' + m[1];
                    return vid.startsWith('http') ? vid : '';
                },
                nextVideo() {
                    if (!this.mainZone.videos.length) return;
                    this.selectedVideoIndex = (this.selectedVideoIndex + 1) % this.mainZone.videos.length;
                },
                prevVideo() {
                    if (!this.mainZone.videos.length) return;
                    this.selectedVideoIndex = (this.selectedVideoIndex - 1 + this.mainZone.videos.length) % this.mainZone.videos.length;
                },
                init() {},
                showToast(text, type = 'success') {
                    if (this.toastTimer) {
                        clearTimeout(this.toastTimer);
                    }
                    this.toast.text = text;
                    this.toast.type = type;
                    this.toast.show = true;
                    this.toastTimer = setTimeout(() => {
                        this.toast.show = false;
                    }, 2400);
                },
                rarityClass(stars) {
                    const s = Number(stars) || 1;
                    return `th-weapon-rarity-${Math.min(5, Math.max(1, s))}`;
                },
                normalizeStarterPosition() {
                    const starter = this.armes.find(a => a.is_starter);
                    const others = this.armes.filter(a => !a.is_starter);
                    this.armes = starter ? [...others, starter] : [...others];
                },
                setStarter(index) {
                    const wasStarter = Boolean(this.armes[index]?.is_starter);

                    if (wasStarter) {
                        // Re-clic: retire l'etat starter.
                        this.armes = this.armes.map(a => ({ ...a, is_starter: false }));
                        this.armesError = '';
                        return;
                    }

                    this.armes = this.armes.map((a, i) => ({ ...a, is_starter: i === index }));
                    this.normalizeStarterPosition();
                    this.armesError = '';
                },
                addArme(arme) {
                    if (this.armes.length >= 6) return;

                    this.armes.push({
                        id_arme: arme.id,
                        nom: arme.nom,
                        slug: arme.slug,
                        etoile: arme.etoile,
                        stars: arme.stars,
                        fid_etoile: arme.fid_etoile,
                        fid_TArmes: arme.fid_TArmes,
                        type: arme.type,
                        icon: arme.icon,
                        is_starter: false,
                        origine: null,
                    });
                },
                removeArme(index) {
                    this.armes.splice(index, 1);
                },
                roundPercent(value) {
                    const num = Number(value);
                    if (Number.isNaN(num)) return 0;
                    return Math.round(Math.max(0, Math.min(100, num)) * 10) / 10;
                },
                mapPointStyle(index) {
                    const key = String(index);
                    const point = this.constellationMapPositions[key];
                    const x = this.roundPercent(point?.x ?? 0);
                    const y = this.roundPercent(point?.y ?? 0);
                    return `left:${x}%;top:${y}%;`;
                },
                nextMapPointIndex() {
                    for (let i = 1; i <= 6; i += 1) {
                        if (!this.constellationMapPositions[String(i)]) {
                            return i;
                        }
                    }
                    return null;
                },
                onConstellationMapClick(event) {
                    const canvas = this.$refs.constellationMapCanvas;
                    if (!canvas) return;

                    const rect = canvas.getBoundingClientRect();
                    if (rect.width <= 0 || rect.height <= 0) return;

                    const x = this.roundPercent(((event.clientX - rect.left) / rect.width) * 100);
                    const y = this.roundPercent(((event.clientY - rect.top) / rect.height) * 100);

                    const targetIndex = this.selectedMapPoint ?? this.nextMapPointIndex();
                    if (!targetIndex) return;

                    this.constellationMapPositions[String(targetIndex)] = { x, y };
                    this.selectedMapPoint = null;
                },
                clearMapPoint(index) {
                    delete this.constellationMapPositions[String(index)];
                    if (this.selectedMapPoint === index) {
                        this.selectedMapPoint = null;
                    }
                    this.constellationMapPositions = { ...this.constellationMapPositions };
                },
                applyConstellationMapImageUrl() {
                    const value = String(this.constellationMapImageUrlInput || '').trim();
                    if (!value) return;
                    this.constellationMapImage = value;
                },
                previewConstellationMapImage(event) {
                    const file = event.target.files?.[0];
                    if (!file) return;
                    this.constellationMapImage = URL.createObjectURL(file);
                },
                normalizeDropArmeIndex(index) {
                    let nextIndex = Math.max(0, Math.min(index, this.armes.length));

                    if (this.dragArmeIndex === null) {
                        return nextIndex;
                    }

                    const dragged = this.armes[this.dragArmeIndex] || null;
                    if (!dragged) {
                        return nextIndex;
                    }

                    if (dragged.is_starter) {
                        return this.armes.length;
                    }

                    const starterIndex = this.armes.findIndex((arme, idx) => arme.is_starter && idx !== this.dragArmeIndex);
                    if (starterIndex !== -1) {
                        nextIndex = Math.min(nextIndex, starterIndex);
                    }

                    return nextIndex;
                },
                onArmeDragStart(index, event) {
                    this.dragArmeIndex = index;
                    this.dropArmeIndex = this.normalizeDropArmeIndex(index);
                    if (event.dataTransfer) {
                        event.dataTransfer.effectAllowed = 'move';
                        event.dataTransfer.setData('text/plain', String(index));
                    }
                },
                onArmeDragOver(index, event) {
                    if (event.dataTransfer) {
                        event.dataTransfer.dropEffect = 'move';
                    }
                    const rect = event.currentTarget.getBoundingClientRect();
                    const isAfter = event.clientY > (rect.top + rect.height / 2);
                    this.dropArmeIndex = this.normalizeDropArmeIndex(index + (isAfter ? 1 : 0));
                },
                onArmeDrop() {
                    if (this.dragArmeIndex === null || this.dropArmeIndex === null) {
                        this.onArmeDragEnd();
                        return;
                    }

                    const moved = this.armes.splice(this.dragArmeIndex, 1)[0];
                    let targetIndex = this.dropArmeIndex;
                    if (this.dragArmeIndex < targetIndex) {
                        targetIndex -= 1;
                    }
                    if (moved?.is_starter) {
                        targetIndex = this.armes.length;
                    } else {
                        const starterIndex = this.armes.findIndex(arme => arme.is_starter);
                        if (starterIndex !== -1) {
                            targetIndex = Math.min(targetIndex, starterIndex);
                        }
                    }
                    targetIndex = Math.max(0, Math.min(targetIndex, this.armes.length));
                    this.armes.splice(targetIndex, 0, moved);
                    this.onArmeDragEnd();
                },
                onArmeDropAtEnd() {
                    if (this.dragArmeIndex === null) {
                        this.onArmeDragEnd();
                        return;
                    }

                    this.dropArmeIndex = this.normalizeDropArmeIndex(this.armes.length);
                    this.onArmeDrop();
                },
                onArmeDragEnd() {
                    this.dragArmeIndex = null;
                    this.dropArmeIndex = null;
                },
                async saveArmes({ strict = true } = {}) {
                    if (!this.armes.length) {
                        const msg = 'Ajoute au moins une arme recommandée.';
                        this.armesError = msg;
                        if (strict) throw new Error(msg);
                        return { saved: false, reason: msg };
                    }

                    if (!this.armes.some(a => a.is_starter)) {
                        const msg = 'Une arme starter est obligatoire.';
                        this.armesError = msg;
                        if (strict) throw new Error(msg);
                        return { saved: false, reason: msg };
                    }

                    this.normalizeStarterPosition();

                    const payload = this.armes.map((a, index) => ({
                        id_arme: Number(a.id_arme),
                        rang: Number(a.stars || 1),
                        is_starter: Boolean(a.is_starter),
                        origine: a.origine || null,
                        position: index + 1,
                    }));

                    const resp = await fetch(data.saveArmesUrl, {
                        method: 'PUT',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': data.csrf },
                        body: JSON.stringify({ armes: payload }),
                    });

                    if (!resp.ok) {
                        let msg = 'Erreur sauvegarde armes';
                        try {
                            const j = await resp.json();
                            const firstKey = Object.keys(j?.errors || {})[0];
                            if (firstKey && j.errors[firstKey]?.[0]) {
                                msg = j.errors[firstKey][0];
                            }
                        } catch (e) {
                            // Ignore parse error and keep generic message.
                        }
                        this.armesError = msg;
                        if (strict) throw new Error(msg);
                        return { saved: false, reason: msg };
                    }

                    this.armesError = '';
                    return { saved: true, reason: null };
                },
                async saveMainZone() {
                    try {
                        const videosPayload = this.mainZone.videos
                            .filter(v => (v?.url_video || '').trim() !== '')
                            .map(v => ({ url_video: String(v.url_video).trim() }));

                        const resp = await fetch(data.saveMainZoneUrl, {
                            method: 'PUT',
                            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': data.csrf },
                            body: JSON.stringify({
                                nom_perso:   this.mainZone.nom_perso,
                                fid_element: this.mainZone.fid_element,
                                fid_etoile:  this.mainZone.fid_etoile,
                                fid_TArmes:  this.mainZone.fid_TArmes,
                                fid_TP:      this.mainZone.fid_TP,
                                fid_nations: this.mainZone.fid_nation ? [this.mainZone.fid_nation] : [],
                                videos:      videosPayload,
                            }),
                        });
                        if (!resp.ok) {
                            let msg = 'Erreur sauvegarde zone principale';
                            try {
                                const j = await resp.json();
                                const firstKey = Object.keys(j?.errors || {})[0];
                                if (firstKey && j.errors[firstKey]?.[0]) {
                                    msg = j.errors[firstKey][0];
                                }
                            } catch (e) {
                                // Ignore parse error and keep generic message.
                            }
                            throw new Error(msg);
                        }

                        const armesResult = await this.saveArmes({ strict: false });

                        if (armesResult.saved) {
                            this.showToast('Modifications sauvegardées', 'success');
                        } else {
                            this.showToast('Zone principale sauvegardée (armes non enregistrées)', 'error');
                        }
                    } catch (e) {
                        this.showToast(e?.message || 'Erreur pendant la sauvegarde', 'error');
                    }
                },
                addVideo() { if (this.mainZone.videos.length < 5) this.mainZone.videos.push({ url_video: '' }); },
                removeVideo(i) {
                    this.mainZone.videos.splice(i, 1);
                    if (this.selectedVideoIndex >= this.mainZone.videos.length) {
                        this.selectedVideoIndex = Math.max(0, this.mainZone.videos.length - 1);
                    }
                },
                async saveConstellations() {
                    if (!this.constellations.length) return;

                    try {
                        const payload = this.constellations.map(c => ({
                            id_const: Number(c.id_const),
                            titre_const: String(c.titre_const || '').trim(),
                            descri_const: c.descri_const || '',
                        }));

                        const resp = await fetch(data.saveConstellationsUrl, {
                            method: 'PUT',
                            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': data.csrf },
                            body: JSON.stringify({ constellations: payload }),
                        });

                        if (!resp.ok) {
                            let msg = 'Erreur sauvegarde constellations';
                            try {
                                const j = await resp.json();
                                const firstKey = Object.keys(j?.errors || {})[0];
                                if (firstKey && j.errors[firstKey]?.[0]) {
                                    msg = j.errors[firstKey][0];
                                }
                            } catch (e) {
                                // Keep fallback message.
                            }
                            this.constellationsError = msg;
                            this.showToast(msg, 'error');
                            return;
                        }

                        this.constellationsError = '';
                        this.showToast('Constellations sauvegardées', 'success');
                    } catch (e) {
                        this.constellationsError = e?.message || 'Erreur sauvegarde constellations';
                        this.showToast(this.constellationsError, 'error');
                    }
                },
                async uploadConstellationImage(event, index) {
                    const file = event.target.files?.[0];
                    if (!file) return;

                    const form = new FormData();
                    form.append('image', file);
                    form.append('constellation_index', String(index + 1));

                    try {
                        const resp = await fetch(data.uploadConstellationUrl, {
                            method: 'POST',
                            headers: { 'X-CSRF-TOKEN': data.csrf },
                            body: form,
                        });

                        if (!resp.ok) {
                            this.showToast('Erreur upload image constellation', 'error');
                            return;
                        }

                        const j = await resp.json();
                        const t = Date.now();
                        if (this.constellations[index]) {
                            this.constellations[index].image_url = `${j.url}?t=${t}`;
                        }
                        this.showToast(`Image C${index + 1} mise à jour`, 'success');
                    } catch (e) {
                        this.showToast('Erreur upload image constellation', 'error');
                    } finally {
                        event.target.value = '';
                    }
                },
                async uploadImage(event, type) {
                    const file = event.target.files[0];
                    if (!file) return;
                    const form = new FormData();
                    form.append('image_type', type);
                    form.append('image', file);
                    const resp = await fetch(data.saveMainZoneUrl.replace('main-zone', 'main-zone/upload-image'), {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': data.csrf },
                        body: form,
                    });
                    if (!resp.ok) { this.showToast('Erreur upload image', 'error'); return; }
                    const j = await resp.json();
                    const t = Date.now();
                    if (type === 'portrait') { this.portraitPreview = `${j.url}?t=${t}`; this.fullPreview = `${j.url}?t=${t}`; }
                    if (type === 'full')     { this.fullPreview = `${j.url}?t=${t}`; }
                    if (type === 'icone')    { this.iconePreview = j.url; }
                },
            };
        }
    </script>


</x-admin-layout>
