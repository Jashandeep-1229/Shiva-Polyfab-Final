<!DOCTYPE html>
<html>
<head>
    <title>Customer Ledger Summary</title>
    <style>
        @page {
            margin: 0.5cm;
        }
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 9px;
            color: #333;
            line-height: 1.4;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #4466f2;
            padding-bottom: 10px;
        }
        .header h1 {
            margin: 0;
            color: #4466f2;
            font-size: 20px;
            text-transform: uppercase;
        }
        .header p {
            margin: 5px 0 0;
            color: #666;
            font-size: 12px;
            font-weight: bold;
        }
        .filters {
            margin-bottom: 15px;
            font-size: 9px;
            color: #555;
            background: #f8f9fa;
            padding: 8px;
            border-radius: 5px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th {
            background-color: #4466f2;
            color: white;
            text-align: left;
            padding: 8px 5px;
            text-transform: uppercase;
            font-weight: bold;
            border: 1px solid #4466f2;
        }
        td {
            padding: 6px 5px;
            border: 1px solid #dee2e6;
            vertical-align: middle;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .fw-bold {
            font-weight: bold;
        }
        .text-danger {
            color: #d9534f;
        }
        .text-success {
            color: #5cb85c;
        }
        tr:nth-child(even) {
            background-color: #fcfcfc;
        }
        .footer {
            margin-top: 30px;
            text-align: right;
            font-size: 9px;
            color: #888;
            border-top: 1px solid #eee;
            padding-top: 5px;
        }
        .badge {
            padding: 2px 5px;
            border-radius: 3px;
            font-size: 8px;
            display: inline-block;
        }
        .badge-info {
            background: #e7f0ff;
            color: #0056b3;
        }
        .summary-box {
            margin-top: 20px;
            float: right;
            width: 300px;
        }
        .summary-table td {
            border: none;
            padding: 3px 5px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>SHIVA POLYFAB</h1>
        <p>Customer Ledger Summary Report</p>
    </div>

    <div class="filters">
        <table style="width: 100%; border: none; margin: 0; background: transparent;">
            <tr style="background: transparent;">
                <td style="border: none; padding: 0;">
                    <strong>Report Date:</strong> {{ date('d-M-Y h:i A') }}
                </td>
                <td style="border: none; padding: 0; text-align: right;">
                    @if($from_date || $to_date)
                        <strong>Period:</strong> {{ $from_date ? date('d-M-Y', strtotime($from_date)) : 'Start' }} to {{ $to_date ? date('d-M-Y', strtotime($to_date)) : 'End' }}
                    @else
                        <strong>Period:</strong> All Time
                    @endif
                </td>
            </tr>
        </table>
    </div>

    <table>
        <thead>
            <tr>
                <th width="5%" class="text-center">#</th>
                <th>Customer Name</th>
                <th width="15%">Type</th>
                <th width="15%">Executive</th>
                <th width="15%" class="text-right">Debit Balance</th>
                <th width="15%" class="text-right">Credit Balance</th>
            </tr>
        </thead>
        <tbody>
            @php 
                $total_dr = 0; 
                $total_cr = 0; 
            @endphp
            @foreach($customers as $index => $customer)
                @php
                    $balance = round($customer->balance, 2);
                @endphp
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>
                        <div class="fw-bold">{{ $customer->name }}</div>
                        <div style="font-size: 8px; color: #777;">{{ $customer->code }} | {{ $customer->phone_no }}</div>
                    </td>
                    <td><span class="badge badge-info">{{ $customer->role }}</span></td>
                    <td>{{ $customer->sale_executive->name ?? 'N/A' }}</td>
                    <td class="text-right fw-bold">
                        @if($balance > 0)
                            @php $total_dr += $balance; @endphp
                            {{ number_format($balance, 2) }}
                        @else
                            -
                        @endif
                    </td>
                    <td class="text-right fw-bold">
                        @if($balance < 0)
                            @php $total_cr += abs($balance); @endphp
                            {{ number_format(abs($balance), 2) }}
                        @else
                            -
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr style="background-color: #f1f5f9; font-weight: bold;">
                <td colspan="4" class="text-right">GRAND TOTAL</td>
                <td class="text-right">{{ number_format($total_dr, 2) }}</td>
                <td class="text-right">{{ number_format($total_cr, 2) }}</td>
            </tr>
        </tfoot>
    </table>

    <div class="summary-box">
        <table class="summary-table">
            <tr>
                <td class="text-right">Total Debit Outstanding:</td>
                <td class="text-right fw-bold" width="100">&#8377; {{ number_format($total_dr, 2) }}</td>
            </tr>
            <tr>
                <td class="text-right">Total Credit Balance:</td>
                <td class="text-right fw-bold" width="100">&#8377; {{ number_format($total_cr, 2) }}</td>
            </tr>
            <tr style="border-top: 1px solid #ddd;">
                <td class="text-right">Net Outstanding:</td>
                <td class="text-right fw-bold" width="100" style="font-size: 11px; color: #4466f2;">
                    &#8377; {{ number_format($total_dr - $total_cr, 2) }}
                </td>
            </tr>
        </table>
    </div>

    <div style="clear: both;"></div>

    <div class="footer">
        Generated by Shiva Polyfab ERP System | Page 1
    </div>
</body>
</html>
