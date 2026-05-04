<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class StaffController extends Controller
{
    private function guardOwner(): void
    {
        abort_unless(auth()->user()?->isOwner(), 403, 'Akses hanya untuk owner.');
        abort_unless(auth()->user()?->tenant_id, 403, 'Akun belum terhubung ke tenant.');
    }

    private function tenantId(): int
    {
        return (int) auth()->user()->tenant_id;
    }

    private function guardSameTenant(User $user): void
    {
        abort_unless((int) $user->tenant_id === $this->tenantId(), 403, 'User bukan bagian dari tenant ini.');
    }

    public function index(Request $request)
    {
        $this->guardOwner();

        $search = trim((string) $request->get('search', ''));
        $role = $request->get('role', 'all');
        $status = $request->get('status', 'all');

        $users = User::query()
            ->where('tenant_id', $this->tenantId())
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($qq) use ($search) {
                    $qq->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('job_title', 'like', "%{$search}%");
                });
            })
            ->when($role !== 'all', fn ($q) => $q->where('role', $role))
            ->when($status !== 'all', fn ($q) => $q->where('status', $status))
            ->orderByRaw("FIELD(role, 'owner', 'staff')")
            ->orderBy('name')
            ->paginate(12)
            ->withQueryString();

        return view('pages.settings.staff.index', compact('users', 'search', 'role', 'status'));
    }

    public function create()
    {
        $this->guardOwner();

        return view('pages.settings.staff.create');
    }

    public function store(Request $request)
    {
        $this->guardOwner();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:150', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role' => ['required', Rule::in(['owner', 'staff'])],
            'job_title' => ['nullable', 'string', 'max:120'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ]);

        User::create([
            'tenant_id' => $this->tenantId(),
            'name' => trim($data['name']),
            'email' => strtolower(trim($data['email'])),
            'password' => $data['password'],
            'role' => $data['role'],
            'job_title' => $data['job_title'] ?? null,
            'status' => $data['status'],
        ]);

        return redirect()
            ->route('settings.staff.index')
            ->with('success', 'Akun staff berhasil dibuat.');
    }

    public function edit(User $user)
    {
        $this->guardOwner();
        $this->guardSameTenant($user);

        return view('pages.settings.staff.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $this->guardOwner();
        $this->guardSameTenant($user);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => [
                'required',
                'email',
                'max:150',
                Rule::unique('users', 'email')->ignore($user->id),
            ],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'role' => ['required', Rule::in(['owner', 'staff'])],
            'job_title' => ['nullable', 'string', 'max:120'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ]);

        $payload = [
            'name' => trim($data['name']),
            'email' => strtolower(trim($data['email'])),
            'role' => $data['role'],
            'job_title' => $data['job_title'] ?? null,
            'status' => $data['status'],
        ];

        if (!empty($data['password'])) {
            $payload['password'] = $data['password'];
        }

        DB::transaction(function () use ($user, $payload) {
            $user->update($payload);

            // Minimal harus ada 1 owner aktif dalam tenant.
            $activeOwnerCount = User::where('tenant_id', $this->tenantId())
                ->where('role', 'owner')
                ->where('status', 'active')
                ->count();

            if ($activeOwnerCount < 1) {
                abort(422, 'Minimal harus ada 1 owner aktif dalam tenant.');
            }
        });

        return redirect()
            ->route('settings.staff.index')
            ->with('success', 'Akun berhasil diperbarui.');
    }

    public function destroy(User $user)
    {
        $this->guardOwner();
        $this->guardSameTenant($user);

        if ((int) $user->id === (int) auth()->id()) {
            return back()->withErrors(['user' => 'Owner tidak bisa menghapus akun sendiri.']);
        }

        DB::transaction(function () use ($user) {
            $user->delete();

            // Minimal harus ada 1 owner aktif setelah delete.
            $activeOwnerCount = User::where('tenant_id', $this->tenantId())
                ->where('role', 'owner')
                ->where('status', 'active')
                ->count();

            if ($activeOwnerCount < 1) {
                abort(422, 'Minimal harus ada 1 owner aktif dalam tenant.');
            }
        });

        return redirect()
            ->route('settings.staff.index')
            ->with('success', 'Akun berhasil dihapus.');
    }
}