<div class="table-responsive">
    <table class="display table table-striped table-hover table-bordered" id="basic-test">
        <thead class="">
            <tr>
                <th>#</th>
                <th>Date</th>
                <th>Type</th>
                <th>Quantity</th>
                <th>Average Factor</th>
                <th>Remarks</th>
                <th>Entry By</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($history as $key => $item)
                @php
                    $is_in = $item->in_out == 'in';
                    $row_class = $is_in ? 'text-success font-weight-bold' : 'text-danger font-weight-bold';
                    $badge_class = $is_in ? 'badge-success' : 'badge-danger';
                @endphp
                <tr class="{{ $row_class }}">
                    <td>{{ $history->firstItem() + $key }}</td>
                    <td>{{ date('d M, Y', strtotime($item->date)) }}</td>
                    <td>
                        <span class="badge {{ $badge_class }}">{{ strtoupper($item->in_out) }}</span>
                    </td>
                    <td>{{ number_format($item->quantity, 2) }} {{ $item->unit }}</td>
                    <td>{{ number_format($item->average, 2) }}</td>
                    <td class="text-dark">{{ $item->remarks ?? '-' }}</td>
                    <td class="text-dark">
                        {{ $item->user->name ?? 'System' }}<br>
                        <small class="text-muted">{{ $item->created_at->format('d/m/Y h:i A') }}</small>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

<div class="mt-3 pages">
    {{ $history->onEachSide(1)->links() }}
</div>
