{{-- KPI Row 1 --}}
<div class="row">
    <div class="col-md-3 mb-4">
        <div class="card report-card bg-primary text-white" style="cursor: pointer;" onclick="window.location.href='{{ route('lead.agent_leads.index') }}'">
            <div class="card-body">
                <div class="metric-label text-white-50">Total Agent Leads</div>
                <div class="metric-value">{{ $total }}</div>
                <div class="mt-2 f-12"><i class="fa fa-info-circle"></i> Filtered results</div>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-4">
        <div class="card report-card bg-success text-white" style="cursor: pointer;" onclick="window.location.href='{{ route('lead.agent_leads.won') }}'">
            <div class="card-body">
                <div class="metric-label text-white-50">Converted (Won)</div>
                <div class="metric-value">{{ $wonCount }}</div>
                <div class="mt-2 f-12">Conversion Rate: {{ $total > 0 ? round(($wonCount/$total)*100, 1) : 0 }}%</div>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-4">
        <div class="card report-card bg-danger text-white" style="cursor: pointer;" onclick="window.location.href='{{ route('lead.agent_leads.lost') }}'">
            <div class="card-body">
                <div class="metric-label text-white-50">Lost Leads</div>
                <div class="metric-value">{{ $lostCount }}</div>
                <div class="mt-2 f-12">Loss Rate: {{ $total > 0 ? round(($lostCount/$total)*100, 1) : 0 }}%</div>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-4">
        <div class="card report-card bg-info text-white" style="cursor: pointer;" onclick="window.location.href='{{ route('lead.agent_leads.pending') }}'">
            <div class="card-body">
                <div class="metric-label text-white-50">In Progress (Pending)</div>
                <div class="metric-value">{{ $pendingCount }}</div>
                <div class="mt-2 f-12">Active pipeline enquiries</div>
            </div>
        </div>
    </div>
</div>

{{-- KPI Row 2 (Followups) --}}
<div class="row">
    <div class="col-md-6 mb-4">
        <div class="card report-card border-start border-info border-5" style="cursor: pointer;" onclick="window.location.href='{{ route('lead.followup.today') }}'">
            <div class="card-body p-3">
                <div class="metric-label">Followups On-Time</div>
                <div class="metric-value text-info">{{ $doneInTime }}</div>
                <div class="f-10 text-muted">Total Completed Followups</div>
            </div>
        </div>
    </div>
    <div class="col-md-6 mb-4">
        <div class="card report-card border-start border-danger border-5" style="cursor: pointer;" onclick="window.location.href='{{ route('lead.followup.pending') }}'">
            <div class="card-body p-3">
                <div class="metric-label">Followup Delays</div>
                <div class="metric-value text-danger">{{ $doneLate }}</div>
                <div class="f-10 text-muted">Followups done after due date</div>
            </div>
        </div>
    </div>
</div>

