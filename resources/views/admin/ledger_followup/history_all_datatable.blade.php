<div class="table-responsive">
    <table class="table table-hover table-striped">
        <thead>
            <tr class="bg-primary text-white">
                <th style="width: 50px;">#</th>
                <th>Client Name</th>
                <th>Remarks</th>
                <th>Scheduled Date</th>
                <th>Completed Date</th>
                <th class="text-center">Delay</th>
                <th>Executive</th>
                <th class="text-center">Status</th>
                <th class="text-center">Result</th>
            </tr>
        </thead>
        <tbody>
            @forelse($histories as $h)
                @php
                    $followup = $h->followup;
                    $is_last = $followup->histories()->max('id') == $h->id;
                    $step_status = ($followup->status == 'Closed' && $is_last) ? 'CLOSED' : 'CONTINUED';
                    
                    $scheduled = strtotime($h->followup_date_time);
                    $completed = strtotime($h->complete_date_time);
                    
                    $result_text = '';
                    $result_class = '';
                    
                    if ($h->total_no_of_days > 0) {
                        $result_text = $h->total_no_of_days . ' Days Delay';
                        $result_class = 'bg-danger';
                    } elseif ($completed < $scheduled) {
                        $result_text = 'Before Time';
                        $result_class = 'bg-success';
                    } else {
                        $result_text = 'On Time';
                        $result_class = 'bg-info';
                    }
                @endphp
                <tr>
                    <td><small class="text-muted">#{{ $followup->id }}</small></td>
                    <td>
                        <div class="fw-bold text-dark small">{{ $followup->customer->name ?? 'N/A' }}</div>
                        <div class="extra-small text-muted text-uppercase fw-bold">{{ $followup->customer->code ?? '' }}</div>
                    </td>
                    <td>
                        <div class="small text-muted text-truncate italic" style="max-width: 200px;" title="{{ $h->remarks }}">
                            {{ $h->remarks ?: '--' }}
                        </div>
                    </td>
                    <td class="small">{{ date('j M, Y H:i', $scheduled) }}</td>
                    <td class="small fw-bold">{{ date('j M, Y H:i', $completed) }}</td>
                    <td class="text-center small">{{ $h->total_no_of_days }} Days</td>
                    <td class="small">
                        {{ $h->completedBy->name ?? 'N/A' }}
                    </td>
                    <td class="text-center">
                        <span class="badge {{ $step_status == 'CLOSED' ? 'bg-success' : 'bg-primary' }} px-2" style="font-size: 10px;">{{ $step_status }}</span>
                    </td>
                    <td class="text-center">
                        <span class="badge {{ $result_class }} px-2" style="font-size: 10px;">{{ $result_text }}</span>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" class="text-center py-5 text-muted">No completed history found for the selected criteria.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-2">
    {{ $histories->links() }}
</div>
