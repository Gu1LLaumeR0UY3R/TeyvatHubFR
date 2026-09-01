<x-app-layout>
<x-slot name="title">{{ $personnage->nom_perso }}</x-slot>

@php
    $photoUrl = function ($photo) {
        if (!$photo) {
            return null;
        }

        if ($photo->source_url) {
            return $photo->source_url;
        }

        if (filter_var($photo->chemin_photo, FILTER_VALIDATE_URL)) {
            return $photo->chemin_photo;
        }

        return asset('storage/' . ltrim($photo->chemin_photo, '/'));
    };

    $iconPhoto = $personnage->photos->where('type', 'icone')->first() ?? $personnage->photos->first();
    $portraitPhoto = $personnage->photos->where('type', 'portrait')->first() ?? $personnage->photos->first();

    $iconeUrl = $photoUrl($iconPhoto) ?? asset('images/placeholder.svg');
    $portraitUrl = $photoUrl($portraitPhoto) ?? asset('images/placeholder.svg');

    $elementIcon = asset('images/placeholder.svg');
    if ($personnage->element) {
        $elPhoto = $personnage->element->photos->first();
        if ($elPhoto) {
            $elementIcon = $photoUrl($elPhoto) ?? $elementIcon;
        } else {
            $file = public_path('storage/photos/elements/icones/' . strtolower($personnage->element->libelle_element) . '.png');
            if (file_exists($file)) {
                $elementIcon = asset('storage/photos/elements/icones/' . strtolower($personnage->element->libelle_element) . '.png');
            }
        }
    }

    $nation = $personnage->nations->first();
    $nationIcon = asset('images/placeholder.svg');
    if ($nation) {
        $slug = $nation->slug ?? \Illuminate\Support\Str::slug($nation->nom_region);
        $file = public_path('storage/photos/regions/icones/' . $slug . '.png');
        if (file_exists($file)) {
            $nationIcon = asset('storage/photos/regions/icones/' . $slug . '.png');
        } elseif (isset($nation->icone_url)) {
            $nationIcon = $nation->icone_url;
        }
    }

    $weaponTypeIcon = asset('images/placeholder.svg');
    if ($personnage->typeArme) {
        $weaponName = strtolower(trim((string) $personnage->typeArme->libelle_TArme));
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
            $path = public_path('storage/photos/armes/' . $weaponIconMap[$weaponName]);
            if (file_exists($path)) {
                $weaponTypeIcon = asset('storage/photos/armes/' . $weaponIconMap[$weaponName]);
            }
        }

        $typePhoto = $personnage->typeArme->photos->first();
        if ($weaponTypeIcon === asset('images/placeholder.svg') && $typePhoto) {
            $weaponTypeIcon = $photoUrl($typePhoto) ?? $weaponTypeIcon;
        }
    }

    $videoUrls = $personnage->videos->pluck('url_video')->filter()->values();

    $heroBackgroundUrl = null;
    if (!empty($personnage->background_actif)) {
        $rawBackground = (string) $personnage->background_actif;
        $heroBackgroundUrl = filter_var($rawBackground, FILTER_VALIDATE_URL)
            ? $rawBackground
            : asset('storage/' . ltrim($rawBackground, '/'));
    }

    $heroInlineStyle = null;
    if ($heroBackgroundUrl) {
        $safeHeroBackgroundUrl = str_replace("'", "\\'", $heroBackgroundUrl);
        $heroInlineStyle = "background-image: linear-gradient(160deg, rgba(255,255,255,0.065), rgba(255,255,255,0.015)), linear-gradient(180deg, rgba(10,15,35,0.82), rgba(10,15,35,0.62)), url('"
            . $safeHeroBackgroundUrl
            . "'); background-size: auto, auto, cover; background-position: center;";
    }

    $constellationImageFor = function ($constellation, string $slug, int $index) use ($photoUrl): string {
        if ($constellation?->photo) {
            return $photoUrl($constellation->photo) ?? asset('images/placeholder.svg');
        }

        $base = 'photos/personnages/constellations/' . $slug . '-c' . $index;
        foreach (['webp', 'png', 'jpg', 'jpeg'] as $ext) {
            $path = $base . '.' . $ext;
            if (\Illuminate\Support\Facades\Storage::disk('public')->exists($path)) {
                return asset('storage/' . $path);
            }
        }

        return asset('images/placeholder.svg');
    };

    $constellations = $personnage->constellations
        ->sortBy('id_const')
        ->values()
        ->map(function ($constellation, $idx) use ($personnage, $constellationImageFor) {
            return [
                'label' => 'C' . ($idx + 1),
                'titre_const' => $constellation->titre_const,
                'descri_const' => $constellation->descri_const,
                'image_url' => $constellationImageFor($constellation, $personnage->slug, $idx + 1),
                'recommandee' => (bool) $constellation->recommandee,
            ];
        })
        ->values();

    $constCarte = $personnage->constellations->sortBy('id_const')->first();
    $constellationMapPositions = [];
    $constellationMapLines = [];
    $constellationMapImage = '';

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

            $constellationMapPositions[$key] = [
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

            $constellationMapLines[] = ['from' => $from, 'to' => $to];
        }
    }

    if ($constCarte?->photo) {
        $constellationMapImage = $photoUrl($constCarte->photo) ?? '';
    }

    $storyReferences = is_array($storyReferences ?? null) ? $storyReferences : [];
    $rotationTeams = collect($teamsRotationJson ?? [])->keyBy('tag');

    $weaponOrderIndex = array_flip([1, 4, 2, 5, 3, 6]);
    $orderedWeaponRecommendations = $personnage->armesRecommandees
        ->sortBy(function ($item) use ($weaponOrderIndex) {
            $position = (int) ($item->position ?? 0);
            return $weaponOrderIndex[$position] ?? (100 + $position);
        })
        ->values();

    $renderStoryHtml = function (?string $text) use ($storyReferences) {
        $raw = (string) ($text ?? '');
        if ($raw === '') {
            return '';
        }

        $pattern = '/\[\[(aptitude|arme|boss|monstre):([^\]|]+)\|([^\]]+)\]\]/i';
        $offset = 0;
        $chunks = [];

        preg_match_all($pattern, $raw, $matches, PREG_OFFSET_CAPTURE);

        foreach ($matches[0] as $idx => $fullMatch) {
            $matchedText = $fullMatch[0];
            $start = $fullMatch[1];

            if ($start > $offset) {
                $chunks[] = nl2br(e(substr($raw, $offset, $start - $offset)));
            }

            $type = strtolower((string) ($matches[1][$idx][0] ?? ''));
            $key = (string) ($matches[2][$idx][0] ?? '');
            $label = (string) ($matches[3][$idx][0] ?? '');

            $entry = $storyReferences[$type][$key] ?? null;

            if (!$entry) {
                $chunks[] = e($matchedText);
                $offset = $start + strlen($matchedText);
                continue;
            }

            $safeLabel = e($label !== '' ? $label : ($entry['label'] ?? $key));
            $safeImage = e((string) ($entry['image'] ?? asset('images/placeholder.svg')));
            $safeUrl = e((string) ($entry['url'] ?? '#'));
            $isAnchor = str_starts_with((string) ($entry['url'] ?? ''), '#');
            $targetAttrs = $isAnchor ? '' : ' target="_blank" rel="noopener"';

            $chunks[] = '<a href="' . $safeUrl . '" class="th-story-ref"' . $targetAttrs . '>'
                . $safeLabel
                . '<span class="th-story-ref-popover"><img src="' . $safeImage . '" alt="' . $safeLabel . '"></span>'
                . '</a>';

            $offset = $start + strlen($matchedText);
        }

        if ($offset < strlen($raw)) {
            $chunks[] = nl2br(e(substr($raw, $offset)));
        }

        return implode('', $chunks);
    };
