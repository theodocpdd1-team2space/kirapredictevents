<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\Inventory;
use App\Models\Rule;
use Illuminate\Http\Request;

class RuleController extends Controller
{
    private function tenantId(): int
    {
        $tenantId = auth()->user()?->tenant_id;

        abort_unless($tenantId, 403, 'User belum memiliki tenant.');

        return (int) $tenantId;
    }

    private function guardOwnerRole(): void
    {
        abort_unless(auth()->user()?->isOwner(), 403, 'Akses hanya untuk Owner.');
    }

    private function guardTenantRule(Rule $rule): void
    {
        abort_unless((int) $rule->tenant_id === $this->tenantId(), 403);
    }

    private function inventoriesForRuleBuilder()
    {
        return Inventory::query()
            ->where('tenant_id', $this->tenantId())
            ->where('status', 'active')
            ->orderBy('equipment_name', 'asc')
            ->get([
                'id',
                'equipment_name',
                'category',
                'quantity',
                'price',
                'status',
            ]);
    }

    private function ruleCategoriesForBuilder()
    {
        $defaultCategories = [
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
        ];

        $dbCategories = Rule::query()
            ->where('tenant_id', $this->tenantId())
            ->whereNotNull('category')
            ->where('category', '!=', '')
            ->distinct()
            ->orderBy('category')
            ->pluck('category')
            ->toArray();

        return collect(array_merge($defaultCategories, $dbCategories))
            ->filter()
            ->unique()
            ->values();
    }

