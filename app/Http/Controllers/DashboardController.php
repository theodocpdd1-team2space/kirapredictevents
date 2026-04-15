<?php

namespace App\Http\Controllers;

use App\Models\Estimation;
use App\Models\Inventory;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $userId = auth()->id();

        // estimations scoped by created_by
        $baseEstimations = Estimation::query()
            ->where('created_by', $userId);

        $totalEstimations    = (clone $baseEstimations)->count();
        $pendingEstimations  = (clone $baseEstimations)->where('status', 'pending')->count();
        $approvedEstimations = (clone $baseEstimations)->where('status', 'approved')->count();

        // recent
        $recentEstimations = (clone $baseEstimations)
            ->with('event')
            ->latest()
            ->take(8)
            ->get();

        // inventory scoped by user_id
        $inventoryItems = Inventory::query()
            ->where('user_id', $userId)
            ->count();

        $inventoryUnits = (int) Inventory::query()
            ->where('user_id', $userId)
            ->sum('quantity');

        // accuracy = accurate evaluations / approved estimations
        $verifiedCount = (clone $baseEstimations)->where('status', 'approved')->count();
        $accurateCount = (clone $baseEstimations)
            ->where('status', 'approved')
            ->where('accuracy', 'accurate')
            ->count();

        $accuracy = $verifiedCount > 0
            ? (int) round(($accurateCount / $verifiedCount) * 100)
            : 0;

        return view('pages.dashboard', compact(
            'totalEstimations',
            'pendingEstimations',
            'approvedEstimations',
            'inventoryItems',
            'inventoryUnits',
            'accuracy',
            'recentEstimations'
        ));
    }
}