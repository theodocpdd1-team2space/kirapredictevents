<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class ThemeController extends Controller
{
    public function edit()
    {
        $themeMode = Setting::getUserValue('theme_mode', 'light');

        return view('pages.settings.theme.edit', compact('themeMode'));
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'theme_mode' => ['required', 'in:light,dark,system'],
        ]);

        Setting::setUserValue('theme_mode', $data['theme_mode']);

        return back()->with('success', 'Theme updated.');
    }
}