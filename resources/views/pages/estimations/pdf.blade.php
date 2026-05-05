<!doctype html>
<html lang="{{ str_replace('_','-',app()->getLocale()) }}">
<head>
  <meta charset="utf-8">
  <title>{{ ($eventName ?? 'Estimation') }} - Estimation</title>

  <style>
    @page { margin: 22px 30px; }

    body {
      font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
      font-size: 11px;
      color: #111827;
      line-height: 1.35;
    }

    .muted { color: #64748b; }
    .small { font-size: 10px; }
    .xs { font-size: 9px; }
    .strong { font-weight: 700; color: #0f172a; }
    .num { text-align: right; white-space: nowrap; }

    .h1 {
      font-size: 19px;
      font-weight: 800;
      margin: 0;
      color: #0f172a;
      line-height: 1.2;
    }

    .h2 {
      font-size: 12px;
      font-weight: 800;
      margin: 0 0 8px;
      color: #334155;
      text-transform: uppercase;
      letter-spacing: 0.04em;
    }

    .topbar-table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 9px;
    }

    .topbar-table td {
      vertical-align: middle;
    }

    .logo-box {
      width: 46px;
      height: 46px;
      border: 1px solid #e2e8f0;
      border-radius: 8px;
      text-align: center;
      vertical-align: middle;
      background: #f8fafc;
    }

    .logo-box img {
      max-width: 37px;
      max-height: 37px;
    }

    .right-meta {
      text-align: right;
      white-space: nowrap;
    }

    .badge {
      display: inline-block;
      border: 1px solid #cbd5e1;
      border-radius: 999px;
      padding: 3px 10px;
      font-size: 9px;
      font-weight: 700;
      color: #334155;
      background: #f1f5f9;
      text-transform: uppercase;
      letter-spacing: 0.05em;
    }

    .card {
      border: 1px solid #cbd5e1;
      border-radius: 8px;
      padding: 13px;
      margin-top: 10px;
      background-color: #ffffff;
    }

    .title-card {
      background-color: #f8fafc;
      border-left: 4px solid #2563eb;
    }

    .grid-table {
      width: 100%;
      border-collapse: collapse;
    }

    .grid-table td {
      padding: 5px 10px 5px 0;
      vertical-align: top;
      width: 33.33%;
    }

    .kv-title {
      font-size: 9px;
      font-weight: 800;
      letter-spacing: .04em;
      color: #64748b;
      text-transform: uppercase;
      margin-bottom: 1px;
    }

    .kv-value {
      font-weight: 700;
      font-size: 11px;
      color: #0f172a;
    }

    .table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 5px;
    }

    .table th {
      text-align: left;
      font-size: 9px;
      letter-spacing: .05em;
      text-transform: uppercase;
      color: #334155;
      background: #e2e8f0;
      border: 1px solid #94a3b8;
      padding: 8px 7px;
    }

    .table td {
      border: 1px solid #cbd5e1;
      padding: 8px 7px;
      vertical-align: top;
    }

    .table tr:nth-child(even) td {
      background-color: #f8fafc;
    }

    .summary-quotation-table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 4px;
      font-size: 11px;
    }

    .summary-quotation-table th {
      background: #d9e2f3;
      border: 1px solid #7f8ea3;
      color: #111827;
      font-size: 10px;
      font-weight: 800;
      padding: 8px 7px;
      text-align: center;
      text-transform: uppercase;
      letter-spacing: .03em;
    }

    .summary-quotation-table td {
      border: 1px solid #7f8ea3;
      padding: 9px 8px;
      vertical-align: top;
    }

    .summary-desc-title {
      font-weight: 800;
      color: #0f172a;
      margin-bottom: 4px;
      font-size: 12px;
    }

    .equipment-inline {
      margin-top: 6px;
      font-size: 10px;
      color: #334155;
      line-height: 1.36;
    }

    .package-pill {
      display: inline-block;
      padding: 3px 8px;
      border-radius: 999px;
      border: 1px solid #cbd5e1;
      background: #f8fafc;
      font-size: 9px;
      font-weight: 700;
      color: #334155;
      margin-top: 7px;
    }

    .total-box {
      width: 100%;
      border-collapse: collapse;
      margin-top: 9px;
    }

    .total-box td {
      padding: 7px 9px;
      border: 1px solid #94a3b8;
    }

    .total-label {
      background: #f1f5f9;
      font-weight: 800;
      text-align: right;
      color: #0f172a;
    }

    .total-value {
      font-size: 14px;
      font-weight: 900;
      text-align: right;
      color: #0f172a;
      white-space: nowrap;
    }

    .layout-totals {
      width: 100%;
      border-collapse: collapse;
    }

    .layout-totals td {
      vertical-align: top;
    }

    .summary-table {
      width: 100%;
      border-collapse: collapse;
    }

    .summary-table td {
      padding: 7px 7px;
      border-bottom: 1px solid #e2e8f0;
    }

    .summary-table tr:last-child td {
      border-bottom: none;
    }

    .total-row td {
      padding-top: 10px;
      padding-bottom: 4px;
      font-size: 13px;
      font-weight: 900;
      color: #0f172a;
      border-top: 2px solid #cbd5e1;
    }

    .note-box {
      background: #f8fafc;
      border: 1px dashed #cbd5e1;
      border-radius: 8px;
      padding: 9px 10px;
      margin-top: 9px;
      color: #475569;
      font-size: 10px;
      line-height: 1.45;
    }

    .signature-table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 8px;
      page-break-inside: avoid;
    }

    .signature-table td {
      width: 50%;
      vertical-align: top;
      text-align: center;
      padding-top: 6px;
      color: #334155;
    }

    .signature-line {
      margin: 25px auto 0;
      width: 165px;
      border-top: 1px solid #64748b;
      padding-top: 5px;
      font-weight: 700;
      color: #0f172a;
    }

    .footer {
      margin-top: 11px;
      font-size: 9px;
      color: #94a3b8;
      text-align: center;
      border-top: 1px dashed #cbd5e1;
      padding-top: 8px;
    }
  </style>
