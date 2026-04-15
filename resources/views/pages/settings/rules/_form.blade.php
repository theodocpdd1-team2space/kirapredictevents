@php
  $isEdit = isset($rule);
  $actionPretty = old('action_raw');
  if ($actionPretty === null && $isEdit) {
      $actionPretty = json_encode($rule->action, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES);
  }
@endphp

<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
  <div>
    <label class="block text-sm font-semibold text-slate-900 dark:text-white mb-2 transition-colors">Condition Field</label>
    <input name="condition_field" value="{{ old('condition_field', $rule->condition_field ?? '') }}"
           class="w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-950/50 px-4 py-3 text-sm text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500/50 dark:focus:ring-blue-500/30 focus:border-blue-500 transition-colors">
    @error('condition_field') <p class="text-xs text-red-600 dark:text-red-400 mt-2">{{ $message }}</p> @enderror
  </div>

  <div>
    <label class="block text-sm font-semibold text-slate-900 dark:text-white mb-2 transition-colors">Operator</label>
    <input name="operator" value="{{ old('operator', $rule->operator ?? '') }}"
           placeholder="=, >, >=, <, <=, between, contains"
           class="w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-950/50 px-4 py-3 text-sm text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500/50 dark:focus:ring-blue-500/30 focus:border-blue-500 transition-colors">
    @error('operator') <p class="text-xs text-red-600 dark:text-red-400 mt-2">{{ $message }}</p> @enderror
  </div>

  <div>
    <label class="block text-sm font-semibold text-slate-900 dark:text-white mb-2 transition-colors">Value</label>
    <input name="value" value="{{ old('value', $rule->value ?? '') }}"
           class="w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-950/50 px-4 py-3 text-sm text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500/50 dark:focus:ring-blue-500/30 focus:border-blue-500 transition-colors">
    @error('value') <p class="text-xs text-red-600 dark:text-red-400 mt-2">{{ $message }}</p> @enderror
  </div>

  <div>
    <label class="block text-sm font-semibold text-slate-900 dark:text-white mb-2 transition-colors">Category</label>
    <input name="category" value="{{ old('category', $rule->category ?? '') }}"
           class="w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-950/50 px-4 py-3 text-sm text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500/50 dark:focus:ring-blue-500/30 focus:border-blue-500 transition-colors">
    @error('category') <p class="text-xs text-red-600 dark:text-red-400 mt-2">{{ $message }}</p> @enderror
  </div>

  <div>
    <label class="block text-sm font-semibold text-slate-900 dark:text-white mb-2 transition-colors">Priority</label>
    <input type="number" name="priority" value="{{ old('priority', $rule->priority ?? 100) }}"
           class="w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-950/50 px-4 py-3 text-sm text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500/50 dark:focus:ring-blue-500/30 focus:border-blue-500 transition-colors">
    @error('priority') <p class="text-xs text-red-600 dark:text-red-400 mt-2">{{ $message }}</p> @enderror
  </div>

  <div class="flex items-center gap-3 pt-8">
    <input id="is_active" type="checkbox" name="is_active" value="1"
           @checked(old('is_active', ($rule->is_active ?? true)))
           class="w-4 h-4 rounded border-slate-300 dark:border-slate-700 dark:bg-slate-950 text-blue-600 focus:ring-blue-500/20 transition-colors">
    <label for="is_active" class="text-sm font-semibold text-slate-900 dark:text-white cursor-pointer transition-colors">Active</label>
  </div>

  <div class="md:col-span-2">
    <label class="block text-sm font-semibold text-slate-900 dark:text-white mb-2 transition-colors">Action (JSON Array)</label>
    <textarea name="action_raw" rows="10"
              class="w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-950/50 px-4 py-3 text-sm font-mono text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500/50 dark:focus:ring-blue-500/30 focus:border-blue-500 transition-colors">{{ $actionPretty }}</textarea>
    <p class="text-xs text-slate-500 dark:text-slate-400 mt-2 transition-colors">Format: [{"type":"ADD_EQUIPMENT","name":"Speaker Aktif","qty":2}]</p>
    @error('action_raw') <p class="text-xs text-red-600 dark:text-red-400 mt-2">{{ $message }}</p> @enderror
  </div>
</div>