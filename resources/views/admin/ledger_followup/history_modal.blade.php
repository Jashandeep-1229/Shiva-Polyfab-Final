<div class="modal fade" id="historyModal" tabindex="-1" aria-hidden="true" style="z-index: 1060 !important;">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-dark text-white">
                <div>
                    <h5 class="modal-title fw-bold mb-0">Followup History</h5>
                    <small class="text-uppercase fw-bold text-muted-light">{{ $followup->customer->name ?? 'N/A' }} | {{ $followup->subject }}</small>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4 bg-light">
                @if($followup->status == 'Closed')
                    <div class="text-center py-4 bg-white rounded-4 border shadow-sm">
                        <h5 class="fw-bold text-dark mb-1 text-uppercase">{{ $followup->subject }} ({{ $followup->completedBy->name ?? ($followup->user->name ?? 'N/A') }})</h5>
                        <div class="fw-bold text-success mb-2">STATUS: Closed</div>
                        <div class="text-muted small fw-bold">
                            {!! nl2br(e($followup->histories->sortByDesc('id')->first()->remarks ?? 'No final remarks recorded.')) !!}
                        </div>
                    </div>
                @else
                    <div class="row g-4">
                        <div class="col-md-7">
                            <div class="h6 fw-bold text-uppercase text-muted border-bottom pb-2 mb-3">Activity Log</div>
                            <div class="history-list px-2 overflow-auto" style="max-height: 480px;">
                                 @foreach($followup->histories->sortBy('id') as $h)
                                     <div class="history-item">
                                         <div class="d-flex align-items-center mb-1">
                                             <span class="history-dot {{ $h->status == 1 ? 'bg-primary' : 'bg-success' }}"></span>
                                             <div class="d-flex flex-column small">
                                                 <span class="fw-bold text-dark">Scheduled: {{ date('j M, Y H:i', strtotime($h->followup_date_time)) }}</span>
                                                 @if($h->complete_date_time)
                                                     <span class="text-success extra-small fw-bold">
                                                         Completed: {{ date('j M, Y H:i', strtotime($h->complete_date_time)) }} 
                                                         <span class="ms-1 px-2 py-0 bg-success text-white rounded-pill" style="font-size: 8px;">{{ $h->total_no_of_days }} DAYS DELAY</span>
                                                     </span>
                                                 @else
                                                     <span class="text-primary extra-small fw-bold">ACTIVE TASK</span>
                                                 @endif
                                             </div>
                                             <span class="ms-auto badge bg-light text-dark border extra-small fw-bold">{{ $h->user->name ?? 'System' }}</span>
                                         </div>
                                         <div class="history-line">
                                             <div class="p-3 rounded bg-white border shadow-sm small">
                                                 @if($h->remarks)
                                                    <div class="text-dark fw-bold" style="font-size: 13px;">{!! nl2br(e($h->remarks)) !!}</div>
                                                 @else
                                                    <div class="text-muted italic small">No remarks for this step.</div>
                                                 @endif
                                             </div>
                                         </div>
                                     </div>
                                 @endforeach
                            </div>
                        </div>
                        <div class="col-md-5">
                            @php 
                                $has_transaction = \DB::table('customer_ledgers')
                                    ->where('customer_id', $followup->customer_id)
                                    ->where('transaction_date', '>', $followup->created_at)
                                    ->where('dr_cr', 'Cr')
                                    ->exists();
                            @endphp
                            <div class="card border-0 shadow-sm rounded-4">
                                <div class="card-body p-4">
                                    <h6 class="fw-bold text-warning mb-3"><i class="fa fa-pencil-square-o me-1"></i> Add Interaction / Update</h6>
                                    <form id="update_thread_form">
                                        @csrf
                                        <input type="hidden" name="parent_id" value="{{ $followup->id }}">
                                        
                                        <div class="mb-3">
                                            <label class="form-label fw-bold small">Current Interaction Status</label>
                                            <div class="p-2 bg-info-subtle border border-info-subtle rounded small mb-2 text-dark">
                                                <i class="fa fa-info-circle me-1"></i> Saving will update the current task and potentially schedule a new one.
                                            </div>
                                            <div class="btn-group w-100" role="group">
                                                <input type="radio" class="btn-check" name="status" id="update_continue" value="Continue" checked onchange="$('#next_date_div').slideDown()">
                                                <label class="btn btn-outline-warning fw-bold py-2" for="update_continue">CONTINUE</label>

                                                @if($has_transaction)
                                                    <input type="radio" class="btn-check" name="status" id="update_closed" value="Closed" onchange="$('#next_date_div').slideUp()">
                                                    <label class="btn btn-outline-success fw-bold py-2" for="update_closed">CLOSED</label>
                                                @endif
                                            </div>
                                            @if(!$has_transaction)
                                                <div class="mt-2 text-danger extra-small fw-bold">
                                                    <i class="fa fa-info-circle me-1"></i> CLOSED option hidden until a new Credit entry (Payment) is recorded.
                                                </div>
                                            @endif
                                        </div>

                                        <div id="next_date_div" class="mb-3">
                                            <label class="form-label fw-bold small">Next Followup After (Days)</label>
                                            <div class="input-group">
                                                <input type="number" id="update_days" class="form-control" value="1" min="1" oninput="calculateFollowupDate(this.value, 'update_fup_date', 'update_fup_display')">
                                                <span class="input-group-text bg-light text-dark fw-bold small">Days</span>
                                            </div>
                                            <input type="hidden" name="followup_date_time" id="update_fup_date">
                                            <div class="extra-small text-muted mt-1"><i class="fa fa-clock-o me-1"></i> Scheduled for: <span id="update_fup_display" class="fw-bold">...</span> </div>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label class="form-label fw-bold small">Interaction Remarks / Response</label>
                                            <textarea name="remarks" class="form-control" rows="4" placeholder="Mention next steps or customer's promise date..." required></textarea>
                                        </div>
                                        
                                        <button type="submit" class="btn btn-warning w-100 fw-bold py-2 shadow-sm rounded-3 mt-2">
                                            <i class="fa fa-refresh me-1"></i> SAVE & UPDATE
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
            </div>
        </div>
    </div>
