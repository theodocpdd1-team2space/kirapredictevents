@php
  $inv = $inventory ?? null;

  $equipmentName = old('equipment_name', $inv->equipment_name ?? '');
  $qty  = old('quantity', $inv->quantity ?? 0);
  $price = old('price', $inv->price ?? 0);

  // category current
  $currentCategory = old('category', $inv->category ?? '');
  $options = $categoryOptions ?? [];

  // Tentukan default pilihan category:
  // kalau category ada di options -> pakai itu
  // kalau tidak -> other + isi di input
  $categoryChoice = old('category_choice');
  $categoryOther  = old('category_other');

  if ($categoryChoice === null) {
    if ($currentCategory && in_array($currentCategory, $options, true) && strtolower($currentCategory) !== 'other') {
      $categoryChoice = $currentCategory;
      $categoryOther = '';
    } else {
      $categoryChoice = 'other';
      $categoryOther = $currentCategory;
    }
  }

  $st = old('status', $inv->status ?? 'active');
@endphp

<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
  <div class="md:col-span-2">
    <label class="block text-sm font-semibold text-slate-900 dark:text-white mb-2 transition-colors">Equipment Name</label>
    <input name="equipment_name" value="{{ $equipmentName }}"
           placeholder="e.g. Professional Camera Kit"
           class="w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-950/50 px-4 py-3 text-sm text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500/50 dark:focus:ring-blue-500/30 focus:border-blue-500 dark:focus:border-blue-500 transition-colors">
    @error('equipment_name') <p class="text-xs text-red-600 dark:text-red-400 mt-2">{{ $message }}</p> @enderror
  </div>

  {{-- CATEGORY DROPDOWN --}}
  <div>
    <label class="block text-sm font-semibold text-slate-900 dark:text-white mb-2 transition-colors">Category</label>

    <select id="category_choice" name="category_choice"
            class="w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-950/50 px-4 py-3 text-sm text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500/50 dark:focus:ring-blue-500/30 focus:border-blue-500 dark:focus:border-blue-500 transition-colors">
      @foreach($options as $opt)
        @php($val = strtolower($opt) === 'other' ? 'other' : $opt)
        <option value="{{ $val }}" @selected($categoryChoice === $val)>{{ $opt }}</option>
      @endforeach
      @if(!in_array('Other', $options, true) && !in_array('other', $options, true))
        <option value="other" @selected($categoryChoice === 'other')>Other</option>
      @endif
    </select>

    <div id="category_other_wrap" class="mt-3 {{ $categoryChoice === 'other' ? '' : 'hidden' }}">
      <input name="category_other" value="{{ $categoryOther }}"
             placeholder="Tulis kategori lainnya..."
             class="w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-950/50 px-4 py-3 text-sm text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500/50 dark:focus:ring-blue-500/30 focus:border-blue-500 dark:focus:border-blue-500 transition-colors">
      @error('category_other') <p class="text-xs text-red-600 dark:text-red-400 mt-2">{{ $message }}</p> @enderror
    </div>

    @error('category_choice') <p class="text-xs text-red-600 dark:text-red-400 mt-2">{{ $message }}</p> @enderror
  </div>

  <div>
    <label class="block text-sm font-semibold text-slate-900 dark:text-white mb-2 transition-colors">Quantity</label>
    <input type="number" name="quantity" value="{{ $qty }}"
           class="w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-950/50 px-4 py-3 text-sm text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500/50 dark:focus:ring-blue-500/30 focus:border-blue-500 dark:focus:border-blue-500 transition-colors">
    @error('quantity') <p class="text-xs text-red-600 dark:text-red-400 mt-2">{{ $message }}</p> @enderror
  </div>

  <div class="md:col-span-2">
    <label class="block text-sm font-semibold text-slate-900 dark:text-white mb-2 transition-colors">Unit Price</label>
    <input type="number" name="price" value="{{ $price }}"
           placeholder="e.g. 800000"
           class="w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-950/50 px-4 py-3 text-sm text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500/50 dark:focus:ring-blue-500/30 focus:border-blue-500 dark:focus:border-blue-500 transition-colors">
    @error('price') <p class="text-xs text-red-600 dark:text-red-400 mt-2">{{ $message }}</p> @enderror
    <p class="text-xs text-slate-500 dark:text-slate-400 mt-2 transition-colors">Input dalam Rupiah (tanpa titik/koma).</p>
  </div>

  {{-- STATUS --}}
  <div class="md:col-span-2">
    <label class="block text-sm font-semibold text-slate-900 dark:text-white mb-2 transition-colors">Status</label>

    <select name="status"
            class="w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-950/50 px-4 py-3 text-sm text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500/50 dark:focus:ring-blue-500/30 focus:border-blue-500 dark:focus:border-blue-500 transition-colors">
      <option value="active" @selected($st==='active')>Active</option>
      <option value="maintenance" @selected($st==='maintenance')>Maintenance</option>
      <option value="inactive" @selected($st==='inactive')>Inactive</option>
    </select>

    @error('status') <p class="text-xs text-red-600 dark:text-red-400 mt-2">{{ $message }}</p> @enderror
  </div>

  {{-- IMAGE --}}
  <div class="md:col-span-2">
    <label class="block text-sm font-semibold text-slate-900 dark:text-white mb-2 transition-colors">Image</label>

    <input type="file" name="image" accept="image/*"
           class="block w-full text-sm text-slate-700 dark:text-slate-300
                  file:mr-4 file:rounded-lg file:border-0 file:bg-slate-900 dark:file:bg-slate-700
                  file:px-4 file:py-2.5 file:text-sm file:font-semibold file:text-white
                  hover:file:bg-slate-800 dark:hover:file:bg-slate-600 transition-all cursor-pointer">

    @error('image') <p class="text-xs text-red-600 dark:text-red-400 mt-2">{{ $message }}</p> @enderror

    @if(!empty($inv?->image_path))
      <div class="mt-3 flex items-center gap-3">
        <img src="{{ asset('storage/'.$inv->image_path) }}"
             class="h-12 w-12 rounded-lg object-cover border border-slate-200 dark:border-slate-700 transition-colors">
        <p class="text-xs text-slate-500 dark:text-slate-400 transition-colors">Current image</p>
      </div>
    @endif
  </div>
</div>

<script>
(function () {
  const sel = document.getElementById('category_choice');
  const wrap = document.getElementById('category_other_wrap');
  if (!sel || !wrap) return;

  function toggle() {
    if (sel.value === 'other') wrap.classList.remove('hidden');
    else wrap.classList.add('hidden');
  }

  sel.addEventListener('change', toggle);
  toggle();
})();
</script>