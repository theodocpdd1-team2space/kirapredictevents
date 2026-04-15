@extends('layouts.app-shell')
@section('title', 'Cost & Rates')

@section('content')
<div class="space-y-6 max-w-5xl">

  <div>
    <h1 class="text-4xl font-semibold tracking-tight text-slate-900 dark:text-white">Cost & Rates</h1>
    <p class="text-slate-500 dark:text-slate-400 mt-2">
      Atur tarif standar tanpa mengubah kode inference engine (scalable untuk vendor lain).
    </p>
  </div>

  @includeIf('pages.settings._tabs', ['active' => 'cost'])

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

  <form method="POST" action="{{ route('settings.cost.update') }}"
        class="bg-white dark:bg-slate-900 rounded-xl shadow-sm border border-slate-200 dark:border-slate-800 overflow-hidden">
    @csrf
    @method('PATCH')

    {{-- General --}}
    <div class="px-8 py-5 border-b border-slate-200 dark:border-slate-800">
      <h2 class="text-lg font-semibold text-slate-900 dark:text-white">General</h2>
      <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Pengaturan global format biaya.</p>
    </div>

    <div class="p-8 grid grid-cols-1 md:grid-cols-2 gap-6">
      <div>
        <label class="block text-sm font-semibold text-slate-900 dark:text-white mb-2">Currency</label>
        <select name="rates_currency"
                class="w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-950/50 px-4 py-3 text-sm text-slate-900 dark:text-white">
          @foreach(['IDR','USD','SGD','MYR'] as $c)
            <option value="{{ $c }}" @selected(old('rates_currency', $rates_currency ?? 'IDR')===$c)>{{ $c }}</option>
          @endforeach
        </select>
        <p class="mt-2 text-xs text-slate-500 dark:text-slate-400">Saat ini hanya label tampilan (tanpa konversi kurs).</p>
      </div>

      <div>
        <label class="block text-sm font-semibold text-slate-900 dark:text-white mb-2">Operational %</label>
        <input type="number" step="0.1" name="operational_percent"
               value="{{ old('operational_percent', $operational_percent ?? 5) }}"
               class="w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-950/50 px-4 py-3 text-sm text-slate-900 dark:text-white">
        <p class="mt-2 text-xs text-slate-500 dark:text-slate-400">Contoh: 5 berarti 5% dari total equipment.</p>
      </div>

      <div class="md:col-span-2">
        <label class="block text-sm font-semibold text-slate-900 dark:text-white mb-2">Markup %</label>
        <input type="number" step="0.1" name="markup_percent"
               value="{{ old('markup_percent', $markup_percent ?? 0) }}"
               class="w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-950/50 px-4 py-3 text-sm text-slate-900 dark:text-white">
        <p class="mt-2 text-xs text-slate-500 dark:text-slate-400">Opsional (misal project besar 10–20%).</p>
      </div>
    </div>

    {{-- Crew Fee Model --}}
    <div class="px-8 py-5 border-t border-b border-slate-200 dark:border-slate-800">
      <h2 class="text-lg font-semibold text-slate-900 dark:text-white">Crew Fee Model</h2>
      <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
        Pilih cara hitung biaya crew (untuk skalabilitas vendor).
      </p>
    </div>

    <div class="p-8 grid grid-cols-1 md:grid-cols-2 gap-6">
      <div class="md:col-span-2">
        <label class="block text-sm font-semibold text-slate-900 dark:text-white mb-2">Model Pembayaran Karyawan</label>
        <select name="crew_fee_model" id="crewFeeModel"
                class="w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-950/50 px-4 py-3 text-sm text-slate-900 dark:text-white">
          <option value="package_by_participants" @selected(old('crew_fee_model',$crew_fee_model ?? 'package_by_participants')==='package_by_participants')>
            Paket (berdasarkan tier peserta) — sederhana
          </option>
          <option value="per_role_per_day" @selected(old('crew_fee_model',$crew_fee_model ?? '')==='per_role_per_day')>
            Per Role × Per Hari (qty dari rules / override event)
          </option>
          <option value="per_role_per_hour" @selected(old('crew_fee_model',$crew_fee_model ?? '')==='per_role_per_hour')>
            Per Role × Per Jam (qty dari rules / override event)
          </option>
        </select>
      </div>
    </div>

    {{-- Package by participants --}}
    <div id="crewPackageSection">
      <div class="px-8 py-5 border-t border-b border-slate-200 dark:border-slate-800">
        <h2 class="text-lg font-semibold text-slate-900 dark:text-white">Labor Base (Package by Participants)</h2>
        <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Tarif crew “paket” per tier peserta.</p>
      </div>

      <div class="p-8 grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
          <label class="block text-sm font-semibold text-slate-900 dark:text-white mb-2">Tier 1 (0–100 pax)</label>
          <input type="number" name="labor_t1" value="{{ old('labor_t1', $labor_t1 ?? 600000) }}"
                 class="w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-950/50 px-4 py-3 text-sm text-slate-900 dark:text-white">
        </div>
        <div>
          <label class="block text-sm font-semibold text-slate-900 dark:text-white mb-2">Tier 2 (101–300 pax)</label>
          <input type="number" name="labor_t2" value="{{ old('labor_t2', $labor_t2 ?? 1200000) }}"
                 class="w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-950/50 px-4 py-3 text-sm text-slate-900 dark:text-white">
        </div>
        <div>
          <label class="block text-sm font-semibold text-slate-900 dark:text-white mb-2">Tier 3 (301–1000 pax)</label>
          <input type="number" name="labor_t3" value="{{ old('labor_t3', $labor_t3 ?? 2500000) }}"
                 class="w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-950/50 px-4 py-3 text-sm text-slate-900 dark:text-white">
        </div>
        <div>
          <label class="block text-sm font-semibold text-slate-900 dark:text-white mb-2">Tier 4 (1001+ pax)</label>
          <input type="number" name="labor_t4" value="{{ old('labor_t4', $labor_t4 ?? 5000000) }}"
                 class="w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-950/50 px-4 py-3 text-sm text-slate-900 dark:text-white">
        </div>
      </div>
    </div>

    {{-- Per role per day --}}
    <div id="crewPerDaySection" class="hidden">
      <div class="px-8 py-5 border-t border-b border-slate-200 dark:border-slate-800">
        <h2 class="text-lg font-semibold text-slate-900 dark:text-white">Crew Rates (Per Role × Per Day)</h2>
        <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Tarif per orang per hari.</p>
      </div>

      <div class="p-8 grid grid-cols-1 md:grid-cols-3 gap-6">
        <div>
          <label class="block text-sm font-semibold text-slate-900 dark:text-white mb-2">Operator / Day</label>
          <input type="number" name="crew_operator_rate_day" value="{{ old('crew_operator_rate_day', $crew_operator_rate_day ?? 350000) }}"
                 class="w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-950/50 px-4 py-3 text-sm text-slate-900 dark:text-white">
        </div>
        <div>
          <label class="block text-sm font-semibold text-slate-900 dark:text-white mb-2">Engineer / Day</label>
          <input type="number" name="crew_engineer_rate_day" value="{{ old('crew_engineer_rate_day', $crew_engineer_rate_day ?? 500000) }}"
                 class="w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-950/50 px-4 py-3 text-sm text-slate-900 dark:text-white">
        </div>
        <div>
          <label class="block text-sm font-semibold text-slate-900 dark:text-white mb-2">Stagehand / Day</label>
          <input type="number" name="crew_stage_rate_day" value="{{ old('crew_stage_rate_day', $crew_stage_rate_day ?? 250000) }}"
                 class="w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-950/50 px-4 py-3 text-sm text-slate-900 dark:text-white">
        </div>
      </div>
    </div>

    {{-- Per role per hour --}}
    <div id="crewPerHourSection" class="hidden">
      <div class="px-8 py-5 border-t border-b border-slate-200 dark:border-slate-800">
        <h2 class="text-lg font-semibold text-slate-900 dark:text-white">Crew Rates (Per Role × Per Hour)</h2>
        <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Tarif per orang per jam.</p>
      </div>

      <div class="p-8 grid grid-cols-1 md:grid-cols-3 gap-6">
        <div>
          <label class="block text-sm font-semibold text-slate-900 dark:text-white mb-2">Operator / Hour</label>
          <input type="number" name="crew_operator_rate_hour" value="{{ old('crew_operator_rate_hour', $crew_operator_rate_hour ?? 60000) }}"
                 class="w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-950/50 px-4 py-3 text-sm text-slate-900 dark:text-white">
        </div>
        <div>
          <label class="block text-sm font-semibold text-slate-900 dark:text-white mb-2">Engineer / Hour</label>
          <input type="number" name="crew_engineer_rate_hour" value="{{ old('crew_engineer_rate_hour', $crew_engineer_rate_hour ?? 90000) }}"
                 class="w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-950/50 px-4 py-3 text-sm text-slate-900 dark:text-white">
        </div>
        <div>
          <label class="block text-sm font-semibold text-slate-900 dark:text-white mb-2">Stagehand / Hour</label>
          <input type="number" name="crew_stage_rate_hour" value="{{ old('crew_stage_rate_hour', $crew_stage_rate_hour ?? 45000) }}"
                 class="w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-950/50 px-4 py-3 text-sm text-slate-900 dark:text-white">
        </div>
      </div>
    </div>

    {{-- Duration Block Multipliers --}}
    <div class="px-8 py-5 border-t border-b border-slate-200 dark:border-slate-800">
      <h2 class="text-lg font-semibold text-slate-900 dark:text-white">Duration Block Multipliers</h2>
      <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
        Pengali berdasarkan jam kerja per hari (hours_per_day).
      </p>
    </div>

    <div class="p-8 grid grid-cols-1 md:grid-cols-3 gap-6">
      <div>
        <label class="block text-sm font-semibold text-slate-900 dark:text-white mb-2">Block 1 (≤4h)</label>
        <input type="number" step="0.1" name="duration_block_1"
               value="{{ old('duration_block_1', $duration_block_1 ?? 1) }}"
               class="w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-950/50 px-4 py-3 text-sm text-slate-900 dark:text-white">
      </div>
      <div>
        <label class="block text-sm font-semibold text-slate-900 dark:text-white mb-2">Block 2 (5–8h)</label>
        <input type="number" step="0.1" name="duration_block_2"
               value="{{ old('duration_block_2', $duration_block_2 ?? 2) }}"
               class="w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-950/50 px-4 py-3 text-sm text-slate-900 dark:text-white">
      </div>
      <div>
        <label class="block text-sm font-semibold text-slate-900 dark:text-white mb-2">Block 3 (&gt;8h)</label>
        <input type="number" step="0.1" name="duration_block_3"
               value="{{ old('duration_block_3', $duration_block_3 ?? 3) }}"
               class="w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-950/50 px-4 py-3 text-sm text-slate-900 dark:text-white">
      </div>
    </div>

    {{-- Transport --}}
    <div class="px-8 py-5 border-t border-b border-slate-200 dark:border-slate-800">
      <h2 class="text-lg font-semibold text-slate-900 dark:text-white">Transport</h2>
      <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Free cities + custom city rate + fallback.</p>
    </div>

    <div class="p-8 space-y-6">
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
          <label class="block text-sm font-semibold text-slate-900 dark:text-white mb-2">Transport Outdoor</label>
          <input type="number" name="transport_outdoor"
                 value="{{ old('transport_outdoor', $transport_outdoor ?? 600000) }}"
                 class="w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-950/50 px-4 py-3 text-sm text-slate-900 dark:text-white">
        </div>

        <div>
          <label class="block text-sm font-semibold text-slate-900 dark:text-white mb-2">Other City Default</label>
          <input type="number" name="transport_other"
                 value="{{ old('transport_other', $transport_other ?? 300000) }}"
                 class="w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-950/50 px-4 py-3 text-sm text-slate-900 dark:text-white">
          <p class="mt-2 text-xs text-slate-500 dark:text-slate-400">Fallback jika kota tidak ada di free/custom.</p>
        </div>

        <div class="md:col-span-2">
          <label class="block text-sm font-semibold text-slate-900 dark:text-white mb-2">Free Cities (comma separated)</label>
          <input name="transport_free_cities"
                 value="{{ old('transport_free_cities', $transport_free_cities ?? 'surabaya,sidoarjo,gresik') }}"
                 class="w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-950/50 px-4 py-3 text-sm text-slate-900 dark:text-white">
          <p class="mt-2 text-xs text-slate-500 dark:text-slate-400">Contoh: surabaya,sidoarjo,gresik</p>
        </div>
      </div>

      <div class="rounded-xl border border-slate-200 dark:border-slate-800 overflow-hidden">
        <div class="px-5 py-4 bg-slate-50 dark:bg-slate-950/40 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
          <div>
            <div class="text-sm font-semibold text-slate-900 dark:text-white">Custom City Rates</div>
            <div class="text-xs text-slate-500 dark:text-slate-400">Jakarta bisa beda dengan kota lain.</div>
          </div>
          <button type="button" onclick="addCityRow()"
                  class="rounded-lg bg-slate-900 text-white px-4 py-2 text-sm font-semibold hover:bg-black dark:bg-slate-700 dark:hover:bg-slate-600">
            + Add City
          </button>
        </div>

        <div class="p-5 space-y-3" id="cityRows">
          @php
            $pairs = old('city_name') ? array_map(null, old('city_name', []), old('city_rate', [])) : null;
            $arr = $transport_city_rates_arr ?? [];
          @endphp

          @if($pairs)
            @foreach($pairs as $pair)
              <div class="grid grid-cols-1 md:grid-cols-12 gap-3 items-center cityRow">
                <div class="md:col-span-7">
                  <input name="city_name[]" value="{{ $pair[0] }}" placeholder="e.g. jakarta"
                         class="w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-950/50 px-4 py-3 text-sm text-slate-900 dark:text-white">
                </div>
                <div class="md:col-span-4">
                  <input type="number" name="city_rate[]" value="{{ $pair[1] }}" placeholder="e.g. 1000000"
                         class="w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-950/50 px-4 py-3 text-sm text-slate-900 dark:text-white">
                </div>
                <div class="md:col-span-1 flex justify-end">
                  <button type="button" onclick="removeCityRow(this)"
                          class="text-red-600 dark:text-red-400 text-sm font-semibold hover:underline">Remove</button>
                </div>
              </div>
            @endforeach
          @else
            @forelse($arr as $city => $rate)
              <div class="grid grid-cols-1 md:grid-cols-12 gap-3 items-center cityRow">
                <div class="md:col-span-7">
                  <input name="city_name[]" value="{{ $city }}" placeholder="e.g. jakarta"
                         class="w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-950/50 px-4 py-3 text-sm text-slate-900 dark:text-white">
                </div>
                <div class="md:col-span-4">
                  <input type="number" name="city_rate[]" value="{{ $rate }}" placeholder="e.g. 1000000"
                         class="w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-950/50 px-4 py-3 text-sm text-slate-900 dark:text-white">
                </div>
                <div class="md:col-span-1 flex justify-end">
                  <button type="button" onclick="removeCityRow(this)"
                          class="text-red-600 dark:text-red-400 text-sm font-semibold hover:underline">Remove</button>
                </div>
              </div>
            @empty
              <div class="grid grid-cols-1 md:grid-cols-12 gap-3 items-center cityRow">
                <div class="md:col-span-7">
                  <input name="city_name[]" value="" placeholder="e.g. jakarta"
                         class="w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-950/50 px-4 py-3 text-sm text-slate-900 dark:text-white">
                </div>
                <div class="md:col-span-4">
                  <input type="number" name="city_rate[]" value="" placeholder="e.g. 1000000"
                         class="w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-950/50 px-4 py-3 text-sm text-slate-900 dark:text-white">
                </div>
                <div class="md:col-span-1 flex justify-end">
                  <button type="button" onclick="removeCityRow(this)"
                          class="text-red-600 dark:text-red-400 text-sm font-semibold hover:underline">Remove</button>
                </div>
              </div>
            @endforelse
          @endif
        </div>
      </div>
    </div>

    <div class="px-8 py-6 border-t border-slate-200 dark:border-slate-800 flex justify-end gap-3">
      <button type="submit"
              class="rounded-lg bg-blue-600 px-6 py-3 text-sm font-semibold text-white hover:bg-blue-700 shadow-sm">
        Save Changes
      </button>
    </div>

  </form>
