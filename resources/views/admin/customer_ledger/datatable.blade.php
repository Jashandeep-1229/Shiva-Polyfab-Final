<div class="table-responsive">
    <table class="display" id="basic-test">
        <thead>
            <tr>
                <th>#</th>
                <th>Customer Name</th>
                <th class="text-end">Debit Balance (Dr)</th>
                <th class="text-end">Credit Balance (Cr)</th>
                <th class="text-end">Action</th>
            </tr>
        </thead>
        <tbody>
            @php
                $page_total_dr = 0;
                $page_total_cr = 0;
            @endphp
            @foreach($customers as $index => $row)
            @php
                if($row->balance > 0) {
                    $page_total_dr += abs($row->balance);
                } else {
                    $page_total_cr += abs($row->balance);
                }
            @endphp
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>
                    <div class="fw-bold">
                        {{ $row->name }}
                        @if($row->is_bad_debt)
                            <span class="badge bg-danger text-white ms-1" style="font-size: 9px; vertical-align: middle;">BAD DEBT CUSTOMER</span>
                        @endif
                    </div>
                    <div class="small text-muted mt-1">
                        <span class="badge bg-light text-dark border py-0 px-1">{{ ucfirst($row->role) }}</span>
                        @if($row->sale_executive)
                            <span class="ms-1 pointer" title="Sale Executive"><i class="fa fa-user me-1 text-info"></i>{{ $row->sale_executive->name }}</span>
                        @endif
                    </div>
                </td>
                <td class="text-end">
                    @if($row->balance > 0)
                        <span class="text-danger fw-bold">{{ number_format(abs($row->balance), 2) }}</span>
                    @else
                        <span class="text-muted small">-</span>
                    @endif
                </td>
                <td class="text-end">
                    @if($row->balance < 0)
                        <span class="text-success fw-bold">{{ number_format(abs($row->balance), 2) }}</span>
                    @else
                        <span class="text-muted small">-</span>
                    @endif
                </td>
                <td class="text-end">
                    <div class="d-flex gap-1 justify-content-end">
                        <a href="{{ route('customer_ledger.view', $row->id) }}?from_date={{ $from_date }}&to_date={{ $to_date }}" class="btn btn-primary btn-sm px-3 shadow-sm" title="View Detail Ledger">
                            <i class="fa fa-eye me-1"></i> VIEW DETAIL
                        </a>
                        <button type="button" class="btn btn-warning btn-sm px-2 shadow-sm" onclick="openFollowupModal({{ $row->id }}, '{{ str_replace("'", "\'", $row->name) }}')" title="Add Followup">
                            <i class="fa fa-calendar-plus-o"></i>
                        </button>
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
        <tfoot class="bg-light fw-bold">
            <tr>
                <td colspan="2" class="text-end text-dark">TOTAL:</td>
                <td class="text-end text-danger">{{ number_format($page_total_dr, 2) }}</td>
                <td class="text-end text-success">{{ number_format($page_total_cr, 2) }}</td>
                <td></td>
            </tr>
        </tfoot>
    </table>
</div>

<div class="mt-3 pages">
    {{ $customers->links() }}
</div>
