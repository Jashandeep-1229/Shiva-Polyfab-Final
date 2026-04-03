@extends('layouts.admin.app')

@section('title', 'Agent Deals In Master')

@section('breadcrumb-items')
    <li class="breadcrumb-item">Agent Master</li>
    <li class="breadcrumb-item active">Deals In</li>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-4">
            <div class="card">
                <div class="card-header pb-0">
                    <h5 id="form-title">Add Deals In</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('lead.agent.deals_in.store') }}" method="POST" id="deal-form">
                        @csrf
                        <input type="hidden" name="id" id="deal-id">
                        <div class="mb-3">
                            <label class="form-label">Name</label>
                            <input type="text" name="name" id="deal-name" class="form-control" placeholder="e.g. Chemicals, Fabrics" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <select name="status" id="deal-status" class="form-control">
                                <option value="1">Active</option>
                                <option value="0">Inactive</option>
                            </select>
                        </div>
                        <div class="d-flex justify-content-between">
                            <button type="submit" class="btn btn-primary">Save</button>
                            <button type="button" class="btn btn-light d-none" id="cancel-edit">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-md-8">
            <div class="card">
                <div class="card-header pb-0">
                    <h5>Deals In List</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Name</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($deals as $deal)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $deal->name }}</td>
                                    <td>
                                        <span class="badge badge-{{ $deal->status ? 'success' : 'danger' }}">
                                            {{ $deal->status ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                    <td>
                                        <button class="btn btn-info btn-xs edit-deal" 
                                            data-id="{{ $deal->id }}" 
                                            data-name="{{ $deal->name }}"
                                            data-status="{{ $deal->status }}">Edit</button>
                                        <a href="{{ route('lead.agent.deals_in.delete', $deal->id) }}" class="btn btn-danger btn-xs" onclick="return confirm('Delete this?')">Delete</a>
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
@endsection

@section('script')
<script>
    $(document).ready(function() {
        $('.edit-deal').click(function() {
            let id = $(this).data('id');
            let name = $(this).data('name');
            let status = $(this).data('status');
            
            $('#deal-id').val(id);
            $('#deal-name').val(name);
            $('#deal-status').val(status);
            $('#form-title').text('Edit Deals In');
            $('#cancel-edit').removeClass('d-none');
        });

        $('#cancel-edit').click(function() {
            $('#deal-id').val('');
            $('#deal-name').val('');
            $('#deal-status').val('1');
            $('#form-title').text('Add Deals In');
            $(this).addClass('d-none');
        });
    });
</script>
@endsection
