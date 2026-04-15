<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BusinessController extends Controller
{
    public function edit()
    {
        $businessName = Setting::getValue('business_name', 'Event Multimedia DSS');
        $businessLogo = Setting::getValue('business_logo', 'images/logo-kira.png'); // path relatif public/

        return view('pages.settings.business.edit', compact('businessName', 'businessLogo'));
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'business_name' => ['required','string','max:120'],
            'logo' => ['nullable','image','max:2048'], // 2MB
        ]);

        Setting::setValue('business_name', $data['business_name']);

        if ($request->hasFile('logo')) {
            // hapus logo lama kalau sebelumnya dari storage/
            $old = Setting::getValue('business_logo');
            if ($old && str_starts_with($old, 'storage/')) {
                Storage::disk('public')->delete(str_replace('storage/', '', $old));
            }

            $path = $request->file('logo')->store('settings', 'public'); // storage/app/public/settings/...
            Setting::setValue('business_logo', 'storage/'.$path); // supaya asset() bisa baca
        }

        return back()->with('success', 'Business information updated.');
    }
}