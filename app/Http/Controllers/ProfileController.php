<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
        'name' => ['required','string','max:255'],
        'email' => ['required','string','lowercase','email','max:255','unique:users,email,'.$user->id],
        'job_title' => ['nullable','string','max:100'],
        'photo' => ['nullable','image','mimes:jpg,jpeg,png','max:2048'],
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
        $path = $request->file('photo')->store('profile-photos', 'public');
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

        // delete profile photo file (optional)
        if ($user->profile_photo_path) {
            Storage::disk('public')->delete($user->profile_photo_path);
        }

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}