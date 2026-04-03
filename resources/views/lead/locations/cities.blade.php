@extends('layouts.admin.app')

@section('title', 'Cities Management')

@section('breadcrumb-items')
    <li class="breadcrumb-item">Locations</li>
    <li class="breadcrumb-item active">Cities</li>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header pb-2">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5>Manage Cities</h5>
                        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addCityModal">Add New City</button>
                    </div>
                </div>
                <div class="card-body">
                    <form action="{{ route('lead.locations.cities') }}" method="GET" class="mb-4">
                        <div class="row">
                            <div class="col-md-4">
                                <label class="form-label">Filter by State</label>
                                <select name="state_id" class="form-select form-select-sm" onchange="this.form.submit()">
                                    <option value="">- All States -</option>
                                    @foreach($states as $st)
                                        <option value="{{ $st->id }}" {{ request('state_id') == $st->id ? 'selected' : '' }}>{{ $st->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </form>

                    <div class="table-responsive">
                        <table class="table table-bordered table-striped" id="cities-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>City Name</th>
                                    <th>State</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($cities as $city)
                                <tr>
                                    <td>{{ ($cities->currentPage() - 1) * $cities->perPage() + $loop->iteration }}</td>
                                    <td>{{ $city->name }}</td>
                                    <td>{{ $city->state->name }}</td>
                                    <td>
                                        <span class="badge badge-{{ $city->status ? 'success' : 'danger' }}">
                                            {{ $city->status ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                    <td>
                                        <button class="btn btn-info btn-xs" onclick='editCity(@json($city))'><i class="fa fa-pencil"></i></button>
                                        <form action="{{ route('lead.locations.cities.destroy', $city->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this city?')">
                                            @csrf @method('DELETE')
                                            <button class="btn btn-danger btn-xs"><i class="fa fa-trash"></i></button>
                                        </form>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                        <div class="mt-3">
                            {{ $cities->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Add City Modal --}}
<div class="modal fade" id="addCityModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('lead.locations.cities.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Add New City</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">State</label>
                        <select name="state_id" class="form-select" required>
                            <option value="">Select State</option>
                            @foreach($states as $st)
                                <option value="{{ $st->id }}">{{ $st->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">City Name</label>
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
                    <button type="submit" class="btn btn-primary">Save City</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Edit City Modal --}}
<div class="modal fade" id="editCityModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="editCityForm" method="POST">
                @csrf @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title">Edit City</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">State</label>
                        <select name="state_id" id="edit_city_state_id" class="form-select" required>
                            @foreach($states as $st)
                                <option value="{{ $st->id }}">{{ $st->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">City Name</label>
                        <input type="text" name="name" id="edit_city_name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select name="status" id="edit_city_status" class="form-select">
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Update City</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
    function editCity(city) {
        $('#edit_city_name').val(city.name);
        $('#edit_city_state_id').val(city.state_id);
        $('#edit_city_status').val(city.status);
        $('#editCityForm').attr('action', "{{ url('lead/locations/cities') }}/" + city.id);
        $('#editCityModal').modal('show');
    }
</script>
@endsection
