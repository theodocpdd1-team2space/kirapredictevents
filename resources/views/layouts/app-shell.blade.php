{{-- resources/views/layouts/app-shell.blade.php --}}
@php
  use App\Models\Setting;
  use Illuminate\Support\Facades\Lang;

  // ✅ Safe translator (kalau key tidak ada / hasilnya array -> fallback)
  $t = function(string $key, string $fallback) {
    if (!Lang::has($key)) return $fallback;
    $val = __($key);
    return is_array($val) ? $fallback : (string) $val;
  };

  // Theme
  // Theme - user preference
  $theme = Setting::getUserValue('theme_mode', 'light'); // light|dark|system
  $themeClass = $theme === 'dark' ? 'dark' : '';

  // Business branding
  $bizName = Setting::getValue('business_name', 'Kira');
  $bizLogo = Setting::getValue('business_logo', 'images/logo-kira.png'); // path relatif ke public/

  // Greeting
  $hour = (int) now()->setTimezone(config('app.timezone'))->format('H');
  if ($hour < 11) $greeting = 'Selamat pagi';
  elseif ($hour < 15) $greeting = 'Selamat siang';
  elseif ($hour < 18) $greeting = 'Selamat sore';
  else $greeting = 'Selamat malam';

  $user = auth()->user();
  $userName = $user?->name ?? 'Admin';

  // Avatar initials
  $n = $userName;
  $parts = preg_split('/\s+/', trim($n));
  $initials = strtoupper(substr($parts[0] ?? 'A',0,1).substr($parts[1] ?? 'U',0,1));

  // Profile photo url (from storage)
  $photoUrl = ($user && $user->profile_photo_path)
    ? asset('storage/'.$user->profile_photo_path)
    : null;

  // Sidebar styles
  $base = "flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-semibold transition-all duration-200";
  $active = "bg-blue-600/10 text-blue-700 ring-1 ring-blue-200 dark:bg-blue-400/10 dark:text-blue-300 dark:ring-blue-500/20";
  $inactive = "text-slate-600 hover:bg-slate-100 hover:text-slate-900 dark:text-slate-300 dark:hover:bg-slate-800/60 dark:hover:text-white";
@endphp

<!doctype html>
<html lang="{{ str_replace('_','-',app()->getLocale()) }}" class="{{ $themeClass }}">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>@yield('title', 'Event Multimedia DSS')</title>
  @vite(['resources/css/app.css','resources/js/app.js'])
</head>

