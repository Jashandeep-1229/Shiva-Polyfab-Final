@extends('layouts.admin.app')

@section('title', 'Lead Management Dashboard')

@section('breadcrumb-items')
    <li class="breadcrumb-item active">Dashboard</li>
@endsection

@section('css')
<style>
    .sales-widget-card {
        transition: all 0.3s ease;
        border: none;
        border-radius: 15px;
        overflow: hidden;
    }
    .sales-widget-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.1);
    }
    .widget-icon-bg {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        flex-shrink: 0;
    }
    .bg-light-primary { background: rgba(115,102,255,0.1); color: #7366ff; }
    .bg-light-success { background: rgba(81,187,37,0.1); color: #51bb25; }
    .bg-light-warning { background: rgba(248,214,43,0.1); color: #c9a800; }
    .bg-light-danger  { background: rgba(220,53,69,0.1); color: #dc3545; }
    .bg-light-info    { background: rgba(0,150,136,0.1); color: #009688; }
    .skeleton { background: linear-gradient(90deg,#f0f0f0 25%,#e0e0e0 50%,#f0f0f0 75%); background-size:200% 100%; animation:pulse 1.4s ease-in-out infinite; border-radius:6px; }
    @keyframes pulse { 0%{background-position:200% 0}100%{background-position:-200% 0} }
    .sk-line { height:14px; margin-bottom:10px; }
    .sk-avatar { width:36px; height:36px; border-radius:50%; flex-shrink:0; }
    .sk-chart { width:100%; height:240px; }
</style>
@endsection

@section('content')
<div class="container-fluid">

    {{-- ══════ ROW 1: Combined stat cards ══════ --}}
    <div class="row widget-grid mb-3">
        {{-- Customer Leads Today --}}
        <div class="col-xl-3 col-md-6 mb-3">
            <a href="{{ route('lead.index') }}?date=today" class="text-decoration-none">
                <div class="card sales-widget-card shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <p class="mb-1 text-muted fw-bold">Today New</p>
                                <h3 class="mb-0">{{ $custTodayNew + $agentTodayNew }}</h3>
                                <small class="text-primary">Customer + Agent</small>
                            </div>
                            <div class="widget-icon-bg bg-light-primary">
                                <i data-feather="plus-circle"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        {{-- Pending Followups --}}
        <div class="col-xl-3 col-md-6 mb-3">
            <a href="{{ route('lead.followup.pending') }}" class="text-decoration-none">
                <div class="card sales-widget-card shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <p class="mb-1 text-muted fw-bold">Pending Followups</p>
                                <h3 class="mb-0">{{ $custPendingFollowups + $agentPendingFollowupsCount }}</h3>
                                <small class="text-warning">Due Today & Overdue</small>
                            </div>
                            <div class="widget-icon-bg bg-light-warning">
                                <i data-feather="calendar"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        {{-- Total Leads --}}
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card sales-widget-card shadow-sm" style="cursor:default;">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="mb-1 text-muted fw-bold">Total Pipeline</p>
                            <h3 class="mb-0">{{ $custTotalLeads + $agentTotalLeads }}</h3>
                            <small class="text-success">
                                <a href="{{ route('lead.index') }}" class="text-success">{{ $custTotalLeads }} Customer</a>
                                &nbsp;+&nbsp;
                                <a href="{{ route('lead.agent_leads.index') }}" class="text-success">{{ $agentTotalLeads }} Agent</a>
                            </small>
                        </div>
                        <div class="widget-icon-bg bg-light-success">
                            <i data-feather="users"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Repeat Candidates --}}
        <div class="col-xl-3 col-md-6 mb-3">
            <a href="{{ route('lead.repeat_suggestions') }}" class="text-decoration-none">
                <div class="card sales-widget-card shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <p class="mb-1 text-muted fw-bold">Repeat Candidates</p>
                                <h3 class="mb-0">{{ $repeatSuggestionCount }}</h3>
                                <small class="text-info">Ready for Repeat Pitch</small>
                            </div>
                            <div class="widget-icon-bg bg-light-info">
                                <i data-feather="repeat"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>
    </div>

    {{-- Neglect Alert (injected by AJAX) --}}
    <div id="neglect-alert-wrapper"></div>

    {{-- ══════ ROW 2: Chart + Leaderboard ══════ --}}
    <div class="row mb-3">
        <div class="col-xl-8">
            <div class="card sales-widget-card shadow-sm">
                <div class="card-header pb-0 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Weekly Activity</h5>
                    <small class="text-muted" id="chart-status">Loading…</small>
                </div>
                <div class="card-body p-0">
                    <div id="chart-skeleton" class="px-3 pt-3"><div class="skeleton sk-chart"></div></div>
                    <div id="weeklyTrendChart" style="display:none;"></div>
                </div>
            </div>
        </div>

        <div class="col-xl-4">
            {{-- Today Converted / Lost --}}
            <div class="row mb-3" id="today-win-loss-row">
                <div class="col-6">
                    <a href="{{ route('lead.won') }}?date=today" class="text-decoration-none">
                        <div class="card sales-widget-card shadow-sm mb-0">
                            <div class="card-body py-3">
                                <p class="mb-1 text-muted fw-bold small">Today Converted</p>
                                <h4 id="today-won-val" class="mb-0 text-success">0</h4>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col-6">
                    <a href="{{ route('lead.lost') }}?date=today" class="text-decoration-none">
                        <div class="card sales-widget-card shadow-sm mb-0">
                            <div class="card-body py-3">
                                <p class="mb-1 text-muted fw-bold small">Today Lost</p>
                                <h4 id="today-lost-val" class="mb-0 text-danger">0</h4>
                            </div>
                        </div>
                    </a>
                </div>
            </div>

            {{-- Leaderboard --}}
            <div class="card sales-widget-card shadow-sm">
                <div class="card-header pb-0 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">🥇 Star Performers</h5>
                    <span id="leaderboard-month-badge" class="badge badge-light-primary f-11" style="display:none;"></span>
                </div>
                <div class="card-body">
                    <div id="leaderboard-skeleton">
                        @for($i=0;$i<3;$i++)
                        <div class="d-flex align-items-center mb-3 gap-2">
                            <div class="skeleton sk-avatar"></div>
                            <div class="flex-grow-1">
                                <div class="skeleton sk-line" style="width:60%"></div>
                                <div class="skeleton sk-line" style="width:35%"></div>
                            </div>
                        </div>
                        @endfor
                    </div>
                    <div id="leaderboard-list" style="display:none;"></div>
                </div>
            </div>
        </div>
    </div>

    {{-- ══════ ROW 3: Pending Followups + Repeat Suggestions ══════ --}}
    <div class="row">
        <div class="col-xl-7">
            <div class="card sales-widget-card shadow-sm">
                <div class="card-header pb-0 d-flex justify-content-between">
                    <h5 class="mb-0">Today & Pending Followups</h5>
                    <a href="{{ route('lead.followup.pending') }}" class="btn btn-xs btn-outline-primary">View All</a>
                </div>
                <div class="card-body">
                    <div id="followups-skeleton">
                        @for($i=0;$i<4;$i++)
                        <div class="d-flex align-items-center mb-3 gap-2">
                            <div class="skeleton sk-avatar"></div>
                            <div class="flex-grow-1">
                                <div class="skeleton sk-line" style="width:55%"></div>
                                <div class="skeleton sk-line" style="width:30%"></div>
                            </div>
                        </div>
                        @endfor
                    </div>
                    <div id="followups-table" style="display:none;">
                        <div class="table-responsive">
                            <table class="table table-bordernone">
                                <thead><tr><th>Lead</th><th>Step</th><th>Due</th><th>Action</th></tr></thead>
                                <tbody id="followups-tbody"></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-5">
            <div id="repeat-suggestions-wrapper"></div>
        </div>
    </div>

</div>
@endsection

@section('script')
<script src="{{ asset('assets/js/chart/apex-chart/apex-chart.js') }}"></script>
<script>
const WIDGETS_URL = "{{ route('lead.dashboard.widgets') }}";
const SHOW_URL    = "{{ url('lead/leads/show') }}";

$(document).ready(function() {
    try { if (typeof feather !== 'undefined') feather.replace(); } catch(e) {}

    $.ajax({
        url: WIDGETS_URL,
        type: 'GET',
        dataType: 'json',
        success: function(data) {
            try { renderChart(data.weeklyChart); }       catch(e) { console.error('Chart:', e); }
            try { renderFollowups(data.pendingFollowups); } catch(e) { console.error('Followups:', e); }
            try { renderLeaderboard(data.leaderboard, data.leaderboardMonth); } catch(e) { console.error('Leaderboard:', e); }
            try { renderRepeat(data.repeatSuggestions); }  catch(e) { console.error('Repeat:', e); }
            try { renderTodayWinLoss(data.todayWon, data.todayLost); } catch(e) {}
            if (data.neglectedCount > 0) try { renderNeglectAlert(data.neglectedCount); } catch(e) {}
            try { if (typeof feather !== 'undefined') feather.replace(); } catch(e) {}
        },
        error: function(xhr) {
            $('#chart-status').text('Error ' + xhr.status).addClass('text-danger');
            $('#chart-skeleton, #followups-skeleton, #leaderboard-skeleton').hide();
            $('#weeklyTrendChart').show().html('<p class="text-center text-muted py-4">Could not load chart data.</p>');
        }
    });
});

function renderChart(weeklyChart) {
    var days    = weeklyChart.map(w => w.day);
    var newData = weeklyChart.map(w => w.new);
    var wonData = weeklyChart.map(w => w.won);
    var opts = {
        chart: { height: 240, type: 'area', toolbar: { show: false } },
        dataLabels: { enabled: false },
        stroke: { curve: 'smooth', width: 2 },
        colors: ['#7366ff', '#51bb25'],
        series: [{ name: 'New Leads', data: newData }, { name: 'Converted', data: wonData }],
        xaxis: { categories: days },
        yaxis: { show: true, min: 0 },
        grid: { show: true, borderColor: '#f1f1f1' },
        fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.4, opacityTo: 0.05 } }
    };
    $('#chart-skeleton').hide();
    $('#weeklyTrendChart').show();
    $('#chart-status').text('');
    new ApexCharts(document.querySelector('#weeklyTrendChart'), opts).render();
}

function renderFollowups(list) {
    var html = '';
    if (!list || list.length === 0) {
        html = '<tr><td colspan="4" class="text-center py-4 text-muted"><i class="fa fa-check-circle text-success fa-2x mb-2"></i><br>No pending followups today!</td></tr>';
    } else {
        list.forEach(function(pf) {
            var isPast = new Date(pf.followup_date) < new Date();
            var dateClass = isPast ? 'text-danger fw-bold' : 'text-primary';
            var badge = pf.lead && pf.lead.status ? `<span class="badge" style="background:${pf.lead.status.color||'#3c4b64'};color:#fff;">${pf.lead.status.name}</span>` : '-';
            var name  = pf.lead ? pf.lead.name : '-';
            var phone = pf.lead ? pf.lead.phone : '-';
            var dateStr = new Date(pf.followup_date).toLocaleString('en-GB', {day:'2-digit',month:'short',hour:'2-digit',minute:'2-digit'});
            html += `<tr>
                <td><h6 class="mb-0">${name}</h6><small class="text-muted">${phone}</small></td>
                <td>${badge}</td>
                <td><span class="${dateClass}">${dateStr}</span></td>
                <td><a href="${SHOW_URL}/${pf.lead_id}" class="btn btn-success btn-xs">Outreach</a></td>
            </tr>`;
        });
    }
    $('#followups-tbody').html(html);
    $('#followups-skeleton').hide();
    $('#followups-table').show();
}

function renderLeaderboard(list, month) {
    if (month) { $('#leaderboard-month-badge').text(month).show(); }
    var html = '';
    if (!list || !list.length) {
        html = '<p class="text-muted text-center f-13">No conversions this month yet.</p>';
    } else {
        var colors = ['#7366ff','#51bb25','#f8d62b','#ff5370','#4facfe'];
        list.forEach(function(staff, i) {
            var initial = staff.name ? staff.name.charAt(0).toUpperCase() : '?';
            var bg      = colors[i] || '#aaa';
            html += `<div class="d-flex align-items-center mb-3">
                <div class="me-3 rounded-circle d-flex align-items-center justify-content-center fw-bold text-white" style="width:38px;height:38px;background:${bg};font-size:13px;flex-shrink:0;">${initial}</div>
                <div class="flex-grow-1">
                    <h6 class="mb-0">${staff.name}</h6>
                    <small class="text-muted">${staff.leads_count} Converted</small>
                </div>
                <div class="fw-bold" style="color:${bg};font-size:15px;">#${i+1}</div>
            </div>`;
        });
    }
    $('#leaderboard-skeleton').hide();
    $('#leaderboard-list').html(html).show();
}

function renderRepeat(list) {
    if (!list || !list.length) return;
    var rows = list.map(rs => {
        var completed = rs.complete_date ? new Date(rs.complete_date).toLocaleDateString('en-GB',{day:'2-digit',month:'short'}) : '-';
        var type = rs.type === 'agent' ? '<span class="badge badge-light-warning">Agent</span>' : '<span class="badge badge-light-primary">Customer</span>';
        return `<tr>
            <td><h6 class="mb-0">${rs.name}</h6><small class="text-muted">Job: ${rs.job_card_no||'-'}</small></td>
            <td>${type}</td>
            <td><span class="text-success fw-bold">${completed}</span></td>
            <td><a href="${SHOW_URL}/${rs.lead_id}" class="btn btn-primary btn-xs">Pitch</a></td>
        </tr>`;
    }).join('');
    $('#repeat-suggestions-wrapper').html(`
        <div class="card sales-widget-card shadow-sm">
            <div class="card-header pb-0 d-flex justify-content-between">
                <h5 class="mb-0">🔄 Repeat Pipeline</h5>
                <a href="{{ route('lead.repeat_suggestions') }}" class="btn btn-xs btn-outline-success">View All</a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordernone">
                        <thead><tr><th>Name</th><th>Type</th><th>Completed</th><th>Action</th></tr></thead>
                        <tbody>${rows}</tbody>
                    </table>
                </div>
            </div>
        </div>`);
}

function renderTodayWinLoss(won, lost) {
    $('#today-won-val').text(won || 0);
    $('#today-lost-val').text(lost || 0);
    feather && feather.replace();
}

function renderNeglectAlert(count) {
    $('#neglect-alert-wrapper').html(`
        <div class="alert alert-light-danger border-left-danger d-flex justify-content-between align-items-center mb-3">
            <div><i class="fa fa-warning me-2 text-danger"></i>
                <strong>Action Required:</strong> You have <span class="badge badge-danger">${count} leads</span> not contacted in 3+ days.
            </div>
            <a href="{{ route('lead.index') }}?filter=neglected" class="btn btn-danger btn-xs">View Neglected</a>
        </div>`);
}
</script>
<style>
    .border-left-danger { border-left: 5px solid #f73164 !important; }
</style>
@endsection
