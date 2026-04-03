<div class="modal-dialog">
    <form id="add_master_vendor_form" class="modal-content" action="{{ route('master_vendor.store') }}" method="POST">
        @csrf
        <div class="modal-header bg-dark text-white">
            <h5 class="modal-title">Add Vendor for {{ $type }} - <span class="text-warning">{{ $master->name ?? '' }}</span></h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
            <input type="hidden" name="master_type" value="{{ $type }}">
            <input type="hidden" name="master_id" value="{{ $id }}">
            
            <div class="mb-3">
                <label class="form-label">Vendor Name <span class="text-danger">*</span></label>
                <input type="text" name="name" class="form-control form-control-sm" required placeholder="Enter Company Name">
            </div>

            <div class="mb-3">
                <label class="form-label">Phone No <span class="text-danger">*</span></label>
                <input type="text" name="phone_no" class="form-control form-control-sm" required placeholder="Enter Phone No">
                <small class="text-muted f-12">Phone No must be unique for this item.</small>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Close</button>
            <button type="submit" class="btn btn-primary btn-sm" id="save_vendor_btn">Save Vendor</button>
        </div>
    </form>
</div>

<script>
    $('#add_master_vendor_form').on('submit', function(e) {
        e.preventDefault();
        var $btn = $('#save_vendor_btn');
        $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin me-2"></i> Saving...');

        $.ajax({
            url: $(this).attr('action'),
            method: 'POST',
            data: $(this).serialize(),
            success: function(data) {
                if (data.result == 1) {
                    $.notify({ title: 'Success', message: data.message }, { type: 'success' });
                    $('#vendorModal').modal('hide');
                } else if (data.result == -1) {
                    $.notify({ title: 'Alert', message: data.message }, { type: 'warning' });
                } else {
                    $.notify({ title: 'Error', message: data.message }, { type: 'danger' });
                }
                $btn.prop('disabled', false).html('Save Vendor');
            },
            error: function() {
                $.notify({ title: 'Error', message: 'Something went wrong!' }, { type: 'danger' });
                $btn.prop('disabled', false).html('Save Vendor');
            }
        });
    });
</script>
