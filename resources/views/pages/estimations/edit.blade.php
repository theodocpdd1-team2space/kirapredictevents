{{-- resources/views/pages/estimations/edit.blade.php --}}

@extends('layouts.app-shell')
@section('title','Revise Estimation')

@php
  $estimation->loadMissing(['event','details']);

  $inventories = $inventories ?? collect();
  $units = ['pcs','set','box','meter','day'];

  $eventDays   = max(1, (int)($estimation->event->event_days ?? 1));
  $hoursPerDay = max(1, (int)($estimation->event->hours_per_day ?? 1));
  $durationBlock = $hoursPerDay <= 4 ? 1 : ($hoursPerDay <= 8 ? 2 : 3);
  $mult = $eventDays * $durationBlock;

  $breakdown = $estimation->breakdown;
  if (is_string($breakdown)) $breakdown = json_decode($breakdown, true) ?: [];
  if (!is_array($breakdown)) $breakdown = [];

  $labor     = (int)($breakdown['labor'] ?? 0);
  $transport = (int)($breakdown['transport'] ?? 0);

  $opPercent     = (float)\App\Models\Setting::getValue('operational_percent', 5);
  $markupPercent = (float)\App\Models\Setting::getValue('markup_percent', 0);

  // inventory map for JS autofill
  $invMap = [];
  foreach ($inventories as $inv) {
    $invMap[$inv->equipment_name] = [
      'price' => (int)$inv->price,
      'qty'   => (int)$inv->quantity,
    ];
  }
@endphp

