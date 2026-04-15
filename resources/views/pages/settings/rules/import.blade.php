@extends('pages.settings._layout')
@section('title','Settings - Rules')

@section('content')
<div class="space-y-6 max-w-3xl">
  <div>
    <h1 class="text-4xl font-semibold tracking-tight text-slate-900 dark:text-white transition-colors">Import Rules (CSV)</h1>
    <p class="text-slate-500 dark:text-slate-400 mt-2 transition-colors">Upload CSV to insert rules into knowledge base.</p>
  </div>

  <div class="bg-white dark:bg-slate-900 rounded-xl shadow-sm border border-slate-200 dark:border-slate-800 p-8 transition-colors duration-300">
    <form method="POST" action="{{ route('settings.rules.import') }}" enctype="multipart/form-data" class="space-y-6">
      @csrf

      <div>
        <label class="block text-sm font-semibold text-slate-900 dark:text-white mb-3 transition-colors">CSV File</label>
        <input type="file" name="csv" accept=".csv,text/csv"
               class="block w-full text-sm text-slate-700 dark:text-slate-300
                      file:mr-4 file:rounded-lg file:border-0 file:bg-slate-900 dark:file:bg-slate-700
                      file:px-4 file:py-2.5 file:text-sm file:font-semibold file:text-white
                      hover:file:bg-slate-800 dark:hover:file:bg-slate-600 transition-all cursor-pointer">
        @error('csv') <p class="text-xs text-red-600 dark:text-red-400 mt-2">{{ $message }}</p> @enderror
      </div>

      <div class="rounded-lg bg-slate-50 dark:bg-slate-800/50 border border-slate-100 dark:border-slate-800 p-4 text-xs text-slate-500 dark:text-slate-400 leading-relaxed transition-colors">
        <span class="font-bold text-slate-700 dark:text-slate-300 uppercase block mb-1">Header wajib:</span>
        <code class="font-mono text-blue-600 dark:text-blue-400">condition_field, operator, value, action, category, priority, is_active</code>
      </div>

      <div class="flex justify-end gap-3 pt-2">
        <a href="{{ route('settings.rules.index') }}"
           class="rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 px-6 py-3 text-sm font-semibold text-slate-900 dark:text-white hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors">
          Back
        </a>
        <button class="rounded-lg bg-blue-600 px-6 py-3 text-sm font-semibold text-white shadow-sm hover:bg-blue-700 transition-colors">
          Import
        </button>
      </div>
    </form>
  </div>
</div>
@endsection