@endphp

<style>
    .character-show-hero { --csh-panel: rgba(13, 18, 42, 0.72); --csh-border: rgba(255,255,255,0.12); --csh-text: #eef2ff; --csh-muted: #bdc8ec; --csh-accent: #6fd0be; max-width: min(2100px, 98vw); margin:0 auto 2.5rem; padding:2.35rem; position:relative; border-radius: 22px; border:1px solid var(--csh-border); background: linear-gradient(160deg, rgba(255,255,255,0.065), rgba(255,255,255,0.015)), linear-gradient(180deg, rgba(10,15,35,0.9), rgba(10,15,35,0.74)); box-shadow: 0 24px 56px rgba(5,9,28,0.52), inset 0 1px 0 rgba(255,255,255,0.07); display:grid; grid-template-columns: clamp(260px,22vw,400px) minmax(0,1fr); grid-template-areas: "portrait hero" "portrait video" "portrait meta"; column-gap: 1.8rem; row-gap:1.15rem; align-items:start; color: var(--csh-text); font-family:'Space Grotesk', 'Trebuchet MS', sans-serif; }
    .character-show-hero[data-element="anemo"] { --csh-accent:#74C2A8; }
    .character-show-hero[data-element="geo"] { --csh-accent:#f2be42; }
    .character-show-hero[data-element="electro"] { --csh-accent:#b88ef8; }
    .character-show-hero[data-element="dendro"] { --csh-accent:#9ecf34; }
    .character-show-hero[data-element="hydro"] { --csh-accent:#67c5ff; }
    .character-show-hero[data-element="pyro"] { --csh-accent:#ff8550; }
    .character-show-hero[data-element="cryo"] { --csh-accent:#91d8ee; }

    .csh-portrait { border-radius:16px; overflow:hidden; border:1px solid rgba(255,255,255,0.15); background: linear-gradient(180deg, rgba(8,12,30,.4), rgba(8,12,30,.18)); width: 100%; max-width: 1200px; height: 860px; grid-area:portrait; }
    .csh-full { border-radius:16px; overflow:hidden; border:1px solid rgba(255,255,255,0.15); background: linear-gradient(180deg, rgba(8,12,30,.4), rgba(8,12,30,.18)); width: min(100%, 1020px); aspect-ratio: 16 / 9; grid-area:video; position:relative; justify-self:center; margin-inline:auto; }
    .csh-portrait img { width:100%; height:100%; object-fit:cover; }
    .csh-hero { grid-area:hero; padding-bottom:0.8rem; border-bottom:1px solid rgba(255,255,255,0.08); }
    .csh-hero-head { display:flex; align-items:center; gap:.8rem; }
    .csh-name { font-family:'Cinzel', Georgia, serif; font-size: clamp(2rem,4vw,3rem); margin:0; line-height:1.05; color:#eff6ff; }
    .csh-icon-img { width:42px; height:42px; border-radius:999px; overflow:hidden; border:1px solid rgba(255,255,255,0.35); background:rgba(255,255,255,0.12); }
    .csh-icon-img img { width:100%; height:100%; object-fit:cover; }
    .csh-meta { grid-area:meta; display:grid; grid-template-columns: repeat(2,minmax(0,1fr)); gap:.6rem; padding-top:.75rem; }
    .csh-pill { background: linear-gradient(145deg, rgba(18,25,55,0.85), rgba(10,15,35,0.75)); border:1px solid rgba(255,255,255,0.13); border-radius:16px; padding:.85rem 1rem; min-height:80px; display:flex; flex-direction:column; justify-content:center; gap:.3rem; transition: border-color .2s, box-shadow .2s; }
    .csh-pill:hover { border-color: rgba(255,255,255,0.22); box-shadow: 0 4px 20px rgba(0,0,0,.35); }
    .csh-pill-label { color:var(--csh-muted); font-size:.65rem; letter-spacing:.12em; text-transform:uppercase; font-weight:600; }
    .csh-pill-value { color:var(--csh-text); font-size:1.05rem; font-weight:700; }
    .csh-pill--element { border-color: rgba(var(--csh-accent-rgb, 111,208,190), 0.35); background: linear-gradient(145deg, rgba(18,30,55,0.9), rgba(10,18,40,0.82)); }
    .csh-pill--element .csh-pill-value { color: var(--csh-accent); font-weight:800; font-size:1.1rem; }
    .csh-pill--element .csh-pill-label { color: color-mix(in srgb, var(--csh-accent) 60%, var(--csh-muted)); }
    .csh-pill img { width:24px; height:24px; border-radius:50%; }

    .csh-preview-table { display:grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 1.25rem; margin: 0 0 1.8rem; }
    .csh-preview-panel { border: 1px solid rgba(255,255,255,0.12); border-radius: 18px; background: linear-gradient(180deg, rgba(15,23,42,0.92), rgba(8,13,30,0.9)); box-shadow: 0 18px 40px rgba(2, 6, 23, 0.32); overflow: hidden; }
    .csh-preview-panel-head { display:flex; align-items:center; justify-content:space-between; gap:.75rem; padding: 1rem 1.15rem; border-bottom: 1px solid rgba(255,255,255,0.08); }
    .csh-preview-panel-title { color:#e5eefc; font-size:.92rem; font-weight:700; letter-spacing:.04em; text-transform:uppercase; }
    .csh-preview-panel-subtitle { color:#8aa0ca; font-size:.72rem; }

    .csh-preview-weapon-list { display:grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap:1rem; padding: 1.05rem 1.2rem 1.2rem; }
    .csh-weapon-item { position:relative; border:1px solid rgba(148,163,184,0.35); border-radius:0.6rem; background: linear-gradient(180deg, rgba(18, 28, 55, 0.86), rgba(10, 16, 34, 0.88)); padding:.55rem; display:flex; align-items:center; gap:.75rem; min-height:72px; text-decoration:none; transition:border-color .2s ease, transform .2s ease; }
    .csh-weapon-item:hover { border-color: rgba(125,211,252,.55); transform: translateY(-1px); }
    .csh-weapon-index { width:26px; height:26px; border-radius:999px; display:flex; align-items:center; justify-content:center; font-size:.72rem; font-weight:700; color:#eff6ff; background: rgba(255,255,255,0.08); border:1px solid rgba(255,255,255,0.1); flex-shrink:0; }
    .csh-weapon-icon-wrap { width:48px; height:48px; border-radius:12px; flex-shrink:0; display:flex; align-items:center; justify-content:center; border:1px solid rgba(255,255,255,0.22); box-shadow: inset 0 1px 0 rgba(255,255,255,0.12); }
    .csh-weapon-icon-wrap img { width:34px; height:34px; object-fit:contain; filter: drop-shadow(0 3px 5px rgba(0,0,0,.35)); }
    .csh-weapon-copy { min-width:0; flex:1; }
    .csh-weapon-name { font-weight:700; color:#e2e8f0; }
    .csh-weapon-type { color:#98a8c7; font-size:.72rem; }
    .csh-weapon-badge { display:inline-flex; align-items:center; margin-top:.35rem; padding:.18rem .45rem; border-radius:999px; font-size:.68rem; font-weight:700; background: rgba(16, 185, 129, 0.18); color: #b9f7df; border:1px solid rgba(52, 211, 153, 0.34); }
    .csh-weapon-tooltip { position:absolute; top:50%; right:10px; transform: translateY(-50%) translateX(10px); width:250px; border:1px solid rgba(125,211,252,.35); border-radius:12px; background: rgba(6,12,25,.98); box-shadow: 0 18px 32px rgba(2,6,23,.5); padding:.65rem .7rem; opacity:0; pointer-events:none; transition: opacity .16s ease, transform .16s ease; z-index: 15; }
    .csh-weapon-item:hover .csh-weapon-tooltip { opacity:1; transform: translateY(-50%) translateX(0); }
    .csh-weapon-tooltip-title { color:#e2e8f0; font-size:.76rem; font-weight:700; margin-bottom:.4rem; }
    .csh-weapon-tooltip-row { display:flex; justify-content:space-between; gap:.55rem; font-size:.7rem; color:#bfdbfe; padding:.16rem 0; }
    .csh-weapon-tooltip-row strong { color:#f8fafc; }
    .csh-weapon-empty, .csh-artefact-empty { padding:1rem 1.15rem 1.15rem; color:#8fa1c5; font-size:.85rem; font-style:italic; }

    .csh-preview-artefact-list { display:grid; gap:.85rem; padding: 1rem 1.15rem 1.15rem; }
    .csh-artefact-item { border:1px solid rgba(148,163,184,0.3); border-radius:14px; padding:.85rem .95rem; background: linear-gradient(180deg, rgba(17, 24, 39, 0.9), rgba(9, 14, 27, 0.92)); }
    .csh-artefact-head { display:flex; align-items:center; justify-content:space-between; gap:.75rem; margin-bottom:.5rem; }
    .csh-artefact-title { color:#e2e8f0; font-size:.9rem; font-weight:700; }
    .csh-artefact-piece { color:#fef3c7; font-size:.72rem; font-weight:700; }
    .csh-artefact-row { display:flex; align-items:center; justify-content:space-between; gap:.75rem; padding:.35rem 0; }
    .csh-artefact-name { color:#cbd5e1; font-size:.82rem; }

    .csh-rotations-shell { margin: 0 0 1.5rem; border: 1px solid rgba(255,255,255,0.12); border-radius: 18px; background: linear-gradient(180deg, rgba(12, 17, 34, 0.95), rgba(6, 10, 22, 0.94)); box-shadow: 0 18px 40px rgba(2, 6, 23, 0.32); overflow: hidden; }
    .csh-rotations-grid { padding: 1rem 1.15rem 1.2rem; display:grid; grid-template-columns: repeat(2, minmax(0,1fr)); gap: .9rem; }
    .csh-rotation-card { border: 1px solid rgba(148,163,184,.24); border-radius: 14px; background: rgba(15,23,42,.5); padding: .85rem; display:grid; gap:.65rem; }
    .csh-rotation-tag { display:inline-flex; align-items:center; border-radius: 999px; padding: .16rem .56rem; font-size: .66rem; font-weight: 800; letter-spacing: .05em; text-transform: uppercase; border: 1px solid transparent; }
    .csh-rotation-tag.recommended { background: rgba(52,211,153,.18); color:#b9f7df; border-color: rgba(52,211,153,.36); }
    .csh-rotation-tag.f2p { background: rgba(56,189,248,.18); color:#c9efff; border-color: rgba(56,189,248,.36); }
    .csh-rotation-reaction { color:#9fb2d7; font-size:.76rem; }
    .csh-rotation-members { display:grid; grid-template-columns: repeat(2, minmax(0,1fr)); gap:.5rem; }
    .csh-rotation-btn { justify-self:start; border:1px solid rgba(99,102,241,.45); background: rgba(79,70,229,.2); color:#dbe4ff; border-radius:10px; padding:.35rem .7rem; font-size:.72rem; font-weight:700; }
    .csh-rotation-btn:hover { background: rgba(79,70,229,.34); }

    .csh-rotation-modal-bg { position: fixed; inset: 0; z-index: 70; background: rgba(2,6,23,.72); backdrop-filter: blur(2px); display:flex; align-items:center; justify-content:center; padding:1rem; }
    .csh-rotation-modal { width: min(760px, 96vw); max-height: 90vh; overflow:auto; border: 1px solid rgba(148,163,184,.32); border-radius: 16px; background: linear-gradient(180deg, rgba(15,23,42,.98), rgba(8,14,30,.98)); box-shadow: 0 28px 60px rgba(2,6,23,.55); padding: 1rem; }
    .csh-rotation-modal-head { display:flex; align-items:center; justify-content:space-between; gap:.75rem; border-bottom:1px solid rgba(148,163,184,.24); padding-bottom:.7rem; margin-bottom:.8rem; }
    .csh-rotation-close { border:1px solid rgba(148,163,184,.35); color:#cbd5e1; background: rgba(15,23,42,.65); border-radius:10px; padding:.35rem .6rem; font-size:.72rem; }
    .csh-rotation-close:hover { background: rgba(30,41,59,.8); }
    .csh-rotation-flow { color:#dbe4ff; font-size:.88rem; line-height:1.5; white-space:pre-wrap; border:1px solid rgba(99,102,241,.28); border-radius:12px; background: rgba(30,41,59,.42); padding:.75rem; }


    .csh-constellation-shell { margin: 0 1.5rem 1.5rem; border: 1px solid rgba(255,255,255,0.12); border-radius: 18px; background: linear-gradient(180deg, rgba(10, 15, 30, 0.95), rgba(5, 10, 24, 0.92)); box-shadow: 0 18px 40px rgba(2, 6, 23, 0.32); overflow: hidden; }
    .csh-constellation-grid { display:grid; grid-template-columns: minmax(220px, 320px) minmax(0, 1fr); min-height: 360px; }
    .csh-constellation-media { padding: 1rem; border-right: 1px solid rgba(255,255,255,0.08); background: radial-gradient(circle at top, rgba(125, 211, 252, 0.12), rgba(15, 23, 42, 0.4)); }
    .csh-constellation-frame { height: 100%; min-height: 300px; border: 1px solid rgba(255,255,255,0.12); border-radius: 14px; overflow: hidden; background: linear-gradient(180deg, rgba(30,41,59,0.7), rgba(15,23,42,0.92)); display:flex; align-items:center; justify-content:center; position:relative; transition: border-color .25s ease, box-shadow .25s ease, transform .25s ease; }
    .csh-constellation-frame.is-glowing { border-color: rgba(125, 211, 252, 0.9); box-shadow: 0 0 0 1px rgba(125, 211, 252, 0.35), 0 0 28px rgba(56, 189, 248, 0.35), inset 0 0 24px rgba(125, 211, 252, 0.2); transform: scale(1.01); }
    .csh-constellation-frame img { width:100%; height:100%; object-fit:cover; }
    .csh-constellation-frame.is-glowing img { animation: csh-constellation-flash .65s ease-out; filter: saturate(1.18) brightness(1.12); }
    .csh-constellation-map-wrap { position:absolute; inset:0; pointer-events:none; }
    .csh-constellation-map-line { position:absolute; height:2px; transform-origin:0 50%; transition: background .2s ease, box-shadow .2s ease, opacity .2s ease; }
    .csh-constellation-map-line.is-off { background: rgba(148,163,184,.42); box-shadow: 0 0 0 1px rgba(148,163,184,.18); opacity: .55; }
    .csh-constellation-map-line.is-on { background: linear-gradient(90deg, #7dd3fc, #38bdf8); box-shadow: 0 0 0 1px rgba(56,189,248,.25), 0 0 14px rgba(56,189,248,.45); opacity: 1; }
    .csh-constellation-map-point { position:absolute; transform: translate(-50%, -50%); width:36px; height:36px; border-radius:999px; border:2px solid #fff; display:inline-flex; align-items:center; justify-content:center; font-size:11px; font-weight:700; font-family:inherit; padding:0; margin:0; overflow:visible; cursor:pointer; pointer-events:auto; background:#334155; transition: box-shadow .2s ease, transform .2s ease, border-color .2s ease; }
    .csh-constellation-map-point:hover { transform: translate(-50%, -50%) scale(1.08); }
    .csh-constellation-map-point:focus-visible { outline: 2px solid #7dd3fc; outline-offset: 2px; }
    .csh-constellation-map-point-img { width:100%; height:100%; border-radius:999px; object-fit:cover; display:block; pointer-events:none; }
    .csh-constellation-map-point-fallback { color:#cbd5e1; pointer-events:none; }
    .csh-constellation-map-point.is-off { box-shadow: 0 2px 8px rgba(2,6,23,.35); filter: grayscale(.55) brightness(.8); }
    .csh-constellation-map-point.is-on { box-shadow: 0 0 0 2px rgba(14,165,233,.28), 0 0 16px rgba(14,165,233,.42); }
    .csh-constellation-map-point.is-current { transform: translate(-50%, -50%) scale(1.12); box-shadow: 0 0 0 2px rgba(125,211,252,.35), 0 0 18px rgba(125,211,252,.55); }
    .csh-constellation-map-point.is-recommended { border-color:#4ade80; box-shadow: 0 0 0 3px rgba(74,222,128,.35), 0 0 18px rgba(74,222,128,.55); }
    .csh-constellation-map-point.is-recommended.is-current { box-shadow: 0 0 0 3px rgba(74,222,128,.45), 0 0 22px rgba(74,222,128,.65); }
    .csh-constellation-map-point-star { position:absolute; top:-6px; right:-6px; width:16px; height:16px; border-radius:999px; background:#22c55e; color:#052e16; display:flex; align-items:center; justify-content:center; font-size:9px; line-height:1; border:1.5px solid rgba(5,46,22,.6); box-shadow: 0 0 6px rgba(74,222,128,.7); pointer-events:none; }
    .csh-constellation-empty-media { color:#93a7cb; font-size:.82rem; text-align:center; padding:0 1rem; }
    .csh-constellation-content { padding: 1rem 1.15rem 1.15rem; display:flex; flex-direction:column; gap:.9rem; justify-content:center; }
    .csh-constellation-hint { color:#93a7cb; font-size:.82rem; line-height:1.5; }
    .csh-constellation-legend { display:flex; align-items:center; gap:.45rem; margin-top:.6rem; color:#bbf7d0; font-size:.76rem; }
    .csh-constellation-legend-dot { width:12px; height:12px; border-radius:999px; border:2px solid #4ade80; box-shadow: 0 0 8px rgba(74,222,128,.55); flex-shrink:0; }
    .csh-constellation-title { color:#f1f5f9; font-size:1rem; font-weight:700; }
    .csh-constellation-desc { color:#cbd5e1; font-size:.84rem; line-height:1.45; margin-top:.45rem; white-space:pre-wrap; }
    .csh-constellation-modal-bg { position: fixed; inset: 0; z-index: 70; background: rgba(2,6,23,.72); backdrop-filter: blur(2px); display:flex; align-items:center; justify-content:center; padding:1rem; }
    .csh-constellation-modal { width: min(480px, 94vw); border: 1px solid rgba(148,163,184,.32); border-radius: 16px; background: linear-gradient(180deg, rgba(15,23,42,.98), rgba(8,14,30,.98)); box-shadow: 0 28px 60px rgba(2,6,23,.55); padding: 1.1rem; }
    .csh-constellation-modal-head { display:flex; align-items:center; justify-content:space-between; gap:.75rem; border-bottom:1px solid rgba(148,163,184,.24); padding-bottom:.7rem; margin-bottom:.8rem; }
    .csh-constellation-modal-badge { display:inline-flex; align-items:center; gap:.3rem; margin-top:.3rem; font-size:.72rem; font-weight:700; color:#4ade80; }

    @keyframes csh-constellation-flash {
        0% { filter: saturate(1) brightness(1); }
        35% { filter: saturate(1.35) brightness(1.3); }
        100% { filter: saturate(1.18) brightness(1.12); }
    }

    @media (max-width: 900px) {
        .character-show-hero { grid-template-columns: 1fr; grid-template-areas: "hero" "portrait" "video" "meta"; padding:1.1rem; }
        .csh-portrait { min-height: 280px; height: auto; }
        .csh-preview-table { grid-template-columns: 1fr; margin: 0 .5rem 1rem; }
        .csh-preview-weapon-list { grid-template-columns: 1fr; }
        .csh-rotations-grid { grid-template-columns: 1fr; }
        .csh-constellation-grid { grid-template-columns: 1fr; }
        .csh-constellation-media { border-right:0; border-bottom:1px solid rgba(255,255,255,0.08); }
    }


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

    .th-story-ref {
        position: relative;
        display: inline-flex;
        align-items: center;
        padding: 0.05rem 0.38rem;
        margin: 0 0.12rem;
        border-radius: 999px;
        border: 1px solid rgba(125, 211, 252, 0.5);
        background: rgba(14, 116, 144, 0.2);
        color: #d8f0ff;
        font-weight: 600;
        text-decoration: none;
        transition: border-color .18s, background .18s;
    }
    .th-story-ref:hover {
        border-color: rgba(125, 211, 252, 0.85);
        background: rgba(14, 116, 144, 0.35);
    }
    .th-story-ref-popover {
        position: absolute;
        left: 50%;
        bottom: calc(100% + 8px);
        transform: translateX(-50%) scale(.98);
        width: 136px;
        height: 136px;
        border-radius: 12px;
        overflow: hidden;
        border: 1px solid rgba(255,255,255,0.2);
        box-shadow: 0 14px 30px rgba(0,0,0,.45);
        background: #0f172a;
        opacity: 0;
        pointer-events: none;
        transition: opacity .16s, transform .16s;
        z-index: 40;
    }
    .th-story-ref-popover img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    .th-story-ref:hover .th-story-ref-popover {
        opacity: 1;
        transform: translateX(-50%) scale(1);
    }
</style>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('personnageShowData', () => ({
        videos: @json($videoUrls->values()),
        rotationTeams: @json($rotationTeams->values()),
        activeRotationTeam: null,
        selectedVideoIndex: 0,
        constellations: @json($constellations->values()),
        selectedConstellationIndex: 0,
        constellationGlow: false,
        constellationPopupOpen: false,
        constellationMapImage: @json($constellationMapImage),
        constellationMapPositions: @json($constellationMapPositions),
        constellationMapLines: @json($constellationMapLines),
        constellationMapNaturalWidth: 0,
        constellationMapNaturalHeight: 0,
        aptitudes: @json($aptitudesJson->values()),
        toEmbed(url) {
            if (!url) return '';
            const m = String(url).match(/(?:youtu\.be\/|youtube\.com\/(?:watch\?v=|embed\/|v\/))([A-Za-z0-9_-]{11})/);
            if (m) return 'https://www.youtube-nocookie.com/embed/' + m[1];
            return String(url).startsWith('http') ? String(url) : '';
        },
        get activeEmbedUrl() {
            const current = this.videos[this.selectedVideoIndex] || '';
            return this.toEmbed(current);
        },
        nextVideo() {
            if (!this.videos.length) return;
            this.selectedVideoIndex = (this.selectedVideoIndex + 1) % this.videos.length;
        },
        prevVideo() {
            if (!this.videos.length) return;
            this.selectedVideoIndex = (this.selectedVideoIndex - 1 + this.videos.length) % this.videos.length;
        },
        teamByTag(tag) {
            return (this.rotationTeams || []).find(team => String(team.tag || '').toLowerCase() === String(tag || '').toLowerCase()) || null;
        },
        openRotation(tag) {
            this.activeRotationTeam = this.teamByTag(tag);
        },
        closeRotation() {
            this.activeRotationTeam = null;
        },
        get activeConstellation() {
            if (!this.constellations.length) return null;
            const idx = Math.max(0, Math.min(this.selectedConstellationIndex, this.constellations.length - 1));
            return this.constellations[idx] || null;
        },
        get activeConstellationLevel() {
            return Math.max(1, Math.min(6, Number(this.selectedConstellationIndex || 0) + 1));
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
            const point = this.constellationMapPositions[String(index)];
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
        constellationPointClass(index) {
            const idx = Number(index);
            let classes = idx === this.activeConstellationLevel ? 'is-current' : (idx <= this.activeConstellationLevel ? 'is-on' : 'is-off');
            if (this.constellationPointRecommended(idx)) {
                classes += ' is-recommended';
            }
            return classes;
        },
        constellationPointImage(index) {
            const data = this.constellations[Number(index) - 1];
            return data?.image_url || '';
        },
        constellationPointRecommended(index) {
            const data = this.constellations[Number(index) - 1];
            return !!(data && data.recommandee);
        },
        openConstellationPopup(index) {
            if (!this.constellations[Number(index) - 1]) return;
            this.selectConstellation(Number(index) - 1);
            this.constellationPopupOpen = true;
        },
        closeConstellationPopup() {
            this.constellationPopupOpen = false;
        },
        constellationLineClass(line) {
            const from = Number(line?.from || 0);
            const to = Number(line?.to || 0);
            const isOn = from <= this.activeConstellationLevel && to <= this.activeConstellationLevel;
            return isOn ? 'is-on' : 'is-off';
        },
        updateConstellationMapNaturalSize(event) {
            const image = event?.target;
            if (!image) return;
            if (image.naturalWidth && image.naturalHeight) {
                this.constellationMapNaturalWidth = image.naturalWidth;
                this.constellationMapNaturalHeight = image.naturalHeight;
            }
        },
        selectConstellation(index) {
            this.selectedConstellationIndex = Number(index);
            this.constellationGlow = false;
            this.$nextTick(() => {
                this.constellationGlow = true;
                setTimeout(() => {
                    this.constellationGlow = false;
                }, 650);
            });
        },
        renderDescriConst(text) {
            if (!text) return '';
            return String(text)
                .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
                .replace(/\[aptitude:(\d+)\]/g, (match, n) => {
                    const apt = this.aptitudes[parseInt(n) - 1];
                    if (!apt) return match;
                    const title = (apt.titre_apti || '').replace(/"/g, '&quot;');
                    const anchor = `#aptitude-${apt.id_aptitude}`;
                    return `<a href="${anchor}" class="inline-flex items-center gap-1 rounded bg-indigo-900/60 border border-indigo-500/50 px-1.5 py-0.5 text-xs font-semibold text-indigo-300 hover:text-indigo-100 hover:border-indigo-400 transition-colors">${title}</a>`;
                });
        },
    }));
});
</script>

<div class="max-w-[120rem] mx-auto px-4 sm:px-6 lg:px-8 py-8"
     x-data="personnageShowData()">

    <nav class="mb-6 text-sm text-hub-text-sec">
        <a href="{{ route('personnages.index') }}" class="hover:text-hub-primary">Personnages</a>
        <span class="mx-2">/</span>
        <span class="text-hub-text">{{ $personnage->nom_perso }}</span>
    </nav>

            <div class="character-show-hero"
                data-element="{{ strtolower($personnage->element?->libelle_element ?? 'geo') }}"
                @if($heroInlineStyle) style="{{ $heroInlineStyle }}" @endif>
        <section class="csh-full mx-auto flex items-center justify-center text-center p-4">
            <div class="absolute inset-0 bg-[radial-gradient(circle_at_center,rgba(255,255,255,0.05),rgba(0,0,0,0.55))]"></div>
            <template x-if="activeEmbedUrl">
                <iframe :src="activeEmbedUrl" frameborder="0" allowfullscreen class="absolute inset-0 z-10 w-full h-full rounded-[16px]"></iframe>
            </template>
            <template x-if="!activeEmbedUrl">
                <div class="z-10 text-white/60 text-sm">Aucune vidéo</div>
            </template>
            <button x-show="videos.length > 1" type="button" @click="prevVideo()" class="csh-video-nav csh-video-nav--prev">&#8249;</button>
            <button x-show="videos.length > 1" type="button" @click="nextVideo()" class="csh-video-nav csh-video-nav--next">&#8250;</button>
            <div x-show="videos.length > 1" class="csh-video-counter">
                <span x-text="selectedVideoIndex + 1"></span>&thinsp;/&thinsp;<span x-text="videos.length"></span>
            </div>
        </section>

        <section class="csh-portrait">
            <img src="{{ $portraitUrl }}" alt="{{ $personnage->nom_perso }}" />
        </section>

        <div class="csh-hero">
            <div class="csh-hero-head">
                <div class="csh-icon-img"><img src="{{ $iconeUrl }}" alt="Icône" /></div>
                <div class="csh-name">{{ $personnage->nom_perso }}</div>
            </div>
            @if($personnage->roles->count())
                <div class="mt-3 flex flex-wrap gap-2">
                    @foreach($personnage->roles as $role)
                        <span class="px-2 py-1 bg-white/10 text-white text-xs rounded-md">{{ $role->libelle_role }}</span>
                    @endforeach
                </div>
            @endif
        </div>

        <div class="csh-meta">
            <div class="csh-pill csh-pill--element">
                <span class="csh-pill-label">Élément</span>
                <div class="flex items-center gap-2.5 mt-1">
                    <img src="{{ $elementIcon }}" alt="" class="w-6 h-6 rounded-full ring-1 ring-white/20" />
                    <span class="csh-pill-value">{{ $personnage->element?->libelle_element ?? 'Inconnu' }}</span>
                </div>
            </div>
            <div class="csh-pill">
                <span class="csh-pill-label">Arme</span>
                <div class="flex items-center gap-2.5 mt-1">
                    <img src="{{ $weaponTypeIcon }}" alt="" class="w-6 h-6 rounded-full ring-1 ring-white/20" />
                    <span class="csh-pill-value">{{ $personnage->typeArme?->libelle_TArme ?? 'Inconnu' }}</span>
                </div>
            </div>
            <div class="csh-pill">
                <span class="csh-pill-label">Rareté</span>
                <span class="csh-pill-value mt-1">{{ $personnage->etoile?->libelle ?? '?' }}</span>
            </div>
            <div class="csh-pill">
                <span class="csh-pill-label">Nation</span>
                <div class="flex items-center gap-2.5 mt-1">
                    <img src="{{ $nationIcon }}" alt="" class="w-6 h-6 rounded-full ring-1 ring-white/20" />
                    <span class="csh-pill-value">{{ $nation?->nom_region ?? 'Inconnue' }}</span>
                </div>
            </div>
        </div>
    </div>

    <section class="csh-preview-table">
        <div class="csh-preview-panel">
            <div class="csh-preview-panel-head">
                <div>
                    <div class="csh-preview-panel-title">Armes</div>
                    <div class="csh-preview-panel-subtitle">Affichage public des recommandations</div>
                </div>
                <div class="text-xs text-slate-400">{{ $orderedWeaponRecommendations->count() }} arme(s)</div>
            </div>

            @if($orderedWeaponRecommendations->count())
                <div class="csh-preview-weapon-list">
                    @foreach($orderedWeaponRecommendations as $index => $armeRec)
                        @php
                            $arme = $armeRec->arme;
                            $rarityLabel = $arme?->etoile?->libelle ?? '?★';
                            $rarityStars = (int) preg_replace('/\D+/', '', (string) $rarityLabel);
                            if ($rarityStars < 1 || $rarityStars > 5) {
                                $rarityStars = (int) ($arme?->fid_etoile ?? 1);
                            }
                            $weaponIcon = $photoUrl($arme?->photos->first()) ?? asset('images/placeholder.svg');
                            $statsLvl1 = $arme?->statsNiveaux?->firstWhere('lvl_ASN', 1) ?? $arme?->statsNiveaux?->first();
                            $statsLvl90 = $arme?->statsNiveaux?->firstWhere('lvl_ASN', 90) ?? $arme?->statsNiveaux?->last();
                        @endphp
                        <a href="{{ $arme ? route('armes.show', $arme) : '#' }}" class="csh-weapon-item">
                            <div class="csh-weapon-index">{{ $armeRec->position ?? ($index + 1) }}</div>
                            <div class="csh-weapon-icon-wrap th-weapon-rarity-{{ max(1, min(5, $rarityStars)) }}">
                                <img src="{{ $weaponIcon }}" alt="{{ $arme?->nom_arme ?? 'Arme' }}">
                            </div>
                            <div class="csh-weapon-copy">
                                <div class="csh-weapon-name truncate">{{ $arme?->nom_arme ?? 'Arme inconnue' }}</div>
                                <div class="csh-weapon-type">{{ $rarityLabel }} · {{ $arme?->typeArme?->libelle_TArme ?? 'Type inconnu' }}</div>
                                @if($armeRec->starter)
                                    <div class="csh-weapon-badge">Starter</div>
                                @endif
                            </div>

                            <div class="csh-weapon-tooltip">
                                <div class="csh-weapon-tooltip-title">Stats de l'arme</div>
                                <div class="csh-weapon-tooltip-row">
                                    <span>Lvl 1 · {{ $arme?->main_stat_type ?? 'Stat principale' }}</span>
                                    <strong>{{ $statsLvl1?->main_stat ?? '-' }}</strong>
                                </div>
                                <div class="csh-weapon-tooltip-row" @if(!$statsLvl1?->subs_stats) style="display:none" @endif>
                                    <span>{{ $arme?->sub_stat_type ?? 'Sub stat' }}</span>
                                    <strong>{{ $statsLvl1?->subs_stats ?? '-' }}</strong>
                                </div>
                                <div class="csh-weapon-tooltip-row">
                                    <span>Lvl 90 · {{ $arme?->main_stat_type ?? 'Stat principale' }}</span>
                                    <strong>{{ $statsLvl90?->main_stat ?? '-' }}</strong>
                                </div>
                                <div class="csh-weapon-tooltip-row" @if(!$statsLvl90?->subs_stats) style="display:none" @endif>
                                    <span>{{ $arme?->sub_stat_type ?? 'Sub stat' }}</span>
                                    <strong>{{ $statsLvl90?->subs_stats ?? '-' }}</strong>
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>
            @else
                <div class="csh-weapon-empty">Aucune arme recommandée pour le moment.</div>
            @endif
        </div>

        <div class="csh-preview-panel">
            <div class="csh-preview-panel-head">
                <div>
                    <div class="csh-preview-panel-title">Artefacts</div>
                    <div class="csh-preview-panel-subtitle">Affichage public des builds</div>
                </div>
                <div class="text-xs text-slate-400">{{ $personnage->artefactsRecommandees->count() }} build(s)</div>
            </div>

            @if($personnage->artefactsRecommandees->count())
                <div class="csh-preview-artefact-list">
                    @foreach($personnage->artefactsRecommandees as $index => $build)
                        <article class="csh-artefact-item">
                            <div class="csh-artefact-head">
                                <div class="csh-artefact-title">Build {{ $index + 1 }}</div>
                                <div class="csh-artefact-piece">{{ $build->artefact2 ? '2P + 2P' : '4P' }}</div>
                            </div>
                            <div class="csh-artefact-row">
                                <span class="csh-artefact-name">{{ $build->artefact1?->nom_artefact ?? 'Set principal' }}</span>
                                <span class="csh-artefact-piece">{{ strtoupper((string) $build->pieces_1) }}</span>
                            </div>
                            @if($build->artefact2)
                                <div class="csh-artefact-row">
                                    <span class="csh-artefact-name">{{ $build->artefact2?->nom_artefact }}</span>
                                    <span class="csh-artefact-piece">{{ strtoupper((string) $build->pieces_2) }}</span>
                                </div>
                            @endif
                        </article>
                    @endforeach
                </div>
            @else
                <div class="csh-artefact-empty">Aucun artefact recommandé pour le moment.</div>
            @endif
        </div>
    </section>

    <section class="csh-rotations-shell">
        <div class="csh-preview-panel-head">
            <div>
                <div class="csh-preview-panel-title">Rotations de Team</div>
                <div class="csh-preview-panel-subtitle">Deux rotations disponibles: F2P et Recommended</div>
            </div>
            <div class="text-xs text-slate-400" x-text="`${rotationTeams.length}/2`"></div>
        </div>

        <div class="csh-rotations-grid">
            <template x-for="tag in ['recommended', 'f2p']" :key="`rotation-${tag}`">
                <article class="csh-rotation-card">
                    <div class="flex items-center justify-between gap-2">
                        <span class="csh-rotation-tag" :class="tag" x-text="tag === 'recommended' ? 'Recommended' : 'F2P'"></span>
                        <span class="csh-rotation-reaction" x-text="teamByTag(tag)?.type_reaction || 'Aucune réaction'"></span>
                    </div>

                    <template x-if="teamByTag(tag)">
                        <div class="csh-rotation-members">
                            <template x-for="member in (teamByTag(tag)?.membres || [])" :key="`rotation-member-${tag}-${member.slot}`">
                                <img :src="member.icon || '{{ asset('images/placeholder.svg') }}'" :alt="member.nom" :title="`${member.nom} (${member.element || 'Element'})`" />
                            </template>
                        </div>
                    </template>

                    <template x-if="teamByTag(tag)">
                        <button type="button" class="csh-rotation-btn" @click="openRotation(tag)">Voir rotation</button>
                    </template>

                    <template x-if="!teamByTag(tag)">
                        <div class="text-sm italic text-slate-400">Pas encore configurée.</div>
                    </template>
                </article>
            </template>
        </div>
    </section>

    <template x-if="activeRotationTeam">
        <div class="csh-rotation-modal-bg" @click.self="closeRotation()">
            <div class="csh-rotation-modal">
                <div class="csh-rotation-modal-head">
                    <div>
                        <div class="csh-preview-panel-title" x-text="activeRotationTeam.tag === 'recommended' ? 'Rotation Recommended' : 'Rotation F2P'"></div>
                        <div class="csh-preview-panel-subtitle" x-text="activeRotationTeam.type_reaction || 'Sans réaction'"></div>
                    </div>
                    <button type="button" class="csh-rotation-close" @click="closeRotation()">Fermer</button>
                </div>

                <div class="csh-rotation-members mb-3">
                    <template x-for="member in (activeRotationTeam.membres || [])" :key="`rotation-modal-member-${member.slot}`">
                        <div class="rounded-lg border border-slate-700 bg-slate-900/60 p-2">
                            <div class="flex items-center gap-2 mb-2">
                                <img :src="member.icon || '{{ asset('images/placeholder.svg') }}'" :alt="member.nom"
                                     style="width:36px;height:36px;border-radius:999px;border:2px solid rgba(255,255,255,.22);object-fit:cover;flex-shrink:0;" />
                                <div class="text-sm font-semibold text-slate-100" x-text="member.nom"></div>
                            </div>
                            <template x-if="member.slot <= 4 && member.aptitudes && member.aptitudes.length > 0">
                                <div class="flex flex-col gap-1">
                                    <template x-for="(apt, ai) in member.aptitudes" :key="`apt-${member.slot}-${ai}`">
                                        <div class="flex items-center gap-2">
                                            <img :src="apt.icon || '{{ asset('images/placeholder.svg') }}'"
                                                 :alt="apt.titre"
                                                 style="width:22px;height:22px;border-radius:6px;object-fit:cover;flex-shrink:0;border:1px solid rgba(148,163,184,.2);" />
                                            <div class="text-xs text-slate-300 leading-tight">
                                                <span class="text-slate-400 mr-1" x-text="apt.type + ' :'"></span>
                                                <span x-text="apt.titre"></span>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </template>
                        </div>
                    </template>
                </div>

                <div class="flex items-center gap-3 flex-wrap">
                    <template x-if="activeRotationTeam.rotationSequence && activeRotationTeam.rotationSequence.length > 0">
                        <template x-for="(apt, idx) in activeRotationTeam.rotationSequence" :key="`rotation-step-${idx}`">
                            <div class="relative group">
                                <img :src="apt.icon || '{{ asset('images/placeholder.svg') }}'"
                                     :alt="`${apt.nom_perso} - ${apt.titre}`"
                                     style="width:48px;height:48px;border-radius:8px;object-fit:cover;border:2px solid rgba(99,102,241,.4);cursor:pointer;transition:all 0.2s;" />
                                <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none z-40">
                                    <div class="bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-xs text-slate-200 whitespace-nowrap shadow-lg">
                                        <div class="font-semibold text-slate-100" x-text="apt.nom_perso"></div>
                                        <div class="text-slate-400" x-text="apt.titre"></div>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </template>
                    <template x-if="!activeRotationTeam.rotationSequence || activeRotationTeam.rotationSequence.length === 0">
                        <div class="text-sm text-slate-400 italic">Rotation non renseignée.</div>
                    </template>
                </div>
            </div>
        </div>
    </template>

    <section class="csh-constellation-shell">
        <div class="csh-preview-panel-head">
            <div>
                <div class="csh-preview-panel-title">Constellations</div>
                <div class="csh-preview-panel-subtitle">Même logique d'affichage que dans la preview admin</div>
            </div>
            <div class="text-xs text-slate-400">{{ $constellations->count() }} constellation(s)</div>
        </div>

        @if($constellations->count())
            <div class="csh-constellation-grid">
                <div class="csh-constellation-media">
                    <div class="csh-constellation-frame" x-ref="constellationPublicCanvas" :class="constellationGlow ? 'is-glowing' : ''">
                        <template x-if="constellationMapImage">
                            <div class="csh-constellation-map-wrap" :style="mapMediaStyle('constellationPublicCanvas')">
                                <img :src="constellationMapImage"
                                     alt="Carte constellation"
                                     @load="updateConstellationMapNaturalSize($event)"
                                     style="object-fit: contain; object-position: center;">

                                <template x-for="(line, idx) in constellationMapLines" :key="`public-map-line-${idx}`">
                                    <template x-if="lineIsValid(line)">
                                        <div class="csh-constellation-map-line"
                                             :class="constellationLineClass(line)"
                                             :style="mapLineStyle(line, 'constellationPublicCanvas')"></div>
                                    </template>
                                </template>

                                <template x-for="index in [1,2,3,4,5,6]" :key="`public-map-point-${index}`">
                                    <template x-if="constellationMapPositions[String(index)]">
                                        <button type="button"
                                             class="csh-constellation-map-point"
                                             :class="constellationPointClass(index)"
                                             :style="mapPointStyle(index)"
                                             :aria-label="`Voir la constellation C${index}`"
                                             @click="openConstellationPopup(index)">
                                            <template x-if="constellationPointImage(index)">
                                                <img class="csh-constellation-map-point-img" :src="constellationPointImage(index)" :alt="`Constellation C${index}`">
                                            </template>
                                            <template x-if="!constellationPointImage(index)">
                                                <span class="csh-constellation-map-point-fallback" x-text="index"></span>
                                            </template>
                                            <template x-if="constellationPointRecommended(index)">
                                                <span class="csh-constellation-map-point-star" title="Constellation recommandée">★</span>
                                            </template>
                                        </button>
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
                    <div class="csh-constellation-hint">
                        Cliquez sur une constellation sur la carte pour afficher son descriptif.
                        <template x-if="constellations.some(c => c.recommandee)">
                            <div class="csh-constellation-legend">
                                <span class="csh-constellation-legend-dot"></span>
                                <span>Halo vert + ★ = constellation recommandée</span>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        @else
            <div class="csh-artefact-empty">Aucune constellation disponible pour ce personnage.</div>
        @endif
    </section>

    <template x-if="constellationPopupOpen && activeConstellation">
        <div class="csh-constellation-modal-bg" @click.self="closeConstellationPopup()" @keydown.window.escape="closeConstellationPopup()">
            <div class="csh-constellation-modal">
                <div class="csh-constellation-modal-head">
                    <div>
                        <div class="csh-constellation-title" x-text="(activeConstellation.label || '') + ' — ' + (activeConstellation.titre_const || 'Constellation sans nom')"></div>
                        <template x-if="activeConstellation.recommandee">
                            <div class="csh-constellation-modal-badge">★ Recommandée</div>
                        </template>
                    </div>
                    <button type="button" class="csh-rotation-close" @click="closeConstellationPopup()">Fermer</button>
                </div>
                <div class="csh-constellation-desc" x-html="renderDescriConst(activeConstellation.descri_const) || 'Aucune description.'"></div>
            </div>
        </div>
    </template>

    @if($personnage->bio)
        <div class="bg-hub-surface border border-hub-border rounded-2xl p-6 mb-6">
            <h2 class="text-xl font-bold text-hub-text mb-3">{{ $personnage->bio->titre_bio }}</h2>
            <p class="text-hub-text-sec leading-relaxed">{{ $personnage->bio->descri_bio }}</p>
        </div>
    @endif

    @if($personnage->histoires->count())
        <div class="bg-hub-surface border border-hub-border rounded-2xl p-6 mb-6">
            <h2 class="text-xl font-bold text-hub-text mb-4">Histoires</h2>
            <div class="space-y-4">
                @foreach($personnage->histoires as $index => $histoire)
                    <article class="border border-hub-border rounded-xl p-4 bg-hub-surface-hover/40">
                        <h3 class="font-semibold text-hub-text mb-2">{{ $histoire->titre_histoire ?: ('Histoire ' . ($index + 1)) }}</h3>
                        <div class="text-hub-text-sec text-sm leading-relaxed">{!! $renderStoryHtml($histoire->histoire) !!}</div>
                    </article>
                @endforeach
            </div>
        </div>
    @endif

    @if($personnage->aptitudes->count())
        <div class="bg-hub-surface border border-hub-border rounded-2xl p-6 mb-6">
            <h2 class="text-xl font-bold text-hub-text mb-4">Aptitudes</h2>
            <div class="space-y-4">
                @foreach($personnage->aptitudes as $aptitude)
                    <div id="aptitude-{{ $aptitude->id_aptitude }}" class="border border-hub-border rounded-xl p-4">
                        <div class="flex items-center gap-2 mb-2">
                            @if($aptitude->typeApti)
                                <span class="text-xs text-hub-text-sec bg-hub-surface-hover px-2 py-0.5 rounded">{{ $aptitude->typeApti->libelle_Apti }}</span>
                            @endif
                            <h3 class="font-semibold text-hub-text">{{ $aptitude->titre_apti }}</h3>
                        </div>
                        <p class="text-hub-text-sec text-sm">{{ $aptitude->descri_apti }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    @if($personnage->specialite && $personnage->specialite->plat)
        <div class="bg-hub-surface border border-hub-border rounded-2xl p-6 mb-6" x-data="{ open: false }">
            <h2 class="text-xl font-bold text-hub-text mb-4">Spécialité culinaire</h2>
            <div class="flex items-center gap-4">
                <img src="{{ $photoUrl($personnage->specialite->plat->photos->first()) ?? asset('images/placeholder.svg') }}"
                     alt="{{ $personnage->specialite->plat->nom_plat }}"
                     class="w-16 h-16 rounded-lg object-cover border border-hub-border">
                <div>
                    <a href="{{ route('cuisine.show', $personnage->specialite->plat->slug) }}" class="font-semibold text-hub-primary hover:underline">
                        {{ $personnage->specialite->libelle_spe }}
                    </a>
                    <p class="text-hub-text-sec text-sm mt-1">{{ $personnage->specialite->descri_spe }}</p>
                </div>
                <button @click="open = !open" class="ml-auto px-4 py-2 border border-hub-border rounded-lg text-sm text-hub-text-sec hover:text-hub-text transition-colors">
                    Voir plat original
                </button>
            </div>
            <div x-show="open" x-transition class="mt-4 p-4 bg-hub-surface-hover rounded-xl">
                <p class="text-hub-text font-medium">{{ $personnage->specialite->plat->nom_plat }}</p>
                <p class="text-hub-text-sec text-sm mt-1">{{ $personnage->specialite->plat->descri_plat }}</p>
            </div>
        </div>
    @endif
</div>
</x-app-layout>
