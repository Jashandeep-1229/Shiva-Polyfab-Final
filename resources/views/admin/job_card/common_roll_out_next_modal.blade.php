<div class="modal-content border-0 shadow-lg">
    <div class="modal-header bg-success text-white py-2 px-3">
        <h6 class="modal-title fw-bold text-white"><i class="fa fa-arrow-right me-2"></i>MOVE TO NEXT PROCESS</h6>
        <button class="btn-close btn-close-white" type="button" data-bs-dismiss="modal" aria-label="Close"></button>
    </div>
    <div class="modal-body p-3 bg-light-soft">
        <div class="d-flex justify-content-between align-items-center mb-3 p-2 bg-white rounded shadow-sm border-start border-3 border-success">
            <div>
                <small class="text-secondary text-uppercase fw-bold f-8 d-block">Stock Left</small>
                <h5 class="mb-0 fw-bold text-dark">{{ number_format($remaining_rolls, 2) }} <small class="f-10 text-muted">Rolls</small></h5>
            </div>
            <div class="text-end">
                <small class="text-secondary text-uppercase fw-bold f-8 d-block">Total Batch</small>
                <h5 class="mb-0 fw-bold text-muted">{{ number_format($total_rolls, 2) }}</h5>
            </div>
        </div>

        <form id="rollOutNextForm" action="{{ route('job_card.store_roll_out', $job_card->id) }}" method="POST" class="bg-white p-3 rounded shadow-sm border">
            @csrf
            <input type="hidden" name="action_type" value="Next Process">
            
            <div class="mb-2">
                <label class="form-label f-10 fw-bold text-dark text-uppercase mb-1">Rolls to Move</label>
                <div class="input-group input-group-sm">
                    <input type="number" step="any" name="rolls_out" id="rolls_out_next" class="form-control fw-bold border-success" max="{{ $remaining_rolls }}" placeholder="Qty..." required autoFocus>
                    <button type="button" class="btn btn-dark fw-bold" onclick="$('#rolls_out_next').val({{ $remaining_rolls }})">MAX</button>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label f-10 fw-bold text-dark text-uppercase mb-1">Remarks</label>
                <input type="text" name="remarks" class="form-control form-control-sm" placeholder="Internal notes...">
            </div>

            <button type="submit" id="submitNext" class="btn btn-success btn-sm w-100 fw-bold shadow-sm py-2">
                <i class="fa fa-check-circle me-1"></i> CONFIRM MOVE
            </button>
            <p class="text-muted f-9 text-center mt-2 mb-0">Creates sub-order: <b>{{ $job_card->job_card_no }}-R{{ $job_card->roll_outs()->where('action_type', 'Next Process')->count() + 1 }}</b></p>
        </form>
    </div>
</div>

<script>
$('#rollOutNextForm').off('submit').on('submit', function(e) {
    e.preventDefault();
    e.stopImmediatePropagation();
    let form = $(this);
    let btn = $('#submitNext');
    if (btn.hasClass('disabled')) return;
    btn.addClass('disabled').html('<i class="fa fa-spinner fa-spin me-2"></i> MOVING...');
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
                btn.removeClass('disabled').html('<i class="fa fa-check-circle me-1"></i> CONFIRM MOVE');
            }
        },
        error: function() {
            $.notify({ title: 'System Error', message: 'Something went wrong.' }, { type: 'danger' });
            btn.removeClass('disabled').html('<i class="fa fa-check-circle me-1"></i> CONFIRM MOVE');
        }
    });
});
</script>

<style>
.f-8 { font-size: 8px; }
.f-9 { font-size: 9px; }
.f-10 { font-size: 10px; }
.bg-light-soft { background-color: #f8fafc; }
</style>
