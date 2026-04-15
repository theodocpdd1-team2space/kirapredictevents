<!doctype html>
<html lang="{{ str_replace('_','-',app()->getLocale()) }}">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>@yield('title', 'Kira')</title>

  {{-- kalau kamu pakai Vite --}}
  @vite(['resources/css/app.css', 'resources/js/app.js'])

  <style>
    /* jaga biar tampilan public rapi */
    body { min-height: 100vh; }
  </style>
</head>
<body class="bg-slate-50 text-slate-900 dark:bg-slate-950 dark:text-slate-100">
  <main class="min-h-screen">
    {{-- topbar public (optional, clean) --}}
    <div class="border-b border-slate-200 dark:border-slate-800 bg-white/80 dark:bg-slate-950/80 backdrop-blur">
      <div class="mx-auto max-w-6xl px-4 py-3 flex items-center justify-between">
        <div class="flex items-center gap-3">
          <div class="h-9 w-9 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 flex items-center justify-center overflow-hidden">
            {{-- logo optional (ambil dari settings) --}}
            @php($logo = \App\Models\Setting::getValue('business_logo', 'images/logo-kira.png'))
            <img src="{{ asset($logo) }}" alt="Logo" class="h-6 w-6 object-contain">
          </div>
          <div class="leading-tight">
            <div class="text-sm font-semibold text-slate-900 dark:text-white">
              {{ \App\Models\Setting::getValue('business_name', 'Kira') }}
            </div>
            <div class="text-xs text-slate-500 dark:text-slate-400">
              Shared estimation link
            </div>
          </div>
        </div>

        <div class="text-xs text-slate-500 dark:text-slate-400">
          {{ now()->format('Y-m-d H:i') }}
        </div>
      </div>
    </div>

    <div class="mx-auto max-w-6xl px-4 py-8">
      @yield('content')
    </div>

    <div class="mx-auto max-w-6xl px-4 pb-10">
      <div class="text-center text-xs text-slate-400">
        Powered by Kira
      </div>
    </div>
  </main>
</body>
</html>