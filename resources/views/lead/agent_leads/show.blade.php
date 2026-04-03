@extends('layouts.admin.app')

@section('title', 'Agent Lead Profile - ' . $lead->lead_no)

@section('css')
<style>
    .lead-profile-sidebar .card-body { padding: 20px; }
    .lead-info-list { list-style: none; padding: 0; margin-top: 15px; }
    .lead-info-list li { margin-bottom: 12px; display: flex; flex-direction: column; }
    .lead-info-list li label { font-size: 11px; text-transform: uppercase; color: #999; font-weight: 600; margin-bottom: 2px; }
    .lead-info-list li span { font-size: 14px; font-weight: 500; color: #333; }
    
    .timeline-wrapper { position: relative; padding-left: 30px; }
    .timeline-wrapper::before { content: ''; position: absolute; left: 7px; top: 0; bottom: 0; width: 2px; background: #e6e9ed; }
    .timeline-item { position: relative; margin-bottom: 30px; transition: all 0.3s ease; }
    .timeline-dot { position: absolute; left: -30px; top: 5px; width: 16px; height: 16px; border-radius: 50%; border: 3px solid #fff; box-shadow: 0 0 0 2px #e6e9ed; background: #7366ff; z-index: 1; }
    .timeline-content { background: #fff; border-radius: 8px; padding: 15px; border: 1px solid #eef1f5; transition: all 0.3s ease; }
    .timeline-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; }
    .timeline-title { font-size: 16px; font-weight: 700; color: #313131; }
    
    .status-badge-pipeline { display: inline-flex; align-items: center; background: rgba(115, 102, 255, 0.1); color: #7366ff; padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; }
    .status-badge-pipeline i { font-size: 10px; margin-right: 5px; }

    @keyframes simple-pulse-won {
        0% { transform: scale(1); box-shadow: 0 0 0 0 rgba(46, 204, 113, 0.4); }
        70% { transform: scale(1.02); box-shadow: 0 0 0 10px rgba(46, 204, 113, 0); }
        100% { transform: scale(1); box-shadow: 0 0 0 0 rgba(46, 204, 113, 0); }
    }
    .anim-won {
        border: 1px solid #2ecc71 !important;
        animation: simple-pulse-won 6s infinite ease-in-out !important;
        background: rgba(46, 204, 113, 0.05) !important;
    }
    .anim-lost {
        border: 1px solid #bdc3c7 !important;
        background: rgba(236, 240, 241, 0.5) !important;
    }
</style>
@endsection

@section('breadcrumb-items')
    <li class="breadcrumb-item"><a href="{{ route('lead.agent_leads.index') }}">Agent Leads</a></li>
    <li class="breadcrumb-item active">Profile</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-12 text-end mb-3">
            <a href="{{ route('lead.agent_leads.index') }}" class="btn btn-primary btn-sm px-3 shadow-sm" style="border-radius: 20px;"><i class="fa fa-arrow-left me-1"></i> Back to List</a>
        </div>
    </div>
    
    <div class="row">
        <!-- Main Content Area (Timeline & Cards) -->
        <div class="col-xl-8 col-md-7 order-xl-2 order-md-2" id="ajax-profile-content">
            @include('lead.agent_leads.profile_main_content', ['lead' => $lead, 'allLeads' => $allLeads])
        </div>

        <!-- Sidebar -->
        <div class="col-xl-4 col-md-5 order-xl-1 order-md-1">
            <div class="card lead-profile-sidebar shadow-sm border-0">
                <div class="card-body">
                    <div class="text-center mb-4">
                        <div class="avatar-wrapper mb-3">
                            <i class="fa fa-truck fa-4x text-primary"></i>
                        </div>
                        <h5 class="mb-1 text-dark fw-bold" id="sidebar-lead-no">{{ $lead->lead_no }}</h5>
                        <div class="status-badge-pipeline {{ ($lead->status && $lead->status->slug == 'won') ? 'anim-won' : (($lead->status && $lead->status->slug == 'lost') ? 'anim-lost' : '') }}">
                            <i class="fa fa-circle"></i> 
                            <span id="sidebar-status-name">
                                @if($lead->status && $lead->status->slug == 'won') 🥳 @elseif($lead->status && $lead->status->slug == 'lost') 😔 @endif
                                {{ $lead->status->name }}
                            </span> 
                        </div>
                        <div class="mt-3" id="followup-btn-container">
                            @include('lead.agent_leads.sidebar_followup_btn')
                        </div>
                    </div>
                    
                    <div id="sidebar-lead-info-container">
                        @include('lead.agent_leads.sidebar_lead_info')
                    </div>

                    <div class="mt-4 pt-3 border-top">
                        <h6 class="text-primary border-bottom pb-2 mb-2 fw-bold">Job Status History</h6>
                        <div id="status-history-container">
                            @include('lead.agent_leads.sidebar_status_history', ['history' => $lead->histories->where('type', 'step_changed')])
                        </div>
                    </div>

                    <div class="mt-4 pt-3 border-top">
                        <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-2">
                            <h6 class="text-success fw-bold p-0 m-0">Overall Agent Followup</h6>
                            <button class="btn btn-xs btn-outline-success p-1 px-2" onclick="openOverallFollowupModal()" style="font-size: 10px;">Followup</button>
                        </div>
                        <div id="overall-followup-container">
                            @include('lead.agent_leads.sidebar_overall_followup', ['overallFollowup' => $overallFollowup])
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

{{-- Common Modal for AJAX content --}}
<div class="modal fade" id="lead_ajax_modal" tabindex="-1" role="dialog" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-lg" id="ajax_modal_dialog" role="document">
    </div>
</div>

<!-- Overall History Modal -->
<div class="modal fade" id="overallHistoryModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Overall Followup History</h5>
                <button class="btn-close" type="button" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="overall-history-body">
                <div class="text-center p-4">
                    <div class="spinner-border text-primary" role="status"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- New Job Modal -->
<div class="modal fade" id="newJobModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Create New Job for {{ $lead->agent->name ?? 'Agent' }}</h5>
                <button class="btn-close" type="button" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('lead.agent_leads.store_single_job') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <input type="hidden" name="agent_id" value="{{ $lead->agent_id }}">
                    <input type="hidden" name="assigned_user_id" value="{{ $lead->assigned_user_id }}">
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Name Of Job <span class="text-danger">*</span></label>
                        <input type="text" name="name_of_job" class="form-control" required placeholder="Enter Job Name">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Process <span class="text-danger">*</span></label>
                        <select name="status_id" class="form-select" required>
                            <option value="">Select Process...</option>
                            @foreach($statuses->whereNotIn('slug', ['won', 'lost']) as $status)
                                <option value="{{ $status->id }}">{{ $status->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Remarks <span class="text-muted">(Optional)</span></label>
                        <textarea name="remarks" class="form-control" rows="3" placeholder="Enter Remarks"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-light" type="button" data-bs-dismiss="modal">Cancel</button>
                    <button class="btn btn-primary" type="submit"><i class="fa fa-save me-1"></i> Create Job</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('script')
<script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.6.0/dist/confetti.browser.min.js"></script>
<script>
    let currentLeadId = {{ $lead->id }};

    function openFollowupModal(id) {
        currentLeadId = id;
        var url = "{{ route('lead.agent_leads.followup_modal', ':id') }}";
        url = url.replace(':id', id);
        $('#ajax_modal_dialog').html('<div class="modal-content"><div class="modal-body text-center p-4"><div class="loader-box"><div class="loader-37"></div></div></div></div>');
        $('#lead_ajax_modal').modal('show');
        $.get(url, function(data) {
            $('#ajax_modal_dialog').html(data);
        });
    }

    function loadLeadProfileContent(id) {
        currentLeadId = id;
        $.ajax({
            url: "{{ url('lead/agent-leads/get-profile-content') }}/" + id,
            type: 'GET',
            success: function(response) {
                $('#ajax-profile-content').html(response.main_content);
                $('#sidebar-status-name').html(response.sidebar_status_name);
                $('#followup-btn-container').html(response.sidebar_followup_btn);
                $('#sidebar-lead-info-container').html(response.sidebar_lead_info);
                $('#status-history-container').html(response.sidebar_status_history);
                $('#overall-followup-container').html(response.sidebar_overall_followup);
                $('#sidebar-lead-no').text(response.lead_no);
                
                // Update URL without reloading
                window.history.pushState(null, '', "{{ url('lead/agent-leads/show') }}/" + id);
            }
        });
    }

    function quickDoneFollowup(id) {
        var $btn = $('#done_btn_' + id);
        var originalHtml = $btn.html();
        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span>');

        $.ajax({
            url: "{{ url('lead/agent-leads/followup/store') }}/" + id,
            type: 'POST',
            data: {
                _token: "{{ csrf_token() }}",
                next_action: 'continue',
                remarks: 'Done'
            },
            success: function(response) {
                $.notify({title:'Success', message:'Activity marked as Done'}, {type:'success'});
                loadLeadProfileContent(id);
            },
            error: function() {
                $.notify({title:'Error', message:'Failed to complete activity'}, {type:'danger'});
                $btn.prop('disabled', false).html(originalHtml);
            }
        });
    }

    function submitRollback() {
        var statusId = $('#rollback_status_id').val();
        var statusName = $('#rollback_status_id option:selected').text();
        
        if (!statusId) {
            alert('Please select a stage to rollback to.');
            return;
        }

        if (confirm(`Are you sure you want to move this Agent Lead BACK to the '${statusName}' stage?`)) {
            $.ajax({
                url: "{{ url('lead/agent-leads/rollback-stage') }}/" + currentLeadId,
                type: 'POST',
                data: {
                    _token: "{{ csrf_token() }}",
                    status_id: statusId
                },
                success: function(response) {
                    if (response.success) {
                        $('#rollbackModal').modal('hide');
                        $.notify({title:'Success', message:response.message}, {type:'success'});
                        loadLeadProfileContent(currentLeadId);
                    }
                }
            });
        }
    }
    function openOverallFollowupModal() {
        var url = "{{ route('lead.agent_leads.overall_followup_modal', $lead->agent_id) }}";
        $('#ajax_modal_dialog').html('<div class="modal-content"><div class="modal-body text-center p-4"><div class="loader-box"><div class="loader-37"></div></div></div></div>');
        $('#lead_ajax_modal').modal('show');
        $.get(url, function(data) {
            $('#ajax_modal_dialog').html(data);
        });
    }

    function viewOverallHistory() {
        $('#overallHistoryModal').modal('show');
        $('#overall-history-body').html('<div class="text-center p-4"><div class="spinner-border text-primary" role="status"></div></div>');
        
        $.get("{{ url('lead/agent-leads/overall-followup/history') }}/" + {{ $lead->agent_id }}, function(data) {
            $('#overall-history-body').html(data);
        });
    }
</script>
@endsection
