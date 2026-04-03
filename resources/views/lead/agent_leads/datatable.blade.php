<div class="dt-ext table-responsive">
    <table class="display nowrap table-striped table-hover" id="basic-test" style="width: 100%;">
        <thead>
            <tr>
                <th class="all">#</th>
                <th class="all">Lead No</th>
                <th class="all">Agent (Firm)</th>
                <th class="all">Mobile No</th>
                <th class="all">Deals In</th>
                <th class="all">Assigned To</th>
                <th class="all">Next Followup</th>
                <th class="all">Action</th>
            </tr>
        </thead>
        <tbody>
            @foreach($leads as $key => $lead)
            <tr>
                <td>{{ $leads->firstItem() + $key }}</td>
                <td>
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge badge-light-primary" style="font-size: 12px; font-weight: 600;">{{ $lead->lead_no }}</span>
                        @if($lead->agent_id)
                            <a href="javascript:void(0)" onclick="openOverallFollowupModal({{ $lead->agent_id }})" class="text-primary" title="Record Agent Activity"><i class="fa fa-bell-o" style="font-size: 14px;"></i></a>
                        @endif
                    </div>
                    @if($lead->lead_count > 1)
                        <div class="mt-1"><span class="badge badge-light-warning text-dark" style="font-size: 11px;">{{ $lead->lead_count }} Jobs</span></div>
                    @endif
                </td>
                <td>
                    <strong>{{ $lead->agent->name ?? 'N/A' }}</strong>
                    @if(isset($lead->agent->firm_name))
                        <br><small class="text-muted">{{ $lead->agent->firm_name }}</small>
                    @endif
                </td>
                <td>
                    <small class="text-dark fw-bold">{{ $lead->agent->phone ?? 'N/A' }}</small>
                </td>
                <td>
                    <span class="badge badge-light-info" style="font-size: 10px;">{{ $lead->agent->dealsIn->name ?? 'N/A' }}</span>
                </td>
                <td>{{ $lead->assignedUser->name ?? 'Unassigned' }}</td>
                <td>
                    @if($lead->agent && $lead->agent->latestPendingOverallFollowup)
                        <span class="badge badge-light-primary" style="font-size: 11px;">
                            <i class="fa fa-calendar me-1"></i>
                            {{ \Carbon\Carbon::parse($lead->agent->latestPendingOverallFollowup->followup_date)->format('d M, Y') }}
                        </span>
                    @else
                        <span class="text-muted f-11">No Followup</span>
                    @endif
                </td>
                <td>
                    <div class="d-flex align-items-center gap-1">
                        <a href="{{ route('lead.agent_leads.show', $lead->id) }}" class="btn btn-primary btn-xs p-1" data-bs-toggle="tooltip" title="Profile & Process"><i class="fa fa-eye"></i></a>
                        @if($lead->agent_id)
                            <a class="btn btn-success btn-xs p-1" onclick="openOverallFollowupModal({{ $lead->agent_id }})" data-bs-toggle="tooltip" title="Record Agent Activity"><i class="fa fa-phone"></i></a>
                        @endif
                        @if($lead->agent_id)
                            <a class="btn btn-warning btn-xs p-1" onclick="history_modal({{ $lead->agent_id }})" data-bs-toggle="tooltip" title="Agent Activity History"><i class="fa fa-history"></i></a>
                        @endif
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

<div class="mt-3 text-center pages">
    {{ $leads->onEachSide(1)->links() }}
</div>
