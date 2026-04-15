@extends('layouts.app-shell')
@section('title', 'Rule Wizard')

@section('content')
@php
  $tiers = $tiers ?? [];
  $inventoryNames = $inventoryNames ?? [];
@endphp

<div class="max-w-6xl mx-auto space-y-6">

  <div class="flex flex-col gap-2">
    <h1 class="text-4xl font-semibold tracking-tight text-slate-900 dark:text-white">Rule Wizard</h1>
    <p class="text-slate-500 dark:text-slate-400">
      Isi sekali → auto-generate rules: tier peserta, indoor/outdoor, service level, plus crew.
    </p>
  </div>

  @if($errors->any())
    <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
      {{ $errors->first() }}
    </div>
  @endif

  {{-- ✅ Inventory datalist --}}
  <datalist id="inventoryList">
    @foreach($inventoryNames as $nm)
      <option value="{{ $nm }}"></option>
    @endforeach
  </datalist>

  <form method="POST" action="{{ route('settings.rules.wizard.preview') }}" class="space-y-6">
    @csrf

    {{-- Priority start --}}
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-5">
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
          <div class="text-sm font-bold text-slate-900 dark:text-white">Basic Settings</div>
          <div class="text-xs text-slate-500 dark:text-slate-400 mt-1">
            Priority akan naik otomatis per tier.
          </div>
        </div>

        <div class="w-full sm:max-w-[220px]">
          <label class="block text-xs font-semibold text-slate-600 dark:text-slate-300 mb-2">Priority Start</label>
          <input name="priority_start" type="number" min="0" max="9999"
                 value="{{ old('priority_start', 50) }}"
                 class="w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-950/50 px-4 py-2 text-sm">
        </div>
      </div>
    </div>

    {{-- TIERS --}}
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 overflow-hidden">
      <div class="px-5 py-4 border-b border-slate-200 dark:border-slate-800">
        <div class="text-sm font-bold text-slate-900 dark:text-white">Participant Tiers</div>
        <div class="text-xs text-slate-500 dark:text-slate-400 mt-1">
          Set range peserta, isi equipment & crew untuk tiap tier.
        </div>
      </div>

      <div class="p-5 space-y-6">
        @foreach($tiers as $i => $t)
          @php
            $tierLabel = old("tiers.$i.label", $t['label'] ?? ("T".($i+1)));
            $tierMin   = old("tiers.$i.min",   $t['min'] ?? 1);
            $tierMax   = old("tiers.$i.max",   $t['max'] ?? null);
          @endphp

          <div class="rounded-2xl border border-slate-200 dark:border-slate-800 p-4">
            <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
              <div>
                <div class="font-bold text-slate-900 dark:text-white">Tier {{ $tierLabel }}</div>
                <div class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                  Rule: participants between <b>{{ $tierMin }}</b> - <b>{{ $tierMax ?? '∞' }}</b>
                </div>
              </div>

              <div class="grid grid-cols-3 gap-2 w-full lg:w-auto">
                <div>
                  <label class="block text-xs font-semibold text-slate-600 dark:text-slate-300 mb-1">Label</label>
                  <input name="tiers[{{ $i }}][label]" value="{{ $tierLabel }}"
                         class="w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-950/50 px-3 py-2 text-sm">
                </div>

                <div>
                  <label class="block text-xs font-semibold text-slate-600 dark:text-slate-300 mb-1">Min</label>
                  <input name="tiers[{{ $i }}][min]" type="number" min="1" value="{{ $tierMin }}"
                         class="w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-950/50 px-3 py-2 text-sm">
                </div>

                <div>
                  <label class="block text-xs font-semibold text-slate-600 dark:text-slate-300 mb-1">Max</label>
                  <input name="tiers[{{ $i }}][max]" type="number" min="1" value="{{ $tierMax }}"
                         placeholder="kosong = >= min"
                         class="w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-950/50 px-3 py-2 text-sm">
                </div>
              </div>
            </div>

            {{-- Equipment rows --}}
            <div class="mt-4">
              <div class="flex items-center justify-between">
                <div class="text-sm font-semibold text-slate-900 dark:text-white">Equipment (Tier {{ $tierLabel }})</div>
                <button type="button"
                        class="text-sm font-semibold text-blue-600 dark:text-blue-400"
                        data-add-row
                        data-target="#tierItems{{ $i }}"
                        data-name-prefix="tier_items[{{ $i }}]">
                  + Add item
                </button>
              </div>

              <div id="tierItems{{ $i }}" class="mt-3 space-y-2">
                @php
                  $oldItems = old("tier_items.$i", []);
                  if (!is_array($oldItems)) $oldItems = [];
                  $seed = count($oldItems) ? $oldItems : [['name'=>'','qty'=>0]];
                @endphp

                @foreach($seed as $ri => $row)
                  <div class="grid grid-cols-12 gap-2 items-center" data-row>
                    <div class="col-span-8">
                      <input list="inventoryList"
                             name="tier_items[{{ $i }}][{{ $ri }}][name]"
                             value="{{ $row['name'] ?? '' }}"
                             placeholder="Pilih dari inventory (autocomplete)"
                             class="w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-950/50 px-3 py-2 text-sm">
                      <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-1">
                        Tips: pilih yang muncul di suggestion supaya match.
                      </p>
                    </div>
                    <div class="col-span-3">
                      <input name="tier_items[{{ $i }}][{{ $ri }}][qty]" type="number" min="0" value="{{ $row['qty'] ?? 0 }}"
                             class="w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-950/50 px-3 py-2 text-sm">
                    </div>
                    <div class="col-span-1 flex justify-end">
                      <button type="button" class="text-slate-400 hover:text-red-600" data-remove-row title="Remove">✕</button>
                    </div>
                  </div>
                @endforeach
              </div>

              <p class="text-xs text-slate-500 dark:text-slate-400 mt-2">
                Qty 0 akan di-skip (tidak dibuat rule action).
              </p>
            </div>

            {{-- Crew --}}
            <div class="mt-4">
              <div class="text-sm font-semibold text-slate-900 dark:text-white">Crew (Tier {{ $tierLabel }})</div>
              <div class="mt-3 grid grid-cols-1 sm:grid-cols-3 gap-2">
                @foreach(['operator'=>'Operator','engineer'=>'Engineer','stage'=>'Stage'] as $k => $label)
                  <div>
                    <label class="block text-xs font-semibold text-slate-600 dark:text-slate-300 mb-1">{{ $label }}</label>
                    <input name="tier_crew[{{ $i }}][{{ $k }}]" type="number" min="0"
                           value="{{ old("tier_crew.$i.$k", 0) }}"
                           class="w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-950/50 px-3 py-2 text-sm">
                  </div>
                @endforeach
              </div>
            </div>

          </div>
        @endforeach
      </div>
    </div>

    {{-- VENUE TYPE --}}
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 overflow-hidden">
      <div class="px-5 py-4 border-b border-slate-200 dark:border-slate-800">
        <div class="text-sm font-bold text-slate-900 dark:text-white">Venue Type</div>
        <div class="text-xs text-slate-500 dark:text-slate-400 mt-1">
          Rule menggunakan <code class="px-1 rounded bg-slate-100 dark:bg-slate-800">location contains indoor/outdoor</code>.
        </div>
      </div>

      <div class="p-5 grid grid-cols-1 lg:grid-cols-2 gap-5">
        @foreach(['indoor'=>'Indoor Extras','outdoor'=>'Outdoor Extras'] as $key => $title)
          @php
            $blockKey = $key . '_items';
            $oldBlock = old($blockKey, []);
            if (!is_array($oldBlock)) $oldBlock = [];
            $seedBlock = count($oldBlock) ? $oldBlock : [['name'=>'','qty'=>0]];
          @endphp

          <div class="rounded-2xl border border-slate-200 dark:border-slate-800 p-4">
            <div class="flex items-center justify-between">
              <div class="font-semibold text-slate-900 dark:text-white">{{ $title }}</div>
              <button type="button"
                      class="text-sm font-semibold text-blue-600 dark:text-blue-400"
                      data-add-row
                      data-target="#{{ $blockKey }}"
                      data-name-prefix="{{ $blockKey }}">
                + Add item
              </button>
            </div>

            <div id="{{ $blockKey }}" class="mt-3 space-y-2">
              @foreach($seedBlock as $ri => $row)
                <div class="grid grid-cols-12 gap-2 items-center" data-row>
                  <div class="col-span-8">
                    <input list="inventoryList"
                           name="{{ $blockKey }}[{{ $ri }}][name]"
                           value="{{ $row['name'] ?? '' }}"
                           placeholder="Pilih dari inventory (autocomplete)"
                           class="w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-950/50 px-3 py-2 text-sm">
                  </div>
                  <div class="col-span-3">
                    <input name="{{ $blockKey }}[{{ $ri }}][qty]" type="number" min="0" value="{{ $row['qty'] ?? 0 }}"
                           class="w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-950/50 px-3 py-2 text-sm">
                  </div>
                  <div class="col-span-1 flex justify-end">
                    <button type="button" class="text-slate-400 hover:text-red-600" data-remove-row title="Remove">✕</button>
                  </div>
                </div>
              @endforeach
            </div>
          </div>
        @endforeach
      </div>
    </div>

    {{-- SERVICE LEVEL --}}
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 overflow-hidden">
      <div class="px-5 py-4 border-b border-slate-200 dark:border-slate-800">
        <div class="text-sm font-bold text-slate-900 dark:text-white">Service Level</div>
        <div class="text-xs text-slate-500 dark:text-slate-400 mt-1">
          Rule menggunakan <code class="px-1 rounded bg-slate-100 dark:bg-slate-800">service_level = basic/standard/premium</code>.
        </div>
      </div>

      <div class="p-5 grid grid-cols-1 lg:grid-cols-3 gap-5">
        @php
          $svc = [
            ['key'=>'service_basic_items', 'title'=>'Basic'],
            ['key'=>'service_standard_items', 'title'=>'Standard'],
            ['key'=>'service_premium_items', 'title'=>'Premium'],
          ];
        @endphp

        @foreach($svc as $block)
          @php
            $key = $block['key'];
            $oldSvc = old($key, []);
            if (!is_array($oldSvc)) $oldSvc = [];
            $seedSvc = count($oldSvc) ? $oldSvc : [['name'=>'','qty'=>0]];
          @endphp

          <div class="rounded-2xl border border-slate-200 dark:border-slate-800 p-4">
            <div class="flex items-center justify-between">
              <div class="font-semibold text-slate-900 dark:text-white">{{ $block['title'] }} Extras</div>
              <button type="button"
                      class="text-sm font-semibold text-blue-600 dark:text-blue-400"
                      data-add-row
                      data-target="#{{ $key }}"
                      data-name-prefix="{{ $key }}">
                + Add item
              </button>
            </div>

            <div id="{{ $key }}" class="mt-3 space-y-2">
              @foreach($seedSvc as $ri => $row)
                <div class="grid grid-cols-12 gap-2 items-center" data-row>
                  <div class="col-span-8">
                    <input list="inventoryList"
                           name="{{ $key }}[{{ $ri }}][name]"
                           value="{{ $row['name'] ?? '' }}"
                           placeholder="Pilih dari inventory (autocomplete)"
                           class="w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-950/50 px-3 py-2 text-sm">
                  </div>
                  <div class="col-span-3">
                    <input name="{{ $key }}[{{ $ri }}][qty]" type="number" min="0" value="{{ $row['qty'] ?? 0 }}"
                           class="w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-950/50 px-3 py-2 text-sm">
                  </div>
                  <div class="col-span-1 flex justify-end">
                    <button type="button" class="text-slate-400 hover:text-red-600" data-remove-row title="Remove">✕</button>
                  </div>
                </div>
              @endforeach
            </div>

          </div>
        @endforeach
      </div>
    </div>

    {{-- Actions --}}
    <div class="flex flex-col sm:flex-row gap-2 justify-end">
      <a href="{{ route('settings.rules.index') }}"
         class="inline-flex items-center justify-center rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 px-5 py-3 text-sm font-semibold text-slate-900 dark:text-white hover:bg-slate-50 dark:hover:bg-slate-800">
        Back
      </a>

      <button type="submit"
              class="inline-flex items-center justify-center rounded-xl bg-blue-600 px-5 py-3 text-sm font-semibold text-white hover:bg-blue-700 shadow-sm">
        Preview Rules
      </button>
    </div>

  </form>
