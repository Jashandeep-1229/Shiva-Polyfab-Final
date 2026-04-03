@extends('layouts.admin.app')

@section('title', 'Size & Color Master')

@section('css')
<style>
    .card-header.pb-0 {
        padding-bottom: 0 !important;
        margin-bottom: 10px;
    }
</style>
<style>
        .select2-container .select2-selection--single{
        height:30px !important;
        padding:5px !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow{
        height:12px !important;
    }
</style>
@endsection

@section('breadcrumb-items')
    <li class="breadcrumb-item">Size & Color Master</li>
@endsection

@section('content')
    <div class="container-fluid">
        <div class="row">
            <!-- Color Master Section (col-md-4) -->
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header pb-0">
                        <h5>Color Master</h5>
                    </div>
                    <div class="card-body">
                        @if(\App\Helpers\PermissionHelper::check('manage_master', 'add'))
                        <form action="{{route('color_master.store')}}" method="POST" class="row mb-3">
                            @csrf
                            <input type="hidden" name="color_id" value="0">
                            <div class="col-12 mb-2">
                                <input type="text" name="name" id="color_name" placeholder="Color Name" oninput="this.value = this.value.toUpperCase()" class="form-control form-control-sm" required>
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary btn-sm w-100">Add Color +</button>
                            </div>
                        </form>
                        @endif
                        
                        <div class="dataTables_wrapper px-0" onchange="get_color_datatable()">
                            <div class="dataTables_length">
                                <label>Show 
                                    <select id="color_value" class="form-control form-control-sm">
                                        <option value="50">50</option>
                                        <option value="250" selected>250</option>
                                        <option value="500">500</option>
                                        <option value="1000">1000</option>
                                    </select>
                                </label>
                            </div>
                            <div class="dataTables_filter">
                                <label>Search:
                                    <input type="search" id="color_search" class="form-control form-control-sm" placeholder="Search">
                                </label>
                            </div>
                        </div>
                        <div class="dt-ext" id="color_datatable_container">
                            <div class="loader-box"><div class="loader-37"></div></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Size Master Section (col-md-8) - Expanded to accommodate more fields -->
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header pb-0">
                        <h5>Size Master</h5>
                    </div>
                    <div class="card-body">
                        @if(\App\Helpers\PermissionHelper::check('manage_master', 'add'))
                        <form action="{{route('size_master.store')}}" method="POST" class="row mb-3">
                            @csrf
                            <input type="hidden" name="size_id" value="0">
                            <div class="col-md-3 mb-2">
                                <input type="text" name="name" id="size_name" placeholder="Size Name" oninput="this.value = this.value.toUpperCase()" class="form-control form-control-sm" required>
                            </div>
                            <div class="col-md-3 mb-2">
                                <select name="fabric_id" id="fabric_id" class="form-control form-control-sm js-example-basic-single" required>
                                    <option value="">Select Fabric</option>
                                    @foreach($fabrics as $f)
                                        <option value="{{$f->id}}">{{$f->name}}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3 mb-2">
                                <select name="bopp_id" id="bopp_id" class="form-control form-control-sm js-example-basic-single" required>
                                    <option value="">Select BOPP</option>
                                    @foreach($bopps as $b)
                                        <option value="{{$b->id}}">{{$b->name}}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3 mb-2">
                                <select name="order_send_for" id="order_send_for" class="form-control form-control-sm" required>
                                    <option value="">Send For</option>
                                    <option value="Cutting">Cutting</option>
                                    <option value="Box">Box</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary btn-sm w-100">Add Size +</button>
                            </div>
                        </form>
                        @endif

                        <div class="dataTables_wrapper px-0" onchange="get_size_datatable()">
                            <div class="dataTables_length">
                                <label>Show 
                                    <select id="size_value" class="form-control form-control-sm">
                                        <option value="50">50</option>
                                        <option value="250" selected>250</option>
                                        <option value="500">500</option>
                                        <option value="1000">1000</option>
                                    </select>
                                </label>
                            </div>
                            <div class="dataTables_filter">
                                <label>Search:
                                    <input type="search" id="size_search" class="form-control form-control-sm" placeholder="Search">
                                </label>
                            </div>
                        </div>
                        <div class="dt-ext" id="size_datatable_container">
                            <div class="loader-box"><div class="loader-37"></div></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modals -->
    <div class="modal fade" id="color_edit_modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog" id="color_ajax_html"></div>
    </div>
    <div class="modal fade" id="size_edit_modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg" id="size_ajax_html"></div>
    </div>

    <audio id="myAudio" controls class="d-none">
        <source src="{{ asset('audio/Beep.wav') }}" type="audio/wav">
    </audio>
@endsection

@section('script')
    <script>
        $(document).ready(function(){
            get_color_datatable();
            get_size_datatable();
            $('.js-example-basic-single').select2();
        });

        $(document).on('click','.color-pages a',function(n){
            n.preventDefault();
            var page = $(this).attr('href').split("page=")[1];
            get_color_datatable(page);
        });

        $(document).on('click','.size-pages a',function(n){
            n.preventDefault();
            var page = $(this).attr('href').split("page=")[1];
            get_size_datatable(page);
        });

        // Color Datatable functions
        function get_color_datatable(page){
            var $container = $('#color_datatable_container');
            $container.html('<div class="loader-box"><div class="loader-37"></div></div>');
            var search = $('#color_search').val();
            var value = $('#color_value').val();
            var page = page ?? 1;

            $.ajax({
                url: '{{ route("color_master.datatable") }}',
                data: { page: page, search: search, value: value, _token: "{{csrf_token() }}" },
                type: 'GET',
                success: function(data) {
                    $container.html(data);
                    $('#color-table').DataTable({ dom: '{{ auth()->user()->role_as == "Admin" ? "Brt" : "rt" }}', "pageLength": -1, responsive: true, ordering: false });
                }
            });
        }

        // Size Datatable functions
        function get_size_datatable(page){
            var $container = $('#size_datatable_container');
            $container.html('<div class="loader-box"><div class="loader-37"></div></div>');
            var search = $('#size_search').val();
            var value = $('#size_value').val();
            var page = page ?? 1;

            $.ajax({
                url: '{{ route("size_master.datatable") }}',
                data: { page: page, search: search, value: value, _token: "{{csrf_token() }}" },
                type: 'GET',
                success: function(data) {
                    $container.html(data);
                    $('#size-table').DataTable({ dom: '{{ auth()->user()->role_as == "Admin" ? "Brt" : "rt" }}', "pageLength": -1, responsive: true, ordering: false });
                }
            });
        }

        // Debounce
        // function debounce(func, wait) {
        //     let timeout;
        //     return function(...args) {
        //         const context = this;
        //         clearTimeout(timeout);
        //         timeout = setTimeout(() => func.apply(context, args), wait);
        //     };
        // }

        // $('#color_search').on('keyup search', debounce(function() { get_color_datatable(); }, 500));
        // $('#size_search').on('keyup search', debounce(function() { get_size_datatable(); }, 500));

        // $('#color_value').on('change', function() { get_color_datatable(); });
        // $('#size_value').on('change', function() { get_size_datatable(); });

        // Edit Modals
        function edit_color(id){
            var url = "{{route('color_master.edit_modal',":id")}}";
            url = url.replace(':id',id);
            $('#color_ajax_html').html('<div class="loader-box"><div class="loader-37"></div></div>');
            $.get(url, function(data){
                $('#color_edit_modal').modal('show');
                $('#color_ajax_html').html(data);
                $('.js-example-basic-single').select2();
            });
        }

        function edit_size(id){
            var url = "{{route('size_master.edit_modal',":id")}}";
            url = url.replace(':id',id);
            $('#size_ajax_html').html('<div class="loader-box"><div class="loader-37"></div></div>');
            $.get(url, function(data){
                $('#size_edit_modal').modal('show');
                $('#size_ajax_html').html(data);
                $('.js-example-basic-single').select2();
            });
        }

        // Form Submit
        $(document).on('submit','form',function(event){
            event.preventDefault();
            var form = event.target;
            var form_data = new FormData(form);
            var $submitBtn = $(form).find('button[type="submit"]');
            $submitBtn.addClass('disabled');
            
            $.ajax({
                url: $(form).attr('action'),
                type: 'POST',
                data: form_data,
                processData: false,
                contentType: false,
                success: function(data){
                    if(data.result == 1){
                        $.notify({ title:'Success', message:data.message }, { type:'success' });
                        if(data.from == 'Color Master'){
                            $('#color_name').val('');
                            get_color_datatable();
                            $('#color_edit_modal').modal('hide');
                        } else {
                            $('#size_name').val('');
                            $('#fabric_id, #bopp_id, #order_send_for').val('').trigger('change');
                            get_size_datatable();
                            $('#size_edit_modal').modal('hide');
                        }
                    } else {
                        $.notify({ title:'Error', message:data.message }, { type:'danger' });
                    }
                    $submitBtn.removeClass('disabled');
                }
            });
        });

        // Delete functions
        function delete_color(id){
            if(confirm("Are you sure?")){
                var url = "{{route('color_master.delete',":id")}}";
                url = url.replace(':id',id);
                $.get(url, function(data){
                    if(data.result == 1) get_color_datatable();
                });
            }
        }

        function delete_size(id){
            if(confirm("Are you sure?")){
                var url = "{{route('size_master.delete',":id")}}";
                url = url.replace(':id',id);
                $.get(url, function(data){
                    if(data.result == 1) get_size_datatable();
                });
            }
        }

        function change_color_status(id){
            var url = "{{route('color_master.change_status',":id")}}";
            url = url.replace(':id',id);
            $.get(url, function(data){
                if(data.result == 1) $.notify({ title:'Status!', message:data.message}, { type:'info' });
            });
        }

        function change_size_status(id){
            var url = "{{route('size_master.change_status',":id")}}";
            url = url.replace(':id',id);
            $.get(url, function(data){
                if(data.result == 1) $.notify({ title:'Status!', message:data.message}, { type:'info' });
            });
        }
    </script>
@endsection
