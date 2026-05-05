<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class InventoryController extends Controller
{
    private function tenantId(): int
    {
        $tenantId = auth()->user()?->tenant_id;

        abort_unless($tenantId, 403, 'User belum memiliki tenant.');

        return (int) $tenantId;
    }

    /**
     * Inventory boleh dikelola oleh Owner dan Staff.
     * Rules/Cost/Business/Staff Management tetap Owner only di controller masing-masing.
     */
    private function guardInventoryManager(): void
    {
        abort_unless(
            auth()->user()?->isOwner() || auth()->user()?->isStaff(),
            403,
            'Akses hanya untuk Owner atau Staff.'
        );

        abort_unless(auth()->user()?->tenant_id, 403, 'User belum memiliki tenant.');
    }

    private function guardTenant(Inventory $inventory): void
    {
        abort_unless((int) $inventory->tenant_id === $this->tenantId(), 403);
    }

    private function normalizeStatus(?string $status): string
    {
        $s = strtolower(trim((string) $status));

        return match ($s) {
            'maintenance' => 'maintenance',
            'inactive'    => 'inactive',
            'available', 'used', 'active', '' => 'active',
            default       => 'active',
        };
    }

    private function categoryOptions(): array
    {
        $existing = Inventory::query()
            ->where('tenant_id', $this->tenantId())
            ->select('category')
            ->distinct()
            ->orderBy('category')
            ->pluck('category')
            ->filter()
            ->values()
            ->toArray();

        $defaults = [
            'Audio',
            'Video',
            'Lighting',
            'Power',
            'Rigging',
            'Crew',
            'Transportation',
            'Stage',
            'Other',
        ];

        $all = $existing;

        foreach ($defaults as $d) {
            if (!in_array($d, $all, true)) {
                $all[] = $d;
            }
        }

        return $all;
    }

    protected function sharedPublicStorageRoot(): string
    {
        return public_path('storage');
    }

    protected function syncPublicFile(string $relativePath): void
    {
        $source = storage_path('app/public/' . $relativePath);

        $publicRoot = $this->sharedPublicStorageRoot();
        $target = $publicRoot . '/' . $relativePath;
        $targetDir = dirname($target);

        if (!File::exists($targetDir)) {
            File::makeDirectory($targetDir, 0755, true);
        }

        if (File::exists($source)) {
            File::copy($source, $target);
        }
    }

    protected function deletePublicFile(?string $relativePath): void
    {
        if (!$relativePath) {
            return;
        }

        Storage::disk('public')->delete($relativePath);

        $publicFile = $this->sharedPublicStorageRoot() . '/' . $relativePath;

        if (File::exists($publicFile)) {
            File::delete($publicFile);
        }
    }

    /**
     * Owner dan Staff boleh melihat inventory tenant.
     */
    public function index(Request $request)
    {
        $this->guardInventoryManager();

        $tenantId = $this->tenantId();

        $q = Inventory::query()
            ->where('tenant_id', $tenantId)
            ->latest();

        if ($request->filled('status')) {
            $q->where('status', $this->normalizeStatus($request->status));
        }

        if ($request->filled('category')) {
            $q->where('category', $request->category);
        }

        if ($request->filled('search')) {
            $q->where('equipment_name', 'like', '%' . $request->search . '%');
        }

        $perPage = $request->get('per_page', '10');

        if ($perPage === 'all') {
            $items = $q->get();
        } else {
            $perPage = max(1, (int) $perPage);
            $items = $q->paginate($perPage)->withQueryString();
            $items->onEachSide(1);
        }

        $categories = Inventory::query()
            ->where('tenant_id', $tenantId)
            ->select('category')
            ->distinct()
            ->orderBy('category')
            ->pluck('category');

        $statuses = ['active', 'maintenance', 'inactive'];

        return view('pages.inventories.index', compact('items', 'categories', 'statuses', 'perPage'));
    }

    /**
     * Owner dan Staff boleh create inventory.
     */
    public function create()
    {
        $this->guardInventoryManager();

        $categoryOptions = $this->categoryOptions();
        $statusOptions = ['active', 'maintenance', 'inactive'];

        return view('pages.inventories.create', compact('categoryOptions', 'statusOptions'));
    }

    /**
     * Owner dan Staff boleh store inventory.
     */
    public function store(Request $request)
    {
        $this->guardInventoryManager();

        $data = $request->validate([
            'equipment_name'   => ['required', 'string', 'max:120'],
            'category_choice'  => ['required', 'string', 'max:80'],
            'category_other'   => ['nullable', 'string', 'max:80'],
            'quantity'         => ['required', 'integer', 'min:0'],
            'price'            => ['required', 'numeric', 'min:0'],
            'status'           => ['required', 'in:active,maintenance,inactive'],
            'image'            => ['nullable', 'image', 'max:2048'],
        ]);

        $categoryFinal = $data['category_choice'] === 'other'
            ? trim((string) ($data['category_other'] ?? ''))
            : $data['category_choice'];

        if ($data['category_choice'] === 'other' && $categoryFinal === '') {
            return back()
                ->withErrors(['category_other' => 'Category (Other) wajib diisi.'])
                ->withInput();
        }

        $payload = [
            'tenant_id'      => $this->tenantId(),
            'created_by'     => auth()->id(),
            'equipment_name' => trim($data['equipment_name']),
            'category'       => $categoryFinal,
            'quantity'       => (int) $data['quantity'],
            'price'          => (int) $data['price'],
            'status'         => $this->normalizeStatus($data['status']),
        ];

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('inventories', 'public');
            $this->syncPublicFile($path);
            $payload['image_path'] = $path;
        }

        try {
            Inventory::create($payload);
        } catch (QueryException $e) {
            if ((int) ($e->errorInfo[1] ?? 0) === 1062) {
                return back()
                    ->withInput()
                    ->withErrors(['equipment_name' => 'Peralatan sudah ada. Silakan edit data yang sudah ada atau ganti nama.']);
            }

            throw $e;
        }

        return redirect()
            ->route('inventories.index')
            ->with('success', 'Equipment added.');
    }

    /**
     * Owner dan Staff boleh edit inventory tenant yang sama.
     */
    public function edit(Inventory $inventory)
    {
        $this->guardInventoryManager();
        $this->guardTenant($inventory);

        $categoryOptions = $this->categoryOptions();
        $statusOptions = ['active', 'maintenance', 'inactive'];

        return view('pages.inventories.edit', compact('inventory', 'categoryOptions', 'statusOptions'));
    }

    /**
     * Owner dan Staff boleh update inventory tenant yang sama.
     */
    public function update(Request $request, Inventory $inventory)
    {
        $this->guardInventoryManager();
        $this->guardTenant($inventory);

        $data = $request->validate([
            'equipment_name'   => ['required', 'string', 'max:120'],
            'category_choice'  => ['required', 'string', 'max:80'],
            'category_other'   => ['nullable', 'string', 'max:80'],
            'quantity'         => ['required', 'integer', 'min:0'],
            'price'            => ['required', 'numeric', 'min:0'],
            'status'           => ['required', 'in:active,maintenance,inactive'],
            'image'            => ['nullable', 'image', 'max:2048'],
        ]);

        $categoryFinal = $data['category_choice'] === 'other'
            ? trim((string) ($data['category_other'] ?? ''))
            : $data['category_choice'];

        if ($data['category_choice'] === 'other' && $categoryFinal === '') {
            return back()
                ->withErrors(['category_other' => 'Category (Other) wajib diisi.'])
                ->withInput();
        }

        $payload = [
            'equipment_name' => trim($data['equipment_name']),
            'category'       => $categoryFinal,
            'quantity'       => (int) $data['quantity'],
            'price'          => (int) $data['price'],
            'status'         => $this->normalizeStatus($data['status']),
        ];

        if ($request->hasFile('image')) {
            if ($inventory->image_path) {
                $this->deletePublicFile($inventory->image_path);
            }

            $path = $request->file('image')->store('inventories', 'public');
            $this->syncPublicFile($path);
            $payload['image_path'] = $path;
        }

        try {
            $inventory->update($payload);
        } catch (QueryException $e) {
            if ((int) ($e->errorInfo[1] ?? 0) === 1062) {
                return back()
                    ->withInput()
                    ->withErrors(['equipment_name' => 'Peralatan sudah ada. Silakan edit data yang sudah ada atau ganti nama.']);
            }

            throw $e;
        }

        return redirect()
            ->route('inventories.index')
            ->with('success', 'Equipment updated.');
    }

    /**
     * Owner dan Staff boleh delete inventory tenant yang sama.
     */
    public function destroy(Inventory $inventory)
    {
        $this->guardInventoryManager();
        $this->guardTenant($inventory);

        if ($inventory->image_path) {
            $this->deletePublicFile($inventory->image_path);
        }

        $inventory->delete();

        return redirect()
            ->route('inventories.index')
            ->with('success', 'Equipment deleted.');
    }

    /**
     * Owner dan Staff boleh import inventory.
     */
    public function importForm()
    {
        $this->guardInventoryManager();

        return view('pages.inventories.import');
    }

    /**
     * Owner dan Staff boleh import inventory.
     */
    public function import(Request $request)
    {
        $this->guardInventoryManager();

        $request->validate([
            'csv' => ['required', 'file', 'mimes:csv,txt', 'max:2048'],
        ]);

        $file = $request->file('csv');
        $rows = array_map('str_getcsv', file($file->getRealPath()));

        if (count($rows) < 2) {
            return back()->withErrors(['csv' => 'CSV kosong atau tidak valid.']);
        }

        $header = array_map(fn ($h) => strtolower(trim((string) $h)), $rows[0]);
        $required = ['equipment_name', 'category', 'quantity', 'price', 'status'];

        foreach ($required as $col) {
            if (!in_array($col, $header, true)) {
                return back()->withErrors([
                    'csv' => 'Header CSV harus mengandung kolom: ' . implode(', ', $required),
                ]);
            }
        }

        $map = array_flip($header);

        $inserted = 0;
        $skipped = 0;
        $tenantId = $this->tenantId();
        $userId = auth()->id();

        foreach (array_slice($rows, 1) as $r) {
            if (count($r) < count($header)) {
                $skipped++;
                continue;
            }

            $equipment = trim($r[$map['equipment_name']] ?? '');
            $category = trim($r[$map['category']] ?? '');
            $quantity = (int) ($r[$map['quantity']] ?? 0);
            $price = (int) ($r[$map['price']] ?? 0);
            $statusRaw = trim($r[$map['status']] ?? 'active');

            if ($equipment === '' || $category === '') {
                $skipped++;
                continue;
            }

            if ($quantity < 0 || $price < 0) {
                $skipped++;
                continue;
            }

            $payload = [
                'tenant_id'      => $tenantId,
                'created_by'     => $userId,
                'equipment_name' => $equipment,
                'category'       => $category,
                'quantity'       => $quantity,
                'price'          => $price,
                'status'         => $this->normalizeStatus($statusRaw),
            ];

            Inventory::updateOrCreate(
                [
                    'tenant_id' => $tenantId,
                    'equipment_name' => $equipment,
                ],
                $payload
            );

            $inserted++;
        }

        return redirect()
            ->route('inventories.index')
            ->with('success', "Import selesai. Insert/Update: {$inserted}, Skipped: {$skipped}");
    }
}