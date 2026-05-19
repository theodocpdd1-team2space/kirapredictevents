@php
  $isEdit = isset($rule);

  $fieldValue = old('condition_field', $rule->condition_field ?? 'package_key');
  $operatorValue = old('operator', $rule->operator ?? '=');
  $ruleValue = old('value', $rule->value ?? '');
  $categoryValue = old('category', $rule->category ?? 'package');
  $priorityValue = old('priority', $rule->priority ?? 100);
  $isActiveValue = old('is_active', ($rule->is_active ?? true));

  $actionPretty = old('action');

  if ($actionPretty === null && $isEdit) {
      $actionRows = [];
      $actionData = $rule->action ?? [];

      if (is_string($actionData)) {
          $decoded = json_decode($actionData, true);
          $actionData = is_array($decoded) ? $decoded : [];
      }

      if (is_array($actionData)) {
          foreach ($actionData as $item) {
              $type = strtoupper((string) ($item['type'] ?? ''));

              if ($type === 'SET_PACKAGE_LABEL') {
                  $name = trim((string) ($item['name'] ?? ''));
                  if ($name !== '') $actionRows[] = 'LABEL: '.$name;
              }

              if ($type === 'ADD_EQUIPMENT') {
                  $qty = (int) ($item['qty'] ?? 1);
                  $name = trim((string) ($item['name'] ?? ''));
                  if ($name !== '') $actionRows[] = 'EQUIPMENT: '.$qty.', '.$name;
              }

              if ($type === 'ADD_CREW') {
                  $qty = (int) ($item['qty'] ?? 1);
                  $role = trim((string) ($item['role'] ?? ''));
                  if ($role !== '') $actionRows[] = 'CREW: '.$qty.', '.$role;
              }
          }
      }

      $actionPretty = implode("\n", $actionRows);
  }

  $actionPretty = $actionPretty ?? '';

  $packageName = '';
  $equipmentRows = [];
  $crewRows = [];

  foreach (preg_split("/\r\n|\n|\r/", $actionPretty) as $line) {
      $line = trim($line);

      if (stripos($line, 'LABEL:') === 0) {
          $packageName = trim(substr($line, strlen('LABEL:')));
      }

      if (stripos($line, 'EQUIPMENT:') === 0) {
          $payload = trim(substr($line, strlen('EQUIPMENT:')));
          $parts = preg_split('/\s*[,|]\s*|\s+-\s+/', $payload, 2);

          $equipmentRows[] = [
              'qty' => trim($parts[0] ?? '1'),
              'name' => trim($parts[1] ?? ''),
          ];
      }

      if (stripos($line, 'CREW:') === 0) {
          $payload = trim(substr($line, strlen('CREW:')));
          $parts = preg_split('/\s*[,|]\s*|\s+-\s+/', $payload, 2);

          $crewRows[] = [
              'qty' => trim($parts[0] ?? '1'),
              'role' => trim($parts[1] ?? 'operator'),
          ];
      }
  }

  if ($packageName === '') {
      $packageName = 'Basic 1-100 Sound System Package';
  }

  if (count($equipmentRows) === 0) {
      $equipmentRows = [
          ['qty' => 1, 'name' => ''],
      ];
  }

  if (count($crewRows) === 0) {
      $crewRows = [
          ['qty' => 1, 'role' => 'operator'],
      ];
  }

  $minParticipants = 1;
  $maxParticipants = 100;
  $serviceLevel = 'basic';

  if (preg_match('/tier_(\d+)_(\d+)_(basic|standard|premium)/i', (string) $ruleValue, $matches)) {
      $minParticipants = (int) $matches[1];
      $maxParticipants = (int) $matches[2];
      $serviceLevel = strtolower($matches[3]);
  }

  $inventoryOptions = collect($inventories ?? [])->map(function ($item) {
      return [
          'id' => $item->id,
          'name' => $item->equipment_name,
          'category' => $item->category,
          'quantity' => $item->quantity,
          'price' => $item->price,
      ];
  })->values();

  $categoryOptions = collect($ruleCategories ?? [])
      ->filter()
      ->unique()
      ->values();

  if ($categoryOptions->count() === 0) {
      $categoryOptions = collect([
          'package',
          'event_type',
          'music',
          'power',
          'duration',
          'reliability',
          'it',
          'rigging',
          'wireless',
          'microphone',
          'custom',
      ]);
  }

  $initialMode = 'custom';

  if ($fieldValue === 'package_key') {
      $initialMode = 'package';
  } elseif ($fieldValue === 'event_type') {
      $initialMode = 'event_type';
  } elseif ($fieldValue === 'special_requirement') {
      $initialMode = 'special_requirement';
  } elseif (in_array($fieldValue, ['venue_type', 'duration', 'service_level'], true)) {
      $initialMode = 'condition';
  }
