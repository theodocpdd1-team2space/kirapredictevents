<?php

namespace App\Http\Controllers;

use App\Models\Estimation;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class EstimationShareController extends Controller
{
    public function show(string $token, Request $request)
    {
        $estimation = Estimation::with(['event', 'details'])
            ->where('share_token', $token)
            ->firstOrFail();

        $hasShortage = $estimation->details->sum('shortage') > 0;

        return view('pages.estimations.show', [
            'estimation'  => $estimation,
            'hasShortage' => $hasShortage,
            'publicMode'  => true,
        ]);
    }

    public function pdf(string $token)
    {
        $estimation = Estimation::with(['event', 'details'])
            ->where('share_token', $token)
            ->firstOrFail();

        $mode = 'summary';

        $pdf = Pdf::loadView('pages.estimations.pdf', compact('estimation', 'mode'))
            ->setPaper('a4', 'portrait');

        return $pdf->download('estimasi-ringkas-' . $estimation->id . '.pdf');
    }

}