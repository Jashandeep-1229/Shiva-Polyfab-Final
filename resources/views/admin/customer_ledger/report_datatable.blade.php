<div class="table-responsive">
    <table class="table table-bordered table-hover" id="aging-report-table">
        <thead class="bg-light">
            <tr>
                <th class="small py-2 text-center" style="width: 40px;">#</th>
                <th class="small py-2">Customer Name</th>
                <th class="small py-2">Sale Executive</th>
                <th class="small py-2 text-end">Net Balance</th>
                <th class="small py-2 text-center">Status</th>
                <th class="small py-2 text-end">0-15 Days</th>
                <th class="small py-2 text-end">16-30 Days</th>
                <th class="small py-2 text-end">31-45 Days</th>
                <th class="small py-2 text-end">45 Days +</th>
            </tr>
        </thead>
        <tbody>
            @php
                $total_net = $summary['total_net'];
                $total_15 = $summary['total_15'];
                $total_30 = $summary['total_30'];
                $total_45 = $summary['total_45'];
                $total_plus = $summary['total_plus'];
            @endphp
            @forelse($report_data as $index => $data)
                @php
                    $row_id = 'remarks_' . $loop->iteration . '_' . $report_data->currentPage();
                @endphp
                <tr class="main-row" @if($data['highlight_7_days']) style="background-color: #fecdd3 !important;" @endif>
                    <td class="text-center align-middle">
                        <div class="d-flex flex-column align-items-center">
                            <span class="small fw-bold text-muted mb-1">{{ ($report_data->currentPage() - 1) * $report_data->perPage() + $loop->iteration }}</span>
                            @if(count($data['remarks']) > 0 || !$data['has_followup'])
                                <a href="javascript:void(0)" class="text-primary collapse-toggle" data-bs-toggle="collapse" data-bs-target="#{{ $row_id }}">
                                    <i class="fa {{ count($data['remarks']) > 0 ? 'fa-minus-square-o' : 'fa-plus-square-o text-success' }}"></i>
                                </a>
                            @endif
                        </div>
                    </td>
                    <td class="align-middle">
                        <div class="fw-bold text-dark" style="font-size: 13px;">{{ $data['customer']->name }}</div>
                    </td>
                    <td class="align-middle">
                        <span class="small badge bg-light text-dark border">{{ $data['customer']->sale_executive->name ?? '-' }}</span>
                    </td>
                    <td class="text-end fw-bold align-middle">
                        <span class="{{ $data['net_balance'] > 0.001 ? 'text-danger' : ($data['net_balance'] < -0.001 ? 'text-success' : 'text-muted') }}">
                            @if(abs($data['net_balance']) > 0.001)
                                {{ number_format(abs($data['net_balance']), 2) }}
                                {{ $data['net_balance'] > 0 ? 'Dr' : 'Cr' }}
                            @else
                                <span class="opacity-50">-</span>
                            @endif
                        </span>
                    </td>
                    <td class="text-center align-middle">
                        @if($data['customer']->status == 1)
                            <span class="badge badge-light-success px-2 py-1" style="font-size: 10px;">Active</span>
                        @else
                            <span class="badge badge-light-danger px-2 py-1" style="font-size: 10px;">Inactive</span>
                        @endif
                    </td>
                    <td class="text-end small align-middle">
                        @if($data['buckets']['1-15'] > 0)
                            <span class="fw-bold">{{ number_format($data['buckets']['1-15'], 2) }}</span>
                        @else
                            <span class="text-muted opacity-50">-</span>
                        @endif
                    </td>
                    <td class="text-end small align-middle">
                        @if($data['buckets']['16-30'] > 0)
                            <span class="fw-bold text-warning">{{ number_format($data['buckets']['16-30'], 2) }}</span>
                        @else
                            <span class="text-muted opacity-50">-</span>
                        @endif
                    </td>
                    <td class="text-end small align-middle">
                        @if($data['buckets']['31-45'] > 0)
                            <span class="fw-bold text-orange" style="color: #ed8936;">{{ number_format($data['buckets']['31-45'], 2) }}</span>
                        @else
                            <span class="text-muted opacity-50">-</span>
                        @endif
                    </td>
                    <td class="text-end small align-middle">
                        @if($data['buckets']['45+'] > 0)
                            <span class="fw-bold text-danger">{{ number_format($data['buckets']['45+'], 2) }}</span>
                        @else
                            <span class="text-muted opacity-50">-</span>
                        @endif
                    </td>
                </tr>
                @if(count($data['remarks']) > 0 || !$data['has_followup'])
                    <tr class="collapse show detail-row" id="{{ $row_id }}" @if($data['highlight_7_days']) style="background-color: #fef2f2 !important;" @endif>
                        <td colspan="1"></td>
                        <td colspan="8" class="p-0 border-top-0">
                            <div class="remarks-container px-3 py-2 border-bottom" @if($data['highlight_7_days']) style="background-color: #fef2f2 !important;" @else style="background-color: #f8fafc;" @endif>
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="fw-bold small text-muted text-uppercase" style="font-size: 10px;">Recent Remarks</span>
                                    <div>
                                        @if($data['has_followup'])
                                            <button class="btn btn-warning btn-xs py-1 px-2 shadow-sm" style="font-size: 11px;" title="Continue Followup" onclick="continueFollowupModal({{ $data['active_followup_id'] }}, '{{ str_replace('\'','\\\'', $data['customer']->name) }}', {{ $data['can_close'] ? 'true' : 'false' }})"><i class="fa fa-calendar-check-o"></i></button>
                                        @else
                                            <button class="btn btn-outline-success btn-xs py-1 px-2 shadow-sm" style="font-size: 11px;" title="Add Followup" onclick="openFollowupModal({{ $data['customer']->id }}, '{{ str_replace('\'','\\\'', $data['customer']->name) }}')"><i class="fa fa-plus"></i></button>
                                        @endif
                                    </div>
                                </div>
                                @foreach($data['remarks'] as $remark)
                                    <div class="remark-item d-flex align-items-center justify-content-between py-1 border-bottom-dotted">
                                        <div class="d-flex align-items-center overflow-hidden">
                                            <i class="fa fa-caret-right text-primary me-2 extra-small"></i>
                                            <span class="text-dark small text-truncate" style="max-width: 500px;" title="{{ $remark['text'] }}">
                                                {{ $remark['text'] }}
                                            </span>
                                            <span class="ms-2 badge {{ $remark['status'] == 'Completed' ? 'bg-success' : 'bg-warning text-dark' }}" style="font-size: 9px;">
                                                {{ $remark['status'] }}
                                            </span>
                                        </div>
                                        <div class="text-end ps-3">
                                            <span class="extra-small fw-600 text-muted">{{ $remark['date'] }}</span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </td>
                    </tr>
                @endif
            @empty
                <tr>
                    <td colspan="9" class="text-center py-4 text-muted">No data found matching the filters.</td>
                </tr>
            @endforelse
        </tbody>
        <tfoot class="fw-bold" style="background: #f8fafc;">
            <tr>
                <td colspan="3" class="text-end py-2">TOTAL SUMMARY (Page {{ $report_data->currentPage() }} of {{ $report_data->lastPage() }}):</td>
                <td class="text-end py-2 {{ $total_net > 0 ? 'text-danger' : 'text-success' }}">
                    {{ number_format(abs($total_net), 2) }} {{ $total_net > 0 ? 'Dr' : 'Cr' }}
                </td>
                <td></td>
                <td class="text-end py-2">{{ number_format($total_15, 2) }}</td>
                <td class="text-end py-2 text-warning">{{ number_format($total_30, 2) }}</td>
                <td class="text-end py-2 text-orange" style="color: #ed8936;">{{ number_format($total_45, 2) }}</td>
                <td class="text-end py-2 text-danger">{{ number_format($total_plus, 2) }}</td>
            </tr>
        </tfoot>
    </table>
