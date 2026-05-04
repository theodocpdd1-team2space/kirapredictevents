@extends('layouts.app-shell')
@section('title', __('estimation.history_title'))

@section('content')
@php
  $isOwner = auth()->user()?->isOwner();

  $statusBadge = function ($status) {
    return match($status) {
      'approved' => 'bg-green-100 text-green-700 dark:bg-green-500/20 dark:text-green-400',
      'pending'  => 'bg-orange-100 text-orange-700 dark:bg-orange-500/20 dark:text-orange-400',
      'rejected' => 'bg-red-100 text-red-700 dark:bg-red-500/20 dark:text-red-400',
      'revised'  => 'bg-blue-100 text-blue-700 dark:bg-blue-500/20 dark:text-blue-400',
      default    => 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-400',
    };
  };

  $statusLabel = function ($status) {
    return match($status) {
      'approved' => __('estimation.status_approved'),
      'pending'  => __('estimation.status_pending'),
      'rejected' => __('estimation.status_rejected'),
      'revised'  => 'Revised',
      default    => ucfirst((string) $status),
    };
  };
@endphp

<div class="space-y-6">

  {{-- Header --}}
  <div>
    <h1 class="text-3xl sm:text-4xl font-semibold tracking-tight text-slate-900 dark:text-white transition-colors">
      {{ __('estimation.history_title') }}
    </h1>
    <p class="text-slate-500 dark:text-slate-400 mt-2 transition-colors">
      {{ __('estimation.history_subtitle') }}
    </p>
  </div>

  {{-- Filter --}}
  <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-800 overflow-hidden transition-colors duration-300">
    <form method="GET" class="p-4 sm:p-6 border-b border-slate-200 dark:border-slate-800 transition-colors">
      <div class="grid grid-cols-1 lg:grid-cols-12 gap-4 items-center">

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
                   class="w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-950/50 pl-10 pr-4 py-3 text-sm text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500/50 dark:focus:ring-blue-500/30 focus:border-blue-500 dark:focus:border-blue-500 transition-colors"/>
          </div>
        </div>

        <div class="lg:col-span-3">
          <select name="status"
                  class="w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-950/50 px-4 py-3 text-sm text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500/50 dark:focus:ring-blue-500/30 focus:border-blue-500 dark:focus:border-blue-500 transition-colors">
            <option value="all" {{ (request('status','all')==='all') ? 'selected' : '' }}>{{ __('estimation.all_status') }}</option>
            <option value="approved" {{ (request('status')==='approved') ? 'selected' : '' }}>{{ __('estimation.status_approved') }}</option>
            <option value="pending" {{ (request('status')==='pending') ? 'selected' : '' }}>{{ __('estimation.status_pending') }}</option>
            <option value="rejected" {{ (request('status')==='rejected') ? 'selected' : '' }}>{{ __('estimation.status_rejected') }}</option>
            <option value="revised" {{ (request('status')==='revised') ? 'selected' : '' }}>Revised</option>
          </select>
        </div>

        <div class="lg:col-span-2 flex justify-end">
          <a href="{{ route('estimations.index') }}"
             class="inline-flex items-center justify-center w-full lg:w-auto rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 px-4 py-3 text-sm font-semibold text-slate-900 dark:text-white hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors">
            {{ __('estimation.reset') }}
          </a>
        </div>

      </div>
    </form>

    <form method="POST" action="{{ route('estimations.bulkDelete') }}" id="bulkEstForm"
          onsubmit="return confirm('Delete selected estimations?');">
      @csrf
      @method('DELETE')

      {{-- Toolbar --}}
      <div class="flex items-center justify-between gap-3 px-4 sm:px-6 py-4 border-b border-slate-200 dark:border-slate-800 transition-colors">
        <div class="flex items-center gap-3">
          <div class="text-sm font-semibold text-slate-900 dark:text-white">
            {{ __('estimation.history_title') }}
          </div>

          @if($isOwner)
            <span id="estSelectedCount" class="text-xs text-slate-500 dark:text-slate-400">
              0 selected
            </span>
          @else
            <span class="text-xs text-slate-500 dark:text-slate-400">
              View only
            </span>
          @endif
        </div>

        @if($isOwner)
          <button type="submit" id="estDeleteBtn" disabled
                  class="rounded-xl border border-red-200 dark:border-red-500/30 bg-white dark:bg-slate-900 px-4 py-2 text-sm font-semibold text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-500/10 transition-colors disabled:opacity-40 disabled:cursor-not-allowed">
            Delete Selected
          </button>
        @endif
      </div>

      {{-- Desktop Table --}}
      <div class="hidden md:block overflow-x-auto">
        <table class="w-full">
          <thead class="bg-slate-50 dark:bg-slate-800/50 border-b border-slate-200 dark:border-slate-800 transition-colors">
            <tr>
              @if($isOwner)
                <th class="px-6 py-4">
                  <input type="checkbox" id="estSelectAll"
                         class="h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                </th>
              @endif

              <th class="px-6 py-4 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                {{ __('estimation.col_event') ?? 'Event' }}
              </th>
              <th class="px-6 py-4 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                {{ __('estimation.col_date') ?? 'Date' }}
              </th>
              <th class="px-6 py-4 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                {{ __('estimation.col_cost') ?? 'Estimated Cost' }}
              </th>
              <th class="px-6 py-4 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                {{ __('estimation.col_status') ?? 'Status' }}
              </th>
              <th class="px-6 py-4 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                {{ __('estimation.col_action') ?? 'Action' }}
              </th>
            </tr>
          </thead>

          <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
            @forelse($items as $e)
              @php
                $eventName = ucfirst($e->event->event_name ?? $e->event->event_type ?? 'Event');
                $location = ucfirst($e->event->location ?? '-');
                $participants = $e->event->participants ?? '-';
                $duration = $e->event->duration ?? '-';
              @endphp

              <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors">
                @if($isOwner)
                  <td class="px-6 py-5">
                    <input type="checkbox" name="ids[]" value="{{ $e->id }}"
                           class="estRowCheckbox h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                  </td>
                @endif

                <td class="px-6 py-5">
                  <div class="text-sm font-semibold text-slate-900 dark:text-white">
                    {{ $eventName }}
                  </div>
                  <div class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                    {{ $location }} • {{ $participants }} pax • {{ $duration }}h
                    @if(!empty($e->creator?->name))
                      • Created by {{ $e->creator->name }}
                    @endif
                  </div>
                </td>

                <td class="px-6 py-5">
                  <div class="text-sm text-slate-500 dark:text-slate-400">
                    {{ optional($e->created_at)->format('Y-m-d') }}
                  </div>
                </td>

                <td class="px-6 py-5">
                  <div class="text-sm font-semibold text-slate-900 dark:text-white">
                    Rp {{ number_format((int)$e->total_cost,0,',','.') }}
                  </div>
                </td>

                <td class="px-6 py-5">
                  <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold {{ $statusBadge($e->status) }}">
                    {{ $statusLabel($e->status) }}
                  </span>
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
                <td colspan="{{ $isOwner ? 6 : 5 }}" class="px-6 py-10 text-center text-sm text-slate-500 dark:text-slate-400">
                  {{ __('estimation.no_data') }}
                </td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>

      {{-- Mobile Cards --}}
      <div class="md:hidden divide-y divide-slate-200 dark:divide-slate-800">
        @forelse($items as $e)
          @php
            $eventName = ucfirst($e->event->event_name ?? $e->event->event_type ?? 'Event');
            $location = ucfirst($e->event->location ?? '-');
            $participants = $e->event->participants ?? '-';
            $duration = $e->event->duration ?? '-';
          @endphp

          <div class="p-4">
            <div class="flex items-start gap-3">
              @if($isOwner)
                <div class="pt-1 shrink-0">
                  <input type="checkbox" name="ids[]" value="{{ $e->id }}"
                         class="estRowCheckbox h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                </div>
              @endif

              <div class="min-w-0 flex-1">
                <div class="flex items-start justify-between gap-3">
                  <div class="min-w-0">
                    <h2 class="text-base font-bold leading-snug text-slate-900 dark:text-white break-words">
                      {{ $eventName }}
                    </h2>

                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400 leading-5">
                      {{ $location }} • {{ $participants }} pax • {{ $duration }}h
                    </p>

                    @if(!empty($e->creator?->name))
                      <p class="mt-1 text-xs text-slate-400 dark:text-slate-500">
                        Created by {{ $e->creator->name }}
                      </p>
                    @endif
                  </div>

                  <span class="shrink-0 inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold {{ $statusBadge($e->status) }}">
                    {{ $statusLabel($e->status) }}
                  </span>
                </div>

                <div class="mt-4 grid grid-cols-2 gap-3">
                  <div class="rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950/40 p-3">
                    <div class="text-[11px] font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                      Tanggal
                    </div>
                    <div class="mt-1 text-sm font-semibold text-slate-900 dark:text-white">
                      {{ optional($e->created_at)->format('Y-m-d') }}
                    </div>
                  </div>

                  <div class="rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950/40 p-3">
                    <div class="text-[11px] font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                      Estimasi Biaya
                    </div>
                    <div class="mt-1 text-sm font-semibold text-slate-900 dark:text-white">
                      Rp {{ number_format((int)$e->total_cost,0,',','.') }}
                    </div>
                  </div>
                </div>

                <div class="mt-4">
                  <a href="{{ route('estimations.show', $e->id) }}"
                     class="w-full inline-flex items-center justify-center rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 px-4 py-3 text-sm font-bold text-slate-900 dark:text-white hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors">
                    {{ __('estimation.view_details') }}
                  </a>
                </div>
              </div>
            </div>
          </div>
        @empty
          <div class="px-6 py-10 text-center text-sm text-slate-500 dark:text-slate-400">
            {{ __('estimation.no_data') }}
          </div>
        @endforelse
      </div>
    </form>

    <div class="px-4 sm:px-6 py-4 border-t border-slate-200 dark:border-slate-800 transition-colors">
      {{ $items->links() }}
    </div>
  </div>
</div>

<script>
(function () {
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

  @if($isOwner)
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
  @endif
})();
</script>
@endsection