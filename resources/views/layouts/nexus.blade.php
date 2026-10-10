<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-nx-theme="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>NexusAI — {{ config('app.name') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        /* ─── THEME TOKENS ──────────────────────────────────────────────────── */
        [data-nx-theme="dark"] {
            --nx-bg:           #070b12;
            --nx-surface:      #0c1018;
            --nx-surface2:     #131920;
            --nx-surface3:     #1a2230;
            --nx-surface4:     #1e2838;
            --nx-border:       #1e2838;
            --nx-border2:      #2a3548;
            --nx-border3:      #364258;
            --nx-accent:       #6366f1;
            --nx-accent-dim:   #4f46e5;
            --nx-accent-light: #818cf8;
            --nx-accent-glow:  rgba(99,102,241,0.18);
            --nx-accent-glow2: rgba(99,102,241,0.08);
            --nx-cyan:         #22d3ee;
            --nx-cyan-dim:     #0891b2;
            --nx-green:        #10b981;
            --nx-red:          #f43f5e;
            --nx-yellow:       #f59e0b;
            --nx-orange:       #f97316;
            --nx-purple:       #a855f7;
            --nx-text:         #e2e8f0;
            --nx-text-muted:   #94a3b8;
            --nx-text-dim:     #475569;
            --nx-user-bubble:  #1a2235;
            --nx-ai-bubble:    #0c1018;
            --nx-rail-bg:      #080c14;
            --nx-radius:       12px;
            --nx-shadow:       0 4px 24px rgba(0,0,0,.45);
            --nx-shadow-sm:    0 2px 8px rgba(0,0,0,.3);
            --nx-gradient:     linear-gradient(135deg, #6366f1 0%, #8b5cf6 50%, #22d3ee 100%);
        }
        [data-nx-theme="light"] {
            --nx-bg:           #f1f4f9;
            --nx-surface:      #ffffff;
            --nx-surface2:     #f8fafc;
            --nx-surface3:     #f1f5f9;
            --nx-surface4:     #e9eef5;
            --nx-border:       #e2e8f0;
            --nx-border2:      #cbd5e1;
            --nx-border3:      #b0bec7;
            --nx-accent:       #6366f1;
            --nx-accent-dim:   #4f46e5;
            --nx-accent-light: #818cf8;
            --nx-accent-glow:  rgba(99,102,241,0.12);
            --nx-accent-glow2: rgba(99,102,241,0.06);
            --nx-cyan:         #0891b2;
            --nx-cyan-dim:     #0e7490;
            --nx-green:        #059669;
            --nx-red:          #e11d48;
            --nx-yellow:       #d97706;
            --nx-orange:       #ea580c;
            --nx-purple:       #9333ea;
            --nx-text:         #0f172a;
            --nx-text-muted:   #475569;
            --nx-text-dim:     #94a3b8;
            --nx-user-bubble:  #eff6ff;
            --nx-ai-bubble:    #ffffff;
            --nx-rail-bg:      #0c1018;
            --nx-radius:       12px;
            --nx-shadow:       0 4px 24px rgba(0,0,0,.12);
            --nx-shadow-sm:    0 2px 8px rgba(0,0,0,.08);
            --nx-gradient:     linear-gradient(135deg, #6366f1 0%, #8b5cf6 50%, #22d3ee 100%);
        }

        /* ─── RESET ─────────────────────────────────────────────────────────── */
        * { box-sizing: border-box; margin: 0; padding: 0; }
        html, body { height: 100%; }
        body {
            font-family: 'Inter', sans-serif;
            background: var(--nx-bg);
            color: var(--nx-text);
            overflow: hidden;
            -webkit-font-smoothing: antialiased;
        }

        /* ─── SCROLLBAR ──────────────────────────────────────────────────────── */
        ::-webkit-scrollbar { width: 5px; height: 5px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: var(--nx-border2); border-radius: 99px; }
        ::-webkit-scrollbar-thumb:hover { background: var(--nx-border3); }

        /* ─── ANIMATIONS ─────────────────────────────────────────────────────── */
        @keyframes fadeSlideIn  { from { opacity:0; transform:translateY(10px); } to { opacity:1; transform:translateY(0); } }
        @keyframes fadeIn       { from { opacity:0; } to { opacity:1; } }
        @keyframes scaleIn      { from { opacity:0; transform:scale(.95); } to { opacity:1; transform:scale(1); } }
        @keyframes blink        { 0%,100% { opacity:1; } 50% { opacity:0; } }
        @keyframes spin         { to { transform: rotate(360deg); } }
        @keyframes wave         { 0%,60%,100%{ transform:translateY(0); } 30%{ transform:translateY(-6px); } }
        @keyframes pulseGlow    { 0%,100% { box-shadow: 0 0 0 0 var(--nx-accent-glow); } 50% { box-shadow: 0 0 0 6px transparent; } }
        @keyframes shimmer      { from { background-position: -200% 0; } to { background-position: 200% 0; } }
        @keyframes slideInRight { from { opacity:0; transform:translateX(12px); } to { opacity:1; transform:translateX(0); } }

        .animate-fade-slide  { animation: fadeSlideIn .3s cubic-bezier(.16,1,.3,1) both; }
        .animate-scale-in    { animation: scaleIn .25s cubic-bezier(.16,1,.3,1) both; }
        .animate-slide-right { animation: slideInRight .25s ease both; }
        .animate-spin        { animation: spin .8s linear infinite; }
        .animate-fade        { animation: fadeIn .2s ease both; }
        .cursor-blink {
            display: inline-block; width: 2px; height: 1.1em;
            background: var(--nx-accent); border-radius: 1px;
            animation: blink 1s step-end infinite;
            vertical-align: text-bottom; margin-left: 1px;
        }

        /* ─── GLASS ──────────────────────────────────────────────────────────── */
        .glass {
            background: rgba(12,16,24,.82);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
        }
        [data-nx-theme="light"] .glass {
            background: rgba(255,255,255,.88);
        }

        /* ─── PROSE (Markdown) ───────────────────────────────────────────────── */
        .nx-prose { line-height: 1.75; }
        .nx-prose p { margin: .55em 0; }
        .nx-prose h1, .nx-prose h2, .nx-prose h3 {
            color: var(--nx-text); font-weight: 700; margin: 1.1em 0 .4em;
            letter-spacing: -.01em;
        }
        .nx-prose h1 { font-size: 1.4em; }
        .nx-prose h2 { font-size: 1.2em; }
        .nx-prose h3 { font-size: 1.05em; }
        .nx-prose ul, .nx-prose ol { padding-left: 1.6em; margin: .5em 0; }
        .nx-prose li { margin: .25em 0; }
        .nx-prose strong { color: var(--nx-text); font-weight: 700; }
        .nx-prose em { color: var(--nx-accent-light); }
        .nx-prose blockquote {
            border-left: 3px solid var(--nx-accent); padding: .4em 1em;
            color: var(--nx-text-muted); margin: .8em 0;
            background: var(--nx-accent-glow2); border-radius: 0 6px 6px 0;
        }
        .nx-prose code {
            font-family: 'JetBrains Mono', monospace; font-size: .84em;
            background: var(--nx-surface3); color: var(--nx-cyan);
            padding: .15em .45em; border-radius: 5px;
            border: 1px solid var(--nx-border);
        }
        .nx-prose pre {
            background: var(--nx-surface2); border: 1px solid var(--nx-border);
            border-radius: 10px; overflow: auto; margin: .8em 0; position: relative;
        }
        .nx-prose pre::before {
            content: attr(data-lang);
            position: absolute; top: .5em; right: .8em;
            font-size: .7em; color: var(--nx-text-dim);
            font-family: 'JetBrains Mono', monospace; text-transform: uppercase;
        }
        .nx-prose pre code {
            background: none; color: var(--nx-text); padding: 1em 1.2em;
            font-size: .83em; display: block; border: none; border-radius: 0;
        }
        .nx-prose table { width: 100%; border-collapse: collapse; font-size: .9em; margin: .8em 0; }
        .nx-prose th {
            background: var(--nx-surface3); padding: .5em .9em;
            text-align: left; border: 1px solid var(--nx-border);
            font-weight: 600; font-size: .85em; color: var(--nx-text-muted);
            text-transform: uppercase; letter-spacing: .04em;
        }
        .nx-prose td { padding: .45em .9em; border: 1px solid var(--nx-border); }
        .nx-prose tr:nth-child(even) td { background: var(--nx-surface2); }
        .nx-prose a { color: var(--nx-accent-light); text-decoration: none; }
        .nx-prose a:hover { text-decoration: underline; }
        .nx-prose hr { border: none; border-top: 1px solid var(--nx-border); margin: 1em 0; }

        /* ─── BUTTONS ────────────────────────────────────────────────────────── */
        .nx-btn {
            display: inline-flex; align-items: center; gap: .4em;
            padding: .42em .9em; border-radius: 8px; font-size: .82em;
            font-weight: 500; border: none; cursor: pointer;
            transition: all .15s ease; user-select: none; outline: none;
            font-family: 'Inter', sans-serif; white-space: nowrap;
        }
        .nx-btn:disabled { opacity: .4; cursor: not-allowed; }
        .nx-btn-ghost { background: transparent; color: var(--nx-text-muted); }
        .nx-btn-ghost:hover:not(:disabled) { background: var(--nx-surface3); color: var(--nx-text); }
        .nx-btn-accent {
            background: var(--nx-accent); color: #fff;
            box-shadow: 0 0 0 0 var(--nx-accent-glow);
        }
        .nx-btn-accent:hover:not(:disabled) {
            background: var(--nx-accent-dim);
            box-shadow: 0 4px 16px var(--nx-accent-glow);
            transform: translateY(-1px);
        }
        .nx-btn-outline {
            background: transparent; color: var(--nx-accent);
            border: 1px solid var(--nx-accent);
        }
        .nx-btn-outline:hover:not(:disabled) { background: var(--nx-accent-glow); }
        .nx-btn-danger { background: transparent; color: var(--nx-red); }
        .nx-btn-danger:hover:not(:disabled) { background: rgba(244,63,94,.12); }

        /* ─── INPUTS ─────────────────────────────────────────────────────────── */
        .nx-input {
            background: var(--nx-surface3); border: 1.5px solid var(--nx-border);
            border-radius: 9px; color: var(--nx-text);
            font-family: 'Inter', sans-serif; font-size: .9em;
            padding: .58em 1em; width: 100%; outline: none;
            transition: border-color .15s, box-shadow .15s;
        }
        .nx-input:focus {
            border-color: var(--nx-accent);
            box-shadow: 0 0 0 3px var(--nx-accent-glow2);
        }
        .nx-input::placeholder { color: var(--nx-text-dim); }
        .nx-input[type="color"] { padding: .3em .5em; cursor: pointer; height: 2.6em; }
        select.nx-input { cursor: pointer; }
        textarea.nx-input { resize: vertical; }

        /* ─── SIDEBAR ITEMS ──────────────────────────────────────────────────── */
        .sidebar-item {
            display: flex; align-items: center; gap: .65em;
            padding: .5em .75em; border-radius: 9px;
            cursor: pointer; font-size: .84em; font-weight: 400;
            transition: all .15s ease; user-select: none;
            width: 100%; border: 1px solid transparent;
            text-align: left; color: var(--nx-text-muted);
            background: transparent;
        }
        .sidebar-item:hover { background: var(--nx-surface3); color: var(--nx-text); }
        .sidebar-item.active {
            background: var(--nx-accent-glow);
            color: var(--nx-accent-light);
            border-color: rgba(99,102,241,.25);
            font-weight: 500;
        }

        /* ─── TOOL CARDS ─────────────────────────────────────────────────────── */
        .tool-card {
            border: 1px solid var(--nx-border);
            border-left: 3px solid var(--nx-cyan);
            border-radius: 9px; background: var(--nx-surface2);
            font-size: .82em; margin-bottom: .5em; overflow: hidden;
        }
        .tool-card-header {
            padding: .55em .9em; display: flex; align-items: center;
            gap: .5em; cursor: pointer; user-select: none;
            transition: background .15s;
        }
        .tool-card-header:hover { background: var(--nx-surface3); }
        .tool-card-body {
            padding: .7em 1em; border-top: 1px solid var(--nx-border);
        }

        /* ─── STATUS DOTS ────────────────────────────────────────────────────── */
        .status-dot { width: 7px; height: 7px; border-radius: 50%; display: inline-block; flex-shrink: 0; }
        .status-pending  { background: var(--nx-yellow); }
        .status-running  { background: var(--nx-cyan); animation: pulseGlow 1.2s infinite; }
        .status-success  { background: var(--nx-green); }
        .status-error    { background: var(--nx-red); }

        /* ─── TYPING DOTS ────────────────────────────────────────────────────── */
        .typing-dot {
            display: inline-block; width: 6px; height: 6px; border-radius: 50%;
            background: var(--nx-text-dim); animation: wave 1.3s ease infinite;
        }
        .typing-dot:nth-child(2) { animation-delay: .15s; }
        .typing-dot:nth-child(3) { animation-delay: .3s; }

        /* ─── IMAGE RESULT ───────────────────────────────────────────────────── */
        .nx-image-result {
            border-radius: 12px; max-width: 100%; border: 1px solid var(--nx-border);
            margin: .6em 0; box-shadow: var(--nx-shadow-sm);
        }

        /* ─── MODAL ──────────────────────────────────────────────────────────── */
        .nx-modal-overlay {
            position: fixed; inset: 0;
            background: rgba(0,0,0,.6);
            backdrop-filter: blur(6px);
            -webkit-backdrop-filter: blur(6px);
            z-index: 60; display: flex;
            align-items: center; justify-content: center;
            padding: 1rem; animation: fadeIn .15s ease;
        }
        .nx-modal {
            background: var(--nx-surface);
            border: 1px solid var(--nx-border2);
            border-radius: 16px; padding: 1.75rem;
            width: 100%; max-width: 520px;
            box-shadow: var(--nx-shadow);
            animation: scaleIn .25s cubic-bezier(.16,1,.3,1);
        }
        .nx-modal-header {
            display: flex; align-items: center; justify-content: space-between;
            margin-bottom: 1.25rem; padding-bottom: 1rem;
            border-bottom: 1px solid var(--nx-border);
        }
        .nx-modal-title {
            font-size: 1em; font-weight: 700; color: var(--nx-text);
            display: flex; align-items: center; gap: .5em;
        }
        .nx-modal-section {
            background: var(--nx-surface2); border: 1px solid var(--nx-border);
            border-radius: 10px; padding: 1rem; margin-bottom: .75rem;
        }
        .nx-modal-label {
            display: block; font-size: .78em; font-weight: 600;
            color: var(--nx-text-dim); text-transform: uppercase;
            letter-spacing: .06em; margin-bottom: .45em;
        }

        /* ─── MOBILE RESPONSIVE ──────────────────────────────────────────────── */
        @media (max-width: 768px) {
            .nx-sidebar {
                position: fixed; left: 0; top: 0; bottom: 0;
                z-index: 40; transform: translateX(-100%);
                transition: transform .28s cubic-bezier(.16,1,.3,1);
            }
            .nx-sidebar.open { transform: translateX(0); }
            .nx-right-panel { display: none !important; }
        }
        @media (max-width: 1024px) {
            .nx-right-panel { display: none !important; }
        }

        /* ─── BADGE ──────────────────────────────────────────────────────────── */
        .nx-badge {
            display: inline-flex; align-items: center; gap: .3em;
            padding: .18em .6em; border-radius: 99px; font-size: .73em;
            font-weight: 600; letter-spacing: .02em;
        }
        .nx-badge-accent { background: var(--nx-accent-glow); color: var(--nx-accent-light); }
        .nx-badge-green  { background: rgba(16,185,129,.15); color: var(--nx-green); }
        .nx-badge-cyan   { background: rgba(34,211,238,.12); color: var(--nx-cyan); }

        /* ─── TOAST OVERRIDE ────────────────────────────────────────────────── */
        [data-nx-theme="light"] .toast { color: #0f172a; }
    </style>
    @livewireStyles
</head>
<body>
    {{ $slot }}
    @livewireScripts
    <script>
    /* ── Theme Manager ───────────────────────────────────────────────────────── */
    (function() {
        const saved = localStorage.getItem('nx-theme') || 'dark';
        document.documentElement.setAttribute('data-nx-theme', saved);
    })();

    window.nexusTheme = {
        get current() { return document.documentElement.getAttribute('data-nx-theme') || 'dark'; },
        set(theme) {
            document.documentElement.setAttribute('data-nx-theme', theme);
            localStorage.setItem('nx-theme', theme);
        },
        toggle() {
            this.set(this.current === 'dark' ? 'light' : 'dark');
        }
    };

    /* ── Global renderMarkdown (accessible from any x-html, not just nexusApp scope) ── */
    window.renderMarkdown = function(t) {
        if (!t) return '';
        t = t.replace(/\[\[NEXUS_IMAGE:(https?:[^\]]+)\]\]/g, (_, url) =>
            `<img src="${url}" class="nx-image-result" alt="AI generated image">`
        );
        t = t.replace(/```(\w*)\n([\s\S]*?)```/g, (_, lang, code) =>
            `<pre data-lang="${lang}"><code class="lang-${lang}">${code.replace(/</g,'&lt;')}</code></pre>`
        );
        t = t.replace(/`([^`]+)`/g, '<code>$1</code>');
        t = t.replace(/^### (.+)$/gm, '<h3>$1</h3>').replace(/^## (.+)$/gm, '<h2>$1</h2>').replace(/^# (.+)$/gm, '<h1>$1</h1>');
        t = t.replace(/\*\*([^*]+)\*\*/g, '<strong>$1</strong>').replace(/\*([^*]+)\*/g, '<em>$1</em>');
        t = t.replace(/^[-•] (.+)$/gm, '<li>$1</li>').replace(/(<li>[\s\S]+?<\/li>)+/g, '<ul>$&</ul>');
        t = t.replace(/^\d+\. (.+)$/gm, '<li>$1</li>');
        t = t.replace(/\[([^\]]+)\]\(([^)]+)\)/g, '<a href="$2" target="_blank" rel="noopener">$1</a>');
        t = t.replace(/\n\n/g, '</p><p>').replace(/\n/g, '<br>');
        return `<p>${t}</p>`;
    };

    /* ── Alpine helpers ─────────────────────────────────────────────────────── */
    document.addEventListener('alpine:init', () => {
        Alpine.data('nexusMarkdown', (text) => ({
            html: '',
            init() { this.html = window.renderMarkdown(text); }
        }));
    });
    </script>
    <x-toast />
</body>
</html>
