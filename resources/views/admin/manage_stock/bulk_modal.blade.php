<form action="{{ route('manage_stock.bulk_store') }}" method="post" id="bulkStoreForm" class="modal-content">
    @csrf
    <input type="hidden" name="stock_name" value="{{$stock_name}}">
    <input type="hidden" name="unit_name" value="{{$unit_name}}">
    <input type="hidden" name="in_out" value="{{$in_out}}">
    <input type="hidden" name="average_factor" value="{{$average}}">

    <div class="modal-header">
        <h4 class="modal-title">Bulk {{ucfirst($in_out)}} - {{ucfirst($stock_name)}}</h4>
        <button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Close"></button>
    </div>
    <div class="modal-body">
        <div class="row mb-3">
            <div class="col-md-4">
                <label class="form-label">Date</label>
                <input type="date" name="date" value="{{ date('Y-m-d') }}" class="form-control form-control-sm" required>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-bordered table-sm" id="bulk_table">
                <thead>
                    <tr class="bg-light">
                        <th width="40%">{{ucfirst($stock_name)}}</th>
                        <th width="20%">{{$unit_name}}</th>
                        <th width="30%">Remarks</th>
                        <th width="10%">Action</th>
                    </tr>
                </thead>
                <tbody id="bulk_tbody">
                    @for($i=0; $i<5; $i++)
                    <tr>
                        <td>
                            <select name="items[{{$i}}][stock_id]" class="form-select form-select-sm js-example-basic-single-modal" required>
                                <option value="">Select</option>
                                @foreach($stock_list as $stock)
                                    <option value="{{$stock->id}}">{{$stock->name}}</option>
                                @endforeach
                            </select>
                        </td>
                        <td>
                            <input type="number" step="any" name="items[{{$i}}][quantity]" class="form-control form-control-sm qty-input" placeholder="{{$unit_name}}">
                        </td>
                        <td>
                            <input type="text" name="items[{{$i}}][remarks]" class="form-control form-control-sm remarks-input" placeholder="Remarks">
                        </td>
                        <td class="text-center">
                            <button type="button" class="btn btn-danger btn-xs remove-row"><i class="fa fa-trash"></i></button>
                        </td>
                    </tr>
                    @endfor
                </tbody>
            </table>
        </div>
        <div class="mt-2">
            <button type="button" class="btn btn-outline-info btn-xs" id="add_row_btn"><i class="fa fa-plus"></i> Add Row</button>
        </div>
    </div>
    <div class="modal-footer">
        <button type="submit" class="btn btn-primary" id="saveBulkBtn">Save All</button>
    </div>
</form>

<script>
$(document).ready(function() {
    $('.js-example-basic-single-modal').select2({
        dropdownParent: $('#edit_modal')
    });

    var rowCount = 5;

    $('#add_row_btn').click(function() {
        addRow();
    });

    $(document).on('click', '.remove-row', function() {
        if($('#bulk_tbody tr').length > 1) {
            $(this).closest('tr').remove();
        }
    });

    function addRow() {
        var options = $('#bulk_tbody tr:first select').html();
        var html = `<tr>
            <td>
                <select name="items[${rowCount}][stock_id]" class="form-select form-select-sm js-example-basic-single-modal" required>
                    ${options}
                </select>
            </td>
            <td>
                <input type="number" step="any" name="items[${rowCount}][quantity]" class="form-control form-control-sm qty-input" placeholder="{{$unit_name}}">
            </td>
            <td>
                <input type="text" name="items[${rowCount}][remarks]" class="form-control form-control-sm remarks-input" placeholder="Remarks">
            </td>
            <td class="text-center">
                <button type="button" class="btn btn-danger btn-xs remove-row"><i class="fa fa-trash"></i></button>
            </td>
        </tr>`;
        $('#bulk_tbody').append(html);
        $('#bulk_tbody tr:last select').select2({
            dropdownParent: $('#edit_modal')
        });
        rowCount++;
    }

    // Enter key behavior
    $(document).on('keypress', '.remarks-input', function(e) {
        if(e.which == 13) {
            e.preventDefault();
            var row = $(this).closest('tr');
            if(row.is(':last-child')) {
                addRow();
                $('#bulk_tbody tr:last select').select2('open');
            } else {
                row.next('tr').find('select').select2('open');
            }
        }
    });

    $(document).on('select2:select', '.js-example-basic-single-modal', function(e) {
        $(this).closest('tr').find('.qty-input').focus();
    });
    
    $(document).on('keypress', '.qty-input', function(e) {
        if(e.which == 13) {
             e.preventDefault();
             $(this).closest('tr').find('.remarks-input').focus();
        }
    });

});
</script>