</head>

<body>
@php
  $bizName    = $bizName ?? 'Kira';
  $bizTagline = $bizTagline ?? 'Event Multimedia DSS';
  $bizLogo    = $bizLogo ?? null;

  $event = $estimation->event;
  $eventNameLocal = $eventName ?? ($event->event_name ?? $event->event_type ?? 'Event');

  $mode = $mode ?? 'detail'; // detail|summary

  $b = $breakdown ?? $estimation->breakdown;
  if (is_string($b)) $b = json_decode($b, true) ?: [];
  if (!is_array($b)) $b = [];

  $details = $estimation->details ?? collect();

  // Final PDF: item dengan quantity 0 tidak ditampilkan.
  $visibleDetails = $details->filter(function ($d) {
      return (int)($d->quantity ?? 0) > 0;
  })->values();

  // Untuk PDF Ringkas: tampilkan item utama saja, bukan semua item kecil.
  $importantKeywords = [
      'Mixer',
      'Speaker Line Array',
      'Speaker Aktif',
      'Speaker Passive',
      'Subwoofer',
      'Mic Wireless',
      'Mic Gooseneck',
      'Mic Drum',
      'Keyboard',
      'Drum Set',
      'Gitar Listrik',
      'Bass Listrik',
      'Stage Box',
      'Monitor',
      'Genset',
      'Intercom',
      'Walkie',
  ];

  $importantEquipment = $visibleDetails
      ->filter(function ($d) use ($importantKeywords) {
          $name = (string)($d->equipment_name ?? '');

          foreach ($importantKeywords as $keyword) {
              if (stripos($name, $keyword) !== false) {
                  return true;
              }
          }

          return false;
      })
      ->pluck('equipment_name')
      ->unique()
      ->take(12)
      ->values()
      ->all();

  // Fallback kalau tidak ada keyword cocok.
  if (count($importantEquipment) === 0) {
      $importantEquipment = $visibleDetails
          ->pluck('equipment_name')
          ->filter()
          ->unique()
          ->take(10)
          ->values()
          ->all();
  }

  $equipmentInline = implode(', ', $importantEquipment);
  $hasMoreEquipment = $visibleDetails->count() > count($importantEquipment);

  $eventTypeLabel = ucfirst((string)($event->event_type ?? 'Event'));
  $locationLabel = ucfirst((string)($event->location ?? '-'));
  $serviceLevelLabel = ucfirst((string)($event->service_level ?? '-'));

  $participants = (int)($event->participants ?? 0);
  $eventDays = (int)($event->event_days ?? 1);
  $hoursPerDay = (int)($event->hours_per_day ?? 1);

  $summaryDescription = 'Paket Sound System';
  if (!empty($event->event_type)) {
      $summaryDescription .= ' untuk ' . $eventTypeLabel;
  }
  if (!empty($eventNameLocal)) {
      $summaryDescription .= ' - ' . $eventNameLocal;
  }

  $supportText = 'termasuk kabel, power, aksesoris, kebutuhan teknis pendukung, setup, dan operasional crew sesuai kebutuhan acara';
