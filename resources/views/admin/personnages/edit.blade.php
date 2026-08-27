<x-admin-layout>
    @php $isFreshCreate = request()->boolean('fresh'); @endphp
    <x-slot name="title">{{ $isFreshCreate ? 'Ajouter un personnage — Admin' : 'Modifier '.$personnage->nom_perso.' — Admin' }}</x-slot>

    <div class="hidden" aria-hidden="true">
        <input type="text" name="nom_perso" value="{{ $isFreshCreate ? '' : $personnage->nom_perso }}" />
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
            justify-self:center;
            margin-inline:auto;
        }
        .csh-portrait img, .csh-full img { width: 100%; height: 100%; object-fit: cover; object-position: center; transform: scale(1); transition: transform .4s ease; pointer-events: none; -webkit-user-drag: none; user-select: none; }
        .csh-portrait:hover img, .csh-full:hover img { transform: scale(1.02); }

        .csh-video-nav {
            position: absolute; top: 50%; transform: translateY(-50%);
            z-index: 20; width: 44px; height: 44px; border-radius: 50%;
            background: rgba(0,0,0,0.55); border: 1px solid rgba(255,255,255,0.22);
            color: #fff; font-size: 1.4rem; line-height: 1;
            display: flex; align-items: center; justify-content: center;
            cursor: pointer; opacity: 0; transition: opacity 0.2s, background 0.2s, transform 0.2s;
            backdrop-filter: blur(4px);
        }
        .csh-full:hover .csh-video-nav { opacity: 1; }
        .csh-video-nav:hover { background: rgba(0,0,0,0.85); transform: translateY(-50%) scale(1.1); }
        .csh-video-nav--prev { left: 14px; }
        .csh-video-nav--next { right: 14px; }

        .csh-video-counter {
            position: absolute; bottom: 12px; right: 14px; z-index: 20;
            background: rgba(0,0,0,0.58); border: 1px solid rgba(255,255,255,0.18);
            border-radius: 999px; padding: 3px 11px;
            color: #fff; font-size: 0.72rem; font-weight: 600; letter-spacing: .04em;
            opacity: 0; transition: opacity 0.2s;
            backdrop-filter: blur(4px);
        }
        .csh-full:hover .csh-video-counter { opacity: 1; }

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
        .csh-versatility-panel { min-height: 0; }
        .versatility-bar { margin-inline: auto; }
        .versatility-filler, .versatility-frame {
            position: absolute; inset: 0; width: 100%; height: 100%;
            object-fit: contain; pointer-events: none; user-select: none;
        }
        .versatility-filler {
            clip-path: inset(0 calc(11.5% + var(--versatility-empty, 100%)) 0 11.5%);
            transition: clip-path .35s cubic-bezier(.22, 1, .36, 1);
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
        .csh-weapon-tooltip {
            position:absolute;
            left:calc(100% - 12px);
            top:50%;
            width:min(340px, 48vw);
            transform:translateY(-50%) translateX(-8px);
            opacity:0;
            pointer-events:none;
            transition:opacity .18s ease, transform .18s ease;
            border:1px solid rgba(148,163,184,0.24);
            border-radius:12px;
            background:rgba(7, 12, 25, 0.96);
            box-shadow:0 18px 36px rgba(2,6,23,0.48);
            padding:.7rem .8rem;
            z-index:4;
        }
        .csh-weapon-item:hover .csh-weapon-tooltip { opacity:1; transform:translateY(-50%) translateX(0); }
        .csh-weapon-tooltip-title { color:#f8fafc; font-size:.78rem; font-weight:700; margin-bottom:.4rem; }
        .csh-weapon-tooltip-copy { color:#cbd5e1; font-size:.72rem; line-height:1.4; white-space:pre-line; }
        .csh-artefact-item {
            border:1px solid rgba(148,163,184,0.3);
            border-radius:14px;
            padding:.85rem .95rem;
            background: linear-gradient(180deg, rgba(17, 24, 39, 0.9), rgba(9, 14, 27, 0.92));
            box-shadow: inset 0 1px 0 rgba(255,255,255,0.05);
            display:grid;
            grid-template-columns:minmax(0, 1fr) minmax(150px, .72fr);
            column-gap:1rem;
            row-gap:.35rem;
        }
        .csh-artefact-head {
            display:flex;
            align-items:center;
            justify-content:space-between;
            gap:.75rem;
            margin-bottom:.5rem;
            grid-column:1 / -1;
        }
        .csh-artefact-title { color:#e2e8f0; font-size:.9rem; font-weight:700; }
        .csh-artefact-piece { color:#fef3c7; font-size:.72rem; font-weight:700; }
        .csh-artefact-row { display:flex; align-items:center; gap:.85rem; padding:.35rem 0; }
        .csh-artefact-media { position:relative; width:64px; height:64px; flex-shrink:0; }
        .csh-artefact-icon { width:64px; height:64px; border-radius:12px; object-fit:cover; border:1px solid rgba(148,163,184,0.28); background:rgba(15,23,42,0.35); display:block; }
        .csh-artefact-tooltip {
            position:absolute;
            left:calc(100% + 12px);
            top:50%;
            width:min(320px, 48vw);
            transform:translateY(-50%) translateX(-8px);
            opacity:0;
            pointer-events:none;
            transition:opacity .18s ease, transform .18s ease;
            border:1px solid rgba(148,163,184,0.24);
            border-radius:12px;
            background:rgba(7, 12, 25, 0.96);
            box-shadow:0 18px 36px rgba(2,6,23,0.48);
            padding:.7rem .8rem;
            z-index:4;
        }
        .csh-artefact-media:hover .csh-artefact-tooltip { opacity:1; transform:translateY(-50%) translateX(0); }
        .csh-artefact-tooltip-title { color:#f8fafc; font-size:.78rem; font-weight:700; margin-bottom:.4rem; }
        .csh-artefact-tooltip-line { display:flex; gap:.45rem; align-items:flex-start; margin-top:.35rem; }
        .csh-artefact-tooltip-badge { flex-shrink:0; min-width:34px; text-align:center; border-radius:999px; background:rgba(250,204,21,0.14); color:#fde68a; border:1px solid rgba(250,204,21,0.24); font-size:.64rem; font-weight:700; padding:.12rem .35rem; }
        .csh-artefact-tooltip-copy { color:#cbd5e1; font-size:.72rem; line-height:1.4; }
        .csh-artefact-copy { min-width:0; display:flex; flex-direction:column; gap:.15rem; }
        .csh-artefact-name { color:#cbd5e1; font-size:.82rem; }
        .csh-artefact-meta { color:#94a3b8; font-size:.72rem; }
        .csh-artefact-empty { padding:1rem 1.15rem 1.15rem; color:#8fa1c5; font-size:.85rem; font-style:italic; }
        .csh-substats-priority { grid-column:2; grid-row:2 / span 2; align-self:stretch; margin-top:0; padding:.55rem .7rem; border:1px dashed rgba(148,163,184,0.28); border-radius:10px; display:flex; flex-direction:column; align-items:center; justify-content:center; gap:.15rem; }
        .csh-substats-priority-title { font-size:.66rem; text-transform:uppercase; letter-spacing:.05em; color:#8aa0ca; margin-bottom:.35rem; }
        .csh-substats-priority-row { display:flex; flex-direction:column; align-items:center; gap:.15rem; }
        .csh-substats-priority-rank { display:inline-flex; align-items:center; justify-content:center; width:20px; height:20px; border-radius:999px; background:rgba(250,204,21,0.16); color:#fde68a; border:1px solid rgba(250,204,21,0.3); font-size:.65rem; font-weight:700; }
        .csh-substats-priority-name { font-size:.76rem; color:#e2e8f0; font-weight:600; }
        .csh-substats-priority-arrow { color:#64748b; font-size:.8rem; line-height:1; }
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
            position: relative;
        }
        .csh-constellation-frame img { width:100%; height:100%; object-fit:cover; }
        .csh-constellation-map-wrap {
            position: absolute;
            inset: 0;
            pointer-events: none;
        }
        .csh-constellation-map-line {
            position: absolute;
            height: 2px;
            transform-origin: 0 50%;
            transition: background .2s ease, box-shadow .2s ease, opacity .2s ease;
        }
        .csh-constellation-map-line.is-off {
            background: rgba(148,163,184,.45);
            box-shadow: 0 0 0 1px rgba(148,163,184,.2);
            opacity: .55;
        }
        .csh-constellation-map-line.is-on {
            background: linear-gradient(90deg, #fde68a, #f59e0b);
            box-shadow: 0 0 0 1px rgba(245,158,11,.25), 0 0 12px rgba(245,158,11,.35);
            opacity: 1;
        }
        .csh-constellation-map-point {
            position: absolute;
            transform: translate(-50%, -50%);
            width: 24px;
            height: 24px;
            border-radius: 999px;
            border: 2px solid #ffffff;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 11px;
            font-weight: 700;
            transition: background .2s ease, box-shadow .2s ease, transform .2s ease;
        }
        .csh-constellation-map-point.is-off {
            background: #475569;
            color: #cbd5e1;
            box-shadow: 0 2px 8px rgba(2,6,23,.35);
        }
        .csh-constellation-map-point.is-on {
            background: #f59e0b;
            color: #fff7ed;
            box-shadow: 0 0 0 2px rgba(245,158,11,.3), 0 0 16px rgba(245,158,11,.45);
        }
        .csh-constellation-map-point.is-current {
            background: #facc15;
            color: #422006;
            transform: translate(-50%, -50%) scale(1.07);
            box-shadow: 0 0 0 2px rgba(250,204,21,.35), 0 0 18px rgba(250,204,21,.55);
        }
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
        .csh-aptitudes-shell {
            margin: 0 1.5rem 1.5rem;
            border: 1px solid rgba(255,255,255,0.12);
            border-radius: 18px;
            background: linear-gradient(180deg, rgba(10,15,30,0.95), rgba(5,10,24,0.92));
            box-shadow: 0 18px 40px rgba(2,6,23,0.32);
            overflow: hidden;
        }
        .csh-aptitudes-list { display:flex; flex-direction:column; gap:.65rem; padding:1rem 1.15rem 1.15rem; }
        .csh-aptitude-item {
            display:grid;
            grid-template-columns: 52px minmax(0,1fr);
            gap:.75rem;
            align-items:flex-start;
            border:1px solid rgba(148,163,184,0.18);
            border-radius:14px;
            padding:.7rem .85rem;
            background: linear-gradient(180deg, rgba(18,28,55,0.86), rgba(10,16,34,0.88));
        }
        .csh-aptitude-icon {
            width:52px; height:52px; border-radius:12px; flex-shrink:0;
            object-fit:cover; border:1px solid rgba(255,255,255,.15);
            background: rgba(255,255,255,.04);
        }
        .csh-aptitude-icon-placeholder {
            width:52px; height:52px; border-radius:12px;
            border:1px dashed rgba(255,255,255,.18);
            display:flex; align-items:center; justify-content:center;
            font-size:1.4rem; color:rgba(255,255,255,.2);
        }
        .csh-aptitude-body { min-width:0; }
        .csh-aptitude-type { font-size:.62rem; font-weight:700; letter-spacing:.06em; text-transform:uppercase; color:#7dd3fc; margin-bottom:.2rem; }
        .csh-aptitude-title { font-size:.9rem; font-weight:700; color:#e2e8f0; margin-bottom:.25rem; }
        .csh-aptitude-desc { font-size:.75rem; color:#94a3b8; line-height:1.5; display:-webkit-box; line-clamp:3; -webkit-line-clamp:3; -webkit-box-orient:vertical; overflow:hidden; }
        .csh-aptitudes-empty { padding:1rem 1.15rem 1.15rem; color:#8fa1c5; font-size:.85rem; font-style:italic; }
        .csh-team-shell {
            margin: 0 1.5rem 1.5rem;
            border: 1px solid rgba(200,169,110,0.25);
            border-radius: 18px;
            background: linear-gradient(180deg, rgba(26,26,46,0.95), rgba(16,18,38,0.95));
            box-shadow: 0 18px 40px rgba(2, 6, 23, 0.4);
            overflow: hidden;
        }
        .csh-team-group { padding: 1rem 1.15rem 1.15rem; display:grid; gap:.75rem; }
        .csh-team-group-head { display:flex; align-items:center; justify-content:space-between; gap:.75rem; }
        .csh-team-group-title { color:#f3ead9; font-size:.9rem; font-weight:800; letter-spacing:.06em; text-transform:uppercase; }
        .csh-team-group-sub { color:#9fb0dc; font-size:.72rem; }
        .csh-team-card {
            border:1px solid rgba(148,163,184,.28);
            border-radius:14px;
            background: linear-gradient(180deg, rgba(31,35,56,.9), rgba(26,30,50,.92));
            padding:.7rem .75rem .8rem;
            display:grid;
            gap:.6rem;
        }
        .csh-team-card.recommended { border-color: rgba(200,169,110,.42); background: linear-gradient(180deg, rgba(50,40,30,.92), rgba(37,31,42,.95)); }
        .csh-team-card.f2p { border-color: rgba(81,199,137,.45); }
        .csh-team-card-head { display:flex; align-items:center; justify-content:space-between; gap:.6rem; }
        .csh-team-card-tags { display:flex; align-items:center; gap:.35rem; flex-wrap:wrap; }
        .csh-team-tag { border-radius:999px; padding:.14rem .5rem; font-size:.62rem; font-weight:800; letter-spacing:.04em; border:1px solid rgba(255,255,255,.12); }
        .csh-team-tag-rec { background:rgba(200,169,110,.22); color:#f3ddaf; border-color:rgba(200,169,110,.45); }
        .csh-team-tag-f2p { background:rgba(81,199,137,.2); color:#baf3d2; border-color:rgba(81,199,137,.45); }
        .csh-team-remplacants-btn {
            border:1px solid rgba(200,169,110,.38);
            background: rgba(20,22,36,.5);
            color:#f3ddaf;
            border-radius:999px;
            padding:.18rem .5rem;
            font-size:.72rem;
            font-weight:700;
            transition: .2s ease;
        }
        .csh-team-remplacants-btn:hover { background: rgba(50,40,30,.7); }
        .csh-team-slots { display:grid; grid-template-columns:repeat(4,minmax(0,1fr)); gap:.55rem; }
        .csh-team-slot { border:1px solid rgba(148,163,184,.22); border-radius:10px; background:rgba(11,16,31,.5); padding:.45rem; transition:.2s ease; }
        .csh-team-slot:hover { background:rgba(28,37,65,.62); border-color:rgba(96,165,250,.45); }
        .csh-team-slot img { width:100%; aspect-ratio:1; object-fit:cover; border-radius:10px; border:2px solid rgba(255,255,255,.2); }
        .csh-team-slot-name { margin-top:.3rem; font-size:.7rem; font-weight:700; color:#eff3ff; text-align:center; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
        .csh-team-slot-role { margin:.18rem auto 0; width:fit-content; border-radius:999px; padding:.12rem .42rem; font-size:.58rem; font-weight:700; color:#d8e4ff; background:rgba(43,52,91,.8); border:1px solid rgba(73,90,149,.5); }
        .csh-team-remplacants { margin-top:.45rem; display:grid; gap:.35rem; }
        .csh-team-remplacant-row { border:1px dashed rgba(148,163,184,.3); border-radius:10px; padding:.35rem; }
        .csh-team-remplacant-head { color:#94a3b8; font-size:.62rem; font-weight:700; margin-bottom:.28rem; }
        .csh-team-remplacant-list { display:flex; flex-wrap:wrap; gap:.35rem; }
        .csh-team-remplacant-item { width:48px; }
        .csh-team-remplacant-item img { width:48px; height:48px; border-radius:8px; object-fit:cover; border:1px solid rgba(125,211,252,.32); }
        .csh-team-drawer-btn {
            width:100%;
            border:1px dashed rgba(125,211,252,.36);
            border-radius:10px;
            padding:.45rem .65rem;
            display:flex;
            align-items:center;
            justify-content:center;
            gap:.45rem;
            color:#d9e4ff;
            background: rgba(20,27,50,.45);
            font-size:.72rem;
            font-weight:700;
        }
        .csh-team-drawer-btn:hover { background: rgba(31,44,79,.65); }
        .csh-team-drawer-chevron { display:inline-block; transition:transform .22s ease; animation: csh-team-chevron-pulse 1s ease-in-out infinite; }
        .csh-team-drawer-chevron.is-open { transform:rotate(180deg); animation:none; }
        .csh-team-drawer-count { border-radius:999px; padding:.08rem .45rem; font-size:.62rem; background:rgba(125,211,252,.25); border:1px solid rgba(125,211,252,.44); color:#eaf4ff; }
        .csh-team-others { display:grid; gap:.6rem; }
        @keyframes csh-team-chevron-pulse { 0%,100%{transform:translateY(0);} 50%{transform:translateY(2px);} }
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
        .th-const-map-dropzone {
            border: 1px dashed #94a3b8;
            border-radius: 10px;
            background: #ffffff;
            padding: .5rem .6rem;
            color: #475569;
            font-size: 11px;
            text-align: center;
            cursor: pointer;
        }
        .th-const-map-dropzone:hover { border-color: #0284c7; background: #f0f9ff; }
        .th-const-map-dropzone input[type="file"] {
            display: block;
            width: 100%;
            margin-top: .4rem;
            font-size: 11px;
            color: #334155;
        }
        .th-apt-dropzone {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            border: 1.5px dashed #94a3b8;
            border-radius: 10px;
            background: #f8fafc;
            padding: .6rem;
            cursor: pointer;
            transition: border-color .15s, background .15s;
            min-height: 80px;
            text-align: center;
        }
        .th-apt-dropzone:hover { border-color: #6366f1; background: #eef2ff; }
        .th-apt-dropzone--over { border-color: #6366f1; background: #eef2ff; border-style: solid; }
        .th-const-map-canvas {
            position: relative;
            width: min(100%, 240px);
            aspect-ratio: 4 / 5;
            border: 1px dashed #94a3b8;
            border-radius: 12px;
            overflow: hidden;
            background: linear-gradient(180deg, #0f172a 0%, #1e293b 100%);
            margin: 0 auto;
        }
        .th-const-map-canvas--modal {
            width: min(100%, 980px);
            aspect-ratio: 16 / 10;
            margin: 0 auto;
            cursor: crosshair;
        }
        .th-const-map-canvas img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            object-position: center;
            pointer-events: none;
            user-select: none;
        }
        .th-const-map-media {
            position: absolute;
            overflow: visible;
        }
        .th-const-map-media img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            object-position: center;
            display: block;
        }
        .th-const-map-line {
            position: absolute;
            height: 2px;
            background: linear-gradient(90deg, #f8fafc, #38bdf8);
            transform-origin: 0 50%;
            pointer-events: none;
            box-shadow: 0 0 0 1px rgba(2, 132, 199, 0.15);
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
        .th-const-map-modal-overlay {
            position: fixed;
            inset: 0;
            background: rgba(2, 6, 23, 0.6);
            z-index: 70;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
        }
        .th-const-map-modal {
            width: min(1080px, 96vw);
            max-height: 92vh;
            overflow: auto;
            border-radius: 14px;
            border: 1px solid #cbd5e1;
            background: #f8fafc;
            box-shadow: 0 20px 60px rgba(2, 6, 23, 0.45);
            padding: .9rem;
        }
        .th-const-mode-btn {
            border: 1px solid #cbd5e1;
            background: #ffffff;
            color: #334155;
            border-radius: 8px;
            padding: .25rem .5rem;
            font-size: 11px;
            font-weight: 700;
        }
        .th-const-mode-btn.is-active {
            border-color: #0284c7;
            background: #e0f2fe;
            color: #075985;
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
        .th-artefact-picker-modal { z-index: 90; }
        .th-armes-picker-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 8px; padding: 12px; }
        .th-armes-picker-item { cursor: pointer; border: 1px solid #e2e8f0; border-radius: 8px; padding: 8px; text-align: center; background: #f8fafc; transition: all 0.2s; }
        .th-armes-picker-item:hover { border-color: #0ea5e9; background: #f0f9ff; transform: scale(1.02); }
        .th-armes-picker-icon { display: flex; justify-content: center; margin-bottom: 6px; }
        .th-armes-picker-item .name { font-size: 11px; font-weight: 600; color: #0f172a; line-height: 1.2; }
        .th-armes-picker-item .rarity { font-size: 10px; color: #64748b; }

        /* ── Modal édition des 6 constellations ─────────────────────── */
        .th-const-edit-overlay {
            position: fixed; inset: 0;
            background: rgba(2, 6, 23, 0.62);
            z-index: 75;
            display: flex; align-items: center; justify-content: center; padding: 1rem;
        }
        .th-const-edit-modal {
            width: min(1140px, 97vw);
            max-height: 92vh;
            overflow-y: auto;
            border-radius: 16px;
            border: 1px solid #cbd5e1;
            background: #f8fafc;
            box-shadow: 0 24px 70px rgba(2, 6, 23, 0.50);
            padding: 1.25rem 1.5rem 1.5rem;
        }
        .th-apt-single-modal {
            width: min(480px, 97vw);
            max-height: 92vh;
            overflow-y: auto;
            border-radius: 16px;
            border: 1px solid #cbd5e1;
            background: #f8fafc;
            box-shadow: 0 24px 70px rgba(2, 6, 23, 0.50);
            padding: 1.25rem 1.5rem 1.5rem;
        }
        .th-const-edit-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1rem;
            margin-top: 1rem;
        }
        @media (max-width: 800px) {
            .th-const-edit-grid { grid-template-columns: 1fr 1fr; }
        }
        .th-const-edit-card {
            border: 1px solid #cbd5e1;
            border-radius: 12px;
            background: #fff;
            padding: .9rem;
            display: flex; flex-direction: column; gap: .55rem;
            overflow: visible;
            box-shadow: 0 2px 8px rgba(15, 23, 42, 0.07);
        }
        .th-apt-type-radio { display: flex; flex-wrap: wrap; gap: .3rem; }
        .th-apt-type-radio button {
            padding: .22rem .55rem;
            font-size: 11px; font-weight: 600;
            border: 1px solid #cbd5e1; border-radius: 999px;
            background: #f1f5f9; color: #475569;
            cursor: pointer; transition: all .12s;
            white-space: nowrap;
        }
        .th-apt-type-radio button:hover { border-color: #38bdf8; background: #f0f9ff; color: #0369a1; }
        .th-apt-type-radio button.is-active { border-color: #0284c7; background: #0284c7; color: #fff; }
        .th-const-edit-card-header {
            display: flex; align-items: center; gap: .6rem;
        }
        .th-const-edit-badge {
            flex-shrink: 0;
            width: 28px; height: 28px;
            border-radius: 50%;
            background: #0f172a;
            color: #fff;
            font-size: 11px; font-weight: 700;
            display: flex; align-items: center; justify-content: center;
        }
        .th-const-edit-card input[type="text"],
        .th-const-edit-card textarea {
            width: 100%;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            padding: .35rem .55rem;
            font-size: 11.5px;
            color: #0f172a;
            background: #f8fafc;
            resize: vertical;
        }
        .th-const-edit-card input[type="text"]:focus,
        .th-const-edit-card textarea:focus {
            outline: none; border-color: #38bdf8; background: #fff;
            box-shadow: 0 0 0 2px rgba(56, 189, 248, 0.18);
        }
        .th-const-edit-img-row {
            display: flex; align-items: center; gap: .5rem;
        }
        .th-const-edit-img-row img {
            width: 52px; height: 52px;
            border-radius: 8px;
            object-fit: contain;
            border: 1px solid #e2e8f0;
            background: #f1f5f9;
        }
        .th-const-edit-upload-btn {
            flex: 1;
            border: 1px dashed #94a3b8;
            border-radius: 6px;
            padding: .35rem .6rem;
            font-size: 11px; font-weight: 600; color: #475569;
            background: #f8fafc;
            cursor: pointer; text-align: center;
            transition: border-color .15s, background .15s;
        }
        .th-const-edit-upload-btn:hover { border-color: #38bdf8; background: #f0f9ff; color: #0369a1; }
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
        @media (max-width: 640px) {
            .csh-artefact-item { grid-template-columns:1fr; }
            .csh-artefact-head, .csh-substats-priority { grid-column:1; }
            .csh-substats-priority { grid-row:auto; }
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
        
        $nationBarFrames = [];
        foreach ($nations as $nation) {
            $nationSlug = $nation->slug ?? \Illuminate\Support\Str::slug($nation->nom_region);
            $barPath = public_path('images/versatility-bars/' . $nationSlug . '.png');
            $nationBarFrames[$nation->id_region] = file_exists($barPath)
                ? asset('images/versatility-bars/' . $nationSlug . '.png')
                : asset('images/versatility-bars/default.png');
        }

        $nationBarFillers = [];
foreach ($nations as $nation) {
    $nationSlug = $nation->slug ?? \Illuminate\Support\Str::slug($nation->nom_region);
    $fillerPath = public_path('images/versatility-bars/' . $nationSlug . '-filler.png');
    $nationBarFillers[$nation->id_region] = file_exists($fillerPath)
        ? asset('images/versatility-bars/' . $nationSlug . '-filler.png')
        : asset('images/versatility-bars/filler.png');
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
        'versatilite' => $personnage->versatilite,
        'arme_icon' => $personnage->arme_icon ?? null,
        'background_actif' => $personnage->background_actif ?? '',
        'videos' => $personnage->videos->map(fn($v)=>['url_video'=>$v->url_video])->values(),
        'nationBarFillers' => $nationBarFillers,
        'nationBarFrames' => $nationBarFrames,
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
                'passive_name' => $a->nom_competence,
                'passive_desc' => $a->descr_arme,
                'etoile' => $rarityLabel,
                'stars' => $rarityStars,
                'fid_etoile' => $a->fid_etoile,
                'fid_TArmes' => $a->fid_TArmes,
                'type' => $type,
                'icon' => $fileExists ? $localIcon : ($a->icone_url ?? asset('images/placeholder.svg')),
            ];
        });

        $availableArtefactsJson = $artefactsDisponibles->map(function ($artefact) {
            $photo = $artefact->photos->first();
            $icon = $photo?->source_url
                ?? ($photo?->chemin_photo
                    ? (filter_var((string) $photo->chemin_photo, FILTER_VALIDATE_URL)
                        ? $photo->chemin_photo
                        : asset('storage/' . ltrim((string) $photo->chemin_photo, '/')))
                    : asset('images/placeholder.svg'));

            $rarityLabel = $artefact->rareté?->libelle_rareté ?? '';
            $rarityStars = (int) preg_replace('/\D+/', '', (string) $rarityLabel);
            $rarityOptions = [];

            if ($rarityStars >= 1 && $rarityStars <= 5) {
                $rarityOptions[] = $rarityStars;

                // Les sets 5★ existent généralement aussi en 4★.
                if ($rarityStars === 5) {
                    $rarityOptions[] = 4;
                }
            }

            $rarityOptions = array_values(array_unique($rarityOptions));
            sort($rarityOptions);
            $displayRarity = !empty($rarityOptions)
                ? implode(' / ', array_map(fn ($star) => $star . '★', $rarityOptions))
                : ($rarityLabel ?: '?');

            return [
                'id' => (int) $artefact->id_artefact,
                'nom' => $artefact->nom_artefact,
                'slug' => $artefact->slug,
                'bonus_2p' => $artefact->bonus_2p,
                'bonus_4p' => $artefact->bonus_4p,
                'rarete' => $displayRarity,
                'stars' => $rarityStars,
                'rarity_options' => $rarityOptions,
                'icon' => $icon,
            ];
        })->values();

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
                'passive_name' => $w->arme?->nom_competence,
                'passive_desc' => $w->arme?->descr_arme,
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
            $artefact1Photo = $build->artefact1?->photos->first();
            $artefact2Photo = $build->artefact2?->photos->first();

            return [
                'id_build' => $build->id_build,
                'artefact1_id' => $build->artefact1?->id_artefact,
                'artefact1_nom' => $artefact1,
                'artefact1_bonus_2p' => $build->artefact1?->bonus_2p,
                'artefact1_bonus_4p' => $build->artefact1?->bonus_4p,
                'artefact1_icon' => $artefact1Photo?->source_url
                    ?? ($artefact1Photo?->chemin_photo
                        ? (filter_var((string) $artefact1Photo->chemin_photo, FILTER_VALIDATE_URL)
                            ? $artefact1Photo->chemin_photo
                            : asset('storage/' . ltrim((string) $artefact1Photo->chemin_photo, '/')))
                        : asset('images/placeholder.svg')),
                'pieces_1' => (int) $build->pieces_1,
                'artefact2_id' => $build->artefact2?->id_artefact,
                'artefact2_nom' => $artefact2,
                'artefact2_bonus_2p' => $build->artefact2?->bonus_2p,
                'artefact2_bonus_4p' => $build->artefact2?->bonus_4p,
                'artefact2_icon' => $artefact2Photo?->source_url
                    ?? ($artefact2Photo?->chemin_photo
                        ? (filter_var((string) $artefact2Photo->chemin_photo, FILTER_VALIDATE_URL)
                            ? $artefact2Photo->chemin_photo
                            : asset('storage/' . ltrim((string) $artefact2Photo->chemin_photo, '/')))
                        : asset('images/placeholder.svg')),
                'pieces_2' => (int) ($build->pieces_2 ?? 0),
                'main_stat_sablier' => $build->main_stat_sablier,
                'main_stat_gobelet' => $build->main_stat_gobelet,
                'main_stat_couronne' => $build->main_stat_couronne,
                'sub_stats' => $build->sub_stats
                    ? array_map('trim', explode(',', $build->sub_stats))
                    : [],
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
        $constellationMapLinesJson = [];
        if ($constCarte && is_array($constCarte->positions_const)) {
            $rawMapPayload = $constCarte->positions_const;
            $rawPoints = is_array($rawMapPayload['points'] ?? null) ? $rawMapPayload['points'] : $rawMapPayload;
            foreach ($rawPoints as $k => $point) {
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

            $rawLines = is_array($rawMapPayload['lines'] ?? null) ? $rawMapPayload['lines'] : [];
            foreach ($rawLines as $line) {
                if (!is_array($line)) {
                    continue;
                }
                $from = isset($line['from']) ? (int) $line['from'] : null;
                $to = isset($line['to']) ? (int) $line['to'] : null;
                if (!$from || !$to || $from === $to) {
                    continue;
                }
                if ($from < 1 || $from > 6 || $to < 1 || $to > 6) {
                    continue;
                }
                $constellationMapLinesJson[] = ['from' => $from, 'to' => $to];
            }
        }

        $constellationMapImage = '';
        if ($constCarte && $constCarte->photo) {
            if ($constCarte->photo->source_url) {
                $constellationMapImage = $constCarte->photo->source_url;
            } elseif (filter_var((string) $constCarte->photo->chemin_photo, FILTER_VALIDATE_URL)) {
                $constellationMapImage = $constCarte->photo->chemin_photo;
            } elseif ($constCarte->photo->chemin_photo) {
                $constellationMapImage = asset('storage/' . ltrim((string) $constCarte->photo->chemin_photo, '/'));
            }
        }

        $aptitudesJson = $personnage->aptitudes
            ->sortBy('id_aptitude')
            ->values()
            ->map(fn($a) => [
                'id_aptitude'  => (int) $a->id_aptitude,
                'titre_apti'   => $a->titre_apti,
                'descri_apti'  => $a->descri_apti ?? '',
                'fid_TypeApti' => (int) $a->fid_TypeApti,
                'image_url'    => $a->photos->first()?->source_url
                               ?? ($a->photos->first()?->chemin_photo
                                   ? asset('storage/' . $a->photos->first()->chemin_photo)
                                   : null),
            ]);

        $histoiresJson = $personnage->histoires
            ->sortBy('ordre')
            ->values()
            ->map(fn($h) => [
                'id_histoire' => (int) $h->id_histoire,
                'titre_histoire' => $h->titre_histoire ?? '',
                'histoire' => $h->histoire ?? '',
                'ordre' => (int) ($h->ordre ?? 1),
            ]);

        $storyMonstresJson = $ennemisDisponibles->map(function ($ennemi) {
            $photo = $ennemi->photos->first();
            $image = $photo?->source_url
                ?? ($photo?->chemin_photo
                    ? (filter_var((string) $photo->chemin_photo, FILTER_VALIDATE_URL)
                        ? $photo->chemin_photo
                        : asset('storage/' . ltrim((string) $photo->chemin_photo, '/')))
                    : asset('images/placeholder.svg'));

            return [
                'key' => $ennemi->slug,
                'label' => $ennemi->nom_ennemi,
                'image' => $image,
                'url' => route('ennemis.show', $ennemi),
                'is_boss' => str_contains(strtolower((string) ($ennemi->typeEnnemi?->libelle_Type ?? '')), 'boss'),
            ];
        })->values();

        $storyArmesJson = collect($availableArmesJson)->map(fn($arme) => [
            'key' => $arme['slug'] ?? null,
            'label' => $arme['nom'] ?? 'Arme',
            'image' => $arme['icon'] ?? asset('images/placeholder.svg'),
            'url' => !empty($arme['slug']) ? route('armes.show', $arme['slug']) : null,
        ])->filter(fn($arme) => !empty($arme['key']))->values();

        $storyAptitudesJson = $aptitudesJson->map(fn($aptitude) => [
            'key' => (string) $aptitude['id_aptitude'],
            'label' => $aptitude['titre_apti'] ?: ('Aptitude #' . $aptitude['id_aptitude']),
            'image' => $aptitude['image_url'] ?? asset('images/placeholder.svg'),
            'url' => '#aptitude-' . $aptitude['id_aptitude'],
        ])->values();

        $storyCommandSourcesJson = [
            'aptitudes' => $storyAptitudesJson,
            'armes' => $storyArmesJson,
            'monstres' => $storyMonstresJson,
            'boss' => $storyMonstresJson->where('is_boss', true)->values(),
        ];

        $typesAptiJson = $typesApti->map(fn($t) => [
            'id'      => (int) $t->id_TypeApti,
            'libelle' => $t->libelle_Apti,
        ]);

        $teamsJson = $personnage->teamCompositions()
            ->with([
                'membres.personnage.element',
                'membres.personnage.photos',
                'membres.personnage.roles',
                'alternatives.personnage.element',
                'alternatives.personnage.photos',
                'alternatives.personnage.roles',
            ])
            ->get()
            ->map(function ($team) {
                return [
                    'id_team' => (int) $team->id_team,
                    'type_reaction' => $team->type_reaction,
                    'tag' => $team->tag,
                    'rotation' => $team->rotation,
                    'membres' => $team->membres->sortBy('slot')->values()->map(function ($m) {
                        $photo = $m->personnage?->photos->where('type', 'icone')->first() ?? $m->personnage?->photos->first();
                        $defaultRole = $m->personnage?->roles->first()?->libelle_role;

                        return [
                            'slot' => (int) $m->slot,
                            'id_perso' => (int) $m->fid_perso,
                            'nom' => $m->personnage?->nom_perso ?? '',
                            'element' => $m->personnage?->element?->libelle_element ?? '',
                            'default_role' => $defaultRole,
                            'role_override' => $m->role_override,
                            'role' => $m->role_override ?: $defaultRole,
                            'icon' => $photo?->source_url
                                ?? ($photo?->chemin_photo
                                    ? (filter_var((string) $photo->chemin_photo, FILTER_VALIDATE_URL)
                                        ? $photo->chemin_photo
                                        : asset('storage/' . ltrim((string) $photo->chemin_photo, '/')))
                                    : null),
                        ];
                    })->all(),
                    'remplacants' => $team->alternatives->values()->map(function ($r) {
                        $photo = $r->personnage?->photos->where('type', 'icone')->first() ?? $r->personnage?->photos->first();
                        $defaultRole = $r->personnage?->roles->first()?->libelle_role;

                        return [
                            'id' => (int) $r->id,
                            'slot' => (int) $r->slot,
                            'id_perso' => (int) $r->fid_perso_remplacant,
                            'nom' => $r->personnage?->nom_perso ?? '',
                            'element' => $r->personnage?->element?->libelle_element ?? '',
                            'default_role' => $defaultRole,
                            'role_override' => $r->role_override,
                            'role' => $r->role_override ?: $defaultRole,
                            'icon' => $photo?->source_url
                                ?? ($photo?->chemin_photo
                                    ? (filter_var((string) $photo->chemin_photo, FILTER_VALIDATE_URL)
                                        ? $photo->chemin_photo
                                        : asset('storage/' . ltrim((string) $photo->chemin_photo, '/')))
                                    : null),
                        ];
                    })->all(),
                ];
            })->values();

        $teamPersonnagesPoolJson = \App\Models\Personnage::with(['element', 'photos', 'roles'])
            ->orderBy('nom_perso')
            ->get()
            ->map(function ($p) {
                $photo = $p->photos->where('type', 'icone')->first() ?? $p->photos->first();
                return [
                    'id_perso' => (int) $p->getKey(),
                    'nom' => $p->nom_perso,
                    'element' => $p->element?->libelle_element ?? '',
                    'default_role' => $p->roles->first()?->libelle_role,
                    'icon' => $photo?->source_url
                        ?? ($photo?->chemin_photo
                            ? (filter_var((string) $photo->chemin_photo, FILTER_VALIDATE_URL)
                                ? $photo->chemin_photo
                                : asset('storage/' . ltrim((string) $photo->chemin_photo, '/')))
                            : null),
                ];
            })->values();

        $reactionsJson = $reactions->map(fn($r) => [
            'id_reaction'  => (int) $r->id_reaction,
            'nom_reaction' => $r->nom_reaction,
            'slug'         => $r->slug,
            'icon'         => (function ($r) {
                $photo = $r->photos->first();
                return $photo?->source_url
                    ?? ($photo?->chemin_photo
                        ? (filter_var((string) $photo->chemin_photo, FILTER_VALIDATE_URL)
                            ? $photo->chemin_photo
                            : asset('storage/' . ltrim((string) $photo->chemin_photo, '/')))
                        : null);
            })($r),
        ])->values();
    @endphp

    <div id="personnage-editor-config"
         data-main-zone="{{ e($mainZoneJson) }}"
         data-fresh-create="{{ $isFreshCreate ? '1' : '0' }}"
         data-nom-perso="{{ e($personnage->nom_perso) }}"
         data-fid-element="{{ e($personnage->fid_element) }}"
         data-fid-etoile="{{ e($personnage->fid_etoile) }}"
         data-fid-tarmes="{{ e($personnage->fid_TArmes) }}"
         data-fid-tp="{{ e($personnage->fid_TP) }}"
         data-fid-nation="{{ e($personnage->nations->first()?->id_region ?? '') }}"
         data-arme-icon="{{ e($personnage->arme_icon ?? '') }}"
         data-nation-bar-frames="{{ e(json_encode($nationBarFrames)) }}"
         data-nation-bar-fillers="{{ e(json_encode($nationBarFillers)) }}"
         data-available-armes="{{ e(json_encode($availableArmesJson)) }}"
         data-available-artefacts="{{ e(json_encode($availableArtefactsJson)) }}"
         data-existing-armes="{{ e(json_encode($existingArmesJson)) }}"
         data-existing-artefacts="{{ e(json_encode($existingArtefactsJson)) }}"
         data-constellations="{{ e(json_encode($constellationsJson)) }}"
         data-const-map-positions="{{ e(json_encode($constellationMapPositionsJson)) }}"
         data-const-map-lines="{{ e(json_encode($constellationMapLinesJson)) }}"
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
         data-upload-main-zone-image-url="{{ route('admin.personnage.block.main-zone.upload', $personnage) }}"
         data-save-armes-url="{{ route('admin.personnage.block.armes.update', $personnage) }}"
         data-save-artefacts-url="{{ route('admin.personnage.block.artefacts.update', $personnage) }}"
         data-save-constellations-url="{{ route('admin.personnage.block.constellations.update', $personnage) }}"
         data-upload-constellation-url="{{ route('admin.personnage.block.constellations.upload', $personnage) }}"
         data-save-constellation-map-url="{{ route('admin.personnage.block.constellation-map.update', $personnage) }}"
         data-save-competences-url="{{ route('admin.personnage.block.competences.update', $personnage) }}"
         data-upload-competences-url="{{ route('admin.personnage.block.competences.upload', $personnage) }}"
         data-aptitudes="{{ e(json_encode($aptitudesJson)) }}"
         data-histoires="{{ e(json_encode($histoiresJson)) }}"
         data-story-command-sources="{{ e(json_encode($storyCommandSourcesJson)) }}"
         data-types-apti="{{ e(json_encode($typesAptiJson)) }}"
         data-teams="{{ e(json_encode($teamsJson)) }}"
         data-team-pool="{{ e(json_encode($teamPersonnagesPoolJson)) }}"
         data-reactions="{{ e(json_encode($reactionsJson)) }}"
         data-store-team-url="{{ route('admin.personnage.block.teams.store', $personnage) }}"
         data-update-team-url-base="{{ route('admin.personnage.block.teams.store', $personnage) }}"
         data-delete-team-url-base="{{ route('admin.personnage.block.teams.store', $personnage) }}"
         data-team-aptitudes-url-base="{{ route('admin.personnage.block.teams.aptitudes', [$personnage, 'id_team']) }}"
         data-update-team-rotation-url-base="{{ route('admin.personnage.block.teams.rotation.update', [$personnage, 'id_team']) }}"
         data-showcase-url="{{ route('personnages.show', $personnage) }}"
         data-save-histoires-url="{{ route('admin.personnage.block.histoires.update', $personnage) }}"
         data-google-drive-api-key="{{ e((string) config('services.google_drive.api_key', '')) }}"
         data-google-drive-client-id="{{ e((string) config('services.google_drive.client_id', '')) }}"
         data-google-drive-app-id="{{ e((string) config('services.google_drive.app_id', '')) }}"
         data-google-drive-folder-id="{{ e((string) config('services.google_drive.folder_id', '')) }}"
         data-google-drive-folder-url="{{ e((string) config('services.google_drive.folder_url', 'https://drive.google.com/drive/folders/1rHi1v6620v3H_gEOhpgwBQnm2a0cTWNn?usp=drive_link')) }}"
         data-google-drive-browse-url="{{ route('admin.google-drive.browse') }}"
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
                   :class="sidebarCollapsed ? 'border-r-0' : 'border-r border-slate-300'"
                   :style="sidebarCollapsed
                       ? 'width:0;min-width:0;max-width:0;'
                       : 'width:340px;min-width:340px;max-width:340px;'">

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
                            <option value="">-- Choisir un élément --</option>
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
                            <option value="">-- Choisir un type d'arme --</option>
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
                        <option value="">-- Choisir une rareté --</option>
                        @foreach($etoiles as $et)
                            <option value="{{ $et->id_etoile }}">{{ $et->libelle }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-slate-700 text-xs font-semibold uppercase tracking-wide mb-1">
                        Versatilité <span class="text-slate-400 font-normal normal-case">(0 = P2W, 10 = très versatile)</span>
                    </label>
                    <div class="flex items-center gap-3">
                        <input type="range" x-model.number="mainZone.versatilite" min="0" max="10" step="1"
                               class="flex-1 accent-blue-600">
                        <span class="w-8 text-center font-bold text-slate-800" x-text="mainZone.versatilite ?? '—'"></span>
                    </div>
                </div>

                <div>
                    <label class="block text-slate-700 text-xs font-semibold uppercase tracking-wide mb-1">Région / Nation</label>
                    <div class="flex items-center gap-2">
                        <img x-effect="$el.src = selectedNationIcon" alt="" class="w-7 h-7 rounded-full border border-slate-300 bg-slate-200 p-1 shadow-inner" />
                        <select x-model="mainZone.fid_nation"
                                @change="mainZone.fid_nation = String($event.target.value)"
                                class="flex-1 rounded border border-slate-300 bg-white px-2 py-2 text-black text-sm focus:outline-none focus:border-blue-500">
                            <option value="">-- Aucune nation --</option>
                            @foreach($nations as $nation)
                                <option value="{{ $nation->id_region }}">{{ $nation->nom_region }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-slate-700 text-xs font-semibold uppercase tracking-wide mb-1">Fond personnage</label>
                    <div class="space-y-2 rounded border border-slate-300 bg-white p-2.5">
                        <input type="url"
                               x-model="driveBackgroundUrlInput"
                               @blur="applyBackgroundUrlInput()"
                               class="w-full rounded border border-slate-300 bg-white px-2 py-2 text-black text-xs placeholder-slate-400 focus:outline-none focus:border-blue-500"
                               placeholder="https://drive.google.com/file/d/..." />

                        <div class="flex gap-2">
                            <button type="button"
                                    @click="openGoogleDriveBrowser()"
                                    class="w-full rounded border border-blue-300 bg-blue-50 px-2 py-1.5 text-[11px] font-semibold text-blue-700 hover:bg-blue-100">
                                Parcourir Drive
                            </button>
                        </div>

                        <template x-if="mainZone.background_actif">
                            <div class="overflow-hidden rounded border border-slate-200">
                                <img :src="mainZone.background_actif" alt="Background" class="h-16 w-full object-cover" />
                            </div>
                        </template>
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
                        <label class="block text-slate-700 text-xs font-semibold uppercase tracking-wide">Histoires</label>
                        <button type="button" @click="openHistoireForm(null)"
                                class="flex items-center gap-1 rounded border border-slate-300 bg-white px-2 py-1 text-[11px] font-bold text-slate-700 hover:bg-slate-100">
                            <span>+</span> Ajouter
                        </button>
                    </div>

                    <template x-if="histoiresError">
                        <div class="mb-2 rounded border border-red-300 bg-red-50 px-2 py-1 text-xs text-red-700" x-text="histoiresError"></div>
                    </template>

                    <div class="space-y-1.5">
                        <template x-for="(histoire, index) in histoires" :key="`histoire-sidebar-${index}`">
                            <div class="flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-2 py-1.5">
                                <span class="text-[11px] font-semibold text-slate-700 flex-1 truncate"
                                      x-text="histoire.titre_histoire || `Histoire ${index + 1}`"></span>
                                <button type="button" @click="moveHistoireUp(index)" class="shrink-0 rounded px-1 text-slate-400 hover:text-slate-700 text-xs">↑</button>
                                <button type="button" @click="moveHistoireDown(index)" class="shrink-0 rounded px-1 text-slate-400 hover:text-slate-700 text-xs">↓</button>
                                <button type="button" @click="openHistoireForm(index)" class="shrink-0 rounded px-1 text-slate-400 hover:text-slate-700 text-xs">✎</button>
                                <button type="button" @click="removeHistoire(index)" class="shrink-0 rounded px-1 text-slate-400 hover:text-red-500 text-xs">×</button>
                            </div>
                        </template>

                        <template x-if="!histoires.length">
                            <p class="text-[11px] text-slate-400 italic">Aucune histoire.</p>
                        </template>
                    </div>

                    <div class="mt-2">
                        <button type="button" @click="saveHistoires()"
                                class="w-full rounded border border-blue-600 bg-blue-600 py-2 text-xs font-semibold text-white hover:bg-blue-500 transition-colors">
                            Enregistrer les histoires
                        </button>
                    </div>
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
                                :disabled="armes.length >= 6"
                                class="w-full rounded border border-slate-300 py-2 text-sm text-slate-800 hover:bg-slate-100 transition-colors disabled:cursor-not-allowed disabled:opacity-50">
                            + Ajouter
                        </button>
                    </div>
                </div>

                <div class="rounded border border-slate-300 bg-slate-50 p-3">
                    <div class="flex items-center justify-between mb-2">
                        <div class="text-xs font-semibold uppercase tracking-wide text-slate-700">Artefacts</div>
                        <button type="button" @click="showArtefactManager = true"
                                class="rounded border border-slate-300 bg-white px-2 py-1 text-[11px] text-slate-700 hover:bg-slate-100">
                            Gérer
                        </button>
                    </div>
                    <template x-if="artefactsError">
                        <div class="mb-2 rounded border border-red-300 bg-red-50 px-2 py-1 text-xs text-red-700" x-text="artefactsError"></div>
                    </template>
                    <template x-if="!artefactBuilds.length">
                        <p class="text-xs text-slate-600 italic">Aucun build artefact recommandé.</p>
                    </template>
                    <div class="space-y-2" x-show="artefactBuilds.length">
                        <template x-for="(build, index) in artefactBuilds" :key="`artefact-side-${build.id_build || index}`">
                            <div class="rounded-lg border border-slate-200 bg-white px-2 py-2">
                                <div class="flex items-center justify-between gap-2">
                                    <span class="text-[11px] font-semibold text-slate-700" x-text="`Build ${index + 1}`"></span>
                                    <span class="rounded-full bg-amber-100 px-2 py-0.5 text-[10px] font-bold text-amber-800" x-text="artefactBuildLabel(build)"></span>
                                </div>
                                <div class="mt-1 text-[11px] text-slate-600" x-text="build.artefact1_nom || 'Set principal manquant'"></div>
                                <div class="mt-0.5 text-[10px] text-slate-500" x-show="build.artefact1_id" x-text="artefactRarityForId(build.artefact1_id)"></div>
                                <template x-if="build.pieces_1 === 2 && build.artefact2_nom">
                                    <div>
                                        <div class="text-[11px] text-slate-500" x-text="build.artefact2_nom"></div>
                                        <div class="mt-0.5 text-[10px] text-slate-500" x-show="build.artefact2_id" x-text="artefactRarityForId(build.artefact2_id)"></div>
                                    </div>
                                </template>
                            </div>
                        </template>
                    </div>
                </div>

                <hr class="border-slate-300" />

                <div>
                    <div class="flex items-center justify-between mb-2">
                        <label class="block text-slate-700 text-xs font-semibold uppercase tracking-wide">Constellations</label>
                        <button type="button" @click="constellationWizardStep = 0; showConstellationsModal = true"
                                class="rounded border border-slate-300 bg-white px-2 py-1 text-[11px] text-slate-700 hover:bg-slate-100">
                            Éditer les constellations
                        </button>
                    </div>

                    <template x-if="constellationsError">
                        <div class="mb-2 rounded border border-red-300 bg-red-50 px-2 py-1 text-xs text-red-700" x-text="constellationsError"></div>
                    </template>
                </div>

                <hr class="border-slate-300" />

                <div id="constellation-map" class="th-const-map-shell">
                    <div class="mb-2 flex items-center justify-between gap-2">
                        <div>
                            <div class="text-xs font-semibold uppercase tracking-wide text-slate-700">Carte constellation</div>
                            <div class="text-[11px] text-slate-600">Image de fond + placement C1 a C6</div>
                        </div>
                        <button type="button"
                                class="rounded border border-slate-300 bg-white px-2 py-1 text-[11px] text-slate-700 hover:bg-slate-100"
                                @click="openConstellationMapModal()">
                            Mise en place des points
                        </button>
                    </div>

                    <form enctype="multipart/form-data"
                          class="space-y-2"
                          @submit.prevent="submitConstellationMapAjax($event)">
                        <input type="hidden" name="positions_const" :value="constellationMapPositionsJson">

                           <label class="th-const-map-dropzone">
                            Drop image ici ou clique pour uploader
                            <input type="file"
                                x-ref="constellationMapUploadInput"
                                name="constellation_map_image"
                                accept="image/*"
                                @change="previewConstellationMapImage($event)" />
                           </label>

                        <div class="th-const-map-canvas"
                             x-ref="constellationMapCanvas">
                            <div class="th-const-map-media" :style="mapMediaStyle('constellationMapCanvas')">
                                <img :src="constellationMapImage || '{{ asset('images/placeholder.svg') }}'"
                                     alt="Carte constellation"
                                     @load="updateConstellationMapNaturalSize($event)">

                                <template x-for="(line, idx) in constellationMapLines" :key="`line-mini-${idx}`">
                                    <template x-if="lineIsValid(line)">
                                        <div class="th-const-map-line" :style="mapLineStyle(line, 'constellationMapCanvas')"></div>
                                    </template>
                                </template>

                                <template x-for="index in [1,2,3,4,5,6]" :key="`map-point-${index}`">
                                    <template x-if="constellationMapPositions[String(index)]">
                                        <div class="th-const-map-point"
                                             :class="selectedMapPoint === index ? 'is-selected' : ''"
                                             :style="mapPointStyle(index)">
                                            <span x-text="index"></span>
                                        </div>
                                    </template>
                                </template>
                            </div>
                        </div>

                        <div class="rounded border border-slate-200 bg-white px-2 py-1 text-[11px] text-slate-700">
                            Apercu statique dans la sidebar. Utilise la pop-up pour placer les points et les lignes.
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

                        <button type="submit"
                                class="w-full rounded border border-blue-600 bg-blue-600 px-2 py-1.5 text-xs font-semibold text-white hover:bg-blue-500">
                            Enregistrer carte constellation
                        </button>
                    </form>

                    <template x-if="showConstellationMapModal">
                        <div class="th-const-map-modal-overlay" @click.self="closeConstellationMapModal()">
                            <div class="th-const-map-modal">
                                <div class="mb-3 flex items-center justify-between gap-2">
                                    <div>
                                        <div class="text-sm font-semibold text-slate-900">Editeur carte constellation</div>
                                        <div class="text-xs text-slate-600">Place les points avec precision puis relie-les en mode ligne</div>
                                    </div>
                                    <button type="button" class="rounded border border-slate-300 bg-white px-2 py-1 text-xs text-slate-700" @click="closeConstellationMapModal()">Fermer</button>
                                </div>

                                <div class="mb-3 flex items-center gap-2">
                                    <button type="button" class="th-const-mode-btn" :class="mapEditorMode === 'point' ? 'is-active' : ''" @click="setMapEditorMode('point')">Mode point</button>
                                    <button type="button" class="th-const-mode-btn" :class="mapEditorMode === 'line' ? 'is-active' : ''" @click="setMapEditorMode('line')">Mode ligne</button>
                                    <span class="text-xs text-slate-600">Prochain : <span class="font-semibold" x-text="nextMapPointLabel"></span></span>
                                </div>

                                <div class="th-const-map-canvas th-const-map-canvas--modal"
                                     x-ref="constellationMapModalCanvas"
                                     >
                                    <div class="th-const-map-media"
                                         :style="mapMediaStyle('constellationMapModalCanvas')"
                                         @click="onConstellationMapCanvasClick($event)">
                                        <img :src="constellationMapImage || '{{ asset('images/placeholder.svg') }}'"
                                             alt="Carte constellation"
                                             @load="updateConstellationMapNaturalSize($event)">

                                        <template x-for="(line, idx) in constellationMapLines" :key="`line-modal-${idx}`">
                                            <template x-if="lineIsValid(line)">
                                                <div class="th-const-map-line" :style="mapLineStyle(line, 'constellationMapModalCanvas')"></div>
                                            </template>
                                        </template>

                                        <template x-for="index in [1,2,3,4,5,6]" :key="`modal-point-${index}`">
                                            <template x-if="constellationMapPositions[String(index)]">
                                                <button type="button"
                                                        class="th-const-map-point"
                                                        :class="selectedMapPoint === index || (mapEditorMode === 'line' && lineDraftStart === index) ? 'is-selected' : ''"
                                                        :style="mapPointStyle(index)"
                                                        @click.stop="onConstellationPointClick(index)">
                                                    <span x-text="index"></span>
                                                    <span class="th-const-map-remove" @click.stop="clearMapPoint(index)">x</span>
                                                </button>
                                            </template>
                                        </template>
                                    </div>
                                </div>

                                <div class="mt-3 rounded border border-slate-200 bg-white px-2 py-2">
                                    <div class="mb-1 text-xs font-semibold text-slate-700">Lignes enregistrées</div>
                                    <template x-if="!constellationMapLines.length">
                                        <p class="text-xs text-slate-500 italic">Aucune ligne.</p>
                                    </template>
                                    <template x-if="constellationMapLines.length">
                                        <div class="space-y-1">
                                            <template x-for="(line, idx) in constellationMapLines" :key="`line-row-${idx}`">
                                                <div class="flex items-center justify-between text-xs text-slate-700">
                                                    <span x-text="`C${line.from} -> C${line.to}`"></span>
                                                    <button type="button" class="rounded border border-red-300 bg-white px-1.5 py-0.5 text-[11px] text-red-600" @click="removeMapLine(idx)">x</button>
                                                </div>
                                            </template>
                                        </div>
                                    </template>
                                </div>

                                <div class="mt-3 space-y-1">
                                    <label class="block text-[11px] font-semibold text-slate-700">JSON (lecture seule)</label>
                                    <textarea readonly rows="6"
                                              class="w-full rounded border border-slate-300 bg-slate-100 px-2 py-1 text-[11px] text-slate-700"
                                              :value="constellationMapPositionsPretty"></textarea>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>

                <hr class="border-slate-300" />

                {{-- ── Aptitudes / Compétences ── --}}
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <label class="block text-slate-700 text-xs font-semibold uppercase tracking-wide">Compétences</label>
                        <button type="button" @click="openAptitudeForm(null)"
                                class="flex items-center gap-1 rounded border border-slate-300 bg-white px-2 py-1 text-[11px] font-bold text-slate-700 hover:bg-slate-100">
                            <span>+</span> Ajouter
                        </button>
                    </div>
                    <template x-if="!aptitudes.length">
                        <p class="text-[11px] text-slate-400 italic">Aucune compétence.</p>
                    </template>
                    <div class="mt-1 space-y-1">
                        <template x-for="(apt, idx) in aptitudes" :key="`apt-sb-${idx}`">
                            <div class="flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-2 py-1.5">
                                <span class="flex-1 truncate text-[11px] font-semibold text-slate-700" x-text="apt.titre_apti || 'Sans titre'"></span>
                                <button type="button" @click="openAptitudeForm(idx)" title="Modifier"
                                        class="shrink-0 rounded px-1 text-slate-400 hover:text-slate-700 transition-colors text-xs">✎</button>
                                <button type="button" @click="removeAptitude(idx)" title="Supprimer"
                                        class="shrink-0 rounded px-1 text-slate-400 hover:text-red-500 transition-colors text-xs">&times;</button>
                            </div>
                        </template>
                    </div>
                </div>

                <hr class="border-slate-300" />

                <div>
                    <div class="flex items-center justify-between mb-2">
                        <label class="block text-slate-700 text-xs font-semibold uppercase tracking-wide">Compositions d'équipe</label>
                        <button type="button" @click="openTeamManager()"
                                class="flex items-center gap-1 rounded border border-slate-300 bg-white px-2 py-1 text-[11px] font-bold text-slate-700 hover:bg-slate-100">
                            <span>◎</span> Gérer
                        </button>
                    </div>
                    <template x-if="!teams.length">
                        <p class="text-[11px] text-slate-400 italic">Aucune team. Ouvre le gestionnaire pour créer un slot de réaction.</p>
                    </template>
                    <div class="mt-1 space-y-1" x-show="teams.length">
                        <template x-for="group in teamGroups" :key="`team-sb-group-${group.key}`">
                            <div class="flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-2 py-1.5">
                                <span class="flex-1 truncate text-[11px] font-semibold text-slate-700"
                                      x-text="`${group.reaction} · ${group.teams.length} team(s)`"></span>
                                <button type="button" @click="openTeamManager()" title="Gérer"
                                        class="shrink-0 rounded px-1 text-slate-400 hover:text-slate-700 transition-colors text-xs">✎</button>
                            </div>
                        </template>
                    </div>
                </div>

                </div>
            </aside>

            <template x-if="teamManagerOpen">
                <div class="th-const-edit-overlay" @click.self="teamManagerOpen = false">
                    <div class="th-apt-single-modal" style="width:min(980px,98vw)">
                        <div class="flex items-center justify-between gap-3 pb-3 border-b border-slate-200">
                            <div>
                                <div class="text-base font-bold text-slate-900">Gestion des compositions d'equipe</div>
                                <div class="text-[11px] text-slate-400">D'abord les slots de reaction, puis les teams a l'interieur.</div>
                            </div>
                            <div class="flex items-center gap-2">
                                <button type="button" @click="openReactionSlotPicker()"
                                        class="rounded-lg border border-indigo-500 bg-indigo-500 px-3 py-1.5 text-xs font-semibold text-white hover:bg-indigo-400">+ Slot reaction</button>
                                <button type="button" @click="teamManagerOpen = false"
                                        class="rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-100">Fermer</button>
                            </div>
                        </div>

                        <template x-if="!teamReactionSlots.length">
                            <div class="mt-6 rounded-xl border border-dashed border-slate-300 bg-slate-50 px-4 py-8 text-center">
                                <div class="text-sm font-semibold text-slate-700">Aucun slot de reaction</div>
                                <div class="mt-1 text-xs text-slate-400">Ajoute un slot de reaction pour pouvoir y mettre des teams.</div>
                                <button type="button" @click="openReactionSlotPicker()"
                                        class="mt-4 rounded-lg border border-indigo-500 bg-indigo-500 px-3 py-1.5 text-xs font-semibold text-white hover:bg-indigo-400">+ Ajouter un slot</button>
                            </div>
                        </template>

                        <div class="mt-4 grid grid-cols-1 gap-4 lg:grid-cols-2" x-show="teamReactionSlots.length">
                            <template x-for="slot in teamReactionSlots" :key="`reaction-slot-${slot.nom_reaction}`">
                                <div class="rounded-2xl border border-slate-200 bg-white p-3 shadow-sm">
                                    <div class="flex items-start gap-3">
                                        <div class="flex h-14 w-14 shrink-0 items-center justify-center overflow-hidden rounded-2xl border border-slate-200 bg-slate-50">
                                            <img :src="slot.icon || '{{ asset('images/placeholder.svg') }}'" :alt="slot.nom_reaction" class="h-10 w-10 object-contain" />
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <div class="text-sm font-bold text-slate-900" x-text="slot.nom_reaction"></div>
                                            <div class="mt-0.5 text-[11px] text-slate-400" x-text="`${teamsForReaction(slot.nom_reaction).length} team(s)`"></div>
                                        </div>
                                        <button type="button" @click="removeReactionSlot(slot.nom_reaction)"
                                                class="rounded px-1 text-slate-300 hover:text-red-500">&times;</button>
                                    </div>

                                    <div class="mt-3 flex items-center gap-2">
                                        <button type="button" @click="openTeamForm(null, slot.nom_reaction)"
                                                class="rounded-lg border border-slate-300 bg-white px-2.5 py-1 text-[11px] font-semibold text-slate-700 hover:bg-slate-100">+ Ajouter une team</button>
                                    </div>

                                    <div class="mt-3 space-y-2">
                                        <template x-if="!teamsForReaction(slot.nom_reaction).length">
                                            <div class="rounded-xl border border-dashed border-slate-200 bg-slate-50 px-3 py-4 text-center text-[11px] italic text-slate-400">Aucune team dans ce slot.</div>
                                        </template>
                                        <template x-for="team in teamsForReaction(slot.nom_reaction)" :key="`reaction-team-${slot.nom_reaction}-${team.id_team}`">
                                            <div class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2">
                                                <div class="flex items-center gap-2">
                                                    <span class="rounded-full px-2 py-0.5 text-[10px] font-bold"
                                                          :class="team.tag === 'recommended' ? 'bg-emerald-100 text-emerald-700' : 'bg-sky-100 text-sky-700'"
                                                          x-text="team.tag === 'recommended' ? 'Recommended' : 'F2P'"></span>
                                                    <div class="flex -space-x-2">
                                                        <template x-for="member in sortedMembers(team)" :key="`reaction-team-member-${team.id_team}-${member.slot}`">
                                                            <img :src="member.icon || '{{ asset('images/placeholder.svg') }}'" :alt="member.nom" class="h-7 w-7 rounded-full border-2 border-white object-cover shadow-sm" />
                                                        </template>
                                                    </div>
                                                    <div class="ml-auto flex items-center gap-2">
                                                        <button type="button" @click="openTeamForm(team, slot.nom_reaction)" class="text-xs text-slate-400 hover:text-slate-700">✎</button>
                                                        <button type="button" @click="deleteTeam(team.id_team)" class="text-xs text-slate-400 hover:text-red-500">×</button>
                                                    </div>
                                                </div>
                                                <div class="mt-2 rounded-lg border border-slate-200 bg-white px-2 py-1.5 text-[11px] text-slate-600" x-text="team.rotation || 'Rotation non renseignée.'"></div>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
            </template>

            <template x-if="reactionSlotPickerOpen">
                <div class="th-const-edit-overlay" @click.self="closeReactionSlotPicker()">
                    <div class="th-apt-single-modal" style="width:min(560px,96vw)">
                        <div class="flex items-center justify-between gap-3 pb-3 border-b border-slate-200">
                            <div class="text-base font-bold text-slate-900">Ajouter un slot de reaction</div>
                            <button type="button" @click="closeReactionSlotPicker()"
                                    class="rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-100">Fermer</button>
                        </div>
                        <template x-if="!availableReactionSlots.length">
                            <div class="mt-6 py-6 text-center text-sm italic text-slate-400">Toutes les reactions sont deja ajoutees.</div>
                        </template>
                        <div class="mt-4 grid grid-cols-2 gap-3 sm:grid-cols-3" x-show="availableReactionSlots.length">
                            <template x-for="reaction in availableReactionSlots" :key="`reaction-slot-pick-${reaction.id_reaction}`">
                                <button type="button" @click="addReactionSlot(reaction.nom_reaction)"
                                        class="rounded-2xl border border-slate-200 bg-white p-3 text-center transition hover:border-indigo-400 hover:bg-indigo-50">
                                    <div class="mx-auto flex h-14 w-14 items-center justify-center overflow-hidden rounded-2xl border border-slate-200 bg-slate-50">
                                        <img :src="reaction.icon || '{{ asset('images/placeholder.svg') }}'" :alt="reaction.nom_reaction" class="h-10 w-10 object-contain" />
                                    </div>
                                    <div class="mt-2 text-[11px] font-semibold text-slate-700" x-text="reaction.nom_reaction"></div>
                                </button>
                            </template>
                        </div>
                    </div>
                </div>
            </template>

            <template x-if="teamFormOpen">
                <div class="th-const-edit-overlay" @click.self="closeTeamForm()">
                    <div class="th-apt-single-modal" style="width:min(900px,98vw)">
                        <div class="flex items-center justify-between gap-3 pb-3 border-b border-slate-200">
                            <div class="text-base font-bold text-slate-900" x-text="teamEditingId ? 'Modifier composition' : 'Nouvelle composition'"></div>
                            <button type="button" @click="teamFormOpen = false"
                                    class="rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-100">Fermer</button>
                        </div>

                        <template x-if="teamError">
                            <div class="mt-3 rounded border border-red-300 bg-red-50 px-3 py-2 text-sm text-red-700" x-text="teamError"></div>
                        </template>

                        <div class="mt-4 rounded-2xl border border-indigo-100 bg-indigo-50 px-3 py-2">
                            <div class="text-[10px] font-bold uppercase tracking-wide text-indigo-500">Reaction</div>
                            <div class="mt-0.5 flex items-center gap-2">
                                <img :src="reactionMeta(teamForm.type_reaction)?.icon || '{{ asset('images/placeholder.svg') }}'"
                                     :alt="teamForm.type_reaction || 'Reaction'"
                                     class="h-8 w-8 rounded-xl border border-indigo-100 bg-white object-contain" />
                                <div class="text-sm font-bold text-slate-800" x-text="teamForm.type_reaction || 'Aucune reaction' "></div>
                            </div>
                        </div>

                        <div class="mt-5">
                            <div class="mb-3 flex items-center gap-2">
                                <span class="text-xs font-bold text-slate-700">Type de team</span>
                                <div class="ml-auto flex items-center gap-2">
                                    <button type="button" @click="teamForm.tag = 'recommended'"
                                            :class="teamForm.tag === 'recommended' ? 'border-emerald-600 bg-emerald-600 text-white' : 'border-emerald-200 bg-white text-emerald-700'"
                                            class="rounded-full border px-3 py-1 text-[11px] font-semibold">Recommended</button>
                                    <button type="button" @click="teamForm.tag = 'f2p'"
                                            :class="teamForm.tag === 'f2p' ? 'border-sky-600 bg-sky-600 text-white' : 'border-sky-200 bg-white text-sky-700'"
                                            class="rounded-full border px-3 py-1 text-[11px] font-semibold">F2P</button>
                                </div>
                            </div>

                            <div class="mb-4">
                                <div class="mb-3 flex items-center justify-between">
                                    <label class="text-[11px] font-bold uppercase tracking-wide text-slate-500">Constructeur de rotation</label>
                                    <template x-if="allTeamSlotsFilledValidation()">
                                        <button type="button" @click="loadTeamAptitudes()" :disabled="teamAptitudesLoading"
                                                class="rounded-lg border border-indigo-300 bg-indigo-50 px-2.5 py-1 text-[11px] font-semibold text-indigo-600 hover:bg-indigo-100 disabled:opacity-50">
                                            <span x-show="!teamAptitudesLoading">Charger aptitudes</span>
                                            <span x-show="teamAptitudesLoading">Chargement...</span>
                                        </button>
                                    </template>
                                </div>

                                {{-- Groupe d'aptitudes par perso --}}
                                <template x-if="teamConstructorAptitudes.length > 0">
                                    <div class="space-y-3 rounded-lg border border-slate-200 bg-slate-50 p-3">
                                        <template x-for="member in teamConstructorAptitudes" :key="`apt-member-${member.slot}`">
                                            <div>
                                                <div class="mb-2 flex items-center gap-2">
                                                    <img :src="member.icon_perso || '{{ asset('images/placeholder.svg') }}'"
                                                         :alt="member.nom_perso"
                                                         class="h-6 w-6 rounded-full border border-slate-300 object-cover" />
                                                    <span class="text-[11px] font-bold text-slate-700" x-text="member.nom_perso"></span>
                                                </div>
                                                <div class="flex flex-wrap gap-2">
                                                    <template x-for="apt in member.aptitudes" :key="`apt-pick-${apt.id_aptitude}`">
                                                        <button type="button" @click="addToRotationSequence(apt)"
                                                                :title="`${apt.titre} (${apt.type})`"
                                                                class="relative cursor-pointer transition-transform hover:scale-110">
                                                            <img :src="apt.icon || '{{ asset('images/placeholder.svg') }}'"
                                                                 :alt="apt.titre"
                                                                 class="h-10 w-10 rounded-lg border-2 border-indigo-300 object-cover hover:border-indigo-600" />
                                                        </button>
                                                    </template>
                                                </div>
                                            </div>
                                        </template>
                                    </div>
                                </template>

                                {{-- Séquence construite --}}
                                <div class="mt-3 rounded-lg border border-slate-200 bg-white p-3">
                                    <div class="mb-2 flex items-center justify-between">
                                        <span class="text-[11px] font-bold text-slate-700">Séquence</span>
                                        <template x-if="rotationSequence.length > 0">
                                            <button type="button" @click="rotationSequence = []; teamForm.rotation = JSON.stringify(rotationSequence)"
                                                    class="rounded-lg border border-red-300 bg-red-50 px-2 py-1 text-[10px] font-semibold text-red-600 hover:bg-red-100">Reset</button>
                                        </template>
                                    </div>
                                    <template x-if="rotationSequence.length === 0">
                                        <div class="text-center text-[11px] italic text-slate-400 py-4">Aucune compétence ajoutée</div>
                                    </template>
                                    <template x-if="rotationSequence.length > 0">
                                        <div class="flex flex-wrap gap-2">
                                            <template x-for="(apt, idx) in rotationSequence" :key="`seq-${idx}`">
                                                <div class="group relative">
                                                    <button type="button" @click="removeFromRotationSequence(idx)"
                                                            class="absolute -right-2 -top-2 z-10 hidden h-5 w-5 items-center justify-center rounded-full bg-red-500 text-white text-[10px] font-bold group-hover:flex">×</button>
                                                    <img :src="apt.icon || '{{ asset('images/placeholder.svg') }}'"
                                                         :alt="`${apt.nom_perso} - ${apt.titre}`"
                                                         :title="`${apt.nom_perso} - ${apt.titre}`"
                                                         class="h-10 w-10 rounded-lg border-2 border-emerald-400 object-cover cursor-pointer" />
                                                </div>
                                            </template>
                                        </div>
                                        <button type="button" @click="saveRotationSequence()" :disabled="teamRotationSaving"
                                                class="mt-3 w-full rounded-lg border border-emerald-500 bg-emerald-500 px-3 py-2 text-[11px] font-semibold text-white hover:bg-emerald-600 disabled:opacity-60">
                                            <span x-show="!teamRotationSaving">Enregistrer séquence</span>
                                            <span x-show="teamRotationSaving">Enregistrement...</span>
                                        </button>
                                    </template>
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-4 md:grid-cols-4">
                                <template x-for="slot in [1,2,3,4]" :key="`slot-col-${slot}`">
                                    <div class="flex flex-col gap-1.5">
                                        <div class="text-[10px] font-bold uppercase tracking-wide text-slate-400" x-text="`Slot ${slot}`"></div>

                                        {{-- Slot principal --}}
                                        <div class="group relative">
                                            <template x-if="!teamMemberForSlot(slot)">
                                                <button type="button" @click="openSlotPicker(slot, false)"
                                                        class="flex h-24 w-full flex-col items-center justify-center rounded-xl border-2 border-dashed border-slate-300 bg-slate-50 text-slate-400 transition hover:border-indigo-400 hover:bg-indigo-50 hover:text-indigo-500">
                                                    <svg class="mb-1 h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                                    <span class="text-[10px] font-semibold">Ajouter</span>
                                                </button>
                                            </template>
                                            <template x-if="teamMemberForSlot(slot)">
                                                <div class="relative rounded-xl border-2 border-indigo-300 bg-white p-2 text-center">
                                                    <button type="button" @click="clearTeamMember(slot)"
                                                            class="absolute -right-1.5 -top-1.5 z-10 hidden h-5 w-5 items-center justify-center rounded-full bg-red-500 text-[10px] font-bold text-white shadow group-hover:flex">×</button>
                                                    <button type="button" @click="openSlotPicker(slot, false)" class="w-full">
                                                        <img :src="teamMemberForSlot(slot)?.icon || '{{ asset('images/placeholder.svg') }}'"
                                                             :alt="teamMemberForSlot(slot)?.nom"
                                                             class="mx-auto h-12 w-12 rounded-full border border-slate-200 object-cover" />
                                                        <div class="mt-1 truncate text-[10px] font-bold text-slate-800" x-text="teamMemberForSlot(slot)?.nom"></div>
                                                        <div class="truncate text-[9px] text-slate-400" x-text="teamMemberForSlot(slot)?.element"></div>
                                                    </button>
                                                </div>
                                            </template>
                                        </div>

                                        {{-- Rôle override --}}
                                        <template x-if="teamMemberForSlot(slot)">
                                            <input type="text"
                                                   :value="teamForm.membres[slot-1]?.role_override || ''"
                                                   @input="setTeamMemberRole(slot, $event.target.value)"
                                                   class="rounded border border-slate-200 bg-white px-2 py-1 text-[10px] text-slate-600 placeholder-slate-300 focus:border-indigo-300 focus:outline-none"
                                                   placeholder="Rôle (optionnel)" />
                                        </template>

                                        {{-- Alts --}}
                                        <div class="mt-0.5 space-y-1">
                                            <template x-for="r in teamRemplacantsBySlot(slot)" :key="`alt-chip-${slot}-${r.id_perso}`">
                                                <div class="flex items-center gap-1.5 rounded-lg border border-amber-200 bg-amber-50 px-1.5 py-1">
                                                    <img :src="r.icon || '{{ asset('images/placeholder.svg') }}'"
                                                         class="h-5 w-5 shrink-0 rounded-full border border-amber-200 object-cover" />
                                                    <span class="min-w-0 flex-1 truncate text-[10px] font-semibold text-amber-800" x-text="r.nom"></span>
                                                    <input type="text" :value="r.role_override || ''"
                                                           @input="setRemplacantRole(slot, r.id_perso, $event.target.value)"
                                                           class="min-w-0 w-20 rounded border border-amber-200 bg-white px-1 py-0.5 text-[9px] text-amber-700 placeholder-amber-300 focus:border-amber-300 focus:outline-none"
                                                           placeholder="Role" />
                                                    <button type="button" @click="removeRemplacant(slot, r.id_perso)"
                                                            class="shrink-0 leading-none text-[12px] text-red-400 hover:text-red-600">×</button>
                                                </div>
                                            </template>
                                            <button type="button" @click="openSlotPicker(slot, true)"
                                                    :disabled="!teamMemberForSlot(slot)"
                                                    class="w-full rounded-lg border border-dashed border-amber-300 bg-amber-50 py-0.5 text-[10px] font-semibold text-amber-600 hover:bg-amber-100 disabled:cursor-not-allowed disabled:opacity-40">
                                                + Alt
                                            </button>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <div class="mt-5 flex items-center justify-end gap-2 border-t border-slate-200 pt-4">
                            <button type="button" @click="closeTeamForm()"
                                    class="rounded border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100">Annuler</button>
                            <button type="button" @click="saveTeam()" :disabled="teamSaving"
                                    class="rounded border border-indigo-500 bg-indigo-500 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-400 disabled:opacity-60">
                                <span x-show="!teamSaving">Enregistrer</span>
                                <span x-show="teamSaving">Enregistrement...</span>
                            </button>
                        </div>
                    </div>
                </div>
            </template>


            {{-- ============ MODAL WIZARD CONSTELLATIONS ============ --}}


            {{-- ============ MODAL WIZARD CONSTELLATIONS ============ --}}

                {{-- ============ MODAL PICKER PERSONNAGE TEAM ============ --}}
                <template x-if="teamSlotPickerOpen !== null">
                    <div class="th-const-edit-overlay" @click.self="closeSlotPicker()">
                        <div class="th-apt-single-modal" style="width:min(640px,98vw)">
                            <div class="flex items-center justify-between gap-3 pb-3 border-b border-slate-200">
                                <div class="text-sm font-bold text-slate-900"
                                     x-text="teamSlotPickerOpen?.isAlt
                                         ? `Choisir un alt — Slot ${teamSlotPickerOpen.slot}`
                                         : `Choisir un personnage — Slot ${teamSlotPickerOpen.slot}`"></div>
                                <button type="button" @click="closeSlotPicker()"
                                        class="rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-100">Fermer</button>
                            </div>
                            <div class="mt-3">
                                <input x-model="teamSlotPickerSearch"
                                       type="text"
                                       placeholder="Rechercher un personnage..."
                                       class="w-full rounded border border-slate-300 bg-white px-3 py-2 text-sm text-slate-800 placeholder-slate-400 focus:border-indigo-400 focus:outline-none"
                                       @keydown.escape="closeSlotPicker()" />
                            </div>
                            <template x-if="filteredPickerPool.length === 0">
                                <div class="mt-6 py-6 text-center text-sm italic text-slate-400">Aucun personnage trouvé</div>
                            </template>
                            <div class="mt-3 grid grid-cols-4 gap-2 overflow-y-auto sm:grid-cols-5 md:grid-cols-6" style="max-height:55vh">
                                <template x-for="p in filteredPickerPool" :key="`picker-card-${p.id_perso}`">
                                    <button type="button" @click="selectFromPicker(p)"
                                            class="flex flex-col items-center rounded-xl border border-slate-200 bg-white p-2 transition hover:border-indigo-400 hover:bg-indigo-50">
                                        <img :src="p.icon || '{{ asset('images/placeholder.svg') }}'"
                                             :alt="p.nom"
                                             class="h-11 w-11 rounded-full border border-slate-200 object-cover" />
                                        <span class="mt-1 w-full truncate text-center text-[9px] font-bold text-slate-700" x-text="p.nom"></span>
                                        <span class="w-full truncate text-center text-[8px] text-slate-400" x-text="p.element"></span>
                                    </button>
                                </template>
                            </div>
                        </div>
                    </div>
                </template>

            <template x-if="showConstellationsModal">
                <div class="th-const-edit-overlay" @click.self="showConstellationsModal = false">
                    <div class="th-apt-single-modal" style="width:min(560px,97vw)">

                        {{-- En-tête --}}
                        <div class="flex items-center justify-between gap-3 pb-3 border-b border-slate-200">
                            <div class="flex items-center gap-3">
                                {{-- Dots de progression --}}
                                <div class="flex items-center gap-1.5">
                                    <template x-for="i in 6" :key="i">
                                        <button type="button"
                                                @click="constellationWizardStep = i - 1"
                                                :title="`Aller à C${i}`"
                                                class="w-2.5 h-2.5 rounded-full transition-all"
                                                :class="constellationWizardStep === i - 1
                                                    ? 'bg-slate-800 scale-125'
                                                    : (constellationSlots[i-1]?.titre_const ? 'bg-emerald-400' : 'bg-slate-300 hover:bg-slate-400')">
                                        </button>
                                    </template>
                                </div>
                                <div>
                                    <div class="text-base font-bold text-slate-900"
                                         x-text="`Constellation C${constellationWizardStep + 1} / 6`"></div>
                                    <div class="text-[11px] text-slate-400 mt-0.5"
                                         x-text="constellationSlots[constellationWizardStep]?.titre_const || 'Aucun titre'"></div>
                                </div>
                            </div>
                            <div class="flex items-center gap-2 shrink-0">
                                <button type="button"
                                        @click="saveConstellations()"
                                        class="rounded-lg border border-emerald-500 bg-emerald-500 px-3 py-1.5 text-xs font-semibold text-white hover:bg-emerald-400 transition-colors">
                                    Enregistrer tout
                                </button>
                                <button type="button"
                                        @click="showConstellationsModal = false"
                                        class="rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-100 transition-colors">
                                    Fermer
                                </button>
                            </div>
                        </div>

                        <template x-if="constellationsError">
                            <div class="mt-3 rounded border border-red-300 bg-red-50 px-3 py-2 text-sm text-red-700" x-text="constellationsError"></div>
                        </template>

                        {{-- Carte unique pour la constellation courante --}}
                        <template x-if="constellationSlots[constellationWizardStep]">
                        <div class="mt-4 space-y-4">

                            {{-- Nom --}}
                            <div>
                                <label class="block text-[11px] font-semibold text-slate-500 mb-1">
                                    Nom <span class="text-red-400">*</span>
                                </label>
                                <input type="text"
                                       :placeholder="`Nom de C${constellationWizardStep + 1}`"
                                       x-model="constellationSlots[constellationWizardStep].titre_const"
                                       class="w-full rounded border border-slate-300 bg-white px-3 py-1.5 text-sm text-slate-800 focus:border-indigo-400 focus:outline-none" />
                            </div>

                            {{-- Description --}}
                            <div class="relative">
                                <label class="block text-[11px] font-semibold text-slate-500 mb-1">Description</label>
                                <textarea :placeholder="`Description de C${constellationWizardStep + 1}...`"
                                          rows="4"
                                          x-model="constellationSlots[constellationWizardStep].descri_const"
                                          :class="showAptitudePicker && aptitudePickerSlotIndex === constellationWizardStep ? 'border-indigo-400 ring-1 ring-indigo-300' : ''"
                                          @input="handleConstellationDescInput($event, constellationWizardStep)"
                                          @keydown="handleConstellationDescKeydown($event, constellationWizardStep)"
                                          class="w-full rounded border border-slate-300 bg-white px-3 py-1.5 text-sm text-slate-800 focus:border-indigo-400 focus:outline-none resize-none"></textarea>

                                {{-- Menu autocomplete slash commandes --}}
                                <div x-show="slashMenuOpen && slashMenuSlotIndex === constellationWizardStep"
                                     class="mt-1 rounded-lg border border-indigo-500 bg-slate-800 shadow-xl overflow-hidden">
                                    <template x-for="(cmd, cmdIdx) in getSlashCommands()" :key="cmd.value">
                                        <button type="button"
                                                @mousedown.prevent="confirmSlashCommand(cmd, constellationWizardStep)"
                                                :class="cmdIdx === slashMenuSelectedIndex ? 'bg-indigo-600' : 'hover:bg-slate-700'"
                                                class="w-full flex items-center gap-3 px-3 py-2.5 text-left text-sm transition-colors">
                                            <span class="font-mono text-xs flex-shrink-0 w-5 h-5 flex items-center justify-center rounded text-indigo-200"
                                                  :class="cmdIdx === slashMenuSelectedIndex ? 'bg-indigo-500 text-white' : 'bg-slate-700 text-indigo-300'">/</span>
                                            <span class="font-mono text-xs font-semibold flex-shrink-0"
                                                  :class="cmdIdx === slashMenuSelectedIndex ? 'text-white' : 'text-indigo-300'"
                                                  x-text="cmd.label"></span>
                                            <span class="text-xs truncate"
                                                  :class="cmdIdx === slashMenuSelectedIndex ? 'text-indigo-100' : 'text-slate-400'"
                                                  x-text="cmd.description"></span>
                                            <span x-show="cmdIdx === slashMenuSelectedIndex"
                                                  class="ml-auto text-[10px] text-indigo-200 flex-shrink-0 opacity-80 border border-indigo-400 rounded px-1">↵</span>
                                        </button>
                                    </template>
                                    <div x-show="getSlashCommands().length === 0"
                                         class="px-3 py-2 text-xs text-slate-500 italic">Aucune commande trouvée</div>
                                </div>

                                <div class="mt-1 flex items-center gap-1.5"
                                     x-show="!(showAptitudePicker && aptitudePickerSlotIndex === constellationWizardStep) && !(slashMenuOpen && slashMenuSlotIndex === constellationWizardStep)">
                                    <kbd class="inline-flex items-center rounded border border-slate-300 bg-slate-100 px-1 py-0.5 font-mono text-[10px] text-slate-600 shadow-sm">/aptitudes</kbd>
                                    <span class="text-[10px] text-slate-400">pour insérer une compétence</span>
                                </div>
                            </div>

                            {{-- Picker /aptitudes --}}
                            <template x-if="showAptitudePicker && aptitudePickerSlotIndex === constellationWizardStep">
                                <div class="rounded-lg border border-indigo-400 bg-slate-800 p-2 shadow-lg">
                                    <div class="mb-2 flex items-center justify-between">
                                        <div class="flex items-center gap-1.5">
                                            <kbd class="rounded border border-indigo-500 bg-indigo-900 px-1 py-0.5 font-mono text-[10px] text-indigo-300">/aptitudes</kbd>
                                            <span class="text-[11px] font-semibold text-slate-300">Cliquez sur une compétence pour l'insérer</span>
                                        </div>
                                        <button type="button"
                                                @click="showAptitudePicker = false; aptitudePickerSlotIndex = null"
                                                title="Fermer (Échap)"
                                                class="flex h-5 w-5 items-center justify-center rounded text-slate-500 hover:bg-slate-700 hover:text-slate-200 transition-colors text-xs font-bold">✕</button>
                                    </div>
                                    <div class="flex flex-wrap gap-2">
                                        <template x-for="(apt, aptIdx) in aptitudes" :key="apt.id_aptitude">
                                            <button type="button"
                                                    @click="insertAptitudeTag(aptIdx, constellationWizardStep)"
                                                    class="flex flex-col items-center rounded-md border border-transparent p-1.5 transition hover:border-indigo-400 hover:bg-slate-700"
                                                    :title="apt.titre_apti">
                                                <img :src="apt.image_url || '{{ asset('images/placeholder.svg') }}'"
                                                     :alt="apt.titre_apti"
                                                    class="h-10 w-10 object-contain" />
                                                <span class="mt-0.5 line-clamp-2 max-w-[64px] text-center text-[10px] leading-tight text-slate-300"
                                                      x-text="apt.titre_apti"></span>
                                            </button>
                                        </template>
                                    </div>
                                    <p class="mt-2 text-[10px] text-slate-500">Effacez <span class="font-mono text-slate-400">/aptitudes</span> dans le texte pour fermer le picker</p>
                                </div>
                            </template>

                            {{-- Aperçu rendu de la description --}}
                            <template x-if="constellationSlots[constellationWizardStep].descri_const">
                                <div class="mt-1 rounded border border-slate-700 bg-slate-900/60 px-3 py-2">
                                    <div class="mb-1 text-[10px] font-semibold uppercase tracking-wide text-slate-500">Aperçu</div>
                                    <div class="text-sm text-slate-300 leading-relaxed"
                                         x-html="renderDescriConst(constellationSlots[constellationWizardStep].descri_const)"></div>
                                </div>
                            </template>

                            {{-- Image --}}
                            <div>
                                <label class="block text-[11px] font-semibold text-slate-500 mb-1">Image</label>
                                <div class="th-const-edit-img-row">
                                    <img :src="constellationSlots[constellationWizardStep].image_url || '{{ asset('images/placeholder.svg') }}'"
                                         :alt="constellationSlots[constellationWizardStep].titre_const || `C${constellationWizardStep + 1}`" />
                                    <label class="th-const-edit-upload-btn">
                                        📷 Changer l'image
                                        <input type="file" class="hidden" accept="image/*"
                                               @change="uploadConstellationImageSlot($event, constellationWizardStep)" />
                                    </label>
                                </div>
                            </div>

                        </div>
                        </template>

                        {{-- Navigation --}}
                        <div class="mt-5 flex items-center justify-between">
                            {{-- Précédent --}}
                            <button type="button"
                                    @click="constellationWizardStep--"
                                    x-show="constellationWizardStep > 0"
                                    class="flex items-center gap-1 rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100 transition-colors">
                                ← Précédent
                            </button>
                            <div x-show="constellationWizardStep === 0" class="w-1"></div>

                            {{-- Indicateur textuel --}}
                            <span class="text-xs text-slate-400"
                                  x-text="`${constellationSlots.filter(s => s.titre_const).length} / 6 renseignée(s)`"></span>

                            {{-- Suivant / Terminer --}}
                            <template x-if="constellationWizardStep < 5">
                                <button type="button"
                                        @click="constellationWizardStep++"
                                        class="flex items-center gap-1 rounded-lg border border-slate-700 bg-slate-800 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-700 transition-colors">
                                    Suivant →
                                </button>
                            </template>
                            <template x-if="constellationWizardStep === 5">
                                <button type="button"
                                        @click="saveConstellations()"
                                        class="flex items-center gap-1 rounded-lg border border-emerald-500 bg-emerald-500 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-400 transition-colors">
                                    ✓ Enregistrer tout
                                </button>
                            </template>
                        </div>

                    </div>
                </div>
            </template>

            {{-- ============ MODAL AJOUT / ÉDITION HISTOIRE ============ --}}
            <template x-if="histoireFormOpen">
                <div class="th-const-edit-overlay" @click.self="histoireFormOpen = false; closeStoryCommandMenu()">
                    <div class="th-apt-single-modal" style="width:min(980px,98vw)">
                        <div class="flex items-center justify-between gap-4 pb-3 border-b border-slate-200">
                            <div class="text-base font-bold text-slate-900"
                                 x-text="histoireFormIdx === null ? 'Ajouter une histoire' : 'Modifier l\'histoire'"></div>
                            <button type="button" @click="histoireFormOpen = false; closeStoryCommandMenu()"
                                    class="rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-sm font-semibold text-slate-700 hover:bg-slate-100 transition-colors">
                                Annuler
                            </button>
                        </div>

                        <template x-if="histoiresError">
                            <div class="mt-3 rounded border border-red-300 bg-red-50 px-3 py-2 text-sm text-red-700" x-text="histoiresError"></div>
                        </template>

                        <div class="mt-4 space-y-4">
                            <div>
                                <label class="block text-[11px] font-semibold text-slate-500 mb-1">Titre <span class="text-red-400">*</span></label>
                                <input type="text" x-model="histoireFormData.titre_histoire"
                                       placeholder="Titre de l'histoire"
                                       class="w-full rounded border border-slate-300 bg-white px-3 py-2 text-sm text-slate-800 focus:border-indigo-400 focus:outline-none">
                            </div>

                            <div>
                                <div class="mb-1 flex items-center justify-between gap-2">
                                    <label class="block text-[11px] font-semibold text-slate-500">Histoire <span class="text-red-400">*</span></label>
                                    <div class="text-[11px] text-slate-500">Commandes: /aptitudes, /armes, /boss, /monstres</div>
                                </div>
                                <div class="relative">
                                    <textarea x-ref="histoireTextarea"
                                              x-model="histoireFormData.histoire"
                                              @input="onHistoireTextareaInput($event)"
                                              @keydown="onHistoireTextareaKeydown($event)"
                                              rows="12"
                                              placeholder="Écris l'histoire... puis utilise /aptitudes, /armes, /boss, /monstres pour insérer des références"
                                              class="w-full rounded border border-slate-300 bg-white px-3 py-2 text-sm text-slate-800 focus:border-indigo-400 focus:outline-none resize-y"></textarea>

                                    <template x-if="storyCommandMenu.open">
                                        <div class="absolute left-0 right-0 top-[calc(100%+6px)] z-[90] rounded-xl border border-slate-300 bg-white shadow-xl max-h-72 overflow-y-auto">
                                            <template x-for="(option, idx) in getStorySlashCommands()" :key="`story-cmd-${idx}`">
                                                <button type="button"
                                                        @click="confirmStorySlashCommand(option)"
                                                        class="w-full flex items-center gap-2 px-3 py-2 text-left hover:bg-slate-100"
                                                        :class="storyCommandMenu.selectedIndex === idx ? 'bg-slate-100' : ''">
                                                    <template x-if="option.image">
                                                        <img :src="option.image" alt="" class="w-8 h-8 rounded object-cover border border-slate-200" />
                                                    </template>
                                                    <template x-if="!option.image">
                                                        <span class="w-8 h-8 rounded border border-slate-200 bg-slate-50 flex items-center justify-center text-xs text-slate-400">/</span>
                                                    </template>
                                                    <div class="min-w-0">
                                                        <div class="text-sm font-medium text-slate-800 truncate" x-text="option.label"></div>
                                                        <div class="text-[11px] text-slate-500 truncate" x-text="option.meta"></div>
                                                    </div>
                                                </button>
                                            </template>
                                        </div>
                                    </template>
                                </div>

                                <div class="mt-1 flex items-center gap-1.5"
                                     x-show="!storyPickerOpen && !storyCommandMenu.open">
                                    <kbd class="inline-flex items-center rounded border border-slate-300 bg-slate-100 px-1 py-0.5 font-mono text-[10px] text-slate-600 shadow-sm">/aptitudes</kbd>
                                    <kbd class="inline-flex items-center rounded border border-slate-300 bg-slate-100 px-1 py-0.5 font-mono text-[10px] text-slate-600 shadow-sm">/armes</kbd>
                                    <kbd class="inline-flex items-center rounded border border-slate-300 bg-slate-100 px-1 py-0.5 font-mono text-[10px] text-slate-600 shadow-sm">/boss</kbd>
                                    <kbd class="inline-flex items-center rounded border border-slate-300 bg-slate-100 px-1 py-0.5 font-mono text-[10px] text-slate-600 shadow-sm">/monstres</kbd>
                                    <span class="text-[10px] text-slate-400">pour insérer une référence</span>
                                </div>

                                <template x-if="storyPickerOpen">
                                    <div class="mt-2 rounded-lg border border-indigo-400 bg-slate-800 p-2 shadow-lg">
                                        <div class="mb-2 flex items-center justify-between gap-2">
                                            <div class="flex items-center gap-1.5">
                                                <kbd class="rounded border border-indigo-500 bg-indigo-900 px-1 py-0.5 font-mono text-[10px] text-indigo-300" x-text="storyCommandLabel(storyPickerCommand)"></kbd>
                                                <span class="text-[11px] font-semibold text-slate-300">Recherche rapide + clic pour insérer</span>
                                            </div>
                                            <button type="button"
                                                    @click="closeStoryPicker()"
                                                    class="flex h-5 w-5 items-center justify-center rounded text-slate-500 hover:bg-slate-700 hover:text-slate-200 transition-colors text-xs font-bold">✕</button>
                                        </div>

                                        <input type="text"
                                               x-model="storyPickerSearch"
                                               placeholder="Recherche rapide..."
                                               class="mb-2 w-full rounded border border-slate-600 bg-slate-900 px-2 py-1.5 text-xs text-slate-100 placeholder-slate-500 focus:border-indigo-400 focus:outline-none" />

                                        <div class="max-h-52 overflow-y-auto">
                                            <div class="grid grid-cols-1 gap-1">
                                                <template x-for="item in filteredStoryPickerOptions()" :key="`story-picker-${storyPickerCommand}-${item.key}`">
                                                    <button type="button"
                                                            @click="applyStoryPickerItem(item)"
                                                            class="flex items-center gap-2 rounded border border-transparent bg-slate-900/40 px-2 py-1.5 text-left hover:border-indigo-400 hover:bg-slate-700">
                                                        <img :src="item.image || '{{ asset('images/placeholder.svg') }}'" alt="" class="h-8 w-8 rounded object-cover" />
                                                        <span class="truncate text-xs font-medium text-slate-200" x-text="item.label"></span>
                                                    </button>
                                                </template>
                                                <template x-if="filteredStoryPickerOptions().length === 0">
                                                    <div class="px-2 py-2 text-[11px] italic text-slate-400">Aucun résultat</div>
                                                </template>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <div class="mt-5 flex justify-end">
                            <button type="button" @click="saveHistoireForm()"
                                    class="rounded-lg border border-emerald-500 bg-emerald-500 px-5 py-2 text-sm font-bold text-white hover:bg-emerald-400 transition-colors">
                                Enregistrer
                            </button>
                        </div>
                    </div>
                </div>
            </template>

            {{-- ============ MODAL AJOUT / ÉDITION COMPÉTENCE ============ --}}
            <template x-if="aptitudeFormOpen">
                <div class="th-const-edit-overlay" @click.self="aptitudeFormOpen = false">
                    <div class="th-apt-single-modal">

                        {{-- En-tête --}}
                        <div class="flex items-center justify-between gap-4 pb-3 border-b border-slate-200">
                            <div class="text-base font-bold text-slate-900"
                                 x-text="aptitudeFormIdx === null ? 'Ajouter une compétence' : 'Modifier la compétence'"></div>
                            <button type="button" @click="aptitudeFormOpen = false"
                                    class="rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-sm font-semibold text-slate-700 hover:bg-slate-100 transition-colors">
                                Annuler
                            </button>
                        </div>

                        <template x-if="aptitudeFormError">
                            <div class="mt-3 rounded border border-red-300 bg-red-50 px-3 py-2 text-sm text-red-700"
                                 x-text="aptitudeFormError"></div>
                        </template>

                        <div class="mt-4 space-y-4">

                            {{-- Nom --}}
                            <div>
                                <label class="block text-[11px] font-semibold text-slate-500 mb-1">
                                    Nom de la compétence <span class="text-red-400">*</span>
                                </label>
                                <input type="text" x-model="aptitudeFormData.titre_apti"
                                       placeholder="Nom de la compétence"
                                       class="w-full rounded border border-slate-300 bg-white px-3 py-1.5 text-sm text-slate-800 focus:border-indigo-400 focus:outline-none">
                            </div>

                            {{-- Type --}}
                            <div>
                                <label class="block text-[11px] font-semibold text-slate-500 mb-1">
                                    Type <span class="text-red-400">*</span>
                                </label>
                                <div class="th-apt-type-radio">
                                    <template x-for="type in typesApti" :key="type.id">
                                        <button type="button"
                                                :class="aptitudeFormData.fid_TypeApti == type.id ? 'is-active' : ''"
                                                @click="aptitudeFormData.fid_TypeApti = type.id"
                                                x-text="type.libelle"></button>
                                    </template>
                                </div>
                            </div>

                            {{-- Icône --}}
                            <div>
                                <label class="block text-[11px] font-semibold text-slate-500 mb-1">Icône</label>
                                <label class="th-apt-dropzone"
                                       :class="aptitudeFormData._dragging ? 'th-apt-dropzone--over' : ''"
                                       @dragover.prevent="aptitudeFormData._dragging = true"
                                       @dragleave.prevent="aptitudeFormData._dragging = false"
                                       @drop.prevent="aptitudeFormData._dragging = false; handleAptitudeFormImageDrop($event)">
                                    <template x-if="aptitudeFormData.image_url">
                                        <img :src="aptitudeFormData.image_url" class="mx-auto mb-1 h-12 w-12 object-contain rounded" />
                                    </template>
                                    <template x-if="!aptitudeFormData.image_url">
                                        <div class="text-2xl text-slate-300 mb-1">🖼</div>
                                    </template>
                                    <span class="text-[10px] text-slate-400"
                                          x-text="aptitudeFormData.image_url ? 'Changer (drop ou clic)' : 'Drop ou clic pour uploader'"></span>
                                    <input type="file" accept="image/*" class="hidden"
                                           @change="handleAptitudeFormImageFile($event)" />
                                </label>
                            </div>

                            {{-- Description --}}
                            <div>
                                <label class="block text-[11px] font-semibold text-slate-500 mb-1">
                                    Description <span class="font-normal text-slate-400">(optionnel)</span>
                                </label>
                                <textarea x-model="aptitudeFormData.descri_apti"
                                          placeholder="Description de la compétence..."
                                          rows="4"
                                          class="w-full rounded border border-slate-300 bg-white px-3 py-1.5 text-sm text-slate-800 focus:border-indigo-400 focus:outline-none resize-none"></textarea>
                            </div>

                        </div>

                        <div class="mt-5 flex justify-end">
                            <button type="button" @click="saveAptitudeForm()"
                                    :disabled="aptitudeFormSaving"
                                    class="rounded-lg border border-emerald-500 bg-emerald-500 px-5 py-2 text-sm font-bold text-white hover:bg-emerald-400 disabled:opacity-60 transition-colors">
                                <span x-show="!aptitudeFormSaving">Enregistrer</span>
                                <span x-show="aptitudeFormSaving">Enregistrement...</span>
                            </button>
                        </div>

                    </div>
                </div>
            </template>
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

              <div class="character-show-hero mx-6 mb-6"
                  data-element="{{ strtolower($personnage->element?->libelle_element ?? 'geo') }}"
                  :style="heroBackgroundStyle">

                <section class="csh-full relative mx-auto flex items-center justify-center text-center p-4">
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
                    <button x-show="mainZone.videos.length > 1" type="button" @click="prevVideo()" class="csh-video-nav csh-video-nav--prev">&#8249;</button>
                    <button x-show="mainZone.videos.length > 1" type="button" @click="nextVideo()" class="csh-video-nav csh-video-nav--next">&#8250;</button>
                    <div x-show="mainZone.videos.length > 1" class="csh-video-counter">
                        <span x-text="selectedVideoIndex + 1"></span>&thinsp;/&thinsp;<span x-text="mainZone.videos.length"></span>
                    </div>
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

            <section class="csh-preview-panel csh-versatility-panel mx-6 mb-6" style="padding: 1rem 1.15rem;">
                <div class="csh-preview-panel-head" style="padding: 0 0 .75rem 0; border: none;">
                    <div>
                        <div class="csh-preview-panel-title">Versatilité</div>
                        <div class="csh-preview-panel-subtitle">P2W (0) à très versatile (10)</div>
                    </div>
                    <div class="text-xs text-slate-400" x-text="mainZone.versatilite !== null ? mainZone.versatilite + ' / 10' : 'Non renseigné'"></div>
                </div>
                <div class="versatility-bar" style="position:relative; width:100%; max-width:900px; aspect-ratio: 2084 / 754;">
                    <img class="versatility-filler" :src="versatilityBarFiller"
                        :style="`--versatility-empty: ${(100 - ((mainZone.versatilite ?? 0) / 10 * 100)) * 0.77}%`" alt="" />
                    <img class="versatility-frame" :src="versatilityBarFrame" alt="Versatilité" />
                </div>
            </section>

            <section class="csh-preview-table mx-6">
                <div class="csh-preview-panel">
                    <div class="csh-preview-panel-head">
                        <div>
                            <div class="csh-preview-panel-title">Armes</div>
                            <div class="csh-preview-panel-subtitle">Colonne gauche du tableau preview</div>
                        </div>
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
                                    <template x-if="arme.passive_name || arme.passive_desc">
                                        <div class="csh-weapon-tooltip">
                                            <div class="csh-weapon-tooltip-title" x-text="arme.passive_name || arme.nom"></div>
                                            <div class="csh-weapon-tooltip-copy" x-text="arme.passive_desc || 'Aucun passif renseigné.'"></div>
                                        </div>
                                    </template>
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
                    </div>

                    <template x-if="artefactBuilds.length">
                        <div class="csh-preview-artefact-list">
                            <template x-for="(build, index) in artefactBuilds" :key="`preview-build-${build.id_build || index}`">
                                <article class="csh-artefact-item">
                                    <div class="csh-artefact-head">
                                        <div class="csh-artefact-title" x-text="build.artefact2_nom ? `${build.artefact1_nom || 'Set principal'} + ${build.artefact2_nom}` : (build.artefact1_nom || 'Set principal')"></div>
                                        <div class="csh-artefact-piece" x-text="build.artefact2_nom ? '2P + 2P' : `${build.pieces_1}P`"></div>
                                    </div>
                                    <div class="csh-artefact-row">
                                        <div class="csh-artefact-media">
                                            <img :src="build.artefact1_icon || '{{ asset('images/placeholder.svg') }}'" alt="" class="csh-artefact-icon">
                                            <div class="csh-artefact-tooltip" x-show="build.artefact1_bonus_2p || build.artefact1_bonus_4p">
                                                <div class="csh-artefact-tooltip-title" x-text="build.artefact1_nom || 'Set principal'"></div>
                                                <div class="csh-artefact-tooltip-line" x-show="build.artefact1_bonus_2p">
                                                    <span class="csh-artefact-tooltip-badge">2P</span>
                                                    <span class="csh-artefact-tooltip-copy" x-text="build.artefact1_bonus_2p"></span>
                                                </div>
                                                <div class="csh-artefact-tooltip-line" x-show="build.artefact1_bonus_4p">
                                                    <span class="csh-artefact-tooltip-badge">4P</span>
                                                    <span class="csh-artefact-tooltip-copy" x-text="build.artefact1_bonus_4p"></span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="csh-artefact-copy">
                                            <span class="csh-artefact-name" x-text="build.artefact1_nom || 'Artefact principal'"></span>
                                            <span class="csh-artefact-meta" x-text="build.artefact2_nom ? 'Premier set' : 'Set principal'"></span>
                                        </div>
                                    </div>
                                    <template x-if="build.artefact2_nom">
                                        <div class="csh-artefact-row">
                                            <div class="csh-artefact-media">
                                                <img :src="build.artefact2_icon || '{{ asset('images/placeholder.svg') }}'" alt="" class="csh-artefact-icon">
                                                <div class="csh-artefact-tooltip" x-show="build.artefact2_bonus_2p || build.artefact2_bonus_4p">
                                                    <div class="csh-artefact-tooltip-title" x-text="build.artefact2_nom || 'Set secondaire'"></div>
                                                    <div class="csh-artefact-tooltip-line" x-show="build.artefact2_bonus_2p">
                                                        <span class="csh-artefact-tooltip-badge">2P</span>
                                                        <span class="csh-artefact-tooltip-copy" x-text="build.artefact2_bonus_2p"></span>
                                                    </div>
                                                    <div class="csh-artefact-tooltip-line" x-show="build.artefact2_bonus_4p">
                                                        <span class="csh-artefact-tooltip-badge">4P</span>
                                                        <span class="csh-artefact-tooltip-copy" x-text="build.artefact2_bonus_4p"></span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="csh-artefact-copy">
                                                <span class="csh-artefact-name" x-text="build.artefact2_nom"></span>
                                                <span class="csh-artefact-meta">Second set</span>
                                            </div>
                                        </div>
                                    </template>
                                    <template x-if="build.sub_stats && build.sub_stats.length">
                                        <div class="csh-substats-priority">
                                            <div class="csh-substats-priority-title">Sous-stats prioritaires</div>
                                            <template x-for="(stat, sIdx) in build.sub_stats" :key="`substat-${build.id_build || index}-${sIdx}`">
                                                <div class="csh-substats-priority-row">
                                                    <span class="csh-substats-priority-rank" x-text="sIdx + 1"></span>
                                                    <span class="csh-substats-priority-name" x-text="stat"></span>
                                                    <template x-if="sIdx < build.sub_stats.length - 1">
                                                        <span class="csh-substats-priority-arrow">↓</span>
                                                    </template>
                                                </div>
                                            </template>
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

            {{-- ── Compétences preview ── --}}
            <section class="csh-aptitudes-shell mx-6 mb-6">
                <div class="csh-preview-panel-head">
                    <div>
                        <div class="csh-preview-panel-title">Compétences</div>
                        <div class="csh-preview-panel-subtitle">Aptitudes actives et passives</div>
                    </div>
                    <div class="text-xs text-slate-400" x-text="aptitudes.length ? `${aptitudes.length} compétence(s)` : 'Aucune'"></div>
                </div>

                <template x-if="!aptitudes.length">
                    <div class="csh-aptitudes-empty">Aucune compétence enregistrée.</div>
                </template>

                <template x-if="aptitudes.length">
                    <div class="csh-aptitudes-list">
                        <template x-for="(apt, idx) in aptitudes" :key="`prev-apt-${apt.id_aptitude || idx}`">
                            <div class="csh-aptitude-item">
                                <template x-if="apt.image_url">
                                    <img :src="apt.image_url" :alt="apt.titre_apti" class="csh-aptitude-icon" />
                                </template>
                                <template x-if="!apt.image_url">
                                    <div class="csh-aptitude-icon-placeholder">?</div>
                                </template>
                                <div class="csh-aptitude-body">
                                    <div class="csh-aptitude-type"
                                         x-text="typesApti.find(t => t.id === apt.fid_TypeApti)?.libelle || ''"></div>
                                    <div class="csh-aptitude-title" x-text="apt.titre_apti || 'Sans titre'"></div>
                                    <div class="csh-aptitude-desc" x-text="apt.descri_apti || ''"></div>
                                </div>
                            </div>
                        </template>
                    </div>
                </template>
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
                        <div class="csh-constellation-frame" x-ref="constellationPreviewCanvas">
                            <template x-if="constellationMapImage">
                                <div class="csh-constellation-map-wrap th-const-map-media" :style="mapMediaStyle('constellationPreviewCanvas')">
                                    <img :src="constellationMapImage" alt="Carte constellation"
                                         @load="updateConstellationMapNaturalSize($event)"
                                         style="object-fit: contain; object-position: center;">

                                    <template x-for="(line, idx) in constellationMapLines" :key="`line-preview-${idx}`">
                                        <template x-if="lineIsValid(line)">
                                            <div class="csh-constellation-map-line"
                                                 :class="constellationPreviewLineClass(line)"
                                                 :style="mapLineStyle(line, 'constellationPreviewCanvas')"></div>
                                        </template>
                                    </template>

                                    <template x-for="index in [1,2,3,4,5,6]" :key="`point-preview-${index}`">
                                        <template x-if="constellationMapPositions[String(index)]">
                                            <div class="csh-constellation-map-point"
                                                 :class="constellationPreviewPointClass(index)"
                                                 :style="mapPointStyle(index)">
                                                <span x-text="index"></span>
                                            </div>
                                        </template>
                                    </template>
                                </div>
                            </template>
                            <template x-if="!constellationMapImage && activeConstellation && activeConstellation.image_url">
                                <img :src="activeConstellation.image_url" :alt="activeConstellation.titre_const || 'Constellation'">
                            </template>
                            <template x-if="!constellationMapImage && !activeConstellation">
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
                                            @click="selectConstellationPreview(index)">
                                        <div x-text="constellation.label || ('C' + (index + 1))"></div>
                                        <div class="truncate text-[11px] opacity-80" x-text="constellation.titre_const || 'Sans titre'"></div>
                                    </button>
                                </template>
                            </div>
                        </template>

                        <template x-if="activeConstellation">
                            <div class="csh-constellation-detail">
                                <div class="csh-constellation-title" x-text="activeConstellation.titre_const || 'Constellation sans nom'"></div>
                                <div class="csh-constellation-desc" x-html="renderDescriConst(activeConstellation.descri_const) || 'Aucune description.'"></div>
                            </div>
                        </template>

                        <template x-if="!constellations.length">
                            <div class="csh-artefact-empty">Aucune constellation disponible pour ce personnage.</div>
                        </template>
                    </div>
                </div>
            </section>

            <section class="csh-team-shell">
                <div class="csh-preview-panel-head">
                    <div>
                        <div class="csh-preview-panel-title">Compositions d'équipe</div>
                        <div class="csh-preview-panel-subtitle">Groupées par réaction, with Recommended / F2P / autres teams</div>
                    </div>
                    <div class="text-xs text-slate-400" x-text="teams.length ? `${teams.length} team(s)` : 'Aucune'"></div>
                </div>

                <template x-if="!teams.length">
                    <div class="csh-aptitudes-empty">Aucune team enregistrée pour ce personnage.</div>
                </template>

                <template x-for="group in (teamGroups || [])" :key="`team-group-${group.key}`">
                    <div class="csh-team-group">
                        <div class="csh-team-group-head">
                            <div>
                                <div class="csh-team-group-title" x-text="`${teamReactionEmoji(group.reaction)} ${group.reaction}`"></div>
                                <div class="csh-team-group-sub" x-text="`${group.teams.length} team(s)`"></div>
                            </div>
                        </div>

                        <template x-if="group.recommended">
                            <article class="csh-team-card recommended">
                                <div class="csh-team-card-head">
                                    <div class="csh-team-card-tags">
                                        <span class="csh-team-tag csh-team-tag-rec">Recommended</span>
                                        <span class="text-xs text-slate-300" x-text="group.recommended.type_reaction"></span>
                                    </div>
                                    <button type="button" class="csh-team-remplacants-btn"
                                            @click="toggleRecommendedReplacements(group.recommended.id_team)">⇄ remplaçants</button>
                                </div>
                                <div class="csh-team-slots">
                                    <template x-for="member in sortedMembers(group.recommended || [])" :key="`team-rec-member-${group.recommended?.id_team || 'unknown'}-${member.slot}`">
                                        <div class="csh-team-slot">
                                            <img :src="member.icon || '{{ asset('images/placeholder.svg') }}'"
                                                 :alt="member.nom"
                                                 :style="`border-color:${teamElementColor(member.element)}`" />
                                            <div class="csh-team-slot-name" x-text="member.nom"></div>
                                            <div class="csh-team-slot-role" x-text="member.role || 'Rôle' "></div>

                                            <template x-if="recommendedReplacementsOpen(group.recommended.id_team)">
                                                <div class="csh-team-remplacants">
                                                    <template x-if="(slotRemplacants(group.recommended, member.slot) || []).length">
                                                        <div class="csh-team-remplacant-row">
                                                            <div class="csh-team-remplacant-head" x-text="`Slot ${member.slot} - remplaçants`"></div>
                                                            <div class="csh-team-remplacant-list">
                                                                <template x-for="alt in (slotRemplacants(group.recommended, member.slot) || [])" :key="`team-rec-alt-${group.recommended?.id_team || 'unknown'}-${member.slot}-${alt.id_perso}`">
                                                                    <div class="csh-team-remplacant-item" :title="alt.role || 'Rôle non défini'">
                                                                        <img :src="alt.icon || '{{ asset('images/placeholder.svg') }}'" :alt="alt.nom" />
                                                                    </div>
                                                                </template>
                                                            </div>
                                                        </div>
                                                    </template>
                                                </div>
                                            </template>
                                        </div>
                                    </template>
                                </div>
                            </article>
                        </template>

                        <template x-if="group.f2p">
                            <article class="csh-team-card f2p">
                                <div class="csh-team-card-head">
                                    <div class="csh-team-card-tags">
                                        <span class="csh-team-tag csh-team-tag-f2p">F2P</span>
                                        <span class="text-xs text-slate-300" x-text="group.f2p.type_reaction"></span>
                                    </div>
                                </div>
                                <div class="csh-team-slots">
                                    <template x-for="member in sortedMembers(group.f2p || [])" :key="`team-f2p-member-${group.f2p?.id_team || 'unknown'}-${member.slot}`">
                                        <div class="csh-team-slot">
                                            <img :src="member.icon || '{{ asset('images/placeholder.svg') }}'"
                                                 :alt="member.nom"
                                                 :style="`border-color:${teamElementColor(member.element)}`" />
                                            <div class="csh-team-slot-name" x-text="member.nom"></div>
                                            <div class="csh-team-slot-role" x-text="member.role || 'Rôle'"></div>
                                        </div>
                                    </template>
                                </div>
                            </article>
                        </template>

                        <template x-if="group.others.length">
                            <div>
                                <button type="button" class="csh-team-drawer-btn" @click="toggleGroupDrawer(group.key)">
                                    <span class="csh-team-drawer-chevron" :class="groupDrawerOpen(group.key) ? 'is-open' : ''">▼</span>
                                    <span>Autres teams</span>
                                    <span class="csh-team-drawer-count" x-text="`${group.others.length} autres teams`"></span>
                                </button>
                                <div class="csh-team-others" x-show="groupDrawerOpen(group.key)">
                                    <template x-for="team in (group.others || [])" :key="`team-other-${team?.id_team || 'unknown'}`">
                                        <article class="csh-team-card">
                                            <div class="csh-team-card-head">
                                                <div class="text-xs text-slate-300" x-text="team?.type_reaction || 'Team vide'"></div>
                                            </div>
                                            <div class="csh-team-slots">
                                                <template x-for="member in sortedMembers(team || [])" :key="`team-other-member-${team?.id_team || 'unknown'}-${member.slot}`">
                                                    <div class="csh-team-slot">
                                                        <img :src="member.icon || '{{ asset('images/placeholder.svg') }}'"
                                                             :alt="member.nom"
                                                             :style="`border-color:${teamElementColor(member.element)}`" />
                                                        <div class="csh-team-slot-name" x-text="member.nom"></div>
                                                        <div class="csh-team-slot-role" x-text="member.role || 'Rôle'"></div>
                                                    </div>
                                                </template>
                                            </div>
                                        </article>
                                    </template>
                                </div>
                            </div>
                        </template>
                    </div>
                </template>
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

        <template x-if="showArtefactManager">
            <div class="th-const-edit-overlay" @click.self="showArtefactManager = false">
                <div class="th-apt-single-modal" style="width:min(920px,98vw)">
                    <div class="flex items-center justify-between gap-3 pb-3 border-b border-slate-200">
                        <div>
                            <div class="text-base font-bold text-slate-900">Builds artefacts</div>
                            <div class="text-[11px] text-slate-400">Chaque build doit être soit 4P, soit 2P + 2P.</div>
                        </div>
                        <div class="flex items-center gap-2">
                                <button type="button" @click="addArtefactBuild()"
                                    :disabled="artefactBuilds.length >= 4"
                                    class="rounded-lg border border-indigo-500 bg-indigo-500 px-3 py-1.5 text-xs font-semibold text-white hover:bg-indigo-400 disabled:cursor-not-allowed disabled:opacity-50">+ Ajouter</button>
                            <button type="button" @click="showArtefactManager = false"
                                    class="rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-100">Fermer</button>
                        </div>
                    </div>

                    <template x-if="artefactsError">
                        <div class="mt-4 rounded border border-red-300 bg-red-50 px-3 py-2 text-xs text-red-700" x-text="artefactsError"></div>
                    </template>

                    <template x-if="!artefactBuilds.length">
                        <div class="mt-6 rounded-xl border border-dashed border-slate-300 bg-slate-50 px-4 py-8 text-center">
                            <div class="text-sm font-semibold text-slate-700">Aucun build artefact</div>
                            <div class="mt-1 text-xs text-slate-400">Ajoute un build puis choisis un set 4P ou deux sets 2P.</div>
                        </div>
                    </template>

                    <div class="mt-4 space-y-4" x-show="artefactBuilds.length">
                        <template x-for="(build, index) in artefactBuilds" :key="`artefact-build-${build.id_build || index}`">
                            <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <div class="text-sm font-bold text-slate-900" x-text="`Build ${index + 1}`"></div>
                                        <div class="mt-1 flex items-center gap-2">
                                            <button type="button" @click="setArtefactPieces(index, 4)"
                                                    class="rounded-full px-2.5 py-1 text-[11px] font-bold border"
                                                    :class="build.pieces_1 === 4 ? 'bg-amber-100 text-amber-800 border-amber-300' : 'bg-white text-slate-600 border-slate-300'">4P</button>
                                            <button type="button" @click="setArtefactPieces(index, 2)"
                                                    class="rounded-full px-2.5 py-1 text-[11px] font-bold border"
                                                    :class="build.pieces_1 === 2 ? 'bg-amber-100 text-amber-800 border-amber-300' : 'bg-white text-slate-600 border-slate-300'">2P + 2P</button>
                                        </div>
                                    </div>
                                    <button type="button" @click="removeArtefactBuild(index)"
                                            class="rounded px-1 text-slate-300 hover:text-red-500">×</button>
                                </div>

                                <div class="mt-4 grid grid-cols-1 gap-3 md:grid-cols-2">
                                    <div class="rounded-xl border border-slate-200 bg-slate-50 p-3">
                                        <div class="mb-2 text-[11px] font-semibold uppercase tracking-wide text-slate-600">Set principal</div>
                                        <button type="button" @click="openArtefactPicker(index, 1)"
                                                class="flex w-full items-center gap-3 rounded-xl border border-slate-300 bg-white px-3 py-2 text-left hover:bg-slate-50">
                                            <img :src="build.artefact1_icon || '{{ asset('images/placeholder.svg') }}'" alt="" class="h-11 w-11 rounded-lg object-cover border border-slate-200" />
                                            <div class="min-w-0 flex-1">
                                                <div class="truncate text-sm font-semibold text-slate-900" x-text="build.artefact1_nom || 'Choisir un set'" ></div>
                                                <div class="text-[10px] text-slate-500" x-show="build.artefact1_id" x-text="artefactRarityForId(build.artefact1_id)"></div>
                                                <div class="text-[11px] text-slate-500" x-text="build.pieces_1 === 4 ? 'Set en 4 pièces' : 'Première moitié du 2P + 2P'"></div>
                                            </div>
                                        </button>
                                        <button type="button" @click="clearArtefactSlot(index, 1)" x-show="build.artefact1_id"
                                                class="mt-2 text-[11px] text-red-500 hover:text-red-600">Retirer</button>
                                    </div>

                                    <div class="rounded-xl border border-slate-200 bg-slate-50 p-3" :class="build.pieces_1 === 4 ? 'opacity-50' : ''">
                                        <div class="mb-2 text-[11px] font-semibold uppercase tracking-wide text-slate-600">Set secondaire</div>
                                        <button type="button" @click="build.pieces_1 === 2 && openArtefactPicker(index, 2)"
                                                :disabled="build.pieces_1 === 4"
                                                class="flex w-full items-center gap-3 rounded-xl border border-slate-300 bg-white px-3 py-2 text-left hover:bg-slate-50 disabled:cursor-not-allowed">
                                            <img :src="build.artefact2_icon || '{{ asset('images/placeholder.svg') }}'" alt="" class="h-11 w-11 rounded-lg object-cover border border-slate-200" />
                                            <div class="min-w-0 flex-1">
                                                <div class="truncate text-sm font-semibold text-slate-900" x-text="build.artefact2_nom || (build.pieces_1 === 4 ? 'Non utilisé en 4P' : 'Choisir un second 2P')"></div>
                                                <div class="text-[10px] text-slate-500" x-show="build.artefact2_id" x-text="artefactRarityForId(build.artefact2_id)"></div>
                                                <div class="text-[11px] text-slate-500">Obligatoire si le build est en 2P + 2P</div>
                                            </div>
                                        </button>
                                        <button type="button" @click="clearArtefactSlot(index, 2)" x-show="build.artefact2_id && build.pieces_1 === 2"
                                                class="mt-2 text-[11px] text-red-500 hover:text-red-600">Retirer</button>
                                    </div>
                                </div>

                                <div class="mt-3 rounded-xl border border-slate-200 bg-slate-50 p-3">
                                    <div class="mb-2 text-[11px] font-semibold uppercase tracking-wide text-slate-600">Stats recommandees</div>
                                    <div class="grid grid-cols-1 gap-2 md:grid-cols-2">
                                        <label class="rounded-lg border border-slate-200 bg-white px-2 py-1.5">
                                            <div class="mb-1 text-[10px] font-semibold uppercase tracking-wide text-slate-500">Subs stats (max 4, ordre = priorité)</div>
                                            <div class="grid grid-cols-2 gap-1">
                                                <template x-for="stat in subStatsOptions" :key="stat">
                                                    <label class="flex items-center gap-1.5 text-[11px] text-slate-700">
                                                        <input type="checkbox"
                                                               :value="stat"
                                                               :checked="build.sub_stats.includes(stat)"
                                                               :disabled="build.sub_stats.length >= 4 && !build.sub_stats.includes(stat)"
                                                               @change="toggleSubStat(build, stat, $event.target.checked)">
                                                        <span x-text="stat"></span>
                                                        <span x-show="build.sub_stats.includes(stat)"
                                                              x-text="build.sub_stats.indexOf(stat) + 1"
                                                              class="text-amber-600 font-bold"></span>
                                                    </label>
                                                </template>
                                            </div>
                                        </label>
                                        <label class="rounded-lg border border-slate-200 bg-white px-2 py-1.5">
                                            <div class="mb-1 text-[10px] font-semibold uppercase tracking-wide text-slate-500">Gobelet</div>
                                            <div class="grid grid-cols-2 gap-1">
                                                <template x-for="stat in mainStatsGobeletOptions" :key="stat">
                                                    <label class="flex items-center gap-1.5 text-[11px] text-slate-700">
                                                        <input type="radio" :name="`gobelet-${build.id_build || index}`"
                                                               :value="stat" :checked="build.main_stat_gobelet === stat"
                                                               @change="build.main_stat_gobelet = stat">
                                                        <span x-text="stat"></span>
                                                    </label>
                                                </template>
                                            </div>
                                        </label>

                                        <label class="rounded-lg border border-slate-200 bg-white px-2 py-1.5">
                                            <div class="mb-1 flex items-center gap-1.5 text-[10px] font-semibold uppercase tracking-wide text-slate-500">
                                                <span class="inline-flex h-4 w-4 items-center justify-center" title="Sablier">
                                                    <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                                        <path d="M7 3h10"></path><path d="M7 21h10"></path><path d="M8 3c0 4 3 5 4 6-1 1-4 2-4 6"></path><path d="M16 3c0 4-3 5-4 6 1 1 4 2 4 6"></path>
                                                    </svg>
                                                </span>
                                                Sablier
                                            </div>
                                            <div class="grid grid-cols-2 gap-1">
                                                <template x-for="stat in mainStatsSablierOptions" :key="stat">
                                                    <label class="flex items-center gap-1.5 text-[11px] text-slate-700">
                                                        <input type="radio" :name="`sablier-${build.id_build || index}`"
                                                               :value="stat" :checked="build.main_stat_sablier === stat"
                                                               @change="build.main_stat_sablier = stat">
                                                        <span x-text="stat"></span>
                                                    </label>
                                                </template>
                                            </div>
                                        </label>

                                        <label class="flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-2 py-1.5">
                                            <span class="inline-flex h-6 w-6 items-center justify-center rounded border border-slate-200 bg-slate-50 text-slate-500" title="Couronne">
                                                <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                                    <path d="M4 8l4 3 4-6 4 6 4-3-2 9H6L4 8Z"></path><path d="M8 20h8"></path>
                                                </svg>
                                            </span>
                                            <label class="rounded-lg border border-slate-200 bg-white px-2 py-1.5">
                                                <div class="mb-1 text-[10px] font-semibold uppercase tracking-wide text-slate-500">Couronne</div>
                                                <div class="grid grid-cols-2 gap-1">
                                                    <template x-for="stat in mainStatsCouronneOptions" :key="stat">
                                                        <label class="flex items-center gap-1.5 text-[11px] text-slate-700">
                                                            <input type="radio" :name="`couronne-${build.id_build || index}`"
                                                                   :value="stat" :checked="build.main_stat_couronne === stat"
                                                                   @change="build.main_stat_couronne = stat">
                                                            <span x-text="stat"></span>
                                                        </label>
                                                    </template>
                                                </div>
                                            </label>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>

                    <div class="mt-4 flex justify-end gap-2 border-t border-slate-200 pt-4">
                        <button type="button" @click="showArtefactManager = false"
                                class="rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-100">Annuler</button>
                        <button type="button" @click="saveArtefacts()"
                                class="rounded-lg border border-indigo-500 bg-indigo-500 px-3 py-1.5 text-xs font-semibold text-white hover:bg-indigo-400">Enregistrer</button>
                    </div>
                </div>
            </div>
        </template>

           <div x-show="artefactPicker.open" x-cloak @click.outside="closeArtefactPicker()"
               class="th-armes-picker-modal th-artefact-picker-modal"
             :style="`left: ${ (window.innerWidth * 0.33) + 100 }px; top: 120px;`">

            <div class="sticky top-0 border-b border-slate-300 bg-white p-3 space-y-2">
                <div class="flex items-center justify-between">
                    <h3 class="font-semibold text-slate-900 text-sm">Sélectionner un artefact</h3>
                    <button type="button" @click="closeArtefactPicker()" class="text-2xl text-slate-500 hover:text-slate-700">×</button>
                </div>
                <div class="flex items-center gap-2">
                    <select x-model="artefactRarityFilter" class="flex-1 rounded border border-slate-300 bg-white px-2 py-1.5 text-xs text-black">
                        <option value="">Rareté: toutes</option>
                        <option value="1">1★</option>
                        <option value="2">2★</option>
                        <option value="3">3★</option>
                        <option value="4">4★</option>
                        <option value="5">5★</option>
                    </select>
                    <button type="button" @click="artefactRarityFilter = ''" class="rounded border border-slate-300 px-2 py-1.5 text-xs text-slate-700 hover:bg-slate-100">
                        Reset
                    </button>
                </div>
            </div>

            <template x-if="!filteredAvailableArtefacts.length">
                <div class="p-4 text-center text-xs text-slate-500">
                    Aucun artefact disponible pour ce filtre.
                </div>
            </template>

            <template x-if="filteredAvailableArtefacts.length">
                <div class="th-armes-picker-grid">
                    <template x-for="artefact in filteredAvailableArtefacts" :key="'picker-art-' + artefact.id">
                        <button type="button" @click="selectArtefactForBuild(artefact)"
                                class="th-armes-picker-item">
                            <div class="th-armes-picker-icon">
                                <div class="th-weapon-icon-wrap" :class="rarityClass(artefact.stars)">
                                    <img :src="artefact.icon" :alt="artefact.nom">
                                </div>
                            </div>
                            <div class="name" x-text="artefact.nom"></div>
                            <div class="rarity flex flex-wrap items-center justify-center gap-1" x-show="artefact.rarity_options && artefact.rarity_options.length">
                                <template x-for="star in artefact.rarity_options" :key="`art-rarity-${artefact.id}-${star}`">
                                    <span class="rounded-full border border-slate-300 bg-slate-50 px-1.5 py-0.5 text-[10px] font-semibold text-slate-700" x-text="`${star}★`"></span>
                                </template>
                            </div>
                            <div class="rarity" x-show="!artefact.rarity_options || !artefact.rarity_options.length" x-text="artefact.rarete || '?'"></div>
                        </button>
                    </template>
                </div>
            </template>
        </div>

        <template x-if="driveBrowser.open">
            <div class="th-const-edit-overlay" @click.self="closeDriveBrowser()">
                <div class="th-apt-single-modal" style="width:min(1400px,99vw);max-height:95vh;">
                    <div class="flex items-center justify-between gap-3 pb-3 border-b border-slate-200">
                        <div>
                            <div class="text-base font-bold text-slate-900">Google Drive — Lecteur d'images</div>
                            <div class="text-[11px] text-slate-400">Parcours les dossiers et clique sur une image pour l'appliquer en background.</div>
                        </div>
                        <button type="button" @click="closeDriveBrowser()"
                                class="rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-100">Fermer</button>
                    </div>

                    <div class="mt-3 flex flex-wrap items-center gap-2">
                        <template x-for="(crumb, idx) in driveBrowser.breadcrumbs" :key="`crumb-${crumb.id}-${idx}`">
                            <button type="button"
                                    @click="goToDriveBreadcrumb(idx)"
                                    class="rounded border border-slate-300 bg-white px-2 py-1 text-[11px] text-slate-700 hover:bg-slate-100"
                                    x-text="crumb.name || 'Dossier'"></button>
                        </template>
                    </div>

                    <template x-if="driveBrowser.error">
                        <div class="mt-3 rounded border border-red-300 bg-red-50 px-3 py-2 text-xs text-red-700" x-text="driveBrowser.error"></div>
                    </template>

                    <template x-if="driveBrowser.loading">
                        <div class="mt-4 rounded border border-slate-200 bg-slate-50 px-3 py-3 text-xs text-slate-600">Chargement du dossier...</div>
                    </template>

                    <div class="mt-4 grid grid-cols-1 gap-4 xl:grid-cols-3" x-show="!driveBrowser.loading">
                        <div class="rounded-xl border border-slate-200 bg-slate-50 p-3 xl:col-span-1">
                            <div class="mb-2 text-xs font-bold uppercase tracking-wide text-slate-600">Dossiers</div>
                            <template x-if="!driveBrowser.folders.length">
                                <div class="rounded border border-dashed border-slate-300 px-3 py-5 text-center text-xs text-slate-500">Aucun sous-dossier.</div>
                            </template>
                            <div class="space-y-2 max-h-[420px] overflow-y-auto">
                                <template x-for="folder in driveBrowser.folders" :key="folder.id">
                                    <button type="button"
                                            @click="openDriveSubFolder(folder)"
                                            class="flex w-full items-center gap-2 rounded border border-slate-200 bg-white px-3 py-2 text-left text-sm text-slate-700 hover:border-blue-300 hover:bg-blue-50">
                                        <span class="text-base">📁</span>
                                        <span class="truncate" x-text="folder.name"></span>
                                    </button>
                                </template>
                            </div>
                        </div>

                        <div class="rounded-xl border border-slate-200 bg-slate-50 p-3 xl:col-span-2">
                            <div class="mb-2 text-xs font-bold uppercase tracking-wide text-slate-600">Images</div>
                            <template x-if="!driveBrowser.images.length">
                                <div class="rounded border border-dashed border-slate-300 px-3 py-5 text-center text-xs text-slate-500">Aucune image dans ce dossier.</div>
                            </template>
                            <div class="grid grid-cols-2 gap-2 max-h-[70vh] overflow-y-auto pr-1 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5">
                                <template x-for="image in driveBrowser.images" :key="image.id">
                                    <button type="button"
                                            @click="selectDriveBackground(image)"
                                            class="rounded border border-slate-200 bg-white p-2 text-left hover:border-blue-300 hover:bg-blue-50">
                                        <img :src="image.thumbnail_url || image.direct_url"
                                             :alt="image.name"
                                             class="h-24 w-full rounded object-cover border border-slate-200" />
                                        <div class="mt-1 truncate text-[11px] font-semibold text-slate-700" x-text="image.name"></div>
                                    </button>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </template>

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
            const isFreshCreate = String(data.freshCreate || '0') === '1';
            const availableArmes = safeJsonParse(data.availableArmes, []);
            const availableArtefacts = safeJsonParse(data.availableArtefacts, []);
            const existingArmes  = safeJsonParse(data.existingArmes, []).slice(0, 6);
            const existingArtefacts = safeJsonParse(data.existingArtefacts, []).slice(0, 4);
            const existingConstellations = safeJsonParse(data.constellations, []);
            const existingAptitudes = safeJsonParse(data.aptitudes, []);
            const existingHistoires = safeJsonParse(data.histoires, []);
            const storyCommandSources = safeJsonParse(data.storyCommandSources, {});
            const existingTypesApti = safeJsonParse(data.typesApti, []);
            const existingTeams = safeJsonParse(data.teams, []);
            const existingTeamPool = safeJsonParse(data.teamPool, []);
            const existingReactions = safeJsonParse(data.reactions, []);
            const existingConstellationMapPositions = safeJsonParse(data.constMapPositions, {});
            const existingConstellationMapLines = safeJsonParse(data.constMapLines, []);
            const elementIcons   = safeJsonParse(data.elementIcons, {});
            const nationIcons    = safeJsonParse(data.nationIcons, {});
            const nationBarFrames = safeJsonParse(data.nationBarFrames, {});
            const nationBarFillers = safeJsonParse(data.nationBarFillers, {});
            const weaponTypeIcons = safeJsonParse(data.weaponTypeIcons, {});
            const elementLabels   = safeJsonParse(data.elementLabels, {});
            const nationLabels    = safeJsonParse(data.nationLabels, {});
            const weaponTypeLabels = safeJsonParse(data.weaponTypeLabels, {});
            const etoileLabels    = safeJsonParse(data.etoileLabels, {});
            const defaultPortrait = data.defaultPortrait || '{{ asset("images/placeholder.svg") }}';
            const defaultIcone    = data.defaultIcone    || '{{ asset("images/placeholder.svg") }}';
            const defaultWeapon   = data.defaultWeapon   || '{{ asset("images/placeholder.svg") }}';
            const defaultArtefact = '{{ asset("images/placeholder.svg") }}';

            return {
                mainZone: {
                    nom_perso:  isFreshCreate ? '' : (parsedMain.nom_perso || data.nomPerso || ''),
                    fid_element:isFreshCreate ? '' : (parsedMain.fid_element || data.fidElement || ''),
                    fid_etoile: isFreshCreate ? '' : (parsedMain.fid_etoile || data.fidEtoile || ''),
                    fid_TArmes: isFreshCreate ? '' : (parsedMain.fid_TArmes || data.fidTarmes || ''),
                    fid_TP:     parsedMain.fid_TP || data.fidTp || '',
                    fid_nation: isFreshCreate ? '' : (parsedMain.fid_nation || data.fidNation || ''),
                    versatilite: isFreshCreate ? null : (parsedMain.versatilite ?? null),
                    arme_icon:  parsedMain.arme_icon  || data.armeIcon || '',
                    background_actif: isFreshCreate ? '' : (parsedMain.background_actif || ''),
                    videos:     isFreshCreate ? [] : (parsedMain.videos || []),
                },
                driveBackgroundUrlInput: isFreshCreate ? '' : (parsedMain.background_actif || ''),
                googleDrive: {
                    apiKey: data.googleDriveApiKey || '',
                    clientId: data.googleDriveClientId || '',
                    appId: data.googleDriveAppId || '',
                    folderId: data.googleDriveFolderId || '',
                    folderUrl: data.googleDriveFolderUrl || '',
                    browseUrl: data.googleDriveBrowseUrl || '',
                    tokenClient: null,
                    accessToken: '',
                    gapiLoaded: false,
                    gisLoaded: false,
                },
                driveBrowser: {
                    open: false,
                    loading: false,
                    error: '',
                    currentFolderId: '',
                    folders: [],
                    images: [],
                    breadcrumbs: [],
                },
                portraitPreview: data.portraitPreview || defaultPortrait,
                fullPreview:     data.fullPreview     || defaultPortrait,
                iconePreview:    data.iconePreview    || defaultIcone,
                weaponToAdd: '',
                armes:           existingArmes,
                artefactBuilds:  existingArtefacts,
                constellations:  existingConstellations,
                constellationSlots: (() => {
                    const slots = [];
                    for (let i = 1; i <= 6; i++) {
                        const found = existingConstellations.find(c => c.index === i) || existingConstellations[i - 1];
                        slots.push(found ? { ...found, index: i, label: 'C' + i } : {
                            id_const: null, index: i, label: 'C' + i,
                            titre_const: '', descri_const: '', image_url: ''
                        });
                    }
                    return slots;
                })(),
                selectedConstellationIndex: 0,
                constellationMapPositions: existingConstellationMapPositions,
                constellationMapLines: Array.isArray(existingConstellationMapLines) ? existingConstellationMapLines : [],
                selectedMapPoint: null,
                mapEditorMode: 'point',
                showConstellationMapModal: false,
                showConstellationsModal: false,
                constellationWizardStep: 0,
                aptitudeFormOpen: false,
                aptitudeFormIdx: null,
                aptitudeFormData: { titre_apti: '', descri_apti: '', fid_TypeApti: '', image_url: '', _pendingFile: null, _dragging: false },
                aptitudeFormError: '',
                aptitudeFormSaving: false,
                showAptitudePicker: false,
                aptitudePickerSlotIndex: null,
                histoireFormOpen: false,
                histoireFormIdx: null,
                histoireFormData: { titre_histoire: '', histoire: '' },
                storyCommandSources,
                storyPickerOpen: false,
                storyPickerCommand: '',
                storyPickerSearch: '',
                storyCommandMenu: {
                    open: false,
                    start: null,
                    end: null,
                    query: '',
                    selectedIndex: 0,
                },
                slashMenuOpen: false,
                slashMenuQuery: '',
                slashMenuSelectedIndex: 0,
                slashMenuSlotIndex: null,
                aptitudes: existingAptitudes,
                histoires: Array.isArray(existingHistoires) ? existingHistoires : [],
                typesApti: existingTypesApti,
                teams: Array.isArray(existingTeams) ? existingTeams.filter(team => team && typeof team === 'object') : [],
                teamPool: Array.isArray(existingTeamPool) ? existingTeamPool.filter(person => person && typeof person === 'object') : [],
                teamReactions: existingReactions,
                teamManagerOpen: false,
                reactionSlotPickerOpen: false,
                teamReactionSlotDrafts: [],
                teamFormOpen: false,
                teamEditingId: null,
                teamForm: {
                    type_reaction: '',
                    tag: '',
                    rotation: '',
                    membres: [
                        { slot: 1, id_perso: null, role_override: null },
                        { slot: 2, id_perso: null, role_override: null },
                        { slot: 3, id_perso: null, role_override: null },
                        { slot: 4, id_perso: null, role_override: null },
                    ],
                    remplacants: [],
                },
                teamError: '',
                teamSaving: false,
                teamConstructorAptitudes: [],
                rotationSequence: [],
                teamAptitudesLoading: false,
                teamRotationSaving: false,
                teamSlotPickerOpen: null,
                teamSlotPickerSearch: '',
                teamDrawerState: {},
                teamRecommendedOpen: {},
                lineDraftStart: null,
                constellationMapNaturalWidth: 0,
                constellationMapNaturalHeight: 0,
                constellationMapImage: data.constMapImage || '',
                constellationMapImageUrlInput: '',
                sidebarCollapsed: false,
                availableArmes:  availableArmes,
                availableArtefacts: availableArtefacts,
                showArmesPicker: false,
                showArtefactManager: false,
                artefactPicker: { open: false, buildIndex: null, slot: 1 },
                artefactRarityFilter: '',
                weaponRarityFilter: '',
                armesError: '',
                artefactsError: '',
                constellationsError: '',
                histoiresError: '',
                elementIcons,
                nationIcons,
                nationBarFrames,
                nationBarFillers,
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
                get normalizedTeams() {
                    return Array.isArray(this.teams)
                        ? this.teams.filter(team => team && typeof team === 'object')
                        : [];
                },
                get teamReactionSlots() {
                    const existing = (this.normalizedTeams || []).map(team => String(team?.type_reaction || '').trim()).filter(Boolean);
                    const merged = [...existing, ...(this.teamReactionSlotDrafts || [])]
                        .map(name => String(name || '').trim())
                        .filter(Boolean)
                        .filter((value, index, arr) => arr.findIndex(item => item.toLowerCase() === value.toLowerCase()) === index);

                    return merged
                        .map(name => this.reactionMeta(name) || { id_reaction: name, nom_reaction: name, icon: null })
                        .sort((a, b) => a.nom_reaction.localeCompare(b.nom_reaction, 'fr', { sensitivity: 'base' }));
                },
                get availableReactionSlots() {
                    const used = new Set(this.teamReactionSlots.map(slot => String(slot.nom_reaction).toLowerCase()));
                    return (this.teamReactions || []).filter(reaction => !used.has(String(reaction.nom_reaction).toLowerCase()));
                },
                get filteredPickerPool() {
                    const state = this.teamSlotPickerOpen;
                    if (!state) return [];
                    const search = String(this.teamSlotPickerSearch || '').toLowerCase().trim();
                    const slot = Number(state.slot);
                    const isAlt = !!state.isAlt;
                    return (this.teamPool || []).filter(p => {
                        if (search && !p.nom.toLowerCase().includes(search)) return false;
                        if (isAlt) {
                            const mainId = this.teamForm.membres[slot - 1]?.id_perso;
                            if (mainId && Number(p.id_perso) === Number(mainId)) return false;
                            if ((this.teamForm.remplacants || []).some(r => Number(r.slot) === slot && Number(r.id_perso) === Number(p.id_perso))) return false;
                        } else {
                            if (this.isTeamMemberTakenByOtherSlot(slot, p.id_perso)) return false;
                        }
                        return true;
                    });
                },
                get teamGroups() {
                    const grouped = {};
                    (this.normalizedTeams || []).forEach(team => {
                        const reaction = String(team?.type_reaction || 'Sans réaction').trim() || 'Sans réaction';
                        if (!grouped[reaction]) grouped[reaction] = [];
                        grouped[reaction].push(team);
                    });

                    return Object.entries(grouped)
                        .map(([reaction, teams]) => {
                            const recommended = (teams || []).find(t => t?.tag === 'recommended') || null;
                            const f2p = (teams || []).find(t => t?.tag === 'f2p') || null;
                            const others = (teams || []).filter(t => t?.id_team !== recommended?.id_team && t?.id_team !== f2p?.id_team);
                            return { key: reaction.toLowerCase(), reaction, teams: teams || [], recommended, f2p, others };
                        })
                        .sort((a, b) => a.reaction.localeCompare(b.reaction, 'fr', { sensitivity: 'base' }));
                },
                get activeConstellation() {
                    if (!this.constellations.length) return null;
                    const idx = Math.max(0, Math.min(this.selectedConstellationIndex, this.constellations.length - 1));
                    return this.constellations[idx] || null;
                },
                get activeConstellationLevel() {
                    return Math.max(1, Math.min(6, Number(this.selectedConstellationIndex || 0) + 1));
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
                get lineDraftLabel() {
                    if (this.mapEditorMode !== 'line') {
                        return 'Passe en mode ligne pour relier des points';
                    }
                    if (this.lineDraftStart === null) {
                        return 'Selectionne le point de depart';
                    }
                    return `Depart: C${this.lineDraftStart}, selectionne le point d'arrivee`;
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

                    const lines = this.constellationMapLines
                        .map(line => ({ from: Number(line.from), to: Number(line.to) }))
                        .filter(line => this.lineIsValid(line));

                    return JSON.stringify({ points: normalized, lines });
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
                get versatilityBarFrame() {
                    const key = String(this.mainZone.fid_nation || '');
                    return this.nationBarFrames[key] || this.nationBarFrames[Number(key)] || '{{ asset("images/versatility-bars/default.png") }}';
                },
                get versatilityBarFiller() {
                    const key = String(this.mainZone.fid_nation || '');
                    return this.nationBarFillers[key] || this.nationBarFillers[Number(key)] || '{{ asset("images/versatility-bars/filler.png") }}';
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
                get heroBackgroundStyle() {
                    if (!this.mainZone.background_actif) return '';

                    const bg = String(this.mainZone.background_actif)
                        .replace(/"/g, '%22')
                        .replace(/\)/g, '%29')
                        .trim();

                    if (!bg) return '';

                    return `background-image: linear-gradient(160deg, rgba(255,255,255,0.065), rgba(255,255,255,0.015)), linear-gradient(180deg, rgba(10,15,35,0.82), rgba(10,15,35,0.62)), url("${bg}"); background-size: auto, auto, cover; background-position: center;`;
                },
                get filteredAvailableArtefacts() {
                    if (!this.artefactPicker.open || this.artefactPicker.buildIndex === null) return [];

                    const build = this.artefactBuilds[this.artefactPicker.buildIndex];
                    if (!build) return [];

                    return this.availableArtefacts.filter(artefact => {
                        if (this.artefactRarityFilter) {
                            const requested = Number(this.artefactRarityFilter);
                            const options = Array.isArray(artefact.rarity_options) && artefact.rarity_options.length
                                ? artefact.rarity_options.map(Number)
                                : [Number(artefact.stars || 0)];

                            if (!options.includes(requested)) return false;
                        }
                        if (this.artefactPicker.slot === 1 && build.pieces_1 === 2 && build.artefact2_id && Number(build.artefact2_id) === Number(artefact.id)) return false;
                        if (this.artefactPicker.slot === 2 && build.artefact1_id && Number(build.artefact1_id) === Number(artefact.id)) return false;
                        return true;
                    });
                },
                get activeEmbedUrl() {
                    const vid = this.mainZone.videos[this.selectedVideoIndex]?.url_video || '';
                    if (!vid) return '';
                    const m = vid.match(/(?:youtu\.be\/|youtube\.com\/(?:watch\?v=|embed\/|v\/))([A-Za-z0-9_-]{11})/);
                    if (m) return 'https://www.youtube-nocookie.com/embed/' + m[1];
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
                init() {
                },
                extractGoogleDriveFileId(rawUrl) {
                    if (!rawUrl) return null;
                    const source = String(rawUrl).trim();

                    const filePathMatch = source.match(/\/file\/d\/([a-zA-Z0-9_-]+)/);
                    if (filePathMatch?.[1]) return filePathMatch[1];

                    try {
                        const parsed = new URL(source);
                        const fileIdFromParam = parsed.searchParams.get('id');
                        if (fileIdFromParam) return fileIdFromParam;

                        if (parsed.pathname.includes('/d/')) {
                            const parts = parsed.pathname.split('/d/')[1]?.split('/') || [];
                            if (parts[0]) return parts[0];
                        }
                    } catch (e) {
                        // Laisse tomber: ce n'est peut-être pas une URL complète.
                    }

                    if (/^[a-zA-Z0-9_-]{20,}$/.test(source)) return source;

                    return null;
                },
                normalizeBackgroundUrl(rawUrl) {
                    const raw = String(rawUrl || '').trim();
                    if (!raw) return '';

                    const driveId = this.extractGoogleDriveFileId(raw);
                    if (driveId) {
                        return `https://drive.google.com/thumbnail?id=${driveId}&sz=w2000`;
                    }

                    return raw;
                },
                applyBackgroundUrlInput() {
                    const normalized = this.normalizeBackgroundUrl(this.driveBackgroundUrlInput);
                    this.mainZone.background_actif = normalized;
                    this.driveBackgroundUrlInput = normalized;
                },
                openDriveFolder() {
                    if (!this.googleDrive.folderUrl) return;
                    window.open(this.googleDrive.folderUrl, '_blank', 'noopener');
                },
                async openGoogleDriveBrowser() {
                    const rootFolderId = this.extractGoogleDriveFileId(this.googleDrive.folderId || this.googleDrive.folderUrl);

                    if (!this.googleDrive.browseUrl || !rootFolderId) {
                        this.showToast('Configuration Drive incomplète: GOOGLE_DRIVE_FOLDER_ID (ou ROOT_FOLDER_ID) requis.', 'error');
                        return;
                    }

                    this.driveBrowser.open = true;
                    this.driveBrowser.error = '';
                    await this.loadDriveFolder(rootFolderId, [{ id: rootFolderId, name: 'Racine Drive' }]);
                },
                closeDriveBrowser() {
                    this.driveBrowser.open = false;
                    this.driveBrowser.error = '';
                },
                async loadDriveFolder(folderId, breadcrumbs = null) {
                    if (!this.googleDrive.browseUrl || !folderId) return;

                    this.driveBrowser.loading = true;
                    this.driveBrowser.error = '';

                    try {
                        const url = `${this.googleDrive.browseUrl}?folder_id=${encodeURIComponent(folderId)}`;
                        const resp = await fetch(url, {
                            headers: { 'Accept': 'application/json' },
                        });

                        const payload = await resp.json();
                        if (!resp.ok || !payload?.ok) {
                            this.driveBrowser.error = payload?.message || 'Impossible de lire ce dossier Drive.';
                            return;
                        }

                        this.driveBrowser.currentFolderId = payload.folder_id;
                        this.driveBrowser.folders = Array.isArray(payload.folders) ? payload.folders : [];
                        this.driveBrowser.images = Array.isArray(payload.images) ? payload.images : [];
                        if (Array.isArray(breadcrumbs)) {
                            this.driveBrowser.breadcrumbs = breadcrumbs;
                        }
                    } catch (error) {
                        this.driveBrowser.error = 'Erreur réseau en lisant Google Drive.';
                    } finally {
                        this.driveBrowser.loading = false;
                    }
                },
                async openDriveSubFolder(folder) {
                    if (!folder?.id) return;
                    const crumbs = Array.isArray(this.driveBrowser.breadcrumbs)
                        ? [...this.driveBrowser.breadcrumbs, { id: folder.id, name: folder.name || 'Dossier' }]
                        : [{ id: folder.id, name: folder.name || 'Dossier' }];
                    await this.loadDriveFolder(folder.id, crumbs);
                },
                async goToDriveBreadcrumb(index) {
                    const crumbs = this.driveBrowser.breadcrumbs || [];
                    if (index < 0 || index >= crumbs.length) return;
                    const target = crumbs[index];
                    await this.loadDriveFolder(target.id, crumbs.slice(0, index + 1));
                },
                selectDriveBackground(image) {
                    const source = image?.background_url || image?.direct_url || image?.thumbnail_url;
                    if (!source) return;
                    const url = this.normalizeBackgroundUrl(source);
                    this.mainZone.background_actif = url;
                    this.driveBackgroundUrlInput = url;
                    this.closeDriveBrowser();
                    this.showToast('Background sélectionné depuis Google Drive.', 'success');
                },
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
                        passive_name: arme.passive_name || '',
                        passive_desc: arme.passive_desc || '',
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
                artefactBuildLabel(build) {
                    return Number(build?.pieces_1) === 2 ? '2P + 2P' : '4P';
                },
                artefactRarityForId(artefactId) {
                    const targetId = Number(artefactId);
                    if (!targetId) return '';

                    const artefact = this.availableArtefacts.find(item => Number(item.id) === targetId);
                    if (!artefact) return '';

                    const options = Array.isArray(artefact.rarity_options) && artefact.rarity_options.length
                        ? artefact.rarity_options
                        : (artefact.stars ? [artefact.stars] : []);

                    if (options.length) {
                        return options.map(star => `${star}★`).join(' / ');
                    }

                    return artefact.rarete || '';
                },

                subStatsOptions: ['ATK%', 'HP%', 'DEF%', 'ATK', 'HP', 'DEF', 'Taux CRIT%', 'DGT CRIT%', 'Recharge d\'energie%', 'Maitrise elementaire'],
                mainStatsSablierOptions: ['ATK%', 'HP%', 'DEF%', 'Recharge d\'energie%', 'Maitrise elementaire'],
                mainStatsGobeletOptions: ['ATK%', 'HP%', 'DEF%', 'Maitrise elementaire', 'Bonus DGT Pyro%', 'Bonus DGT Hydro%', 'Bonus DGT Electro%', 'Bonus DGT Cryo%', 'Bonus DGT Anemo%', 'Bonus DGT Geo%', 'Bonus DGT Dendro%', 'Bonus DGT Physiques%'],
                mainStatsCouronneOptions: ['ATK%', 'HP%', 'DEF%', 'Maitrise elementaire', 'Taux CRIT%', 'DGT CRIT%', 'Bonus de soin%'],
                toggleSubStat(build, stat, checked) {
                    if (checked) { if (build.sub_stats.length < 4) build.sub_stats.push(stat); }
                    else { build.sub_stats = build.sub_stats.filter(s => s !== stat); }
                },

                addArtefactBuild() {
                    if (this.artefactBuilds.length >= 4) return;

                    this.artefactBuilds.push({
                        id_build: null,
                        artefact1_id: null,
                        artefact1_nom: '',
                        artefact1_bonus_2p: '',
                        artefact1_bonus_4p: '',
                        artefact1_icon: defaultArtefact,
                        pieces_1: 4,
                        artefact2_id: null,
                        artefact2_nom: '',
                        artefact2_bonus_2p: '',
                        artefact2_bonus_4p: '',
                        artefact2_icon: defaultArtefact,
                        pieces_2: 0,
                        main_stat_sablier: '',
                        main_stat_gobelet: '',
                        main_stat_couronne: '',
                        sub_stats: [],
                        position: this.artefactBuilds.length + 1,
                    });
                    this.artefactsError = '';
                },
                removeArtefactBuild(index) {
                    this.artefactBuilds.splice(index, 1);
                    this.normalizeArtefactBuilds();
                    if (this.artefactPicker.buildIndex === index) {
                        this.closeArtefactPicker();
                    }
                },
                normalizeArtefactBuilds() {
                    this.artefactBuilds = this.artefactBuilds.map((build, index) => ({ ...build, position: index + 1 }));
                },
                setArtefactPieces(index, pieces) {
                    const build = this.artefactBuilds[index];
                    if (!build) return;
                    build.pieces_1 = Number(pieces) === 2 ? 2 : 4;
                    if (build.pieces_1 === 4) {
                        build.artefact2_id = null;
                        build.artefact2_nom = '';
                        build.artefact2_bonus_2p = '';
                        build.artefact2_bonus_4p = '';
                        build.artefact2_icon = defaultArtefact;
                        build.pieces_2 = 0;
                    } else {
                        build.pieces_2 = 2;
                    }
                    this.artefactsError = '';
                },
                openArtefactPicker(buildIndex, slot) {
                    this.artefactPicker = { open: true, buildIndex: Number(buildIndex), slot: Number(slot) };
                    this.artefactRarityFilter = '';
                },
                closeArtefactPicker() {
                    this.artefactPicker = { open: false, buildIndex: null, slot: 1 };
                },
                selectArtefactForBuild(artefact) {
                    const build = this.artefactBuilds[this.artefactPicker.buildIndex];
                    if (!build) return;
                    if (this.artefactPicker.slot === 1) {
                        build.artefact1_id = artefact.id;
                        build.artefact1_nom = artefact.nom;
                        build.artefact1_bonus_2p = artefact.bonus_2p || '';
                        build.artefact1_bonus_4p = artefact.bonus_4p || '';
                        build.artefact1_icon = artefact.icon;
                    } else {
                        build.artefact2_id = artefact.id;
                        build.artefact2_nom = artefact.nom;
                        build.artefact2_bonus_2p = artefact.bonus_2p || '';
                        build.artefact2_bonus_4p = artefact.bonus_4p || '';
                        build.artefact2_icon = artefact.icon;
                        build.pieces_2 = 2;
                    }
                    this.artefactsError = '';
                    this.closeArtefactPicker();
                },
                clearArtefactSlot(index, slot) {
                    const build = this.artefactBuilds[index];
                    if (!build) return;
                    if (Number(slot) === 1) {
                        build.artefact1_id = null;
                        build.artefact1_nom = '';
                        build.artefact1_bonus_2p = '';
                        build.artefact1_bonus_4p = '';
                        build.artefact1_icon = defaultArtefact;
                    } else {
                        build.artefact2_id = null;
                        build.artefact2_nom = '';
                        build.artefact2_bonus_2p = '';
                        build.artefact2_bonus_4p = '';
                        build.artefact2_icon = defaultArtefact;
                        build.pieces_2 = 0;
                    }
                },
                async saveArtefacts() {
                    if (!this.artefactBuilds.length) {
                        this.artefactsError = 'Ajoute au moins un build artefact.';
                        return;
                    }

                    const payload = [];
                    const builds = this.artefactBuilds.slice(0, 4);

                    if (this.artefactBuilds.length > 4) {
                        this.artefactsError = 'Maximum 4 builds artefacts.';
                        return;
                    }
                    for (let index = 0; index < builds.length; index += 1) {
                        const build = builds[index];
                        const pieces1 = Number(build.pieces_1) === 2 ? 2 : 4;
                        if (!build.artefact1_id) {
                            this.artefactsError = `Le build ${index + 1} doit avoir un set principal.`;
                            return;
                        }
                        if (pieces1 === 2) {
                            if (!build.artefact2_id) {
                                this.artefactsError = `Le build ${index + 1} en 2P + 2P doit avoir un second set.`;
                                return;
                            }
                            if (Number(build.artefact1_id) === Number(build.artefact2_id)) {
                                this.artefactsError = `Le build ${index + 1} doit utiliser deux sets 2P différents.`;
                                return;
                            }
                        }

                        payload.push({
                            artefact1_id: Number(build.artefact1_id),
                            pieces_1: pieces1,
                            artefact2_id: pieces1 === 2 ? Number(build.artefact2_id) : null,
                            pieces_2: pieces1 === 2 ? 2 : null,
                            main_stat_sablier: String(build.main_stat_sablier || '').trim() || null,
                            main_stat_gobelet: String(build.main_stat_gobelet || '').trim() || null,
                            main_stat_couronne: String(build.main_stat_couronne || '').trim() || null,
                            sub_stats: build.sub_stats.length ? build.sub_stats : null,
                        });
                    }

                    const resp = await fetch(data.saveArtefactsUrl, {
                        method: 'PUT',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': data.csrf },
                        body: JSON.stringify({ builds: payload }),
                    });

                    if (!resp.ok) {
                        let msg = 'Erreur sauvegarde artefacts';
                        try {
                            const j = await resp.json();
                            const firstKey = Object.keys(j?.errors || {})[0];
                            if (firstKey && j.errors[firstKey]?.[0]) {
                                msg = j.errors[firstKey][0];
                            }
                        } catch (e) {
                            // Keep fallback message.
                        }
                        this.artefactsError = msg;
                        this.showToast(msg, 'error');
                        return;
                    }

                    this.normalizeArtefactBuilds();
                    this.artefactsError = '';
                    this.showArtefactManager = false;
                    this.showToast('Artefacts sauvegardés', 'success');
                },
                roundPercent(value) {
                    const num = Number(value);
                    if (Number.isNaN(num)) return 0;
                    return Math.round(Math.max(0, Math.min(100, num)) * 10) / 10;
                },
                lineIsValid(line) {
                    const from = Number(line?.from);
                    const to = Number(line?.to);
                    return Number.isInteger(from)
                        && Number.isInteger(to)
                        && from >= 1
                        && from <= 6
                        && to >= 1
                        && to <= 6
                        && from !== to
                        && this.constellationMapPositions[String(from)]
                        && this.constellationMapPositions[String(to)];
                },
                mapMediaMetrics(refName) {
                    const canvas = this.$refs?.[refName];
                    if (!canvas) {
                        return { left: 0, top: 0, width: 0, height: 0 };
                    }

                    const canvasWidth = canvas.clientWidth || 0;
                    const canvasHeight = canvas.clientHeight || 0;
                    if (!canvasWidth || !canvasHeight) {
                        return { left: 0, top: 0, width: 0, height: 0 };
                    }

                    const naturalWidth = this.constellationMapNaturalWidth || canvasWidth;
                    const naturalHeight = this.constellationMapNaturalHeight || canvasHeight;
                    const imageRatio = naturalWidth / naturalHeight;
                    const canvasRatio = canvasWidth / canvasHeight;

                    let width = canvasWidth;
                    let height = canvasHeight;
                    let left = 0;
                    let top = 0;

                    if (canvasRatio > imageRatio) {
                        height = canvasHeight;
                        width = height * imageRatio;
                        left = (canvasWidth - width) / 2;
                    } else {
                        width = canvasWidth;
                        height = width / imageRatio;
                        top = (canvasHeight - height) / 2;
                    }

                    return { left, top, width, height };
                },
                mapMediaStyle(refName) {
                    const metrics = this.mapMediaMetrics(refName);
                    return `left:${metrics.left}px;top:${metrics.top}px;width:${metrics.width}px;height:${metrics.height}px;`;
                },
                mapPointStyle(index) {
                    const key = String(index);
                    const point = this.constellationMapPositions[key];
                    const x = this.roundPercent(point?.x ?? 0);
                    const y = this.roundPercent(point?.y ?? 0);
                    return `left:${x}%;top:${y}%;`;
                },
                mapLineStyle(line, refName) {
                    const from = this.constellationMapPositions[String(line.from)];
                    const to = this.constellationMapPositions[String(line.to)];
                    if (!from || !to) {
                        return 'display:none;';
                    }

                    const metrics = this.mapMediaMetrics(refName);
                    if (!metrics.width || !metrics.height) {
                        return 'display:none;';
                    }

                    const x1 = this.roundPercent(from.x);
                    const y1 = this.roundPercent(from.y);
                    const x2 = this.roundPercent(to.x);
                    const y2 = this.roundPercent(to.y);

                    const dxPx = ((x2 - x1) / 100) * metrics.width;
                    const dyPx = ((y2 - y1) / 100) * metrics.height;
                    const lenPx = Math.sqrt((dxPx * dxPx) + (dyPx * dyPx));
                    const angle = Math.atan2(dyPx, dxPx) * (180 / Math.PI);

                    return `left:${x1}%;top:${y1}%;width:${lenPx}px;transform:rotate(${angle}deg);`;
                },
                constellationPreviewPointClass(index) {
                    const idx = Number(index);
                    if (idx === this.activeConstellationLevel) return 'is-current';
                    return idx <= this.activeConstellationLevel ? 'is-on' : 'is-off';
                },
                constellationPreviewLineClass(line) {
                    const from = Number(line?.from || 0);
                    const to = Number(line?.to || 0);
                    const isOn = from <= this.activeConstellationLevel && to <= this.activeConstellationLevel;
                    return isOn ? 'is-on' : 'is-off';
                },
                selectConstellationPreview(index) {
                    this.selectedConstellationIndex = Number(index);
                },
                nextMapPointIndex() {
                    for (let i = 1; i <= 6; i += 1) {
                        if (!this.constellationMapPositions[String(i)]) {
                            return i;
                        }
                    }
                    return null;
                },
                onConstellationMapCanvasClick(event) {
                    if (this.mapEditorMode !== 'point') {
                        return;
                    }

                    const media = event.currentTarget;
                    if (!media) return;

                    const rect = media.getBoundingClientRect();
                    if (rect.width <= 0 || rect.height <= 0) return;

                    const x = this.roundPercent(((event.clientX - rect.left) / rect.width) * 100);
                    const y = this.roundPercent(((event.clientY - rect.top) / rect.height) * 100);

                    const targetIndex = this.selectedMapPoint ?? this.nextMapPointIndex();
                    if (!targetIndex) return;

                    this.constellationMapPositions[String(targetIndex)] = { x, y };
                    this.selectedMapPoint = null;
                },
                updateConstellationMapNaturalSize(event) {
                    const image = event?.target;
                    if (!image) return;
                    if (image.naturalWidth && image.naturalHeight) {
                        this.constellationMapNaturalWidth = image.naturalWidth;
                        this.constellationMapNaturalHeight = image.naturalHeight;
                    }
                },
                setMapEditorMode(mode) {
                    this.mapEditorMode = mode === 'line' ? 'line' : 'point';
                    this.selectedMapPoint = null;
                    this.lineDraftStart = null;
                },
                openConstellationMapModal() {
                    this.showConstellationMapModal = true;
                },
                closeConstellationMapModal() {
                    this.showConstellationMapModal = false;
                    this.lineDraftStart = null;
                    this.selectedMapPoint = null;
                },
                onConstellationPointClick(index) {
                    if (this.mapEditorMode === 'line') {
                        if (!this.constellationMapPositions[String(index)]) {
                            return;
                        }
                        if (this.lineDraftStart === null) {
                            this.lineDraftStart = index;
                            return;
                        }

                        if (this.lineDraftStart === index) {
                            this.lineDraftStart = null;
                            return;
                        }

                        this.addMapLine(this.lineDraftStart, index);
                        this.lineDraftStart = null;
                        return;
                    }

                    this.selectedMapPoint = index;
                },
                addMapLine(from, to) {
                    const a = Number(from);
                    const b = Number(to);
                    const normalizedFrom = Math.min(a, b);
                    const normalizedTo = Math.max(a, b);
                    const alreadyExists = this.constellationMapLines.some(line =>
                        Number(line.from) === normalizedFrom && Number(line.to) === normalizedTo
                    );
                    if (alreadyExists) {
                        return;
                    }
                    this.constellationMapLines.push({ from: normalizedFrom, to: normalizedTo });
                },
                removeMapLine(index) {
                    this.constellationMapLines.splice(index, 1);
                },
                clearMapPoint(index) {
                    delete this.constellationMapPositions[String(index)];
                    this.constellationMapLines = this.constellationMapLines.filter(line =>
                        Number(line.from) !== Number(index) && Number(line.to) !== Number(index)
                    );
                    if (this.selectedMapPoint === index) {
                        this.selectedMapPoint = null;
                    }
                    if (this.lineDraftStart === index) {
                        this.lineDraftStart = null;
                    }
                    this.constellationMapPositions = { ...this.constellationMapPositions };
                },
                applyConstellationMapImageUrl() {
                    const value = String(this.constellationMapImageUrlInput || '').trim();
                    if (!value) return;
                    this.constellationMapImage = value;
                },
                onConstellationMapDrop(event) {
                    const file = event.dataTransfer?.files?.[0];
                    if (!file) return;
                    const input = this.$refs.constellationMapUploadInput;
                    if (input) {
                        const dt = new DataTransfer();
                        dt.items.add(file);
                        input.files = dt.files;
                    }
                    this.constellationMapImage = URL.createObjectURL(file);
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
                    if (this.armes.length > 6) {
                        const msg = 'Maximum 6 armes recommandées.';
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
                        this.applyBackgroundUrlInput();

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
                                versatilite: this.mainZone.versatilite,
                                fid_nations: this.mainZone.fid_nation ? [this.mainZone.fid_nation] : [],
                                background_actif: this.mainZone.background_actif || null,
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
                        await this.saveHistoires();

                        if (armesResult.saved && !this.histoiresError) {
                            this.showToast('Modifications sauvegardées', 'success');
                        } else {
                            this.showToast('Zone principale sauvegardée (certaines sections ont échoué)', 'error');
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
                    handleConstellationDescInput(event, index) {
                        const val = event.target.value;
                        const cursor = event.target.selectionStart;
                        const textBeforeCursor = val.slice(0, cursor);

                        // Slash menu : détection de /mot en cours de frappe
                        if (!this.showAptitudePicker) {
                            const slashMatch = textBeforeCursor.match(/\/(\w*)$/);
                            if (slashMatch) {
                                this.slashMenuQuery = slashMatch[1];
                                this.slashMenuOpen = true;
                                this.slashMenuSelectedIndex = 0;
                                this.slashMenuSlotIndex = index;
                            } else {
                                this.slashMenuOpen = false;
                                this.slashMenuQuery = '';
                                this.slashMenuSlotIndex = null;
                            }
                        }

                        // Ouvrir le picker quand /aptitudes vient d'être tapé (fallback)
                        if (textBeforeCursor.endsWith('/aptitudes') && this.aptitudes.length > 0) {
                            this.aptitudePickerSlotIndex = index;
                            this.showAptitudePicker = true;
                            this.slashMenuOpen = false;
                            this.slashMenuQuery = '';
                            this.slashMenuSlotIndex = null;
                            return;
                        }

                        // Fermer le picker si /aptitudes n'est plus présent dans le texte
                        if (this.showAptitudePicker && this.aptitudePickerSlotIndex === index) {
                            if (!val.includes('/aptitudes')) {
                                this.showAptitudePicker = false;
                                this.aptitudePickerSlotIndex = null;
                            }
                        }
                    },
                    handleConstellationDescKeydown(event, index) {
                        if (this.slashMenuOpen && this.slashMenuSlotIndex === index) {
                            const cmds = this.getSlashCommands();
                            if (event.key === 'ArrowDown') {
                                event.preventDefault();
                                this.slashMenuSelectedIndex = (this.slashMenuSelectedIndex + 1) % Math.max(1, cmds.length);
                            } else if (event.key === 'ArrowUp') {
                                event.preventDefault();
                                this.slashMenuSelectedIndex = (this.slashMenuSelectedIndex - 1 + Math.max(1, cmds.length)) % Math.max(1, cmds.length);
                            } else if (event.key === 'Enter' || event.key === 'Tab') {
                                if (cmds.length > 0) {
                                    event.preventDefault();
                                    this.confirmSlashCommand(cmds[this.slashMenuSelectedIndex], index);
                                }
                            } else if (event.key === 'Escape') {
                                event.preventDefault();
                                this.slashMenuOpen = false;
                                this.slashMenuQuery = '';
                                this.slashMenuSlotIndex = null;
                            }
                        } else if (event.key === 'Escape') {
                            this.showAptitudePicker = false;
                            this.aptitudePickerSlotIndex = null;
                        }
                    },
                    getSlashCommands() {
                        const all = [
                            { value: '/aptitudes', label: 'aptitudes', description: 'Insérer une icône de compétence' },
                        ];
                        if (!this.slashMenuQuery) return all;
                        const q = this.slashMenuQuery.toLowerCase();
                        return all.filter(c => c.label.toLowerCase().startsWith(q));
                    },
                    confirmSlashCommand(cmd, slotIndex) {
                        const slot = this.constellationSlots[slotIndex];
                        const val = slot.descri_const || '';
                        const slashPos = val.lastIndexOf('/');
                        if (slashPos !== -1) {
                            slot.descri_const = val.slice(0, slashPos) + cmd.value;
                        }
                        this.slashMenuOpen = false;
                        this.slashMenuQuery = '';
                        this.slashMenuSelectedIndex = 0;
                        this.slashMenuSlotIndex = null;
                        if (cmd.value === '/aptitudes' && this.aptitudes.length > 0) {
                            this.aptitudePickerSlotIndex = slotIndex;
                            this.showAptitudePicker = true;
                        }
                    },
                    insertAptitudeTag(aptitudeIndex, slotIndex) {
                        const slot = this.constellationSlots[slotIndex];
                        const tag = `[aptitude:${aptitudeIndex + 1}]`;
                        slot.descri_const = (slot.descri_const || '').replace('/aptitudes', tag);
                        this.showAptitudePicker = false;
                        this.aptitudePickerSlotIndex = null;
                    },
                    renderDescriConst(text) {
                        if (!text) return '';
                        return String(text)
                            .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
                            .replace(/\[aptitude:(\d+)\]/g, (match, n) => {
                                const apt = this.aptitudes[parseInt(n) - 1];
                                if (!apt) return match;
                                const title = (apt.titre_apti || '').replace(/"/g, '&quot;');
                                return `<span class="inline-flex items-center gap-1 rounded bg-indigo-900/60 border border-indigo-500/50 px-1.5 py-0.5 text-xs font-semibold text-indigo-300">${title}</span>`;
                            });
                    },
                async saveConstellations() {
                    try {
                        // Envoie les constellations existantes + les nouveaux slots remplis
                        const payload = this.constellationSlots
                            .filter(s => {
                                if (s.id_const) return true;
                                return String(s.titre_const || '').trim() !== '' || String(s.descri_const || '').trim() !== '';
                            })
                            .map(s => ({
                                id_const: s.id_const ? Number(s.id_const) : null,
                                index: Number(s.index),
                                titre_const: String(s.titre_const || '').trim(),
                                descri_const: s.descri_const || '',
                            }));

                        if (!payload.length) {
                            this.showConstellationsModal = false;
                            this.showToast('Aucune constellation à sauvegarder', 'success');
                            return;
                        }

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

                        const json = await resp.json();
                        if (Array.isArray(json?.constellations)) {
                            this.constellations = json.constellations;
                            this.constellationSlots = (() => {
                                const slots = [];
                                for (let i = 1; i <= 6; i++) {
                                    const found = this.constellations.find(c => c.index === i) || this.constellations[i - 1];
                                    slots.push(found ? { ...found, index: i, label: 'C' + i } : {
                                        id_const: null, index: i, label: 'C' + i,
                                        titre_const: '', descri_const: '', image_url: ''
                                    });
                                }
                                return slots;
                            })();
                        }

                        this.constellationsError = '';
                        this.showConstellationsModal = false;
                        this.showToast('Constellations sauvegardées', 'success');
                    } catch (e) {
                        this.constellationsError = e?.message || 'Erreur sauvegarde constellations';
                        this.showToast(this.constellationsError, 'error');
                    }
                },
                async uploadAptitudeImage(event, index, isDrop = false) {
                    const file = isDrop
                        ? event.dataTransfer?.files?.[0]
                        : event.target?.files?.[0];
                    if (!file) return;

                    const apt = this.aptitudes[index];

                    // Pas encore en base : stocker le fichier en attente + preview locale
                    if (!apt?.id_aptitude) {
                        apt._pendingFile = file;
                        apt.image_url = URL.createObjectURL(file);
                        if (!isDrop && event.target) event.target.value = '';
                        return;
                    }

                    await this._doUploadAptitudeFile(file, apt.id_aptitude, index);
                    if (!isDrop && event.target) event.target.value = '';
                },
                async _doUploadAptitudeFile(file, id_aptitude, index) {
                    const form = new FormData();
                    form.append('image', file);
                    form.append('id_aptitude', String(id_aptitude));

                    try {
                        const resp = await fetch(data.uploadCompetencesUrl, {
                            method: 'POST',
                            headers: { 'X-CSRF-TOKEN': data.csrf },
                            body: form,
                        });

                        if (!resp.ok) {
                            this.showToast('Erreur upload image compétence', 'error');
                            return;
                        }

                        const j = await resp.json();
                        if (this.aptitudes[index]) {
                            this.aptitudes[index].image_url = j.url + '?t=' + Date.now();
                            this.aptitudes[index]._pendingFile = null;
                        }
                    } catch (e) {
                        this.showToast('Erreur upload image compétence', 'error');
                    }
                },
                openAptitudeForm(index) {
                    this.aptitudeFormIdx = index;
                    if (index === null) {
                        this.aptitudeFormData = {
                            titre_apti: '', descri_apti: '',
                            fid_TypeApti: this.typesApti[0]?.id || '',
                            image_url: '', _pendingFile: null, _dragging: false,
                        };
                    } else {
                        const a = this.aptitudes[index];
                        this.aptitudeFormData = { ...a, _pendingFile: null, _dragging: false };
                    }
                    this.aptitudeFormError = '';
                    this.aptitudeFormOpen = true;
                },
                async saveAptitudeForm() {
                    const d = this.aptitudeFormData;
                    if (!String(d.titre_apti || '').trim()) {
                        this.aptitudeFormError = 'Le nom est obligatoire.';
                        return;
                    }
                    if (!d.fid_TypeApti) {
                        this.aptitudeFormError = 'Veuillez sélectionner un type.';
                        return;
                    }
                    this.aptitudeFormSaving = true;
                    if (this.aptitudeFormIdx === null) {
                        this.aptitudes.push({ ...d });
                    } else {
                        Object.assign(this.aptitudes[this.aptitudeFormIdx], d);
                    }
                    this.aptitudeFormOpen = false;
                    await this.saveCompetences();
                    this.aptitudeFormSaving = false;
                },
                handleAptitudeFormImageFile(event) {
                    const file = event.target.files?.[0];
                    if (!file) return;
                    this.aptitudeFormData._pendingFile = file;
                    this.aptitudeFormData.image_url = URL.createObjectURL(file);
                    event.target.value = '';
                },
                handleAptitudeFormImageDrop(event) {
                    const file = event.dataTransfer?.files?.[0];
                    if (!file) return;
                    this.aptitudeFormData._pendingFile = file;
                    this.aptitudeFormData.image_url = URL.createObjectURL(file);
                },
                async removeAptitude(index) {
                    this.aptitudes.splice(index, 1);
                    await this.saveCompetences();
                },

                openHistoireForm(index) {
                    this.closeStoryCommandMenu();
                    this.closeStoryPicker();
                    this.histoireFormIdx = index;
                    if (index === null) {
                        this.histoireFormData = {
                            id_histoire: null,
                            titre_histoire: '',
                            histoire: '',
                        };
                    } else {
                        const histoire = this.histoires[index] || {};
                        this.histoireFormData = {
                            id_histoire: histoire.id_histoire || null,
                            titre_histoire: histoire.titre_histoire || '',
                            histoire: histoire.histoire || '',
                        };
                    }
                    this.histoiresError = '';
                    this.histoireFormOpen = true;
                    this.$nextTick(() => {
                        this.$refs?.histoireTextarea?.focus();
                    });
                },

                saveHistoireForm() {
                    const titre = String(this.histoireFormData.titre_histoire || '').trim();
                    const contenu = String(this.histoireFormData.histoire || '').trim();

                    if (!titre) {
                        this.histoiresError = 'Le titre de l\'histoire est obligatoire.';
                        return;
                    }
                    if (!contenu) {
                        this.histoiresError = 'Le texte de l\'histoire est obligatoire.';
                        return;
                    }

                    const payload = {
                        id_histoire: this.histoireFormData.id_histoire || null,
                        titre_histoire: titre,
                        histoire: contenu,
                        ordre: this.histoireFormIdx === null ? this.histoires.length + 1 : (this.histoireFormIdx + 1),
                    };

                    if (this.histoireFormIdx === null) {
                        this.histoires.push(payload);
                    } else {
                        this.histoires.splice(this.histoireFormIdx, 1, payload);
                    }

                    this.histoires = this.histoires.map((h, idx) => ({ ...h, ordre: idx + 1 }));
                    this.histoireFormOpen = false;
                    this.closeStoryCommandMenu();
                    this.histoiresError = '';
                },

                removeHistoire(index) {
                    this.histoires.splice(index, 1);
                    this.histoires = this.histoires.map((h, idx) => ({ ...h, ordre: idx + 1 }));
                },

                moveHistoireUp(index) {
                    if (index <= 0) return;
                    const current = this.histoires[index];
                    this.histoires[index] = this.histoires[index - 1];
                    this.histoires[index - 1] = current;
                    this.histoires = this.histoires.map((h, idx) => ({ ...h, ordre: idx + 1 }));
                },

                moveHistoireDown(index) {
                    if (index >= this.histoires.length - 1) return;
                    const current = this.histoires[index];
                    this.histoires[index] = this.histoires[index + 1];
                    this.histoires[index + 1] = current;
                    this.histoires = this.histoires.map((h, idx) => ({ ...h, ordre: idx + 1 }));
                },

                closeStoryCommandMenu() {
                    this.storyCommandMenu = {
                        open: false,
                        start: null,
                        end: null,
                        query: '',
                        selectedIndex: 0,
                    };
                },

                closeStoryPicker() {
                    this.storyPickerOpen = false;
                    this.storyPickerCommand = '';
                    this.storyPickerSearch = '';
                },

                storyCommandLabel(command) {
                    const labels = {
                        aptitudes: '/aptitudes',
                        armes: '/armes',
                        monstres: '/monstres',
                        boss: '/boss',
                    };
                    return labels[command] || ('/' + command);
                },

                storySourceForCommand(command) {
                    return Array.isArray(this.storyCommandSources?.[command])
                        ? this.storyCommandSources[command]
                        : [];
                },

                storyTokenType(command) {
                    const map = {
                        aptitudes: 'aptitude',
                        armes: 'arme',
                        monstres: 'monstre',
                        boss: 'boss',
                    };
                    return map[command] || command;
                },

                getStorySlashCommands(query = null) {
                    const all = [
                        { value: '/aptitudes', key: 'aptitudes', label: 'aptitudes', description: 'Insérer une aptitude' },
                        { value: '/armes', key: 'armes', label: 'armes', description: 'Insérer une arme' },
                        { value: '/boss', key: 'boss', label: 'boss', description: 'Insérer un boss' },
                        { value: '/monstres', key: 'monstres', label: 'monstres', description: 'Insérer un monstre' },
                    ];
                    const rawQuery = query ?? this.storyCommandMenu.query;
                    if (!rawQuery) return all;
                    const q = String(rawQuery || '').toLowerCase();
                    return all.filter(c => c.label.toLowerCase().startsWith(q));
                },

                filteredStoryPickerOptions() {
                    const source = this.storySourceForCommand(this.storyPickerCommand);
                    const q = String(this.storyPickerSearch || '').trim().toLowerCase();
                    if (!q) return source.slice(0, 80);
                    return source.filter(item => String(item.label || '').toLowerCase().includes(q)).slice(0, 80);
                },

                onHistoireTextareaInput(event) {
                    const textarea = event.target;
                    const value = String(textarea.value || '');
                    const cursor = textarea.selectionStart || 0;
                    const before = value.slice(0, cursor);

                    const commandOnly = before.match(/\/([a-z]*)$/i);
                    if (!commandOnly) {
                        this.closeStoryCommandMenu();
                        return;
                    }

                    const query = String(commandOnly[1] || '').toLowerCase();
                    const commands = this.getStorySlashCommands(query);

                    if (!commands.length) {
                        this.closeStoryCommandMenu();
                        return;
                    }

                    this.storyCommandMenu = {
                        open: true,
                        start: cursor - commandOnly[0].length,
                        end: cursor,
                        query,
                        selectedIndex: 0,
                    };
                },

                onHistoireTextareaKeydown(event) {
                    if (!this.storyCommandMenu.open) return;

                    if (event.key === 'ArrowDown') {
                        const cmds = this.getStorySlashCommands();
                        event.preventDefault();
                        this.storyCommandMenu.selectedIndex = (this.storyCommandMenu.selectedIndex + 1) % Math.max(1, cmds.length);
                        return;
                    }

                    if (event.key === 'ArrowUp') {
                        const cmds = this.getStorySlashCommands();
                        event.preventDefault();
                        this.storyCommandMenu.selectedIndex = (this.storyCommandMenu.selectedIndex - 1 + Math.max(1, cmds.length)) % Math.max(1, cmds.length);
                        return;
                    }

                    if (event.key === 'Enter' || event.key === 'Tab') {
                        const cmds = this.getStorySlashCommands();
                        if (cmds.length > 0) {
                            event.preventDefault();
                            this.confirmStorySlashCommand(cmds[this.storyCommandMenu.selectedIndex]);
                        }
                        return;
                    }

                    if (event.key === 'Escape') {
                        event.preventDefault();
                        this.closeStoryCommandMenu();
                    }
                },

                confirmStorySlashCommand(cmd) {
                    const textarea = this.$refs?.histoireTextarea;
                    if (!textarea) return;

                    const current = String(this.histoireFormData.histoire || '');
                    const start = Number(this.storyCommandMenu.start ?? current.length);
                    const end = Number(this.storyCommandMenu.end ?? current.length);
                    const replacement = cmd.value;

                    this.histoireFormData.histoire = current.slice(0, start) + replacement + current.slice(end);
                    this.storyPickerOpen = true;
                    this.storyPickerCommand = cmd.key;
                    this.storyPickerSearch = '';

                    this.$nextTick(() => {
                        const nextPos = start + replacement.length;
                        textarea.focus();
                        textarea.setSelectionRange(nextPos, nextPos);
                        this.closeStoryCommandMenu();
                    });
                },

                applyStoryPickerItem(item) {
                    if (!item) return;

                    const commandLiteral = this.storyCommandLabel(this.storyPickerCommand);
                    const tokenType = this.storyTokenType(this.storyPickerCommand);
                    const token = `[[${tokenType}:${item.key}|${item.label}]]`;
                    const current = String(this.histoireFormData.histoire || '');

                    const idx = current.lastIndexOf(commandLiteral);
                    if (idx !== -1) {
                        this.histoireFormData.histoire = current.slice(0, idx) + token + current.slice(idx + commandLiteral.length);
                    } else {
                        this.histoireFormData.histoire = current + (current.endsWith(' ') ? '' : ' ') + token;
                    }

                    this.$nextTick(() => {
                        const textarea = this.$refs?.histoireTextarea;
                        if (!textarea) return;
                        const nextPos = this.histoireFormData.histoire.length;
                        textarea.focus();
                        textarea.setSelectionRange(nextPos, nextPos);
                        this.closeStoryPicker();
                    });
                },

                async saveHistoires() {
                    const payload = this.histoires
                        .map((histoire, index) => ({
                            id_histoire: histoire.id_histoire || null,
                            titre_histoire: String(histoire.titre_histoire || '').trim(),
                            histoire: String(histoire.histoire || '').trim(),
                            ordre: index + 1,
                        }))
                        .filter(histoire => histoire.titre_histoire !== '' && histoire.histoire !== '');

                    if (!payload.length) {
                        this.histoiresError = 'Ajoute au moins une histoire avec titre et texte.';
                        this.showToast(this.histoiresError, 'error');
                        return;
                    }

                    try {
                        const resp = await fetch(data.saveHistoiresUrl, {
                            method: 'PUT',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': data.csrf,
                            },
                            body: JSON.stringify({ histoires: payload }),
                        });

                        const respJson = await resp.json().catch(() => ({}));

                        if (!resp.ok) {
                            let msg = 'Erreur sauvegarde histoires';
                            const firstKey = Object.keys(respJson?.errors || {})[0];
                            if (firstKey && respJson.errors[firstKey]?.[0]) {
                                msg = respJson.errors[firstKey][0];
                            }
                            this.histoiresError = msg;
                            this.showToast(msg, 'error');
                            return;
                        }

                        const ids = respJson.histoires_ids || [];
                        this.histoires = payload.map((histoire, index) => ({
                            ...histoire,
                            id_histoire: ids[index] || histoire.id_histoire || null,
                            ordre: index + 1,
                        }));

                        this.histoiresError = '';
                        this.showToast('Histoires sauvegardées', 'success');
                    } catch (error) {
                        this.histoiresError = 'Erreur réseau lors de la sauvegarde des histoires.';
                        this.showToast(this.histoiresError, 'error');
                    }
                },

                openTeamManager() {
                    this.teamManagerOpen = true;
                },

                openReactionSlotPicker() {
                    this.reactionSlotPickerOpen = true;
                },

                closeReactionSlotPicker() {
                    this.reactionSlotPickerOpen = false;
                },

                addReactionSlot(reactionName) {
                    const value = String(reactionName || '').trim();
                    if (!value) return;
                    const exists = this.teamReactionSlots.some(slot => String(slot.nom_reaction).toLowerCase() === value.toLowerCase());
                    if (!exists) {
                        this.teamReactionSlotDrafts = [...this.teamReactionSlotDrafts, value];
                    }
                    // Ne pas fermer le picker — l'utilisateur peut ajouter plusieurs slots en une passe.
                    // Le picker se met à jour automatiquement (reaction disparaît de availableReactionSlots).
                },

                removeReactionSlot(reactionName) {
                    const value = String(reactionName || '').trim();
                    if (!value) return;
                    if (this.teamsForReaction(value).length) {
                        this.showToast('Supprime d\'abord les teams de cette reaction', 'error');
                        return;
                    }
                    this.teamReactionSlotDrafts = (this.teamReactionSlotDrafts || []).filter(item => String(item).toLowerCase() !== value.toLowerCase());
                },

                teamsForReaction(reactionName) {
                    return (this.normalizedTeams || []).filter(team => String(team?.type_reaction || '').toLowerCase() === String(reactionName || '').toLowerCase());
                },

                reactionMeta(reactionName) {
                    return (this.teamReactions || []).find(reaction => String(reaction.nom_reaction || '').toLowerCase() === String(reactionName || '').toLowerCase()) || null;
                },

                openSlotPicker(slot, isAlt = false) {
                    this.teamSlotPickerSearch = '';
                    this.teamSlotPickerOpen = { slot: Number(slot), isAlt: !!isAlt };
                },

                closeSlotPicker() {
                    this.teamSlotPickerOpen = null;
                    this.teamSlotPickerSearch = '';
                },

                closeTeamForm() {
                    this.teamFormOpen = false;
                    this.teamConstructorAptitudes = [];
                    this.rotationSequence = [];
                    this.teamError = '';
                },

                selectFromPicker(person) {
                    const state = this.teamSlotPickerOpen;
                    if (!state) return;
                    const slot = Number(state.slot);
                    if (state.isAlt) {
                        if (!(this.teamForm.remplacants || []).find(r => Number(r.slot) === slot && Number(r.id_perso) === Number(person.id_perso))) {
                            this.teamForm.remplacants = [...(this.teamForm.remplacants || []), {
                                slot,
                                id_perso: Number(person.id_perso),
                                nom: person.nom,
                                icon: person.icon || null,
                                role_override: null,
                            }];
                        }
                    } else {
                        this.setTeamMember(slot, person.id_perso);
                    }
                    this.closeSlotPicker();
                },

                openTeamForm(team = null, reactionName = null) {
                    this.teamError = '';
                    this.teamConstructorAptitudes = [];
                    this.rotationSequence = [];
                    const safeTeams = Array.isArray(this.teams) ? this.teams.filter(t => t && typeof t === 'object') : [];

                    if (!team) {
                        const reaction = String(reactionName || '').trim();
                        if (!reaction) {
                            this.showToast('Cree d\'abord un slot de reaction', 'error');
                            return;
                        }

                        const reactionTeams = safeTeams.filter(t => String(t?.type_reaction || '').trim().toLowerCase() === reaction.toLowerCase());
                        if (reactionTeams.length >= 2) {
                            this.teamError = 'Maximum 2 équipes par réaction: Recommended et F2P.';
                            return;
                        }

                        const existingTags = new Set(reactionTeams.map(t => String(t.tag || '').toLowerCase()));
                        const defaultTag = existingTags.has('recommended') ? 'f2p' : 'recommended';

                        this.teamEditingId = null;
                        this.teamForm = {
                            type_reaction: reaction,
                            tag: defaultTag,
                            rotation: '',
                            membres: [
                                { slot: 1, id_perso: null, role_override: null },
                                { slot: 2, id_perso: null, role_override: null },
                                { slot: 3, id_perso: null, role_override: null },
                                { slot: 4, id_perso: null, role_override: null },
                            ],
                            remplacants: [],
                        };
                    } else {
                        this.teamEditingId = team.id_team;
                        const members = [1, 2, 3, 4].map(slot => {
                            const found = (team.membres || []).find(m => Number(m.slot) === slot);
                            return {
                                slot,
                                id_perso: found?.id_perso || null,
                                role_override: found?.role_override || null,
                            };
                        });
                        
                        let rotationFromDb = [];
                        try {
                            const parsed = JSON.parse(team.rotation || '[]');
                            if (Array.isArray(parsed)) {
                                rotationFromDb = parsed;
                            }
                        } catch (e) {
                            // Ignore parse errors
                        }
                        this.rotationSequence = rotationFromDb;

                        this.teamForm = {
                            type_reaction: team.type_reaction || '',
                            tag: team.tag || '',
                            rotation: team.rotation || '',
                            membres: members,
                            remplacants: (team.remplacants || []).map(r => ({
                                slot: Number(r.slot),
                                id_perso: Number(r.id_perso),
                                nom: r.nom,
                                icon: r.icon || null,
                                role_override: r.role_override || null,
                            })),
                        };
                    }
                    this.teamFormOpen = true;
                },

                setTeamMember(slot, idPerso) {
                    const idx = Number(slot) - 1;
                    if (idx < 0 || idx > 3) return;
                    const member = this.teamPool.find(p => Number(p.id_perso) === Number(idPerso));
                    this.teamForm.membres[idx] = {
                        slot: Number(slot),
                        id_perso: member ? Number(member.id_perso) : null,
                        role_override: this.teamForm.membres[idx]?.role_override || null,
                    };
                },

                clearTeamMember(slot) {
                    const idx = Number(slot) - 1;
                    if (idx < 0 || idx > 3) return;
                    this.teamForm.membres[idx] = {
                        slot: Number(slot),
                        id_perso: null,
                        role_override: this.teamForm.membres[idx]?.role_override || null,
                    };
                },

                isTeamMemberTakenByOtherSlot(slot, idPerso) {
                    const targetSlot = Number(slot);
                    const targetId = Number(idPerso);
                    return (this.teamForm.membres || []).some(m => Number(m.slot) !== targetSlot && Number(m.id_perso) === targetId);
                },

                teamMemberForSlot(slot) {
                    const member = (this.teamForm.membres || []).find(m => Number(m.slot) === Number(slot));
                    if (!member?.id_perso) return null;
                    return this.teamPool.find(p => Number(p.id_perso) === Number(member.id_perso)) || null;
                },

                setTeamMemberRole(slot, role) {
                    const idx = Number(slot) - 1;
                    if (idx < 0 || idx > 3) return;
                    this.teamForm.membres[idx] = {
                        ...this.teamForm.membres[idx],
                        role_override: String(role || '').trim() || null,
                    };
                },

                teamRemplacantsBySlot(slot) {
                    return (this.teamForm.remplacants || []).filter(r => Number(r.slot) === Number(slot));
                },

                pushRemplacant(slot) {
                    const picker = document.getElementById(`team-rpl-picker-${slot}`);
                    if (!picker || !picker.value) return;
                    const idPerso = Number(picker.value);
                    const member = this.teamPool.find(p => Number(p.id_perso) === idPerso);
                    if (!member) return;
                    if (this.teamForm.remplacants.find(r => Number(r.slot) === Number(slot) && Number(r.id_perso) === idPerso)) return;

                    this.teamForm.remplacants.push({
                        slot: Number(slot),
                        id_perso: idPerso,
                        nom: member.nom,
                        role_override: null,
                    });
                    picker.value = '';
                },

                removeRemplacant(slot, idPerso) {
                    this.teamForm.remplacants = this.teamForm.remplacants.filter(
                        r => !(Number(r.slot) === Number(slot) && Number(r.id_perso) === Number(idPerso))
                    );
                },

                setRemplacantRole(slot, idPerso, role) {
                    this.teamForm.remplacants = this.teamForm.remplacants.map(r => {
                        if (Number(r.slot) !== Number(slot) || Number(r.id_perso) !== Number(idPerso)) return r;
                        return { ...r, role_override: String(role || '').trim() || null };
                    });
                },

                async saveTeam() {
                    this.teamError = '';
                    const payload = {
                        type_reaction: String(this.teamForm.type_reaction || '').trim(),
                        tag: this.teamForm.tag || null,
                        rotation: String(this.teamForm.rotation || '').trim(),
                        membres: this.teamForm.membres.map(m => ({
                            slot: Number(m.slot),
                            id_perso: m.id_perso ? Number(m.id_perso) : null,
                            role_override: m.role_override || null,
                        })),
                        remplacants: (this.teamForm.remplacants || []).map(r => ({
                            slot: Number(r.slot),
                            id_perso: Number(r.id_perso),
                            role_override: r.role_override || null,
                        })),
                    };

                    if (!payload.type_reaction) {
                        this.teamError = 'Selectionne d\'abord un slot de reaction.';
                        return;
                    }
                    if (!payload.tag) {
                        this.teamError = 'Selectionne Recommended ou F2P.';
                        return;
                    }
                    if (payload.membres.some(m => !m.id_perso)) {
                        this.teamError = 'Les 4 slots membres doivent être remplis.';
                        return;
                    }

                    this.teamSaving = true;
                    try {
                        const isEdit = !!this.teamEditingId;
                        const url = isEdit ? `${data.updateTeamUrlBase}/${this.teamEditingId}` : data.storeTeamUrl;
                        const resp = await fetch(url, {
                            method: isEdit ? 'PUT' : 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': data.csrf,
                            },
                            body: JSON.stringify(payload),
                        });

                        const json = await resp.json().catch(() => ({}));
                        if (!resp.ok) {
                            this.teamError = json.message || Object.values(json.errors || {})[0]?.[0] || 'Erreur lors de la sauvegarde de la team.';
                            return;
                        }

                        const savedTeam = json && typeof json === 'object' && json.team && typeof json.team === 'object' ? json.team : null;
                        if (!savedTeam) {
                            this.teamError = json?.message || 'Réponse serveur invalide lors de la sauvegarde de la team.';
                            return;
                        }

                        const safeTeams = Array.isArray(this.teams)
                            ? this.teams.filter(team => team && typeof team === 'object')
                            : [];
                        const index = safeTeams.findIndex(t => Number(t?.id_team) === Number(savedTeam.id_team));
                        this.teams = index === -1 ? [...safeTeams, savedTeam] : safeTeams.map((team, i) => i === index ? savedTeam : team);

                        this.teamFormOpen = false;
                        this.showToast(isEdit ? 'Team mise à jour' : 'Team créée', 'success');
                    } catch (e) {
                        this.teamError = e?.message || 'Erreur réseau.';
                    } finally {
                        this.teamSaving = false;
                    }
                },

                async deleteTeam(idTeam) {
                    if (!confirm('Supprimer cette team ?')) return;
                    try {
                        const resp = await fetch(`${data.deleteTeamUrlBase}/${idTeam}`, {
                            method: 'DELETE',
                            headers: { 'X-CSRF-TOKEN': data.csrf },
                        });
                        if (!resp.ok) {
                            this.showToast('Erreur suppression team', 'error');
                            return;
                        }
                        const safeTeams = Array.isArray(this.teams)
                            ? this.teams.filter(team => team && typeof team === 'object')
                            : [];
                        this.teams = safeTeams.filter(t => Number(t?.id_team) !== Number(idTeam));
                        this.showToast('Team supprimée', 'success');
                    } catch (e) {
                        this.showToast('Erreur réseau', 'error');
                    }
                },

                allTeamSlotsFilledValidation() {
                    return this.teamForm.membres.every(m => m.id_perso);
                },

                async loadTeamAptitudes() {
                    this.teamAptitudesLoading = true;
                    try {
                        const idTeam = this.teamEditingId;
                        
                        if (!idTeam) {
                            this.teamError = 'Enregistre d\'abord la team pour charger les aptitudes.';
                            this.teamAptitudesLoading = false;
                            return;
                        }

                        const url = `${data.teamAptitudesUrlBase}`.replace('id_team', idTeam);

                        const resp = await fetch(url, {
                            headers: { 'X-CSRF-TOKEN': data.csrf },
                        });
                        const json = await resp.json().catch(() => ({}));
                        
                        if (!resp.ok) {
                            this.teamError = json.message || 'Erreur chargement aptitudes.';
                            return;
                        }

                        this.teamConstructorAptitudes = json.members || [];
                        this.rotationSequence = [];
                    } catch (e) {
                        this.teamError = e?.message || 'Erreur réseau.';
                    } finally {
                        this.teamAptitudesLoading = false;
                    }
                },

                addToRotationSequence(apt) {
                    this.rotationSequence.push({
                        id_aptitude: apt.id_aptitude,
                        fid_perso: apt.fid_perso,
                        nom_perso: apt.nom_perso,
                        titre: apt.titre,
                        type: apt.type,
                        icon: apt.icon,
                    });
                },

                removeFromRotationSequence(idx) {
                    this.rotationSequence.splice(idx, 1);
                },

                async saveRotationSequence() {
                    if (this.rotationSequence.length === 0) {
                        this.showToast('La séquence est vide', 'error');
                        return;
                    }

                    if (!this.teamEditingId) {
                        this.teamError = 'Enregistre d\'abord la team.';
                        return;
                    }

                    this.teamRotationSaving = true;
                    try {
                        const payload = {
                            rotation: JSON.stringify(this.rotationSequence),
                        };

                        const url = `${data.updateTeamRotationUrlBase}`.replace('id_team', this.teamEditingId);

                        const resp = await fetch(url, {
                            method: 'PATCH',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': data.csrf,
                            },
                            body: JSON.stringify(payload),
                        });

                        const json = await resp.json().catch(() => ({}));
                        if (!resp.ok) {
                            this.teamError = json.message || 'Erreur sauvegarde rotation.';
                            return;
                        }

                        const safeTeams = Array.isArray(this.teams)
                            ? this.teams.filter(team => team && typeof team === 'object')
                            : [];
                        const index = safeTeams.findIndex(t => Number(t?.id_team) === Number(this.teamEditingId));
                        if (index !== -1) {
                            this.teams = safeTeams.map((team, i) => i === index ? { ...team, rotation: payload.rotation } : team);
                        }

                        this.showToast('Séquence sauvegardée', 'success');
                    } catch (e) {
                        this.teamError = e?.message || 'Erreur réseau.';
                    } finally {
                        this.teamRotationSaving = false;
                    }
                },

                sortedMembers(team) {
                    const members = Array.isArray(team?.membres) ? team.membres.filter(member => member && typeof member === 'object') : [];
                    return [...members].sort((a, b) => Number(a.slot) - Number(b.slot));
                },

                slotRemplacants(team, slot) {
                    const list = Array.isArray(team?.remplacants) ? team.remplacants.filter(item => item && typeof item === 'object') : [];
                    return list.filter(r => Number(r.slot) === Number(slot));
                },

                teamReactionEmoji(reaction) {
                    const key = String(reaction || '').toLowerCase();
                    if (key.includes('vaporize')) return '🔥';
                    if (key.includes('melt')) return '❄️';
                    if (key.includes('freeze')) return '🧊';
                    if (key.includes('hyperbloom') || key.includes('bloom')) return '🌸';
                    if (key.includes('aggravate') || key.includes('spread')) return '⚡';
                    return '✨';
                },

                teamElementColor(element) {
                    const key = String(element || '').toLowerCase();
                    const map = {
                        pyro: '#ff8a5b',
                        hydro: '#5ba2ff',
                        anemo: '#58d0ad',
                        electro: '#af88ff',
                        cryo: '#90d7ff',
                        geo: '#e0bc6d',
                        dendro: '#7ccf62',
                    };
                    return map[key] || 'rgba(255,255,255,.2)';
                },

                groupDrawerOpen(groupKey) {
                    return !!this.teamDrawerState[groupKey];
                },

                toggleGroupDrawer(groupKey) {
                    this.teamDrawerState = {
                        ...this.teamDrawerState,
                        [groupKey]: !this.groupDrawerOpen(groupKey),
                    };
                },

                recommendedReplacementsOpen(teamId) {
                    return !!this.teamRecommendedOpen[String(teamId)];
                },

                toggleRecommendedReplacements(teamId) {
                    const key = String(teamId);
                    this.teamRecommendedOpen = {
                        ...this.teamRecommendedOpen,
                        [key]: !this.recommendedReplacementsOpen(teamId),
                    };
                },


                async saveCompetences() {
                    try {
                        const payload = this.aptitudes.map(a => ({
                            id_aptitude: a.id_aptitude || null,
                            titre_apti: String(a.titre_apti || '').trim(),
                            descri_apti: a.descri_apti || null,
                            fid_TypeApti: Number(a.fid_TypeApti),
                            lvl_apt: a.lvl_apt || 1,
                            sub_Apt: a.sub_Apt || null,
                        })).filter(a => a.titre_apti && a.fid_TypeApti);

                        if (this.aptitudes.length > 0 && !payload.length) {
                            this.showToast('Aucune compétence valide à enregistrer', 'error');
                            return;
                        }

                        const resp = await fetch(data.saveCompetencesUrl, {
                            method: 'PUT',
                            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': data.csrf },
                            body: JSON.stringify({ competences: payload }),
                        });

                        const respJson = await resp.json().catch(() => ({}));

                        if (!resp.ok) {
                            let msg = 'Erreur sauvegarde compétences';
                            const firstKey = Object.keys(respJson?.errors || {})[0];
                            if (firstKey && respJson.errors[firstKey]?.[0]) msg = respJson.errors[firstKey][0];
                            this.aptitudeFormError = msg;
                            this.showToast(msg, 'error');
                            return;
                        }

                        this.aptitudeFormError = '';

                        // Upload des images en attente (nouvelles compétences)
                        const ids = respJson.competences_ids || [];
                        const pendingUploads = [];
                        for (let i = 0; i < this.aptitudes.length; i++) {
                            const apt = this.aptitudes[i];
                            if (apt._pendingFile && ids[i]) {
                                apt.id_aptitude = ids[i];
                                pendingUploads.push(this._doUploadAptitudeFile(apt._pendingFile, ids[i], i));
                            }
                        }
                        if (pendingUploads.length) await Promise.all(pendingUploads);

                        this.showToast('Compétences sauvegardées', 'success');
                    } catch (e) {
                        this.aptitudeFormError = e?.message || 'Erreur sauvegarde compétences';
                        this.showToast(this.aptitudeFormError, 'error');
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
                async uploadConstellationImageSlot(event, slotIndex) {
                    const file = event.target.files?.[0];
                    if (!file) return;

                    const slot = this.constellationSlots[slotIndex];
                    const constIndex = slot ? slot.index : slotIndex + 1;

                    const form = new FormData();
                    form.append('image', file);
                    form.append('constellation_index', String(constIndex));

                    try {
                        const resp = await fetch(data.uploadConstellationUrl, {
                            method: 'POST',
                            headers: { 'X-CSRF-TOKEN': data.csrf },
                            body: form,
                        });

                        if (!resp.ok) {
                            this.showToast('Erreur upload image C' + constIndex, 'error');
                            return;
                        }

                        const j = await resp.json();
                        this.constellationSlots[slotIndex].image_url = j.url + '?t=' + Date.now();
                        this.showToast('Image C' + constIndex + ' mise à jour', 'success');
                    } catch (e) {
                        this.showToast('Erreur upload image C' + constIndex, 'error');
                    } finally {
                        event.target.value = '';
                    }
                },
                async submitConstellationMapAjax(event) {
                    const form = event.target;
                    const fd = new FormData();
                    // Positions JSON
                    fd.append('positions_const', this.constellationMapPositionsJson);
                    // File upload if selected
                    const fileInput = form.querySelector('input[name="constellation_map_image"]');
                    if (fileInput && fileInput.files.length > 0) {
                        fd.append('constellation_map_image', fileInput.files[0]);
                    }
                    // URL si renseignée
                    const urlInput = form.querySelector('input[name="constellation_map_image_url"]');
                    if (urlInput && urlInput.value.trim()) {
                        fd.append('constellation_map_image_url', urlInput.value.trim());
                    }
                    try {
                        const resp = await fetch(data.saveConstellationMapUrl, {
                            method: 'POST',
                            headers: { 'X-CSRF-TOKEN': data.csrf, 'Accept': 'application/json' },
                            body: fd,
                        });
                        const j = await resp.json();
                        if (resp.ok && j.success) {
                            if (j.image_url) {
                                this.constellationMapImage = j.image_url + '?t=' + Date.now();
                            }
                            this.showToast('Carte constellation enregistrée', 'success');
                        } else {
                            const firstErr = Object.values(j?.errors || {})[0]?.[0] || 'Erreur sauvegarde';
                            this.showToast(firstErr, 'error');
                        }
                    } catch (e) {
                        this.showToast('Erreur réseau', 'error');
                    }
                },
                async uploadImage(event, type) {
                    const file = event.target.files[0];
                    if (!file) return;
                    const form = new FormData();
                    form.append('image_type', type);
                    form.append('image', file);
                    const uploadUrl = data.uploadMainZoneImageUrl || data.saveMainZoneUrl.replace('main-zone', 'main-zone/upload-image');

                    try {
                        const resp = await fetch(uploadUrl, {
                            method: 'POST',
                            headers: { 'X-CSRF-TOKEN': data.csrf, 'Accept': 'application/json' },
                            body: form,
                        });

                        if (!resp.ok) {
                            this.showToast('Erreur upload image', 'error');
                            return;
                        }

                        const j = await resp.json();
                        const t = Date.now();
                        if (type === 'portrait') { this.portraitPreview = `${j.url}?t=${t}`; this.fullPreview = `${j.url}?t=${t}`; }
                        if (type === 'full')     { this.fullPreview = `${j.url}?t=${t}`; }
                        if (type === 'icone')    { this.iconePreview = `${j.url}?t=${t}`; }
                    } catch (e) {
                        this.showToast('Erreur upload image', 'error');
                    } finally {
                        event.target.value = '';
                    }
                },
            };
        }
    </script>


</x-admin-layout>