<!DOCTYPE html>
<html>
<head>
    <title>Remaining Common Stock Matrix</title>
    <style>
        @page { margin: 1cm; }
        body { font-family: 'Helvetica', sans-serif; font-size: 9px; color: #1e293b; margin: 0; padding: 0; }
        
        .header { margin-bottom: 20px; border-bottom: 3px solid #0f172a; padding-bottom: 10px; }
        .company-name { font-size: 22px; font-weight: 900; color: #0f172a; margin: 0; }
        .report-title { font-size: 14px; color: #475569; margin: 5px 0 0; text-transform: uppercase; letter-spacing: 1px; }
        
        .meta-container { margin-top: 10px; overflow: hidden; }
        .meta-item { float: left; width: 33%; font-size: 10px; }
        
        table { width: 100%; border-collapse: collapse; margin-top: 20px; table-layout: fixed; }
        
        th { background-color: #0f172a; color: #ffffff; padding: 6px 4px; border: 1px solid #334155; font-weight: bold; text-align: center; }
        th.corner { background-color: #1e293b; text-align: left; padding-left: 10px; width: 120px; }
        
        td { border: 1px solid #cbd5e1; padding: 6px 4px; text-align: center; overflow: hidden; }
        td.color-name { background-color: #f8fafc; font-weight: bold; text-align: left; padding-left: 10px; border-right: 2px solid #94a3b8; }
        
        .val { font-size: 11px; font-weight: 800; display: block; }
        .unit { font-size: 7px; color: #64748b; margin-top: 1px; }
        
        .low { background-color: #fef2f2; color: #dc2626; }
        .in-stock { background-color: #f0fdf4; color: #16a34a; }
        .empty { background-color: #f8fafc; color: #64748b; }
        
        .footer { position: fixed; bottom: -10px; width: 100%; text-align: right; font-size: 8px; color: #94a3b8; }
    </style>
</head>
<body>
    <div class="header">
        <div class="company-name">Shiva Polyfab</div>
        <div class="report-title">Live Inventory Matrix - Common Products</div>
        <div class="meta-container">
            <div class="meta-item"><b>Date:</b> {{ date('d M, Y') }}</div>
            <div class="meta-item"><b>Exported By:</b> {{ auth()->user()->name }}</div>
            <div class="meta-item"><b>Summary:</b> {{ $colors->count() }} Colors | {{ $sizes->count() }} Sizes</div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th class="corner">COLOR \ SIZE</th>
                @foreach($sizes as $size)
                    <th>
                        {{ $size->name }}
                        <div style="font-size: 7px; font-weight: normal; margin-top: 2px; color: #cbd5e1;">{{ $size->fabric->name ?? '' }}</div>
                    </th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach($colors as $color)
                <tr>
                    <td class="color-name">{{ $color->name }}</td>
                    @foreach($sizes as $size)
                        @php
                            $balance = $stock_data[$color->id . '-' . $size->id]->total ?? 0;
                            $class = $balance <= 10 && $balance > 0 ? 'low' : ($balance > 10 ? 'in-stock' : 'empty');
                        @endphp
                        <td class="{{ $class }}">
                            <span class="val">{{ number_format($balance, 1) }}</span>
                            <span class="unit">KGS</span>
                        </td>
                    @endforeach
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        Page generated on {{ date('d-m-Y h:i A') }} | CONFIDENTIAL STOCK DATA
    </div>
</body>
</html>
