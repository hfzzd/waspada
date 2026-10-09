<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Waspada — platform intelijen penipuan open-source untuk Indonesia. Cek pesan, URL, nomor HP, dan rekening sebelum jadi korban.">
    <title>@yield('title', 'Waspada') — Cek Penipuan Online</title>

    {{-- Tailwind CSS via CDN (tanpa build step) --}}
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        brand: {
                            50: '#eef4ff', 100: '#dbe6fe', 200: '#bfd3fe',
                            300: '#93b4fd', 400: '#608dfa', 500: '#3b67f6',
                            600: '#2549eb', 700: '#1d3ad8', 800: '#1e32af',
                            900: '#1e2f8a', 950: '#172054',
                        },
                    },
                    fontFamily: {
                        sans: ['Inter', 'system-ui', 'Segoe UI', 'sans-serif'],
                    },
                }
            }
        }
    </script>

    {{-- Alpine.js via CDN --}}
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.13.10/dist/cdn.min.js"></script>

    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body class="bg-slate-50 text-slate-800 antialiased dark:bg-slate-950 dark:text-slate-200 font-sans">

    {{-- ===== Navbar ===== --}}
    <header class="sticky top-0 z-40 border-b border-slate-200 bg-white/80 backdrop-blur dark:border-slate-800 dark:bg-slate-950/80">
        <nav class="mx-auto flex max-w-5xl items-center justify-between px-4 py-3">
            <a href="{{ url('/') }}" class="flex items-center gap-2 font-extrabold text-xl text-brand-700 dark:text-brand-300">
                <span class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-brand-600 text-white shadow">
                    {{-- ikon perisai --}}
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor"><path fill-rule="evenodd" d="M12 2l8 3v6c0 5-3.4 9.4-8 11-4.6-1.6-8-6-8-11V5l8-3zm0 4.2L6 8.4V11c0 3.6 2.5 6.9 6 8.2 3.5-1.3 6-4.6 6-8.2V8.4l-6-2.2z" clip-rule="evenodd"/></svg>
                </span>
                Waspada
            </a>
            <div class="flex items-center gap-1 text-sm font-medium">
                <a href="{{ url('/') }}" class="rounded-lg px-3 py-2 hover:bg-slate-100 dark:hover:bg-slate-800">Beranda</a>
                <a href="{{ url('/cek') }}" class="rounded-lg px-3 py-2 hover:bg-slate-100 dark:hover:bg-slate-800">Cek</a>
                <a href="{{ url('/komunitas') }}" class="rounded-lg px-3 py-2 hover:bg-slate-100 dark:hover:bg-slate-800">Komunitas</a>
                {{-- TODO (frontend-interaction): tombol toggle dark mode — logika Alpine.js --}}
                <button type="button" id="toggle-tema" aria-label="Ganti tema"
                    class="ml-1 rounded-lg p-2 hover:bg-slate-100 dark:hover:bg-slate-800" title="Mode gelap/terang">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 12.8A9 9 0 1111.2 3 7 7 0 0021 12.8z"/></svg>
                </button>
            </div>
        </nav>
    </header>

    {{-- ===== Konten ===== --}}
    <main class="mx-auto max-w-5xl px-4 py-8 min-h-[70vh]">
        @yield('content')
    </main>

    {{-- ===== Footer ===== --}}
    <footer class="border-t border-slate-200 dark:border-slate-800">
        <div class="mx-auto max-w-5xl px-4 py-6 text-sm text-slate-500 dark:text-slate-400 flex flex-col sm:flex-row items-center justify-between gap-2">
            <p>&copy; {{ date('Y') }} <span class="font-semibold text-slate-700 dark:text-slate-300">Waspada</span> — open-source untuk Indonesia.</p>
            <p>Data komunitas. Selalu verifikasi sebelum bertindak.</p>
        </div>
    </footer>

    @stack('scripts')
</body>
</html>
