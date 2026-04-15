@extends('layouts.app-shell')
@section('title', __('dashboard.title'))

@section('content')
  <div class="space-y-6">

    {{-- Header --}}
    <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
      <div>
        <h1 class="text-4xl font-semibold tracking-tight text-slate-900 dark:text-white transition-colors">
          {{ __('dashboard.title') }}
        </h1>
        <p class="text-slate-500 dark:text-slate-400 mt-2 transition-colors">
          {{ __('dashboard.subtitle') }}
        </p>
      </div>

      <div class="flex flex-col sm:flex-row gap-2 sm:gap-3 w-full lg:w-auto">
        <a href="{{ route('events.create') }}"
           class="w-full sm:w-auto inline-flex items-center justify-center gap-2 rounded-full bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-700 transition-colors">
          {{ __('dashboard.actions.new_estimation') }}
        </a>
        <a href="{{ route('estimations.index') }}"
           class="w-full sm:w-auto inline-flex items-center justify-center rounded-full border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 px-4 py-2 text-sm font-semibold text-slate-900 dark:text-white hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors">
          {{ __('dashboard.actions.history') }}
        </a>
      </div>
    </div>

    {{-- Summary Cards --}}
    <div class="grid grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-4 sm:gap-6">

      {{-- Total Estimations --}}
      <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden hover:shadow-md dark:hover:border-slate-700 transition-all duration-300">
        <div class="h-1 bg-blue-600"></div>
        <div class="p-4 sm:p-6">
          <div class="flex items-start justify-between gap-3">
            <div>
              <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 leading-5 transition-colors">
                {{ __('dashboard.cards.total_estimations') }}
              </p>
              <p class="text-3xl sm:text-4xl font-semibold text-slate-900 dark:text-white mt-3 sm:mt-4 transition-colors">
                {{ $totalEstimations }}
              </p>
            </div>
            <div class="bg-blue-50 dark:bg-blue-500/10 p-2.5 sm:p-3 rounded-lg transition-colors">
              <svg class="w-5 h-5 sm:w-6 sm:h-6 text-blue-600 dark:text-blue-400" viewBox="0 0 24 24" fill="none">
                <path d="M7 3h10v18H7V3Z" stroke="currentColor" stroke-width="2"/>
                <path d="M9 7h6M9 11h6M9 15h4" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
              </svg>
            </div>
          </div>
          <div class="hidden lg:block mt-4 h-10 rounded-lg bg-blue-50/50 dark:bg-blue-500/5 transition-colors"></div>
        </div>
      </div>

      {{-- Pending --}}
      <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden hover:shadow-md dark:hover:border-slate-700 transition-all duration-300">
        <div class="h-1 bg-orange-500"></div>
        <div class="p-4 sm:p-6">
          <div class="flex items-start justify-between gap-3">
            <div>
              <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 leading-5 transition-colors">
                {{ __('dashboard.cards.pending_estimations') }}
              </p>
              <p class="text-3xl sm:text-4xl font-semibold text-slate-900 dark:text-white mt-3 sm:mt-4 transition-colors">
                {{ $pendingEstimations }}
              </p>
            </div>
            <div class="bg-orange-50 dark:bg-orange-500/10 p-2.5 sm:p-3 rounded-lg transition-colors">
              <svg class="w-5 h-5 sm:w-6 sm:h-6 text-orange-600 dark:text-orange-400" viewBox="0 0 24 24" fill="none">
                <path d="M12 8v5l3 2" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                <path d="M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" stroke="currentColor" stroke-width="2"/>
              </svg>
            </div>
          </div>
          <div class="hidden lg:block mt-4 h-10 rounded-lg bg-orange-50/60 dark:bg-orange-500/5 transition-colors"></div>
        </div>
      </div>

      {{-- Approved --}}
      <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden hover:shadow-md dark:hover:border-slate-700 transition-all duration-300">
        <div class="h-1 bg-green-600"></div>
        <div class="p-4 sm:p-6">
          <div class="flex items-start justify-between gap-3">
            <div>
              <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 leading-5 transition-colors">
                {{ __('dashboard.cards.approved_estimations') }}
              </p>
              <p class="text-3xl sm:text-4xl font-semibold text-slate-900 dark:text-white mt-3 sm:mt-4 transition-colors">
                {{ $approvedEstimations }}
              </p>
            </div>
            <div class="bg-green-50 dark:bg-green-500/10 p-2.5 sm:p-3 rounded-lg transition-colors">
              <svg class="w-5 h-5 sm:w-6 sm:h-6 text-green-600 dark:text-green-400" viewBox="0 0 24 24" fill="none">
                <path d="M20 6 9 17l-5-5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
              </svg>
            </div>
          </div>
          <div class="hidden lg:block mt-4 h-10 rounded-lg bg-green-50/60 dark:bg-green-500/5 transition-colors"></div>
        </div>
      </div>

      {{-- Inventory --}}
      <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden hover:shadow-md dark:hover:border-slate-700 transition-all duration-300">
        <div class="h-1 bg-purple-600"></div>
        <div class="p-4 sm:p-6">
          <div class="flex items-start justify-between gap-3">
            <div>
              <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 leading-5 transition-colors">
                {{ __('dashboard.cards.inventory_title') }}
              </p>

              <p class="text-3xl sm:text-4xl font-semibold text-slate-900 dark:text-white mt-3 sm:mt-4 transition-colors">
                {{ number_format($inventoryUnits) }}
              </p>

              <p class="text-[11px] sm:text-xs text-slate-500 dark:text-slate-400 mt-2 transition-colors">
                {{ __('dashboard.cards.inventory_types', ['count' => number_format($inventoryItems)]) }}
              </p>
            </div>

            <div class="bg-purple-50 dark:bg-purple-500/10 p-2.5 sm:p-3 rounded-lg transition-colors">
              <svg class="w-5 h-5 sm:w-6 sm:h-6 text-purple-600 dark:text-purple-400" viewBox="0 0 24 24" fill="none">
                <path d="M12 2 21 7v10l-9 5-9-5V7l9-5Z" stroke="currentColor" stroke-width="2"/>
                <path d="M12 22V12" stroke="currentColor" stroke-width="2"/>
                <path d="M21 7l-9 5-9-5" stroke="currentColor" stroke-width="2"/>
              </svg>
            </div>
          </div>

          <div class="hidden lg:block mt-4 h-10 rounded-lg bg-purple-50/60 dark:bg-purple-500/5 transition-colors"></div>
        </div>
      </div>

      {{-- Accuracy --}}
      <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden hover:shadow-md dark:hover:border-slate-700 transition-all duration-300 col-span-2 xl:col-span-1">
        <div class="h-1 bg-teal-600"></div>
        <div class="p-4 sm:p-6">
          <div class="flex items-start justify-between gap-3">
            <div>
              <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 leading-5 transition-colors">
                {{ __('dashboard.cards.accuracy_title') }}
              </p>

              <p class="text-3xl sm:text-4xl font-semibold text-slate-900 dark:text-white mt-3 sm:mt-4 transition-colors">
                {{ $accuracy }}%
              </p>

              <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-2 leading-4 transition-colors">
                {{ __('dashboard.cards.accuracy_note') }}
              </p>

              <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-1 leading-4 transition-colors">
                {{ __('dashboard.cards.accuracy_formula') }}
              </p>
            </div>

            <div class="bg-teal-50 dark:bg-teal-500/10 p-2.5 sm:p-3 rounded-lg transition-colors">
              <svg class="w-5 h-5 sm:w-6 sm:h-6 text-teal-600 dark:text-teal-400" viewBox="0 0 24 24" fill="none">
                <path d="M4 14l6-6 4 4 6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M20 7v6h-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
              </svg>
            </div>
          </div>

          <div class="hidden lg:block mt-4 h-10 rounded-lg bg-teal-50/60 dark:bg-teal-500/5 transition-colors"></div>
        </div>
      </div>

    </div>

    {{-- Recent table --}}
    <div class="bg-white dark:bg-slate-900 rounded-xl shadow-sm border border-slate-200 dark:border-slate-800 overflow-hidden transition-colors duration-300">
      <div class="px-4 sm:px-6 py-4 sm:py-5 border-b border-slate-200 dark:border-slate-800 transition-colors">
        <h2 class="text-lg font-semibold text-slate-900 dark:text-white transition-colors">
          {{ __('dashboard.table.title') }}
        </h2>
      </div>

      <div class="overflow-x-auto">
        <table class="w-full">
          <thead class="bg-slate-50 dark:bg-slate-800/50 border-b border-slate-200 dark:border-slate-800 transition-colors">
            <tr>
              <th class="px-4 sm:px-6 py-3 sm:py-4 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                {{ __('dashboard.table.event_name') }}
              </th>
              <th class="px-4 sm:px-6 py-3 sm:py-4 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                {{ __('dashboard.table.date') }}
              </th>
              <th class="px-4 sm:px-6 py-3 sm:py-4 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                {{ __('dashboard.table.estimated_cost') }}
              </th>
              <th class="px-4 sm:px-6 py-3 sm:py-4 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                {{ __('dashboard.table.status') }}
              </th>
            </tr>
          </thead>

          <tbody class="divide-y divide-slate-200 dark:divide-slate-800 transition-colors">
            @forelse($recentEstimations as $e)
              @php
                $statusLabel = __('dashboard.status.' . ($e->status ?? 'pending'));
                $eventLabel = ucfirst($e->event->event_name ?? $e->event->event_type ?? 'Event'); // sama kayak history
              @endphp

              <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors">
                <td class="px-4 sm:px-6 py-4 sm:py-5 whitespace-nowrap">
                  <div class="text-sm font-semibold text-slate-900 dark:text-white">
                    {{ $eventLabel }}
                  </div>
                </td>

                <td class="px-4 sm:px-6 py-4 sm:py-5 whitespace-nowrap">
                  <div class="text-sm text-slate-500 dark:text-slate-400">
                    {{ optional($e->created_at)->format('Y-m-d') }}
                  </div>
                </td>

                <td class="px-4 sm:px-6 py-4 sm:py-5 whitespace-nowrap">
                  <div class="text-sm font-semibold text-slate-900 dark:text-white">
                    Rp {{ number_format($e->total_cost,0,',','.') }}
                  </div>
                </td>

                <td class="px-4 sm:px-6 py-4 sm:py-5 whitespace-nowrap">
                  @if($e->status === 'approved')
                    <span class="inline-flex items-center rounded-full bg-green-100 dark:bg-green-500/20 px-3 py-1 text-xs font-semibold text-green-700 dark:text-green-400 transition-colors">
                      {{ $statusLabel }}
                    </span>
                  @elseif($e->status === 'pending')
                    <span class="inline-flex items-center rounded-full bg-orange-100 dark:bg-orange-500/20 px-3 py-1 text-xs font-semibold text-orange-700 dark:text-orange-400 transition-colors">
                      {{ $statusLabel }}
                    </span>
                  @else
                    <span class="inline-flex items-center rounded-full bg-slate-100 dark:bg-slate-800 px-3 py-1 text-xs font-semibold text-slate-700 dark:text-slate-400 transition-colors">
                      {{ $statusLabel }}
                    </span>
                  @endif
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="4" class="px-6 py-10 text-center text-sm text-slate-500 dark:text-slate-400">
                  {{ __('dashboard.table.empty') }}
                </td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>

  </div>
@endsection