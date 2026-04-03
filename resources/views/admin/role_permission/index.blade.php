@extends('layouts.admin.app')
@section('title', 'Role Permissions')

@section('css')
@endsection

@section('breadcrumb-items')
    <li class="breadcrumb-item">Team</li>
    <li class="breadcrumb-item active">Role Permissions</li>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header pb-0">
                    <h5>Select Role to Configure</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-4 align-items-end">
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Select Role</label>
                            <select id="role_selector" class="form-select shadow-sm border-primary">
                                <option value="">-- Select Role --</option>
                                @foreach($roles as $role)
                                    <option value="{{ $role }}">{{ $role }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Add New Role Name</label>
                            <div class="input-group">
                                <input type="text" id="new_role_name" class="form-control" placeholder="e.g. Supervisor">
                                <button class="btn btn-secondary" type="button" id="btn_add_role">Add</button>
                            </div>
                        </div>
                    </div>

                    <div id="permission_container" class="mt-4">
                        <div class="text-center p-5 text-muted">
                            <i class="fa fa-shield fa-3x mb-3"></i>
                            <p>Please select a role from the dropdown above to manage its permissions.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
    $('#btn_add_role').on('click', function() {
        var newRole = $('#new_role_name').val().trim();
        if (newRole) {
            // Check if already exists
            var exists = false;
            $('#role_selector option').each(function() {
                if ($(this).val().toLowerCase() == newRole.toLowerCase()) exists = true;
            });

            if (!exists) {
                $('#role_selector').append($('<option>', {
                    value: newRole,
                    text: newRole
                }));
                $('#role_selector').val(newRole).trigger('change');
                $('#new_role_name').val('');
                $.notify({ title: 'Success', message: 'Role added to list. Configure permissions below.' }, { type: 'success' });
            } else {
                $('#role_selector').val(newRole).trigger('change');
                $.notify({ title: 'Info', message: 'Role already exists.' }, { type: 'info' });
            }
        }
    });

    $('#role_selector').on('change', function() {
        var role = $(this).val();
        if (role) {
            $('#permission_container').html('<div class="text-center p-5"><i class="fa fa-spinner fa-spin fa-2x"></i><p class="mt-2">Loading Permissions...</p></div>');
            $.ajax({
                url: "{{ route('role_permission.get') }}",
                type: 'GET',
                data: { role: role },
                success: function(response) {
                    $('#permission_container').html(response);
                }
            });
        } else {
            $('#permission_container').html('<div class="text-center p-5 text-muted"><i class="fa fa-shield fa-3x mb-3"></i><p>Please select a role from the dropdown above to manage its permissions.</p></div>');
        }
    });

    $(document).on('submit', '#permission_form', function(e) {
        e.preventDefault();
        var form = $(this);
        var btn = form.find('button[type="submit"]');
        btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin me-1"></i> Saving...');

        $.ajax({
            url: form.attr('action'),
            type: 'POST',
            data: form.serialize(),
            success: function(data) {
                if (data.result == 1) {
                    $.notify({ title: 'Success', message: data.message }, { type: 'success' });
                } else {
                    $.notify({ title: 'Error', message: data.message }, { type: 'danger' });
                }
                btn.prop('disabled', false).html('Save Permissions');
            },
            error: function() {
                $.notify({ title: 'Error', message: 'Something went wrong!' }, { type: 'danger' });
                btn.prop('disabled', false).html('Save Permissions');
            }
        });
    });
</script>
@endsection