@section('content')
<div class="max-w-6xl mx-auto space-y-6">

  <div>
    <h1 class="text-4xl font-semibold tracking-tight text-slate-900 dark:text-white">Revise Estimation</h1>
    <p class="text-slate-600 dark:text-slate-400 mt-2">
      Edit quantity, unit price, ganti equipment langsung, tambah custom/sewa, dan simpan revision note.
      <span class="ml-2 text-xs text-slate-500 dark:text-slate-500">
        Multiplier: {{ $eventDays }} day × block {{ $durationBlock }} = <b>{{ $mult }}</b>
      </span>
    </p>
  </div>

  @if(session('success'))
    <div class="rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800
                dark:border-green-500/20 dark:bg-green-500/10 dark:text-green-200">
      {{ session('success') }}
    </div>
  @endif

  @if($errors->any())
    <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800
                dark:border-red-500/20 dark:bg-red-500/10 dark:text-red-200">
      {{ $errors->first() }}
    </div>
  @endif

  <form method="POST" action="{{ route('estimations.update', $estimation->id) }}" class="space-y-6" id="reviseForm">
    @csrf
    @method('PATCH')

    {{-- Hidden bucket for removed rows (still submitted to backend) --}}
    <div id="removedBucket" class="hidden"></div>

    <datalist id="invList">
      @foreach($inventories as $inv)
        <option value="{{ $inv->equipment_name }}"></option>
      @endforeach
    </datalist>

    {{-- Revision note --}}
    <div class="rounded-2xl border border-slate-200 bg-white overflow-hidden
                dark:border-slate-700/60 dark:bg-slate-900/40">
      <div class="px-6 py-5 border-b border-slate-200
                  dark:border-slate-700/60">
        <div class="text-sm font-bold text-slate-900 dark:text-white">Revision Note</div>
      </div>
      <div class="p-6">
        <textarea name="revision_note" rows="4"
          class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 placeholder-slate-400
                 focus:outline-none focus:ring-2 focus:ring-blue-500/30
                 dark:border-slate-700 dark:bg-slate-950/40 dark:text-white dark:placeholder-slate-500 dark:focus:ring-blue-500/40"
          placeholder="Tulis alasan perubahan (misal: tambah monitor, ganti mixer, sewa genset...)">{{ old('revision_note', $estimation->revision_note) }}</textarea>
      </div>
    </div>

    {{-- Existing items --}}
    <div class="rounded-2xl border border-slate-200 bg-white overflow-hidden
                dark:border-slate-700/60 dark:bg-slate-900/40">
      <div class="px-6 py-5 border-b border-slate-200 flex items-center justify-between
                  dark:border-slate-700/60">
        <div class="text-sm font-bold text-slate-900 dark:text-white">Equipment</div>
        <div class="text-xs text-slate-500 dark:text-slate-400">Action: Remove / Ganti item langsung (qty=0 auto-remove)</div>
      </div>

      <div class="overflow-x-auto">
        <table class="w-full text-sm">
          <thead class="bg-slate-50 border-b border-slate-200
                        dark:bg-slate-950/40 dark:border-slate-700/60">
            <tr>
              <th class="px-6 py-4 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase">Equipment</th>
              <th class="px-3 py-4 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase">Unit</th>
              <th class="px-3 py-4 text-right text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase">Qty</th>
              <th class="px-3 py-4 text-right text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase">Unit Price</th>
              <th class="px-3 py-4 text-right text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase">Available</th>
              <th class="px-3 py-4 text-right text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase">Shortage</th>
              <th class="px-6 py-4 text-right text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase">Line Total</th>
              <th class="px-6 py-4 text-right text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase">Action</th>
            </tr>
          </thead>

          <tbody class="divide-y divide-slate-200 dark:divide-slate-800" id="existingTbody">
            @foreach($estimation->details as $d)
              @php
                $idx = $loop->index;
                $isCustom = (bool)($d->is_custom ?? false);
                $invQty = 0;
                if (!$isCustom && isset($invMap[$d->equipment_name])) $invQty = (int)$invMap[$d->equipment_name]['qty'];
                $avail = (int)($d->available ?? $invQty);
                $short = (int)($d->shortage ?? max(0, (int)$d->quantity - $avail));
              @endphp

              <tr class="hover:bg-slate-50 dark:hover:bg-white/5" data-existing-row data-idx="{{ $idx }}">
                <td class="px-6 py-5">
                  <div class="flex items-center gap-2">
                    <input type="hidden" name="items[{{ $idx }}][id]" value="{{ $d->id }}" data-id>

                    <input
                      name="items[{{ $idx }}][equipment_name]"
                      list="invList"
                      value="{{ old("items.$idx.equipment_name", $d->equipment_name) }}"
                      class="h-10 w-full rounded-xl border border-slate-300 bg-white px-3 text-sm text-slate-900 placeholder-slate-400 focus:outline-none
                             dark:border-slate-700 dark:bg-slate-950/40 dark:text-white dark:placeholder-slate-500"
                      placeholder="Pilih dari inventory / ketik bebas (custom)"
                      data-eq
                    >

                    @if($isCustom)
                      <span class="shrink-0 rounded-full px-2 py-0.5 text-xs font-semibold
                                   bg-purple-50 text-purple-700 border border-purple-200
                                   dark:bg-purple-500/15 dark:text-purple-200 dark:border-purple-500/20">
                        custom
                      </span>
                    @endif
                  </div>

                  <div class="mt-2">
                    <label class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase">Notes (optional)</label>
                    <input name="items[{{ $idx }}][notes]" value="{{ old("items.$idx.notes", $d->notes) }}"
                      class="mt-2 w-full h-10 rounded-xl border border-slate-300 bg-white px-3 text-sm text-slate-900 placeholder-slate-400 focus:outline-none
                             dark:border-slate-700 dark:bg-slate-950/40 dark:text-white dark:placeholder-slate-500"
                      placeholder="contoh: sewa partner, butuh kabel extra, rider artist..."
                      data-notes
                    >
                  </div>
                </td>

                <td class="px-3 py-5">
                  <select name="items[{{ $idx }}][unit]"
                    class="h-10 rounded-xl border border-slate-300 bg-white px-3 text-sm text-slate-900 focus:outline-none
                           dark:border-slate-700 dark:bg-slate-950/40 dark:text-white"
                    data-unit
                  >
                    @foreach($units as $u)
                      <option value="{{ $u }}" @selected(old("items.$idx.unit", $d->unit ?? 'pcs') === $u)>{{ $u }}</option>
                    @endforeach
                  </select>
                </td>

                <td class="px-3 py-5 text-right">
                  <input name="items[{{ $idx }}][quantity]" type="number" min="0"
                    value="{{ old("items.$idx.quantity", (int)$d->quantity) }}"
                    class="h-10 w-24 text-right rounded-xl border border-slate-300 bg-white px-3 text-sm text-slate-900 focus:outline-none
                           dark:border-slate-700 dark:bg-slate-950/40 dark:text-white"
                    data-qty
                  >
                  <div class="text-[11px] text-slate-500 dark:text-slate-500 mt-1">0 = remove</div>
                </td>

                <td class="px-3 py-5 text-right">
                  <input name="items[{{ $idx }}][price]" type="number" min="0"
                    value="{{ old("items.$idx.price", (int)$d->price) }}"
                    class="h-10 w-36 text-right rounded-xl border border-slate-300 bg-white px-3 text-sm text-slate-900 focus:outline-none
                           dark:border-slate-700 dark:bg-slate-950/40 dark:text-white"
                    data-price
                  >
                  <div class="text-[11px] text-slate-500 dark:text-slate-500 mt-1">editable</div>
                </td>

                <td class="px-3 py-5 text-right font-semibold text-slate-700 dark:text-slate-200" data-avail>
                  {{ $avail }}
                </td>

                <td class="px-3 py-5 text-right" data-shortage>
                  @if($short > 0)
                    <span class="inline-flex rounded-full px-2 py-1 text-xs font-semibold
                                 bg-red-50 text-red-700 border border-red-200
                                 dark:bg-red-500/10 dark:text-red-300 dark:border-red-500/20">
                      {{ $short }}
                    </span>
                  @else
                    <span class="text-slate-500 dark:text-slate-400">0</span>
                  @endif
                </td>

                <td class="px-6 py-5 text-right font-semibold text-slate-900 dark:text-white" data-line-total>
                  Rp {{ number_format((int)$d->total,0,',','.') }}
                </td>

                <td class="px-6 py-5 text-right">
                  <button type="button"
                    class="rounded-xl border px-3 py-2 text-xs font-semibold
                           border-red-200 bg-red-50 text-red-700 hover:bg-red-100
                           dark:border-red-500/20 dark:bg-red-500/10 dark:text-red-200 dark:hover:bg-red-500/20"
                    data-remove
                  >
                    Remove
                  </button>
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>

    {{-- Add from inventory --}}
    <div class="rounded-2xl border border-slate-200 bg-white overflow-hidden
                dark:border-slate-700/60 dark:bg-slate-900/40">
      <div class="px-6 py-5 border-b border-slate-200
                  dark:border-slate-700/60">
        <div class="text-sm font-bold text-slate-900 dark:text-white">Add Equipment (from Inventory)</div>
        <div class="text-xs text-slate-600 dark:text-slate-400 mt-1">Ketik nama item → pilih suggestion supaya match inventory (auto price).</div>
      </div>

      <div class="p-6 space-y-3" id="newItemsWrap">
        <div class="grid grid-cols-12 gap-2 text-xs text-slate-500 dark:text-slate-400 uppercase font-semibold">
          <div class="col-span-6">Equipment</div>
          <div class="col-span-2">Unit</div>
          <div class="col-span-2 text-right">Qty</div>
          <div class="col-span-2 text-right">Price</div>
        </div>

        {{-- seed 1 row (safe: will be disabled if empty on submit) --}}
        <div class="grid grid-cols-12 gap-2 items-center" data-new-row>
          <div class="col-span-6">
            <input name="new_items[0][equipment_name]" list="invList"
              class="h-10 w-full rounded-xl border border-slate-300 bg-white px-3 text-sm text-slate-900 placeholder-slate-400 focus:outline-none
                     dark:border-slate-700 dark:bg-slate-950/40 dark:text-white dark:placeholder-slate-500"
              placeholder="Pilih dari inventory (autocomplete)"
              data-new-eq
            >
          </div>
          <div class="col-span-2">
            <select name="new_items[0][unit]"
              class="h-10 w-full rounded-xl border border-slate-300 bg-white px-3 text-sm text-slate-900 focus:outline-none
                     dark:border-slate-700 dark:bg-slate-950/40 dark:text-white">
              @foreach($units as $u)<option value="{{ $u }}">{{ $u }}</option>@endforeach
            </select>
          </div>
          <div class="col-span-2">
            <input name="new_items[0][quantity]" type="number" min="1" value="1"
              class="h-10 w-full text-right rounded-xl border border-slate-300 bg-white px-3 text-sm text-slate-900 focus:outline-none
                     dark:border-slate-700 dark:bg-slate-950/40 dark:text-white">
          </div>
          <div class="col-span-2">
            <input name="new_items[0][price]" type="number" min="0"
              class="h-10 w-full text-right rounded-xl border border-slate-300 bg-white px-3 text-sm text-slate-900 placeholder-slate-400 focus:outline-none
                     dark:border-slate-700 dark:bg-slate-950/40 dark:text-white dark:placeholder-slate-500"
              placeholder="(auto)"
              data-new-price
            >
          </div>

          <div class="col-span-11">
            <input name="new_items[0][notes]"
              class="h-10 w-full rounded-xl border border-slate-300 bg-white px-3 text-sm text-slate-900 placeholder-slate-400 focus:outline-none
                     dark:border-slate-700 dark:bg-slate-950/40 dark:text-white dark:placeholder-slate-500"
              placeholder="notes (optional)">
          </div>
          <div class="col-span-1 flex justify-end">
            <button type="button" class="text-slate-500 hover:text-red-600 dark:text-slate-400 dark:hover:text-red-300" data-remove-new title="Remove">✕</button>
          </div>
        </div>

        <div class="flex justify-end">
          <button type="button" id="addNewItemBtn"
            class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-2 text-sm font-semibold text-slate-900 hover:bg-slate-100
                   dark:border-slate-700 dark:bg-white/5 dark:text-white dark:hover:bg-white/10">
            + Add Row
          </button>
        </div>
      </div>
    </div>

    {{-- Add custom/sewa --}}
    <div class="rounded-2xl border border-slate-200 bg-white overflow-hidden
                dark:border-slate-700/60 dark:bg-slate-900/40">
      <div class="px-6 py-5 border-b border-slate-200
                  dark:border-slate-700/60">
        <div class="text-sm font-bold text-slate-900 dark:text-white">Add Custom / Sewa (Free Text)</div>
        <div class="text-xs text-slate-600 dark:text-slate-400 mt-1">Dipakai untuk item yang tidak ada di inventory (partner rental, transport, dll).</div>
      </div>

      <div class="p-6 space-y-3" id="customItemsWrap">
        <div class="grid grid-cols-12 gap-2 text-xs text-slate-500 dark:text-slate-400 uppercase font-semibold">
          <div class="col-span-6">Name</div>
          <div class="col-span-2">Unit</div>
          <div class="col-span-2 text-right">Qty</div>
          <div class="col-span-2 text-right">Price</div>
        </div>

        {{-- seed 1 row (safe: will be disabled if empty on submit) --}}
        <div class="grid grid-cols-12 gap-2 items-center" data-custom-row>
          <div class="col-span-6">
            <input name="custom_items[0][name]"
              class="h-10 w-full rounded-xl border border-slate-300 bg-white px-3 text-sm text-slate-900 placeholder-slate-400 focus:outline-none
                     dark:border-slate-700 dark:bg-slate-950/40 dark:text-white dark:placeholder-slate-500"
              placeholder="contoh: Sewa Truk / Sewa Monitor / Sewa Genset 20 KVA">
          </div>
          <div class="col-span-2">
            <select name="custom_items[0][unit]"
              class="h-10 w-full rounded-xl border border-slate-300 bg-white px-3 text-sm text-slate-900 focus:outline-none
                     dark:border-slate-700 dark:bg-slate-950/40 dark:text-white">
              @foreach($units as $u)<option value="{{ $u }}">{{ $u }}</option>@endforeach
            </select>
          </div>
          <div class="col-span-2">
            <input name="custom_items[0][quantity]" type="number" min="1" value="1"
              class="h-10 w-full text-right rounded-xl border border-slate-300 bg-white px-3 text-sm text-slate-900 focus:outline-none
                     dark:border-slate-700 dark:bg-slate-950/40 dark:text-white">
          </div>
          <div class="col-span-2">
            <input name="custom_items[0][price]" type="number" min="0" value="0"
              class="h-10 w-full text-right rounded-xl border border-slate-300 bg-white px-3 text-sm text-slate-900 focus:outline-none
                     dark:border-slate-700 dark:bg-slate-950/40 dark:text-white">
          </div>

          <div class="col-span-11">
            <input name="custom_items[0][notes]"
              class="h-10 w-full rounded-xl border border-slate-300 bg-white px-3 text-sm text-slate-900 placeholder-slate-400 focus:outline-none
                     dark:border-slate-700 dark:bg-slate-950/40 dark:text-white dark:placeholder-slate-500"
              placeholder="notes (optional)">
          </div>
          <div class="col-span-1 flex justify-end">
            <button type="button" class="text-slate-500 hover:text-red-600 dark:text-slate-400 dark:hover:text-red-300" data-remove-custom title="Remove">✕</button>
          </div>
        </div>

        <div class="flex justify-end">
          <button type="button" id="addCustomItemBtn"
            class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-2 text-sm font-semibold text-slate-900 hover:bg-slate-100
                   dark:border-slate-700 dark:bg-white/5 dark:text-white dark:hover:bg-white/10">
            + Add Row
          </button>
        </div>
      </div>
    </div>

    {{-- Live summary --}}
    <div class="rounded-2xl border border-slate-200 bg-white overflow-hidden
                dark:border-slate-700/60 dark:bg-slate-900/40">
      <div class="px-6 py-5 border-b border-slate-200 flex items-center justify-between
                  dark:border-slate-700/60">
        <div class="text-sm font-bold text-slate-900 dark:text-white">Live Total (After Revision)</div>
        <div class="text-xs text-slate-600 dark:text-slate-400">Auto update saat qty/price berubah</div>
      </div>

      <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-4">
        <div class="rounded-xl border border-slate-200 bg-slate-50 p-4
                    dark:border-slate-700/60 dark:bg-slate-950/30">
          <div class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase">Equipment Subtotal</div>
          <div class="mt-2 text-2xl font-bold text-slate-900 dark:text-white" id="sumEquipment">Rp 0</div>
          <div class="mt-2 text-xs text-slate-500 dark:text-slate-400">formula: Σ(qty × price × {{ $mult }})</div>
        </div>

        <div class="rounded-xl border border-slate-200 bg-slate-50 p-4 space-y-2
                    dark:border-slate-700/60 dark:bg-slate-950/30">
          <div class="flex items-center justify-between text-sm">
            <span class="text-slate-700 dark:text-slate-300">Labor</span>
            <span class="text-slate-900 dark:text-white font-semibold" id="sumLabor">Rp {{ number_format($labor,0,',','.') }}</span>
          </div>
          <div class="flex items-center justify-between text-sm">
            <span class="text-slate-700 dark:text-slate-300">Transport</span>
            <span class="text-slate-900 dark:text-white font-semibold" id="sumTransport">Rp {{ number_format($transport,0,',','.') }}</span>
          </div>
          <div class="flex items-center justify-between text-sm">
            <span class="text-slate-700 dark:text-slate-300">Operational ({{ $opPercent }}%)</span>
            <span class="text-slate-900 dark:text-white font-semibold" id="sumOperational">Rp 0</span>
          </div>
          <div class="flex items-center justify-between text-sm">
            <span class="text-slate-700 dark:text-slate-300">Markup ({{ $markupPercent }}%)</span>
            <span class="text-slate-900 dark:text-white font-semibold" id="sumMarkup">Rp 0</span>
          </div>

          <div class="pt-2 mt-2 border-t border-slate-200 flex items-center justify-between
                      dark:border-slate-700/60">
            <span class="text-slate-900 dark:text-white font-bold">Grand Total</span>
            <span class="text-slate-900 dark:text-white font-bold text-lg" id="sumGrand">Rp 0</span>
          </div>
        </div>
      </div>
    </div>

    <div class="flex justify-end gap-2">
      <a href="{{ route('estimations.show', $estimation->id) }}"
        class="h-11 inline-flex items-center rounded-xl border border-slate-200 bg-white px-5 text-sm font-semibold text-slate-900 hover:bg-slate-50
               dark:border-slate-700 dark:bg-white/5 dark:text-white dark:hover:bg-white/10">
        Cancel
      </a>

      <button type="submit"
        class="h-11 inline-flex items-center rounded-xl bg-blue-600 px-6 text-sm font-semibold text-white hover:bg-blue-700 shadow-sm">
        Save Revision
      </button>
    </div>

  </form>
