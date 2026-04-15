@extends('layouts.app-shell')
@section('title','Rule Wizard')

@section('content')
@php
  $inventoryNames = $inventoryNames ?? [];
  $inventoryCategories = $inventoryCategories ?? [];
@endphp

<div class="max-w-5xl mx-auto space-y-6">

  <div class="flex flex-col gap-2">
    <h1 class="text-4xl font-semibold tracking-tight text-slate-900 dark:text-white">Rule Wizard</h1>
    <p class="text-slate-500 dark:text-slate-400">
      Jawab 10 indikator → sistem bentuk <b>facts</b> → kamu mapping ke equipment/crew → jadi rules.
    </p>
  </div>

  @includeIf('pages.settings._tabs', ['active' => 'rules_wizard'])

  <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 overflow-hidden shadow-sm">
    {{-- progress bar --}}
    <div class="h-1.5 w-full bg-slate-100 dark:bg-slate-800">
      <div id="wizBar" class="h-full bg-blue-600 transition-all duration-300" style="width:10%"></div>
    </div>

    <div class="p-6 md:p-10">

      <div class="flex items-center justify-between mb-8">
        <div class="text-xs font-bold text-blue-600 uppercase tracking-widest">
          <span id="wizStepLabel">Question 1 of 10</span>
        </div>
        <div class="flex gap-1" id="wizDots"></div>
      </div>

      <div id="wizBody" class="min-h-[320px]"></div>

      {{-- mapping panel (muncul setelah finish) --}}
      <div id="wizMapping" class="hidden mt-8 space-y-5">
        <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950/30 p-5">
          <div class="font-bold text-slate-900 dark:text-white">Facts Output</div>
          <div class="text-xs text-slate-500 dark:text-slate-400 mt-1">Ini facts yang akan jadi condition rules.</div>
          <pre id="factsPreview" class="mt-3 text-xs overflow-auto p-4 rounded-xl bg-slate-900 text-green-300" style="max-height:260px;"></pre>
        </div>

        <div class="rounded-2xl border border-slate-200 dark:border-slate-800 p-5">
          <div class="flex items-start justify-between gap-4">
            <div>
              <div class="font-bold text-slate-900 dark:text-white">Generate Rules</div>
              <div class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                Tambahkan beberapa blok rule. Condition diambil dari facts. Actions pilih equipment dari inventory + crew.
              </div>
            </div>

            <button type="button" id="addRuleBlockBtn"
              class="inline-flex items-center justify-center rounded-xl bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">
              + Add Rule Block
            </button>
          </div>

          <div id="ruleBlocks" class="mt-5 space-y-4"></div>
        </div>

        <form method="POST" action="{{ route('settings.rules.wizard.preview') }}" id="previewForm">
          @csrf
          <input type="hidden" name="facts_json" id="factsJsonInput" value="">
          <div id="ruleBlocksHidden"></div>

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

    </div>

    {{-- footer nav --}}
    <div id="wizNav" class="p-6 bg-slate-50 dark:bg-slate-950/40 border-t border-slate-200 dark:border-slate-800 flex gap-3">
      <button id="backBtn" type="button"
        class="hidden px-6 py-4 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-200 font-bold rounded-2xl hover:bg-slate-100 dark:hover:bg-slate-800 transition-all">
        ← Back
      </button>

      <button id="nextBtn" type="button"
        class="flex-1 py-4 rounded-2xl font-bold text-white bg-blue-600 hover:bg-blue-700 transition-all shadow-lg">
        Continue →
      </button>
    </div>
  </div>
</div>

{{-- datalist untuk autocomplete inventory --}}
<datalist id="inventoryList">
  @foreach($inventoryNames as $n)
    <option value="{{ $n }}"></option>
  @endforeach
</datalist>

