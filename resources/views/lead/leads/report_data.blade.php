{{-- KPI Row 1 --}}
<div class="row">
    <div class="col-md-3 mb-4">
        <div class="card report-card bg-primary text-white" style="cursor: pointer;" onclick="window.location.href='{{ route('lead.index') }}'">
            <div class="card-body">
                <div class="metric-label text-white-50">Total Leads Found</div>
                <div class="metric-value">{{ $total }}</div>
                <div class="mt-2 f-12"><i class="fa fa-info-circle"></i> Filtered results</div>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-4">
        <div class="card report-card bg-success text-white" style="cursor: pointer;" onclick="window.location.href='{{ route('lead.won') }}'">
            <div class="card-body">
                <div class="metric-label text-white-50">Converted (Won)</div>
                <div class="metric-value">{{ $wonCount }}</div>
                <div class="mt-2 f-12">Conversion Rate: {{ $total > 0 ? round(($wonCount/$total)*100, 1) : 0 }}%</div>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-4">
        <div class="card report-card bg-danger text-white" style="cursor: pointer;" onclick="window.location.href='{{ route('lead.lost') }}'">
            <div class="card-body">
                <div class="metric-label text-white-50">Lost Leads</div>
                <div class="metric-value">{{ $lostCount }}</div>
                <div class="mt-2 f-12">Loss Rate: {{ $total > 0 ? round(($lostCount/$total)*100, 1) : 0 }}%</div>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-4">
        <div class="card report-card bg-info text-white" style="cursor: pointer;" onclick="window.location.href='{{ route('lead.pending') }}'">
            <div class="card-body">
                <div class="metric-label text-white-50">In Progress (Pending)</div>
                <div class="metric-value">{{ $pendingCount }}</div>
                <div class="mt-2 f-12">Active pipeline enquiries</div>
            </div>
        </div>
    </div>
</div>

