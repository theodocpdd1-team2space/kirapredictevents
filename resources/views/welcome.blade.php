<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ config('app.name', 'Kira DSS') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800&display=swap" rel="stylesheet" />

        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @else
            <script src="https://cdn.tailwindcss.com"></script>
            <script>
                tailwind.config = {
                    darkMode: 'class', // Atur ini ke 'media' jika ingin mengikuti OS otomatis
                    theme: {
                        fontFamily: {
                            sans: ['Inter', 'sans-serif'],
                        }
                    }
                }
            </script>
        @endif
        
        <style>
            /* Custom utility for smooth background gradients */
            .bg-grid-slate-100 {
                background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 32 32' width='32' height='32' fill='none' stroke='%23f1f5f9'%3e%3cpath d='M0 .5H31.5V32'/%3e%3c/svg%3e");
            }
            .dark .bg-grid-slate-900 {
                background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 32 32' width='32' height='32' fill='none' stroke='%230f172a'%3e%3cpath d='M0 .5H31.5V32'/%3e%3c/svg%3e");
            }
        </style>
    </head>
    <body class="antialiased bg-slate-50 text-slate-900 dark:bg-slate-950 dark:text-slate-100 transition-colors duration-300 min-h-screen flex flex-col relative overflow-x-hidden">
        
        {{-- Background Pattern --}}
        <div class="absolute inset-0 z-[-1] bg-grid-slate-100 dark:bg-grid-slate-900 [mask-image:linear-gradient(to_bottom,white,transparent)]"></div>
        <div class="absolute top-0 right-0 -mr-20 -mt-20 w-[500px] h-[500px] rounded-full bg-blue-500/10 blur-[100px] pointer-events-none"></div>
        <div class="absolute bottom-0 left-0 -ml-20 -mb-20 w-[500px] h-[500px] rounded-full bg-purple-500/10 blur-[100px] pointer-events-none"></div>

        {{-- Navigation --}}
        <header class="w-full max-w-7xl mx-auto px-6 py-6 flex items-center justify-between z-10 relative">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-blue-600 rounded-xl flex items-center justify-center shadow-lg shadow-blue-500/30">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                    </svg>
                </div>
                <div class="font-bold text-xl tracking-tight">Kira<span class="text-blue-600 dark:text-blue-500">.</span></div>
            </div>

            @if (Route::has('login'))
                <nav class="flex items-center gap-4">
                    @auth
                        <a href="{{ url('/dashboard') }}" class="text-sm font-semibold text-slate-600 hover:text-slate-900 dark:text-slate-300 dark:hover:text-white transition-colors">
                            Dashboard
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="text-sm font-semibold text-slate-600 hover:text-slate-900 dark:text-slate-300 dark:hover:text-white transition-colors">
                            Log in
                        </a>
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="text-sm font-semibold px-4 py-2 bg-slate-900 text-white rounded-lg hover:bg-slate-800 dark:bg-white dark:text-slate-900 dark:hover:bg-slate-100 transition-colors shadow-sm">
                                Register
                            </a>
                        @endif
                    @endauth
                </nav>
            @endif
        </header>

        {{-- Main Hero Content --}}
        <main class="flex-1 flex flex-col items-center justify-center w-full max-w-7xl mx-auto px-6 py-12 z-10 relative text-center">
            
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-blue-50 dark:bg-blue-500/10 border border-blue-200 dark:border-blue-500/20 text-blue-700 dark:text-blue-400 text-sm font-medium mb-8">
                <span class="flex w-2 h-2 rounded-full bg-blue-600 animate-pulse"></span>
                Event Multimedia Decision Support System
            </div>

            <h1 class="text-5xl md:text-7xl font-extrabold tracking-tight text-slate-900 dark:text-white mb-6 leading-tight max-w-4xl">
                Estimasi Biaya Event <br class="hidden md:block" /> 
                <span class="text-transparent bg-clip-text bg-gradient-to-r from-blue-600 to-purple-600 dark:from-blue-400 dark:to-purple-400">Lebih Cerdas & Akurat</span>
            </h1>

            <p class="text-lg md:text-xl text-slate-600 dark:text-slate-400 max-w-2xl mx-auto mb-10 leading-relaxed">
                Sistem pendukung keputusan cerdas berbasis rule-based expert system untuk manajemen inventaris dan pembuatan estimasi harga (RAB) event multimedia Anda.
            </p>

            <div class="flex flex-col sm:flex-row items-center justify-center gap-4 w-full sm:w-auto">
                @auth
                    <a href="{{ url('/dashboard') }}" class="w-full sm:w-auto px-8 py-4 bg-blue-600 hover:bg-blue-700 text-white text-base font-semibold rounded-xl shadow-lg shadow-blue-500/30 transition-all hover:-translate-y-1">
                        Buka Dashboard
                    </a>
                @else
                    <a href="{{ route('login') }}" class="w-full sm:w-auto px-8 py-4 bg-blue-600 hover:bg-blue-700 text-white text-base font-semibold rounded-xl shadow-lg shadow-blue-500/30 transition-all hover:-translate-y-1">
                        Mulai Sekarang
                    </a>
                    <a href="#features" class="w-full sm:w-auto px-8 py-4 bg-white dark:bg-slate-900 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-900 dark:text-white border border-slate-200 dark:border-slate-800 text-base font-semibold rounded-xl shadow-sm transition-all">
                        Pelajari Fitur
                    </a>
                @endauth
            </div>

            {{-- Feature Highlights --}}
            <div id="features" class="grid grid-cols-1 md:grid-cols-3 gap-8 w-full mt-24 text-left">
                {{-- Feature 1 --}}
                <div class="bg-white/60 dark:bg-slate-900/60 backdrop-blur-md p-6 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm">
                    <div class="w-12 h-12 bg-blue-100 dark:bg-blue-500/20 text-blue-600 dark:text-blue-400 rounded-lg flex items-center justify-center mb-4">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6M9 11h6M9 15h4M7 3h10v18H7V3z"></path></svg>
                    </div>
                    <h3 class="text-xl font-bold text-slate-900 dark:text-white mb-2">Smart Estimation</h3>
                    <p class="text-slate-600 dark:text-slate-400 text-sm leading-relaxed">
                        Hasilkan rincian biaya otomatis berdasarkan jenis event, skala, durasi, dan aturan expert yang dapat disesuaikan.
                    </p>
                </div>

                {{-- Feature 2 --}}
                <div class="bg-white/60 dark:bg-slate-900/60 backdrop-blur-md p-6 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm">
                    <div class="w-12 h-12 bg-purple-100 dark:bg-purple-500/20 text-purple-600 dark:text-purple-400 rounded-lg flex items-center justify-center mb-4">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                    </div>
                    <h3 class="text-xl font-bold text-slate-900 dark:text-white mb-2">Inventory Validation</h3>
                    <p class="text-slate-600 dark:text-slate-400 text-sm leading-relaxed">
                        Sistem mendeteksi kekurangan alat (shortage) secara real-time saat pembuatan estimasi berdasarkan stok gudang.
                    </p>
                </div>

                {{-- Feature 3 --}}
                <div class="bg-white/60 dark:bg-slate-900/60 backdrop-blur-md p-6 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm">
                    <div class="w-12 h-12 bg-emerald-100 dark:bg-emerald-500/20 text-emerald-600 dark:text-emerald-400 rounded-lg flex items-center justify-center mb-4">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <h3 class="text-xl font-bold text-slate-900 dark:text-white mb-2">PDF & Reporting</h3>
                    <p class="text-slate-600 dark:text-slate-400 text-sm leading-relaxed">
                        Verifikasi hasil estimasi dan ekspor langsung menjadi dokumen PDF bergaya invoice profesional untuk klien.
                    </p>
                </div>
            </div>

        </main>

        <footer class="w-full border-t border-slate-200 dark:border-slate-800 bg-white/50 dark:bg-slate-950/50 backdrop-blur-sm mt-12 py-6 text-center relative z-10">
            <p class="text-slate-500 dark:text-slate-400 text-sm">
                &copy; {{ date('Y') }} Kira Decision Support System. Built for multimedia professionals.
            </p>
        </footer>

        {{-- Script untuk deteksi Dark Mode bawaan OS (jika Tailwind tidak menggunakan 'class' mode) --}}
        <script>
            // Opsional: Script untuk toggle class 'dark' pada element <html> jika Anda punya tombol toggle theme
            // const html = document.documentElement;
            // html.classList.add('dark'); // Paksa dark mode untuk testing
        </script>
    </body>
</html>