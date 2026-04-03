@extends('layouts.admin.app')

@section('title', 'Agent Lead Management Dashboard')

@section('breadcrumb-items')
    <li class="breadcrumb-item">Agent Lead</li>
    <li class="breadcrumb-item active">Dashboard</li>
@endsection

@section('css')
<style>
    /* Skeleton pulse loader */
    .skeleton { background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%); background-size: 200% 100%; animation: pulse 1.4s ease-in-out infinite; border-radius: 6px; }
    @keyframes pulse { 0% { background-position: 200% 0; } 100% { background-position: -200% 0; } }
    .sk-line { height: 14px; margin-bottom: 10px; }
    .sk-line.wide { width: 100%; }
    .sk-line.half { width: 55%; }
    .sk-line.short { width: 30%; }
    .sk-avatar { width: 36px; height: 36px; border-radius: 50%; flex-shrink: 0; }
    .sk-chart { width: 100%; height: 280px; }
    .stat-card-val { font-size: 2rem; font-weight: 700; line-height: 1; }
    .neglect-alert { display: none; }
    .hover-clickable { cursor: pointer; transition: transform 0.2s; }
    .hover-clickable:hover { transform: scale(1.02); box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
</style>
@endsection

@section('content')
<div class="container-fluid">

    {{-- ═══════════ FAST STAT CARDS (render immediately) ═══════════ --}}
    <div class="row widget-grid mb-2">
        <div class="col-md-3 col-6">
            <div class="card small-widget hover-clickable" onclick="window.location.href='{{ route('lead.agent_leads.index') }}?date=today'">
                <div class="card-body primary">
                    <span class="f-light">Today New Agent Lead</span>
                    <div class="d-flex align-items-end gap-1">
                        <h4>{{ $todayNew }}</h4>
                    </div>
                    <div class="bg-gradient font-primary"><i data-feather="plus-circle"></i></div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card small-widget hover-clickable" onclick="window.location.href='{{ route('lead.followup.pending') }}'">
                <div class="card-body warning">
                    <span class="f-light">Agent Pending Followups</span>
                    <div class="d-flex align-items-end gap-1">
                        <h4>{{ $todayFollowupPending }}</h4>
                    </div>
                    <div class="bg-gradient font-warning"><i data-feather="calendar"></i></div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card small-widget hover-clickable" onclick="window.location.href='{{ route('lead.agent_leads.index') }}'">
                <div class="card-body success">
                    <span class="f-light">Total Agent Leads</span>
                    <div class="d-flex align-items-end gap-1">
                        <h4>{{ $totalLeads }}</h4>
                    </div>
                    <div class="bg-gradient font-success"><i data-feather="users"></i></div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card small-widget hover-clickable" onclick="window.location.href='{{ route('lead.agent_leads.repeat_suggestions') }}'">
                <div class="card-body danger">
                    <span class="f-light">Agent Repeat Candidates</span>
                    <div class="d-flex align-items-end gap-1">
                        <h4>{{ $repeatSuggestionCount }}</h4>
                    </div>
                    <div class="bg-gradient font-danger"><i data-feather="repeat"></i></div>
                </div>
            </div>
        </div>
    </div>

    {{-- ═══════════ NEGLECT ALERT (injected by AJAX) ═══════════ --}}
    <div id="neglect-alert-wrapper"></div>

    <div class="row">
        {{-- Main Column --}}
        <div class="col-xl-8">

            {{-- Chart --}}
            <div class="card">
                <div class="card-header pb-0 d-flex justify-content-between align-items-center">
                    <h5>Weekly Agent Sales Momentum</h5>
                    <small class="text-muted" id="chart-status">Loading…</small>
                </div>
                <div class="card-body p-0">
                    {{-- Skeleton --}}
                    <div id="chart-skeleton" class="px-3 pt-3">
                        <div class="skeleton sk-chart"></div>
                    </div>
                    <div id="weeklyTrendChart" style="display:none;"></div>
                </div>
            </div>

            {{-- Today Won / Lost mini row —loaded via AJAX --}}
            <div class="row mb-3" id="today-win-loss-row" style="display:none;">
                <div class="col-6">
                    <div class="card small-widget mb-0 hover-clickable" onclick="window.location.href='{{ route('lead.agent_leads.won') }}?date=today'">
                        <div class="card-body success py-3">
                            <span class="f-light">Today Converted</span>
                            <div class="d-flex align-items-end gap-1">
                                <h4 id="today-won-val">0</h4>
                            </div>
                            <div class="bg-gradient font-success"><i data-feather="smile"></i></div>
                        </div>
                    </div>
                </div>
                <div class="col-6">
                    <div class="card small-widget mb-0 hover-clickable" onclick="window.location.href='{{ route('lead.agent_leads.lost') }}?date=today'">
                        <div class="card-body danger py-3">
                            <span class="f-light">Today Lost</span>
                            <div class="d-flex align-items-end gap-1">
                                <h4 id="today-lost-val">0</h4>
                            </div>
                            <div class="bg-gradient font-danger"><i data-feather="frown"></i></div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Pending Followups --}}
            <div class="card">
                <div class="card-header pb-0 d-flex justify-content-between">
                    <h5>Today & Pending Agent Followups</h5>
                    <a href="{{ route('lead.followup.pending') }}" class="btn btn-xs btn-outline-primary">View All</a>
                </div>
                <div class="card-body">
                    <div id="followups-skeleton">
                        @for($i=0;$i<4;$i++)
                        <div class="d-flex align-items-center mb-3 gap-2">
                            <div class="skeleton sk-avatar"></div>
                            <div class="flex-grow-1">
                                <div class="skeleton sk-line half"></div>
                                <div class="skeleton sk-line short"></div>
                            </div>
                        </div>
                        @endfor
                    </div>
                    <div id="followups-table" style="display:none;">
                        <div class="user-status table-responsive">
                            <table class="table table-bordernone" id="followups-tbody-table">
                                <thead>
                                    <tr>
                                        <th>Job Name</th>
                                        <th>Agent</th>
                                        <th>Step</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody id="followups-tbody"></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Repeat Suggestions --}}
            <div id="repeat-suggestions-wrapper"></div>

        </div>

        {{-- Sidebar Column --}}
        <div class="col-xl-4">

            {{-- Leaderboard --}}
            <div class="card">
                <div class="card-header pb-0 d-flex justify-content-between align-items-center">
                    <h5>🥇 Star Performers (Agent)</h5>
                    <span id="leaderboard-month-badge" class="badge badge-light-primary f-11" style="display:none;"></span>
                </div>
                <div class="card-body">
                    <div id="leaderboard-skeleton">
                        @for($i=0;$i<4;$i++)
                        <div class="d-flex align-items-center mb-3 gap-2">
                            <div class="skeleton sk-avatar"></div>
                            <div class="flex-grow-1">
                                <div class="skeleton sk-line half"></div>
                                <div class="skeleton sk-line short"></div>
                            </div>
                        </div>
                        @endfor
                    </div>
                    <div id="leaderboard-list" style="display:none;"></div>
                </div>
            </div>

        </div>
    </div>