</div>

<script>
(function(){
  const invMap = @json($invMap);
  const mult = {{ (int)$mult }};
  const labor = {{ (int)$labor }};
  const transport = {{ (int)$transport }};
  const opPercent = {{ (float)$opPercent }};
  const markupPercent = {{ (float)$markupPercent }};

  const removedBucket = document.getElementById('removedBucket');

  const fmt = (n) => 'Rp ' + (Math.max(0, Math.round(n))).toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');

  function toInt(v) {
    const n = parseInt(String(v ?? '').replace(/[^\d-]/g,''), 10);
    return Number.isFinite(n) ? n : 0;
  }

  function removeExistingRow(row) {
    if (!row || row.dataset.removed === '1') return;
    row.dataset.removed = '1';

    const idx = row.getAttribute('data-idx');
    const id  = row.querySelector('[data-id]')?.value;

    const wrap = document.createElement('div');
    wrap.innerHTML = `
      <input type="hidden" name="items[${idx}][id]" value="${id ?? ''}">
      <input type="hidden" name="items[${idx}][quantity]" value="0">
    `;
    removedBucket.appendChild(wrap);

    row.remove();
  }

  function calcExistingRows() {
    let equipmentSum = 0;

    document.querySelectorAll('[data-existing-row]').forEach(row => {
      const eq  = row.querySelector('[data-eq]');
      const qty = row.querySelector('[data-qty]');
      const pr  = row.querySelector('[data-price]');
      const availEl = row.querySelector('[data-avail]');
      const shortEl = row.querySelector('[data-shortage]');
      const totalEl = row.querySelector('[data-line-total]');

      const name = (eq?.value || '').trim();
      const q = toInt(qty?.value);
      const p = toInt(pr?.value);

      let avail = 0;
      if (invMap[name]) avail = toInt(invMap[name].qty);
      if (availEl) availEl.textContent = String(avail);

      const shortage = Math.max(0, q - avail);
      if (shortEl) {
        shortEl.innerHTML = shortage > 0
          ? `<span class="inline-flex rounded-full px-2 py-1 text-xs font-semibold bg-red-50 text-red-700 border border-red-200 dark:bg-red-500/10 dark:text-red-300 dark:border-red-500/20">${shortage}</span>`
          : `<span class="text-slate-500 dark:text-slate-400">0</span>`;
      }

      if (q === 0) {
        removeExistingRow(row);
        return;
      }

      const line = q * p * mult;
      if (totalEl) totalEl.textContent = fmt(line);

      equipmentSum += line;
    });

    return equipmentSum;
  }

  function calcNewRows() {
    let equipmentSum = 0;

    document.querySelectorAll('[data-new-row]').forEach(row => {
      const eq = row.querySelector('[data-new-eq]');
      const qty = row.querySelector('input[name*="[quantity]"]');
      const price = row.querySelector('[data-new-price]');

      const name = (eq?.value || '').trim();
      const q = toInt(qty?.value);

      if (invMap[name] && price) {
        const invPrice = toInt(invMap[name].price);
        if (String(price.value).trim() === '' || toInt(price.value) === 0) price.value = invPrice;
      }

      const p = toInt(price?.value);
      if (name !== '' && q > 0) equipmentSum += (q * p * mult);
    });

    document.querySelectorAll('[data-custom-row]').forEach(row => {
      const qty = row.querySelector('input[name*="[quantity]"]');
      const price = row.querySelector('input[name*="[price]"]');
      const nameInput = row.querySelector('input[name*="[name]"]');

      const name = (nameInput?.value || '').trim();
      const q = toInt(qty?.value);
      const p = toInt(price?.value);

      if (name !== '' && q > 0) equipmentSum += (q * p * mult);
    });

    return equipmentSum;
  }

  function refreshTotals() {
    const equipmentSum = calcExistingRows() + calcNewRows();

    const operational = Math.round(equipmentSum * (opPercent / 100));
    const subTotal = equipmentSum + labor + transport + operational;
    const markup = Math.round(subTotal * (markupPercent / 100));
    const grand = subTotal + markup;

    document.getElementById('sumEquipment').textContent = fmt(equipmentSum);
    document.getElementById('sumOperational').textContent = fmt(operational);
    document.getElementById('sumMarkup').textContent = fmt(markup);
    document.getElementById('sumGrand').textContent = fmt(grand);
  }

  document.addEventListener('click', (e) => {
    const btn = e.target.closest('[data-remove]');
    if (btn) {
      const row = btn.closest('[data-existing-row]');
      removeExistingRow(row);
      refreshTotals();
      return;
    }

    const rn = e.target.closest('[data-remove-new]');
    if (rn) {
      const row = rn.closest('[data-new-row]');
      if (row) {
        const all = document.querySelectorAll('[data-new-row]');
        if (all.length > 1) row.remove();
        else row.querySelectorAll('input').forEach(i => i.value = '');
      }
      refreshTotals();
      return;
    }

    const rc = e.target.closest('[data-remove-custom]');
    if (rc) {
      const row = rc.closest('[data-custom-row]');
      if (row) {
        const all = document.querySelectorAll('[data-custom-row]');
        if (all.length > 1) row.remove();
        else row.querySelectorAll('input').forEach(i => i.value = '');
      }
      refreshTotals();
      return;
    }
  });

  document.addEventListener('input', (e) => {
    const qty = e.target.closest('[data-qty]');
    if (qty) {
      const row = qty.closest('[data-existing-row]');
      if (toInt(qty.value) === 0) {
        removeExistingRow(row);
        refreshTotals();
        return;
      }
    }

    const eqNew = e.target.closest('[data-new-eq]');
    if (eqNew) {
      const row = eqNew.closest('[data-new-row]');
      const price = row?.querySelector('[data-new-price]');
      const name = (eqNew.value || '').trim();
      if (invMap[name] && price) {
        const invPrice = toInt(invMap[name].price);
        if (String(price.value).trim() === '' || toInt(price.value) === 0) price.value = invPrice;
      }
    }

    refreshTotals();
  });

  document.addEventListener('change', refreshTotals);
  document.addEventListener('keyup', refreshTotals);

  function makeRow(prefix, idx, isInventory) {
    const wrap = document.createElement('div');
    wrap.className = 'grid grid-cols-12 gap-2 items-center';
    wrap.setAttribute(isInventory ? 'data-new-row' : 'data-custom-row', '1');

    const unitOptions = @json($units).map(u => `<option value="${u}">${u}</option>`).join('');

    const baseInput = `h-10 w-full rounded-xl border border-slate-300 bg-white px-3 text-sm text-slate-900 placeholder-slate-400 focus:outline-none
                       dark:border-slate-700 dark:bg-slate-950/40 dark:text-white dark:placeholder-slate-500`;

    const baseSelect = `h-10 w-full rounded-xl border border-slate-300 bg-white px-3 text-sm text-slate-900 focus:outline-none
                        dark:border-slate-700 dark:bg-slate-950/40 dark:text-white`;

    const removeBtn = `text-slate-500 hover:text-red-600 dark:text-slate-400 dark:hover:text-red-300`;

    if (isInventory) {
      wrap.innerHTML = `
        <div class="col-span-6">
          <input name="${prefix}[${idx}][equipment_name]" list="invList" class="${baseInput}"
            placeholder="Pilih dari inventory (autocomplete)" data-new-eq>
        </div>
        <div class="col-span-2">
          <select name="${prefix}[${idx}][unit]" class="${baseSelect}">${unitOptions}</select>
        </div>
        <div class="col-span-2">
          <input name="${prefix}[${idx}][quantity]" type="number" min="1" value="1"
            class="h-10 w-full text-right rounded-xl border border-slate-300 bg-white px-3 text-sm text-slate-900 focus:outline-none
                   dark:border-slate-700 dark:bg-slate-950/40 dark:text-white">
        </div>
        <div class="col-span-2">
          <input name="${prefix}[${idx}][price]" type="number" min="0" class="${baseInput}" placeholder="(auto)" data-new-price>
        </div>
        <div class="col-span-11">
          <input name="${prefix}[${idx}][notes]" class="${baseInput}" placeholder="notes (optional)">
        </div>
        <div class="col-span-1 flex justify-end">
          <button type="button" class="${removeBtn}" data-remove-new title="Remove">✕</button>
        </div>
      `;
    } else {
      wrap.innerHTML = `
        <div class="col-span-6">
          <input name="${prefix}[${idx}][name]" class="${baseInput}" placeholder="Nama item (free text)">
        </div>
        <div class="col-span-2">
          <select name="${prefix}[${idx}][unit]" class="${baseSelect}">${unitOptions}</select>
        </div>
        <div class="col-span-2">
          <input name="${prefix}[${idx}][quantity]" type="number" min="1" value="1"
            class="h-10 w-full text-right rounded-xl border border-slate-300 bg-white px-3 text-sm text-slate-900 focus:outline-none
                   dark:border-slate-700 dark:bg-slate-950/40 dark:text-white">
        </div>
        <div class="col-span-2">
          <input name="${prefix}[${idx}][price]" type="number" min="0" value="0"
            class="h-10 w-full text-right rounded-xl border border-slate-300 bg-white px-3 text-sm text-slate-900 focus:outline-none
                   dark:border-slate-700 dark:bg-slate-950/40 dark:text-white">
        </div>
        <div class="col-span-11">
          <input name="${prefix}[${idx}][notes]" class="${baseInput}" placeholder="notes (optional)">
        </div>
        <div class="col-span-1 flex justify-end">
          <button type="button" class="${removeBtn}" data-remove-custom title="Remove">✕</button>
        </div>
      `;
    }

    return wrap;
  }

  function nextIndex(container, prefixKey) {
    const inputs = container.querySelectorAll(`input[name^="${prefixKey}["]`);
    let max = -1;
    inputs.forEach(inp => {
      const m = inp.name.match(/\[(\d+)\]\[/);
      if (m) max = Math.max(max, parseInt(m[1], 10));
    });
    return max + 1;
  }

  const newWrap = document.getElementById('newItemsWrap');
  const addNew = document.getElementById('addNewItemBtn');
  if (newWrap && addNew) {
    addNew.addEventListener('click', () => {
      const idx = nextIndex(newWrap, 'new_items');
      newWrap.insertBefore(makeRow('new_items', idx, true), addNew.parentElement);
      refreshTotals();
    });
  }

  const custWrap = document.getElementById('customItemsWrap');
  const addCust = document.getElementById('addCustomItemBtn');
  if (custWrap && addCust) {
    addCust.addEventListener('click', () => {
      const idx = nextIndex(custWrap, 'custom_items');
      custWrap.insertBefore(makeRow('custom_items', idx, false), addCust.parentElement);
      refreshTotals();
    });
  }

  // submit sanitizer: disable empty seed rows so they don't validate
  const form = document.getElementById('reviseForm');
  if (form) {
    form.addEventListener('submit', () => {
      document.querySelectorAll('[data-new-row]').forEach(row => {
        const name = (row.querySelector('[data-new-eq]')?.value || '').trim();
        if (name === '') row.querySelectorAll('input,select,textarea').forEach(el => el.disabled = true);
      });

      document.querySelectorAll('[data-custom-row]').forEach(row => {
        const name = (row.querySelector('input[name*="[name]"]')?.value || '').trim();
        if (name === '') row.querySelectorAll('input,select,textarea').forEach(el => el.disabled = true);
      });
    });
  }

  refreshTotals();
})();
</script>
@endsection