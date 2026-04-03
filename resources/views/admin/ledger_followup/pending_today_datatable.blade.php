<div class="table-responsive">
    <table class="table table-hover table-striped">
        <thead>
            <tr class="bg-primary text-white">
                <th style="width: 50px;">#</th>
                <th>Client Name</th>
                <th>Executive</th>
                <th style="width: 150px;">Followup Date</th>
                <th>Subject</th>
                <th>Latest Remarks</th>
                <th class="text-center">Late By</th>
                <th class="text-center">Action</th>
            </tr>
        </thead>
        <tbody>
            @forelse($histories as $h)
                @php
                    $followup = $h->followup;
                    $scheduled = strtotime($h->followup_date_time);
                    $today = strtotime(date('Y-m-d'));
                    $diff = $today - $scheduled;
                    $days_late = $diff > 0 ? floor($diff / 86400) : 0;
                @endphp
                <tr>
                    <td><small class="text-muted">#{{ $followup->id }}</small></td>
                    <td>
                        <div class="fw-bold text-dark">{{ $followup->customer->name ?? 'N/A' }}</div>
                        <div class="extra-small text-muted text-uppercase fw-bold">{{ $followup->customer->code ?? '' }}</div>
                    </td>
                    <td class="small">
                        {{ $followup->customer->sale_executive->name ?? 'N/A' }}
                    </td>
                    <td class="small fw-bold">
                        {{ date('j M, Y H:i', strtotime($h->followup_date_time)) }}
                    </td>
                    <td>
                        <div class="fw-bold text-dark small" style="max-width: 180px;">{{ $followup->subject }}</div>
                    </td>
                    <td>
                        <div class="small text-muted text-truncate italic" style="max-width: 250px;" title="{{ $h->remarks }}">
                            {{ $h->remarks ?: '--' }}
                        </div>
                    </td>
                    <td class="text-center">
                        @if($days_late > 0)
                            <span class="badge bg-danger px-3">{{ $days_late }} Days Late</span>
                        @elseif(date('Y-m-d', $scheduled) == date('Y-m-d'))
                            <span class="badge bg-warning text-dark px-3">Due Today</span>
                        @else
                            <span class="badge bg-info px-3">Upcoming</span>
                        @endif
                    </td>
                    <td class="text-center">
                        <button class="btn btn-outline-primary btn-xs" onclick="viewHistory({{ $followup->id }})" title="Add Remark / Continue">
                            <i class="fa fa-plus-circle"></i> Update
                        </button>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center py-5 text-muted">No pending followups found for the selected criteria.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-2">
    {{ $histories->links() }}
</div>
