<!DOCTYPE html>
<html lang="fr" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>NetoCar — Le système d’exploitation de votre centre de lavage</title>
    <meta name="description" content="NetoCar centralise réservations, clients, équipe, tickets et revenus pour rendre votre centre de lavage plus fluide, plus rentable et plus simple à piloter.">
    <meta name="theme-color" content="#050b16">
    <link rel="icon" href="/favicon.ico" sizes="any">
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">
    <link rel="apple-touch-icon" href="/apple-touch-icon.png">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=manrope:400,500,600,700,800&family=space-grotesk:500,600,700" rel="stylesheet">
    <link rel="preload" as="image" href="{{ asset('images/netocar-hero.webp') }}" fetchpriority="high">
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        :root {
            --nc-ink: #050b16;
            --nc-ink-soft: #0b1425;
            --nc-blue: #2764ff;
            --nc-cyan: #42d7ff;
            --nc-mint: #8ff7d4;
            --nc-paper: #f6f8fc;
            --nc-paper-alt: #edf3fa;
            --nc-page-bg: #f7fbff;
            --nc-surface: rgba(255,255,255,.88);
            --nc-muted: #5d6b80;
            --nc-line: rgba(15, 23, 42, .12);
        }

        * { box-sizing: border-box; }
        body { margin: 0; background: var(--nc-page-bg); color: var(--nc-ink); font-family: 'Manrope', sans-serif; font-feature-settings: 'kern' 1, 'liga' 1; text-rendering: optimizeLegibility; }
        img, svg, video { display: block; }
        section[id] { scroll-margin-top: 4.75rem; }
        :focus-visible { outline: 3px solid rgba(66,215,255,.85); outline-offset: 4px; border-radius: .5rem; }
        .nc-display { font-family: 'Space Grotesk', sans-serif; letter-spacing: -.04em; }
        .nc-shell { width: min(calc(100% - 2rem), 1240px); margin-inline: auto; }
        .nc-page {
            position: relative; isolation: isolate; min-height: 100vh; overflow-x: clip;
            background: linear-gradient(180deg, #f9fcff 0%, #eef8fb 44%, #f8fbff 100%);
        }
        .nc-page::before {
            position: fixed; inset: 0; z-index: 0; content: ''; pointer-events: none; opacity: .62;
            background-image: linear-gradient(115deg, transparent 0 38%, rgba(255,255,255,.55) 44%, transparent 51% 100%), linear-gradient(180deg, rgba(66,215,255,.08), rgba(143,247,212,.06) 46%, rgba(255,255,255,0) 78%);
            background-size: 180% 100%, 100% 100%;
        }
        .nc-page > main, .nc-page > footer { position: relative; z-index: 1; }
        .nc-water-bubbles {
            position: fixed; inset: 0; z-index: 0; overflow: hidden; pointer-events: none; contain: paint; opacity: .5;
        }
        .nc-water-bubbles span {
            position: absolute; left: var(--x); bottom: -8rem; width: var(--s); height: var(--s); border-radius: 999px;
            border: 1px solid rgba(39,100,255,.18);
            background: radial-gradient(circle at 34% 28%, rgba(255,255,255,.82) 0 14%, rgba(255,255,255,.2) 15% 27%, rgba(66,215,255,.08) 28% 100%);
            box-shadow: inset 0 0 0 1px rgba(255,255,255,.42);
            animation: nc-bubble-rise var(--d) linear infinite;
            animation-delay: var(--delay);
            will-change: transform, opacity;
        }
        @keyframes nc-bubble-rise {
            0% { transform: translate3d(0, 12vh, 0) scale(.7); opacity: 0; }
            12% { opacity: .62; }
            82% { opacity: .42; }
            100% { transform: translate3d(var(--drift), -112vh, 0) scale(1); opacity: 0; }
        }
        .nc-surface-section, .nc-tint-section { background: transparent; }
        .nc-section-title { max-width: 17ch; color: #0f172a; font-size: clamp(2.55rem, 5.1vw, 4.35rem); line-height: .98; text-wrap: balance; }
        .nc-section-title .nc-title-accent { color: #14b8a6; }
        .nc-section-copy { color: var(--nc-muted); font-size: 1rem; line-height: 2; }
        .nc-noise::after {
            position: absolute; inset: 0; content: ''; pointer-events: none; opacity: .055;
            background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 180 180' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='.9' numOctaves='3' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)' opacity='.7'/%3E%3C/svg%3E");
        }
        .nc-grid {
            background-image: linear-gradient(rgba(255,255,255,.045) 1px, transparent 1px), linear-gradient(90deg, rgba(255,255,255,.045) 1px, transparent 1px);
            background-size: 72px 72px;
        }
        .nc-nav { transition: background .35s ease, border-color .35s ease, transform .35s ease; }
        .nc-menu-trigger { display: none; }
        .nc-nav.is-scrolled { background: rgba(5,11,22,.82); border-color: rgba(255,255,255,.1); backdrop-filter: blur(18px); }
        .nc-logo-mark { box-shadow: 0 0 0 1px rgba(255,255,255,.14), 0 12px 30px rgba(39,100,255,.3); }
        .nc-hero {
            min-height: 100svh; color: white; isolation: isolate; overflow: hidden;
            background: radial-gradient(circle at 72% 38%, rgba(39,100,255,.22), transparent 27%), var(--nc-ink);
        }
        .nc-hero-photo { position: absolute; inset: 0 0 0 36%; z-index: -3; }
        .nc-hero-photo img { width: 100%; height: 100%; object-fit: cover; object-position: 57% center; filter: saturate(1.05) contrast(1.08) brightness(1.03); transform: scale(1.01); transform-origin: center; }
        .nc-hero-photo::after {
            position: absolute; inset: 0; content: '';
            background: linear-gradient(90deg, #050b16 0%, rgba(5,11,22,.88) 17%, rgba(5,11,22,.2) 58%, rgba(5,11,22,.18) 100%), linear-gradient(0deg, rgba(5,11,22,.9) 0%, transparent 34%);
        }
        .nc-hero.nc-noise::after { opacity: .025; }
        .nc-orb { position: absolute; border-radius: 999px; filter: blur(2px); pointer-events: none; }
        .nc-orb-a { width: 36rem; height: 36rem; top: -20rem; left: -12rem; background: rgba(105,223,255,.2); }
        .nc-orb-b { width: 26rem; height: 26rem; right: -12rem; bottom: -10rem; background: rgba(66,215,255,.28); }
        .nc-pill { border: 1px solid rgba(120,210,255,.27); background: rgba(39,100,255,.12); box-shadow: inset 0 1px rgba(255,255,255,.08); }
        .nc-btn { display: inline-flex; align-items: center; justify-content: center; gap: .7rem; min-height: 3.35rem; padding: .8rem 1.35rem; border-radius: .85rem; font-weight: 800; transition: transform .25s ease, box-shadow .25s ease, background .25s ease, border-color .25s ease; }
        .nc-btn:hover { transform: translateY(-3px); }
        .nc-btn-primary { color: white; background: var(--nc-blue); box-shadow: 0 18px 40px rgba(39,100,255,.28); }
        .nc-btn-primary:hover { background: #3d74ff; box-shadow: 0 22px 48px rgba(39,100,255,.4); }
        .nc-btn-light { color: var(--nc-ink); background: white; box-shadow: 0 18px 40px rgba(0,0,0,.18); }
        .nc-btn-ghost { color: white; border: 1px solid rgba(255,255,255,.2); background: rgba(255,255,255,.055); backdrop-filter: blur(12px); }
        .nc-hero-signal { position: relative; display: grid; width: min(28rem, 82%); aspect-ratio: 1; place-items: center; border: 1px solid rgba(180,238,255,.32); border-radius: 999px; box-shadow: 0 0 70px rgba(66,215,255,.1); }
        .nc-hero-signal::before, .nc-hero-signal::after { position: absolute; content: ''; border: 1px solid rgba(105,223,255,.34); border-radius: inherit; }
        .nc-hero-signal::before { inset: 13%; }
        .nc-hero-signal::after { inset: 28%; background: radial-gradient(circle, rgba(66,215,255,.22), transparent 68%); }
        .nc-hero-orbit { position: absolute; inset: -1px; border-radius: inherit; animation: nc-orbit 18s linear infinite; }
        .nc-hero-orbit::before { position: absolute; top: 12%; right: 12%; width: 11px; height: 11px; content: ''; border-radius: 999px; background: #69dfff; box-shadow: 0 0 24px #42d7ff; }
        .nc-chart-line { stroke-dasharray: 500; stroke-dashoffset: 500; animation: nc-draw 2.2s .45s cubic-bezier(.2,.8,.2,1) forwards; }
        @keyframes nc-orbit { to { transform: rotate(360deg); } }
        @keyframes nc-draw { to { stroke-dashoffset: 0; } }
        .nc-float { animation: nc-float 6s ease-in-out infinite; }
        .nc-float-late { animation: nc-float 7s 1s ease-in-out infinite; }
        @keyframes nc-float { 0%,100% { transform: translate3d(0,0,0); } 50% { transform: translate3d(0,-10px,0); } }
        .nc-marquee { display: flex; width: max-content; animation: nc-marquee 28s linear infinite; }
        .nc-marquee:hover { animation-play-state: paused; }
        @keyframes nc-marquee { to { transform: translateX(-50%); } }
        .nc-section { padding-block: clamp(5.5rem, 9vw, 8.5rem); }
        .nc-kicker { display: inline-flex; align-items: center; gap: .65rem; color: var(--nc-blue); font-size: .7rem; font-weight: 800; letter-spacing: .2em; text-transform: uppercase; }
        .nc-kicker::before { width: 1.8rem; height: 2px; content: ''; background: currentColor; }
        .nc-card { border: 1px solid rgba(148,163,184,.24); background: var(--nc-surface); box-shadow: 0 24px 65px rgba(15,23,42,.07); backdrop-filter: blur(14px); }
        .nc-card-dark { border: 1px solid rgba(255,255,255,.1); background: rgba(255,255,255,.055); box-shadow: inset 0 1px rgba(255,255,255,.06); }
        .nc-lift { transition: transform .35s cubic-bezier(.2,.8,.2,1), box-shadow .35s ease, border-color .35s ease; }
        .nc-lift:hover { transform: translateY(-7px); box-shadow: 0 32px 80px rgba(15,23,42,.14); border-color: rgba(39,100,255,.28); }
        .nc-number { color: transparent; -webkit-text-stroke: 1px rgba(39,100,255,.4); }
        .nc-photo-mask { clip-path: polygon(0 0, 100% 0, 100% 88%, 86% 100%, 0 100%); }
        .nc-product-glow::before { position: absolute; width: 70%; height: 50%; left: 15%; bottom: -8%; content: ''; background: rgba(39,100,255,.2); filter: blur(80px); border-radius: 999px; }
        .nc-feature-story { background: transparent; }
        .nc-feature-row { display: grid; grid-template-columns: minmax(0,1.08fr) minmax(0,.92fr); gap: clamp(2.5rem,6vw,6rem); align-items: center; padding-block: clamp(4.5rem,8vw,7.5rem); border-top: 1px solid rgba(15,23,42,.14); }
        .nc-feature-row:first-child { border-top: 0; }
        .nc-feature-visual { position: relative; isolation: isolate; aspect-ratio: 1.5; overflow: hidden; border: 1px solid rgba(148,163,184,.28); border-radius: 1.75rem; background: #071326; box-shadow: 0 30px 80px rgba(15,23,42,.16); }
        .nc-feature-visual::after { position: absolute; inset: 0; content: ''; pointer-events: none; border-radius: inherit; box-shadow: inset 0 0 0 1px rgba(255,255,255,.12); }
        .nc-feature-visual img { width: 100%; height: 100%; object-fit: contain; object-position: center; transition: transform 1.1s cubic-bezier(.2,.75,.2,1); }
        .nc-feature-visual[data-fit="cover"] img { object-fit: cover; }
        .nc-feature-row:hover .nc-feature-visual img { transform: scale(1.025); }
        .nc-feature-index { color: transparent; -webkit-text-stroke: 1px rgba(39,100,255,.38); }
        .nc-feature-proof { display: flex; align-items: center; gap: .8rem; margin-top: 2rem; padding-top: 1.25rem; border-top: 1px solid rgba(15,23,42,.13); color: #334155; }
        .nc-pricing-feature::before { content: '✓'; display: grid; place-items: center; flex: 0 0 1.25rem; height: 1.25rem; border-radius: 999px; color: var(--nc-blue); background: rgba(39,100,255,.1); font-size: .72rem; font-weight: 900; }
        .nc-faq summary::-webkit-details-marker { display: none; }
        .nc-faq summary { list-style: none; }
        .nc-faq[open] .nc-plus { transform: rotate(45deg); color: var(--nc-blue); }
        .nc-plus { transition: transform .25s ease, color .25s ease; }
        [data-reveal] { opacity: 0; transform: translate3d(0, 28px, 0); transition: opacity .85s cubic-bezier(.2,.75,.2,1), transform .85s cubic-bezier(.2,.75,.2,1); transition-delay: var(--delay, 0ms); }
        [data-reveal="left"] { transform: translate3d(-36px, 0, 0); }
        [data-reveal="right"] { transform: translate3d(36px, 0, 0); }
        [data-reveal].is-visible { opacity: 1; transform: translate3d(0,0,0); }
        .nc-spotlight { background: radial-gradient(520px circle at var(--mx, 50%) var(--my, 50%), rgba(66,215,255,.11), transparent 48%); }

        /* Premium comparison module */
        .nc-problem-section { background: #fff; }
        .nc-problem-shell { width: min(calc(100% - 2rem), 1280px); margin-inline: auto; }
        .nc-problem-panel {
            position: relative; overflow: hidden; border: 1px solid rgba(148,163,184,.16); border-radius: 24px;
            background: #fff; padding: clamp(3rem, 6.5vw, 6rem);
            box-shadow: 0 28px 80px rgba(15,23,42,.07), 0 1px 0 rgba(255,255,255,.8) inset;
        }
        .nc-problem-panel::before {
            position: absolute; inset: 0; content: ''; pointer-events: none;
            background: radial-gradient(circle at 18% 10%, rgba(20,184,166,.08), transparent 26%), radial-gradient(circle at 84% 14%, rgba(39,100,255,.06), transparent 24%);
        }
        .nc-problem-intro { position: relative; z-index: 1; max-width: 780px; margin-inline: auto; text-align: center; }
        .nc-problem-eyebrow { color: #14b8a6; font-size: .72rem; font-weight: 900; letter-spacing: .18em; text-transform: uppercase; }
        .nc-problem-title { margin-top: 1.25rem; color: #0f172a; font-size: clamp(2.45rem, 5vw, 4.7rem); line-height: .98; text-wrap: balance; }
        .nc-problem-copy { margin: 1.4rem auto 0; max-width: 660px; color: #64748b; font-size: clamp(1rem, 1.5vw, 1.12rem); line-height: 1.9; }
        .nc-comparison-grid {
            position: relative; z-index: 1; display: grid; grid-template-columns: minmax(0, 1fr) 88px minmax(0, 1fr);
            gap: clamp(1.25rem, 3vw, 2rem); align-items: stretch; margin-top: clamp(3rem, 6vw, 4.75rem);
        }
        .nc-compare-card {
            position: relative; overflow: hidden; border: 1px solid rgba(148,163,184,.18); border-radius: 24px;
            padding: clamp(1.25rem, 2.4vw, 1.8rem); box-shadow: 0 18px 48px rgba(15,23,42,.07);
            transition: translate .35s cubic-bezier(.22,1,.36,1), box-shadow .35s cubic-bezier(.22,1,.36,1), border-color .35s cubic-bezier(.22,1,.36,1);
            will-change: transform, opacity;
        }
        .nc-compare-card:hover { translate: 0 -8px; box-shadow: 0 30px 80px rgba(15,23,42,.13); }
        .nc-compare-card--before { background: linear-gradient(135deg, #f8fafc 0%, #eef2f7 100%); }
        .nc-compare-card--after { background: linear-gradient(135deg, #fff 0%, #f8fffd 100%); }
        .nc-compare-card--after:hover { border-color: rgba(20,184,166,.34); box-shadow: 0 34px 90px rgba(20,184,166,.18), 0 18px 48px rgba(15,23,42,.08); }
        .nc-compare-head { display: flex; align-items: center; justify-content: space-between; gap: 1rem; margin-bottom: 1.25rem; }
        .nc-compare-title { color: #0f172a; font-size: 1.25rem; font-weight: 900; letter-spacing: -.02em; }
        .nc-compare-chip { border-radius: 999px; padding: .45rem .7rem; font-size: .68rem; font-weight: 900; letter-spacing: .12em; text-transform: uppercase; }
        .nc-compare-chip--muted { color: #64748b; background: #e2e8f0; }
        .nc-compare-chip--good { color: #0f766e; background: rgba(20,184,166,.13); }
        .nc-image-stage {
            position: relative; overflow: hidden; display: grid; align-items: center; min-height: 26rem; border-radius: 20px;
            background: #f8fafc; box-shadow: inset 0 0 0 1px rgba(148,163,184,.18);
        }
        .nc-image-stage img { width: 100%; height: 100%; max-height: 28rem; object-fit: contain; object-position: center; padding: clamp(.6rem, 1.5vw, 1rem); }
        .nc-image-stage--before { background: linear-gradient(145deg, #f1f5f9, #e5e7eb); }
        .nc-image-stage--after { background: linear-gradient(145deg, #f8fffd, #ecfeff); }
        .nc-chaos-note {
            position: absolute; z-index: 2; width: 7.5rem; border: 1px solid rgba(148,163,184,.28); border-radius: 14px;
            background: rgba(255,255,255,.88); padding: .7rem; color: #475569; font-size: .72rem; font-weight: 800;
            box-shadow: 0 14px 34px rgba(15,23,42,.11); animation: nc-note-float 5.5s ease-in-out infinite;
        }
        .nc-chaos-note--a { top: 12%; left: 7%; rotate: -5deg; }
        .nc-chaos-note--b { right: 7%; bottom: 30%; rotate: 4deg; animation-delay: -1.8s; }
        .nc-phone-frame {
            position: absolute; left: 8%; bottom: 9%; z-index: 3; width: 8.5rem; border: 1px solid rgba(15,23,42,.1); border-radius: 22px;
            background: rgba(15,23,42,.92); padding: .55rem; color: white; box-shadow: 0 24px 50px rgba(15,23,42,.2);
            animation: nc-phone-shake 6s cubic-bezier(.36,.07,.19,.97) infinite;
        }
        .nc-phone-screen { border-radius: 16px; background: #f8fafc; padding: .7rem; color: #0f172a; }
        .nc-whatsapp-toast {
            position: absolute; right: 8%; top: 12%; z-index: 4; border-radius: 16px; background: rgba(255,255,255,.95);
            padding: .8rem .9rem; color: #0f172a; font-size: .78rem; font-weight: 850; box-shadow: 0 18px 44px rgba(15,23,42,.14);
            animation: nc-toast-cycle 6.5s ease-in-out infinite;
        }
        .nc-missed-badge {
            position: absolute; left: 10%; top: 10%; z-index: 4; border-radius: 999px; background: #fee2e2; color: #dc2626;
            padding: .48rem .72rem; font-size: .72rem; font-weight: 900; box-shadow: 0 12px 28px rgba(220,38,38,.15);
        }
        .nc-kpi-row { position: absolute; inset-inline: 1rem; bottom: 1rem; z-index: 3; display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: .6rem; }
        .nc-kpi-card {
            border: 1px solid rgba(20,184,166,.18); border-radius: 16px; background: rgba(255,255,255,.88); padding: .72rem;
            box-shadow: 0 16px 34px rgba(15,23,42,.08); backdrop-filter: blur(12px);
        }
        .nc-kpi-value { display: block; color: #0f172a; font-size: 1.1rem; font-weight: 950; line-height: 1; }
        .nc-kpi-label { margin-top: .28rem; display: block; color: #64748b; font-size: .65rem; font-weight: 800; }
        .nc-status-stack { position: absolute; top: 1rem; right: 1rem; z-index: 3; display: flex; flex-wrap: wrap; justify-content: flex-end; gap: .45rem; }
        .nc-status-pill {
            border: 1px solid rgba(20,184,166,.18); border-radius: 999px; background: rgba(255,255,255,.9);
            padding: .45rem .65rem; color: #0f766e; font-size: .68rem; font-weight: 900; box-shadow: 0 12px 28px rgba(15,23,42,.08);
            animation: nc-status-glide 6s ease-in-out infinite;
        }
        .nc-status-pill:nth-child(2) { animation-delay: -1.4s; }
        .nc-status-pill:nth-child(3) { animation-delay: -2.8s; }
        .nc-success-check {
            position: absolute; left: 1rem; top: 1rem; z-index: 4; display: grid; width: 2.75rem; height: 2.75rem; place-items: center;
            border-radius: 999px; background: #14b8a6; color: white; box-shadow: 0 18px 38px rgba(20,184,166,.32);
        }
        .nc-problem-list { display: grid; gap: .85rem; margin-top: 1.35rem; }
        .nc-problem-list li { display: flex; align-items: flex-start; gap: .75rem; color: #334155; font-size: .94rem; font-weight: 750; line-height: 1.55; }
        .nc-list-icon {
            display: grid; flex: 0 0 1.45rem; width: 1.45rem; height: 1.45rem; place-items: center; border-radius: 999px;
            transition: rotate .35s cubic-bezier(.22,1,.36,1), transform .35s cubic-bezier(.22,1,.36,1);
        }
        .nc-compare-card:hover .nc-list-icon { rotate: 8deg; transform: scale(1.05); }
        .nc-list-icon--bad { color: #dc2626; background: #fee2e2; }
        .nc-list-icon--good { color: #0f766e; background: rgba(20,184,166,.14); }
        .nc-list-icon svg { width: .875rem; height: .875rem; }
        .nc-flow-rail { position: relative; display: grid; min-height: 100%; place-items: center; pointer-events: none; }
        .nc-flow-line {
            position: absolute; top: 1.1rem; bottom: 1.1rem; width: 1px; transform-origin: top center;
            background: linear-gradient(180deg, transparent, rgba(20,184,166,.8), transparent);
        }
        .nc-flow-arrow {
            position: relative; z-index: 2; display: grid; width: 3.25rem; height: 3.25rem; place-items: center; border-radius: 999px;
            color: #14b8a6; background: #0f172a; box-shadow: 0 18px 45px rgba(20,184,166,.24);
        }
        .nc-flow-arrow svg, .nc-success-check svg { width: 1.25rem; height: 1.25rem; }
        @keyframes nc-note-float {
            0%, 100% { transform: translate3d(0, 0, 0); }
            50% { transform: translate3d(.35rem, -.45rem, 0); }
        }
        @keyframes nc-toast-cycle {
            0%, 16%, 100% { opacity: 0; transform: translate3d(.8rem, -.5rem, 0) scale(.96); }
            28%, 68% { opacity: 1; transform: translate3d(0, 0, 0) scale(1); }
        }
        @keyframes nc-phone-shake {
            0%, 88%, 100% { transform: translate3d(0,0,0) rotate(-2deg); }
            90%, 94%, 98% { transform: translate3d(-1px,0,0) rotate(-3deg); }
            92%, 96% { transform: translate3d(1px,0,0) rotate(-1deg); }
        }
        @keyframes nc-status-glide {
            0%, 100% { transform: translate3d(0,0,0); }
            50% { transform: translate3d(-.45rem,.25rem,0); }
        }

        @media (max-width: 1023px) {
            .nc-hero-photo { left: 20%; opacity: .84; }
            .nc-hero-photo::after { background: linear-gradient(90deg, #050b16 0%, rgba(5,11,22,.74) 48%, rgba(5,11,22,.28)), linear-gradient(0deg, rgba(5,11,22,.92) 0%, transparent 42%); }
            .nc-menu-trigger { display: grid !important; flex: 0 0 2.75rem; }
            .nc-feature-row { grid-template-columns: 1fr; gap: 2.25rem; }
            .nc-comparison-grid { grid-template-columns: 1fr; }
            .nc-flow-rail { min-height: 5rem; }
            .nc-flow-line { top: 0; bottom: 0; }
            .nc-flow-arrow svg { rotate: 90deg; }
        }
        @media (max-width: 767px) {
            .nc-shell { width: min(calc(100% - 2rem), 1240px); }
            .nc-water-bubbles { opacity: .38; }
            .nc-water-bubbles span:nth-child(n+8) { display: none; }
            .nc-problem-panel { padding: 2rem 1rem; }
            .nc-problem-title { font-size: clamp(2.15rem, 11vw, 3.1rem); }
            .nc-compare-card { padding: 1rem; }
            .nc-image-stage { min-height: 21rem; }
            .nc-chaos-note--b { display: none; }
            .nc-phone-frame { width: 7.5rem; }
            .nc-kpi-row { position: static; margin: .85rem; grid-template-columns: 1fr; }
            .nc-hero-photo { inset: 0; opacity: .68; }
            .nc-hero-photo img { object-position: 52% center; }
            .nc-hero-photo::after { background: linear-gradient(180deg, rgba(5,11,22,.5), rgba(5,11,22,.88) 76%); }
            .nc-section { padding-block: 4.75rem; }
            .nc-section-title { font-size: clamp(2.35rem, 11vw, 3.3rem); }
            .nc-mobile-menu { max-height: 0; opacity: 0; overflow: hidden; transition: max-height .35s ease, opacity .25s ease; }
            .nc-mobile-menu.is-open { max-height: 24rem; opacity: 1; }
            .nc-photo-mask { clip-path: none; }
            .nc-nav .nc-menu-trigger { position: fixed; top: 1rem; right: 1rem; z-index: 60; border: 1px solid rgba(255,255,255,.22); background: rgba(5,11,22,.72); color: white; }
            .nc-hero .nc-shell { width: 100%; max-width: 100vw; margin: 0; padding-inline: 1rem; }
            .nc-hero .nc-shell > * { min-width: 0; }
            .nc-hero h1 { max-width: 100%; font-size: 3.25rem; overflow-wrap: anywhere; }
            .nc-hero p { max-width: 100%; overflow-wrap: anywhere; }
            .nc-hero .nc-btn { width: 100%; }
        }
        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after { scroll-behavior: auto !important; animation-duration: .01ms !important; animation-iteration-count: 1 !important; transition-duration: .01ms !important; }
            .nc-water-bubbles { display: none; }
            .nc-chaos-note, .nc-phone-frame, .nc-whatsapp-toast, .nc-status-pill { animation: none !important; }
            .nc-flow-line, .nc-flow-arrow, .nc-success-check { opacity: 1 !important; transform: none !important; }
            [data-reveal] { opacity: 1 !important; transform: none !important; }
        }
    </style>
</head>
<body class="overflow-x-hidden antialiased selection:bg-blue-200 selection:text-slate-950">
    @php
        $plans = config('netocar.plans');
        $contactEmail = config('netocar.contact.email');
        $whatsappHref = config('netocar.contact.whatsapp_url');
        $whatsappPhone = preg_replace('/\D+/', '', config('netocar.contact.phone_e164'));
        $planWhatsappHref = fn (string $planLabel) => 'https://wa.me/'.$whatsappPhone.'?text='.rawurlencode('Bonjour NetoCar, je veux créer une agence et choisir le plan '.$planLabel.'.');
        $demoEmailHref = 'mailto:'.$contactEmail.'?subject=Demande%20de%20d%C3%A9mo%20NetoCar';
        $demoMode = (bool) config('netocar.demo.enabled');
        $featureStories = [
            ['number' => '01', 'eyebrow' => 'Pilotage', 'title' => 'Toute l’agence tient dans un seul regard.', 'copy' => 'Réservations, activité en cours, prestations terminées et revenus sont réunis dans une vue immédiatement exploitable. Le responsable voit les priorités sans construire lui-même son rapport.', 'image' => 'images/execution-netocar.png', 'alt' => 'Responsable consultant le tableau de bord NetoCar depuis son bureau', 'proof' => 'Décider plus vite, avec les bons indicateurs sous les yeux.', 'fit' => 'cover'],
            ['number' => '02', 'eyebrow' => 'Exécution', 'title' => 'Chaque véhicule avance avec un statut clair.', 'copy' => 'En attente, en cours ou terminé : la file de production traduit les réservations en travail concret. L’équipe sait quoi prendre en charge et le responsable conserve le rythme.', 'image' => 'images/pilotage-netocar.png', 'alt' => 'Centre de lavage organisé par statuts avec NetoCar', 'proof' => 'Moins de questions, moins d’attente, plus de débit.', 'fit' => 'contain'],
            ['number' => '03', 'eyebrow' => 'Données', 'title' => 'Importez sans perdre le contrôle de la qualité.', 'copy' => 'NetoCar vérifie les fichiers CSV avant validation, distingue les lignes conformes des erreurs et laisse l’administrateur corriger avant d’intégrer définitivement les données.', 'image' => 'images/import-netocar.png', 'alt' => 'Administrateur vérifiant un import de données dans NetoCar', 'proof' => 'Un aperçu clair avant chaque import définitif.', 'fit' => 'cover'],
        ];
        $faqs = [
            ['q' => 'NetoCar convient-il à un petit centre ?', 'a' => 'Oui. Le plan Basique couvre les besoins essentiels d’un point de lavage, puis les limites évoluent avec votre volume et votre équipe.'],
            ['q' => 'Puis-je gérer plusieurs branches ?', 'a' => 'Oui. Chaque site garde ses employés, services, réservations, tickets, capacité et horaires, tout en restant piloté depuis la même agence.'],
            ['q' => 'Mon équipe doit-elle être très technique ?', 'a' => 'Non. L’administrateur d’agence pilote tout depuis son espace et peut créer des comptes employés limités au travail qui leur est assigné.'],
            ['q' => 'Puis-je récupérer mes données existantes ?', 'a' => 'Oui. NetoCar inclut un centre d’import CSV pour les clients, employés et services, avec aperçu et validation avant l’import.'],
            ['q' => 'Comment se déroule la mise en place ?', 'a' => 'Nous commençons par votre organisation, vos branches et vos services. Une démo ciblée permet ensuite de choisir le plan et préparer vos données.'],
        ];
    @endphp

    <div class="nc-page min-h-screen">
        <div class="nc-water-bubbles" aria-hidden="true">
            <span style="--x:6%; --s:1.1rem; --d:22s; --delay:-5s; --drift:1.5rem"></span>
            <span style="--x:14%; --s:.7rem; --d:18s; --delay:-11s; --drift:-1rem"></span>
            <span style="--x:24%; --s:1.5rem; --d:26s; --delay:-2s; --drift:2.3rem"></span>
            <span style="--x:36%; --s:.85rem; --d:20s; --delay:-14s; --drift:-1.6rem"></span>
            <span style="--x:48%; --s:1.25rem; --d:24s; --delay:-8s; --drift:1.8rem"></span>
            <span style="--x:58%; --s:.65rem; --d:17s; --delay:-4s; --drift:-1.2rem"></span>
            <span style="--x:68%; --s:1.7rem; --d:29s; --delay:-16s; --drift:2.6rem"></span>
            <span style="--x:77%; --s:.95rem; --d:21s; --delay:-7s; --drift:-1.5rem"></span>
            <span style="--x:86%; --s:1.35rem; --d:25s; --delay:-12s; --drift:1.7rem"></span>
            <span style="--x:94%; --s:.75rem; --d:19s; --delay:-3s; --drift:-1rem"></span>
        </div>
        <header class="nc-nav fixed inset-x-0 top-0 z-50 border-b border-transparent" data-nav>
            <div class="nc-shell flex h-[76px] items-center justify-between gap-5">
                <a href="#top" class="flex items-center gap-3 text-white" aria-label="NetoCar, accueil">
                    <span class="nc-logo-mark grid h-10 w-10 place-items-center rounded-xl bg-[#2764ff]">
                        <svg viewBox="0 0 24 24" class="h-6 w-6" fill="none" aria-hidden="true">
                            <path d="M12 2.8c3.4 4.2 6.2 7.4 6.2 11A6.2 6.2 0 1 1 5.8 13.8c0-3.6 2.8-6.8 6.2-11Z" fill="currentColor"/>
                            <path d="M8.5 14.2c.7 1.8 2 2.7 4 2.8" stroke="#050b16" stroke-width="1.7" stroke-linecap="round"/>
                        </svg>
                    </span>
                    <span class="nc-display text-xl font-bold tracking-[-.04em]">NetoCar</span>
                </a>

                <nav class="hidden items-center gap-7 text-sm font-bold text-slate-300 lg:flex" aria-label="Navigation principale">
                    <a href="#solution" class="transition hover:text-white">Solution</a>
                    <a href="#features" class="transition hover:text-white">Fonctionnalités</a>
                    <a href="#pricing" class="transition hover:text-white">Tarifs</a>
                    <a href="#faq" class="transition hover:text-white">FAQ</a>
                </nav>

                <div class="hidden items-center gap-2 sm:flex">
                    @auth
                        <a href="{{ route('dashboard') }}" class="nc-btn nc-btn-primary min-h-0 py-2.5">Tableau de bord</a>
                    @else
                        <a href="{{ route('login') }}" class="px-4 py-2 text-sm font-bold text-slate-300 transition hover:text-white">Connexion</a>
                        <a href="{{ $demoMode ? route('login') : $whatsappHref }}" @unless($demoMode) target="_blank" rel="noopener" @endunless class="nc-btn nc-btn-primary min-h-0 py-2.5">{{ $demoMode ? 'Voir la demo' : 'Demander une demo' }}</a>
                    @endauth
                </div>

                <button type="button" class="nc-menu-trigger h-11 w-11 place-items-center rounded-xl border border-white/15 text-white" data-menu-button aria-label="Ouvrir le menu" aria-expanded="false">
                    <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M4 7h16M4 12h16M4 17h16"/></svg>
                </button>
            </div>
            <div class="nc-mobile-menu border-t border-white/10 bg-[#050b16]/95 lg:hidden" data-mobile-menu>
                <nav class="nc-shell grid gap-1 py-4 text-sm font-bold text-slate-200">
                    <a href="#solution" class="rounded-xl px-4 py-3 hover:bg-white/5">Solution</a>
                    <a href="#features" class="rounded-xl px-4 py-3 hover:bg-white/5">Fonctionnalités</a>
                    <a href="#pricing" class="rounded-xl px-4 py-3 hover:bg-white/5">Tarifs</a>
                    <a href="#faq" class="rounded-xl px-4 py-3 hover:bg-white/5">FAQ</a>
                    @guest <a href="{{ route('login') }}" class="mt-2 rounded-xl bg-white px-4 py-3 text-center text-slate-950">{{ $demoMode ? 'Voir la demo' : 'Connexion' }}</a> @endguest
                </nav>
            </div>
        </header>

        <main>
            <section id="top" class="nc-hero nc-noise nc-grid relative flex items-center pt-28">
                <div class="nc-hero-photo" data-parallax=".035">
                    <img src="{{ asset('images/netocar-hero.webp') }}" alt="Cliente devant son véhicule dans un centre de lavage moderne" width="1920" height="1280" fetchpriority="high">
                </div>
                <div class="nc-orb nc-orb-a"></div><div class="nc-orb nc-orb-b"></div>
                <div class="nc-shell relative z-10 grid min-h-[calc(100svh-7rem)] items-center gap-12 py-12 lg:grid-cols-[1.02fr_.98fr] lg:py-20">
                    <div class="max-w-3xl">
                        <div class="nc-pill inline-flex items-center gap-2 rounded-full px-4 py-2 text-xs font-bold text-blue-100" data-reveal>
                            <span class="h-2 w-2 rounded-full bg-[#42d7ff] shadow-[0_0_14px_#42d7ff]"></span>
                            Le cockpit des centres de lavage modernes
                        </div>
                        <h1 class="nc-display mt-7 text-[clamp(3.6rem,7.6vw,7.3rem)] font-bold leading-[.88] text-white" data-reveal style="--delay:80ms">
                            Votre centre.<br><span class="text-[#69dfff]">Sous contrôle.</span>
                        </h1>
                        <p class="mt-7 max-w-xl text-base font-medium leading-8 text-slate-300 sm:text-lg" data-reveal style="--delay:160ms">
                            NetoCar réunit réservations, équipe, prestations et revenus dans un seul espace — pour servir plus vite, oublier moins et piloter avec une vision nette.
                        </p>
                        <div class="mt-9 flex flex-col gap-3 sm:flex-row" data-reveal style="--delay:240ms">
                            @auth
                                <a href="{{ route('dashboard') }}" class="nc-btn nc-btn-primary">Ouvrir mon espace <span aria-hidden="true">→</span></a>
                            @else
                                <a href="{{ $demoMode ? route('login') : $whatsappHref }}" @unless($demoMode) target="_blank" rel="noopener" @endunless class="nc-btn nc-btn-primary">{{ $demoMode ? 'Tester la demo' : 'Voir NetoCar en action' }} <span aria-hidden="true">→</span></a>
                                <a href="#solution" class="nc-btn nc-btn-ghost"><span class="grid h-7 w-7 place-items-center rounded-full bg-white/10">▶</span> Découvrir la solution</a>
                            @endauth
                        </div>
                        <div class="mt-9 flex flex-wrap items-center gap-x-7 gap-y-3 text-xs font-bold text-slate-400" data-reveal style="--delay:300ms">
                            <span class="flex items-center gap-2"><b class="text-[#8ff7d4]">✓</b> Sans carte bancaire</span>
                            <span class="flex items-center gap-2"><b class="text-[#8ff7d4]">✓</b> Mise en place guidée</span>
                            <span class="flex items-center gap-2"><b class="text-[#8ff7d4]">✓</b> Données isolées</span>
                        </div>
                    </div>

                    <div class="relative hidden h-[34rem] items-center justify-center lg:flex" data-reveal="right" style="--delay:220ms">
                        <div class="nc-hero-signal">
                            <span class="nc-hero-orbit"></span>
                            <svg viewBox="0 0 160 200" class="relative z-10 h-40 w-32 text-white/85 drop-shadow-[0_20px_45px_rgba(39,100,255,.45)]" fill="none" aria-hidden="true">
                                <path d="M80 7C123 61 151 96 151 133a71 71 0 1 1-142 0C9 96 37 61 80 7Z" stroke="currentColor" stroke-width="2"/>
                                <path d="M40 136c5 20 20 32 42 34" stroke="#69dfff" stroke-width="4" stroke-linecap="round"/>
                            </svg>
                            <span class="nc-display absolute text-[7.5rem] font-bold text-white/[.035]">N</span>
                        </div>
                        <div class="absolute bottom-8 right-0 max-w-xs border-l border-[#69dfff]/50 pl-5 text-white">
                            <p class="text-[10px] font-extrabold uppercase tracking-[.22em] text-[#69dfff]">L’expérience NetoCar</p>
                            <p class="nc-display mt-3 text-2xl font-bold leading-tight">Une organisation invisible.<br>Un service qui se ressent.</p>
                            <p class="mt-3 text-sm leading-6 text-slate-400">Organiser · Exécuter · Fidéliser</p>
                        </div>
                    </div>
                </div>
                <a href="#solution" class="absolute bottom-7 left-1/2 z-20 hidden -translate-x-1/2 text-[10px] font-extrabold uppercase tracking-[.24em] text-slate-500 lg:flex lg:flex-col lg:items-center lg:gap-3">Explorer <span class="h-10 w-px bg-gradient-to-b from-slate-500 to-transparent"></span></a>
            </section>

            <section class="nc-surface-section overflow-hidden border-y border-slate-200/80 py-5">
                <div class="nc-marquee gap-12 pr-12 text-sm font-extrabold uppercase tracking-[.18em] text-slate-400">
                    @for ($i = 0; $i < 2; $i++)
                        <span>Réservations sans friction</span><span class="text-[#2764ff]">✦</span><span>Tickets en temps réel</span><span class="text-[#2764ff]">✦</span><span>Équipe alignée</span><span class="text-[#2764ff]">✦</span><span>Branches synchronisées</span><span class="text-[#2764ff]">✦</span><span>Revenus visibles</span><span class="text-[#2764ff]">✦</span>
                    @endfor
                </div>
            </section>

            <section id="solution" class="nc-section nc-problem-section">
                <div class="nc-problem-shell">
                    <div class="nc-problem-panel" data-premium-problem>
                        <div class="nc-problem-intro">
                            <span class="nc-problem-eyebrow">Avant / Après NetoCar</span>
                            <h2 class="nc-problem-title nc-display">Le problème n’est pas le lavage.<br>C’est tout ce qui se passe autour.</h2>
                            <p class="nc-problem-copy">Quand les informations vivent dans les appels, les messages et la mémoire de l’équipe, la journée devient difficile à piloter. NetoCar transforme ce bruit opérationnel en flux clair, partagé et mesurable.</p>
                        </div>

                        <div class="nc-comparison-grid">
                            <article class="nc-compare-card nc-compare-card--before" data-problem-card="before">
                                <div class="nc-compare-head">
                                    <h3 class="nc-compare-title">Avant NetoCar</h3>
                                    <span class="nc-compare-chip nc-compare-chip--muted">Fragmenté</span>
                                </div>

                                <div class="nc-image-stage nc-image-stage--before">
                                    <img src="{{ asset('images/avant.png') }}" alt="Organisation dispersée avant NetoCar" loading="lazy">
                                    <span class="nc-missed-badge">3 appels manqués</span>
                                    <div class="nc-whatsapp-toast"><span class="text-[#22c55e]">WhatsApp</span><br>“Je suis arrivé, c’est prêt ?”</div>
                                    <div class="nc-chaos-note nc-chaos-note--a">Client rappelé deux fois</div>
                                    <div class="nc-chaos-note nc-chaos-note--b">Ticket pas clôturé</div>
                                    <div class="nc-phone-frame" aria-hidden="true">
                                        <div class="nc-phone-screen">
                                            <div class="mb-2 h-1.5 w-10 rounded-full bg-slate-300"></div>
                                            <p class="text-[10px] font-black uppercase tracking-[.12em] text-slate-400">Appel entrant</p>
                                            <p class="mt-2 text-sm font-black">Créneau 11h ?</p>
                                        </div>
                                    </div>
                                </div>

                                <ul class="nc-problem-list">
                                    <li><span class="nc-list-icon nc-list-icon--bad"><svg viewBox="0 0 20 20" class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2.4"><path d="M5 5l10 10M15 5 5 15" stroke-linecap="round"/></svg></span>Appels oubliés</li>
                                    <li><span class="nc-list-icon nc-list-icon--bad"><svg viewBox="0 0 20 20" class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2.4"><path d="M5 5l10 10M15 5 5 15" stroke-linecap="round"/></svg></span>Employés mal affectés</li>
                                    <li><span class="nc-list-icon nc-list-icon--bad"><svg viewBox="0 0 20 20" class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2.4"><path d="M5 5l10 10M15 5 5 15" stroke-linecap="round"/></svg></span>Clients qui attendent</li>
                                    <li><span class="nc-list-icon nc-list-icon--bad"><svg viewBox="0 0 20 20" class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2.4"><path d="M5 5l10 10M15 5 5 15" stroke-linecap="round"/></svg></span>Tickets jamais clôturés</li>
                                </ul>
                            </article>

                            <div class="nc-flow-rail" aria-hidden="true">
                                <span class="nc-flow-line" data-flow-line></span>
                                <span class="nc-flow-arrow" data-flow-arrow>
                                    <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m13 6 6 6-6 6"/></svg>
                                </span>
                            </div>

                            <article class="nc-compare-card nc-compare-card--after" data-problem-card="after">
                                <div class="nc-compare-head">
                                    <h3 class="nc-compare-title">Avec NetoCar</h3>
                                    <span class="nc-compare-chip nc-compare-chip--good">Synchronisé</span>
                                </div>

                                <div class="nc-image-stage nc-image-stage--after">
                                    <div class="nc-success-check" data-success-check aria-hidden="true">
                                        <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2.7" stroke-linecap="round" stroke-linejoin="round"><path d="m5 12 4 4L19 6"/></svg>
                                    </div>
                                    <div class="nc-status-stack" aria-hidden="true">
                                        <span class="nc-status-pill" data-status-pill>En attente</span>
                                        <span class="nc-status-pill" data-status-pill>En cours</span>
                                        <span class="nc-status-pill" data-status-pill>Terminé</span>
                                    </div>
                                    <img src="{{ asset('images/apres.png') }}" alt="Organisation centralisée avec NetoCar" loading="lazy">
                                    <div class="nc-kpi-row" aria-label="Indicateurs NetoCar">
                                        <div class="nc-kpi-card"><span class="nc-kpi-value"><span data-kpi-count data-target="27">0</span></span><span class="nc-kpi-label">tickets suivis</span></div>
                                        <div class="nc-kpi-card"><span class="nc-kpi-value"><span data-kpi-count data-target="13">0</span></span><span class="nc-kpi-label">terminés</span></div>
                                        <div class="nc-kpi-card"><span class="nc-kpi-value"><span data-kpi-count data-target="98">0</span>%</span><span class="nc-kpi-label">infos à jour</span></div>
                                    </div>
                                </div>

                                <ul class="nc-problem-list">
                                    <li><span class="nc-list-icon nc-list-icon--good"><svg viewBox="0 0 20 20" class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2.4"><path d="m4 10 4 4 8-8" stroke-linecap="round" stroke-linejoin="round"/></svg></span>Toutes les réservations centralisées</li>
                                    <li><span class="nc-list-icon nc-list-icon--good"><svg viewBox="0 0 20 20" class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2.4"><path d="m4 10 4 4 8-8" stroke-linecap="round" stroke-linejoin="round"/></svg></span>Planning partagé</li>
                                    <li><span class="nc-list-icon nc-list-icon--good"><svg viewBox="0 0 20 20" class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2.4"><path d="m4 10 4 4 8-8" stroke-linecap="round" stroke-linejoin="round"/></svg></span>Statuts en temps réel</li>
                                    <li><span class="nc-list-icon nc-list-icon--good"><svg viewBox="0 0 20 20" class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2.4"><path d="m4 10 4 4 8-8" stroke-linecap="round" stroke-linejoin="round"/></svg></span>Toute l’équipe travaille sur les mêmes informations</li>
                                </ul>
                            </article>
                        </div>
                    </div>
                </div>
            </section>

            <section class="nc-section nc-product-glow nc-tint-section relative overflow-hidden">
                <div class="nc-shell relative z-10">
                    <div class="grid gap-8 lg:grid-cols-[.9fr_1.1fr] lg:items-end" data-reveal>
                        <div><span class="nc-kicker">Une vision instantanée</span><h2 class="nc-section-title nc-display mt-6 font-bold">Les chiffres utiles.<br><span class="text-[#2764ff]">Pas le bruit.</span></h2></div>
                        <p class="nc-section-copy max-w-xl justify-self-end">La journée n’attend pas un rapport de fin de semaine. NetoCar transforme l’activité du terrain en indicateurs clairs, lisibles sur ordinateur comme sur téléphone.</p>
                    </div>

                    <div class="mt-14 grid gap-5 lg:grid-cols-12" data-reveal style="--delay:120ms">
                        <article class="relative overflow-hidden rounded-[1.75rem] bg-[#071326] p-6 text-white shadow-[0_35px_90px_rgba(15,23,42,.22)] sm:p-8 lg:col-span-7 lg:row-span-2">
                            <div class="absolute -right-24 -top-24 h-72 w-72 rounded-full bg-blue-500/20 blur-3xl"></div>
                            <div class="relative flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                                <div><p class="text-[10px] font-extrabold uppercase tracking-[.2em] text-[#69dfff]">Revenus des tickets terminés</p><div class="mt-3 flex items-end gap-3"><strong class="nc-display text-4xl font-bold sm:text-5xl">8 450</strong><span class="mb-1 text-sm font-bold text-slate-400">MAD</span></div><p class="mt-2 text-xs text-slate-400">Exemple de données · période sélectionnée</p></div>
                                <span class="self-start rounded-full border border-white/10 bg-white/5 px-3 py-1.5 text-xs font-extrabold text-slate-300">Jour · Mois · Année</span>
                            </div>

                            <div class="relative mt-10 h-56 sm:h-64">
                                <div class="absolute inset-0 grid grid-rows-4"><i class="border-t border-white/[.07]"></i><i class="border-t border-white/[.07]"></i><i class="border-t border-white/[.07]"></i><i class="border-y border-white/[.07]"></i></div>
                                <svg viewBox="0 0 640 230" class="absolute inset-0 h-full w-full" preserveAspectRatio="none" aria-label="Évolution des revenus de la journée">
                                    <defs><linearGradient id="ncArea" x1="0" x2="0" y1="0" y2="1"><stop offset="0" stop-color="#42d7ff" stop-opacity=".34"/><stop offset="1" stop-color="#42d7ff" stop-opacity="0"/></linearGradient></defs>
                                    <path d="M0 203 C55 190 65 178 115 181 S185 155 226 160 S292 112 338 127 S405 80 454 91 S527 47 566 61 S610 31 640 24 L640 230 L0 230 Z" fill="url(#ncArea)"/>
                                    <path class="nc-chart-line" d="M0 203 C55 190 65 178 115 181 S185 155 226 160 S292 112 338 127 S405 80 454 91 S527 47 566 61 S610 31 640 24" fill="none" stroke="#69dfff" stroke-width="4" stroke-linecap="round"/>
                                </svg>
                                <div class="absolute inset-x-0 bottom-0 flex justify-between text-[10px] font-bold text-slate-500"><span>08h</span><span>10h</span><span>12h</span><span>14h</span><span>16h</span><span>18h</span></div>
                            </div>

                            <div class="relative mt-6 grid grid-cols-2 gap-px overflow-hidden rounded-2xl border border-white/10 bg-white/10 sm:grid-cols-4">
                                <div class="bg-[#0c1930] p-4"><small class="text-[10px] font-bold uppercase tracking-wider text-slate-500">Tickets terminés</small><b class="mt-2 block text-lg">13</b></div>
                                <div class="bg-[#0c1930] p-4"><small class="text-[10px] font-bold uppercase tracking-wider text-slate-500">Réservations confirmées</small><b class="mt-2 block text-lg">05</b></div>
                                <div class="bg-[#0c1930] p-4"><small class="text-[10px] font-bold uppercase tracking-wider text-slate-500">Clients récurrents</small><b class="mt-2 block text-lg">38%</b></div>
                                <div class="bg-[#0c1930] p-4"><small class="text-[10px] font-bold uppercase tracking-wider text-slate-500">Taux d’annulation</small><b class="mt-2 block text-lg">7,4%</b></div>
                            </div>
                        </article>

                        <div class="grid grid-cols-2 gap-4 lg:col-span-5">
                            <article class="nc-card nc-lift rounded-3xl p-5 sm:p-6"><div class="flex items-center justify-between"><span class="grid h-10 w-10 place-items-center rounded-xl bg-blue-100 text-[#2764ff]">◇</span><small class="text-xs font-extrabold text-slate-400">activité</small></div><p class="nc-display mt-7 text-3xl font-bold sm:text-4xl">Samedi</p><p class="mt-1 text-xs font-bold text-slate-500">Jour le plus chargé</p></article>
                            <article class="nc-card nc-lift rounded-3xl p-5 sm:p-6"><div class="flex items-center justify-between"><span class="grid h-10 w-10 place-items-center rounded-xl bg-cyan-100 text-cyan-700">◷</span><small class="text-xs font-extrabold text-slate-400">moyenne</small></div><p class="nc-display mt-7 text-4xl font-bold">42 <i class="text-lg not-italic text-slate-400">min</i></p><p class="mt-1 text-xs font-bold text-slate-500">Temps par prestation</p></article>
                        </div>

                        <article class="nc-card rounded-3xl p-6 lg:col-span-5">
                            <div class="flex items-center justify-between gap-4"><div><p class="text-[10px] font-extrabold uppercase tracking-[.18em] text-[#2764ff]">Répartition des tickets</p><h3 class="nc-display mt-2 text-2xl font-bold">La file, en un regard.</h3></div><div class="grid h-24 w-24 shrink-0 place-items-center rounded-full" style="background:conic-gradient(#2764ff 0 48%,#42d7ff 48% 72%,#8ff7d4 72% 91%,#e2e8f0 91%)"><span class="grid h-16 w-16 place-items-center rounded-full bg-white text-sm font-extrabold">27</span></div></div>
                            <div class="mt-6 grid grid-cols-2 gap-3 text-xs font-bold text-slate-500"><span class="flex items-center gap-2"><i class="h-2.5 w-2.5 rounded-full bg-[#2764ff]"></i>13 terminés</span><span class="flex items-center gap-2"><i class="h-2.5 w-2.5 rounded-full bg-[#42d7ff]"></i>7 en cours</span><span class="flex items-center gap-2"><i class="h-2.5 w-2.5 rounded-full bg-[#8ff7d4]"></i>5 confirmés</span><span class="flex items-center gap-2"><i class="h-2.5 w-2.5 rounded-full bg-slate-200"></i>2 annulés</span></div>
                        </article>

                        <article class="nc-card rounded-3xl p-6 lg:col-span-12">
                            <div class="flex flex-col gap-5 sm:flex-row sm:items-center sm:justify-between"><div><p class="text-[10px] font-extrabold uppercase tracking-[.18em] text-[#2764ff]">Performance par branche</p><h3 class="nc-display mt-2 text-2xl font-bold">Comparez l’activité de chaque site.</h3></div><span class="text-xs font-bold text-slate-500">Revenus issus des tickets terminés</span></div>
                            <div class="mt-7 grid gap-5 md:grid-cols-3">
                                @foreach ([['Centre-ville', 100, '5 200 MAD', '13 tickets'], ['Maarif', 64, '3 350 MAD', '8 tickets'], ['Aïn Sebaâ', 39, '2 050 MAD', '6 tickets']] as [$branch, $performance, $revenue, $tickets])
                                    <div><div class="flex justify-between gap-3 text-xs font-extrabold"><span>{{ $branch }}</span><span class="text-slate-500">{{ $revenue }}</span></div><div class="mt-3 h-2 overflow-hidden rounded-full bg-slate-100"><span class="block h-full rounded-full bg-[linear-gradient(90deg,#2764ff,#42d7ff)]" style="width:{{ $performance }}%"></span></div><p class="mt-2 text-right text-[10px] font-bold text-slate-400">{{ $tickets }}</p></div>
                                @endforeach
                            </div>
                        </article>
                    </div>
                </div>
            </section>

            <section id="features" class="nc-section nc-feature-story relative overflow-clip">
                <div class="nc-shell">
                    <div class="grid gap-8 lg:grid-cols-[.9fr_1.1fr] lg:items-end" data-reveal>
                        <div>
                            <span class="nc-kicker">Pensé pour le terrain</span>
                            <h2 class="nc-section-title nc-display mt-6 font-bold text-slate-950">Puissant pour l’administrateur.<br><span class="text-[#14b8a6]">Évident pour l’équipe.</span></h2>
                        </div>
                        <p class="nc-section-copy max-w-xl justify-self-end">NetoCar ne vous demande pas de travailler comme un logiciel. Il traduit la réalité de l’agence en décisions simples, du premier regard jusqu’au dernier ticket de la journée.</p>
                    </div>

                    <div class="mt-12 lg:mt-16">
                        @foreach ($featureStories as $feature)
                            @php($imageOnRight = $loop->even)
                            <article class="nc-feature-row">
                                <figure class="nc-feature-visual {{ $imageOnRight ? 'lg:order-2' : 'lg:order-1' }}" data-fit="{{ $feature['fit'] }}" data-reveal="{{ $imageOnRight ? 'right' : 'left' }}">
                                    <img src="{{ asset($feature['image']) }}" alt="{{ $feature['alt'] }}" loading="lazy">
                                </figure>

                                <div class="{{ $imageOnRight ? 'lg:order-1' : 'lg:order-2' }}" data-reveal="{{ $imageOnRight ? 'left' : 'right' }}" style="--delay:100ms">
                                    <div class="flex items-center gap-4">
                                        <span class="nc-feature-index nc-display text-5xl font-bold">{{ $feature['number'] }}</span>
                                        <span class="text-[10px] font-extrabold uppercase tracking-[.2em] text-[#2764ff]">{{ $feature['eyebrow'] }}</span>
                                    </div>
                                    <h3 class="nc-display mt-6 max-w-xl text-3xl font-bold leading-[1.04] text-slate-950 sm:text-5xl">{{ $feature['title'] }}</h3>
                                    <p class="mt-6 max-w-xl text-sm leading-7 text-slate-600 sm:text-base sm:leading-8">{{ $feature['copy'] }}</p>
                                    <p class="nc-feature-proof max-w-xl text-sm font-extrabold"><span class="h-2.5 w-2.5 shrink-0 rounded-full bg-[#2764ff] shadow-[0_0_14px_rgba(39,100,255,.45)]"></span>{{ $feature['proof'] }}</p>
                                </div>
                            </article>
                        @endforeach
                    </div>
                </div>
            </section>

            <section class="nc-section nc-tint-section overflow-hidden">
                <div class="nc-shell grid gap-10 lg:grid-cols-[.9fr_1.1fr] lg:items-center">
                    <div data-reveal="left">
                        <span class="nc-kicker">Un outil qui grandit avec vous</span>
                        <h2 class="nc-section-title nc-display mt-6 font-bold text-slate-950">Petit aujourd’hui.<br><span class="nc-title-accent">Structuré pour demain.</span></h2>
                        <p class="nc-section-copy mt-6 max-w-xl">Commencez avec les fondamentaux. Ajoutez des employés, des services et des branches quand le volume augmente — sans reconstruire votre organisation.</p>
                        <div class="mt-9 grid grid-cols-1 gap-3 sm:grid-cols-3">
                            <div class="nc-card rounded-2xl border-teal-100 bg-white p-5">
                                <b class="nc-display block text-3xl font-bold" style="color:#0f172a !important;">1</b>
                                <small class="mt-2 block text-sm font-bold" style="color:#334155 !important;">administrateur</small>
                            </div>
                            <div class="nc-card rounded-2xl border-teal-100 bg-white p-5">
                                <b class="nc-display block text-3xl font-bold" style="color:#14b8a6 !important;">24/7</b>
                                <small class="mt-2 block text-sm font-bold" style="color:#334155 !important;">vision cloud</small>
                            </div>
                            <div class="nc-card rounded-2xl border-teal-100 bg-white p-5">
                                <b class="nc-display block text-3xl font-bold" style="color:#0f172a !important;">100%</b>
                                <small class="mt-2 block text-sm font-bold" style="color:#334155 !important;">isolé par agence</small>
                            </div>
                        </div>
                    </div>
                    <div class="relative" data-reveal="right">
                        <div class="overflow-hidden rounded-[2rem] shadow-[0_35px_90px_rgba(15,23,42,.2)]">
                            <img src="{{ asset('images/blueliquid.jpg') }}" alt="Centre de lavage professionnel" class="h-[34rem] w-full object-cover object-center" loading="lazy">
                        </div>
                        <div class="nc-float absolute -bottom-6 -left-5 max-w-xs rounded-2xl border border-teal-100 bg-white/95 p-6 text-slate-950 shadow-[0_28px_70px_rgba(15,23,42,.18)] backdrop-blur">
                            <p class="text-xs font-extrabold uppercase tracking-[.18em] text-[#0f766e]">Votre prochain palier</p>
                            <p class="nc-display mt-3 text-2xl font-bold leading-tight text-[#0f172a]">Plus de volume.<br><span class="text-[#14b8a6]">Pas plus de chaos.</span></p>
                        </div>
                    </div>
                </div>
            </section>

            <section id="pricing" class="nc-section nc-surface-section">
                <div class="nc-shell">
                    <div class="text-center" data-reveal><span class="nc-kicker">Des tarifs lisibles</span><h2 class="nc-section-title nc-display mx-auto mt-6 font-bold">Choisissez votre rythme.</h2><p class="nc-section-copy mx-auto mt-5 max-w-2xl">Un abonnement annuel simple, sans coût caché par réservation. Passez au niveau supérieur quand votre activité le demande.</p></div>
                    <div class="mt-14 grid gap-5 lg:grid-cols-3">
                        @foreach ($plans as $key => $plan)
                            @php($featured = $key === 'standard')
                            <article class="nc-lift relative flex flex-col rounded-3xl border p-7 {{ $featured ? 'border-[#2764ff] bg-[#071326] text-white shadow-[0_35px_90px_rgba(39,100,255,.2)]' : 'border-slate-200 bg-white text-slate-950 shadow-[0_20px_60px_rgba(15,23,42,.07)]' }}" data-reveal style="--delay:{{ $loop->index * 80 }}ms">
                                @if ($featured)<span class="absolute right-5 top-5 rounded-full bg-[#2764ff] px-3 py-1 text-[10px] font-extrabold uppercase tracking-[.16em] text-white">Le plus choisi</span>@endif
                                <p class="text-sm font-extrabold {{ $featured ? 'text-[#69dfff]' : 'text-[#2764ff]' }}">{{ $plan['label'] }}</p>
                                <div class="mt-7 flex items-end gap-2"><span class="nc-display text-5xl font-bold">{{ number_format($plan['price_yearly_mad'], 0, ',', ' ') }}</span><span class="mb-1 text-sm {{ $featured ? 'text-slate-400' : 'text-slate-500' }}">MAD / an</span></div>
                                <p class="mt-5 min-h-14 text-sm leading-7 {{ $featured ? 'text-slate-400' : 'text-slate-600' }}">{{ $plan['tagline'] }}</p>
                                <div class="my-7 h-px {{ $featured ? 'bg-white/10' : 'bg-slate-200' }}"></div>
                                <ul class="grid gap-3 text-sm {{ $featured ? 'text-slate-300' : 'text-slate-600' }}">
                                    @foreach (array_slice($plan['features'], 0, 3) as $feature)<li class="nc-pricing-feature flex gap-3">{{ $feature }}</li>@endforeach
                                </ul>
                                <a href="{{ $planWhatsappHref($plan['label']) }}" target="_blank" rel="noopener" class="nc-btn mt-8 {{ $featured ? 'nc-btn-primary' : 'border border-slate-300 bg-slate-50 text-slate-950 hover:border-blue-300' }}">Choisir {{ $plan['label'] }}</a>
                            </article>
                        @endforeach
                    </div>
                    <p class="mt-6 text-center text-xs font-semibold text-slate-500">Les limites exactes dépendent du plan. Contactez-nous pour valider le meilleur niveau selon votre volume.</p>
                </div>
            </section>

            <section id="faq" class="nc-section nc-tint-section">
                <div class="nc-shell grid gap-12 lg:grid-cols-[.72fr_1.28fr]">
                    <div data-reveal="left"><span class="nc-kicker">Questions fréquentes</span><h2 class="nc-section-title nc-display mt-6 font-bold">Tout ce qu’il faut savoir avant de démarrer.</h2><p class="nc-section-copy mt-6">Une question plus précise sur vos branches ou vos données ? Écrivez-nous, nous répondrons avec un cas concret.</p><a href="{{ $whatsappHref }}" target="_blank" rel="noopener" class="mt-7 inline-flex items-center gap-3 font-extrabold text-[#2764ff]">Parler à l’équipe <span>→</span></a></div>
                    <div class="divide-y divide-slate-200 border-y border-slate-200" data-reveal="right">
                        @foreach ($faqs as $faq)
                            <details class="nc-faq group py-1"><summary class="flex cursor-pointer items-center justify-between gap-5 py-6"><span class="text-base font-extrabold text-slate-950 sm:text-lg">{{ $faq['q'] }}</span><span class="nc-plus grid h-9 w-9 shrink-0 place-items-center rounded-full border border-slate-300 text-xl">+</span></summary><p class="max-w-2xl pb-6 pr-14 text-sm leading-7 text-slate-600">{{ $faq['a'] }}</p></details>
                        @endforeach
                    </div>
                </div>
            </section>

          
        </main>

        <footer class="bg-[#050b16] text-slate-400">
            <div class="nc-shell grid gap-10 border-t border-white/10 py-14 md:grid-cols-2 lg:grid-cols-[1.3fr_.7fr_.7fr_.8fr]">
                <div><a href="#top" class="flex items-center gap-3 text-white"><span class="nc-logo-mark grid h-10 w-10 place-items-center rounded-xl bg-[#2764ff]"><svg viewBox="0 0 24 24" class="h-6 w-6" fill="currentColor"><path d="M12 2.8c3.4 4.2 6.2 7.4 6.2 11A6.2 6.2 0 1 1 5.8 13.8c0-3.6 2.8-6.8 6.2-11Z"/></svg></span><span class="nc-display text-xl font-bold">NetoCar</span></a><p class="mt-5 max-w-sm text-sm leading-7">Le système d’exploitation conçu pour les centres de lavage qui veulent gagner en clarté, en vitesse et en maîtrise.</p></div>
                <div><p class="text-sm font-extrabold text-white">Produit</p><div class="mt-5 grid gap-3 text-sm"><a href="#solution" class="hover:text-white">Solution</a><a href="#features" class="hover:text-white">Fonctionnalités</a><a href="#pricing" class="hover:text-white">Tarifs</a></div></div>
                <div><p class="text-sm font-extrabold text-white">Accès</p><div class="mt-5 grid gap-3 text-sm"><a href="{{ route('login') }}" class="hover:text-white">Connexion</a><a href="{{ $whatsappHref }}" target="_blank" rel="noopener" class="hover:text-white">Créer une agence</a><a href="#faq" class="hover:text-white">FAQ</a></div></div>
                <div><p class="text-sm font-extrabold text-white">Nous contacter</p><div class="mt-5 grid gap-3 text-sm"><a href="{{ $demoEmailHref }}" class="hover:text-white">{{ $contactEmail }}</a><a href="{{ $whatsappHref }}" target="_blank" rel="noopener" class="hover:text-white">WhatsApp</a><span>Casablanca, Maroc</span></div></div>
            </div>
            <div class="border-t border-white/10"><div class="nc-shell flex flex-col gap-3 py-5 text-xs sm:flex-row sm:items-center sm:justify-between"><p>© {{ date('Y') }} NetoCar. Tous droits réservés.</p><p>Conçu au Maroc pour les professionnels du lavage automobile.</p></div></div>
        </footer>

        <a href="{{ $whatsappHref }}" target="_blank" rel="noopener" class="fixed bottom-5 right-5 z-40 grid h-14 w-14 place-items-center rounded-full bg-[#23c763] text-white shadow-[0_18px_45px_rgba(35,199,99,.38)] transition hover:-translate-y-1" aria-label="Contacter NetoCar sur WhatsApp">
            <svg viewBox="0 0 24 24" class="h-7 w-7" fill="currentColor" aria-hidden="true"><path d="M12 2a9.8 9.8 0 0 0-8.5 14.7L2.2 22l5.5-1.2A10 10 0 1 0 12 2Zm0 18.2a8.2 8.2 0 0 1-4.1-1.1l-.3-.2-3.2.7.8-3.1-.2-.3a8.1 8.1 0 1 1 7 4Zm4.5-6.1c-.2-.1-1.4-.7-1.7-.8-.2-.1-.4-.1-.6.1l-.7.9c-.1.2-.3.2-.5.1-1.5-.7-2.6-1.7-3.3-3.1-.2-.3.2-.5.6-1 .1-.1.1-.3 0-.5L9.6 8c-.1-.3-.3-.3-.5-.3h-.4c-.2 0-.5.1-.7.3-.8.8-1.2 1.8-1.1 2.9.1 1.2.8 2.7 2.3 4.1 1.7 1.6 3.7 2.4 5.1 2.7 1 .2 2 .1 2.7-.4.8-.5 1-1.4 1.1-1.9 0-.2 0-.4-.2-.5-.4-.3-.9-.5-1.4-.8Z"/></svg>
        </a>
    </div>

    <script>
        (() => {
            const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
            const nav = document.querySelector('[data-nav]');
            const menuButton = document.querySelector('[data-menu-button]');
            const mobileMenu = document.querySelector('[data-mobile-menu]');
            const syncNav = () => nav?.classList.toggle('is-scrolled', window.scrollY > 24);
            syncNav();
            window.addEventListener('scroll', syncNav, { passive: true });

            menuButton?.addEventListener('click', () => {
                const open = mobileMenu.classList.toggle('is-open');
                menuButton.setAttribute('aria-expanded', String(open));
            });
            mobileMenu?.querySelectorAll('a').forEach((link) => link.addEventListener('click', () => {
                mobileMenu.classList.remove('is-open');
                menuButton?.setAttribute('aria-expanded', 'false');
            }));

            const animateCounter = (counter) => {
                const target = Number(counter.dataset.target || 0);
                const duration = 950;
                const startedAt = performance.now();

                const tick = (now) => {
                    const progress = Math.min((now - startedAt) / duration, 1);
                    const eased = 1 - Math.pow(1 - progress, 3);
                    counter.textContent = String(Math.round(target * eased));
                    if (progress < 1) requestAnimationFrame(tick);
                };

                requestAnimationFrame(tick);
            };

            const bootPremiumCounters = () => {
                const counters = document.querySelectorAll('[data-kpi-count]');
                if (!counters.length) return;

                if (reducedMotion || !('IntersectionObserver' in window)) {
                    counters.forEach((counter) => {
                        counter.textContent = counter.dataset.target || '0';
                    });
                    return;
                }

                const counted = new WeakSet();
                const observer = new IntersectionObserver((entries) => {
                    entries.forEach((entry) => {
                        if (!entry.isIntersecting || counted.has(entry.target)) return;
                        counted.add(entry.target);
                        animateCounter(entry.target);
                        observer.unobserve(entry.target);
                    });
                }, { threshold: .45 });

                counters.forEach((counter) => observer.observe(counter));
            };

            const loadScript = (src) => new Promise((resolve, reject) => {
                const existing = document.querySelector(`script[src="${src}"]`);
                if (existing) {
                    existing.addEventListener('load', resolve, { once: true });
                    existing.addEventListener('error', reject, { once: true });
                    return;
                }

                const script = document.createElement('script');
                script.src = src;
                script.async = true;
                script.onload = resolve;
                script.onerror = reject;
                document.head.appendChild(script);
            });

            const loadGsap = async () => {
                if (window.gsap && window.ScrollTrigger) return true;

                try {
                    await loadScript('https://cdn.jsdelivr.net/npm/gsap@3.12.5/dist/gsap.min.js');
                    await loadScript('https://cdn.jsdelivr.net/npm/gsap@3.12.5/dist/ScrollTrigger.min.js');
                    return Boolean(window.gsap && window.ScrollTrigger);
                } catch {
                    return false;
                }
            };

            const bootPremiumComparison = async () => {
                const section = document.querySelector('[data-premium-problem]');
                if (!section || reducedMotion) return;

                const hasGsap = await loadGsap();
                if (!hasGsap) return;

                const gsap = window.gsap;
                const ScrollTrigger = window.ScrollTrigger;

                gsap.registerPlugin(ScrollTrigger);

                const beforeCard = section.querySelector('[data-problem-card="before"]');
                const afterCard = section.querySelector('[data-problem-card="after"]');
                const line = section.querySelector('[data-flow-line]');
                const arrow = section.querySelector('[data-flow-arrow]');
                const check = section.querySelector('[data-success-check]');
                const statusPills = section.querySelectorAll('[data-status-pill]');
                if (!beforeCard || !afterCard || !line || !arrow || !check) return;

                const timeline = gsap.timeline({
                    defaults: { ease: 'power3.out' },
                    scrollTrigger: {
                        trigger: section,
                        start: 'top 70%',
                        once: true,
                    },
                });

                timeline
                    .fromTo(beforeCard, { autoAlpha: 0, y: 52, rotate: -2 }, { autoAlpha: 1, y: 0, rotate: 2, duration: .9 })
                    .fromTo(afterCard, { autoAlpha: 0, y: 52, scale: .96 }, { autoAlpha: 1, y: 0, scale: 1, duration: .9 }, '-=.58')
                    .fromTo(line, { scaleY: 0 }, { scaleY: 1, duration: .7, ease: 'power2.out' }, '-=.35')
                    .fromTo(arrow, { autoAlpha: 0, x: -32 }, { autoAlpha: 1, x: 0, duration: .64 }, '-=.42');

                if (statusPills.length) {
                    timeline.fromTo(statusPills, { autoAlpha: 0, x: 18 }, { autoAlpha: 1, x: 0, duration: .45, stagger: .08 }, '-=.35');
                }

                timeline.fromTo(check, { autoAlpha: 0, scale: .45 }, { autoAlpha: 1, scale: 1, duration: .48, ease: 'back.out(1.8)' }, '-=.25');
            };

            const bootPremiumSection = () => {
                bootPremiumCounters();
                bootPremiumComparison();
            };

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', bootPremiumSection, { once: true });
            } else {
                bootPremiumSection();
            }

            const revealItems = document.querySelectorAll('[data-reveal]');
            if (reducedMotion || !('IntersectionObserver' in window)) {
                revealItems.forEach((item) => item.classList.add('is-visible'));
            } else {
                const observer = new IntersectionObserver((entries) => {
                    entries.forEach((entry) => {
                        if (!entry.isIntersecting) return;
                        entry.target.classList.add('is-visible');
                        observer.unobserve(entry.target);
                    });
                }, { threshold: .12, rootMargin: '0px 0px -8% 0px' });
                revealItems.forEach((item) => observer.observe(item));
            }

            document.querySelectorAll('[data-spotlight]').forEach((section) => {
                section.addEventListener('pointermove', (event) => {
                    const rect = section.getBoundingClientRect();
                    section.style.setProperty('--mx', `${event.clientX - rect.left}px`);
                    section.style.setProperty('--my', `${event.clientY - rect.top}px`);
                });
            });

            if (!reducedMotion) {
                const parallaxItems = [...document.querySelectorAll('[data-parallax]')];
                let ticking = false;
                const updateParallax = () => {
                    parallaxItems.forEach((item) => {
                        const speed = Number(item.dataset.parallax || .02);
                        const rect = item.getBoundingClientRect();
                        const offset = (rect.top + rect.height / 2 - window.innerHeight / 2) * speed;
                        item.style.transform = `translate3d(0, ${offset}px, 0)`;
                    });
                    ticking = false;
                };
                window.addEventListener('scroll', () => {
                    if (ticking) return;
                    ticking = true;
                    requestAnimationFrame(updateParallax);
                }, { passive: true });
                updateParallax();
            }
        })();
    </script>
</body>
</html>
