@extends('layouts.app-shell')
@section('title','Wizard Preview')

@section('content')
@php
  $facts = $facts ?? [];
  $generated = $generated ?? [];
  $wizardPayload = $wizardPayload ?? [];
@endphp

<div class="max-w-6xl mx-auto space-y-6">

  <div>
    <h1 class="text-4xl font-semibold tracking-tight text-slate-900 dark:text-white">Preview Generated Rules</h1>
    <p class="text-slate-500 dark:text-slate-400 mt-2">
      Pilih rules yang mau disimpan, lalu klik <b>Save Selected</b>.
    </p>
  </div>

  <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-5">
    <div class="font-bold text-slate-900 dark:text-white">Facts</div>
    <pre class="mt-3 text-xs overflow-auto p-4 rounded-xl bg-slate-900 text-green-300" style="max-height:260px;">{{ json_encode($facts, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES) }}</pre>
  </div>

  @if(empty($generated))
    <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-6">
      <div class="text-sm text-slate-700 dark:text-slate-300">Tidak ada rule yang tergenerate (mungkin action qty = 0 semua).</div>
      <div class="mt-4">
        <a href="{{ route('settings.rules.wizard.create') }}"
           class="inline-flex items-center justify-center rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 px-5 py-3 text-sm font-semibold text-slate-900 dark:text-white hover:bg-slate-50 dark:hover:bg-slate-800">
          Back to Wizard
        </a>
      </div>
    </div>
  @else
    <form method="POST" action="{{ route('settings.rules.wizard.store') }}" class="space-y-6">
      @csrf

      <input type="hidden" name="facts_json" value="{{ $wizardPayload['facts_json'] ?? json_encode($facts) }}">
      <input type="hidden" name="rules_json" value="{{ json_encode($generated) }}">

      <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between gap-3">
          <div class="text-sm font-bold text-slate-900 dark:text-white">
            Generated: {{ count($generated) }} rules
          </div>
          <div class="flex items-center gap-2">
            <button type="button" id="selectAllBtn"
              class="rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 px-4 py-2 text-sm font-semibold text-slate-900 dark:text-white hover:bg-slate-50 dark:hover:bg-slate-800">
              Select All
            </button>
            <button type="button" id="clearAllBtn"
              class="rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 px-4 py-2 text-sm font-semibold text-slate-900 dark:text-white hover:bg-slate-50 dark:hover:bg-slate-800">
              Clear
            </button>
          </div>
        </div>

        <div class="overflow-x-auto">
          <table class="w-full text-sm">
            <thead class="bg-slate-50 dark:bg-slate-950 border-b border-slate-200 dark:border-slate-800">
              <tr>
                <th class="px-4 py-3 text-left">
                  <input type="checkbox" id="checkAll" class="h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                </th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase">Condition</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase">Category</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase">Priority</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
              @foreach($generated as $idx => $r)
                @php
                  $cond = ($r['condition_field'] ?? '') . ' ' . ($r['operator'] ?? '') . ' ' . ($r['value'] ?? '');
                  $cat  = $r['category'] ?? '-';
                  $prio = $r['priority'] ?? 100;
                  $actions = $r['action'] ?? [];
                @endphp
                <tr class="hover:bg-slate-50 dark:hover:bg-slate-900/40">
                  <td class="px-4 py-3">
                    <input type="checkbox" class="rowCheck h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500"
                           name="selected[]" value="{{ $idx }}" checked>
                  </td>
                  <td class="px-4 py-3">
                    <div class="font-semibold text-slate-900 dark:text-white">{{ $cond }}</div>
                  </td>
                  <td class="px-4 py-3">
                    <span class="inline-flex rounded-full bg-slate-100 dark:bg-slate-800 px-3 py-1 text-xs font-semibold text-slate-700 dark:text-slate-200">
                      {{ $cat }}
                    </span>
                  </td>
                  <td class="px-4 py-3 text-slate-700 dark:text-slate-200">{{ $prio }}</td>
                  <td class="px-4 py-3">
                    <div class="space-y-1">
                      @foreach($actions as $a)
                        @php $type = strtoupper((string)($a['type'] ?? '')); @endphp
                        @if($type === 'ADD_EQUIPMENT')
                          <div class="text-xs text-slate-700 dark:text-slate-200">
                            + Equip: <b>{{ $a['name'] ?? '-' }}</b> × {{ $a['qty'] ?? 0 }}
                          </div>
                        @elseif($type === 'ADD_CREW')
                          <div class="text-xs text-slate-700 dark:text-slate-200">
                            + Crew: <b>{{ $a['role'] ?? '-' }}</b> × {{ $a['qty'] ?? 0 }}
                          </div>
                        @endif
                      @endforeach
                    </div>
                  </td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </div>

      <div class="flex flex-col sm:flex-row gap-2 justify-end">
        <a href="{{ route('settings.rules.wizard.create') }}"
           class="inline-flex items-center justify-center rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 px-5 py-3 text-sm font-semibold text-slate-900 dark:text-white hover:bg-slate-50 dark:hover:bg-slate-800">
          Back
        </a>
        <button type="submit"
                class="inline-flex items-center justify-center rounded-xl bg-blue-600 px-5 py-3 text-sm font-semibold text-white hover:bg-blue-700 shadow-sm">
          Save Selected
        </button>
      </div>
    </form>
  @endif

</div>

<script>
(function () {
  const checkAll = document.getElementById('checkAll');
  const selectAllBtn = document.getElementById('selectAllBtn');
  const clearAllBtn = document.getElementById('clearAllBtn');

  const rows = () => Array.from(document.querySelectorAll('.rowCheck'));

  function refreshMaster() {
    const r = rows();
    const checked = r.filter(x => x.checked).length;
    if (!checkAll) return;
    checkAll.checked = r.length > 0 && checked === r.length;
    checkAll.indeterminate = checked > 0 && checked < r.length;
  }

  if (checkAll) {
    checkAll.addEventListener('change', () => {
      rows().forEach(x => x.checked = checkAll.checked);
      refreshMaster();
    });
  }

  if (selectAllBtn) {
    selectAllBtn.addEventListener('click', () => {
      rows().forEach(x => x.checked = true);
      refreshMaster();
    });
  }

  if (clearAllBtn) {
    clearAllBtn.addEventListener('click', () => {
      rows().forEach(x => x.checked = false);
      refreshMaster();
    });
  }

  document.addEventListener('change', (e) => {
    if (e.target && e.target.classList && e.target.classList.contains('rowCheck')) {
      refreshMaster();
    }
  });

  refreshMaster();
})();
</script>
@endsection