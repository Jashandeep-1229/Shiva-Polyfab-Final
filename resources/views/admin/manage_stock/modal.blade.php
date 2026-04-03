<form action="{{ route('manage_stock.store') }}" method="post" id="updateForm" class="modal-content" enctype="multipart/form-data">
    @csrf
    <input type="hidden" name="manage_stock_id" id="m_manage_stock_id" value="{{$manage_stock->id ?? 0}}">
    <input type="hidden" name="from" value="Manually">
    <input type="hidden" name="stock_name" value="{{$stock_name}}">
    <input type="hidden" name="unit_name" value="{{$unit_name}}">
    <input type="hidden" name="in_out" value="{{$in_out}}">
    <input type="hidden" name="old_quantity" id="m_old_quantity" value="{{$manage_stock->quantity ?? 0}}">

    <div class="modal-header">
        <h4 class="modal-title" id="mySmallModalLabel">{{($manage_stock->id ?? 0) ? 'Edit' : 'Add'}} Manage Stock - {{ucfirst($in_out)}}</h4>
        <button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Close" data-bs-original-title="" title=""></button>
    </div>
    <div class="modal-body dark-modal">
        <div class="row">
            <div class="col-md-12 form-group mb-3">
                <h6>Date</h6>
                <input type="date" name="date" value="{{$manage_stock->date ?? date('Y-m-d')}}" placeholder="Date" class="form-control form-control-sm" required>
            </div>
            <div class="col-md-12 form-group mb-3">
                <h6>Stock Name</h6>
                <select name="stock_id" id="m_stock_id" class="form-control form-control-sm js-example-basic-single" onchange="get_current_stock_modal(this.value)" required>
                    <option value="">Select {{$stock_name_capital}}</option>
                    @foreach($stock_list as $stock)
                        <option value="{{$stock->id}}" {{($manage_stock->stock_id ?? 0) == $stock->id ? 'selected' : ''}}>{{$stock->name}}</option>
                    @endforeach
                </select>
                <span class="f-12 text-dark">Total Current Average: <span id="m_current_stock">0</span></span>
            </div>
            <div class="col-md-12 form-group mb-3">
                <h6>Quantity ({{$unit_name}})</h6>
                <input type="number" step="any" name="quantity" id="m_quantity" onkeyup="get_average_modal(this.value)" placeholder="Enter {{$unit_name}}" value="{{$manage_stock->quantity ?? ''}}" class="form-control form-control-sm">
                <span class="f-12 text-dark">Projected New Average: <span id="m_news_stock">0</span></span>
            </div>
            <div class="col-md-12 form-group mb-3">
                <h6>Total Average Value</h6>
                <input type="number" step="any" name="average" id="m_average" value="{{$manage_stock->average ?? ''}}" placeholder="Average" readonly class="form-control form-control-sm">
            </div>
            <div class="col-md-12 form-group mb-3">
                <h6>Remarks</h6>
                <textarea name="remarks" class="form-control form-control-sm" placeholder="Remarks">{{$manage_stock->remarks ?? ''}}</textarea>
            </div>
        </div>
    </div>
    <div class="modal-footer text-end">
        <button type="submit" id="update" class="btn btn-primary">{{($manage_stock->id ?? 0) ? 'Update' : 'Add'}}</button>
    </div>
</form>

<script>
    $(document).ready(function() {
        var stock_id = $('#m_stock_id').val();
        if(stock_id) {
            get_current_stock_modal(stock_id);
        }
    });

    function get_current_stock_modal(id) {
        var url = "{{route('manage_stock.get_current_stock', ':id')}}";
        url = url.replace(':id', id);
        $.get(url, {stock_name: "{{$stock_name}}"}, function(data) {
            if(data.result == 1) {
                // The server returns the balance accounting for all records, including this one if it exists in DB
                $('#m_current_stock').text(data.current_average);
                get_average_modal($('#m_quantity').val()); 
            }
        });
    }

    function get_average_modal(value) {
        var avg_factor = "{{$average}}"; // Default average weight per unit
        var total_avg = (parseFloat(value) || 0) * parseFloat(avg_factor);
        $('#m_average').val(total_avg.toFixed(2));

        var server_current = parseFloat($('#m_current_stock').text()) || 0;
        var in_out = "{{$in_out}}";
        var manage_stock_id = parseInt($('#m_manage_stock_id').val()) || 0;
        
        var base_stock = server_current;
        
        // If we are editing, we need to treat the current balance as if this entry didn't exist yet
        if(manage_stock_id > 0) {
            var old_qty = parseFloat($('#m_old_quantity').val()) || 0;
            var old_avg_val = old_qty * parseFloat(avg_factor);
            
            if(in_out == 'in') {
                base_stock = server_current - old_avg_val;
            } else {
                base_stock = server_current + old_avg_val;
            }
        }

        var new_avg_input = (parseFloat(value) || 0) * parseFloat(avg_factor);
        var final_stock = 0;
        
        if(in_out == 'in') {
            final_stock = base_stock + new_avg_input;
        } else {
            final_stock = base_stock - new_avg_input;
        }
        
        $('#m_news_stock').text(final_stock.toFixed(2));

        // Validation for negative stock
        if(in_out != 'in' && final_stock < 0) {
            $('#update').prop('disabled', true).addClass('disabled');
            $('#m_news_stock').addClass('text-danger').removeClass('text-dark');
        } else {
            $('#update').prop('disabled', false).removeClass('disabled');
            $('#m_news_stock').addClass('text-dark').removeClass('text-danger');
        }
    }
</script>