</div>
@endsection

@section('script')
<script src="{{ asset('assets/js/chart/apex-chart/apex-chart.js') }}"></script>
<script>
const WIDGETS_URL = "{{ route('lead.agent_dashboard.widgets') }}";
const SHOW_URL = "{{ url('lead/agent-leads/show') }}";

$(document).ready(function() {
    // Safe feather — don't let it crash and block AJAX
    try { if (typeof feather !== 'undefined') feather.replace(); } catch(e) {}

    // Fetch all heavy widgets in ONE AJAX call
    $.ajax({
        url: WIDGETS_URL,
        type: 'GET',
        dataType: 'json',
        success: function(data) {
            try { renderChart(data.weeklyChart); } catch(e) { console.error('Chart error:', e); }
            try { renderFollowups(data.pendingFollowups); } catch(e) { console.error('Followups error:', e); }
            try { renderLeaderboard(data.leaderboard, data.leaderboardMonth); } catch(e) { console.error('Leaderboard error:', e); }
            try { renderRepeat(data.repeatSuggestions); } catch(e) { console.error('Repeat error:', e); }
            try { renderTodayWinLoss(data.todayWon, data.todayLost); } catch(e) { console.error('WinLoss error:', e); }
            if (data.neglectedCount > 0) try { renderNeglectAlert(data.neglectedCount); } catch(e) {}
            try { if (typeof feather !== 'undefined') feather.replace(); } catch(e) {}
        },
        error: function(xhr) {
            var msg = 'Error ' + xhr.status;
            try { var r = JSON.parse(xhr.responseText); if(r.message) msg += ': ' + r.message; } catch(e){}
            $('#chart-status').text(msg).addClass('text-danger');
            console.error('Widgets AJAX failed:', xhr.status, xhr.responseText.substring(0,200));
            $('#chart-skeleton, #followups-skeleton, #stage-skeleton, #leaderboard-skeleton').hide();
            $('#weeklyTrendChart').show().html('<p class="text-center text-muted py-4">Could not load chart data.</p>');
            $('#followups-table, #stage-stats, #leaderboard-list').show().html('<p class="text-center text-muted py-3">Could not load data.</p>');
        }
    });
});

