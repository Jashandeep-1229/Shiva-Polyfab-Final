<div class="matrix-wrapper">
    <table class="matrix-table">
        <thead>
            <tr>
                <th>COLOR \ SIZE</th>
                @foreach($sizes as $size)
                    <th>
                        <span class="size-name">{{ $size->name }}</span>
                        <!-- <div class="mt-1">
                            <span class="badge bg-white text-dark f-9 p-1 px-2 border">{{ $size->fabric->name ?? 'N/A' }}</span>
                        </div> -->
                    </th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach($colors as $color)
                <tr>
                    <td>{{ $color->name }}</td>
                    @foreach($sizes as $size)
                        @php
                            $balance = \App\Models\CommonManageStock::where('color_id', $color->id)
                                ->where('size_id', $size->id)
                                ->selectRaw("SUM(CASE WHEN in_out = 'In' THEN quantity ELSE -quantity END) as total")
                                ->first()->total ?? 0;
                            $is_low = $balance <= 10;
                        @endphp
                        <td class="{{ $is_low && $balance > 0 ? 'low-stock' : ($balance <= 0 ? 'bg-light' : '') }}">
                            <button type="button" class="stock-btn" onclick="viewHistory({{ $color->id }}, {{ $size->id }})">
                                <span class="stock-val {{ $balance < 0 ? 'text-danger' : ($balance > 0 ? 'text-primary' : 'text-muted') }}">
                                    {{ number_format($balance, 1) }}
                                </span>
                                <span class="stock-unit">KGS</span>
                            </button>
                        </td>
                    @endforeach
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
