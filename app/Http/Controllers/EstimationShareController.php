<?php

namespace App\Http\Controllers;

use App\Models\Estimation;
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
}