{{-- Graphs Area --}}
<style>
    .sk-chart-box { background: linear-gradient(90deg,#f0f0f0 25%,#e8e8e8 50%,#f0f0f0 75%); background-size: 200% 100%; animation: skpulse 1.4s ease-in-out infinite; border-radius: 8px; }
    @keyframes skpulse { 0%{background-position:200% 0} 100%{background-position:-200% 0} }
    .prog-bar-wrap { margin-bottom: 18px; }
    .prog-bar-label { display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px; }
    .prog-bar-label span { font-size: 12px; font-weight: 600; color: #444; }
    .prog-bar-label b { font-size: 13px; font-weight: 800; }
    .prog-bar-track { height: 10px; background: #f0f2ff; border-radius: 20px; overflow: hidden; }
    .prog-bar-fill { height: 10px; border-radius: 20px; transition: width 1s ease; }
</style>
<div class="row">
    {{-- Monthly Lead Analysis Chart --}}
    <div class="col-md-8 mb-4">
        <div class="card report-card h-100">
            <div class="card-header pb-0 d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-0">Monthly Agent Lead Analysis</h5>
                    <small class="text-muted">Stacked volume + conversion rate trend</small>
                </div>
                <small class="text-muted" id="chart-growth-status">Loading…</small>
            </div>
            <div class="card-body">
                <div id="chart-growth-skeleton" class="sk-chart-box" style="height:300px;"></div>
                <div id="conversionChart" style="display:none;"></div>
            </div>
        </div>
    </div>
    {{-- Performance Metrics Progress Bars --}}
    <div class="col-md-4 mb-4">
        <div class="card report-card h-100">
            <div class="card-header pb-0">
                <h5 class="mb-0">Performance Metrics</h5>
                <small class="text-muted">Key ratios at a glance</small>
            </div>
            <div class="card-body pt-3">
                @php
                    $winRate    = $total > 0 ? round(($wonCount / $total) * 100, 1) : 0;
                    $lossRate   = $total > 0 ? round(($lostCount / $total) * 100, 1) : 0;
                    $pendRate   = $total > 0 ? round(($pendingCount / $total) * 100, 1) : 0;
                    $otRate     = ($doneInTime + $doneLate) > 0 ? round(($doneInTime / ($doneInTime + $doneLate)) * 100, 1) : 0;
                    $bars = [
                        ['label' => 'Win Rate',            'value' => $winRate,     'color' => '#28a745', 'suffix' => '%'],
                        ['label' => 'Loss Rate',           'value' => $lossRate,    'color' => '#dc3545', 'suffix' => '%'],
                        ['label' => 'Still in Pipeline',   'value' => $pendRate,    'color' => '#007bff', 'suffix' => '%'],
                        ['label' => 'On-Time Followups',   'value' => $otRate,      'color' => '#17a2b8', 'suffix' => '%'],
                    ];
                @endphp
                @foreach($bars as $bar)
                <div class="prog-bar-wrap">
                    <div class="prog-bar-label">
                        <span>{{ $bar['label'] }}</span>
                        <b style="color:{{ $bar['color'] }}">{{ $bar['value'] }}{{ $bar['suffix'] }}</b>
                    </div>
                    <div class="prog-bar-track">
                        <div class="prog-bar-fill" style="width:{{ $bar['value'] }}%;background:{{ $bar['color'] }};"></div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

{{-- Breakdown Distributions --}}
<div class="row">
    <div class="col-md-6 mb-4">
        <div class="card report-card h-100">
            <div class="card-header pb-0"><h6>Step-wise Distribution</h6></div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <thead class="bg-light">
                        <tr><th class="ps-3">Status</th><th>Total</th><th>%</th></tr>
                    </thead>
                    <tbody>
                        @foreach($statuses as $st)
                        @php $count = $stepDistrib[$st->id] ?? 0; @endphp
                        @if($count > 0)
                        <tr>
                            <td class="ps-3"><i class="fa fa-circle me-1" style="color: {{ $st->color }}"></i> {{ $st->name }}</td>
                            <td>{{ $count }}</td>
                            <td>{{ $total > 0 ? round(($count/$total)*100, 1) : 0 }}%</td>
                        </tr>
                        @endif
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-md-6 mb-4">
        <div class="card report-card h-100">
            <div class="card-header pb-0"><h6>Agent Wise Distribution</h6></div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <thead class="bg-light">
                        <tr><th class="ps-3">Agent</th><th>Leads</th><th>%</th></tr>
                    </thead>
                    <tbody>
                        @foreach($agents as $ag)
                        @php $count = $agentDistrib[$ag->id] ?? 0; @endphp
                        @if($count > 0)
                        <tr>
                            <td class="ps-3">{{ $ag->name }}</td>
                            <td>{{ $count }}</td>
                            <td>{{ $total > 0 ? round(($count/$total)*100, 1) : 0 }}%</td>
                        </tr>
                        @endif
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- Leaderboard & Summary --}}
<div class="row mb-4">
    <div class="col-md-5">
        <div class="card h-100">
            <div class="card-header pb-0 d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-0" style="font-size:15px;">⭐ Star Performers</h5>
                    <small class="text-muted" style="font-size:11px;">{{ $perfLabel === 'All Time' ? 'All Time Rankings' : 'Period: '.$perfLabel }}</small>
                </div>
                <span class="badge badge-light-primary" style="font-size:10px;font-weight:600;">By Agent Conversions</span>
            </div>
            <div class="card-body">
                @forelse($leaderboard as $i => $staff)
                @php
                    $pos      = $i + 1;
                    $palettes = ['#7366ff','#51bb25','#f8a800','#ff5370','#4facfe'];
                    $color    = $palettes[$i] ?? '#888';
                    $initial  = strtoupper(substr($staff->name, 0, 1));
                @endphp
                <div class="d-flex align-items-center mb-3">
                    <div class="me-3 rounded-circle d-flex align-items-center justify-content-center fw-bold text-white flex-shrink-0"
                         style="width:38px;height:38px;background:{{ $color }};font-size:13px;">{{ $initial }}</div>
                    <div class="flex-grow-1">
                        <div class="fw-bold" style="font-size:13px;">{{ $staff->name }}</div>
                        <small class="text-muted">{{ $staff->agent_leads_count }} Agent Job{{ $staff->agent_leads_count != 1 ? 's' : '' }} Converted</small>
                    </div>
                    <div class="fw-bold" style="color:{{ $color }};font-size:16px;">#{{ $pos }}</div>
                </div>
                @empty
                <div class="text-center py-4 text-muted">
                    <i class="fa fa-trophy fa-2x mb-2 d-block"></i>
                    No conversions recorded for this period.
                </div>
                @endforelse
            </div>
        </div>
    </div>
    <div class="col-md-7">
        <div class="card h-100">
            <div class="card-header pb-0"><h6 class="mb-0">Agent Conversion Summary</h6></div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-6">
                        <div class="p-3 rounded" style="background:#f0f2ff;">
                            <div style="font-size:10px;color:#888;text-transform:uppercase;letter-spacing:.5px;font-weight:700;">Total Conversions</div>
                            <div style="font-size:28px;font-weight:900;color:#1a1a6e;line-height:1.2;">{{ $wonCount }}</div>
                            <small class="text-muted">Won agent leads in period</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="p-3 rounded" style="background:#f0fff4;">
                            <div style="font-size:10px;color:#888;text-transform:uppercase;letter-spacing:.5px;font-weight:700;">Conversion Rate</div>
                            <div style="font-size:28px;font-weight:900;color:#28a745;line-height:1.2;">{{ $total > 0 ? round(($wonCount/$total)*100,1) : 0 }}%</div>
                            <small class="text-muted">Of {{ $total }} agent leads</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="p-3 rounded" style="background:#fff8f0;">
                            <div style="font-size:10px;color:#888;text-transform:uppercase;letter-spacing:.5px;font-weight:700;">Top Closer</div>
                            <div style="font-size:15px;font-weight:800;color:#e97c00;margin-top:4px;line-height:1.3;">{{ $leaderboard->first()->name ?? '—' }}</div>
                            <small class="text-muted">{{ $leaderboard->first()->agent_leads_count ?? 0 }} agent conversions</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="p-3 rounded" style="background:#fff0f5;">
                            <div style="font-size:10px;color:#888;text-transform:uppercase;letter-spacing:.5px;font-weight:700;">Active Closers</div>
                            <div style="font-size:28px;font-weight:900;color:#dc3545;line-height:1.2;">{{ $leaderboard->count() }}</div>
                            <small class="text-muted">Users closing agent jobs</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Detailed Table --}}
