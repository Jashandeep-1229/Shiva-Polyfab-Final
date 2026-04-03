@extends('layouts.admin.app')
@section('title', 'Machine Wise Report')
@section('breadcrumb-items')
    <li class="breadcrumb-item">Reports</li>
    <li class="breadcrumb-item active">Machine Wise Report</li>
@endsection

@section('css')
<style>
    .bg-light-primary { background-color: rgba(115, 102, 255, 0.08) !important; }
    .bg-light-success { background-color: rgba(40, 167, 69, 0.08) !important; }
    .bg-light-danger { background-color: rgba(220, 53, 69, 0.08) !important; }
    .bg-light-warning { background-color: rgba(255, 193, 7, 0.08) !important; }
    .bg-light-info { background-color: rgba(0, 123, 255, 0.08) !important; }
    
    /* Dark mode support */
    .dark-only .bg-light-primary { background-color: rgba(115, 102, 255, 0.15) !important; }
    .dark-only .bg-light-success { background-color: rgba(40, 167, 69, 0.15) !important; }
    .dark-only .bg-light-danger { background-color: rgba(220, 53, 69, 0.15) !important; }
    .dark-only .bg-light-warning { background-color: rgba(255, 193, 7, 0.15) !important; }
    .dark-only .bg-light-info { background-color: rgba(0, 123, 255, 0.15) !important; }

    .f-w-700 { font-weight: 700; }
    .shadow-none { box-shadow: none !important; }
    .text-dark-theme { color: #242934 !important; }
    .dark-only .text-dark-theme { color: #ffffff !important; }
    
    @media print {
        .back-btn, .sidebar-wrapper, .page-header, .card-header, #filter_form, .footer { display: none !important; }
        .page-body { margin-top: 0 !important; margin-left: 0 !important; }
        .card { border: none !important; }
    }
</style>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header pb-3 border-bottom d-flex justify-content-between align-items-center">
                    <h5 class="text-dark-theme">Machine Wise Report Filters</h5>
                </div>
                <div class="card-body">
                    <form id="filter_form" class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label fw-bold text-dark-theme">Select Process</label>
                            <select name="process" id="process_select" class="form-select form-select-sm" required>
                                <option value="">Select Process</option>
                                @foreach($processes as $val => $name)
                                    <option value="{{ $val }}">{{ $name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold text-dark-theme">Select Machine</label>
                            <select name="machine_id" id="machine_select" class="form-select form-select-sm">
                                <option value="">Select Process First</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold text-dark-theme">From Date</label>
                            <input type="date" name="from_date" class="form-control form-control-sm" value="{{ date('Y-m-d') }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold text-dark-theme">To Date</label>
                            <input type="date" name="to_date" class="form-control form-control-sm" value="{{ date('Y-m-d') }}">
                        </div>
                        <div class="col-12 text-center mt-3">
                            <button type="submit" class="btn btn-primary btn-sm px-5"><i class="fa fa-search me-2"></i>Get Report</button>
                            <button type="button" class="btn btn-secondary btn-sm px-4 ms-2" onclick="window.print()"><i class="fa fa-print me-2"></i>Print</button>
                        </div>
                    </form>
                </div>
            </div>
            
            <div class="card d-none" id="report_card">
                <div class="card-body">
                    <div id="report_html"></div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
    $('#process_select').on('change', function() {
        var process = $(this).val();
        if(process) {
            $.get('{{ route("report.machine_wise.get_machines") }}', {process: process}, function(data) {
                $('#machine_select').html(data);
            });
        } else {
            $('#machine_select').html('<option value="">Select Process First</option>');
        }
    });

    $('#filter_form').on('submit', function(e) {
        e.preventDefault();
        $('#report_card').removeClass('d-none');
        $('#report_html').html('<div class="loader-box"><div class="loader-37"></div></div>');
        
        $.get('{{ route("report.machine_wise.report_data") }}', $(this).serialize(), function(data) {
            $('#report_html').html(data);
            // Scroll to report
            $('html, body').animate({
                scrollTop: $("#report_card").offset().top - 50
            }, 500);
        });
    });
</script>
@endsection
