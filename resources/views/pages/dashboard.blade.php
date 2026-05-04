@extends('layouts.app-shell')
@section('title', __('dashboard.title'))

@section('content')
@php
  $user = auth()->user();
  $isOwner = $user?->isOwner();

  $statusBadge = function ($status) {
      return match($status) {
          'approved' => 'bg-emerald-100 text-emerald-800 border-emerald-200 dark:bg-emerald-500/15 dark:text-emerald-400 dark:border-emerald-500/20',
          'pending'  => 'bg-amber-100 text-amber-800 border-amber-200 dark:bg-amber-500/15 dark:text-amber-400 dark:border-amber-500/20',
          'revised'  => 'bg-blue-100 text-blue-800 border-blue-200 dark:bg-blue-500/15 dark:text-blue-400 dark:border-blue-500/20',
          'rejected' => 'bg-rose-100 text-rose-800 border-rose-200 dark:bg-rose-500/15 dark:text-rose-400 dark:border-rose-500/20',
          default    => 'bg-slate-100 text-slate-800 border-slate-200 dark:bg-slate-800 dark:text-slate-300 dark:border-slate-700',
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

<!-- Catatan: Pastikan tag <body> atau <main> di layouts.app-shell kamu menggunakan bg-slate-50 (light) dan bg-slate-950 (dark) agar card putihnya terlihat kontras -->
<div class="space-y-6 animate-in fade-in slide-in-from-bottom-4 duration-500">

  {{-- Hero Header --}}
  <div class="relative overflow-hidden rounded-3xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 shadow transition-all hover:shadow-md">
    <!-- Efek blur diredupkan di light mode (bg-blue-500/5) agar tidak terlihat seperti bercak -->
    <div class="absolute -right-20 -top-20 h-72 w-72 rounded-full bg-blue-500/5 dark:bg-blue-500/10 blur-3xl"></div>
    <div class="absolute -left-24 bottom-0 h-72 w-72 rounded-full bg-purple-500/5 dark:bg-purple-500/10 blur-3xl"></div>

    <div class="relative p-5 sm:p-7 lg:p-8">
      <div class="flex flex-col gap-6 lg:flex-row lg:items-start lg:justify-between">
        <div>
          <div class="inline-flex items-center gap-2 rounded-full border border-blue-200 dark:border-blue-500/20 bg-blue-50 dark:bg-blue-500/10 px-3 py-1 text-xs font-bold text-blue-800 dark:text-blue-300">
            <span class="relative flex h-2.5 w-2.5">
              <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-blue-400 opacity-75"></span>
              <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-blue-600 dark:bg-blue-500"></span>
            </span>
            Kira Decision Support System
          </div>

          <h1 class="mt-4 text-3xl sm:text-4xl lg:text-5xl font-semibold tracking-tight text-slate-950 dark:text-white">
            Halo, {{ explode(' ', $user->name ?? 'Tim')[0] }}! 👋
          </h1>

          <p class="mt-3 max-w-2xl text-sm sm:text-base text-slate-600 dark:text-slate-400">
            Berikut adalah ringkasan performa estimasi, potensi revenue, dan kesehatan inventory tenant kamu hari ini.
          </p>
        </div>

        <div class="grid grid-cols-1 sm:flex sm:items-center gap-3 w-full lg:w-auto">
          <a href="{{ route('events.create') }}"
             class="w-full sm:w-auto inline-flex items-center justify-center gap-2 rounded-2xl bg-blue-600 px-6 py-3.5 text-sm font-bold text-white shadow-lg shadow-blue-500/30 hover:bg-blue-700 hover:-translate-y-0.5 transition-all duration-200">
            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            {{-- Trik untuk hapus karakter "+" bawaan dari file lang --}}
            {{ trim(str_replace('+', '', __('dashboard.actions.new_estimation'))) }}
          </a>

          <a href="{{ route('estimations.index') }}"
             class="w-full sm:w-auto inline-flex items-center justify-center gap-2 rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 px-6 py-3.5 text-sm font-bold text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-700 hover:-translate-y-0.5 transition-all duration-200">
             <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            {{ __('dashboard.actions.history') }}
          </a>
        </div>
      </div>

      {{-- Hero Metrics --}}
      <div class="mt-8 grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
        <!-- Card 1 -->
        <div class="group rounded-2xl border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-900/50 p-5 hover:bg-white dark:hover:bg-slate-800 transition-colors shadow-sm hover:shadow-md">
          <div class="flex items-start justify-between gap-4">
            <div>
              <p class="text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400">Total Value Estimasi</p>
              <p class="mt-2 text-3xl font-bold text-slate-950 dark:text-white tracking-tight">
                {{ $totalValueShort }}
              </p>
              <div class="mt-2 flex items-center gap-1.5 text-xs">
                <span class="inline-flex items-center text-emerald-700 dark:text-emerald-400 font-medium">
                  <svg class="h-3 w-3 mr-0.5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M12 7a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0V8.414l-4.293 4.293a1 1 0 01-1.414 0L8 10.414l-4.293 4.293a1 1 0 01-1.414-1.414l5-5a1 1 0 011.414 0L11 10.586 14.586 7H12z" clip-rule="evenodd"/></svg>
                  +12% 
                </span>
                <span class="text-slate-500 dark:text-slate-400">dari bulan lalu</span>
              </div>
            </div>
            <div class="rounded-xl bg-blue-100 dark:bg-blue-500/20 p-3 text-blue-700 dark:text-blue-400 group-hover:scale-110 transition-transform">
              <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
          </div>
        </div>

        <!-- Card 2 -->
        <div class="group rounded-2xl border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-900/50 p-5 hover:bg-white dark:hover:bg-slate-800 transition-colors shadow-sm hover:shadow-md">
          <div class="flex items-start justify-between gap-4">
            <div>
              <p class="text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400">Approved Value</p>
              <p class="mt-2 text-3xl font-bold text-slate-950 dark:text-white tracking-tight">
                {{ $approvedValueShort }}
              </p>
              <div class="mt-2 flex items-center gap-1.5 text-xs text-slate-600 dark:text-slate-400">
                <span class="font-medium text-slate-800 dark:text-slate-300">{{ number_format($approvedEstimations) }}</span> estimasi tembus
              </div>
            </div>
            <div class="rounded-xl bg-emerald-100 dark:bg-emerald-500/20 p-3 text-emerald-700 dark:text-emerald-400 group-hover:scale-110 transition-transform">
              <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
          </div>
        </div>

        <!-- Card 3 -->
        <div class="group rounded-2xl border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-900/50 p-5 hover:bg-white dark:hover:bg-slate-800 transition-colors shadow-sm hover:shadow-md">
          <div class="flex items-start justify-between gap-4">
            <div>
              <p class="text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400">Pending Potential</p>
              <p class="mt-2 text-3xl font-bold text-slate-950 dark:text-white tracking-tight">
                {{ $pendingValueShort }}
              </p>
              <div class="mt-2 flex items-center gap-1.5 text-xs text-slate-600 dark:text-slate-400">
                <span class="font-medium text-amber-700 dark:text-amber-400">{{ number_format($pendingEstimations) }}</span> butuh keputusan
              </div>
            </div>
            <div class="rounded-xl bg-amber-100 dark:bg-amber-500/20 p-3 text-amber-700 dark:text-amber-400 group-hover:scale-110 transition-transform">
              <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
          </div>
        </div>

        <!-- Card 4 -->
        <div class="group rounded-2xl border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-900/50 p-5 hover:bg-white dark:hover:bg-slate-800 transition-colors shadow-sm hover:shadow-md">
          <div class="flex items-start justify-between gap-4">
            <div>
              <p class="text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400">Bulan Ini</p>
              <p class="mt-2 text-3xl font-bold text-slate-950 dark:text-white tracking-tight">
                {{ $monthlyValueShort }}
              </p>
              <div class="mt-2 flex items-center gap-1.5 text-xs text-slate-600 dark:text-slate-400">
                <span class="font-medium text-purple-700 dark:text-purple-400">{{ number_format($monthlyEstimations) }}</span> estimasi baru
              </div>
            </div>
            <div class="rounded-xl bg-purple-100 dark:bg-purple-500/20 p-3 text-purple-700 dark:text-purple-400 group-hover:scale-110 transition-transform">
              <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  {{-- Compact Cards / Mini Insights --}}
  <div class="grid grid-cols-2 lg:grid-cols-4 xl:grid-cols-6 gap-4">
    <div class="group rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-4 shadow hover:shadow-md hover:-translate-y-1 transition-all duration-300">
      <div class="flex items-center gap-2 mb-2">
        <div class="h-2 w-2 rounded-full bg-blue-500"></div>
        <p class="text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400">Total Estimasi</p>
      </div>
      <p class="text-2xl font-bold text-slate-950 dark:text-white">{{ number_format($totalEstimations) }}</p>
      <p class="mt-1 text-xs text-slate-500">Avg <span class="font-medium text-slate-800 dark:text-slate-300">{{ $avgEstimationValueShort }}</span></p>
    </div>

    <div class="group rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-4 shadow hover:shadow-md hover:-translate-y-1 transition-all duration-300">
      <div class="flex items-center gap-2 mb-2">
        <div class="h-2 w-2 rounded-full bg-amber-500"></div>
        <p class="text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400">Pending</p>
      </div>
      <p class="text-2xl font-bold text-amber-600 dark:text-amber-400">{{ number_format($pendingEstimations) }}</p>
      <p class="mt-1 text-xs text-slate-500">Perlu ditindaklanjuti</p>
    </div>

    <div class="group rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-4 shadow hover:shadow-md hover:-translate-y-1 transition-all duration-300">
      <div class="flex items-center gap-2 mb-2">
        <div class="h-2 w-2 rounded-full bg-emerald-500"></div>
        <p class="text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400">Approved</p>
      </div>
      <p class="text-2xl font-bold text-emerald-600 dark:text-emerald-400">{{ number_format($approvedEstimations) }}</p>
      <p class="mt-1 text-xs text-slate-500">Deal berhasil</p>
    </div>

    <div class="group rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-4 shadow hover:shadow-md hover:-translate-y-1 transition-all duration-300">
      <div class="flex items-center gap-2 mb-2">
        <div class="h-2 w-2 rounded-full bg-indigo-500"></div>
        <p class="text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400">Inventory Unit</p>
      </div>
      <p class="text-2xl font-bold text-slate-950 dark:text-white">{{ number_format($inventoryUnits) }}</p>
      <p class="mt-1 text-xs text-slate-500"><span class="font-medium text-slate-800 dark:text-slate-300">{{ number_format($inventoryItems) }}</span> tipe alat</p>
    </div>

    <div class="group rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-4 shadow hover:shadow-md hover:-translate-y-1 transition-all duration-300">
      <div class="flex items-center gap-2 mb-2">
        <div class="h-2 w-2 rounded-full bg-teal-500"></div>
        <p class="text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400">Akurasi</p>
      </div>
      <p class="text-2xl font-bold text-teal-600 dark:text-teal-400">{{ $accuracy }}%</p>
      <p class="mt-1 text-xs text-slate-500"><span class="font-medium">{{ number_format($unevaluatedApproved) }}</span> belum dievaluasi</p>
    </div>

    <div class="group rounded-2xl border {{ $shortageEstimations > 0 ? 'border-rose-200 bg-rose-50 dark:border-rose-900/50 dark:bg-rose-900/10' : 'border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900' }} p-4 shadow hover:shadow-md hover:-translate-y-1 transition-all duration-300">
      <div class="flex items-center gap-2 mb-2">
        <div class="h-2 w-2 rounded-full {{ $shortageEstimations > 0 ? 'bg-rose-500' : 'bg-slate-400 dark:bg-slate-600' }}"></div>
        <p class="text-xs font-bold uppercase tracking-wider {{ $shortageEstimations > 0 ? 'text-rose-800 dark:text-rose-400' : 'text-slate-600 dark:text-slate-400' }}">Shortage</p>
      </div>
      <p class="text-2xl font-bold {{ $shortageEstimations > 0 ? 'text-rose-600 dark:text-rose-400' : 'text-slate-950 dark:text-slate-400' }}">
        {{ number_format($shortageEstimations) }}
      </p>
      <p class="mt-1 text-xs {{ $shortageEstimations > 0 ? 'text-rose-700/80 dark:text-rose-400/70' : 'text-slate-500' }}">Masalah ketersediaan</p>
    </div>
  </div>

  {{-- Main Grid --}}
  <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">

    {{-- Recent Estimations --}}
    <div class="xl:col-span-2 rounded-3xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 shadow overflow-hidden flex flex-col">
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 px-6 py-5 border-b border-slate-200 dark:border-slate-800">
        <div>
          <h2 class="text-lg font-bold text-slate-950 dark:text-white">Estimasi Terbaru</h2>
          <p class="mt-1 text-sm text-slate-600 dark:text-slate-400">Menampilkan 5 estimasi terakhir yang dibuat oleh tim.</p>
        </div>

        <a href="{{ route('estimations.index') }}" class="inline-flex items-center justify-center gap-1 rounded-xl bg-slate-100 dark:bg-slate-800 px-4 py-2 text-sm font-semibold text-slate-800 dark:text-slate-200 hover:bg-slate-200 dark:hover:bg-slate-700 transition-colors border border-slate-200 dark:border-slate-700">
          Lihat Semua
          <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/></svg>
        </a>
      </div>

      <div class="hidden md:block overflow-x-auto flex-1">
        <table class="w-full text-left border-collapse">
          <thead class="bg-slate-100 dark:bg-slate-800/40 border-b border-slate-200 dark:border-slate-800">
            <tr>
              <th class="px-6 py-4 text-xs font-bold text-slate-600 dark:text-slate-400 uppercase tracking-wider">Event Details</th>
              <th class="px-6 py-4 text-xs font-bold text-slate-600 dark:text-slate-400 uppercase tracking-wider">Tanggal</th>
              <th class="px-6 py-4 text-xs font-bold text-slate-600 dark:text-slate-400 uppercase tracking-wider">Total Estimasi</th>
              <th class="px-6 py-4 text-xs font-bold text-slate-600 dark:text-slate-400 uppercase tracking-wider">Status</th>
              <th class="px-6 py-4 text-right text-xs font-bold text-slate-600 dark:text-slate-400 uppercase tracking-wider">Aksi</th>
            </tr>
          </thead>

          <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60">
            @forelse($recentEstimations as $e)
              @php
                $eventLabel = ucfirst($e->event->event_name ?? $e->event->event_type ?? 'Event');
                $creatorInitial = strtoupper(substr($e->creator?->name ?? 'U', 0, 1));
                $eventMeta = trim(implode(' • ', array_filter([
                    ucfirst($e->event->location ?? ''),
                    ($e->event->participants ?? null) ? number_format((int)$e->event->participants).' pax' : null,
                ])));
              @endphp

              <tr onclick="window.location='{{ route('estimations.show', $e->id) }}'" class="group cursor-pointer hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors">
                <td class="px-6 py-4">
                  <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400 font-bold text-sm">
                      {{ $creatorInitial }}
                    </div>
                    <div>
                      <div class="text-sm font-bold text-slate-900 dark:text-white group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">{{ $eventLabel }}</div>
                      <div class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                        {{ $eventMeta ?: 'Detail belum lengkap' }}
                      </div>
                    </div>
                  </div>
                </td>

                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm text-slate-800 dark:text-slate-300 font-medium">{{ optional($e->created_at)->format('d M Y') }}</div>
                  <div class="text-xs text-slate-500">{{ optional($e->created_at)->format('H:i') }} WIB</div>
                </td>

                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm font-bold text-slate-900 dark:text-white">Rp {{ number_format((int)$e->total_cost,0,',','.') }}</div>
                </td>

                <td class="px-6 py-4 whitespace-nowrap">
                  <span class="inline-flex items-center gap-1.5 rounded-full border px-2.5 py-1 text-xs font-bold {{ $statusBadge($e->status) }}">
                    <span class="h-1.5 w-1.5 rounded-full currentColor bg-current"></span>
                    {{ $statusLabel($e->status) }}
                  </span>
                </td>

                <td class="px-6 py-4 text-right">
                  <a href="{{ route('estimations.show', $e->id) }}" onclick="event.stopPropagation()" class="inline-flex items-center justify-center rounded-lg p-2 text-slate-400 hover:bg-blue-100 hover:text-blue-700 dark:hover:bg-slate-700 dark:hover:text-blue-400 transition-colors">
                    <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/></svg>
                  </a>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="5" class="px-6 py-16 text-center">
                  <div class="inline-flex h-12 w-12 items-center justify-center rounded-full bg-slate-100 dark:bg-slate-800 text-slate-500 mb-3">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/></svg>
                  </div>
                  <p class="text-sm font-medium text-slate-900 dark:text-white">{{ __('dashboard.table.empty') }}</p>
                  <p class="text-xs text-slate-500 mt-1">Estimasi yang dibuat akan muncul di sini.</p>
                </td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>

      {{-- Mobile Recent Cards --}}
      <div class="md:hidden divide-y divide-slate-100 dark:divide-slate-800/60">
        @forelse($recentEstimations as $e)
          @php $eventLabel = ucfirst($e->event->event_name ?? $e->event->event_type ?? 'Event'); @endphp
          <a href="{{ route('estimations.show', $e->id) }}" class="block p-5 hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors">
            <div class="flex items-start justify-between gap-4 mb-3">
              <div class="font-bold text-slate-900 dark:text-white">{{ $eventLabel }}</div>
              <span class="shrink-0 inline-flex items-center gap-1.5 rounded-full border px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider {{ $statusBadge($e->status) }}">
                {{ $statusLabel($e->status) }}
              </span>
            </div>
            <div class="flex items-end justify-between">
              <div>
                <div class="text-sm font-bold text-slate-900 dark:text-white">Rp {{ number_format((int)$e->total_cost,0,',','.') }}</div>
                <div class="text-xs text-slate-500 mt-1">{{ optional($e->created_at)->format('d M Y') }} • {{ $e->creator->name ?? 'Unknown' }}</div>
              </div>
              <div class="text-blue-600 dark:text-blue-400">
                <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/></svg>
              </div>
            </div>
          </a>
        @empty
           <div class="p-8 text-center text-sm text-slate-500">{{ __('dashboard.table.empty') }}</div>
        @endforelse
      </div>
    </div>

    {{-- Right Panel --}}
    <div class="space-y-6">

      {{-- Action Required --}}
      <div class="rounded-3xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 shadow overflow-hidden">
        <div class="px-6 py-5 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
          <div>
            <h2 class="text-lg font-bold text-slate-950 dark:text-white">Action Required</h2>
            <p class="mt-1 text-sm text-slate-600 dark:text-slate-400">Membutuhkan atensimu.</p>
          </div>
          @if($pendingEstimations > 0 || $shortageEstimations > 0)
            <span class="relative flex h-3 w-3">
              <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-rose-400 opacity-75"></span>
              <span class="relative inline-flex rounded-full h-3 w-3 bg-rose-600"></span>
            </span>
          @endif
        </div>

        <div class="p-5 space-y-3">
          <!-- Item 1 -->
          <a href="{{ route('estimations.index', ['status' => 'pending']) }}" class="group flex items-center justify-between rounded-2xl border border-amber-200 dark:border-amber-500/20 bg-amber-50 dark:bg-amber-500/10 p-4 hover:bg-amber-100 dark:hover:bg-amber-500/20 transition-all shadow-sm">
            <div class="flex items-start gap-3">
              <div class="mt-0.5 rounded-full bg-amber-200 dark:bg-amber-500/30 p-1.5 text-amber-800 dark:text-amber-400">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
              </div>
              <div>
                <div class="text-sm font-bold text-amber-950 dark:text-amber-300 group-hover:underline">Estimasi Pending</div>
                <div class="text-xs text-amber-800/80 dark:text-amber-400/70 mt-0.5">Perlu approval / follow up</div>
              </div>
            </div>
            <div class="text-xl font-bold text-amber-700 dark:text-amber-400">{{ number_format($pendingEstimations) }}</div>
          </a>

          <!-- Item 2 -->
          <a href="{{ route('estimations.index') }}" class="group flex items-center justify-between rounded-2xl border border-rose-200 dark:border-rose-500/20 bg-rose-50 dark:bg-rose-500/10 p-4 hover:bg-rose-100 dark:hover:bg-rose-500/20 transition-all shadow-sm">
            <div class="flex items-start gap-3">
              <div class="mt-0.5 rounded-full bg-rose-200 dark:bg-rose-500/30 p-1.5 text-rose-800 dark:text-rose-400">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
              </div>
              <div>
                <div class="text-sm font-bold text-rose-950 dark:text-rose-300 group-hover:underline">Shortage Alert</div>
                <div class="text-xs text-rose-800/80 dark:text-rose-400/70 mt-0.5">Kekurangan alat</div>
              </div>
            </div>
            <div class="text-xl font-bold text-rose-700 dark:text-rose-400">{{ number_format($shortageEstimations) }}</div>
          </a>

          <!-- Item 3 -->
          <a href="{{ route('inventories.index') }}" class="group flex items-center justify-between rounded-2xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50 p-4 hover:bg-slate-100 dark:hover:bg-slate-800 transition-all shadow-sm">
            <div class="flex items-start gap-3">
              <div class="mt-0.5 rounded-full bg-slate-200 dark:bg-slate-700 p-1.5 text-slate-700 dark:text-slate-300">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
              </div>
              <div>
                <div class="text-sm font-bold text-slate-900 dark:text-white group-hover:underline">Missing Price</div>
                <div class="text-xs text-slate-600 dark:text-slate-400 mt-0.5">Alat belum diset harga</div>
              </div>
            </div>
            <div class="text-xl font-bold text-slate-900 dark:text-white">{{ number_format($missingPriceItems) }}</div>
          </a>
        </div>
      </div>

      {{-- Inventory Health --}}
      <div class="rounded-3xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 shadow p-6">
        <div class="flex items-center justify-between mb-5">
          <h2 class="text-lg font-bold text-slate-950 dark:text-white">Inventory Health</h2>
          <a href="{{ route('inventories.index') }}" class="text-sm font-semibold text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300">Kelola</a>
        </div>

        <div class="grid grid-cols-2 gap-3">
          <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-900/50 p-4 text-center shadow-sm">
            <div class="text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">Tipe Alat</div>
            <div class="text-2xl font-bold text-slate-900 dark:text-white">{{ number_format($inventoryItems) }}</div>
          </div>
          <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-900/50 p-4 text-center shadow-sm">
             <div class="text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">Total Unit</div>
            <div class="text-2xl font-bold text-slate-900 dark:text-white">{{ number_format($inventoryUnits) }}</div>
          </div>
          <div class="rounded-2xl border border-orange-200 dark:border-orange-900/30 bg-orange-50 dark:bg-orange-900/10 p-4 text-center shadow-sm">
            <div class="text-xs font-bold uppercase tracking-wider text-orange-700 dark:text-orange-400/70 mb-1">Low Stock</div>
            <div class="text-2xl font-bold text-orange-700 dark:text-orange-400">{{ number_format($lowStockItems) }}</div>
          </div>
          <div class="rounded-2xl border border-purple-200 dark:border-purple-900/30 bg-purple-50 dark:bg-purple-900/10 p-4 text-center shadow-sm">
             <div class="text-xs font-bold uppercase tracking-wider text-purple-700 dark:text-purple-400/70 mb-1">Maintenance</div>
            <div class="text-2xl font-bold text-purple-700 dark:text-purple-400">{{ number_format($maintenanceItems) }}</div>
          </div>
        </div>
      </div>

      {{-- Top Equipment --}}
      <div class="rounded-3xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 shadow p-6">
        <h2 class="text-lg font-bold text-slate-950 dark:text-white mb-1">Top Equipment</h2>
        <p class="text-sm text-slate-600 dark:text-slate-400 mb-5">Peralatan paling sering disewa.</p>

        <div class="space-y-4">
          @forelse($topEquipments as $index => $item)
            @php
              $maxCount = $topEquipments[0]->used_count ?? 1;
              $percentage = ($item->used_count / ($maxCount == 0 ? 1 : $maxCount)) * 100;
            @endphp
            <div class="relative">
              <div class="flex items-center justify-between text-sm mb-1.5 relative z-10">
                <div class="flex items-center gap-2 font-semibold text-slate-900 dark:text-slate-200">
                  <span class="text-slate-500 text-xs w-4">{{ $index + 1 }}.</span>
                  <span class="truncate max-w-[150px]">{{ $item->equipment_name }}</span>
                </div>
                <div class="text-slate-700 dark:text-slate-400 font-medium text-xs">
                  {{ number_format((int)$item->used_count) }}x pakai
                </div>
              </div>
              <!-- Visual Bar -->
              <div class="h-1.5 w-full bg-slate-100 dark:bg-slate-800 rounded-full overflow-hidden">
                <div class="h-full bg-blue-600 dark:bg-blue-400 rounded-full" style="width: {{ $percentage }}%"></div>
              </div>
            </div>
          @empty
            <div class="rounded-xl border border-dashed border-slate-300 dark:border-slate-700 p-6 text-center text-sm text-slate-500">
              Belum ada data riwayat pemakaian alat.
            </div>
          @endforelse
        </div>
      </div>

    </div>
  </div>
</div>
@endsection