@extends('layouts.app-shell')
@section('title', __('rules.title'))

@section('content')
<div class="space-y-6">

  {{-- Header --}}
  <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
    <div>
      <h1 class="text-3xl md:text-4xl font-semibold tracking-tight text-slate-900 dark:text-slate-100">
        {{ __('rules.title') }}
      </h1>
      <p class="text-slate-500 dark:text-slate-300 mt-2">
        {{ __('rules.subtitle') }}
      </p>
    </div>

    <div class="flex flex-wrap items-center gap-2">
      <a href="{{ route('settings.rules.import.form') }}"
         class="inline-flex items-center justify-center rounded-lg border border-slate-200 dark:border-slate-700
                bg-white dark:bg-slate-900 px-4 py-2 text-sm font-semibold text-slate-900 dark:text-slate-100
                hover:bg-slate-50 dark:hover:bg-slate-800">
        {{ __('rules.import_csv') }}
      </a>


      <a href="{{ route('settings.rules.create') }}"
         class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white
                hover:bg-blue-700 shadow-sm">
        + {{ __('rules.add_rule') }}
      </a>
    </div>
  </div>

  {{-- Tabs --}}
  @includeIf('pages.settings._tabs', ['active' => 'rules'])

  {{-- Alerts --}}
  @if(session('success'))
    <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
      {{ session('success') }}
    </div>
  @endif
  @if($errors->any())
    <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
      {{ $errors->first() }}
    </div>
  @endif

  {{-- Filter --}}
  <form method="GET" class="bg-white dark:bg-slate-900 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 p-4">
    <div class="grid grid-cols-1 md:grid-cols-12 gap-3 items-center">
      <div class="md:col-span-9">
        <div class="relative">
          <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">
            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none">
              <path d="M21 21l-4.3-4.3" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
              <path d="M10.5 18a7.5 7.5 0 1 1 0-15 7.5 7.5 0 0 1 0 15Z" stroke="currentColor" stroke-width="2"/>
            </svg>
          </span>

          <input name="search" value="{{ $search ?? request('search') }}"
                 placeholder="{{ __('rules.search_placeholder') }}"
                 class="w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-950
                        pl-10 pr-4 py-3 text-sm text-slate-900 dark:text-slate-100
                        placeholder-slate-400 dark:placeholder-slate-500
                        focus:outline-none focus:ring-2 focus:ring-blue-500/30 focus:border-blue-500">
        </div>
      </div>

      <div class="md:col-span-3 flex justify-end gap-2">
        <a href="{{ route('settings.rules.index') }}"
           class="inline-flex items-center justify-center rounded-lg border border-slate-200 dark:border-slate-700
                  bg-white dark:bg-slate-900 px-4 py-3 text-sm font-semibold text-slate-900 dark:text-slate-100
                  hover:bg-slate-50 dark:hover:bg-slate-800">
          {{ __('common.reset') ?? 'Reset' }}
        </a>

        <button type="submit"
                class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-4 py-3 text-sm font-semibold text-white hover:bg-blue-700">
          {{ __('common.filter') ?? 'Filter' }}
        </button>
      </div>
    </div>
  </form>

  {{-- ✅ BULK DELETE FORM (TERPISAH, TIDAK MEMBUNGKUS TABLE) --}}
  <form id="bulkDeleteRulesForm"
        method="POST"
        action="{{ route('settings.rules.bulkDelete') }}"
        onsubmit="return confirm('{{ __('rules.confirm_bulk_delete') ?? 'Delete selected rules?' }}');">
    @csrf
    @method('DELETE')
  </form>

  {{-- Table Card --}}
  <div class="bg-white dark:bg-slate-900 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden">

    <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-700 flex items-center justify-between">
      <div class="flex items-center gap-3 text-sm text-slate-500 dark:text-slate-300">
        <span>
          {{ __('rules.total') ?? 'Total rules' }}:
          <span class="font-semibold text-slate-900 dark:text-slate-100">
            {{ method_exists($rules, 'total') ? $rules->total() : $rules->count() }}
          </span>
        </span>

        <span id="rulesSelectedCount" class="text-xs text-slate-400 dark:text-slate-400">
          0 selected
        </span>
      </div>

      {{-- ✅ tombol submit bulk delete: pakai form attribute --}}
      <button type="submit"
              form="bulkDeleteRulesForm"
              id="rulesDeleteBtn"
              disabled
              class="inline-flex items-center justify-center rounded-lg border border-red-200 dark:border-red-500/30
                     bg-white dark:bg-slate-900 px-4 py-2 text-sm font-semibold text-red-600 dark:text-red-400
                     hover:bg-red-50 dark:hover:bg-red-500/10
                     disabled:opacity-40 disabled:cursor-not-allowed">
        {{ __('rules.delete_selected') ?? 'Delete Selected' }}
      </button>
    </div>

    <div class="overflow-x-auto">
      <table class="w-full text-sm">
        <thead class="bg-slate-50 dark:bg-slate-950 border-b border-slate-200 dark:border-slate-700">
          <tr>
            <th class="px-4 py-3 text-left">
              <input id="checkAllRules" type="checkbox"
                     class="h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500">
            </th>

            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-300 uppercase">
              {{ __('rules.col_field') }}
            </th>
            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-300 uppercase">
              {{ __('rules.col_operator') }}
            </th>
            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-300 uppercase">
              {{ __('rules.col_value') }}
            </th>
            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-300 uppercase">
              {{ __('rules.col_category') }}
            </th>
            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-300 uppercase">
              {{ __('rules.col_priority') }}
            </th>
            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-300 uppercase">
              {{ __('rules.col_active') }}
            </th>
            <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500 dark:text-slate-300 uppercase">
              {{ __('rules.col_action') }}
            </th>
          </tr>
        </thead>

        <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
          @forelse($rules as $r)
            <tr class="hover:bg-slate-50 dark:hover:bg-slate-950/60">
              <td class="px-4 py-3">
                {{-- ✅ checkbox “ikut” bulk form --}}
                <input type="checkbox"
                       name="ids[]"
                       value="{{ $r->id }}"
                       form="bulkDeleteRulesForm"
                       class="ruleCheck h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500">
              </td>

              <td class="px-4 py-3 font-semibold text-slate-900 dark:text-slate-100">
                {{ $r->condition_field }}
              </td>

              <td class="px-4 py-3 text-slate-700 dark:text-slate-200">
                {{ $r->operator }}
              </td>

              <td class="px-4 py-3 text-slate-700 dark:text-slate-200">
                <span class="inline-flex rounded-full bg-slate-100 dark:bg-slate-800 px-3 py-1 text-xs font-semibold">
                  {{ $r->value }}
                </span>
              </td>

              <td class="px-4 py-3 text-slate-700 dark:text-slate-200">
                {{ $r->category ?? '-' }}
              </td>

              <td class="px-4 py-3 text-slate-700 dark:text-slate-200">
                {{ $r->priority }}
              </td>

              <td class="px-4 py-3">
                {{-- toggle form (PATCH) aman karena TIDAK nested --}}
                <form method="POST" action="{{ route('settings.rules.toggle', $r->id) }}">
                  @csrf
                  @method('PATCH')
                  <button type="submit"
                          class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold
                            {{ $r->is_active ? 'bg-green-100 text-green-700 dark:bg-green-500/15 dark:text-green-300' : 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300' }}">
                    {{ $r->is_active ? __('rules.active') : __('rules.inactive') }}
                  </button>
                </form>
              </td>

              <td class="px-4 py-3">
                <div class="flex justify-end gap-2">
                  <a href="{{ route('settings.rules.edit', $r->id) }}"
                     class="inline-flex items-center justify-center rounded-lg border border-slate-200 dark:border-slate-700
                            bg-white dark:bg-slate-900 px-3 py-2 text-sm font-semibold text-slate-900 dark:text-slate-100
                            hover:bg-slate-50 dark:hover:bg-slate-800">
                    {{ __('rules.edit') }}
                  </a>

                  <form method="POST" action="{{ route('settings.rules.destroy', $r->id) }}"
                        onsubmit="return confirm('{{ __('rules.confirm_delete') ?? 'Delete this rule?' }}');">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                            class="inline-flex items-center justify-center rounded-lg border border-red-200 dark:border-red-500/30
                                   bg-white dark:bg-slate-900 px-3 py-2 text-sm font-semibold text-red-600 dark:text-red-400
                                   hover:bg-red-50 dark:hover:bg-red-500/10">
                      {{ __('rules.delete') }}
                    </button>
                  </form>
                </div>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="8" class="px-6 py-10 text-center text-sm text-slate-500 dark:text-slate-300">
                {{ __('rules.no_data') }}
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    @if($rules instanceof \Illuminate\Pagination\AbstractPaginator)
      <div class="px-6 py-4 border-t border-slate-200 dark:border-slate-700">
        {{ $rules->links() }}
      </div>
    @endif
  </div>
</div>

<script>
(function () {
  const selectAll = document.getElementById('checkAllRules');
  const deleteBtn = document.getElementById('rulesDeleteBtn');
  const countEl = document.getElementById('rulesSelectedCount');

  const rows = () => Array.from(document.querySelectorAll('.ruleCheck'));

  function refresh() {
    const r = rows();
    const checked = r.filter(x => x.checked).length;

    if (deleteBtn) deleteBtn.disabled = checked === 0;
    if (countEl) countEl.textContent = `${checked} selected`;

    if (selectAll) {
      selectAll.checked = r.length > 0 && r.every(x => x.checked);
      selectAll.indeterminate = checked > 0 && checked < r.length;
    }
  }

  if (selectAll) {
    selectAll.addEventListener('change', () => {
      rows().forEach(x => x.checked = selectAll.checked);
      refresh();
    });
  }

  document.addEventListener('change', (e) => {
    if (e.target && e.target.classList && e.target.classList.contains('ruleCheck')) {
      refresh();
    }
  });

  refresh();
})();
</script>
@endsection