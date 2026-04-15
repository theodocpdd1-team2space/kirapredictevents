<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Event DSS') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-slate-50 dark:bg-slate-950 text-slate-900 dark:text-slate-100 antialiased transition-colors duration-300 relative">
    
    {{-- Background blobs --}}
    <div class="fixed inset-0 -z-10 overflow-hidden pointer-events-none">
        <div class="absolute -top-24 -left-24 h-[420px] w-[420px] rounded-full bg-blue-500/20 dark:bg-blue-600/10 blur-3xl transition-colors duration-300"></div>
        <div class="absolute top-1/3 -right-24 h-[520px] w-[520px] rounded-full bg-blue-600/15 dark:bg-blue-500/10 blur-3xl transition-colors duration-300"></div>
        <div class="absolute -bottom-24 left-1/4 h-[420px] w-[420px] rounded-full bg-blue-400/15 dark:bg-purple-500/10 blur-3xl transition-colors duration-300"></div>

        {{-- Grid Halus (Light Mode) --}}
        <div class="absolute inset-0 opacity-[0.05] dark:opacity-0 transition-opacity duration-300"
             style="background-image: linear-gradient(to right, #0f172a 1px, transparent 1px), linear-gradient(to bottom, #0f172a 1px, transparent 1px); background-size: 48px 48px;">
        </div>

        {{-- Grid Halus (Dark Mode) --}}
        <div class="absolute inset-0 opacity-0 dark:opacity-[0.03] transition-opacity duration-300"
             style="background-image: linear-gradient(to right, #ffffff 1px, transparent 1px), linear-gradient(to bottom, #ffffff 1px, transparent 1px); background-size: 48px 48px;">
        </div>
    </div>

    <main class="flex min-h-screen items-center justify-center px-4 py-10 relative z-10">
        <div class="w-full max-w-[980px]">
            {{ $slot }}
        </div>
    </main>
</body>
</html>