<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class LanguageController extends Controller
{
    public function edit()
    {
        $language = Setting::getUserValue('language', 'id');

        return view('pages.settings.language.edit', compact('language'));
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'language' => ['required', 'in:id,en'],
        ]);

        Setting::setUserValue('language', $data['language']);

        return back()->with('success', 'Language updated.');
    }
}