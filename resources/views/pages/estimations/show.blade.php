{{-- resources/views/pages/estimations/show.blade.php --}}

@extends(($publicMode ?? false) ? 'layouts.public-shell' : 'layouts.app-shell')
@section('title','Estimation Result')

@php
  $publicMode = (bool)($publicMode ?? false);
  $viewMode   = request('view', 'final');

  $bizName = \App\Models\Setting::getValue('business_name', 'Kira — Event Multimedia DSS');
  $bizLogo = \App\Models\Setting::getValue('business_logo', 'images/logo-kira.png');

  // ✅ print mode: ?print=detail|summary → auto hide UI (treat as public)
  $printMode   = request('print'); // detail|summary|null
  $isPrintView = in_array($printMode, ['detail','summary'], true);
  if ($isPrintView) $publicMode = true;

  $badge = match($estimation->status) {
    'approved' => 'bg-green-100 text-green-700 dark:bg-green-500/20 dark:text-green-400',
    'pending'  => 'bg-orange-100 text-orange-700 dark:bg-orange-500/20 dark:text-orange-400',
    'rejected' => 'bg-red-100 text-red-700 dark:bg-red-500/20 dark:text-red-400',
    'revised'  => 'bg-blue-100 text-blue-700 dark:bg-blue-500/20 dark:text-blue-400',
    default    => 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-400',
  };

  $acc = $estimation->accuracy;
  $accBadge = match($acc) {
    'accurate'       => 'bg-green-100 text-green-700 dark:bg-green-500/20 dark:text-green-400',
    'underestimated' => 'bg-red-100 text-red-700 dark:bg-red-500/20 dark:text-red-400',
    'overestimated'  => 'bg-purple-100 text-purple-700 dark:bg-purple-500/20 dark:text-purple-400',
    default          => 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-400',
  };
  $accLabel = match($acc) {
    'accurate'       => 'Accurate',
    'underestimated' => 'Underestimated',
    'overestimated'  => 'Overestimated',
    default          => null,
  };

  // breakdown
  $b = is_array($estimation->breakdown)
    ? $estimation->breakdown
    : (json_decode($estimation->breakdown ?? '[]', true) ?: []);

  // Toggle url final/original
  $selfUrlFinal = $publicMode
      ? route('share.estimations.show', $estimation->share_token)
      : route('estimations.show', $estimation->id);

  $selfUrlOriginal = $publicMode
      ? (route('share.estimations.show', $estimation->share_token) . '?view=original')
      : (route('estimations.show', $estimation->id) . '?view=original');

  // parsed tags
  $parsedTags = is_array($estimation->parsed_tags ?? null)
    ? $estimation->parsed_tags
    : (json_decode($estimation->parsed_tags ?? '[]', true) ?: []);

  // trace from controller (owner only)
  $traceArr = $traceArr ?? [];

  // share url
  $shareUrl = $estimation->share_token
    ? route('share.estimations.show', $estimation->share_token)
    : null;

  // print links
  $printDetailUrl  = route('estimations.show', $estimation->id) . '?print=detail';
  $printSummaryUrl = route('estimations.show', $estimation->id) . '?print=summary';
@endphp