@endphp

  {{-- HEADER --}}
  <table class="topbar-table">
    <tr>
      <td style="width:58px;">
        <div class="logo-box">
          @if($bizLogo)
            <img src="{{ public_path($bizLogo) }}" alt="Logo">
          @else
            <span class="muted small" style="display:block; margin-top:14px;">Logo</span>
          @endif
        </div>
      </td>

      <td>
        <div style="margin-left:9px;">
          <div class="strong" style="font-size:15px;">{{ $bizName }}</div>
          <div class="muted small">{{ $bizTagline }}</div>
          <div style="margin-top:5px;">
            <span class="badge">
              {{ $mode === 'summary' ? 'PENAWARAN RINGKAS' : 'ESTIMATION DETAIL' }}
            </span>
          </div>
        </div>
      </td>

      <td class="right-meta">
        <div class="muted small">Estimation ID</div>
        <div class="strong" style="font-size: 13px;">#{{ $estimation->id }}</div>
        <div style="height:6px;"></div>
        <div class="muted small">Date</div>
        <div class="strong">{{ optional($estimation->created_at)->format('Y-m-d H:i') }}</div>
      </td>
    </tr>
  </table>

  {{-- TITLE --}}
  <div class="card title-card">
    <div class="h1">{{ $eventNameLocal }}</div>
    <div class="muted" style="margin-top:3px;">
      @if($mode === 'summary')
        Ringkasan penawaran biaya paket event berdasarkan kebutuhan acara.
      @else
        Dokumen ini dihasilkan otomatis oleh sistem sebagai estimasi biaya awal.
      @endif
    </div>
  </div>

  {{-- EVENT INFO --}}
  <div class="card">
    <div class="h2">Event Information</div>

    <table class="grid-table">
      <tr>
        <td>
          <div class="kv-title">Event Type</div>
          <div class="kv-value">{{ $eventTypeLabel }}</div>
        </td>
        <td>
          <div class="kv-title">Location</div>
          <div class="kv-value">{{ $locationLabel }}</div>
        </td>
        <td>
          <div class="kv-title">Service Level</div>
          <div class="kv-value">{{ $serviceLevelLabel }}</div>
        </td>
      </tr>
      <tr>
        <td>
          <div class="kv-title">Participants</div>
          <div class="kv-value">{{ number_format($participants) }}</div>
        </td>
        <td>
          <div class="kv-title">Duration</div>
          <div class="kv-value">
            {{ $eventDays }} day(s) × {{ $hoursPerDay }} h/day
            <span class="muted small">({{ (int)($event->duration ?? 0) }} hours)</span>
          </div>
        </td>
        <td>
          <div class="kv-title">Special Requirements</div>
          <div class="kv-value">
            @if($mode === 'summary')
              {{ \Illuminate\Support\Str::limit($event->special_requirement ?: '-', 90) }}
            @else
              {{ $event->special_requirement ?: '-' }}
            @endif
          </div>
        </td>
      </tr>
    </table>
  </div>

  @if($mode === 'summary')
    {{-- SUMMARY QUOTATION --}}
    <div class="card" style="padding: 13px;">
      <div class="h2">Ringkasan Penawaran</div>

      <table class="summary-quotation-table">
        <thead>
          <tr>
            <th style="width: 7%;">No</th>
            <th style="width: 57%;">Deskripsi</th>
            <th style="width: 16%;">Jumlah</th>
            <th style="width: 20%;">Harga</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td style="text-align:center; font-weight:800;">1</td>
            <td>
              <div class="summary-desc-title">{{ $summaryDescription }}</div>

              <div class="small muted">
                Kapasitas {{ number_format($participants) }} peserta,
                durasi {{ $eventDays }} hari × {{ $hoursPerDay }} jam/hari,
                lokasi {{ $locationLabel }},
                layanan {{ $serviceLevelLabel }}.
              </div>

              @if(!empty($equipmentInline))
                <div class="equipment-inline">
                  <span class="strong">Rincian utama:</span>
                  {{ $equipmentInline }}@if($hasMoreEquipment), {{ $supportText }}@endif.
                </div>
              @else
                <div class="equipment-inline">
                  <span class="strong">Rincian utama:</span>
                  Paket sound system, {{ $supportText }}.
                </div>
              @endif

              <div class="package-pill">1 paket sound system & technical support</div>
            </td>
            <td style="text-align:center; font-weight:800; vertical-align:middle;">
              1 Paket
            </td>
            <td class="num strong" style="vertical-align:middle; font-size:12px;">
              Rp {{ number_format((int)($estimation->total_cost ?? 0),0,',','.') }}
            </td>
          </tr>
        </tbody>
      </table>

      <table class="total-box">
        <tr>
          <td class="total-label" style="width: 78%;">TOTAL PENAWARAN</td>
          <td class="total-value" style="width: 22%;">
            Rp {{ number_format((int)($estimation->total_cost ?? 0),0,',','.') }}
          </td>
        </tr>
      </table>

      <div class="note-box">
        <strong>Catatan:</strong>
        Harga merupakan estimasi paket awal dan dapat berubah apabila terdapat revisi kebutuhan acara,
        perubahan durasi, lokasi, penambahan equipment, atau kebutuhan teknis tambahan di lapangan.
      </div>
    </div>

    {{-- SIMPLE SIGNATURE --}}
    <table class="signature-table">
      <tr>
        <td>
          <div class="muted small">Disiapkan oleh,</div>
          <div class="signature-line">{{ $bizName }}</div>
        </td>
        <td>
          <div class="muted small">Disetujui oleh,</div>
          <div class="signature-line">Client</div>
        </td>
      </tr>
    </table>
  @else
    {{-- DETAIL MODE: EQUIPMENT LIST --}}
    <div class="card" style="padding: 16px 0;">
      <div style="padding: 0 16px;">
        <div class="h2">Equipment List</div>
        <div class="muted small" style="margin-bottom: 10px;">{{ $visibleDetails->count() }} item(s)</div>
      </div>

      <table class="table">
        <thead>
          <tr>
            <th style="width:42%; padding-left: 16px;">Item</th>
            <th class="num" style="width:10%;">Qty</th>
            <th class="num" style="width:18%;">Unit Price</th>
            <th class="num" style="width:18%;">Total</th>
            <th class="num" style="width:12%; padding-right: 16px;">Shortage</th>
          </tr>
        </thead>
        <tbody>
          @foreach($visibleDetails as $d)
            <tr>
              <td class="strong" style="padding-left: 16px;">{{ $d->equipment_name }}</td>
              <td class="num">{{ (int)$d->quantity }}</td>
              <td class="num muted">Rp {{ number_format((int)$d->price,0,',','.') }}</td>
              <td class="num strong">Rp {{ number_format((int)$d->total,0,',','.') }}</td>
              <td class="num" style="padding-right: 16px;">{{ (int)$d->shortage }}</td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>

    {{-- COST BREAKDOWN --}}
    <div class="card">
      <table class="layout-totals">
        <tr>
          <td style="width: 50%; padding-right: 20px;">
            <div class="h2">Note</div>
            <div class="muted small" style="line-height: 1.5;">
              Dokumen ini bersifat estimasi awal dan dapat berubah sesuai revisi kebutuhan event.
            </div>
          </td>

          <td style="width: 50%;">
            <div class="h2" style="text-align: right;">Cost Breakdown</div>
            <table class="summary-table">
              <tr>
                <td class="muted">Equipment</td>
                <td class="num strong">Rp {{ number_format((int)($b['equipment'] ?? 0),0,',','.') }}</td>
              </tr>
              <tr>
                <td class="muted">Labor</td>
                <td class="num strong">Rp {{ number_format((int)($b['labor'] ?? 0),0,',','.') }}</td>
              </tr>
              <tr>
                <td class="muted">Transport</td>
                <td class="num strong">Rp {{ number_format((int)($b['transport'] ?? 0),0,',','.') }}</td>
              </tr>
              <tr>
                <td class="muted">Operational</td>
                <td class="num strong">Rp {{ number_format((int)($b['operational'] ?? 0),0,',','.') }}</td>
              </tr>
              @if(isset($b['markup']))
                <tr>
                  <td class="muted">Markup</td>
                  <td class="num strong">Rp {{ number_format((int)($b['markup'] ?? 0),0,',','.') }}</td>
                </tr>
              @endif
              <tr class="total-row">
                <td>Total</td>
                <td class="num">Rp {{ number_format((int)($estimation->total_cost ?? 0),0,',','.') }}</td>
              </tr>
            </table>
          </td>
        </tr>
      </table>
    </div>
  @endif

  <div class="footer">
    &copy; {{ date('Y') }} {{ $bizName }}. All rights reserved.
  </div>
</body>
</html>