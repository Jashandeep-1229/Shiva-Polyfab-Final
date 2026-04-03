<div class="table-responsive mt-3">
    <table class="table table-bordered table-striped" id="job-status-table">
        <thead>
            <tr>
                <th>Job #</th>
                <th>Lead Name</th>
                <th>Current Stage</th>
                <th>Last Update</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            @forelse($jobCards as $jc)
            <tr>
                <td><span class="badge badge-primary">{{ $jc->job_card_no }}</span></td>
                <td>
                    <strong>{{ $jc->lead->name ?? 'N/A' }}</strong><br>
                    <small class="text-muted">{{ $jc->lead->phone ?? '-' }}</small>
                </td>
                <td>
                    @if($jc->processes->count() > 0)
                        @php $latest = $jc->processes->sortByDesc('id')->first(); @endphp
                        <span class="text-primary f-w-600">{{ $latest->process_name }}</span><br>
                        <small class="text-muted">By {{ $latest->user->name ?? 'System' }}</small>
                    @else
                        <span class="text-warning Italics">Waiting to Start</span>
                    @endif
                </td>
                <td>
                    @if($jc->processes->count() > 0)
                        {{ $jc->processes->sortByDesc('id')->first()->updated_at->format('d M, h:i A') }}
                    @else
                        -
                    @endif
                </td>
                <td>
                    @if($jc->complete_date)
                        <span class="badge badge-success">Completed</span>
                        @php $diff = \Carbon\Carbon::parse($jc->complete_date)->diffInDays(now()); @endphp
                        @if($diff >= 10 && ($jc->lead->is_repeat ?? 0) == 0)
                            <div class="mt-1"><span class="badge badge-light-warning text-dark">Repeat Candidate</span></div>
                        @endif
                    @else
                        <span class="badge badge-info text-white">In Process</span>
                    @endif
                </td>
                <td>
                    <a href="{{ route('lead.leads.show', $jc->lead_id) }}" class="btn btn-xs btn-primary"><i class="fa fa-eye"></i> Lead Detail</a>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="text-center py-4 text-muted">No active job cards linked to leads found.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-3 pages">
    {{ $jobCards->links('pagination::bootstrap-4') }}
</div>
