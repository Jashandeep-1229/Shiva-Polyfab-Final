<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Common Packing Slip - {{ $packing_slip->packing_slip_no }}</title>
    <style>
        @page { margin: 0.25in; }
        body { font-family: 'Helvetica', 'Arial', sans-serif; color: #333; margin: 0; padding: 0; line-height: 1.2; font-size: 13px; }
        .header { text-align: center; border-bottom: 2px solid #2563eb; padding-bottom: 10px; margin-bottom: 20px; }
        .logo { max-width: 150px; margin-bottom: 5px; }
        .doc-type { font-size: 24px; font-weight: 800; text-transform: uppercase; color: #1e293b; margin: 4px 0; }
        .slip-no { font-size: 11px; color: #64748b; }
        
        .info-table { width: 100%; margin-bottom: 20px; border-collapse: collapse; }
        .info-label { font-size: 10px; color: #64748b; text-transform: uppercase; font-weight: 800; display: block; margin-bottom: 2px; }
        .info-value { font-size: 15px; font-weight: 700; color: #0f172a; border-bottom: 1px solid #e2e8f0; padding-bottom: 3px; }
        
        .main-table { width: 100%; border-collapse: collapse; border: 1px solid #e2e8f0; }
        .main-table th { background-color: #2563eb; color: #ffffff; padding: 8px 6px; font-size: 12px; font-weight: 700; text-transform: uppercase; border: 1px solid #2563eb; }
        .main-table td { padding: 8px 6px; border: 1px solid #e2e8f0; text-align: center; }
        .main-table tr:nth-child(even) { background-color: #f8fafc; }
        
        .summary-row td { border-top: 2px solid #2563eb; font-weight: 800; font-size: 15px; color: #0f172a; padding: 12px 6px; }
        .footer { margin-top: 40px; width: 100%; }
        .signature-box { float: right; width: 280px; text-align: right; }
        .thanks { font-size: 16px; font-weight: 800; color: #2563eb; margin-bottom: 10px; }
        .signee { font-size: 14px; font-weight: 700; text-transform: uppercase; }
        .company { font-weight: 800; color: #000; margin-top: 5px; }
    </style>
</head>
<body>
    @php
        $logoPath = public_path(env('APP_LOGO_DARK'));
        $logoBase64 = '';
        if (file_exists($logoPath)) {
            $logoBase64 = base64_encode(file_get_contents($logoPath));
        }
    @endphp

    <div class="header">
        @if($logoBase64)
            <img src="data:image/webp;base64,{{ $logoBase64 }}" class="logo">
        @endif
        <div class="doc-type">Common Packing Slip</div>
        <div class="slip-no">Ref. No: {{ $packing_slip->packing_slip_no }}</div>
    </div>

    <table class="info-table">
        <tr>
            <td style="width: 60%;">
                <span class="info-label">Customer / Agent Name</span>
                <div class="info-value">{{ $customerName }}</div>
            </td>
            <td style="width: 40%; text-align: right;">
                <span class="info-label">Dispatch Date</span>
                <div class="info-value">{{ date('d M, Y', strtotime($packing_slip->packing_date)) }}</div>
            </td>
        </tr>
    </table>

    <table class="main-table">
        <thead>
            <tr>
                <th style="width: 10%">BAG #</th>
                <th style="width: 35%">SIZE SPEC</th>
                <th style="width: 30%">COLOR SHADE</th>
                <th style="width: 25%">WEIGHT (KG)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($packing_slip->packing_details as $key => $detail)
            <tr>
                <td>{{ $key + 1 }}</td>
                <td>{{ $detail->size->name ?? ($detail->job_card->size->name ?? 'Common Size') }}</td>
                <td>{{ $detail->color->name ?? ($detail->job_card->color->name ?? 'Common Color') }}</td>
                <td style="font-weight: 800;">{{ number_format($detail->weight, 3) }}</td>
            </tr>
            @endforeach
            <tr class="summary-row">
                <td colspan="3" style="text-align: right; text-transform: uppercase;">Total Despatched Weight:</td>
                <td>{{ number_format($packing_slip->total_weight, 3) }} KG</td>
            </tr>
            <tr class="summary-row">
                <td colspan="3" style="text-align: right; text-transform: uppercase;">Total Bags:</td>
                <td>{{ $packing_slip->total_bags }}</td>
            </tr>
        </tbody>
    </table>

    <div class="footer">
        <div class="signature-box">
            <div class="thanks">Thank You!</div>
            <div class="signee">{{ auth()->user()->name ?? 'Administrator' }}</div>
            <div style="font-size: 12px; color: #64748b;">(Generated Electronically)</div>
            <div class="company">{{ config('app.name', 'SHIVA POLYFAB') }}</div>
        </div>
    </div>
</body>
</html>
