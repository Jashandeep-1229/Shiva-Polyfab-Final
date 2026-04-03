<form id="permission_form" action="{{ route('role_permission.store') }}" method="POST">
    @csrf
    <input type="hidden" name="role_name" value="{{ $role }}">
    @if($user_id ?? false)
    <input type="hidden" name="user_id" value="{{ $user_id }}">
    @endif
    <div class="table-responsive">
        <table class="table table-bordered table-hover custom-permission-table">
            <thead class="text-center text-white">
                <tr style="background-color: #7366ff !important; color: #ffffff !important;">
                    <th rowspan="2" class="align-middle text-start ps-4" style="width: 250px; color: #ffffff !important; background-color: #7366ff !important;">MENU NAME</th>
                    <th colspan="4" class="py-2" style="color: #ffffff !important; background-color: #7366ff !important; border-bottom: 1px solid rgba(255,255,255,0.2) !important;">ACTIONS</th>
                    <th rowspan="2" class="align-middle" style="width: 200px; color: #ffffff !important; background-color: #7366ff !important;">DATA ACCESS</th>
                </tr>
                    <th style="width: 100px; color: #ffffff !important; background-color: #7366ff !important;" class="py-2">View</th>
                    <th style="width: 100px; color: #ffffff !important; background-color: #7366ff !important;" class="py-2">Add</th>
                    <th style="width: 100px; color: #ffffff !important; background-color: #7366ff !important;" class="py-2">Edit</th>
                    <th style="width: 100px; color: #ffffff !important; background-color: #7366ff !important;" class="py-2">Next Proc</th>
                </tr>
            </thead>
            <tbody>
                @foreach($menus as $group => $items)
                <tr style="background-color: #f8f9fa;">
                    <td colspan="6" class="fw-bold text-primary py-2 ps-4" style="border-left: 4px solid #7366ff;">{{ $group }}</td>
                </tr>
                    @foreach($items as $key => $name)
                    @php
                        $perm = $permissions[$key] ?? null;
                    @endphp
                    <tr>
                        <td class="ps-5">{{ $name }}</td>
                        <td class="text-center">
                            <div class="checkbox checkbox-primary">
                                <input id="view_{{ $key }}" type="checkbox" name="permissions[{{ $key }}][view]" {{ ($perm && $perm->can_view) ? 'checked' : '' }}>
                                <label for="view_{{ $key }}"></label>
                            </div>
                        </td>
                        <td class="text-center">
                            @if(strpos($key, '_heading') === false)
                            <div class="checkbox checkbox-success">
                                <input id="add_{{ $key }}" type="checkbox" name="permissions[{{ $key }}][add]" {{ ($perm && $perm->can_add) ? 'checked' : '' }}>
                                <label for="add_{{ $key }}"></label>
                            </div>
                            @endif
                        </td>
                        <td class="text-center">
                            @if(strpos($key, '_heading') === false)
                            <div class="checkbox checkbox-warning">
                                <input id="edit_{{ $key }}" type="checkbox" name="permissions[{{ $key }}][edit]" {{ ($perm && $perm->can_edit) ? 'checked' : '' }}>
                                <label for="edit_{{ $key }}"></label>
                            </div>
                            @endif
                        </td>

                        <td class="text-center">
                            @if(strpos($key, '_heading') === false)
                            <div class="checkbox checkbox-info">
                                <input id="next_process_{{ $key }}" type="checkbox" name="permissions[{{ $key }}][next_process]" {{ ($perm && $perm->can_next_process) ? 'checked' : '' }}>
                                <label for="next_process_{{ $key }}"></label>
                            </div>
                            @endif
                        </td>
                        <td>
                            @if(strpos($key, '_heading') === false)
                            <select name="permissions[{{ $key }}][data_access]" class="form-select form-select-sm">
                                <option value="owned" {{ ($perm && $perm->data_access == 'owned') ? 'selected' : ( (!$perm) ? 'selected' : '' ) }}>Private (Own Data)</option>
                                <option value="all" {{ ($perm && $perm->data_access == 'all') ? 'selected' : '' }}>Public (All Data)</option>
                            </select>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="text-end mt-4">
        <button type="submit" class="btn btn-primary shadow-sm"><i class="fa fa-save me-1"></i> Save Permissions</button>
    </div>
</form>

<style>
    .custom-permission-table thead th {
        font-size: 13px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .custom-permission-table tbody td {
        vertical-align: middle;
    }
    
    /* Custom Checkbox Styling to show real checkmark */
    .checkbox label {
        display: inline-block;
        position: relative;
        padding-left: 25px;
        cursor: pointer;
        margin-bottom: 0;
        vertical-align: middle;
    }
    .checkbox label::before {
        content: "";
        display: inline-block;
        position: absolute;
        width: 18px;
        height: 18px;
        left: 0;
        top: 50%;
        transform: translateY(-50%);
        border: 2px solid #ddd;
        border-radius: 3px;
        background-color: #fff;
        transition: all 0.2s;
    }
    .checkbox input[type="checkbox"] {
        display: none;
    }
    .checkbox input[type="checkbox"]:checked + label::before {
        background-color: #7366ff;
        border-color: #7366ff;
    }
    .checkbox.checkbox-success input[type="checkbox"]:checked + label::before {
        background-color: #51bb25;
        border-color: #51bb25;
    }
    .checkbox.checkbox-warning input[type="checkbox"]:checked + label::before {
        background-color: #f8d62b;
        border-color: #f8d62b;
    }
    .checkbox.checkbox-info input[type="checkbox"]:checked + label::before {
        background-color: #167dff;
        border-color: #167dff;
    }
    .checkbox.checkbox-danger input[type="checkbox"]:checked + label::before {
        background-color: #ef3341;
        border-color: #ef3341;
    }
    .checkbox input[type="checkbox"]:checked + label::after {
        content: "\f00c";
        font-family: 'FontAwesome';
        position: absolute;
        left: 3px;
        top: 50%;
        transform: translateY(-50%);
        font-size: 10px;
        color: #fff;
    }
</style>
