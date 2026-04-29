<div class="table-responsive">
    <table class="table table-bordered table-striped" id="basic-test">
        <thead>
            <tr>
                <th>#</th>
                <th>Fabric Size</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            @foreach($sizes as $key => $item)
            <tr>
                <td>{{ $sizes->firstItem() + $key }}</td>
                <td>{{ $item->name }}</td>
                <td>
                    <div class="media-body text-start">
                        <label class="switch">
                            <input type="checkbox" onchange="change_status({{ $item->id }})" {{ $item->status == 1 ? 'checked' : '' }}><span class="switch-state"></span>
                        </label>
                    </div>
                </td>
                <td>
                    @if(\App\Helpers\PermissionHelper::check('manage_master', 'edit'))
                    <a onclick="edit_modal({{ $item->id }})" class="btn btn-warning btn-sm pointer p-1" data-bs-toggle="modal" data-bs-target="#edit_modal">
                        <i class="fa fa-pencil text-dark"></i>
                    </a>
                    @endif
                    @if(auth()->user()->role_as == 'Admin')
                    <a onclick="delete_record({{ $item->id }})" class="btn btn-danger btn-sm pointer p-1">
                        <i class="fa fa-trash-o"></i>
                    </a>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
<div class="mt-2 pages">
    {{ $sizes->links() }}
</div>