    public function index(Request $request)
    {
        $this->guardOwnerRole();

        $search = trim((string) $request->get('search', ''));

        $rules = Rule::query()
            ->where('tenant_id', $this->tenantId())
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($qq) use ($search) {
                    $qq->where('condition_field', 'like', "%{$search}%")
                        ->orWhere('operator', 'like', "%{$search}%")
                        ->orWhere('value', 'like', "%{$search}%")
                        ->orWhere('category', 'like', "%{$search}%");
                });
            })
            ->orderBy('priority', 'asc')
            ->orderBy('id', 'asc')
            ->paginate(20)
            ->withQueryString();

        return view('pages.settings.rules.index', compact('rules', 'search'));
    }

    public function create()
    {
        $this->guardOwnerRole();

        $inventories = $this->inventoriesForRuleBuilder();
        $ruleCategories = $this->ruleCategoriesForBuilder();

        return view('pages.settings.rules.create', compact('inventories', 'ruleCategories'));
    }

    public function store(Request $request)
    {
        $this->guardOwnerRole();

        $data = $request->validate([
            'condition_field' => ['required', 'string', 'max:100'],
            'operator'        => ['required', 'string', 'max:20'],
            'value'           => ['required', 'string', 'max:255'],
            'action'          => ['required', 'string'],
            'category'        => ['nullable', 'string', 'max:100'],
            'priority'        => ['required', 'integer', 'min:0', 'max:9999'],
            'is_active'       => ['nullable'],
        ]);

        $data['tenant_id'] = $this->tenantId();
        $data['user_id'] = auth()->id();
        $data['action'] = $this->normalizeAction($data['action'] ?? null);
        $data['is_active'] = $request->boolean('is_active');

        Rule::create($data);

        return redirect()
            ->route('settings.rules.index')
            ->with('success', 'Rule created successfully.');
    }

    public function edit(Rule $rule)
    {
        $this->guardOwnerRole();
        $this->guardTenantRule($rule);

        $inventories = $this->inventoriesForRuleBuilder();
        $ruleCategories = $this->ruleCategoriesForBuilder();

        return view('pages.settings.rules.edit', compact('rule', 'inventories', 'ruleCategories'));
    }

    public function update(Request $request, Rule $rule)
    {
        $this->guardOwnerRole();
        $this->guardTenantRule($rule);

        $data = $request->validate([
            'condition_field' => ['required', 'string', 'max:100'],
            'operator'        => ['required', 'string', 'max:20'],
            'value'           => ['required', 'string', 'max:255'],
            'action'          => ['required', 'string'],
            'category'        => ['nullable', 'string', 'max:100'],
            'priority'        => ['required', 'integer', 'min:0', 'max:9999'],
            'is_active'       => ['nullable'],
        ]);

        $data['action'] = $this->normalizeAction($data['action'] ?? null);
        $data['is_active'] = $request->boolean('is_active');

        $rule->update($data);

        return redirect()
            ->route('settings.rules.index')
            ->with('success', 'Rule updated successfully.');
    }

    public function toggle(Rule $rule)
    {
        $this->guardOwnerRole();
        $this->guardTenantRule($rule);

        $rule->update([
            'is_active' => ! $rule->is_active,
        ]);

        return back()->with('success', 'Rule status updated.');
    }

    public function destroy(Rule $rule)
    {
        $this->guardOwnerRole();
        $this->guardTenantRule($rule);

        $rule->delete();

        return back()->with('success', 'Rule deleted successfully.');
    }

    public function bulkDelete(Request $request)
    {
        $this->guardOwnerRole();

        $data = $request->validate([
            'ids'   => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'exists:rules,id'],
        ]);

        Rule::where('tenant_id', $this->tenantId())
            ->whereIn('id', $data['ids'])
            ->delete();

        return back()->with('success', count($data['ids']) . ' selected rule(s) deleted successfully.');
    }

    public function importForm()
    {
        $this->guardOwnerRole();

        return view('pages.settings.rules.import');
    }

    public function import(Request $request)
    {
        $this->guardOwnerRole();

        $request->validate([
            'csv' => ['required', 'file', 'mimes:csv,txt', 'max:2048'],
        ]);

        $rows = array_map('str_getcsv', file($request->file('csv')->getRealPath()));

        if (count($rows) < 2) {
            return back()->withErrors(['csv' => 'CSV kosong atau tidak valid.']);
        }

        $header = array_map(fn ($h) => strtolower(trim((string) $h)), $rows[0]);
        $required = ['condition_field', 'operator', 'value', 'action', 'category', 'priority', 'is_active'];

        foreach ($required as $col) {
            if (! in_array($col, $header, true)) {
                return back()->withErrors([
                    'csv' => 'Header CSV wajib: ' . implode(', ', $required),
                ]);
            }
        }

        $map = array_flip($header);
        $tenantId = $this->tenantId();
        $userId = auth()->id();

        $ok = 0;
        $skip = 0;

        foreach (array_slice($rows, 1) as $r) {
            if (count($r) < count($header)) {
                $skip++;
                continue;
            }

            $conditionField = trim((string) ($r[$map['condition_field']] ?? ''));
            $operator = trim((string) ($r[$map['operator']] ?? ''));
            $value = trim((string) ($r[$map['value']] ?? ''));

            if ($conditionField === '' || $operator === '' || $value === '') {
                $skip++;
                continue;
            }

            $rawAction = trim((string) ($r[$map['action']] ?? ''));

            try {
                $action = $this->normalizeAction($rawAction);
            } catch (\Throwable $e) {
                $skip++;
                continue;
            }

            $category = trim((string) ($r[$map['category']] ?? ''));
            $priority = (int) ($r[$map['priority']] ?? 100);

            $rawActive = trim((string) ($r[$map['is_active']] ?? '1'));
            $isActive = in_array(strtolower($rawActive), ['1', 'true', 'yes', 'y'], true) ? 1 : 0;

            Rule::create([
                'tenant_id'       => $tenantId,
                'user_id'         => $userId,
                'condition_field' => $conditionField,
                'operator'        => $operator,
                'value'           => $value,
                'action'          => $action,
                'category'        => $category !== '' ? $category : null,
                'priority'        => $priority,
                'is_active'       => $isActive,
            ]);

            $ok++;
        }

        return redirect()
            ->route('settings.rules.index')
            ->with('success', "Import selesai. Berhasil: {$ok}, Dilewati: {$skip}");
    }

    private function normalizeAction($action): array
    {
        if (is_array($action)) {
            return $action;
        }

        $action = trim((string) $action);

        if ($action === '') {
            abort(422, 'Action rules wajib diisi.');
        }

        if (str_starts_with($action, '[')) {
            $decoded = json_decode($action, true);

            if (json_last_error() !== JSON_ERROR_NONE || ! is_array($decoded)) {
                abort(422, 'Action JSON tidak valid.');
            }

            return $decoded;
        }

        return $this->parseActionText($action);
    }

    private function parseActionText(string $text): array
    {
        $text = trim($text);

        if ($text === '') {
            abort(422, 'Action rules wajib diisi.');
        }

        $normalized = str_replace(["\r\n", "\r"], "\n", $text);
        $normalized = str_replace(';', "\n", $normalized);

        $lines = array_values(array_filter(array_map(function ($line) {
            return trim((string) $line);
        }, explode("\n", $normalized))));

        $actions = [];

        foreach ($lines as $index => $line) {
            if (str_starts_with($line, '#')) {
                continue;
            }

            if (! str_contains($line, ':')) {
                abort(422, 'Format action baris ke-' . ($index + 1) . ' tidak valid. Gunakan format LABEL:, EQUIPMENT:, atau CREW:.');
            }

            [$rawType, $rawPayload] = explode(':', $line, 2);

            $type = strtoupper(trim((string) $rawType));
            $payload = trim((string) $rawPayload);

            if ($payload === '') {
                abort(422, 'Isi action baris ke-' . ($index + 1) . ' tidak boleh kosong.');
            }

            if (in_array($type, ['LABEL', 'PACKAGE', 'PACKAGE_LABEL', 'SET_PACKAGE_LABEL'], true)) {
                $actions[] = [
                    'type' => 'SET_PACKAGE_LABEL',
                    'name' => $payload,
                ];

                continue;
            }

            if (in_array($type, ['EQUIPMENT', 'ALAT', 'ADD_EQUIPMENT'], true)) {
                [$qty, $name] = $this->parseQtyAndText($payload, $index + 1, 'equipment');

                $actions[] = [
                    'type' => 'ADD_EQUIPMENT',
                    'qty' => $qty,
                    'name' => $name,
                ];

                continue;
            }

            if (in_array($type, ['CREW', 'STAFF', 'ADD_CREW'], true)) {
                [$qty, $role] = $this->parseQtyAndText($payload, $index + 1, 'crew');

                $actions[] = [
                    'type' => 'ADD_CREW',
                    'qty' => $qty,
                    'role' => $role,
                ];

                continue;
            }

            abort(422, 'Tipe action baris ke-' . ($index + 1) . ' tidak dikenal: ' . $type . '. Gunakan LABEL, EQUIPMENT, atau CREW.');
        }

        if (count($actions) === 0) {
            abort(422, 'Action rules minimal berisi 1 baris.');
        }

        return $actions;
    }

    private function parseQtyAndText(string $payload, int $lineNumber, string $context): array
    {
        $payload = trim($payload);

        $parts = preg_split('/\s*[,|]\s*|\s+-\s+/', $payload, 2);

        if (! is_array($parts) || count($parts) < 2) {
            $label = $context === 'crew' ? 'role crew' : 'nama alat';

            abort(422, 'Format baris ke-' . $lineNumber . ' tidak valid. Gunakan format: ' . strtoupper($context) . ': jumlah, ' . $label . '.');
        }

        $qtyRaw = trim((string) ($parts[0] ?? ''));
        $text = trim((string) ($parts[1] ?? ''));

        if ($qtyRaw === '' || ! is_numeric($qtyRaw)) {
            abort(422, 'Jumlah pada baris ke-' . $lineNumber . ' harus berupa angka.');
        }

        $qty = (int) $qtyRaw;

        if ($qty < 1) {
            abort(422, 'Jumlah pada baris ke-' . $lineNumber . ' minimal 1.');
        }

        if ($text === '') {
            $label = $context === 'crew' ? 'Role crew' : 'Nama alat';

            abort(422, $label . ' pada baris ke-' . $lineNumber . ' wajib diisi.');
        }

        return [$qty, $text];
    }
}