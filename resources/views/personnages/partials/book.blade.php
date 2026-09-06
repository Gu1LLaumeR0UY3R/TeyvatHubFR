{{--
    Partial "livre du personnage" — utilisé par :
      - resources/views/personnages/show.blade.php (public)
      - resources/views/admin/personnages/show.blade.php (aperçu admin)

    Attend en entrée toutes les variables préparées par
    App\Http\Controllers\Concerns\PreparesPersonnageBookData::preparePersonnageBookData()
    ainsi que $personnage (avec les relations chargées via eagerLoadRelations()).
--}}
@php
    $renderStoryHtml = function (?string $text) use ($storyReferences) {
        if (!$text) {
            return '';
        }

        $escaped = e($text);

        return preg_replace_callback('/\[\[(aptitude|arme|monstre|boss):([a-zA-Z0-9\-_]+)\|([^\]]+)\]\]/', function ($m) use ($storyReferences) {
            [$full, $type, $key, $label] = $m;
            $ref = $storyReferences[$type][$key] ?? null;

            if (!$ref) {
                return e($label);
            }

            $img = e($ref['image']);
            $url = e($ref['url']);

            return '<a href="' . $url . '" class="tb-story-ref" data-ref-type="' . e($type) . '">'
                . '<img src="' . $img . '" alt="" loading="lazy"> ' . e($label) . '</a>';
        }, $escaped);
    };

    $rarityStars = (int) preg_replace('/\D+/', '', (string) ($personnage->etoile?->libelle ?? ''));
    if ($rarityStars < 1 || $rarityStars > 5) {
        $rarityStars = (int) ($personnage->fid_etoile ?? 4);
    }

    $radarValues = [
        'PV' => min(100, 48 + ($rarityStars * 8)),
        'ATQ' => min(100, 42 + ($personnage->typeArme ? 14 : 0) + ($rarityStars * 6)),
        'DEF' => min(100, 45 + ($personnage->nations->count() ? 10 : 0) + ($rarityStars * 5)),
        'Taux CRIT' => min(100, 35 + ($rarityStars * 8)),
        'Dégâts CRIT' => min(100, 38 + ($rarityStars * 9)),
        'Recharge' => min(100, 40 + ($personnage->roles->count() * 8)),
        'Maîtrise' => min(100, 38 + ($personnage->roles->count() * 9)),
    ];

    $featuredStats = $personnage->statsRecommandees->first();
    $statDisplayValues = [
        'PV' => $featuredStats?->pv,
        'ATQ' => $featuredStats?->atq,
        'DEF' => $featuredStats?->def,
        'Taux CRIT' => $featuredStats?->taux_crit,
        'Dégâts CRIT' => $featuredStats?->degats_crit,
        'Recharge d’énergie' => $featuredStats?->recharge_energetique,
        'Maîtrise élémentaire' => $featuredStats?->maitrise_elementaire,
    ];

    $pageShellStyle = null;
    if (!empty($heroBackgroundUrl)) {
        $safeHeroBackgroundUrl = str_replace("'", "\\'", $heroBackgroundUrl);
        $pageShellStyle = "background-image: radial-gradient(circle at 50% 7%, rgba(101,153,205,.22), transparent 25%), radial-gradient(circle at 8% 80%, rgba(173,121,49,.13), transparent 25%), linear-gradient(145deg, rgba(15,23,50,.88), rgba(13,23,41,.93) 52%), url('"
            . $safeHeroBackgroundUrl
            . "'); background-size: auto, auto, auto, cover; background-position: center;";
    }
@endphp

