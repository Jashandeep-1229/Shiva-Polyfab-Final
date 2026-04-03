<div class="dt-ext table-responsive">
    <table class="display nowrap table-striped table-hover" id="size-table">
        <thead>
            <tr>
                <th>#</th>
                <th>Name</th>
                <th>Fabric</th>
                <th>BOPP</th>
                <th>Send For</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($sizes as $key => $item)
            <tr>
                <td>{{ $sizes->firstItem() + $key }}</td>
                <td>{{ $item->name ?? 'N/A' }}</td>
                <td>{{ $item->fabric->name ?? 'N/A' }}</td>
                <td>{{ $item->bopp->name ?? 'N/A' }}</td>
                <td>
                    <span class="badge {{ $item->order_send_for == 'Cutting' ? 'badge-info' : 'badge-warning' }}">
                        {{ $item->order_send_for ?? 'N/A' }}
                    </span>
                </td>
                <td>
                    <div class="media-body text-start">
                        <label class="switch">
                          <input type="checkbox" onchange="change_size_status({{$item->id}})" {{$item->status == 1 ? 'checked' : ''}} 
                          {{ \App\Helpers\PermissionHelper::check('manage_master', 'edit') ? '' : 'disabled' }}><span class="switch-state"></span>
                        </label>
                    </div>
                </td>
                <td>
                    @if(\App\Helpers\PermissionHelper::check('manage_master', 'edit'))
                    <a onclick="edit_size({{$item->id}})" class="btn btn-warning btn-sm pointer p-1 f-14" title="Edit">
                        <i class="fa fa-edit"></i>
                    </a>
                    @endif
                    @if (auth()->user()->role_as == 'Admin')
                        <a onclick="delete_size({{$item->id}})" class="btn btn-danger btn-sm pointer p-1 f-14" title="Delete">
                            <i class="fa fa-trash-o"></i>
                        </a>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
<div class="mt-2 size-pages">
    {{$sizes->onEachSide(1)->links()}}
</div>