</div>

<script>
function addCityRow() {
  const wrap = document.getElementById('cityRows');
  const row = document.createElement('div');
  row.className = 'grid grid-cols-1 md:grid-cols-12 gap-3 items-center cityRow';
  row.innerHTML = `
    <div class="md:col-span-7">
      <input name="city_name[]" value="" placeholder="e.g. jakarta"
        class="w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-950/50 px-4 py-3 text-sm text-slate-900 dark:text-white">
    </div>
    <div class="md:col-span-4">
      <input type="number" name="city_rate[]" value="" placeholder="e.g. 1000000"
        class="w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-950/50 px-4 py-3 text-sm text-slate-900 dark:text-white">
    </div>
    <div class="md:col-span-1 flex justify-end">
      <button type="button" onclick="removeCityRow(this)"
        class="text-red-600 dark:text-red-400 text-sm font-semibold hover:underline">Remove</button>
    </div>
  `;
  wrap.appendChild(row);
}
function removeCityRow(btn) {
  const row = btn.closest('.cityRow');
  if (row) row.remove();
}

(function(){
  const model = document.getElementById('crewFeeModel');
  const pkg = document.getElementById('crewPackageSection');
  const day = document.getElementById('crewPerDaySection');
  const hour = document.getElementById('crewPerHourSection');

  function sync() {
    const v = model ? model.value : 'package_by_participants';
    if (pkg) pkg.classList.toggle('hidden', v !== 'package_by_participants');
    if (day) day.classList.toggle('hidden', v !== 'per_role_per_day');
    if (hour) hour.classList.toggle('hidden', v !== 'per_role_per_hour');
  }

  if (model) model.addEventListener('change', sync);
  sync();
})();
</script>
@endsection