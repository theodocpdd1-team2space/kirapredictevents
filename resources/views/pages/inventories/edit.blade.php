@extends('layouts.app-shell')
@section('title','Edit Equipment')

@section('content')
<div class="space-y-6 max-w-5xl">
  <div>
    <h1 class="text-4xl font-semibold tracking-tight text-slate-900 dark:text-white transition-colors">Edit Equipment</h1>
    <p class="text-slate-500 dark:text-slate-400 mt-2 transition-colors">Update equipment information</p>
  </div>

  <div class="bg-white dark:bg-slate-900 rounded-xl shadow-sm border border-slate-200 dark:border-slate-800 overflow-hidden transition-colors duration-300">
    <div class="px-8 py-5 border-b border-slate-200 dark:border-slate-800 transition-colors">
      <h2 class="text-lg font-semibold text-slate-900 dark:text-white">Equipment Information</h2>
    </div>

    <form method="POST" action="{{ route('inventories.update', $inventory->id) }}" enctype="multipart/form-data" class="p-8">
      @csrf
      @method('PUT')

      @include('pages.inventories._form', [
        'inventory' => $inventory,
        'categoryOptions' => $categoryOptions ?? [],
        'statusOptions' => $statusOptions ?? ['active','maintenance','inactive'],
      ])

      <div class="mt-8 flex justify-end gap-3 pt-2">
        <a href="{{ route('inventories.index') }}"
           class="rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 px-6 py-3 text-sm font-semibold text-slate-900 dark:text-white hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors">
          Cancel
        </a>
        <button class="rounded-lg bg-blue-600 px-6 py-3 text-sm font-semibold text-white hover:bg-blue-700 transition-colors shadow-sm">
          Update
        </button>
      </div>
    </form>
  </div>
</div>
@endsection