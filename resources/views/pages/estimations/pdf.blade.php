<!doctype html>
<html lang="{{ str_replace('_','-',app()->getLocale()) }}">
<head>
  <meta charset="utf-8">
  <title>{{ ($eventName ?? 'Estimation') }} - Estimation</title>

  <style>
    /* Menggunakan margin yang proporsional untuk ruang napas */
    @page { margin: 40px 50px; }

    body {
      font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
      font-size: 12px; /* Ukuran font ideal, tidak kekecilan */
      color: #1f2937;
      line-height: 1.5;
    }

    .text-blue { color: #2563eb; }
    .bg-light-blue { background-color: #eff6ff; }
    
    .muted { color: #6b7280; }
    .strong { font-weight: 700; color: #111827; }
    .num { text-align: right; white-space: nowrap; }

    /* Header & Title */
    .header-table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 30px;
    }

    .header-table td { vertical-align: top; }

    .logo-text {
      font-size: 42px;
      font-weight: 800;
      color: #2563eb; /* Biru Saldo/Wave */
      letter-spacing: -0.03em;
      line-height: 1;
      margin: 0;
    }

    .doc-title {
      font-size: 26px;
      font-weight: 700;
      color: #111827;
      margin: 0 0 5px 0;
    }

    .meta-text {
      font-size: 11px;
      color: #6b7280;
    }

    /* Event Info Block */
    .info-table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 30px;
    }

    .info-table td {
      vertical-align: top;
      width: 33.33%;
    }

    .info-label {
      font-size: 10px;
      font-weight: 700;
      text-transform: uppercase;
      color: #6b7280;
      letter-spacing: 0.05em;
      margin-bottom: 3px;
    }

    .info-value {
      font-size: 13px;
      font-weight: 600;
      color: #111827;
      padding-right: 20px;
    }

    /* Main Data Table (Gaya Referensi Saldo) */
    .table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 20px;
    }

    .table th {
      background-color: #2563eb; /* Header Biru Solid */
      color: #ffffff;
      text-align: left;
      font-size: 11px;
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: 0.05em;
      padding: 12px 10px;
    }

    .table td {
      padding: 14px 10px;
      vertical-align: top;
      border-bottom: 1px solid #e5e7eb;
      font-size: 12px;
    }

    /* Khusus Tampilan Mode Summary */
    .summary-desc-title {
      font-weight: 700;
      color: #111827;
      font-size: 14px;
      margin-bottom: 6px;
    }

    .summary-details {
      color: #4b5563;
      font-size: 11px;
      line-height: 1.6;
    }

    /* Totals & Notes Layout */
    .bottom-layout {
      width: 100%;
      border-collapse: collapse;
      margin-top: 20px;
    }

    .bottom-layout td { vertical-align: top; }

    .totals-table {
      width: 100%;
      border-collapse: collapse;
    }

    .totals-table td {
      padding: 8px 10px;
      font-size: 12px;
    }

    .total-row td {
      padding-top: 14px;
      padding-bottom: 14px;
      font-size: 16px;
      font-weight: 800;
      color: #111827;
      background-color: #f8fafc;
      border-top: 2px solid #2563eb; /* Garis aksen biru di atas total */
    }

    .notes-box {
      padding-right: 40px;
      font-size: 11px;
      color: #6b7280;
      line-height: 1.6;
    }

    /* Signatures */
    .signature-table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 50px;
      page-break-inside: avoid;
    }

    .signature-table td {
      width: 50%;
      vertical-align: bottom;
    }

    .signature-line {
      margin-top: 50px;
      width: 180px;
      border-bottom: 1px solid #111827;
      padding-bottom: 5px;
      font-weight: 700;
      font-size: 13px;
    }

    /* Footer (Gaya Referensi Wave) */
    .footer-block {
      background-color: #eff6ff; /* Biru sangat muda */
      padding: 15px;
      text-align: center;
      position: absolute;
      bottom: -40px; /* Menempel ke bawah margin */
      left: -50px;
      right: -50px;
      font-size: 10px;
      color: #6b7280;
      page-break-inside: avoid;
    }
  </style>
</head>

