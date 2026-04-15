@extends('layouts.app-shell')
@section('title','Import Inventory')

@section('content')
<div class="space-y-6 max-w-5xl">
  <div>
    <h1 class="text-4xl font-semibold tracking-tight text-slate-900 dark:text-white transition-colors">Import Inventory (CSV)</h1>
    <p class="text-slate-500 dark:text-slate-400 mt-2 transition-colors">Upload CSV untuk menambah/update inventory sekaligus.</p>
  </div>

  <div class="bg-white dark:bg-slate-900 rounded-xl shadow-sm border border-slate-200 dark:border-slate-800 overflow-hidden transition-colors duration-300">
    <div class="px-8 py-5 border-b border-slate-200 dark:border-slate-800 transition-colors">
      <h2 class="text-lg font-semibold text-slate-900 dark:text-white">Upload File</h2>
    </div>

    <div class="p-8 space-y-6">
      <div class="rounded-lg border border-blue-200 dark:border-blue-500/20 bg-blue-50 dark:bg-blue-500/10 px-4 py-3 text-sm text-blue-800 dark:text-blue-300 transition-colors">
        <b class="text-blue-900 dark:text-blue-200">Header CSV wajib:</b> 
        <code class="bg-blue-100 dark:bg-blue-900/50 px-1.5 py-0.5 rounded text-blue-900 dark:text-blue-200 font-mono text-xs ml-1 border border-blue-200 dark:border-blue-800">equipment_name,category,quantity,price,status</code>
        <br>
        <div class="mt-1.5">
          <b class="text-blue-900 dark:text-blue-200">Status valid:</b> available, used, active, inactive, maintenance
        </div>
      </div>

      @if($errors->any())
        <div class="rounded-lg border border-red-200 dark:border-red-500/20 bg-red-50 dark:bg-red-500/10 px-4 py-3 text-sm text-red-800 dark:text-red-400 flex items-center gap-2 transition-colors">
          <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
          {{ $errors->first() }}
        </div>
      @endif

      <form method="POST" action="{{ route('inventories.import') }}" enctype="multipart/form-data" class="space-y-5">
        @csrf

        <input type="file" name="csv" accept=".csv,text/csv"
               class="block w-full text-sm text-slate-700 dark:text-slate-300 file:mr-4 file:rounded-lg file:border-0 file:bg-slate-900 dark:file:bg-slate-700 file:px-4 file:py-2.5 file:text-sm file:font-semibold file:text-white hover:file:bg-slate-800 dark:hover:file:bg-slate-600 transition-all cursor-pointer">

        <div class="flex justify-end gap-3 pt-2">
          <a href="{{ route('inventories.index') }}"
             class="rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 px-6 py-3 text-sm font-semibold text-slate-900 dark:text-white hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors">
            Back
          </a>
          <button class="rounded-lg bg-blue-600 px-6 py-3 text-sm font-semibold text-white hover:bg-blue-700 transition-colors shadow-sm">
            Import
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection