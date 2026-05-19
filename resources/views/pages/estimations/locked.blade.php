{{-- resources/views/pages/estimations/locked.blade.php --}}

@extends('layouts.app-shell')

@section('title', 'Estimasi Belum Bisa Dibuat')

@section('content')
<div class="mx-auto flex min-h-[70vh] max-w-4xl items-center justify-center px-4 py-10">
    <div class="w-full overflow-hidden rounded-[28px] border border-amber-200 bg-white shadow-xl shadow-amber-500/10 dark:border-amber-500/20 dark:bg-slate-900">
        <div class="relative overflow-hidden bg-gradient-to-br from-amber-50 via-white to-blue-50 px-6 py-10 text-center dark:from-amber-500/10 dark:via-slate-900 dark:to-blue-500/10 sm:px-10">
            <div class="absolute -right-20 -top-20 h-52 w-52 rounded-full bg-amber-300/20 blur-3xl"></div>
            <div class="absolute -left-20 -bottom-20 h-52 w-52 rounded-full bg-blue-300/20 blur-3xl"></div>

            <div class="relative mx-auto flex h-20 w-20 items-center justify-center rounded-3xl bg-amber-100 text-4xl shadow-inner dark:bg-amber-500/15">
                ⚠️
            </div>

            <h1 class="relative mt-6 text-3xl font-extrabold tracking-tight text-slate-900 dark:text-white">
                Oops, estimasi belum bisa dibuat
            </h1>

            <p class="relative mx-auto mt-3 max-w-2xl text-sm leading-7 text-slate-600 dark:text-slate-300">
                Inventory dan rules masih kosong atau belum lengkap. Silakan isi terlebih dahulu agar sistem
                memiliki dasar perhitungan sebelum membuat estimasi biaya event.
            </p>

            <div class="relative mt-8 grid gap-4 sm:grid-cols-2">
                <div class="rounded-2xl border border-slate-200 bg-white/80 p-5 text-left shadow-sm backdrop-blur dark:border-slate-700 dark:bg-slate-900/70">
                    <p class="text-xs font-bold uppercase tracking-[0.2em] text-slate-400">
                        Inventory
                    </p>

                    <div class="mt-3 flex items-end justify-between">
                        <p class="text-4xl font-black text-slate-900 dark:text-white">
                            {{ number_format($inventoryCount ?? 0) }}
                        </p>

                        @if(($inventoryCount ?? 0) > 0)
                            <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-bold text-emerald-700">
                                Ready
                            </span>
                        @else
                            <span class="rounded-full bg-rose-100 px-3 py-1 text-xs font-bold text-rose-700">
                                Kosong
                            </span>
                        @endif
                    </div>

                    <p class="mt-3 text-sm text-slate-500 dark:text-slate-400">
                        Data alat dibutuhkan untuk menghitung kebutuhan, harga, stok tersedia, dan shortage.
                    </p>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-white/80 p-5 text-left shadow-sm backdrop-blur dark:border-slate-700 dark:bg-slate-900/70">
                    <p class="text-xs font-bold uppercase tracking-[0.2em] text-slate-400">
                        Rules
                    </p>

                    <div class="mt-3 flex items-end justify-between">
                        <p class="text-4xl font-black text-slate-900 dark:text-white">
                            {{ number_format($ruleCount ?? 0) }}
                        </p>

                        @if(($ruleCount ?? 0) > 0)
                            <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-bold text-emerald-700">
                                Ready
                            </span>
                        @else
                            <span class="rounded-full bg-rose-100 px-3 py-1 text-xs font-bold text-rose-700">
                                Kosong
                            </span>
                        @endif
                    </div>

                    <p class="mt-3 text-sm text-slate-500 dark:text-slate-400">
                        Rules dibutuhkan agar inference engine dapat menentukan rekomendasi estimasi.
                    </p>
                </div>
            </div>

            <div class="relative mt-8 flex flex-col justify-center gap-3 sm:flex-row">
                <a href="{{ route('inventories.index') }}"
                   class="inline-flex items-center justify-center rounded-2xl bg-slate-900 px-6 py-3 text-sm font-bold text-white shadow-lg shadow-slate-900/20 transition hover:-translate-y-0.5 hover:bg-slate-800 dark:bg-white dark:text-slate-900">
                    Isi Inventory
                </a>

                <a href="{{ route('settings.rules.index') }}"
                   class="inline-flex items-center justify-center rounded-2xl border border-slate-300 bg-white px-6 py-3 text-sm font-bold text-slate-700 shadow-sm transition hover:-translate-y-0.5 hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800">
                    Buat Rules
                </a>
            </div>

            <p class="relative mt-6 text-xs font-medium text-slate-400">
                Setelah inventory dan rules tersedia, tombol Buat Estimasi akan otomatis aktif. Cheers!
            </p>
        </div>
    </div>
</div>
@endsection