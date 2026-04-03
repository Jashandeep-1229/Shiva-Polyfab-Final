<div class="table-responsive mt-3">
    <table class="table table-bordered" id="repeat-suggestion-table">
        <thead>
            <tr>
                <th>Customer</th>
                <th>Order Info</th>
                <th>Job Completed</th>
                <th>Status</th>
                <th>Assigned To</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($leads as $jc)
            <tr>
                <td>
                    <strong>{{ $jc->lead->name ?? 'N/A' }}</strong><br>
                    <small class="text-muted">{{ $jc->lead->phone ?? '-' }}</small>
                </td>
                <td>
                    <span class="badge badge-light-primary">{{ $jc->job_card_no }}</span><br>
                    <small>{{ $jc->lead->product ?? 'N/A' }}</small>
                </td>
                <td>
                    <span class="text-success f-w-600">{{ $jc->complete_date ? \Carbon\Carbon::parse($jc->complete_date)->format('d-m-Y') : '-' }}</span><br>
                    <small class="text-muted">{{ $jc->complete_date ? '(' . \Carbon\Carbon::parse($jc->complete_date)->diffForHumans() . ')' : '' }}</small>
                </td>
                <td>
                    @if($jc->lead && $jc->lead->status)
                        <span class="badge" style="background-color: {{ $jc->lead->status->color }}; color: #fff;">{{ $jc->lead->status->name }}</span>
                    @else
                        <span class="badge badge-secondary">New</span>
                    @endif
                </td>
                <td>{{ $jc->lead->assignedUser->name ?? 'Unassigned' }}</td>
                <td>
                    <div class="btn-group">
                        <a href="{{ route('lead.leads.show', $jc->lead_id) }}" class="btn btn-xs btn-primary"><i class="fa fa-envelope-o"></i> Re-engage</a>
                        <button type="button" class="btn btn-xs btn-success" onclick="markRepeat({{ $jc->lead_id }})">Mark Repeat</button>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="text-center py-5">
                    <div class="text-muted">No customers matching the repeat criteria (10 days post-completion) at this moment.</div>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-3 pages">
    {{ $leads->links('pagination::bootstrap-4') }}
</div>
