<ul class="lead-info-list mt-3">
    <li><label>Job Name</label><span>{{ $lead->name_of_job }}</span></li>
    <li><label>Agent Name</label><span>{{ $lead->agent->name ?? 'N/A' }}</span></li>
    <li><label>Agent Contact</label><span>{{ $lead->agent->phone ?? 'N/A' }}</span></li>
    <li><label>Assigned To</label><span>{{ $lead->assignedUser->name ?? 'Unassigned' }}</span></li>
    <li><label>Requirement</label><span>{{ $lead->requirement ?? 'N/A' }}</span></li>
    @if($lead->status && $lead->status->slug == 'won')
    <li><label>Order No</label><span>{{ $lead->order_no ?? 'Pending' }}</span></li>
    @endif
</ul>
