<div class="dt-ext" style="overflow-x: visible;">
    <table class="display nowrap table-striped table-hover" id="basic-test" style="width: 100%;">
        <thead>
            <tr>
                <th class="all">#</th>
                <th class="all">Lead No</th>
                <th class="all">Client Name</th>
                <th class="all">Contact</th>
                <th class="all">Location</th>
                <th class="all">Source</th>
                <th class="all">Assigned To</th>
                <th class="all">Tags</th>
                <th class="all">Action</th>
            </tr>
        </thead>
        <tbody>
            @foreach($leads as $key => $lead)
            <tr>
                <td>{{ $leads->firstItem() + $key }}</td>
                <td>
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge badge-light-primary" style="font-size: 13px; font-weight: 600;">{{ $lead->lead_no }}</span>
                        @if($lead->status && !in_array($lead->status->slug, ['won', 'lost']))
                            <a href="javascript:void(0)" onclick="openFollowupModal({{ $lead->id }})" class="text-primary" title="Quick Followup"><i class="fa fa-bell-o" style="font-size: 14px;"></i></a>
                        @endif
                    </div>
                    @if($lead->lead_count > 1)
                        <div class="mt-1"><span class="badge badge-light-warning text-dark" style="font-size: 12px;">{{ $lead->lead_count }} Enquiries</span></div>
                    @endif
                </td>
                <td>
                    <a href="{{ route('lead.leads.show', $lead->id) }}"><strong style="font-size: 14px;">{{ strtoupper($lead->name) }}</strong></a><br>
                    <small class="text-muted">{{ $lead->regarding }}</small>
                </td>
                <td style="font-size: 14px; font-weight: 600;">{{ $lead->phone }}</td>
                <td style="font-size: 13px;">{{ strtoupper($lead->city) }}, {{ strtoupper($lead->state) }}</td>
                <td>{{ $lead->source->name ?? 'N/A' }}</td>
                <td>
                    <div class="d-flex align-items-center justify-content-between">
                        <span>{{ strtoupper($lead->assignedUser->name ?? 'Unassigned') }}</span>
                        @if($lead->status && !in_array($lead->status->slug, ['won', 'lost']))
                            <a class="btn btn-secondary btn-xs p-1 ms-2" data-bs-toggle="modal" data-bs-target="#transferModal{{$lead->id}}" data-bs-toggle="tooltip" title="Transfer Lead"><i class="fa fa-exchange"></i></a>
                        @endif
                    </div>
                </td>
                <td>
                    @foreach($lead->tags as $tag)
                        <span class="badge" style="background-color: {{ $tag->color }}; color: #fff; font-size: 10px; padding: 4px 6px;">{{ strtoupper($tag->name) }}</span>
                    @endforeach
                </td>
                <td>
                    <div class="d-flex align-items-center gap-1">
                        <a href="{{ route('lead.leads.show', $lead->id) }}" class="btn btn-primary btn-xs-custom" data-bs-toggle="tooltip" title="Profile"><i class="fa fa-eye"></i></a>
                        @if($lead->status && !in_array($lead->status->slug, ['won', 'lost']))
                            <a class="btn btn-success btn-xs-custom" onclick="followup_modal({{$lead->id}})" data-bs-toggle="tooltip" title="Followup"><i class="fa fa-phone"></i></a>
                        @endif
                        <a class="btn btn-warning btn-xs-custom text-white" onclick="history_modal({{$lead->id}})" data-bs-toggle="tooltip" title="History"><i class="fa fa-history"></i></a>
                        @if($lead->status && !in_array($lead->status->slug, ['won', 'lost']))
                            <a href="{{ route('lead.edit', $lead->id) }}" class="btn btn-info btn-xs-custom text-white" data-bs-toggle="tooltip" title="Edit"><i class="fa fa-pencil"></i></a>
                        @endif
                        <a class="btn btn-danger btn-xs-custom" onclick="delete_lead({{$lead->id}})" data-bs-toggle="tooltip" title="Delete"><i class="fa fa-trash"></i></a>
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

@foreach ($leads as $lead)
    <div class="modal fade" id="transferModal{{$lead->id}}" tabindex="-1" role="dialog" aria-labelledby="transferModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form action="{{ route('lead.transfer', $lead->id) }}" method="POST" class="transfer-lead-form">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Transfer Lead: {{ $lead->name }}</h5>
                        <button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Transfer To User</label>
                            <select name="assigned_user_id" class="form-select" required>
                                <option value="">- Select User -</option>
                                @foreach(\App\Models\User::where('status', 1)->get() as $u)
                                    <option value="{{ $u->id }}">{{ $u->name }} ({{ $u->role_as }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Transfer Remarks</label>
                            <textarea name="remarks" class="form-control" rows="3" placeholder="Reason for transfer..." required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-secondary btn-sm" type="button" data-bs-dismiss="modal">Close</button>
                        <button class="btn btn-primary btn-sm" type="submit">Complete Transfer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endforeach

<div class="mt-4 mb-5">
    <div class="d-flex justify-content-between align-items-center px-2">
        <div class="text-muted" style="font-size: 12px; font-weight: 500;">
            Showing <span class="text-dark fw-bold">{{ $leads->firstItem() ?? 0 }}</span> to <span class="text-dark fw-bold">{{ $leads->lastItem() ?? 0 }}</span> of <span class="text-dark fw-bold">{{ $leads->total() }}</span> results
        </div>
        <div class="pages">
            {{ $leads->onEachSide(1)->links('pagination::bootstrap-4') }}
        </div>
    </div>
</div>

<style>
    .pages .pagination {
        margin-bottom: 0;
        gap: 3px;
    }
    .pages .page-item .page-link {
        border-radius: 4px !important;
        padding: 4px 10px;
        color: #4b4b4b;
        font-weight: 600;
        border: 1px solid #dee2e6;
        font-size: 12px;
    }
    .pages .page-item.active .page-link {
        background-color: #24695c;
        border-color: #24695c;
        color: #fff;
    }
    .dt-ext {
        overflow-x: visible !important;
        width: 100% !important;
    }
    #basic-test {
        width: 100% !important;
        margin-bottom: 0;
        table-layout: auto;
    }
    #basic-test th, #basic-test td {
        padding: 6px 8px !important;
        font-size: 12px;
    }
    .btn-xs-custom {
        padding: 3px 6px !important;
        font-size: 11px !important;
    }
</style>