<body>
@php
  $bizName    = $bizName ?? 'Kira';
  $bizTagline = $bizTagline ?? 'Professional Multimedia Vendor';
  $bizLogo    = $bizLogo ?? null;

  $event = $estimation->event;
  $eventNameLocal = $eventName ?? ($event->event_name ?? $event->event_type ?? 'Event');

  $mode = $mode ?? 'detail'; // detail|summary

  $b = $breakdown ?? $estimation->breakdown;
  if (is_string($b)) $b = json_decode($b, true) ?: [];
  if (!is_array($b)) $b = [];

  $details = $estimation->details ?? collect();

  // Membuang item dengan qty 0
  $visibleDetails = $details->filter(function ($d) {
      return (int)($d->quantity ?? 0) > 0;
  })->values();

  // Setup Keywords Ringkasan
  $importantKeywords = [
      'Mixer', 'Speaker Line Array', 'Speaker Aktif', 'Speaker Passive',
      'Subwoofer', 'Mic Wireless', 'Mic Gooseneck', 'Mic Drum', 'Keyboard',
      'Drum Set', 'Gitar Listrik', 'Bass Listrik', 'Stage Box', 'Monitor',
      'Genset', 'Intercom', 'Walkie',
  ];

  $importantEquipment = $visibleDetails
      ->filter(function ($d) use ($importantKeywords) {
          $name = (string)($d->equipment_name ?? '');
          foreach ($importantKeywords as $keyword) {
              if (stripos($name, $keyword) !== false) { return true; }
          }
          return false;
      })
      ->pluck('equipment_name')->unique()->take(12)->values()->all();

  if (count($importantEquipment) === 0) {
      $importantEquipment = $visibleDetails->pluck('equipment_name')->filter()->unique()->take(10)->values()->all();
  }

  $equipmentInline = implode(', ', $importantEquipment);
  $hasMoreEquipment = $visibleDetails->count() > count($importantEquipment);

  $eventTypeLabel = ucfirst((string)($event->event_type ?? 'Event'));
  $locationLabel = ucfirst((string)($event->location ?? '-'));
  $serviceLevelLabel = ucfirst((string)($event->service_level ?? '-'));

  $participants = (int)($event->participants ?? 0);
  $eventDays = (int)($event->event_days ?? 1);
  $hoursPerDay = (int)($event->hours_per_day ?? 1);

  $summaryDescription = 'Paket Sound System & Multimedia';
  if (!empty($event->event_type)) { $summaryDescription .= ' untuk ' . $eventTypeLabel; }
  
  $supportText = 'termasuk kabel, power, aksesoris, teknis pendukung, setup, dan operasional crew sesuai kebutuhan';
