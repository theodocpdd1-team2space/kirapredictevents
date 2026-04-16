<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information (name/email) + profile photo.
     */
    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'job_title' => ['nullable', 'string', 'max:100'],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
        ]);

        $user->fill([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'job_title' => $validated['job_title'] ?? null,
        ]);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        if ($request->hasFile('photo')) {
            // hapus file lama jika ada
            if ($user->profile_photo_path) {
                $this->deletePublicFile($user->profile_photo_path);
            }

            // simpan ke storage/app/public/profile-photos
            $path = $request->file('photo')->store('profile-photos', 'public');

            // sinkron ke public/storage supaya kebaca di shared hosting
            $this->syncPublicFile($path);

            // simpan path relatif ke database
            $user->profile_photo_path = $path;
        }

        $user->save();

        // supaya topbar langsung update
        $user->refresh();
        auth()->setUser($user);

        return back()->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        // delete profile photo file
        if ($user->profile_photo_path) {
            $this->deletePublicFile($user->profile_photo_path);
        }

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
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