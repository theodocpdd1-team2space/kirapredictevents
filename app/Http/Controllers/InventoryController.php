<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\QueryException;

class InventoryController extends Controller
{
    private function guardOwner(Inventory $inventory): void
    {
        abort_unless((int) $inventory->user_id === (int) auth()->id(), 403);
    }

    /**
     * Status DB kamu cuma 3: active, maintenance, inactive
     * Tapi import CSV/legacy bisa kirim: available, used, active, inactive, maintenance
     */
    private function normalizeStatus(?string $status): string
    {
        $s = strtolower(trim((string) $status));

        return match ($s) {
            'maintenance' => 'maintenance',
            'inactive'    => 'inactive',
            // legacy mapping:
            'available', 'used', 'active', '' => 'active',
            default      => 'active',
        };
    }

    /**
     * Category dropdown options (per user + default).
     */
    private function categoryOptions(): array
    {
        $existing = Inventory::query()
            ->where('user_id', auth()->id())
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

        // merge unique, keep order: existing first, then defaults not already included
        $all = $existing;
        foreach ($defaults as $d) {
            if (!in_array($d, $all, true)) $all[] = $d;
        }

        return $all;
    }

    public function index(Request $request)
    {
        $q = Inventory::query()
            ->where('user_id', auth()->id())
            ->latest();

        if ($request->filled('status')) {
            $q->where('status', $this->normalizeStatus($request->status));
        }

        if ($request->filled('category')) {
            $q->where('category', $request->category);
        }

        if ($request->filled('search')) {
            $q->where('equipment_name', 'like', '%'.$request->search.'%');
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
            ->where('user_id', auth()->id())
            ->select('category')->distinct()->orderBy('category')->pluck('category');

        // status untuk filter UI (DB enum)
        $statuses = ['active', 'maintenance', 'inactive'];

        return view('pages.inventories.index', compact('items', 'categories', 'statuses', 'perPage'));
    }

    public function create()
    {
        $categoryOptions = $this->categoryOptions();
        $statusOptions = ['active', 'maintenance', 'inactive'];

        return view('pages.inventories.create', compact('categoryOptions', 'statusOptions'));
    }

    public function store(Request $request)
{
    $data = $request->validate([
        'equipment_name'   => ['required','string','max:120'],

        'category_choice'  => ['required','string','max:80'],
        'category_other'   => ['nullable','string','max:80'],

        'quantity'         => ['required','integer','min:0'],
        'price'            => ['required','numeric','min:0'],
        'status'           => ['required','in:active,maintenance,inactive'],

        'image'            => ['nullable','image','max:2048'],
    ]);

    $categoryFinal = $data['category_choice'] === 'other'
        ? trim((string)($data['category_other'] ?? ''))
        : $data['category_choice'];

    if ($data['category_choice'] === 'other' && $categoryFinal === '') {
        return back()->withErrors(['category_other' => 'Category (Other) wajib diisi.'])->withInput();
    }

    $payload = [
        'user_id'        => auth()->id(),
        'equipment_name' => trim($data['equipment_name']),
        'category'       => $categoryFinal,
        'quantity'       => (int)$data['quantity'],
        'price'          => (int)$data['price'],
        'status'         => $this->normalizeStatus($data['status']),
    ];

    if ($request->hasFile('image')) {
        $payload['image_path'] = $request->file('image')->store('inventories', 'public');
    }

    try {
        Inventory::create($payload);
    } catch (QueryException $e) {
        // 1062 = duplicate key MySQL
        if ((int)($e->errorInfo[1] ?? 0) === 1062) {
            return back()
                ->withInput()
                ->withErrors(['equipment_name' => 'Peralatan sudah ada. Silakan edit data yang sudah ada atau ganti nama.']);
        }
        throw $e; // error lain biar ketahuan
    }

    return redirect()->route('inventories.index')->with('success', 'Equipment added.');
}

    public function edit(Inventory $inventory)
    {
        $this->guardOwner($inventory);

        $categoryOptions = $this->categoryOptions();
        $statusOptions = ['active', 'maintenance', 'inactive'];

        return view('pages.inventories.edit', compact('inventory', 'categoryOptions', 'statusOptions'));
    }

    public function update(Request $request, Inventory $inventory)
{
    $this->guardOwner($inventory);

    $data = $request->validate([
        'equipment_name'   => ['required','string','max:120'],

        'category_choice'  => ['required','string','max:80'],
        'category_other'   => ['nullable','string','max:80'],

        'quantity'         => ['required','integer','min:0'],
        'price'            => ['required','numeric','min:0'],
        'status'           => ['required','in:active,maintenance,inactive'],

        'image'            => ['nullable','image','max:2048'],
    ]);

    $categoryFinal = $data['category_choice'] === 'other'
        ? trim((string)($data['category_other'] ?? ''))
        : $data['category_choice'];

    if ($data['category_choice'] === 'other' && $categoryFinal === '') {
        return back()->withErrors(['category_other' => 'Category (Other) wajib diisi.'])->withInput();
    }

    $payload = [
        'equipment_name' => trim($data['equipment_name']),
        'category'       => $categoryFinal,
        'quantity'       => (int)$data['quantity'],
        'price'          => (int)$data['price'],
        'status'         => $this->normalizeStatus($data['status']),
    ];

    if ($request->hasFile('image')) {
        if ($inventory->image_path) {
            Storage::disk('public')->delete($inventory->image_path);
        }
        $payload['image_path'] = $request->file('image')->store('inventories', 'public');
    }

    try {
        $inventory->update($payload);
    } catch (QueryException $e) {
        if ((int)($e->errorInfo[1] ?? 0) === 1062) {
            return back()
                ->withInput()
                ->withErrors(['equipment_name' => 'Peralatan sudah ada. Silakan edit data yang sudah ada atau ganti nama.']);
        }
        throw $e;
    }

    return redirect()->route('inventories.index')->with('success', 'Equipment updated.');
}
    
    public function destroy(Inventory $inventory)
    {
        $this->guardOwner($inventory);

        if ($inventory->image_path) {
            Storage::disk('public')->delete($inventory->image_path);
        }

        $inventory->delete();

        return redirect()->route('inventories.index')->with('success', 'Equipment deleted.');
    }

    public function importForm()
    {
        return view('pages.inventories.import');
    }

    public function import(Request $request)
    {
        $request->validate([
            'csv' => ['required','file','mimes:csv,txt','max:2048'],
        ]);

        $file = $request->file('csv');
        $rows = array_map('str_getcsv', file($file->getRealPath()));

        if (count($rows) < 2) {
            return back()->withErrors(['csv' => 'CSV kosong atau tidak valid.']);
        }

        // header wajib: equipment_name,category,quantity,price,status
        $header = array_map(fn($h) => strtolower(trim($h)), $rows[0]);
        $required = ['equipment_name','category','quantity','price','status'];

        foreach ($required as $col) {
            if (!in_array($col, $header, true)) {
                return back()->withErrors(['csv' => "Header CSV harus mengandung kolom: ".implode(', ', $required)]);
            }
        }

        $map = array_flip($header);

        $inserted = 0;
        $skipped  = 0;

        foreach (array_slice($rows, 1) as $r) {
            if (count($r) < count($header)) { $skipped++; continue; }

            $equipment = trim($r[$map['equipment_name']] ?? '');
            $category  = trim($r[$map['category']] ?? '');
            $quantity  = (int)($r[$map['quantity']] ?? 0);
            $price     = (int)($r[$map['price']] ?? 0);
            $statusRaw = trim($r[$map['status']] ?? 'active');

            if ($equipment === '' || $category === '') { $skipped++; continue; }
            if ($quantity < 0 || $price < 0) { $skipped++; continue; }

            $payload = [
                'user_id'        => auth()->id(),
                'equipment_name' => $equipment,
                'category'       => $category,
                'quantity'       => $quantity,
                'price'          => $price,
                'status'         => $this->normalizeStatus($statusRaw),
            ];

            // upsert per user + equipment_name
            Inventory::updateOrCreate(
                ['user_id' => auth()->id(), 'equipment_name' => $equipment],
                $payload
            );

            $inserted++;
        }

        return redirect()
            ->route('inventories.index')
            ->with('success', "Import selesai. Insert/Update: {$inserted}, Skipped: {$skipped}");
    }
}