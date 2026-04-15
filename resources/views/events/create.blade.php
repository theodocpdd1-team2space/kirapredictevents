{{-- resources/views/pages/events/create.blade.php --}}
@extends('layouts.app-shell')

@section('title', __('event.create_title') ?? 'Estimasi Baru')

@section('content')
<div class="max-w-5xl space-y-6">

  {{-- Header --}}
  <div>
    <h1 class="text-4xl font-semibold tracking-tight text-slate-900 dark:text-white">
      {{ __('event.create_title') ?? 'New Event Estimation' }}
    </h1>
    <p class="mt-2 text-slate-500 dark:text-slate-400">
      {{ __('event.create_subtitle') ?? 'Fill in the event details to generate a cost estimation' }}
    </p>
  </div>

  {{-- Alerts --}}
  @if(session('success'))
    <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
      {{ session('success') }}
    </div>
  @endif

  @if($errors->any())
    <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
      {{ $errors->first() }}
    </div>
  @endif

  <form method="POST" action="{{ route('events.store') }}"
        class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-800 overflow-hidden">
    @csrf

    {{-- Card Header --}}
    <div class="px-8 py-6 border-b border-slate-200 dark:border-slate-800">
      <h2 class="text-lg font-semibold text-slate-900 dark:text-white">
        {{ __('event.section_info') ?? 'Informasi Event' }}
      </h2>
    </div>

    <div class="p-8 space-y-8">

      {{-- Nama Acara --}}
      <div>
        <label class="block text-sm font-semibold text-slate-900 dark:text-white mb-2">
          {{ __('event.event_name') ?? 'Nama Acara' }}
        </label>
        <input name="event_name"
               value="{{ old('event_name') }}"
               placeholder="{{ __('event.event_name_ph') ?? 'contoh: Perayaan Natal GBI Surabaya' }}"
               class="w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-950/40
                      px-4 py-3 text-sm text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500
                      focus:outline-none focus:ring-2 focus:ring-blue-500/40 focus:border-blue-500">
        @error('event_name') <p class="text-xs text-red-600 dark:text-red-400 mt-2">{{ $message }}</p> @enderror
      </div>

      {{-- Client name + whatsapp --}}
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
          <label class="block text-sm font-semibold text-slate-900 dark:text-white mb-2">
            {{ __('event.client_name') ?? 'Nama Client' }}
          </label>
          <input name="client_name"
                 value="{{ old('client_name') }}"
                 placeholder="{{ __('event.client_name_ph') ?? 'contoh: Bpk/Ibu Andi Wijaya' }}"
                 class="w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-950/40
                        px-4 py-3 text-sm text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500
                        focus:outline-none focus:ring-2 focus:ring-blue-500/40 focus:border-blue-500">
          @error('client_name') <p class="text-xs text-red-600 dark:text-red-400 mt-2">{{ $message }}</p> @enderror
        </div>

        <div>
          <label class="block text-sm font-semibold text-slate-900 dark:text-white mb-2">
            {{ __('event.client_whatsapp') ?? 'No. WhatsApp Client' }}
          </label>
          <input name="client_whatsapp"
                 value="{{ old('client_whatsapp') }}"
                 placeholder="{{ __('event.client_whatsapp_ph') ?? '6281234567890' }}"
                 class="w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-950/40
                        px-4 py-3 text-sm text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500
                        focus:outline-none focus:ring-2 focus:ring-blue-500/40 focus:border-blue-500">
          <p class="mt-2 text-xs text-slate-500 dark:text-slate-400">
            {{ __('event.client_whatsapp_help') ?? 'Gunakan format 62xxxx (tanpa + dan tanpa spasi).' }}
          </p>
          @error('client_whatsapp') <p class="text-xs text-red-600 dark:text-red-400 mt-2">{{ $message }}</p> @enderror
        </div>
      </div>

      {{-- Type + participants --}}
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
          <label class="block text-sm font-semibold text-slate-900 dark:text-white mb-2">
            {{ __('event.event_type') ?? 'Tipe Acara' }}
          </label>

          {{-- contoh pilihan; sesuaikan dengan list kamu --}}
          @php($typeChoice = old('event_type_choice'))
          <select name="event_type_choice"
                  class="w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-950/40
                         px-4 py-3 text-sm text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500/40 focus:border-blue-500">
            <option value="" disabled {{ $typeChoice==='' ? 'selected' : '' }}>
              {{ __('event.choose_event_type') ?? 'Pilih tipe acara' }}
            </option>
            <option value="wedding" @selected($typeChoice==='wedding')>Wedding</option>
            <option value="seminar" @selected($typeChoice==='seminar')>Seminar</option>
            <option value="concert" @selected($typeChoice==='concert')>Concert</option>
            <option value="church" @selected($typeChoice==='church')>Church</option>
            <option value="other" @selected($typeChoice==='other')>{{ __('event.other') ?? 'Lainnya' }}</option>
          </select>
          @error('event_type_choice') <p class="text-xs text-red-600 dark:text-red-400 mt-2">{{ $message }}</p> @enderror

          {{-- input "other" --}}
          <div class="mt-3">
            <input name="event_type_other"
                   value="{{ old('event_type_other') }}"
                   placeholder="{{ __('event.other_type_ph') ?? 'Jika pilih Lainnya, tulis tipe acara…' }}"
                   class="w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-950/40
                          px-4 py-3 text-sm text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500
                          focus:outline-none focus:ring-2 focus:ring-blue-500/40 focus:border-blue-500">
            @error('event_type_other') <p class="text-xs text-red-600 dark:text-red-400 mt-2">{{ $message }}</p> @enderror
          </div>
        </div>

        <div>
          <label class="block text-sm font-semibold text-slate-900 dark:text-white mb-2">
            {{ __('event.participants') ?? 'Jumlah Peserta' }}
          </label>
          <input type="number" min="1" name="participants"
                 value="{{ old('participants') }}"
                 placeholder="{{ __('event.participants_ph') ?? 'Masukkan jumlah peserta' }}"
                 class="w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-950/40
                        px-4 py-3 text-sm text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500
                        focus:outline-none focus:ring-2 focus:ring-blue-500/40 focus:border-blue-500">
          @error('participants') <p class="text-xs text-red-600 dark:text-red-400 mt-2">{{ $message }}</p> @enderror
        </div>
      </div>

      {{-- Location + duration --}}
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
          <label class="block text-sm font-semibold text-slate-900 dark:text-white mb-2">
            {{ __('event.location') ?? 'Lokasi (Kota)' }}
          </label>

          @php($locChoice = old('location_choice'))
          <select name="location_choice"
                  class="w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-950/40
                         px-4 py-3 text-sm text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500/40 focus:border-blue-500">
            <option value="" disabled {{ $locChoice==='' ? 'selected' : '' }}>
              {{ __('event.choose_location') ?? 'Pilih lokasi' }}
            </option>
            <option value="surabaya" @selected($locChoice==='surabaya')>Surabaya</option>
            <option value="sidoarjo" @selected($locChoice==='sidoarjo')>Sidoarjo</option>
            <option value="gresik" @selected($locChoice==='gresik')>Gresik</option>
            <option value="other" @selected($locChoice==='other')>{{ __('event.other') ?? 'Lainnya' }}</option>
          </select>
          @error('location_choice') <p class="text-xs text-red-600 dark:text-red-400 mt-2">{{ $message }}</p> @enderror

          <div class="mt-3">
            <input name="location_other"
                   value="{{ old('location_other') }}"
                   placeholder="{{ __('event.other_location_ph') ?? 'Jika pilih Lainnya, tulis kota…' }}"
                   class="w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-950/40
                          px-4 py-3 text-sm text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500
                          focus:outline-none focus:ring-2 focus:ring-blue-500/40 focus:border-blue-500">
            @error('location_other') <p class="text-xs text-red-600 dark:text-red-400 mt-2">{{ $message }}</p> @enderror
          </div>
        </div>

        <div>
          <label class="block text-sm font-semibold text-slate-900 dark:text-white mb-2">
            {{ __('event.duration') ?? 'Durasi (jam)' }}
          </label>
          <input type="number" min="1" name="duration"
                 value="{{ old('duration') }}"
                 placeholder="{{ __('event.duration_ph') ?? 'Masukkan durasi dalam jam' }}"
                 class="w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-950/40
                        px-4 py-3 text-sm text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500
                        focus:outline-none focus:ring-2 focus:ring-blue-500/40 focus:border-blue-500">
          @error('duration') <p class="text-xs text-red-600 dark:text-red-400 mt-2">{{ $message }}</p> @enderror
        </div>
      </div>

      {{-- Service Level --}}
      <div>
        <label class="block text-sm font-semibold text-slate-900 dark:text-white mb-2">
          {{ __('event.service_level') ?? 'Service Level' }}
        </label>

        @php($sv = old('service_level'))
        <select name="service_level"
                class="w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-950/40
                       px-4 py-3 text-sm text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500/40 focus:border-blue-500">
          <option value="" disabled {{ $sv==='' ? 'selected' : '' }}>
            {{ __('event.choose_service_level') ?? 'Pilih service level' }}
          </option>
          <option value="basic" @selected($sv==='basic')>Basic</option>
          <option value="standard" @selected($sv==='standard')>Standard</option>
          <option value="premium" @selected($sv==='premium')>Premium</option>
        </select>
        @error('service_level') <p class="text-xs text-red-600 dark:text-red-400 mt-2">{{ $message }}</p> @enderror
      </div>

      {{-- Crew (Opsional) --}}
      <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-slate-50/60 dark:bg-slate-950/30 p-6">
        <div class="flex items-start justify-between gap-4">
          <div>
            <div class="text-base font-semibold text-slate-900 dark:text-white">
              {{ __('event.crew_section') ?? 'Crew (Opsional)' }}
            </div>
            <div class="mt-1 text-sm text-slate-500 dark:text-slate-400">
              {{ __('event.crew_help') ?? 'Kalau diisi, sistem pakai angka ini. Kalau kosong, sistem ambil dari rules (ADD_CREW).' }}
            </div>
          </div>
        </div>

        <div class="mt-5 grid grid-cols-1 md:grid-cols-3 gap-4">
          <div>
            <label class="block text-sm font-semibold text-slate-900 dark:text-white mb-2">
              {{ __('event.crew_operator') ?? 'Crew Operator' }}
            </label>
            <input type="number" min="0" name="crew_operator_qty"
                   value="{{ old('crew_operator_qty') }}"
                   placeholder="0"
                   class="w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-950/40
                          px-4 py-3 text-sm text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500/40 focus:border-blue-500">
            @error('crew_operator_qty') <p class="text-xs text-red-600 dark:text-red-400 mt-2">{{ $message }}</p> @enderror
          </div>

          <div>
            <label class="block text-sm font-semibold text-slate-900 dark:text-white mb-2">
              {{ __('event.crew_engineer') ?? 'Crew Engineer' }}
            </label>
            <input type="number" min="0" name="crew_engineer_qty"
                   value="{{ old('crew_engineer_qty') }}"
                   placeholder="0"
                   class="w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-950/40
                          px-4 py-3 text-sm text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500/40 focus:border-blue-500">
            @error('crew_engineer_qty') <p class="text-xs text-red-600 dark:text-red-400 mt-2">{{ $message }}</p> @enderror
          </div>

          <div>
            <label class="block text-sm font-semibold text-slate-900 dark:text-white mb-2">
              {{ __('event.crew_stage') ?? 'Crew Stage' }}
            </label>
            <input type="number" min="0" name="crew_stage_qty"
                   value="{{ old('crew_stage_qty') }}"
                   placeholder="0"
                   class="w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-950/40
                          px-4 py-3 text-sm text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500/40 focus:border-blue-500">
            @error('crew_stage_qty') <p class="text-xs text-red-600 dark:text-red-400 mt-2">{{ $message }}</p> @enderror
          </div>
        </div>
      </div>

      {{-- Special Requirement --}}
      <div>
        <label class="block text-sm font-semibold text-slate-900 dark:text-white mb-2">
          {{ __('event.special_requirement') ?? 'Kebutuhan Khusus' }}
        </label>
        <textarea name="special_requirement" rows="4"
                  placeholder="{{ __('event.special_requirement_ph') ?? 'Tulis kebutuhan khusus atau catatan' }}"
                  class="w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-950/40
                         px-4 py-3 text-sm text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500
                         focus:outline-none focus:ring-2 focus:ring-blue-500/40 focus:border-blue-500">{{ old('special_requirement') }}</textarea>
        @error('special_requirement') <p class="text-xs text-red-600 dark:text-red-400 mt-2">{{ $message }}</p> @enderror
      </div>

      {{-- Submit --}}
      <div class="flex justify-end pt-2">
        <button type="submit"
                class="rounded-xl bg-blue-600 px-8 py-3 text-sm font-semibold text-white hover:bg-blue-700 shadow-sm">
          {{ __('event.generate') ?? 'Generate Estimation' }}
        </button>
      </div>

    </div>
  </form>
</div>
@endsection