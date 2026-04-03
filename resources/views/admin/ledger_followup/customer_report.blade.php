@extends('layouts.admin.app')
@section('title', 'Customer Followup Intelligence')
@section('css')
<style>
    .report-card { border-radius: 15px; border: none; box-shadow: 0 4px 20px rgba(0,0,0,0.05); }
    .health-gauge { width: 120px; height: 120px; border-radius: 50%; border: 8px solid #f1f5f9; display: flex; align-items: center; justify-content: center; position: relative; }
    .health-gauge::before { content: ''; position: absolute; width: 100%; height: 100%; border-radius: 50%; border: 8px solid transparent; border-top-color: currentColor; transition: 0.5s; }
    .stat-box { background: #f8fafc; border-radius: 12px; padding: 15px; border: 1px solid #f1f5f9; }
    .dt-controls-wrap { display: flex; flex-wrap: wrap; gap: 20px; align-items: flex-end; padding: 20px 0; border-bottom: 1px solid #f1f5f9; margin-bottom: 25px; }
</style>
@endsection

@section('breadcrumb-items')
    <li class="breadcrumb-item">Account Department</li>
    <li class="breadcrumb-item active">Customer Report</li>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card report-card">
                <div class="card-body p-4">
                    <form action="{{ route('ledger_followup.customer_report') }}" method="GET">
                        <div class="dt-controls-wrap px-2">
                            <div class="dt-controls-item" style="flex: 1; min-width: 300px;">
                                <label class="fw-bold small text-muted text-uppercase mb-2">Select Customer to Analyze <span class="text-danger">*</span></label>
                                <select name="customer_id" class="form-control form-control-sm js-example-basic-single" required onchange="this.form.submit()">
                                    <option value="">-- Choose Customer --</option>
                                    @foreach($customers as $c)
                                        <option value="{{ $c->id }}" {{ $customer_id == $c->id ? 'selected' : '' }}>{{ $c->name }} ({{ $c->code }})</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="dt-controls-item">
                                <button type="submit" class="btn btn-primary btn-sm px-4 fw-bold">ANALYZE ACCOUNT</button>
                            </div>
                        </div>
                    </form>

                    @if(!$report_data)
                        <div class="text-center py-5">
                            <i class="fa fa-line-chart fa-4x text-light mb-3"></i>
                            <h5 class="text-muted">Select a customer account to view communication intelligence and payment health.</h5>
                        </div>
                    @else
                        <div class="row g-4 mt-2">
                            <!-- Left: Basic Stats -->
                            <div class="col-md-7">
                                <div class="row g-3">
                                    <div class="col-sm-6">
                                        <div class="stat-box">
                                            <div class="small fw-bold text-muted mb-1 text-uppercase">Total Threads</div>
                                            <div class="h4 fw-bold mb-0 text-primary">{{ $report_data['total_followups'] }}</div>
                                            <div class="extra-small text-muted mt-1">{{ $report_data['closed_count'] }} Closed | {{ $report_data['pending_count'] }} Pending</div>
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="stat-box">
                                            <div class="small fw-bold text-muted mb-1 text-uppercase">Avg Days to Settle</div>
                                            <div class="h4 fw-bold mb-0 text-dark">{{ $report_data['avg_days_to_close'] }} Days</div>
                                            <div class="extra-small text-muted mt-1">Average time from start to final closure</div>
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="stat-box">
                                            <div class="small fw-bold text-muted mb-1 text-uppercase">Communication Intensity</div>
                                            <div class="h4 fw-bold mb-0 text-info">{{ $report_data['avg_iterations'] }} Steps</div>
                                            <div class="extra-small text-muted mt-1">Interactions required per thread</div>
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="stat-box">
                                            <div class="small fw-bold text-muted mb-1 text-uppercase">Collection Rate</div>
                                            @php 
                                                $rate = $report_data['total_followups'] > 0 ? round(($report_data['closed_count'] / $report_data['total_followups']) * 100) : 0;
                                            @endphp
                                            <div class="h4 fw-bold mb-0 text-success">{{ $rate }}%</div>
                                            <div class="extra-small text-muted mt-1">Percentage of threads successfully closed</div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Right: Health & Intelligence -->
                            <div class="col-md-5">
                                <div class="p-4 rounded-4" style="background: #f1f5f9; min-height: 100%;">
                                    <div class="text-center mb-4">
                                        @php 
                                            $score = $report_data['health_score'];
                                            $color = $score >= 80 ? '#10b981' : ($score >= 50 ? '#f59e0b' : '#ef4444');
                                            $label = $score >= 80 ? 'EXCELLENT' : ($score >= 50 ? 'FAIR' : 'DIFFICULT');
                                        @endphp
                                        <div class="d-inline-flex health-gauge" style="color: {{ $color }};">
                                            <div class="text-center mt-1">
                                                <div class="h3 fw-bold mb-0">{{ $score }}%</div>
                                                <div class="extra-small fw-bold">HEALTH</div>
                                            </div>
                                        </div>
                                        <h5 class="mt-3 fw-bold" style="color: {{ $color }};">{{ $label }} ACCOUNT</h5>
                                    </div>

                                    <div class="mt-4">
                                        <h6 class="fw-bold small mb-3">INTELLIGENCE SUMMARY:</h6>
                                        @forelse($report_data['crit_reasons'] as $reason)
                                            <div class="d-flex gap-2 mb-2 p-2 bg-white rounded border-start border-danger border-4">
                                                <i class="fa fa-info-circle text-danger mt-1"></i>
                                                <span class="small fw-bold text-muted">{{ $reason }}</span>
                                            </div>
                                        @empty
                                            <div class="d-flex gap-2 mb-2 p-2 bg-white rounded border-start border-success border-4">
                                                <i class="fa fa-check-circle text-success mt-1"></i>
                                                <span class="small fw-bold text-muted">A smooth account with high collection efficiency.</span>
                                            </div>
                                        @endforelse
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
@endsection

@section('script')
<script>
    $(document).ready(function(){
        $('.js-example-basic-single').select2({ width: '100%' });
    });
</script>
@endsection
