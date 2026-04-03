<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Customer Ledger - {{ $customer->name }}</title>
    <style>
        @page {
            margin: 20px 40px;
        }
        body {
            font-family: 'Helvetica Neue', 'Helvetica', 'Arial', sans-serif;
            font-size: 11.5px;
            color: #2d3748;
            line-height: 1.25;
            background-color: #fff;
        }
        .header {
            text-align: center;
            margin-bottom: 6px;
            border-bottom: 1.5px solid #1a365d;
            padding-bottom: 4px;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
            color: #1a365d;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            font-weight: 800;
        }
        .header p {
            margin: 0;
            font-size: 8.5px;
            color: #718096;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight:bold;
        }
        
        .customer-section {
            width: 100%;
            margin-bottom: 5px;
            background: #f8fafc;
            border: 1px solid #cbd5e0;
            border-radius: 4px;
            padding: 6px 0;
        }
        .customer-details {
            padding-left: 10px;
        }
        .date-period {
            padding-right: 10px;
        }
        .customer-section p {
            margin: 0;
        }
        .customer-details b {
            font-size: 13.5px;
            color: #1a365d;
        }
        .copy-account {
            color: #2b6cb0;
            font-weight: 800;
            font-size: 14px;
            letter-spacing: 1px;
            text-decoration: underline;
        }
        .date-period p {
            color: #4a5568;
            font-size: 11px;
        }
        
        table.ledger-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 5px;
            font-size: 11.5px;
        }
        table.ledger-table th {
            background-color: #1a365d;
            color: #ffffff;
            text-transform: uppercase;
            font-size: 10px;
            padding: 8px 6px;
            text-align: left;
            border: 1px solid #1a365d;
        }
        table.ledger-table td {
            border: 1px solid #cbd5e0;
            padding: 6px 8px;
            vertical-align: middle;
            color: #2d3748;
        }
        table.ledger-table tr:nth-child(even) {
            background-color: #f7fafc;
        }
        
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .fw-bold { font-weight: bold; color: #1a365d; }
        
        .footer-section {
            margin-top: 40px;
            width: 100%;
        }
        .confirmation {
            float: left;
            width: 50%;
            font-size: 9px;
            color: #718096;
            line-height: 1.6;
        }
        .signature-area {
            float: right;
            width: 40%;
            text-align: center;
        }
        .signature-box {
            border-top: 1px solid #2d3748;
            margin-top: 50px;
            padding-top: 5px;
            font-weight: bold;
            color: #1a365d;
            text-transform: uppercase;
            font-size: 10px;
        }
        
        .clear { clear: both; }
        
        .pan-bottom {
            margin-top: 30px;
            font-weight: bold;
            font-size: 10px;
            border-top: 1px solid #edf2f7;
            padding-top: 10px;
        }
        .pan-accent { color: #2c5282; }
        .pa-accent { color: #3182ce; }
    </style>
</head>
<body>
    <div class="header">
        <h1>SHIVA POLYFAB</h1>
        <p>M.K ROAD, OPP. GODREJ AGROVET, VILL. IKOLAHA</p>
        <p>Vill Ikolaha</p>
    </div>

    <div class="customer-section">
        <table style="width: 100%; border-collapse: collapse;">
            <tr>
                <td style="width: 40%; vertical-align: top;" class="customer-details">
                    <p>A/c : <b>{{ strtoupper($customer->name) }}</b></p>
                    <p>{{ strtoupper($customer->address) }}</p>
                    <p>{{ strtoupper($customer->city) }} {{ $customer->pincode ? '- ' . $customer->pincode : '' }}</p>
                    @if($customer->gst || $customer->gst_no)
                        <p style="margin-top: 5px; font-weight: bold;">
                            <span style="color: #4a5568;">PAN :</span> {{ strtoupper($pan_no ?? '-') }} &nbsp;&nbsp; 
                            <span style="color: #4a5568;">GST#</span> {{ strtoupper($customer->gst ?? $customer->gst_no) }}
                        </p>
                    @endif
                </td>
                <td style="width: 30%; vertical-align: middle; text-align: center;">
                    <div class="copy-account" style="white-space: nowrap;">COPY OF ACCOUNT</div>
                </td>
                <td style="width: 30%; vertical-align: top; text-align: right;" class="date-period">
                    <p>From {{ $from_date ? date('d-m-Y', strtotime($from_date)) : 'Start' }}</p>
                    <p>Upto {{ $to_date ? date('d-m-Y', strtotime($to_date)) : date('d-m-Y') }}</p>
                    <p>Page 1</p>
                </td>
            </tr>
        </table>
    </div>

    <table class="ledger-table">
        <thead>
            <tr>
                <th width="12%">Date</th>
                <th width="48%">Narration</th>
                <th width="12%" class="text-right">Debit</th>
                <th width="12%" class="text-right">Credit</th>
                <th width="12%" class="text-right">Balance</th>
                <th width="4%" class="text-center">D/C</th>
            </tr>
        </thead>
        <tbody>
            @php $balance = $opening_balance; @endphp
            @if($opening_balance != 0)
            <tr>
                <td class="text-center">-</td>
                <td class="fw-bold">OPENING BALANCE / C/F</td>
                <td class="text-right">-</td>
                <td class="text-right">-</td>
                <td class="text-right fw-bold">{{ number_format(abs($opening_balance), 2, '.', '') }}</td>
                <td class="text-center fw-bold">{{ $opening_balance > 0 ? 'Dr' : 'Cr' }}</td>
            </tr>
            @endif

            @foreach($ledger as $row)
                @php
                    if ($row->dr_cr == 'Dr') $balance += $row->grand_total_amount;
                    else $balance -= $row->grand_total_amount;
                    
                    $prefix = ($row->dr_cr == 'Dr') ? 'To' : 'By';
                    
                    $narration = $prefix . ' ';
                    if ($row->bill_id) {
                        $narration .= 'B.No. ' . ($row->bill->bill_no ?? '#'.$row->bill_id);
                    } elseif ($row->job_card_id) {
                        $narration .= ($row->job_card->name_of_job ?? 'JOB') . ' (#' . $row->job_card_id . ')';
                    } elseif ($row->packing_slip_id) {
                        $narration .= 'SLIP #' . $row->packing_slip_id;
                    } else {
                        $narration .= $row->remarks ?: ($row->payment_method ? $row->payment_method->name : '');
                    }
                @endphp
                <tr>
                    <td class="text-nowrap">{{ date('d-m-Y', strtotime($row->transaction_date)) }}</td>
                    <td>{{ strtoupper($narration) }}</td>
                    <td class="text-right">{{ $row->dr_cr == 'Dr' ? number_format($row->grand_total_amount, 2, '.', '') : '-' }}</td>
                    <td class="text-right">{{ $row->dr_cr == 'Cr' ? number_format($row->grand_total_amount, 2, '.', '') : '-' }}</td>
                    <td class="text-right fw-bold">{{ number_format(abs($balance), 2, '.', '') }}</td>
                    <td class="text-center">{{ $balance > 0 ? 'Dr' : ($balance < 0 ? 'Cr' : '') }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="fw-bold" style="background-color: #edf2f7;">
                <td colspan="2" class="text-right">TOTAL SUMMARY</td>
                <td class="text-right">
                    @php
                        $period_dr = $ledger->where('dr_cr', 'Dr')->sum('grand_total_amount');
                        if($opening_balance > 0) $period_dr += $opening_balance;
                    @endphp
                    {{ number_format($period_dr, 2, '.', '') }}
                </td>
                <td class="text-right">
                    @php
                        $period_cr = $ledger->where('dr_cr', 'Cr')->sum('grand_total_amount');
                        if($opening_balance < 0) $period_cr += abs($opening_balance);
                    @endphp
                    {{ number_format($period_cr, 2, '.', '') }}
                </td>
                <td class="text-right">{{ number_format(abs($balance), 2, '.', '') }}</td>
                <td class="text-center">{{ $balance > 0 ? 'Dr' : ($balance < 0 ? 'Cr' : '') }}</td>
            </tr>
        </tfoot>
    </table>

    <div class="footer-section">
        <div class="confirmation">
            <p>I/we Confirm that the above particulars are true and correct.</p>
            <p>This is a computer generated document and does not require a physical signature.</p>
        </div>
        <div class="signature-area">
            <div class="signature-box">
                FOR SHIVA POLYFAB
            </div>
        </div>
        <div class="clear"></div>
        
        <div class="pan-bottom">
            <span class="pan-accent">PAN & Ward ::</span> ATXPJ6301K, 
            <span style="float: right;" class="pa-accent">P.A.No.AEKFS3882A</span>
        </div>
    </div>
</body>
</html>