@endphp

  {{-- TOP HEADER --}}
  <table class="header-table">
    <tr>
      <td style="width: 50%;">
        @if($bizLogo)
          <img src="{{ public_path($bizLogo) }}" alt="Logo" style="max-height: 55px;">
        @else
          <div class="logo-text">{{ $bizName }}</div>
        @endif
        <div class="meta-text" style="margin-top: 5px;">{{ $bizTagline }}</div>
      </td>
      <td style="width: 50%; text-align: right;">
        <div class="doc-title">{{ $mode === 'summary' ? 'Quotation' : 'Estimation' }}</div>
        
        <table style="width: 100%; margin-top: 10px;">
          <tr>
            <td style="text-align: right; padding-right: 15px;" class="meta-text">Ref Number:</td>
            <td style="text-align: right; width: 100px;" class="strong">#{{ str_pad($estimation->id, 5, '0', STR_PAD_LEFT) }}</td>
          </tr>
          <tr>
            <td style="text-align: right; padding-right: 15px;" class="meta-text">Date Issued:</td>
            <td style="text-align: right;" class="strong">{{ optional($estimation->created_at)->format('M d, Y') }}</td>
          </tr>
        </table>
      </td>
    </tr>
  </table>

  {{-- EVENT INFORMATION --}}
  <div style="margin-bottom: 25px;">
    <div class="info-label text-blue">Event Details</div>
    <div style="font-size: 18px; font-weight: 700; color: #111827; margin-bottom: 15px;">{{ $eventNameLocal }}</div>
    
    <table class="info-table">
      <tr>
        <td>
          <div class="info-label">Location</div>
          <div class="info-value">{{ $locationLabel }}</div>
        </td>
        <td>
          <div class="info-label">Duration</div>
          <div class="info-value">{{ $eventDays }} Day(s)</div>
        </td>
        <td>
          <div class="info-label">Scale & Service</div>
          <div class="info-value">{{ number_format($participants) }} Pax &mdash; {{ $serviceLevelLabel }}</div>
        </td>
      </tr>
    </table>
  </div>

  @if($mode === 'summary')
    {{-- SUMMARY MODE --}}
    <table class="table">
      <thead>
        <tr>
          <th style="width: 60%;">Description</th>
          <th class="num" style="width: 15%;">Qty</th>
          <th class="num" style="width: 25%;">Amount</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td>
            <div class="summary-desc-title">{{ $summaryDescription }}</div>
            <div class="summary-details">
              <strong>Spesifikasi Utama:</strong> 
              @if(!empty($equipmentInline))
                {{ $equipmentInline }}@if($hasMoreEquipment), {{ $supportText }}@endif.
              @else
                Paket sound system lengkap, {{ $supportText }}.
              @endif
              <br><br>
              <em>*Durasi operasional {{ $hoursPerDay }} jam/hari. Penawaran sudah termasuk biaya teknis.</em>
            </div>
          </td>
          <td class="num strong" style="font-size: 14px;">1 Lot</td>
          <td class="num strong" style="font-size: 14px;">
            Rp {{ number_format((int)($estimation->total_cost ?? 0),0,',','.') }}
          </td>
        </tr>
      </tbody>
    </table>

    <table class="bottom-layout">
      <tr>
        <td style="width: 60%;" class="notes-box">
          <div class="strong" style="color: #111827; margin-bottom: 5px;">Payment Instruction & Notes</div>
          Harga ini adalah estimasi awal (Quotation) berdasarkan diskusi kebutuhan event. Harga dapat disesuaikan kembali apabila terdapat perubahan durasi, layout panggung, atau tambahan equipment di lokasi acara.
        </td>
        <td style="width: 40%;">
          <table class="totals-table">
            <tr class="total-row">
              <td>Balance Due:</td>
              <td class="num text-blue">Rp {{ number_format((int)($estimation->total_cost ?? 0),0,',','.') }}</td>
            </tr>
          </table>
        </td>
      </tr>
    </table>

  @else
    {{-- DETAIL MODE --}}
    <table class="table">
      <thead>
        <tr>
          <th style="width: 45%;">Description</th>
          <th class="num" style="width: 10%;">Qty</th>
          <th class="num" style="width: 20%;">Rate</th>
          <th class="num" style="width: 25%;">Amount</th>
        </tr>
      </thead>
      <tbody>
        @foreach($visibleDetails as $d)
          <tr>
            <td class="strong">{{ $d->equipment_name }}</td>
            <td class="num">{{ (int)$d->quantity }}</td>
            <td class="num muted">Rp {{ number_format((int)$d->price,0,',','.') }}</td>
            <td class="num strong">Rp {{ number_format((int)$d->total,0,',','.') }}</td>
          </tr>
        @endforeach
      </tbody>
    </table>

    <table class="bottom-layout">
      <tr>
        <td style="width: 55%;" class="notes-box">
          <div class="strong" style="color: #111827; margin-bottom: 5px;">System Notes</div>
          Rincian item di atas di-generate otomatis oleh sistem KIRA. Ketersediaan stok dan harga final akan dikonfirmasi ulang pada saat penyusunan invoice resmi.
        </td>
        <td style="width: 45%;">
          <table class="totals-table">
            <tr>
              <td class="muted">Subtotal Equipment</td>
              <td class="num strong">Rp {{ number_format((int)($b['equipment'] ?? 0),0,',','.') }}</td>
            </tr>
            <tr>
              <td class="muted">Labor & Crew</td>
              <td class="num strong">Rp {{ number_format((int)($b['labor'] ?? 0),0,',','.') }}</td>
            </tr>
            <tr>
              <td class="muted">Transportation</td>
              <td class="num strong">Rp {{ number_format((int)($b['transport'] ?? 0),0,',','.') }}</td>
            </tr>
            <tr>
              <td class="muted">Operational Fee</td>
              <td class="num strong">Rp {{ number_format((int)($b['operational'] ?? 0),0,',','.') }}</td>
            </tr>
            @if(isset($b['markup']))
              <tr>
                <td class="muted">Margin / Markup</td>
                <td class="num strong">Rp {{ number_format((int)($b['markup'] ?? 0),0,',','.') }}</td>
              </tr>
            @endif
            <tr class="total-row">
              <td>Total Amount:</td>
              <td class="num text-blue">Rp {{ number_format((int)($estimation->total_cost ?? 0),0,',','.') }}</td>
            </tr>
          </table>
        </td>
      </tr>
    </table>
  @endif

  {{-- SIGNATURE AREA --}}
  <table class="signature-table">
    <tr>
      <td>
        <div class="muted" style="font-size: 11px;">Prepared by</div>
        <div class="signature-line">{{ $bizName }} Representative</div>
      </td>
      <td style="text-align: right;">
        <div class="muted" style="font-size: 11px; padding-right: 25px;">Acknowledged by</div>
        <div class="signature-line" style="margin-left: auto; margin-right: 0;">Authorized Client</div>
      </td>
    </tr>
  </table>

  {{-- BLUE FOOTER BLOCK (Gaya Referensi Wave) --}}
  <div class="footer-block">
    <strong class="text-blue">{{ $bizName }}</strong> &mdash; This document was securely generated by KIRA Event Multimedia Decision Support System.
  </div>

</body>
</html>