<div class="modal-content border-0">
    <div class="modal-header">
        <h5 class="modal-title fw-bold">Edit Team Member</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
    </div>
    <form action="{{ route('team.store') }}" method="POST" id="edit_team_form">
        @csrf
        <input type="hidden" name="user_id" value="{{ $user->id }}">
        <div class="modal-body p-4">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-bold f-12 text-muted">NAME</label>
                    <input type="text" name="name" value="{{ $user->name }}" class="form-control" placeholder="Enter name" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold f-12 text-muted">PHONE</label>
                    <input type="tel" name="phone" value="{{ $user->phone }}" class="form-control" placeholder="Enter phone" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold f-12 text-muted">EMAIL</label>
                    <input type="email" name="email" value="{{ $user->email }}" class="form-control" placeholder="Enter email" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold f-12 text-muted">PASSWORD</label>
                    <input type="text" name="password" class="form-control" placeholder="New Password">
                    <small class="text-info f-10 mt-1 d-block"><i class="fa fa-info-circle me-1"></i>Current: <strong>{{ $user->show_password }}</strong></small>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold f-12 text-muted">ROLE</label>
                    <select name="role_as" class="form-select" required>
                        <option value="Employee" {{ $user->role_as == 'Employee' ? 'selected' : '' }}>Employee</option>
                        <option value="Manager" {{ $user->role_as == 'Manager' ? 'selected' : '' }}>Manager</option>
                        <option value="Sale Executive" {{ $user->role_as == 'Sale Executive' ? 'selected' : '' }}>Sale Executive</option>
                        <option value="Senior Sale Executive" {{ $user->role_as == 'Senior Sale Executive' ? 'selected' : '' }}>Senior Sale Executive</option>
                        <option value="Data Entry" {{ $user->role_as == 'Data Entry' ? 'selected' : '' }}>Data Entry</option>
                        <option value="EA" {{ $user->role_as == 'EA' ? 'selected' : '' }}>EA</option>
                        @if($user->role_as == 'Admin')
                            <option value="Admin" selected>Admin</option>
                        @endif
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold f-12 text-muted">ASSIGNED MANAGERS</label>
                    <select name="manager_ids[]" id="edit_manager_ids" class="form-select js-example-basic-multiple-modal" multiple>
                        @php
                            $selectedManagers = $user->managedBy->pluck('id')->toArray();
                        @endphp
                        @foreach($managers as $manager)
                            <option value="{{ $manager->id }}" {{ in_array($manager->id, $selectedManagers) ? 'selected' : '' }}>
                                {{ $manager->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
        <div class="modal-footer border-0">
            <button type="button" class="btn btn-secondary px-4 btn-sm" data-bs-dismiss="modal">Close</button>
            <button type="submit" class="btn btn-primary px-4 btn-sm shadow-sm">Update Details</button>
        </div>
    </form>
</div>

<script>
    $(document).ready(function() {
        $('.js-example-basic-multiple-modal').select2({
            dropdownParent: $('#edit_modal')
        });
    });

    $('#edit_team_form').on('submit', function(event) {
        event.preventDefault();
        var form = event.target;
        var form_data = new FormData(form);
        var $submitBtn = $(form).find('button[type="submit"]');
        $submitBtn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin me-1"></i> Updating...');

        $.ajax({
            url: $(form).attr('action'),
            type: 'POST',
            data: form_data,
            processData: false,
            contentType: false,
            success: function(data) {
                if (data.result == 1) {
                    $.notify({ title: 'Success', message: data.message }, { type: 'success' });
                    $('#edit_modal').modal('hide');
                    get_datatable();
                } else {
                    $.notify({ title: 'Error', message: data.message }, { type: 'danger' });
                    $submitBtn.prop('disabled', false).html('Update Details');
                }
            },
            error: function() {
                $submitBtn.prop('disabled', false).html('Update Details');
            }
        });
    });
</script>
