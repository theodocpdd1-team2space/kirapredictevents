<?php

namespace App\Http\Middleware;

use App\Models\Setting;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;

class SetLocaleFromSettings
{
    public function handle(Request $request, Closure $next)
    {
        $raw = (string) Setting::getValue('language', 'id');
        $lang = strtolower(trim($raw));

        $locale = match ($lang) {
            'id', 'in', 'indo', 'indonesia' => 'id',
            'en', 'english'                => 'en',
            default                         => 'id',
        };

        Log::info('Locale middleware hit', [
            'raw' => $raw,
            'lang' => $lang,
            'locale' => $locale,
            'path' => $request->path(),
        ]);

        App::setLocale($locale);

        return $next($request);
    }
}