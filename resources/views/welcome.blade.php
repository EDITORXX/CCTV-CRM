<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#0f172a">
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <title>{{ config('app.name', 'CCTV Management') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
    <style>
        :root {
            --bg: #0f172a;
            --bg-card: #1e293b;
            --bg-card-hover: #334155;
            --text: #f8fafc;
            --text-muted: #94a3b8;
            --accent: #3b82f6;
            --accent-hover: #2563eb;
            --border: rgba(248, 250, 252, 0.08);
            --radius: 12px;
            --radius-lg: 16px;
            --shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.2), 0 2px 4px -2px rgba(0, 0, 0, 0.15);
            --shadow-lg: 0 20px 25px -5px rgba(0, 0, 0, 0.25), 0 8px 10px -6px rgba(0, 0, 0, 0.2);
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Inter', system-ui, sans-serif;
            background: var(--bg);
            color: var(--text);
            min-height: 100vh;
            line-height: 1.5;
            -webkit-font-smoothing: antialiased;
        }
        .wrap {
            max-width: 1200px;
            margin: 0 auto;
            padding: 24px 20px 40px;
        }
        .header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 48px;
        }
        .logo {
            font-weight: 700;
            font-size: 1.125rem;
            color: var(--text);
            text-decoration: none;
            letter-spacing: -0.02em;
        }
        .nav {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .nav a {
            font-size: 0.875rem;
            font-weight: 500;
            padding: 8px 16px;
            border-radius: 8px;
            text-decoration: none;
            transition: background 0.2s, color 0.2s;
        }
        .nav .link-ghost {
            color: var(--text-muted);
        }
        .nav .link-ghost:hover {
            color: var(--text);
            background: var(--bg-card);
        }
        .nav .link-outline {
            color: var(--text);
            border: 1px solid var(--border);
        }
        .nav .link-outline:hover {
            background: var(--bg-card);
            border-color: var(--text-muted);
        }
        .nav .link-primary {
            background: var(--accent);
            color: white;
            border: none;
        }
        .nav .link-primary:hover {
            background: var(--accent-hover);
            color: white;
        }
        .hero {
            display: grid;
            grid-template-columns: 1fr;
            gap: 32px;
            align-items: center;
            margin-bottom: 48px;
        }
        @media (min-width: 768px) {
            .hero { grid-template-columns: 1fr 1fr; gap: 48px; }
        }
        .hero-content h1 {
            font-size: clamp(1.75rem, 4vw, 2.5rem);
            font-weight: 700;
            letter-spacing: -0.03em;
            line-height: 1.2;
            margin-bottom: 12px;
            color: var(--text);
        }
        .hero-content p {
            font-size: 1.0625rem;
            color: var(--text-muted);
            margin-bottom: 28px;
            max-width: 420px;
        }
        .hero-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
        }
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-family: inherit;
            font-size: 0.9375rem;
            font-weight: 600;
            padding: 12px 20px;
            border-radius: 10px;
            cursor: pointer;
            text-decoration: none;
            border: none;
            transition: background 0.2s, color 0.2s, border-color 0.2s;
        }
        .btn-primary {
            background: var(--accent);
            color: white;
        }
        .btn-primary:hover {
            background: var(--accent-hover);
            color: white;
        }
        .btn-secondary {
            background: var(--bg-card);
            color: var(--text);
            border: 1px solid var(--border);
        }
        .btn-secondary:hover {
            background: var(--bg-card-hover);
            border-color: var(--text-muted);
        }
        .hero-visual {
            background: linear-gradient(145deg, var(--bg-card) 0%, #0f172a 100%);
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            padding: 48px 32px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 280px;
            box-shadow: var(--shadow-lg);
        }
        .hero-visual-icon {
            width: 80px;
            height: 80px;
            background: rgba(59, 130, 246, 0.15);
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 20px;
        }
        .hero-visual-icon svg {
            width: 40px;
            height: 40px;
            color: var(--accent);
        }
        .hero-visual span {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--text);
            text-align: center;
        }
        .pwa-row { margin-top: 12px; }
        .hidden { display: none !important; }
        .pwa-status {
            font-size: 0.8125rem;
            color: var(--text-muted);
            margin-top: 8px;
        }
        .footer {
            text-align: center;
            padding-top: 32px;
            border-top: 1px solid var(--border);
            font-size: 0.8125rem;
            color: var(--text-muted);
        }
    </style>
