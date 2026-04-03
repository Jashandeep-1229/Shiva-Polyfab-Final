<div class="modal fade" id="editEntryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <form action="{{route('customer_ledger.store_payment')}}" method="POST" id="edit_ledger_form" class="modal-content border-0 shadow-lg">
            @csrf
            <input type="hidden" name="id" value="{{ $ledger->id }}">
            <input type="hidden" name="customer_id" value="{{ $ledger->customer_id }}">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title fw-bold"><i class="fa fa-edit me-2"></i> Edit Ledger Entry #{{ $ledger->id }}</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-bold small text-uppercase mb-1">Transaction Date</label>
                        <input type="date" name="date" value="{{ $ledger->transaction_date }}" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold small text-uppercase mb-1">Amount</label>
                        <input type="text" name="amount" value="{{ number_format($ledger->grand_total_amount, 3, '.', '') }}" class="form-control amount-decimal" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold small text-uppercase mb-1">Entry Type</label>
                        <select name="type" id="edit_entry_type" class="form-select" onchange="toggleEditPaymentMethod()">
                            <option value="Cr" {{ $ledger->dr_cr == 'Cr' ? 'selected' : '' }}>Payment Received (Cr)</option>
                            <option value="Dr" {{ $ledger->dr_cr == 'Dr' ? 'selected' : '' }}>Additional Due (Dr)</option>
                        </select>
                    </div>
                    <div class="col-md-6" id="edit_payment_method_val">
                        <label class="form-label fw-bold small text-uppercase mb-1">Payment Method</label>
                        <select name="payment_method_id" id="edit_payment_method_id" class="form-select">
                            <option value="">Select Method...</option>
                            @foreach($payment_methods as $method)
                                <option value="{{ $method->id }}" {{ $ledger->payment_method_id == $method->id ? 'selected' : '' }}>{{ $method->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-12">
                        <label class="form-label fw-bold small text-uppercase mb-1">Remarks / Note <span class="text-danger">*</span></label>
                        <textarea name="remarks" class="form-control" rows="2" oninput="this.value = this.value.toUpperCase()" required>{{ $ledger->remarks }}</textarea>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="submit" id="submit_edit_ledger" class="btn btn-info px-4 fw-bold text-white">UPDATE ENTRY</button>
            </div>
        </form>
    </div>
</div>

<script>
    function toggleEditPaymentMethod() {
        if($('#edit_entry_type').val() == 'Dr') {
            $('#edit_payment_method_val').hide().find('select').prop('required', false).val('');
        } else {
            $('#edit_payment_method_val').show().find('select').prop('required', true);
        }
    }
    toggleEditPaymentMethod();

    $('#edit_ledger_form').on('submit', function(e){
        e.preventDefault();
        var form = $(this);
        var btn = $('#submit_edit_ledger');
        btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin me-2"></i> UPDATING...');

        $.ajax({
            url: form.attr('action'),
            type: 'POST',
            data: form.serialize(),
            success: function(data){
                if(data.result == 1){
                    $.notify({ title:'Success', message:data.message }, { type:'success' });
                    $('#editEntryModal').modal('hide');
                    get_datatable();
                } else {
                    $.notify({ title:'Error', message:data.message }, { type:'danger' });
                    btn.prop('disabled', false).html('UPDATE ENTRY');
                }
            },
            error: function(xhr){
                let msg = xhr.responseJSON ? xhr.responseJSON.message : 'Error processing request.';
                $.notify({ title:'Error', message:msg }, { type:'danger' });
                btn.prop('disabled', false).html('UPDATE ENTRY');
            }
        });
    });
</script>
