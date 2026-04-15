@extends('layouts.app-shell')

@section('content')
<div class="space-y-6">
  {{-- Judul Utama yang Pangeran maksud --}}
  <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
    <div>
      <h1 class="text-4xl font-semibold tracking-tight text-slate-900 dark:text-white transition-colors">Settings</h1>
      <p class="text-slate-500 dark:text-slate-400 mt-2 transition-colors">Manage rules, business information, language, and theme.</p>
    </div>

    {{-- Tombol aksi jika ada di header --}}
    @yield('settings_actions')
  </div>

  {{-- Tab Menu (_tabs.blade.php) --}}
  @include('pages.settings._tabs')

  {{-- Konten Spesifik (Business, Language, dll) --}}
  <div class="relative">
    @yield('settings_content')
  </div>
</div>
@endsection