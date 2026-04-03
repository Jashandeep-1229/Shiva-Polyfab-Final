{{-- Hold Order Modal Content (loaded via AJAX into #ajax_html2 inside #job_card_modal) --}}
<div class="modal-content border-0">
    <div class="modal-header" style="background: linear-gradient(135deg, #dc3545, #a71d2a); color: #fff;">
        <h5 class="modal-title mb-0">
            <i class="fa fa-pause-circle me-2"></i>
            Place Order On Hold
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
    </div>

    <div class="modal-body p-4">
        {{-- Job Card Info Banner --}}
        <div class="d-flex align-items-center gap-3 bg-light border rounded p-3 mb-4">
            <div class="rounded-circle d-flex align-items-center justify-content-center"
                 style="width:46px;height:46px;background:#fff3cd;flex-shrink:0;">
                <i class="fa fa-exclamation-triangle text-warning" style="font-size:20px;"></i>
            </div>
            <div>
                <div class="fw-bold text-dark" style="font-size:14px;">
                    {{ $job_card->job_card_no ?? 'N/A' }}
                    &nbsp;|&nbsp; {{ $job_card->name_of_job ?? 'N/A' }}
                </div>
                <div class="text-muted" style="font-size:12px;">
                    Current Process: <span class="fw-bold text-primary">{{ $job_card->job_card_process ?? 'N/A' }}</span>
                    &nbsp;&bull;&nbsp; Type: <span class="fw-bold">{{ $job_card->job_type ?? 'N/A' }}</span>
                </div>
            </div>
        </div>

        {{-- Warning Box --}}
        <div class="alert alert-warning py-2 px-3 d-flex align-items-center gap-2" style="font-size:13px;">
            <i class="fa fa-lock text-warning"></i>
            <span>Placing this order on HOLD will <strong>block further process movement</strong> until it is released.</span>
        </div>

        <form id="hold_order_form">
            @csrf
            <input type="hidden" id="hold_job_card_id" value="{{ $job_card->id }}">

            <div class="mb-3">
                <label class="form-label fw-bold" style="font-size:12px;text-transform:uppercase;color:#334155;">
                    Hold Reason <span class="text-danger">*</span>
                </label>
                <select name="hold_reason_id" id="hold_reason_id" class="form-select form-select-sm" required>
                    <option value="">-- Select Reason --</option>
                    @foreach($hold_reasons as $reason)
                        <option value="{{ $reason->id }}">{{ $reason->name }}</option>
                    @endforeach
                </select>
                @if($hold_reasons->isEmpty())
                    <div class="text-warning mt-1" style="font-size:11px;">
                        <i class="fa fa-info-circle"></i> No hold reasons configured. Please add reasons with type <strong>"hold"</strong> in the Blockage Reasons master.
                    </div>
                @endif
            </div>

            <div class="mb-3">
                <label class="form-label fw-bold" style="font-size:12px;text-transform:uppercase;color:#334155;">
                    Additional Notes <span class="text-muted fw-normal">(Optional)</span>
                </label>
                <textarea name="hold_notes" id="hold_notes" class="form-control form-control-sm" rows="3"
                    placeholder="Add details about why this order is being held..."></textarea>
            </div>
        </form>
    </div>

    <div class="modal-footer border-0 pt-0">
        <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">
            <i class="fa fa-times me-1"></i> Cancel
        </button>
        <button type="button" class="btn btn-danger btn-sm px-4" id="confirm_hold_btn"
                @if($hold_reasons->isEmpty()) disabled @endif>
            <i class="fa fa-pause-circle me-1"></i> Confirm Hold
        </button>
    </div>
</div>

<script>
$(document).ready(function () {
    $('#confirm_hold_btn').off('click').on('click', function () {
        var reasonId = $('#hold_reason_id').val();
        if (!reasonId) {
            $.notify({ title: 'Validation', message: 'Please select a Hold Reason.' }, { type: 'warning' });
            return;
        }

        var $btn = $(this);
        var jobCardId = $('#hold_job_card_id').val();
        var url = "{{ route('job_card.hold', ':id') }}".replace(':id', jobCardId);

        $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin me-1"></i> Placing on Hold...');

        $.ajax({
            url: url,
            method: 'POST',
            data: {
                _token: "{{ csrf_token() }}",
                hold_reason_id: reasonId,
                hold_notes: $('#hold_notes').val()
            },
            success: function (data) {
                if (data.result == 1) {
                    $.notify({ title: 'Held!', message: data.message }, { type: 'warning', placement: { from: 'top', align: 'right' } });
                    $('#job_card_modal').modal('hide');
                    if (typeof update_single_row === 'function') {
                        update_single_row(jobCardId);
                    } else if (typeof get_datatable === 'function') {
                        var page = Number($(".pages").find('span[aria-current="page"] span').text()) || 1;
                        get_datatable(page);
                    }
                    if (typeof performSearch === 'function') performSearch();
                } else {
                    $.notify({ title: 'Error', message: data.message }, { type: 'danger' });
                    $btn.prop('disabled', false).html('<i class="fa fa-pause-circle me-1"></i> Confirm Hold');
                }
            },
            error: function (xhr) {
                var msg = 'Request failed.';
                if (xhr.responseJSON && xhr.responseJSON.errors) {
                    var errs = xhr.responseJSON.errors;
                    var firstErr = Object.values(errs)[0];
                    msg = Array.isArray(firstErr) ? firstErr[0] : firstErr;
                }
                $.notify({ title: 'Error', message: msg }, { type: 'danger' });
                $btn.prop('disabled', false).html('<i class="fa fa-pause-circle me-1"></i> Confirm Hold');
            }
        });
    });
});
</script>
