<?php

return [
  'title' => 'Dashboard',
  'subtitle' => 'Ringkasan sistem estimasi event',

  'actions' => [
    'new_estimation' => '+ Estimasi Baru',
    'history' => 'Riwayat',
  ],

  'cards' => [
    'total_estimations' => 'Total Estimasi',
    'pending_estimations' => 'Estimasi Pending',
    'approved_estimations' => 'Estimasi Disetujui',
    'inventory_title' => 'Total Inventory Equipment',
    'inventory_types' => ':count jenis alat',
    'accuracy_title' => 'Akurasi Estimasi',
    'accuracy_note' => 'Berdasarkan evaluasi pakar',
    'accuracy_formula' => 'Akurasi = evaluasi “Akurat” / estimasi terverifikasi',
  ],

  'table' => [
    'title' => 'Estimasi Terbaru',
    'event_name' => 'Event',
    'date' => 'Tanggal',
    'estimated_cost' => 'Estimasi Biaya',
    'status' => 'Status',
    'empty' => 'Belum ada estimasi. Klik “Estimasi Baru” untuk membuat.',
  ],

  'status' => [
    'pending' => 'Pending',
    'approved' => 'Disetujui',
    'rejected' => 'Ditolak',
    'revised' => 'Direvisi',
  ],
];