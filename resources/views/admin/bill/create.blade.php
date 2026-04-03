@extends('layouts.admin.app')
@section('title', 'Add Manual Bill')
@section('breadcrumb-items')
    <li class="breadcrumb-item"><a href="{{ route('bill.index') }}">Bill Management</a></li>
    <li class="breadcrumb-item">Add Manual Bill</li>
@endsection

@section('content')
<style>
    .billing-table thead th {
        background: #f1f3f5;
        font-size: 0.8rem;
        padding: 10px;
    }
    .billing-input {
        border: 1px solid #ced4da;
        border-radius: 4px;
        padding: 5px 8px;
        width: 100%;
        font-size: 0.85rem;
    .billing-input:focus {
        border: 1px solid #86b7fe !important;
        outline: none !important;
        box-shadow: 0 0 0 0.1rem rgba(13, 110, 253, 0.25) !important;
    }
</style>
<div class="container-fluid">
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header pb-0 d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">Add Manual Bill</h4>
                        <a href="{{ route('bill.index') }}" class="btn btn-dark btn-sm"><i class="fa fa-arrow-left"></i> Back to List</a>
                    </div>
                    <form action="{{ route('bill.store') }}" method="POST" id="create_bill_form">
                        @csrf
                        <div class="card-body">
                            <div class="row mb-4">
                                <div class="col-md-3">
                                    <label>Bill No <span class="text-danger">*</span></label>
                                    <input type="text" name="bill_no" class="form-control" required>
                                </div>
                                <div class="col-md-2">
                                    <label>Bill Date <span class="text-danger">*</span></label>
                                    <input type="date" name="bill_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                                </div>
                                <div class="col-md-2">
                                    <label>Due (Days)</label>
                                    <input type="number" name="due_days" class="form-control" placeholder="0">
                                </div>
                                <div class="col-md-4">
                                    <label>Customer <span class="text-danger">*</span></label>
                                    <select name="customer_id" class="form-control select2" required>
                                        <option value="">Select Customer</option>
                                        @foreach($customers as $customer)
                                            <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            
                            <h5 class="mb-3">Items Details</h5>
                            <div class="table-responsive">
                                <table class="table billing-table table-bordered">
                                    <thead>
                                        <tr>
                                            <th style="width: 40px;"></th>
                                            <th style="width: 30%;">Description of Goods</th>
                                            <th style="width: 100px;">Weight/Qty</th>
                                            <th style="width: 100px;">Unit</th>
                                            <th style="width: 100px;">Rate</th>
                                            <th style="width: 120px;">Amount</th>
                                            <th style="width: 90px;">GST %</th>
                                            <th style="width: 150px;">Total Amount</th>
                                        </tr>
                                    </thead>
                                    <tbody id="billing_tbody">
                                        <tr class="bill-row">
                                            <td class="text-center">
                                                <button type="button" class="btn btn-sm btn-danger remove-row"><i class="fa fa-times"></i></button>
                                            </td>
                                            <td><input type="text" name="items[0][description]" class="billing-input" placeholder="Description of Goods" required></td>
                                            <td><input type="number" step="0.001" name="items[0][qty]" class="billing-input amount-calc row-qty" placeholder="0.000" required></td>
                                            <td>
                                                <select name="items[0][unit]" class="billing-input">
                                                    <option value="Kgs">Kgs</option>
                                                    <option value="Pcs">Pcs</option>
                                                    <option value="Bags">Bags</option>
                                                    <option value="Mtrs">Mtrs</option>
                                                </select>
                                            </td>
                                            <td><input type="number" step="0.01" name="items[0][rate]" class="billing-input amount-calc row-rate" placeholder="0.00" required></td>
                                            <td><input type="number" step="0.01" name="items[0][amount]" class="billing-input row-amount" readonly placeholder="0.00"></td>
                                            <td>
                                                <select name="items[0][gst_percent]" class="billing-input amount-calc row-gst">
                                                    <option value="0">0%</option>
                                                    <option value="5">5%</option>
                                                    <option value="18">18%</option>
                                                </select>
                                            </td>
                                            <td><input type="number" step="0.01" name="items[0][total_amount]" class="billing-input fw-bold row-total" readonly placeholder="0.00"></td>
                                        </tr>
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <td colspan="8" class="border-0 pt-3">
                                                <button type="button" class="btn btn-outline-primary btn-sm" id="add_bill_row"><i class="fa fa-plus"></i> Add Another Item</button>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th colspan="7" class="text-end bg-dark text-white">Grand Total Payable</th>
                                            <th id="grand_total_display" class="bg-dark text-white f-20">₹ 0.00</th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                            
                            <div class="row mt-4">
                                <div class="col-md-12">
                                    <label>Remarks / Notes</label>
                                    <textarea name="remarks" class="form-control" rows="3"></textarea>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer text-end">
                            <button type="submit" class="btn btn-success" id="btn_save">Save Bill</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
<script>
    $(document).ready(function() {
        $('.select2').select2();
    });
    let rowCount = 1;
    $('#add_bill_row').on('click', function() {
        let newRow = `
        <tr class="bill-row">
            <td class="text-center">
                <button type="button" class="btn btn-sm btn-danger remove-row"><i class="fa fa-times"></i></button>
            </td>
            <td><input type="text" name="items[${rowCount}][description]" class="billing-input" placeholder="Description of Goods" required></td>
            <td><input type="number" step="0.001" name="items[${rowCount}][qty]" class="billing-input amount-calc row-qty" placeholder="0.000" required></td>
            <td>
                <select name="items[${rowCount}][unit]" class="billing-input">
                    <option value="Kgs">Kgs</option>
                    <option value="Pcs">Pcs</option>
                    <option value="Bags">Bags</option>
                    <option value="Mtrs">Mtrs</option>
                </select>
            </td>
            <td><input type="number" step="0.01" name="items[${rowCount}][rate]" class="billing-input amount-calc row-rate" placeholder="0.00" required></td>
            <td><input type="number" step="0.01" name="items[${rowCount}][amount]" class="billing-input row-amount" readonly placeholder="0.00"></td>
            <td>
                <select name="items[${rowCount}][gst_percent]" class="billing-input amount-calc row-gst">
                    <option value="0">0%</option>
                    <option value="5">5%</option>
                    <option value="18">18%</option>
                </select>
            </td>
            <td><input type="number" step="0.01" name="items[${rowCount}][total_amount]" class="billing-input fw-bold row-total" readonly placeholder="0.00"></td>
        </tr>`;
        $('#billing_tbody').append(newRow);
        $('#billing_tbody tr:last-child').find('input[placeholder="Description of Goods"]').focus();
        rowCount++;
    });

    $(document).on('click', '.remove-row', function() {
        if ($('#billing_tbody .bill-row').length > 1) {
            $(this).closest('tr').remove();
            calculateGrandTotal();
        }
    });

    $(document).on('input change', '.amount-calc', function() {
        let $row = $(this).closest('tr');
        let weight = parseFloat($row.find('.row-qty').val()) || 0;
        let rate = parseFloat($row.find('.row-rate').val()) || 0;
        let gstPerc = parseFloat($row.find('.row-gst').val()) || 0;

        let amount = weight * rate;
        let total = amount + (amount * (gstPerc / 100));

        $row.find('.row-amount').val(amount.toFixed(2));
        $row.find('.row-total').val(total.toFixed(2));

        calculateGrandTotal();
    });

    function calculateGrandTotal() {
        let grandTotal = 0;
        $('.row-total').each(function() {
            let val = parseFloat($(this).val()) || 0;
            grandTotal += val;
        });
        $('#grand_total_display').text('₹ ' + grandTotal.toLocaleString('en-IN', {minimumFractionDigits: 2}));
    }

    $("#create_bill_form").submit(function(e) {
        e.preventDefault();
        var formData = new FormData(this);
        $.ajax({
            url: "{{ route('bill.store') }}",
            type: "POST",
            data: formData,
            cache: false,
            contentType: false,
            processData: false,
            dataType: 'JSON',
            beforeSend: function() {
                $('#btn_save').attr('disabled', true);
            },
            success: function(response) {
                if(response.result == 1) {
                    $.notify({ title:'Success', message:response.message }, { type:'success', });
                    window.location.href = response.url;
                } else {
                    $.notify({ title:'Error', message:response.message }, { type:'danger', });
                    $('#btn_save').attr('disabled', false);
                }
            },
            error: function(xhr) {
                var errorMessage = 'Something went wrong. Please try again.';
                if(xhr.status == 422) {
                    var errors = xhr.responseJSON.errors;
                    var firstError = Object.values(errors)[0][0];
                    errorMessage = firstError;
                } else if(xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }
                $.notify({ title:'Error', message: errorMessage }, { type:'danger', });
                $('#btn_save').attr('disabled', false);
            }
        });
    });
</script>
@endsection
