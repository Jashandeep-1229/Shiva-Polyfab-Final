<!DOCTYPE html>
<html>
<head>
    <title>Remaining Stock List</title>
    <style>
        @page { margin: 20px; }
        body { font-family: sans-serif; font-size: 9px; color: #333; margin: 0; padding: 0; line-height: 1.25; }
        .header { text-align: center; border-bottom: 2px solid #333; padding-bottom: 8px; margin-bottom: 12px; }
        .header h2 { margin: 0; color: #000; letter-spacing: 1px; font-size: 16px; }
        .header h3 { margin: 3px 0; color: #666; font-size: 11px; }
        
        table { width: 100%; border-collapse: collapse; table-layout: fixed; }
        thead { display: table-header-group; }
        
        th { 
            background-color: #333; 
            color: #fff; 
            font-weight: bold; 
            text-transform: uppercase; 
            padding: 7px 4px; 
            font-size: 9px;
            border: 1px solid #000;
        }
        
        td { border: 1px solid #ccc; padding: 6px 6px; vertical-align: middle; }
        
        .size-name { font-size: 10px; color: #1d4ed8; font-weight: bold; line-height: 1.1; }
        .size-total { font-size: 8px; margin-top: 3px; color: #000; border-top: 1px solid #ddd; padding-top: 2px; font-weight: normal; }
        
        .color-name { font-weight: bold; font-size: 10px; }
        .stock-val { font-weight: bold; text-align: center; font-size: 12px; }
        .text-primary { color: #1d4ed8; }
        .text-danger { color: #dc2626; }
        
        tr { page-break-inside: avoid !important; }
        .spacer-row td { background-color: #f9f9f9; height: 10px; padding: 0; border: none; }
        
        tfoot td { background-color: #333; color: #fff !important; font-weight: bold; padding: 10px 12px; font-size: 13px; }
        
        .col-size { width: 25%; }
        .col-color { width: 50%; }
        .col-qty { width: 25%; }
    </style>
</head>
<body>
    <div class="header">
        <h2>SHIVA POLYFAB</h2>
        <h3>REMAINING COMMON STOCK REPORT</h3>
        <p style="margin:0; font-size: 10px;">Date: {{ date('d-M-Y h:i A') }}</p>
    </div>

    <table cellpadding="0" cellspacing="0">
        <thead>
            <tr>
                <th class="col-size">Size (Specification)</th>
                <th class="col-color">Color (Shade)</th>
                <th class="col-qty" style="text-align: center;">Remaining Qty (KGS)</th>
            </tr>
        </thead>
        <tbody>
            @php $grand_total = 0; @endphp
            @forelse($grouped_stocks as $sizeName => $stocks)
                @php $size_total = $stocks->sum('balance'); @endphp
                @foreach($stocks as $index => $stock)
                    @php $grand_total += $stock->balance; @endphp
                    <tr>
                        <td style="border-bottom: {{ $loop->last ? '1px solid #ccc' : 'none' }}; border-top: {{ $loop->first ? '1px solid #ccc' : 'none' }}; text-align: center; background-color: #fcfcfc;">
                            @if($index === 0)
                                <div class="size-name">{{ $sizeName }}</div>
                                <div class="size-total">Total: {{ number_format($size_total, 3) }}</div>
                            @endif
                        </td>
                        <td class="color-name">{{ $stock->color->name ?? 'N/A' }}</td>
                        <td class="stock-val {{ $stock->balance < 0 ? 'text-danger' : ($stock->balance > 0 ? 'text-primary' : 'text-muted') }}">
                            {{ number_format($stock->balance, 3) }}
                        </td>
                    </tr>
                @endforeach
                <tr class="spacer-row"><td colspan="3"></td></tr>
            @empty
                <tr>
                    <td colspan="3" style="text-align: center; padding: 30px;">No Stock Data Available.</td>
                </tr>
            @endforelse
        </tbody>
        @if($grouped_stocks->isNotEmpty())
            <tfoot>
                <tr>
                    <td colspan="2" style="text-align: right; color: #fff;">GRAND TOTAL:</td>
                    <td style="text-align: center; color: #fff;">{{ number_format($grand_total, 3) }} KGS</td>
                </tr>
            </tfoot>
        @endif
    </table>
</body>
</html>