<body class="min-h-screen bg-slate-50 text-slate-900 dark:bg-slate-950 dark:text-slate-100">
  <div class="min-h-screen md:flex">

    {{-- Mobile overlay --}}
    <div id="sidebarOverlay"
         class="fixed inset-0 z-40 bg-slate-900/40 backdrop-blur-sm hidden md:hidden"
         onclick="closeSidebar()"></div>

    {{-- Sidebar --}}
    <aside id="sidebar"
           class="fixed inset-y-0 left-0 z-50 w-[280px] flex flex-col
                  bg-white border-r border-slate-200
                  dark:bg-slate-900 dark:border-slate-800
                  transform -translate-x-full transition-transform duration-300 ease-in-out
                  md:translate-x-0 md:static md:inset-auto md:z-auto">

      {{-- Brand --}}
      <div class="px-6 pt-6 pb-5 border-b border-slate-100 dark:border-slate-800">
        <div class="flex items-start gap-3">
          <div class="h-10 w-10 rounded-xl border border-slate-200 bg-white grid place-items-center overflow-hidden
                      dark:border-slate-700 dark:bg-slate-800">
            <img src="{{ asset($bizLogo) }}" class="h-8 w-8 object-contain" alt="{{ $bizName }}">
          </div>
          <div>
            <div class="font-extrabold tracking-tight text-slate-900 dark:text-white leading-tight">
              {{ $bizName }}
            </div>
            <div class="text-xs font-medium text-slate-500 dark:text-slate-400 mt-0.5">
              Decision Support System
            </div>
          </div>
        </div>
      </div>

      {{-- Nav --}}
      <nav class="px-4 py-5 space-y-1.5">
        <a href="{{ route('dashboard') }}"
           class="{{ $base }} {{ request()->routeIs('dashboard') ? $active : $inactive }}">
          <svg class="h-5 w-5 opacity-80" viewBox="0 0 24 24" fill="none">
            <path d="M4 13h7V4H4v9ZM13 20h7V11h-7v9ZM13 9h7V4h-7v5ZM4 20h7v-5H4v5Z"
                  stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>
          </svg>
          {{ $t('nav.dashboard','Dashboard') }}
        </a>

        <a href="{{ route('events.create') }}"
           class="{{ $base }} {{ request()->routeIs('events.create') ? $active : $inactive }}">
          <svg class="h-5 w-5 opacity-80" viewBox="0 0 24 24" fill="none">
            <path d="M12 5v14M5 12h14" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
          </svg>
          {{ $t('nav.new_estimation','Estimasi Baru') }}
        </a>

        <a href="{{ route('estimations.index') }}"
           class="{{ $base }} {{ request()->routeIs('estimations.*') ? $active : $inactive }}">
          <svg class="h-5 w-5 opacity-80" viewBox="0 0 24 24" fill="none">
            <path d="M7 3h10v18H7V3Z" stroke="currentColor" stroke-width="2"/>
            <path d="M9 7h6M9 11h6M9 15h4" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
          </svg>
          {{ $t('nav.estimation_history','Riwayat Estimasi') }}
        </a>

        <a href="{{ route('inventories.index') }}"
           class="{{ $base }} {{ request()->routeIs('inventories.*') ? $active : $inactive }}">
          <svg class="h-5 w-5 opacity-80" viewBox="0 0 24 24" fill="none">
            <path d="M12 2 21 7v10l-9 5-9-5V7l9-5Z" stroke="currentColor" stroke-width="2"/>
            <path d="M12 22V12" stroke="currentColor" stroke-width="2"/>
            <path d="M21 7l-9 5-9-5" stroke="currentColor" stroke-width="2"/>
          </svg>
          {{ $t('nav.inventory','Inventaris') }}
        </a>

        <a href="{{ route('settings.home') }}"
           class="{{ $base }} {{ request()->routeIs('settings.*') ? $active : $inactive }}">
          <svg class="h-5 w-5 opacity-80" viewBox="0 0 24 24" fill="none">
            <path d="M12 15.5a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7Z" stroke="currentColor" stroke-width="2"/>
            <path d="M19.4 15a7.8 7.8 0 0 0 .1-2l2-1.2-2-3.5-2.3.6a8 8 0 0 0-1.7-1l-.3-2.3H9l-.3 2.3a8 8 0 0 0-1.7 1l-2.3-.6-2 3.5 2 1.2a7.8 7.8 0 0 0 .1 2l-2 1.2 2 3.5 2.3-.6a8 8 0 0 0 1.7 1l.3 2.3h6l.3-2.3a8 8 0 0 0 1.7-1l2.3.6 2-3.5-2-1.2Z"
                  stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>
          </svg>
          {{ $t('nav.settings','Pengaturan') }}
        </a>
      </nav>

      {{-- Logout (sidebar) --}}
      <div class="mt-auto px-6 py-5 border-t border-slate-100 dark:border-slate-800">
        <form method="POST" action="{{ route('logout') }}">
          @csrf
          <button type="submit"
                  class="w-full flex items-center gap-3 text-slate-600 hover:text-red-600 transition-colors
                         dark:text-slate-300 dark:hover:text-red-400">
            <svg class="h-5 w-5 opacity-80" viewBox="0 0 24 24" fill="none">
              <path d="M10 17l5-5-5-5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
              <path d="M15 12H3" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
              <path d="M21 21V3" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
            </svg>
            <span class="font-semibold">{{ $t('nav.logout','Logout') }}</span>
          </button>
        </form>
      </div>
    </aside>

    {{-- Main --}}
    <div class="flex-1 min-w-0 flex flex-col relative">

      {{-- Topbar --}}
      <header class="h-16 px-4 md:px-8 flex items-center justify-between sticky top-0 z-30
                     border-b border-slate-200 bg-white/80 backdrop-blur
                     dark:border-slate-800 dark:bg-slate-900/70">
        <div class="flex items-center gap-3">
          <button type="button"
                  class="md:hidden rounded-lg border border-slate-200 bg-white p-2 text-slate-700 hover:bg-slate-50
                         dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700"
                  onclick="openSidebar()">
            <span class="sr-only">Open menu</span>
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M4 6h16M4 12h16M4 18h16"></path>
            </svg>
          </button>

          <div class="leading-tight">
            <div class="text-xs font-medium text-slate-500 dark:text-slate-400">{{ $greeting }},</div>
            <div class="text-sm font-bold text-slate-900 dark:text-white">{{ $userName }}</div>
          </div>
        </div>

        {{-- Right: Profile dropdown --}}
        <details class="relative">
          <summary class="list-none cursor-pointer select-none">
            <div class="flex items-center gap-3">
              <div class="hidden sm:block text-right leading-tight">
                <div class="text-sm font-bold text-slate-900 dark:text-slate-100">{{ $userName }}</div>
                <div class="text-xs font-medium text-slate-500 dark:text-slate-400">
                  {{ $user?->job_title ?: ucfirst(str_replace('_',' ', $user?->role ?? 'admin')) }}
                </div>
              </div>

              @if($photoUrl)
                <img
                  src="{{ $photoUrl }}"
                  alt="Profile photo"
                  class="h-10 w-10 rounded-full object-cover border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800"
                />
              @else
                <div class="h-10 w-10 rounded-full bg-blue-100 text-blue-700 border border-blue-200 grid place-items-center font-bold
                            dark:bg-blue-500/20 dark:text-blue-200 dark:border-blue-500/30">
                  {{ $initials }}
                </div>
              @endif
            </div>
          </summary>

          <div class="absolute right-0 mt-3 w-56 rounded-xl border border-slate-200 bg-white shadow-lg
                      dark:border-slate-800 dark:bg-slate-900 overflow-hidden z-50">
            <a href="{{ route('profile.edit') }}"
               class="block px-4 py-3 text-sm font-semibold text-slate-900 hover:bg-slate-50
                      dark:text-slate-100 dark:hover:bg-slate-800/60">
              {{ $t('nav.profile','Profile') }}
            </a>

            <div class="h-px bg-slate-200 dark:bg-slate-800"></div>

            <form method="POST" action="{{ route('logout') }}">
              @csrf
              <button type="submit"
                      class="w-full text-left px-4 py-3 text-sm font-semibold text-red-600 hover:bg-red-50
                             dark:text-red-400 dark:hover:bg-red-500/10">
                {{ $t('nav.logout','Logout') }}
              </button>
            </form>
          </div>
        </details>
      </header>

      <main class="p-4 md:p-8 flex-1 relative">
        {{-- Soft background blobs --}}
        <div class="pointer-events-none absolute -top-16 -right-24 h-80 w-80 rounded-full bg-blue-500/10 blur-3xl dark:bg-blue-600/5"></div>
        <div class="pointer-events-none absolute top-1/2 -left-24 h-80 w-80 rounded-full bg-purple-500/10 blur-3xl dark:bg-purple-600/5"></div>

        <div class="relative z-10">
          @yield('content')
        </div>
      </main>
    </div>
  </div>

  <script>
    function openSidebar() {
      const sidebar = document.getElementById('sidebar');
      const overlay = document.getElementById('sidebarOverlay');
      sidebar.classList.remove('-translate-x-full');
      overlay.classList.remove('hidden');
      document.body.classList.add('overflow-hidden');
    }

    function closeSidebar() {
      const sidebar = document.getElementById('sidebar');
      const overlay = document.getElementById('sidebarOverlay');
      sidebar.classList.add('-translate-x-full');
      overlay.classList.add('hidden');
      document.body.classList.remove('overflow-hidden');
    }

    // optional: close dropdown when click outside (details)
    document.addEventListener('click', (e) => {
      document.querySelectorAll('details').forEach((d) => {
        if (!d.contains(e.target)) d.removeAttribute('open');
      });
    });
  </script>
</body>
</html>