@section('content')
<div class="space-y-6 max-w-6xl mx-auto">

  {{-- HEADER --}}
  <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
    <div>
      <h1 class="text-4xl font-semibold tracking-tight text-slate-900 dark:text-white">
        Estimation Result
      </h1>

      <p class="text-slate-500 dark:text-slate-400 mt-2">
        Invoice-style report generated from rules + inventory validation
      </p>

      <div class="mt-3 flex flex-wrap items-center gap-2">
        @if($hasShortage)
          <span class="inline-flex items-center rounded-full bg-red-50 dark:bg-red-500/10 px-3 py-1 text-xs font-semibold text-red-700 dark:text-red-400 border border-red-200 dark:border-red-500/20">
            Inventory shortage detected
          </span>
        @else
          <span class="inline-flex items-center rounded-full bg-green-50 dark:bg-green-500/10 px-3 py-1 text-xs font-semibold text-green-700 dark:text-green-400 border border-green-200 dark:border-green-500/20">
            All equipment available
          </span>
        @endif

        <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold {{ $badge }}">
          {{ ucfirst($estimation->status) }}
        </span>

        @if(!empty($accLabel))
          <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold {{ $accBadge }}">
            {{ $accLabel }}
          </span>
        @endif

        {{-- Toggle Final / Original (hide on print) --}}
        @if(!$isPrintView && in_array($estimation->status, ['revised','approved'], true))
          <div class="inline-flex rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 overflow-hidden shadow-sm">
            <a href="{{ $selfUrlFinal }}"
               class="px-4 py-2 text-sm font-semibold
                      {{ $viewMode==='final' ? 'bg-blue-600 text-white' : 'text-slate-900 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800' }}">
              Final
            </a>
            <a href="{{ $selfUrlOriginal }}"
               class="px-4 py-2 text-sm font-semibold
                      {{ $viewMode==='original' ? 'bg-blue-600 text-white' : 'text-slate-900 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800' }}">
              Original
            </a>
          </div>
        @endif

        @if($publicMode)
          <span class="inline-flex items-center rounded-full bg-slate-50 dark:bg-slate-800 px-3 py-1 text-xs font-semibold text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700">
            Shared Link
          </span>
        @endif
      </div>

      {{-- Parsed Tags --}}
      @if(!empty($parsedTags))
        <div class="mt-4">
          <div class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase">Parsed Tags</div>
          <div class="mt-2 flex flex-wrap gap-2">
            @foreach($parsedTags as $t)
              <span class="inline-flex items-center rounded-full border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 px-3 py-1 text-xs font-semibold text-slate-700 dark:text-slate-200">
                {{ $t }}
              </span>
            @endforeach
          </div>
        </div>
      @endif

      {{-- Revision note --}}
      @if(!empty($estimation->revision_note))
        <div class="mt-4 rounded-xl border border-blue-200 dark:border-blue-500/30 bg-blue-50 dark:bg-blue-500/10 p-4 text-sm text-blue-900 dark:text-blue-200">
          <div class="font-semibold">Revision Note</div>
          <div class="mt-1">{{ $estimation->revision_note }}</div>
        </div>
      @endif
    </div>

    {{-- ACTIONS --}}
    <div class="no-print w-full lg:w-auto">
      <div class="flex flex-col gap-3">

        {{-- ADMIN actions (hide when public) --}}
        @unless($publicMode)
          <div class="flex flex-wrap items-center justify-start lg:justify-end gap-2">

            {{-- Verify --}}
            <form method="POST" action="{{ route('estimations.status', $estimation->id) }}">
              @csrf
              @method('PATCH')
              <input type="hidden" name="status" value="approved">
              <button type="submit"
                class="h-11 inline-flex items-center gap-2 rounded-xl bg-blue-600 px-4 text-sm font-semibold text-white shadow-sm hover:bg-blue-700">
                Verify
              </button>
            </form>

            {{-- Reject --}}
            <form method="POST" action="{{ route('estimations.status', $estimation->id) }}"
                  onsubmit="return confirm('Reject estimation ini?');">
              @csrf
              @method('PATCH')
              <input type="hidden" name="status" value="rejected">
              <button type="submit"
                class="h-11 inline-flex items-center gap-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 px-4 text-sm font-semibold text-red-600 dark:text-red-400 shadow-sm hover:bg-slate-50 dark:hover:bg-slate-800">
                Reject
              </button>
            </form>

            {{-- Accuracy --}}
            <div class="relative min-w-[220px]">
              <form method="POST" action="{{ route('estimations.accuracy', $estimation->id) }}">
                @csrf
                @method('PATCH')
                <select name="accuracy" onchange="this.form.submit()"
                        class="h-11 w-full appearance-none rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 pl-4 pr-10 text-sm font-semibold text-slate-700 dark:text-slate-300 shadow-sm hover:bg-slate-50 dark:hover:bg-slate-800 focus:outline-none">
                  <option value="" @selected(empty($estimation->accuracy))>Evaluate Accuracy</option>
                  <option value="accurate" @selected($estimation->accuracy==='accurate')>Accurate</option>
                  <option value="underestimated" @selected($estimation->accuracy==='underestimated')>Underestimated</option>
                  <option value="overestimated" @selected($estimation->accuracy==='overestimated')>Overestimated</option>
                </select>
                <span class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-slate-400">
                  <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="m6 9 6 6 6-6"/></svg>
                </span>
              </form>
            </div>
          </div>
        @endunless

        {{-- Secondary --}}
        <div class="flex flex-wrap items-center justify-start lg:justify-end gap-2">

          @unless($publicMode)
            <a href="{{ route('estimations.index') }}"
               class="h-10 inline-flex items-center gap-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 px-3 text-sm font-semibold text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800">
              History
            </a>

            <a href="{{ route('estimations.edit', $estimation->id) }}"
               class="h-10 inline-flex items-center gap-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 px-3 text-sm font-semibold text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800">
              Revise
            </a>
          @endunless

          {{-- ✅ SHARE DROPDOWN --}}
          <div class="relative" id="shareWrap">
            <button type="button" id="shareBtn"
                    class="h-10 inline-flex items-center gap-2 rounded-xl bg-slate-900 dark:bg-slate-800 px-4 text-sm font-semibold text-white shadow-sm hover:bg-slate-800 dark:hover:bg-slate-700">
              Share
              <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path d="m6 9 6 6 6-6"/></svg>
            </button>

            <div id="shareMenu"
                 class="hidden absolute right-0 mt-2 w-64 origin-top-right rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 shadow-lg ring-1 ring-black ring-opacity-5 z-50 overflow-hidden">
              <div class="py-1">

                <a href="{{ route('estimations.pdf', $estimation->id) }}?mode=detail"
                   class="flex items-center gap-2 px-4 py-2 text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800">
                  PDF Detail
                </a>

                <a href="{{ route('estimations.pdf', $estimation->id) }}?mode=summary"
                   class="flex items-center gap-2 px-4 py-2 text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800">
                  PDF Ringkas
                </a>

                <div class="my-1 h-px bg-slate-200 dark:bg-slate-700"></div>

                {{-- Share Link --}}
                @unless($publicMode)
                  @if(empty($estimation->share_token))
                    <form method="POST" action="{{ route('estimations.shareToken', $estimation->id) }}">
                      @csrf
                      <button type="submit"
                              class="w-full text-left flex items-center gap-2 px-4 py-2 text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800">
                        Generate Share Link
                      </button>
                    </form>
                  @else
                    <button type="button"
                            id="copyShareLinkBtn"
                            data-link="{{ $shareUrl }}"
                            class="w-full text-left flex items-center gap-2 px-4 py-2 text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800">
                      Copy Share Link
                    </button>
                  @endif

                  @if(\Illuminate\Support\Facades\Route::has('estimations.wa'))
                    <a href="{{ route('estimations.wa', $estimation->id) }}"
                       class="flex items-center gap-2 px-4 py-2 text-sm text-green-600 hover:bg-green-50 dark:hover:bg-green-500/10">
                      WhatsApp
                    </a>
                  @endif
                @endunless

                <div class="my-1 h-px bg-slate-200 dark:bg-slate-700"></div>

                {{-- Print new tab --}}
                <a href="{{ $printDetailUrl }}" target="_blank"
                   class="flex items-center gap-2 px-4 py-2 text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800">
                  Print Detail
                </a>

                <a href="{{ $printSummaryUrl }}" target="_blank"
                   class="flex items-center gap-2 px-4 py-2 text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800">
                  Print Ringkas
                </a>

              </div>
            </div>
          </div>

        </div>
      </div>
    </div>
  </div>

  {{-- INVOICE CARD --}}
  <div class="bg-white dark:bg-slate-900 rounded-xl shadow-sm border border-slate-200 dark:border-slate-800 overflow-hidden">

    {{-- Invoice head --}}
    <div class="p-6 border-b border-slate-200 dark:border-slate-800 flex items-start justify-between gap-6">
      <div class="flex items-center gap-3">
        <div class="h-11 w-11 rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 flex items-center justify-center overflow-hidden">
          <img src="{{ asset($bizLogo) }}" class="h-8 w-8 object-contain" alt="Logo">
        </div>
        <div>
          <div class="text-lg font-semibold text-slate-900 dark:text-white">{{ $bizName }}</div>
          <div class="text-sm text-slate-500 dark:text-slate-400">Cost estimation report</div>
        </div>
      </div>

      <div class="text-right">
        <div class="text-sm text-slate-500 dark:text-slate-400">Estimation ID</div>
        <div class="font-semibold text-slate-900 dark:text-white">#{{ $estimation->id }}</div>
        <div class="text-sm text-slate-500 dark:text-slate-400 mt-1">Created</div>
        <div class="text-sm font-medium text-slate-900 dark:text-white">
          {{ optional($estimation->created_at)->format('Y-m-d H:i') }}
        </div>
      </div>
    </div>

    {{-- Event summary --}}
    <div class="p-6 grid grid-cols-1 md:grid-cols-3 gap-4">
      <div class="rounded-xl border border-slate-200 dark:border-slate-700/50 bg-slate-50 dark:bg-slate-800/50 p-4">
        <div class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase">Event Type</div>
        <div class="mt-1 font-semibold text-slate-900 dark:text-white">{{ ucfirst($estimation->event->event_type ?? '-') }}</div>
      </div>

      <div class="rounded-xl border border-slate-200 dark:border-slate-700/50 bg-slate-50 dark:bg-slate-800/50 p-4">
        <div class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase">Participants</div>
        <div class="mt-1 font-semibold text-slate-900 dark:text-white">{{ number_format($estimation->event->participants ?? 0) }}</div>
      </div>

      <div class="rounded-xl border border-slate-200 dark:border-slate-700/50 bg-slate-50 dark:bg-slate-800/50 p-4">
        <div class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase">Location</div>
        <div class="mt-1 font-semibold text-slate-900 dark:text-white">{{ ucfirst($estimation->event->location ?? '-') }}</div>
      </div>

      <div class="rounded-xl border border-slate-200 dark:border-slate-700/50 bg-slate-50 dark:bg-slate-800/50 p-4">
        <div class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase">Duration</div>
        <div class="mt-1 font-semibold text-slate-900 dark:text-white">
          {{ (int)($estimation->event->event_days ?? 1) }} day(s) × {{ (int)($estimation->event->hours_per_day ?? 1) }} h/day
          <span class="text-slate-500 dark:text-slate-400 text-xs">({{ (int)($estimation->event->duration ?? 0) }} hours)</span>
        </div>
      </div>

      <div class="rounded-xl border border-slate-200 dark:border-slate-700/50 bg-slate-50 dark:bg-slate-800/50 p-4">
        <div class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase">Service Level</div>
        <div class="mt-1 font-semibold text-slate-900 dark:text-white">{{ ucfirst($estimation->event->service_level ?? '-') }}</div>
      </div>

      <div class="rounded-xl border border-slate-200 dark:border-slate-700/50 bg-slate-50 dark:bg-slate-800/50 p-4">
        <div class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase">Special Requirements</div>
        <div class="mt-1 text-sm text-slate-900 dark:text-white">{{ $estimation->event->special_requirement ?: '-' }}</div>
      </div>
    </div>

    {{-- Equipment table --}}
    <div class="px-6 pb-6">
      <div class="flex items-center justify-between mb-3">
        <h2 class="text-lg font-semibold text-slate-900 dark:text-white">Equipment List</h2>
        <p class="text-sm text-slate-500 dark:text-slate-400">{{ $estimation->details->count() }} items</p>
      </div>

      <div class="overflow-x-auto rounded-xl border border-slate-200 dark:border-slate-800">
        <table class="w-full text-sm">
          <thead class="bg-slate-50 dark:bg-slate-800/50 border-b border-slate-200 dark:border-slate-800">
            <tr>
              <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase">Equipment</th>
              <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase">Need</th>
              <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase">Available</th>
              <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase">Shortage</th>
              <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase">Unit Price</th>
              <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase">Total</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
            @foreach($estimation->details as $d)
              @php
                $qty = $viewMode==='original' ? (int)($d->original_quantity ?? $d->quantity) : (int)$d->quantity;
                $price = $viewMode==='original' ? (int)($d->original_price ?? $d->price) : (int)$d->price;
                $lineTotal = $viewMode==='original' ? (int)($d->original_total ?? $d->total) : (int)$d->total;
              @endphp
              <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50">
                <td class="px-4 py-3 font-semibold text-slate-900 dark:text-white">{{ $d->equipment_name }}</td>
                <td class="px-4 py-3 text-right text-slate-900 dark:text-slate-300">{{ $qty }}</td>
                <td class="px-4 py-3 text-right text-slate-900 dark:text-slate-300">{{ (int)$d->available }}</td>
                <td class="px-4 py-3 text-right">
                  @if((int)$d->shortage > 0)
                    <span class="inline-flex rounded-full bg-red-50 dark:bg-red-500/10 px-2 py-1 text-xs font-semibold text-red-700 dark:text-red-400 border border-red-200 dark:border-red-500/20">
                      {{ (int)$d->shortage }}
                    </span>
                  @else
                    <span class="text-slate-500 dark:text-slate-400">0</span>
                  @endif
                </td>
                <td class="px-4 py-3 text-right text-slate-900 dark:text-slate-300">Rp {{ number_format($price,0,',','.') }}</td>
                <td class="px-4 py-3 text-right font-semibold text-slate-900 dark:text-white">Rp {{ number_format($lineTotal,0,',','.') }}</td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>

    {{-- Cost breakdown --}}
    <div class="border-t border-slate-200 dark:border-slate-800 p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
      <div>
        <h3 class="text-lg font-semibold text-slate-900 dark:text-white">Cost Breakdown</h3>
        <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Summary of estimated costs</p>
      </div>

      <div class="rounded-xl border border-slate-200 dark:border-slate-700/50 bg-slate-50 dark:bg-slate-800/50 p-5 space-y-3">
        <div class="flex items-center justify-between text-sm">
          <span class="text-slate-600 dark:text-slate-400">Equipment</span>
          <span class="font-semibold text-slate-900 dark:text-white">Rp {{ number_format((int)($b['equipment'] ?? 0),0,',','.') }}</span>
        </div>
        <div class="flex items-center justify-between text-sm">
          <span class="text-slate-600 dark:text-slate-400">Labor Crew</span>
          <span class="font-semibold text-slate-900 dark:text-white">Rp {{ number_format((int)($b['labor'] ?? 0),0,',','.') }}</span>
        </div>
        <div class="flex items-center justify-between text-sm">
          <span class="text-slate-600 dark:text-slate-400">Transportation</span>
          <span class="font-semibold text-slate-900 dark:text-white">Rp {{ number_format((int)($b['transport'] ?? 0),0,',','.') }}</span>
        </div>
        <div class="flex items-center justify-between text-sm">
          <span class="text-slate-600 dark:text-slate-400">Operational</span>
          <span class="font-semibold text-slate-900 dark:text-white">Rp {{ number_format((int)($b['operational'] ?? 0),0,',','.') }}</span>
        </div>
        @if(isset($b['markup']))
          <div class="flex items-center justify-between text-sm">
            <span class="text-slate-600 dark:text-slate-400">Markup</span>
            <span class="font-semibold text-slate-900 dark:text-white">Rp {{ number_format((int)($b['markup'] ?? 0),0,',','.') }}</span>
          </div>
        @endif

        <div class="pt-3 mt-3 border-t border-slate-200 dark:border-slate-700 flex items-center justify-between">
          <span class="text-slate-900 dark:text-white font-semibold">Total</span>
          <span class="text-slate-900 dark:text-white font-semibold text-lg">Rp {{ number_format((int)($estimation->total_cost ?? 0),0,',','.') }}</span>
        </div>
      </div>
    </div>
  </div>

  {{-- DECISION TREE (owner only) --}}
  @unless($publicMode)
    @if(!empty($traceArr) && !empty($traceArr['steps']))
      @php
        $steps = $traceArr['steps'] ?? [];
        $stepByName = [];
        foreach ($steps as $s) {
          $name = $s['step'] ?? 'unknown';
          $stepByName[$name] = $s;
        }

        $parserStep = $stepByName['parser'] ?? [];
        $rulesStep  = $stepByName['rules'] ?? [];
        $costStep   = $stepByName['costs'] ?? [];

        $ruleTrace = $rulesStep['rule_trace'] ?? [];
        $matchedRules = [];
        $unmatchedRules = [];
        foreach ($ruleTrace as $r) {
          if (!empty($r['matched'])) $matchedRules[] = $r;
          else $unmatchedRules[] = $r;
        }

        $parserTags2 = is_array($parserStep['tags'] ?? null) ? $parserStep['tags'] : [];
        $cb = $costStep['breakdown'] ?? [];
      @endphp

      <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 p-5">
        <div class="flex items-center justify-between">
          <div>
            <div class="font-semibold text-slate-900 dark:text-white">Decision Tree (Trace)</div>
            <div class="text-xs text-slate-500 dark:text-slate-400 mt-1">
              Visualisasi langkah sistem saat menentukan equipment & biaya.
            </div>
          </div>

          <button type="button"
            class="text-sm font-semibold text-blue-600 dark:text-blue-400"
            onclick="document.querySelectorAll('.dt-node-children').forEach(el => el.classList.toggle('hidden'))">
            Toggle All
          </button>
        </div>

        <div class="mt-4">
          <ul class="dt-tree">
            <li class="dt-node">
              <button type="button" class="dt-btn" data-dt-toggle>
                <span class="dt-dot dt-dot-root"></span>
                <span class="dt-title">Estimation Engine</span>
                <span class="dt-badge">trace</span>
              </button>

              <ul class="dt-node-children">
                {{-- PARSER --}}
                <li class="dt-node">
                  <button type="button" class="dt-btn" data-dt-toggle>
                    <span class="dt-dot dt-dot-step"></span>
                    <span class="dt-title">Special Requirements Parser</span>
                    <span class="dt-badge">{{ count($parserTags2) }} tags</span>
                  </button>

                  <ul class="dt-node-children">
                    <li class="dt-leaf">
                      <div class="dt-leaf-row">
                        <span class="dt-label">Input</span>
                        <span class="dt-val">{{ $parserStep['input'] ?? '-' }}</span>
                      </div>
                    </li>

                    <li class="dt-leaf">
                      <div class="dt-leaf-row">
                        <span class="dt-label">Tags</span>
                        <span class="dt-val">
                          @if(empty($parserTags2))
                            -
                          @else
                            @foreach($parserTags2 as $t)
                              <span class="inline-flex items-center rounded-full border border-slate-200 dark:border-slate-700 px-2 py-0.5 text-xs mr-1 mb-1">
                                {{ $t }}
                              </span>
                            @endforeach
                          @endif
                        </span>
                      </div>
                    </li>
                  </ul>
                </li>

                {{-- RULES --}}
                <li class="dt-node">
                  <button type="button" class="dt-btn" data-dt-toggle>
                    <span class="dt-dot dt-dot-step"></span>
                    <span class="dt-title">Rules Evaluation</span>
                    <span class="dt-badge">{{ count($matchedRules) }} matched / {{ count($unmatchedRules) }} skipped</span>
                  </button>

                  <ul class="dt-node-children">
                    <li class="dt-node">
                      <button type="button" class="dt-btn" data-dt-toggle>
                        <span class="dt-dot dt-dot-ok"></span>
                        <span class="dt-title">Matched Rules (Applied)</span>
                        <span class="dt-badge">{{ count($matchedRules) }}</span>
                      </button>

                      <ul class="dt-node-children">
                        @forelse($matchedRules as $r)
                          <li class="dt-leaf">
                            <div class="dt-card dt-card-ok">
                              <div class="dt-card-top">
                                <div class="dt-card-title">
                                  Rule #{{ $r['rule_id'] ?? '-' }}
                                  <span class="dt-pill">prio {{ $r['priority'] ?? '-' }}</span>
                                  @if(!empty($r['category'])) <span class="dt-pill">{{ $r['category'] }}</span> @endif
                                </div>
                                <div class="dt-status dt-status-ok">MATCH</div>
                              </div>

                              <div class="dt-card-body">
                                <div><span class="dt-muted">IF</span> <b>{{ $r['field'] ?? '-' }}</b> {{ $r['operator'] ?? '-' }} <b>{{ $r['value'] ?? '-' }}</b></div>
                                <div class="dt-muted">fact_value: {{ is_array($r['fact_value'] ?? null) ? json_encode($r['fact_value']) : ($r['fact_value'] ?? '-') }}</div>
                              </div>
                            </div>
                          </li>
                        @empty
                          <li class="dt-leaf"><div class="dt-muted">No matched rules.</div></li>
                        @endforelse
                      </ul>
                    </li>

                    <li class="dt-node">
                      <button type="button" class="dt-btn" data-dt-toggle>
                        <span class="dt-dot dt-dot-no"></span>
                        <span class="dt-title">Skipped Rules (Not Matched)</span>
                        <span class="dt-badge">{{ count($unmatchedRules) }}</span>
                      </button>

                      <ul class="dt-node-children hidden">
                        @forelse($unmatchedRules as $r)
                          <li class="dt-leaf">
                            <div class="dt-card dt-card-no">
                              <div class="dt-card-top">
                                <div class="dt-card-title">
                                  Rule #{{ $r['rule_id'] ?? '-' }}
                                  <span class="dt-pill">prio {{ $r['priority'] ?? '-' }}</span>
                                  @if(!empty($r['category'])) <span class="dt-pill">{{ $r['category'] }}</span> @endif
                                </div>
                                <div class="dt-status dt-status-no">NO</div>
                              </div>

                              <div class="dt-card-body">
                                <div><span class="dt-muted">IF</span> <b>{{ $r['field'] ?? '-' }}</b> {{ $r['operator'] ?? '-' }} <b>{{ $r['value'] ?? '-' }}</b></div>
                                <div class="dt-muted">fact_value: {{ is_array($r['fact_value'] ?? null) ? json_encode($r['fact_value']) : ($r['fact_value'] ?? '-') }}</div>
                              </div>
                            </div>
                          </li>
                        @empty
                          <li class="dt-leaf"><div class="dt-muted">No skipped rules.</div></li>
                        @endforelse
                      </ul>
                    </li>
                  </ul>
                </li>

                {{-- COSTS --}}
                <li class="dt-node">
                  <button type="button" class="dt-btn" data-dt-toggle>
                    <span class="dt-dot dt-dot-step"></span>
                    <span class="dt-title">Cost Calculation</span>
                    <span class="dt-badge">total Rp {{ number_format((int)($cb['total'] ?? 0),0,',','.') }}</span>
                  </button>

                  <ul class="dt-node-children">
                    <li class="dt-leaf"><div class="dt-leaf-row"><span class="dt-label">Equipment</span><span class="dt-val">Rp {{ number_format((int)($cb['equipment'] ?? 0),0,',','.') }}</span></div></li>
                    <li class="dt-leaf"><div class="dt-leaf-row"><span class="dt-label">Labor</span><span class="dt-val">Rp {{ number_format((int)($cb['labor'] ?? 0),0,',','.') }}</span></div></li>
                    <li class="dt-leaf"><div class="dt-leaf-row"><span class="dt-label">Transport</span><span class="dt-val">Rp {{ number_format((int)($cb['transport'] ?? 0),0,',','.') }}</span></div></li>
                    <li class="dt-leaf"><div class="dt-leaf-row"><span class="dt-label">Operational</span><span class="dt-val">Rp {{ number_format((int)($cb['operational'] ?? 0),0,',','.') }}</span></div></li>
                    @if(isset($cb['markup']))
                      <li class="dt-leaf"><div class="dt-leaf-row"><span class="dt-label">Markup</span><span class="dt-val">Rp {{ number_format((int)($cb['markup'] ?? 0),0,',','.') }}</span></div></li>
                    @endif
                  </ul>
                </li>

              </ul>
            </li>
          </ul>
        </div>
      </div>

      <style>
        .dt-tree, .dt-tree ul { list-style: none; margin: 0; padding-left: 18px; }
        .dt-node { position: relative; margin: 8px 0; }
        .dt-node:before {
          content: "";
          position: absolute;
          top: 18px;
          left: -10px;
          width: 10px;
          height: calc(100% - 18px);
          border-left: 2px solid rgba(148,163,184,.6);
        }
        .dt-node:last-child:before { height: 0; }
        .dt-btn{
          width: 100%;
          display:flex; align-items:center; gap:10px;
          padding:10px 12px;
          border-radius: 12px;
          border:1px solid rgba(148,163,184,.35);
          background: rgba(255,255,255,.6);
        }
        .dark .dt-btn{ background: rgba(15,23,42,.6); border-color: rgba(148,163,184,.25); }
        .dt-title{ font-weight: 700; color: inherit; }
        .dt-badge{
          margin-left:auto;
          font-size:12px;
          padding:2px 10px;
          border-radius:999px;
          border:1px solid rgba(148,163,184,.35);
          color: rgba(100,116,139,1);
        }
        .dark .dt-badge{ color: rgba(148,163,184,1); border-color: rgba(148,163,184,.25); }
        .dt-dot{ width:10px; height:10px; border-radius:999px; display:inline-block; }
        .dt-dot-root{ background: rgba(37,99,235,1); }
        .dt-dot-step{ background: rgba(100,116,139,1); }
        .dt-dot-ok{ background: rgba(34,197,94,1); }
        .dt-dot-no{ background: rgba(239,68,68,1); }
        .dt-node-children{ margin-top: 8px; }
        .dt-leaf{ margin: 10px 0 10px 8px; }
        .dt-leaf-row{
          display:flex; justify-content:space-between; gap:12px;
          padding:10px 12px; border-radius:12px;
          border:1px solid rgba(148,163,184,.25);
          background: rgba(248,250,252,1);
        }
        .dark .dt-leaf-row{ background: rgba(2,6,23,.35); }
        .dt-label{ font-size:12px; color: rgba(100,116,139,1); font-weight:700; }
        .dt-val{ font-size:12px; color: inherit; }
        .dt-muted{ color: rgba(100,116,139,1); font-size: 12px; }
        .dt-card{
          border-radius: 14px;
          border:1px solid rgba(148,163,184,.25);
          padding: 10px 12px;
          background: rgba(248,250,252,1);
        }
        .dark .dt-card{ background: rgba(2,6,23,.35); }
        .dt-card-ok{ border-color: rgba(34,197,94,.35); }
        .dt-card-no{ border-color: rgba(239,68,68,.25); }
        .dt-card-top{ display:flex; align-items:center; justify-content:space-between; gap:12px; }
        .dt-card-title{ font-weight:800; font-size:13px; }
        .dt-pill{
          display:inline-flex; align-items:center;
          margin-left:6px;
          font-size:11px; padding:2px 8px;
          border-radius:999px;
          border:1px solid rgba(148,163,184,.25);
          color: rgba(100,116,139,1);
          font-weight:700;
        }
        .dt-status{ font-weight:900; font-size:11px; padding:3px 10px; border-radius:999px; }
        .dt-status-ok{ background: rgba(34,197,94,.12); color: rgba(22,163,74,1); border:1px solid rgba(34,197,94,.25); }
        .dt-status-no{ background: rgba(239,68,68,.10); color: rgba(220,38,38,1); border:1px solid rgba(239,68,68,.20); }
      </style>

      <script>
        (function () {
          document.querySelectorAll('[data-dt-toggle]').forEach(btn => {
            btn.addEventListener('click', () => {
              const li = btn.closest('li');
              if (!li) return;
              const child = li.querySelector(':scope > ul.dt-node-children');
              if (child) child.classList.toggle('hidden');
            });
          });
        })();
      </script>
    @endif
  @endunless

