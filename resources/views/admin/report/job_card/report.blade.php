@extends('layouts.admin.app')

@section('title', 'Roto Order Report')

@section('css')
<style>
    .report-filter-card {
        background: #fff;
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        margin-bottom: 15px;
        padding: 15px;
    }
    .stat-card {
        border: none;
        border-radius: 8px;
        margin-bottom: 10px;
    }
    .stat-card .card-body {
        padding: 0.8rem 1rem;
    }
    .stat-icon {
        width: 35px;
        height: 35px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1rem;
    }
    .bg-soft-primary { background-color: rgba(115, 102, 255, 0.1); color: #7366ff; }
    .bg-soft-warning { background-color: rgba(255, 193, 7, 0.1); color: #ffc107; }
    .bg-soft-success { background-color: rgba(81, 187, 37, 0.1); color: #51bb25; }
    .bg-soft-info { background-color: rgba(0, 184, 212, 0.1); color: #00b8d4; }
    .bg-soft-danger { background-color: rgba(255, 82, 82, 0.1); color: #ff5252; }
    
    .f-14 { font-size: 14px !important; }
    .f-13 { font-size: 13px !important; }
    .f-12 { font-size: 12px !important; }
    .f-10 { font-size: 10px !important; }
    .text-black { color: #000 !important; }
    .fw-600 { font-weight: 600 !important; }

    .timeline-item {
        padding: 10px 15px;
        border-left: 2px solid #e2e8f0;
        position: relative;
        margin-left: 10px;
    }
    .timeline-item::before {
        content: '';
        position: absolute;
        left: -6px;
        top: 15px;
        width: 10px;
        height: 10px;
        border-radius: 50%;
        background: #7366ff;
    }
    .badge-on-time { background-color: #51bb25; color: #fff; }
    .badge-late { background-color: #dc3545; color: #fff; }
    .badge-before-time { background-color: #0d6efd; color: #fff; }
    
    #report_table thead th {
        background-color: #242934 !important;
        color: #ffffff !important;
        padding: 8px 10px !important;
        border: 1px solid #3e444a !important;
        font-weight: 600 !important;
        text-transform: uppercase;
        font-size: 12px;
    }
    #report_table tbody td {
        padding: 6px 10px !important;
        vertical-align: middle;
        color: #111 !important;
        font-weight: 500;
        border: 1px solid #dee2e6;
    }
    /* Fix for empty icons - ensure font-family */
    .fa {
        display: inline-block;
        font: normal normal normal 14px/1 FontAwesome !important;
        font-size: inherit;
        text-rendering: auto;
        -webkit-font-smoothing: antialiased;
    }
    .btn-xs {
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 4px;
        font-size: 14px;
    }
    .btn i {
        color: #fff !important;
    }
    .dt-buttons .btn {
        margin-bottom: 5px;
    }
    .card .card-header {
        padding: 10px 15px !important;
    }
    .select2-container .select2-selection--single{
        height:30px !important;
        padding:5px !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow{
        height:12px !important;
    }
</style>
@endsection

@section('breadcrumb-items')
    <li class="breadcrumb-item">Reports</li>
    <li class="breadcrumb-item active">Roto Order Report</li>
@endsection

@section('content')
<div class="container-fluid f-13">
    <!-- Filter Card -->
    <div class="card report-filter-card">
        <form id="filter_form" class="row g-2">
            <div class="col-md-2">
                <label class="form-label fw-bold mb-0 f-12">From Date</label>
                <input type="date" name="from_date" id="from_date" class="form-control form-control-sm">
            </div>
            <div class="col-md-2">
                <label class="form-label fw-bold mb-0 f-12">To Date</label>
                <input type="date" name="to_date" id="to_date" class="form-control form-control-sm">
            </div>
            <div class="col-md-3">
                <label class="form-label fw-bold mb-0 f-12">Customer/Agent</label>
                <select name="customer_id" id="customer_id" class="form-select form-select-sm select2">
                    <option value="">All Customers</option>
                    @foreach($customers as $customer)
                        <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label fw-bold mb-0 f-12">Status</label>
                <select name="status" id="status" class="form-select form-select-sm">
                    <option value="">All Status</option>
                    <option value="Pending">Pending</option>
                    <option value="Progress">Progress</option>
                    <option value="Account Pending">Account Pending</option>
                    <option value="Completed">Completed</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label fw-bold mb-0 f-12">Process Status</label>
                <select name="process" id="process" class="form-select form-select-sm">
                    <option value="">All Processes</option>
                    @foreach($processes as $p)
                        <option value="{{ $p }}">{{ $p }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3 mt-2">
                <label class="form-label fw-bold mb-0 f-12">Sale Executive</label>
                <select name="executive_id" id="executive_id" class="form-select form-select-sm select2">
                    <option value="">All Executives</option>
                    @foreach($executives as $ex)
                        <option value="{{ $ex->id }}">{{ $ex->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3 mt-2">
                <label class="form-label fw-bold mb-0 f-12">Delivery (Compl.)</label>
                <select name="delivery_filter" id="delivery_filter" class="form-select form-select-sm">
                    <option value="">All Delivery</option>
                    <option value="on_time">On Time</option>
                    <option value="late">Late Delivery</option>
                    <option value="before_time">Before Time</option>
                </select>
            </div>
            <div class="col-md-4 mt-2">
                <label class="form-label fw-bold mb-0 f-12">Search</label>
                <input type="text" name="search" id="search_input" class="form-control form-control-sm" placeholder="Search Job Name...">
            </div>
            <div class="col-md-2 mt-2 d-flex align-items-end">
                <button type="button" onclick="get_report()" class="btn btn-primary btn-sm w-100"><i class="fa fa-filter me-1"></i> Filter</button>
            </div>
        </form>
    </div>

    <!-- Statistics Cards -->
    <div id="stats_container" class="row">
        <div class="col-md-4 col-sm-6">
            <div class="card stat-card shadow-sm border-start border-primary border-4">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <p class="text-muted mb-0 fw-600 f-12">Total Orders</p>
                            <h4 class="mb-0 fw-bold" id="stat_total">0</h4>
                        </div>
                        <div class="stat-icon bg-soft-primary">
                            <i data-feather="package"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4 col-sm-6">
            <div class="card stat-card shadow-sm border-start border-warning border-4">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <p class="text-muted mb-0 fw-600 f-12">Active/Pending</p>
                            <h4 class="mb-0 fw-bold" id="stat_active">0</h4>
                        </div>
                        <div class="stat-icon bg-soft-warning">
                            <i data-feather="clock"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4 col-sm-6">
            <div class="card stat-card shadow-sm border-start border-success border-4">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <p class="text-muted mb-0 fw-600 f-12">Completed</p>
                            <h4 class="mb-0 fw-bold" id="stat_completed">0</h4>
                        </div>
                        <div class="stat-icon bg-soft-success">
                            <i data-feather="check-circle"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Result Table -->
    <div class="card">
        <div class="card-body p-0">
            <div class="dt-ext" id="report_container">
                <div class="loader-box"><div class="loader-37"></div></div>
            </div>
        </div>
    </div>
</div>

<!-- Modals -->
<div class="modal fade" id="report_modal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content" id="report_modal_content"></div>
    </div>
</div>
@endsection

@section('script')
<script src="{{ asset('assets/js/datatable/datatables/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('assets/js/datatable/datatable-extension/dataTables.buttons.min.js') }}"></script>
<script src="{{ asset('assets/js/datatable/datatable-extension/jszip.min.js') }}"></script>
<script src="{{ asset('assets/js/datatable/datatable-extension/buttons.colVis.min.js') }}"></script>
<script src="{{ asset('assets/js/datatable/datatable-extension/pdfmake.min.js') }}"></script>
<script src="{{ asset('assets/js/datatable/datatable-extension/vfs_fonts.js') }}"></script>
<script src="{{ asset('assets/js/datatable/datatable-extension/buttons.bootstrap4.min.js') }}"></script>
<script src="{{ asset('assets/js/datatable/datatable-extension/buttons.html5.min.js') }}"></script>
<script src="{{ asset('assets/js/datatable/datatable-extension/buttons.print.min.js') }}"></script>

<script>
    $(document).ready(function() {
        get_report();
        $('.select2').select2({
            dropdownParent: $('.report-filter-card')
        });
    });

    var reportTable = null;

    function get_report(page = 1) {
        var $container = $('#report_container');
        $container.html('<div class="loader-box"><div class="loader-37"></div></div>');
        
        var formData = $('#filter_form').serialize();
        formData += '&page=' + page;

        $.ajax({
            url: '{{ route("job_card.report_datatable") }}',
            data: formData,
            type: 'GET',
            success: function(data) {
                $container.html(data);
                
                // Update stats
                $('#stat_total').text($('#hidden_total').val());
                $('#stat_active').text($('#hidden_active').val());
                $('#stat_completed').text($('#hidden_completed').val());
                
                if (reportTable) {
                    reportTable.destroy();
                }
                
                reportTable = $('#report_table').DataTable({
                    dom: '{{ auth()->user()->role_as == "Admin" ? "Bfrtip" : "frtip" }}',
                    @if(auth()->user()->role_as == 'Admin')
                    buttons: [
                        {
                            extend: 'excelHtml5',
                            text: '<i class="fa fa-file-excel-o"></i> Excel',
                            className: 'btn btn-success btn-xs me-1',
                            exportOptions: { columns: [0, 1, 2, 3, 4, 5, 6, 7, 8, 9] }
                        },
                        {
                            extend: 'pdfHtml5',
                            text: '<i class="fa fa-file-pdf-o"></i> PDF',
                            className: 'btn btn-danger btn-xs me-1',
                            orientation: 'landscape',
                            pageSize: 'A4',
                            exportOptions: { columns: [0, 1, 2, 3, 4, 5, 6, 7, 8, 9] }
                        },
                        {
                            extend: 'print',
                            text: '<i class="fa fa-print"></i> Print',
                            className: 'btn btn-primary btn-xs',
                            exportOptions: { columns: [0, 1, 2, 3, 4, 5, 6, 7, 8, 9] }
                        }
                    ],
                    @endif
                    paging: false,
                    info: false,
                    ordering: true,
                    searching: false, // Form filters handle searching
                });
                
                feather.replace();
            }
        });
    }

    $(document).on('click', '.pagination a', function(e) {
        e.preventDefault();
        var url = new URL($(this).attr('href'));
        var page = url.searchParams.get('page');
        if (page) {
            get_report(page);
        }
    });

    function viewTimeline(id) {
        $('#report_modal_content').html('<div class="loader-box"><div class="loader-37"></div></div>');
        $('#report_modal').modal('show');
        $.get('{{ route("job_card.view_timeline", ["id" => ":id"]) }}'.replace(':id', id), function(data) {
            $('#report_modal_content').html(data);
            feather.replace();
        });
    }

    function viewBilling(id) {
        $('#report_modal_content').html('<div class="loader-box"><div class="loader-37"></div></div>');
        $('#report_modal').modal('show');
        $.get('{{ route("job_card.view_billing_details", ["id" => ":id"]) }}'.replace(':id', id), function(data) {
            $('#report_modal_content').html(data);
            feather.replace();
        });
    }

    function viewPackingSlips(id) {
        $('#report_modal_content').html('<div class="loader-box"><div class="loader-37"></div></div>');
        $('#report_modal').modal('show');
        $.get('{{ route("job_card.view_packing_details", ["id" => ":id"]) }}'.replace(':id', id), function(data) {
            $('#report_modal_content').html(data);
            feather.replace();
        });
    }
</script>
@endsection
