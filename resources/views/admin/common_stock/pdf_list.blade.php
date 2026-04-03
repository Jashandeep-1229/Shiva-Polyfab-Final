<!DOCTYPE html>
<html>
<head>
    <title>Common Stock {{ $type }} Report</title>
    <style>
        body { font-family: 'Helvetica', sans-serif; font-size: 11px; color: #333; margin: 0; padding: 0; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #242934; padding-bottom: 10px; }
        .header h2 { margin: 0; color: #242934; text-transform: uppercase; font-size: 18px; }
        .header p { margin: 5px 0 0; color: #666; font-size: 12px; }
        
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th { background-color: #242934; color: #ffffff; text-align: left; padding: 8px; font-weight: bold; border: 1px solid #1a1e26; }
        td { padding: 8px; border: 1px solid #e2e8f0; vertical-align: middle; }
        tr:nth-child(even) { background-color: #f8fafc; }
        
        .fw-bold { font-weight: bold; }
        .text-center { text-align: center; }
        .text-success { color: #16a34a; }
        .text-danger { color: #dc2626; }
        
        .footer { position: fixed; bottom: 0; width: 100%; text-align: center; font-size: 9px; color: #94a3b8; border-top: 1px solid #e2e8f0; padding-top: 5px; }
        .meta-info { font-size: 10px; color: #64748b; margin-top: 5px; }
    </style>
</head>
<body>
    <div class="header">
        <h2>Shiva Polyfab</h2>
        <p>Common Product Stock {{ $type }} Report</p>
        <div class="meta-info">Generated on: {{ date('d M, Y h:i A') }}</div>
    </div>

    <table>
        <thead>
            <tr>
                <th width="12%">Date</th>
                <th>Color</th>
                <th>Size</th>
                <th class="text-center" width="15%">Quantity (Kgs)</th>
                <th>Remarks</th>
            </tr>
        </thead>
        <tbody>
            @php $total_qty = 0; @endphp
            @foreach($records as $item)
                @php $total_qty += $item->quantity; @endphp
                <tr>
                    <td>{{ date('d-m-Y', strtotime($item->date)) }}</td>
                    <td class="fw-bold">{{ $item->color->name ?? 'N/A' }}</td>
                    <td>{{ $item->size->name ?? 'N/A' }}</td>
                    <td class="text-center fw-bold {{ $type == 'In' ? 'text-success' : 'text-danger' }}">
                        {{ number_format($item->quantity, 3) }}
                    </td>
                    <td>
                        @if($item->remarks)
                            <div style="font-size: 10px; color: #333 text-align: left;">{{ $item->remarks }}</div>
                        @else
                            -
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr style="background-color: #f1f5f9;">
                <td colspan="3" class="text-center fw-bold" style="padding: 10px;">TOTAL {{ strtoupper($type) }}</td>
                <td class="text-center fw-bold {{ $type == 'In' ? 'text-success' : 'text-danger' }}" style="font-size: 13px;">
                    {{ number_format($total_qty, 3) }}
                </td>
                <td></td>
            </tr>
        </tfoot>
    </table>

    <div class="footer">
        © {{ date('Y') }} Shiva Polyfab. This is a computer-generated stock report.
    </div>
</body>
</html>
