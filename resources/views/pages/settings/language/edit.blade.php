@extends('pages.settings._layout')
@section('title','Settings - Language')

@section('settings_content')
<div class="space-y-6">
  <div>
    <h2 class="text-xl font-semibold text-slate-900 dark:text-white transition-colors">Language</h2>
    <p class="text-sm text-slate-500 dark:text-slate-400 mt-1 transition-colors">This language will be used for PDF invoice labels.</p>
  </div>

  @if(session('success'))
    <div class="mt-4 rounded-lg border border-green-200 dark:border-green-500/20 bg-green-50 dark:bg-green-500/10 px-4 py-3 text-sm text-green-800 dark:text-green-400 flex items-center gap-2 transition-colors">
      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
      {{ session('success') }}
    </div>
  @endif

  <form method="POST" action="{{ route('settings.language.update') }}"
        class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 p-6 shadow-sm transition-colors duration-300">
    @csrf
    @method('PATCH')

    <label class="block text-sm font-semibold text-slate-900 dark:text-white mb-2 transition-colors">Select Language</label>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
      <label class="flex items-center gap-3 rounded-xl border border-slate-200 dark:border-slate-700 p-4 hover:bg-slate-50 dark:hover:bg-slate-800/50 cursor-pointer transition-colors has-[:checked]:border-blue-300 dark:has-[:checked]:border-blue-500/50 has-[:checked]:bg-blue-50 dark:has-[:checked]:bg-blue-500/5">
        <input type="radio" name="language" value="id" @checked($language==='id')>
        <div>
          <div class="font-semibold text-slate-900 dark:text-white">Bahasa Indonesia</div>
          <div class="text-xs text-slate-500 dark:text-slate-400">Label PDF: “Rincian Biaya”, “Total”, dll.</div>
        </div>
      </label>

      <label class="flex items-center gap-3 rounded-xl border border-slate-200 dark:border-slate-700 p-4 hover:bg-slate-50 dark:hover:bg-slate-800/50 cursor-pointer transition-colors has-[:checked]:border-blue-300 dark:has-[:checked]:border-blue-500/50 has-[:checked]:bg-blue-50 dark:has-[:checked]:bg-blue-500/5">
        <input type="radio" name="language" value="en" @checked($language==='en')>
        <div>
          <div class="font-semibold text-slate-900 dark:text-white">English</div>
          <div class="text-xs text-slate-500 dark:text-slate-400">PDF labels in English.</div>
        </div>
      </label>
    </div>

    <div class="mt-6 flex justify-end">
      <button class="rounded-lg bg-blue-600 px-6 py-3 text-sm font-semibold text-white shadow-sm hover:bg-blue-700 transition-colors">
        Save
      </button>
    </div>
  </form>
</div>
@endsection