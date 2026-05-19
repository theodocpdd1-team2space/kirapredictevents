@extends('layouts.app-shell')
@section('title', __('dashboard.title'))

@section('content')
@php
  use App\Models\Inventory;
  use App\Models\Rule;

  $user = auth()->user();
  $isOwner = $user?->isOwner();

  $tenantId = $user?->tenant_id;

  $inventoryCountForEstimation = $tenantId
      ? Inventory::where('tenant_id', $tenantId)->count()
      : 0;

  $ruleCountForEstimation = $tenantId
      ? Rule::where('tenant_id', $tenantId)->count()
      : 0;

  $canCreateEstimation = $inventoryCountForEstimation > 0 && $ruleCountForEstimation > 0;

  // Minimalist & Soft Badges
  $statusBadge = function ($status) {
      return match($status) {
          'approved' => 'bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400',
          'pending'  => 'bg-amber-50 text-amber-600 dark:bg-amber-500/10 dark:text-amber-400',
          'revised'  => 'bg-blue-50 text-blue-600 dark:bg-blue-500/10 dark:text-blue-400',
          'rejected' => 'bg-rose-50 text-rose-600 dark:bg-rose-500/10 dark:text-rose-400',
          default    => 'bg-slate-50 text-slate-600 dark:bg-slate-500/10 dark:text-slate-400',
      };
  };

  $statusLabel = function ($status) {
      return match($status) {
          'approved' => 'Disetujui',
          'pending'  => 'Pending',
          'revised'  => 'Direvisi',
          'rejected' => 'Ditolak',
          default    => ucfirst((string)$status),
      };
  };
@endphp