</div>

<div class="d-flex align-items-center justify-content-between mt-3 pages">
    <div class="small text-muted">
        Showing {{ $report_data->firstItem() ?? 0 }} to {{ $report_data->lastItem() ?? 0 }} of {{ $report_data->total() }} customers
    </div>
    <div class="report-pagination">
        {!! $report_data->links() !!}
    </div>
</div>

<style>
    .bg-indigo-subtle { background-color: #f5f3ff !important; }
    .badge-light-success { background-color: #e6fffa; color: #047857; border: 1px solid #b2f5ea; }
    .badge-light-danger { background-color: #fff5f5; color: #c53030; border: 1px solid #fed7d7; }
    .bg-pink-subtle { background-color: #ffe4e6 !important; }

    /* Disable slow transitions for instant toggle */
    .collapse { transition: none !important; }
    .collapsing { transition: none !important; display: none !important; }
    
    .border-bottom-dotted { border-bottom: 1px dotted #e2e8f0; }
    .border-bottom-dotted:last-child { border-bottom: none; }
    
    .remark-item:hover { background-color: #f1f5f9; }
    .collapse-toggle { font-size: 14px; line-height: 1; transition: 0.2s; }
    .collapse-toggle:hover { color: #1e40af; transform: scale(1.1); }
    
    #aging-report-table th { white-space: nowrap; vertical-align: middle; background: #f1f5f9; color: #334155; font-weight: 700; border-bottom: 2px solid #e2e8f0; }
    #aging-report-table td { vertical-align: middle; }
    
    .detail-row td { padding: 0 !important; border-top: none !important; }
    .remarks-container { border-left: 3px solid #3b82f6; margin-left: -1px; }

    /* For smooth transition on first column indicator */
    .collapse-toggle i.fa-minus-square-o:before { content: "\f147"; } /* minus */
    .collapsed i.fa-minus-square-o:before { content: "\f196"; } /* plus */
</style>