<style>
        .tb-scope {
            --night-1: #07111f;
            --night-2: #13233a;
            --night-3: #243c5b;

            --paper: #eadcc2;
            --paper-light: #f8f0df;
            --paper-dark: #d6ba91;
            --paper-line: rgba(103, 72, 37, 0.27);

            --ink: #18293e;
            --ink-muted: #667080;
            --gold: #b88337;
            --gold-light: #e3bd6e;

            --profile: #bd8a3c;
            --builds: #a8464c;
            --skills: #348e8b;
            --constellations: #4a669b;
            --teams: #80509b;
            --other: #657281;

            --shadow: rgba(0, 0, 0, 0.45);
            --page-turn-duration: 720ms;
            --font-title: Georgia, "Times New Roman", serif;
            --font-ui: Inter, "Segoe UI", Arial, sans-serif;
        }

        * {
            box-sizing: border-box;
        }

        button {
            font: inherit;
        }

        .tb-page-shell {
            padding: 26px clamp(12px, 3vw, 50px) 46px;
        }

        .tb-back-link {
            display: inline-flex;
            margin: 0 0 16px 8px;
            color: #adc9ea;
            font-size: 13px;
            text-decoration: none;
        }

        .tb-back-link:hover {
            color: #f1d48b;
        }

        .tb-book-scene {
            display: flex;
            justify-content: center;
            perspective: 2200px;
        }

        .tb-book {
            position: relative;
            perspective: 2000px;
            width: min(1240px, calc(100vw - 90px));
            height: 780px;
            padding: 20px;
            border: 1px solid rgba(193, 142, 60, 0.58);
            border-radius: 14px 22px 22px 14px;
            background:
                linear-gradient(100deg, rgba(68, 38, 18, 0.8), rgba(137, 89, 37, 0.58) 7%, rgba(44, 27, 17, 0.85) 12%, rgba(51, 31, 18, 0.86) 88%, rgba(157, 107, 50, 0.5)),
                #332014;
            box-shadow:
                0 30px 65px var(--shadow),
                0 0 0 7px rgba(14, 18, 27, 0.58),
                inset 0 0 20px rgba(255, 205, 119, 0.16);
        }

        .tb-book::before,
        .tb-book::after {
            position: absolute;
            z-index: 4;
            top: 38px;
            bottom: 38px;
            width: 18px;
            content: "";
            opacity: 0.75;
            border: 2px solid #bd8a3c;
            pointer-events: none;
        }

        .tb-book::before {
            left: 10px;
            border-right: 0;
            border-radius: 12px 0 0 12px;
        }

        .tb-book::after {
            right: 10px;
            border-left: 0;
            border-radius: 0 12px 12px 0;
        }

        .tb-book-spread {
            position: relative;
            isolation: isolate;
            perspective: 1800px;
            z-index: 5;
            display: grid;
            grid-template-columns: 1fr 1fr;
            width: 100%;
            height: auto;
            min-height: 738px;
            overflow: hidden;
            border: 1px solid rgba(111, 76, 36, 0.45);
            border-radius: 10px;
            background: var(--paper);
            box-shadow:
                inset 0 0 40px rgba(94, 58, 26, 0.18),
                0 8px 22px rgba(0, 0, 0, 0.3);
            transform-style: preserve-3d;
        }

        /* The supplied artwork already contains the cover, paper texture and spine. */
        .tb-book {
            padding: 0;
            border: 0;
            background: transparent;
            box-shadow: none;
        }

        .tb-book-spread {
            min-height: 738px;
            border: 0;
            border-radius: 0;
            background: url("{{ asset('images/book-background.png') }}") center / 118% 118% no-repeat;
            box-shadow: none;
        }

        .tb-book-spread::before {
            display: none;
        }

        .tb-book-page {
            background: transparent;
        }

        .tb-book-spread::before {
            position: absolute;
            z-index: 20;
            top: 0;
            bottom: 0;
            left: 50%;
            width: 26px;
            content: "";
            transform: translateX(-50%);
            background: linear-gradient(90deg, rgba(91, 56, 29, 0.2), rgba(255, 245, 223, 0.35), rgba(91, 56, 29, 0.22));
            box-shadow: 0 0 17px rgba(64, 38, 18, 0.22);
            pointer-events: none;
        }

        .tb-book-page {
            position: relative;
            min-width: 0;
            height: auto;
            min-height: 738px;
            overflow: visible;
            padding: 28px;
            background:
                radial-gradient(circle at 85% 15%, rgba(255, 255, 255, 0.47), transparent 22%),
                radial-gradient(circle at 10% 90%, rgba(187, 139, 76, 0.13), transparent 35%),
                repeating-linear-gradient(0deg, transparent 0 26px, rgba(116, 75, 37, 0.055) 27px 28px),
                linear-gradient(115deg, var(--paper-light), var(--paper) 58%, #dfcaa7);
        }

        .tb-left-page {
            border-right: 1px solid rgba(94, 57, 26, 0.2);
        }

        .tb-right-page {
            border-left: 1px solid rgba(255, 255, 255, 0.35);
        }

        .tb-page-content {
            position: relative;
            z-index: 2;
            height: 100%;
        }

        @keyframes appear {
            from {
                opacity: 0;
                transform: translateY(8px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .tb-central-page {
            position: absolute;
            z-index: 50;
            top: 0;
            right: 0;
            width: 50%;
            height: 100%;
            display: none;
            transform-style: preserve-3d;
            backface-visibility: hidden;
            pointer-events: none;
        }

        .tb-central-page-2,
        .tb-central-page-3 {
            z-index: 49;
        }

        .tb-central-page-3 {
            z-index: 48;
        }

        .tb-central-page-front,
        .tb-central-page-back {
            position: absolute;
            inset: 0;
            backface-visibility: hidden;
            background:
                radial-gradient(circle at 70% 20%, rgba(255, 255, 255, 0.45), transparent 25%),
                repeating-linear-gradient(0deg, transparent 0 26px, rgba(116, 75, 37, 0.05) 27px 28px),
                linear-gradient(115deg, #f7ecd5, #d8bd92);
            box-shadow:
                inset 0 0 28px rgba(81, 50, 23, 0.14),
                0 0 20px rgba(60, 34, 13, 0.18);
        }

            .tb-central-page-content {
                position: absolute;
                inset: 0;
                overflow: hidden;
                padding: 28px;
            }

        .tb-central-page-back {
            transform: rotateY(180deg);
        }

        .tb-book-spread.tb-turn-forward .tb-central-page,
        .tb-book-spread.tb-turn-forward .tb-central-page-2,
        .tb-book-spread.tb-turn-forward .tb-central-page-3,
        .tb-book-spread.tb-turn-backward .tb-central-page,
        .tb-book-spread.tb-turn-backward .tb-central-page-2,
        .tb-book-spread.tb-turn-backward .tb-central-page-3 {
            display: block;
            animation-duration: calc(var(--page-turn-duration) - var(--page-turn-delay, 0ms));
            animation-delay: var(--page-turn-delay, 0ms);
            animation-timing-function: cubic-bezier(0.22, 0.61, 0.36, 1);
            animation-fill-mode: both;
        }

        .tb-central-page-2 {
            --page-turn-delay: 45ms;
        }

        .tb-central-page-3 {
            --page-turn-delay: 90ms;
        }

        .tb-book-spread.tb-turn-forward .tb-central-page {
            right: 0;
            left: auto;
            transform-origin: left center;
            animation-name: central-turn-forward;
        }

        .tb-book-spread.tb-turn-forward .tb-central-page-2,
        .tb-book-spread.tb-turn-forward .tb-central-page-3,
        .tb-book-spread.tb-turn-backward .tb-central-page-2,
        .tb-book-spread.tb-turn-backward .tb-central-page-3 {
            right: 0;
            left: auto;
        }

        .tb-book-spread.tb-turn-backward .tb-central-page,
        .tb-book-spread.tb-turn-backward .tb-central-page-2,
        .tb-book-spread.tb-turn-backward .tb-central-page-3 {
            right: auto;
            left: 0;
            transform-origin: right center;
            animation-name: central-turn-backward;
        }

        @keyframes central-turn-forward {
            from {
                transform: rotateY(0deg);
            }

            to {
                transform: rotateY(-180deg);
            }
        }

        @keyframes central-turn-backward {
            from {
                transform: rotateY(0deg);
            }

            to {
                transform: rotateY(180deg);
            }
        }

        .tb-book-tabs {
            position: absolute;
            z-index: 30;
            top: 20px;
            right: -148px;

            display: flex;
            flex-direction: column;
            gap: 10px;

            pointer-events: none;
        }

        .tb-book-tab {
            position: relative;
            width: 163px;
            min-height: 65px;
            padding: 9px 14px 9px 44px;

            color: #fff1ce;
            text-align: left;
            text-shadow: 0 1px 1px rgba(0, 0, 0, 0.35);

            cursor: pointer;
            pointer-events: auto;

            border: 1px solid rgba(255, 231, 175, 0.33);
            border-left: 0;
            border-radius: 0 9px 9px 0;

            opacity: 1;
            filter: none;

            box-shadow:
                6px 5px 11px rgba(0, 0, 0, 0.32),
                inset 0 1px rgba(255, 255, 255, 0.2);

            transition:
                transform 180ms ease,
                width 180ms ease,
                filter 180ms ease;
        }

        .tb-book-tab::before {
            position: absolute;
            left: 15px;
            top: 50%;

            width: 18px;
            margin: 0;

            color: rgba(255, 247, 225, 0.9);
            content: "✦";
            font-size: 18px;
            transform: translateY(-50%);
        }

        .tb-book-tab:hover,
        .tb-book-tab:focus-visible {
            z-index: 40;
            width: 174px;
            transform: translateX(-7px);
            filter: brightness(1.12);
            outline: none;
        }

        .tb-book-tab.tb-active {
            z-index: 50;
            width: 174px;
            transform: translateX(-7px);
            filter: brightness(1.12);

            border-left: 1px solid rgba(255, 231, 175, 0.33);
            border-radius: 0 9px 9px 0;
        }

        /* Aucun ::after qui déborde sur les pages */
        .tb-book-tab.tb-active::after {
            display: none;
        }

        .tb-profile-tab { background: linear-gradient(135deg, #d1a34f, var(--profile)); }
        .tb-builds-tab { background: linear-gradient(135deg, #c45c5e, var(--builds)); }
        .tb-skills-tab { background: linear-gradient(135deg, #54aaa0, var(--skills)); }
        .tb-constellations-tab { background: linear-gradient(135deg, #6383bd, var(--constellations)); }
        .tb-teams-tab { background: linear-gradient(135deg, #9a6bb1, var(--teams)); }
        .tb-other-tab { background: linear-gradient(135deg, #8c98a5, var(--other)); }

        .tb-section-title {
            display: flex;
            align-items: baseline;
            gap: 10px;
            margin: 0 0 18px;
            font-family: var(--font-title);
            font-size: clamp(23px, 2vw, 31px);
        }

        .tb-section-title small {
            color: var(--ink-muted);
            font-family: var(--font-ui);
            font-size: 12px;
            font-weight: 500;
        }

        .tb-small-heading {
            margin: 0 0 10px;
            color: var(--ink);
            font-family: var(--font-title);
            font-size: 17px;
        }

        .tb-panel {
            padding: 16px;
            border: 1px solid var(--paper-line);
            border-radius: 12px;
            background: rgba(255, 248, 229, 0.42);
            box-shadow: inset 0 0 20px rgba(132, 85, 38, 0.05);
        }

        .tb-profile-polaroid {
            position: relative;
            width: min(100%, 500px);
            margin: 2px auto 24px;
            padding: 14px 14px 58px;
            background: #f4e6cc;
            border: 1px solid rgba(109, 73, 36, 0.25);
            box-shadow: 7px 10px 17px rgba(60, 36, 19, 0.26);
            transform: rotate(-3deg);
        }

        .tb-profile-gallery {
            position: relative;
            min-height: 570px;
            padding: 12px 8px 0;
        }

        .tb-profile-gallery + .tb-info-list {
            margin-top: 28px;
        }

        .tb-profile-gallery .tb-profile-polaroid {
            position: absolute;
            inset: 12px 8px auto;
            margin: 0 auto;
            width: auto;
            z-index: 2;
        }

        .tb-splash-polaroid {
            position: absolute;
            z-index: 1;
            inset: 28px 8px auto;
            width: auto;
            padding: 14px 14px 58px;
            background: #eadcc2;
            border: 1px solid rgba(109, 73, 36, 0.25);
            box-shadow: 5px 8px 14px rgba(60, 36, 19, 0.24);
            transform: rotate(3deg);
            transition: z-index 0ms linear 180ms, transform 180ms ease, opacity 180ms ease;
        }

        .tb-splash-polaroid .tb-portrait-frame {
            height: clamp(300px, 35vw, 420px);
            border-width: 4px;
        }

        .tb-splash-polaroid .tb-polaroid-name {
            bottom: 15px;
            left: 22px;
            font-size: 24px;
        }

        .tb-profile-polaroid.is-photo-front {
            position: absolute;
            z-index: 2;
            inset: 12px 8px auto;
            margin: 0;
            width: auto;
            transition: z-index 0ms linear 180ms, transform 180ms ease, opacity 180ms ease;
        }

        .tb-profile-polaroid.is-photo-back {
            z-index: 1;
            opacity: 0.84;
            transform: rotate(-1deg) translate(16px, 8px) scale(0.97);
        }

        .tb-splash-polaroid.is-photo-front {
            z-index: 4;
            opacity: 1;
            transform: rotate(-3deg);
        }

        .tb-photo-arrow {
            position: absolute;
            z-index: 7;
            top: 50%;
            display: grid;
            width: 30px;
            height: 30px;
            place-items: center;
            padding: 0;
            color: #f8eedb;
            cursor: pointer;
            border: 1px solid rgba(255, 246, 220, 0.55);
            border-radius: 50%;
            background: rgba(24, 41, 62, 0.62);
            transform: translateY(-50%);
            transition: background 150ms ease, transform 150ms ease;
        }

        .tb-photo-arrow:hover,
        .tb-photo-arrow:focus-visible {
            background: rgba(24, 41, 62, 0.9);
            outline: none;
            transform: translateY(-50%) scale(1.08);
        }

        .tb-photo-arrow-left { left: 24px; }
        .tb-photo-arrow-right { right: 24px; }

        .tb-photo-toggle {
            position: absolute;
            z-index: 8;
            right: 50%;
            bottom: 8px;
            display: inline-flex;
            gap: 8px;
            align-items: center;
            padding: 4px 10px;
            color: #31445d;
            cursor: pointer;
            border: 1px solid rgba(49, 68, 93, 0.24);
            border-radius: 999px;
            background: rgba(248, 240, 223, 0.86);
            transform: translateX(50%);
        }

        .tb-photo-toggle:hover,
        .tb-photo-toggle:focus-visible {
            color: #18293e;
            background: #f8f0df;
            outline: none;
        }

        .tb-paperclip {
            position: absolute;
            z-index: 5;
            top: -25px;
            left: 52%;
            width: 24px;
            height: 76px;
            border: 3px solid #9a9da3;
            border-bottom: 0;
            border-radius: 14px 14px 0 0;
            transform: rotate(12deg);
            filter: drop-shadow(1px 2px 1px rgba(55, 43, 28, 0.28));
        }

        .tb-paperclip::after {
            position: absolute;
            top: 5px;
            left: 5px;
            width: 10px;
            height: 59px;
            content: "";
            border: 2px solid #c8cbd0;
            border-bottom: 0;
            border-radius: 9px 9px 0 0;
        }

        .tb-portrait-frame {
            position: relative;
            display: grid;
            height: 365px;
            overflow: hidden;
            place-items: center;
            color: #f8ebcb;
            text-align: center;
            background:
                radial-gradient(circle at 50% 30%, rgba(255, 193, 230, 0.85), transparent 27%),
                linear-gradient(145deg, #835896, #bb94d5 45%, #537aa5);
            border: 4px solid #d9bf92;
        }

        .tb-profile-polaroid .tb-portrait-frame {
            width: 100%;
            height: clamp(300px, 35vw, 420px);
        }

        .tb-profile-polaroid .tb-portrait-frame img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        .tb-splash-polaroid .tb-portrait-frame img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            background: #edf5f8;
        }

        .tb-portrait-frame::after {
            position: absolute;
            inset: 10px;
            content: "";
            border: 1px solid rgba(255, 246, 220, 0.65);
        }

        .tb-portrait-label {
            position: relative;
            z-index: 2;
            padding: 12px;
            font-family: var(--font-title);
            font-size: 31px;
            text-shadow: 0 2px 5px rgba(0, 0, 0, 0.55);
        }

        .tb-polaroid-name {
            position: absolute;
            bottom: 15px;
            left: 22px;
            color: #4e5e89;
            font-family: "Comic Sans MS", cursive;
            font-size: 27px;
            transform: rotate(-6deg);
        }

        .tb-photo-switch {
            display: flex;
            justify-content: center;
            gap: 5px;
            padding: 4px;
            border-radius: 999px;
            background: rgba(23, 38, 58, 0.1);
        }

        .tb-photo-switch button {
            padding: 8px 13px;
            color: #31445d;
            cursor: pointer;
            border: 0;
            border-radius: 999px;
            background: transparent;
        }

        .tb-photo-switch button.tb-active {
            color: #f6ecd6;
            background: #213750;
        }

        .tb-character-head {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 17px;
        }

        .tb-element-orb {
            display: grid;
            flex: 0 0 auto;
            width: 58px;
            height: 58px;
            place-items: center;
            color: #e3faf0;
            border: 3px solid #b8e6d3;
            border-radius: 50%;
            background: #3b9d89;
            box-shadow: 0 0 17px rgba(58, 157, 137, 0.5);
            font-size: 28px;
        }

        .tb-character-head h1 {
            margin: 0;
            font-family: var(--font-title);
            font-size: clamp(33px, 3vw, 48px);
        }

        .tb-character-head p {
            margin: 2px 0 0;
            color: #6b5e51;
            font-family: var(--font-title);
        }

        .tb-stars {
            color: #d59d38;
            letter-spacing: 3px;
        }

        .tb-info-list {
            margin: 0 0 17px;
            padding: 0;
            list-style: none;
            border-top: 1px solid var(--paper-line);
        }

        .tb-info-list li {
            display: grid;
            grid-template-columns: 42% 1fr;
            gap: 12px;
            padding: 10px 0;
            border-bottom: 1px solid var(--paper-line);
            font-size: 14px;
        }

        .tb-info-list span {
            color: #756b61;
        }

        .tb-info-list strong {
            color: var(--ink);
        }

        .tb-quote {
            margin: 0;
            padding: 16px 18px;
            color: #695b4d;
            font-family: var(--font-title);
            font-style: italic;
            line-height: 1.55;
            text-align: right;
        }

        .tb-story {
            margin-top: 17px;
        }

        .tb-story p {
            margin: 0;
            color: #394656;
            line-height: 1.55;
            font-size: 14px;
        }

        .tb-profile-bottom {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-top: 16px;
        }

        .tb-profile-stats {
            display: block;
            margin-top: 18px;
        }

        .tb-radar-card {
            min-width: 0;
            padding: 12px;
            border: 1px solid var(--paper-line);
            border-radius: 12px;
            background: rgba(255, 248, 229, 0.42);
        }

        .tb-radar-layout {
            display: grid;
            grid-template-columns: minmax(180px, 0.8fr) minmax(220px, 1.2fr);
            gap: 18px;
            align-items: center;
        }

        .tb-radar-numbers {
            display: grid;
            gap: 7px;
        }

        .tb-radar-number {
            display: flex;
            align-items: baseline;
            justify-content: space-between;
            gap: 12px;
            padding-bottom: 6px;
            color: #667080;
            font-size: 12px;
            border-bottom: 1px solid var(--paper-line);
        }

        .tb-radar-number strong {
            color: var(--ink);
            font-size: 14px;
        }

        .tb-radar {
            display: block;
            width: 100%;
            max-width: 270px;
            margin: 0 auto;
            overflow: visible;
        }

        .tb-radar-grid {
            fill: rgba(105, 139, 178, 0.08);
            stroke: rgba(64, 94, 126, 0.32);
            stroke-width: 0.7;
        }

        .tb-radar-axis {
            stroke: rgba(64, 94, 126, 0.26);
            stroke-width: 0.55;
        }

        .tb-radar-value {
            fill: rgba(65, 157, 153, 0.42);
            stroke: #348e8b;
            stroke-width: 1.5;
            stroke-linejoin: round;
        }

        .tb-radar-label {
            fill: #53606b;
            font-size: 7px;
            font-weight: 700;
            text-anchor: middle;
        }

        .tb-stat-row {
            display: grid;
            grid-template-columns: 1fr 1.2fr auto;
            gap: 8px;
            align-items: center;
            padding: 8px 0;
            color: #445161;
            font-size: 13px;
        }

        .tb-stat-bar {
            height: 8px;
            overflow: hidden;
            border-radius: 999px;
            background: rgba(60, 77, 91, 0.12);
        }

        .tb-stat-bar span {
            display: block;
            height: 100%;
            border-radius: inherit;
            background: linear-gradient(90deg, #59a99d, #8bc8bd);
        }

        .tb-build-menu {
            display: flex;
            flex-direction: column;
            gap: 9px;
        }

        .tb-build-option {
            position: relative;
            width: 100%;
            padding: 14px 15px;
            color: var(--ink);
            text-align: left;
            cursor: pointer;
            border: 1px solid rgba(109, 70, 38, 0.22);
            border-left: 4px solid transparent;
            border-radius: 9px;
            background: rgba(255, 249, 232, 0.45);
            transition: transform 150ms ease, background 150ms ease, border-color 150ms ease;
        }

        .tb-build-option:hover {
            transform: translateX(3px);
        }

        .tb-build-option.tb-active {
            border-left-color: #a8464c;
            background: rgba(255, 237, 217, 0.84);
            box-shadow: 0 4px 12px rgba(110, 54, 45, 0.13);
        }

        .tb-build-option strong,
        .tb-build-option small {
            display: block;
        }

        .tb-build-option small {
            margin-top: 3px;
            color: var(--ink-muted);
        }

        .tb-versatility {
            margin: 0 0 22px;
        }

        .tb-versatility-label {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
            color: #5c5c5d;
            font-size: 13px;
        }

        .tb-versatility-bar {
            display: flex;
            gap: 4px;
        }

        .tb-versatility-bar span {
            flex: 1;
            height: 12px;
            border-radius: 3px;
            background: rgba(49, 62, 74, 0.16);
        }

        .tb-versatility-bar span.tb-filled {
            background: linear-gradient(90deg, #ad3f46, #d56f62);
            box-shadow: 0 0 8px rgba(170, 65, 67, 0.37);
        }

        .tb-equipment-card {
            display: grid;
            grid-template-columns: 77px 1fr;
            gap: 13px;
            align-items: center;
            margin-bottom: 12px;
            padding: 12px;
            border: 1px solid var(--paper-line);
            border-radius: 11px;
            background: rgba(255, 248, 229, 0.52);
        }

        .tb-equipment-icon {
            display: grid;
            width: 77px;
            height: 77px;
            place-items: center;
            color: #fff1d1;
            border: 2px solid #cf9c4f;
            border-radius: 9px;
            background: linear-gradient(145deg, #8c5d35, #3d5675);
            font-size: 27px;
        }

        .tb-equipment-card h3 {
            margin: 0 0 4px;
            font-family: var(--font-title);
            font-size: 18px;
        }

        .tb-equipment-card p {
            margin: 0;
            color: #5c6873;
            font-size: 13px;
            line-height: 1.4;
        }

        .tb-artifact-tags {
            display: flex;
            flex-wrap: wrap;
            gap: 7px;
            margin-top: 13px;
        }

        .tb-artifact-tags span,
        .tb-tag {
            padding: 6px 9px;
            color: #42536a;
            border: 1px solid rgba(70, 92, 117, 0.2);
            border-radius: 999px;
            background: rgba(191, 210, 224, 0.42);
            font-size: 12px;
        }

        .tb-skill-list {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .tb-skill-card {
            display: grid;
            grid-template-columns: 66px 1fr;
            gap: 13px;
            padding: 12px;
            border: 1px solid var(--paper-line);
            border-radius: 11px;
            background: rgba(255, 248, 229, 0.52);
        }

        .tb-skill-icon {
            display: grid;
            width: 66px;
            height: 66px;
            place-items: center;
            color: #eafcf9;
            border: 2px solid #75c8b5;
            border-radius: 11px;
            background: #3c9585;
            font-size: 26px;
        }

        .tb-skill-card h3 {
            margin: 0 0 5px;
            font-family: var(--font-title);
            font-size: 18px;
        }

        .tb-skill-card p {
            margin: 0;
            color: #53606b;
            font-size: 13px;
            line-height: 1.48;
        }

        .tb-talent-level {
            display: inline-block;
            margin-top: 8px;
            padding: 4px 7px;
            color: #266d64;
            border-radius: 5px;
            background: rgba(74, 163, 148, 0.13);
            font-size: 11px;
            font-weight: 700;
        }

        .tb-passive-list {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .tb-passive {
            padding: 16px;
            border-left: 4px solid #348e8b;
            border-radius: 8px;
            background: rgba(255, 249, 234, 0.45);
        }

        .tb-passive h3 {
            margin: 0 0 6px;
            font-family: var(--font-title);
            font-size: 18px;
        }

        .tb-passive p {
            margin: 0;
            color: #56626d;
            line-height: 1.5;
            font-size: 14px;
        }

        .tb-constellations-layout {
            display: grid;
            grid-template-columns: 1fr 0.98fr;
            gap: 20px;
            height: 100%;
        }

        .tb-constellation-map {
            position: relative;
            min-height: 560px;
            overflow: hidden;
            border: 1px solid rgba(56, 88, 138, 0.42);
            border-radius: 14px;
            background:
                radial-gradient(circle at 50% 42%, rgba(123, 183, 230, 0.36), transparent 36%),
                radial-gradient(circle at 10% 10%, rgba(255, 255, 255, 0.28) 1px, transparent 2px),
                radial-gradient(circle at 85% 34%, rgba(255, 255, 255, 0.26) 1px, transparent 2px),
                linear-gradient(145deg, #102a50, #1d4a77 60%, #153555);
            box-shadow: inset 0 0 30px rgba(0, 0, 0, 0.35);
        }

        .tb-constellation-map::before {
            position: absolute;
            inset: 8%;
            content: "";
            opacity: 0.45;
            border: 1px solid rgba(191, 222, 255, 0.33);
            border-radius: 50%;
            transform: rotate(25deg);
        }

        .tb-constellation-svg {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
        }

        .tb-constellation-svg path {
            fill: none;
            stroke: rgba(191, 228, 255, 0.47);
            stroke-width: 2;
            stroke-linecap: round;
            stroke-dasharray: 5 7;
        }

        .tb-constellation-svg path.tb-active-line {
            stroke: #ffdc80;
            stroke-width: 3;
            filter: drop-shadow(0 0 4px #fbd278);
        }

        .tb-constellation-node {
            position: absolute;
            z-index: 3;
            display: grid;
            width: 50px;
            height: 50px;
            padding: 0;
            place-items: center;
            color: #f8e8b8;
            cursor: pointer;
            border: 2px solid rgba(229, 222, 176, 0.58);
            border-radius: 50%;
            background: rgba(39, 86, 133, 0.82);
            box-shadow: inset 0 0 12px rgba(255, 255, 255, 0.18);
            transition: transform 180ms ease, background 180ms ease, border-color 180ms ease, box-shadow 180ms ease;
        }

        .tb-constellation-node:hover,
        .tb-constellation-node:focus-visible {
            border-color: #ffe5a7;
            outline: none;
            transform: scale(1.12);
        }

        .tb-constellation-node.tb-active {
            border-color: #ffe09a;
            background: #bd8a3c;
            transform: scale(1.18);
            box-shadow:
                0 0 0 6px rgba(255, 215, 127, 0.14),
                0 0 26px rgba(255, 207, 89, 0.95),
                inset 0 0 14px rgba(255, 248, 216, 0.75);
            animation: constellation-pulse 1.7s ease-in-out infinite;
        }

        @keyframes constellation-pulse {
            0%,
            100% {
                box-shadow:
                    0 0 0 5px rgba(255, 211, 113, 0.12),
                    0 0 19px rgba(255, 203, 94, 0.7);
            }

            50% {
                box-shadow:
                    0 0 0 11px rgba(255, 211, 113, 0.03),
                    0 0 32px rgba(255, 203, 94, 1);
            }
        }

        .tb-constellation-node[data-id="c1"] { top: 11%; left: 46%; }
        .tb-constellation-node[data-id="c2"] { top: 28%; left: 20%; }
        .tb-constellation-node[data-id="c3"] { top: 34%; left: 67%; }
        .tb-constellation-node[data-id="c4"] { top: 52%; left: 34%; }
        .tb-constellation-node[data-id="c5"] { top: 67%; left: 70%; }
        .tb-constellation-node[data-id="c6"] { top: 83%; left: 46%; }

        .tb-constellation-details {
            display: flex;
            flex-direction: column;
            gap: 9px;
            padding-right: 5px;
        }

        .tb-constellation-description {
            padding: 13px 14px;
            color: var(--ink-muted);
            cursor: pointer;
            border: 1px solid rgba(83, 69, 50, 0.2);
            border-left: 4px solid transparent;
            border-radius: 10px;
            background: rgba(255, 248, 227, 0.43);
            opacity: 0.55;
            transition: opacity 200ms ease, transform 200ms ease, background 200ms ease, border-color 200ms ease, box-shadow 200ms ease;
        }

        .tb-constellation-description:hover {
            opacity: 0.82;
        }

        .tb-constellation-description.tb-active {
            color: #48596c;
            border-left-color: var(--gold);
            background: rgba(255, 246, 215, 0.92);
            box-shadow: 0 5px 14px rgba(80, 52, 24, 0.16);
            opacity: 1;
            transform: translateX(-3px);
        }

        .tb-constellation-index {
            display: block;
            margin-bottom: 3px;
            color: #906024;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.08em;
        }

        .tb-constellation-description h3 {
            margin: 0 0 6px;
            color: var(--ink);
            font-family: var(--font-title);
            font-size: 17px;
        }

        .tb-constellation-description p {
            margin: 0;
            font-size: 12px;
            line-height: 1.42;
        }

        .tb-reaction-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 11px;
        }

        .tb-reaction-card {
            padding: 15px;
            color: #31495b;
            border: 1px solid rgba(72, 75, 134, 0.2);
            border-radius: 10px;
            background: rgba(226, 220, 250, 0.38);
        }

        .tb-reaction-card strong {
            display: block;
            margin-bottom: 5px;
            color: #493c74;
            font-family: var(--font-title);
            font-size: 17px;
        }

        .tb-reaction-card p {
            margin: 0;
            font-size: 12px;
            line-height: 1.4;
        }

        .tb-team-card {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 7px;
            margin-bottom: 15px;
            padding: 13px;
            border: 1px solid var(--paper-line);
            border-radius: 11px;
            background: rgba(255, 249, 235, 0.52);
        }

        .tb-team-card h3 {
            grid-column: 1 / -1;
            margin: 0 0 4px;
            font-family: var(--font-title);
            font-size: 18px;
        }

        .tb-team-member {
            display: grid;
            min-height: 70px;
            padding: 6px;
            place-items: center;
            color: #f4e6c4;
            text-align: center;
            border-radius: 8px;
            background: linear-gradient(145deg, #536687, #263e61);
            font-size: 11px;
        }

        .tb-other-list {
            display: grid;
            gap: 13px;
        }

        .tb-other-card {
            padding: 17px;
            border: 1px solid var(--paper-line);
            border-radius: 11px;
            background: rgba(255, 248, 229, 0.45);
        }

        .tb-other-card h3 {
            margin: 0 0 7px;
            font-family: var(--font-title);
            font-size: 20px;
        }

        .tb-other-card p {
            margin: 0;
            color: #56616c;
            line-height: 1.53;
            font-size: 14px;
        }

        .tb-book-navigation {
            display: flex;
            justify-content: center;
            gap: 11px;
            margin-top: 18px;
        }

        .tb-nav-button {
            min-width: 128px;
            padding: 10px 15px;
            color: #f3dfb2;
            cursor: pointer;
            border: 1px solid rgba(225, 178, 89, 0.45);
            border-radius: 7px;
            background: rgba(19, 35, 57, 0.75);
            box-shadow: 0 5px 12px rgba(0, 0, 0, 0.24);
        }

        .tb-nav-button:hover:not(:disabled) {
            background: #2c496c;
        }

        .tb-nav-button:disabled {
            cursor: not-allowed;
            opacity: 0.45;
        }

        .tb-footer-note {
            margin-top: 22px;
            color: #c7d7ed;
            text-align: center;
            font-family: var(--font-title);
            font-size: 13px;
            font-style: italic;
        }

        @media (max-width: 1120px) {
            .tb-book {
                width: min(950px, calc(100vw - 45px));
            }

            .tb-book-tabs {
                position: static;
                flex-direction: row;
                flex-wrap: wrap;
                justify-content: center;
                margin: 14px 0 0;
            }

            .tb-book-tab,
            .tb-book-tab:hover,
            .tb-book-tab.tb-active {
                width: auto;
                min-height: auto;
                padding: 8px 12px;
                transform: none;
            }

            .tb-book-tab::before {
                position: static;
                margin-right: 6px;
                margin-left: 0;
            }

            .tb-book-tab span {
                display: inline;
                margin-right: 5px;
            }
        }

        @media (max-width: 800px) {

            .top-links {
                display: none;
            }

            .tb-book {
                width: calc(100vw - 24px);
                height: auto;
                min-height: 0;
                padding: 10px;
            }

            .tb-book-spread {
                display: block;
                width: 100%;
                height: auto;
                min-height: 0;
            }

            .tb-book-spread::before {
                display: none;
            }

            .tb-book-page {
                height: auto;
                min-height: 600px;
                max-height: none;
                overflow: visible;
                padding: 20px;
            }

            .tb-left-page {
                border-bottom: 1px solid var(--paper-line);
            }

            .tb-profile-bottom,
            .tb-constellations-layout,
            .tb-profile-stats {
                grid-template-columns: 1fr;
            }

            .tb-profile-gallery {
                min-height: 540px;
            }

            .tb-constellation-map {
                min-height: 430px;
            }

            .tb-constellation-details {
                max-height: none;
            }
        }

        @media (max-width: 500px) {

            .tb-page-shell {
                padding: 16px 6px 30px;
            }

            .tb-book-page {
                padding: 15px;
            }

            .tb-profile-polaroid {
                width: 94%;
            }

            .tb-radar-layout {
                grid-template-columns: 1fr;
            }

            .tb-portrait-frame {
                height: 280px;
            }

            .tb-character-head h1 {
                font-size: 34px;
            }

            .tb-info-list li {
                grid-template-columns: 1fr;
                gap: 3px;
            }

            .tb-reaction-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (prefers-reduced-motion: reduce) {
            *,
            *::before,
            *::after {
                scroll-behavior: auto !important;
                animation-duration: 0.01ms !important;
                animation-iteration-count: 1 !important;
                transition-duration: 0.01ms !important;
            }
        }
        /* --- Intégration TeyvatHub : ajustements pour contenu dynamique --- */
        .tb-page-shell {
            border-radius: 24px;
            background:
                radial-gradient(circle at 50% 7%, rgba(101, 153, 205, 0.22), transparent 25%),
                radial-gradient(circle at 8% 80%, rgba(173, 121, 49, 0.13), transparent 25%),
                linear-gradient(145deg, var(--night-1), var(--night-2) 52%, #0d1729);
            margin-bottom: 2.5rem;
        }

        /* Le livre grandit avec le contenu réel (portraits, tableaux, cartes SVG)
           plutôt que d'être clippé à une hauteur fixe comme dans la maquette statique. */
        .tb-book {
            height: auto;
            min-height: 780px;
        }
        .tb-book-spread {
            min-height: 738px;
            height: auto;
            border: 0;
            border-radius: 0;
            background: url("{{ asset('images/book-background.png') }}") center / 118% 118% no-repeat;
            box-shadow: none;
        }
        .tb-book-page {
            background: transparent;
        }
        .tb-book-page {
            max-height: none;
        }
        .tb-central-page,
        .tb-central-page-2,
        .tb-central-page-3 {
            height: 100%;
        }

        /* Fenêtre sombre encastrée dans la page pour les widgets interactifs
           déjà existants (carte de constellations, rotations, recommandations). */
        .tb-inset-dark {
            border: 0;
            border-radius: 0;
            background: transparent;
            box-shadow: none;
            padding: 0;
            color: inherit;
        }
        .tb-inset-dark .csh-preview-panel,
        .tb-inset-dark .csh-rotations-shell,
        .tb-inset-dark .csh-constellation-shell,
        .tb-inset-dark .csh-artefact-empty { background: transparent; border: none; box-shadow: none; padding: 0; }
        .tb-inset-dark .csh-preview-table { margin: 0; gap: 1rem; }

        .tb-tab-empty {
            color: var(--ink-muted);
            font-style: italic;
            padding: 1rem 0;
        }

        .tb-empty-placeholder {
            display: grid;
            min-height: 90px;
            place-items: center;
            gap: 5px;
            padding: 18px;
            color: #8fa1c5;
            text-align: center;
            font-size: 13px;
            font-style: italic;
            border: 1px dashed rgba(148, 163, 184, 0.28);
            border-radius: 12px;
            background: linear-gradient(145deg, rgba(24, 32, 55, 0.72), rgba(8, 14, 29, 0.78));
        }

        .tb-empty-placeholder strong {
            color: #e2e8f0;
            font-size: 14px;
            font-style: normal;
        }

        .tb-photo-frame { width: 100%; height: 360px; border-radius: 12px; overflow: hidden; }
        .tb-photo-frame img { width: 100%; height: 100%; object-fit: cover; }

        @media (max-width: 900px) {
            .tb-photo-frame { height: 260px; }
        }

        button.tb-tag { cursor: pointer; font: inherit; }
        .tb-tag.is-current { background: var(--gold); color: #241a08; border-color: var(--gold); }
        .tb-story-ref { display: inline-flex; align-items: center; gap: 4px; color: var(--gold, #b8863d); text-decoration: underline; }
        .tb-story-ref img { width: 16px; height: 16px; border-radius: 4px; object-fit: cover; }
        .tb-nav-button:disabled { opacity: 0.35; cursor: not-allowed; }

    /* --- Styles réutilisés des widgets existants (armes/artefacts/rotations/constellations) --- */
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
    .csh-artefact-stats { display:grid; gap:.3rem; margin-top:.5rem; padding-top:.5rem; border-top:1px solid rgba(148,163,184,0.18); }
    .csh-artefact-stat { display:flex; justify-content:space-between; gap:.6rem; font-size:.76rem; color:#9fb2d7; }
    .csh-artefact-stat strong { color:#fef3c7; font-weight:700; }
    .csh-artefact-substats { display:flex; flex-wrap:wrap; gap:.35rem; margin-top:.45rem; }
    .csh-artefact-substat { font-size:.68rem; color:#c7d2e5; background: rgba(255,255,255,0.06); border:1px solid rgba(148,163,184,0.22); border-radius:999px; padding:.16rem .5rem; }
    .csh-artefact-note, .csh-weapon-note { margin-top:.4rem; color:#93a7cb; font-size:.7rem; font-style:italic; line-height:1.4; }

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
    Alpine.data('personnageBookData', () => ({
        // --- Livre / navigation par onglets --------------------------------
        tabsOrder: ['profile', 'builds', 'skills', 'constellations', 'teams'],
        activeTab: 'profile',
        isTurning: false,
        PAGE_TURN_DURATION: 720,

        get activeTabIndex() {
            return this.tabsOrder.indexOf(this.activeTab);
        },

        pageSourceId(tab, side) {
            return `tb-page-${tab}-${side}`;
        },

        fillTransitionContent(container, html) {
            if (!container) return;
            container.innerHTML = html;
            container.querySelectorAll('[id]').forEach((el) => el.removeAttribute('id'));
        },

        prepareTransitionPages(direction, targetTab) {
            const currentSide = direction === 'forward' ? 'right' : 'left';
            const targetSide = direction === 'forward' ? 'left' : 'right';

            const currentEl = document.getElementById(this.pageSourceId(this.activeTab, currentSide));
            const targetEl = document.getElementById(this.pageSourceId(targetTab, targetSide));

            const currentContent = currentEl ? currentEl.innerHTML : '';
            const targetContent = targetEl ? targetEl.innerHTML : '';

            this.$root.querySelectorAll('.tb-central-page, .tb-central-page-2, .tb-central-page-3').forEach((page) => {
                this.fillTransitionContent(page.querySelector('.tb-central-page-front .tb-central-page-content'), currentContent);
                this.fillTransitionContent(page.querySelector('.tb-central-page-back .tb-central-page-content'), targetContent);
            });
        },

        changeSection(targetTab) {
            const targetIndex = this.tabsOrder.indexOf(targetTab);
            if (this.isTurning || targetIndex === -1 || targetTab === this.activeTab) {
                return;
            }

            const direction = targetIndex > this.activeTabIndex ? 'forward' : 'backward';
            this.isTurning = true;

            this.prepareTransitionPages(direction, targetTab);

            const spread = this.$refs.bookSpread;
            spread.classList.remove('turn-forward', 'turn-backward');
            void spread.offsetWidth;

            window.setTimeout(() => {
                this.activeTab = targetTab;
                this.$nextTick(() => {
                    if (this.$refs.leftPageScroll) this.$refs.leftPageScroll.scrollTop = 0;
                    if (this.$refs.rightPageScroll) this.$refs.rightPageScroll.scrollTop = 0;
                });
            }, this.PAGE_TURN_DURATION * 0.48);

            const central = this.$refs.centralPage;
            const handleAnimationEnd = (event) => {
                if (event.animationName !== 'central-turn-forward' && event.animationName !== 'central-turn-backward') {
                    return;
                }
                central.removeEventListener('animationend', handleAnimationEnd);
                spread.classList.remove('turn-forward', 'turn-backward');
                this.isTurning = false;
            };
            central.addEventListener('animationend', handleAnimationEnd, { once: true });

            spread.classList.add(direction === 'forward' ? 'turn-forward' : 'turn-backward');

            window.setTimeout(() => {
                spread.classList.remove('turn-forward', 'turn-backward');
                this.isTurning = false;
            }, this.PAGE_TURN_DURATION + 180);
        },

        nextSection() {
            const idx = this.activeTabIndex;
            if (idx < this.tabsOrder.length - 1) this.changeSection(this.tabsOrder[idx + 1]);
        },
        prevSection() {
            const idx = this.activeTabIndex;
            if (idx > 0) this.changeSection(this.tabsOrder[idx - 1]);
        },

        initBookKeyboardNav() {
            document.addEventListener('keydown', (event) => {
                const el = document.activeElement;
                const isTyping = el && ['INPUT', 'TEXTAREA', 'SELECT'].includes(el.tagName);
                if (isTyping) return;
                if (event.key === 'ArrowRight') this.nextSection();
                if (event.key === 'ArrowLeft') this.prevSection();
            });
        },

        // --- Données personnage (vidéos, rotations, constellations…) -------
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
        radarValues: @json($radarValues),
        currentPhoto: 'portrait',

        radarPolygon(values, scale = 1) {
            const center = 50;
            const radius = 34 * scale;
            const keys = ['PV', 'ATQ', 'DEF', 'Taux CRIT', 'Dégâts CRIT', 'Recharge', 'Maîtrise'];
            return keys.map((key, index) => {
                const angle = (-Math.PI / 2) + (index * Math.PI * 2 / 7);
                const value = Math.max(0, Math.min(100, Number(values?.[key] ?? 0))) / 100;
                return `${center + Math.cos(angle) * radius * value},${center + Math.sin(angle) * radius * value}`;
            }).join(' ');
        },

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
                && from >= 1 && from <= 6
                && to >= 1 && to <= 6
                && from !== to
                && this.constellationMapPositions[String(from)]
                && this.constellationMapPositions[String(to)];
        },
        mapMediaMetrics(refName) {
            const canvas = this.$refs?.[refName];
            if (!canvas) return { left: 0, top: 0, width: 0, height: 0 };

            const canvasWidth = canvas.clientWidth || 0;
            const canvasHeight = canvas.clientHeight || 0;
            if (!canvasWidth || !canvasHeight) return { left: 0, top: 0, width: 0, height: 0 };

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
            const m = this.mapMediaMetrics(refName);
            return `left:${m.left}px;top:${m.top}px;width:${m.width}px;height:${m.height}px;`;
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
            if (!from || !to) return 'display:none;';

            const metrics = this.mapMediaMetrics(refName);
            if (!metrics.width || !metrics.height) return 'display:none;';

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
            if (this.constellationPointRecommended(idx)) classes += ' is-recommended';
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
        constellationLineClass(line) {
            const from = Number(line?.from || 0);
            const to = Number(line?.to || 0);
            const isOn = from <= this.activeConstellationLevel && to <= this.activeConstellationLevel;
            return isOn ? 'is-on' : 'is-off';
        },
        openConstellationPopup(index) {
            if (!this.constellations[Number(index) - 1]) return;
            this.selectConstellation(Number(index) - 1);
            this.constellationPopupOpen = true;
        },
        closeConstellationPopup() {
            this.constellationPopupOpen = false;
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
                setTimeout(() => { this.constellationGlow = false; }, 650);
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

        init() {
            this.initBookKeyboardNav();
        },
    }));
});
</script>

<div class="tb-scope">
<div class="tb-page-shell" @if($pageShellStyle) style="{{ $pageShellStyle }}" @endif x-data="personnageBookData()" x-init="init()">
    <div class="tb-book-scene">
        <section class="tb-book" aria-label="Dossier encyclopédique du personnage">
            <div class="tb-book-spread" x-ref="bookSpread">

                <section class="tb-book-page tb-left-page">
                    <div class="tb-page-content" id="tb-page-profile-left" x-show="activeTab === 'profile'">
                        <div class="tb-profile-gallery">
                            <div class="tb-profile-polaroid is-photo-front" :class="currentPhoto === 'portrait' ? 'is-photo-front' : 'is-photo-back'">
                                <div class="tb-photo-frame tb-portrait-frame">
                                    <img src="{{ $portraitUrl }}" alt="{{ $personnage->nom_perso }}" loading="lazy">
                                </div>
                                <div class="tb-polaroid-name">{{ $personnage->nom_perso }}</div>
                            </div>
                            <div class="tb-splash-polaroid" :class="currentPhoto === 'splash' ? 'is-photo-front' : 'is-photo-back'">
                                <span class="tb-paperclip" aria-hidden="true"></span>
                                <div class="tb-photo-frame tb-portrait-frame">
                                    <img src="{{ $splashUrl }}" alt="{{ $personnage->nom_perso }} — Splash Art" loading="lazy">
                                </div>
                                <div class="tb-polaroid-name">Splash Art</div>
                            </div>
                            <button type="button" class="tb-photo-toggle" @click="currentPhoto = currentPhoto === 'portrait' ? 'splash' : 'portrait'" aria-label="Changer de photo">&#8249;&nbsp;&nbsp;&#8250;</button>
                        </div>
                        <div class="tb-info-list">
                            <div><span>VA</span><strong>{{ $personnage->voix_va ?: '?' }}</strong></div>
                            <div><span>VJ</span><strong>{{ $personnage->voix_vj ?: '?' }}</strong></div>
                            <div><span>VC</span><strong>{{ $personnage->voix_vc ?: '?' }}</strong></div>
                        </div>
                    </div>

                    <div class="tb-page-content" id="tb-page-builds-left" x-show="activeTab === 'builds'">
                        <div class="tb-section-title">Armes recommandées</div>
                        <div class="tb-inset-dark">
                            @if($orderedWeaponRecommendations->count())
                                <div class="csh-preview-weapon-list">
                                    @foreach($orderedWeaponRecommendations as $index => $armeRec)
                                        @php
                                            $arme = $armeRec->arme;
                                            $rl = $arme?->etoile?->libelle ?? '?★';
                                            $rs = (int) preg_replace('/\D+/', '', (string) $rl);
                                            if ($rs < 1 || $rs > 5) { $rs = (int) ($arme?->fid_etoile ?? 1); }
                                            $weaponIcon = $photoUrl($arme?->photos->first()) ?? asset('images/placeholder.svg');
                                        @endphp
                                        <a href="{{ $arme ? route('armes.show', $arme) : '#' }}" class="csh-weapon-item">
                                            <div class="csh-weapon-index">{{ $armeRec->position ?? ($index + 1) }}</div>
                                            <div class="csh-weapon-icon-wrap th-weapon-rarity-{{ max(1, min(5, $rs)) }}">
                                                <img src="{{ $weaponIcon }}" alt="{{ $arme?->nom_arme ?? 'Arme' }}">
                                            </div>
                                            <div class="csh-weapon-copy">
                                                <div class="csh-weapon-name truncate">{{ $arme?->nom_arme ?? 'Arme inconnue' }}</div>
                                                <div class="csh-weapon-type">{{ $rl }} · {{ $arme?->typeArme?->libelle_TArme ?? '' }}</div>
                                                @if($armeRec->starter)
                                                    <div class="csh-weapon-badge">Starter</div>
                                                @endif
                                                @if($armeRec->commentaire)
                                                    <div class="csh-weapon-note">{{ $armeRec->commentaire }}</div>
                                                @endif
                                            </div>
                                        </a>
                                    @endforeach
                                </div>
                            @else
                                <div class="tb-empty-placeholder">
                                    <strong>Armes recommandées</strong>
                                    <span>Les recommandations apparaîtront ici.</span>
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="tb-page-content" id="tb-page-skills-left" x-show="activeTab === 'skills'">
                        <div class="tb-section-title">Showcase</div>
                        <div class="tb-inset-dark" style="padding:0; overflow:hidden; aspect-ratio:16/9; position:relative;">
                            <template x-if="activeEmbedUrl">
                                <iframe :src="activeEmbedUrl" frameborder="0" allowfullscreen style="position:absolute; inset:0; width:100%; height:100%;"></iframe>
                            </template>
                            <template x-if="!activeEmbedUrl">
                                <div class="tb-tab-empty" style="display:flex; align-items:center; justify-content:center; height:100%;">Aucune vidéo</div>
                            </template>
                            <button x-show="videos.length > 1" type="button" @click="prevVideo()" class="csh-video-nav csh-video-nav--prev">&#8249;</button>
                            <button x-show="videos.length > 1" type="button" @click="nextVideo()" class="csh-video-nav csh-video-nav--next">&#8250;</button>
                        </div>
                    </div>

                    <div class="tb-page-content" id="tb-page-constellations-left" x-show="activeTab === 'constellations'">
                        <div class="tb-section-title">Carte des constellations</div>
                        <div class="tb-inset-dark" style="min-height:360px;">
                            @if($constellations->count())
                                <div class="csh-constellation-media">
                                    <div class="csh-constellation-frame" x-ref="constellationPublicCanvas" :class="constellationGlow ? 'is-glowing' : ''">
                                        <template x-if="constellationMapImage">
                                            <div class="csh-constellation-map-wrap" :style="mapMediaStyle('constellationPublicCanvas')">
                                                <img :src="constellationMapImage" alt="Carte constellation" @load="updateConstellationMapNaturalSize($event)" style="object-fit: contain; object-position: center;">
                                                <template x-for="(line, idx) in constellationMapLines" :key="`public-map-line-${idx}`">
                                                    <template x-if="lineIsValid(line)">
                                                        <div class="csh-constellation-map-line" :class="constellationLineClass(line)" :style="mapLineStyle(line, 'constellationPublicCanvas')"></div>
                                                    </template>
                                                </template>
                                                <template x-for="index in [1,2,3,4,5,6]" :key="`public-map-point-${index}`">
                                                    <template x-if="constellationMapPositions[String(index)]">
                                                        <button type="button" class="csh-constellation-map-point" :class="constellationPointClass(index)" :style="mapPointStyle(index)" :aria-label="`Voir la constellation C${index}`" @click="openConstellationPopup(index)">
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
                            @else
                                <div class="tb-tab-empty">Aucune constellation disponible pour ce personnage.</div>
                            @endif
                        </div>
                    </div>

                    <div class="tb-page-content" id="tb-page-teams-left" x-show="activeTab === 'teams'">
                        <div class="tb-section-title">Rotations d'équipe</div>
                        <div class="tb-inset-dark">
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
                                                    <img :src="member.icon || '{{ asset('images/placeholder.svg') }}'" :alt="member.nom" :title="`${member.nom} (${member.element || 'Element'})`">
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
                        </div>
                    </div>

                    <div class="tb-page-content" id="tb-page-other-left" x-show="activeTab === 'other'">
                        <div class="tb-section-title">Histoires</div>
                        @if($personnage->histoires->count())
                            <div class="space-y-3">
                                @foreach($personnage->histoires as $index => $histoire)
                                    <div class="tb-panel">
                                        <div class="tb-small-heading">{{ $histoire->titre_histoire ?: ('Histoire ' . ($index + 1)) }}</div>
                                        <div class="tb-story">{!! $renderStoryHtml($histoire->histoire) !!}</div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="tb-tab-empty">Aucune histoire renseignée pour le moment.</div>
                        @endif
                    </div>
                </section>

                <section class="tb-book-page tb-right-page">
                    <div class="tb-page-content" id="tb-page-profile-right" x-show="activeTab === 'profile'">
                        <div class="tb-character-head">
                            <h1>{{ $personnage->nom_perso }}</h1>
                        </div>
                        <div class="tb-profile-facts">
                            <ul class="tb-info-list">
                                <li><span>Élément</span><strong><img src="{{ $elementIcon }}" alt="{{ $personnage->element?->libelle_element ?? 'Élément' }}" style="width:24px;height:24px;object-fit:contain;"></strong></li>
                                <li><span>Région</span><strong>{{ $nation?->nom_region ?? 'Inconnue' }}</strong></li>
                                <li><span>Arme</span><strong>{{ $personnage->typeArme?->libelle_TArme ?? 'Inconnue' }}</strong></li>
                                <li><span>Rareté</span><strong class="tb-stars">{{ str_repeat('★', $rarityStars) }}</strong></li>
                            </ul>
                        </div>
                        <div class="tb-profile-stats">
                            <div class="tb-radar-card">
                                <h2 class="tb-small-heading">Aperçu des statistiques</h2>
                                <div class="tb-radar-layout">
                                    <div class="tb-radar-numbers">
                                        @foreach($statDisplayValues as $label => $value)
                                            <div class="tb-radar-number"><span>{{ $label }}</span><strong>{{ $value ?: '?' }}</strong></div>
                                        @endforeach
                                    </div>
                                    <svg class="tb-radar" viewBox="0 0 100 100" role="img" aria-label="Radar des statistiques du personnage">
                                        <polygon class="tb-radar-grid" :points="radarPolygon({PV: 100, ATQ: 100, DEF: 100, 'Taux CRIT': 100, 'Dégâts CRIT': 100, Recharge: 100, Maîtrise: 100})"></polygon>
                                        <polygon class="tb-radar-grid" :points="radarPolygon({PV: 66, ATQ: 66, DEF: 66, 'Taux CRIT': 66, 'Dégâts CRIT': 66, Recharge: 66, Maîtrise: 66})"></polygon>
                                        <polygon class="tb-radar-grid" :points="radarPolygon({PV: 33, ATQ: 33, DEF: 33, 'Taux CRIT': 33, 'Dégâts CRIT': 33, Recharge: 33, Maîtrise: 33})"></polygon>
                                        <template x-for="index in 7" :key="`radar-axis-${index}`">
                                            <line class="tb-radar-axis" x1="50" y1="50" :x2="50 + Math.cos((-Math.PI / 2) + ((index - 1) * Math.PI * 2 / 7)) * 34" :y2="50 + Math.sin((-Math.PI / 2) + ((index - 1) * Math.PI * 2 / 7)) * 34"></line>
                                        </template>
                                        <polygon class="tb-radar-value" :points="radarPolygon(radarValues)"></polygon>
                                        <text class="tb-radar-label" x="50" y="9">PV</text>
                                        <text class="tb-radar-label" x="82" y="25">ATQ</text>
                                        <text class="tb-radar-label" x="86" y="68">DEF</text>
                                        <text class="tb-radar-label" x="66" y="96">Taux CRIT</text>
                                        <text class="tb-radar-label" x="34" y="96">Dégâts CRIT</text>
                                        <text class="tb-radar-label" x="14" y="68">Recharge</text>
                                        <text class="tb-radar-label" x="18" y="25">Maîtrise</text>
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="tb-page-content" id="tb-page-builds-right" x-show="activeTab === 'builds'">
                        <div class="tb-section-title">Artefacts recommandés</div>
                        <div class="tb-inset-dark">
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

                                            @if($build->main_stat_sablier || $build->main_stat_gobelet || $build->main_stat_couronne)
                                                <div class="csh-artefact-stats">
                                                    @if($build->main_stat_sablier)
                                                        <div class="csh-artefact-stat"><span>Sablier</span><strong>{{ $build->main_stat_sablier }}</strong></div>
                                                    @endif
                                                    @if($build->main_stat_gobelet)
                                                        <div class="csh-artefact-stat"><span>Gobelet</span><strong>{{ $build->main_stat_gobelet }}</strong></div>
                                                    @endif
                                                    @if($build->main_stat_couronne)
                                                        <div class="csh-artefact-stat"><span>Couronne</span><strong>{{ $build->main_stat_couronne }}</strong></div>
                                                    @endif
                                                </div>
                                            @endif

                                            @if($build->sub_stats)
                                                <div class="csh-artefact-substats">
                                                    @foreach(array_filter(array_map('trim', explode(',', (string) $build->sub_stats))) as $subIndex => $sub)
                                                        <span class="csh-artefact-substat">{{ $subIndex + 1 }}. {{ $sub }}</span>
                                                    @endforeach
                                                </div>
                                            @endif

                                            @if($build->commentaire)
                                                <div class="csh-artefact-note">{{ $build->commentaire }}</div>
                                            @endif
                                        </article>
                                    @endforeach
                                </div>
                            @else
                                <div class="tb-empty-placeholder">
                                    <strong>Artefacts recommandés</strong>
                                    <span>Les sets et statistiques apparaîtront ici.</span>
                                </div>
                            @endif
                        </div>

                        <div class="tb-section-title" style="margin-top:1.25rem;">Stats recommandées</div>
                        <div class="tb-inset-dark">
                            @if($personnage->statsRecommandees->count())
                                <div class="csh-stats-recommandees-list">
                                    @foreach($personnage->statsRecommandees as $stats)
                                        <article class="csh-stats-card">
                                            <div class="csh-artefact-title">{{ $stats->nom_build ?: 'Build ' . ($loop->iteration) }}</div>
                                            <div class="csh-stats-grid">
                                                @foreach([
                                                    'PV' => $stats->pv,
                                                    'ATQ' => $stats->atq,
                                                    'DEF' => $stats->def,
                                                    'Taux CRIT' => $stats->taux_crit,
                                                    'Dégâts CRIT' => $stats->degats_crit,
                                                    'Maîtrise élémentaire' => $stats->maitrise_elementaire,
                                                    'Recharge énergétique' => $stats->recharge_energetique,
                                                ] as $label => $value)
                                                    <div class="csh-stats-cell"><span>{{ $label }}</span><strong>{{ $value ?: '?' }}</strong></div>
                                                @endforeach
                                            </div>
                                            @if($stats->commentaire)
                                                <div class="csh-weapon-note">{{ $stats->commentaire }}</div>
                                            @endif
                                        </article>
                                    @endforeach
                                </div>
                            @else
                                <div class="tb-empty-placeholder">
                                    <strong>Stats recommandées</strong>
                                    <span>Les objectifs de build apparaîtront ici.</span>
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="tb-page-content" id="tb-page-skills-right" x-show="activeTab === 'skills'">
                        <div class="tb-section-title">Aptitudes</div>
                        @if($personnage->aptitudes->count())
                            <div class="tb-skill-list">
                                @foreach($personnage->aptitudes as $aptitude)
                                    <div id="aptitude-{{ $aptitude->id_aptitude }}" class="tb-skill-card">
                                        <div class="tb-skill-icon">
                                            <img src="{{ $photoUrl($aptitude->photos->first()) ?? asset('images/placeholder.svg') }}" alt="">
                                        </div>
                                        <div>
                                            @if($aptitude->typeApti)
                                                <span class="tb-tag" style="margin-bottom:.35rem; display:inline-block;">{{ $aptitude->typeApti->libelle_Apti }}</span>
                                            @endif
                                            <div class="tb-small-heading" style="margin:0 0 .25rem;">{{ $aptitude->titre_apti }}</div>
                                            <p style="margin:0; font-size:.85rem; color: var(--ink-muted, #6b5847);">{{ $aptitude->descri_apti }}</p>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="tb-tab-empty">Aucune aptitude renseignée pour le moment.</div>
                        @endif
                    </div>

                    <div class="tb-page-content" id="tb-page-constellations-right" x-show="activeTab === 'constellations'">
                        <div class="tb-section-title">Détails</div>
                        @if($constellations->count())
                            <div class="tb-constellation-index">
                                <template x-for="(c, i) in constellations" :key="`const-idx-${i}`">
                                    <button type="button" class="tb-tag" :class="i + 1 === activeConstellationLevel ? 'is-current' : ''" @click="openConstellationPopup(i + 1)" x-text="c.label + (c.recommandee ? ' ★' : '')"></button>
                                </template>
                            </div>
                            <div class="tb-tab-empty" style="margin-top:1rem;">
                                Cliquez sur une constellation (sur la carte ou ci-dessus) pour afficher son descriptif.
                                <template x-if="constellations.some(c => c.recommandee)">
                                    <div style="margin-top:.5rem;">★ = constellation recommandée</div>
                                </template>
                            </div>
                        @else
                            <div class="tb-tab-empty">—</div>
                        @endif
                    </div>

                    <div class="tb-page-content" id="tb-page-teams-right" x-show="activeTab === 'teams'">
                        <div class="tb-section-title">À propos des réactions</div>
                        <div class="tb-panel">
                            <p style="margin:0 0 .75rem;">Chaque équipe propose une rotation optimisée autour d'une réaction élémentaire. Cliquez sur « Voir rotation » pour l'ordre exact des compétences.</p>
                            <div class="tb-info-list">
                                <div><span>Recommended</span><strong>Meilleur DPS global</strong></div>
                                <div><span>F2P</span><strong>Accessible sans gacha</strong></div>
                            </div>
                        </div>
                    </div>

                    <div class="tb-page-content" id="tb-page-other-right" x-show="activeTab === 'other'">
                        <div class="tb-section-title">Spécialité culinaire</div>
                        @if($personnage->specialite && $personnage->specialite->plat)
                            <div class="tb-panel" x-data="{ open: false }">
                                <div style="display:flex; gap:1rem; align-items:center;">
                                    <img src="{{ $photoUrl($personnage->specialite->plat->photos->first()) ?? asset('images/placeholder.svg') }}"
                                         alt="{{ $personnage->specialite->plat->nom_plat }}"
                                         style="width:64px;height:64px;border-radius:10px;object-fit:cover;">
                                    <div>
                                        <a href="{{ route('cuisine.show', $personnage->specialite->plat->slug) }}" class="tb-small-heading" style="text-decoration:underline;">
                                            {{ $personnage->specialite->libelle_spe }}
                                        </a>
                                        <p style="margin:.35rem 0 0; font-size:.85rem;">{{ $personnage->specialite->descri_spe }}</p>
                                    </div>
                                </div>
                                <button type="button" @click="open = !open" class="tb-tag" style="margin-top:.75rem;">Voir plat original</button>
                                <div x-show="open" x-transition style="margin-top:.75rem;">
                                    <p style="margin:0; font-weight:600;">{{ $personnage->specialite->plat->nom_plat }}</p>
                                    <p style="margin:.25rem 0 0; font-size:.85rem;">{{ $personnage->specialite->plat->descri_plat }}</p>
                                </div>
                            </div>
                        @else
                            <div class="tb-tab-empty">Aucune spécialité culinaire renseignée pour le moment.</div>
                        @endif
                    </div>
                </section>

                <div class="tb-central-page" x-ref="centralPage" aria-hidden="true">
                    <div class="tb-central-page-front"><div class="tb-central-page-content"></div></div>
                    <div class="tb-central-page-back"><div class="tb-central-page-content"></div></div>
                </div>
                <div class="tb-central-page tb-central-page-2" aria-hidden="true">
                    <div class="tb-central-page-front"><div class="tb-central-page-content"></div></div>
                    <div class="tb-central-page-back"><div class="tb-central-page-content"></div></div>
                </div>
                <div class="tb-central-page tb-central-page-3" aria-hidden="true">
                    <div class="tb-central-page-front"><div class="tb-central-page-content"></div></div>
                    <div class="tb-central-page-back"><div class="tb-central-page-content"></div></div>
                </div>
            </div>

            <nav class="tb-book-tabs" aria-label="Sections du dossier">
                <button type="button" class="tb-book-tab tb-profile-tab" :class="activeTab === 'profile' ? 'tb-active' : ''" @click="changeSection('profile')"><span>01</span>Profil</button>
                <button type="button" class="tb-book-tab tb-builds-tab" :class="activeTab === 'builds' ? 'tb-active' : ''" @click="changeSection('builds')"><span>02</span>Builds</button>
                <button type="button" class="tb-book-tab tb-skills-tab" :class="activeTab === 'skills' ? 'tb-active' : ''" @click="changeSection('skills')"><span>03</span>Compétences</button>
                <button type="button" class="tb-book-tab tb-constellations-tab" :class="activeTab === 'constellations' ? 'tb-active' : ''" @click="changeSection('constellations')"><span>04</span>Constellations</button>
                <button type="button" class="tb-book-tab tb-teams-tab" :class="activeTab === 'teams' ? 'tb-active' : ''" @click="changeSection('teams')"><span>05</span>Équipes / Réactions</button>
            </nav>
        </section>
    </div>

    <div class="tb-book-navigation">
        <button type="button" class="tb-nav-button" :disabled="activeTabIndex === 0" @click="prevSection()">← Précédent</button>
        <button type="button" class="tb-nav-button" :disabled="activeTabIndex === tabsOrder.length - 1" @click="nextSection()">Suivant →</button>
    </div>

    {{-- Modales (hors des pages, restent accessibles quel que soit l'onglet actif) --}}
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
                                     style="width:36px;height:36px;border-radius:999px;border:2px solid rgba(255,255,255,.22);object-fit:cover;flex-shrink:0;">
                                <div class="text-sm font-semibold text-slate-100" x-text="member.nom"></div>
                            </div>
                            <template x-if="member.slot <= 4 && member.aptitudes && member.aptitudes.length > 0">
                                <div class="flex flex-col gap-1">
                                    <template x-for="(apt, ai) in member.aptitudes" :key="`apt-${member.slot}-${ai}`">
                                        <div class="flex items-center gap-2">
                                            <img :src="apt.icon || '{{ asset('images/placeholder.svg') }}'" :alt="apt.titre"
                                                 style="width:22px;height:22px;border-radius:6px;object-fit:cover;flex-shrink:0;border:1px solid rgba(148,163,184,.2);">
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
                                <img :src="apt.icon || '{{ asset('images/placeholder.svg') }}'" :alt="`${apt.nom_perso} - ${apt.titre}`"
                                     style="width:48px;height:48px;border-radius:8px;object-fit:cover;border:2px solid rgba(99,102,241,.4);cursor:pointer;">
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
</div>
</div>