</div>

<script>
    function calculateFollowupDate(days, targetId, displayId) {
        if(!days || days < 0) days = 0;
        let date = new Date();
        date.setDate(date.getDate() + parseInt(days));
        
        let year = date.getFullYear();
        let month = String(date.getMonth() + 1).padStart(2, '0');
        let day = String(date.getDate()).padStart(2, '0');
        let formatted = `${year}-${month}-${day}T12:00`;
        
        $(`#${targetId}`).val(formatted);
        
        const options = { day: '2-digit', month: 'short', year: 'numeric' };
        $(`#${displayId}`).text(date.toLocaleDateString('en-GB', options) + ' 12:00 PM');
    }

    $(document).ready(function() {
        calculateFollowupDate(1, 'update_fup_date', 'update_fup_display');
    });

    $('#update_thread_form').submit(function(e){
        e.preventDefault();
        var $btn = $(this).find('button[type="submit"]');
        $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin me-1"></i> SAVING...');
        
        $.ajax({
            url: '{{ route("ledger_followup.update_thread") }}',
            type: 'POST',
            data: $(this).serialize(),
            success: function(res){
                if(res.result == 1){
                    $.notify({title:'Success', message:res.message}, {type:'success'});
                    $('#historyModal').modal('hide');
                    if (typeof get_datatable === 'function') {
                        get_datatable();
                    } else {
                        location.reload();
                    }
                } else {
                    $.notify({title:'Error', message:res.message}, {type:'danger'});
                }
                $btn.prop('disabled', false).html('SAVE & UPDATE');
            },
            error: function(xhr){
                let msg = xhr.responseJSON ? xhr.responseJSON.message : 'Something went wrong';
                $.notify({title:'Error', message:msg}, {type:'danger'});
                $btn.prop('disabled', false).html('SAVE & UPDATE');
            }
        });
    });
</script>