{{-- KPI Row 2 (Repeat & Recovery) --}}
<div class="row">
    <div class="col-md-3 mb-4">
        <div class="card report-card border-start border-primary border-5" style="cursor: pointer;" onclick="window.location.href='{{ route('lead.repeat_suggestions') }}'">
            <div class="card-body p-3">
                <div class="metric-label">Repeat Leads</div>
                <div class="metric-value text-primary">{{ $repeatLead }}</div>
                <div class="f-10 text-muted">Repeat Won: {{ $repeatWon }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-4">
        <div class="card report-card border-start border-warning border-5" style="cursor: pointer;" onclick="window.location.href='{{ route('lead.index') }}'">
            <div class="card-body p-3">
                <div class="metric-label">Recovered Lost</div>
                <div class="metric-value text-warning">{{ $recoverLead }}</div>
                <div class="f-10 text-muted">Recovered Won: {{ $recoverWon }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-4">
        <div class="card report-card border-start border-info border-5" style="cursor: pointer;" onclick="window.location.href='{{ route('lead.followup.today') }}'">
            <div class="card-body p-3">
                <div class="metric-label">Followups On-Time</div>
                <div class="metric-value text-info">{{ $doneInTime }}</div>
                <div class="f-10 text-muted">Total Completed Followups</div>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-4">
        <div class="card report-card border-start border-danger border-5" style="cursor: pointer;" onclick="window.location.href='{{ route('lead.followup.pending') }}'">
            <div class="card-body p-3">
                <div class="metric-label">Delayed Followups</div>
                <div class="metric-value text-danger">{{ $doneLate }}</div>
                <div class="f-10 text-muted">Followups done after due date</div>
            </div>
        </div>
    </div>
</div>

{{-- Graphs Area — Option B (Stacked Bar + Line) + Option D (Progress Bars) --}}
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
    {{-- Option B: Stacked Bar + Conversion Rate Line --}}
    <div class="col-md-8 mb-4">
        <div class="card report-card h-100">
            <div class="card-header pb-0 d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-0">Monthly Lead Analysis</h5>
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
    {{-- Option D: Horizontal Progress Bars (rendered instantly from PHP) --}}
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
                    $repeatRate = $repeatLead > 0 ? round(($repeatWon / $repeatLead) * 100, 1) : 0;
                    $recoverRate= $recoverLead > 0 ? round(($recoverWon / $recoverLead) * 100, 1) : 0;
                    $bars = [
                        ['label' => 'Win Rate',            'value' => $winRate,     'color' => '#28a745', 'suffix' => '%'],
                        ['label' => 'Loss Rate',           'value' => $lossRate,    'color' => '#dc3545', 'suffix' => '%'],
                        ['label' => 'Still in Pipeline',   'value' => $pendRate,    'color' => '#007bff', 'suffix' => '%'],
                        ['label' => 'On-Time Followups',   'value' => $otRate,      'color' => '#17a2b8', 'suffix' => '%'],
                        ['label' => 'Repeat Win Rate',     'value' => $repeatRate,  'color' => '#fd7e14', 'suffix' => '%'],
                        ['label' => 'Recovery Win Rate',   'value' => $recoverRate, 'color' => '#6f42c1', 'suffix' => '%'],
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


{{-- Category Breakdown Tables — using SQL GROUP BY data (instantaneous) --}}
<div class="row">
    <div class="col-md-4 mb-4">
        <div class="card report-card h-100">
            <div class="card-header pb-0"><h6>Step-wise Distribution</h6></div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <thead class="bg-light">
                        <tr><th class="ps-3">Status</th><th>Total</th><th>%</th></tr>
                    </thead>
                    <tbody>
                        @foreach($statuses as $st)
                        @php $count = $stageDistrib[$st->id] ?? 0; @endphp
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
    <div class="col-md-4 mb-4">
        <div class="card report-card h-100">
            <div class="card-header pb-0"><h6>Source Performance</h6></div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <thead class="bg-light">
                        <tr><th class="ps-3">Source</th><th>Leads</th><th>Won</th></tr>
                    </thead>
                    <tbody>
                        @foreach($sources as $sc)
                        @php
                            $scount = $sourceDistrib[$sc->id] ?? 0;
                            $swon   = $sourceWon[$sc->id] ?? 0;
                        @endphp
                        @if($scount > 0)
                        <tr>
                            <td class="ps-3">{{ $sc->name }}</td>
                            <td>{{ $scount }}</td>
                            <td><span class="text-success">{{ $swon }}</span></td>
                        </tr>
                        @endif
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-4">
        <div class="card report-card h-100">
            <div class="card-header pb-0"><h6>Staff Contribution</h6></div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <thead class="bg-light">
                        <tr><th class="ps-3">User</th><th>Added</th><th>Assigned</th></tr>
                    </thead>
                    <tbody>
                        @foreach($users as $u)
                        @php
                            $added    = $addedDistrib[$u->id] ?? 0;
                            $assigned = $assignDistrib[$u->id] ?? 0;
                        @endphp
                        @if($added > 0 || $assigned > 0)
                        <tr>
                            <td class="ps-3">{{ $u->name }}</td>
                            <td>{{ $added }}</td>
                            <td>{{ $assigned }}</td>
                        </tr>
                        @endif
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- ═══ Star Performers ═══ --}}
<div class="row mb-4">
    <div class="col-md-5">
        <div class="card h-100">
            <div class="card-header pb-0 d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-0" style="font-size:15px;">⭐ Star Performers</h5>
                    <small class="text-muted" style="font-size:11px;">{{ $perfLabel === 'All Time' ? 'All Time Rankings' : 'Period: '.$perfLabel }}</small>
                </div>
                <span class="badge badge-light-primary" style="font-size:10px;font-weight:600;">By Conversions</span>
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
                        <small class="text-muted">{{ $staff->leads_count }} Customer{{ $staff->leads_count != 1 ? 's' : '' }} Converted</small>
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
            <div class="card-header pb-0"><h6 class="mb-0">Conversion Summary</h6></div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-6">
                        <div class="p-3 rounded" style="background:#f0f2ff;">
                            <div style="font-size:10px;color:#888;text-transform:uppercase;letter-spacing:.5px;font-weight:700;">Total Conversions</div>
                            <div style="font-size:28px;font-weight:900;color:#1a1a6e;line-height:1.2;">{{ $wonCount }}</div>
                            <small class="text-muted">Won leads in period</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="p-3 rounded" style="background:#f0fff4;">
                            <div style="font-size:10px;color:#888;text-transform:uppercase;letter-spacing:.5px;font-weight:700;">Conversion Rate</div>
                            <div style="font-size:28px;font-weight:900;color:#28a745;line-height:1.2;">{{ $total > 0 ? round(($wonCount/$total)*100,1) : 0 }}%</div>
                            <small class="text-muted">Of {{ $total }} leads</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="p-3 rounded" style="background:#fff8f0;">
                            <div style="font-size:10px;color:#888;text-transform:uppercase;letter-spacing:.5px;font-weight:700;">Top Converter</div>
                            <div style="font-size:15px;font-weight:800;color:#e97c00;margin-top:4px;line-height:1.3;">{{ $leaderboard->first()->name ?? '—' }}</div>
                            <small class="text-muted">{{ $leaderboard->first()->leads_count ?? 0 }} conversions</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="p-3 rounded" style="background:#fff0f5;">
                            <div style="font-size:10px;color:#888;text-transform:uppercase;letter-spacing:.5px;font-weight:700;">Active Converters</div>
                            <div style="font-size:28px;font-weight:900;color:#dc3545;line-height:1.2;">{{ $leaderboard->count() }}</div>
                            <small class="text-muted">Users with conversions</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Toggle View & Advanced Table --}}
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5>Lead Repository Analysis</h5>
    <div class="btn-group btn-group-sm">
        <button class="btn btn-outline-primary active" onclick="toggleView('table', this)">Table View</button>
        <button class="btn btn-outline-primary" onclick="toggleView('cards', this)">Card View</button>
    </div>
