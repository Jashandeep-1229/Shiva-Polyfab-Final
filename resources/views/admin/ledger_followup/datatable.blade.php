@php
    function formatDate($date) {
        return $date ? date('j M, Y', strtotime($date)) : 'N/A';
    }
@endphp

<div class="table-responsive">
    <table class="table table-hover table-striped">
        <thead>
            <tr class="bg-primary text-white">
                <th style="width: 50px;">#</th>
                <th style="width: 120px;">Start Date</th>
                <th style="width: 120px;">Next Followup</th>
                <th style="width: 120px;">Completed On</th>
                <th>Customer</th>
                <th>Executive</th>
                <th>Subject</th>
                <th class="text-center">Iterations</th>
                <th>Time Taken</th>
                <th>Status</th>
                <th class="text-center">Action</th>
            </tr>
        </thead>
        <tbody>
            @forelse($followups as $f)
                @php
                    $active = $f->activeHistory;
                    $histories_count = $f->histories()->count();
                @endphp
                <tr>
                    <td><small class="text-muted">#{{ $f->id }}</small></td>
                    <td class="small fw-bold">
                        {{ formatDate($f->start_date) }}
                    </td>
                    <td class="small fw-bold">
                        @if($f->status == 'Pending' && $active)
                            <span class="text-primary">{{ formatDate($active->followup_date_time) }}</span>
                        @else
                            <span class="text-muted">--</span>
                        @endif
                    </td>
                    <td class="small fw-bold">
                        @if($f->status == 'Closed')
                            <span class="text-success">{{ formatDate($f->complete_date) }}</span>
                            <div class="extra-small text-muted italic">By: {{ $f->completedBy->name ?? 'N/A' }}</div>
                        @else
                            <span class="text-warning italic f-8">In Progress</span>
                        @endif
                    </td>
                    <td>
                        <div class="d-flex flex-column">
                            <span class="fw-bold text-dark">{{ $f->customer->name ?? 'N/A' }}</span>
                            <span class="text-muted extra-small text-uppercase fw-bold">{{ $f->customer->code ?? '' }}</span>
                        </div>
                    </td>
                    <td>
                        <span class="badge rounded-pill bg-light text-dark border">{{ $f->user->name ?? 'N/A' }}</span>
                    </td>
                    <td>
                        <div class="fw-bold text-dark">{{ $f->subject }}</div>
                        @php
                            $last_remark_item = $f->histories()->orderBy('id', 'desc')->first();
                        @endphp
                        @if($last_remark_item && $last_remark_item->remarks)
                            <div class="text-primary small fw-bold mt-1">LATEST REMARK:</div>
                            <div class="small text-muted text-truncate italic" style="max-width: 250px;" title="{{ $last_remark_item->remarks }}">
                                {{ $last_remark_item->remarks }}
                            </div>
                        @endif
                    </td>
                    <td class="text-center">
                        <span class="badge rounded-pill bg-info text-white fw-bold px-3">{{ $histories_count }}</span>
                    </td>
                    <td class="text-center">
                        @if($f->status == 'Closed')
                            <div class="fw-bold text-dark">{{ $f->total_no_of_days }} Days</div>
                        @else
                            @php
                                $start = strtotime($f->start_date);
                                $now = time();
                                $current_days = round(($now - $start) / 86400, 1);
                            @endphp
                            <div class="fw-bold text-dark">{{ $current_days }} Days</div>
                            <div class="extra-small text-muted">Active since</div>
                        @endif
                    </td>
                    <td>
                        @if($f->status == 'Pending')
                            <span class="status-badge-continue"><i class="fa fa-refresh me-1"></i> PENDING</span>
                        @else
                            <span class="status-badge-closed"><i class="fa fa-check-circle me-1"></i> CLOSED</span>
                        @endif
                    </td>
                    <td class="text-center">
                        <div class="d-flex justify-content-center gap-1">
                            @if($f->status == 'Pending')
                                <button class="btn btn-outline-primary btn-xs" onclick="viewHistory({{ $f->id }})" title="Add Remark / Continue">
                                    <i class="fa fa-plus-circle"></i>
                                </button>
                            @else
                                <button class="btn btn-outline-info btn-xs" onclick="viewHistory({{ $f->id }})" title="View Complete History">
                                    <i class="fa fa-history"></i>
                                </button>
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="10" class="text-center py-5 text-muted">No followups found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-2">
    {{ $followups->links() }}
</div>
