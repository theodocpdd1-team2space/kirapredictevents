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
        $businessLogo = Setting::getValue('business_logo', 'images/logo-kira.png');

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
            $old = Setting::getValue('business_logo');

            if ($old && str_starts_with($old, 'storage/')) {
                $oldRelativePath = str_replace('storage/', '', $old);
                $this->deletePublicFile($oldRelativePath);
            }

            $path = $request->file('logo')->store('settings', 'public');

            $this->syncPublicFile($path);

            Setting::setValue('business_logo', 'storage/' . $path);
        }

        return back()->with('success', 'Business information updated.');
    }

    /**
     * Root folder public_html/storage di shared hosting.
     */
    protected function sharedPublicStorageRoot(): string
    {
        return dirname(base_path(), 2) . '/public_html/storage';
    }

    /**
     * Copy a file from storage/app/public to public_html/storage.
     */
    protected function syncPublicFile(string $relativePath): void
    {
        $source = storage_path('app/public/' . $relativePath);

        $publicRoot = $this->sharedPublicStorageRoot();
        $target = $publicRoot . '/' . $relativePath;
        $targetDir = dirname($target);

        if (! File::exists($targetDir)) {
            File::makeDirectory($targetDir, 0755, true);
        }

        if (File::exists($source)) {
            File::copy($source, $target);
        }
    }

    /**
     * Delete file from both storage/app/public and public_html/storage.
     */
    protected function deletePublicFile(?string $relativePath): void
    {
        if (! $relativePath) {
            return;
        }

        Storage::disk('public')->delete($relativePath);

        $publicFile = $this->sharedPublicStorageRoot() . '/' . $relativePath;
        if (File::exists($publicFile)) {
            File::delete($publicFile);
        }
    }
}