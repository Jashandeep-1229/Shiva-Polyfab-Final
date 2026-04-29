    <!-- Process-wise Delay Cards (Showing only late ones) -->
    <div class="row mb-4">
        @php
            $process_config = [
                'Cylinder' => ['color' => 'primary', 'icon' => 'layers', 'limit' => env('CYLINDER_LIMIT'), 'label' => 'CYLINDER'],
                'Printing' => ['color' => 'warning', 'icon' => 'printer', 'limit' => env('PRINTING_LIMIT'), 'label' => 'PRINTING'],
                'Lamination' => ['color' => 'info', 'icon' => 'copy', 'limit' => env('LAMINATION_LIMIT'), 'label' => 'LAMINATION'],
                'Cutting' => ['color' => 'danger', 'icon' => 'scissors', 'limit' => env('CUTTING_LIMIT'), 'label' => 'BOX / CUTTING']
            ];
        @endphp

        @foreach($process_config as $name => $conf)
            @php 
                $count = $late_jobs['by_process'][$name] ?? 0; 
                $total_count = $late_jobs['total_by_process'][$name] ?? 0;
            @endphp
            @if($total_count > 0)
            <div class="col-xl-3 col-md-4 col-sm-6 mb-3">
                <div class="card border-0 shadow-sm h-100 pointer overflow-hidden" onclick="showLateDetail('{{ $name }}')" 
                     style="border-radius: 12px; border-left: 5px solid var(--bs-{{ $conf['color'] }}) !important;">
                    <div class="card-body p-4 position-relative">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <div>
                                <h6 class="text-muted fw-bold mb-0" style="font-size: 0.8rem; letter-spacing: 0.5px;">{{ $conf['label'] }} LATE</h6>
                                <h2 class="mb-0 fw-900 mt-1 text-{{ $conf['color'] }}">
                                    {{ $count }} <span class="text-muted fw-normal" style="font-size: 1rem;">/ {{ $total_count }}</span>
                                </h2>
                            </div>
                            <div class="bg-light-{{ $conf['color'] }} rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 45px; height: 45px;">
                                <i data-feather="{{ $conf['icon'] }}" class="text-{{ $conf['color'] }}"></i>
                            </div>
                        </div>
                        <div class="d-flex align-items-center">
                            <span class="badge bg-light-{{ $conf['color'] }} text-{{ $conf['color'] }} border-0 px-2 py-1" style="font-size: 0.7rem;">
                                Limit: {{ $conf['limit'] }} Days
                            </span>
                            <small class="ms-2 text-muted" style="font-size: 0.7rem;">Drill down <i class="fa fa-arrow-right f-8"></i></small>
                        </div>
                    </div>
                </div>
            </div>
            @endif
        @endforeach

        <!-- Dispatch Overdue Card (Global-No Date Filter) -->
        <div class="col-xl-3 col-md-4 col-sm-6 mb-3">
            <div class="card border-0 shadow-sm h-100 pointer overflow-hidden" onclick="showOverdueDispatches()" 
                 style="border-radius: 12px; border-left: 5px solid #7366ff !important; background: #fdfcff;">
                <div class="card-body p-4 position-relative">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div>
                            <h6 class="text-muted fw-bold mb-0" style="font-size: 0.8rem; letter-spacing: 0.5px;">DISPATCH OVERDUE</h6>
                            <h2 class="mb-0 fw-900 mt-1" style="color: #7366ff;">{{ count($overdue_dispatches) }}</h2>
                        </div>
                        <div class="bg-light-primary rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 45px; height: 45px;">
                            <i data-feather="alert-triangle" style="color: #7366ff;"></i>
                        </div>
                    </div>
                    <div class="d-flex align-items-center">
                        <span class="badge bg-light-primary border-0 px-2 py-1" style="font-size: 0.7rem; color: #7366ff;">
                            Not Completed
                        </span>
                        <small class="ms-2 text-muted" style="font-size: 0.7rem;">Click to view list <i class="fa fa-arrow-right f-8"></i></small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Account Pending Card (Global-No Date Filter) -->
        <div class="col-xl-3 col-md-4 col-sm-6 mb-3">
            <div class="card border-0 shadow-sm h-100 pointer overflow-hidden" onclick="showAccountPending()" 
                 style="border-radius: 12px; border-left: 5px solid #ffa000 !important; background: #fffcf0;">
                <div class="card-body p-4 position-relative">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div>
                            <h6 class="text-muted fw-bold mb-0" style="font-size: 0.8rem; letter-spacing: 0.5px;">ACCOUNT PENDING</h6>
                            <h2 class="mb-0 fw-900 mt-1" style="color: #ffa000;">{{ $account_pending_count }}</h2>
                        </div>
                        <div class="bg-light-warning rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 45px; height: 45px;">
                            <i data-feather="dollar-sign" style="color: #ffa000;"></i>
                        </div>
                    </div>
                    <div class="d-flex align-items-center">
                        <span class="badge bg-light-warning border-0 px-2 py-1" style="font-size: 0.7rem; color: #ffa000;">
                            Verify Payments
                        </span>
                        <small class="ms-2 text-muted" style="font-size: 0.7rem;">Click to view <i class="fa fa-arrow-right f-8"></i></small>
                    </div>
                </div>
            </div>
        </div>

        <!-- ON HOLD Jobs Card (Global - always visible if > 0) -->
        @if(count($on_hold_jobs) > 0)
        <div class="col-xl-3 col-md-4 col-sm-6 mb-3">
            <div class="card border-0 shadow-sm h-100 pointer overflow-hidden" onclick="showHoldDetail()"
                 style="border-radius: 12px; border-left: 5px solid #ef4444 !important; background: #fff5f5; animation: holdCardPulse 2s ease-in-out infinite;">
                <div class="card-body p-4 position-relative">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div>
                            <h6 class="fw-bold mb-0 d-flex align-items-center gap-1" style="font-size: 0.8rem; letter-spacing: 0.5px; color: #dc2626;">
                                <i class="fa fa-pause-circle"></i> ORDERS ON HOLD
                            </h6>
                            <h2 class="mb-0 fw-900 mt-1 text-danger">{{ count($on_hold_jobs) }}</h2>
                        </div>
                        <div class="rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 45px; height: 45px; background: #fee2e2;">
                            <i class="fa fa-lock" style="color: #ef4444; font-size: 18px;"></i>
                        </div>
                    </div>
                    <div class="d-flex align-items-center">
                        <span class="badge px-2 py-1" style="font-size: 0.7rem; background: #fee2e2; color: #b91c1c;">
                            ⛔ Process Blocked
                        </span>
                        <small class="ms-2 text-muted" style="font-size: 0.7rem;">Click to view reasons <i class="fa fa-arrow-right f-8"></i></small>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- If no late jobs at all -->
        @if($late_jobs['total_late'] == 0)
        <div class="col-12">
            <div class="card border-0 shadow-sm p-5 text-center" style="border-radius: 12px;">
                <div class="bg-light-success rounded-circle d-inline-flex p-3 mb-3 mx-auto">
                    <i data-feather="check-circle" class="text-success" style="width: 40px; height: 40px;"></i>
                </div>
                <h4 class="fw-bold">All Jobs on Schedule!</h4>
                <p class="text-muted mb-0">No production delays detected for the current selection.</p>
            </div>
        </div>
        @endif
    </div>

    <!-- Secondary Summary Stats -->
    @if($late_jobs['total_late'] > 0)
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm h-100" style="border-radius: 12px; border-bottom: 3px solid #f8d62b;">
                <div class="card-body p-3 d-flex align-items-center">
                    <div class="bg-light-warning rounded-pill p-2 me-3">
                        <i data-feather="clock" class="text-warning"></i>
                    </div>
                    <div>
                        <h6 class="text-muted mb-0 small fw-bold">AVG DELAY</h6>
                        <h4 class="mb-0 fw-bold">{{ $late_jobs['avg_delay_days'] }} Days</h4>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm h-100" style="border-radius: 12px; border-bottom: 3px solid #7366ff;">
                <div class="card-body p-3 d-flex align-items-center">
                    <div class="bg-light-primary rounded-pill p-2 me-3">
                        <i data-feather="alert-octagon" class="text-primary"></i>
                    </div>
                    <div>
                        <h6 class="text-muted mb-0 small fw-bold">TOTAL LATE</h6>
                        <h4 class="mb-0 fw-bold">{{ $late_jobs['total_late'] }} Jobs</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Factory Performance Deep Dive -->
    <div class="row mb-4">
        <div class="col-md-7">
            <div class="card factory-card h-100">
                <div class="card-header bg-white pb-0 border-0 d-flex justify-content-between">
                    <h5 class="fw-bold">Machine Analytics</h5>
                    <span class="badge bg-light-info text-dark">Production vs Blockage</span>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead>
                                <tr>
                                    <th>Machine</th>
                                    <th class="text-end">Production</th>
                                    <th class="text-end">Blockage (Min)</th>
                                    <th class="text-end">Wastage (Kg)</th>
                                    <th class="text-center">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($machine_stats as $m)
                                <tr class="pointer" onclick="showMachineDetail('{{ $m['id'] }}', '{{ $m['name'] }}')">
                                    <td>
                                        <div class="fw-bold text-dark">{{ $m['name'] }}</div>
                                        <small class="text-muted">{{ $m['type'] }}</small>
                                    </td>
                                    <td class="text-end fw-600 text-dark">
                                        {{ number_format($m['production']) }} <br>
                                        <small class="text-muted">Target: {{ number_format($m['target']) }}</small>
                                    </td>
                                    <td class="text-end text-danger fw-bold">{{ $m['blockage'] }} min</td>
                                    <td class="text-end text-muted">{{ $m['wastage'] }}</td>
                                    <td class="text-center">
                                        @if($m['status'] == 'Best') 
                                            <span class="status-badge bg-light-success">Best</span>
                                        @elseif($m['status'] == 'OK') 
                                            <span class="status-badge bg-light-primary">OK</span>
                                        @else 
                                            <span class="status-badge bg-light-danger">Weak</span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-5">
            <div class="row">
                <div class="col-12">
                    <div class="card factory-card">
                        <div class="card-header bg-white pb-0 border-0">
                            <h5 class="fw-bold text-danger">Top Blockage Reasons <small class="text-muted" style="font-size: 0.65rem;">(Factory Wide)</small></h5>
                        </div>
                        <div class="card-body">
                            @if(count($blockage_analytics) > 0)
                                @foreach($blockage_analytics as $block)
                                <div class="mb-3 hover-shadow rounded p-2" style="cursor: pointer;" onclick="showBlockageDetail('{{ $block['id'] }}', '{{ addslashes($block['reason']) }}')">
                                    <div class="d-flex justify-content-between mb-1">
                                        <span class="fw-bold" style="font-size: 0.8rem;">{{ $block['reason'] }}</span>
                                        <span class="text-danger small fw-bold">{{ $block['total_min'] }} Min</span>
                                    </div>
                                    <div class="progress" style="height: 6px;">
                                        <div class="progress-bar bg-danger" role="progressbar" 
                                             style="width: {{ min(100, ($block['total_min'] / (max(1, $blockage_analytics->max('total_min')))) * 100) }}%"></div>
                                    </div>
                                    <div class="d-flex justify-content-between mt-1">
                                        <small class="text-muted">{{ $block['count'] }} Occurrences</small>
                                        <div class="text-end">
                                            <small class="text-primary fw-bold d-block" style="font-size: 0.65rem;">{{ $block['machines'] }}</small>
                                            <small class="text-muted d-block" style="font-size: 0.6rem;">JC: {{ $block['job_cards'] }}</small>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            @else
                                <div class="text-center py-4 text-muted">No Blockages recorded in this period</div>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="col-12">
                    <div class="card factory-card" style="border-left: 5px solid #dc2626 !important; background: #fffcfc;">
                        <div class="card-header bg-white pb-0 border-0">
                            <h5 class="fw-bold text-danger">Top Hold Reasons <small class="text-muted" style="font-size: 0.65rem;">(Active Holds)</small></h5>
                        </div>
                        <div class="card-body">
                            @if(count($hold_reasons_analytics) > 0)
                                @foreach($hold_reasons_analytics as $hr)
                                <div class="mb-3 p-2 rounded bg-white shadow-xs border" style="cursor: pointer;" onclick="showHoldDetail()">
                                    <div class="d-flex justify-content-between mb-1 align-items-center">
                                        <span class="fw-bold text-dark" style="font-size: 0.8rem;">{{ $hr['reason'] }}</span>
                                        <span class="badge bg-danger shadow-sm">{{ $hr['count'] }} Jobs</span>
                                    </div>
                                    <div class="progress" style="height: 6px; background: #fee2e2;">
                                        <div class="progress-bar bg-danger" role="progressbar" 
                                             style="width: {{ ($hr['count'] / max(1, $hold_reasons_analytics->max('count'))) * 100 }}%"></div>
                                    </div>
                                </div>
                                @endforeach
                            @else
                                <div class="text-center py-4 text-muted">No Orders currently on hold</div>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="col-12">
                    <div class="card factory-card">
                        <div class="card-header bg-white pb-0 border-0">
                            <h5 class="fw-bold">Cylinder Agent Delays</h5>
                        </div>
                        <div class="card-body py-2">
                            @foreach($cylinder_delays as $agent)
                            <div class="mb-3 pointer p-1 rounded" onclick="showCylinderAgentDetail('{{ $agent['id'] }}', '{{ $agent['name'] }}', '{{ env('CYLINDER_LIMIT') }}')">
                                <div class="d-flex justify-content-between mb-1">
                                    <span class="fw-bold" style="font-size: 0.8rem;">{{ $agent['name'] }}</span>
                                    <span class="text-danger small fw-bold" style="font-size: 0.7rem;">{{ $agent['performance'] }}% Eff.</span>
                                </div>
                                <div class="progress" style="height: 6px;">
                                    <div class="progress-bar bg-{{ $agent['performance'] > 80 ? 'success' : ($agent['performance'] > 50 ? 'warning' : 'danger') }}" 
                                         role="progressbar" style="width: {{ $agent['performance'] }}%"></div>
                                </div>
                                <div class="d-flex justify-content-between mt-1">
                                    <small class="text-muted" style="font-size: 0.65rem;">Total: {{ $agent['total_jobs'] }} | <span class="text-danger">Late: {{ $agent['late_jobs'] }}</span></small>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Financial & Stock Alerts -->
    <div class="row">
        <div class="col-md-12 mb-4">
            <div class="card factory-card h-100">
                <div class="card-header bg-white pb-3 border-0 d-flex flex-wrap align-items-center">
                    <h5 class="fw-bold mb-0 text-danger me-4">Inventory Management & Alerts</h5>
                    
                    <!-- Color Legend (Integrated) -->
                    <div class="d-flex flex-wrap gap-3 align-items-center small me-auto pt-1">
                        <span class="d-flex align-items-center"><span class="rounded-circle bg-danger me-1" style="width: 8px; height: 8px;"></span> <b class="text-dark" style="font-size: 0.65rem;">Ink</b></span>
                        <span class="d-flex align-items-center"><span class="rounded-circle bg-primary me-1" style="width: 8px; height: 8px;"></span> <b class="text-dark" style="font-size: 0.65rem;">Fabric</b></span>
                        <span class="d-flex align-items-center"><span class="rounded-circle bg-success me-1" style="width: 8px; height: 8px;"></span> <b class="text-dark" style="font-size: 0.65rem;">Loop</b></span>
                        <span class="d-flex align-items-center"><span class="rounded-circle bg-warning me-1" style="width: 8px; height: 8px;"></span> <b class="text-dark" style="font-size: 0.65rem;">Dana</b></span>
                        <span class="d-flex align-items-center"><span class="rounded-circle bg-info me-1" style="width: 8px; height: 8px;"></span> <b class="text-dark" style="font-size: 0.65rem;">BOPP</b></span>
                    </div>

                    <div class="d-flex gap-2">
                        <select name="filter_stock_by" class="form-select form-select-sm" onchange="refreshDashboard()" style="width: auto; height: 32px; font-size: 0.70rem; border-color: #dee2e6;">
                            <option value="All" {{ request('filter_stock_by') == 'All' ? 'selected' : '' }}>Stock By (All)</option>
                            <option value="Fabric" {{ request('filter_stock_by') == 'Fabric' ? 'selected' : '' }}>Fabric</option>
                            <option value="Bopp" {{ request('filter_stock_by') == 'Bopp' ? 'selected' : '' }}>BOPP</option>
                            <option value="Ink" {{ request('filter_stock_by') == 'Ink' ? 'selected' : '' }}>Ink</option>
                            <option value="Dana" {{ request('filter_stock_by') == 'Dana' ? 'selected' : '' }}>Dana</option>
                            <option value="Loop" {{ request('filter_stock_by') == 'Loop' ? 'selected' : '' }}>Loop Color</option>
                        </select>
                        <select name="filter_stock_status" class="form-select form-select-sm" onchange="refreshDashboard()" style="width: auto; height: 32px; font-size: 0.70rem; border-color: #dee2e6;">
                            <option value="All" {{ request('filter_stock_status') == 'All' ? 'selected' : '' }}>Filter By (All)</option>
                            <option value="Over Stock" {{ request('filter_stock_status') == 'Over Stock' ? 'selected' : '' }}>Over Stock</option>
                            <option value="Low Stock" {{ request('filter_stock_status') == 'Low Stock' ? 'selected' : '' }}>Low Stock</option>
                            <option value="Zero Stock" {{ request('filter_stock_status') == 'Zero Stock' ? 'selected' : '' }}>Zero Stock</option>
                        </select>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <!-- Left Part: col-lg-9 Alerts -->
                        <div class="col-lg-9 border-end">
                            <h6 class="small text-muted fw-bold mb-3">Critical Alerts (Filtered View)</h6>
                            <div class="table-responsive" style="max-height: 480px; overflow-y: auto;">
                                <table class="table table-sm align-middle mb-0">
                                    <thead>
                                        <tr class="bg-light">
                                            <th class="small border-0 px-2 py-2">Item Name</th>
                                            <th class="small border-0 px-2 py-2 text-center">Status</th>
                                            <th class="small border-0 px-2 py-2 text-end">Stock Level</th>
                                            <th class="small border-0 px-2 py-2 text-end">Limit</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($stock_alerts as $alert)
                                        <tr class="hover-light">
                                            <td class="px-2">
                                                <div class="fw-bold text-dark" style="font-size: 0.85rem;">{{ $alert['name'] }}</div>
                                                <small class="text-muted" style="font-size: 0.65rem;">{{ $alert['type'] == 'Loop' ? 'Loop Color' : $alert['type'] }}</small>
                                            </td>
                                            <td class="text-center">
                                                @if($alert['status'] == 'Zero Stock')
                                                    <span class="status-badge bg-light-danger px-2" style="font-size: 0.65rem;">Zero Stock</span>
                                                @elseif($alert['status'] == 'Low Stock')
                                                    <span class="status-badge bg-light-warning px-2" style="font-size: 0.65rem;">Low Stock</span>
                                                @else
                                                    <span class="status-badge bg-light-info px-3" style="font-size: 0.65rem;">Over Stock</span>
                                                @endif
                                            </td>
                                            <td class="text-end fw-bold {{ $alert['status'] == 'Zero Stock' || $alert['status'] == 'Low Stock' ? 'text-danger' : 'text-primary' }}" style="font-size: 0.9rem;">
                                                {{ $alert['current'] }} <small class="fw-normal">{{ $alert['unit'] }}</small>
                                            </td>
                                            <td class="text-end text-muted px-2" style="font-size: 0.7rem;">
                                                @if($alert['status'] == 'Over Stock')
                                                    Max: {{ $alert['max'] }}
                                                @else
                                                    Min: {{ $alert['min'] }}
                                                @endif
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="4" class="text-center py-5 text-muted small">No items matching criteria.</td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        
                        <!-- Right Part: col-lg-3 Stock Out List -->
                        <div class="col-lg-3">
                            <h6 class="small text-danger fw-bold mb-3 text-center">Date Wise Stock Out (All Items)</h6>
                            <div class="stock-out-scroll" style="max-height: 480px; overflow-y: auto;">
                                @forelse($stock_out_list as $out)
                                @php
                                    $catColor = [
                                        'Ink' => 'danger',
                                        'Fabric' => 'primary',
                                        'Loop' => 'success',
                                        'Dana' => 'warning',
                                        'Bopp' => 'info'
                                    ][$out['type']] ?? 'secondary';
                                @endphp
                                <div class="d-flex align-items-center p-2 mb-2 rounded bg-light border-start border-{{ $catColor }} border-4 shadow-none hover-shadow">
                                    <div class="flex-grow-1">
                                        <div class="fw-bold text-dark" style="font-size: 0.75rem;">{{ $out['name'] }}</div>
                                        <small class="text-{{ $catColor }} fw-bold" style="font-size: 0.65rem;">
                                            {{ $out['date'] ? $out['date']->format('d-M-Y') : 'N/A' }} ({{ $out['type'] }})
                                        </small>
                                    </div>
                                    <span class="badge bg-{{ $catColor }} shadow-sm">{{ $out['qty'] }} {{ $out['unit'] }}</span>
                                </div>
                                @empty
                                <div class="text-center py-5 text-muted small">No transactions found for selected dates.</div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Performance Reports Row -->
    <div class="row">
        <!-- Top 10 Customers Card -->
        <div class="col-md-4 mb-4">
            <div class="card factory-card h-100 shadow-sm border-0 overflow-hidden" style="border-radius: 12px; border: 1px solid #eef0f3 !important;">
                <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold mb-0 d-flex align-items-center" style="color: #1d4ed8;"><i class="fas fa-users-crown me-2"></i>Top Customers</h6>
                </div>
                <div class="card-body p-0">
                    <table class="table table-sm table-hover mb-0 align-middle">
                        <thead class="bg-light bg-opacity-50">
                            <tr>
                                <th class="ps-3 py-2 small fw-bold text-muted border-0">Name</th>
                                <th class="text-center py-2 small fw-bold text-muted border-0">N/R</th>
                                <th class="pe-3 py-2 text-end small fw-bold text-muted border-0">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($customer_reports as $c)
                            <tr onclick="showCustomerDetail('{{ $c['id'] }}', '{{ addslashes($c['name']) }}')" style="cursor: pointer;">
                                <td class="ps-3 py-2">
                                    <div class="text-truncate fw-bold text-dark" style="max-width: 140px; font-size: 0.7rem;">{{ $c['name'] }}</div>
                                </td>
                                <td class="text-center py-2">
                                    <div class="d-flex justify-content-center gap-1">
                                        <span class="badge bg-success px-2 text-white" style="font-size: 0.65rem;">{{ $c['new'] }}</span>
                                        <span class="badge bg-primary px-2 text-white" style="font-size: 0.65rem;">{{ $c['repeat'] }}</span>
                                    </div>
                                </td>
                                <td class="pe-3 py-2 text-end">
                                    <span class="badge bg-dark px-2 text-white" style="font-size: 0.75rem;">{{ $c['total'] }}</span>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="3" class="text-center py-5 text-muted small">No data</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Top 10 Agents Card -->
        <div class="col-md-4 mb-4">
            <div class="card factory-card h-100 shadow-sm border-0 overflow-hidden" style="border-radius: 12px; border: 1px solid #eef0f3 !important;">
                <div class="card-header bg-white py-3 border-0">
                    <h6 class="fw-bold mb-0 d-flex align-items-center" style="color: #0891b2;"><i class="fas fa-handshake me-2"></i>Top Agents</h6>
                </div>
                <div class="card-body p-0">
                    <table class="table table-sm table-hover mb-0 align-middle">
                        <thead class="bg-light bg-opacity-50">
                            <tr>
                                <th class="ps-3 py-2 small fw-bold text-muted border-0">Agent/Agency</th>
                                <th class="text-center py-2 small fw-bold text-muted border-0">N/R</th>
                                <th class="pe-3 py-2 text-end small fw-bold text-muted border-0">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($agent_reports as $a)
                            <tr onclick="showAgentDetail('{{ $a['id'] }}', '{{ addslashes($a['name']) }}')" style="cursor: pointer;">
                                <td class="ps-3 py-2">
                                    <div class="text-truncate fw-bold text-dark" style="max-width: 140px; font-size: 0.7rem;">{{ $a['name'] }}</div>
                                </td>
                                <td class="text-center py-2">
                                    <div class="d-flex justify-content-center gap-1">
                                        <span class="badge bg-success px-2 text-white" style="font-size: 0.65rem;">{{ $a['new'] }}</span>
                                        <span class="badge bg-primary px-2 text-white" style="font-size: 0.65rem;">{{ $a['repeat'] }}</span>
                                    </div>
                                </td>
                                <td class="pe-3 py-2 text-end">
                                    <span class="badge bg-info px-2 py-1 text-white shadow-sm" style="font-size: 0.7rem; min-width: 30px;">{{ $a['total'] }}</span>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="3" class="text-center py-5 text-muted small">No data</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Top 10 Executives Card -->
        <div class="col-md-4 mb-4">
            <div class="card factory-card h-100 shadow-sm border-0 overflow-hidden" style="border-radius: 12px; border: 1px solid #eef0f3 !important;">
                <div class="card-header bg-white py-3 border-0">
                    <h6 class="fw-bold mb-0 d-flex align-items-center" style="color: #ea580c;"><i class="fas fa-chart-line me-2"></i>Sales Performance</h6>
                </div>
                <div class="card-body p-0">
                    <table class="table table-sm table-hover mb-0 align-middle">
                        <thead class="bg-light bg-opacity-50">
                            <tr>
                                <th class="ps-3 py-2 small fw-bold text-muted border-0">Executive</th>
                                <th class="text-center py-2 small fw-bold text-muted border-0">Cust/Agent</th>
                                <th class="pe-3 py-2 text-end small fw-bold text-muted border-0">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($executive_reports as $e)
                            <tr onclick="showExecutiveDetail('{{ $e['id'] }}', '{{ addslashes($e['name']) }}')" style="cursor: pointer;">
                                <td class="ps-3 py-2">
                                    <div class="text-truncate fw-bold text-dark" style="max-width: 140px; font-size: 0.7rem;">{{ $e['name'] }}</div>
                                </td>
                                <td class="text-center py-2">
                                    <div class="d-flex justify-content-center gap-1">
                                        <span class="badge bg-primary px-2 text-white" title="Customer Orders" style="font-size: 0.65rem;">{{ $e['cust'] }}</span>
                                        <span class="badge bg-info px-2 text-white" title="Agent Orders" style="font-size: 0.65rem;">{{ $e['agent'] }}</span>
                                    </div>
                                </td>
                                <td class="pe-3 py-2 text-end">
                                    <span class="badge bg-warning px-2 py-1 text-white shadow-sm" style="font-size: 0.7rem; min-width: 30px;">{{ $e['total'] }}</span>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="3" class="text-center py-5 text-muted small">No data</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    <!-- Financial Insights Row -->
    <div class="row">
        <!-- Overdue Bills Card -->
        <div class="col-md-4 mb-4">
            <div class="card factory-card h-100 shadow-sm border-0 overflow-hidden" style="border-radius: 12px; border: 1px solid #ffeded !important;">
                <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold mb-0 d-flex align-items-center" style="color: #dc2626;"><i class="fas fa-file-invoice-dollar me-2" style="font-size: 1.1rem;"></i>Overdue Bills</h6>
                    <span class="badge bg-danger text-white px-2 fw-bold" style="font-size: 0.65rem;">Due</span>
                </div>
                <div class="card-body p-0">
                    <table class="table table-sm table-hover mb-0 align-middle">
                        <thead style="background: #fff5f5;">
                            <tr>
                                <th class="ps-3 py-2 small fw-bold text-dark text-opacity-75 border-0">Customer / Bill</th>
                                <th class="text-end py-2 small fw-bold text-dark text-opacity-75 border-0">Amount</th>
                                <th class="pe-3 py-2 text-end small fw-bold text-dark text-opacity-75 border-0">Days</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($overdue_bills as $b)
                            <tr>
                                <td class="ps-3 py-2" style="cursor: pointer;" onclick="showLedgerModal('{{ $b['customer_id'] ?? 0 }}', '{{ addslashes($b['customer']) }}')">
                                    <div class="fw-bold text-dark" style="font-size: 0.82rem; text-decoration: underline; text-decoration-style: dotted;">{{ $b['customer'] }}</div>
                                    <div class="text-muted extra-small fw-bold" style="font-size: 0.65rem;">{{ $b['bill_no'] }}</div>
                                </td>
                                <td class="text-end py-2 fw-bold text-danger" style="font-size: 0.8rem;">₹{{ $indian_format($b['amount']) }}</td>
                                <td class="pe-3 py-2 text-end">
                                    @if($b['due_days'] > 0)
                                        <span class="badge bg-danger text-white fw-bold px-2 py-1" style="font-size: 0.7rem;">+{{ $b['due_days'] }}</span>
                                    @else
                                        <span class="badge bg-success text-white fw-bold px-2 py-1" style="font-size: 0.7rem;">Today</span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="3" class="text-center py-5 text-muted small fw-bold">All bills are clear</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Top Customer Payments Received Card -->
        <div class="col-md-4 mb-4">
            <div class="card factory-card h-100 shadow-sm border-0 overflow-hidden" style="border-radius: 12px; border: 1px solid #e8f5e9 !important;">
                <div class="card-header bg-white py-3 border-0">
                    <h6 class="fw-bold mb-0 d-flex align-items-center" style="color: #16a34a;"><i class="fas fa-hand-holding-usd me-2" style="font-size: 1.1rem;"></i>Top Payments Recd</h6>
                </div>
                <div class="card-body p-0">
                    <table class="table table-sm table-hover mb-0 align-middle">
                        <thead style="background: #f0fdf4;">
                            <tr>
                                <th class="ps-3 py-2 small fw-bold text-dark text-opacity-75 border-0">Customer</th>
                                <th class="pe-3 py-2 text-end small fw-bold text-dark text-opacity-75 border-0">Amount Received</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($top_payments as $p)
                            <tr class="table-row-hover">
                                <td class="py-2 ps-3" style="cursor: pointer;" onclick="showLedgerModal('{{ $p['customer_id'] ?? 0 }}', '{{ addslashes($p['customer']) }}')">
                                    <div class="fw-bold text-dark" style="font-size: 0.8rem; text-decoration: underline; text-decoration-style: dotted;">{{ $p['customer'] }}</div>
                                </td>
                                <td class="pe-3 py-2 text-end">
                                    <span class="badge bg-success text-white px-3 py-2 fw-bold shadow-sm" style="font-size: 0.8rem;">₹{{ $indian_format($p['amount']) }}</span>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="2" class="text-center py-5 text-muted small fw-bold">No payments in this period</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Top Outstanding Balances Card -->
        <div class="col-md-4 mb-4">
            <div class="card factory-card h-100 shadow-sm border-0 overflow-hidden" style="border-radius: 12px; border: 1px solid #f1f5f9 !important;">
                <div class="card-header bg-white py-3 border-0">
                    <h6 class="fw-bold mb-0 d-flex align-items-center" style="color: #334155;"><i class="fas fa-wallet me-2" style="font-size: 1.1rem;"></i>Top Due Amount</h6>
                </div>
                <div class="card-body p-0">
                    <table class="table table-sm table-hover mb-0 align-middle">
                        <thead style="background: #f8fafc;">
                            <tr>
                                <th class="ps-3 py-2 small fw-bold text-dark text-opacity-75 border-0">Customer</th>
                                <th class="pe-3 py-2 text-end small fw-bold text-dark text-opacity-75 border-0">Total Due</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($top_pending as $tp)
                            <tr>
                                <td class="py-2 ps-3" style="cursor: pointer;" onclick="showLedgerModal('{{ $tp['customer_id'] ?? 0 }}', '{{ addslashes($tp['customer']) }}')">
                                    <div class="fw-bold text-dark" style="font-size: 0.85rem; text-decoration: underline; text-decoration-style: dotted;">{{ $tp['customer'] }}</div>
                                </td>
                                <td class="pe-3 py-2 text-end">
                                    <span class="badge bg-secondary text-white px-3 py-2 fw-bold shadow-sm" style="background-color: #475569 !important; font-size: 0.8rem;">₹{{ $indian_format($tp['balance']) }}</span>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="2" class="text-center py-5 text-muted small fw-bold">No pending dues</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <!-- Payment Late (45 Days) Card -->
        <div class="col-md-4 mb-4">
            <div class="card factory-card h-100 shadow-sm border-0 overflow-hidden" style="border-radius: 12px; border: 1px solid #fff3e0 !important;">
                <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold mb-0 d-flex align-items-center" style="color: #f59e0b;">
                        <i class="fas fa-list-alt me-2" style="font-size: 1.1rem; color: #f59e0b !important; font-weight: 900 !important;"></i>
                        Payment Late (45 Days)
                    </h6>
                    <span class="badge bg-warning text-dark px-2 fw-bold" style="font-size: 0.65rem;">Critical</span>
                </div>
                <div class="card-body p-0">
                    <table class="table table-sm table-hover mb-0 align-middle">
                        <thead style="background: #fff8e1;">
                            <tr>
                                <th class="ps-3 py-2 small fw-bold text-dark text-opacity-75 border-0">Customer / Bill</th>
                                <th class="text-end py-2 small fw-bold text-dark text-opacity-75 border-0">Amount</th>
                                <th class="pe-3 py-2 text-end small fw-bold text-dark text-opacity-75 border-0">Days</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($payment_late_45 as $bl)
                            <tr class="table-row-hover">
                                <td class="py-3 ps-3" style="cursor: pointer;" onclick="showLedgerModal('{{ $bl['customer_id'] ?? 0 }}', '{{ addslashes($bl['customer']) }}')">
                                    <div class="fw-bold text-dark mb-1" style="font-size: 0.9rem; text-decoration: underline; text-decoration-style: dotted;">{{ $bl['customer'] }}</div>
                                    <div class="text-muted small fw-normal">{{ $bl['bill_no'] }}</div>
                                </td>
                                <td class="text-end py-2 fw-bold text-warning" style="color: #d97706 !important; font-size: 0.8rem;">₹{{ $indian_format($bl['amount']) }}</td>
                                <td class="pe-3 py-2 text-end">
                                    <span class="badge bg-warning text-dark fw-bold px-2 py-1" style="font-size: 0.7rem;">{{ $bl['days'] }} Days</span>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="3" class="text-center py-5 text-muted small fw-bold">No bills older than 45 days</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
