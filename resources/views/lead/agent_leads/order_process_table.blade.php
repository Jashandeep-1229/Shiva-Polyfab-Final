<div class="table-responsive mt-3">
    <table class="table table-bordered table-striped" id="job-status-table">
        <thead>
            <tr>
                <th>Job #</th>
                <th>Job Name</th>
                <th>Agent</th>
                <th>Current Stage</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            @forelse($jobCards as $jc)
            <tr>
                <td><span class="badge badge-primary">{{ $jc->job_card_no }}</span></td>
                <td><strong>{{ $jc->agentLead->name_of_job ?? 'N/A' }}</strong></td>
                <td>
                    <strong>{{ $jc->agentLead->agent->name ?? 'N/A' }}</strong><br>
                    <small class="text-muted">{{ $jc->agentLead->agent->firm_name ?? '-' }}</small>
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
                    @if($jc->complete_date)
                        <span class="badge badge-success">Completed</span>
                    @else
                        <span class="badge badge-info text-white">In Process</span>
                    @endif
                </td>
                <td>
                    <a href="{{ route('lead.agent_leads.show', $jc->agent_lead_id) }}" class="btn btn-xs btn-primary"><i class="fa fa-eye"></i> Lead Detail</a>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="text-center py-4 text-muted">No active agent job cards found.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-3 pages">
    {{ $jobCards->links() }}
</div>