</div>

{{-- Card View Container --}}
<div id="leads-cards-container" style="display: none;">
    <div class="row">
        @forelse($leads as $l)
        <div class="col-md-4 mb-3">
            <div class="card report-card p-3 shadow-sm border h-100">
                <div class="d-flex justify-content-between mb-2">
                    <span class="badge badge-light-primary">{{ $l->lead_no }}</span>
                    <span class="badge" style="background-color: {{ $l->status->color }}; color: #fff;">{{ $l->status->name }}</span>
                </div>
                <h6 class="mb-1 text-primary">{{ $l->name }}</h6>
                <p class="mb-2 f-12 text-muted"><i class="fa fa-map-marker"></i> {{ $l->city }}, {{ $l->state }}</p>
                <div class="d-flex justify-content-between f-11">
                    <span>Source: {{ $l->source->name ?? 'N/A' }}</span>
                    <span>Date: {{ $l->created_at->format('d M') }}</span>
                </div>
            </div>
        </div>
        @empty
        <div class="col-12 text-center text-muted">No records found.</div>
        @endforelse
    </div>
</div>

{{-- Datatable Container --}}
<div id="leads-table-container">
    <div class="card report-card">
        <div class="card-header pb-0 d-flex justify-content-between align-items-center">
            <h6>Detailed Lead Listing (Showing {{ $leads->count() }} of {{ $total }})</h6>
                <a id="table-pdf-export-btn" href="{{ route('lead.report.pdf.simple') }}{{ $filterQuery ? '?'.$filterQuery : '' }}" target="_blank"
                   style="display:inline-flex;align-items:center;gap:7px;background:linear-gradient(135deg,#51bb25,#4facfe);color:#fff;padding:7px 16px;border-radius:8px;font-size:12px;font-weight:600;text-decoration:none;box-shadow:0 4px 15px rgba(81,187,37,0.35);">
                    <i class="fa fa-table"></i>
                    Table PDF
                </a>
                <a id="pdf-export-btn" href="{{ route('lead.report.pdf') }}{{ $filterQuery ? '?'.$filterQuery : '' }}" target="_blank"
                   style="display:inline-flex;align-items:center;gap:7px;background:linear-gradient(135deg,#7366ff,#4facfe);color:#fff;padding:7px 16px;border-radius:8px;font-size:12px;font-weight:600;text-decoration:none;box-shadow:0 4px 15px rgba(115,102,255,0.35);">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                        <polyline points="14 2 14 8 20 8"></polyline>
                        <line x1="12" y1="18" x2="12" y2="12"></line>
                        <line x1="9" y1="15" x2="15" y2="15"></line>
                    </svg>
                    Export PDF
                </a>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped display" id="reportTable">
                    <thead>
                        <tr>
                            <th>Lead No</th>
                            <th>Date</th>
                            <th>Name</th>
                            <th>Source</th>
                            <th>Tags</th>
                            <th>Response Time</th>
                            <th>Lead Step</th>
                            <th>Quotation</th>
                            <th>Won/Lost</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($leads as $l)
                        @php
                            $statusSlug = $l->status->slug ?? '';
                            $bgClass = '';
                            if ($statusSlug === 'won') $bgClass = 'table-success';
                            elseif ($statusSlug === 'lost') $bgClass = 'table-danger';

                            // Show 'Yes' if reached Quotation, Negotiation, or Won
                            $hasQuotation = in_array($statusSlug, ['quotation', 'negotiation', 'won']);
                        @endphp
                        <tr class="{{ $bgClass }}">
                            <td><span class="badge badge-light-primary">{{ $l->lead_no }}</span></td>
                            <td>{{ $l->created_at->format('d M, Y') }}</td>
                            <td><strong>{{ $l->name }}</strong></td>
                            <td>{{ $l->source->name ?? '—' }}</td>
                            <td>
                                @foreach($l->tags as $tag)
                                    <span class="badge" style="background-color: {{ $tag->color }}; color: #fff; font-size: 9px;">{{ $tag->name }}</span>
                                @endforeach
                            </td>
                            <td>—</td>
                            <td><span class="badge" style="background-color: {{ $l->status->color ?? '#666' }}; color: #fff;">{{ $l->status->name ?? '—' }}</span></td>
                            <td>{{ $hasQuotation ? 'Yes' : 'No' }}</td>
                            <td>
                                @if($statusSlug == 'won')
                                    <span class="fw-bold">Won</span>
                                @elseif($statusSlug == 'lost')
                                    <span class="fw-bold">Lost</span>
                                @else
                                    <span class="text-info">Pending</span>
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
</div>

