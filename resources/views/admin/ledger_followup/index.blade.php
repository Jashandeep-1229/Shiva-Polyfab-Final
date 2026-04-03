@extends('layouts.admin.app')
@section('title', 'Ledger Followups')
@section('css')
<style>
    .followup-card { border-radius: 15px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); border: none; }
    .status-badge-continue { background: #fff7ed; color: #9a3412; border: 1px solid #fdba74; padding: 2px 8px; border-radius: 5px; font-weight: 600; font-size: 11px; }
    .status-badge-closed { background: #f0fdf4; color: #166534; border: 1px solid #86efac; padding: 2px 8px; border-radius: 5px; font-weight: 600; font-size: 11px; }
    .history-dot { height: 10px; width: 10px; background-color: #3b82f6; border-radius: 50%; display: inline-block; margin-right: 10px; }
    .history-line { border-left: 2px dashed #e2e8f0; margin-left: 4px; padding-left: 20px; padding-bottom: 15px; }
     .select2-container .select2-selection--single{
        height:30px !important;
        padding:5px !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow{
        height:12px !important;
    }
    .dt-controls-wrap { display: flex; flex-wrap: wrap; gap: 20px; align-items: flex-end; padding: 15px 0; border-bottom: 1px solid #f1f5f9; margin-bottom: 15px; }
    .dt-controls-item { display: flex; flex-direction: column; gap: 5px; }
    .dt-controls-item label { font-weight: 700; font-size: 12px; color: #64748b; text-uppercase: uppercase; margin-bottom: 0; }
</style>
@endsection

@section('breadcrumb-items')
    <li class="breadcrumb-item">Account Department</li>
    <li class="breadcrumb-item active">Ledger Followup</li>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card followup-card mb-4">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-4">
                        <div>
                            <h4 class="mb-1 fw-bold text-primary">Ledger Followup</h4>
                            <p class="mb-0 text-muted small text-uppercase fw-bold">Track payment reminders & responses</p>
                        </div>
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createFollowupModal">
                            <i class="fa fa-plus-circle me-1"></i> New Followup
                        </button>
                    </div>



                        <div class="dt-controls-wrap px-2">
                            <input type="hidden" id="filter_type_val" value="{{ $active_filter }}">
                            <div class="dt-controls-item" style="flex: 1; min-width: 200px;">
                                <label>Customer Selection</label>
                                <select id="customer_filter" class="form-control form-control-sm js-example-basic-single" onchange="get_datatable()">
                                    <option value="">All Customers</option>
                                    @foreach($customers as $customer)
                                        <option value="{{ $customer->id }}">{{ $customer->name }} ({{ $customer->code }})</option>
                                    @endforeach
                                </select>
                            </div>

                            @if(count($executives) > 0)
                            <div class="dt-controls-item" style="flex: 1; min-width: 200px;">
                                <label>Executive Selection</label>
                                <select id="executive_filter" class="form-control form-control-sm" onchange="get_datatable()">
                                    <option value="">All Executives</option>
                                    @foreach($executives as $ex)
                                        <option value="{{ $ex->id }}">{{ $ex->name }} ({{ $ex->role_as }})</option>
                                    @endforeach
                                </select>
                            </div>
                            @endif

                            <div class="dt-controls-item">
                                <label>Status</label>
                                <select id="status_filter" class="form-control form-control-sm" onchange="get_datatable()" style="width: 140px;">
                                    <option value="">All Status</option>
                                    <option value="Pending">Pending</option>
                                    <option value="Closed">Closed</option>
                                </select>
                            </div>



                            <div id="date_filters_container" style="{{ request('filter') == 'pending' ? '' : 'display: none;' }}">
                                <div class="d-flex gap-3">
                                    <div class="dt-controls-item">
                                        <label>From Date</label>
                                        <input type="date" id="from_date_filter" class="form-control form-control-sm" onchange="get_datatable()">
                                    </div>
                                    <div class="dt-controls-item">
                                        <label>To Date</label>
                                        <input type="date" id="to_date_filter" class="form-control form-control-sm" value="{{ date('Y-m-d') }}" onchange="get_datatable()">
                                    </div>
                                </div>
                            </div>

                            <div class="dt-controls-item" style="flex: 1; min-width: 200px;">
                                <label>Quick Search</label>
                                <input type="search" id="basic-2_search" class="form-control form-control-sm" placeholder="Subject, Remarks...">
                            </div>
                        </div>

                    <div id="get_datatable">
                        <div class="loader-box"><div class="loader-37"></div></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Create Modal -->
<div class="modal fade" id="createFollowupModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form id="create_followup_form" class="modal-content border-0 shadow-lg">
            @csrf
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title fw-bold">Register New Followup</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="mb-3">
                    <label class="form-label fw-bold small">Select Customer</label>
                    <select name="customer_id" class="form-control js-example-basic-single" required>
                        <option value="">-- Choose Customer --</option>
                        @foreach($customers as $customer)
                            <option value="{{ $customer->id }}">{{ $customer->name }} ({{ $customer->code }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold small">Next Followup After (Days)</label>
                    <div class="input-group">
                        <input type="number" id="create_days" class="form-control" value="1" min="1" oninput="calculateFollowupDate(this.value, 'create_fup_date', 'create_fup_display')">
                        <span class="input-group-text bg-light text-dark fw-bold small">Days</span>
                    </div>
                    <input type="hidden" name="followup_date_time" id="create_fup_date">
                    <div class="extra-small text-muted mt-1"><i class="fa fa-clock-o me-1"></i> Scheduled for: <span id="create_fup_display" class="fw-bold">...</span> </div>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold small">Subject</label>
                    <input type="text" name="subject" class="form-control" placeholder="Payment reminder for Invoice #..." required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold small">Remarks</label>
                    <textarea name="remarks" class="form-control" rows="3" placeholder="What was the response?"></textarea>
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary px-4 fw-bold">SAVE FOLLOWUP</button>
            </div>
        </form>
    </div>
</div>

<div id="dynamic_modal_container"></div>
@endsection

@section('script')
<script>
    $(document).ready(function(){
        $('.js-example-basic-single').select2({ width: '100%', dropdownParent: $('#createFollowupModal') });
        get_datatable();
        
        // Initialize date for create modal
        $('#createFollowupModal').on('show.bs.modal', function() {
            calculateFollowupDate(1, 'create_fup_date', 'create_fup_display');
        });
    });

    function calculateFollowupDate(days, targetId, displayId) {
        if(!days || days < 0) days = 0;
        let date = new Date();
        date.setDate(date.getDate() + parseInt(days));
        
        let year = date.getFullYear();
        let month = String(date.getMonth() + 1).padStart(2, '0');
        let day = String(date.getDate()).padStart(2, '0');
        let formatted = `${year}-${month}-${day}T12:00`;
        
        $(`#${targetId}`).val(formatted);
        
        const options = { day: '2-digit', month: 'short', year: 'numeric' };
        $(`#${displayId}`).text(date.toLocaleDateString('en-GB', options) + ' 12:00 PM');
    }

    function setFilterType(type) {
        $('#filter_type_val').val(type);
        if(type == 'today') {
            $('#status_filter').val('Continue');
        } else if(type == 'pending') {
            $('#status_filter').val('Continue');
        }
        get_datatable();
    }

    function get_datatable(page){
        var search = $('#basic-2_search').val();
        var customer_id = $('#customer_filter').val();
        var executive_id = $('#executive_filter').length ? $('#executive_filter').val() : '';
        var status = $('#status_filter').val();
        var from_date = $('#from_date_filter').val();
        var to_date = $('#to_date_filter').val();
        var filter_type = '{{ request("filter") }}';
        var page = page ?? 1;

        $('#get_datatable').html('<div class="loader-box"><div class="loader-37"></div></div>');
        
        $.ajax({
            url: '{{ route("ledger_followup.datatable") }}',
            data: { 
                page: page, 
                search: search, 
                customer_id: customer_id, 
                executive_id: executive_id, 
                status: status, 
                from_date: from_date, 
                to_date: to_date, 
                filter_type: filter_type 
            },
            type: 'GET',
            success: function(data){
                $('#get_datatable').html(data);
            }
        });
    }

    $('#create_followup_form').submit(function(e){
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
                    $('#createFollowupModal').modal('hide');
                    $('#create_followup_form')[0].reset();
                    get_datatable();
                }
                $btn.prop('disabled', false).html('SAVE FOLLOWUP');
            },
            error: function(){
                $.notify({title:'Error', message:'Something went wrong'}, {type:'danger'});
                $btn.prop('disabled', false).html('SAVE FOLLOWUP');
            }
        });
    });

    function viewHistory(id) {
        $.ajax({
            url: '{{ url("admin/ledger_followups/history") }}/' + id,
            success: function(html){
                $('#dynamic_modal_container').html(html);
                $('#historyModal').modal('show');
            }
        });
    }

    $(document).on('click', '.pages a', function(e){
        e.preventDefault();
        get_datatable($(this).attr('href').split('page=')[1]);
    });
</script>
@endsection
