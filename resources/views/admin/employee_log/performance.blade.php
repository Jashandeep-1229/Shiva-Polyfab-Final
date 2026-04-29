@extends('layouts.admin.app')

@section('title', 'Employee Performance Report')

@section('css')
<style>
    .performance-card {
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        background: #fff;
        transition: all 0.3s ease;
        height: 100%;
        display: flex;
        flex-direction: column;
    }
    .performance-card:hover {
        border-color: #cbd5e1;
        box-shadow: 0 10px 25px rgba(0,0,0,0.05);
    }
    .employee-header {
        padding: 15px;
        border-bottom: 1px solid #f1f5f9;
        display: flex;
        align-items: center;
        gap: 12px;
    }
    .employee-avatar {
        width: 36px;
        height: 36px;
        background: #f1f5f9;
        color: #475569;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 14px;
        font-weight: 700;
        border: 1px solid #e2e8f0;
    }
    .employee-info h5 {
        margin: 0;
        font-size: 15px;
        font-weight: 700;
        color: #1e293b;
    }
    .employee-info span {
        font-size: 11px;
        color: #64748b;
        display: block;
    }
    .stats-section {
        padding: 12px 15px;
    }
    .section-title {
        font-size: 10px;
        font-weight: 700;
        color: #94a3b8;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 8px;
        padding-bottom: 4px;
        border-bottom: 1px solid #f1f5f9;
    }
    .stats-row {
        display: flex;
        flex-direction: column;
        gap: 2px;
    }
    .stat-item {
        display: flex;
        align-items: center;
        padding: 6px 0;
        border-radius: 4px;
        margin: 0 -4px;
        padding: 6px 8px;
        transition: background 0.15s;
    }
    .stat-item:hover {
        background: #f8fafc;
        cursor: pointer;
    }
    .stat-icon {
        font-size: 12px;
        width: 16px;
        color: #94a3b8;
        margin-right: 10px;
    }
    .stat-label {
        font-size: 12px;
        color: #475569;
        font-weight: 500;
    }
    .stat-value {
        margin-left: auto;
        font-size: 13px;
        font-weight: 700;
        color: #1e293b;
        background: #f1f5f9;
        padding: 2px 8px;
        border-radius: 12px;
        min-width: 30px;
        text-align: center;
    }
    .filter-card {
        border-radius: 12px;
        border: 1px solid #e2e8f0;
        box-shadow: none;
        margin-bottom: 25px;
    }
</style>
@endsection

@section('breadcrumb-items')
<li class="breadcrumb-item">Employee Logs</li>
<li class="breadcrumb-item active">Performance</li>
@endsection

@section('content')

