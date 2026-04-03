@extends('layouts.admin.app')

@section('title', 'States Management')

@section('breadcrumb-items')
    <li class="breadcrumb-item">Locations</li>
    <li class="breadcrumb-item active">States</li>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header pb-2">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5>Manage States</h5>
                        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addStateModal">Add New State</button>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive px-4 pb-4">
                        <table class="table table-bordered table-striped" id="states-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>State Name</th>
                                    <th>Total Cities</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($states as $state)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $state->name }}</td>
                                    <td>
                                        <a href="{{ route('lead.locations.cities', ['state_id' => $state->id]) }}" class="badge badge-primary">
                                            {{ $state->cities_count }} Cities
                                        </a>
                                    </td>
                                    <td>
                                        <span class="badge badge-{{ $state->status ? 'success' : 'danger' }}">
                                            {{ $state->status ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                    <td>
                                        <button class="btn btn-info btn-xs" onclick="editState({{ $state->id }}, '{{ $state->name }}', {{ $state->status }})"><i class="fa fa-pencil"></i></button>
                                        <form action="{{ route('lead.locations.states.destroy', $state->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this state and all its cities?')">
                                            @csrf @method('DELETE')
                                            <button class="btn btn-danger btn-xs"><i class="fa fa-trash"></i></button>
                                        </form>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Add State Modal --}}
<div class="modal fade" id="addStateModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('lead.locations.states.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Add New State</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">State Name</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save State</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Edit State Modal --}}
<div class="modal fade" id="editStateModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="editStateForm" method="POST">
                @csrf @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title">Edit State</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">State Name</label>
                        <input type="text" name="name" id="edit_state_name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select name="status" id="edit_state_status" class="form-select">
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Update State</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
    function editState(id, name, status) {
        $('#edit_state_name').val(name);
        $('#edit_state_status').val(status);
        $('#editStateForm').attr('action', "{{ url('lead/locations/states') }}/" + id);
        $('#editStateModal').modal('show');
    }
</script>
@endsection
