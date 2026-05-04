<?php

namespace App\Http\Controllers;

use App\Models\Estimation;
use App\Models\EstimationDetail;
use App\Models\Inventory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    private function tenantId(): int
    {
        $tenantId = auth()->user()?->tenant_id;

        abort_unless($tenantId, 403, 'User belum memiliki tenant.');

        return (int) $tenantId;
    }

    private function moneyShort(int|float $value): string
    {
        $value = (float) $value;

        if ($value >= 1000000000) {
            return 'Rp ' . number_format($value / 1000000000, 1, ',', '.') . 'M';
        }

        if ($value >= 1000000) {
            return 'Rp ' . number_format($value / 1000000, 1, ',', '.') . 'jt';
        }

        if ($value >= 1000) {
            return 'Rp ' . number_format($value / 1000, 0, ',', '.') . 'rb';
        }

        return 'Rp ' . number_format($value, 0, ',', '.');
    }

    public function index(Request $request)
    {
        $tenantId = $this->tenantId();

        $baseEstimations = Estimation::query()
            ->where('tenant_id', $tenantId);

        $totalEstimations = (clone $baseEstimations)->count();

        $pendingEstimations = (clone $baseEstimations)
            ->where('status', 'pending')
            ->count();

        $approvedEstimations = (clone $baseEstimations)
            ->where('status', 'approved')
            ->count();

        $revisedEstimations = (clone $baseEstimations)
            ->where('status', 'revised')
            ->count();

        $rejectedEstimations = (clone $baseEstimations)
            ->where('status', 'rejected')
            ->count();

        $totalValue = (int) (clone $baseEstimations)
            ->sum('total_cost');

        $approvedValue = (int) (clone $baseEstimations)
            ->where('status', 'approved')
            ->sum('total_cost');

        $pendingValue = (int) (clone $baseEstimations)
            ->where('status', 'pending')
            ->sum('total_cost');

        $revisedValue = (int) (clone $baseEstimations)
            ->where('status', 'revised')
            ->sum('total_cost');

        $avgEstimationValue = $totalEstimations > 0
            ? (int) round($totalValue / $totalEstimations)
            : 0;

        $monthStart = now()->startOfMonth();

        $monthlyEstimations = (clone $baseEstimations)
            ->where('created_at', '>=', $monthStart)
            ->count();

        $monthlyValue = (int) (clone $baseEstimations)
            ->where('created_at', '>=', $monthStart)
            ->sum('total_cost');

        $recentEstimations = (clone $baseEstimations)
            ->with(['event', 'creator'])
            ->latest()
            ->take(8)
            ->get();

        $inventoryItems = Inventory::query()
            ->where('tenant_id', $tenantId)
            ->count();

        $inventoryUnits = (int) Inventory::query()
            ->where('tenant_id', $tenantId)
            ->sum('quantity');

        $lowStockItems = Inventory::query()
            ->where('tenant_id', $tenantId)
            ->where('quantity', '<=', 1)
            ->count();

        $maintenanceItems = Inventory::query()
            ->where('tenant_id', $tenantId)
            ->where('status', 'maintenance')
            ->count();

        $inactiveItems = Inventory::query()
            ->where('tenant_id', $tenantId)
            ->where('status', 'inactive')
            ->count();

        $missingPriceItems = Inventory::query()
            ->where('tenant_id', $tenantId)
            ->where(function ($q) {
                $q->whereNull('price')
                    ->orWhere('price', '<=', 0);
            })
            ->count();

        $verifiedCount = (clone $baseEstimations)
            ->where('status', 'approved')
            ->count();

        $accurateCount = (clone $baseEstimations)
            ->where('status', 'approved')
            ->where('accuracy', 'accurate')
            ->count();

        $accuracy = $verifiedCount > 0
            ? (int) round(($accurateCount / $verifiedCount) * 100)
            : 0;

        $unevaluatedApproved = (clone $baseEstimations)
            ->where('status', 'approved')
            ->whereNull('accuracy')
            ->count();

        $estimationIds = (clone $baseEstimations)->pluck('id');

        $shortageEstimations = EstimationDetail::query()
            ->whereIn('estimation_id', $estimationIds)
            ->where('shortage', '>', 0)
            ->distinct('estimation_id')
            ->count('estimation_id');

        $topEquipments = EstimationDetail::query()
            ->select('equipment_name', DB::raw('SUM(quantity) as total_qty'), DB::raw('COUNT(*) as used_count'))
            ->whereIn('estimation_id', $estimationIds)
            ->where(function ($q) {
                $q->where('is_removed', false)
                    ->orWhereNull('is_removed');
            })
            ->groupBy('equipment_name')
            ->orderByDesc('used_count')
            ->orderByDesc('total_qty')
            ->take(5)
            ->get();

        $statusTotal = max(1, $totalEstimations);

        $statusBreakdown = [
            'pending' => [
                'label' => 'Pending',
                'count' => $pendingEstimations,
                'percent' => (int) round(($pendingEstimations / $statusTotal) * 100),
                'class' => 'bg-orange-500',
            ],
            'approved' => [
                'label' => 'Approved',
                'count' => $approvedEstimations,
                'percent' => (int) round(($approvedEstimations / $statusTotal) * 100),
                'class' => 'bg-green-500',
            ],
            'revised' => [
                'label' => 'Revised',
                'count' => $revisedEstimations,
                'percent' => (int) round(($revisedEstimations / $statusTotal) * 100),
                'class' => 'bg-blue-500',
            ],
            'rejected' => [
                'label' => 'Rejected',
                'count' => $rejectedEstimations,
                'percent' => (int) round(($rejectedEstimations / $statusTotal) * 100),
                'class' => 'bg-red-500',
            ],
        ];

        return view('pages.dashboard', [
            'totalEstimations' => $totalEstimations,
            'pendingEstimations' => $pendingEstimations,
            'approvedEstimations' => $approvedEstimations,
            'revisedEstimations' => $revisedEstimations,
            'rejectedEstimations' => $rejectedEstimations,

            'totalValue' => $totalValue,
            'approvedValue' => $approvedValue,
            'pendingValue' => $pendingValue,
            'revisedValue' => $revisedValue,
            'avgEstimationValue' => $avgEstimationValue,
            'monthlyEstimations' => $monthlyEstimations,
            'monthlyValue' => $monthlyValue,

            'inventoryItems' => $inventoryItems,
            'inventoryUnits' => $inventoryUnits,
            'lowStockItems' => $lowStockItems,
            'maintenanceItems' => $maintenanceItems,
            'inactiveItems' => $inactiveItems,
            'missingPriceItems' => $missingPriceItems,

            'accuracy' => $accuracy,
            'unevaluatedApproved' => $unevaluatedApproved,
            'shortageEstimations' => $shortageEstimations,

            'recentEstimations' => $recentEstimations,
            'topEquipments' => $topEquipments,
            'statusBreakdown' => $statusBreakdown,

            'totalValueShort' => $this->moneyShort($totalValue),
            'approvedValueShort' => $this->moneyShort($approvedValue),
            'pendingValueShort' => $this->moneyShort($pendingValue),
            'monthlyValueShort' => $this->moneyShort($monthlyValue),
            'avgEstimationValueShort' => $this->moneyShort($avgEstimationValue),
        ]);
    }
}