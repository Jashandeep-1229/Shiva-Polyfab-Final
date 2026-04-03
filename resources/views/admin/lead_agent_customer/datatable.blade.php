<div class="dt-ext" style="overflow-x: visible;">
    <table class="display table-striped table-hover" id="basic-test">
        <thead>
            <tr>
                <th class="all">#</th>
                <th class="all">Name <small>(GST)</small></th>
                <th class="all">Phone No</th>
                <th class="all">Role</th>
                <th class="all">Sale Executive</th>
                <th class="all">Address</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($lead_customers as $key => $item)
            <tr>
                <td>{{ $lead_customers->firstItem() + $key }}</td>
                <td>{{ $item->name ?? '-' }} <br> <small class="text-muted">GST: {{ $item->gst ?? '-' }}</small></td>
                <td>{{ $item->phone_no ?? '-' }}</td>
                <td>{{ $item->role ?? '-' }}</td>
                <td>
                    @if($item->sale_executive)
                        {{ $item->sale_executive->name }}
                    @elseif($item->sale_executive_id == 1)
                        Admin
                    @else
                        -
                    @endif
                </td>
                <td style="max-width: 250px;">
                    {{ $item->address ?? '' }} <br>
                    <small class="text-muted">{{ $item->city ?? '' }}, {{ strtoupper($item->state ?? '') }} - {{ $item->pincode ?? '' }}</small>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

<div class="mt-4 mb-4">
    <div class="d-flex justify-content-between align-items-center px-2">
        <div class="text-muted" style="font-size: 13px; font-weight: 500;">
            Showing <span class="text-dark fw-bold">{{ $lead_customers->firstItem() ?? 0 }}</span> to <span class="text-dark fw-bold">{{ $lead_customers->lastItem() ?? 0 }}</span> of <span class="text-dark fw-bold">{{ $lead_customers->total() }}</span> results
        </div>
        <div class="custom-pagination">
            {{ $lead_customers->onEachSide(1)->links('pagination::bootstrap-4') }}
        </div>
    </div>
</div>

<style>
    .custom-pagination .pagination { margin-bottom: 0; gap: 5px; }
    .custom-pagination .page-item .page-link { border-radius: 4px !important; padding: 6px 12px; color: #4b4b4b; font-weight: 600; border: 1px solid #dee2e6; }
    .custom-pagination .page-item.active .page-link { background-color: #24695c; border-color: #24695c; color: #fff; }
    .dt-ext { overflow-x: visible !important; width: 100% !important; }
    #basic-test { width: 100% !important; margin-bottom: 0; }
    #basic-test th, #basic-test td { padding: 6px 8px !important; font-size: 13px; }
</style>
