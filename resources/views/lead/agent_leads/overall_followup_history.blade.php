<div class="table-responsive">
    <table class="table table-sm table-hover">
        <thead>
            <tr>
                <th>Status</th>
                <th>Scheduled For</th>
                <th>Completed On</th>
                <th>Discussion Notes</th>
                <th>By</th>
            </tr>
        </thead>
        <tbody>
            @forelse($history as $item)
                <tr class="{{ $item->status == 0 ? 'table-warning' : '' }}">
                    <td>
                        @if($item->status == 0)
                            <span class="badge badge-primary">PENDING</span>
                        @else
                            <span class="badge badge-success">COMPLETED</span>
                        @endif
                    </td>
                    <td>{{ \Carbon\Carbon::parse($item->followup_date)->format('d M, Y') }}</td>
                    <td>{{ $item->complete_date ? \Carbon\Carbon::parse($item->complete_date)->format('d M, Y h:i a') : '-' }}</td>
                    <td>
                        <div class="mb-1 text-muted f-11"><strong>Topic:</strong> {{ $item->remarks ?: 'N/A' }}</div>
                        @if($item->status == 1 && $item->complete_remarks != 'Continued to next interaction')
                            <div class="text-dark f-13"><strong>Outcome:</strong> {{ $item->complete_remarks ?: 'N/A' }}</div>
                        @endif
                    </td>
                    <td>{{ ($item->status == 1 ? $item->completedBy->name : $item->addedBy->name) ?? 'N/A' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center">No past overall followups found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
