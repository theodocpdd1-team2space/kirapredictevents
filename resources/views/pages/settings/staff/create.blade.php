@extends('pages.settings._layout')

@section('title', 'Tambah Staff')

@section('settings_actions')
  <a href="{{ route('settings.staff.index') }}"
     class="inline-flex items-center justify-center rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 px-5 py-3 text-sm font-semibold text-slate-900 dark:text-white hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors">
    Kembali
  </a>
@endsection

@section('settings_content')
<div class="max-w-3xl">
  <form method="POST" action="{{ route('settings.staff.store') }}"
        class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 shadow-sm overflow-hidden">
    @csrf

    <div class="p-5 sm:p-6 border-b border-slate-200 dark:border-slate-800">
      <h2 class="text-lg font-semibold text-slate-900 dark:text-white">Tambah Akun Staff / Owner</h2>
      <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
        Akun akan otomatis masuk ke tenant yang sama dengan owner saat ini.
      </p>
    </div>

    <div class="p-5 sm:p-6 space-y-5">
      @if($errors->any())
        <div class="rounded-xl border border-red-200 dark:border-red-500/20 bg-red-50 dark:bg-red-500/10 px-4 py-3 text-sm text-red-800 dark:text-red-400">
          {{ $errors->first() }}
        </div>
      @endif

      <div>
        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-2">Nama</label>
        <input name="name" value="{{ old('name') }}" required
               class="w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-950/50 px-4 py-3 text-sm text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500/50">
      </div>

      <div>
        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-2">Email</label>
        <input type="email" name="email" value="{{ old('email') }}" required
               class="w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-950/50 px-4 py-3 text-sm text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500/50">
      </div>

      <div>
        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-2">Jabatan / Posisi</label>
        <input name="job_title" value="{{ old('job_title') }}" placeholder="Contoh: Admin Operasional"
               class="w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-950/50 px-4 py-3 text-sm text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500/50">
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-2">Role</label>
          <select name="role" required
                  class="w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-950/50 px-4 py-3 text-sm text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500/50">
            <option value="staff" @selected(old('role','staff') === 'staff')>Staff</option>
            <option value="owner" @selected(old('role') === 'owner')>Owner</option>
          </select>
        </div>

        <div>
          <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-2">Status</label>
          <select name="status" required
                  class="w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-950/50 px-4 py-3 text-sm text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500/50">
            <option value="active" @selected(old('status','active') === 'active')>Active</option>
            <option value="inactive" @selected(old('status') === 'inactive')>Inactive</option>
          </select>
        </div>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-2">Password</label>
          <input type="password" name="password" required
                 class="w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-950/50 px-4 py-3 text-sm text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500/50">
        </div>

        <div>
          <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-2">Konfirmasi Password</label>
          <input type="password" name="password_confirmation" required
                 class="w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-950/50 px-4 py-3 text-sm text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500/50">
        </div>
      </div>
    </div>

    <div class="p-5 sm:p-6 border-t border-slate-200 dark:border-slate-800 flex flex-col sm:flex-row gap-3 sm:justify-end">
      <a href="{{ route('settings.staff.index') }}"
         class="inline-flex items-center justify-center rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 px-5 py-3 text-sm font-semibold text-slate-900 dark:text-white">
        Batal
      </a>

      <button type="submit"
              class="inline-flex items-center justify-center rounded-xl bg-blue-600 px-5 py-3 text-sm font-semibold text-white shadow-sm hover:bg-blue-700">
        Simpan Akun
      </button>
    </div>
  </form>
</div>
@endsection