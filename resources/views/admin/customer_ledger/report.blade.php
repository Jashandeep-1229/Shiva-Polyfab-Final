@extends('layouts.admin.app')
@section('title', 'Customer Report')

@section('breadcrumb-items')
<li class="breadcrumb-item">Customer Ledger</li>
@endsection


@section('css')
<style>
    .select2-container .select2-selection--single{
        height:30px !important;
        padding:5px !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow{
        height:12px !important;
    }
</style>
@endsection

@section('content')

<div class="container-fluid">
    <div class="row">
        <div class="col-sm-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3 border-bottom">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <h5 class="mb-0 fw-bold text-dark">Outstanding Report</h5>
                            <p class="mb-0 text-muted small">FIFO based debt aging analysis</p>
                        </div>
                        <div class="d-flex gap-2">
                             <button type="button" class="btn btn-outline-success btn-sm" onclick="exportExcel()">
                                <i class="fa fa-file-excel-o me-1"></i> EXCEL
                             </button>
                             <button type="button" class="btn btn-outline-danger btn-sm" onclick="generatePDF()">
                                <i class="fa fa-file-pdf-o me-1"></i> PDF
                             </button>
                        </div>
                    </div>
                </div>
                <div class="card-body p-4">
                    <div class="dt-controls-wrap mb-4 bg-light p-3 rounded border">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label small aging-label">Select Customer</label>
                                <select id="customer_id" class="form-control form-control-sm js-example-basic-single" onchange="get_datatable()">
                                    <option value="">All Customers</option>
                                    @foreach($customers as $c)
                                        <option value="{{ $c->id }}">{{ $c->name }} ({{ $c->code }})</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small aging-label">Executive</label>
                                <select id="sale_executive_id" class="form-control form-control-sm" onchange="get_datatable()">
                                    <option value="">All Executives</option>
                                    @foreach($executives as $e)
                                        <option value="{{ $e->id }}">{{ $e->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small aging-label">Filter By</label>
                                <select id="filter_by" class="form-control form-control-sm" onchange="get_datatable()">
                                    <option value="">None</option>
                                    <option value="top_amount">Top Amount (Net Balance)</option>
                                    <option value="bad_debt">Bad Debt Customer List</option>
                                    <option value="0-15">0-15 Days Due</option>
                                    <option value="15-30">15-30 Days Due</option>
                                    <option value="30-45">30-45 Days Due</option>
                                    <option value="45+">45+ Days Due</option>
                                    <option value="advance">Show Advance (Credit)</option>
                                    <option value="7_days_followup">7 Days Followup Pending</option>
                                    <option value="less_than_1_lakh">Payment Less Than 1 Lakh</option>
                                    <option value="less_than_50k">Payment Less than 50K</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small aging-label">Balance Filter</label>
                                <select id="balance_type" class="form-control form-control-sm" onchange="get_datatable()">
                                    <option value="active" selected>Show Active Only (Non-Zero)</option>
                                    <option value="all">Show All Customers</option>
                                </select>
                            </div>
                            <div class="col-md-12 text-end mt-2">
                                <button class="btn btn-primary btn-sm px-4 fw-bold" onclick="get_datatable()">
                                    <i class="fa fa-search me-1"></i> GENERATE REPORT
                                </button>
                            </div>
                        </div>
                    </div>

                    <div id="datatable_container">
                        <div class="text-center py-5">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <p class="mt-2 text-muted">Calculating aging data, please wait...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

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
                    <label class="form-label fw-bold small">Next Followup After (Days)</label>
                    <div class="input-group">
                        <input type="number" id="quick_days" class="form-control" value="1" min="1" oninput="calculateFollowupDate(this.value, 'quick_fup_date', 'quick_fup_display')">
                        <span class="input-group-text bg-light text-dark fw-bold small">Days</span>
                    </div>
                    <input type="hidden" name="followup_date_time" id="quick_fup_date">
                    <div class="extra-small text-muted mt-1"><i class="fa fa-clock-o me-1"></i> Scheduled for: <span id="quick_fup_display" class="fw-bold">...</span> </div>
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

<!-- Dynamic Modal Container -->
<div id="dynamic_modal_container"></div>

@section('script')
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js" integrity="sha512-GsLlZN/3F2ErC5ifS5QtgpiJtWd43JWSuIgh7mbzZ8zBps+dvLusV+eNQATqgA/HdeKFVgA5v3S/cIrLF7QnIg==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<style>
    .text-indigo { color: #4338ca !important; }
    .bg-indigo-subtle { background-color: #f5f3ff !important; }
    .bg-pink-subtle { background-color: #fff1f2 !important; }
    .extra-small { font-size: 10px; }
    .fw-600 { font-weight: 600; }
    .aging-label { color: #1e293b !important; font-weight: 700 !important; }
</style>
<script>
    $(document).ready(function(){
        $('.js-example-basic-single').select2();
        get_datatable();

        $(document).on('click', '.pages a', function(e){
            e.preventDefault();
            var page = $(this).attr('href').split('page=')[1];
            get_datatable(page);
        });
    });

    function get_datatable(page = 1) {
        var customer_id = $('#customer_id').val();
        var sale_executive_id = $('#sale_executive_id').val();
        var filter_by = $('#filter_by').val();
        var balance_type = $('#balance_type').val();
        
        $('#datatable_container').html('<div class="text-center py-5"><div class="spinner-border text-primary"></div><p class="mt-2 text-muted">Calculating aging data...</p></div>');

        $.ajax({
            url: '{{ route("customer_ledger.report_datatable") }}',
            data: { 
                customer_id: customer_id, 
                sale_executive_id: sale_executive_id, 
                filter_by: filter_by,
                balance_type: balance_type,
                page: page
            },
            type: 'GET',
            success: function(data){
                $('#datatable_container').html(data);
            },
            error: function(){
                $('#datatable_container').html('<div class="alert alert-danger">Error loading report data. Please try again.</div>');
            }
        });
    }

    function exportExcel() {
        var table = document.getElementById("aging-report-table");
        if(!table) return;
        
        var clone = table.cloneNode(true);
        var buttons = clone.querySelectorAll('button, a.collapse-toggle');
        buttons.forEach(btn => btn.remove());
        
        var html = clone.outerHTML;
        var context = `<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">
            <head><!--[if gte mso 9]><xml><x` + `:ExcelWorkbook><x` + `:ExcelWorksheets><x` + `:ExcelWorksheet><x` + `:Name>Followup Report</x` + `:Name><x` + `:WorksheetOptions><x` + `:DisplayGridlines/></x` + `:WorksheetOptions></x` + `:ExcelWorksheet></x` + `:ExcelWorksheets></x` + `:ExcelWorkbook></xml><![endif]--></head>
            <body>${html}</body></html>`;

        var link = document.createElement("a");
        link.href = 'data:application/vnd.ms-excel;base64,' + window.btoa(unescape(encodeURIComponent(context)));
        link.download = "Customer_Outstanding_Report.xls";
        link.click();
    }

    function generatePDF() {
        var table = document.getElementById("aging-report-table");
        if(!table) return;
        
        var btn = document.querySelector('button[onclick="generatePDF()"]');
        var originalText = btn.innerHTML;
        btn.innerHTML = '<i class="fa fa-spinner fa-spin me-1"></i> Generating...';
        btn.disabled = true;

        var clone = table.cloneNode(true);
        var buttons = clone.querySelectorAll('button, a.collapse-toggle');
        buttons.forEach(b => b.remove());

        // Optimize for ultra-compact printable view
        var style = document.createElement('style');
        style.innerHTML = `
            table { width: 100%; border-collapse: collapse; font-family: 'Arial', sans-serif !important; table-layout: fixed; }
            th, td { padding: 2px 4px !important; border: 0.5px solid #94a3b8 !important; font-size: 8.5px !important; line-height: 1.1 !important; word-wrap: break-word; }
            th { background-color: #f1f5f9 !important; color: #0f172a !important; font-weight: 700 !important; }
            .remarks-container { padding: 2px 5px !important; margin: 0 !important; }
            .remark-item { padding: 1px 0 !important; border-bottom: 0.25px dotted #cbd5e1 !important; }
            .badge { padding: 0 3px !important; border-radius: 2px !important; font-size: 7.5px !important; display: inline-block; border: 0.5px solid #cbd5e1; }
            .text-truncate { overflow: visible !important; white-space: normal !important; }
            .text-muted-uppercase { display: none !important; } /* Hide "Recent Remarks" text to save space */
        `;

        var wrapper = document.createElement('div');
        wrapper.style.padding = '10px';
        wrapper.style.backgroundColor = '#ffffff';

        // Branded Slim Header
        var header = document.createElement('div');
        header.innerHTML = `
            <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1.5px solid #000; padding-bottom: 5px; margin-bottom: 10px;">
                <h1 style="margin: 0; color: #000; font-size: 16px; font-weight: 900;">SHIVA POLYFAB - OUTSTANDING REPORT</h1>
                <div style="text-align: right; color: #334155; font-size: 8.5px;">
                    Report Date: <b>${new Date().toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' })}</b>
                </div>
            </div>
        `;

        wrapper.appendChild(header);
        wrapper.appendChild(style);
        wrapper.appendChild(clone);

        var opt = {
            margin:       0.25,
            filename:     'Shiva_Polyfab_Outstanding_Report.pdf',
            image:        { type: 'jpeg', quality: 0.98 },
            html2canvas:  { scale: 3, useCORS: true, logging: false },
            jsPDF:        { unit: 'in', format: 'letter', orientation: 'landscape' }
        };

        html2pdf().set(opt).from(wrapper).save().then(function() {
            btn.innerHTML = originalText;
            btn.disabled = false;
        });
    }

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

    function openFollowupModal(id, name) {
        $('#followup_customer_id').val(id);
        $('#followup_customer_name').text(name);
        calculateFollowupDate(1, 'quick_fup_date', 'quick_fup_display');
        $('#quickFollowupModal').modal('show');
    }

    function continueFollowupModal(id, name, can_close) {
        $.ajax({
            url: '{{ url("admin/ledger_followups/history") }}/' + id,
            success: function(html){
                $('#dynamic_modal_container').html(html);
                $('#historyModal').modal('show');
                calculateFollowupDate(1, 'update_fup_date', 'update_fup_display');
            }
        });
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
</script>
@endsection
@endsection
