<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <style>
    body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color:#111827; }
    .muted { color:#6B7280; }
    .title { font-size:18px; font-weight:700; margin:0; }
    .h2 { font-size:14px; font-weight:700; margin:0 0 6px 0; }
    .card { border:1px solid #E5E7EB; border-radius:10px; padding:14px; margin-top:14px; }
    table { width:100%; border-collapse: collapse; }
    th, td { border-bottom:1px solid #E5E7EB; padding:10px 8px; vertical-align: top; }
    th { background:#F9FAFB; text-align:left; font-size:11px; text-transform: uppercase; letter-spacing:.06em; color:#6B7280; }
    .right { text-align:right; }
    .badge { display:inline-block; padding:4px 10px; border-radius:999px; font-weight:700; font-size:10px; }
    .b-pending { background:#FFEDD5; color:#9A3412; }
    .b-approved { background:#DCFCE7; color:#166534; }
    .b-rejected { background:#FEE2E2; color:#991B1B; }
    .total { font-size:16px; font-weight:800; }
    .small { font-size:10px; }
  </style>
</head>
<body>

  {{-- Header --}}
  <table style="width:100%; border:0; border-bottom:1px solid #E5E7EB; margin-bottom:14px;">
    <tr>
      <td style="border:0; padding:0 0 12px 0;">
        <table style="border:0;">
          <tr>
            <td style="border:0; padding:0; width:64px;">
              <img src="{{ public_path('images/logo-kira.png') }}" style="width:56px; height:auto;">
            </td>
            <td style="border:0; padding:0 0 0 10px;">
              <p class="title">Event Multimedia Cost Estimation</p>
              <p class="muted" style="margin:4px 0 0 0;">Rule-Based Decision Support System</p>
            </td>
          </tr>
        </table>
      </td>
      <td style="border:0; padding:0 0 12px 0;" class="right">
        <div style="font-weight:700;">Estimation #{{ $estimation->id }}</div>
        <div class="muted">{{ optional($estimation->created_at)->format('Y-m-d H:i') }}</div>
        @php
          $s = $estimation->status ?? 'pending';
          $cls = $s === 'approved' ? 'b-approved' : ($s === 'rejected' ? 'b-rejected' : 'b-pending');
        @endphp
        <div style="margin-top:6px;">
          <span class="badge {{ $cls }}">{{ strtoupper($s) }}</span>
        </div>
      </td>
    </tr>
  </table>

  {{-- Event Summary --}}
  <div class="card">
    <p class="h2">Event Information</p>
    <table style="border:0;">
      <tr>
        <td style="border:0; padding:6px 8px;">
          <div class="muted small">Event Type</div>
          <div style="font-weight:700;">{{ ucfirst($event->event_type ?? '-') }}</div>
        </td>
        <td style="border:0; padding:6px 8px;">
          <div class="muted small">Participants</div>
          <div style="font-weight:700;">{{ $event->participants ?? '-' }}</div>
        </td>
        <td style="border:0; padding:6px 8px;">
          <div class="muted small">Location</div>
          <div style="font-weight:700;">{{ ucfirst($event->location ?? '-') }}</div>
        </td>
        <td style="border:0; padding:6px 8px;">
          <div class="muted small">Duration</div>
          <div style="font-weight:700;">{{ $event->duration ?? '-' }} hours</div>
        </td>
      </tr>
      <tr>
        <td style="border:0; padding:6px 8px;" colspan="2">
          <div class="muted small">Service Level</div>
          <div style="font-weight:700;">{{ ucfirst($event->service_level ?? '-') }}</div>
        </td>
        <td style="border:0; padding:6px 8px;" colspan="2">
          <div class="muted small">Special Requirements</div>
          <div style="font-weight:700;">{{ $event->special_requirement ?: '-' }}</div>
        </td>
      </tr>
    </table>
  </div>

  {{-- Equipment Table --}}
  <div class="card">
    <p class="h2">Equipment Recommendation</p>
    <table>
      <thead>
        <tr>
          <th>Equipment</th>
          <th class="right">Need</th>
          <th class="right">Available</th>
          <th class="right">Shortage</th>
          <th class="right">Unit Price</th>
          <th class="right">Line Total</th>
        </tr>
      </thead>
      <tbody>
        @foreach($details as $d)
          <tr>
            <td>
              <div style="font-weight:700;">{{ $d->equipment_name }}</div>
            </td>
            <td class="right">{{ $d->quantity }}</td>
            <td class="right">{{ $d->available }}</td>
            <td class="right">{{ $d->shortage }}</td>
            <td class="right">Rp {{ number_format((int)$d->price,0,',','.') }}</td>
            <td class="right">Rp {{ number_format((int)$d->total,0,',','.') }}</td>
          </tr>
        @endforeach
      </tbody>
    </table>

    <p class="muted small" style="margin-top:10px;">
      Notes: shortage & availability are validated from current inventory at the time of estimation.
    </p>
  </div>

  {{-- Cost Breakdown --}}
  <div class="card">
    <p class="h2">Cost Breakdown</p>
    @php
      $b = $estimation->breakdown ?? [];
      // kalau breakdown disimpan JSON string
      if (is_string($b)) { $b = json_decode($b, true) ?: []; }
      $equipment = (int)($b['equipment'] ?? 0);
      $labor = (int)($b['labor'] ?? 0);
      $transport = (int)($b['transport'] ?? 0);
      $operational = (int)($b['operational'] ?? 0);
      $total = (int)($b['total'] ?? $estimation->total_cost ?? 0);
    @endphp

    <table style="width:100%; border:0;">
      <tr>
        <td style="border:0; padding:6px 8px;" class="muted">Equipment</td>
        <td style="border:0; padding:6px 8px;" class="right">Rp {{ number_format($equipment,0,',','.') }}</td>
      </tr>
      <tr>
        <td style="border:0; padding:6px 8px;" class="muted">Labor</td>
        <td style="border:0; padding:6px 8px;" class="right">Rp {{ number_format($labor,0,',','.') }}</td>
      </tr>
      <tr>
        <td style="border:0; padding:6px 8px;" class="muted">Transportation</td>
        <td style="border:0; padding:6px 8px;" class="right">Rp {{ number_format($transport,0,',','.') }}</td>
      </tr>
      <tr>
        <td style="border:0; padding:6px 8px;" class="muted">Operational</td>
        <td style="border:0; padding:6px 8px;" class="right">Rp {{ number_format($operational,0,',','.') }}</td>
      </tr>
      <tr>
        <td style="border:0; padding:10px 8px; font-weight:800;">Total Estimated Cost</td>
        <td style="border:0; padding:10px 8px;" class="right total">Rp {{ number_format($total,0,',','.') }}</td>
      </tr>
    </table>
  </div>

  <div class="muted small" style="margin-top:12px;">
    Generated by Event Multimedia DSS • Forward Chaining Rule Engine • {{ now()->format('Y-m-d H:i') }}
  </div>

</body>
</html>