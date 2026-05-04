@extends('layouts.app-shell')
@section('title', __('inventory.title'))

@section('content')
@php
  $user = auth()->user();

  // Untuk sekarang owner & staff masih bisa manage inventory.
  // Kalau nanti staff hanya view, ubah jadi: $canManageInventory = $user?->isOwner();
  $canManageInventory = $user?->isOwner() || $user?->isStaff();

  $pp = request('per_page','10');
@endphp

<div class="space-y-6">

  {{-- Header --}}
  <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
    <div>
      <h1 class="text-3xl sm:text-4xl font-semibold tracking-tight text-slate-900 dark:text-white transition-colors">
        {{ __('inventory.title') }}
      </h1>
      <p class="text-slate-500 dark:text-slate-400 mt-2 transition-colors">
        {{ __('inventory.subtitle') }}
      </p>
    </div>

    @if($canManageInventory)
      <div class="grid grid-cols-1 sm:flex sm:items-center gap-3 w-full lg:w-auto">
        <a href="{{ route('inventories.import.form') }}"
           class="w-full sm:w-auto inline-flex items-center justify-center gap-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 px-5 py-3 text-sm font-semibold text-slate-900 dark:text-white hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors">
          {{ __('inventory.import') }}
        </a>

        <a href="{{ route('inventories.create') }}"
           class="w-full sm:w-auto inline-flex items-center justify-center gap-2 rounded-xl bg-blue-600 px-5 py-3 text-sm font-semibold text-white shadow-sm hover:bg-blue-700 transition-colors">
          <span class="text-lg leading-none">+</span> {{ __('inventory.add') }}
        </a>
      </div>
    @endif
  </div>

  {{-- Alert --}}
  @if(session('success'))
    <div class="rounded-xl border border-green-200 dark:border-green-500/20 bg-green-50 dark:bg-green-500/10 px-4 py-3 text-sm text-green-800 dark:text-green-400 flex items-center gap-2 transition-colors">
      <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
      </svg>
      <span>{{ session('success') }}</span>
    </div>
  @endif

  {{-- Filter --}}
  <form method="GET" class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-800 p-4 transition-colors duration-300">
    <div class="grid grid-cols-1 md:grid-cols-12 gap-3">

      <div class="md:col-span-5">
        <input id="invSearch" name="search" value="{{ request('search') }}"
               placeholder="{{ __('inventory.search') }}"
               class="w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-950/50 px-4 py-3 text-sm text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500
                      focus:outline-none focus:ring-2 focus:ring-blue-500/50 dark:focus:ring-blue-500/30 focus:border-blue-500 dark:focus:border-blue-500 transition-colors">
      </div>

      <div class="md:col-span-3">
        <select name="category"
                class="w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-950/50 px-4 py-3 text-sm text-slate-900 dark:text-white
                       focus:outline-none focus:ring-2 focus:ring-blue-500/50 dark:focus:ring-blue-500/30 focus:border-blue-500 dark:focus:border-blue-500 transition-colors">
          <option value="">{{ __('inventory.all_category') }}</option>
          @foreach($categories as $cat)
            <option value="{{ $cat }}" @selected(request('category')===$cat)>{{ $cat }}</option>
          @endforeach
        </select>
      </div>

      <div class="md:col-span-2">
        <select name="status"
                class="w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-950/50 px-4 py-3 text-sm text-slate-900 dark:text-white
                       focus:outline-none focus:ring-2 focus:ring-blue-500/50 dark:focus:ring-blue-500/30 focus:border-blue-500 dark:focus:border-blue-500 transition-colors">
          <option value="">{{ __('inventory.all_status') }}</option>
          @foreach($statuses as $st)
            <option value="{{ $st }}" @selected(request('status')===$st)>{{ ucfirst($st) }}</option>
          @endforeach
        </select>
      </div>

      <div class="md:col-span-2">
        <select name="per_page"
                class="w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-950/50 px-4 py-3 text-sm text-slate-900 dark:text-white
                       focus:outline-none focus:ring-2 focus:ring-blue-500/50 dark:focus:ring-blue-500/30 focus:border-blue-500 dark:focus:border-blue-500 transition-colors">
          <option value="10"  @selected($pp=='10')>Show 10</option>
          <option value="25"  @selected($pp=='25')>Show 25</option>
          <option value="50"  @selected($pp=='50')>Show 50</option>
          <option value="all" @selected($pp=='all')>Show All</option>
        </select>
      </div>
    </div>

    <div class="mt-3 flex items-center justify-end">
      <a href="{{ route('inventories.index') }}"
         class="w-full sm:w-auto inline-flex items-center justify-center rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 px-4 py-2.5 text-sm font-semibold text-slate-900 dark:text-white hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors">
        Reset
      </a>
    </div>
  </form>

  <script>
  (function () {
    const form = document.querySelector('form[method="GET"]');
    if (!form) return;

    const search = document.getElementById('invSearch');
    let t;

    if (search) {
      search.addEventListener('input', () => {
        clearTimeout(t);
        t = setTimeout(() => form.submit(), 300);
      });
    }

    ['category', 'status', 'per_page'].forEach((name) => {
      const el = form.querySelector(`select[name="${name}"]`);
      if (el) el.addEventListener('change', () => form.submit());
    });
  })();
  </script>

  {{-- Desktop Table --}}
  <div class="hidden md:block bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-800 overflow-hidden transition-colors duration-300">
    <div class="overflow-x-auto">
      <table class="w-full">
        <thead class="bg-slate-50 dark:bg-slate-800/50 border-b border-slate-200 dark:border-slate-800 transition-colors">
          <tr>
            <th class="px-6 py-4 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Image</th>
            <th class="px-6 py-4 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Equipment Name</th>
            <th class="px-6 py-4 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Category</th>
            <th class="px-6 py-4 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Quantity</th>
            <th class="px-6 py-4 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Unit Price</th>
            @if($canManageInventory)
              <th class="px-6 py-4 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Actions</th>
            @endif
          </tr>
        </thead>

        <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
          @forelse($items as $item)
            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors">
              <td class="px-6 py-5">
                @if($item->image_path)
                  <img src="{{ asset('storage/'.$item->image_path) }}"
                       class="h-12 w-12 rounded-xl object-cover border border-slate-200 dark:border-slate-700"
                       alt="{{ $item->equipment_name }}">
                @else
                  <div class="h-12 w-12 rounded-xl bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 flex items-center justify-center text-slate-400 dark:text-slate-500">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                  </div>
                @endif
              </td>

              <td class="px-6 py-5">
                <div class="text-sm font-semibold text-slate-900 dark:text-white">{{ $item->equipment_name }}</div>
              </td>

              <td class="px-6 py-5">
                <div class="text-sm text-slate-600 dark:text-slate-400">{{ $item->category }}</div>
              </td>

              <td class="px-6 py-5">
                <div class="text-sm text-slate-700 dark:text-slate-300">{{ number_format((int)$item->quantity) }}</div>
              </td>

              <td class="px-6 py-5">
                <div class="text-sm font-semibold text-slate-900 dark:text-white">
                  Rp {{ number_format((int)$item->price,0,',','.') }}
                </div>
              </td>

              @if($canManageInventory)
                <td class="px-6 py-5">
                  <div class="flex items-center gap-3">
                    <a href="{{ route('inventories.edit', $item->id) }}"
                       class="inline-flex items-center gap-2 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 px-4 py-2 text-sm font-semibold text-slate-900 dark:text-white hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors">
                      Edit
                    </a>

                    <form method="POST" action="{{ route('inventories.destroy', $item->id) }}"
                          onsubmit="return confirm('Delete this equipment?');">
                      @csrf
                      @method('DELETE')
                      <button
                        class="inline-flex items-center gap-2 rounded-lg border border-red-200 dark:border-red-500/30 bg-white dark:bg-slate-900 px-4 py-2 text-sm font-semibold text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-500/10 transition-colors">
                        Delete
                      </button>
                    </form>
                  </div>
                </td>
              @endif
            </tr>
          @empty
            <tr>
              <td colspan="{{ $canManageInventory ? 6 : 5 }}" class="px-6 py-10 text-center text-sm text-slate-500 dark:text-slate-400">
                {{ __('inventory.empty') }}
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    @if($items instanceof \Illuminate\Pagination\AbstractPaginator)
      <div class="px-6 py-4 border-t border-slate-200 dark:border-slate-800">
        {{ $items->links() }}
      </div>
    @endif
  </div>

  {{-- Mobile List --}}
  <div class="md:hidden space-y-3">
    @forelse($items as $item)
      @php
        $statusClass = match($item->status) {
          'active' => 'bg-green-50 text-green-700 border-green-200 dark:bg-green-500/10 dark:text-green-400 dark:border-green-500/20',
          'maintenance' => 'bg-orange-50 text-orange-700 border-orange-200 dark:bg-orange-500/10 dark:text-orange-400 dark:border-orange-500/20',
          'inactive' => 'bg-red-50 text-red-700 border-red-200 dark:bg-red-500/10 dark:text-red-400 dark:border-red-500/20',
          default => 'bg-slate-50 text-slate-700 border-slate-200 dark:bg-slate-800 dark:text-slate-300 dark:border-slate-700',
        };
      @endphp

      <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 shadow-sm overflow-hidden">
        <div class="p-4">
          <div class="flex gap-4">
            <div class="shrink-0">
              @if($item->image_path)
                <img src="{{ asset('storage/'.$item->image_path) }}"
                     class="h-24 w-24 rounded-2xl object-cover border border-slate-200 dark:border-slate-700"
                     alt="{{ $item->equipment_name }}">
              @else
                <div class="h-24 w-24 rounded-2xl bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 flex items-center justify-center text-slate-400 dark:text-slate-500">
                  <svg class="w-9 h-9" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                  </svg>
                </div>
              @endif
            </div>

            <div class="min-w-0 flex-1">
              <h2 class="text-base font-bold leading-snug text-slate-900 dark:text-white break-words">
                {{ $item->equipment_name }}
              </h2>

              <div class="mt-2 space-y-1.5 text-sm">
                <div class="flex items-center justify-between gap-3">
                  <span class="font-semibold text-slate-700 dark:text-slate-200">Stock:</span>
                  <span class="text-right text-slate-600 dark:text-slate-400">
                    {{ number_format((int)$item->quantity) }} in stock
                  </span>
                </div>

                <div class="flex items-center justify-between gap-3">
                  <span class="font-semibold text-slate-700 dark:text-slate-200">Category:</span>
                  <span class="text-right text-slate-600 dark:text-slate-400">
                    {{ $item->category ?: '-' }}
                  </span>
                </div>

                <div class="flex items-center justify-between gap-3">
                  <span class="font-semibold text-slate-700 dark:text-slate-200">Price:</span>
                  <span class="text-right font-semibold text-slate-900 dark:text-white">
                    Rp {{ number_format((int)$item->price,0,',','.') }}
                  </span>
                </div>
              </div>

              <div class="mt-3 flex flex-wrap items-center gap-2">
                <span class="inline-flex items-center rounded-full border px-3 py-1 text-xs font-bold {{ $statusClass }}">
                  {{ ucfirst($item->status ?: 'active') }}
                </span>

                @if($canManageInventory)
                  <a href="{{ route('inventories.edit', $item->id) }}"
                     class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-blue-600 text-white shadow-sm hover:bg-blue-700 transition-colors"
                     aria-label="Edit {{ $item->equipment_name }}">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                  </a>
                @endif
              </div>
            </div>
          </div>
        </div>

        @if($canManageInventory)
          <div class="grid grid-cols-2 border-t border-slate-200 dark:border-slate-800">
            <a href="{{ route('inventories.edit', $item->id) }}"
               class="inline-flex items-center justify-center px-4 py-3 text-sm font-bold text-slate-900 dark:text-white hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors">
              Edit
            </a>

            <form method="POST" action="{{ route('inventories.destroy', $item->id) }}"
                  onsubmit="return confirm('Delete this equipment?');">
              @csrf
              @method('DELETE')
              <button
                class="w-full inline-flex items-center justify-center px-4 py-3 text-sm font-bold text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-500/10 transition-colors">
                Delete
              </button>
            </form>
          </div>
        @endif
      </div>
    @empty
      <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 px-6 py-10 text-center text-sm text-slate-500 dark:text-slate-400">
        {{ __('inventory.empty') }}
      </div>
    @endforelse

    @if($items instanceof \Illuminate\Pagination\AbstractPaginator)
      <div class="pt-2">
        {{ $items->links() }}
      </div>
    @endif
  </div>
</div>
@endsection