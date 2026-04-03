@extends('layouts.admin.app')

@section('title', 'Team Management')

@section('breadcrumb-items')
    <li class="breadcrumb-item">Master</li>
    <li class="breadcrumb-item active">Team Management</li>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-4">
            <div class="card">
                <div class="card-header pb-0">
                    <h5 id="form-title">Add Team Member</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('lead.team.store') }}" method="POST" id="team-form">
                        @csrf
                        <input type="hidden" name="id" id="member-id">
                        <div class="mb-3">
                            <label class="form-label">Full Name</label>
                            <input type="text" name="name" id="member-name" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email Address</label>
                            <input type="email" name="email" id="member-email" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Phone No</label>
                            <input type="text" name="phone" id="member-phone" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Role</label>
                            <select name="role" id="member-role" class="form-select" required>
                                <option value="Sale Executive">Sale Executive</option>
                                <option value="Senior Sale Executive">Senior Sale Executive</option>
                                <option value="Admin">Admin</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Reporting Manager (Parent)</label>
                            <select name="parent_id" id="member-parent" class="form-select">
                                <option value="">None (Top Level)</option>
                                @foreach($parents as $parent)
                                    <option value="{{ $parent->id }}">{{ $parent->name }} ({{ $parent->role }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Password</label>
                            <input type="text" name="password" id="member-password" class="form-control" placeholder="Leave blank to keep current">
                        </div>
                        <div class="d-flex justify-content-between">
                            <button type="submit" class="btn btn-primary">Save Member</button>
                            <button type="button" class="btn btn-light d-none" id="cancel-edit">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-md-8">
            <div class="card">
                <div class="card-header pb-0">
                    <h5>Team List</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Name</th>
                                    <th>Email / Phone</th>
                                    <th>Role</th>
                                    <th>Reporting To</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($users as $user)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>
                                        <strong>{{ $user->name }}</strong><br>
                                        <small class="text-muted">Pass: {{ $user->show_password }}</small>
                                    </td>
                                    <td>
                                        {{ $user->email }}<br>
                                        {{ $user->phone }}
                                    </td>
                                    <td><span class="badge badge-light-primary">{{ $user->role }}</span></td>
                                    <td>{{ $user->parent->name ?? 'None' }}</td>
                                    <td>
                                        @if($user->status == 1)
                                            <span class="badge badge-success">Active</span>
                                        @else
                                            <span class="badge badge-danger">Inactive</span>
                                        @endif
                                    </td>
                                    <td>
                                        <button class="btn btn-info btn-xs edit-member" 
                                            data-id="{{ $user->id }}" 
                                            data-name="{{ $user->name }}"
                                            data-email="{{ $user->email }}"
                                            data-phone="{{ $user->phone }}"
                                            data-role="{{ $user->role }}"
                                            data-parent="{{ $user->parent_id }}">Edit</button>
                                        
                                        @if($user->id != auth()->id())
                                            <a href="{{ route('lead.team.delete', $user->id) }}" class="btn btn-danger btn-xs" onclick="return confirm('Delete this member?')">Delete</a>
                                        @endif
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
        $('.edit-member').click(function() {
            let id = $(this).data('id');
            let name = $(this).data('name');
            let email = $(this).data('email');
            let phone = $(this).data('phone');
            let role = $(this).data('role');
            let parent = $(this).data('parent');
            
            $('#member-id').val(id);
            $('#member-name').val(name);
            $('#member-email').val(email);
            $('#member-phone').val(phone);
            $('#member-role').val(role);
            $('#member-parent').val(parent);
            $('#member-password').attr('placeholder', 'Leave blank to keep current');
            
            $('#form-title').text('Edit Team Member');
            $('#cancel-edit').removeClass('d-none');
        });

        $('#cancel-edit').click(function() {
            $('#member-id').val('');
            $('#member-name').val('');
            $('#member-email').val('');
            $('#member-phone').val('');
            $('#member-role').val('Executive');
            $('#member-parent').val('');
            $('#member-password').attr('placeholder', '');
            
            $('#form-title').text('Add Team Member');
            $(this).addClass('d-none');
        });
    });
</script>
@endsection
