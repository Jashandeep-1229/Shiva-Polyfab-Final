<div class="modal-content border-0 shadow-lg">
    <div class="modal-header bg-primary text-white py-2 px-3">
        <h6 class="modal-title fw-bold text-white"><i class="fa fa-cubes me-2"></i>MANUAL ROLL OUT</h6>
        <button class="btn-close btn-close-white" type="button" data-bs-dismiss="modal" aria-label="Close"></button>
    </div>
    <div class="modal-body p-3 bg-light-soft">
        <div class="row g-2 mb-3 text-center">
            <div class="col-6">
                <small class="text-secondary text-uppercase fw-bold f-8 d-block">Produced</small>
                <h6 class="mb-0 fw-bold text-dark">{{ number_format($total_rolls, 2) }}</h6>
            </div>
            <div class="col-6">
                <small class="text-secondary text-uppercase fw-bold f-8 d-block">Stock Left</small>
                <h6 class="mb-0 fw-bold text-danger">{{ number_format($remaining_rolls, 2) }}</h6>
            </div>
        </div>

        @if($remaining_rolls > 0)
        <form id="rollOutForm" action="{{ route('job_card.store_roll_out', $job_card->id) }}" method="POST" class="bg-white p-3 rounded shadow-sm border">
            @csrf
            <input type="hidden" name="action_type" value="Manual Out">
            <div class="row g-2">
                <div class="col-5">
                    <label class="form-label f-9 fw-bold text-dark text-uppercase mb-0">Qty to Out</label>
                    <div class="input-group input-group-sm">
                        <input type="number" step="any" name="rolls_out" id="rolls_out_input" class="form-control fw-bold" max="{{ $remaining_rolls }}" placeholder="Max: {{ $remaining_rolls }}" required autoFocus>
                        <button type="button" class="btn btn-dark px-2 fw-bold" onclick="$('#rolls_out_input').val({{ $remaining_rolls }})">MAX</button>
                    </div>
                </div>
                <div class="col-7">
                    <label class="form-label f-9 fw-bold text-dark text-uppercase mb-0">Remarks</label>
                    <input type="text" name="remarks" class="form-control form-control-sm" placeholder="Reason for manual out...">
                </div>
                <div class="col-12 mt-3">
                    <button type="submit" id="submitRollOut" class="btn btn-primary w-100 fw-bold shadow-sm">
                        <i class="fa fa-save me-1"></i> SAVE MANUAL OUT
                    </button>
                </div>
            </div>
        </form>
        @else
        <div class="alert alert-warning text-center f-10 mb-0">No rolls left for manual out.</div>
        @endif
    </div>
</div>

<script>
$('#rollOutForm').off('submit').on('submit', function(e) {
    e.preventDefault();
    e.stopImmediatePropagation();
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
                btn.removeClass('disabled').html('<i class="fa fa-save me-1"></i> SAVE MANUAL OUT');
            }
        },
        error: function() {
            $.notify({ title: 'System Error', message: 'Something went wrong.' }, { type: 'danger' });
            btn.removeClass('disabled').html('<i class="fa fa-save me-1"></i> SAVE MANUAL OUT');
        }
    });
});
</script>

<style>
.f-8 { font-size: 8px; }
.f-9 { font-size: 9px; }
.f-10 { font-size: 10px; }
</style>
