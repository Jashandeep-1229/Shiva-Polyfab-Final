@extends('layouts.admin.app')

@section('title', $title)

@section('breadcrumb-items')
    <li class="breadcrumb-item">Follow-ups</li>
    <li class="breadcrumb-item active">{{ $title }}</li>
@endsection

@section('css')
<style>
    .type-badge-customer { background-color: #eeebff !important; color: #7366ff !important; font-size: 10px; font-weight: 700; border: 1px solid #7366ff !important; }
    .type-badge-agent { background-color: #fff9e6 !important; color: #ffa800 !important; font-size: 10px; font-weight: 700; border: 1px solid #ffa800 !important; }
</style>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header pb-0">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="mb-0">{{ $title }}</h5>
                        <span class="badge badge-light-primary">{{ count($followups) }} Scheduled Actions</span>
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <ul class="nav nav-tabs nav-primary" id="pills-tab" role="tablist" style="border: none;">
                                <li class="nav-item">
                                    <a class="nav-link {{ $filter == 'all' ? 'active' : '' }}" href="{{ route('lead.followup.index', ['filter' => 'all']) }}">
                                        <i class="fa fa-list me-2"></i>All
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link {{ ($filter == 'pending' || !$filter) ? 'active' : '' }}" href="{{ route('lead.followup.index', ['filter' => 'pending']) }}">
                                        <i class="fa fa-clock-o me-2"></i>Pending &amp; Today
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link {{ $filter == 'upcoming' ? 'active' : '' }}" href="{{ route('lead.followup.index', ['filter' => 'upcoming']) }}">
                                        <i class="fa fa-calendar-check-o me-2"></i>Upcoming
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="dt-ext table-responsive">
                        <table class="display table-striped table-hover" id="followup-datatable" style="width: 100%;">
                            <thead>
                                <tr>
                                    <th>Type</th>
                                    <th>Client/Agent Name</th>
                                    <th>Phone</th>
                                    <th>Follow-up Date</th>
                                    <th>Current Step</th>
                                    <th>Proposed Activity</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($followups as $followup)
                                @php
                                    $isCustomer = ($followup->model_type == 'customer');
                                    $name = $isCustomer ? $followup->lead->name : ($followup->agent->name ?? 'N/A');
                                    $phone = $isCustomer ? $followup->lead->phone : ($followup->agent->phone ?? 'N/A');
                                    $fdate = \Carbon\Carbon::parse($followup->followup_date);
                                    $isOverdue = $fdate->isPast() && !$fdate->isToday();
                                    $textClass = $isOverdue ? 'text-danger fw-bold' : ($fdate->isToday() ? 'text-success fw-bold' : 'text-primary');
                                    
                                    $stepName = $isCustomer ? ($followup->lead->status->name ?? 'N/A') : 'Agent Activity';
                                    $stepColor = $isCustomer ? ($followup->lead->status->color ?? '#7366ff') : '#ffa800';
                                    $leadId = $isCustomer ? $followup->lead_id : ($followup->agent_id);
                                @endphp
                                <tr>
                                    <td>
                                        <span class="badge {{ $isCustomer ? 'type-badge-customer' : 'type-badge-agent' }}">
                                            {{ strtoupper($followup->model_type) }}
                                        </span>
                                    </td>
                                    <td>
                                        <strong>{{ $name }}</strong>
                                        @if(!$isCustomer && isset($followup->agent->firm_name))
                                            <br><small class="text-muted">{{ $followup->agent->firm_name }}</small>
                                        @endif
                                    </td>
                                    <td>{{ $phone }}</td>
                                    <td>
                                        <span class="{{ $textClass }}">
                                            {{ $fdate->format('d M, Y h:i A') }}
                                        </span>
                                        @if($isOverdue)
                                            <br><small class="text-danger f-11">MISSING: {{ $fdate->diffForHumans() }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge" style="background-color: {{ $stepColor }}; color: #fff;">
                                            {{ $stepName }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="f-12 text-muted italic" style="max-width: 200px; white-space: normal;">
                                            {{ $followup->remarks ?? 'No specific objective provided.' }}
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex gap-1">
                                            @if($isCustomer)
                                                <button class="btn btn-success btn-xs" onclick="openFollowupModal({{ $leadId }})">Update</button>
                                                <a href="{{ route('lead.leads.show', $leadId) }}" class="btn btn-primary btn-xs d-inline-flex align-items-center justify-content-center"><i class="fa fa-eye"></i></a>
                                            @else
                                                <button class="btn btn-warning btn-xs" onclick="openAgentFollowupModal({{ $leadId }})">Update</button>
                                                @if(isset($followup->agent->latestLead))
                                                    <a href="{{ route('lead.agent_leads.show', $followup->agent->latestLead->id) }}" class="btn btn-primary btn-xs d-inline-flex align-items-center justify-content-center" title="View Agent Profile"><i class="fa fa-eye"></i></a>
                                                @endif
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Advanced Followup Modal (Shared) --}}
<div class="modal fade" id="advancedFollowupModal" tabindex="-1">
    <div class="modal-dialog modal-lg" id="followup-modal-container">
        <div class="modal-content">
            <div class="modal-body text-center p-5">
                <div class="loader-box"><div class="loader-37"></div></div>
                <p class="mt-3">Loading details...</p>
            </div>
        </div>
    </div>
</div>

@endsection

@section('script')
<script>
    $(document).ready(function() {
        $('#followup-datatable').DataTable({
            "order": [[ 3, "asc" ]], // Sort by date by default
            "pageLength": 50
        });
    });

    function openFollowupModal(id) {
        $('#advancedFollowupModal').modal('show');
        $('#followup-modal-container').html('<div class="modal-content"><div class="modal-body text-center p-5"><div class="loader-box"><div class="loader-37"></div></div><p class="mt-3">Loading details...</p></div></div>');
        
        $.get(`{{ url('lead/leads/followup-modal') }}/${id}`, function(html) {
            $('#followup-modal-container').html(html);
        });
    }

    function openAgentFollowupModal(agentId) {
        $('#advancedFollowupModal').modal('show');
        $('#followup-modal-container').html('<div class="modal-content"><div class="modal-body text-center p-5"><div class="loader-box"><div class="loader-37"></div></div><p class="mt-3">Loading agent info...</p></div></div>');
        
        $.get(`{{ url('lead/agent-leads/overall-followup-modal') }}/${agentId}`, function(html) {
            $('#followup-modal-container').html(html);
        });
    }

    // This callback is called from the advanced modal script on success
    function reloadTimeline(leadId, justWon) {
        location.reload(); 
    }
</script>
@endsection
