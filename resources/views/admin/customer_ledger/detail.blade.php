@extends('layouts.admin.app')

@section('title', 'Ledger Detail - ' . $customer->name)

@section('css')
<style>
    .ledger-card { border-radius: 12px; border: 1px solid #e2e8f0; }
    .customer-summary { background: #f8fafc; border-radius: 10px; padding: 15px; border-left: 5px solid #3b82f6; }
    .select2-container .select2-selection--single{
        height:30px !important;
        padding:5px !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow{
        height:12px !important;
    }
    .dt-controls-wrap {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 15px;
        margin-bottom: 20px;
    }
    .dt-controls-item {
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .dt-controls-item label {
        margin-bottom: 0;
        font-weight: 700;
        font-size: 10px;
        text-transform: uppercase;
        color: #64748b;
        white-space: nowrap;
    }
    .btn-success i, 
    .btn-success:hover i,
    .btn-success:focus i,
    .btn-success:active i,
    table.table tr:hover .btn-success i,
    table.table .btn-success:hover i {
        color: #ffffff !important;
        fill: #ffffff !important;
    }
    .btn-success .fa-check {
        color: #28a745 !important;
    }
</style>
@endsection

@section('breadcrumb-items')
    <li class="breadcrumb-item"><a href="{{ route('customer_ledger.index') }}">Customer Ledger</a></li>
    <li class="breadcrumb-item active">Ledger Detail</li>
@endsection

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card ledger-card shadow-sm mb-4 border-0">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-4">
                            <div class="customer-summary">
                                <h4 class="mb-1 fw-bold text-primary">{{ $customer->name }}</h4>
                                <p class="mb-0 text-muted small text-uppercase fw-bold"><i class="fa fa-user-circle me-1"></i> {{ $customer->role }} | <i class="fa fa-phone me-1"></i> {{ $customer->phone_no }}</p>
                            </div>
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-dark px-4 shadow-sm fw-bold" onclick="exportIndividualPDF()">
                                    <i class="fa fa-file-pdf-o me-2"></i> VIEW PDF SUMMARY
                                </button>
                                @php
                                    $lastSent = \Illuminate\Support\Facades\Cache::get('ledger_whatsapp_sent_' . $customer->id);
                                    $isRecentlySent = $lastSent ? true : false;
                                @endphp
                                <button type="button" 
                                    class="btn {{ $isRecentlySent ? 'btn-success' : 'btn-outline-success' }} px-4 shadow-sm fw-bold border-2 d-flex align-items-center gap-2" 
                                    onclick="sendWhatsAppSummary()" 
                                    title="{{ $isRecentlySent ? 'Last sent: ' . \Carbon\Carbon::parse($lastSent)->diffForHumans() : 'Send to WhatsApp' }}">
                                    <i class="fa fa-whatsapp {{ $isRecentlySent ? 'text-white' : 'text-success' }}"></i>
                                    <span>{{ $isRecentlySent ? 'WHATSAPP SENT' : 'SEND TO WHATSAPP' }}</span>
                                    @if($isRecentlySent)
                                        <i class="fa fa-check-circle-o fa-lg ms-1"></i>
                                    @endif
                                </button>
                                <a href="{{ route('customer_ledger.index') }}" class="btn btn-outline-secondary px-4 shadow-sm fw-bold">
                                    <i class="fa fa-arrow-left me-2"></i> BACK TO SUMMARY
                                </a>
                            </div>
                        </div>

                        <div class="dataTables_wrapper">
                            <div class="dt-controls-wrap px-1">
                                <div class="dt-controls-item">
                                    <label>Show</label>
                                    <select id="basic-2_value" class="form-control form-control-sm" style="width: auto;">
                                        <option value="50">50</option>
                                        <option value="250" selected>250</option>
                                        <option value="500">500</option>
                                    </select>
                                </div>

                                <div class="dt-controls-item">
                                    <label>Filter From</label>
                                    <input type="date" id="from_date" value="{{ $from_date ?? '' }}" class="form-control form-control-sm" onchange="get_datatable()">
                                    <label>To</label>
                                    <input type="date" id="to_date" value="{{ $to_date ?? '' }}" class="form-control form-control-sm" onchange="get_datatable()">
                                </div>

                                <div class="dt-controls-item">
                                    <label>Type</label>
                                    <select id="type_filter" class="form-control form-control-sm" onchange="get_datatable()" style="width: 120px;">
                                        <option value="">All Types</option>
                                        <option value="Dr">Debit (Dr)</option>
                                        <option value="Cr">Credit (Cr)</option>
                                        <option value="BadDebt">Bad Debt</option>
                                    </select>
                                </div>

                                <div class="dt-controls-item">
                                    <label>Search Particulars</label>
                                    <input type="search" id="basic-2_search" class="form-control form-control-sm" placeholder="Search job, remarks..." style="min-width: 200px;">
                                </div>
                            </div>

                            <div class="dt-ext" id="get_datatable">
                                <div class="loader-box"><div class="loader-37"></div></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Followup History Section -->
                <div class="card ledger-card shadow-sm mb-4 border-0">
                    <div class="card-header bg-light py-3">
                        <h5 class="mb-0 fw-bold text-dark"><i class="fa fa-history me-2"></i> Followup History ({{ $followups->count() }})</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="small fw-bold">SCHEDULED</th>
                                        <th class="small fw-bold">SUBJECT</th>
                                        <th class="small fw-bold text-center">ITERATIONS</th>
                                        <th class="small fw-bold">STATUS</th>
                                        <th class="small fw-bold">EXECUTIVE</th>
                                        <th class="small fw-bold text-center">ACTION</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($followups as $f)
                                        @php
                                            $latest = $f->histories->sortByDesc('id')->first();
                                            $history_count = $f->histories->count() + 1;
                                            $current_status = $latest ? $latest->status : $f->status;
                                            
                                            // Delay calc (simplified since columns are different)
                                            $delay_days = $f->total_no_of_days ?? 0;
                                            foreach($f->histories as $h) {
                                                $delay_days += ($h->total_no_of_days ?? 0);
                                            }
                                        @endphp
                                        <tr>
                                            <td class="small fw-bold text-dark">{{ date('d-m-Y H:i', strtotime($f->start_date)) }}</td>
                                            <td>
                                                <div class="small fw-bold text-dark">{{ $f->subject }}</div>
                                                <div class="extra-small text-muted italic text-truncate" style="max-width: 300px;">{{ $latest ? $latest->remarks : $f->remarks }}</div>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge bg-info-subtle text-info border px-2">{{ $history_count }}</span>
                                            </td>
                                            <td>
                                                @if($current_status == 'Continue')
                                                    <span class="badge bg-warning-subtle text-warning border extra-small">CONTINUE</span>
                                                @else
                                                    <span class="badge bg-success-subtle text-success border extra-small">CLOSED</span>
                                                @endif
                                                @if($delay_days > 0)
                                                    <div class="extra-small text-danger fw-bold mt-1">Delayed {{ $delay_days }} Days</div>
                                                @endif
                                            </td>
                                            <td class="small">{{ $f->user->name ?? 'N/A' }}</td>
                                            <td class="text-center">
                                                <button type="button" class="btn btn-outline-primary btn-xs px-2" onclick="viewHistory({{ $f->id }})">
                                                    <i class="fa fa-eye"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center py-4 text-muted small italic">No followup recorded for this customer yet.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- History Modal Container -->
    <div id="historyModalContainer"></div>

    <!-- Multi Entry Modal -->
    <div class="modal fade" id="multiEntryModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <form action="{{route('customer_ledger.store_multi_payment')}}" method="POST" id="multi_ledger_form" class="modal-content border-0 shadow-lg">
                @csrf
                <input type="hidden" name="type" id="multi_entry_type">
                <input type="hidden" id="multi_entry_original_type">
                <div class="modal-header py-3 text-white" id="multi_modal_header">
                    <h5 class="modal-title fw-bold" id="multi_modal_title">Multi Entry for {{ $customer->name }}</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-0">
                    <div class="table-responsive">
                        <table class="table table-bordered mb-0" id="multi_entry_table">
                            <thead class="bg-light">
                                <tr>
                                    <th style="width: 150px;">Date</th>
                                    <th style="width: 150px;">Amount</th>
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

    <div id="dynamic_modal_container"></div>
    <div class="modal fade" id="job_card_modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl" id="ajax_html2"></div>
    </div>
    <!-- Manual Entry Modal -->
    <div class="modal fade" id="addEntryModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <form action="{{route('customer_ledger.store_payment')}}" method="POST" id="ledger_form" class="modal-content border-0 shadow-lg">
                @csrf
                <input type="hidden" name="customer_id" value="{{ $customer->id }}">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title fw-bold"><i class="fa fa-plus-circle me-2"></i> Record Payment / Due for {{ $customer->name }}</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-uppercase mb-1">Transaction Date</label>
                            <input type="date" name="date" value="{{ date('Y-m-d') }}" class="form-control" required>
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
                        <div class="col-md-4" id="payment_method_val">
                            <label class="form-label fw-bold small text-uppercase mb-1">Payment Method</label>
                            <div class="input-group">
                                <select name="payment_method_id" id="payment_method_id" class="form-select">
                                    <option value="">Select Method...</option>
                                    @foreach($payment_methods as $method)
                                        <option value="{{ $method->id }}">{{ $method->name }}</option>
                                    @endforeach
                                </select>
                                @if(auth()->user()->role_as == 'Admin')
                                <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addPaymentMethodModal" title="Quick Add Payment Method">
                                    <i class="fa fa-plus"></i>
                                </button>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label fw-bold small text-uppercase mb-1">Remarks / Note</label>
                            <textarea name="remarks" class="form-control" rows="2" placeholder="Enter any additional details..."></textarea>
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
            <form action="{{ route('payment_method.store') }}" method="POST" id="payment_method_form" class="modal-content border-0 shadow-lg">
                @csrf
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title fw-bold"><i class="fa fa-plus-circle me-2"></i> Add Payment Method</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-uppercase mb-1">Method Name</label>
                        <input type="text" name="name" class="form-control" placeholder="e.g. Bank, Cash, GPay" required>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" id="submit_method" class="btn btn-success px-4 fw-bold">SAVE METHOD</button>
                </div>
            </form>
        </div>
    </div>
    @endif
@endsection

@section('script')
    <script>
        $(document).ready(function(){
            $('.js-example-basic-single').select2({
                dropdownParent: $('#addEntryModal'),
                width: '100%'
            });
            get_datatable();
        });

        function openMultiEntryModal(type) {
            $('#multi_entry_type').val(type == 'BadDebt' ? 'Cr' : type);
            $('#multi_entry_original_type').val(type);
            $('#multi_entry_body').empty();
            
            if(type == 'Dr') {
                $('#multi_modal_header').removeClass('bg-success bg-warning').addClass('bg-danger');
                $('#multi_modal_title').html('<i class="fa fa-minus-circle me-2"></i> Add Multiple Debits for {{ str_replace("'", "\'", $customer->name) }}');
                $('#th_payment_method').hide();
                $('#submit_multi_ledger').removeClass('btn-success btn-warning').addClass('btn-danger');
            } else if(type == 'BadDebt') {
                $('#multi_modal_header').removeClass('bg-danger bg-success').addClass('bg-warning text-dark');
                $('#multi_modal_title').html('<i class="fa fa-trash me-2"></i> Record Multiple Bad Debts for {{ str_replace("'", "\'", $customer->name) }}');
                $('#th_payment_method').hide();
                $('#submit_multi_ledger').removeClass('btn-danger btn-success').addClass('btn-warning text-dark');
            } else {
                $('#multi_modal_header').removeClass('bg-danger bg-warning').addClass('bg-success');
                $('#multi_modal_title').html('<i class="fa fa-plus-circle me-2"></i> Add Multiple Credits for {{ str_replace("'", "\'", $customer->name) }}');
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
            var finalType = $('#multi_entry_original_type').val();
            
            var html = `<tr>
                <td>
                    <input type="hidden" name="customer_ids[]" value="{{ $customer->id }}">
                    <input type="hidden" name="is_bad_debt[]" value="${isBadDebt}">
                    <input type="date" name="dates[]" value="{{ date('Y-m-d') }}" class="form-control form-control-sm multi-date" required>
                </td>
                <td><input type="text" name="amounts[]" class="form-control form-control-sm amount-decimal" placeholder="0.00" required></td>
                <td class="td-payment-method" style="${(type == 'Dr' || type == 'BadDebt') ? 'display:none;' : ''}">
                    <select name="payment_method_ids[]" class="form-select form-select-sm" ${type == 'Cr' ? 'required' : ''}>
                        <option value="">Method</option>
                        @foreach($payment_methods as $method)
                            <option value="{{ $method->id }}">{{ $method->name }}</option>
                        @endforeach
                    </select>
                </td>
                <td><input type="text" name="remarks[]" class="form-control form-control-sm last-row-input" placeholder="Remarks"></td>
                <td class="text-center">
                    <button type="button" class="btn btn-outline-danger btn-xs" onclick="removeMultiEntryRow(this)">
                        <i class="fa fa-times"></i>
                    </button>
                </td>
            </tr>`;
            
            $('#multi_entry_body').append(html);
            var $newRow = $('#multi_entry_body tr').last();
            
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
                    // If it's Tab, we need to prevent default if we're manually focusing
                    if(e.which == 9) {
                        e.preventDefault();
                    }
                    addMultiEntryRow();
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
                // Keep Math.abs as per original logic if it's there to prevent negative entries, 
                // but if negative entries are desired, the Math.abs should be removed.
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
            btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin me-2"></i> SAVING...');

            $.ajax({
                url: form.attr('action'),
                type: 'POST',
                data: form.serialize(),
                success: function(data){
                    if(data.result == 1){
                        $.notify({ title:'Success', message:data.message }, { type:'success' });
                        $('#multiEntryModal').modal('hide');
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
        });

        function togglePaymentMethod() {
            if($('#entry_type').val() == 'Dr') {
                $('#payment_method_val').hide().find('select').prop('required', false).val('');
            } else {
                $('#payment_method_val').show().find('select').prop('required', true);
            }
        }

        function get_datatable(page){
            var $container = $('#get_datatable');
            if ($container.length) {
                $container.html('<div class="loader-box"><div class="loader-37"></div></div>');
                var value = $('#basic-2_value').val();
                var search = $('#basic-2_search').val();
                var from_date = $('#from_date').val();
                var to_date = $('#to_date').val();
                var type = $('#type_filter').val();
                var page = page ?? 1;
                $.ajax({
                    url: '{{ route("customer_ledger.detail_datatable", $customer->id) }}',
                    data: { page: page, value: value, search: search, from_date: from_date, to_date: to_date, type: type },
                    type: 'GET',
                    success: function(data){
                        $container.html(data);
                        $('#basic-test').DataTable({ dom: 'Brt', "pageLength": -1 , responsive: true, ordering: false});
                    }
                });
            }
        }

        $('#ledger_form').on('submit', function(e){
            e.preventDefault();
            var form = $(this);
            var btn = $('#submit_ledger');
            btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin me-2"></i> SAVING...');

            $.ajax({
                url: form.attr('action'),
                type: 'POST',
                data: form.serialize(),
                success: function(data){
                    if(data.result == 1){
                        $.notify({ title:'Success', message:data.message }, { type:'success' });
                        $('#addEntryModal').modal('hide');
                        form[0].reset();
                        $('#entry_type').val('Cr');
                        togglePaymentMethod();
                        get_datatable();
                        btn.prop('disabled', false).html('SAVE ENTRY');
                    } else {
                        $.notify({ title:'Error', message:data.message }, { type:'danger' });
                        btn.prop('disabled', false).html('SAVE ENTRY');
                    }
                },
                error: function(xhr){
                    let msg = xhr.responseJSON ? xhr.responseJSON.message : 'Error processing request.';
                    $.notify({ title:'Error', message:msg }, { type:'danger' });
                    btn.prop('disabled', false).html('SAVE ENTRY');
                }
            });
        });

        $('#payment_method_form').on('submit', function(e){
            e.preventDefault();
            var form = $(this);
            var btn = $('#submit_method');
            btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin me-2"></i> SAVING...');

            $.ajax({
                url: form.attr('action'),
                type: 'POST',
                data: form.serialize(),
                success: function(data){
                    if(data.result == 1){
                        $.notify({ title:'Success', message:data.message }, { type:'success' });
                        $('#addPaymentMethodModal').modal('hide');
                        form[0].reset();
                        
                        // Add new option to the dropdown and select it
                        var newOption = new Option(data.method.name, data.method.id, true, true);
                        $('#payment_method_id').append(newOption).trigger('change');
                        
                        btn.prop('disabled', false).html('SAVE METHOD');
                    } else {
                        $.notify({ title:'Error', message:data.message }, { type:'danger' });
                        btn.prop('disabled', false).html('SAVE METHOD');
                    }
                },
                error: function(xhr){
                    let msg = xhr.responseJSON ? xhr.responseJSON.message : 'Error processing request.';
                    $.notify({ title:'Error', message:msg }, { type:'danger' });
                    btn.prop('disabled', false).html('SAVE METHOD');
                }
            });
        });

        // Debounce function
        function debounce(func, wait) {
            let timeout;
            return function(...args) {
                const context = this;
                clearTimeout(timeout);
                timeout = setTimeout(() => func.apply(context, args), wait);
            };
        }

        function viewHistory(id) {
            $.get('{{ url("admin/ledger_followups/history") }}/' + id, function(html) {
                $('#historyModalContainer').html(html);
                $('#historyModal').modal('show');
            });
        }

        // Auto refresh detail datatable on followup update
        $(document).on('submit', '#followup_update_form', function(e) {
            e.preventDefault();
            var form = $(this);
            $.ajax({
                url: '{{ route("ledger_followup.update_thread") }}',
                type: 'POST',
                data: form.serialize(),
                success: function(res) {
                    if(res.result == 1) {
                        $.notify({title:'Success', message:res.message}, {type:'success'});
                        $('#historyModal').modal('hide');
                        location.reload(); // Reload to refresh history list
                    }
                }
            });
        });

        function exportIndividualPDF() {
            var from_date = $('#from_date').val();
            var to_date = $('#to_date').val();
            var url = '{{ route("customer_ledger.individual_pdf", $customer->id) }}?from_date=' + from_date + '&to_date=' + to_date;
            window.open(url, '_blank');
        }

        $(document).on('click','.pages a',function(n){
            n.preventDefault();
            var page = $(this).attr('href').split("page=")[1];
            get_datatable(page);
        });

        $('#basic-2_value').on('change', function() { get_datatable(); });
        $('#basic-2_search').on('keyup search', debounce(function() { get_datatable(); }, 500));

        function editEntry(id) {
            $.ajax({
                url: '{{ url("admin/customer_ledgers/edit_modal") }}/' + id,
                type: 'GET',
                success: function(data) {
                    if(data.result === 0) {
                        $.notify({ title:'Error', message:data.message }, { type:'danger' });
                    } else {
                        $('#dynamic_modal_container').html(data);
                        $('#editEntryModal').modal('show');
                    }
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

        function viewJobDetails(id) {
            var url = '{{ url("admin/job_cards/view_billing_details") }}/' + id + '?hide_tabs=1';
            $('#ajax_html2').html('<div class="loader-box"><div class="loader-37"></div></div>');
            $('#job_card_modal').modal('show');
            $.get(url, {}, function(data) {
                $('#ajax_html2').html(data);
                if($('#account-tab').length) {
                    $('#account-tab').tab('show');
                }
            });
        }
        function sendWhatsAppSummary() {
            swal({
                title: "Are you sure?",
                text: "Send the current ledger summary and PDF to {{ str_replace("'", "\'", $customer->name) }} via WhatsApp?",
                icon: "info",
                buttons: true,
                dangerMode: false,
            })
            .then((confirm) => {
                if (confirm) {
                    $.notify({ title:'Processing', message:'Generating PDF and sending WhatsApp...' }, { type:'info' });
                    $.ajax({
                        url: '{{ route("customer_ledger.whatsapp_summary", $customer->id) }}',
                        type: 'GET',
                        success: function(data) {
                            if(data.result == 1) {
                                $.notify({ title:'Success', message:data.message }, { type:'success' });
                                setTimeout(() => { location.reload(); }, 1500);
                            } else {
                                $.notify({ title:'Error', message:data.message }, { type:'danger' });
                            }
                        }
                    });
                }
            });
        }
        function sendWhatsAppBill(btn, id) {
            swal({
                title: "Are you sure?",
                text: "Send this bill notification to the customer via WhatsApp?",
                icon: "info",
                buttons: true,
                dangerMode: false,
            })
            .then((confirm) => {
                if (confirm) {
                    var $btn = $(btn);
                    var originalHtml = $btn.html();
                    $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i>');
                    
                    $.notify({ title:'Processing', message:'Sending WhatsApp notification...' }, { type:'info' });
                    $.ajax({
                        url: '{{ url("admin/bills/whatsapp") }}/' + id,
                        type: 'GET',
                        success: function(data) {
                            if(data.result == 1) {
                                $.notify({ title:'Success', message:data.message }, { type:'success' });
                                $btn.removeClass('btn-outline-success border-success').addClass('btn-success position-relative');
                                $btn.html('<i class="fa fa-whatsapp text-white" style="color: white !important;"></i>' +
                                          '<span class="position-absolute top-0 start-100 translate-middle badge rounded-circle bg-white border border-success p-0" style="width: 14px; height: 14px; margin-top: 2px; margin-left: -2px; display: flex; align-items: center; justify-content: center; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">' +
                                          '<i class="fa fa-check text-success" style="font-size: 8px; color: #28a745 !important;"></i>' +
                                          '</span>');
                                $btn.attr('title', 'Sent Just Now');
                                $btn.prop('disabled', false);
                            } else {
                                $.notify({ title:'Error', message:data.message }, { type:'danger' });
                                $btn.html(originalHtml).prop('disabled', false);
                            }
                        },
                        error: function() {
                            $.notify({ title:'Error', message:'Communication Error' }, { type:'danger' });
                            $btn.html(originalHtml).prop('disabled', false);
                        }
                    });
                }
            });
        }
    </script>
@endsection
