@extends('layouts.admin.app')

@section('title', 'Agent / Customer Master')

@section('css')
<style>
    .is-invalid {
        border-color: #dc3545 !important;
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 12 12' width='12' height='12' fill='none' stroke='%23dc3545'%3e%3ccircle cx='6' cy='6' r='4.5'/%3e%3cpath stroke-linejoin='round' d='M5.8 3.6h.4L6 6.5z'/%3e%3ccircle cx='6' cy='8.2' r='.6' fill='%23dc3545' stroke='none'/%3e%3c/svg%3e");
        background-repeat: no-repeat;
        background-position: right calc(0.375em + 0.1875rem) center;
        background-size: calc(0.75em + 0.375rem) calc(0.75em + 0.375rem);
    }
</style>
@endsection

@section('breadcrumb-items')
    <li class="breadcrumb-item">Agent / Customer Master</li>
@endsection

@section('content')
    <div class="container-fluid">
        <!-- All Client Table Start -->
        <div class="row">
            <div class="col-12">
                @if(PermissionHelper::check('agent_customer', 'add'))
                <div class="card" id="add_type">
                    <form action="{{route('agent_customer.store')}}" method="POST" id="" class="modal-content" enctype="multipart/form-data">
                        @csrf
                        <div class="card-body row py-3">
                            <div class="col-md-2 mb-2">
                                <input type="text" name="name" id="name" placeholder="Name" oninput="this.value = this.value.toUpperCase()" class="form-control form-control-sm" required>
                            </div>
                            <div class="col-md-2 mb-2">
                                <input type="tel" name="phone_no" id="phone_no" placeholder="Phone No" class="form-control form-control-sm" maxlength="10" oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 10); if(this.value.length == 10) { $(this).removeClass('is-invalid').addClass('is-valid'); } else { $(this).removeClass('is-valid').addClass('is-invalid'); }" required>
                            </div>
                            <div class="col-md-2 mb-2">
                                <select name="role" id="role" class="form-control form-control-sm" required>
                                    <option value="" selected disabled>Select Role</option>
                                    <option value="Customer">Customer</option>
                                    <option value="Agent">Agent</option>
                                </select>
                            </div>

                            <div class="col-md-2 mb-2">
                                <select name="type" id="type" class="form-control form-control-sm" required>
                                    <option value="A" selected>Type A</option>
                                    <option value="B">Type B</option>
                                </select>
                            </div>
                            <div class="col-md-2 mb-2">
                                <select name="sale_executive_id" id="sale_executive_id" class="form-control form-control-sm" required>
                                    <option value="" selected disabled>Select Executive</option>
                                    @foreach($sales_executives as $se)
                                        <option value="{{$se->id}}">{{$se->name}}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2 mb-2">
                                <input type="text" name="gst" id="gst" placeholder="GST" oninput="this.value = this.value.toUpperCase()" class="form-control form-control-sm" value="NA" required>
                            </div>
                            <div class="col-md-2 mb-2">
                                <input type="text" name="address" id="address" placeholder="Address" class="form-control form-control-sm">
                            </div>
                            <div class="col-md-2 mb-2">
                                <input type="text" name="pincode" id="pincode" placeholder="Pincode" class="form-control form-control-sm" maxlength="6" oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 6); fetchLocation(this.value, 'add')">
                            </div>
                            <div class="col-md-2 mb-2">
                                <input type="text" name="state" id="state_add" placeholder="State" class="form-control form-control-sm" readonly>
                            </div>
                            <div class="col-md-2 mb-2">
                                <input type="text" name="city" id="city_add" placeholder="City" class="form-control form-control-sm" readonly>
                            </div>
                            <div class="col-md-2 mb-2 text-end">
                                <button type="submit" id="add_data" class="btn btn-primary btn-sm w-100" >Add +</button>
                            </div>
                        </div>
                    </form>
                </div>
                @endif
                <div class="card">
                    <div class="card-body">
                        <div  id="basic-2_wrapper" class="dataTables_wrapper px-2">
                            <div class="dataTables_length">
                                <label>Show 
                                    <select name="basic-2_value"  id="basic-2_value" aria-controls="basic-2" class="form-control form-control-sm">
                                        <option value="50">50</option>
                                        <option value="250" selected>250</option>
                                        <option value="500">500</option>
                                        <option value="1000">1000</option>
                                    </select>
                                </label>
                            </div>
                            <div class="dataTables_filter d-flex align-items-center gap-2">
                                <label class="mb-0">Role:
                                    <select id="role_filter" class="form-control form-control-sm" onchange="get_datatable()">
                                        <option value="">All</option>
                                        <option value="Customer">Customer</option>
                                        <option value="Agent">Agent</option>
                                    </select>
                                </label>
                                <label class="mb-0">Type:
                                    <select id="type_filter" class="form-control form-control-sm" onchange="get_datatable()">
                                        <option value="">All</option>
                                        <option value="A">A</option>
                                        <option value="B">B</option>
                                    </select>
                                </label>
                                <label class="mb-0">Executive:
                                    <select id="sale_executive_filter" class="form-control form-control-sm" onchange="get_datatable()">
                                        <option value="">All</option>
                                        @foreach($sales_executives as $se)
                                            <option value="{{$se->id}}">{{$se->name}} ({{$se->role_as}})</option>
                                        @endforeach
                                    </select>
                                </label>
                                <label class="mb-0">Search:
                                    <input type="search"  id="basic-2_search" class="form-control form-control-sm" placeholder="Search" aria-controls="basic-2" data-bs-original-title="" title="">
                                </label>
                                @if(PermissionHelper::check('agent_customer', 'edit'))
                                <button type="button" class="btn btn-success btn-sm ms-2" data-bs-toggle="modal" data-bs-target="#upload_excel_modal">
                                    <i class="fa fa-upload me-1"></i> Upload Excel
                                </button>
                                @endif
                            </div>
                        </div>
                        <div class="dt-ext" id="get_datatable">
                            <div class="loader-box"><div class="loader-37"></div></div>
                            
                        </div>
                       
                    </div>
                </div>
            </div>
        </div>
        <!-- All Client Table End -->
    </div>


    <div class="modal fade" id="edit_modal"  aria-labelledby="mySmallModalLabel" aria-hidden="true">
        <div class="modal-dialog" id="ajax_html">
            
        </div>
    </div>

    <div class="modal fade" id="upload_excel_modal" tabindex="-1" role="dialog" aria-labelledby="uploadExcelLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <form action="{{ route('agent_customer.upload') }}" method="POST" id="upload_excel_form" enctype="multipart/form-data">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="uploadExcelLabel">Bulk Update Customers from Excel</h5>
                        <button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Select Excel File (.xlsx, .xls, .csv)</label>
                            <input type="file" name="excel_file" class="form-control" accept=".xlsx, .xls, .csv" required>
                        </div>
                        <div class="alert alert-info">
                            <ul class="mb-0 small">
                                <li><strong>Phone:</strong> Search key (Column D)</li>
                                <li><strong>GST:</strong> Updated from Column F</li>
                                <li><strong>Address:</strong> Combined from B & C</li>
                                <li><strong>Pincode:</strong> Updated from Column I</li>
                                <li><strong>State/City:</strong> Auto-fetched by pincode API</li>
                                <li><em>Note: Only existing records found by phone will be updated.</em></li>
                            </ul>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-secondary" type="button" data-bs-dismiss="modal">Close</button>
                        <button class="btn btn-primary" type="submit" id="btn_upload">Start Update</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <audio id="myAudio" controls class="d-none">
        <source src="{{ asset('audio/Beep.wav') }}" type="audio/wav">
    </audio>
