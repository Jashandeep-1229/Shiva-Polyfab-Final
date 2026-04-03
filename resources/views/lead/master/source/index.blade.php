@extends('layouts.admin.app')

@section('title', 'Lead Sources')

@section('breadcrumb-items')
    <li class="breadcrumb-item">Master</li>
    <li class="breadcrumb-item active">Lead Source</li>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-4">
            <div class="card">
                <div class="card-header pb-0">
                    <h5 id="form-title">Add Source</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('lead.source.store') }}" method="POST" id="source-form">
                        @csrf
                        <input type="hidden" name="id" id="source-id">
                        <div class="mb-3">
                            <label class="form-label">Source Name</label>
                            <input type="text" name="name" id="source-name" class="form-control" placeholder="e.g. Google, Indiamart" required>
                        </div>
                        <div class="d-flex justify-content-between">
                            <button type="submit" class="btn btn-primary">Save Source</button>
                            <button type="button" class="btn btn-light d-none" id="cancel-edit">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-md-8">
            <div class="card">
                <div class="card-header pb-0">
                    <h5>Source List</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Name</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($sources as $source)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $source->name }}</td>
                                    <td>
                                        <button class="btn btn-info btn-xs edit-source" data-id="{{ $source->id }}" data-name="{{ $source->name }}">Edit</button>
                                        <a href="{{ route('lead.source.delete', $source->id) }}" class="btn btn-danger btn-xs" onclick="return confirm('Delete this source?')">Delete</a>
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
        $('.edit-source').click(function() {
            let id = $(this).data('id');
            let name = $(this).data('name');
            
            $('#source-id').val(id);
            $('#source-name').val(name);
            $('#form-title').text('Edit Source');
            $('#cancel-edit').removeClass('d-none');
        });

        $('#cancel-edit').click(function() {
            $('#source-id').val('');
            $('#source-name').val('');
            $('#form-title').text('Add Source');
            $(this).addClass('d-none');
        });
    });
</script>
@endsection
