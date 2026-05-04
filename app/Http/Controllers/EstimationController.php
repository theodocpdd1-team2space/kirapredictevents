<?php

namespace App\Http\Controllers;

use App\Models\Estimation;
use App\Models\EstimationDetail;
use App\Models\Inventory;
use App\Models\Setting;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class EstimationController extends Controller
{
    private function tenantId(): int
    {
        $tenantId = auth()->user()?->tenant_id;

        abort_unless($tenantId, 403, 'User belum memiliki tenant.');

        return (int) $tenantId;
    }

    private function guardTenant(Estimation $estimation): void
    {
        abort_unless((int) $estimation->tenant_id === $this->tenantId(), 403);
    }

    private function guardOwnerRole(): void
    {
        abort_unless(auth()->user()?->isOwner(), 403, 'Akses hanya untuk Owner.');
    }

    private function durationBlockFromHoursPerDay(int $hoursPerDay): int
    {
        $h = max(1, $hoursPerDay);

        return $h <= 4 ? 1 : ($h <= 8 ? 2 : 3);
    }

    public function index(Request $request)
    {
        $tenantId = $this->tenantId();

        $q = $request->get('search');
        $status = $request->get('status', 'all');

        $items = Estimation::with(['event', 'creator'])
            ->where('tenant_id', $tenantId)
            ->when($status !== 'all', fn ($qr) => $qr->where('status', $status))
            ->when($q, function ($qr) use ($q) {
                $qr->whereHas('event', function ($ev) use ($q) {
                    $ev->where('event_type', 'like', "%{$q}%")
                        ->orWhere('event_name', 'like', "%{$q}%")
                        ->orWhere('client_name', 'like', "%{$q}%");
                });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('pages.estimations.index', compact('items', 'q', 'status'));
    }

    public function show(Estimation $estimation)
    {
        $this->guardTenant($estimation);

        $estimation->load([
            'event',
            'creator',
            'details' => fn ($q) => $q->orderBy('id', 'asc'),
        ]);

        $viewMode = request('view', 'final');

        $detailQuery = $estimation->details;

        if ($viewMode !== 'original') {
            $detailQuery = $detailQuery->where('is_removed', false);
        }

        $hasShortage = $detailQuery->sum('shortage') > 0;

        $traceArr = [];

        if (is_array($estimation->trace_json ?? null)) {
            $traceArr = $estimation->trace_json;
        } elseif (is_string($estimation->trace_json ?? null)) {
            $traceArr = json_decode($estimation->trace_json, true) ?: [];
        }

        return view('pages.estimations.show', compact('estimation', 'hasShortage', 'traceArr'));
    }

    public function bulkDelete(Request $request)
    {
        $this->guardOwnerRole();

        $tenantId = $this->tenantId();

        $data = $request->validate([
            'ids'   => ['required', 'array', 'min:1'],
            'ids.*' => ['integer'],
        ]);

        $ids = $data['ids'];

        DB::transaction(function () use ($ids, $tenantId) {
            $ownedIds = Estimation::where('tenant_id', $tenantId)
                ->whereIn('id', $ids)
                ->pluck('id')
                ->all();

            if (count($ownedIds) === 0) {
                return;
            }

            EstimationDetail::whereIn('estimation_id', $ownedIds)->delete();
            Estimation::whereIn('id', $ownedIds)->delete();
        });

        return back()->with('success', count($ids) . ' estimasi diproses untuk dihapus.');
    }

    public function updateStatus(Request $request, Estimation $estimation)
    {
        $this->guardTenant($estimation);
        $this->guardOwnerRole();

        $data = $request->validate([
            'status' => ['required', 'in:pending,approved,rejected,revised'],
        ]);

        $estimation->update([
            'status' => $data['status'],
        ]);

        return back()->with('success', 'Status updated.');
    }

    public function updateAccuracy(Request $request, Estimation $estimation)
    {
        $this->guardTenant($estimation);
        $this->guardOwnerRole();

        $data = $request->validate([
            'accuracy' => ['nullable', 'in:accurate,underestimated,overestimated'],
        ]);

        $estimation->update([
            'accuracy' => $data['accuracy'] ?? null,
        ]);

        return back()->with('success', 'Accuracy updated.');
    }

    public function pdf(Estimation $estimation)
    {
        $this->guardTenant($estimation);

        $estimation->load([
            'event',
            'details' => fn ($q) => $q->where('is_removed', false)->orderBy('id', 'asc'),
        ]);

        $mode = request('mode', 'detail');
        $mode = in_array($mode, ['detail', 'summary'], true) ? $mode : 'detail';

        $bizName    = Setting::getValue('business_name', 'Kira');
        $bizTagline = Setting::getValue('business_tagline', 'Event Multimedia DSS');
        $bizLogo    = Setting::getValue('business_logo', 'images/logo-kira.png');

        $eventName = $estimation->event->event_name
            ?? $estimation->event->event_type
            ?? 'Estimation';

        $filename = Str::slug($eventName) . "-estimation-{$mode}.pdf";

        $breakdown = $estimation->breakdown;

        if (is_string($breakdown)) {
            $breakdown = json_decode($breakdown, true) ?: [];
        }

        if (!is_array($breakdown)) {
            $breakdown = [];
        }

        $pdf = Pdf::loadView('pages.estimations.pdf', [
            'estimation' => $estimation,
            'bizName'    => $bizName,
            'bizTagline' => $bizTagline,
            'bizLogo'    => $bizLogo,
            'eventName'  => $eventName,
            'mode'       => $mode,
            'breakdown'  => $breakdown,
        ])->setPaper('a4', 'portrait');

        return $pdf->download($filename);
    }

    public function wa(Estimation $estimation)
    {
        $this->guardTenant($estimation);

        $estimation->load(['event']);

        $clientName = (string) ($estimation->event->client_name ?? '');
        $waRaw      = (string) ($estimation->event->client_whatsapp ?? '');
        $eventName  = (string) ($estimation->event->event_name ?? ($estimation->event->event_type ?? 'Event'));
        $total      = (int) ($estimation->total_cost ?? 0);

        $wa = preg_replace('/\D+/', '', $waRaw ?? '');

        if ($wa === '') {
            return back()->withErrors([
                'client_whatsapp' => 'Nomor WhatsApp client belum ada di event.',
            ]);
        }

        if (str_starts_with($wa, '0')) {
            $wa = '62' . substr($wa, 1);
        }

        if (str_starts_with($wa, '8')) {
            $wa = '62' . $wa;
        }

        if (empty($estimation->share_token)) {
            $estimation->update([
                'share_token' => $this->makeUniqueShareToken(),
            ]);

            $estimation->refresh();
        }

        $shareLink = route('share.estimations.show', $estimation->share_token);

        $lines = [];
        $lines[] = "Halo " . ($clientName !== '' ? $clientName : 'Bapak/Ibu') . ",";
        $lines[] = "";
        $lines[] = "Berikut estimasi biaya untuk:";
        $lines[] = "Event: {$eventName}";
        $lines[] = "Total: Rp " . number_format($total, 0, ',', '.');
        $lines[] = "";
        $lines[] = "Detail estimasi: {$shareLink}";
        $lines[] = "";
        $lines[] = "Terima kasih.";

        return redirect()->away("https://wa.me/{$wa}?text=" . urlencode(implode("\n", $lines)));
    }

    public function edit(Estimation $estimation)
    {
        $this->guardTenant($estimation);

        $estimation->load([
            'event',
            'details' => fn ($q) => $q->orderBy('id', 'asc'),
        ]);

        $inventories = Inventory::where('tenant_id', $this->tenantId())
            ->orderBy('equipment_name')
            ->get();

        return view('pages.estimations.edit', compact('estimation', 'inventories'));
    }

    public function update(Request $request, Estimation $estimation)
    {
        $this->guardTenant($estimation);

        $estimation->load([
            'event',
            'details' => fn ($q) => $q->orderBy('id', 'asc'),
        ]);

        $data = $request->validate([
            'revision_note' => ['nullable', 'string', 'max:1000'],

            'items' => ['nullable', 'array'],
            'items.*.id' => ['required', 'integer'],
            'items.*.equipment_name' => ['nullable', 'string', 'max:255'],
            'items.*.quantity' => ['required', 'integer', 'min:0'],
            'items.*.price' => ['nullable', 'integer', 'min:0'],
            'items.*.unit' => ['nullable', 'string', 'max:20'],
            'items.*.notes' => ['nullable', 'string', 'max:500'],

            'new_items' => ['nullable', 'array'],
            'new_items.*.equipment_name' => ['required', 'string', 'max:255'],
            'new_items.*.quantity' => ['required', 'integer', 'min:1'],
            'new_items.*.price' => ['nullable', 'integer', 'min:0'],
            'new_items.*.unit' => ['nullable', 'string', 'max:20'],
            'new_items.*.notes' => ['nullable', 'string', 'max:500'],

            'custom_items' => ['nullable', 'array'],
            'custom_items.*.name' => ['required', 'string', 'max:255'],
            'custom_items.*.unit' => ['nullable', 'string', 'max:20'],
            'custom_items.*.quantity' => ['required', 'integer', 'min:1'],
            'custom_items.*.price' => ['required', 'integer', 'min:0'],
            'custom_items.*.notes' => ['nullable', 'string', 'max:500'],
        ]);

        $tenantId = $this->tenantId();
        $userId = auth()->id();

        return DB::transaction(function () use ($data, $estimation, $tenantId, $userId) {
            $eventDays = max(1, (int) ($estimation->event->event_days ?? 1));
            $hoursPerDay = max(1, (int) ($estimation->event->hours_per_day ?? 1));
            $durationBlock = $this->durationBlockFromHoursPerDay($hoursPerDay);

            $mult = $eventDays * $durationBlock;

            $byId = $estimation->details->keyBy('id');

            foreach (($data['items'] ?? []) as $row) {
                $detail = $byId->get((int) ($row['id'] ?? 0));

                if (!$detail) {
                    continue;
                }

                $qty = (int) ($row['quantity'] ?? 0);

                if ($qty === 0) {
                    $detail->update([
                        'quantity'   => 0,
                        'total'      => 0,
                        'shortage'   => 0,
                        'is_removed' => true,
                        'removed_at' => now(),
                        'removed_by' => $userId,
                        'notes'      => $row['notes'] ?? ($detail->notes ?? null),
                    ]);

                    continue;
                }

                $unitPrice = array_key_exists('price', $row) && $row['price'] !== null
                    ? (int) $row['price']
                    : (int) $detail->price;

                $newName = trim((string) ($row['equipment_name'] ?? $detail->equipment_name));

                if ($newName === '') {
                    $newName = $detail->equipment_name;
                }

                $inv = Inventory::where('tenant_id', $tenantId)
                    ->where('equipment_name', $newName)
                    ->first();

                $available = $inv ? (int) $inv->quantity : (int) ($detail->available ?? 0);
                $shortage = max(0, $qty - $available);

                $isCustom = $inv ? 0 : (int) ($detail->is_custom ? 1 : 0);

                $detail->update([
                    'equipment_name' => $newName,
                    'is_custom'      => $isCustom,
                    'available'      => $available,
                    'shortage'       => $shortage,
                    'quantity'       => $qty,
                    'price'          => $unitPrice,
                    'unit'           => $row['unit'] ?? ($detail->unit ?? null),
                    'notes'          => $row['notes'] ?? ($detail->notes ?? null),
                    'total'          => $unitPrice * $qty * $mult,

                    // kalau item sebelumnya pernah dihapus, lalu qty diisi lagi, aktifkan kembali
                    'is_removed'     => false,
                    'removed_at'     => null,
                    'removed_by'     => null,
                ]);
            }

            foreach (($data['new_items'] ?? []) as $row) {
                $name = trim((string) ($row['equipment_name'] ?? ''));

                if ($name === '') {
                    continue;
                }

                $inv = Inventory::where('tenant_id', $tenantId)
                    ->where('equipment_name', $name)
                    ->first();

                if (!$inv) {
                    continue;
                }

                $qty = (int) ($row['quantity'] ?? 0);

                if ($qty <= 0) {
                    continue;
                }

                $unitPrice = isset($row['price']) && $row['price'] !== null
                    ? (int) $row['price']
                    : (int) $inv->price;

                $lineTotal = $unitPrice * $qty * $mult;

                EstimationDetail::create([
                    'estimation_id'  => $estimation->id,
                    'equipment_name' => $inv->equipment_name,
                    'unit'           => $row['unit'] ?? null,
                    'notes'          => $row['notes'] ?? null,
                    'is_custom'      => 0,

                    'quantity'       => $qty,
                    'price'          => $unitPrice,
                    'total'          => $lineTotal,

                    // item baru tidak ada di original
                    'original_quantity' => 0,
                    'original_price'    => $unitPrice,
                    'original_total'    => 0,

                    'available'      => (int) $inv->quantity,
                    'shortage'       => max(0, $qty - (int) $inv->quantity),

                    'is_removed'     => false,
                    'removed_at'     => null,
                    'removed_by'     => null,
                ]);
            }

            foreach (($data['custom_items'] ?? []) as $row) {
                $name = trim((string) ($row['name'] ?? ''));

                if ($name === '') {
                    continue;
                }

                $qty = (int) ($row['quantity'] ?? 0);

                if ($qty <= 0) {
                    continue;
                }

                $unitPrice = (int) ($row['price'] ?? 0);
                $lineTotal = $unitPrice * $qty * $mult;

                EstimationDetail::create([
                    'estimation_id'  => $estimation->id,
                    'equipment_name' => $name,
                    'unit'           => $row['unit'] ?? 'pcs',
                    'notes'          => $row['notes'] ?? null,
                    'is_custom'      => 1,

                    'quantity'       => $qty,
                    'price'          => $unitPrice,
                    'total'          => $lineTotal,

                    // item custom baru tidak ada di original
                    'original_quantity' => 0,
                    'original_price'    => $unitPrice,
                    'original_total'    => 0,

                    'available'      => 0,
                    'shortage'       => 0,

                    'is_removed'     => false,
                    'removed_at'     => null,
                    'removed_by'     => null,
                ]);
            }

            $estimation->refresh();

            $equipmentCost = (int) $estimation->details()
                ->where('is_removed', false)
                ->sum('total');

            $b = $estimation->breakdown ?? [];

            if (is_string($b)) {
                $b = json_decode($b, true) ?: [];
            }

            if (!is_array($b)) {
                $b = [];
            }

            $labor = (int) ($b['labor'] ?? 0);
            $transport = (int) ($b['transport'] ?? 0);

            $opPercent = (float) Setting::getValue('operational_percent', 5, $tenantId);
            $operational = (int) round($equipmentCost * ($opPercent / 100));

            $markupPercent = (float) Setting::getValue('markup_percent', 0, $tenantId);

            $subTotal = $equipmentCost + $labor + $transport + $operational;
            $markup = (int) round($subTotal * ($markupPercent / 100));
            $total = $subTotal + $markup;

            $b['equipment'] = $equipmentCost;
            $b['operational'] = $operational;
            $b['markup'] = $markup;
            $b['total'] = $total;
            $b['event_days'] = $eventDays;
            $b['hours_per_day'] = $hoursPerDay;
            $b['duration_block'] = $durationBlock;
            $b['equipment_days'] = $eventDays;

            $estimation->update([
                'breakdown'     => $b,
                'total_cost'    => $total,
                'status'        => 'revised',
                'is_revised'    => 1,
                'revision_note' => $data['revision_note'] ?? null,
            ]);

            return redirect()
                ->route('estimations.show', $estimation->id)
                ->with('success', 'Estimation revised.');
        });
    }

    private function makeUniqueShareToken(): string
    {
        do {
            $token = Str::random(48);
        } while (Estimation::where('share_token', $token)->exists());

        return $token;
    }

    public function ensureShareToken(Request $request, Estimation $estimation)
    {
        $this->guardTenant($estimation);

        if (empty($estimation->share_token)) {
            $estimation->update([
                'share_token' => $this->makeUniqueShareToken(),
            ]);

            $estimation->refresh();
        }

        $shareUrl = route('share.estimations.show', $estimation->share_token);

        return back()->with('success', 'Share link ready: ' . $shareUrl);
    }

    public function publicShow(string $token)
    {
        $estimation = Estimation::with([
            'event',
            'details' => fn ($q) => $q->orderBy('id', 'asc'),
        ])
            ->where('share_token', $token)
            ->firstOrFail();

        $viewMode = request('view', 'final');

        $detailQuery = $estimation->details;

        if ($viewMode !== 'original') {
            $detailQuery = $detailQuery->where('is_removed', false);
        }

        $hasShortage = $detailQuery->sum('shortage') > 0;

        return view('pages.estimations.show', [
            'estimation'  => $estimation,
            'hasShortage' => $hasShortage,
            'publicMode'  => true,
            'traceArr'    => [],
        ]);
    }
}