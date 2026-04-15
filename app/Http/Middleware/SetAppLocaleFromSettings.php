<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cache;
use App\Models\Setting;

class SetAppLocaleFromSettings
{
    public function handle(Request $request, Closure $next)
    {
        $locale = Cache::remember('app_locale', 60, function () {
            return Setting::getValue('app_locale', 'id');
        });

        App::setLocale($locale ?: 'id');

        return $next($request);
    }
}