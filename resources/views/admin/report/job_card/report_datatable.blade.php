<input type="hidden" id="hidden_total" value="{{ $total_orders }}">
<input type="hidden" id="hidden_active" value="{{ $progress_orders }}">
<input type="hidden" id="hidden_completed" value="{{ $completed_orders }}">

<style>
    #report_table tr.table-danger td {
        background-color: #fee2e2 !important;
        border-bottom: 1px solid #fecaca !important;
    }
</style>

<div class="table-responsive">
    <table class="table table-bordered table-striped table-sm mb-0 f-13" id="report_table">
        <thead>
            <tr style="background-color: #242934 !important; color: #fff !important;">
                <th class="text-white py-2">S.No</th>
                <th class="text-white py-2">Job Name</th>
                <th class="text-white py-2">Executive</th>
                <th class="text-white py-2">Customer</th>
                <th class="text-white py-2">Date</th>
                <th class="text-white py-2">Exp. Dispatch</th>
                <th class="text-white py-2">Compl. Date</th>
                <th class="text-white py-2">Status</th>
                <th class="text-white py-2">Process</th>
                <th class="text-white py-2">Delivery</th>
                <th class="text-white py-2 text-center">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($job_cards as $item)
                @php 
                    $isHold = $item->is_hold == 1; 
                @endphp
                <tr class="{{ $isHold ? 'table-danger' : '' }}" style="{{ $isHold ? 'border-left: 4px solid #ef4444 !important;' : '' }}">
                    <td>{{ ($job_cards->currentPage() - 1) * $job_cards->perPage() + $loop->iteration }}</td>
                    <td class="fw-bold">
                        {{ $item->name_of_job }}
                        @if($isHold)
                            <i class="fa fa-lock text-danger ms-2" title="Order is On Hold" style="font-size:1.1rem; vertical-align:middle;"></i>
                        @endif
                    </td>
                    <td>{{ $item->sale_executive->name ?? 'N/A' }}</td>
                    <td>{{ $item->customer_agent->name ?? 'N/A' }}</td>
                    <td>{{ date('d-m-Y', strtotime($item->job_card_date)) }}</td>
                    <td>{{ $item->dispatch_date ? date('d-m-Y', strtotime($item->dispatch_date)) : '-' }}</td>
                    <td>{{ $item->complete_date ? date('d-m-Y', strtotime($item->complete_date)) : '-' }}</td>
                    <td>
                        @php
                            $statusClass = 'bg-secondary';
                            if($item->is_hold == 1) $statusClass = 'bg-danger';
                            elseif($item->status == 'Pending') $statusClass = 'bg-warning text-dark';
                            elseif($item->status == 'Progress') $statusClass = 'bg-info';
                            elseif($item->status == 'Account Pending') $statusClass = 'bg-primary';
                            elseif($item->status == 'Completed') $statusClass = 'bg-success';
                        @endphp
                        <span class="badge {{ $statusClass }}">
                            {{ $item->is_hold == 1 ? 'ON HOLD' : $item->status }}
                        </span>
                    </td>
                    <td>
                        @if($item->is_hold == 1)
                            <span class="text-danger fw-bold small">Hold: {{ $item->hold_reason->reason ?? 'No Reason' }}</span>
                        @else
                            <span class="text-muted small">{{ $item->job_card_process }}</span>
                        @endif
                    </td>
                    <td>
                        @if($item->status == 'Completed' && $item->complete_date && $item->dispatch_date)
                            @php
                                $compl = strtotime($item->complete_date);
                                $disp = strtotime($item->dispatch_date);
                            @endphp
                            @if($compl <= $disp)
                                @if($compl < $disp)
                                    <span class="badge badge-before-time">Before Time</span>
                                @else
                                    <span class="badge badge-on-time">On Time</span>
                                @endif
                            @else
                                <span class="badge badge-late">Late ({{ round(($compl - $disp) / 86400) }} days)</span>
                            @endif
                        @else
                            -
                        @endif
                    </td>
                    <td class="text-center">
                        <div class="d-flex justify-content-center gap-1">
                            <button type="button" class="btn btn-primary btn-xs" onclick="viewTimeline({{ $item->id }})" title="Timeline" style="width: 32px; height: 32px;">
                                <i class="fa fa-history text-white"></i>
                            </button>
                            
                            @if($item->packing_slips->count() > 0)
                                <button type="button" class="btn btn-info btn-xs" onclick="viewPackingSlips({{ $item->id }})" title="Packing Slips" style="width: 32px; height: 32px;">
                                    <i class="fa fa-truck text-white"></i>
                                </button>
                            @endif

                            @php
                                $btnColor = ($item->status == 'Completed' || $item->status == 'Account Pending') ? 'success' : 'secondary';
                                $btnIcon = ($item->status == 'Completed' || $item->status == 'Account Pending') ? 'fa-file-text-o' : 'fa-list-alt';
                                $btnTitle = ($item->status == 'Completed' || $item->status == 'Account Pending') ? 'Bill & Details' : 'Order Details';
                            @endphp
                            
                            <button type="button" class="btn btn-{{ $btnColor }} btn-xs" onclick="viewBilling({{ $item->id }})" title="{{ $btnTitle }}" style="width: 32px; height: 32px;">
                                <i class="fa {{ $btnIcon }} text-white"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="11" class="text-center p-4 text-muted">No records found matching your filters.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="d-flex justify-content-between align-items-center p-3">
    <div class="text-muted small">
        Showing {{ $job_cards->firstItem() ?? 0 }} to {{ $job_cards->lastItem() ?? 0 }} of {{ $job_cards->total() }} entries
    </div>
    <div class="pagination-wrapper">
        {{ $job_cards->appends(request()->all())->links() }}
    </div>
</div>
