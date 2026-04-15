<section>
    <header>
        <h2 class="text-xl font-bold text-slate-900 dark:text-white tracking-tight">
            {{ __('Profile Information') }}
        </h2>
        <p class="mt-1 text-sm text-slate-600 dark:text-slate-400">
            {{ __("Update your account's profile information, email address, and profile photo.") }}
        </p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" class="mt-8 space-y-6" enctype="multipart/form-data">
        @csrf
        @method('patch')

        {{-- Name --}}
        <div class="space-y-1">
            <x-input-label for="name" :value="__('Name')" class="text-sm font-bold text-slate-700 dark:text-slate-300" />
            <x-text-input id="name" name="name" type="text" 
                class="mt-1 block w-full rounded-xl border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900/50 text-slate-900 dark:text-white shadow-sm focus:border-blue-600 dark:focus:border-blue-500 focus:ring-blue-600/20 transition-all" 
                :value="old('name', $user->name)" required autofocus autocomplete="name" />
            <x-input-error class="mt-2 text-red-500" :messages="$errors->get('name')" />
        </div>

        {{-- Email --}}
        <div class="space-y-1">
            <x-input-label for="email" :value="__('Email')" class="text-sm font-bold text-slate-700 dark:text-slate-300" />
            <x-text-input id="email" name="email" type="email" 
                class="mt-1 block w-full rounded-xl border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900/50 text-slate-900 dark:text-white shadow-sm focus:border-blue-600 dark:focus:border-blue-500 focus:ring-blue-600/20 transition-all" 
                :value="old('email', $user->email)" required autocomplete="username" />
            <x-input-error class="mt-2 text-red-500" :messages="$errors->get('email')" />

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div class="mt-3 p-4 rounded-xl bg-amber-50 dark:bg-amber-500/10 border border-amber-200 dark:border-amber-500/20">
                    <p class="text-sm text-amber-800 dark:text-amber-200 font-medium">
                        {{ __('Your email address is unverified.') }}
                        <button form="send-verification" class="ml-2 font-bold underline hover:text-amber-900 dark:hover:text-white transition-colors focus:outline-none">
                            {{ __('Click here to re-send the verification email.') }}
                        </button>
                    </p>
                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-2 font-bold text-sm text-green-600 dark:text-green-400">
                            {{ __('A new verification link has been sent to your email address.') }}
                        </p>
                    @endif
                </div>
            @endif
        </div>

        {{-- Job Position --}}
        <div class="space-y-1">
            <x-input-label for="job_title" :value="__('Job Position')" class="text-sm font-bold text-slate-700 dark:text-slate-300" />
            <x-text-input id="job_title" name="job_title" type="text"
                class="mt-1 block w-full rounded-xl border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900/50 text-slate-900 dark:text-white shadow-sm focus:border-blue-600 dark:focus:border-blue-500 focus:ring-blue-600/20 transition-all"
                :value="old('job_title', $user->job_title)"
                autocomplete="organization-title"
                placeholder="e.g. Visual Engineer / Project Manager"
            />
            <x-input-error class="mt-2 text-red-500" :messages="$errors->get('job_title')" />
        </div>

        {{-- Profile Photo --}}
        <div class="space-y-1">
            <x-input-label for="photo" :value="__('Profile Photo')" class="text-sm font-bold text-slate-700 dark:text-slate-300" />
            <div class="mt-3 flex items-center gap-5">
                <div class="relative group">
                    <img src="{{ auth()->user()->profilePhotoUrl() }}" alt="Profile" class="h-16 w-16 rounded-full object-cover border-2 border-slate-200 dark:border-slate-700 bg-white shadow-sm" />
                </div>
                
                <div class="flex-1">
                    <input id="photo" type="file" name="photo" accept="image/*" 
                        class="block w-full text-sm text-slate-600 dark:text-slate-400
                               file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 
                               file:text-sm file:font-bold file:bg-blue-50 file:text-blue-700 
                               dark:file:bg-blue-500/10 dark:file:text-blue-400
                               hover:file:bg-blue-100 dark:hover:file:bg-blue-500/20 transition-all cursor-pointer" />
                    <p class="mt-2 text-xs font-medium text-slate-500 dark:text-slate-500">Dianjurkan rasio 1:1 (Persegi). Maksimal 2MB (PNG, JPG, JPEG).</p>
                </div>
            </div>
            <x-input-error class="mt-2 text-red-500" :messages="$errors->get('photo')" />
        </div>

        {{-- Action Buttons --}}
        <div class="flex items-center gap-4 pt-4 border-t border-slate-200 dark:border-slate-800">
            <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-blue-600 px-6 py-2.5 text-sm font-bold text-white shadow-lg shadow-blue-600/20 transition-all hover:bg-blue-700 active:scale-[0.98]">
                {{ __('Save Changes') }}
            </button>

            @if (session('status') === 'profile-updated')
                <p x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 3000)" class="text-sm font-bold text-green-600 dark:text-green-400 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    {{ __('Saved Successfully.') }}
                </p>
            @endif
        </div>
    </form>
</section>