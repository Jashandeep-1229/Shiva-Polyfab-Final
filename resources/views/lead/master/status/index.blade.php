@extends('layouts.admin.app')

@section('title', 'Lead Steps (Pipeline)')

@section('breadcrumb-items')
    <li class="breadcrumb-item">Master</li>
    <li class="breadcrumb-item active">Lead Steps</li>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-4">
            <div class="card">
                <div class="card-header pb-0">
                    <h5 id="form-title">Add New Step</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('lead.status.store') }}" method="POST" id="status-form">
                        @csrf
                        <input type="hidden" name="id" id="status-id">
                        <div class="mb-3">
                            <label class="form-label">Step Name</label>
                            <input type="text" name="name" id="status-name" class="form-control" placeholder="e.g. New Lead, Quoted, Closed" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Sort Order</label>
                            <input type="number" name="sort_order" id="status-order" class="form-control" value="0">
                        </div>
                        <div class="mb-3">
                            <div class="form-check form-switch mt-2">
                                <input class="form-check-input" type="checkbox" name="is_required" id="status-required" value="1" checked>
                                <label class="form-check-label" for="status-required">Is Required Step?</label>
                            </div>
                            <small class="text-muted">If required, following steps will only be visible after completing this one.</small>
                        </div>
                        <div class="mb-3" style="display: none;">
                            <label class="form-label">Step Form Fields (Comma Separated Labels)</label>
                            <textarea name="form_fields" id="status-fields" class="form-control" rows="2" placeholder="e.g. Width, Height, Remarks"></textarea>
                            <small class="text-muted f-12">Leave blank if no extra fields needed.</small>
                        </div>
                        <div class="d-flex justify-content-between">
                            <button type="submit" class="btn btn-primary">Save Step</button>
                            <button type="button" class="btn btn-light d-none" id="cancel-edit">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>
            <div class="alert alert-info mt-3">
                <i class="fa fa-info-circle"></i> Tip: Drag and drop the steps in the list to re-order them instantly.
            </div>
        </div>
        <div class="col-md-8">
            <div class="card">
                <div class="card-header pb-0">
                    <h5>Steps List (Draggable)</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th width="50">#</th>
                                    <th>Name</th>
                                    <th>Required</th>
                                    <th>Sort Order</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody id="sortable-steps">
                                @foreach($statuses as $status)
                                <tr data-id="{{ $status->id }}">
                                    <td class="text-center cursor-move"><i class="fa fa-bars text-muted"></i></td>
                                    <td>
                                        <div class="fw-bold">{{ $status->name }}</div>
                                        @if($status->slug) <small class="text-muted">{{ $status->slug }}</small> @endif
                                    </td>
                                    <td class="text-center">
                                        <button class="btn btn-xs {{ $status->is_required ? 'btn-primary' : 'btn-light text-muted' }}" 
                                                style="padding: 2px 10px; font-size: 10px;"
                                                onclick="toggleStatusField({{ $status->id }}, 'is_required', {{ $status->is_required ? 0 : 1 }})">
                                            {{ $status->is_required ? 'Yes' : 'No' }}
                                        </button>
                                    </td>
                                    <td class="order-val">{{ $status->sort_order }}</td>
                                    <td>
                                        <button class="btn btn-info btn-xs edit-status" 
                                                data-id="{{ $status->id }}" 
                                                data-name="{{ $status->name }}" 
                                                data-order="{{ $status->sort_order }}"
                                                data-fields="{{ $status->form_fields }}"
                                                data-required="{{ $status->is_required }}">Edit</button>
                                        <a href="{{ route('lead.status.delete', $status->id) }}" class="btn btn-danger btn-xs" onclick="return confirm('Delete this step?')">Delete</a>
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
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script>
    function toggleStatusField(id, field, value) {
        $.ajax({
            url: "{{ route('lead.status.update-field') }}",
            method: 'POST',
            data: {
                _token: "{{ csrf_token() }}",
                id: id,
                field: field,
                value: value
            },
            success: function(response) {
                location.reload(); // Quick refresh to update state or I can just update the button classes
            }
        });
    }

    $(document).ready(function() {
        // Edit Functionality
        $('.edit-status').click(function() {
            let id = $(this).data('id');
            let name = $(this).data('name');
            let order = $(this).data('order');
            let fields = $(this).data('fields');
            let required = $(this).data('required');
            
            $('#status-id').val(id);
            $('#status-name').val(name);
            $('#status-order').val(order);
            $('#status-fields').val(fields);
            $('#status-required').prop('checked', required == 1);
            $('#form-title').text('Edit Step');
            $('#cancel-edit').removeClass('d-none');
        });

        $('#cancel-edit').click(function() {
            $('#status-id').val('');
            $('#status-name').val('');
            $('#status-order').val('0');
            $('#status-fields').val('');
            $('#status-required').prop('checked', true);
            $('#form-title').text('Add New Step');
            $(this).addClass('d-none');
        });

        // Drag and Drop Sorting
        const el = document.getElementById('sortable-steps');
        if (el) {
            new Sortable(el, {
                animation: 150,
                handle: '.cursor-move',
                onEnd: function() {
                    let order = [];
                    $('#sortable-steps tr').each(function() {
                        order.push($(this).data('id'));
                    });

                    // Update order visualization
                    $('.order-val').each(function(index) {
                        $(this).text(index + 1);
                    });

                    // AJAX to save new order
                    $.ajax({
                        url: "{{ route('lead.status.update-order') }}",
                        method: 'POST',
                        data: {
                            _token: "{{ csrf_token() }}",
                            order: order
                        },
                        success: function(response) {
                            $.notify({ title:'Success', message:'Order updated' }, { type:'success', delay: 1000 });
                        }
                    });
                }
            });
        }
    });
</script>
<style>
    .cursor-move { cursor: move; }
    .sortable-ghost { opacity: 0.4; background-color: #f0f0f0; }
</style>
@endsection
