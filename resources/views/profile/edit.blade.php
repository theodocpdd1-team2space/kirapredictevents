@extends('layouts.app-shell')

@section('title', 'Profile Settings')

@section('content')
    <div class="max-w-7xl mx-auto space-y-6">
        <div class="mb-8">
            <h2 class="text-2xl font-black text-slate-900 dark:text-white">Profile Settings</h2>
            <p class="text-sm text-slate-500 dark:text-slate-400">Perbarui informasi profil dan keamanan akun Anda.</p>
        </div>

        <div class="p-4 sm:p-8 bg-white dark:bg-slate-900 shadow sm:rounded-2xl border border-slate-200 dark:border-slate-800">
            <div class="max-w-xl">
                @include('profile.partials.update-profile-information-form')
            </div>
        </div>

        <div class="p-4 sm:p-8 bg-white dark:bg-slate-900 shadow sm:rounded-2xl border border-slate-200 dark:border-slate-800">
            <div class="max-w-xl">
                @include('profile.partials.update-password-form')
            </div>
        </div>

        <div class="p-4 sm:p-8 bg-white dark:bg-slate-900 shadow sm:rounded-2xl border border-slate-200 dark:border-slate-800">
            <div class="max-w-xl">
                @include('profile.partials.delete-user-form')
            </div>
        </div>
    </div>
@endsection