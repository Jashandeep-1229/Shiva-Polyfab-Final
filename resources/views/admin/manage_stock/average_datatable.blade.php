<div class="table-responsive">
    <table class="display table-striped table-hover" id="basic-test">
        <thead>
            <tr>
                <th>#</th>
                <th>Item Name</th>
                <th>Stock In</th>
                <th>Stock Out</th>
                <th>Remaining Stock</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($results as $key => $item)
                @php
                    $bgColor = '';
                    if ($item->total_average <= 0) {
                        $bgColor = 'background-color: #ffdada;'; // Red
                    } elseif ($item->alert_min_stock !== null && $item->total_average <= $item->alert_min_stock) {
                        $bgColor = 'background-color: #fff0c2ff;'; // Yellow
                    } elseif ($item->alert_max_stock !== null && $item->total_average >= $item->alert_max_stock) {
                        $bgColor = 'background-color: #e2c4ffff;'; // Violet
                    }
                @endphp
                <tr style="{{ $bgColor }} ">
                    <td>{{ $results->firstItem() + $key }}</td>
                    <td>{{ $item->item_name }}</td>
                    <!-- IN -->
                    <td>{{$unit_name}}: {{ number_format($item->in_quantity, 2) }}
                    <br>    Average: {{ number_format($item->in_average, 2) }}
                    </td>
                    
                    <!-- OUT -->
                    <td>{{$unit_name}}: {{ number_format($item->out_quantity, 2) }}
                    <br> Average: {{ number_format($item->out_average, 2) }}
                    </td>
                    
                    <!-- REMAINING -->
                    <td>
                        {{$unit_name}}:  {{ number_format($item->total_quantity, 2) }}
                        <br> Average: {{ number_format($item->total_average, 2) }}
                    </td>
                    <td>
                        <a target="_blank" href="{{ route('manage_stock.history', ['stock_name' => $stock_name, 'stock_id' => $item->stock_id]) }}" class="btn btn-info btn-sm shadow-sm">
                            <i class="fa fa-history"></i>
                        </a>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
<div class="mt-2 pages">
    {{ $results->onEachSide(1)->links() }}
</div>
