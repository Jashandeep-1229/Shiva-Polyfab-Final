<div class="modal-content">
    <form id="advancedFollowupForm{{$lead->id}}" action="{{ route('lead.followup.store', $lead->id) }}" method="POST">
        @csrf
        <div class="modal-header">
            <h5 class="modal-title">Quick Follow Up: {{ $lead->name }}</h5>
            <div class="ms-3"><span class="badge badge-primary">{{ $lead->status->name }}</span></div>
            <button class="btn-close" type="button" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
            <div class="row">
                <div class="col-md-12 mb-3">
                    <div class="bg-light p-2 rounded d-flex justify-content-between align-items-center">
                        <span class="text-muted f-12">Current Progress:</span>
                        <span class="badge badge-primary">{{ $lead->status->name }}</span>
                    </div>
                </div>
                
                <div class="col-md-12 mb-4">
                    <label class="form-label d-block">Next Action <span>*</span></label>
                    <div class="d-flex flex-wrap gap-3 mt-2">
                         <div class="form-check">
                            <input class="form-check-input" type="radio" name="next_action" value="continue" id="action_continue{{$lead->id}}" checked>
                            <label class="form-check-label" for="action_continue{{$lead->id}}">Continue Follow Up</label>
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
                <div class="col-md-12 mb-3" id="dynamic_fields_wrapper{{$lead->id}}" style="display: none;">
                    <div class="row" id="dynamic_fields_container{{$lead->id}}">
                        <!-- Dynamic fields will be injected here -->
                    </div>
                </div>



                <div class="col-md-12 mb-3" id="lost_fields{{$lead->id}}" style="display: none;">
                    <label class="form-label">Reason for Lost <span>*</span></label>
                    <select name="lost_reason" class="form-select" id="lost_reason_select{{$lead->id}}">
                        <option value="">Select Reason...</option>
                        @foreach($lostReasons ?? [] as $reason)
                            <option value="{{ $reason }}">{{ $reason }}</option>
                        @endforeach
                    </select>
                    
                    <div id="other_lost_reason_wrapper{{$lead->id}}" class="mt-2" style="display: none;">
                        <textarea name="other_lost_reason" class="form-control" rows="2" placeholder="Specify other reason..."></textarea>
                    </div>
                </div>

                <div class="col-md-12 mb-3" id="remarks_container{{$lead->id}}">
                    <label class="form-label">Communication Notes (Remarks) <span>*</span></label>
                    <textarea name="remarks" class="form-control" rows="3" placeholder="What happened in the communication?" required></textarea>
                </div>

                <input type="hidden" name="followup_date" value="{{ date('Y-m-d\TH:i') }}">

                <div class="col-md-12 mb-3" id="next_followup_container{{$lead->id}}">
                    <label class="form-label">Followup after (Days) <span>*</span></label>
                    <input type="number" name="next_followup_date" class="form-control" id="next_followup_date{{$lead->id}}" value="1" min="0" required>
                    <small class="text-muted">Followup will be scheduled at 12:00 PM</small>
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
            const _statusFields = @json(\App\Models\LeadStatus::pluck('form_fields', 'id'));
            const _currentStepData = @json($lead->stepDetails->pluck('field_value', 'field_key'));
            const leadId = '{{$lead->id}}';
            
            // Scope selectors to the containing element (modal or local div)
            const $scope = $('#advancedFollowupForm' + leadId).length ? $('#advancedFollowupForm' + leadId).parent() : $('form').last().parent();
            const $form = $('#advancedFollowupForm' + leadId);

            function toggleDynamicFields(action, lId) {
                $scope.find('#dynamic_fields_wrapper' + lId).hide();
                $scope.find('#dynamic_fields_container' + lId).empty();

                let targetStatusId = null;
                if (action === 'continue') {
                    targetStatusId = '{{ $lead->status_id }}';
                } else if (!isNaN(action)) {
                    targetStatusId = action;
                }

                if (targetStatusId && _statusFields[targetStatusId]) {
                    let fieldsString = _statusFields[targetStatusId];
                    if (fieldsString) {
                        let fields = fieldsString.split(',');
                        let html = '';
                        fields.forEach(field => {
                            field = field.trim();
                            if (field) {
                                let fieldKey = field.toLowerCase().replace(/ /g, '_');
                                let existingValue = _currentStepData[fieldKey] || '';
                                html += `
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">${field}</label>
                                        <input type="text" name="${fieldKey}" class="form-control f-12" value="${existingValue}" placeholder="Enter ${field}">
                                    </div>`;
                            }
                        });
                        if (html) {
                            $scope.find('#dynamic_fields_container' + lId).html(html);
                            $scope.find('#dynamic_fields_wrapper' + lId).show();
                        }
                    }
                }
            }

            function updateFields(action) {
                if (action === 'won') {
                    $scope.find('#lost_fields' + leadId).hide().find('select, textarea').prop('required', false);
                    $scope.find('#next_followup_container' + leadId).hide().find('input').prop('required', false);
                    $scope.find('#remarks_container' + leadId).show().find('textarea').prop('required', true);
                } else if (action === 'lost') {
                    $scope.find('#lost_fields' + leadId).show().find('select').prop('required', true);
                    $scope.find('#won_fields' + leadId).hide().find('input').prop('required', false);
                    $scope.find('#next_followup_container' + leadId).hide().find('input').prop('required', false);
                    $scope.find('#remarks_container' + leadId).hide().find('textarea').prop('required', false);
                } else {
                    $scope.find('#lost_fields' + leadId).hide().find('select, textarea').prop('required', false);
                    $scope.find('#next_followup_container' + leadId).show().find('input').prop('required', true);
                    $scope.find('#remarks_container' + leadId).show().find('textarea').prop('required', true);
                }
                toggleDynamicFields(action, leadId);
            }

            // Bind change listener
            $form.find('input[name="next_action"]').on('change', function() {
                updateFields($(this).val());
            });

            // Lost reason "Other" toggle
            $form.find('#lost_reason_select' + leadId).on('change', function() {
                if($(this).val() === 'Other') {
                    $scope.find('#other_lost_reason_wrapper' + leadId).show().find('textarea').prop('required', true);
                } else {
                    $scope.find('#other_lost_reason_wrapper' + leadId).hide().find('textarea').prop('required', false);
                }
            });

            // Form submission handling
            $form.off('submit').on('submit', function(e) {
                e.preventDefault();
                const $btn = $(this).find('button[type="submit"]');
                const $origText = $btn.html();
                $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Processing...');

                $.ajax({
                    url: $(this).attr('action'),
                    type: 'POST',
                    data: $(this).serialize(),
                    success: function(res) {
                        const _actionVal = $form.find('input[name="next_action"]:checked').val();
                        const justWon = (_actionVal === 'won');
                        
                        // Handle modal hiding if open
                        $('#lead_ajax_modal, #advancedFollowupModal').modal('hide');
                        
                        // Close inline if open
                        if($scope.hasClass('accordion-body')) {
                             $scope.closest('.accordion-collapse').collapse('hide');
                        }

                        if (typeof get_datatable === 'function') get_datatable(); 
                        if (typeof reloadTimeline === 'function') reloadTimeline(res.lead_id, justWon);

                        if (res.new_status_name && $('#sidebar-status-name').length) {
                             $('#sidebar-status-name').text(res.new_status_name);
                        }

                        if (!justWon) {
                            $.notify({ title: 'Success', message: 'Action recorded successfully' }, { type: 'success' });
                        }
                    },
                    error: function() {
                        $.notify({ title: 'Error', message: 'Something went wrong.' }, { type: 'danger' });
                        $btn.prop('disabled', false).html($origText);
                    }
                });
            });

            // Initial trigger
            const currentAction = $form.find('input[name="next_action"]:checked').val() || 'continue';
            updateFields(currentAction);
        })();
    </script>
