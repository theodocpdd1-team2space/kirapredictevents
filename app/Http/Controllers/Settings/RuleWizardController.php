<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\Inventory;
use App\Models\Rule;
use Illuminate\Http\Request;

class RuleWizardController extends Controller
{
    public function create(Request $request)
    {
        $userId = auth()->id();

        // Autocomplete data dari inventories milik user
        $inventoryNames = Inventory::query()
            ->where('user_id', $userId)
            ->orderBy('equipment_name')
            ->pluck('equipment_name')
            ->values()
            ->all();

        $inventoryCategories = Inventory::query()
            ->where('user_id', $userId)
            ->whereNotNull('category')
            ->select('category')
            ->distinct()
            ->orderBy('category')
            ->pluck('category')
            ->values()
            ->all();

        return view('pages.settings.rules.wizard.create', [
            'inventoryNames' => $inventoryNames,
            'inventoryCategories' => $inventoryCategories,
        ]);
    }

    public function preview(Request $request)
    {
        $data = $request->validate([
            'facts_json' => ['required', 'string'],
            'rule_blocks' => ['nullable', 'array'],
            'rule_blocks.*.condition_field' => ['required', 'string', 'max:100'],
            'rule_blocks.*.operator' => ['required', 'string', 'max:20'],
            'rule_blocks.*.value' => ['required', 'string', 'max:255'],
            'rule_blocks.*.category' => ['nullable', 'string', 'max:100'],
            'rule_blocks.*.priority' => ['required', 'integer', 'min:0', 'max:9999'],
            'rule_blocks.*.is_active' => ['nullable'],
            'rule_blocks.*.actions' => ['nullable', 'array'],
            'rule_blocks.*.actions.*.type' => ['required', 'string'],
            'rule_blocks.*.actions.*.name' => ['nullable', 'string'],
            'rule_blocks.*.actions.*.role' => ['nullable', 'string'],
            'rule_blocks.*.actions.*.qty' => ['nullable'],
        ]);

        $facts = json_decode($data['facts_json'], true);
        if (!is_array($facts)) $facts = [];

        // normalize rules
        $generated = [];
        foreach (($data['rule_blocks'] ?? []) as $r) {
            $actions = $r['actions'] ?? [];
            if (!is_array($actions)) $actions = [];

            // filter qty > 0 saja
            $actions = array_values(array_filter($actions, function ($a) {
                $qty = (int)($a['qty'] ?? 0);
                return $qty > 0;
            }));

            if (count($actions) === 0) continue;

            $generated[] = [
                'condition_field' => $r['condition_field'],
                'operator'        => $r['operator'],
                'value'           => $r['value'],
                'category'        => $r['category'] ?? null,
                'priority'        => (int)$r['priority'],
                'is_active'       => !empty($r['is_active']),
                'action'          => $actions,
            ];
        }

        return view('pages.settings.rules.wizard.preview', [
            'facts' => $facts,
            'wizardPayload' => $data,
            'generated' => $generated,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'facts_json' => ['required', 'string'],
            'rules_json' => ['required', 'string'],
            'selected'   => ['nullable', 'array'],
            'selected.*' => ['integer'],
        ]);

        $userId = auth()->id();

        $rules = json_decode($data['rules_json'], true);
        if (!is_array($rules)) $rules = [];

        $selectedIdx = $data['selected'] ?? [];
        $selectedIdx = array_map('intval', $selectedIdx);

        $saved = 0;

        foreach ($rules as $idx => $r) {
            if (!in_array((int)$idx, $selectedIdx, true)) continue;

            Rule::create([
                'user_id'         => $userId,
                'condition_field' => (string)($r['condition_field'] ?? ''),
                'operator'        => (string)($r['operator'] ?? ''),
                'value'           => (string)($r['value'] ?? ''),
                'action'          => $r['action'] ?? [],
                'category'        => $r['category'] ?? null,
                'priority'        => (int)($r['priority'] ?? 100),
                'is_active'       => (bool)($r['is_active'] ?? true),
            ]);

            $saved++;
        }

        return redirect()
            ->route('settings.rules.index')
            ->with('success', "Wizard saved: {$saved} rules dibuat.");
    }
}