</div>

<script>
(function () {
  function makeRow(namePrefix, idx) {
    const wrap = document.createElement('div');
    wrap.className = 'grid grid-cols-12 gap-2 items-center';
    wrap.setAttribute('data-row', '1');

    wrap.innerHTML = `
      <div class="col-span-8">
        <input list="inventoryList" name="${namePrefix}[${idx}][name]" placeholder="Pilih dari inventory (autocomplete)"
          class="w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-950/50 px-3 py-2 text-sm">
      </div>
      <div class="col-span-3">
        <input name="${namePrefix}[${idx}][qty]" type="number" min="0" value="0"
          class="w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-950/50 px-3 py-2 text-sm">
      </div>
      <div class="col-span-1 flex justify-end">
        <button type="button" class="text-slate-400 hover:text-red-600" data-remove-row title="Remove">✕</button>
      </div>
    `;
    return wrap;
  }

  function nextIndex(container) {
    const rows = Array.from(container.querySelectorAll('[data-row]'));
    let max = -1;
    rows.forEach(r => {
      const input = r.querySelector('input[name*="[name]"]');
      if (!input) return;
      const m = input.name.match(/\[(\d+)\]\[name\]/);
      if (m) max = Math.max(max, parseInt(m[1], 10));
    });
    return max + 1;
  }

  document.addEventListener('click', (e) => {
    const addBtn = e.target.closest('[data-add-row]');
    if (addBtn) {
      const targetSel = addBtn.getAttribute('data-target');
      const prefix = addBtn.getAttribute('data-name-prefix');
      const container = document.querySelector(targetSel);
      if (!container || !prefix) return;

      const idx = nextIndex(container);
      container.appendChild(makeRow(prefix, idx));
      return;
    }

    const removeBtn = e.target.closest('[data-remove-row]');
    if (removeBtn) {
      const row = removeBtn.closest('[data-row]');
      const container = row?.parentElement;
      if (!row || !container) return;

      const all = container.querySelectorAll('[data-row]');
      if (all.length <= 1) {
        const name = row.querySelector('input[name*="[name]"]');
        const qty  = row.querySelector('input[name*="[qty]"]');
        if (name) name.value = '';
        if (qty) qty.value = 0;
        return;
      }

      row.remove();
    }
  });
})();
</script>
@endsection