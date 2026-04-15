<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>KIRA - Sign In</title>

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;700;800;900&display=swap');
        .font-jakarta { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-jakarta antialiased bg-[#0f172a] selection:bg-blue-500 selection:text-white m-0 p-0 overflow-x-hidden">

    {{-- Wrapper Utama --}}
    <div class="min-h-screen w-full relative flex items-center justify-center p-4 sm:p-8">
        
        {{-- Efek Ambient Glow Biru --}}
        <div class="absolute top-0 left-0 w-[500px] h-[500px] bg-blue-600/20 rounded-full blur-[100px] -translate-x-1/4 -translate-y-1/4 pointer-events-none"></div>
        <div class="absolute bottom-0 right-0 w-[600px] h-[600px] bg-blue-600/10 rounded-full blur-[120px] translate-x-1/4 translate-y-1/4 pointer-events-none"></div>

        <div class="w-full max-w-6xl grid lg:grid-cols-2 gap-8 lg:gap-16 items-center relative z-10">
            
            {{-- KIRI: Storytelling & Branding (HANYA MUNCUL DI DESKTOP) --}}
            <div class="hidden lg:flex flex-col justify-center">
                {{-- Logo Container --}}
                <div class="mb-8 inline-block p-4 bg-white/5 backdrop-blur-xl rounded-3xl border border-white/10 self-start">
                    <div class="flex items-center space-x-4">
                        <div class="w-16 h-16 bg-white rounded-2xl flex items-center justify-center p-1.5 shadow-lg shadow-blue-500/20">
                            <img src="{{ asset('images/logo-kira.png') }}" alt="Logo KIRA" class="h-full w-full object-contain">
                        </div>
                        <span class="text-4xl font-black tracking-tighter text-white">Kira<span class="text-blue-500">.</span></span>
                    </div>
                </div>

                {{-- Judul Dramatis --}}
                <h2 class="text-blue-500 font-bold tracking-[0.4em] uppercase text-xs mb-4">The Final Project 2026</h2>
                <h1 class="text-5xl xl:text-6xl font-black mb-8 leading-tight text-white">
                    Estimasi Harga <br><span class="text-blue-500 italic">Tanpa Pusing.</span>
                </h1>
                
                <p class="text-slate-400 text-lg max-w-md mb-10 leading-relaxed">
                    Sistem pendukung keputusan untuk rekomendasi peralatan & estimasi biaya event berbasis <span class="text-white font-bold">Rule-Based Decision Tree</span>.
                </p>

                {{-- Tags --}}
                <div class="flex flex-wrap gap-3">
                    <span class="bg-white/5 border border-white/10 px-4 py-2 rounded-full text-xs font-bold text-slate-300 uppercase tracking-widest cursor-default">Rule-Based</span>
                    <span class="bg-white/5 border border-white/10 px-4 py-2 rounded-full text-xs font-bold text-slate-300 uppercase tracking-widest cursor-default">Forward Chaining</span>
                </div>
            </div>

            {{-- KANAN: Glassmorphism Login Form (RESPONSIF) --}}
            <div class="w-full max-w-md mx-auto lg:max-w-none">
                <div class="bg-slate-900/50 p-8 sm:p-10 rounded-[2.5rem] border border-white/10 backdrop-blur-xl shadow-2xl relative overflow-hidden group">
                    
                    {{-- Efek Glow Halus di dalam Card --}}
                    <div class="absolute -inset-4 bg-blue-500/5 rounded-[3rem] blur-xl opacity-0 group-hover:opacity-100 transition-opacity duration-700 pointer-events-none"></div>

                    <div class="relative z-10">
                        {{-- Mobile Branding --}}
                        <div class="flex lg:hidden items-center space-x-3 mb-10">
                            <div class="w-12 h-12 bg-white rounded-xl flex items-center justify-center p-1 shadow-md shadow-blue-500/20">
                                <img src="{{ asset('images/logo-kira.png') }}" alt="Logo KIRA" class="h-full w-full object-contain">
                            </div>
                            <div>
                                <span class="text-2xl font-black tracking-tighter text-white block leading-none">Kira<span class="text-blue-500">.</span></span>
                                <span class="text-[10px] text-blue-500 font-bold tracking-[0.2em] uppercase">Event Multimedia</span>
                            </div>
                        </div>

                        <div>
                            <h3 class="text-3xl font-extrabold mb-2 text-white">Sign In</h3>
                            <p class="text-sm text-slate-400 mb-8">
                                Masukkan kredensial admin untuk masuk ke Kira.
                            </p>
                        </div>

                        <x-auth-session-status class="mb-6" :status="session('status')" />

                        <form method="POST" action="{{ route('login') }}" class="space-y-6">
                            @csrf

                            {{-- Input Email --}}
                            <div class="space-y-2">
                                <label for="email" class="text-xs font-bold text-slate-300 uppercase tracking-wider">Email Address</label>
                                <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username"
                                    class="w-full bg-black/20 border border-white/10 rounded-xl px-4 py-3.5 text-white placeholder-slate-600 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-all font-medium"
                                    placeholder="admin@kira-event.com">
                                <x-input-error :messages="$errors->get('email')" class="mt-2 text-red-400" />
                            </div>

                            {{-- Input Password --}}
                            <div class="space-y-2">
                                <div class="flex items-center justify-between">
                                    <label for="password" class="text-xs font-bold text-slate-300 uppercase tracking-wider">Password</label>
                                    @if (Route::has('password.request'))
                                        <a href="{{ route('password.request') }}" class="text-xs font-bold text-blue-500 hover:text-blue-400 transition-colors">
                                            Forgot?
                                        </a>
                                    @endif
                                </div>
                                <input id="password" type="password" name="password" required autocomplete="current-password"
                                    class="w-full bg-black/20 border border-white/10 rounded-xl px-4 py-3.5 text-white placeholder-slate-600 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-all font-medium"
                                    placeholder="••••••••">
                                <x-input-error :messages="$errors->get('password')" class="mt-2 text-red-400" />
                            </div>

                            {{-- Remember Me --}}
                            <div class="flex items-center">
                                <label for="remember_me" class="inline-flex items-center cursor-pointer">
                                    <input id="remember_me" type="checkbox" name="remember"
                                        class="rounded bg-black/20 border-white/20 text-blue-600 shadow-sm focus:ring-blue-500/50 cursor-pointer">
                                    <span class="ml-3 text-sm text-slate-400">Keep me logged in</span>
                                </label>
                            </div>

                            {{-- Button Submit --}}
                            <button type="submit"
                                class="w-full bg-blue-600 hover:bg-blue-500 text-white px-6 py-4 rounded-xl font-black text-sm tracking-wide shadow-lg shadow-blue-600/30 transition-all hover:scale-[1.02] active:scale-[0.98] uppercase flex items-center justify-center space-x-2">
                                <span>Masuk ke Dashboard</span>
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                            </button>

                            @if (Route::has('register'))
                            <p class="text-center text-sm text-slate-400 mt-6">
                                Belum punya akses? 
                                <a class="font-bold text-white hover:text-blue-400 transition-colors underline decoration-blue-500/50" href="{{ route('register') }}">
                                    Buat sekarang
                                </a>
                            </p>
                            @endif
                        </form>
                    </div>
                </div>
            </div>
            
        </div>
    </div>
</body>
</html>