<div class="table-responsive mt-3">
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Job Name</th>
                <th>Agent</th>
                <th>Job Card</th>
                <th>Completed On</th>
                <th>Stage</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            @forelse($jobCards as $jc)
            <tr>
                <td><strong>{{ $jc->agentLead->name_of_job ?? 'N/A' }}</strong></td>
                <td>
                    <strong>{{ $jc->agentLead->agent->name ?? 'N/A' }}</strong><br>
                    <small class="text-muted">{{ $jc->agentLead->agent->firm_name ?? '-' }}</small>
                </td>
                <td><span class="badge badge-light-primary">{{ $jc->job_card_no }}</span></td>
                <td>
                    <span class="text-success">{{ \Carbon\Carbon::parse($jc->complete_date)->format('d-m-Y') }}</span><br>
                    <small class="text-muted">({{ \Carbon\Carbon::parse($jc->complete_date)->diffForHumans() }})</small>
                </td>
                <td>{{ $jc->agentLead->status->name ?? 'N/A' }}</td>
                <td>
                    <a href="{{ route('lead.agent_leads.show', $jc->agent_lead_id) }}" class="btn btn-xs btn-primary">Re-engage</a>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="text-center py-5">No repeat suggestions at this time.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-3 pages">
    {{ $jobCards->links() }}
</div>