@endphp

<div
  x-data="layeredRuleBuilder()"
  x-init="init()"
  class="space-y-6"
>
  @if($inventoryOptions->count() === 0)
    <div class="rounded-[24px] border border-amber-200 bg-amber-50 p-5 dark:border-amber-500/20 dark:bg-amber-500/10">
      <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div>
          <h3 class="text-sm font-black text-slate-900 dark:text-white">
            Inventory masih kosong
          </h3>
          <p class="mt-1 text-sm leading-6 text-slate-600 dark:text-slate-300">
            Rule builder membutuhkan data inventory agar equipment pada rule sesuai dengan alat yang tersedia.
            Silakan isi inventory terlebih dahulu.
          </p>
        </div>

        <a href="{{ route('inventories.index') }}"
           class="inline-flex items-center justify-center rounded-xl bg-slate-900 px-4 py-2 text-xs font-bold text-white transition hover:bg-slate-800 dark:bg-white dark:text-slate-950 dark:hover:bg-slate-200">
          Isi Inventory
        </a>
      </div>
    </div>
  @endif

  <div class="rounded-[24px] border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
    <div class="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
      <div>
        <h2 class="text-lg font-black text-slate-900 dark:text-white">
          Layered Rule Builder
        </h2>
        <p class="mt-1 max-w-3xl text-sm leading-6 text-slate-500 dark:text-slate-400">
          Buat rule berdasarkan layer inferensi. Equipment diambil dari inventory, category diambil dari database rule.
        </p>
      </div>

      <div class="grid grid-cols-2 gap-2 rounded-2xl bg-slate-100 p-1 dark:bg-slate-950/60 md:grid-cols-5">
        <button type="button" @click="setMode('package')" :class="tabClass('package')" class="rounded-xl px-3 py-2 text-xs font-black transition">
          Package
        </button>
        <button type="button" @click="setMode('event_type')" :class="tabClass('event_type')" class="rounded-xl px-3 py-2 text-xs font-black transition">
          Event Type
        </button>
        <button type="button" @click="setMode('special_requirement')" :class="tabClass('special_requirement')" class="rounded-xl px-3 py-2 text-xs font-black transition">
          Requirement
        </button>
        <button type="button" @click="setMode('condition')" :class="tabClass('condition')" class="rounded-xl px-3 py-2 text-xs font-black transition">
          Condition
        </button>
        <button type="button" @click="setMode('custom')" :class="tabClass('custom')" class="rounded-xl px-3 py-2 text-xs font-black transition">
          Custom
        </button>
      </div>
    </div>
  </div>

  <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
    <div class="rounded-2xl border border-blue-200 bg-blue-50 p-4 dark:border-blue-500/20 dark:bg-blue-500/10">
      <p class="text-xs font-black uppercase tracking-widest text-blue-600 dark:text-blue-300">Layer 1-2</p>
      <p class="mt-1 text-sm font-bold text-slate-900 dark:text-white">Package</p>
      <p class="mt-1 text-xs leading-5 text-slate-600 dark:text-slate-400">Paket dasar dari peserta + service level.</p>
    </div>

    <div class="rounded-2xl border border-slate-200 bg-white p-4 dark:border-slate-800 dark:bg-slate-900">
      <p class="text-xs font-black uppercase tracking-widest text-slate-400">Layer 3</p>
      <p class="mt-1 text-sm font-bold text-slate-900 dark:text-white">Event Type</p>
      <p class="mt-1 text-xs leading-5 text-slate-600 dark:text-slate-400">Tambahan berdasarkan jenis acara.</p>
    </div>

    <div class="rounded-2xl border border-slate-200 bg-white p-4 dark:border-slate-800 dark:bg-slate-900">
      <p class="text-xs font-black uppercase tracking-widest text-slate-400">Layer 4</p>
      <p class="mt-1 text-sm font-bold text-slate-900 dark:text-white">Requirement</p>
      <p class="mt-1 text-xs leading-5 text-slate-600 dark:text-slate-400">Band, drum, rider, livestream, dll.</p>
    </div>

    <div class="rounded-2xl border border-slate-200 bg-white p-4 dark:border-slate-800 dark:bg-slate-900">
      <p class="text-xs font-black uppercase tracking-widest text-slate-400">Layer 4</p>
      <p class="mt-1 text-sm font-bold text-slate-900 dark:text-white">Condition</p>
      <p class="mt-1 text-xs leading-5 text-slate-600 dark:text-slate-400">Venue, duration, power, backup.</p>
    </div>
  </div>

  <div x-show="mode === 'package'" class="rounded-[24px] border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
    <h3 class="text-sm font-black text-slate-900 dark:text-white">Package Rule</h3>
    <p class="mt-1 text-sm leading-6 text-slate-500 dark:text-slate-400">
      Digunakan untuk menentukan paket dasar. Sistem otomatis membuat <span class="font-mono font-bold">package_key</span>.
    </p>

    <div class="mt-5 grid grid-cols-1 gap-5 md:grid-cols-2">
      <div>
        <label class="block text-sm font-bold text-slate-900 dark:text-white">Range Peserta</label>
        <div class="mt-3 grid grid-cols-2 gap-3">
          <input type="number" x-model="packageMin" @input="syncPackageRule()" min="1" placeholder="Dari"
                 class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-semibold text-slate-900 outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-slate-700 dark:bg-slate-950/50 dark:text-white">

          <input type="number" x-model="packageMax" @input="syncPackageRule()" min="1" placeholder="Sampai"
                 class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-semibold text-slate-900 outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-slate-700 dark:bg-slate-950/50 dark:text-white">
        </div>
      </div>

      <div>
        <label class="block text-sm font-bold text-slate-900 dark:text-white">Service Level</label>
        <select x-model="serviceLevel" @change="syncPackageRule()"
                class="mt-3 w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-semibold text-slate-900 outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-slate-700 dark:bg-slate-950/50 dark:text-white">
          <option value="basic">Basic</option>
          <option value="standard">Standard</option>
          <option value="premium">Premium</option>
        </select>
      </div>

      <div class="md:col-span-2">
        <label class="block text-sm font-bold text-slate-900 dark:text-white">Nama Paket</label>
        <input type="text" x-model="packageName" @input="syncAction()"
               class="mt-3 w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-semibold text-slate-900 outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-slate-700 dark:bg-slate-950/50 dark:text-white">
      </div>
    </div>
  </div>

  <div x-show="mode === 'event_type'" class="rounded-[24px] border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
    <h3 class="text-sm font-black text-slate-900 dark:text-white">Event Type Rule</h3>
    <p class="mt-1 text-sm leading-6 text-slate-500 dark:text-slate-400">
      Digunakan untuk menambah kebutuhan berdasarkan jenis acara.
    </p>

    <div class="mt-5 grid grid-cols-1 gap-5 md:grid-cols-3">
      <div>
        <label class="block text-sm font-bold text-slate-900 dark:text-white">Jenis Acara</label>
        <select x-model="eventType" @change="syncEventTypeRule()"
                class="mt-3 w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-semibold text-slate-900 outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-slate-700 dark:bg-slate-950/50 dark:text-white">
          <option value="wedding">Wedding</option>
          <option value="seminar">Seminar</option>
          <option value="graduation">Graduation</option>
          <option value="corporate">Corporate</option>
          <option value="concert">Concert</option>
          <option value="church">Church</option>
          <option value="birthday">Birthday</option>
        </select>
      </div>

      <div>
        <label class="block text-sm font-bold text-slate-900 dark:text-white">Category</label>
        <select x-model="categoryValue" @change="syncAction()"
                class="mt-3 w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-semibold text-slate-900 outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-slate-700 dark:bg-slate-950/50 dark:text-white">
          <template x-for="cat in categoryOptions" :key="cat">
            <option :value="cat" x-text="cat"></option>
          </template>
        </select>
      </div>

      <div>
        <label class="block text-sm font-bold text-slate-900 dark:text-white">Priority</label>
        <input type="number" x-model="priority"
               class="mt-3 w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-semibold text-slate-900 outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-slate-700 dark:bg-slate-950/50 dark:text-white">
      </div>
    </div>
  </div>

  <div x-show="mode === 'special_requirement'" class="rounded-[24px] border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
    <h3 class="text-sm font-black text-slate-900 dark:text-white">Special Requirement Rule</h3>
    <p class="mt-1 text-sm leading-6 text-slate-500 dark:text-slate-400">
      Digunakan untuk kebutuhan tambahan seperti band, drum, keyboard, livestream, rider, rigging, dan microphone.
    </p>

    <div class="mt-5 grid grid-cols-1 gap-5 md:grid-cols-4">
      <div>
        <label class="block text-sm font-bold text-slate-900 dark:text-white">Keyword</label>
        <input type="text" x-model="requirementKeyword" @input="syncRequirementRule()" placeholder="Contoh: band"
               class="mt-3 w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-semibold text-slate-900 outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-slate-700 dark:bg-slate-950/50 dark:text-white">
      </div>

      <div>
        <label class="block text-sm font-bold text-slate-900 dark:text-white">Category</label>
        <select x-model="requirementCategory" @change="syncRequirementRule()"
                class="mt-3 w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-semibold text-slate-900 outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-slate-700 dark:bg-slate-950/50 dark:text-white">
          <template x-for="cat in categoryOptions" :key="cat">
            <option :value="cat" x-text="cat"></option>
          </template>
        </select>
      </div>

      <div>
        <label class="block text-sm font-bold text-slate-900 dark:text-white">Priority</label>
        <input type="number" x-model="priority"
               class="mt-3 w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-semibold text-slate-900 outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-slate-700 dark:bg-slate-950/50 dark:text-white">
      </div>

      <div>
        <label class="block text-sm font-bold text-slate-900 dark:text-white">Operator</label>
        <input type="text" x-model="operatorValue" readonly
               class="mt-3 w-full rounded-xl border border-slate-200 bg-slate-100 px-4 py-3 text-sm font-semibold text-slate-500 outline-none dark:border-slate-700 dark:bg-slate-950/50 dark:text-slate-400">
      </div>
    </div>
  </div>

  <div x-show="mode === 'condition'" class="rounded-[24px] border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
    <h3 class="text-sm font-black text-slate-900 dark:text-white">Venue / Duration / Service Rule</h3>
    <p class="mt-1 text-sm leading-6 text-slate-500 dark:text-slate-400">
      Digunakan untuk venue outdoor, durasi panjang, service premium, power, dan backup equipment.
    </p>

    <div class="mt-5 grid grid-cols-1 gap-5 md:grid-cols-5">
      <div>
        <label class="block text-sm font-bold text-slate-900 dark:text-white">Field</label>
        <select x-model="conditionField" @change="syncConditionRule(true)"
                class="mt-3 w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-semibold text-slate-900 outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-slate-700 dark:bg-slate-950/50 dark:text-white">
          <option value="venue_type">venue_type</option>
          <option value="duration">duration</option>
          <option value="service_level">service_level</option>
        </select>
      </div>

      <div>
        <label class="block text-sm font-bold text-slate-900 dark:text-white">Operator</label>
        <select x-model="operatorValue" @change="syncConditionRule(false)"
                class="mt-3 w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-semibold text-slate-900 outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-slate-700 dark:bg-slate-950/50 dark:text-white">
          <option value="=">=</option>
          <option value=">">&gt;</option>
          <option value=">=">&gt;=</option>
          <option value="<">&lt;</option>
          <option value="<=">&lt;=</option>
          <option value="between">between</option>
          <option value="contains">contains</option>
        </select>
      </div>

      <div>
        <label class="block text-sm font-bold text-slate-900 dark:text-white">Value</label>
        <input type="text" x-model="ruleValue" @input="syncConditionRule(false)" placeholder="outdoor / 5-8 / premium"
               class="mt-3 w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-semibold text-slate-900 outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-slate-700 dark:bg-slate-950/50 dark:text-white">
      </div>

      <div>
        <label class="block text-sm font-bold text-slate-900 dark:text-white">Category</label>
        <select x-model="categoryValue" @change="syncAction()"
                class="mt-3 w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-semibold text-slate-900 outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-slate-700 dark:bg-slate-950/50 dark:text-white">
          <template x-for="cat in categoryOptions" :key="cat">
            <option :value="cat" x-text="cat"></option>
          </template>
        </select>
      </div>

      <div>
        <label class="block text-sm font-bold text-slate-900 dark:text-white">Priority</label>
        <input type="number" x-model="priority"
               class="mt-3 w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-semibold text-slate-900 outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-slate-700 dark:bg-slate-950/50 dark:text-white">
      </div>
    </div>
  </div>

  <div x-show="mode !== 'custom'" class="rounded-[24px] border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
    <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
      <div>
        <h3 class="text-sm font-black text-slate-900 dark:text-white">Equipment Action</h3>
        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
          Pilih alat dari inventory agar nama equipment selalu match.
        </p>
      </div>

      <button type="button" @click="addEquipment()" @disabled($inventoryOptions->count() === 0)
              class="inline-flex items-center justify-center rounded-xl bg-blue-600 px-4 py-2 text-xs font-black text-white transition hover:bg-blue-700 disabled:cursor-not-allowed disabled:bg-slate-300 disabled:text-slate-500">
        + Tambah Alat
      </button>
    </div>

    <div class="mt-5 space-y-3">
      <template x-for="(item, index) in equipments" :key="index">
        <div class="grid grid-cols-12 gap-3 rounded-2xl bg-slate-50 p-3 dark:bg-slate-950/60">
          <div class="col-span-3 md:col-span-2">
            <label class="mb-1 block text-[10px] font-black uppercase tracking-widest text-slate-400">Qty</label>
            <input type="number" min="1" x-model="item.qty" @input="syncAction()"
                   class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-bold text-slate-900 outline-none focus:border-blue-500 dark:border-slate-700 dark:bg-slate-900 dark:text-white">
          </div>

          <div class="col-span-7 md:col-span-9">
            <label class="mb-1 block text-[10px] font-black uppercase tracking-widest text-slate-400">
              Pilih Alat dari Inventory
            </label>

            <select x-model="item.name" @change="syncAction()"
                    class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-semibold text-slate-900 outline-none focus:border-blue-500 dark:border-slate-700 dark:bg-slate-900 dark:text-white">
              <option value="">-- Pilih alat --</option>

              <template x-for="inv in inventoryOptions" :key="inv.id">
                <option :value="inv.name" x-text="`${inv.name} — stok ${inv.quantity}`"></option>
              </template>
            </select>
          </div>

          <div class="col-span-2 md:col-span-1 flex items-end">
            <button type="button" @click="removeEquipment(index)"
                    class="h-10 w-full rounded-xl bg-rose-50 text-xs font-black text-rose-600 transition hover:bg-rose-100 dark:bg-rose-500/10 dark:text-rose-300">
              ×
            </button>
          </div>
        </div>
      </template>
    </div>
  </div>

  <div x-show="mode !== 'custom'" class="rounded-[24px] border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
    <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
      <div>
        <h3 class="text-sm font-black text-slate-900 dark:text-white">Crew Action</h3>
        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
          Tambahkan crew jika rule membutuhkan operator, engineer, stage, atau helper.
        </p>
      </div>

      <button type="button" @click="addCrew()"
              class="inline-flex items-center justify-center rounded-xl bg-blue-600 px-4 py-2 text-xs font-black text-white transition hover:bg-blue-700">
        + Tambah Crew
      </button>
    </div>

    <div class="mt-5 space-y-3">
      <template x-for="(item, index) in crews" :key="index">
        <div class="grid grid-cols-12 gap-3 rounded-2xl bg-slate-50 p-3 dark:bg-slate-950/60">
          <div class="col-span-3 md:col-span-2">
            <label class="mb-1 block text-[10px] font-black uppercase tracking-widest text-slate-400">Qty</label>
            <input type="number" min="1" x-model="item.qty" @input="syncAction()"
                   class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-bold text-slate-900 outline-none focus:border-blue-500 dark:border-slate-700 dark:bg-slate-900 dark:text-white">
          </div>

          <div class="col-span-7 md:col-span-9">
            <label class="mb-1 block text-[10px] font-black uppercase tracking-widest text-slate-400">Role</label>
            <select x-model="item.role" @change="syncAction()"
                    class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-semibold text-slate-900 outline-none focus:border-blue-500 dark:border-slate-700 dark:bg-slate-900 dark:text-white">
              <option value="operator">operator</option>
              <option value="engineer">engineer</option>
              <option value="stage">stage</option>
              <option value="helper">helper</option>
              <option value="technician">technician</option>
            </select>
          </div>

          <div class="col-span-2 md:col-span-1 flex items-end">
            <button type="button" @click="removeCrew(index)"
                    class="h-10 w-full rounded-xl bg-rose-50 text-xs font-black text-rose-600 transition hover:bg-rose-100 dark:bg-rose-500/10 dark:text-rose-300">
              ×
            </button>
          </div>
        </div>
      </template>
    </div>
  </div>

  <div class="rounded-[24px] border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
    <details :open="mode === 'custom'">
      <summary class="cursor-pointer list-none">
        <div class="flex items-center justify-between gap-4">
          <div>
            <h3 class="text-sm font-black text-slate-900 dark:text-white">Advanced Rule Fields</h3>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
              Field teknis yang akan disimpan ke database. Pada mode builder, field ini otomatis terisi.
            </p>
          </div>

          <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-slate-500 dark:bg-slate-800 dark:text-slate-400">
            buka/tutup
          </span>
        </div>
      </summary>

      <div class="mt-5 grid grid-cols-1 gap-6 md:grid-cols-2">
        <div>
          <label class="mb-2 block text-sm font-semibold text-slate-900 dark:text-white">Condition Field</label>
          <input name="condition_field" x-model="conditionField" @input="mode = 'custom'"
                 class="w-full rounded-lg border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/50 dark:border-slate-700 dark:bg-slate-950/50 dark:text-white">
          @error('condition_field') <p class="mt-2 text-xs text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
        </div>

        <div>
          <label class="mb-2 block text-sm font-semibold text-slate-900 dark:text-white">Operator</label>
          <input name="operator" x-model="operatorValue" @input="mode = 'custom'"
                 class="w-full rounded-lg border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/50 dark:border-slate-700 dark:bg-slate-950/50 dark:text-white">
          @error('operator') <p class="mt-2 text-xs text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
        </div>

        <div>
          <label class="mb-2 block text-sm font-semibold text-slate-900 dark:text-white">Value</label>
          <input name="value" x-model="ruleValue" @input="mode = 'custom'"
                 class="w-full rounded-lg border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/50 dark:border-slate-700 dark:bg-slate-950/50 dark:text-white">
          @error('value') <p class="mt-2 text-xs text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
        </div>

        <div>
          <label class="mb-2 block text-sm font-semibold text-slate-900 dark:text-white">Category</label>
          <select name="category" x-model="categoryValue" @change="mode = 'custom'"
                  class="w-full rounded-lg border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/50 dark:border-slate-700 dark:bg-slate-950/50 dark:text-white">
            <template x-for="cat in categoryOptions" :key="cat">
              <option :value="cat" x-text="cat"></option>
            </template>
          </select>
          @error('category') <p class="mt-2 text-xs text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
        </div>

        <div>
          <label class="mb-2 block text-sm font-semibold text-slate-900 dark:text-white">Priority</label>
          <input type="number" name="priority" x-model="priority"
                 class="w-full rounded-lg border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/50 dark:border-slate-700 dark:bg-slate-950/50 dark:text-white">
          @error('priority') <p class="mt-2 text-xs text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
        </div>

        <div class="flex items-center gap-3 pt-8">
          <input id="is_active" type="checkbox" name="is_active" value="1" @checked($isActiveValue)
                 class="h-4 w-4 rounded border-slate-300 text-blue-600 transition focus:ring-blue-500/20 dark:border-slate-700 dark:bg-slate-950">
          <label for="is_active" class="cursor-pointer text-sm font-semibold text-slate-900 dark:text-white">
            Active
          </label>
        </div>

        <div class="md:col-span-2">
          <label class="mb-2 block text-sm font-semibold text-slate-900 dark:text-white">Action Rules</label>
          <textarea name="action" x-model="actionText" @input="mode = 'custom'" rows="10"
                    class="w-full rounded-lg border border-slate-200 bg-slate-50 px-4 py-3 font-mono text-sm text-slate-900 transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/50 dark:border-slate-700 dark:bg-slate-950/50 dark:text-white"></textarea>
          @error('action') <p class="mt-2 text-xs text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
        </div>
      </div>
    </details>
  </div>

  <div class="rounded-[24px] border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
    <h3 class="text-sm font-black text-slate-900 dark:text-white">Preview Generated Rule</h3>

    <div class="mt-4 grid gap-4 lg:grid-cols-2">
      <div class="rounded-2xl bg-slate-50 p-4 text-xs dark:bg-slate-950/60">
        <p class="font-mono text-slate-600 dark:text-slate-300">condition_field: <strong x-text="conditionField"></strong></p>
        <p class="font-mono text-slate-600 dark:text-slate-300">operator: <strong x-text="operatorValue"></strong></p>
        <p class="font-mono text-slate-600 dark:text-slate-300">value: <strong x-text="ruleValue"></strong></p>
        <p class="font-mono text-slate-600 dark:text-slate-300">category: <strong x-text="categoryValue"></strong></p>
        <p class="font-mono text-slate-600 dark:text-slate-300">priority: <strong x-text="priority"></strong></p>
      </div>

      <pre x-text="actionText"
           class="max-h-72 overflow-auto rounded-2xl bg-slate-950 p-4 text-xs leading-6 text-slate-100"></pre>
    </div>
  </div>
