@extends('layouts.admin.app')
@section('title', 'Followup Performance Report')
@section('css')
<style>
    .report-card { border-radius: 15px; border: none; box-shadow: 0 4px 20px rgba(0,0,0,0.05); }
    .stat-card { border-radius: 12px; transition: transform 0.2s; border: 1px solid #f1f5f9; position: relative; overflow: hidden; }
    .stat-card:hover { transform: translateY(-5px); }
    .stat-card .icon-box { width: 45px; height: 45px; border-radius: 10px; display: flex; align-items: center; justify-content: center; margin-bottom: 15px; }
    .trend-badge { font-size: 10px; padding: 2px 8px; border-radius: 20px; font-weight: 700; }
    .dt-controls-wrap { display: flex; flex-wrap: wrap; gap: 20px; align-items: flex-end; padding: 20px 0; border-bottom: 1px solid #f1f5f9; margin-bottom: 25px; }
    .dt-controls-item { display: flex; flex-direction: column; gap: 5px; }
    .dt-controls-item label { font-weight: 700; font-size: 12px; color: #64748b; text-transform: uppercase; margin-bottom: 0; }
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
    <li class="breadcrumb-item">Account Department</li>
    <li class="breadcrumb-item active">Followup Report</li>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card report-card">
                <div class="card-body p-4">
                    <form action="{{ route('ledger_followup.report') }}" method="GET">
                        <div class="dt-controls-wrap px-2">
                            <div class="dt-controls-item" style="flex: 1; min-width: 220px;">
                                <label>Select Employee <span class="text-danger">*</span></label>
                                <select name="employee_id" class="form-control form-control-sm" required>
                                    <option value="">-- Choose Employee --</option>
                                    @foreach($executives as $ex)
                                        <option value="{{ $ex->id }}" {{ $employee_id == $ex->id ? 'selected' : '' }}>{{ $ex->name }} ({{ $ex->role_as }})</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="dt-controls-item" style="flex: 1; min-width: 220px;">
                                <label>Select Customer</label>
                                <select name="customer_id" class="form-control form-control-sm js-example-basic-single">
                                    <option value="">All Customers</option>
                                    @foreach($customers as $customer)
                                        <option value="{{ $customer->id }}" {{ request('customer_id') == $customer->id ? 'selected' : '' }}>{{ $customer->name }} ({{ $customer->code }})</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="dt-controls-item">
                                <label>From Date</label>
                                <input type="date" name="from_date" class="form-control form-control-sm" value="{{ request('from_date') ?? date('Y-m-d', strtotime('-15 days')) }}">
                            </div>
                            <div class="dt-controls-item">
                                <label>To Date</label>
                                <input type="date" name="to_date" class="form-control form-control-sm" value="{{ request('to_date') ?? date('Y-m-d') }}">
                            </div>

                            <div class="dt-controls-item">
                                <button type="submit" class="btn btn-primary btn-sm px-4 fw-bold">GENERATE REPORT</button>
                            </div>
                        </div>
                    </form>

                    @if(!$report_data)
                        <div class="text-center py-5">
                            <i class="fa fa-user-circle-o fa-4x text-light mb-3"></i>
                            <h5 class="text-muted">Please select an employee to generate the performance report.</h5>
                        </div>
                    @else
                        <div class="row g-4 mt-2">
                            <div class="col-12 mb-2">
                                <div class="alert alert-info py-2 px-3 border-0 small d-flex justify-content-between">
                                    <span><strong>Current Period:</strong> {{ date('j M', strtotime($report_data['from_date'])) }} - {{ date('j M, Y', strtotime($report_data['to_date'])) }}</span>
                                    <span><strong>Comparison Period:</strong> {{ date('j M', strtotime($report_data['prev_from'])) }} - {{ date('j M, Y', strtotime($report_data['prev_to'])) }}</span>
                                </div>
                            </div>

                            <!-- Total Pending -->
                            <div class="col-md-3">
                                <div class="stat-card p-3 bg-white h-100">
                                    <div class="icon-box bg-primary-subtle text-primary">
                                        <i class="fa fa-clock-o"></i>
                                    </div>
                                    <h6 class="text-muted small fw-bold mb-1">TOTAL PENDING</h6>
                                    <h3 class="fw-bold mb-2">{{ $report_data['pending'] }}</h3>
                                    @php renderComparison($report_data['pending'], $report_data['previous']['pending'], 'inverse'); @endphp
                                </div>
                            </div>

                            <!-- Delayed Pending -->
                            <div class="col-md-3">
                                <div class="stat-card p-3 bg-white h-100">
                                    <div class="icon-box bg-danger-subtle text-danger">
                                        <i class="fa fa-warning"></i>
                                    </div>
                                    <h6 class="text-muted small fw-bold mb-1">DELAYED PENDING</h6>
                                    <h3 class="fw-bold mb-2 text-danger">{{ $report_data['delayed_pending'] }}</h3>
                                    @php renderComparison($report_data['delayed_pending'], $report_data['previous']['delayed_pending'], 'inverse'); @endphp
                                </div>
                            </div>

                            <!-- On Time Complete -->
                            <div class="col-md-3">
                                <div class="stat-card p-3 bg-white h-100">
                                    <div class="icon-box bg-success-subtle text-success">
                                        <i class="fa fa-check-circle"></i>
                                    </div>
                                    <h6 class="text-muted small fw-bold mb-1">ON-TIME COMPLETED</h6>
                                    <h3 class="fw-bold mb-2 text-success">{{ $report_data['on_time'] }}</h3>
                                    @php renderComparison($report_data['on_time'], $report_data['previous']['on_time'], 'direct'); @endphp
                                </div>
                            </div>

                            <!-- Delayed Complete -->
                            <div class="col-md-3">
                                <div class="stat-card p-3 bg-white h-100">
                                    <div class="icon-box bg-warning-subtle text-warning">
                                        <i class="fa fa-hourglass-end text-dark"></i>
                                    </div>
                                    <h6 class="text-muted small fw-bold mb-1 f-10">COMPLETED WITH DELAY</h6>
                                    <h3 class="fw-bold mb-2">{{ $report_data['delayed_complete'] }}</h3>
                                    @php renderComparison($report_data['delayed_complete'], $report_data['previous']['delayed_complete'], 'inverse'); @endphp
                                </div>
                            </div>

                            <!-- Avg Iteration -->
                            <div class="col-md-12 mt-4">
                                <div class="card bg-primary text-white p-4 border-0" style="border-radius: 15px;">
                                    <div class="row align-items-center">
                                        <div class="col-md-8">
                                            <h5 class="fw-bold mb-1">Employee Efficiency Score</h5>
                                            <p class="small mb-0 opacity-75">Calculated based on average interactions required to process a customer followup.</p>
                                        </div>
                                        <div class="col-md-4 text-md-end">
                                            <div class="d-inline-block text-center px-4 py-2 bg-white text-primary rounded-3 shadow">
                                                <div class="f-10 fw-bold uppercase">Avg Iterations</div>
                                                <div class="h4 fw-bold mb-0">{{ $report_data['avg_iterations'] }}</div>
                                            </div>
                                            <div class="mt-2 small">
                                                @php renderComparison($report_data['avg_iterations'], $report_data['previous']['avg_iterations'], 'inverse', true); @endphp
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@php
function renderComparison($current, $previous, $type = 'direct', $whiteText = false) {
    if ($previous == 0) {
        echo '<span class="trend-badge bg-secondary text-white">N/A</span> <span class="extra-small text-muted ' . ($whiteText ? 'text-white-50' : '') . '">Prev: 0</span>';
        return;
    }

    $diff = $current - $previous;
    $perc = round(($diff / $previous) * 100, 1);
    
    // For inverse: less is better (delayed things). For direct: more is better (completed things).
    $isImprovement = ($type == 'direct' && $diff >= 0) || ($type == 'inverse' && $diff <= 0);
    
    $color = $isImprovement ? 'success' : 'danger';
    $arrow = $diff >= 0 ? '<i class="fa fa-arrow-up"></i>' : '<i class="fa fa-arrow-down"></i>';
    $status = $isImprovement ? 'Improved' : 'Downgraded';
    
    echo '<span class="trend-badge bg-'. $color .' text-white">'. $arrow .' '. abs($perc) .'%</span> ';
    echo '<span class="extra-small ' . ($whiteText ? 'text-white' : ($isImprovement ? 'text-success' : 'text-danger')) . ' fw-bold">' . $status . '</span> ';
    echo '<span class="extra-small ' . ($whiteText ? 'text-white-50' : 'text-muted') . '">vs Prev: '. $previous .'</span>';
}
@endphp

@endsection

@section('script')
<script>
    $(document).ready(function(){
        $('.js-example-basic-single').select2({ width: '100%' });
    });
</script>
@endsection
