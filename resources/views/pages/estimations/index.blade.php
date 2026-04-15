@extends('layouts.app-shell')
@section('title', __('estimation.history_title'))

@section('content')
<div class="space-y-6">

  <div>
    <h1 class="text-4xl font-semibold tracking-tight text-slate-900 dark:text-white transition-colors">
      {{ __('estimation.history_title') }}
    </h1>
    <p class="text-slate-500 dark:text-slate-400 mt-2 transition-colors">
      {{ __('estimation.history_subtitle') }}
    </p>
  </div>

  <div class="bg-white dark:bg-slate-900 rounded-xl shadow-sm border border-slate-200 dark:border-slate-800 overflow-hidden transition-colors duration-300">

    {{-- Filters --}}
    <form method="GET" class="p-6 border-b border-slate-200 dark:border-slate-800 transition-colors">
      <div class="grid grid-cols-1 lg:grid-cols-12 gap-4 items-center">

        {{-- Search --}}
        <div class="lg:col-span-7">
          <div class="relative">
            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 dark:text-slate-500">
              <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none">
                <path d="M21 21l-4.3-4.3" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                <path d="M10.5 18a7.5 7.5 0 1 1 0-15 7.5 7.5 0 0 1 0 15Z" stroke="currentColor" stroke-width="2"/>
              </svg>
            </span>

            <input id="estSearch" name="search" value="{{ $q ?? request('search') }}"
                   placeholder="{{ __('estimation.search_placeholder') }}"
                   class="w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-950/50 pl-10 pr-4 py-3 text-sm text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500/50 dark:focus:ring-blue-500/30 focus:border-blue-500 dark:focus:border-blue-500 transition-colors"/>
          </div>
        </div>

        {{-- Status --}}
        <div class="lg:col-span-3">
          <select name="status"
                  class="w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-950/50 px-4 py-3 text-sm text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500/50 dark:focus:ring-blue-500/30 focus:border-blue-500 dark:focus:border-blue-500 transition-colors">
            <option value="all" {{ (request('status','all')==='all') ? 'selected' : '' }}>{{ __('estimation.all_status') }}</option>
            <option value="approved" {{ (request('status')==='approved') ? 'selected' : '' }}>{{ __('estimation.status_approved') }}</option>
            <option value="pending" {{ (request('status')==='pending') ? 'selected' : '' }}>{{ __('estimation.status_pending') }}</option>
            <option value="rejected" {{ (request('status')==='rejected') ? 'selected' : '' }}>{{ __('estimation.status_rejected') }}</option>
          </select>
        </div>

        {{-- Reset --}}
        <div class="lg:col-span-2 flex justify-end">
          <a href="{{ route('estimations.index') }}"
             class="inline-flex items-center justify-center w-full lg:w-auto rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 px-4 py-3 text-sm font-semibold text-slate-900 dark:text-white hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors">
            {{ __('estimation.reset') }}
          </a>
        </div>

      </div>
    </form>

    {{-- Bulk Delete Form (wrap table) --}}
    <form method="POST" action="{{ route('estimations.bulkDelete') }}" id="bulkEstForm"
          onsubmit="return confirm('Delete selected estimations?');">
      @csrf
      @method('DELETE')

      <div class="flex items-center justify-between px-6 py-4 border-b border-slate-200 dark:border-slate-800 transition-colors">
        <div class="flex items-center gap-3">
          <div class="text-sm font-semibold text-slate-900 dark:text-white">{{ __('estimation.history_title') }}</div>
          <span id="estSelectedCount" class="text-xs text-slate-500 dark:text-slate-400">0 selected</span>
        </div>

        <button type="submit" id="estDeleteBtn" disabled
                class="rounded-lg border border-red-200 dark:border-red-500/30 bg-white dark:bg-slate-900 px-4 py-2 text-sm font-semibold text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-500/10 transition-colors disabled:opacity-40 disabled:cursor-not-allowed">
          Delete Selected
        </button>
      </div>

      {{-- Table --}}
      <div class="overflow-x-auto">
        <table class="w-full">
          <thead class="bg-slate-50 dark:bg-slate-800/50 border-b border-slate-200 dark:border-slate-800 transition-colors">
            <tr>
              <th class="px-6 py-4">
                <input type="checkbox" id="estSelectAll"
                       class="h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500">
              </th>
              <th class="px-6 py-4 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">{{ __('estimation.col_event') ?? 'Event' }}</th>
              <th class="px-6 py-4 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">{{ __('estimation.col_date') ?? 'Date' }}</th>
              <th class="px-6 py-4 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">{{ __('estimation.col_cost') ?? 'Estimated Cost' }}</th>
              <th class="px-6 py-4 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">{{ __('estimation.col_status') ?? 'Status' }}</th>
              <th class="px-6 py-4 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">{{ __('estimation.col_action') ?? 'Action' }}</th>
            </tr>
          </thead>

          <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
            @forelse($items as $e)
              <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors">
                <td class="px-6 py-5">
                  <input type="checkbox" name="ids[]" value="{{ $e->id }}"
                         class="estRowCheckbox h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                </td>

                <td class="px-6 py-5">
                  <div class="text-sm font-semibold text-slate-900 dark:text-white">
                    {{ ucfirst($e->event->event_name ?? $e->event->event_type ?? 'Event') }}
                  </div>
                  <div class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                    {{ ucfirst($e->event->location ?? '-') }} • {{ $e->event->participants ?? '-' }} pax • {{ $e->event->duration ?? '-' }}h
                  </div>
                </td>

                <td class="px-6 py-5">
                  <div class="text-sm text-slate-500 dark:text-slate-400">
                    {{ optional($e->created_at)->format('Y-m-d') }}
                  </div>
                </td>

                <td class="px-6 py-5">
                  <div class="text-sm font-semibold text-slate-900 dark:text-white">
                    Rp {{ number_format($e->total_cost,0,',','.') }}
                  </div>
                </td>

                <td class="px-6 py-5">
                  @switch($e->status)
                    @case('approved')
                      <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold bg-green-100 text-green-700 dark:bg-green-500/20 dark:text-green-400 transition-colors">
                        {{ __('estimation.status_approved') }}
                      </span>
                      @break
                    @case('pending')
                      <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold bg-orange-100 text-orange-700 dark:bg-orange-500/20 dark:text-orange-400 transition-colors">
                        {{ __('estimation.status_pending') }}
                      </span>
                      @break
                    @case('rejected')
                      <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold bg-red-100 text-red-700 dark:bg-red-500/20 dark:text-red-400 transition-colors">
                        {{ __('estimation.status_rejected') }}
                      </span>
                      @break
                    @default
                      <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-400 transition-colors">
                        {{ ucfirst($e->status) }}
                      </span>
                  @endswitch
                </td>

                <td class="px-6 py-5">
                  <a href="{{ route('estimations.show', $e->id) }}"
                     class="inline-flex items-center gap-2 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 px-4 py-2 text-sm font-semibold text-slate-900 dark:text-white hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors">
                    {{ __('estimation.view_details') }}
                  </a>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="6" class="px-6 py-10 text-center text-sm text-slate-500 dark:text-slate-400">
                  {{ __('estimation.no_data') }}
                </td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </form>

    {{-- Pagination --}}
    <div class="px-6 py-4 border-t border-slate-200 dark:border-slate-800 transition-colors">
      {{ $items->links() }}
    </div>
  </div>
