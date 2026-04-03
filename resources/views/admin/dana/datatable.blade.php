<div class="dt-ext table-responsive">
    <table class="display table-striped table-hover" id="basic-test">
        <thead>
            <tr>
                <th class="all">#</th>
                <th class="all">Name</th>
                <th class="all">Alert & Min Order</th>
                <th class="all">Status</th>
                <th class="all">Action</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($dana as $key => $item)
            <tr>
                <td>{{ $dana->firstItem() + $key }}</td>
                <td>{{ $item->name ?? 'N/A' }}</td>
                <td>
                    <span class="badge badge-warning">Min Stock: {{ $item->alert_min_stock ?? 'N/A' }}</span>
                    <span class="badge badge-primary">Max Stock: {{ $item->alert_max_stock ?? 'N/A' }}</span><br>
                    <span class="badge badge-info">Order Qty: {{ $item->order_qty ?? 'N/A' }}</span>
                </td>
                <td>
                    <div class="media-body text-start ">
                        <label class="switch">
                          <input type="checkbox" {{$item->status == 1 ? 'checked':''}} onchange="change_status({{$item->id}})" {{ \App\Helpers\PermissionHelper::check('manage_master', 'edit') ? '' : 'disabled' }}><span class="switch-state"></span>
                        </label>
                      </div>
                </td>
                <td>
                    <div class="d-flex gap-1">
                        @if(\App\Helpers\PermissionHelper::check('manage_master', 'edit'))
                        <a onclick="edit_modal({{$item->id}},{{$key+1}})"  class="btn btn-warning btn-sm pointer p-1 f-12" data-bs-toggle="modal" data-bs-target="#edit_modal" title="Edit">
                            <i class="fa fa-edit"></i>
                        </a>
                        <button type="button" onclick="openAddVendorModal('Dana', {{$item->id}})" class="btn btn-primary btn-sm pointer p-1 f-12" title="Add Vendor">
                            <i class="fa fa-plus-circle"></i>
                        </button>
                        <button type="button" onclick="openVendorListModal('Dana', {{$item->id}})" class="btn btn-info btn-sm pointer p-1 f-12" title="Vendor List">
                            <i class="fa fa-users"></i>
                        </button>
                        @endif
                        @if (auth()->user()->role_as == 'Admin')
                            <a onclick="delete_dana({{$item->id}})" class="btn btn-danger btn-sm pointer p-1 f-12" title="Delete">
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
<div class="mt-2">
    {{$dana->onEachSide(1)->links()}}
</div>
