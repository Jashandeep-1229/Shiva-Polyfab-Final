<div class="table-responsive">
    <table class="display" id="basic-test">
        <thead>
            <tr>
                <th>#</th>
                <th>Name</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            @foreach($payment_method as $index => $row)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td class="fw-bold">{{ $row->name }}</td>
                <td>
                    @if($row->status == 1)
                        <span class="badge bg-success">Active</span>
                    @else
                        <span class="badge bg-danger">Inactive</span>
                    @endif
                </td>
                <td>
                    <div class="d-flex gap-2">
                        @if(\App\Helpers\PermissionHelper::check('manage_master', 'edit'))
                        <button onclick="editMethod({{ $row->id }})" class="btn btn-primary btn-xs" title="Edit">
                            <i class="fa fa-pencil"></i>
                        </button>
                        @endif
                        @if(auth()->user()->role_as == 'Admin')
                        <button onclick="deleteMethod({{ $row->id }})" class="btn btn-danger btn-xs" title="Delete">
                            <i class="fa fa-trash"></i>
                        </button>
                        @endif
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

<div class="mt-3 pages">
    {{ $payment_method->links() }}
</div>
