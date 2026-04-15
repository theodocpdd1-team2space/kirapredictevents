@extends('layouts.app-shell')
@section('title','Settings • Theme')

@section('content')
<div class="space-y-6">
  {{-- Header --}}
  <div class="transition-colors">
    <h1 class="text-4xl font-semibold tracking-tight text-slate-900 dark:text-white">Settings</h1>
    <p class="text-slate-500 dark:text-slate-400 mt-2">Manage rules, business information, language, and theme.</p>
  </div>

  {{-- Menu Tab --}}
  @include('pages.settings._tabs')

  @php($mode = $themeMode ?? \App\Models\Setting::getValue('theme_mode','light'))

  {{-- Kontainer Utama Theme --}}
  <div class="bg-white dark:bg-slate-900 rounded-xl shadow-sm border border-slate-200 dark:border-slate-800 p-6 max-w-3xl transition-colors duration-300">
    <h2 class="text-xl font-semibold text-slate-900 dark:text-white">Theme</h2>
    <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Pilih mode tampilan aplikasi.</p>

    @if(session('success'))
      <div class="mt-4 rounded-lg border border-green-200 dark:border-green-500/20 bg-green-50 dark:bg-green-500/10 px-4 py-3 text-sm text-green-800 dark:text-green-400 flex items-center gap-2">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
        {{ session('success') }}
      </div>
    @endif

    <form method="POST" action="{{ route('settings.theme.update') }}" class="mt-6 space-y-4">
      @csrf
      @method('PATCH')

      <label class="flex items-start gap-3 rounded-xl border border-slate-200 dark:border-slate-700 p-4 hover:bg-slate-50 dark:hover:bg-slate-800/50 cursor-pointer transition-colors has-[:checked]:border-blue-300 dark:has-[:checked]:border-blue-500/50 has-[:checked]:bg-blue-50 dark:has-[:checked]:bg-blue-500/5">
        <input type="radio" name="theme_mode" value="light" class="mt-1 text-blue-600 focus:ring-blue-500/20" @checked($mode==='light')>
        <div>
          <div class="font-semibold text-slate-900 dark:text-white">Light</div>
          <div class="text-sm text-slate-500 dark:text-slate-400">Tampilan terang (default).</div>
        </div>
      </label>

      <label class="flex items-start gap-3 rounded-xl border border-slate-200 dark:border-slate-700 p-4 hover:bg-slate-50 dark:hover:bg-slate-800/50 cursor-pointer transition-colors has-[:checked]:border-blue-300 dark:has-[:checked]:border-blue-500/50 has-[:checked]:bg-blue-50 dark:has-[:checked]:bg-blue-500/5">
        <input type="radio" name="theme_mode" value="dark" class="mt-1 text-blue-600 focus:ring-blue-500/20" @checked($mode==='dark')>
        <div>
          <div class="font-semibold text-slate-900 dark:text-white">Dark</div>
          <div class="text-sm text-slate-500 dark:text-slate-400">Tampilan gelap.</div>
        </div>
      </label>

      <label class="flex items-start gap-3 rounded-xl border border-slate-200 dark:border-slate-700 p-4 opacity-50 cursor-not-allowed transition-colors">
        <input type="radio" name="theme_mode" value="system" class="mt-1" disabled>
        <div>
          <div class="font-semibold text-slate-900 dark:text-white">System (Soon)</div>
          <div class="text-sm text-slate-500 dark:text-slate-400">Ikuti setting OS.</div>
        </div>
      </label>

      <div class="pt-2 flex justify-end">
        <button class="rounded-lg bg-blue-600 px-6 py-3 text-sm font-semibold text-white shadow-sm hover:bg-blue-700 transition-colors">
          Save Theme
        </button>
      </div>
    </form>
  </div>
</div>
@endsection