<!-- Main Container -->
<div class="space-y-8 animate-in fade-in slide-in-from-bottom-4 duration-500 pb-10">

  {{-- Clean Header Section --}}
  <div class="flex flex-col gap-6 md:flex-row md:items-end md:justify-between px-2">
    <div>
      <div class="inline-flex items-center gap-2 rounded-full bg-blue-50 dark:bg-blue-500/10 px-3 py-1 text-xs font-semibold text-blue-600 dark:text-blue-400 mb-4">
        <span class="relative flex h-2 w-2">
          <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-blue-400 opacity-75"></span>
          <span class="relative inline-flex rounded-full h-2 w-2 bg-blue-600"></span>
        </span>
        Kira Decision Support System
      </div>

      <h1 class="text-3xl md:text-4xl font-bold tracking-tight text-slate-900 dark:text-white">
        Halo, {{ explode(' ', $user->name ?? 'Tim')[0] }}! 👋
      </h1>

      <p class="mt-2 text-sm text-slate-500 dark:text-slate-400 max-w-xl">
        Berikut ringkasan performa estimasi, revenue, dan kesehatan inventory hari ini.
      </p>
    </div>

    <div class="flex items-center gap-3 w-full md:w-auto">
      <a href="{{ route('estimations.index') }}"
         class="flex-1 md:flex-none inline-flex items-center justify-center gap-2 rounded-2xl bg-white px-5 py-3 text-sm font-semibold text-slate-700 shadow-[0_2px_10px_-3px_rgba(0,0,0,0.05)] transition-all duration-300 hover:shadow-md dark:bg-slate-800 dark:text-slate-200">
        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        History
      </a>

      <a href="{{ $canCreateEstimation ? route('events.create') : route('estimations.locked') }}"
         title="{{ $canCreateEstimation ? 'Buat estimasi baru' : 'Inventory dan rules masih kosong. Silakan isi terlebih dahulu.' }}"
         class="flex-1 md:flex-none inline-flex items-center justify-center gap-2 rounded-2xl px-6 py-3 text-sm font-semibold shadow-lg transition-all duration-300
         {{ $canCreateEstimation
            ? 'bg-blue-600 text-white shadow-blue-500/30 hover:bg-blue-700 hover:-translate-y-0.5'
            : 'cursor-not-allowed bg-slate-200 text-slate-400 shadow-slate-200/30 dark:bg-slate-800 dark:text-slate-500 dark:shadow-slate-950/20'
         }}">
        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M12 4v16m8-8H4"/>
        </svg>

        <span>Buat Estimasi</span>

        @unless($canCreateEstimation)
          <span class="hidden sm:inline-flex rounded-full bg-white/60 px-2 py-0.5 text-[10px] font-black uppercase tracking-wider text-slate-500 dark:bg-slate-700/60 dark:text-slate-400">
            Locked
          </span>
        @endunless
      </a>
    </div>
  </div>

  @unless($canCreateEstimation)
    <div class="mx-2 rounded-[24px] border border-blue-200/70 bg-white p-5 shadow-sm ring-1 ring-blue-100/80 transition-colors dark:border-blue-500/20 dark:bg-slate-900 dark:ring-blue-500/10">
      <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div class="flex gap-4">
          <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-blue-50 text-blue-600 dark:bg-blue-500/10 dark:text-blue-300">
            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 9v4m0 4h.01M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/>
            </svg>
          </div>

          <div>
            <h2 class="text-sm font-black text-slate-900 dark:text-white">
              Estimasi belum bisa dibuat
            </h2>

            <p class="mt-1 text-sm leading-6 text-slate-600 dark:text-slate-400">
              Inventory dan rules harus diisi terlebih dahulu agar sistem memiliki dasar perhitungan.
              Saat ini inventory:
              <strong class="text-slate-900 dark:text-white">{{ number_format($inventoryCountForEstimation) }}</strong>,
              rules:
              <strong class="text-slate-900 dark:text-white">{{ number_format($ruleCountForEstimation) }}</strong>.
            </p>
          </div>
        </div>

        <div class="flex shrink-0 gap-2">
          <a href="{{ route('inventories.index') }}"
             class="inline-flex items-center justify-center rounded-xl bg-blue-600 px-4 py-2 text-xs font-bold text-white shadow-sm transition hover:bg-blue-700 dark:bg-white dark:text-slate-950 dark:hover:bg-slate-200">
            Isi Inventory
          </a>

          <a href="{{ route('settings.rules.index') }}"
             class="inline-flex items-center justify-center rounded-xl border border-slate-300 bg-white px-4 py-2 text-xs font-bold text-slate-700 shadow-sm transition hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800">
            Buat Rules
          </a>
        </div>
      </div>
    </div>
  @endunless

  {{-- Main Metrics --}}
  <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-5">
    
    <!-- Card 1: Highlight Blue -->
    <div class="relative overflow-hidden rounded-[24px] bg-blue-600 p-6 text-white shadow-lg shadow-blue-500/20 hover:-translate-y-1 transition-transform duration-300">
      <div class="absolute -right-6 -top-6 h-32 w-32 rounded-full bg-white/10 blur-2xl"></div>
      <div class="absolute -left-6 -bottom-6 h-24 w-24 rounded-full bg-blue-400/20 blur-xl"></div>
      
      <div class="relative flex items-start justify-between">
        <div>
          <p class="text-xs font-medium text-blue-100 uppercase tracking-wider">Total Value Estimasi</p>
          <p class="mt-3 text-3xl font-bold tracking-tight">{{ $totalValueShort }}</p>

          <div class="mt-3 inline-flex items-center gap-1.5 rounded-full {{ $monthlyGrowthPercent >= 0 ? 'bg-white/20' : 'bg-rose-500/30' }} px-2.5 py-1 text-xs font-medium backdrop-blur-sm">
            @if($monthlyGrowthPercent >= 0)
              <svg class="h-3 w-3" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M12 7a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0V8.414l-4.293 4.293a1 1 0 01-1.414 0L8 10.414l-4.293 4.293a1 1 0 01-1.414-1.414l5-5a1 1 0 011.414 0L11 10.586 14.586 7H12z" clip-rule="evenodd"/>
              </svg>
              +{{ $monthlyGrowthPercent }}% bulan ini
            @else
              <svg class="h-3 w-3" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M8 13a1 1 0 110 2H3a1 1 0 01-1-1v-5a1 1 0 112 0v4.586l4.293-4.293a1 1 0 011.414 0L12 9.586l4.293-4.293a1 1 0 011.414 1.414l-5 5a1 1 0 01-1.414 0L9 9.414 5.414 13H8z" clip-rule="evenodd"/>
              </svg>
              {{ $monthlyGrowthPercent }}% bulan ini
            @endif
          </div>
        </div>

        <div class="rounded-2xl bg-white/20 p-3 backdrop-blur-md">
          <svg class="h-6 w-6 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor">
            <path stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                  d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
        </div>
      </div>
    </div>

    <!-- Card 2 -->
    <div class="rounded-[24px] bg-white dark:bg-slate-900 p-6 shadow-[0_4px_20px_-4px_rgba(0,0,0,0.05)] hover:-translate-y-1 transition-transform duration-300">
      <div class="flex items-start justify-between">
        <div>
          <p class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Approved Value</p>
          <p class="mt-3 text-3xl font-bold text-slate-900 dark:text-white tracking-tight">{{ $approvedValueShort }}</p>
          <p class="mt-3 text-xs text-slate-500">
            <span class="font-semibold text-emerald-500">{{ number_format($approvedEstimations) }}</span> estimasi goal
          </p>
        </div>

        <div class="rounded-2xl bg-emerald-50 dark:bg-emerald-500/10 p-3 text-emerald-500">
          <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor">
            <path stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                  d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
        </div>
      </div>
    </div>

    <!-- Card 3 -->
    <div class="rounded-[24px] bg-white dark:bg-slate-900 p-6 shadow-[0_4px_20px_-4px_rgba(0,0,0,0.05)] hover:-translate-y-1 transition-transform duration-300">
      <div class="flex items-start justify-between">
        <div>
          <p class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Pending Potential</p>
          <p class="mt-3 text-3xl font-bold text-slate-900 dark:text-white tracking-tight">{{ $pendingValueShort }}</p>
          <p class="mt-3 text-xs text-slate-500">
            <span class="font-semibold text-amber-500">{{ number_format($pendingEstimations) }}</span> butuh follow up
          </p>
        </div>

        <div class="rounded-2xl bg-amber-50 dark:bg-amber-500/10 p-3 text-amber-500">
          <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor">
            <path stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                  d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
        </div>
      </div>
    </div>

    <!-- Card 4 -->
    <div class="rounded-[24px] bg-white dark:bg-slate-900 p-6 shadow-[0_4px_20px_-4px_rgba(0,0,0,0.05)] hover:-translate-y-1 transition-transform duration-300">
      <div class="flex items-start justify-between">
        <div>
          <p class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Bulan Ini</p>
          <p class="mt-3 text-3xl font-bold text-slate-900 dark:text-white tracking-tight">{{ $monthlyValueShort }}</p>
          <p class="mt-3 text-xs text-slate-500">
            <span class="font-semibold text-blue-500">{{ number_format($monthlyEstimations) }}</span> estimasi baru
          </p>
        </div>

        <div class="rounded-2xl bg-blue-50 dark:bg-blue-500/10 p-3 text-blue-600 dark:text-blue-400">
          <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor">
            <path stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                  d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
          </svg>
        </div>
      </div>
    </div>
  </div>

  {{-- Compact Stats Grid --}}
  <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-6 gap-4">
    <div class="rounded-[20px] bg-white dark:bg-slate-900 p-5 shadow-sm flex flex-col items-center justify-center text-center">
      <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400 mb-1">Total Dokumen</p>
      <p class="text-2xl font-bold text-slate-800 dark:text-white">{{ number_format($totalEstimations) }}</p>
    </div>

    <div class="rounded-[20px] bg-white dark:bg-slate-900 p-5 shadow-sm flex flex-col items-center justify-center text-center">
      <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400 mb-1">Avg Val</p>
      <p class="text-xl font-bold text-slate-800 dark:text-white">{{ $avgEstimationValueShort }}</p>
    </div>

    <div class="rounded-[20px] bg-white dark:bg-slate-900 p-5 shadow-sm flex flex-col items-center justify-center text-center">
      <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400 mb-1">Inventory</p>
      <p class="text-2xl font-bold text-slate-800 dark:text-white">{{ number_format($inventoryUnits) }}</p>
    </div>

    <div class="rounded-[20px] bg-white dark:bg-slate-900 p-5 shadow-sm flex flex-col items-center justify-center text-center">
      <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400 mb-1">Items</p>
      <p class="text-2xl font-bold text-slate-800 dark:text-white">{{ number_format($inventoryItems) }}</p>
    </div>

    <div class="rounded-[20px] bg-white dark:bg-slate-900 p-5 shadow-sm flex flex-col items-center justify-center text-center">
      <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400 mb-1">Akurasi</p>
      <p class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ $accuracy }}%</p>
    </div>

    <div class="rounded-[20px] {{ $shortageEstimations > 0 ? 'bg-rose-50 text-rose-600' : 'bg-white dark:bg-slate-900 text-slate-800 dark:text-white' }} p-5 shadow-sm flex flex-col items-center justify-center text-center">
      <p class="text-[10px] font-bold uppercase tracking-widest {{ $shortageEstimations > 0 ? 'text-rose-400' : 'text-slate-400' }} mb-1">Shortage</p>
      <p class="text-2xl font-bold">{{ number_format($shortageEstimations) }}</p>
    </div>
  </div>

  {{-- Split Layout --}}
  <div class="grid grid-cols-1 xl:grid-cols-3 gap-8">

    {{-- Left: Recent Estimations Table --}}
    <div class="xl:col-span-2 rounded-[24px] bg-white dark:bg-slate-900 shadow-[0_4px_20px_-4px_rgba(0,0,0,0.05)] overflow-hidden">
      <div class="flex items-center justify-between p-6">
        <div>
          <h2 class="text-lg font-bold text-slate-900 dark:text-white">Estimasi Terbaru</h2>
          <p class="text-sm text-slate-500 mt-0.5">Analisis tren sales untuk keputusan lebih cepat.</p>
        </div>

        <a href="{{ route('estimations.index') }}"
           class="inline-flex h-9 items-center justify-center rounded-xl bg-slate-50 dark:bg-slate-800 px-4 text-xs font-semibold text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors">
          View All
        </a>
      </div>

      <div class="overflow-x-auto pb-4">
        <table class="w-full text-left border-collapse">
          <thead>
            <tr>
              <th class="px-6 py-3 text-[11px] font-bold text-slate-400 uppercase tracking-wider border-b border-slate-100 dark:border-slate-800">Event Details</th>
              <th class="px-6 py-3 text-[11px] font-bold text-slate-400 uppercase tracking-wider border-b border-slate-100 dark:border-slate-800">Tanggal</th>
              <th class="px-6 py-3 text-[11px] font-bold text-slate-400 uppercase tracking-wider border-b border-slate-100 dark:border-slate-800">Total Harga</th>
              <th class="px-6 py-3 text-[11px] font-bold text-slate-400 uppercase tracking-wider border-b border-slate-100 dark:border-slate-800">Status</th>
            </tr>
          </thead>

          <tbody>
            @forelse($recentEstimations as $e)
              @php
                $eventLabel = ucfirst($e->event->event_name ?? $e->event->event_type ?? 'Event');
                $creatorInitial = strtoupper(substr($e->creator?->name ?? 'U', 0, 1));
                $eventMeta = trim(implode(' • ', array_filter([
                    ucfirst($e->event->location ?? ''),
                    ($e->event->participants ?? null) ? number_format((int)$e->event->participants).' pax' : null,
                ])));
              @endphp

              <tr onclick="window.location='{{ route('estimations.show', $e->id) }}'"
                  class="group cursor-pointer hover:bg-slate-50/80 dark:hover:bg-slate-800/50 transition-colors">
                <td class="px-6 py-4 border-b border-slate-50 dark:border-slate-800/60">
                  <div class="flex items-center gap-4">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-blue-50 text-blue-600 dark:bg-blue-900/30 font-bold text-sm">
                      {{ $creatorInitial }}
                    </div>

                    <div>
                      <div class="text-sm font-semibold text-slate-900 dark:text-white group-hover:text-blue-600 transition-colors">
                        {{ $eventLabel }}
                      </div>
                      <div class="text-xs text-slate-500 mt-0.5">
                        {{ $eventMeta ?: 'Detail belum lengkap' }}
                      </div>
                    </div>
                  </div>
                </td>

                <td class="px-6 py-4 border-b border-slate-50 dark:border-slate-800/60 whitespace-nowrap">
                  <div class="text-sm text-slate-700 dark:text-slate-300 font-medium">
                    {{ optional($e->created_at)->format('d M Y') }}
                  </div>
                  <div class="text-xs text-slate-400">
                    {{ optional($e->created_at)->format('H:i') }} WIB
                  </div>
                </td>

                <td class="px-6 py-4 border-b border-slate-50 dark:border-slate-800/60 whitespace-nowrap">
                  <div class="text-sm font-bold text-slate-900 dark:text-white">
                    Rp {{ number_format((int)$e->total_cost, 0, ',', '.') }}
                  </div>
                </td>

                <td class="px-6 py-4 border-b border-slate-50 dark:border-slate-800/60 whitespace-nowrap">
                  <span class="inline-flex items-center rounded-[8px] px-2.5 py-1 text-[11px] font-bold uppercase tracking-wider {{ $statusBadge($e->status) }}">
                    {{ $statusLabel($e->status) }}
                  </span>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="4" class="px-6 py-16 text-center">
                  <p class="text-sm text-slate-500">{{ __('dashboard.table.empty') }}</p>
                </td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>

    {{-- Right: Sidebar Insights --}}
    <div class="space-y-8">

      {{-- Action Required --}}
      <div class="rounded-[24px] bg-white dark:bg-slate-900 p-6 shadow-[0_4px_20px_-4px_rgba(0,0,0,0.05)]">
        <h2 class="text-lg font-bold text-slate-900 dark:text-white mb-4">Task & Alerts</h2>
        
        <div class="space-y-3">
          <a href="{{ route('estimations.index', ['status' => 'pending']) }}"
             class="group flex items-center justify-between rounded-2xl p-3 hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors">
            <div class="flex items-center gap-3">
              <div class="rounded-xl bg-amber-50 p-2.5 text-amber-500 dark:bg-amber-500/10">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
              </div>

              <div>
                <div class="text-sm font-semibold text-slate-900 dark:text-white">Estimasi Pending</div>
                <div class="text-xs text-slate-500">Perlu follow up</div>
              </div>
            </div>

            <div class="text-base font-bold text-amber-500">{{ number_format($pendingEstimations) }}</div>
          </a>

          <a href="{{ route('estimations.index') }}"
             class="group flex items-center justify-between rounded-2xl p-3 hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors">
            <div class="flex items-center gap-3">
              <div class="rounded-xl bg-rose-50 p-2.5 text-rose-500 dark:bg-rose-500/10">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
              </div>

              <div>
                <div class="text-sm font-semibold text-slate-900 dark:text-white">Shortage Alert</div>
                <div class="text-xs text-slate-500">Kekurangan unit alat</div>
              </div>
            </div>

            <div class="text-base font-bold text-rose-500">{{ number_format($shortageEstimations) }}</div>
          </a>
        </div>
      </div>

      {{-- Top Equipment --}}
      <div class="rounded-[24px] bg-white dark:bg-slate-900 p-6 shadow-[0_4px_20px_-4px_rgba(0,0,0,0.05)]">
        <div class="flex items-center justify-between mb-5">
          <h2 class="text-lg font-bold text-slate-900 dark:text-white">Top Equipment</h2>
          <a href="{{ route('inventories.index') }}" class="text-xs font-semibold text-blue-600 hover:underline">
            Kelola
          </a>
        </div>

        <div class="space-y-5">
          @forelse($topEquipments as $index => $item)
            @php
              $maxCount = $topEquipments[0]->used_count ?? 1;
              $percentage = ($item->used_count / ($maxCount == 0 ? 1 : $maxCount)) * 100;
            @endphp

            <div>
              <div class="flex items-center justify-between text-sm mb-2">
                <div class="font-medium text-slate-700 dark:text-slate-200 truncate pr-4">
                  {{ $item->equipment_name }}
                </div>

                <div class="text-slate-400 font-semibold text-xs">
                  {{ number_format((int)$item->used_count) }}x
                </div>
              </div>

              <div class="h-2 w-full bg-slate-50 dark:bg-slate-800 rounded-full overflow-hidden">
                <div class="h-full bg-blue-500 rounded-full transition-all duration-1000" style="width: {{ $percentage }}%"></div>
              </div>
            </div>
          @empty
            <div class="text-center text-sm text-slate-500 py-4">
              Belum ada data pemakaian.
            </div>
          @endforelse
        </div>
      </div>

    </div>
  </div>
</div>
@endsection