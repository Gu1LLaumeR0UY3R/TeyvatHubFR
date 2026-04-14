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

    $constellationImageFor = function (int $personnageId, string $slug, int $index): string {
        $bases = [
            'photos/personnages/constellations/p' . $personnageId . '-c' . $index,
            'photos/personnages/constellations/' . $slug . '-c' . $index,
        ];

        foreach ($bases as $base) {
            foreach (['webp', 'png', 'jpg', 'jpeg'] as $ext) {
                $path = $base . '.' . $ext;
                if (\Illuminate\Support\Facades\Storage::disk('public')->exists($path)) {
                    return asset('storage/' . $path);
                }
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
                'image_url' => $constellationImageFor((int) $personnage->id_perso, (string) $personnage->slug, $idx + 1),
            ];
        })
        ->values();

    $storyReferences = is_array($storyReferences ?? null) ? $storyReferences : [];

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

    .csh-preview-weapon-list { display:grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap:1rem; padding: 1.05rem 1.2rem 1.2rem; }
    .csh-weapon-item { border:1px solid rgba(148,163,184,0.35); border-radius:0.6rem; background: linear-gradient(180deg, rgba(18, 28, 55, 0.86), rgba(10, 16, 34, 0.88)); padding:.55rem; display:flex; align-items:center; gap:.75rem; min-height:72px; }
    .csh-weapon-index { width:26px; height:26px; border-radius:999px; display:flex; align-items:center; justify-content:center; font-size:.72rem; font-weight:700; color:#eff6ff; background: rgba(255,255,255,0.08); border:1px solid rgba(255,255,255,0.1); flex-shrink:0; }
    .csh-weapon-icon-wrap { width:48px; height:48px; border-radius:12px; flex-shrink:0; display:flex; align-items:center; justify-content:center; border:1px solid rgba(255,255,255,0.22); box-shadow: inset 0 1px 0 rgba(255,255,255,0.12); }
    .csh-weapon-icon-wrap img { width:34px; height:34px; object-fit:contain; filter: drop-shadow(0 3px 5px rgba(0,0,0,.35)); }
    .csh-weapon-copy { min-width:0; flex:1; }
    .csh-weapon-name { font-weight:700; color:#e2e8f0; }
    .csh-weapon-type { color:#98a8c7; font-size:.72rem; }
    .csh-weapon-badge { display:inline-flex; align-items:center; margin-top:.35rem; padding:.18rem .45rem; border-radius:999px; font-size:.68rem; font-weight:700; background: rgba(16, 185, 129, 0.18); color: #b9f7df; border:1px solid rgba(52, 211, 153, 0.34); }
    .csh-weapon-empty, .csh-artefact-empty { padding:1rem 1.15rem 1.15rem; color:#8fa1c5; font-size:.85rem; font-style:italic; }

    .csh-preview-artefact-list { display:grid; gap:.85rem; padding: 1rem 1.15rem 1.15rem; }
    .csh-artefact-item { border:1px solid rgba(148,163,184,0.3); border-radius:14px; padding:.85rem .95rem; background: linear-gradient(180deg, rgba(17, 24, 39, 0.9), rgba(9, 14, 27, 0.92)); }
    .csh-artefact-head { display:flex; align-items:center; justify-content:space-between; gap:.75rem; margin-bottom:.5rem; }
    .csh-artefact-title { color:#e2e8f0; font-size:.9rem; font-weight:700; }
    .csh-artefact-piece { color:#fef3c7; font-size:.72rem; font-weight:700; }
    .csh-artefact-row { display:flex; align-items:center; justify-content:space-between; gap:.75rem; padding:.35rem 0; }
    .csh-artefact-name { color:#cbd5e1; font-size:.82rem; }

    .csh-constellation-shell { margin: 0 1.5rem 1.5rem; border: 1px solid rgba(255,255,255,0.12); border-radius: 18px; background: linear-gradient(180deg, rgba(10, 15, 30, 0.95), rgba(5, 10, 24, 0.92)); box-shadow: 0 18px 40px rgba(2, 6, 23, 0.32); overflow: hidden; }
    .csh-constellation-grid { display:grid; grid-template-columns: minmax(220px, 320px) minmax(0, 1fr); min-height: 360px; }
    .csh-constellation-media { padding: 1rem; border-right: 1px solid rgba(255,255,255,0.08); background: radial-gradient(circle at top, rgba(125, 211, 252, 0.12), rgba(15, 23, 42, 0.4)); }
    .csh-constellation-frame { height: 100%; min-height: 300px; border: 1px solid rgba(255,255,255,0.12); border-radius: 14px; overflow: hidden; background: linear-gradient(180deg, rgba(30,41,59,0.7), rgba(15,23,42,0.92)); display:flex; align-items:center; justify-content:center; }
    .csh-constellation-frame img { width:100%; height:100%; object-fit:cover; }
    .csh-constellation-content { padding: 1rem 1.15rem 1.15rem; display:flex; flex-direction:column; gap:.9rem; }
    .csh-constellation-tabs { display:grid; grid-template-columns: repeat(3, minmax(0,1fr)); gap:.5rem; }
    .csh-constellation-tab { border:1px solid rgba(148,163,184,0.4); border-radius:10px; padding:.45rem .55rem; color:#cbd5e1; background: rgba(15,23,42,0.55); text-align:left; font-size:.76rem; }
    .csh-constellation-tab.is-active { border-color:#7dd3fc; box-shadow: 0 0 0 1px rgba(125,211,252,0.35) inset; background: rgba(14,116,144,0.22); color:#e0f2fe; }
    .csh-constellation-detail { border:1px solid rgba(148,163,184,0.35); border-radius:12px; padding:.8rem .9rem; background: rgba(15,23,42,0.5); }
    .csh-constellation-title { color:#f1f5f9; font-size:1rem; font-weight:700; }
    .csh-constellation-desc { color:#cbd5e1; font-size:.84rem; line-height:1.45; margin-top:.45rem; white-space:pre-wrap; }

    @media (max-width: 900px) {
        .character-show-hero { grid-template-columns: 1fr; grid-template-areas: "hero" "portrait" "video" "meta"; padding:1.1rem; }
        .csh-portrait { min-height: 280px; height: auto; }
        .csh-preview-table { grid-template-columns: 1fr; margin: 0 .5rem 1rem; }
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
        selectedVideoIndex: 0,
        constellations: @json($constellations->values()),
        selectedConstellationIndex: 0,
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
        get activeConstellation() {
            if (!this.constellations.length) return null;
            const idx = Math.max(0, Math.min(this.selectedConstellationIndex, this.constellations.length - 1));
            return this.constellations[idx] || null;
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
                <div class="text-xs text-slate-400">{{ $personnage->armesRecommandees->count() }} arme(s)</div>
            </div>

            @if($personnage->armesRecommandees->count())
                <div class="csh-preview-weapon-list">
                    @foreach($personnage->armesRecommandees as $index => $armeRec)
                        @php
                            $arme = $armeRec->arme;
                            $rarityLabel = $arme?->etoile?->libelle ?? '?★';
                            $rarityStars = (int) preg_replace('/\D+/', '', (string) $rarityLabel);
                            if ($rarityStars < 1 || $rarityStars > 5) {
                                $rarityStars = (int) ($arme?->fid_etoile ?? 1);
                            }
                            $weaponIcon = $photoUrl($arme?->photos->first()) ?? asset('images/placeholder.svg');
                        @endphp
                        <article class="csh-weapon-item">
                            <div class="csh-weapon-index">{{ $index + 1 }}</div>
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
                        </article>
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
                    <div class="csh-constellation-frame">
                        <template x-if="activeConstellation && activeConstellation.image_url">
                            <img :src="activeConstellation.image_url" :alt="activeConstellation.titre_const || 'Constellation'">
                        </template>
                    </div>
                </div>

                <div class="csh-constellation-content">
                    <div class="csh-constellation-tabs">
                        <template x-for="(constellation, index) in constellations" :key="`public-c-${index}`">
                            <button type="button"
                                    class="csh-constellation-tab"
                                    :class="selectedConstellationIndex === index ? 'is-active' : ''"
                                    @click="selectedConstellationIndex = index">
                                <div x-text="constellation.label"></div>
                                <div class="truncate text-[11px] opacity-80" x-text="constellation.titre_const || 'Sans titre'"></div>
                            </button>
                        </template>
                    </div>

                    <template x-if="activeConstellation">
                        <div class="csh-constellation-detail">
                            <div class="csh-constellation-title" x-text="activeConstellation.titre_const || 'Constellation sans nom'"></div>
                            <div class="csh-constellation-desc" x-html="renderDescriConst(activeConstellation.descri_const) || 'Aucune description.'"></div>
                        </div>
                    </template>
                </div>
            </div>
        @else
            <div class="csh-artefact-empty">Aucune constellation disponible pour ce personnage.</div>
        @endif
    </section>

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
