@php
  $isOwner = auth()->user()?->isOwner();

  $tabBase = "inline-flex items-center gap-2 rounded-lg px-4 py-2 text-sm font-semibold border transition-colors";
  $tab = $tabBase." border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-800";
  $activeTab = $tabBase." border-blue-200 dark:border-blue-500/30 bg-blue-50 dark:bg-blue-500/10 text-blue-800 dark:text-blue-200";
@endphp

<div class="flex flex-wrap items-center gap-2">
  @if($isOwner)
    <a href="{{ route('settings.staff.index') }}"
       class="{{ request()->routeIs('settings.staff.*') ? $activeTab : $tab }}">
      Staff
    </a>

    <a href="{{ route('settings.rules.index') }}"
       class="{{ request()->routeIs('settings.rules.*') ? $activeTab : $tab }}">
      Rules
    </a>

    <a href="{{ route('settings.business.edit') }}"
       class="{{ request()->routeIs('settings.business.*') ? $activeTab : $tab }}">
      Business Info
    </a>

    <a href="{{ route('settings.cost.edit') }}"
       class="{{ request()->routeIs('settings.cost.*') ? $activeTab : $tab }}">
      Cost & Rates
    </a>
  @endif

  <a href="{{ route('settings.language.edit') }}"
     class="{{ request()->routeIs('settings.language.*') ? $activeTab : $tab }}">
    Language
  </a>

  <a href="{{ route('settings.theme.edit') }}"
     class="{{ request()->routeIs('settings.theme.*') ? $activeTab : $tab }}">
    Theme
  </a>

  <a href="{{ route('settings.about') }}"
     class="{{ request()->routeIs('settings.about') ? $activeTab : $tab }}">
    Tentang Kira
  </a>
</div>