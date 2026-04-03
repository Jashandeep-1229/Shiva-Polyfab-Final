@extends('layouts.admin.app')

@section('title', 'Lead Profile - ' . $lead->lead_no)

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
    .timeline-user { font-size: 12px; color: #777; }
    
    .followup-entry { background: #f9fafb; border-radius: 6px; padding: 10px; margin-top: 10px; border-left: 3px solid #7366ff; }
    .followup-meta { font-size: 12px; font-weight: 600; color: #555; display: flex; justify-content: space-between; }
    .followup-remarks { font-size: 13px; color: #666; margin-top: 5px; font-style: italic; }
    
    .status-badge-pipeline { display: inline-flex; align-items: center; background: rgba(115, 102, 255, 0.1); color: #7366ff; padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; transition: all 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275); }
    .status-badge-pipeline i { font-size: 10px; margin-right: 5px; }

    /* Sidebar Timeline */
    .sidebar-timeline { position: relative; padding-left: 15px; margin-top: 15px; }
    .sidebar-timeline::before { content: ''; position: absolute; left: 4px; top: 5px; height: calc(100% - 10px); width: 2px; background: #e6e9ed; }
    .sidebar-timeline-item { position: relative; margin-bottom: 20px; }
    .sidebar-timeline-dot { position: absolute; left: -15px; top: 5px; width: 10px; height: 10px; border-radius: 50%; border: 2px solid #fff; box-shadow: 0 0 0 1px #7366ff; background: #7366ff; z-index: 1; }
    .sidebar-timeline-content { padding-left: 10px; }
    .sidebar-timeline-content p { margin-bottom: 3px; font-size: 13px; color: #555; line-height: 1.4; }
    .sidebar-timeline-meta { font-size: 11px; color: #999; display: flex; justify-content: space-between; align-items: center; margin-bottom: 5px; }

    /* Simple & Slow Animations */
    @keyframes simple-pulse-won {
        0% { transform: scale(1); box-shadow: 0 0 0 0 rgba(46, 204, 113, 0.4); }
        70% { transform: scale(1.02); box-shadow: 0 0 0 10px rgba(46, 204, 113, 0); }
        100% { transform: scale(1); box-shadow: 0 0 0 0 rgba(46, 204, 113, 0); }
    }

    @keyframes simple-pulse-lost {
        0% { transform: scale(1); box-shadow: 0 0 0 0 rgba(189, 195, 199, 0.4); }
        70% { transform: scale(1.01); box-shadow: 0 0 0 8px rgba(189, 195, 199, 0); }
        100% { transform: scale(1); box-shadow: 0 0 0 0 rgba(189, 195, 199, 0); }
    }

    .anim-won {
        border: 1px solid #2ecc71 !important;
        animation: simple-pulse-won 6s infinite ease-in-out !important;
        background: rgba(46, 204, 113, 0.05) !important;
    }

    .anim-lost {
        border: 1px solid #bdc3c7 !important;
        animation: simple-pulse-lost 6s infinite ease-in-out !important;
        background: rgba(236, 240, 241, 0.5) !important;
        filter: grayscale(0.5);
    }

    .badge-won-special {
        background: linear-gradient(45deg, #f093fb 0%, #f5576c 100%);
        color: white !important;
        padding: 5px 15px !important;
        font-weight: 800 !important;
        box-shadow: 0 4px 15px rgba(245, 87, 108, 0.4);
        transform: rotate(-2deg);
        display: inline-block;
    }

</style>
@endsection

@section('breadcrumb-items')
    <li class="breadcrumb-item">Leads</li>
    <li class="breadcrumb-item active">Profile</li>
@endsection

@section('content')
    <div class="row pt-3">
        <div class="col-12 text-end mb-3">
            <a href="{{ route('lead.index') }}" class="btn btn-primary btn-sm px-3 shadow-sm" style="border-radius: 20px;"><i class="fa fa-arrow-left me-1"></i> Back to List</a>
        </div>
    </div>
    <div class="row">
        <!-- Timeline & Cards (Main Content) - Comes first on mobile -->
        <div class="col-xl-8 col-md-7 order-xl-2 order-md-2" id="ajax-profile-content">
            @include('lead.leads.profile_main_content', ['lead' => $lead, 'allLeads' => $allLeads])
        </div>

        <!-- Sidebar (Client Details) - Comes last on mobile -->
        <div class="col-xl-4 col-md-5 order-xl-1 order-md-1">
            <div class="card lead-profile-sidebar shadow-sm border-0">
                <div class="card-body">
                    <div class="text-center mb-4">
                        <div class="avatar-wrapper mb-3">
                            <img src="{{ asset('assets/images/user/7.jpg') }}" alt="" class="rounded-circle" width="80">
                        </div>
                        <h5 class="mb-1 text-dark fw-bold">{{ $lead->lead_no }}</h5>
                        <div class="status-badge-pipeline {{ ($lead->status && $lead->status->slug == 'won') ? 'anim-won' : (($lead->status && $lead->status->slug == 'lost') ? 'anim-lost' : '') }}">
                            <i class="fa fa-circle"></i> 
                            <span id="sidebar-status-name">
                                @if($lead->status && $lead->status->slug == 'won') 🥳 @elseif($lead->status && $lead->status->slug == 'lost') 😔 @endif
                                {{ $lead->status->name }}
                            </span> 
                        </div>
                        @php $showFollowup = ($lead->status && !in_array($lead->status->slug, ['won', 'lost'])); @endphp
                        <div class="mt-3" id="followup-btn-container" style="display: {{ $showFollowup ? 'block' : 'none' }}">
                            @include('lead.leads.sidebar_followup_btn')
                        </div>
                    </div>
                    
                    <div id="sidebar-lead-info-container">
                        @include('lead.leads.sidebar_lead_info')
                    </div>

                    <div class="mt-4 pt-3 border-top">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h6 class="mb-0 fw-bold">General Lead Remarks</h6>
                            <button class="btn btn-xs btn-outline-primary" data-bs-toggle="modal" data-bs-target="#updateFieldModal" onclick="setField('lead_remarks', '{{ $lead->lead_remarks }}')"><i class="fa fa-pencil"></i></button>
                        </div>
                        <p class="f-13 text-muted">{{ $lead->lead_remarks ?? 'No general remarks added.' }}</p>
                    </div>

                    <div class="mt-4">
                        <h6 class="text-primary border-bottom pb-2 mb-2 fw-bold">Lead Status Change History</h6>
                        <div id="status-history-container">
                            @include('lead.leads.sidebar_status_history')
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

<!-- Advanced Followup Modal (Fallback) -->
<div class="modal fade" id="advancedFollowupModal" tabindex="-1">
    <div class="modal-dialog modal-lg" id="followup-modal-container">
        <div class="modal-content">
            <div class="modal-body text-center p-5">
                <div class="loader-box"><div class="loader-37"></div></div>
                <p class="mt-3">Loading current status...</p>
            </div>
        </div>
    </div>
</div>

<!-- Simple Field Update Modal -->
<div class="modal fade" id="updateFieldModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
        <form id="genericFieldForm" action="{{ route('lead.update', $lead->id) }}" method="POST">
                @csrf
                <div id="method_wrapper">
                    @method('PUT')
                </div>
                <div class="modal-header">
                    <h5 class="modal-title" id="modalFieldTitle">Update Field</h5>
                    <button class="btn-close" type="button" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="field_name" id="modal_field_name">
                    <div id="field_input_wrapper">
                        <!-- Dynamic Input -->
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-primary" type="submit">Update Now</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@section('script')
<script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.6.0/dist/confetti.browser.min.js"></script>
<script>
    let currentLeadId = {{ $lead->id }}; // Use 'let' to allow re-assignment

    function openFollowupModal(id) {
        // Force opening the modal directly as requested by the user
        $('#advancedFollowupModal').modal('show');
        $('#followup-modal-container').html('<div class="modal-content"><div class="modal-body text-center p-5"><div class="loader-box"><div class="loader-37"></div></div><p class="mt-3">Loading status...</p></div></div>');
        
        $.get(`{{ url('lead/leads/followup-modal') }}/${id}`, function(html) {
            $('#followup-modal-container').html(html);
        });
    }

    function reloadTimeline(leadId, triggerCelebration = false) {
        $.get(`{{ url('lead/leads/get-profile-content') }}/${leadId}`, function(response) {
            // 1. Update Workspace Sidebar Status
            $('#sidebar-status-name').html(response.sidebar_status_name);
            $('#sidebar-lead-info-container').html(response.sidebar_lead_info);
            $('#status-history-container').html(response.sidebar_status_history);
            
            // 2. Sync Badge Styling (Animations)
            const badgeWrapper = $('#sidebar-status-name').closest('.status-badge-pipeline');
            badgeWrapper.removeClass('anim-won anim-lost');
            if (response.sidebar_status_name.includes('🥳')) badgeWrapper.addClass('anim-won');
            if (response.sidebar_status_name.includes('😔')) badgeWrapper.addClass('anim-lost');

            // 3. Update Breadcrumb & Document Title
            $('#breadcrumb-lead-no').text(`Lead Profile - ${response.lead_no}`);
            document.title = `Lead Profile - ${response.lead_no}`;

            // 4. Update Follow-up Button Container (Real-time removal for Won/Lost)
            if (response.sidebar_followup_btn.trim()) {
                $('#followup-btn-container').html(response.sidebar_followup_btn).show();
            } else {
                $('#followup-btn-container').fadeOut(400, function() {
                    $(this).html('').hide();
                });
            }
            
            // 5. Update Main Content (Cards and Timeline)
            $('#ajax-profile-content').html(response.main_content);

            // 6. Maintenance: update global lead ID for further modal actions
            currentLeadId = leadId;

            // 7. Celebration Logic
            if (triggerCelebration) celebrateWin();
        });
    }

    function loadLeadProfileContent(leadId) {
        // Show a loader in the main content area
        $('#ajax-profile-content').html('<div class="card shadow-none border-0"><div class="card-body text-center p-5"><div class="spinner-border text-primary" role="status"></div><p class="mt-3">Loading lead profile...</p></div></div>');

        // Update active state in sidebar list if it exists
        $('.list-group-item-action').removeClass('active');
        $(`#sidebar-lead-link-${leadId}`).addClass('active');

        // Use reloadTimeline to handle the heavy lifting
        reloadTimeline(leadId, false);
        
        // Scroll to top
        window.scrollTo({ top: 0, behavior: 'smooth' });
        
        // Update URL state (optional but good for UX)
        if (history.pushState) {
            history.pushState(null, null, `{{ url('lead/leads/show') }}/${leadId}`);
        }
    }


    function celebrateWin() {
        // Strict once-per-trigger check
        if (window.isCelebrating) return;
        window.isCelebrating = true;

        // One clean burst
        confetti({
            particleCount: 150,
            spread: 70,
            origin: { y: 0.6 },
            zIndex: 9999,
            colors: ['#7366ff', '#2ecc71', '#f1c40f']
        });

        // Notify at the exact same time
        $.notify({ title: '🎊 Success! 🎊', message: 'Lead Converted Successfully! 🥳' }, { type: 'success', delay: 4000 });

        // Reset flag after 5 seconds to allow future celebrations if updated again
        setTimeout(() => { window.isCelebrating = false; }, 500);
    }

    // Only celebrate if explicitly triggered by the status change (reloadTimeline)
    // We remove the $(document).ready block to avoid celebrating every time the page is viewed.


    function setField(name, val) {
        let label = name.replace('_', ' ').toUpperCase();
        $('#modalFieldTitle').text('Update ' + label);
        $('#modal_field_name').val(name);
        $('#genericFieldForm').attr('action', "{{ route('lead.update', $lead->id) }}");
        $('#method_wrapper').html('@method("PUT")');
        
        $('#field_input_wrapper').html(`
            <label class="form-label">${label}</label>
            <textarea name="${name}" class="form-control" rows="3" required>${val || ''}</textarea>
        `);
        $('#updateFieldModal').modal('show');
    }

    function setJobCardField(val) {
        $('#modalFieldTitle').text('Link / Clear Order No');
        $('#modal_field_name').val('order_no');
        $('#genericFieldForm').attr('action', "{{ route('lead.leads.update-job-card-no', $lead->id) }}");
        $('#method_wrapper').html(''); 
        
        $('#field_input_wrapper').html(`
            <label class="form-label">Order No <small class="text-muted">(Leave empty to clear &amp; set as Pending)</small></label>
            <input type="text" name="order_no" id="jc_input_realtime" class="form-control" placeholder="e.g. JC-24-25-01" value="${val || ''}" autocomplete="off">
            <div id="jc_realtime_msg" class="mt-2 f-12" style="display: none;"></div>
            <small class="text-muted d-block mt-1">The system will verify if this number exists in production.</small>
        `);
        
        // If pre-filled value exists, start verify; otherwise allow submit to clear
        var btn = $('#genericFieldForm').find('button[type="submit"]');
        if (val && val.length >= 5) {
            btn.prop('disabled', true); // wait for keyup verification
        } else {
            // Empty = clearing is allowed immediately
            btn.prop('disabled', false);
            $('#jc_realtime_msg').html('<i class="fa fa-info-circle"></i> Saving empty will remove the current Order No.').addClass('text-warning').show();
        }
        $('#updateFieldModal').modal('show');
    }

    $(document).on('keyup', '#jc_input_realtime', function() {
        var val = $(this).val().trim();
        var msgDiv = $('#jc_realtime_msg');
        var btn = $('#genericFieldForm').find('button[type="submit"]');

        if (val.length === 0) {
            // Empty = admin wants to clear it
            msgDiv.html('<i class="fa fa-info-circle"></i> Saving empty will remove the current Order No.').removeClass('text-danger text-success').addClass('text-warning').show();
            btn.prop('disabled', false);
        } else if (val.length >= 5) {
            btn.prop('disabled', true);
            $.post("{{ route('lead.check-job-card-no') }}", {
                _token: '{{ csrf_token() }}',
                job_card_no: val,
                lead_id: '{{ $lead->id }}'
            }, function(res) {
                if (res.status === 'success') {
                    msgDiv.html(`<i class="fa fa-check-circle"></i> ${res.message}`).removeClass('text-danger text-warning').addClass('text-success').show();
                    btn.prop('disabled', false);
                } else {
                    msgDiv.html(`<i class="fa fa-times-circle"></i> ${res.message}`).removeClass('text-success text-warning').addClass('text-danger').show();
                    btn.prop('disabled', true);
                }
            });
        } else {
            msgDiv.hide();
            btn.prop('disabled', true);
        }
    });
    function deleteLead(id, isCurrent) {
        if (confirm('Are you sure you want to delete this enquiry? This will be moved to trash.')) {
            $.ajax({
                url: "{{ url('lead/leads') }}/" + id,
                type: 'DELETE',
                data: { _token: '{{ csrf_token() }}' },
                success: function(response) {
                    if (response.success) {
                        alert(response.message);
                        if (isCurrent) {
                            window.location.href = "{{ route('lead.index') }}";
                        } else {
                            // Reload current profile content to update the card list
                            loadLeadProfileContent(currentLeadId);
                        }
                    }
                }
            });
        }
    }

    function deleteAllLeadsForClient(id) {
        if (confirm('Are you sure you want to delete ALL enquiries for this client? This will move everything to trash.')) {
            $.ajax({
                url: "{{ url('lead/leads/destroy-all-for-client') }}/" + id,
                type: 'DELETE',
                data: { _token: '{{ csrf_token() }}' },
                success: function(response) {
                    if (response.success) {
                        alert(response.message);
                        window.location.href = "{{ route('lead.index') }}";
                    }
                }
            });
        }
    }
    function submitRollback() {
        var statusId = $('#rollback_status_id').val();
        var statusName = $('#rollback_status_id option:selected').text();
        
        if (!statusId) {
            alert('Please select a stage to rollback to.');
            return;
        }

        if (confirm(`Are you sure you want to move this lead BACK to the '${statusName}' stage?`)) {
            $.ajax({
                url: "{{ url('lead/leads/rollback-stage') }}/" + currentLeadId,
                type: 'POST',
                data: {
                    _token: "{{ csrf_token() }}",
                    status_id: statusId
                },
                success: function(response) {
                    if (response.success) {
                        $('#rollbackModal').modal('hide');
                        $.notify({
                            title: 'Success',
                            message: response.message
                        }, {
                            type: 'success',
                            timer: 2000,
                            placement: { from: 'top', align: 'right' }
                        });
                        loadLeadProfileContent(currentLeadId);
                    }
                }
            });
        }
    }

    function rollbackLeadStage(leadId, statusId, statusName) {
        if (confirm(`Are you sure you want to move this lead BACK to the '${statusName}' stage?`)) {
            $.ajax({
                url: "{{ url('lead/leads/rollback-stage') }}/" + leadId,
                type: 'POST',
                data: {
                    _token: "{{ csrf_token() }}",
                    status_id: statusId
                },
                success: function(response) {
                    if (response.success) {
                        $.notify({
                            title: 'Success',
                            message: response.message
                        }, {
                            type: 'success',
                            allow_dismiss: false,
                            newest_on_top: true,
                            timer: 2000,
                            placement: {
                                from: 'top',
                                align: 'right'
                            }
                        });
                        loadLeadProfileContent(leadId);
                    }
                }
            });
        }
    }
</script>
@endsection
