<section>
    <header>
        <h2 class="text-xl font-bold text-slate-900 dark:text-white tracking-tight">
            {{ __('Update Password') }}
        </h2>
        <p class="mt-1 text-sm text-slate-600 dark:text-slate-400">
            {{ __('Ensure your account is using a long, random password to stay secure.') }}
        </p>
    </header>

    <form method="post" action="{{ route('password.update') }}" class="mt-8 space-y-6">
        @csrf
        @method('put')

        {{-- Current Password --}}
        <div class="space-y-1">
            <x-input-label for="update_password_current_password" :value="__('Current Password')" class="text-sm font-bold text-slate-700 dark:text-slate-300" />
            <x-text-input id="update_password_current_password" name="current_password" type="password" 
                class="mt-1 block w-full rounded-xl border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900/50 text-slate-900 dark:text-white shadow-sm focus:border-blue-600 dark:focus:border-blue-500 focus:ring-blue-600/20 transition-all" 
                autocomplete="current-password" />
            <x-input-error :messages="$errors->updatePassword->get('current_password')" class="mt-2 text-red-500" />
        </div>

        {{-- New Password --}}
        <div class="space-y-1">
            <x-input-label for="update_password_password" :value="__('New Password')" class="text-sm font-bold text-slate-700 dark:text-slate-300" />
            <x-text-input id="update_password_password" name="password" type="password" 
                class="mt-1 block w-full rounded-xl border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900/50 text-slate-900 dark:text-white shadow-sm focus:border-blue-600 dark:focus:border-blue-500 focus:ring-blue-600/20 transition-all" 
                autocomplete="new-password" />
            <x-input-error :messages="$errors->updatePassword->get('password')" class="mt-2 text-red-500" />
        </div>

        {{-- Confirm Password --}}
        <div class="space-y-1">
            <x-input-label for="update_password_password_confirmation" :value="__('Confirm Password')" class="text-sm font-bold text-slate-700 dark:text-slate-300" />
            <x-text-input id="update_password_password_confirmation" name="password_confirmation" type="password" 
                class="mt-1 block w-full rounded-xl border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900/50 text-slate-900 dark:text-white shadow-sm focus:border-blue-600 dark:focus:border-blue-500 focus:ring-blue-600/20 transition-all" 
                autocomplete="new-password" />
            <x-input-error :messages="$errors->updatePassword->get('password_confirmation')" class="mt-2 text-red-500" />
        </div>

        {{-- Action Buttons --}}
        <div class="flex items-center gap-4 pt-4 border-t border-slate-200 dark:border-slate-800">
            <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-blue-600 px-6 py-2.5 text-sm font-bold text-white shadow-lg shadow-blue-600/20 transition-all hover:bg-blue-700 active:scale-[0.98]">
                {{ __('Save Changes') }}
            </button>

            @if (session('status') === 'password-updated')
                <p x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 3000)" class="text-sm font-bold text-green-600 dark:text-green-400 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    {{ __('Saved Successfully.') }}
                </p>
            @endif
        </div>
    </form>
</section>