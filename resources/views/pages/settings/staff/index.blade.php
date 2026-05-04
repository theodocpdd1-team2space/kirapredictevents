@extends('pages.settings._layout')

@section('title', 'Staff Management')

@section('settings_actions')
  <a href="{{ route('settings.staff.create') }}"
     class="inline-flex items-center justify-center gap-2 rounded-xl bg-blue-600 px-5 py-3 text-sm font-semibold text-white shadow-sm hover:bg-blue-700 transition-colors">
    <span class="text-lg leading-none">+</span> Tambah Staff
  </a>
@endsection

@section('settings_content')
<div class="space-y-6">

  @if(session('success'))
    <div class="rounded-xl border border-green-200 dark:border-green-500/20 bg-green-50 dark:bg-green-500/10 px-4 py-3 text-sm text-green-800 dark:text-green-400">
      {{ session('success') }}
    </div>
  @endif

  @if($errors->any())
    <div class="rounded-xl border border-red-200 dark:border-red-500/20 bg-red-50 dark:bg-red-500/10 px-4 py-3 text-sm text-red-800 dark:text-red-400">
      {{ $errors->first() }}
    </div>
  @endif

  <form method="GET" class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-4 shadow-sm">
    <div class="grid grid-cols-1 md:grid-cols-12 gap-3">
      <div class="md:col-span-6">
        <input id="staffSearch" name="search" value="{{ $search ?? request('search') }}"
               placeholder="Cari nama, email, atau jabatan..."
               class="w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-950/50 px-4 py-3 text-sm text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500/50">
      </div>

      <div class="md:col-span-3">
        <select name="role"
                class="w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-950/50 px-4 py-3 text-sm text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500/50">
          <option value="all" @selected(($role ?? request('role','all')) === 'all')>Semua Role</option>
          <option value="owner" @selected(($role ?? request('role')) === 'owner')>Owner</option>
          <option value="staff" @selected(($role ?? request('role')) === 'staff')>Staff</option>
        </select>
      </div>

      <div class="md:col-span-3">
        <select name="status"
                class="w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-950/50 px-4 py-3 text-sm text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500/50">
          <option value="all" @selected(($status ?? request('status','all')) === 'all')>Semua Status</option>
          <option value="active" @selected(($status ?? request('status')) === 'active')>Active</option>
          <option value="inactive" @selected(($status ?? request('status')) === 'inactive')>Inactive</option>
        </select>
      </div>
    </div>

    <div class="mt-3 flex justify-end">
      <a href="{{ route('settings.staff.index') }}"
         class="inline-flex w-full sm:w-auto items-center justify-center rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 px-4 py-2.5 text-sm font-semibold text-slate-900 dark:text-white hover:bg-slate-50 dark:hover:bg-slate-800">
        Reset
      </a>
    </div>
  </form>

  <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 shadow-sm overflow-hidden">
    {{-- Desktop --}}
    <div class="hidden md:block overflow-x-auto">
      <table class="w-full">
        <thead class="bg-slate-50 dark:bg-slate-800/50 border-b border-slate-200 dark:border-slate-800">
          <tr>
            <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">User</th>
            <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Role</th>
            <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Status</th>
            <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Joined</th>
            <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Action</th>
          </tr>
        </thead>

        <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
          @forelse($users as $staff)
            @php
              $roleClass = $staff->role === 'owner'
                ? 'bg-blue-100 text-blue-700 dark:bg-blue-500/20 dark:text-blue-400'
                : 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300';

              $statusClass = $staff->status === 'inactive'
                ? 'bg-red-100 text-red-700 dark:bg-red-500/20 dark:text-red-400'
                : 'bg-green-100 text-green-700 dark:bg-green-500/20 dark:text-green-400';
            @endphp

            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors">
              <td class="px-6 py-5">
                <div class="flex items-center gap-3">
                  <img src="{{ $staff->profilePhotoUrl() }}"
                       class="h-10 w-10 rounded-full object-cover border border-slate-200 dark:border-slate-700"
                       alt="{{ $staff->name }}">
                  <div>
                    <div class="text-sm font-semibold text-slate-900 dark:text-white">
                      {{ $staff->name }}
                      @if((int)$staff->id === (int)auth()->id())
                        <span class="text-xs text-slate-400 dark:text-slate-500">(You)</span>
                      @endif
                    </div>
                    <div class="text-xs text-slate-500 dark:text-slate-400">{{ $staff->email }}</div>
                    @if($staff->job_title)
                      <div class="text-xs text-slate-400 dark:text-slate-500 mt-0.5">{{ $staff->job_title }}</div>
                    @endif
                  </div>
                </div>
              </td>

              <td class="px-6 py-5">
                <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $roleClass }}">
                  {{ ucfirst($staff->role) }}
                </span>
              </td>

              <td class="px-6 py-5">
                <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $statusClass }}">
                  {{ ucfirst($staff->status ?? 'active') }}
                </span>
              </td>

              <td class="px-6 py-5 text-sm text-slate-500 dark:text-slate-400">
                {{ optional($staff->created_at)->format('Y-m-d') }}
              </td>

              <td class="px-6 py-5">
                <div class="flex items-center gap-2">
                  <a href="{{ route('settings.staff.edit', $staff->id) }}"
                     class="inline-flex items-center rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 px-4 py-2 text-sm font-semibold text-slate-900 dark:text-white hover:bg-slate-50 dark:hover:bg-slate-800">
                    Edit
                  </a>

                  @if((int)$staff->id !== (int)auth()->id())
                    <form method="POST" action="{{ route('settings.staff.destroy', $staff->id) }}"
                          onsubmit="return confirm('Hapus akun ini?');">
                      @csrf
                      @method('DELETE')
                      <button class="inline-flex items-center rounded-lg border border-red-200 dark:border-red-500/30 bg-white dark:bg-slate-900 px-4 py-2 text-sm font-semibold text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-500/10">
                        Delete
                      </button>
                    </form>
                  @endif
                </div>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="5" class="px-6 py-10 text-center text-sm text-slate-500 dark:text-slate-400">
                Belum ada staff.
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    {{-- Mobile --}}
    <div class="md:hidden divide-y divide-slate-200 dark:divide-slate-800">
      @forelse($users as $staff)
        @php
          $roleClass = $staff->role === 'owner'
            ? 'bg-blue-100 text-blue-700 dark:bg-blue-500/20 dark:text-blue-400'
            : 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300';

          $statusClass = $staff->status === 'inactive'
            ? 'bg-red-100 text-red-700 dark:bg-red-500/20 dark:text-red-400'
            : 'bg-green-100 text-green-700 dark:bg-green-500/20 dark:text-green-400';
        @endphp

        <div class="p-4">
          <div class="flex items-start gap-3">
            <img src="{{ $staff->profilePhotoUrl() }}"
                 class="h-12 w-12 rounded-full object-cover border border-slate-200 dark:border-slate-700"
                 alt="{{ $staff->name }}">

            <div class="min-w-0 flex-1">
              <div class="font-bold text-slate-900 dark:text-white break-words">
                {{ $staff->name }}
                @if((int)$staff->id === (int)auth()->id())
                  <span class="text-xs font-semibold text-slate-400 dark:text-slate-500">(You)</span>
                @endif
              </div>
              <div class="text-sm text-slate-500 dark:text-slate-400 break-words">{{ $staff->email }}</div>
              @if($staff->job_title)
                <div class="text-xs text-slate-400 dark:text-slate-500 mt-1">{{ $staff->job_title }}</div>
              @endif

              <div class="mt-3 flex flex-wrap gap-2">
                <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $roleClass }}">
                  {{ ucfirst($staff->role) }}
                </span>
                <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $statusClass }}">
                  {{ ucfirst($staff->status ?? 'active') }}
                </span>
              </div>

              <div class="mt-4 grid grid-cols-2 gap-2">
                <a href="{{ route('settings.staff.edit', $staff->id) }}"
                   class="inline-flex items-center justify-center rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 px-4 py-2.5 text-sm font-bold text-slate-900 dark:text-white">
                  Edit
                </a>

                @if((int)$staff->id !== (int)auth()->id())
                  <form method="POST" action="{{ route('settings.staff.destroy', $staff->id) }}"
                        onsubmit="return confirm('Hapus akun ini?');">
                    @csrf
                    @method('DELETE')
                    <button class="w-full inline-flex items-center justify-center rounded-xl border border-red-200 dark:border-red-500/30 bg-white dark:bg-slate-900 px-4 py-2.5 text-sm font-bold text-red-600 dark:text-red-400">
                      Delete
                    </button>
                  </form>
                @else
                  <div class="inline-flex items-center justify-center rounded-xl border border-slate-200 dark:border-slate-800 px-4 py-2.5 text-sm font-bold text-slate-400">
                    Current
                  </div>
                @endif
              </div>
            </div>
          </div>
        </div>
      @empty
        <div class="px-6 py-10 text-center text-sm text-slate-500 dark:text-slate-400">
          Belum ada staff.
        </div>
      @endforelse
    </div>

    <div class="px-4 sm:px-6 py-4 border-t border-slate-200 dark:border-slate-800">
      {{ $users->links() }}
    </div>
  </div>
</div>

<script>
(function () {
  const form = document.querySelector('form[method="GET"]');
  if (!form) return;

  const search = document.getElementById('staffSearch');
  let t;

  if (search) {
    search.addEventListener('input', () => {
      clearTimeout(t);
      t = setTimeout(() => form.submit(), 300);
    });
  }

  ['role', 'status'].forEach((name) => {
    const el = form.querySelector(`select[name="${name}"]`);
    if (el) el.addEventListener('change', () => form.submit());
  });
})();
</script>
@endsection