</head>
<body>
    <div class="wrap">
        <header class="header">
            <a href="{{ url('/') }}" class="logo">{{ config('app.name', 'CCTV Management') }}</a>
            @if (Route::has('login'))
                <nav class="nav">
                    @auth
                        <a href="{{ url('/dashboard') }}" class="link-primary">Dashboard</a>
                    @else
                        <a href="{{ route('login') }}" class="link-ghost">Log in</a>
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="link-outline">Register</a>
                        @endif
                    @endauth
                </nav>
            @endif
        </header>

        <main class="hero">
            <div class="hero-content">
                <h1>Manage your CCTV business in one place</h1>
                <p>Sign in to access your dashboard, or install the app for quick access from your device.</p>
                <div class="hero-actions">
                    <a href="{{ route('login') }}" class="btn btn-primary">
                        Log in
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M6 3.5a.5.5 0 0 1 .5-.5h8a.5.5 0 0 1 .5.5v9a.5.5 0 0 1-.5.5h-8a.5.5 0 0 1-.5-.5v-2a.5.5 0 0 0-1 0v2A1.5 1.5 0 0 0 6.5 14h8a1.5 1.5 0 0 0 1.5-1.5v-9A1.5 1.5 0 0 0 14.5 2h-8A1.5 1.5 0 0 0 5 3.5v2a.5.5 0 0 0 1 0v-2z"/><path fill-rule="evenodd" d="M11.854 8.354a.5.5 0 0 0 0-.708l-3-3a.5.5 0 1 0-.708.708L10.293 7.5H1.5a.5.5 0 0 0 0 1h8.793l-2.147 2.146a.5.5 0 0 0 .708.708l3-3z"/></svg>
                    </a>
                    <span class="pwa-row">
                        <button type="button" id="pwa-install-btn" class="btn btn-secondary hidden" aria-label="Install app">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" viewBox="0 0 16 16"><path d="M4.715 6.542 3.343 7.914a3 3 0 1 0 4.243 4.243l1.828-1.829A3 3 0 0 0 8.586 5.5L8 6.086a1.002 1.002 0 0 0-.154.199 2 2 0 0 1 .861 3.337L6.88 11.45a2 2 0 1 1-2.83-2.83l.793-.792a4.018 4.018 0 0 1-.128-1.287z"/><path d="M6.586 4.672A3 3 0 0 0 7.414 9.5l.775-.776a2 2 0 0 1-.896-3.346L9.12 3.55a2 2 0 1 1 2.83 2.83l-.793.792c.112.42.155.855.128 1.287l1.372-1.372a3 3 0 1 0-4.243-4.243L6.586 4.672z"/></svg>
                            Install app
                        </button>
                        <span id="pwa-install-status" class="pwa-status hidden"></span>
                    </span>
                </div>
            </div>
            <div class="hero-visual">
                <div class="hero-visual-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 16 16"><path d="M0 3a2 2 0 0 1 2-2h7.5a2 2 0 0 1 1.983 1.738l3.11-1.382A1 1 0 0 1 16 2.269v11.462a1 1 0 0 1-1.406.913l-3.111-1.382A2 2 0 0 1 9.5 13H2a2 2 0 0 1-2-2V3z"/></svg>
                </div>
                <span>{{ config('app.name', 'CCTV Management') }}</span>
            </div>
        </main>

        <footer class="footer">
            &copy; {{ date('Y') }} {{ config('app.name', 'CCTV Management System') }}
        </footer>
    </div>

    <script>
    (function() {
        var installBtn = document.getElementById('pwa-install-btn');
        var statusEl = document.getElementById('pwa-install-status');
        var deferredPrompt;
        if (window.matchMedia && window.matchMedia('(display-mode: standalone)').matches) { if (installBtn) installBtn.classList.add('hidden'); return; }
        if (window.navigator.standalone === true) { if (installBtn) installBtn.classList.add('hidden'); return; }
        window.addEventListener('beforeinstallprompt', function(e) {
            e.preventDefault();
            deferredPrompt = e;
            if (installBtn) installBtn.classList.remove('hidden');
        });
        if (installBtn) {
            installBtn.addEventListener('click', function() {
                if (!deferredPrompt) return;
                deferredPrompt.prompt();
                deferredPrompt.userChoice.then(function(choice) {
                    if (choice.outcome === 'accepted' && statusEl) { statusEl.textContent = 'App installed.'; statusEl.classList.remove('hidden'); installBtn.classList.add('hidden'); }
                    else if (statusEl) { statusEl.textContent = 'Install cancelled.'; statusEl.classList.remove('hidden'); }
                    deferredPrompt = null;
                });
            });
        }
        if ('serviceWorker' in navigator) { navigator.serviceWorker.register('{{ asset("sw.js") }}').catch(function() {}); }
    })();
    </script>
</body>
</html>
