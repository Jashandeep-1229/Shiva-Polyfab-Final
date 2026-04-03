<table class="table table-sm table-striped f-12 mb-0">
    <thead class="bg-white sticky-top shadow-sm">
        <tr>
            <th class="py-2 px-3">Date</th>
            <th class="py-2 px-3 text-center">Type</th>
            <th class="py-2 px-3 text-center">Qty</th>
            <th class="py-2 px-3">Remarks</th>
            <th class="py-2 px-3 text-center">By</th>
            <th class="py-2 px-3 text-center">Action</th>
        </tr>
    </thead>
    <tbody>
        @forelse($history as $item)
            <tr>
                <td class="px-3">{{ date('d M, Y', strtotime($item->date)) }}</td>
                <td class="text-center">
                    <span class="badge {{ $item->in_out == 'In' ? 'bg-success' : 'bg-danger' }} rounded-1 f-10">
                        {{ strtoupper($item->in_out) }}
                    </span>
                </td>
                <td class="text-center fw-bold {{ $item->in_out == 'In' ? 'text-success' : 'text-danger' }}">
                    {{ $item->in_out == 'In' ? '+' : '-' }} {{ number_format($item->quantity, 3) }}
                </td>
                <td class="px-3">
                    <span class="text-dark f-11">{{ $item->remarks ?: '-' }}</span>
                </td>
                <td class="text-center text-muted px-3">
                    {{ $item->user->name ?? 'N/A' }}
                </td>
                <td class="text-center px-3">
                    @if($item->from == 'Manually')
                        <button onclick="deleteHistory({{ $item->id }})" class="btn text-danger p-0" title="Delete">
                            <i class="fa fa-trash-o"></i>
                        </button>
                    @else
                        <span class="text-muted f-10 italic">System Auto</span>
                    @endif
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="6" class="text-center py-4 text-muted">No transaction history found.</td>
            </tr>
        @endforelse
    </tbody>
</table>