<div class="card report-card" id="leads-table-container">
    <div class="card-header pb-0">
        <h6>Detailed Agent Lead Listing (Showing {{ $leads->count() }} of {{ $total }})</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped display" id="reportTable">
                <thead>
                    <tr>
                        <th>Lead No</th>
                        <th>Created</th>
                        <th>Job Name</th>
                        <th>Agent</th>
                        <th>Step</th>
                        <th>Assigned</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($leads as $l)
                    <tr>
                        <td><span class="badge badge-light-primary">{{ $l->lead_no }}</span></td>
                        <td>{{ $l->created_at->format('d-m-Y') }}</td>
                        <td><strong>{{ $l->name_of_job }}</strong></td>
                        <td>{{ $l->agent->name ?? 'N/A' }} <br> <small class="text-muted">{{ $l->agent->city ?? '' }}</small></td>
                        <td><span class="badge" style="background-color: {{ $l->status->color }}; color: #fff;">{{ $l->status->name }}</span></td>
                        <td>{{ $l->assignedUser->name ?? 'N/A' }}</td>
                        <td>
                            @if($l->status->slug == 'won')
                                <span class="text-success">Won</span>
                            @elseif($l->status->slug == 'lost')
                                <span class="text-danger">Lost</span>
                            @else
                                <span class="text-info">In Progress</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        
        <div class="mt-3 d-flex justify-content-center">
            {{ $leads->appends(request()->all())->links('pagination::bootstrap-4') }}
        </div>
    </div>
