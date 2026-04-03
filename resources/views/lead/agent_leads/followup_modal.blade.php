<div class="modal-content">
    <form id="advancedFollowupForm{{$lead->id}}" action="{{ route('lead.agent_leads.followup.store', $lead->id) }}" method="POST">
        @csrf
        <div class="modal-header">
            <h5 class="modal-title">Quick Follow Up: {{ $lead->lead_no }}</h5>
            <div class="ms-3"><span class="badge badge-primary">{{ $lead->status->name }}</span></div>
            <button class="btn-close" type="button" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
            <div class="row">
                <div class="col-md-12 mb-3">
                    <div class="bg-light p-2 rounded d-flex justify-content-between align-items-center">
                        <span class="text-muted f-12">Client: <strong>{{ $lead->agent->name ?? 'N/A' }}</strong></span>
                        <span class="badge badge-primary">{{ $lead->status->name }}</span>
                    </div>
                </div>
                
                <div class="col-md-12 mb-4">
                    <label class="form-label d-block fw-bold">Next Action <span>*</span></label>
                    <div class="d-flex flex-wrap gap-3 mt-2">
                         <div class="form-check">
                            <input class="form-check-input" type="radio" name="next_action" value="continue" id="action_continue{{$lead->id}}" checked>
                            <label class="form-check-label" for="action_continue{{$lead->id}}">Log Discussion Activity</label>
                        </div>
                        @foreach($nextSteps as $step)
                        <div class="form-check">
                            <input class="form-check-input dynamic-next-step" type="radio" name="next_action" value="{{ $step->id }}" id="action_status_{{ $step->id }}_{{$lead->id}}">
                            <label class="form-check-label" for="action_status_{{ $step->id }}_{{$lead->id}}">{{ $step->name }}</label>
                        </div>
                        @endforeach

                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="next_action" value="lost" id="action_lost{{$lead->id}}">
                            <label class="form-check-label" for="action_lost{{$lead->id}}">Lost</label>
                        </div>

                        @if($isLastStep)
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="next_action" value="won" id="action_won{{$lead->id}}">
                            <label class="form-check-label" for="action_won{{$lead->id}}">Won</label>
                        </div>
                        @endif
                    </div>
                </div>



                <div class="col-md-12 mb-3" id="lost_fields{{$lead->id}}" style="display: none;">
                    <label class="form-label fw-bold">Reason for Lost <span>*</span></label>
                    <select name="lost_reason" class="form-select" id="lost_reason_select{{$lead->id}}">
                        <option value="">Select Reason...</option>
                        @foreach($lostReasons ?? [] as $reason)
                            <option value="{{ $reason }}">{{ $reason }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-12 mb-3" id="remarks_container{{$lead->id}}">
                    <label class="form-label fw-bold">Process Discussion (Remarks) <span>*</span></label>
                    <textarea name="remarks" class="form-control" rows="3" placeholder="Describe the discussion or current process status..." required></textarea>
                </div>

                <div class="col-md-12 mb-3" id="next_followup_container{{$lead->id}}" style="display: none;">
                    <label class="form-label fw-bold">Followup after (Days) *</label>
                    <input type="number" name="next_followup_date" class="form-control" id="next_followup_date{{$lead->id}}" value="0">
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" type="button" data-bs-dismiss="modal">Close</button>
            <button class="btn btn-primary" type="submit">Submit Followup</button>
        </div>
    </form>
</div>

<script>
    (function() {
        const leadId = '{{$lead->id}}';
        const $form = $('#advancedFollowupForm' + leadId);

        function updateFields(action) {
            if (action === 'won') {
                $form.find('#lost_fields' + leadId).hide();
                $form.find('#next_followup_container' + leadId).hide().find('input').prop('required', false);
                $form.find('#remarks_container' + leadId).show().find('textarea').prop('required', true);
            } else if (action === 'lost') {
                $form.find('#lost_fields' + leadId).show().find('select').prop('required', true);
                $form.find('#next_followup_container' + leadId).hide().find('input').prop('required', false);
                $form.find('#remarks_container' + leadId).hide().find('textarea').prop('required', false);
            } else {
                $form.find('#lost_fields' + leadId).hide().find('select').prop('required', false);
                $form.find('#next_followup_container' + leadId).hide().find('input').prop('required', false);
                $form.find('#remarks_container' + leadId).show().find('textarea').prop('required', true);
            }
        }

        $form.find('input[name="next_action"]').on('change', function() {
            updateFields($(this).val());
        });

        $form.on('submit', function(e) {
            e.preventDefault();
            const $btn = $(this).find('button[type="submit"]');
            $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Processing...');

            $.ajax({
                url: $(this).attr('action'),
                type: 'POST',
                data: $(this).serialize(),
                success: function(res) {
                    const _actionVal = $form.find('input[name="next_action"]:checked').val();
                    const justWon = (_actionVal === 'won');
                    
                    $('#lead_ajax_modal, #followupModal').modal('hide');
                    
                    if (res.new_status_name && res.new_status_name.toLowerCase().includes('won')) {
                        confetti({ particleCount: 150, spread: 70, origin: { y: 0.6 }, zIndex: 9999 });
                    }

                    if (typeof get_datatable === 'function') get_datatable(); 
                    if (typeof loadLeadProfileContent === 'function') loadLeadProfileContent(leadId);

                    $.notify({ title: 'Success', message: 'Action recorded successfully' }, { type: 'success' });
                },
                error: function() {
                    $.notify({ title: 'Error', message: 'Something went wrong.' }, { type: 'danger' });
                    $btn.prop('disabled', false).html('Submit Followup');
                }
            });
        });

        const currentAction = $form.find('input[name="next_action"]:checked').val() || 'continue';
        updateFields(currentAction);
    })();
</script>
