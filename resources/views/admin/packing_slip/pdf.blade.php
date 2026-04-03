<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Packing Slip - {{ $packing_slip->packing_slip_no }}</title>
    <style>
        @page {
            margin: 0.2in;
        }
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            color: #333;
            margin: 0;
            padding: 0;
            background-color: #fff;
            line-height: 1.1;
        }
        .container {
            width: 100%;
        }
        /* Header */
        .header {
            text-align: center;
            margin-bottom: 15px;
            border-bottom: 2px solid #5d5fef;
            padding-bottom: 10px;
        }
        .logo {
            max-width: 120px;
            margin-bottom: 5px;
        }
        .doc-type {
            font-size: 22px;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #1a1a1a;
            margin: 2px 0;
        }
        .slip-no {
            font-size: 11px;
            color: #666;
            margin-top: 2px;
        }

        /* Top Info Table */
        .info-table {
            width: 100%;
            margin-bottom: 15px;
            border-collapse: collapse;
        }
        .info-table td {
            vertical-align: top;
            padding: 3px 0;
        }
        .info-label {
            font-size: 10px;
            color: #777;
            text-transform: uppercase;
            font-weight: bold;
            display: block;
            margin-bottom: 1px;
        }
        .info-value {
            font-size: 14px;
            font-weight: 700;
            color: #000;
            border-bottom: 1px solid #eee;
            padding-bottom: 2px;
        }

        /* Main Table */
        .main-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 5px;
            border: 1px solid #e0e0e0;
        }
        .main-table th {
            background-color: #5d5fef;
            color: #ffffff;
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
            padding: 6px 5px;
            text-align: center;
            border: 1px solid #5d5fef;
        }
        .main-table td {
            padding: 4px 5px;
            font-size: 12px;
            border: 1px solid #f0f0f0;
            text-align: center;
            color: #444;
        }
        .main-table tr:nth-child(even) {
            background-color: #fcfcff;
        }
        
        /* Summary Row */
        .summary-row {
            background-color: #f8f9ff !important;
        }
        .summary-row td {
            border-top: 2px solid #5d5fef;
            font-weight: 800;
            font-size: 13px;
            color: #000;
            padding: 8px 5px;
        }

        /* Footer Section */
        .footer-container {
            margin-top: 30px;
            width: 100%;
        }
        .signature-box {
            float: right;
            width: 250px;
            text-align: right;
        }
        .thanks-msg {
            font-size: 14px;
            font-weight: bold;
            color: #5d5fef;
            margin-bottom: 8px;
        }
        .signee-info {
            font-size: 12px;
            line-height: 1.3;
        }
        .signee-name {
            font-weight: 900;
            font-size: 14px;
            text-transform: uppercase;
        }
        .company-stamp {
            margin-top: 5px;
            font-weight: 700;
            color: #000;
            text-transform: uppercase;
        }
        .line-spacer {
            margin-top: 40px;
            border-top: 1px solid #ddd;
            width: 100%;
        }
    </style>
</head>
<body>
    @php
        $logoPath = public_path(env('APP_LOGO_LIGHT'));
        $logoBase64 = '';
        if (file_exists($logoPath)) {
            $logoBase64 = base64_encode(file_get_contents($logoPath));
        }
    @endphp

    <div class="container">
        <!-- Header Section -->
        <div class="header">
            @if($logoBase64)
                <img src="data:image/webp;base64,{{ $logoBase64 }}" class="logo">
            @endif
            <div class="doc-type">Packing Slip</div>
            <div class="slip-no">Ref. No: {{ $packing_slip->packing_slip_no }}</div>
        </div>

        <!-- Meta Info Section -->
        <table class="info-table">
            <tr>
                <td style="width: 60%;">
                    <span class="info-label">Customer / Job Name</span>
                    <div class="info-value">{{ $packing_slip->job_card->name_of_job ?? 'N/A' }}</div>
                </td>
                <td style="width: 40%; text-align: right;">
                    <span class="info-label">Dispatch Date</span>
                    <div class="info-value">{{ date('d M, Y', strtotime($packing_slip->packing_date)) }}</div>
                </td>
            </tr>
        </table>

        <!-- Details Table -->
        <table class="main-table">
            <thead>
                <tr>
                    <th style="width: 35%;">Bags.</th>
                    <th style="width: 30%;">Weight (Kg)</th>
                    <th style="width: 35%;">Completion Date</th>
                </tr>
            </thead>
            <tbody>
                @foreach($packing_slip->packing_details as $key => $detail)
                <tr>
                    <td>{{ $key + 1 }}</td>
                    <td style="font-weight: bold; color: #000;">{{ number_format($detail->weight, 2) }}</td>
                    <td style="font-size: 12px; color: #666;">
                        {{ $detail->complete_date ? date('d M, Y', strtotime($detail->complete_date)) : 'Pending' }}
                    </td>
                </tr>
                @endforeach
                
                <tr class="summary-row">
                    <td style="text-align: right; text-transform: uppercase; white-space: nowrap;">Total Despatched Weight:</td>
                    <td>{{ number_format($packing_slip->total_weight, 2) }} Kg</td>
                    <td></td>
                </tr>
            </tbody>
        </table>

        <!-- Footer / Signature -->
        <div class="footer-container">
            <div class="signature-box">
                <div class="thanks-msg">Thank You!</div>
                <div class="signee-info">
                    <div class="signee-name">{{ auth()->user()->name ?? 'Administrator' }}</div>
                    <div>Executive, Account Dept.</div>
                    <div>{{ auth()->user()->phone_no ?? '99881-10892' }}</div>
                    <div class="company-stamp">{{ config('app.name', 'SHIVA POLYFAB') }}</div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
