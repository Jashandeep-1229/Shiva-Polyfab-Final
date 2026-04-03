<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Lead Performance Report</title>
<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: DejaVu Sans, sans-serif; font-size: 9.5px; color: #1a1a2e; background: #fff; }

    /* ── Page wrapper ── */
    .page { padding: 22px 26px; }

    /* ── Header Band ── */
    .header-band { background-color: #1a1a6e; color: #fff; padding: 18px 22px 14px; border-radius: 8px; margin-bottom: 16px; }
    .header-band table { width: 100%; }
    .company-name { font-size: 18px; font-weight: 800; color: #fff; letter-spacing: 0.5px; }
    .company-sub  { font-size: 8px; color: #a8b4ff; text-transform: uppercase; letter-spacing: 1.5px; margin-top: 2px; }
    .report-title { font-size: 15px; font-weight: 800; color: #a8b4ff; text-align: right; }
    .report-sub   { font-size: 8px; color: rgba(255,255,255,0.6); text-align: right; margin-top: 3px; }
    .header-divider { border-top: 1px solid rgba(255,255,255,0.2); margin: 10px 0 8px; }
    .header-meta td { font-size: 8px; color: rgba(255,255,255,0.7); padding-right: 24px; }
    .header-meta td b { color: #fff; }

    /* ── Section Label ── */
    .section-label { font-size: 9px; font-weight: 800; text-transform: uppercase; letter-spacing: 1px; color: #7366ff; border-left: 3px solid #7366ff; padding-left: 7px; margin: 14px 0 8px; }

    /* ── KPI Cards (5-col table) ── */
    .kpi-table { width: 100%; border-collapse: separate; border-spacing: 6px; margin-bottom: 4px; }
    .kpi-cell { padding: 12px 10px; border-radius: 8px; text-align: left; }
    .kpi-cell .k-label { font-size: 7.5px; text-transform: uppercase; letter-spacing: 0.8px; color: rgba(255,255,255,0.8); font-weight: 700; }
    .kpi-cell .k-value { font-size: 26px; font-weight: 900; color: #fff; line-height: 1.1; margin: 3px 0 2px; }
    .kpi-cell .k-sub   { font-size: 7.5px; color: rgba(255,255,255,0.7); }
    .kpi-blue   { background-color: #5c6fd8; }
    .kpi-green  { background-color: #28a745; }
    .kpi-red    { background-color: #dc3545; }
    .kpi-amber  { background-color: #e97c00; }
    .kpi-violet { background-color: #7366ff; }

    /* ── Metrics Row ── */
    .metrics-table { width: 100%; border-collapse: separate; border-spacing: 6px; margin-bottom: 4px; }
    .metric-cell { background: #f8f9ff; border: 1px solid #e0e4f5; border-radius: 7px; padding: 9px 11px; }
    .metric-cell .m-label { font-size: 7px; text-transform: uppercase; letter-spacing: 0.7px; color: #888; font-weight: 700; }
    .metric-cell .m-value { font-size: 22px; font-weight: 900; color: #1a1a6e; line-height: 1.1; margin: 2px 0 1px; }
    .metric-cell .m-sub   { font-size: 7.5px; color: #aaa; }

    /* ── Breakdown Tables (3-col grid) ── */
    .breakdown-outer { width: 100%; border-collapse: separate; border-spacing: 6px; margin-bottom: 6px; }
    .breakdown-outer td { vertical-align: top; }
    .breakdown-box { border: 1px solid #dde2f5; border-radius: 8px; overflow: hidden; }
    .breakdown-box .bh { background-color: #1a1a6e; color: #fff; font-size: 8px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.7px; padding: 6px 10px; }
    .breakdown-box table { width: 100%; border-collapse: collapse; }
    .breakdown-box thead th { background: #f0f2ff; font-size: 7.5px; font-weight: 700; color: #555; text-transform: uppercase; padding: 5px 8px; text-align: left; border-bottom: 1px solid #d8dcf0; }
    .breakdown-box tbody td { font-size: 8px; padding: 5px 8px; color: #333; border-bottom: 1px solid #f0f3ff; }
    .breakdown-box tbody tr:last-child td { border-bottom: none; }
    .breakdown-box tbody tr:nth-child(even) td { background: #fafaff; }
    .dot { display: inline-block; width: 7px; height: 7px; border-radius: 50%; margin-right: 3px; }
    .won-color { color: #166534; font-weight: 700; }

    /* ── Star Performers ── */
    .star-band { background-color: #1a1a6e; border-radius: 8px; padding: 12px 16px 14px; margin-bottom: 12px; }
    .star-band-head { font-size: 12px; font-weight: 800; color: #fff; margin-bottom: 4px; }
    .star-band-sub { font-size: 8px; color: #a8b4ff; margin-bottom: 10px; }
    .star-table { width: 100%; border-collapse: separate; border-spacing: 6px; }
    .star-cell { background-color: #2a2a8e; border-radius: 7px; padding: 9px 12px; border: 1px solid rgba(255,255,255,0.1); }
    .star-pos { font-size: 16px; font-weight: 900; color: #a8b4ff; float: right; }
    .star-initial { display: inline-block; width: 28px; height: 28px; border-radius: 50%; text-align: center; line-height: 28px; font-size: 12px; font-weight: 800; color: #fff; margin-bottom: 5px; }
    .star-name { font-size: 9.5px; font-weight: 800; color: #fff; margin-bottom: 2px; white-space: nowrap; overflow: hidden; }
    .star-count { font-size: 7.5px; color: #a8b4ff; }

    /* ── Main Lead Table ── */
    .leads-header { background-color: #1a1a6e; color: #fff; border-radius: 8px 8px 0 0; padding: 9px 14px; overflow: hidden; }
    .leads-header-title { font-size: 11px; font-weight: 800; display: inline; }
    .leads-header-count { font-size: 8px; color: #a8b4ff; float: right; background: rgba(255,255,255,0.1); padding: 2px 8px; border-radius: 20px; margin-top: 1px; }
    .leads-table { width: 100%; border-collapse: collapse; border: 1px solid #dde2f5; border-top: none; }
    .leads-table thead th { background: #f0f2ff; font-size: 7.5px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.4px; color: #444; padding: 6px 7px; text-align: left; border-bottom: 2px solid #c5caf0; }
    .leads-table tbody td { font-size: 8px; padding: 5px 7px; color: #333; border-bottom: 1px solid #f0f3ff; vertical-align: middle; }
    .leads-table tbody tr:nth-child(even) td { background: #fafaff; }
    .leads-table tbody tr:last-child td { border-bottom: none; }

    /* Badges */
    .badge { display: inline-block; padding: 2px 7px; border-radius: 20px; font-size: 7px; font-weight: 800; }
    .b-lead    { background: #e8eaf6; color: #1a1a6e; }
    .b-won     { background: #d4edda; color: #155724; }
    .b-lost    { background: #f8d7da; color: #721c24; }
    .b-pending { background: #cce5ff; color: #004085; }
    .b-repeat  { background: #fff3cd; color: #856404; }
    .b-recover { background: #f8d7da; color: #721c24; }
    .dot-status { display: inline-block; width: 6px; height: 6px; border-radius: 50%; margin-right: 3px; }

    /* ── Footer ── */
    .footer-table { width: 100%; margin-top: 14px; border-top: 1px solid #e8eaf6; padding-top: 8px; }
    .footer-left  { font-size: 7px; color: #ccc; }
    .footer-right { font-size: 7px; color: #ccc; text-align: right; }
    .footer-brand { font-size: 9px; font-weight: 800; color: #7366ff; }

    .page-break { page-break-after: always; }
    .no-break   { page-break-inside: avoid; }
</style>
</head>
<body>
<div class="page">

{{-- ═══ HEADER ═══ --}}
<div class="header-band">
    <table><tr>
        <td>
            <div class="company-name">Shiva Polyfab</div>
            <div class="company-sub">Lead Management System</div>
        </td>
        <td style="text-align:right;">
            <div class="report-title">Lead Performance Report</div>
            <div class="report-sub">Comprehensive Analytics &amp; Insights Export</div>
        </td>
    </tr></table>
    <div class="header-divider"></div>
    <table class="header-meta"><tr>
        <td><b>Generated On:</b> {{ $filters['generated_at'] }}</td>
        <td><b>Generated By:</b> {{ $filters['generated_by'] }}</td>
        <td>
            <b>Period:</b>
            @if($filters['from_date'])
                {{ \Carbon\Carbon::parse($filters['from_date'])->format('d M Y') }}
                @if($filters['to_date']) &#8594; {{ \Carbon\Carbon::parse($filters['to_date'])->format('d M Y') }} @endif
            @else All Time @endif
        </td>
        <td><b>Total Records:</b> {{ $total }}</td>
    </tr></table>
</div>

{{-- ═══ KPI CARDS ═══ --}}
<div class="section-label">Key Performance Indicators</div>
<table class="kpi-table"><tr>
    <td width="20%" class="kpi-cell kpi-blue">
        <div class="k-label">Total Leads</div>
        <div class="k-value">{{ $total }}</div>
        <div class="k-sub">Filtered records</div>
    </td>
    <td width="20%" class="kpi-cell kpi-green">
        <div class="k-label">Converted (Won)</div>
        <div class="k-value">{{ $wonCount }}</div>
        <div class="k-sub">Rate: {{ $total > 0 ? round(($wonCount/$total)*100,1) : 0 }}%</div>
    </td>
    <td width="20%" class="kpi-cell kpi-red">
        <div class="k-label">Lost Leads</div>
        <div class="k-value">{{ $lostCount }}</div>
        <div class="k-sub">Loss: {{ $total > 0 ? round(($lostCount/$total)*100,1) : 0 }}%</div>
    </td>
    <td width="20%" class="kpi-cell kpi-amber">
        <div class="k-label">In Pipeline</div>
        <div class="k-value">{{ $pendingCount }}</div>
        <div class="k-sub">Active enquiries</div>
    </td>
    <td width="20%" class="kpi-cell kpi-violet">
        <div class="k-label">Repeat Leads</div>
        <div class="k-value">{{ $repeatLead }}</div>
        <div class="k-sub">Won: {{ $repeatWon }}</div>
    </td>
</tr></table>

{{-- ═══ SECONDARY METRICS ═══ --}}
<div class="section-label">Advanced Metrics</div>
<table class="metrics-table"><tr>
    <td class="metric-cell">
        <div class="m-label">Recovered Lost</div>
        <div class="m-value">{{ $recoverLead }}</div>
        <div class="m-sub">Recovered Won: {{ $recoverWon }}</div>
    </td>
    <td class="metric-cell">
        <div class="m-label">On-Time Followups</div>
        <div class="m-value">{{ $doneInTime }}</div>
        <div class="m-sub">Completed in time</div>
    </td>
    <td class="metric-cell">
        <div class="m-label">Delayed Followups</div>
        <div class="m-value">{{ $doneLate }}</div>
        <div class="m-sub">Done after due date</div>
    </td>
    <td class="metric-cell">
        <div class="m-label">Engagement Rate</div>
        <div class="m-value">{{ ($doneInTime+$doneLate)>0 ? round(($doneInTime/($doneInTime+$doneLate))*100,1) : 100 }}%</div>
        <div class="m-sub">Followup efficiency</div>
    </td>
    <td class="metric-cell">
        <div class="m-label">Conversion Rate</div>
        <div class="m-value">{{ $total>0 ? round(($wonCount/$total)*100,1) : 0 }}%</div>
        <div class="m-sub">Leads &#8594; Won</div>
    </td>
</tr></table>

{{-- ═══ BREAKDOWN TABLES ═══ --}}
<div class="section-label">Category Breakdown</div>
<table class="breakdown-outer"><tr>

    <td width="34%">
        <div class="breakdown-box">
            <div class="bh">Step-wise Distribution</div>
            <table>
                <thead><tr><th>Stage</th><th>Count</th><th>Share</th></tr></thead>
                <tbody>
                @foreach($statuses as $st)
                    @php $count = $allResults->where('status_id', $st->id)->count(); @endphp
                    @if($count > 0)
                    <tr>
                        <td><span class="dot" style="background:{{ $st->color ?? '#7366ff' }};"></span>{{ $st->name }}</td>
                        <td><b>{{ $count }}</b></td>
                        <td>{{ $total > 0 ? round(($count/$total)*100,1) : 0 }}%</td>
                    </tr>
                    @endif
                @endforeach
                </tbody>
            </table>
        </div>
    </td>

    <td width="33%">
        <div class="breakdown-box">
            <div class="bh">Source Performance</div>
            <table>
                <thead><tr><th>Source</th><th>Leads</th><th>Won</th><th>Rate</th></tr></thead>
                <tbody>
                @foreach($sources as $sc)
                    @php
                        $sLeads = $allResults->where('source_id', $sc->id);
                        $sc_count = $sLeads->count();
                        $sc_won   = $sLeads->where('status.slug','won')->count();
                    @endphp
                    @if($sc_count > 0)
                    <tr>
                        <td>{{ $sc->name }}</td>
                        <td><b>{{ $sc_count }}</b></td>
                        <td class="won-color">{{ $sc_won }}</td>
                        <td>{{ $sc_count > 0 ? round(($sc_won/$sc_count)*100,1) : 0 }}%</td>
                    </tr>
                    @endif
                @endforeach
                </tbody>
            </table>
        </div>
    </td>

    <td width="33%">
        <div class="breakdown-box">
            <div class="bh">Staff Contribution</div>
            <table>
                <thead><tr><th>Staff</th><th>Added</th><th>Assigned</th><th>Won</th></tr></thead>
                <tbody>
                @foreach($users as $u)
                    @php
                        $added    = $allResults->where('added_by', $u->id)->count();
                        $assigned = $allResults->where('assigned_user_id', $u->id)->count();
                        $won      = $allResults->where('assigned_user_id', $u->id)->where('status.slug','won')->count();
                    @endphp
                    @if($added > 0 || $assigned > 0)
                    <tr>
                        <td>{{ $u->name }}</td>
                        <td>{{ $added }}</td>
                        <td>{{ $assigned }}</td>
                        <td class="won-color">{{ $won }}</td>
                    </tr>
                    @endif
                @endforeach
                </tbody>
            </table>
        </div>
    </td>

</tr></table>

{{-- ═══ STAR PERFORMERS ═══ --}}
<div class="star-band no-break">
    <div class="star-band-head">&#9733; Star Performers</div>
    <div class="star-band-sub">Top performers for {{ $perfLabel }} &mdash; by conversions</div>
    @if($leaderboard->count() > 0)
    <table class="star-table"><tr>
        @php $palettes = ['#7366ff','#51bb25','#f8a800','#ff5370','#4facfe']; @endphp
        @foreach($leaderboard->take(5) as $i => $staff)
        @php $color = $palettes[$i] ?? '#888'; $initial = strtoupper(substr($staff->name,0,1)); $pos = $i + 1; @endphp
        <td class="star-cell" style="width:20%;">
            <span class="star-pos">#{{ $pos }}</span>
            <span class="star-initial" style="background:{{ $color }};">{{ $initial }}</span><br>
            <div class="star-name">{{ $staff->name }}</div>
            <div class="star-count">{{ $staff->leads_count }} Customer{{ $staff->leads_count!=1?'s':'' }} Converted</div>
        </td>
        @endforeach
        @for($p = $leaderboard->count(); $p < 5; $p++)
        <td style="width:20%;"></td>
        @endfor
    </tr></table>
    @else
    <div style="color:rgba(255,255,255,0.45);font-size:9px;padding:12px 0;">No conversions recorded for this period.</div>
    @endif
</div>

{{-- ═══ LEAD TABLE ═══ --}}
@if($allResults->count() > 0)
<div class="no-break">
    <div class="leads-header">
        <span class="leads-header-title">Complete Lead Listing</span>
        <span class="leads-header-count">{{ $allResults->count() }} records</span>
    </div>
    <table class="leads-table">
        <thead>
            <tr>
                <th>#</th>
                <th>Lead No</th>
                <th>Date</th>
                <th>Name</th>
                <th>Location</th>
                <th>Phone</th>
                <th>Source</th>
                <th>Stage</th>
                <th>Assigned To</th>
                <th>Status</th>
                <th>Order No</th>
            </tr>
        </thead>
        <tbody>
            @foreach($allResults as $i => $l)
            <tr>
                <td style="color:#bbb;">{{ $i + 1 }}</td>
                <td><span class="badge b-lead">{{ $l->lead_no }}</span></td>
                <td>{{ $l->created_at->format('d M Y') }}</td>
                <td>
                    <b>{{ $l->name }}</b>
                    @if($l->is_repeat) <span class="badge b-repeat">R</span> @endif
                    @if($l->is_returning_lost) <span class="badge b-recover">RC</span> @endif
                </td>
                <td>{{ trim($l->city . ($l->city && $l->state ? ', ' : '') . $l->state) }}</td>
                <td>{{ $l->phone }}</td>
                <td>{{ $l->source->name ?? '—' }}</td>
                <td>
                    @if($l->status)
                        <span class="dot-status" style="background:{{ $l->status->color ?? '#666' }};"></span>{{ $l->status->name }}
                    @else —
                    @endif
                </td>
                <td>{{ $l->assignedUser->name ?? '—' }}</td>
                <td>
                    @if($l->status)
                        @if($l->status->slug === 'won') <span class="badge b-won">Won</span>
                        @elseif($l->status->slug === 'lost') <span class="badge b-lost">Lost</span>
                        @else <span class="badge b-pending">Pending</span>
                        @endif
                    @endif
                </td>
                <td>{{ $l->order_no ?? '—' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endif

{{-- ═══ FOOTER ═══ --}}
<table class="footer-table" style="margin-top:14px;border-top:1px solid #e8eaf6;padding-top:8px;"><tr>
    <td class="footer-left">
        &copy; {{ date('Y') }} Shiva Polyfab &mdash; Lead Management System<br>
        This report is confidential and intended for internal use only.
    </td>
    <td class="footer-right">
        <div class="footer-brand">Shiva Polyfab LMS</div>
        Generated: {{ $filters['generated_at'] }} &mdash; Total: {{ $total }} records
    </td>
</tr></table>

</div>
</body>
</html>
