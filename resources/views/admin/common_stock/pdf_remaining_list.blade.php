<!DOCTYPE html>
<html>
<head>
    <title>Remaining Stock List</title>
    <style>
        @page { margin: 35px; }
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 10px; color: #334155; margin: 0; padding: 0; line-height: 1.6; }
        
        .header { text-align: center; margin-bottom: 25px; border-bottom: 2px solid #333; padding-bottom: 10px; }
        .header h2 { margin: 0; font-size: 20px; color: #000; text-transform: uppercase; letter-spacing: 1px; font-weight: bold; }
        
        table { width: 100%; border-collapse: collapse; }
        
        th { 
            background-color: #fff; 
            color: #000; 
            font-weight: bold; 
            text-transform: uppercase; 
            padding: 10px 12px; 
            font-size: 10px;
            border-bottom: 2px solid #333;
            text-align: left;
        }
        
        td { 
            padding: 8px 12px; 
            border-bottom: 1px solid #ddd; 
            vertical-align: middle; 
        }

        /* Zebra Striping: Odd rows gray, even rows white */
        .odd-row { background-color: #f2f2f2; }
        .even-row { background-color: #fff; }

        .size-cell { font-weight: bold; color: #000; }
        .color-cell { font-weight: bold; color: #000; }
        .qty-cell { text-align: right; font-weight: bold; font-size: 11px; color: #000; }
        
        .subtotal-row {
            background-color: #fff !important;
        }
        .subtotal-row td {
            font-weight: bold;
            color: #000;
            border-top: 1px solid #000;
            border-bottom: 2px solid #000;
        }

        .text-danger { color: #ef4444; }
        .text-right { text-align: right; }
        
        tfoot td { 
            background-color: #000; 
            color: #ffffff !important; 
            font-weight: bold; 
            padding: 12px; 
            font-size: 12px; 
        }

        .serial-col { width: 40px; color: #000; font-weight: bold; text-align: center; }
        .size-col { width: 220px; }
        .color-col { }
        .qty-col { width: 120px; }

    </style>
</head>
<body>
    <div class="header">
        <h2>SHIVA POLYFAB</h2>
    </div>

    <table cellpadding="0" cellspacing="0">
        <thead>
            <tr>
                <th class="serial-col">S.No.</th>
                <th class="size-col">Size (Specification)</th>
                <th class="color-col">Color (Shade)</th>
                <th class="qty-col" style="text-align: right;">Remaining Stock</th>
            </tr>
        </thead>
        <tbody>
            @php 
                $grand_total = 0; 
                $overall_serial = 1;
                $row_counter = 0;
            @endphp
            @forelse($grouped_stocks as $sizeName => $stocks)
                @php $size_total = $stocks->sum('balance'); @endphp
                @foreach($stocks as $index => $stock)
                    @php 
                        $grand_total += $stock->balance;
                        $row_class = ($row_counter % 2 == 0) ? 'odd-row' : 'even-row';
                        $row_counter++;
                    @endphp
                    <tr class="{{ $row_class }}">
                        <td class="serial-col">{{ $overall_serial++ }}</td>
                        <td class="size-col size-cell">{{ $sizeName }}</td>
                        <td class="color-col color-cell">{{ $stock->color->name ?? 'N/A' }}</td>
                        <td class="qty-col qty-cell {{ $stock->balance < 0 ? 'text-danger' : '' }}">
                            {{ number_format($stock->balance, 3) }}
                        </td>
                    </tr>
                @endforeach
                <tr class="subtotal-row">
                    <td colspan="3" class="text-right">SUBTOTAL ({{ $sizeName }}):</td>
                    <td class="qty-col qty-cell {{ $size_total < 0 ? 'text-danger' : '' }}">
                        {{ number_format($size_total, 3) }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" style="text-align: center; padding: 40px; color: #94a3b8;">
                        No stock data available.
                    </td>
                </tr>
            @endforelse
        </tbody>
        @if($grouped_stocks->isNotEmpty())
            <tfoot>
                <tr>
                    <td colspan="3" class="text-right">GRAND TOTAL:</td>
                    <td class="qty-col qty-cell">{{ number_format($grand_total, 3) }} KGS</td>
                </tr>
            </tfoot>
        @endif
    </table>
</body>
</html>
