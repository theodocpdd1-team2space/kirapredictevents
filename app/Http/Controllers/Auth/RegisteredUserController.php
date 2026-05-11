<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * Signup digunakan untuk membuat tenant/workspace baru.
     * User yang melakukan signup otomatis menjadi Owner tenant tersebut.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        return DB::transaction(function () use ($validated) {
            $tenantName = trim($validated['name']) . ' Workspace';

            $tenant = Tenant::create([
                'name'   => $tenantName,
                'slug'   => $this->uniqueTenantSlug($tenantName),
                'status' => 'active',
            ]);

            $user = User::create([
                'tenant_id' => $tenant->id,
                'name'      => $validated['name'],
                'email'     => $validated['email'],
                'password'  => Hash::make($validated['password']),
                'role'      => 'owner',
                'status'    => 'active',
            ]);

            event(new Registered($user));

            Auth::login($user);

            return redirect()->route('dashboard');
        });
    }

    private function uniqueTenantSlug(string $name): string
    {
        $base = Str::slug($name);

        if ($base === '') {
            $base = 'tenant';
        }

        $slug = $base;
        $i = 1;

        while (Tenant::where('slug', $slug)->exists()) {
            $slug = $base . '-' . $i;
            $i++;
        }

        return $slug;
    }
}