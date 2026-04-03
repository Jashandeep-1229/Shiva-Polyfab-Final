<div class="dt-ext table-responsive">
    <table class="display nowrap table-striped table-hover" id="color-table">
        <thead>
            <tr>
                <th>#</th>
                <th>Name</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($colors as $key => $item)
            <tr>
                <td>{{ $colors->firstItem() + $key }}</td>
                <td>{{ $item->name ?? 'N/A' }}</td>
                <td>
                    <div class="media-body text-start">
                        <label class="switch">
                          <input type="checkbox" onchange="change_color_status({{$item->id}})" {{$item->status == 1 ? 'checked' : ''}} 
                          {{ \App\Helpers\PermissionHelper::check('manage_master', 'edit') ? '' : 'disabled' }}><span class="switch-state"></span>
                        </label>
                    </div>
                </td>
                <td>
                    @if(\App\Helpers\PermissionHelper::check('manage_master', 'edit'))
                    <a onclick="edit_color({{$item->id}})" class="btn btn-warning btn-sm pointer p-1 f-14" title="Edit">
                        <i class="fa fa-edit"></i>
                    </a>
                    @endif
                    @if (auth()->user()->role_as == 'Admin')
                        <a onclick="delete_color({{$item->id}})" class="btn btn-danger btn-sm pointer p-1 f-14" title="Delete">
                            <i class="fa fa-trash-o"></i>
                        </a>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
<div class="mt-2 color-pages">
    {{$colors->onEachSide(1)->links()}}
</div>
