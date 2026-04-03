<div class="dt-ext" style="overflow-x: visible;">
    <table class="display table-striped table-hover" id="basic-test">
        <thead>
            <tr>
                <th class="all">#</th>
                <th class="all">Code</th>
                <th class="all">Name <small>(GST)</small></th>
                <th class="all">Phone No</th>
                <th class="all">Role</th>
                <th class="all">Type</th>
                <th class="all">Sale Executive</th>
                <th class="all">Address</th>
                <th class="all">Remarks</th>
                <th class="all">Status</th>
                <th class="all">Action</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($agent_customer as $key => $item)
            <tr>
                <td>{{ $agent_customer->firstItem() + $key }}</td>
                <td>{{ $item->code ?? 'N/A' }}</td>
                <td>{{ $item->name ?? 'N/A' }} <br> <small class="text-muted">GST: {{ $item->gst ?? 'N/A' }}</small></td>
                <td>{{ $item->phone_no ?? 'N/A' }}</td>
                <td>
                    {{ $item->role ?? 'N/A' }}
                </td>
                <td><span class="badge bg-light text-dark border">{{ $item->type ?? 'A' }}</span></td>
                <td>{{ $item->sale_executive->name ?? 'N/A' }}</td>
                <td style="max-width: 250px;">
                    {{ $item->address ?? '' }} <br>
                    <small class="text-muted">{{ $item->city ?? '' }}, {{ strtoupper($item->state ?? '') }} - {{ $item->pincode ?? '' }}</small>
                </td>
                <td>{{ $item->remarks ?? '' }}</td>
                <td>
                    <div class="media-body text-start ">
                        <label class="switch">
                          <input type="checkbox" {{$item->status == 1 ? 'checked':''}} onchange="change_status({{$item->id}})" {{ !PermissionHelper::check('agent_customer', 'edit') ? 'disabled' : '' }}><span class="switch-state"></span>
                        </label>
                      </div>
                </td>
                <td>
                    <div class="d-flex align-items-center gap-1">
                        @if (auth()->user()->role_as == 'Admin' || PermissionHelper::check('agent_customer', 'edit'))
                            <a onclick="edit({{$item->id}})" class="btn btn-primary btn-xs-custom pointer" data-toggle="tooltip" title="Edit">
                                <i class="fa fa-pencil"></i>
                            </a>
                        @endif
                        @if (auth()->user()->role_as == 'Admin' || PermissionHelper::check('agent_customer', 'delete'))
                            <a onclick="delete_ac({{$item->id}})" class="btn btn-danger btn-xs-custom pointer" data-toggle="tooltip" title="Delete">
                                <i class="fa fa-trash-o"></i>
                            </a>
                        @endif
                    </div>
                </td>
            </tr>
            @endforeach

        </tbody>
    </table>
</div>

<div class="mt-4 mb-4">
    <div class="d-flex justify-content-between align-items-center px-2">
        <div class="text-muted" style="font-size: 13px; font-weight: 500;">
            Showing <span class="text-dark fw-bold">{{ $agent_customer->firstItem() ?? 0 }}</span> to <span class="text-dark fw-bold">{{ $agent_customer->lastItem() ?? 0 }}</span> of <span class="text-dark fw-bold">{{ $agent_customer->total() }}</span> results
        </div>
        <div class="custom-pagination">
            {{ $agent_customer->onEachSide(1)->links('pagination::bootstrap-4') }}
        </div>
    </div>
</div>

<style>
    .custom-pagination .pagination {
        margin-bottom: 0;
        gap: 5px;
    }
    .custom-pagination .page-item .page-link {
        border-radius: 4px !important;
        padding: 6px 12px;
        color: #4b4b4b;
        font-weight: 600;
        border: 1px solid #dee2e6;
    }
    .custom-pagination .page-item.active .page-link {
        background-color: #24695c;
        border-color: #24695c;
        color: #fff;
    }
    .dt-ext {
        overflow-x: visible !important;
        width: 100% !important;
    }
    #basic-test {
        width: 100% !important;
        margin-bottom: 0;
    }
    #basic-test th, #basic-test td {
        padding: 6px 8px !important;
        font-size: 13px;
    }
    .btn-xs-custom {
        padding: 2px 6px !important;
        font-size: 11px !important;
    }
</style>
