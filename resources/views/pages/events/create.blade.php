@extends('layouts.app-shell')
@section('title', __('events.title'))

@section('content')
@php
  use Illuminate\Support\Facades\Lang;

  $t = function(string $key, string $fallback) {
    if (!Lang::has($key)) return $fallback;
    $val = __($key);
    return is_array($val) ? $fallback : (string)$val;
  };

  $locationOptions = $locationOptions ?? [];
  $venueTypeOld = old('venue_type', 'indoor');
@endphp

<div class="max-w-5xl mx-auto space-y-6">

  <div>
    <h1 class="text-4xl font-semibold tracking-tight text-slate-900 dark:text-white">
      {{ $t('events.title', 'New Event Estimation') }}
    </h1>
    <p class="text-slate-500 dark:text-slate-400 mt-2">
      {{ $t('events.subtitle', 'Fill in the event details to generate a cost estimation') }}
    </p>
  </div>

  <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-800 overflow-hidden">
    <div class="px-6 py-5 border-b border-slate-200 dark:border-slate-800">
      <div class="text-lg font-semibold text-slate-900 dark:text-white">
        {{ $t('events.card_title', 'Event Information') }}
      </div>
    </div>

    <form method="POST" action="{{ route('events.store') }}" class="p-6 space-y-6" novalidate>
      @csrf

      @if($errors->any())
        <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
          {{ $errors->first() }}
        </div>
      @endif

      {{-- Event Name --}}
      <div>
        <label class="block text-sm font-semibold text-slate-900 dark:text-white mb-2">
          {{ $t('events.event_name', 'Event Name') }}
        </label>
        <input name="event_name" value="{{ old('event_name') }}"
          class="w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-950/50 px-4 py-3 text-sm text-slate-900 dark:text-white
                 placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500/40 dark:focus:ring-blue-500/25 focus:border-blue-500"
          placeholder="{{ $t('events.event_name_ph', 'e.g. Perayaan Natal GBI Surabaya') }}" required>
        @error('event_name')<p class="text-xs text-red-600 mt-2">{{ $message }}</p>@enderror
      </div>

      {{-- Client --}}
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-semibold text-slate-900 dark:text-white mb-2">
            {{ $t('events.client_name', 'Client Name') }}
          </label>
          <input name="client_name" value="{{ old('client_name') }}"
            class="w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-950/50 px-4 py-3 text-sm text-slate-900 dark:text-white"
            placeholder="{{ $t('events.client_name_ph', 'e.g. Bpk/Ibu Andi Wijaya') }}" required>
          @error('client_name')<p class="text-xs text-red-600 mt-2">{{ $message }}</p>@enderror
        </div>

        <div>
          <label class="block text-sm font-semibold text-slate-900 dark:text-white mb-2">
            {{ $t('events.client_wa', 'Client WhatsApp') }}
          </label>
          <input name="client_whatsapp" value="{{ old('client_whatsapp') }}"
            class="w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-950/50 px-4 py-3 text-sm text-slate-900 dark:text-white"
            placeholder="{{ $t('events.client_wa_ph', '6281234567890') }}" required>
          <p class="text-xs text-slate-500 dark:text-slate-400 mt-2">
            {{ $t('events.client_wa_help', 'Use 62xxxx format (no +, no spaces).') }}
          </p>
          @error('client_whatsapp')<p class="text-xs text-red-600 mt-2">{{ $message }}</p>@enderror
        </div>
      </div>

      {{-- Event Type + Participants --}}
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-semibold text-slate-900 dark:text-white mb-2">
            {{ $t('events.event_type', 'Event Type') }}
          </label>

          <select name="event_type_choice" id="eventTypeChoice"
            class="w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-950/50 px-4 py-3 text-sm
                   focus:outline-none focus:ring-2 focus:ring-blue-500/40 dark:focus:ring-blue-500/25 focus:border-blue-500" required>
            <option value="" disabled {{ old('event_type_choice') ? '' : 'selected' }}>
              {{ $t('events.event_type_select', 'Select event type') }}
            </option>

            <option value="wedding"    {{ old('event_type_choice')==='wedding'?'selected':'' }}>Wedding</option>
            <option value="seminar"    {{ old('event_type_choice')==='seminar'?'selected':'' }}>Seminar</option>
            <option value="concert"    {{ old('event_type_choice')==='concert'?'selected':'' }}>Concert</option>
            <option value="corporate"  {{ old('event_type_choice')==='corporate'?'selected':'' }}>Corporate</option>
            <option value="graduation" {{ old('event_type_choice')==='graduation'?'selected':'' }}>Graduation</option>
            <option value="other"      {{ old('event_type_choice')==='other'?'selected':'' }}>Other</option>
          </select>
          @error('event_type_choice')<p class="text-xs text-red-600 mt-2">{{ $message }}</p>@enderror

          <div id="eventTypeOtherWrap" class="mt-3 {{ old('event_type_choice')==='other' ? '' : 'hidden' }}">
            <input name="event_type_other" value="{{ old('event_type_other') }}"
              class="w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-950/50 px-4 py-3 text-sm text-slate-900 dark:text-white"
              placeholder="Type your event type...">
            @error('event_type_other')<p class="text-xs text-red-600 mt-2">{{ $message }}</p>@enderror
          </div>
        </div>

        <div>
          <label class="block text-sm font-semibold text-slate-900 dark:text-white mb-2">
            {{ $t('events.participants', 'Number of Participants') }}
          </label>
          <input name="participants" type="number" min="1" value="{{ old('participants') }}"
            class="w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-950/50 px-4 py-3 text-sm text-slate-900 dark:text-white"
            placeholder="Enter number of participants" required>
          @error('participants')<p class="text-xs text-red-600 mt-2">{{ $message }}</p>@enderror
        </div>
      </div>

      {{-- Location (City) + Venue Type + Duration --}}
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

        {{-- LEFT --}}
        <div class="space-y-4">

          {{-- City --}}
          <div>
            <label class="block text-sm font-semibold text-slate-900 dark:text-white mb-2">
              {{ $t('events.location', 'Location (City)') }}
            </label>

            <select name="location_choice" id="locationChoice"
              class="w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-950/50 px-4 py-3 text-sm
                     focus:outline-none focus:ring-2 focus:ring-blue-500/40 dark:focus:ring-blue-500/25 focus:border-blue-500" required>
              <option value="" disabled {{ old('location_choice') ? '' : 'selected' }}>
                Select location
              </option>

              @foreach($locationOptions as $city)
                <option value="{{ $city }}" {{ old('location_choice')===$city ? 'selected' : '' }}>
                  {{ ucfirst($city) }}
                </option>
              @endforeach

              <option value="other" {{ old('location_choice')==='other'?'selected':'' }}>Other</option>
            </select>
            @error('location_choice')<p class="text-xs text-red-600 mt-2">{{ $message }}</p>@enderror

            <div id="locationOtherWrap" class="mt-3 {{ old('location_choice')==='other' ? '' : 'hidden' }}">
              <input name="location_other" value="{{ old('location_other') }}"
                class="w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-950/50 px-4 py-3 text-sm text-slate-900 dark:text-white"
                placeholder="Type location / venue...">
              @error('location_other')<p class="text-xs text-red-600 mt-2">{{ $message }}</p>@enderror
            </div>
          </div>

          {{-- Venue Type --}}
          <div>
            <label class="block text-sm font-semibold text-slate-900 dark:text-white mb-2">
              Venue Type
            </label>

            <div class="grid grid-cols-2 gap-3">
              <label class="flex items-center gap-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-950/50 px-4 py-3 text-sm cursor-pointer">
                <input type="radio" name="venue_type" value="indoor" class="accent-blue-600"
                       {{ $venueTypeOld === 'indoor' ? 'checked' : '' }}>
                <span class="font-semibold text-slate-900 dark:text-white">Indoor</span>
              </label>

              <label class="flex items-center gap-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-950/50 px-4 py-3 text-sm cursor-pointer">
                <input type="radio" name="venue_type" value="outdoor" class="accent-blue-600"
                       {{ $venueTypeOld === 'outdoor' ? 'checked' : '' }}>
                <span class="font-semibold text-slate-900 dark:text-white">Outdoor</span>
              </label>
            </div>

            <p class="text-xs text-slate-500 dark:text-slate-400 mt-2">
              Dipakai untuk rule <code class="px-1 rounded bg-slate-100 dark:bg-slate-800">venue_type = indoor/outdoor</code>.
            </p>
            @error('venue_type')<p class="text-xs text-red-600 mt-2">{{ $message }}</p>@enderror
          </div>

        </div>

        {{-- RIGHT: Duration --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-semibold text-slate-900 dark:text-white mb-2">
              Event Days
            </label>
            <input name="event_days" type="number" min="1" max="30"
              value="{{ old('event_days', 1) }}"
              class="w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-950/50 px-4 py-3 text-sm text-slate-900 dark:text-white" required>
            @error('event_days')<p class="text-xs text-red-600 mt-2">{{ $message }}</p>@enderror
          </div>

          <div>
            <label class="block text-sm font-semibold text-slate-900 dark:text-white mb-2">
              Hours / Day
            </label>
            <input name="hours_per_day" type="number" min="1" max="24"
              value="{{ old('hours_per_day', 3) }}"
              class="w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-950/50 px-4 py-3 text-sm text-slate-900 dark:text-white" required>
            @error('hours_per_day')<p class="text-xs text-red-600 mt-2">{{ $message }}</p>@enderror

            <p class="text-xs text-slate-500 dark:text-slate-400 mt-2">
              Contoh: 3 hari × 3 jam/hari
            </p>
          </div>
        </div>
      </div>

      {{-- Service Level --}}
      <div>
        <label class="block text-sm font-semibold text-slate-900 dark:text-white mb-2">
          {{ $t('events.service_level', 'Service Level') }}
        </label>
        <select name="service_level"
          class="w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-950/50 px-4 py-3 text-sm
                 focus:outline-none focus:ring-2 focus:ring-blue-500/40 dark:focus:ring-blue-500/25 focus:border-blue-500" required>
          <option value="" disabled {{ old('service_level') ? '' : 'selected' }}>
            Select service level
          </option>
          <option value="basic"    {{ old('service_level')==='basic'?'selected':'' }}>Basic</option>
          <option value="standard" {{ old('service_level')==='standard'?'selected':'' }}>Standard</option>
          <option value="premium"  {{ old('service_level')==='premium'?'selected':'' }}>Premium</option>
        </select>
        @error('service_level')<p class="text-xs text-red-600 mt-2">{{ $message }}</p>@enderror
      </div>

      {{-- Crew Override --}}
      <div class="rounded-xl border border-slate-200 dark:border-slate-800 p-5 bg-slate-50/50 dark:bg-slate-950/40">
        <div>
          <div class="text-sm font-bold text-slate-900 dark:text-white">Crew Override (Optional)</div>
          <div class="text-xs text-slate-500 dark:text-slate-400 mt-1">
            Kosongkan jika ingin otomatis dari rules. Isi jika mau override manual.
          </div>
        </div>

        <div class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-4">
          <div>
            <label class="block text-sm font-semibold text-slate-900 dark:text-white mb-2">Operator Qty</label>
            <input name="crew_operator_qty" type="number" min="0" value="{{ old('crew_operator_qty') }}"
              class="w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-950/50 px-4 py-3 text-sm"
              placeholder="(auto)">
            @error('crew_operator_qty')<p class="text-xs text-red-600 mt-2">{{ $message }}</p>@enderror
          </div>

          <div>
            <label class="block text-sm font-semibold text-slate-900 dark:text-white mb-2">Engineer Qty</label>
            <input name="crew_engineer_qty" type="number" min="0" value="{{ old('crew_engineer_qty') }}"
              class="w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-950/50 px-4 py-3 text-sm"
              placeholder="(auto)">
            @error('crew_engineer_qty')<p class="text-xs text-red-600 mt-2">{{ $message }}</p>@enderror
          </div>

          <div>
            <label class="block text-sm font-semibold text-slate-900 dark:text-white mb-2">Stagehand Qty</label>
            <input name="crew_stage_qty" type="number" min="0" value="{{ old('crew_stage_qty') }}"
              class="w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-950/50 px-4 py-3 text-sm"
              placeholder="(auto)">
            @error('crew_stage_qty')<p class="text-xs text-red-600 mt-2">{{ $message }}</p>@enderror
          </div>
        </div>
      </div>

      {{-- Special Requirements --}}
      <div>
        <label class="block text-sm font-semibold text-slate-900 dark:text-white mb-2">
          {{ $t('events.special_req', 'Special Requirements') }}
        </label>
        <textarea name="special_requirement" rows="4"
          class="w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-950/50 px-4 py-3 text-sm"
          placeholder="Enter any special requirements or notes">{{ old('special_requirement') }}</textarea>
        @error('special_requirement')<p class="text-xs text-red-600 mt-2">{{ $message }}</p>@enderror
      </div>

      <div class="pt-2 flex justify-end">
        <button type="submit"
          class="inline-flex items-center justify-center rounded-xl bg-blue-600 px-6 py-3 text-sm font-semibold text-white hover:bg-blue-700 shadow-sm">
          {{ $t('events.submit','Generate Estimation') }}
        </button>
      </div>
    </form>
  </div>
</div>

<script>
(function(){
  const eventType = document.getElementById('eventTypeChoice');
  const eventOther = document.getElementById('eventTypeOtherWrap');

  const loc = document.getElementById('locationChoice');
  const locOther = document.getElementById('locationOtherWrap');

  function toggle() {
    if (eventType && eventOther) eventOther.classList.toggle('hidden', eventType.value !== 'other');
    if (loc && locOther) locOther.classList.toggle('hidden', loc.value !== 'other');
  }

  if (eventType) eventType.addEventListener('change', toggle);
  if (loc) loc.addEventListener('change', toggle);
  toggle();
})();
</script>
@endsection