<script>
(function () {
  // ====== 10 indikator (facts) ======
  const steps = [
    {
      key: 'event_scale',
      title: 'Event scale & complexity',
      question: 'Perkiraan jumlah peserta?',
      type: 'choice',
      options: [
        {label:'S (1–99)', value:'S'},
        {label:'S+ (100–500)', value:'S+'},
        {label:'M (501–1000)', value:'M'},
        {label:'M+ (1001–2500)', value:'M+'},
        {label:'L (2501–5000)', value:'L'},
        {label:'XL (5001+)', value:'XL'},
      ],
      fact: (v) => ({ participants_tier: v })
    },
    {
      key: 'event_type',
      title: 'Event scale & complexity',
      question: 'Tipe event dominan?',
      type: 'choice',
      options: [
        {label:'Wedding', value:'wedding'},
        {label:'Seminar', value:'seminar'},
        {label:'Concert', value:'concert'},
        {label:'Corporate', value:'corporate'},
        {label:'Graduation', value:'graduation'},
        {label:'Other', value:'other'},
      ],
      fact: (v) => ({ event_type: v })
    },
    {
      key: 'venue_risk',
      title: 'Venue & environment risk',
      question: 'Venue indoor atau outdoor?',
      type: 'choice',
      options: [
        {label:'Indoor', value:'indoor'},
        {label:'Outdoor', value:'outdoor'},
        {label:'Semi-outdoor', value:'semi'},
      ],
      fact: (v) => ({ venue_type: v })
    },
    {
      key: 'power_access',
      title: 'Venue & environment risk',
      question: 'Kondisi listrik di venue?',
      type: 'choice',
      options: [
        {label:'Stabil (venue)', value:'stable'},
        {label:'Ada tapi tidak yakin', value:'unstable'},
        {label:'Tidak ada / harus genset', value:'none'},
      ],
      fact: (v) => ({ power_source: v })
    },
    {
      key: 'time_constraints',
      title: 'Time constraints',
      question: 'Durasi kerja per hari?',
      type: 'choice',
      options: [
        {label:'≤ 4 jam', value:'short'},
        {label:'5–8 jam', value:'normal'},
        {label:'> 8 jam', value:'long'},
      ],
      fact: (v) => ({ time_block: v })
    },
    {
      key: 'reliability',
      title: 'Reliability / criticality',
      question: 'Seberapa “tidak boleh gagal”?',
      type: 'choice',
      options: [
        {label:'Normal', value:'normal'},
        {label:'High (butuh backup)', value:'high'},
        {label:'Extreme (redundant)', value:'extreme'},
      ],
      fact: (v) => ({ criticality: v })
    },
    {
      key: 'scope_service',
      title: 'Scope of service',
      question: 'Layanan utama?',
      type: 'choice',
      options: [
        {label:'Sound System', value:'sound'},
        {label:'Broadcast / Live Streaming', value:'broadcast'},
        {label:'Lighting', value:'lighting'},
        {label:'LED / Screen', value:'led'},
        {label:'Mixed', value:'mixed'},
      ],
      fact: (v) => ({ service_scope: v })
    },
    {
      key: 'crew_ops',
      title: 'Crew & operations',
      question: 'Butuh shift / tambahan crew?',
      type: 'choice',
      options: [
        {label:'Minimal', value:'minimal'},
        {label:'Standard', value:'standard'},
        {label:'Heavy (shift)', value:'heavy'},
      ],
      fact: (v) => ({ crew_ops_level: v })
    },
    {
      key: 'logistics',
      title: 'Logistics & transport',
      question: 'Akses venue / mobilisasi?',
      type: 'choice',
      options: [
        {label:'Mudah', value:'easy'},
        {label:'Normal', value:'normal'},
        {label:'Sulit (jauh/tangga)', value:'hard'},
        {label:'Butuh rigging/truss', value:'rigging'},
      ],
      fact: (v) => ({ logistics_level: v })
    },
    {
      key: 'output_req',
      title: 'Output requirement',
      question: 'Output yang diinginkan?',
      type: 'choice',
      options: [
        {label:'Ringkas (summary)', value:'summary'},
        {label:'Detail (invoice)', value:'detail'},
      ],
      fact: (v) => ({ output_mode: v })
    },
  ];

  // ====== state ======
  let step = 0;
  const answers = {};
  let facts = {};

  // dom
  const body = document.getElementById('wizBody');
  const bar = document.getElementById('wizBar');
  const stepLabel = document.getElementById('wizStepLabel');
  const dots = document.getElementById('wizDots');
  const backBtn = document.getElementById('backBtn');
  const nextBtn = document.getElementById('nextBtn');

  const mapping = document.getElementById('wizMapping');
  const nav = document.getElementById('wizNav');
  const factsPreview = document.getElementById('factsPreview');
  const factsJsonInput = document.getElementById('factsJsonInput');
  const ruleBlocksWrap = document.getElementById('ruleBlocks');
  const addRuleBlockBtn = document.getElementById('addRuleBlockBtn');
  const ruleBlocksHidden = document.getElementById('ruleBlocksHidden');
  const previewForm = document.getElementById('previewForm');

  function renderDots() {
    dots.innerHTML = '';
    for (let i=0;i<steps.length;i++) {
      const d = document.createElement('div');
      d.className = 'w-2 h-2 rounded-full ' + (i <= step ? 'bg-blue-600' : 'bg-slate-200 dark:bg-slate-700');
      dots.appendChild(d);
    }
  }

  function renderStep() {
    const s = steps[step];
    stepLabel.textContent = `Question ${step+1} of ${steps.length}`;
    bar.style.width = `${((step+1)/steps.length)*100}%`;
    renderDots();

    backBtn.classList.toggle('hidden', step === 0);

    // button state
    const hasAnswer = answers[s.key] !== undefined && answers[s.key] !== '';
    nextBtn.disabled = !hasAnswer;
    nextBtn.className = nextBtn.disabled
      ? 'flex-1 py-4 rounded-2xl font-bold bg-slate-200 text-slate-400 cursor-not-allowed shadow-none'
      : 'flex-1 py-4 rounded-2xl font-bold text-white bg-blue-600 hover:bg-blue-700 transition-all shadow-lg';

    // body
    body.innerHTML = `
      <div class="space-y-4">
        <div class="text-xs font-bold text-slate-400 uppercase tracking-widest">${s.title}</div>
        <h2 class="text-2xl font-bold text-slate-900 dark:text-white">${s.question}</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3" id="optWrap"></div>
      </div>
    `;

    const optWrap = document.getElementById('optWrap');

    s.options.forEach(opt => {
      const btn = document.createElement('button');
      btn.type = 'button';
      const active = answers[s.key] === opt.value;

      btn.className =
        'p-4 rounded-2xl border-2 text-left transition-all ' +
        (active
          ? 'border-blue-600 bg-blue-50 text-blue-700'
          : 'border-slate-200 dark:border-slate-700 hover:border-blue-200 dark:hover:border-blue-700 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-200');

      btn.innerHTML = `<div class="font-semibold">${opt.label}</div>`;

      btn.addEventListener('click', () => {
        answers[s.key] = opt.value;
        // apply facts update
        facts = { ...facts, ...s.fact(opt.value) };
        renderStep();
      });

      optWrap.appendChild(btn);
    });
  }

  function finishWizard() {
    // show mapping UI
    body.innerHTML = '';
    mapping.classList.remove('hidden');
    nav.classList.add('hidden');

    factsPreview.textContent = JSON.stringify(facts, null, 2);
    factsJsonInput.value = JSON.stringify(facts);

    // default: buat 1 rule block awal
    if (ruleBlocksWrap.children.length === 0) addRuleBlock();
  }

  function addRuleBlock() {
    const idx = ruleBlocksWrap.children.length;

    const card = document.createElement('div');
    card.className = 'rounded-2xl border border-slate-200 dark:border-slate-800 p-4';
    card.setAttribute('data-rule-block', idx);

    card.innerHTML = `
      <div class="flex items-start justify-between gap-3">
        <div>
          <div class="font-bold text-slate-900 dark:text-white">Rule Block #${idx+1}</div>
          <div class="text-xs text-slate-500 dark:text-slate-400 mt-1">IF (fact) THEN (actions)</div>
        </div>
        <button type="button" class="text-red-600 font-bold" data-remove>✕</button>
      </div>

      <div class="mt-4 grid grid-cols-1 md:grid-cols-4 gap-3">
        <div>
          <label class="text-xs font-semibold text-slate-600 dark:text-slate-300">Condition Field</label>
          <input class="mt-1 w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-950/50 px-3 py-2 text-sm"
                 placeholder="contoh: venue_type / criticality / participants_tier"
                 data-field value="venue_type">
        </div>
        <div>
          <label class="text-xs font-semibold text-slate-600 dark:text-slate-300">Operator</label>
          <select class="mt-1 w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-950/50 px-3 py-2 text-sm" data-operator>
            <option value="=">=</option>
            <option value="contains">contains</option>
          </select>
        </div>
        <div>
          <label class="text-xs font-semibold text-slate-600 dark:text-slate-300">Value</label>
          <input class="mt-1 w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-950/50 px-3 py-2 text-sm"
                 placeholder="contoh: outdoor / high / XL"
                 data-value value="outdoor">
        </div>
        <div>
          <label class="text-xs font-semibold text-slate-600 dark:text-slate-300">Priority</label>
          <input type="number" min="0" max="9999"
                 class="mt-1 w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-950/50 px-3 py-2 text-sm"
                 data-priority value="100">
        </div>
      </div>

      <div class="mt-3 grid grid-cols-1 md:grid-cols-3 gap-3">
        <div>
          <label class="text-xs font-semibold text-slate-600 dark:text-slate-300">Category</label>
          <input class="mt-1 w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-950/50 px-3 py-2 text-sm"
                 placeholder="contoh: power / paket / service"
                 data-category value="wizard">
        </div>
        <div class="flex items-center gap-2 mt-6">
          <input type="checkbox" checked data-active class="h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500">
          <span class="text-sm text-slate-700 dark:text-slate-200 font-semibold">Active</span>
        </div>
      </div>

      <div class="mt-5">
        <div class="font-semibold text-slate-900 dark:text-white">Actions</div>
        <div class="text-xs text-slate-500 dark:text-slate-400 mt-1">Tambah equipment dan/atau crew.</div>

        <div class="mt-3 space-y-2" data-actions></div>

        <div class="mt-3 flex flex-wrap gap-2">
          <button type="button" class="rounded-xl border border-slate-200 dark:border-slate-700 px-3 py-2 text-sm font-semibold hover:bg-slate-50 dark:hover:bg-slate-800"
                  data-add-eq>+ Equipment</button>

          <button type="button" class="rounded-xl border border-slate-200 dark:border-slate-700 px-3 py-2 text-sm font-semibold hover:bg-slate-50 dark:hover:bg-slate-800"
                  data-add-crew>+ Crew</button>
        </div>
      </div>
    `;

    // handlers
    card.querySelector('[data-remove]').addEventListener('click', () => card.remove());
    card.querySelector('[data-add-eq]').addEventListener('click', () => addActionEquipment(card));
    card.querySelector('[data-add-crew]').addEventListener('click', () => addActionCrew(card));

    ruleBlocksWrap.appendChild(card);

    // seed 1 eq row
    addActionEquipment(card);
  }

  function addActionEquipment(card) {
    const actionsWrap = card.querySelector('[data-actions]');
    const row = document.createElement('div');
    row.className = 'grid grid-cols-12 gap-2 items-center';
    row.innerHTML = `
      <div class="col-span-7">
        <input list="inventoryList"
          class="w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-950/50 px-3 py-2 text-sm"
          placeholder="Pilih dari inventory (autocomplete)"
          data-eq-name>
      </div>
      <div class="col-span-3">
        <input type="number" min="0" value="1"
          class="w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-950/50 px-3 py-2 text-sm"
          data-eq-qty>
      </div>
      <div class="col-span-2 flex justify-end">
        <button type="button" class="text-slate-400 hover:text-red-600 font-bold" data-del>✕</button>
      </div>
    `;
    row.querySelector('[data-del]').addEventListener('click', () => row.remove());
    actionsWrap.appendChild(row);
  }

  function addActionCrew(card) {
    const actionsWrap = card.querySelector('[data-actions]');
    const row = document.createElement('div');
    row.className = 'grid grid-cols-12 gap-2 items-center';
    row.innerHTML = `
      <div class="col-span-7">
        <select class="w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-950/50 px-3 py-2 text-sm" data-crew-role>
          <option value="operator">operator</option>
          <option value="engineer">engineer</option>
          <option value="stage">stage</option>
        </select>
      </div>
      <div class="col-span-3">
        <input type="number" min="0" value="1"
          class="w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-950/50 px-3 py-2 text-sm"
          data-crew-qty>
      </div>
      <div class="col-span-2 flex justify-end">
        <button type="button" class="text-slate-400 hover:text-red-600 font-bold" data-del>✕</button>
      </div>
    `;
    row.querySelector('[data-del]').addEventListener('click', () => row.remove());
    actionsWrap.appendChild(row);
  }

  function buildHiddenInputs() {
    // build rule_blocks[*] payload ke form
    ruleBlocksHidden.innerHTML = '';

    const blocks = Array.from(ruleBlocksWrap.querySelectorAll('[data-rule-block]'));

    blocks.forEach((card, idx) => {
      const field = card.querySelector('[data-field]').value.trim();
      const op = card.querySelector('[data-operator]').value.trim();
      const val = card.querySelector('[data-value]').value.trim();
      const cat = card.querySelector('[data-category]').value.trim();
      const prio = card.querySelector('[data-priority]').value.trim() || '100';
      const active = card.querySelector('[data-active]').checked ? '1' : '';

      // base
      ruleBlocksHidden.insertAdjacentHTML('beforeend', `
        <input type="hidden" name="rule_blocks[${idx}][condition_field]" value="${escapeHtml(field)}">
        <input type="hidden" name="rule_blocks[${idx}][operator]" value="${escapeHtml(op)}">
        <input type="hidden" name="rule_blocks[${idx}][value]" value="${escapeHtml(val)}">
        <input type="hidden" name="rule_blocks[${idx}][category]" value="${escapeHtml(cat)}">
        <input type="hidden" name="rule_blocks[${idx}][priority]" value="${escapeHtml(prio)}">
        <input type="hidden" name="rule_blocks[${idx}][is_active]" value="${escapeHtml(active)}">
      `);

      // actions
      const actionRows = Array.from(card.querySelectorAll('[data-actions] > div'));
      let aidx = 0;

      actionRows.forEach(r => {
        const eqNameEl = r.querySelector('[data-eq-name]');
        const eqQtyEl  = r.querySelector('[data-eq-qty]');
        const crewRoleEl = r.querySelector('[data-crew-role]');
        const crewQtyEl  = r.querySelector('[data-crew-qty]');

        if (eqNameEl && eqQtyEl) {
          const name = eqNameEl.value.trim();
          const qty = parseInt(eqQtyEl.value || '0', 10);
          if (!name || qty <= 0) return;

          ruleBlocksHidden.insertAdjacentHTML('beforeend', `
            <input type="hidden" name="rule_blocks[${idx}][actions][${aidx}][type]" value="ADD_EQUIPMENT">
            <input type="hidden" name="rule_blocks[${idx}][actions][${aidx}][name]" value="${escapeHtml(name)}">
            <input type="hidden" name="rule_blocks[${idx}][actions][${aidx}][qty]" value="${qty}">
          `);
          aidx++;
          return;
        }

        if (crewRoleEl && crewQtyEl) {
          const role = crewRoleEl.value.trim();
          const qty = parseInt(crewQtyEl.value || '0', 10);
          if (!role || qty <= 0) return;

          ruleBlocksHidden.insertAdjacentHTML('beforeend', `
            <input type="hidden" name="rule_blocks[${idx}][actions][${aidx}][type]" value="ADD_CREW">
            <input type="hidden" name="rule_blocks[${idx}][actions][${aidx}][role]" value="${escapeHtml(role)}">
            <input type="hidden" name="rule_blocks[${idx}][actions][${aidx}][qty]" value="${qty}">
          `);
          aidx++;
        }
      });
    });
  }

  function escapeHtml(str) {
    return String(str)
      .replaceAll('&','&amp;')
      .replaceAll('<','&lt;')
      .replaceAll('>','&gt;')
      .replaceAll('"','&quot;')
      .replaceAll("'","&#039;");
  }

  // nav
  backBtn.addEventListener('click', () => {
    if (step > 0) step--;
    renderStep();
  });

  nextBtn.addEventListener('click', () => {
    if (step < steps.length - 1) {
      step++;
      renderStep();
      return;
    }
    finishWizard();
  });

  addRuleBlockBtn.addEventListener('click', addRuleBlock);

  previewForm.addEventListener('submit', (e) => {
    buildHiddenInputs();
    factsJsonInput.value = JSON.stringify(facts);
  });

  // init dots
  for (let i=0;i<steps.length;i++) {
    const d = document.createElement('div');
    d.className = 'w-2 h-2 rounded-full bg-slate-200 dark:bg-slate-700';
    dots.appendChild(d);
  }

  renderStep();
})();
</script>
@endsection