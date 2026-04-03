@extends('layouts.admin.app')

@section('title', 'Lead Tags')

@section('breadcrumb-items')
    <li class="breadcrumb-item">Master</li>
    <li class="breadcrumb-item active">Lead Tag</li>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-4">
            <div class="card">
                <div class="card-header pb-0">
                    <h5 id="form-title">Add Tag</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('lead.tag.store') }}" method="POST" id="tag-form">
                        @csrf
                        <input type="hidden" name="id" id="tag-id">
                        <div class="mb-3">
                            <label class="form-label">Tag Name</label>
                            <input type="text" name="name" id="tag-name" class="form-control" placeholder="e.g. High Priority, Regular" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Color</label>
                            <input type="color" name="color" id="tag-color" class="form-control form-control-color" value="#7366ff">
                        </div>
                        <div class="d-flex justify-content-between">
                            <button type="submit" class="btn btn-primary">Save Tag</button>
                            <button type="button" class="btn btn-light d-none" id="cancel-edit">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-md-8">
            <div class="card">
                <div class="card-header pb-0">
                    <h5>Tag List</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Name</th>
                                    <th>Color</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($tags as $tag)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $tag->name }}</td>
                                    <td><span class="badge" style="background-color: {{ $tag->color }}; color: #fff;">{{ $tag->color }}</span></td>
                                    <td>
                                        <button class="btn btn-info btn-xs edit-tag" data-id="{{ $tag->id }}" data-name="{{ $tag->name }}" data-color="{{ $tag->color }}">Edit</button>
                                        <a href="{{ route('lead.tag.delete', $tag->id) }}" class="btn btn-danger btn-xs" onclick="return confirm('Delete this tag?')">Delete</a>
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
        $('.edit-tag').click(function() {
            let id = $(this).data('id');
            let name = $(this).data('name');
            let color = $(this).data('color');
            
            $('#tag-id').val(id);
            $('#tag-name').val(name);
            $('#tag-color').val(color);
            $('#form-title').text('Edit Tag');
            $('#cancel-edit').removeClass('d-none');
        });

        $('#cancel-edit').click(function() {
            $('#tag-id').val('');
            $('#tag-name').val('');
            $('#tag-color').val('#7366ff');
            $('#form-title').text('Add Tag');
            $(this).addClass('d-none');
        });
    });
</script>
@endsection
