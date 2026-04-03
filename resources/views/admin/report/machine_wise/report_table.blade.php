<style>
    .machine-report-table thead th {
        background-color: #242934 !important;
        color: #ffffff !important;
        border: none !important;
        padding-top: 10px !important;
        padding-bottom: 10px !important;
    }
    .text-dark-theme { color: #242934 !important; }
    .dark-only .text-dark-theme { color: #ffffff !important; }
    
    /* Force dark mode for tables */
    .dark-only .machine-report-table,
    .dark-only .machine-report-table tbody,
    .dark-only .machine-report-table tbody tr,
    .dark-only .machine-report-table tbody td {
        background-color: #242934 !important;
        color: #efefef !important;
        border-color: rgba(255,255,255,0.1) !important;
    }
    
    .dark-only .machine-report-table tbody tr:nth-of-type(odd) td {
        background-color: #2b313d !important;
    }

    /* Card bodies in dark mode */
    .dark-only .card, .dark-only .card-body {
        background-color: #242934 !important;
        color: #efefef !important;
    }
    
    /* Header contrast */
    .custom-bg-light { background-color: #f8f9fa !important; }
    .dark-only .custom-bg-light { background-color: #1d1e26 !important; }
    
    /* Summary card contrast in dark mode */
    .dark-only .bg-light-primary, .dark-only .bg-light-success, .dark-only .bg-light-danger, .dark-only .bg-light-warning, .dark-only .bg-light-info {
        border-width: 2px !important;
    }
</style>
<!-- Summary Cards -->
<div class="row mb-5 g-3">
    <div class="col-md-3">
        <div class="border rounded p-3 text-center bg-light-primary border-primary h-100 d-flex flex-column justify-content-center">
            <h6 class="text-primary f-w-700 f-12 text-uppercase mb-2">Pieces Production</h6>
            <h3 class="mb-0 fw-bold text-dark-theme">{{ number_format($totals['estimate_production'], 2) }}</h3>
        </div>
    </div>
    <div class="col-md-3">
        <div class="border rounded p-3 text-center bg-light-success border-success h-100 d-flex flex-column justify-content-center">
            <h6 class="text-success f-w-700 f-12 text-uppercase mb-2">Actual Production</h6>
            <h3 class="mb-0 fw-bold text-dark-theme">{{ number_format($totals['actual_order'], 2) }}</h3>
        </div>
    </div>
    <div class="col-md-2">
        <div class="border rounded p-3 text-center bg-light-danger border-danger h-100 d-flex flex-column justify-content-center">
            <h6 class="text-danger f-w-700 f-12 text-uppercase mb-2">Total Wastage</h6>
            <h3 class="mb-0 fw-bold text-dark-theme">{{ number_format($totals['wastage'], 2) }}</h3>
        </div>
    </div>
    <div class="col-md-2">
        <div class="border rounded p-3 text-center bg-light-warning border-warning h-100 d-flex flex-column justify-content-center">
            <h6 class="text-warning f-w-700 f-12 text-uppercase mb-2">Blockage Time</h6>
            <h3 class="mb-0 fw-bold text-dark-theme">{{ number_format($totals['blockage_time'], 2) }}h</h3>
        </div>
    </div>
    <div class="col-md-2">
        <div class="border rounded p-3 text-center bg-light-info border-info h-100 d-flex flex-column justify-content-center">
            <h6 class="text-info f-w-700 f-12 text-uppercase mb-2">Working Hours</h6>
            <h3 class="mb-0 fw-bold text-dark-theme">{{ number_format($totals['working_hours'], 2) }}h</h3>
        </div>
    </div>
</div>

<div class="row">
    <!-- Analysis Column -->
    <div class="col-md-4">
        <div class="card mb-0 shadow-none border">
            <div class="card-header py-3 custom-bg-light d-flex justify-content-between align-items-center border-bottom">
                <h6 class="mb-0 f-w-700 text-dark-theme"><i class="fa fa-pie-chart me-2 text-primary"></i>Blockage Reason Analysis</h6>
            </div>
            <div class="card-body p-0">
                @if($blockage_stats->count() > 0)
                <div class="table-responsive">
                    <table class="table table-sm mb-0 machine-report-table">
                        <thead>
                            <tr class="f-10 text-uppercase">
                                <th class="ps-3">Reason</th>
                                <th class="text-center">Counter</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($blockage_stats as $stat)
                            <tr>
                                <td class="ps-3 border-0 py-2">{{ $stat['reason'] }}</td>
                                <td class="text-center border-0 py-2">
                                    <span class="badge rounded-pill bg-warning text-dark px-3">{{ $stat['count'] }}</span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="p-5 text-center text-dark-theme">
                    <i class="fa fa-info-circle f-30 mb-3" style="opacity: 0.5;"></i>
                    <p class="mb-0">No blockage data found for this period</p>
                </div>
                @endif
            </div>
        </div>
    </div>
    
    <!-- Logs Column -->
    <div class="col-md-8">
        <div class="card mb-0 shadow-none border">
            <div class="card-header py-3 custom-bg-light border-bottom">
                <h6 class="mb-0 f-w-700 text-dark-theme"><i class="fa fa-list me-2 text-primary"></i>Detailed Production Logs</h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm table-bordered mb-0 machine-report-table">
                        <thead>
                            <tr class="f-10 text-uppercase">
                                <th class="ps-3 border-0 text-white">Date</th>
                                <th class="border-0 text-white">Job Name</th>
                                <th class="border-0 text-white">Machine</th>
                                <th class="border-0 text-white text-end">Prod.</th>
                                <th class="border-0 text-white text-end">Actual</th>
                                <th class="border-0 text-white text-end">Waste</th>
                                <th class="border-0 text-white text-center">Block</th>
                                <th class="border-0 text-white text-center">Work</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($report_data as $row)
                            <tr class="f-12">
                                <td class="ps-3">{{ date('d M, Y', strtotime($row->date)) }}</td>
                                <td class="fw-bold">{{ $row->job_card->name_of_job ?? 'N/A' }}</td>
                                <td>{{ $row->machine->name ?? 'N/A' }}</td>
                                <td class="text-end">{{ number_format($row->estimate_production, 2) }}</td>
                                <td class="text-end">{{ number_format($row->actual_order, 2) }}</td>
                                <td class="text-end text-danger fw-bold">{{ number_format($row->wastage, 2) }}</td>
                                <td class="text-center {{ $row->blockage_time > 0 ? 'text-warning fw-bold' : '' }}">{{ (float)$row->blockage_time }}h</td>
                                <td class="text-center text-info">{{ (float)$row->working_hours }}h</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="text-center p-5 text-dark-theme f-14">No data found matching your query</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
