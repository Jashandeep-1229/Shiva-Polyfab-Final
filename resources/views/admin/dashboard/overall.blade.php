@extends('layouts.admin.app')

@section('title', 'Overall Factory Dashboard')

@section('css')
<style>
    .factory-card {
        border-radius: 15px;
        transition: transform 0.2s;
        border: none;
        box-shadow: 0 4px 20px rgba(0,0,0,0.05);
    }
    .factory-card:hover {
        transform: translateY(-5px);
    }
    .status-badge {
        padding: 5px 12px;
        border-radius: 50px;
        font-weight: 600;
        font-size: 0.75rem;
    }
    .bg-light-primary { background: #e0dbff; color: #4b449c !important; }
    .bg-light-success { background: #d2f9e4; color: #1e7e34 !important; }
    .bg-light-warning { background: #fff4d5; color: #856404 !important; }
    .bg-light-danger { background: #ffe5e7; color: #721c24 !important; }
    .bg-light-info { background: #e1f5fe; color: #01579b !important; }
    
    .limit-input {
        max-width: 80px;
        text-align: center;
        border-radius: 8px;
        border: 1px solid #ddd;
        padding: 4px;
    }
    .progress-bar {
        border-radius: 10px;
    }
    .table thead th {
        background-color: #f8f9fa;
        text-transform: uppercase;
        font-size: 0.7rem;
        letter-spacing: 0.5px;
    }

    .modal-95 {
        max-width: 95%;
        margin: 1.75rem auto;
    }
    .modal-content {
        border-radius: 15px;
        border: none;
        box-shadow: 0 10px 50px rgba(0,0,0,0.15);
    }
    .modal-header {
        background: #f8f9fa;
        border-bottom: 2px solid #eee;
        border-radius: 15px 15px 0 0;
        padding: 20px 30px;
    }
    .modal-title {
        font-size: 1.4rem;
        letter-spacing: -0.5px;
    }
    .modal-body {
        max-height: 85vh;
        overflow-y: auto;
    }
    .report-table thead th {
        position: sticky;
        top: 0;
        z-index: 10;
        background: #ffffff;
        border-bottom: 2px solid #000;
        padding: 18px 12px;
        font-weight: 800;
        font-size: 0.85rem;
        color: #111;
        text-transform: uppercase;
        letter-spacing: 1px;
    }
    .report-table tbody td {
        padding: 15px 12px;
        font-size: 0.95rem;
        color: #333;
        border-bottom: 1px solid #f0f0f0;
    }
    .report-table tbody tr:hover {
        background-color: #f8faff !important;
    }
    .jc-highlight {
        font-size: 1.05rem;
        font-weight: 900;
        color: #007bff;
        letter-spacing: 0.5px;
    }
    .job-name-highlight {
        font-weight: 700;
        color: #222;
        font-size: 0.95rem;
    }
    /* Hold card pulse animation */
    @keyframes holdCardPulse {
        0%, 100% { box-shadow: 0 4px 20px rgba(239,68,68,0.1); }
        50%       { box-shadow: 0 4px 30px rgba(239,68,68,0.35); }
    }
    /* Hold row in modal */
    .hold-modal-row {
        border-left: 4px solid #ef4444 !important;
        background: #fff9f9 !important;
    }
    .hold-modal-row:hover {
        background: #fee2e2 !important;
    }
    #holdDetailModal .modal-header {
        background: linear-gradient(135deg, #dc2626, #991b1b);
        border-radius: 15px 15px 0 0;
    }
</style>
@endsection

@section('breadcrumb-items')
    <li class="breadcrumb-item">Admin</li>
    <li class="breadcrumb-item active">Overall Dashboard</li>
@endsection

@section('content')
<div class="container-fluid">
    <!-- Filter Bar -->
    <div class="row">
        <div class="col-12 mb-4">
            <div class="card factory-card">
                <div class="card-body p-3">
                    <form action="{{ route('admin.dashboard.overall') }}" method="GET" class="row g-3 align-items-end justify-content-center" id="filterForm">
                        <div class="col-md-3">
                            <label class="form-label small fw-bold text-muted mb-1">From Date</label>
                            <input type="date" name="from_date" value="{{ $from_date->format('Y-m-d') }}" class="form-control form-control-sm">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-bold text-muted mb-1">To Date</label>
                            <input type="date" name="to_date" value="{{ $to_date->format('Y-m-d') }}" class="form-control form-control-sm">
                        </div>
                        <input type="hidden" name="filter_stock_by" id="form_stock_by" value="{{ request('filter_stock_by', 'All') }}">
                        <input type="hidden" name="filter_stock_status" id="form_stock_status" value="{{ request('filter_stock_status', 'All') }}">
                        <div class="col-md-2">
                             <button type="submit" class="btn btn-primary btn-sm w-100 fw-bold" id="generate_report_btn">
                                Generate Report
                             </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- AJAX Data Container -->
    <div id="dashboard_data">
        @include('admin.dashboard.overall_data')
    </div>
</div>

<!-- Drill-down Modal -->
<div class="modal fade" id="drillDownModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-95">
        <div class="modal-content shadow-lg">
            <div class="modal-header border-0 p-4">
                <h4 class="modal-title fw-bold text-dark" id="detailTitle">Details</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle row-border report-table mb-0" id="detailTable">
                        <thead id="detailTableHeader">
                        </thead>
                        <tbody id="detailTableBody">
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer border-0 p-3 bg-light">
                <button type="button" class="btn btn-secondary btn-sm fw-bold px-4" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- ON HOLD Detail Modal -->
<div class="modal fade" id="holdDetailModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-95">
        <div class="modal-content shadow-lg" style="border-radius:15px;border:none;">
            <div class="modal-header p-4" style="background:linear-gradient(135deg,#dc2626,#991b1b);border-radius:15px 15px 0 0;">
                <div>
                    <h4 class="modal-title fw-bold text-white mb-0">
                        <i class="fa fa-lock me-2"></i> Orders Currently ON HOLD
                    </h4>
                    <small class="text-white opacity-75">These orders cannot proceed to next process until unheld</small>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0" style="max-height:85vh;overflow-y:auto;">
                <div class="table-responsive">
                    <table class="table table-hover align-middle report-table mb-0" id="holdDetailTable">
                        <thead style="position:sticky;top:0;z-index:10;background:#fff;">
                            <tr style="border-bottom:2px solid #ef4444;">
                                <th class="ps-3" style="font-size:0.75rem;color:#dc2626;">#</th>
                                <th style="font-size:0.75rem;color:#dc2626;">ORDER NO</th>
                                <th style="font-size:0.75rem;color:#dc2626;">JOB NAME</th>
                                <th style="font-size:0.75rem;color:#dc2626;">CUSTOMER</th>
                                <th style="font-size:0.75rem;color:#dc2626;">CURRENT STAGE</th>
                                <th style="font-size:0.75rem;color:#dc2626;">HOLD REASON</th>
                                <th style="font-size:0.75rem;color:#dc2626;">NOTES</th>
                                <th style="font-size:0.75rem;color:#dc2626;">HELD BY</th>
                                <th style="font-size:0.75rem;color:#dc2626;">HELD SINCE</th>
                            </tr>
                        </thead>
                        <tbody id="holdDetailTableBody">
                            <tr><td colspan="9" class="text-center p-5"><div class="spinner-border text-danger"></div></td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer border-0 p-3" style="background:#fff5f5;border-radius:0 0 15px 15px;">
                <span class="text-danger small fw-bold me-auto">
                    <i class="fa fa-info-circle me-1"></i> Go to Order Process page to Unhold these orders
                </span>
                <button type="button" class="btn btn-outline-danger btn-sm fw-bold px-4" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
</div>

<!-- Hold Reason Sub-Modal (stacks on top of drillDownModal) -->
<div class="modal fade" id="holdReasonSubModal" tabindex="-1" aria-hidden="true" style="z-index: 1060;">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 480px;">
        <div class="modal-content" style="border-radius:16px;border:none;overflow:hidden;">
            <div class="modal-header p-4" style="background:linear-gradient(135deg,#dc2626,#991b1b);">
                <div>
                    <h5 class="modal-title fw-bold text-white mb-0">
                        <i class="fa fa-lock me-2"></i> Order On Hold
                    </h5>
                    <small class="text-white opacity-75" id="holdSub_jcNo"></small>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="d-flex flex-column gap-3">
                    <div class="p-3 rounded" style="background:#fff5f5;border-left:4px solid #ef4444;">
                        <div class="small text-muted fw-bold mb-1">HOLD REASON</div>
                        <div class="fw-bold text-dark" id="holdSub_reason" style="font-size:1.05rem;">—</div>
                    </div>
                    <div class="p-3 rounded" style="background:#f8fafc;border-left:4px solid #94a3b8;">
                        <div class="small text-muted fw-bold mb-1">NOTES</div>
                        <div class="text-dark" id="holdSub_notes">—</div>
                    </div>
                    <div class="row g-2">
                        <div class="col-6">
                            <div class="p-3 rounded" style="background:#f0fdf4;border-left:3px solid #22c55e;">
                                <div class="small text-muted fw-bold mb-1">HELD BY</div>
                                <div class="fw-bold text-dark small" id="holdSub_heldBy">—</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-3 rounded" style="background:#fff7ed;border-left:3px solid #f97316;">
                                <div class="small text-muted fw-bold mb-1">HELD SINCE</div>
                                <div class="fw-bold text-dark small" id="holdSub_heldAt">—</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer p-3" style="background:#fff5f5;">
                <span class="text-danger small fw-bold me-auto"><i class="fa fa-info-circle me-1"></i> Go to Order Process to unhold</span>
                <button type="button" class="btn btn-outline-danger btn-sm fw-bold px-4" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

@endsection

@section('script')
<script>
    $(document).ready(function() {
        if(typeof feather !== 'undefined') {
            feather.replace();
        }

        // Real-time AJAX refresh on Generate or Date Change
        $('#filterForm').on('submit', function(e) {
            e.preventDefault();
            refreshDashboard();
        });

        $('input[name="from_date"], input[name="to_date"]').on('change', function() {
            refreshDashboard();
        });

    }); // Close document.ready

    window.refreshDashboard = function() {
            let btn = $('#generate_report_btn');
            let originalText = btn.html();
            btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Loading...');
            
            // Sync before serializing
            if($('select[name="filter_stock_by"]').length > 0) {
                $('#form_stock_by').val($('select[name="filter_stock_by"]').val());
                $('#form_stock_status').val($('select[name="filter_stock_status"]').val());
            }
            
            let formData = $('#filterForm').serialize();
            
            $.ajax({
                url: "{{ route('admin.dashboard.overall') }}",
                type: 'GET',
                data: formData + '&is_ajax=1',
                success: function(response) {
                    $('#dashboard_data').html(response);
                    if(typeof feather !== 'undefined') {
                        feather.replace();
                    }
                    btn.prop('disabled', false).html(originalText);
                },
                error: function() {
                    btn.prop('disabled', false).html(originalText);
                    alert('Error updating dashboard. Please try again.');
                }
            });
        }

    function showLateDetail(type) {
        let title = type ? `Late Jobs - ${type}` : "All Late Jobs";
        $('#detailTableHeader').html(`
            <tr>
                <th>Job Card #</th>
                <th>Job Name</th>
                <th>Customer Name</th>
                <th>Current Status</th>
                <th class="text-center">Days Overdue</th>
            </tr>
        `);
        $('#detailTableBody').html('<tr><td colspan="5" class="text-center p-5"><div class="spinner-border text-primary"></div></td></tr>');
        $('#drillDownModal').modal('show');

        let params = {
            from_date: $('input[name="from_date"]').val(),
            to_date: $('input[name="to_date"]').val(),
            type: type
        };

        $.get("{{ route('admin.dashboard.late_detail') }}", params, function(data) {
            let html = '';
            if(data.length == 0) {
                html = '<tr><td colspan="5" class="text-center text-muted p-5">No late jobs found for this selection.</td></tr>';
            } else {
                data.forEach(item => {
                    let isHold   = item.is_hold == 1;
                    let rowClass = isHold ? 'table-danger' : '';
                    let tdStyle  = isHold ? 'background-color: #fee2e2 !important;' : '';
                    let trBorder = isHold ? 'border-left: 4px solid #ef4444 !important;' : '';
                    
                    let holdBadge = '';
                    if (isHold) {
                        // Encode all hold data safely for onclick
                        let enc = encodeURIComponent(JSON.stringify({
                            jc:     item.job_card_no,
                            reason: item.hold_reason  || 'No reason specified',
                            notes:  item.hold_notes   || '—',
                            by:     item.held_by      || 'N/A',
                            at:     item.held_at      || 'N/A'
                        }));
                        holdBadge = `<i class="fa fa-lock text-danger ms-2 pointer" style="font-size:1.1rem; vertical-align:middle;" title="Order is On Hold - Click to view reason" onclick="showHoldReasonSubModal(decodeURIComponent('${enc}'))"></i>`;
                    }
                    html += `
                        <tr class="${rowClass}" style="${trBorder}">
                            <td class="jc-highlight" style="white-space:nowrap; ${tdStyle} ${isHold ? 'color:#dc2626;' : ''}">
                                ${item.job_card_no}${holdBadge}
                            </td>
                            <td class="job-name-highlight" style="${tdStyle}">${item.job_card_name}</td>
                            <td class="fw-bold" style="${tdStyle}">${item.customer}</td>
                            <td style="${tdStyle}">
                                <span class="badge text-dark p-2" style="font-size: 0.8rem; background-color: ${isHold ? '#fca5a5 !important' : '#e0f2fe !important'};">
                                    ${item.process}
                                </span>
                            </td>
                            <td class="text-center" style="${tdStyle}"><span class="badge bg-danger p-2" style="font-size: 0.9rem;">+${item.delay} Days</span></td>
                        </tr>
                    `;
                });
            }
            $('#detailTableBody').html(html);
        });
    }

    function showHoldReasonSubModal(jsonStr) {
        try {
            var d = JSON.parse(jsonStr);
            $('#holdSub_jcNo').text('Order: ' + (d.jc || ''));
            $('#holdSub_reason').text(d.reason || '—');
            $('#holdSub_notes').text(d.notes  || '—');
            $('#holdSub_heldBy').text(d.by    || '—');
            $('#holdSub_heldAt').text(d.at    || '—');
            $('#holdReasonSubModal').modal('show');
        } catch(e) { console.error('Hold parse error', e); }
    }

    function showOverdueDispatches() {
        $('#detailTitle').text("Global Overdue Dispatches (All Time)");
        $('#detailTableHeader').html(`
            <tr>
                <th>Job Card #</th>
                <th>Job Name</th>
                <th>Order Date</th>
                <th>Delivery Target</th>
                <th>Customer</th>
                <th class="text-center">Days Late</th>
            </tr>
        `);
        $('#detailTableBody').html('<tr><td colspan="6" class="text-center p-5"><div class="spinner-border text-primary"></div></td></tr>');
        $('#drillDownModal').modal('show');

        $.get("{{ route('admin.dashboard.overdue_dispatches_detail') }}", function(data) {
            let html = '';
            if(data.length == 0) {
                html = '<tr><td colspan="6" class="text-center text-muted p-5">No overdue dispatches found.</td></tr>';
            } else {
                data.forEach(item => {
                    html += `
                        <tr>
                            <td class="jc-highlight">${item.job_card_no}</td>
                            <td class="job-name-highlight">${item.job_card_name}</td>
                            <td>${item.order_date}</td>
                            <td class="fw-bold text-dark">${item.delivery_date}</td>
                            <td class="fw-bold">${item.customer}</td>
                            <td class="text-center"><span class="badge bg-danger p-2" style="font-size: 0.9rem;">+${item.days_late} Days</span></td>
                        </tr>
                    `;
                });
            }
            $('#detailTableBody').html(html);
        });
    }

    function showAccountPending() {
        $('#detailTitle').text("Account Pending Job Cards");
        $('#detailTableHeader').html(`
            <tr>
                <th>Job Card #</th>
                <th>Job Name</th>
                <th>Order Date</th>
                <th>Customer Name</th>
                <th>Process Location</th>
            </tr>
        `);
        $('#detailTableBody').html('<tr><td colspan="5" class="text-center p-5"><div class="spinner-border text-primary"></div></td></tr>');
        $('#drillDownModal').modal('show');

        $.get("{{ route('admin.dashboard.account_pending_detail') }}", function(data) {
            let html = '';
            if(data.length == 0) {
                html = '<tr><td colspan="5" class="text-center text-muted p-5">No account pending found.</td></tr>';
            } else {
                data.forEach(item => {
                    html += `
                        <tr>
                            <td class="jc-highlight">${item.job_card_no}</td>
                            <td class="job-name-highlight">${item.job_card_name}</td>
                            <td>${item.order_date}</td>
                            <td class="fw-bold">${item.customer}</td>
                            <td><span class="badge bg-light-warning text-dark p-2" style="font-size: 0.8rem;">${item.process}</span></td>
                        </tr>
                    `;
                });
            }
            $('#detailTableBody').html(html);
        });
    }

    function showMachineDetail(id, name) {
        $('#detailTitle').text(`Production Logistics - ${name}`);
        $('#detailTableHeader').html(`
            <tr>
                <th>Log Date</th>
                <th>Job Card #</th>
                <th>Customer</th>
                <th class="text-end">Production</th>
                <th class="text-end text-danger">Wastage</th>
                <th class="text-center">Blockage</th>
                <th>Reason</th>
            </tr>
        `);
        $('#detailTableBody').html('<tr><td colspan="7" class="text-center p-5"><div class="spinner-border text-primary"></div></td></tr>');
        $('#drillDownModal').modal('show');

        let params = {
            machine_id: id,
            from_date: $('input[name="from_date"]').val(),
            to_date: $('input[name="to_date"]').val()
        };

        $.get("{{ route('admin.dashboard.machine_detail') }}", params, function(data) {
            let html = '';
            if(data.length == 0) {
                html = '<tr><td colspan="7" class="text-center text-muted p-5">No production data found for this machine.</td></tr>';
            } else {
                data.forEach(item => {
                    html += `
                        <tr>
                            <td class="fw-bold">${item.date}</td>
                            <td class="jc-highlight">${item.job_card_no}</td>
                            <td class="fw-bold">${item.customer}</td>
                            <td class="text-end fw-bold text-dark">${parseFloat(item.production).toLocaleString()}</td>
                            <td class="text-end text-danger fw-bold">${parseFloat(item.wastage).toLocaleString()}</td>
                            <td class="text-center"><span class="badge bg-light-danger text-danger fw-bold p-2">${item.blockage} min</span></td>
                            <td><span class="badge bg-light text-dark border p-2">${item.reason}</span></td>
                        </tr>
                    `;
                });
            }
            $('#detailTableBody').html(html);
        });
    }

    function showCylinderAgentDetail(agentId, agentName, limit) {
        $('#detailTitle').text("Late Deliveries - " + agentName);
        $('#detailTableHeader').html(`
            <tr>
                <th class="text-center">#</th>
                <th>Job Card #</th>
                <th>Job Name</th>
                <th>Check In</th>
                <th>Check Out Status</th>
                <th class="text-center">Days Taken</th>
                <th class="text-center">Delay</th>
            </tr>
        `);
        $('#detailTableBody').html('<tr><td colspan="7" class="text-center p-5"><div class="spinner-border text-primary"></div></td></tr>');
        $('#drillDownModal').modal('show');

        let url = "{{ route('admin.dashboard.cylinder_agent_detail') }}";
        $.get(url, {
            agent_id: agentId,
            limit: limit,
            from_date: $('input[name="from_date"]').val(),
            to_date: $('input[name="to_date"]').val()
        }, function(data) {
            let html = '';
            if(data.length == 0) {
                html = '<tr><td colspan="7" class="text-center text-muted p-5">No late deliveries found.</td></tr>';
            } else {
                data.forEach(item => {
                    html += `
                        <tr>
                            <td class="text-center fw-bold text-muted">${item.sr_no}</td>
                            <td class="jc-highlight">#${item.job_card_no}</td>
                            <td class="job-name-highlight">${item.job_name}</td>
                            <td>${item.check_in}</td>
                            <td class="fw-bold ${item.check_out == 'In-Process' ? 'text-warning' : 'text-success'}">${item.check_out}</td>
                            <td class="text-center fw-bold">${item.days_taken} Days</td>
                            <td class="text-center"><span class="badge bg-danger p-2" style="font-size: 0.9rem;">+${item.late_by} Days</span></td>
                        </tr>
                    `;
                });
            }
            $('#detailTableBody').html(html);
        });
    }
    function showBlockageDetail(reasonId, reasonName) {
        $('#detailTitle').text("Blockage Details - " + reasonName);
        $('#detailTableHeader').html(`
            <tr>
                <th class="text-center">#</th>
                <th>Date</th>
                <th>Job Card #</th>
                <th>Job Name</th>
                <th>Machine</th>
                <th>Duration</th>
                <th>Customer</th>
            </tr>
        `);
        $('#detailTableBody').html('<tr><td colspan="7" class="text-center p-5"><div class="spinner-border text-primary"></div></td></tr>');
        $('#drillDownModal').modal('show');

        let url = "{{ route('admin.dashboard.blockage_detail') }}";
        $.get(url, {
            reason_id: reasonId,
            from_date: $('#from_date').val() || $('input[name="from_date"]').val(),
            to_date: $('#to_date').val() || $('input[name="to_date"]').val()
        }, function(data) {
            let html = '';
            if(data.length == 0) {
                html = '<tr><td colspan="7" class="text-center text-muted p-5">No blockage events found.</td></tr>';
            } else {
                data.forEach(item => {
                    html += `
                        <tr>
                            <td class="text-center fw-bold text-muted">${item.sr_no}</td>
                            <td>${item.date}</td>
                            <td class="jc-highlight">#${item.job_card_no}</td>
                            <td class="job-name-highlight">${item.job_name}</td>
                            <td class="fw-bold">${item.machine}</td>
                            <td class="text-center"><span class="badge bg-light-danger text-danger fw-bold p-2">${item.duration}</span></td>
                            <td class="fw-bold">${item.customer}</td>
                        </tr>
                    `;
                });
            }
            $('#detailTableBody').html(html);
        });
    }

    function showCustomerDetail(id, name) {
        fetchPerformanceDetail("{{ route('admin.dashboard.customer_performance_detail') }}", "Customer Orders - " + name, id);
    }

    function showAgentDetail(id, name) {
        fetchPerformanceDetail("{{ route('admin.dashboard.agent_performance_detail') }}", "Agent Orders - " + name, id);
    }

    function showExecutiveDetail(id, name) {
        fetchPerformanceDetail("{{ route('admin.dashboard.executive_performance_detail') }}", "Executive Performance - " + name, id);
    }

    function fetchPerformanceDetail(url, title, id) {
        $('#detailTitle').text(title);
        $('#detailTableHeader').html(`
            <tr>
                <th class="text-center">#</th>
                <th>Date</th>
                <th>Job Card #</th>
                <th>Job Name</th>
                <th>Customer</th>
                <th class="text-end">Pieces</th>
                <th class="text-center">Status</th>
            </tr>
        `);
        $('#detailTableBody').html('<tr><td colspan="7" class="text-center p-5"><div class="spinner-border text-primary"></div></td></tr>');
        $('#drillDownModal').modal('show');

        $.get(url, {
            id: id,
            from_date: $('#from_date').val() || $('input[name="from_date"]').val(),
            to_date: $('#to_date').val() || $('input[name="to_date"]').val()
        }, function(data) {
            let html = '';
            if(data.length == 0) {
                html = '<tr><td colspan="7" class="text-center text-muted p-5">No records found.</td></tr>';
            } else {
                data.forEach(item => {
                    html += `
                        <tr>
                            <td class="text-center fw-bold text-muted">${item.sr_no}</td>
                            <td>${item.date}</td>
                            <td class="jc-highlight">#${item.job_card_no}</td>
                            <td class="job-name-highlight">${item.job_name}</td>
                            <td class="fw-bold">${item.customer}</td>
                            <td class="text-end fw-bold">${item.pieces}</td>
                            <td class="text-center"><span class="badge bg-light-info text-dark border p-2" style="font-size: 0.8rem;">${item.status}</span></td>
                        </tr>
                    `;
                });
            }
            $('#detailTableBody').html(html);
        });
    }
    function showLedgerModal(customerId, customerName) {
        $('#detailTitle').text("Ledger Details - " + customerName);
        $('#detailTableHeader').html(`
            <tr>
                <th>Date</th>
                <th>Remarks</th>
                <th class="text-end">Debit (Dr)</th>
                <th class="text-end">Credit (Cr)</th>
                <th class="text-end">Running Balance</th>
            </tr>
        `);
        $('#detailTableBody').html('<tr><td colspan="5" class="text-center p-5"><div class="spinner-border text-primary"></div></td></tr>');
        $('#drillDownModal').modal('show');

        $.get("{{ route('admin.dashboard.ledger_detail') }}", { customer_id: customerId }, function(data) {
            let html = '';
            if(data.length == 0) {
                html = '<tr><td colspan="5" class="text-center text-muted p-5">No ledger entries found.</td></tr>';
            } else {
                let balance = 0;
                data.forEach(item => {
                    let dr = parseFloat(item.dr) || 0;
                    let cr = parseFloat(item.cr) || 0;
                    balance += (dr - cr);
                    
                    html += `
                        <tr>
                            <td>${item.date}</td>
                            <td class="small">${item.remarks}</td>
                            <td class="text-end text-danger fw-bold">${dr > 0 ? dr.toLocaleString('en-IN', {minimumFractionDigits: 2}) : '-'}</td>
                            <td class="text-end text-success fw-bold">${cr > 0 ? cr.toLocaleString('en-IN', {minimumFractionDigits: 2}) : '-'}</td>
                            <td class="text-end fw-bold">${balance.toLocaleString('en-IN', {minimumFractionDigits: 2})}</td>
                        </tr>
                    `;
                });
            }
            $('#detailTableBody').html(html);
        });
    }

    // =================== ON HOLD DETAIL MODAL ===================
    var holdJobsData = @json($hold_jobs_for_js);

    function showHoldDetail() {
        let html = '';
        if (!holdJobsData || holdJobsData.length === 0) {
            html = '<tr><td colspan="9" class="text-center text-muted p-5 fw-bold">No orders are currently on HOLD.</td></tr>';
        } else {
            holdJobsData.forEach(function(item, idx) {
                html += `
                    <tr class="hold-modal-row">
                        <td class="ps-3 fw-bold text-muted">${idx + 1}</td>
                        <td>
                            <span class="badge fw-bold" style="background:#fee2e2;color:#b91c1c;font-size:0.85rem;letter-spacing:0.3px;">
                                ${item.job_card_no}
                            </span>
                        </td>
                        <td class="fw-bold text-dark" style="font-size:0.92rem;">${item.name_of_job}</td>
                        <td class="fw-bold">${item.customer}</td>
                        <td>
                            <span class="badge" style="background:#e0f2fe;color:#0369a1;font-size:0.78rem;">${item.process}</span>
                        </td>
                        <td>
                            <span class="badge fw-bold" style="background:#fef3c7;color:#92400e;font-size:0.8rem;">
                                <i class="fa fa-pause-circle me-1"></i>${item.hold_reason}
                            </span>
                        </td>
                        <td class="text-muted small" style="max-width:160px;white-space:normal;">${item.hold_notes}</td>
                        <td class="fw-bold" style="font-size:0.85rem;">${item.held_by}</td>
                        <td>
                            <span class="text-danger fw-bold" style="font-size:0.78rem;white-space:nowrap;">
                                <i class="fa fa-clock-o me-1"></i>${item.held_at}
                            </span>
                        </td>
                    </tr>
                `;
            });
        }
        $('#holdDetailTableBody').html(html);
        $('#holdDetailModal').modal('show');
    }
    // ============================================================
</script>
@endsection