</div>

<script>
(function () {
  // auto refresh filter
  const filterForm = document.querySelector('form[method="GET"]');
  if (filterForm) {
    const search = document.getElementById('estSearch');
    let t;
    if (search) {
      search.addEventListener('input', () => {
        clearTimeout(t);
        t = setTimeout(() => filterForm.submit(), 300);
      });
    }

    const status = filterForm.querySelector('select[name="status"]');
    if (status) status.addEventListener('change', () => filterForm.submit());
  }

  // bulk select
  const selectAll = document.getElementById('estSelectAll');
  const countEl = document.getElementById('estSelectedCount');
  const delBtn = document.getElementById('estDeleteBtn');

  const getRows = () => Array.from(document.querySelectorAll('.estRowCheckbox'));

  function refresh() {
    const rows = getRows();
    const checked = rows.filter(r => r.checked).length;

    if (countEl) countEl.textContent = `${checked} selected`;
    if (delBtn) delBtn.disabled = checked === 0;

    if (selectAll) {
      selectAll.checked = rows.length > 0 && rows.every(r => r.checked);
      selectAll.indeterminate = checked > 0 && checked < rows.length;
    }
  }

  if (selectAll) {
    selectAll.addEventListener('change', () => {
      getRows().forEach(r => r.checked = selectAll.checked);
      refresh();
    });
  }

  document.addEventListener('change', (e) => {
    if (e.target.classList && e.target.classList.contains('estRowCheckbox')) {
      refresh();
    }
  });

  refresh();
})();
</script>
@endsection