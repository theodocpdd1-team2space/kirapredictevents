<?php

return [
  'title' => 'Biaya & Tarif',
  'subtitle' => 'Atur tarif standar tanpa mengubah kode inference engine.',

  'general' => 'Umum',
  'currency' => 'Mata Uang',
  'currency_help' => 'Saat ini hanya label tampilan (tanpa konversi kurs).',

  'operational_percent' => 'Operasional %',
  'operational_help' => 'Contoh: 5 berarti 5% dari total equipment.',

  'markup_percent' => 'Markup %',
  'markup_help' => 'Opsional. Contoh: 20 berarti tambah 20% dari total.',

  'labor' => 'Tarif Crew Dasar (per Tier Peserta)',
  'labor_help' => 'Tarif crew dasar per blok durasi.',

  'tier1' => 'Tier 1 (0–100 pax)',
  'tier2' => 'Tier 2 (101–300 pax)',
  'tier3' => 'Tier 3 (301–1000 pax)',
  'tier4' => 'Tier 4 (1001+ pax)',

  'transport' => 'Transport',
  'transport_help' => 'Free cities + custom rate per kota + fallback.',

  'transport_default' => 'Transport Default',
  'transport_default_help' => 'Dipakai jika kota tidak free/custom.',

  'transport_outdoor' => 'Transport Outdoor',
  'transport_outdoor_help' => 'Tambahan khusus bila event outdoor (opsional logic di engine).',

  'free_cities' => 'Kota Gratis (pisahkan dengan koma)',
  'free_cities_help' => 'Contoh: surabaya,sidoarjo,gresik',

  'other_city_default' => 'Tarif Default Kota Lain',
  'other_city_default_help' => 'Fallback kalau kota tidak ada di free/custom.',

  'custom_city_rates' => 'Tarif Kota Custom',
  'custom_city_rates_help' => 'Isi kota + tarif (contoh: jakarta = 1000000).',

  'add_city' => 'Tambah Kota',
  'remove' => 'Hapus',

  'save' => 'Simpan Perubahan',
];