</div>

<script>
  function layeredRuleBuilder() {
    return {
      mode: @json($initialMode),

      inventoryOptions: @json($inventoryOptions),
      categoryOptions: @json($categoryOptions),

      packageMin: @json((string) $minParticipants),
      packageMax: @json((string) $maxParticipants),
      serviceLevel: @json($serviceLevel),
      packageName: @json($packageName),

      eventType: @json($fieldValue === 'event_type' ? $ruleValue : 'wedding'),

      requirementKeyword: @json($fieldValue === 'special_requirement' ? $ruleValue : 'band'),
      requirementCategory: @json($categoryValue ?: 'music'),

      conditionField: @json($fieldValue),
      operatorValue: @json($operatorValue),
      ruleValue: @json($ruleValue),
      categoryValue: @json($categoryValue ?: 'package'),
      priority: @json((string) $priorityValue),

      equipments: @json($equipmentRows),
      crews: @json($crewRows),
      actionText: @json($actionPretty),

      init() {
        if (this.mode === 'package') this.syncPackageRule();
        if (this.mode === 'event_type') this.syncEventTypeRule();
        if (this.mode === 'special_requirement') this.syncRequirementRule();
        if (this.mode === 'condition') this.syncConditionRule(false);
        if (!this.actionText) this.syncAction();
      },

      tabClass(key) {
        return this.mode === key
          ? 'bg-white text-blue-600 shadow-sm dark:bg-slate-800 dark:text-blue-300'
          : 'text-slate-500 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white';
      },

      setMode(key) {
        this.mode = key;

        if (key === 'package') this.syncPackageRule();
        if (key === 'event_type') this.syncEventTypeRule();
        if (key === 'special_requirement') this.syncRequirementRule();
        if (key === 'condition') this.syncConditionRule(true);
        if (key === 'custom') this.syncAction();
      },

      makePackageKey() {
        const min = String(this.packageMin || '1').trim();
        const max = String(this.packageMax || '100').trim();
        const level = String(this.serviceLevel || 'basic').toLowerCase().trim();

        return `tier_${min}_${max}_${level}`;
      },

      makeDefaultPackageName() {
        const min = String(this.packageMin || '1').trim();
        const max = String(this.packageMax || '100').trim();
        const level = String(this.serviceLevel || 'basic').toLowerCase().trim();
        const label = level.charAt(0).toUpperCase() + level.slice(1);

        return `${label} ${min}-${max} Sound System Package`;
      },

      syncPackageRule() {
        this.conditionField = 'package_key';
        this.operatorValue = '=';
        this.ruleValue = this.makePackageKey();
        this.categoryValue = 'package';

        if (!this.priority || parseInt(this.priority) >= 100) {
          this.priority = '10';
        }

        if (!this.packageName || this.packageName.match(/^(Basic|Standard|Premium)\s+\d+-\d+\s+Sound System Package$/i)) {
          this.packageName = this.makeDefaultPackageName();
        }

        this.syncAction();
      },

      syncEventTypeRule() {
        this.conditionField = 'event_type';
        this.operatorValue = '=';
        this.ruleValue = this.eventType || 'wedding';
        this.categoryValue = this.categoryValue || 'event_type';

        if (!this.priority || parseInt(this.priority) < 100 || parseInt(this.priority) > 139) {
          this.priority = '120';
        }

        this.packageName = '';
        this.syncAction();
      },

      syncRequirementRule() {
        this.conditionField = 'special_requirement';
        this.operatorValue = 'contains';
        this.ruleValue = this.requirementKeyword || '';
        this.categoryValue = this.requirementCategory || 'music';

        if (!this.priority || parseInt(this.priority) < 140 || parseInt(this.priority) > 199) {
          this.priority = '140';
        }

        this.packageName = '';
        this.syncAction();
      },

      syncConditionRule(applyDefault = true) {
        if (applyDefault) {
          if (this.conditionField === 'venue_type') {
            this.operatorValue = '=';
            this.ruleValue = 'outdoor';
            this.categoryValue = 'power';
            this.priority = '160';
          }

          if (this.conditionField === 'duration') {
            this.operatorValue = 'between';
            this.ruleValue = '5-8';
            this.categoryValue = 'duration';
            this.priority = '170';
          }

          if (this.conditionField === 'service_level') {
            this.operatorValue = '=';
            this.ruleValue = 'premium';
            this.categoryValue = 'reliability';
            this.priority = '182';
          }
        }

        this.packageName = '';
        this.syncAction();
      },

      syncAction() {
        const lines = [];

        if (this.mode === 'package' && String(this.packageName || '').trim() !== '') {
          lines.push(`LABEL: ${String(this.packageName).trim()}`);
        }

        this.equipments.forEach((item) => {
          const qty = parseInt(item.qty || 0);
          const name = String(item.name || '').trim();

          if (qty > 0 && name !== '') {
            lines.push(`EQUIPMENT: ${qty}, ${name}`);
          }
        });

        this.crews.forEach((item) => {
          const qty = parseInt(item.qty || 0);
          const role = String(item.role || '').trim();

          if (qty > 0 && role !== '') {
            lines.push(`CREW: ${qty}, ${role}`);
          }
        });

        this.actionText = lines.join("\n");
      },

      addEquipment() {
        this.equipments.push({
          qty: 1,
          name: ''
        });

        this.syncAction();
      },

      removeEquipment(index) {
        this.equipments.splice(index, 1);
        this.syncAction();
      },

      addCrew() {
        this.crews.push({
          qty: 1,
          role: 'operator'
        });

        this.syncAction();
      },

      removeCrew(index) {
        this.crews.splice(index, 1);
        this.syncAction();
      }
    };
  }
</script>