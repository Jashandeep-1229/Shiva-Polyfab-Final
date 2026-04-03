<div class="table-responsive custom-scrollbar">
    <table class="table table-hover table-striped align-middle" id="basic-test">
        <thead class="bg-light">
            <tr>
                <th class="text-center" width="50">#</th>
                <th>Full Name</th>
                <th>Contact Detail</th>
                <th>Role</th>
                <th>Managers</th>
                <th class="text-center" width="100">Action</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($team as $key => $item)
            <tr>
                <td class="text-center text-muted f-12">{{ $team->firstItem() + $key }}</td>
                <td>
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="avatar-sm bg-primary-light rounded-circle text-primary d-flex align-items-center justify-content-center fw-bold" style="width: 32px; height: 32px; font-size: 14px; background: rgba(36, 41, 52, 0.1);">
                                {{ substr($item->name, 0, 1) }}
                            </div>
                        </div>
                        <div class="ms-2">
                            <h6 class="mb-0 f-14 fw-bold">{{ $item->name ?? 'N/A' }}</h6>
                        </div>
                    </div>
                </td>
                <td>
                    <div class="d-flex flex-column f-12">
                        <span class="text-dark"><i class="fa fa-envelope-o me-1 text-muted"></i>{{ $item->email ?? 'N/A' }}</span>
                        <span class="text-muted mt-1"><i class="fa fa-phone me-1 text-muted"></i>{{ $item->phone ?? 'N/A' }}</span>
                    </div>
                </td>
                <td>
                    @php
                        $badgeClass = 'bg-light text-dark border';
                        switch($item->role_as) {
                            case 'Admin': $badgeClass = 'bg-dark text-white'; break;
                            case 'Manager': $badgeClass = 'bg-success text-white'; break;
                            case 'Employee': $badgeClass = 'bg-primary text-white'; break;
                            case 'Sale Executive': $badgeClass = 'bg-info text-white'; break;
                            case 'Senior Sale Executive': $badgeClass = 'bg-warning text-dark'; break;
                            case 'Data Entry': $badgeClass = 'bg-secondary text-white'; break;
                            case 'EA': $badgeClass = 'bg-danger text-white'; break;
                        }
                    @endphp
                    <span class="badge {{ $badgeClass }} rounded-pill px-3 f-10">{{ $item->role_as ?? 'N/A' }}</span>
                </td>
                <td>
                    <div class="d-flex flex-wrap gap-1">
                        @forelse($item->managedBy as $mgr)
                            <span class="badge bg-light text-dark border rounded-pill f-10 px-2">{{ $mgr->name }}</span>
                        @empty
                            <span class="text-muted f-10">No Manager</span>
                        @endforelse
                    </div>
                </td>
                <td class="text-center">
                    <div class="d-flex justify-content-center gap-1">
                        @if($item->role_as != 'Admin')
                            @if(App\Helpers\PermissionHelper::check('team_management', 'edit'))
                            <button onclick="permission_modal({{$item->id}})" class="btn btn-primary btn-sm p-1 px-2" data-bs-toggle="modal" data-bs-target="#permission_modal" title="Manage Permissions">
                                <i class="fa fa-shield"></i>
                            </button>
                            @endif
                        @endif

                        @if(App\Helpers\PermissionHelper::check('team_management', 'edit'))
                        <button onclick="edit_modal({{$item->id}})" class="btn btn-warning btn-sm p-1 px-2" data-bs-toggle="modal" data-bs-target="#edit_modal" title="Edit Member">
                            <i class="fa fa-edit"></i>
                        </button>
                        @endif

                        @if(App\Helpers\PermissionHelper::check('team_management', 'delete'))
                            @if (auth()->user()->role_as == 'Admin' && auth()->user()->id != $item->id)
                                <button onclick="delete_team({{$item->id}})" class="btn btn-danger btn-sm p-1 px-2" title="Delete Member">
                                    <i class="fa fa-trash-o"></i>
                                </button>
                            @endif
                        @endif
                    </div>
                </td>
            </tr>
            @endforeach
            @if($team->isEmpty())
            <tr>
                <td colspan="6" class="text-center py-4 text-muted">No team members found matching your criteria.</td>
            </tr>
            @endif
        </tbody>
    </table>
</div>

<div class="mt-4 d-flex justify-content-between align-items-center px-2">
    <div class="f-12 text-muted">
        Showing {{ $team->firstItem() }} to {{ $team->lastItem() }} of {{ $team->total() }} entries
    </div>
    <div class="pages">
        {{$team->onEachSide(1)->links()}}
    </div>
</div>