</div>

{{-- Print styles --}}
<style>
  @media print {
    aside, header, .no-print { display: none !important; }
    main { padding: 0 !important; }
    body { background: #fff !important; }
    * { color: black !important; }
  }
</style>

{{-- Share dropdown + copy + auto print --}}
<script>
(function(){
  const btn  = document.getElementById('shareBtn');
  const menu = document.getElementById('shareMenu');
  const wrap = document.getElementById('shareWrap');

  if (btn && menu && wrap) {
    function close() { menu.classList.add('hidden'); }
    function toggle() { menu.classList.toggle('hidden'); }

    btn.addEventListener('click', (e) => {
      e.preventDefault();
      toggle();
    });

    document.addEventListener('click', (e) => {
      if (!wrap.contains(e.target)) close();
    });
  }

  const copyBtn = document.getElementById('copyShareLinkBtn');
  if (copyBtn) {
    copyBtn.addEventListener('click', async () => {
      const link = copyBtn.getAttribute('data-link');
      if (!link) return;

      try {
        await navigator.clipboard.writeText(link);
        const old = copyBtn.textContent;
        copyBtn.textContent = 'Copied ✅';
        setTimeout(() => copyBtn.textContent = old, 1200);
      } catch (err) {
        window.prompt('Copy link:', link);
      }
    });
  }

  const url = new URL(window.location.href);
  const p = url.searchParams.get('print');
  if (p === 'detail' || p === 'summary') {
    window.addEventListener('load', () => {
      setTimeout(() => window.print(), 250);
    });
  }
})();
</script>
@endsection