<div class="container-fluid">
    <!-- Filter Section -->
    <div class="card filter-card">
        <div class="card-body">
            <form action="{{ route('employee_log.performance') }}" method="GET" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label fw-bold">Select Employee</label>
                    <select name="user_id" class="form-control select2">
                        <option value="">All Employees</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" {{ $userId == $user->id ? 'selected' : '' }}>{{ $user->name }} ({{ $user->role_as }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold">From Date</label>
                    <input type="date" name="from_date" class="form-control" value="{{ $fromDate }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold">To Date</label>
                    <input type="date" name="to_date" class="form-control" value="{{ $toDate }}">
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fa fa-filter me-2"></i> Filter Report
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Employee Cards -->
    @php
        if (!function_exists('drillLink')) {
            function drillLink($user, $logName, $event = null, $from = null, $to = null) {
                $params = ['user_id' => $user->id, 'from_date' => $from, 'to_date' => $to, 'log_name' => $logName];
                if ($event) $params['event'] = $event;
                return route('employee_log.index', $params);
            }
        }
    @endphp
    <div class="row">
        @foreach($reportStats as $uId => $item)
            @php $user = $item['user']; @endphp
            <div class="col-xl-4 col-md-6 mb-4">
                <div class="performance-card">
                    <div class="employee-header">
                        <div class="employee-avatar">
                            {{ substr($user->name, 0, 1) }}
                        </div>
                        <div class="employee-info">
                            <h5>{{ $user->name }}</h5>
                            <span>{{ $user->role_as }} (ID: #{{ $user->id }})</span>
                        </div>
                        <div class="ms-auto">
                            <i class="fa fa-line-chart fa-2x opacity-25"></i>
                        </div>
                    </div>
                    
                    <div class="stats-section">
                        <div class="section-title">Core Operations</div>
                        <div class="stats-row">
                            <div class="stat-item" onclick="window.location='{{ drillLink($user, 'User', null, $fromDate, $toDate) }}'">
                                <i class="fa fa-sign-in stat-icon"></i>
                                <span class="stat-label">System Logins</span>
                                <span class="stat-value">{{ $item['logins'] }}</span>
                            </div>
                            <div class="stat-item" onclick="window.location='{{ drillLink($user, 'JobCard', 'created', $fromDate, $toDate) }}'">
                                <i class="fa fa-plus-square stat-icon"></i>
                                <span class="stat-label">New Job Cards</span>
                                <span class="stat-value">{{ $item['job_card_created'] }}</span>
                            </div>
                            <div class="stat-item" onclick="window.location='{{ drillLink($user, 'JobCard', 'updated', $fromDate, $toDate) }}'">
                                <i class="fa fa-refresh stat-icon"></i>
                                <span class="stat-label">Process Movements</span>
                                <span class="stat-value">{{ $item['job_card_moved'] }}</span>
                            </div>
                            <div class="stat-item" onclick="window.location='{{ drillLink($user, 'Bill', 'created', $fromDate, $toDate) }}'">
                                <i class="fa fa-file-text-o stat-icon"></i>
                                <span class="stat-label">Bills Created</span>
                                <span class="stat-value">{{ $item['bill_created'] }}</span>
                            </div>
                        </div>

                        <div class="section-title mt-3">Inventory & Logistics</div>
                        <div class="stats-row">
                            <div class="stat-item" onclick="window.location='{{ drillLink($user, 'ManageStock', 'created', $fromDate, $toDate) }}'">
                                <i class="fa fa-exchange stat-icon"></i>
                                <span class="stat-label">Roto Stock (In/Out)</span>
                                <span class="stat-value">{{ $item['stock_in'] + $item['stock_out'] }}</span>
                            </div>
                            <div class="stat-item" onclick="window.location='{{ drillLink($user, 'CommonManageStock', 'created', $fromDate, $toDate) }}'">
                                <i class="fa fa-cubes stat-icon"></i>
                                <span class="stat-label">Common Stock (In/Out)</span>
                                <span class="stat-value">{{ $item['common_stock_in'] + $item['common_stock_out'] }}</span>
                            </div>
                            <div class="stat-item" onclick="window.location='{{ drillLink($user, 'PackingSlip', 'created', $fromDate, $toDate) }}'">
                                <i class="fa fa-cube stat-icon"></i>
                                <span class="stat-label">Packing Slips</span>
                                <span class="stat-value">{{ $item['roto_packing_slip'] + $item['common_packing_slip'] }}</span>
                            </div>
                        </div>

                        <div class="section-title mt-3">CRM & Accounts</div>
                        <div class="stats-row">
                            <div class="stat-item" onclick="window.location='{{ drillLink($user, 'Lead', 'created', $fromDate, $toDate) }}'">
                                <i class="fa fa-user-plus stat-icon"></i>
                                <span class="stat-label">New Leads</span>
                                <span class="stat-value">{{ $item['lead_added'] }}</span>
                            </div>
                            <div class="stat-item" onclick="window.location='{{ drillLink($user, 'Lead', 'updated', $fromDate, $toDate) }}'">
                                <i class="fa fa-check-circle-o stat-icon"></i>
                                <span class="stat-label">Converted Leads</span>
                                <span class="stat-value">{{ $item['converted'] }}</span>
                            </div>
                            <div class="stat-item" onclick="window.location='{{ drillLink($user, 'Lead', 'updated', $fromDate, $toDate) }}'">
                                <i class="fa fa-times-circle-o stat-icon"></i>
                                <span class="stat-label">Lost Leads</span>
                                <span class="stat-value">{{ $item['lost'] }}</span>
                            </div>
                            <div class="stat-item" onclick="window.location='{{ drillLink($user, 'CustomerLedger', 'created', $fromDate, $toDate) }}'">
                                <i class="fa fa-calculator stat-icon"></i>
                                <span class="stat-label">Voucher Entries</span>
                                <span class="stat-value">{{ $item['credit_vouchers'] + $item['debit_vouchers'] + $item['bad_debt_vouchers'] }}</span>
                            </div>
                        </div>

                        <div class="section-title mt-3 text-primary">Followup Intelligence</div>
                        <div class="stats-row">
                            <div class="stat-item" onclick="window.location='{{ route('ledger_followup.index', ['user_id' => $user->id, 'filter' => 'pending']) }}'">
                                <i class="fa fa-hourglass-start stat-icon"></i>
                                <span class="stat-label">Followup Pending</span>
                                <span class="stat-value">{{ $item['followup_pending'] }}</span>
                            </div>
                            <div class="stat-item" onclick="window.location='{{ route('ledger_followup.history_all', ['executive_id' => $user->id, 'from_date' => $fromDate, 'to_date' => $toDate]) }}'">
                                <i class="fa fa-check-square stat-icon"></i>
                                <span class="stat-label">Completed On Time</span>
                                <span class="stat-value">{{ $item['followup_on_time'] }}</span>
                            </div>
                            <div class="stat-item" onclick="window.location='{{ route('ledger_followup.history_all', ['executive_id' => $user->id, 'from_date' => $fromDate, 'to_date' => $toDate]) }}'">
                                <i class="fa fa-clock-o stat-icon"></i>
                                <span class="stat-label">Completed But Late</span>
                                <span class="stat-value">{{ $item['followup_late'] }}</span>
                            </div>
                            <div class="stat-item" onclick="window.location='{{ route('ledger_followup.index', ['user_id' => $user->id, 'filter' => 'pending']) }}'">
                                <i class="fa fa-exclamation-triangle stat-icon"></i>
                                <span class="stat-label">Missing Followup</span>
                                <span class="stat-value text-danger">{{ $item['followup_missing'] }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>
@endsection

@section('script')
@endsection