@endsection
@section('script')
    <script>
        function debounce(func, wait) {
            let timeout;
            return function(...args) {
                const context = this;
                clearTimeout(timeout);
                timeout = setTimeout(() => func.apply(context, args), wait);
            };
        }

        window.fetchLocation = function(pincode, type) {
            var state_id = type === 'add' ? '#state_add' : '#state_input';
            var city_id = type === 'add' ? '#city_add' : '#city_input';
            var status_id = type === 'add' ? '#pincode_status_add' : '#pincode_status';

            if(pincode.length === 6) {
                $.ajax({
                    url: 'https://api.postalpincode.in/pincode/' + pincode,
                    type: 'GET',
                    success: function(response) {
                        if(response && response[0] && response[0].Status === 'Success') {
                            $(status_id).hide();
                            var postOffices = response[0].PostOffice;
                            var state = postOffices[0].State;
                            var city = postOffices[0].District;
                            
                            for(var i=0; i<postOffices.length; i++) {
                                if(postOffices[i].Name === postOffices[i].Block) {
                                    city = postOffices[i].Name;
                                    break;
                                }
                            }
                            $(state_id).val(state);
                            $(city_id).val(city);
                        } else {
                            $(status_id).show();
                            $(state_id).val('');
                            $(city_id).val('');
                        }
                    },
                    error: function() {
                        $(status_id).text('Error fetching location').show();
                        $(state_id).val('');
                        $(city_id).val('');
                    }
                });
            } else {
                $(status_id).hide();
                $(state_id).val('');
                $(city_id).val('');
            }
        };

        window.edit = function(id, key_value){
            var url = "{{route('agent_customer.edit_modal',":id")}}";
            url = url.replace(':id', id);
            $('#ajax_html').html('<div class="loader-box"><div class="loader-37"></div></div>');
            $('#edit_modal').modal('show');
            $.get(url, {key_value: key_value}, function(data){
                $('#ajax_html').html(data);
                $('.js-example-basic-single').select2();
            });
        };

        window.delete_ac = function(id){
            swal({
                title: "Are you sure?",
                text: "You won't be able to revert this!",
                icon: "warning",
                buttons: true,
                dangerMode: true,
            })
            .then((willDelete) => {
                if (willDelete) {
                    var url = "{{route('agent_customer.delete',":id")}}";
                    url = url.replace(':id',id);
                    $.get(url, function(data){
                        if(data.result == 1){
                            var page = Number($(".pages").find('span[aria-current="page"] span').text()) || 1;
                            get_datatable(page);
                            $.notify({ title:'Deleted', message:data.message}, { type:'danger', });
                        }
                    })
                }
            })
        };

        window.change_status = function(id){
            var url = "{{route('agent_customer.change_status',":id")}}";
            url = url.replace(':id',id);
            $.get(url, function(data){
                if(data.result == 1){
                    $.notify({ title:'Status!', message:data.message}, { type:'info', });
                }
            })
        };

        window.convert_to_customer = function(id) {
            swal({
                title: "Are you sure?",
                text: "This will mark this lead as a confirmed Customer/Agent.",
                icon: "info",
                buttons: true,
            })
            .then((confirm) => {
                if (confirm) {
                    var url = "{{route('agent_customer.convert', ':id')}}";
                    url = url.replace(':id', id);
                    $.get(url, function(data) {
                        if(data.result == 1) {
                            $.notify({ title: 'Success', message: data.message }, { type: 'success' });
                            var page = Number($(".pages").find('span[aria-current="page"] span').text()) || 1;
                            get_datatable(page);
                        } else {
                            $.notify({ title: 'Error', message: data.message }, { type: 'danger' });
                        }
                    });
                }
            });
        };

        window.get_datatable = function(page){
            var $container = $('#get_datatable');
            if ($container.length) {
                $container.html('<div class="loader-box"><div class="loader-37"></div></div>');
                var value = $('#basic-2_value').val();
                var search = $('#basic-2_search').val();
                var sale_executive_id = $('#sale_executive_filter').val();
                var role_filter = $('#role_filter').val();
                var type_filter = $('#type_filter').val();
                var page = page ?? 1;
                $.ajax({
                    url: '{{ route("agent_customer.datatable") }}',
                    data: { page: page, value: value, search: search, sale_executive_id: sale_executive_id, role_filter: role_filter, type_filter: type_filter, _token: "{{csrf_token() }}" },
                    type: 'GET',
                    success: function(data){
                        $container.html(data);
                        $('#basic-test').DataTable({ dom: '{{ auth()->user()->role_as == "Admin" ? "Brt" : "rt" }}', "pageLength": -1 , responsive: true, ordering: false});
                    }
                });
            }
        };

        $(document).on('submit','form',function(event){
            if ($(this).attr('id') === 'upload_excel_form') return;

            event.preventDefault();
            var form = event.target;
            var form_data = new FormData(form);
            var $submitBtn = $(form).find('button[type="submit"]');

            var phone = $(form).find('input[name="phone_no"]').val();
            if(phone && phone.length != 10){
                $.notify({ title:'Error', message:'Phone number must be exactly 10 digits.' }, { type:'danger', });
                return false;
            }

            $submitBtn.addClass('disabled');
            $.ajax({
                url: $(form).attr('action'),
                type: 'POST',
                data: form_data,
                processData: false,
                contentType: false,
                success: function(data){
                    if(data.result == 1){
                        $.notify({ title:'Success', message:data.message }, { type:'success', });
                        var page = Number($(".pages").find('span[aria-current="page"] span').text()) || 1;
                        
                        if(!$(form).find('input[name="agent_customer_id"]').val() || $(form).find('input[name="agent_customer_id"]').val() == "0"){
                            form.reset();
                            $('#state_add').val('');
                            $('#city_add').val('');
                            $("#name").focus();
                        }
                        
                        get_datatable(page);
                        $('#edit_modal').modal('hide');
                    }else if(data.result == 2){
                        swal({
                            title: "Duplicate Found!",
                            text: data.message,
                            icon: "warning",
                            buttons: ["Cancel", "Yes, add it!"],
                            dangerMode: true,
                        })
                        .then((confirm) => {
                            if (confirm) {
                                form_data.append('confirm_duplicate', 1);
                                $.ajax({
                                    url: $(form).attr('action'),
                                    type: 'POST',
                                    data: form_data,
                                    processData: false,
                                    contentType: false,
                                    success: function(retryData){
                                        if(retryData.result == 1){
                                            $.notify({ title:'Success', message:retryData.message }, { type:'success', });
                                            form.reset();
                                            get_datatable();
                                            $('#edit_modal').modal('hide');
                                        } else {
                                            $.notify({ title:'Error', message:retryData.message }, { type:'danger', });
                                        }
                                        $submitBtn.removeClass('disabled');
                                    }
                                });
                            } else {
                                $submitBtn.removeClass('disabled');
                            }
                        });
                    }else{
                        $.notify({ title:'Error', message:data.message }, { type:'danger', });
                        $submitBtn.removeClass('disabled');
                    }
                },
                error: function() { 
                    $submitBtn.removeClass('disabled'); 
                }
            });
        });

        $(document).on('submit','#upload_excel_form',function(event){
            event.preventDefault();
            var form_data = new FormData(this);
            var $submitBtn = $('#btn_upload');
            $submitBtn.html('<i class="fa fa-spinner fa-spin"></i> Processing...').addClass('disabled');
            $.ajax({
                url: $(this).attr('action'),
                type: 'POST',
                data: form_data,
                processData: false,
                contentType: false,
                success: function(data){
                    if(data.result == 1){
                        $.notify({ title:'Success', message:data.message }, { type:'success', });
                        $('#upload_excel_modal').modal('hide');
                        $('#upload_excel_form')[0].reset();
                        get_datatable();
                    }else{
                        $.notify({ title:'Error', message:data.message }, { type:'danger', });
                    }
                    $submitBtn.html('Start Update').removeClass('disabled');
                },
                error: function(xhr) {
                    var msg = 'Something went wrong.';
                    if(xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message;
                    $.notify({ title:'Error', message: msg }, { type:'danger', });
                    $submitBtn.html('Start Update').removeClass('disabled');
                }
            });
        });

        $(document).on('click','.pages a',function(n){
            n.preventDefault();
            var page = $(this).attr('href').split("page=")[1];
            get_datatable(page);
        });

        $(document).ready(function(){
            get_datatable();
            $("#name").focus();

            $('#phone_no').on('input', function() {
                var phone = $(this).val();
                if(phone.length === 10) {
                    $.ajax({
                        url: '{{ route("agent_customer.check_lead") }}',
                        type: 'GET',
                        data: { phone: phone },
                        success: function(response) {
                            if(response.status === 'success') {
                                $('#name').val(response.data.name);
                                $('#address').val(response.data.address);
                                $('#state_add').val(response.data.state);
                                $('#city_add').val(response.data.city);
                            }
                        }
                    });
                }
            });

            $('#basic-2_search').on('keyup search', debounce(function() { get_datatable(); }, 500));
            $('#basic-2_value').on('change', function() { get_datatable(); });
        });
    </script>
@endsection
