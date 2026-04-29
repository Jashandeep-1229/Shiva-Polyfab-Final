<form action="{{ route('job_card.update_process', $job_card->id) }}" method="post" id="billingForm" class="modal-content border-0 shadow-lg">
    @csrf
    <input type="hidden" name="job_card_id" value="{{$job_card->id}}">
    <input type="hidden" name="job_card_process" value="Account Pending">
    <input type="hidden" name="next_process" value="Completed">

    <style>
        .billing-modal .modal-header {
            background: #343a40;
            color: white;
            border-bottom: none;
        }
        .billing-modal .btn-close { filter: brightness(0) invert(1); }
        .summary-box {
            background: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 25px;
        }
        .summary-label {
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #6c757d;
            font-weight: 700;
            margin-bottom: 3px;
        }
        .summary-value {
            font-size: 0.95rem;
            color: #212529;
            font-weight: 600;
        }
        .ps-pill {
            background: white;
            border: 1px solid #dee2e6;
            border-radius: 4px;
            padding: 3px 10px;
            font-size: 0.8rem;
            color: #495057;
            font-weight: 600;
            display: inline-block;
            margin: 2px;
        }
        .billing-table { border-collapse: separate; border-spacing: 0; width: 100%; }
        .billing-table thead th {
            background: #f1f3f5;
            color: #495057;
            font-weight: 700;
            text-transform: uppercase;
            font-size: 0.72rem;
            padding: 12px 10px;
            border-top: 1px solid #dee2e6;
            border-bottom: 2px solid #dee2e6;
        }
        .billing-table tbody td {
            vertical-align: middle;
            padding: 8px 10px;
            border-bottom: 1px solid #f1f3f5;
        }
        .billing-input {
            border: 1px solid #ced4da;
            border-radius: 4px;
            padding: 6px 10px;
            font-size: 0.85rem;
            width: 100%;
            transition: all 0.2s;
        }
        .billing-input:focus {
            border: 1px solid #80bdff !important;
            box-shadow: 0 0 0 0.1rem rgba(0,123,255,0.25) !important;
            outline: none !important;
        }
        .billing-input[readonly] { background-color: #fcfcfc; color: #6c757d; border-color: #e9ecef; }
        .grand-total-row th { background: #343a40; color: white; padding: 15px; }
    </style>

    <div class="billing-modal">
        <div class="modal-header">
            <h5 class="modal-title d-flex align-items-center gap-2">
                <i class="fa fa-calculator"></i>
                <span>Account Billing & Closure - {{ $job_card->name_of_job ?? '' }} ({{ $job_card->job_card_no ?? '' }})</span>
            </h5>
            <button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <div class="modal-body bg-white px-4">
            <!-- Job Summary Data -->
            <div class="summary-box">
                <div class="row g-4">
                    <div class="col-md-3">
                        <div class="summary-label">Date of Order</div>
                        <div class="summary-value">{{ $job_card->job_card_date ? $job_card->job_card_date->format('d M, Y') : 'N/A' }}</div>
                    </div>
                    <div class="col-md-3">
                        <div class="summary-label">Dispatch Date</div>
                        <div class="summary-value">{{ $job_card->dispatch_date ? date('d M, Y', strtotime($job_card->dispatch_date)) : 'N/A' }}</div>
                    </div>
                    <div class="col-md-3">
                        <div class="summary-label">Agent / Customer</div>
                        <div class="summary-value">{{ $job_card->customer_agent->name ?? 'N/A' }}</div>
                    </div>
                    <div class="col-md-3">
                        <div class="summary-label">Sale Executive</div>
                        <div class="summary-value">{{ $job_card->sale_executive->name ?? 'N/A' }}</div>
                    </div>
                    <div class="col-md-3">
                        <div class="summary-label text-danger">Bill NO (Required)</div>
                        <input type="text" name="billing_invoice_no" class="billing-input border-danger" placeholder="Enter Bill #" required>
                    </div>
                    <div class="col-md-3">
                        <div class="summary-label">Billing Date</div>
                        <input type="date" name="billing_date" value="{{ date('Y-m-d') }}" class="billing-input" required>
                    </div>
                    <div class="col-md-3">
                        <div class="summary-label">Due (Days)</div>
                        <input type="number" name="due_days" class="billing-input" placeholder="0">
                    </div>
                    <div class="col-md-3">
                        <div class="summary-label">
                            {{ $job_card->job_type == 'Common' ? 'Dispatch Quantity' : 'Packing Slips weights' }}
                        </div>
                        <div class="mt-1">
                            @forelse($job_card->packing_slips as $ps)
                                <div class="ps-pill">
                                    {{ $ps->packing_slip_no }}: <strong>{{ number_format($ps->total_weight, 3) }} KG</strong>
                                </div>
                            @empty
                                @if($job_card->job_type == 'Common')
                                    <div class="ps-pill">
                                        {{ str_replace('Common Packing - ', '', $job_card->name_of_job) }}: <strong>{{ number_format($job_card->actual_pieces, 0) }} PCS</strong>
                                    </div>
                                @else
                                    <span class="text-muted small">No packing records found</span>
                                @endif
                            @endforelse
                        </div>
                    </div>
                    @if($job_card->job_type != 'Common')
                    <div class="col-md-4 text-start">
                        <hr class="mt-3 mb-3">
                        <div class="d-flex gap-5">
                            <div>
                                <div class="summary-label">Printing Produced</div>
                                <div class="summary-value" style="font-size: 1.1rem; color: #3b82f6;">
                                    {{ number_format($job_card->processes()->where('from', 'Printing')->sum('estimate_production')) }} Pcs
                                </div>
                            </div>
                            <div>
                                <div class="summary-label">Cutting/Box Produced</div>
                                <div class="summary-value" style="font-size: 1.1rem; color: #10b981;">
                                    {{ number_format($job_card->processes()->whereIn('from', ['Cutting', 'Box'])->sum('estimate_production')) }} Pcs
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Billing Table -->
            <div class="table-responsive">
                <table class="table billing-table">
                    <thead>
                        <tr>
                            <th style="width: 40px;"></th>
                            <th style="width: 300px;">Description of Goods</th>
                            <th style="width: 100px;">Weight/Qty</th>
                            <th style="width: 80px;">Unit</th>
                            <th style="width: 100px;">Rate</th>
                            <th style="width: 100px;">Amount</th>
                            <th style="width: 80px;">GST</th>
                            <th style="width: 120px;">Total Amount</th>
                        </tr>
                    </thead>
                    <tbody id="billing_tbody">
                        <tr class="bill-row">
                            <td class="align-middle text-center p-1">
                                <button type="button" class="btn btn-sm btn-outline-danger remove-row"><i class="fa fa-times"></i></button>
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
                                    <option value="12">12%</option>
                                    <option value="18">18%</option>
                                    <option value="28">28%</option>
                                </select>
                            </td>
                            <td><input type="number" step="0.01" name="items[0][total_amount]" class="billing-input fw-bold row-total" readonly placeholder="0.00"></td>
                        </tr>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="7" class="text-start border-0 pt-3">
                                <button type="button" class="btn btn-sm btn-outline-primary" id="add_bill_row"><i class="fa fa-plus"></i> Add Item</button>
                            </td>
                        </tr>
                        <tr class="grand-total-row">
                            <th colspan="7" class="text-end">Grand Total Payable</th>
                            <th id="grand_total_display" class="f-20">₹ 0.00</th>
                        </tr>
                    </tfoot>
                </table>
            </div>

        </div>

        <div class="modal-footer border-top-0 d-flex justify-content-between px-4 pb-4">
            <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">Close</button>
            <button type="submit" class="btn btn-dark px-5" style="box-shadow: 0 4px 10px rgba(0,0,0,0.15); border-radius: 8px;">
                Complete Job & Archive
            </button>
        </div>
    </div>
</form>

<script>
    (function() {
        let rowCount = $('#billing_tbody tr.bill-row').length;

        $('#add_bill_row').off('click').on('click', function() {
            let newRow = `
            <tr class="bill-row">
                <td class="align-middle text-center p-1">
                    <button type="button" class="btn btn-sm btn-outline-danger remove-row"><i class="fa fa-times"></i></button>
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
                        <option value="12">12%</option>
                        <option value="18">18%</option>
                        <option value="28">28%</option>
                    </select>
                </td>
                <td><input type="number" step="0.01" name="items[${rowCount}][total_amount]" class="billing-input fw-bold row-total" readonly placeholder="0.00"></td>
            </tr>`;
            $('#billing_tbody').append(newRow);
            rowCount++;
        });

        $(document).off('click', '.remove-row').on('click', '.remove-row', function() {
            $(this).closest('tr').remove();
            calculateGrandTotal();
        });

        $(document).off('input change', '.amount-calc').on('input change', '.amount-calc', function() {
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
    })();
</script>
