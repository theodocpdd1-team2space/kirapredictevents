@extends('layouts.app-shell')
@section('title','Add Rule')

@section('content')
<div class="space-y-6 max-w-5xl">
  <div>
    <h1 class="text-4xl font-semibold tracking-tight text-slate-900 dark:text-white transition-colors">Add Rule</h1>
    <p class="text-slate-500 dark:text-slate-400 mt-2 transition-colors">Create a new knowledge base rule.</p>
  </div>

  <div class="bg-white dark:bg-slate-900 rounded-xl shadow-sm border border-slate-200 dark:border-slate-800 overflow-hidden transition-colors duration-300">
    <form method="POST" action="{{ route('settings.rules.store') }}" class="p-8">
      @csrf
      @include('pages.settings.rules._form')

      <div class="mt-8 flex justify-end gap-3 pt-2">
        <a href="{{ route('settings.rules.index') }}"
           class="rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 px-6 py-3 text-sm font-semibold text-slate-900 dark:text-white hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors">
          Cancel
        </a>
        <button class="rounded-lg bg-blue-600 px-6 py-3 text-sm font-semibold text-white shadow-sm hover:bg-blue-700 transition-colors">
          Save
        </button>
      </div>
    </form>
  </div>
</div>
@endsection