</div>

<script>
try { feather.replace(); } catch(e) {}

var CHARTS_URL = "{{ route('lead.agent_leads.report.charts') }}" + "{!! $filterQuery ? '?' . $filterQuery : '' !!}";
window.__agentChartGen = (window.__agentChartGen || 0) + 1;
var __myChartGen = window.__agentChartGen;

if (window.__agentChartXHR && window.__agentChartXHR.abort) {
    window.__agentChartXHR.abort();
    window.__agentChartXHR = null;
}

if (window.__agentChart && typeof window.__agentChart.destroy === 'function') {
    try { window.__agentChart.destroy(); } catch(e) {}
    window.__agentChart = null;
}

$('#conversionChart').empty().hide();
$('#chart-growth-skeleton').show();
$('#chart-growth-status').text('Loading…').removeClass('text-danger');

window.__agentChartXHR = $.ajax({
    url: CHARTS_URL,
    type: 'GET',
    dataType: 'json',
    success: function(data) {
        if (window.__agentChartGen !== __myChartGen) return;
        try {
            var months   = data.months    || [];
            var newLeads = data.newLeads  || [];
            var wonLeads = data.wonLeads  || [];
            var lostLeads = data.lostLeads || [];
            var pendingLeads = data.pendingLeads || [];
            
            var rateArr = newLeads.map(function(v, i) {
                return v > 0 ? Math.round((wonLeads[i] / v) * 100) : 0;
            });

            $('#chart-growth-skeleton').hide();
            $('#conversionChart').show();
            $('#chart-growth-status').text('');

            var chartObj = new ApexCharts(document.querySelector('#conversionChart'), {
                chart: { height: 310, type: 'bar', stacked: true, toolbar: { show: false } },
                colors: ['#51bb25', '#dc3545', '#007bff', '#7366ff'],
                series: [
                    { name: 'Won',     type: 'bar',  data: wonLeads  },
                    { name: 'Lost',    type: 'bar',  data: lostLeads    },
                    { name: 'Pending', type: 'bar',  data: pendingLeads },
                    { name: 'Conv. Rate %', type: 'line', data: rateArr }
                ],
                stroke: { width: [0, 0, 0, 3], curve: 'smooth' },
                plotOptions: { bar: { columnWidth: '50%', borderRadius: 4 } },
                dataLabels: { enabled: false },
                xaxis: { categories: months },
                yaxis: [
                    { min: 0, forceNiceScale: true, labels: { formatter: function(v){ return Math.round(v); } } },
                    { opposite: true, min: 0, max: 100, labels: { formatter: function(v){ return v + '%'; } } }
                ],
                tooltip: { shared: true },
                legend: { position: 'top', horizontalAlign: 'right' }
            });
            window.__agentChart = chartObj;
            chartObj.render();
        } catch(e) { $('#chart-growth-status').text('Error loading chart').addClass('text-danger'); }
    },
    error: function() { $('#chart-growth-status').text('Chart service error'); }
});
</script>
