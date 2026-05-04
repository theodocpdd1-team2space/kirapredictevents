<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
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

        return view('pages.settings.rules.create');
    }

    public function store(Request $request)
    {
        $this->guardOwnerRole();

        $data = $request->validate([
            'condition_field' => ['required', 'string', 'max:100'],
            'operator'        => ['required', 'string', 'max:20'],
            'value'           => ['required', 'string', 'max:255'],
            'action'          => ['nullable'],
            'category'        => ['nullable', 'string', 'max:100'],
            'priority'        => ['required', 'integer', 'min:0', 'max:9999'],
            'is_active'       => ['nullable'],
        ]);

        $data['tenant_id']  = $this->tenantId();
        $data['created_by'] = auth()->id();
        $data['updated_by'] = auth()->id();
        $data['action']     = $this->normalizeAction($data['action'] ?? null);
        $data['is_active']  = $request->boolean('is_active');

        Rule::create($data);

        return redirect()
            ->route('settings.rules.index')
            ->with('success', 'Rule created successfully.');
    }

    public function edit(Rule $rule)
    {
        $this->guardOwnerRole();
        $this->guardTenantRule($rule);

        return view('pages.settings.rules.edit', compact('rule'));
    }

    public function update(Request $request, Rule $rule)
    {
        $this->guardOwnerRole();
        $this->guardTenantRule($rule);

        $data = $request->validate([
            'condition_field' => ['required', 'string', 'max:100'],
            'operator'        => ['required', 'string', 'max:20'],
            'value'           => ['required', 'string', 'max:255'],
            'action'          => ['nullable'],
            'category'        => ['nullable', 'string', 'max:100'],
            'priority'        => ['required', 'integer', 'min:0', 'max:9999'],
            'is_active'       => ['nullable'],
        ]);

        $data['action']     = $this->normalizeAction($data['action'] ?? null);
        $data['is_active']  = $request->boolean('is_active');
        $data['updated_by'] = auth()->id();

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
            'is_active'  => !$rule->is_active,
            'updated_by' => auth()->id(),
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
            if (!in_array($col, $header, true)) {
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
            $operator       = trim((string) ($r[$map['operator']] ?? ''));
            $value          = trim((string) ($r[$map['value']] ?? ''));

            if ($conditionField === '' || $operator === '' || $value === '') {
                $skip++;
                continue;
            }

            $rawAction = trim((string) ($r[$map['action']] ?? ''));
            $action = $this->normalizeAction($rawAction);

            $category = trim((string) ($r[$map['category']] ?? ''));
            $priority = (int) ($r[$map['priority']] ?? 0);

            $rawActive = trim((string) ($r[$map['is_active']] ?? '1'));
            $isActive = in_array(strtolower($rawActive), ['1', 'true', 'yes', 'y'], true) ? 1 : 0;

            Rule::create([
                'tenant_id'       => $tenantId,
                'created_by'      => $userId,
                'updated_by'      => $userId,
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

    private function normalizeAction($action)
    {
        if ($action === null) {
            return null;
        }

        if (is_array($action)) {
            return $action;
        }

        $action = trim((string) $action);

        if ($action === '') {
            return null;
        }

        $decoded = json_decode($action, true);

        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            return $decoded;
        }

        return null;
    }
}