<div class="modal-content border-0 shadow-lg">
    <div class="modal-header {{ $mode == 'Next Process' ? 'bg-success' : 'bg-primary' }} py-2 px-3">
        <h6 class="modal-title fw-bold text-white f-12">
            <i class="fa {{ $mode == 'Next Process' ? 'fa-arrow-right' : 'fa-cubes' }} me-1"></i>
            {{ strtoupper($mode) }}: {{ $job_card->job_card_no }}
        </h6>
        <button class="btn-close btn-close-white" type="button" data-bs-dismiss="modal" aria-label="Close" style="font-size: 10px;"></button>
    </div>
    <div class="modal-body p-3 bg-light-soft">
        <!-- Compact Info Card -->
        <div class="row g-2 mb-3">
            <div class="col-6">
                <div class="p-2 bg-white rounded shadow-sm border-start border-3 border-info">
                    <small class="text-muted d-block f-9 text-uppercase fw-bold">Current Stock</small>
                    <span class="h6 mb-0 fw-bold text-dark">{{ number_format($remaining_rolls, 2) }}</span>
                    <small class="text-muted f-10">Rolls</small>
                </div>
            </div>
            <div class="col-6">
                <div class="p-2 bg-white rounded shadow-sm border-start border-3 border-secondary">
                    <small class="text-muted d-block f-9 text-uppercase fw-bold">Total Batch</small>
                    <span class="h6 mb-0 fw-bold text-dark">{{ number_format($total_rolls, 2) }}</span>
                    <small class="text-muted f-10">Rolls</small>
                </div>
            </div>
        </div>

        @if($remaining_rolls > 0)
        <form id="rollOutForm" action="{{ route('job_card.store_roll_out', $job_card->id) }}" method="POST">
            @csrf
            <input type="hidden" name="action_type" value="{{ $mode }}">
            
            <div class="mb-2">
                <label class="form-label fw-bold f-10 text-uppercase text-dark mb-1">Qty to Process (Max: {{ $remaining_rolls }})</label>
                <div class="input-group input-group-sm">
                    <input type="number" step="any" name="rolls_out" id="rolls_out_input" class="form-control fw-bold border-{{ $mode == 'Next Process' ? 'success' : 'primary' }}" max="{{ $remaining_rolls }}" placeholder="Qty..." required autoFocus>
                    <button type="button" class="btn btn-dark fw-bold btn-sm" onclick="$('#rolls_out_input').val({{ $remaining_rolls }})">MAX</button>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label fw-bold f-10 text-uppercase text-dark mb-1">Remarks</label>
                <input type="text" name="remarks" class="form-control form-control-sm" placeholder="Optional notes...">
            </div>
            
            <div class="text-end pt-2 border-top">
                <button type="button" class="btn btn-light btn-sm px-3 me-2" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" id="submitRollOut" class="btn {{ $mode == 'Next Process' ? 'btn-success' : 'btn-primary' }} btn-sm px-4 fw-bold">
                    <i class="fa fa-check-circle me-1"></i> SAVE {{ strtoupper($mode) }}
                </button>
            </div>
        </form>
        @else
        <div class="text-center py-3">
            <i class="fa fa-check-circle text-success f-30 mb-2"></i>
            <p class="text-muted small mb-0 fw-bold">Stock Fully Processed</p>
            <button type="button" class="btn btn-secondary btn-sm mt-2" data-bs-dismiss="modal">Close</button>
        </div>
        @endif
    </div>
</div>

<script>
$('#rollOutForm').off('submit').on('submit', function(e) {
    e.preventDefault();
    let form = $(this);
    let btn = $('#submitRollOut');
    
    if (btn.hasClass('disabled')) return;

    btn.addClass('disabled').html('<i class="fa fa-spinner fa-spin me-2"></i> SAVING...');
    
    $.ajax({
        url: form.attr('action'),
        method: 'POST',
        data: form.serialize(),
        success: function(res) {
            if(res.result == 1) {
                $.notify({ title: 'Success', message: res.message }, { type: 'success' });
                $('#job_card_modal').modal('hide');
                if(typeof get_datatable === 'function') get_datatable();
            } else {
                $.notify({ title: 'Error', message: res.message }, { type: 'danger' });
                btn.removeClass('disabled').html('<i class="fa fa-check-circle me-1"></i> SAVE {{ strtoupper($mode) }}');
            }
        },
        error: function() {
            $.notify({ title: 'System Error', message: 'Something went wrong.' }, { type: 'danger' });
            btn.removeClass('disabled').html('<i class="fa fa-check-circle me-1"></i> SAVE {{ strtoupper($mode) }}');
        }
    });
});
</script>

<style>
.bg-light-soft { background-color: #f7f9fb; }
.f-9 { font-size: 9px; }
.f-10 { font-size: 10px; }
.f-11 { font-size: 11px; }
.f-12 { font-size: 12px; }
.f-30 { font-size: 30px; }
</style>