<script>
try { feather.replace(); } catch(e) {}

// ── Load charts asynchronously (deferred — does not slow down table/KPI render) ──
var CHARTS_URL = "{{ route('lead.report.charts') }}" + "{!! $filterQuery ? '?' . $filterQuery : '' !!}";

// ── Generation counter: discard stale AJAX responses ──
window.__leadChartGen = (window.__leadChartGen || 0) + 1;
var __myChartGen = window.__leadChartGen;

// ── Abort any in-flight chart XHR ──
if (window.__leadChartXHR && window.__leadChartXHR.abort) {
    window.__leadChartXHR.abort();
    window.__leadChartXHR = null;
}

// ── Destroy any previously rendered chart ──
if (window.__leadChart && typeof window.__leadChart.destroy === 'function') {
    try { window.__leadChart.destroy(); } catch(e) {}
    window.__leadChart = null;
}
$('#conversionChart').empty().hide();
$('#chart-growth-skeleton').show();
$('#chart-growth-status').text('Loading…').removeClass('text-danger');

var LEAD_CSRF = '{{ csrf_token() }}';
window.__leadChartXHR = $.ajax({
    url: CHARTS_URL,
    type: 'GET',
    dataType: 'json',
    headers: {
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-TOKEN': LEAD_CSRF
    },
    success: function(data) {
        // Discard if a newer filter change already started a newer request
        if (window.__leadChartGen !== __myChartGen) return;
        try {
            var months   = Object.values(data.months    || []);
            var newLeads = Object.values(data.newLeads  || []).map(Number);
            var wonLeads = Object.values(data.wonLeads  || []).map(Number);

            // Derive lost/pending from AJAX data (approximated as difference)
            var lostArr    = Object.values(data.lostLeads   || []).map(Number);
            var pendingArr = Object.values(data.pendingLeads|| []).map(Number);

            // Fallback: if no split data, just show total vs won
            if (!lostArr.length)    lostArr    = newLeads.map(function(v,i){ return 0; });
            if (!pendingArr.length) pendingArr = newLeads.map(function(v,i){ return 0; });

            // Conversion rate line (won / total * 100)
            var rateArr = newLeads.map(function(v, i) {
                return v > 0 ? Math.round((wonLeads[i] / v) * 100) : 0;
            });

            $('#chart-growth-skeleton').hide();
            $('#conversionChart').show();
            $('#chart-growth-status').text('');

            var chartObj = new ApexCharts(document.querySelector('#conversionChart'), {
                chart: {
                    height: 310,
                    type: 'bar',
                    stacked: true,
                    toolbar: { show: false }
                },
                colors: ['#51bb25', '#dc3545', '#007bff', '#7366ff'],
                series: [
                    { name: 'Won',     type: 'bar',  data: wonLeads  },
                    { name: 'Lost',    type: 'bar',  data: lostArr    },
                    { name: 'Pending', type: 'bar',  data: pendingArr },
                    { name: 'Conv. Rate %', type: 'line', data: rateArr }
                ],
                stroke: { width: [0, 0, 0, 3], curve: 'smooth' },
                plotOptions: { bar: { columnWidth: '50%', borderRadius: 4 } },
                dataLabels: { enabled: false },
                xaxis: { categories: months },
                yaxis: [
                    {
                        title: { text: 'Leads', style: { color: '#888', fontSize: '11px' } },
                        min: 0,
                        forceNiceScale: true,
                        labels: { formatter: function(v){ return Math.round(v); } }
                    },
                    {
                        opposite: true,
                        title: { text: 'Conv. Rate %', style: { color: '#7366ff', fontSize: '11px' } },
                        min: 0, max: 100,
                        labels: { formatter: function(v){ return v + '%'; }, style: { colors: '#7366ff' } }
                    }
                ],
                tooltip: {
                    shared: true,
                    y: {
                        formatter: function(val, opts) {
                            return opts.seriesIndex === 3 ? val + '%' : Math.round(val) + ' leads';
                        }
                    }
                },
                legend: { position: 'top', horizontalAlign: 'right', fontSize: '12px' }
            });
            window.__leadChart = chartObj;
            chartObj.render();

        } catch(e) {
            console.error('Chart render error:', e);
            $('#chart-growth-skeleton').hide();
            $('#chart-growth-status').text('Chart error: ' + e.message).addClass('text-danger');
        }
    },
    error: function() {
        $('#chart-growth-skeleton').hide();
        $('#chart-growth-status').text('Chart unavailable').addClass('text-muted');
    }
});
</script>
