@php
    use App\Helpers\PermissionHelper;
@endphp
@extends('layouts.admin.app')

@section('title', 'Ledger Transactions')

@section('css')
<style>
    .ledger-card { border-radius: 15px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); border: none; }
    .badge-dr { background: #fee2e2; color: #b91c1c; border-radius: 6px; padding: 4px 10px; font-weight: 700; }
    .badge-cr { background: #dcfce7; color: #166534; border-radius: 6px; padding: 4px 10px; font-weight: 700; }
    
    .dt-controls-wrap {
        display: flex;
        justify-content: flex-start;
        align-items: flex-end;
        flex-wrap: wrap;
        gap: 15px;
        margin-bottom: 20px;
    }
    .dt-controls-item {
        display: flex;
        flex-direction: column;
        gap: 5px;
    }
    .dt-controls-item label {
        margin-bottom: 0;
        font-weight: 700;
        font-size: 10px;
        text-transform: uppercase;
        color: #64748b;
        white-space: nowrap;
    }
    .select2-container .select2-selection--single{
        height:35px !important;
        padding:5px !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow{
        height:15px !important;
    }
    
    .locked-row input:not([type="hidden"]), 
    .locked-row select, 
    .locked-row .select2-selection {
        pointer-events: none !important;
        background-color: #e9ecef !important;
        opacity: 0.9 !important;
    }
</style>
@endsection

@section('breadcrumb-items')
    <li class="breadcrumb-item">Customer Ledger</li>
    <li class="breadcrumb-item active">Transactions</li>
@endsection

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card ledger-card mb-4 border-0 shadow-sm overflow-hidden">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-4">
                            <div>
                                <h4 class="mb-1 fw-bold text-primary">Ledger Transactions</h4>
                                <p class="mb-0 text-muted small text-uppercase fw-bold">Daily Debit & Credit History</p>
                            </div>
                            <div class="d-flex gap-2">
                                @if(PermissionHelper::check('vouchers', 'add'))
                                <button type="button" class="btn btn-danger px-4 shadow-sm fw-bold" onclick="openMultiEntryModal('Dr')">
                                    <i class="fa fa-minus-circle me-2"></i> ADD DEBIT
                                </button>
                                <button type="button" class="btn btn-success px-4 shadow-sm fw-bold" onclick="openMultiEntryModal('Cr')">
                                    <i class="fa fa-plus-circle me-2"></i> ADD CREDIT
                                </button>
                                <button type="button" class="btn btn-warning px-4 shadow-sm fw-bold text-dark" onclick="openMultiEntryModal('BadDebt')">
                                    <i class="fa fa-trash me-2"></i> ADD BAD DEBTS
                                </button>
                                @endif
                                <button type="button" class="btn btn-primary px-4 shadow-sm fw-bold d-none" data-bs-toggle="modal" data-bs-target="#addEntryModal">
                                    <i class="fa fa-plus-circle me-2"></i> RECORD PAYMENT / DUE
                                </button>
                            </div>
                        </div>

                        <div class="dt-controls-wrap px-2 mt-2">
                             <div class="dt-controls-item">
                                <label>Show</label>
                                <select id="basic-2_value" class="form-control form-control-sm" style="width: 70px;">
                                    <option value="50">50</option>
                                    <option value="250" selected>250</option>
                                    <option value="500">500</option>
                                </select>
                            </div>

                            <div class="dt-controls-item">
                                <label>From Date</label>
                                <input type="date" id="from_date" value="{{ request()->from_date ?? date('Y-m-d', strtotime('-7 days')) }}" class="form-control form-control-sm" onchange="get_datatable()">
                            </div>
                            <div class="dt-controls-item">
                                <label>To Date</label>
                                <input type="date" id="to_date" value="{{ request()->to_date ?? date('Y-m-d') }}" class="form-control form-control-sm" onchange="get_datatable()">
                            </div>

                            <div class="dt-controls-item">
                                <label>Type</label>
                                <select id="type_filter" class="form-control form-control-sm" onchange="get_datatable()" style="width: 100px;">
                                    <option value="">All Types</option>
                                    <option value="Dr" {{ request()->type == 'Dr' ? 'selected' : '' }}>Debit (Dr)</option>
                                    <option value="Cr" {{ request()->type == 'Cr' ? 'selected' : '' }}>Credit (Cr)</option>
                                    <option value="BadDebt" {{ request()->type == 'BadDebt' ? 'selected' : '' }}>Bad Debt</option>
                                </select>
                            </div>

                            <div class="dt-controls-item" style="min-width: 150px;">
                                <label>Payment Method</label>
                                <select id="payment_method_filter" class="form-control form-control-sm" onchange="get_datatable()">
                                    <option value="">All Methods</option>
                                    @foreach($payment_methods as $method)
                                        <option value="{{ $method->id }}">{{ $method->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="dt-controls-item" style="min-width: 200px;">
                                <label>Customer</label>
                                <select id="customer_filter" class="form-control form-control-sm js-example-basic-single" onchange="get_datatable()">
                                    <option value="">All Customers</option>
                                    @foreach($customers as $customer)
                                        <option value="{{ $customer->id }}">{{ $customer->name }} ({{ $customer->code }})</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="dt-controls-item" style="min-width: 180px;">
                                <label>Sale Executive</label>
                                <select id="sale_executive_filter" class="form-control form-control-sm" onchange="get_datatable()">
                                    <option value="">All Executives</option>
                                    @foreach($sale_executives as $executive)
                                        <option value="{{ $executive->id }}" {{ request()->sale_executive_id == $executive->id ? 'selected' : '' }}>{{ $executive->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="dt-controls-item">
                                <label>Role</label>
                                <select id="role_filter" class="form-control form-control-sm" onchange="get_datatable()" style="width: 120px;">
                                    <option value="">All Roles</option>
                                    @foreach($roles as $role)
                                        <option value="{{ $role }}">{{ $role }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="dt-controls-item">
                                <label>Search</label>
                                <input type="search" id="basic-2_search" class="form-control form-control-sm" placeholder="Remarks, Name..." style="min-width: 150px;">
                            </div>
                        </div>
                        
                        <div class="dt-ext" id="get_datatable">
                            <div class="loader-box"><div class="loader-37"></div></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Multi Entry Modal -->
    <div class="modal fade" id="multiEntryModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <form action="{{route('customer_ledger.store_multi_payment')}}" method="POST" id="multi_ledger_form" class="modal-content border-0 shadow-lg">
                @csrf
                <input type="hidden" name="type" id="multi_entry_type">
                <input type="hidden" id="multi_entry_original_type">
                <div class="modal-header py-3 text-white" id="multi_modal_header">
                    <h5 class="modal-title fw-bold" id="multi_modal_title">Multi Entry</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-0">
                    <div class="table-responsive">
                        <table class="table table-bordered mb-0" id="multi_entry_table">
                            <thead class="bg-light">
                                <tr>
                                    <th style="width: 150px;">Date</th>
                                    <th style="width: 250px;">Customer</th>
                                    <th id="th_amount" style="width: 150px;">Amount</th>
                                    <th id="th_payment_method" style="width: 200px;">Payment Method</th>
                                    <th>Remarks</th>
                                    <th style="width: 50px;">Action</th>
                                </tr>
                            </thead>
                            <tbody id="multi_entry_body">
                                <!-- Rows will be added here -->
                            </tbody>
                        </table>
                    </div>
                    <div class="p-3 bg-light border-top">
                        <button type="button" class="btn btn-outline-primary btn-sm fw-bold" onclick="addMultiEntryRow()">
                            <i class="fa fa-plus-circle me-1"></i> ADD MORE ROW
                        </button>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" id="submit_multi_ledger" class="btn btn-primary px-4 fw-bold">SAVE ALL ENTRIES</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Manual Entry Modal (Keeping it for compatibility if needed elsewhere, but hidden) -->
    <div class="modal fade" id="addEntryModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <form action="{{route('customer_ledger.store_payment')}}" method="POST" id="ledger_form" class="modal-content border-0 shadow-lg">
                @csrf
                <div class="modal-header bg-primary text-white py-3">
                    <h5 class="modal-title fw-bold"><i class="fa fa-plus-circle me-2"></i> Record Payment / Due Manually</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-uppercase mb-1">Select Customer</label>
                            <select name="customer_id" id="customer_select" class="form-select js-example-basic-single">
                                <option value="">Choose customer...</option>
                                @foreach($customers as $customer)
                                    <option value="{{ $customer->id }}">{{ $customer->name }} ({{ $customer->role }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-uppercase mb-1">Transaction Date</label>
                            <input type="date" name="date" value="{{ date('Y-m-d') }}" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold small text-uppercase mb-1">Amount</label>
                            <input type="text" name="amount" placeholder="0.00" class="form-control amount-decimal" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold small text-uppercase mb-1">Entry Type</label>
                            <select name="type" id="entry_type" class="form-select" onchange="togglePaymentMethod()">
                                <option value="Cr">Payment Received (Cr)</option>
                                <option value="Dr">Additional Due (Dr)</option>
                            </select>
                        </div>
                        <div class="col-md-12">
                            <div class="form-check form-switch p-2 bg-light rounded border">
                                <input class="form-check-input ms-0 me-2" type="checkbox" name="is_bad_debt" value="1" id="is_bad_debt_manual" onchange="togglePaymentMethod()">
                                <label class="form-check-label fw-bold text-warning-emphasis" for="is_bad_debt_manual">Mark as Bad Debt (Write-off)</label>
                            </div>
                        </div>
                        <div class="col-md-4" id="payment_method_val">
                            <label class="form-label fw-bold small text-uppercase mb-1">Payment Method</label>
                            <div class="input-group">
                                <select name="payment_method_id" id="payment_method_id" class="form-select">
                                    <option value="">Select Method...</option>
                                    @foreach($payment_methods as $method)
                                        <option value="{{ $method->id }}">{{ $method->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label fw-bold small text-uppercase mb-1">Remarks / Note</label>
                            <textarea name="remarks" class="form-control" rows="2" placeholder="ENTER REASON/DETAILS FOR THIS ENTRY..." style="text-transform: uppercase;" oninput="this.value = this.value.toUpperCase()" required></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" id="submit_ledger" class="btn btn-primary px-4 fw-bold">SAVE ENTRY</button>
                </div>
            </form>
        </div>
    </div>

    @if(auth()->user()->role_as == 'Admin')
    <!-- Shortcut Add Payment Method Modal -->
    <div class="modal fade" id="addPaymentMethodModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form action="{{ route('payment_method.store') }}" method="POST" id="shortcut_method_form" class="modal-content border-0 shadow-lg">
                @csrf
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title fw-bold">Quick Add Payment Method</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-uppercase mb-1">Method Name</label>
                        <input type="text" name="name" id="new_method_name" class="form-control" placeholder="E.g. PHONEPE, HDFC BANK" oninput="this.value = this.value.toUpperCase()" required>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-modal="hide">Close</button>
                    <button type="submit" class="btn btn-primary px-4 fw-bold">SAVE METHOD</button>
                </div>
            </form>
        </div>
    </div>
    @endif

    <!-- Quick Followup Modal -->
    <div class="modal fade" id="quickFollowupModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form id="quick_followup_form" class="modal-content border-0 shadow-lg">
                @csrf
                <input type="hidden" name="customer_id" id="followup_customer_id">
                <div class="modal-header bg-warning text-dark py-3">
                    <h5 class="modal-title fw-bold"><i class="fa fa-calendar-plus-o me-2"></i> Add Followup for <span id="followup_customer_name"></span></h5>
                    <button type="button" class="btn-close btn-close-dark" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold small">Date & Time</label>
                        <input type="datetime-local" name="followup_date_time" class="form-control" value="{{ date('Y-m-d\TH:i') }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small">Subject</label>
                        <input type="text" name="subject" class="form-control" placeholder="E.g. Payment Reminder..." required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small">Remarks / Response</label>
                        <textarea name="remarks" class="form-control" rows="3" placeholder="What did the customer say?"></textarea>
                    </div>
                    <input type="hidden" name="status" value="Continue">
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-warning px-4 fw-bold">SAVE FOLLOWUP</button>
                </div>
            </form>
        </div>
    </div>
    
    <div id="edit_modal_container"></div>
@endsection

@section('script')
    <script>
        $(document).ready(function(){
            $('.js-example-basic-single').select2({
                width: '100%'
            });
            $('#customer_select').select2({
                dropdownParent: $('#addEntryModal'),
                width: '100%'
            });
            get_datatable();
        });

        function toggleRowLock(btn) {
            var tr = $(btn).closest('tr');
            if(tr.hasClass('locked-row')) {
                tr.removeClass('locked-row');
                $(btn).removeClass('btn-outline-warning').addClass('btn-outline-success').html('<i class="fa fa-lock"></i>');
            } else {
                tr.addClass('locked-row');
                $(btn).removeClass('btn-outline-success').addClass('btn-outline-warning').html('<i class="fa fa-edit"></i>');
            }
        }

        function openMultiEntryModal(type) {
            var currentType = $('#multi_entry_original_type').val();
            var hasRows = $('#multi_entry_body tr').length > 0;
            
            // Re-open preserving old data if type matches
            if(hasRows && currentType === type) {
                $('#multiEntryModal').modal('show');
                return;
            }

            $('#multi_entry_type').val(type == 'BadDebt' ? 'Cr' : type);
            $('#multi_entry_original_type').val(type);
            $('#multi_entry_body').empty();
            
            // Re-store original type for row generation logic if needed, 
            // but we can just use the header title or a temp var.
            var displayType = type; 
            
            if(displayType == 'Dr') {
                $('#multi_modal_header').removeClass('bg-success bg-warning').addClass('bg-danger');
                $('#multi_modal_title').html('<i class="fa fa-minus-circle me-2"></i> Add Multiple Debits (Additional Due)');
                $('#th_payment_method').hide();
                $('#submit_multi_ledger').removeClass('btn-success btn-warning').addClass('btn-danger');
            } else if(type == 'BadDebt') {
                $('#multi_modal_header').removeClass('bg-danger bg-success').addClass('bg-warning text-dark');
                $('#multi_modal_title').html('<i class="fa fa-trash me-2"></i> Record Multiple Bad Debts (Write-offs)');
                $('#th_amount').hide();
                $('#th_payment_method').hide();
                $('#submit_multi_ledger').removeClass('btn-danger btn-success').addClass('btn-warning text-dark');
            } else {
                $('#multi_modal_header').removeClass('bg-danger bg-warning').addClass('bg-success');
                $('#multi_modal_title').html('<i class="fa fa-plus-circle me-2"></i> Add Multiple Credits (Payment Received)');
                $('#th_amount').show();
                $('#th_payment_method').show();
                $('#submit_multi_ledger').removeClass('btn-danger btn-warning').addClass('btn-success');
            }
            
            addMultiEntryRow();
            $('#multiEntryModal').modal('show');
        }

        function addMultiEntryRow() {
            var type = $('#multi_entry_original_type').val();
            var rowIdx = $('#multi_entry_body tr').length;
            var isBadDebt = type == 'BadDebt' ? 1 : 0;
            var finalType = $('#multi_entry_type').val();
            
            var html = `<tr>
                <td>
                    <input type="date" name="dates[]" value="{{ date('Y-m-d') }}" class="form-control form-control-sm multi-date" required>
                    <input type="hidden" name="is_bad_debt[]" value="${isBadDebt}">
                </td>
                <td>
                    <select name="customer_ids[]" class="form-select form-select-sm customer-select-multi" required>
                        <option value="">Select Customer</option>
                        @foreach($customers as $customer)
                            <option value="{{ $customer->id }}">{{ str_replace('`', '\`', $customer->name) }}</option>
                        @endforeach
                    </select>
                </td>
                <td class="td-amount" style="${type == 'BadDebt' ? 'display:none;' : ''}"><input type="text" name="amounts[]" class="form-control form-control-sm amount-decimal" placeholder="0.00" ${type != 'BadDebt' ? 'required' : ''}></td>
                <td class="td-payment-method" style="${(type == 'Dr' || type == 'BadDebt') ? 'display:none;' : ''}">
                    <select name="payment_method_ids[]" class="form-select form-select-sm payment-method-field" ${type == 'Cr' ? 'required' : ''}>
                        <option value="">Method</option>
                        @foreach($payment_methods as $method)
                            <option value="{{ $method->id }}">{{ $method->name }}</option>
                        @endforeach
                    </select>
                </td>
                <td><input type="text" name="remarks[]" class="form-control form-control-sm last-row-input" placeholder="REMARKS" style="text-transform: uppercase;" oninput="this.value = this.value.toUpperCase()" required></td>
                <td class="text-center">
                    <button type="button" class="btn btn-outline-success btn-xs me-1 toggle-lock-btn" onclick="toggleRowLock(this)" title="Lock/Unlock Row">
                        <i class="fa fa-lock"></i>
                    </button>
                    <button type="button" class="btn btn-outline-danger btn-xs" onclick="removeMultiEntryRow(this)">
                        <i class="fa fa-times"></i>
                    </button>
                </td>
            </tr>`;
            
            // Lock all existing rows before appending the new one
            $('#multi_entry_body tr').each(function() {
                if(!$(this).hasClass('locked-row')) {
                    var lockBtn = $(this).find('.toggle-lock-btn');
                    if(lockBtn.length) {
                        toggleRowLock(lockBtn[0]);
                    }
                }
            });

            $('#multi_entry_body').append(html);
            var $newRow = $('#multi_entry_body tr').last();
            
            $newRow.find('.customer-select-multi').select2({
                dropdownParent: $('#multiEntryModal'),
                width: '100%'
            });

            // Focus first element if not initial load
            if(rowIdx > 0) {
                $newRow.find('.multi-date').focus();
            }
        }

        // Auto add row on Enter/Tab on last input
        $(document).on('keydown', '.last-row-input', function(e) {
            if (e.which == 13 || e.which == 9) { // Enter or Tab
                var isLastRow = $(this).closest('tr').is(':last-child');
                if (isLastRow && $(this).val() !== "") {
                    // Prevent default form submission on Enter, and focus shifting on Tab
                    e.preventDefault();
                    addMultiEntryRow();
                } else if (e.which == 13) {
                    // Always prevent form submission on Enter in this field
                    e.preventDefault();
                }
            }
        });

        // Strict Numeric Input Validation
        $(document).on('keypress', '.amount-decimal', function(e) {
            var charCode = (e.which) ? e.which : e.keyCode;
            var val = $(this).val();
            
            // Allow only one minus sign at the beginning
            if (charCode === 45) { // "-"
                if (val.length > 0 || val.indexOf('-') !== -1) return false;
                return true;
            }
            
            // Allow only one decimal point
            if (charCode === 46) { // "."
                if (val.indexOf('.') !== -1) return false;
                return true;
            }
            
            // Allow only numbers
            if (charCode > 31 && (charCode < 48 || charCode > 57)) {
                return false;
            }
            
            // Max 3 decimal places while typing
            if (val.indexOf('.') !== -1) {
                var parts = val.split('.');
                if (parts[1].length >= 2) {
                    // Check if cursor is after decimal point
                    if (this.selectionStart > val.indexOf('.')) {
                        return false;
                    }
                }
            }
            
            return true;
        });

        // Handle pasting and cleanup on input
        $(document).on('input', '.amount-decimal', function() {
            var val = $(this).val();
            
            // Remove multiple/misplaced minuses
            if (val.indexOf('-') > 0) {
                val = val.substring(0, 1) + val.substring(1).replace(/-/g, '');
            }
            if (val.startsWith('--')) {
                val = '-' + val.replace(/^-+/, '');
            }
            
            // Remove multiple decimals
            var parts = val.split('.');
            if (parts.length > 2) {
                val = parts[0] + '.' + parts.slice(1).join('');
            }
            
            // Max 3 decimals
            if (parts.length > 1 && parts[1].length > 3) {
                val = parts[0] + '.' + parts[1].substring(0, 2);
            }
            
            // Strip non-numeric junk
            val = val.replace(/[^0-9.-]/g, '');
            
            $(this).val(val);
        });

        // Format amount to 3 decimals on blur and prevent negatives (Math.abs)
        $(document).on('blur', '.amount-decimal', function() {
            var val = parseFloat($(this).val());
            if (!isNaN(val)) {
                // Keep Math.abs as per original logic if it's there to prevent negative entries
                $(this).val(Math.abs(val).toFixed(2));
            }
        });

        function removeMultiEntryRow(btn) {
            if($('#multi_entry_body tr').length > 1) {
                $(btn).closest('tr').remove();
            } else {
                $.notify({ title:'Warning', message:'At least one row is required.' }, { type:'warning' });
            }
        }

        $('#multi_ledger_form').on('submit', function(e){
            e.preventDefault();
            var form = $(this);
            var btn = $('#submit_multi_ledger');
            
            swal({
                title: "Are you sure?",
                text: "Do you want to save all these entries?",
                icon: "warning",
                buttons: ["Cancel", "Yes, Save All"],
                dangerMode: false,
            })
            .then((willSave) => {
                if (willSave) {
                    btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin me-2"></i> SAVING...');

                    $.ajax({
                        url: form.attr('action'),
                        type: 'POST',
                        data: form.serialize(),
                        success: function(data){
                            if(data.result == 1){
                                $.notify({ title:'Success', message:data.message }, { type:'success' });
                                $('#multiEntryModal').modal('hide');
                                $('#multi_entry_body').empty(); // Clear form only on success
                                get_datatable();
                                btn.prop('disabled', false).html('SAVE ALL ENTRIES');
                            } else {
                                $.notify({ title:'Error', message:data.message }, { type:'danger' });
                                btn.prop('disabled', false).html('SAVE ALL ENTRIES');
                            }
                        },
                        error: function(xhr){
                            let msg = xhr.responseJSON ? xhr.responseJSON.message : 'Error processing request.';
                            $.notify({ title:'Error', message:msg }, { type:'danger' });
                            btn.prop('disabled', false).html('SAVE ALL ENTRIES');
                        }
                    });
                }
            });
        });

        function togglePaymentMethod() {
            var typeSelect = $('#entry_type');
            var isBadDebt = $('#is_bad_debt_manual').is(':checked');
            
            if(isBadDebt) {
                typeSelect.val('Cr').attr('disabled', true);
                if(!$('#bad_debt_hidden_type').length) {
                    $('<input>').attr({
                        type: 'hidden',
                        name: 'type',
                        id: 'bad_debt_hidden_type',
                        value: 'Cr'
                    }).appendTo('#ledger_form');
                }
            } else {
                typeSelect.attr('disabled', false);
                $('#bad_debt_hidden_type').remove();
            }

            var isDr = typeSelect.val() == 'Dr';
            
            if(isDr || isBadDebt) {
                $('#payment_method_val').hide().find('select').prop('required', false).val('');
            } else {
                $('#payment_method_val').show().find('select').prop('required', true);
            }
        }

        $('#ledger_form').on('submit', function(e){
            e.preventDefault();
            var form = $(this);
            if (form.data('submitting')) return false;
            
            var btn = $('#submit_ledger');
            form.data('submitting', true);
            btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin me-2"></i> SAVING...');

            $.ajax({
                url: form.attr('action'),
                type: 'POST',
                data: form.serialize(),
                success: function(data){
                    if(data.result == 1){
                        $.notify({ title:'Success', message:data.message }, { type:'success' });
                        $('#addEntryModal').modal('hide'); // Close the modal
                        form[0].reset();
                        $('.js-example-basic-single').val('').trigger('change'); // Reset Select2
                        $('#entry_type').val('Cr'); // Ensure reset to Cr
                        togglePaymentMethod(); // Ensure method select is shown Correctly
                        get_datatable();
                        btn.prop('disabled', false).html('SAVE ENTRY');
                        form.data('submitting', false);
                    } else {
                        $.notify({ title:'Error', message:data.message }, { type:'danger' });
                        btn.prop('disabled', false).html('SAVE ENTRY');
                        form.data('submitting', false);
                    }
                },
                error: function(xhr){
                    let msg = xhr.responseJSON ? xhr.responseJSON.message : 'Error processing request.';
                    $.notify({ title:'Error', message:msg }, { type:'danger' });
                    btn.prop('disabled', false).html('SAVE ENTRY');
                    form.data('submitting', false);
                }
            });
        });

        $('#shortcut_method_form').on('submit', function(e){
            e.preventDefault();
            var form = $(this);
            var btn = form.find('button[type="submit"]');
            btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin me-2"></i>...');

            $.ajax({
                url: form.attr('action'),
                type: 'POST',
                data: form.serialize(),
                success: function(data){
                    if(data.result == 1){
                        $.notify({ title:'Success', message:data.message }, { type:'success' });
                        $('#addPaymentMethodModal').modal('hide');
                        form[0].reset();
                        btn.prop('disabled', false).html('SAVE METHOD');
                        
                        // Refresh payment methods list
                        refreshPaymentMethods();
                    } else {
                        $.notify({ title:'Error', message:data.message }, { type:'danger' });
                        btn.prop('disabled', false).html('SAVE METHOD');
                    }
                }
            });
        });

        function refreshPaymentMethods() {
            // Get all payment methods via AJAX and update dropdown
            $.get('{{ route("payment_method.datatable") }}', function(data) {
                // Actually easier to just reload the page or fetch a specific list
                // But let's try to update dynamically
                location.reload(); // Simple and safe for now to ensure all dropdowns match
            });
        }

        function debounce(func, wait) {
            let timeout;
            return function(...args) {
                const context = this;
                clearTimeout(timeout);
                timeout = setTimeout(() => func.apply(context, args), wait);
            };
        }

        function get_datatable(page){
            var $container = $('#get_datatable');
            if ($container.length) {
                $container.html('<div class="loader-box"><div class="loader-37"></div></div>');
                var search = $('#basic-2_search').val();
                var value = $('#basic-2_value').val();
                var from_date = $('#from_date').val();
                var to_date = $('#to_date').val();
                var type = $('#type_filter').val();
                var customer_id = $('#customer_filter').val();
                var payment_method_id = $('#payment_method_filter').val();
                var role = $('#role_filter').val();
                var sale_executive_id = $('#sale_executive_filter').val();
                var page = page ?? 1;
                $.ajax({
                    url: '{{ route("customer_ledger.transactions_datatable") }}',
                    data: { 
                        page: page, 
                        search: search, 
                        value: value, 
                        from_date: from_date, 
                        to_date: to_date, 
                        type: type, 
                        customer_id: customer_id, 
                        payment_method_id: payment_method_id,
                        role: role, 
                        sale_executive_id: sale_executive_id 
                    },
                    type: 'GET',
                    success: function(data){
                        $container.html(data);
                        $('#basic-test').DataTable({ dom: 'Brt', "pageLength": -1 , responsive: true, ordering: false});
                    }
                });
            }
        }

        function editEntry(id) {
            $.ajax({
                url: '{{ route("customer_ledger.edit_modal", "") }}/' + id,
                type: 'GET',
                success: function(data){
                    if(data.result === 0) {
                        $.notify({ title:'Error', message:data.message }, { type:'danger' });
                        return;
                    }
                    $('#edit_modal_container').html(data);
                    $('#editEntryModal').modal('show');
                },
                error: function(xhr){
                    let msg = xhr.responseJSON ? xhr.responseJSON.message : 'Error fetching modal.';
                    $.notify({ title:'Error', message:msg }, { type:'danger' });
                }
            });
        }

        function deleteEntry(id) {
            swal({
                title: "Are you sure?",
                text: "Once deleted, you will not be able to recover this manual ledger entry!",
                icon: "warning",
                buttons: true,
                dangerMode: true,
            })
            .then((willDelete) => {
                if (willDelete) {
                    $.ajax({
                        url: '{{ url("admin/customer_ledgers/delete") }}/' + id,
                        type: 'GET',
                        success: function(data) {
                            if(data.result == 1) {
                                $.notify({ title:'Success', message:data.message }, { type:'success' });
                                get_datatable();
                            } else {
                                $.notify({ title:'Error', message:data.message }, { type:'danger' });
                            }
                        }
                    });
                }
            });
        }

        function openFollowupModal(id, name) {
            $('#followup_customer_id').val(id);
            $('#followup_customer_name').text(name);
            $('#quickFollowupModal').modal('show');
        }

        $('#quick_followup_form').submit(function(e){
            e.preventDefault();
            var $btn = $(this).find('button[type="submit"]');
            $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin me-1"></i> SAVING...');
            
            $.ajax({
                url: '{{ route("ledger_followup.store") }}',
                type: 'POST',
                data: $(this).serialize(),
                success: function(res){
                    if(res.result == 1){
                        $.notify({title:'Success', message:res.message}, {type:'success'});
                        $('#quickFollowupModal').modal('hide');
                        $('#quick_followup_form')[0].reset();
                    }
                    $btn.prop('disabled', false).html('SAVE FOLLOWUP');
                },
                error: function(){
                    $.notify({title:'Error', message:'Something went wrong'}, {type:'danger'});
                    $btn.prop('disabled', false).html('SAVE FOLLOWUP');
                }
            });
        });

        $(document).on('click','.pages a',function(n){
            n.preventDefault();
            var page = $(this).attr('href').split("page=")[1];
            get_datatable(page);
        });

        $('#basic-2_search').on('keyup search', debounce(function() { get_datatable(); }, 500));
        $('#basic-2_value').on('change', function() { get_datatable(); });
    </script>
@endsection
