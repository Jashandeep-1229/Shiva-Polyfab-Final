<div class="modal-content">
    <form id="overallFollowupForm_ajax" method="POST" action="{{ route('lead.agent_leads.overall_followup.store', $agent->id) }}">
        @csrf
        <div class="modal-header">
            <h5 class="modal-title">Overall Agent Followup: {{ $agent->name }}</h5>
            <button class="btn-close" type="button" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
            @if(isset($pendingFollowup) && $pendingFollowup->remarks)
                <div class="alert alert-info py-2 px-3 mb-3" style="border-left: 4px solid #007bff; border-radius: 4px;">
                    <label class="fw-bold d-block mb-1" style="font-size: 11px; color: #0056b3;"><i class="fa fa-info-circle"></i> PREVIOUSLY SCHEDULED TOPIC:</label>
                    <div class="f-13 text-dark">{{ $pendingFollowup->remarks }}</div>
                </div>
            @endif

            <div class="mb-3">
                <label class="form-label fw-bold">Process Discussion (Remarks) <span id="remarks_required_star_ajax">*</span></label>
                <textarea name="remarks" id="remarks_textarea_ajax" class="form-control" rows="3" required placeholder="Describe the discussion or current process status..."></textarea>
            </div>
            
            <div class="mb-3">
                <label class="form-label fw-bold f-13">Schedule Next Followup?</label>
                <select name="has_next" class="form-select" id="has_next_select_ajax" onchange="toggleNextFollowupFieldAjax(this.value)">
                    <option value="yes">Yes, Schedule Next</option>
                    <option value="no">No, Just Complete Current</option>
                </select>            </div>

            <div id="next_days_wrapper_ajax" class="mb-3">
                <div class="row">
                    <div class="col-12">
                        <label class="form-label fw-bold f-13">Next Followup after (Days)</label>
                        <input type="number" name="next_followup_days" class="form-control" value="2" min="1">
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-light" type="button" data-bs-dismiss="modal">Cancel</button>
            <button class="btn btn-primary" type="submit">Update Agent Status</button>
        </div>
    </form>
</div>

<script>
    function toggleNextFollowupFieldAjax(val) {
        if (val === 'yes') {
            $('#next_days_wrapper_ajax').slideDown();
            $('#remarks_textarea_ajax').prop('required', true);
            $('#remarks_required_star_ajax').show();
        } else {
            $('#next_days_wrapper_ajax').slideUp();
            $('#remarks_textarea_ajax').prop('required', false);
            $('#remarks_required_star_ajax').hide();
        }
    }

    $('#overallFollowupForm_ajax').on('submit', function(e) {
        e.preventDefault();
        var $btn = $(this).find('button[type="submit"]');
        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Processing...');

        $.ajax({
            url: $(this).attr('action'),
            type: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                $('#lead_ajax_modal, #advancedFollowupModal').modal('hide');
                $btn.prop('disabled', false).html('Update Agent Status');
                $.notify({title:'Success', message:response.message}, {type:'success'});
                
                // Trigger success event for persistence logic
                $(document).trigger('ajax_modal_success');

                // Refresh datatable if on index, or profile if on show, or reload for unified followup
                if (typeof reloadTimeline === 'function') reloadTimeline();
                else if (typeof get_datatable === 'function') get_datatable();
                else if (typeof loadLeadProfileContent === 'function') loadLeadProfileContent(typeof currentLeadId !== 'undefined' ? currentLeadId : null);
                else location.reload();
            },
            error: function() {
                $.notify({title:'Error', message:'Failed to update overall followup'}, {type:'danger'});
                $btn.prop('disabled', false).html('Update Agent Status');
            }
        });
    });
</script>
