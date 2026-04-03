@extends('layouts.admin.app')

@section('title', 'Team Management')

@section('css')
<style>
    .card-header-primary {
        background: linear-gradient(90deg, #242934 0%, #3e4455 100%);
        color: white;
    }
    .f-12 { font-size: 12px; }
    .f-10 { font-size: 10px; }
</style>
@endsection

@section('breadcrumb-items')
    <li class="breadcrumb-item">Team Management</li>
@endsection

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                @if(App\Helpers\PermissionHelper::check('team_management', 'add'))
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header card-header-primary py-3">
                        <h6 class="mb-0 fw-bold"><i class="fa fa-user-plus me-2"></i>Add New Team Member</h6>
                    </div>
                    <form action="{{route('team.store')}}" method="POST" id="team_form" class="card-body">
                        @csrf
                        <div class="row g-3 align-items-end">
                            <div class="col-md-2">
                                <label class="form-label fw-bold f-12 text-muted uppercase">Name</label>
                                <input type="text" name="name" id="name" placeholder="John Doe" class="form-control form-control-sm" required>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label fw-bold f-12 text-muted uppercase">Phone No</label>
                                <input type="tel" name="phone" id="phone" placeholder="9876543210" class="form-control form-control-sm" required>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label fw-bold f-12 text-muted uppercase">Email</label>
                                <input type="email" name="email" id="email" placeholder="email@example.com" class="form-control form-control-sm" required>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label fw-bold f-12 text-muted uppercase">Password</label>
                                <input type="text" name="password" id="password" placeholder="Create password" class="form-control form-control-sm" required>
                            </div>
                            <div class="col-md-1">
                                <label class="form-label fw-bold f-12 text-muted uppercase">Role</label>
                                <select name="role_as" id="role_as" class="form-select form-select-sm" required>
                                    <option value="">Role</option>
                                    <option value="Employee">Employee</option>
                                    <option value="Manager">Manager</option>
                                    <option value="Sale Executive">Sale Executive</option>
                                    <option value="Senior Sale Executive">Senior Sale Executive</option>
                                    <option value="Data Entry">Data Entry</option>
                                    <option value="EA">EA</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label fw-bold f-12 text-muted uppercase">Assign Manager</label>
                                <select name="manager_ids[]" id="manager_ids" class="form-select form-select-sm js-example-basic-multiple" multiple>
                                    @foreach($managers as $manager)
                                        <option value="{{ $manager->id }}">{{ $manager->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-1">
                                <button type="submit" id="add_data" class="btn btn-primary btn-sm w-100 shadow-sm" title="Add Member">
                                    <i class="fa fa-plus"></i>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
                @endif

                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div id="basic-2_wrapper" class="dataTables_wrapper">
                            <div class="row mb-3 align-items-center">
                                <div class="col-sm-12 col-md-6">
                                    <div class="dataTables_length d-flex align-items-center gap-2">
                                        <label class="mb-0">Show</label>
                                        <select name="basic-2_value" id="basic-2_value" class="form-select form-select-sm w-auto">
                                            <option value="50">50</option>
                                            <option value="250" selected>250</option>
                                            <option value="500">500</option>
                                        </select>
                                        <label class="mb-0">entries</label>
                                    </div>
                                </div>
                                <div class="col-sm-12 col-md-6 text-md-end">
                                    <div class="dataTables_filter d-inline-block">
                                        <div class="input-group">
                                            <span class="input-group-text bg-light border-end-0"><i class="fa fa-search text-muted f-12"></i></span>
                                            <input type="search" id="basic-2_search" class="form-control form-control-sm border-start-0" placeholder="Search team members...">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="dt-ext" id="get_datatable">
                            <div class="loader-box text-center py-5">
                                <div class="loader-37"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="permission_modal" aria-labelledby="permissionModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content border-0">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title fw-bold"><i class="fa fa-shield me-2"></i>User Permissions</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="permission_html">
                    <div class="loader-box text-center py-5">
                        <div class="loader-37"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="edit_modal" aria-labelledby="mySmallModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" id="ajax_html"></div>
    </div>
@endsection

@section('script')
    <script>
        $(document).ready(function(){
            get_datatable();
            $('.js-example-basic-multiple').select2();
            $("#name").focus();
        });

        $(document).on('click','.pages a',function(n){
            n.preventDefault();
            var page = $(this).attr('href').split("page=")[1];
            get_datatable(page);
        });

        function debounce(func, wait) {
            let timeout;
            return function(...args) {
                const context = this;
                clearTimeout(timeout);
                timeout = setTimeout(() => func.apply(context, args), wait);
            };
        }

        function get_datatable(page){
            var $container = $('#get_datatable');
            if ($container.length) {
                $container.html('<div class="loader-box text-center py-5"><div class="loader-37"></div></div>');
                var value = $('#basic-2_value').val();
                var search = $('#basic-2_search').val();
                var page = page ?? 1;

                $.ajax({
                    url: '{{ route("team.datatable") }}',
                    data: { page: page, value: value, search: search, _token: "{{csrf_token() }}" },
                    type: 'GET',
                    success: function(data) {
                        $container.html(data);
                        $('#basic-test').DataTable({ 
                            dom: '{{ auth()->user()->role_as == "Admin" ? "Brt" : "rt" }}', 
                            "pageLength": -1, 
                            responsive: true, 
                                                                                    ordering: true 
                        });
                    }
                });
            }
        }

        $('#basic-2_search').on('keyup search', debounce(function() { get_datatable(); }, 500));
        $('#basic-2_value').on('change', function() { get_datatable(); });

        function edit_modal(id){
            var url = "{{route('team.edit_modal',":id")}}";
            url = url.replace(':id',id);
            $('#ajax_html').html('<div class="loader-box text-center py-5"><div class="loader-37"></div></div>');
            $.get(url, function(data){
                $('#ajax_html').html(data);
            });
        }

        function permission_modal(id){
            $('#permission_html').html('<div class="loader-box text-center py-5"><div class="loader-37"></div></div>');
            $.ajax({
                url: "{{ route('role_permission.get') }}",
                type: 'GET',
                data: { user_id: id },
                success: function(response) {
                    $('#permission_html').html(response);
                }
            });
        }

        $(document).on('submit', '#permission_form', function(e) {
            e.preventDefault();
            var form = $(this);
            var btn = form.find('button[type="submit"]');
            var originalHtml = btn.html();
            btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin me-1"></i> Saving...');

            $.ajax({
                url: form.attr('action'),
                type: 'POST',
                data: form.serialize(),
                success: function(data) {
                    if (data.result == 1) {
                        $.notify({ title: 'Success', message: data.message }, { type: 'success' });
                        $('#permission_modal').modal('hide');
                    } else {
                        $.notify({ title: 'Error', message: data.message }, { type: 'danger' });
                    }
                    btn.prop('disabled', false).html(originalHtml);
                },
                error: function() {
                    $.notify({ title: 'Error', message: 'Something went wrong!' }, { type: 'danger' });
                    btn.prop('disabled', false).html(originalHtml);
                }
            });
        });

        $(document).on('submit','#team_form',function(event){
            event.preventDefault();
            var form = event.target;
            var form_data = new FormData(form);
            var $submitBtn = $(form).find('button[type="submit"]');
            var originalBtnHtml = $submitBtn.html();
            
            $submitBtn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i>');
            
            $.ajax({
                url: $(form).attr('action'),
                type: 'POST',
                data: form_data,
                processData: false,
                contentType: false,
                success: function(data){
                    if(data.result == 1){
                        $.notify({ title:'Success', message:data.message }, { type:'success', });
                        get_datatable();
                        form.reset();
                        $("#name").focus();
                    }else{
                        $.notify({ title:'Error', message:data.message }, { type:'danger', });
                    }
                    $submitBtn.prop('disabled', false).html(originalBtnHtml);
                },
                error: function() {
                    $submitBtn.prop('disabled', false).html(originalBtnHtml);
                }
            });
        });

        function delete_team(id){
            swal({
                title: "Are you sure?",
                text: "This team member will be permanently removed!",
                icon: "warning",
                buttons: true,
                dangerMode: true,
            })
            .then((willDelete) => {
                if (willDelete) {
                    var url = "{{route('team.delete',":id")}}";
                    url = url.replace(':id',id);
                    $.get(url, function(data){
                        if(data.result == 1){
                            get_datatable();
                            $.notify({ title:'Deleted', message:data.message}, { type:'danger', });
                        } else {
                            $.notify({ title:'Error', message:data.message}, { type:'danger', });
                        }
                    })
                }
            })
        }
    </script>
@endsection
