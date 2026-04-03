<form action="{{ route('job_card.packing_store',$job_card->id) }}" method="post" id="updateForm" class="modal-content" enctype="multipart/form-data">
    @csrf
    <input type="hidden" name="job_card_id"  value="{{$job_card->id ?? 0}}">
    <input type="hidden" name="next_process"  value="{{$next_process ?? ''}}">
    <input type="hidden" name="job_card_process"  value="{{$job_card->job_card_process ?? ''}}">
    
    <style>
        .packing-modal .modal-header {
            background: linear-gradient(135deg, #6e8efb, #a777e3);
            color: white;
            border-bottom: none;
        }
        .packing-modal .btn-close { filter: brightness(0) invert(1); }
        .info-card {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 15px;
            border-left: 5px solid #6e8efb;
            height: 100%;
            transition: all 0.3s ease;
        }
        .info-card:hover {
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            transform: translateY(-2px);
        }
        .info-card h6 {
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #777;
            margin-bottom: 5px;
        }
        .info-card .val {
            font-weight: 700;
            color: #333;
            font-size: 1rem;
        }
        .stats-badge {
            padding: 10px 20px;
            border-radius: 50px;
            background: white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .stats-badge .icon {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
        }
        .stats-label { font-size: 0.75rem; color: #777; font-weight: 600; }
        .stats-val { font-size: 1.1rem; font-weight: 800; color: #444; }
        
        .packing-table-container {
            border: 1px solid #eef0f2;
            border-radius: 15px;
            overflow: hidden;
            background: white;
        }
        .packing-table thead th {
            background: #f8f9fa;
            color: #495057;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 1px;
            padding: 15px;
            border: none;
        }
        .packing-table td { padding: 12px 15px; vertical-align: middle; border-color: #f1f3f5; }
        .bag-badge {
            background: #eef2ff;
            color: #4f46e5;
            font-weight: 800;
            padding: 5px 12px;
            border-radius: 8px;
            font-size: 0.9rem;
        }
        .weight-input-wrapper { position: relative; }
        .weight-input-wrapper::after {
            content: 'KG';
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 0.7rem;
            font-weight: 800;
            color: #adb5bd;
        }
        .weight-input-field {
            border: 2px solid #e9ecef;
            border-radius: 8px;
            padding-right: 35px;
            font-weight: 600;
            transition: border-color 0.2s;
        }
        .weight-input-field:focus {
            border-color: #6e8efb;
            box-shadow: none;
        }
        .btn-add-row {
            background: #10b981;
            color: white;
            border: none;
            width: 32px;
            height: 32px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s;
        }
        .btn-add-row:hover { background: #059669; transform: scale(1.1); }
        .btn-remove-row {
            background: #fee2e2;
            color: #ef4444;
            border: none;
            width: 32px;
            height: 32px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s;
        }
        .btn-remove-row:hover { background: #fecaca; transform: scale(1.1); }
    </style>

    <div class="packing-modal">
        <div class="modal-header">
            <h5 class="modal-title d-flex align-items-center gap-2">
                <i class="fa fa-shopping-basket"></i>
                <span>{{$next_process ?? 'Packing Material'}} - {{ $job_card->name_of_job ?? '' }} ({{ $job_card->job_card_no ?? '' }})</span>
            </h5>
            <button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        
        <div class="modal-body bg-white">
            <!-- Header Information Section -->
            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <div class="info-card">
                        <h6>Name Of Job</h6>
                        <div class="val text-truncate">{{$job_card->name_of_job ?? 'N/A'}}</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="info-card" style="border-left-color: #a777e3;">
                        <h6>Agent/Customer</h6>
                        <div class="val text-truncate">{{$job_card->customer_agent->name ?? 'N/A'}}</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="info-card" style="border-left-color: #10b981;">
                        <h6>Packing Date</h6>
                        <input type="date" value="{{date('Y-m-d')}}" name="date" class="form-control border-0 bg-transparent p-0 font-weight-bold" style="font-size: 1rem; box-shadow: none;">
                    </div>
                </div>
            </div>

            <!-- Stats Summary Strip -->
            <div class="row mb-4">
                <div class="col-12 d-flex gap-3 justify-content-center">
                    <div class="stats-badge">
                        <div class="icon bg-primary-light text-primary" style="background: #eef2ff;">
                            <i class="fa fa-briefcase"></i>
                        </div>
                        <div>
                            <div class="stats-label">Total Bags</div>
                            <div class="stats-val" id="total_bags_display">1</div>
                        </div>
                    </div>
                    <div class="stats-badge">
                        <div class="icon text-success" style="background: #ecfdf5;">
                            <i class="fa fa-balance-scale"></i>
                        </div>
                        <div>
                            <div class="stats-label">Total Net Weight</div>
                            <div class="stats-val"><span id="total_weight_display">0.000</span> KG</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Packing Table Section -->
            <div class="packing-table-container">
                <table class="table packing-table mb-0">
                    <thead>
                        <tr>
                            <th class="text-center" style="width: 100px;">Bag #</th>
                            <th>Individual Weight</th>
                            <th class="text-center" style="width: 120px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="packing_slip_body">
                        <tr>
                            <td class="text-center">
                                <span class="bag-badge bag-number">1</span>
                            </td>
                            <td>
                                <div class="weight-input-wrapper">
                                    <input type="hidden" name="packing_slip[0][id]" value="0">
                                    <input type="number" step="0.001" name="packing_slip[0][weight]" required class="form-control weight-input-field weight-input" placeholder="0.000" autocomplete="off">
                                </div>
                            </td>
                            <td class="text-center">
                                <div class="d-flex justify-content-center">
                                    <button type="button" onclick="addRow()" class="btn-add-row" title="Add New Bag">
                                        <i class="fa fa-plus"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Hidden Inputs for Submission -->
            <input type="hidden" name="total_weight" id="total_weight_input" value="0">
            <input type="hidden" name="total_bags" id="total_bags_input" value="1">
        </div>

        <div class="modal-footer border-top-0 d-flex justify-content-between align-items-center px-4 pb-4">
            <div class="text-muted small">
                <i class="fa fa-info-circle me-1"></i> Press <b>Enter</b> or <b>Tab</b> in weight field to add next bag.
            </div>
            <button type="submit" id="update" class="btn btn-lg px-5 text-white" style="background: linear-gradient(135deg, #6e8efb, #a777e3); border-radius: 12px; font-weight: 600; box-shadow: 0 4px 15px rgba(110, 142, 251, 0.3);">
                Save Packing Slip
            </button>
        </div>
    </div>
</form>

<script>
    function addRow() {
        var tbody = $('#packing_slip_body');
        var rowCount = tbody.find('tr').length;
        var newIndex = rowCount;
        
        var newRow = `
            <tr class="fade-in">
                <td class="text-center">
                    <span class="bag-badge bag-number">${newIndex + 1}</span>
                </td>
                <td>
                    <div class="weight-input-wrapper">
                        <input type="hidden" name="packing_slip[${newIndex}][id]" value="0">
                        <input type="number" step="0.001" name="packing_slip[${newIndex}][weight]" required class="form-control weight-input-field weight-input" placeholder="0.000" autocomplete="off">
                    </div>
                </td>
                <td class="text-center">
                    <div class="d-flex justify-content-center">
                        <button type="button" onclick="removeRow(this)" class="btn-remove-row" title="Remove Bag">
                            <i class="fa fa-trash"></i>
                        </button>
                    </div>
                </td>
            </tr>
        `;
        
        tbody.append(newRow);
        tbody.find('tr').last().find('.weight-input').focus();
        calculateTotal();
    }

    function removeRow(btn) {
        $(btn).closest('tr').fadeOut(200, function() {
            $(this).remove();
            recalculateIndices();
            calculateTotal();
        });
    }

    function recalculateIndices() {
        $('#packing_slip_body tr').each(function(index) {
            $(this).find('.bag-number').text(index + 1);
            $(this).find('.weight-input').attr('name', `packing_slip[${index}][weight]`);
        });
    }

    function calculateTotal() {
        var total = 0;
        $('.weight-input').each(function() {
            var val = $(this).val();
            if (val && !isNaN(val)) {
                total += parseFloat(val);
            }
        });
        var totalBags = $('#packing_slip_body tr').length;
        
        // Update display elements
        $('#total_weight_display').text(total.toFixed(3));
        $('#total_bags_display').text(totalBags);
        
        // Update hidden inputs
        $('#total_weight_input').val(total.toFixed(3));
        $('#total_bags_input').val(totalBags);
    }

    $(document).off('keyup', '.weight-input').on('keyup', '.weight-input', function() {
        calculateTotal();
    });

    $(document).off('input', '.weight-input').on('input', '.weight-input', function() {
        var value = $(this).val();
        if (value.indexOf('.') !== -1) {
            var parts = value.split('.');
            if (parts[1].length > 3) {
                $(this).val(parts[0] + '.' + parts[1].substring(0, 3));
                calculateTotal();
            }
        }
    });

    $(document).off('keydown', '.weight-input').on('keydown', '.weight-input', function(e) {
        if (e.which == 13 || e.which == 9) { // Enter or Tab key
            var isLast = $(this).closest('tr').is(':last-child');
            if (isLast) {
                e.preventDefault();
                var currentVal = $(this).val();
                if(currentVal && currentVal > 0) {
                    addRow();
                }
            }
        }
    });

    $('#updateForm').on('submit', function() {
        var total = parseFloat($('#total_weight_input').val());
        if(total <= 0) {
            $.notify({ title: 'Error', message: 'Total weight must be greater than 0' }, { type: 'danger' });
            return false;
        }
    });
</script>
