<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
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
            'business_name' => ['required', 'string', 'max:120'],
            'logo' => ['nullable', 'image', 'max:2048'],
        ]);

        Setting::setValue('business_name', $data['business_name']);

        if ($request->hasFile('logo')) {
            // hapus logo lama kalau sebelumnya dari storage/
            $old = Setting::getValue('business_logo');

            if ($old && str_starts_with($old, 'storage/')) {
                $oldRelativePath = str_replace('storage/', '', $old);
                $this->deletePublicFile($oldRelativePath);
            }

            // simpan ke storage/app/public/settings
            $path = $request->file('logo')->store('settings', 'public');

            // sinkron ke public/storage supaya kebaca di shared hosting
            $this->syncPublicFile($path);

            // simpan path untuk dipanggil dengan asset()
            Setting::setValue('business_logo', 'storage/' . $path);
        }

        return back()->with('success', 'Business information updated.');
    }

    /**
     * Copy a file from storage/app/public to public/storage.
     */
    protected function syncPublicFile(string $relativePath): void
    {
        $source = storage_path('app/public/' . $relativePath);
        $target = public_path('storage/' . $relativePath);
        $targetDir = dirname($target);

        if (! File::exists($targetDir)) {
            File::makeDirectory($targetDir, 0755, true);
        }

        if (File::exists($source)) {
            File::copy($source, $target);
        }
    }

    /**
     * Delete file from both storage/app/public and public/storage.
     */
    protected function deletePublicFile(?string $relativePath): void
    {
        if (! $relativePath) {
            return;
        }

        Storage::disk('public')->delete($relativePath);

        $publicFile = public_path('storage/' . $relativePath);
        if (File::exists($publicFile)) {
            File::delete($publicFile);
        }
    }
}