@extends('pages.settings._layout')
@section('title','Settings - Business')

@section('settings_content')
<div class="space-y-6">
  <div>
    <h2 class="text-xl font-semibold text-slate-900 dark:text-white transition-colors">Business Information</h2>
    <p class="text-sm text-slate-500 dark:text-slate-400 mt-1 transition-colors">Brand name & logo used in PDF invoice.</p>
  </div>

  @if(session('success'))
    <div class="mt-4 rounded-lg border border-green-200 dark:border-green-500/20 bg-green-50 dark:bg-green-500/10 px-4 py-3 text-sm text-green-800 dark:text-green-400 flex items-center gap-2 transition-colors">
      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
      {{ session('success') }}
    </div>
  @endif

  <form method="POST" action="{{ route('settings.business.update') }}" enctype="multipart/form-data"
        class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 p-6 shadow-sm transition-colors duration-300">
    @csrf
    @method('PATCH')

    <div class="grid grid-cols-1 md:grid-cols-12 gap-5">
      <div class="md:col-span-8">
        <label class="block text-sm font-semibold text-slate-900 dark:text-white mb-2 transition-colors">Business Name</label>
        <input name="business_name" value="{{ old('business_name', $businessName) }}"
               class="w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-950/50 px-4 py-3 text-sm text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500/50 dark:focus:ring-blue-500/30 focus:border-blue-500 dark:focus:border-blue-500 transition-colors">
        @error('business_name') <p class="text-xs text-red-600 dark:text-red-400 mt-2">{{ $message }}</p> @enderror
      </div>

      <div class="md:col-span-4">
        <label class="block text-sm font-semibold text-slate-900 dark:text-white mb-2 transition-colors">Current Logo</label>
        <div class="flex items-center gap-3">
          <div class="h-12 w-12 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 flex items-center justify-center overflow-hidden transition-colors">
            <img src="{{ asset($businessLogo) }}" class="h-10 w-10 object-contain" alt="logo">
          </div>
          <div class="text-xs text-slate-500 dark:text-slate-400 transition-colors">
            Used on invoice/PDF
          </div>
        </div>
      </div>

      <div class="md:col-span-12">
        <label class="block text-sm font-semibold text-slate-900 dark:text-white mb-2 transition-colors">Upload New Logo</label>
        <input type="file" name="logo" accept="image/*"
               class="block w-full text-sm text-slate-700 dark:text-slate-300
                      file:mr-4 file:rounded-lg file:border-0 file:bg-slate-900 dark:file:bg-slate-700
                      file:px-4 file:py-2.5 file:text-sm file:font-semibold file:text-white
                      hover:file:bg-slate-800 dark:hover:file:bg-slate-600 transition-all cursor-pointer">
        @error('logo') <p class="text-xs text-red-600 dark:text-red-400 mt-2">{{ $message }}</p> @enderror
      </div>
    </div>

    <div class="mt-6 flex justify-end pt-2">
      <button class="rounded-lg bg-blue-600 px-6 py-3 text-sm font-semibold text-white shadow-sm hover:bg-blue-700 transition-colors">
        Save
      </button>
    </div>
  </form>
</div>
@endsection