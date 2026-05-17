<!doctype html>
<html lang="{{ str_replace('_','-',app()->getLocale()) }}">
<head>
  <meta charset="utf-8">
  <title>{{ ($eventName ?? 'Estimation') }} - Estimation</title>

  <style>
    @page { margin: 40px 50px; }

    body {
      font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
      font-size: 12px;
      color: #1f2937;
      line-height: 1.5;
    }

    .text-blue { color: #2563eb; }
    .bg-light-blue { background-color: #eff6ff; }

    .muted { color: #6b7280; }
    .strong { font-weight: 700; color: #111827; }
    .num { text-align: right; white-space: nowrap; }

    .header-table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 30px;
    }

    .header-table td { vertical-align: top; }

    .logo-text {
      font-size: 42px;
      font-weight: 800;
      color: #2563eb;
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

    .table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 20px;
    }

    .table th {
      background-color: #2563eb;
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
      border-top: 2px solid #2563eb;
    }

    .notes-box {
      padding-right: 40px;
      font-size: 11px;
      color: #6b7280;
      line-height: 1.6;
    }

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

    .footer-block {
      background-color: #eff6ff;
      padding: 15px;
      text-align: center;
      position: absolute;
      bottom: -40px;
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

  $visibleDetails = $details->filter(function ($d) {
      return (int)($d->quantity ?? 0) > 0;
  })->values();

  /*
   * Bahasa PDF.
   * Coba ambil dari beberapa kemungkinan key setting:
   * - pdf_language
   * - language
   * - locale
   *
   * Value yang didukung:
   * id / indonesia / bahasa indonesia
   * en / english
   */
  $pdfLanguage = \App\Models\Setting::getValue('pdf_language', null);

  if (!$pdfLanguage) {
      $pdfLanguage = \App\Models\Setting::getValue('language', null);
  }

  if (!$pdfLanguage) {
      $pdfLanguage = \App\Models\Setting::getValue('locale', null);
  }

  $pdfLanguage = strtolower(trim((string)($pdfLanguage ?? 'id')));

  if (in_array($pdfLanguage, ['english', 'en-us', 'en_us', 'eng'], true)) {
      $pdfLanguage = 'en';
  }

  if (in_array($pdfLanguage, ['indonesia', 'bahasa indonesia', 'bahasa_indonesia', 'id-id', 'id_id', 'indo'], true)) {
      $pdfLanguage = 'id';
  }

  if (!in_array($pdfLanguage, ['id', 'en'], true)) {
      $pdfLanguage = 'id';
  }

  $t = $pdfLanguage === 'en'
      ? [
          'document_title_summary' => 'Quotation',
          'document_title_detail' => 'Estimation',
          'ref_number' => 'Ref Number:',
          'date_issued' => 'Date Issued:',
          'event_details' => 'Event Details',
          'location' => 'Location',
          'duration' => 'Duration',
          'scale_service' => 'Scale & Service',
          'day' => 'Day(s)',
          'description' => 'Description',
          'qty' => 'Qty',
          'amount' => 'Amount',
          'rate' => 'Rate',
          'package_title' => 'Sound System & Multimedia Package',
          'for' => 'for',
          'main_spec' => 'Main Specification:',
          'support_text' => 'including cables, power, accessories, technical support, setup, and operational crew as required',
          'duration_note' => 'Operational duration',
          'hours_day' => 'hours/day',
          'technical_included' => 'This quotation includes technical service fees.',
          'payment_notes_title' => 'Payment Instruction & Notes',
          'payment_notes' => 'This price is an initial quotation based on the event requirements discussion. The price may be adjusted if there are changes in duration, stage layout, or additional equipment on site.',
          'balance_due' => 'Balance Due:',
          'system_notes' => 'System Notes',
          'system_notes_detail' => 'The item details above are automatically generated by the KIRA system. Stock availability and final prices will be reconfirmed when preparing the official invoice.',
          'subtotal_equipment' => 'Subtotal Equipment',
          'labor_crew' => 'Labor & Crew',
          'transportation' => 'Transportation',
          'operational_fee' => 'Operational Fee',
          'markup' => 'Margin / Markup',
          'total_amount' => 'Total Amount:',
          'prepared_by' => 'Prepared by',
          'acknowledged_by' => 'Acknowledged by',
          'representative' => 'Representative',
          'authorized_client' => 'Authorized Client',
          'footer' => 'This document was securely generated by KIRA Event Multimedia Decision Support System.',
        ]
      : [
          'document_title_summary' => 'Penawaran',
          'document_title_detail' => 'Estimasi',
          'ref_number' => 'Nomor Ref:',
          'date_issued' => 'Tanggal:',
          'event_details' => 'Detail Acara',
          'location' => 'Lokasi',
          'duration' => 'Durasi',
          'scale_service' => 'Skala & Layanan',
          'day' => 'Hari',
          'description' => 'Deskripsi',
          'qty' => 'Qty',
          'amount' => 'Jumlah',
          'rate' => 'Harga',
          'package_title' => 'Paket Sound System & Multimedia',
          'for' => 'untuk',
          'main_spec' => 'Spesifikasi Utama:',
          'support_text' => 'termasuk kabel, power, aksesoris, teknis pendukung, setup, dan operasional crew sesuai kebutuhan',
          'duration_note' => 'Durasi operasional',
          'hours_day' => 'jam/hari',
          'technical_included' => 'Penawaran sudah termasuk biaya teknis.',
          'payment_notes_title' => 'Catatan Pembayaran & Penawaran',
          'payment_notes' => 'Harga ini adalah estimasi awal berdasarkan diskusi kebutuhan event. Harga dapat disesuaikan kembali apabila terdapat perubahan durasi, layout panggung, atau tambahan equipment di lokasi acara.',
          'balance_due' => 'Total Tagihan:',
          'system_notes' => 'Catatan Sistem',
          'system_notes_detail' => 'Rincian item di atas dihasilkan otomatis oleh sistem KIRA. Ketersediaan stok dan harga final akan dikonfirmasi ulang pada saat penyusunan invoice resmi.',
          'subtotal_equipment' => 'Subtotal Peralatan',
          'labor_crew' => 'Tenaga Kerja & Crew',
          'transportation' => 'Transportasi',
          'operational_fee' => 'Biaya Operasional',
          'markup' => 'Margin / Markup',
          'total_amount' => 'Total:',
          'prepared_by' => 'Disiapkan oleh',
          'acknowledged_by' => 'Disetujui oleh',
          'representative' => 'Perwakilan',
          'authorized_client' => 'Client',
          'footer' => 'Dokumen ini dibuat secara otomatis oleh KIRA Event Multimedia Decision Support System.',
        ];

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

  $summaryDescription = $t['package_title'];

  if (!empty($event->event_type)) {
      $summaryDescription .= ' ' . $t['for'] . ' ' . $eventTypeLabel;
  }

  $supportText = $t['support_text'];
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
        <div class="doc-title">
          {{ $mode === 'summary' ? $t['document_title_summary'] : $t['document_title_detail'] }}
        </div>

        <table style="width: 100%; margin-top: 10px;">
          <tr>
            <td style="text-align: right; padding-right: 15px;" class="meta-text">{{ $t['ref_number'] }}</td>
            <td style="text-align: right; width: 100px;" class="strong">#{{ str_pad($estimation->id, 5, '0', STR_PAD_LEFT) }}</td>
          </tr>
          <tr>
            <td style="text-align: right; padding-right: 15px;" class="meta-text">{{ $t['date_issued'] }}</td>
            <td style="text-align: right;" class="strong">{{ optional($estimation->created_at)->format('M d, Y') }}</td>
          </tr>
        </table>
      </td>
    </tr>
  </table>

  {{-- EVENT INFORMATION --}}
  <div style="margin-bottom: 25px;">
    <div class="info-label text-blue">{{ $t['event_details'] }}</div>
    <div style="font-size: 18px; font-weight: 700; color: #111827; margin-bottom: 15px;">{{ $eventNameLocal }}</div>

    <table class="info-table">
      <tr>
        <td>
          <div class="info-label">{{ $t['location'] }}</div>
          <div class="info-value">{{ $locationLabel }}</div>
        </td>
        <td>
          <div class="info-label">{{ $t['duration'] }}</div>
          <div class="info-value">{{ $eventDays }} {{ $t['day'] }}</div>
        </td>
        <td>
          <div class="info-label">{{ $t['scale_service'] }}</div>
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
          <th style="width: 60%;">{{ $t['description'] }}</th>
          <th class="num" style="width: 15%;">{{ $t['qty'] }}</th>
          <th class="num" style="width: 25%;">{{ $t['amount'] }}</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td>
            <div class="summary-desc-title">{{ $summaryDescription }}</div>
            <div class="summary-details">
              <strong>{{ $t['main_spec'] }}</strong>
              @if(!empty($equipmentInline))
                {{ $equipmentInline }}@if($hasMoreEquipment), {{ $supportText }}@endif.
              @else
                {{ $t['package_title'] }}, {{ $supportText }}.
              @endif
              <br><br>
              <em>
                *{{ $t['duration_note'] }} {{ $hoursPerDay }} {{ $t['hours_day'] }}.
                {{ $t['technical_included'] }}
              </em>
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
          <div class="strong" style="color: #111827; margin-bottom: 5px;">{{ $t['payment_notes_title'] }}</div>
          {{ $t['payment_notes'] }}
        </td>
        <td style="width: 40%;">
          <table class="totals-table">
            <tr class="total-row">
              <td>{{ $t['balance_due'] }}</td>
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
          <th style="width: 45%;">{{ $t['description'] }}</th>
          <th class="num" style="width: 10%;">{{ $t['qty'] }}</th>
          <th class="num" style="width: 20%;">{{ $t['rate'] }}</th>
          <th class="num" style="width: 25%;">{{ $t['amount'] }}</th>
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
          <div class="strong" style="color: #111827; margin-bottom: 5px;">{{ $t['system_notes'] }}</div>
          {{ $t['system_notes_detail'] }}
        </td>
        <td style="width: 45%;">
          <table class="totals-table">
            <tr>
              <td class="muted">{{ $t['subtotal_equipment'] }}</td>
              <td class="num strong">Rp {{ number_format((int)($b['equipment'] ?? 0),0,',','.') }}</td>
            </tr>
            <tr>
              <td class="muted">{{ $t['labor_crew'] }}</td>
              <td class="num strong">Rp {{ number_format((int)($b['labor'] ?? 0),0,',','.') }}</td>
            </tr>
            <tr>
              <td class="muted">{{ $t['transportation'] }}</td>
              <td class="num strong">Rp {{ number_format((int)($b['transport'] ?? 0),0,',','.') }}</td>
            </tr>
            <tr>
              <td class="muted">{{ $t['operational_fee'] }}</td>
              <td class="num strong">Rp {{ number_format((int)($b['operational'] ?? 0),0,',','.') }}</td>
            </tr>
            @if(isset($b['markup']))
              <tr>
                <td class="muted">{{ $t['markup'] }}</td>
                <td class="num strong">Rp {{ number_format((int)($b['markup'] ?? 0),0,',','.') }}</td>
              </tr>
            @endif
            <tr class="total-row">
              <td>{{ $t['total_amount'] }}</td>
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
        <div class="muted" style="font-size: 11px;">{{ $t['prepared_by'] }}</div>
        <div class="signature-line">{{ $bizName }} {{ $t['representative'] }}</div>
      </td>
      <td style="text-align: right;">
        <div class="muted" style="font-size: 11px; padding-right: 25px;">{{ $t['acknowledged_by'] }}</div>
        <div class="signature-line" style="margin-left: auto; margin-right: 0;">{{ $t['authorized_client'] }}</div>
      </td>
    </tr>
  </table>

  {{-- FOOTER --}}
  <div class="footer-block">
    <strong class="text-blue">{{ $bizName }}</strong> &mdash; {{ $t['footer'] }}
  </div>

</body>
</html>