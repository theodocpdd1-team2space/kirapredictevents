<!doctype html>
<html lang="{{ str_replace('_','-',app()->getLocale()) }}">
<head>
  <meta charset="utf-8">
  <title>{{ ($eventName ?? 'Estimation') }} - Estimation</title>

  <style>
    @page { margin: 30px 40px; }

    body {
      font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
      font-size: 12px;
      color: #1e293b;
      line-height: 1.4;
    }

    .muted { color: #64748b; }
    .small { font-size: 11px; }
    .h1 { font-size: 22px; font-weight: 700; margin: 0; color: #0f172a; }
    .h2 { font-size: 14px; font-weight: 700; margin: 0 0 12px; color: #334155; text-transform: uppercase; letter-spacing: 0.03em; }

    .card {
      border: 1px solid #cbd5e1;
      border-radius: 8px;
      padding: 18px;
      margin-top: 16px;
      background-color: #ffffff;
    }

    .topbar-table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
    .topbar-table td { vertical-align: middle; }

    .logo-box {
      width: 50px; height: 50px;
      border: 1px solid #e2e8f0;
      border-radius: 8px;
      text-align: center;
      vertical-align: middle;
      background: #f8fafc;
    }
    .logo-box img { max-width: 40px; max-height: 40px; }

    .right-meta { text-align: right; white-space: nowrap; }

    .badge {
      display: inline-block;
      border: 1px solid #cbd5e1;
      border-radius: 999px;
      padding: 4px 12px;
      font-size: 10px;
      font-weight: 600;
      color: #334155;
      background: #f1f5f9;
      text-transform: uppercase;
      letter-spacing: 0.05em;
    }

    .grid-table { width: 100%; border-collapse: collapse; }
    .grid-table td {
      padding: 8px 12px 8px 0;
      vertical-align: top;
      width: 33.33%;
    }
    .kv-title { font-size: 10px; font-weight: 700; letter-spacing: .04em; color: #64748b; text-transform: uppercase; margin-bottom: 2px;}
    .kv-value { font-weight: 600; font-size: 12px; color: #0f172a;}

    .table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 5px;
    }
    .table th {
      text-align: left;
      font-size: 10px;
      letter-spacing: .05em;
      text-transform: uppercase;
      color: #475569;
      background: #f1f5f9;
      border-top: 1px solid #cbd5e1;
      border-bottom: 1px solid #cbd5e1;
      padding: 10px 8px;
    }
    .table td {
      border-bottom: 1px solid #e2e8f0;
      padding: 10px 8px;
      vertical-align: top;
    }
    .table tr:nth-child(even) td { background-color: #f8fafc; }

    .num { text-align: right; white-space: nowrap; }
    .strong { font-weight: 700; color: #0f172a; }

    .layout-totals { width: 100%; border-collapse: collapse; }
    .layout-totals td { vertical-align: top; }

    .summary-table { width: 100%; border-collapse: collapse; }
    .summary-table td { padding: 8px 8px; border-bottom: 1px solid #e2e8f0; }
    .summary-table tr:last-child td { border-bottom: none; }

    .total-row td {
      padding-top: 12px;
      padding-bottom: 4px;
      font-size: 14px;
      font-weight: 800;
      color: #0f172a;
      border-top: 2px solid #cbd5e1;
    }

    .footer {
      margin-top: 30px;
      font-size: 10px;
      color: #94a3b8;
      text-align: center;
      border-top: 1px dashed #cbd5e1;
      padding-top: 15px;
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
@endphp

  {{-- HEADER --}}
  <table class="topbar-table">
    <tr>
      <td style="width:60px;">
        <div class="logo-box">
          @if($bizLogo)
            <img src="{{ public_path($bizLogo) }}" alt="Logo">
          @else
            <span class="muted small" style="display:block; margin-top:16px;">Logo</span>
          @endif
        </div>
      </td>

      <td>
        <div style="margin-left:10px;">
          <div class="strong" style="font-size:16px;">{{ $bizName }}</div>
          <div class="muted small">{{ $bizTagline }}</div>
          <div style="margin-top:6px;">
            <span class="badge">ESTIMATION ({{ strtoupper($mode) }})</span>
          </div>
        </div>
      </td>

      <td class="right-meta">
        <div class="muted small">Estimation ID</div>
        <div class="strong" style="font-size: 14px;">#{{ $estimation->id }}</div>
        <div style="height:8px;"></div>
        <div class="muted small">Date</div>
        <div class="strong">{{ optional($estimation->created_at)->format('Y-m-d H:i') }}</div>
      </td>
    </tr>
  </table>

  {{-- TITLE --}}
  <div class="card" style="background-color: #f8fafc; border-left: 4px solid #3b82f6;">
    <div class="h1">{{ $eventNameLocal }}</div>
    <div class="muted" style="margin-top:4px;">
      Dokumen ini dihasilkan otomatis oleh sistem sebagai estimasi biaya awal.
    </div>
  </div>

  {{-- EVENT INFO --}}
  <div class="card">
    <div class="h2">Event Information</div>

    <table class="grid-table">
      <tr>
        <td>
          <div class="kv-title">Event Type</div>
          <div class="kv-value">{{ ucfirst($event->event_type ?? '-') }}</div>
        </td>
        <td>
          <div class="kv-title">Location</div>
          <div class="kv-value">{{ ucfirst($event->location ?? '-') }}</div>
        </td>
        <td>
          <div class="kv-title">Service Level</div>
          <div class="kv-value">{{ ucfirst($event->service_level ?? '-') }}</div>
        </td>
      </tr>
      <tr>
        <td>
          <div class="kv-title">Participants</div>
          <div class="kv-value">{{ number_format((int)($event->participants ?? 0)) }}</div>
        </td>
        <td>
          <div class="kv-title">Duration</div>
          <div class="kv-value">
            {{ (int)($event->event_days ?? 1) }} day(s) × {{ (int)($event->hours_per_day ?? 1) }} h/day
            <span class="muted small">({{ (int)($event->duration ?? 0) }} hours)</span>
          </div>
        </td>
        <td>
          <div class="kv-title">Special Requirements</div>
          <div class="kv-value">{{ $event->special_requirement ?: '-' }}</div>
        </td>
      </tr>
    </table>
  </div>

  {{-- EQUIPMENT LIST --}}
  <div class="card" style="padding: 18px 0;">
    <div style="padding: 0 18px;">
      <div class="h2">Equipment List</div>
      <div class="muted small" style="margin-bottom: 12px;">{{ $estimation->details->count() }} item(s)</div>
    </div>

    {{-- SUMMARY MODE: tanpa unit price & total --}}
    @if($mode === 'summary')
      <table class="table">
        <thead>
          <tr>
            <th style="width:70%; padding-left: 18px;">Item</th>
            <th class="num" style="width:15%;">Qty</th>
            <th class="num" style="width:15%; padding-right: 18px;">Shortage</th>
          </tr>
        </thead>
        <tbody>
          @foreach($estimation->details as $d)
            <tr>
              <td class="strong" style="padding-left: 18px;">{{ $d->equipment_name }}</td>
              <td class="num">{{ (int)$d->quantity }}</td>
              <td class="num" style="padding-right: 18px;">{{ (int)$d->shortage }}</td>
            </tr>
          @endforeach
        </tbody>
      </table>
    @else
      {{-- DETAIL MODE --}}
      <table class="table">
        <thead>
          <tr>
            <th style="width:42%; padding-left: 18px;">Item</th>
            <th class="num" style="width:10%;">Qty</th>
            <th class="num" style="width:18%;">Unit Price</th>
            <th class="num" style="width:18%;">Total</th>
            <th class="num" style="width:12%; padding-right: 18px;">Shortage</th>
          </tr>
        </thead>
        <tbody>
          @foreach($estimation->details as $d)
            <tr>
              <td class="strong" style="padding-left: 18px;">{{ $d->equipment_name }}</td>
              <td class="num">{{ (int)$d->quantity }}</td>
              <td class="num muted">Rp {{ number_format((int)$d->price,0,',','.') }}</td>
              <td class="num strong">Rp {{ number_format((int)$d->total,0,',','.') }}</td>
              <td class="num" style="padding-right: 18px;">{{ (int)$d->shortage }}</td>
            </tr>
          @endforeach
        </tbody>
      </table>
    @endif
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

  <div class="footer">
    &copy; {{ date('Y') }} {{ $bizName }}. All rights reserved.
  </div>
</body>
</html>