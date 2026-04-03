<form id="edit_stock_form" action="{{ route('common_stock.update', $manage_stock->id) }}" method="POST">
    @csrf
    <div class="modal-header bg-warning text-white">
        <h5 class="modal-title fw-bold"><i class="fa fa-pencil me-2"></i>Edit Stock Record</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
    </div>
    <div class="modal-body p-4">
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label fw-600 f-13">Date</label>
                <input type="date" name="date" class="form-control" value="{{ $manage_stock->date->format('Y-m-d') }}" required>
            </div>
            <div class="col-md-6">
                <label class="form-label fw-600 f-13">Type</label>
                <select name="in_out" id="modal_in_out" class="form-select" onchange="calculate_modal_new_stock()" required>
                    <option value="In" {{ $manage_stock->in_out == 'In' ? 'selected' : '' }}>Stock IN</option>
                    <option value="Out" {{ $manage_stock->in_out == 'Out' ? 'selected' : '' }}>Stock OUT</option>
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label fw-600 f-13">Color</label>
                <select name="color_id" id="modal_color_id" class="form-select select2-modal" onchange="get_current_stock_modal()" required>
                    @foreach($colors as $color)
                        <option value="{{ $color->id }}" {{ $manage_stock->color_id == $color->id ? 'selected' : '' }}>{{ $color->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label fw-600 f-13">Size</label>
                <select name="size_id" id="modal_size_id" class="form-select select2-modal" onchange="get_current_stock_modal()" required>
                    @foreach($sizes as $size)
                        <option value="{{ $size->id }}" {{ $manage_stock->size_id == $size->id ? 'selected' : '' }}>{{ $size->name }}</option>
                    @endforeach
                </select>
                <div class="mt-1"><small class="text-muted f-11">Current: <span id="modal_display_current_stock" class="fw-bold text-primary">0.000</span></small></div>
            </div>
            
            <div class="col-12">
                <label class="form-label fw-600 f-13">Quantity (Kgs)</label>
                <input type="number" step="0.001" name="quantity" id="modal_quantity" class="form-control" value="{{ $manage_stock->quantity }}" onkeyup="calculate_modal_new_stock()" required>
                <div class="mt-1"><small class="text-muted f-11">Result: <span id="modal_display_new_stock" class="fw-bold text-dark">0.000</span></small></div>
            </div>
            <div class="col-12">
                <label class="form-label fw-600 f-13">Remarks</label>
                <textarea name="remarks" class="form-control" rows="2">{{ $manage_stock->remarks }}</textarea>
            </div>
        </div>
    </div>
    <div class="modal-footer bg-light border-0">
        <button type="button" class="btn btn-secondary btn-sm px-4" data-bs-dismiss="modal">Close</button>
        <button type="submit" class="btn btn-warning btn-sm px-4 text-white">Update Record</button>
    </div>
</form>

<script>
    $(document).ready(function() {
        get_current_stock_modal();
    });

    function get_current_stock_modal() {
        var colorId = $('#modal_color_id').val();
        var sizeId = $('#modal_size_id').val();
        if(!colorId || !sizeId) return;

        $.get('{{ route("common_stock.get_current_stock") }}', { color_id: colorId, size_id: sizeId }, function(res) {
            $('#modal_display_current_stock').text(res.current_stock.toFixed(3));
            calculate_modal_new_stock();
        });
    }

    function calculate_modal_new_stock() {
        var current = parseFloat($('#modal_display_current_stock').text()) || 0;
        var input = parseFloat($('#modal_quantity').val()) || 0;
        var in_out = $('#modal_in_out').val();
        
        var result = (in_out == 'In') ? (current + input) : (current - input);
        $('#modal_display_new_stock').text(result.toFixed(3));
        
        if(in_out == 'Out' && result < 0) {
            $('#modal_display_new_stock').addClass('text-danger').removeClass('text-dark');
        } else {
            $('#modal_display_new_stock').addClass('text-dark').removeClass('text-danger');
        }
    }

    $('#edit_stock_form').on('submit', function(e) {
        e.preventDefault();
        var $btn = $(this).find('button[type="submit"]');
        $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Updating...');
        
        $.ajax({
            url: $(this).attr('action'),
            method: 'POST',
            data: $(this).serialize(),
            success: function(res) {
                if(res.result == 1) {
                    $.notify({ title: 'Updated', message: res.message }, { type: 'success' });
                    $('#edit_modal').modal('hide');
                    get_datatable(); // Refresh the list
                } else {
                    $.notify({ title: 'Error', message: res.message }, { type: 'danger' });
                }
                $btn.prop('disabled', false).html('Update Record');
            }
        });
    });
</script>
