@extends('layouts.admin.app')

@section('title', isset($packing_slip) ? 'Edit Common Packing Slip' : 'Create Common Packing Slip')

@section('css')
<style>
    .packing-card {
        border-radius: 15px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.08);
        border: none;
    }
    .packing-card .card-header {
        background: {{ isset($packing_slip) ? 'linear-gradient(135deg, #f59e0b, #d97706)' : 'linear-gradient(135deg, #1e293b, #334155)' }};
        color: white;
        border-radius: 15px 15px 0 0;
        padding: 20px;
    }
    .table-responsive {
        border-radius: 10px;
        overflow: hidden;
    }
    .packing-table thead th {
        background-color: #f8fafc;
        color: #475569;
        font-weight: 700;
        text-transform: uppercase;
        font-size: 0.75rem;
        border: none;
        padding: 15px;
    }
    .packing-table td {
        padding: 12px 15px;
        vertical-align: middle;
        border-bottom: 1px solid #f1f5f9;
        position: relative;
    }
    
    .remove-row-btn {
        width: 32px;
        height: 32px;
        border-radius: 8px;
        background: #fee2e2;
        color: #ef4444;
        border: none;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s;
    }
    .remove-row-btn:hover { background: #fecaca; transform: scale(1.1); }
    
    .add-row-btn {
        background: #3b82f6;
        color: white;
        border-radius: 10px;
        padding: 10px 20px;
        font-weight: 600;
        border: none;
        box-shadow: 0 4px 10px rgba(59, 130, 246, 0.3);
    }
    .add-row-btn:hover { background: #2563eb; transform: translateY(-1px); }
    
    .save-btn {
        background: linear-gradient(135deg, #10b981, #059669);
        color: white;
        border-radius: 12px;
        padding: 12px 40px;
        font-weight: 700;
        border: none;
        box-shadow: 0 6px 20px rgba(16, 185, 129, 0.3);
    }
    .save-btn:hover { transform: translateY(-2px); box-shadow: 0 8px 25px rgba(16, 185, 129, 0.4); }

    .select2-container--default .select2-selection--single {
        height: 38px !important;
        padding: 5px !important;
        border-color: #e2e8f0 !important;
    }

    .bag-badge {
        background: #eef2ff;
        color: #4f46e5;
        font-weight: 800;
        padding: 5px 12px;
        border-radius: 8px;
        font-size: 0.9rem;
    }
    
    .summary-badge {
        padding: 12px 25px;
        border-radius: 50px;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        display: flex;
        align-items: center;
        gap: 12px;
    }
    .summary-label { font-size: 0.75rem; color: #64748b; font-weight: 700; text-transform: uppercase; }
    .summary-val { font-size: 1.25rem; font-weight: 800; color: #1e293b; }

    .stock-badge {
        padding: 4px 10px;
        border-radius: 6px;
        font-weight: 700;
        font-size: 0.8rem;
    }
    .stock-available { background: #dcfce7; color: #166534; }
    .stock-low { background: #fee2e2; color: #991b1b; }

    /* New Real-time Validation Styles */
    .weight-input.is-invalid {
        border-color: #ef4444 !important;
        background-color: #fff1f2 !important;
        color: #b91c1c !important;
        box-shadow: 0 0 0 0.25rem rgba(239, 68, 68, 0.25) !important;
    }
    .stock-error-hint {
        color: #ef4444;
        font-size: 10px;
        font-weight: 700;
        margin-top: 4px;
        display: none;
        position: absolute;
        bottom: -2px;
        width: 100%;
    }
</style>
@endsection

@section('breadcrumb-items')
    <li class="breadcrumb-item">Operations</li>
    <li class="breadcrumb-item"><a href="{{ route('packing_slip_common.index') }}">Common Packing Slip</a></li>
    <li class="breadcrumb-item active">{{ isset($packing_slip) ? 'Edit' : 'Create' }}</li>
@endsection

@section('content')
<div class="container-fluid">
    <form action="{{ isset($packing_slip) ? route('packing_slip_common.update', $packing_slip->id) : route('packing_slip_common.store') }}" method="POST" id="common_packing_form">
        @csrf
        <div class="card packing-card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="fa {{ isset($packing_slip) ? 'fa-edit' : 'fa-shopping-basket' }} me-2"></i> 
                    {{ isset($packing_slip) ? 'Edit Common Packing Slip: ' . $packing_slip->packing_slip_no : 'Create Common Packing Slip' }}
                </h5>
                <span class="badge bg-white text-dark px-3 py-2" style="border-radius: 8px; font-weight: 700;">
                    {{ isset($packing_slip) ? 'DATE: ' . date('d M, Y', strtotime($packing_slip->packing_date)) : 'DATE: ' . date('d M, Y') }}
                </span>
            </div>
            <div class="card-body p-4">
                <!-- Master Info -->
                <div class="row g-4 mb-4">
                    <div class="col-md-5">
                        <label class="form-label fw-bold text-muted small text-uppercase mb-2">Select Customer / Agent</label>
                        <select name="customer_agent_id" class="form-select js-example-basic-single" required>
                            <option value="">Choose a customer...</option>
                            @foreach($customers as $customer)
                                <option value="{{ $customer->id }}" {{ (isset($selectedCustomerId) && $selectedCustomerId == $customer->id || isset($packing_slip) && $packing_slip->job_card && $packing_slip->job_card->customer_agent_id == $customer->id) ? 'selected' : '' }}>
                                    {{ $customer->name }} ({{ $customer->role }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold text-muted small text-uppercase mb-2">Packing Date</label>
                        <input type="date" name="date" class="form-control" value="{{ isset($packing_slip) ? $packing_slip->packing_date : date('Y-m-d') }}" required>
                    </div>
                </div>

                <!-- Summary Strip -->
                <div class="d-flex gap-4 justify-content-center mb-5 mt-2">
                    <div class="summary-badge">
                        <i class="fa fa-briefcase text-primary"></i>
                        <div>
                            <div class="summary-label">Total Bags</div>
                            <div class="summary-val" id="total_bags_display">{{ isset($packing_slip) ? $packing_slip->total_bags : '0' }}</div>
                        </div>
                    </div>
                    <div class="summary-badge">
                        <i class="fa fa-balance-scale text-success"></i>
                        <div>
                            <div class="summary-label">Total Net Weight</div>
                            <div class="summary-val"><span id="total_weight_display">{{ isset($packing_slip) ? number_format($packing_slip->total_weight, 3) : '0.000' }}</span> KG</div>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="fw-bold text-dark text-uppercase letter-spacing-1 mb-0">Bag Information</h6>
                    <button type="button" onclick="addRow()" class="add-row-btn btn-sm"><i class="fa fa-plus me-1"></i> Add Bag Row</button>
                </div>

                <div class="table-responsive bg-white border">
                    <table class="table packing-table">
                        <thead>
                            <tr>
                                <th style="width: 80px;" class="text-center">Bag #</th>
                                <th style="width: 25%;">Size Spec</th>
                                <th style="width: 25%;">Color Shade</th>
                                <th style="width: 15%;" class="text-center">Stock (KG)</th>
                                <th style="width: 20%;">Weight (KG)</th>
                                <th style="width: 60px;" class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody id="packing_items_body">
                            @if(isset($packing_slip))
                                @foreach($packing_slip->packing_details as $index => $detail)
                                <tr id="row_{{ $index }}" class="fade-in">
                                    <td class="text-center">
                                        <span class="bag-badge bag-number">{{ $index + 1 }}</span>
                                    </td>
                                    <td>
                                        <select name="items[{{ $index }}][size_id]" class="form-select select2-item size-select" onchange="updateRowStock({{ $index }})" required>
                                            <option value="">Select Size</option>
                                            @foreach($sizes as $size)
                                                <option value="{{ $size->id }}" {{ $detail->size_id == $size->id ? 'selected' : '' }}>{{ $size->name }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td>
                                        <select name="items[{{ $index }}][color_id]" class="form-select select2-item color-select" onchange="updateRowStock({{ $index }})" required>
                                            <option value="">Select Color</option>
                                            @foreach($colors as $color)
                                                <option value="{{ $color->id }}" {{ $detail->color_id == $color->id ? 'selected' : '' }}>{{ $color->name }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td class="text-center">
                                        <span class="stock-badge stock-available" id="stock_display_{{ $index }}" data-stock="0">Loading...</span>
                                    </td>
                                    <td>
                                        <div class="input-group">
                                            <input type="number" step="0.001" name="items[{{ $index }}][weight]" class="form-control weight-input" value="{{ $detail->weight }}" onkeyup="checkRowStock({{ $index }}); calculateTotal();" onchange="checkRowStock({{ $index }}); calculateTotal();" placeholder="0.000" min="0.001" required>
                                            <span class="input-group-text f-10 fw-bold">KG</span>
                                        </div>
                                        <div class="stock-error-hint" id="hint_{{ $index }}">EXCEEDS STOCK</div>
                                    </td>
                                    <td class="text-center">
                                        <div class="d-flex justify-content-center">
                                            <button type="button" onclick="removeRow({{ $index }})" class="remove-row-btn" title="Remove row">
                                                <i class="fa fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            @endif
                        </tbody>
                    </table>
                </div>

                <div class="mt-5 text-center">
                    @if(isset($packing_slip))
                        <button type="submit" class="save-btn" id="submit_button">
                            <i class="fa fa-save me-1"></i> UPDATE PACKING SLIP
                        </button>
                    @else
                        <button type="submit" class="save-btn" id="submit_button">
                            <i class="fa fa-check-circle me-1"></i> SAVE & GENERATE PDF
                        </button>
                    @endif
                    <p class="text-muted small mt-3"><i class="fa fa-info-circle me-1"></i> Stock is automatically deducted (in KG) from Common Product Stock upon saving.</p>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@section('script')
<script>
    let rowIndex = {{ isset($packing_slip) ? count($packing_slip->packing_details) : 0 }};
    let excludeSlipId = {{ isset($packing_slip) ? $packing_slip->id : 'null' }};

    $(document).ready(function() {
        $('.js-example-basic-single').select2();
        
        @if(!isset($packing_slip))
            addRow(); // Initial row for create
        @else
            // Initial stock load for existing rows in edit
            $('#packing_items_body tr').each(function() {
                let id = $(this).attr('id').split('_')[1];
                updateRowStock(id);
            });
        @endif
    });

    function addRow() {
        let row = `
            <tr id="row_${rowIndex}" class="fade-in">
                <td class="text-center">
                    <span class="bag-badge bag-number">${$('#packing_items_body tr').length + 1}</span>
                </td>
                <td>
                    <select name="items[${rowIndex}][size_id]" class="form-select select2-item size-select" onchange="updateRowStock(${rowIndex})" required>
                        <option value="">Select Size</option>
                        @foreach($sizes as $size)
                            <option value="{{ $size->id }}">{{ $size->name }}</option>
                        @endforeach
                    </select>
                </td>
                <td>
                    <select name="items[${rowIndex}][color_id]" class="form-select select2-item color-select" onchange="updateRowStock(${rowIndex})" required>
                        <option value="">Select Color</option>
                        @foreach($colors as $color)
                            <option value="{{ $color->id }}">{{ $color->name }}</option>
                        @endforeach
                    </select>
                </td>
                <td class="text-center">
                    <span class="stock-badge stock-available" id="stock_display_${rowIndex}" data-stock="0">0.000 KG</span>
                </td>
                <td>
                    <div class="input-group">
                        <input type="number" step="0.001" name="items[${rowIndex}][weight]" class="form-control weight-input" onkeyup="checkRowStock(${rowIndex}); calculateTotal();" onchange="checkRowStock(${rowIndex}); calculateTotal();" placeholder="0.000" min="0.001" required>
                        <span class="input-group-text f-10 fw-bold">KG</span>
                    </div>
                    <div class="stock-error-hint" id="hint_${rowIndex}">EXCEEDS STOCK</div>
                </td>
                <td class="text-center">
                    <div class="d-flex justify-content-center">
                        <button type="button" onclick="removeRow(${rowIndex})" class="remove-row-btn" title="Remove row">
                            <i class="fa fa-trash"></i>
                        </button>
                    </div>
                </td>
            </tr>
        `;
        
        $('#packing_items_body').append(row);
        $(`#row_${rowIndex} .select2-item`).select2();
        
        rowIndex++;
        calculateTotal();
    }

    function removeRow(idx) {
        if ($('#packing_items_body tr').length > 1) {
            $(`#row_${idx}`).remove();
            recalculateBagNumbers();
            calculateTotal();
        } else {
            $.notify({ message: 'At least one bag is required' }, { type: 'warning' });
        }
    }

    function recalculateBagNumbers() {
        $('.bag-number').each(function(index) {
            $(this).text(index + 1);
        });
    }

    function updateRowStock(idx) {
        let size_id = $(`#row_${idx} .size-select`).val();
        let color_id = $(`#row_${idx} .color-select`).val();
        let display = $(`#stock_display_${idx}`);

        if (size_id && color_id) {
            display.html('<i class="fa fa-spinner fa-spin"></i>');
            $.ajax({
                url: '{{ route("common_stock.get_current_stock") }}',
                data: { size_id: size_id, color_id: color_id, exclude_slip_id: excludeSlipId },
                success: function(data) {
                    let stock = parseFloat(data.current_stock) || 0;
                    display.text(stock.toFixed(3) + " KG").attr('data-stock', stock).removeClass('stock-available stock-low');
                    if (stock <= 0) {
                        display.addClass('stock-low');
                    } else {
                        display.addClass('stock-available');
                    }
                    // Trigger check again
                    checkRowStock(idx);
                }
            });
        }
    }

    function checkRowStock(idx) {
        let input = $(`#row_${idx} .weight-input`);
        let hint = $(`#hint_${idx}`);
        let size_id = $(`#row_${idx} .size-select`).val();
        let color_id = $(`#row_${idx} .color-select`).val();
        let currentWeight = parseFloat(input.val()) || 0;
        let availableStock = parseFloat($(`#stock_display_${idx}`).attr('data-stock')) || 0;

        // Grouping weights by combo to catch cases where multiple rows use the same stock
        let totalComboWeight = 0;
        if (size_id && color_id) {
            $('#packing_items_body tr').each(function() {
                let rIdx = $(this).attr('id').split('_')[1];
                if ($(`#row_${rIdx} .size-select`).val() == size_id && $(`#row_${rIdx} .color-select`).val() == color_id) {
                    totalComboWeight += parseFloat($(`#row_${rIdx} .weight-input`).val()) || 0;
                }
            });

            if (totalComboWeight > availableStock) {
                input.addClass('is-invalid');
                hint.show().text('STOCK LIMIT REACHED (' + availableStock.toFixed(3) + ')');
            } else {
                input.removeClass('is-invalid');
                hint.hide();
            }
        }
    }

    function calculateTotal() {
        let totalWeight = 0;
        $('.weight-input').each(function() {
            let val = parseFloat($(this).val()) || 0;
            totalWeight += val;
        });
        
        let totalBags = $('#packing_items_body tr').length;
        
        $('#total_weight_display').text(totalWeight.toFixed(3));
        $('#total_bags_display').text(totalBags);
    }

    $(document).on('keydown', '.weight-input', function(e) {
        if (e.which == 13 || e.which == 9) { // Enter or Tab
            let isLast = $(this).closest('tr').is(':last-child');
            if (isLast && $(this).val() > 0 && !$(this).hasClass('is-invalid')) {
                e.preventDefault();
                addRow();
            }
        }
    });

    $('#common_packing_form').on('submit', function(e) {
        e.preventDefault();
        
        if ($('.is-invalid').length > 0) {
            $.notify({ title: 'Alert', message: 'Some items exceed available stock.' }, { type: 'danger' });
            return false;
        }

        let btn = $('#submit_button');
        let originalHtml = btn.html();
        btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin me-2"></i> PROCESSING...');

        $.ajax({
            url: $(this).attr('action'),
            type: 'POST',
            data: $(this).serialize(),
            success: function(data) {
                if (data.result == 1) {
                    $.notify({ title: 'Success', message: data.message }, { type: 'success' });
                    setTimeout(() => {
                        window.location.href = "{{ route('packing_slip_common.index') }}";
                    }, 1500);
                } else {
                    $.notify({ title: 'Failed', message: data.message }, { type: 'danger' });
                    btn.prop('disabled', false).html(originalHtml);
                }
            },
            error: function(xhr) {
                let msg = 'An unexpected error occurred.';
                if (xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message;
                $.notify({ title: 'Error', message: msg }, { type: 'danger' });
                btn.prop('disabled', false).html(originalHtml);
            }
        });
    });
</script>
@endsection