function renderChart(weeklyChart) {
    var days = weeklyChart.map(w => w.day);
    var newData = weeklyChart.map(w => w.new);
    var wonData = weeklyChart.map(w => w.won);

    var opts = {
        chart: { height: 280, type: 'area', toolbar: { show: false } },
        dataLabels: { enabled: false },
        stroke: { curve: 'smooth', width: 2 },
        colors: ['#7366ff', '#51bb25'],
        series: [
            { name: 'New Agent Leads', data: newData },
            { name: 'Converted', data: wonData }
        ],
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
            var badge = pf.agent_lead && pf.agent_lead.status ? `<span class="badge" style="background:${pf.agent_lead.status.color || '#3c4b64'};color:#fff;">${pf.agent_lead.status.name}</span>` : '-';
            var name = pf.agent_lead ? pf.agent_lead.name_of_job : '-';
            var agent = (pf.agent_lead && pf.agent_lead.agent) ? pf.agent_lead.agent.name : '-';
            var leadId = pf.agent_lead_id;
            html += `<tr>
                <td><h6 class="mb-0">${name}</h6></td>
                <td>${agent}</td>
                <td>${badge}</td>
                <td><a href="${SHOW_URL}/${leadId}" class="btn btn-success btn-xs">Outreach</a></td>
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
        list.forEach(function(staff, i) {
            var initial = staff.name ? staff.name.charAt(0).toUpperCase() : '?';
            var pos     = i + 1;
            var colors  = ['#7366ff','#51bb25','#f8d62b','#ff5370','#4facfe'];
            var bg      = colors[i] || '#aaa';
            html += `<div class="d-flex align-items-center mb-3">
                <div class="me-3 rounded-circle d-flex align-items-center justify-content-center fw-bold text-white" style="width:38px;height:38px;background:${bg};font-size:13px;flex-shrink:0;">
                    ${initial}
                </div>
                <div class="flex-grow-1">
                    <h6 class="mb-0">${staff.name}</h6>
                    <small class="text-muted">${staff.agent_leads_count} Agent Lead${staff.agent_leads_count !== 1 ? 's' : ''} Converted</small>
                </div>
                <div class="fw-bold" style="color:${bg};font-size:15px;">#${pos}</div>
            </div>`;
        });
    }
    $('#leaderboard-skeleton').hide();
    $('#leaderboard-list').html(html).show();
}

function renderRepeat(list) {
    if (!list || !list.length) return;
    var rows = list.map(rs => {
        var completed = rs.complete_date ? new Date(rs.complete_date).toLocaleDateString('en-GB', { day:'2-digit', month:'short' }) : '-';
        var name = rs.agent_lead ? rs.agent_lead.name_of_job : '-';
        var jcNo = rs.job_card_no || '-';
        return `<tr>
            <td><h6 class="mb-0">${name}</h6><small class="text-muted">Order: ${jcNo}</small></td>
            <td><span class="text-success fw-bold">${completed}</span></td>
            <td><a href="${SHOW_URL}/${rs.agent_lead_id}" class="btn btn-primary btn-xs">Pitch Repeat</a></td>
        </tr>`;
    }).join('');

    $('#repeat-suggestions-wrapper').html(`
        <div class="card">
            <div class="card-header pb-0 d-flex justify-content-between">
                <h5>🔄 Agent Repeat Opportunity</h5>
                <a href="{{ route('lead.agent_leads.repeat_suggestions') }}" class="btn btn-xs btn-outline-success">View All</a>
            </div>
            <div class="card-body">
                <p class="text-muted f-12 mb-3">These jobs completed 10+ days ago. Time to check with agents for repeats!</p>
                <div class="table-responsive">
                    <table class="table table-bordernone">
                        <thead><tr><th>Job Name</th><th>Completed</th><th>Action</th></tr></thead>
                        <tbody>${rows}</tbody>
                    </table>
                </div>
            </div>
        </div>`);
}

function renderTodayWinLoss(won, lost) {
    $('#today-won-val').text(won || 0);
    $('#today-lost-val').text(lost || 0);
    $('#today-win-loss-row').show();
    feather && feather.replace();
}

function renderNeglectAlert(count) {
    $('#neglect-alert-wrapper').html(`
        <div class="alert alert-light-danger border-left-danger d-flex justify-content-between align-items-center mb-4">
            <div>
                <i class="fa fa-warning me-2 text-danger"></i>
                <strong>Action Required (Agent Leads):</strong> You have <span class="badge badge-danger">${count} agent leads</span> that haven't been contacted in over 3 days.
            </div>
            <a href="{{ route('lead.agent_leads.index') }}?filter=neglected" class="btn btn-danger btn-xs">View Neglected</a>
        </div>`);
}
</script>
<style>
    .border-left-danger { border-left: 5px solid #f73164 !important; }
</style>
@endsection
