@extends('layouts.admin.app')

@section('title', 'Loop Master')

@section('css')

@endsection

@section('breadcrumb-items')
    <li class="breadcrumb-item">Loop Master</li>
@endsection

@section('content')
    <div class="container-fluid">
        <!-- All Client Table Start -->
        <div class="row">
            <div class="col-12">
                <div class="card" id="add_type">
                    <form action="{{route('loop.store')}}" method="POST" id="" class="modal-content" enctype="multipart/form-data">
                        @csrf
                        <div class="card-body row">
                            <div class="col-md-3 mb-3">
                                <input type="text" name="name" id="name"   placeholder="Name" oninput="this.value = this.value.toUpperCase()" class="form-control form-control-sm" required>
                            </div>
                             <div class="col-md-3">
                                <input type="number" step="any" name="alert_min_stock" id="alert_min_stock"   placeholder="Alert Min Stock"  class="form-control form-control-sm">
                            </div>
                            <div class="col-md-3">
                                <input type="number" step="any" name="alert_max_stock" id="alert_max_stock"   placeholder="Alert Max Stock"  class="form-control form-control-sm">
                            </div>
                             <div class="col-md-2 ">
                                <input type="number" step="any" name="order_qty" id="order_qty"   placeholder="Minimum Qty To Make Order When Stock Alert"  class="form-control form-control-sm">
                            </div>
                           
                            <div class="col-md-1">
                               <button type="submit" id="add_data" class="btn btn-primary btn-sm w-50" >+</button>
                            </div>
                           
                        </div>
                    </form>
                </div>
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
                            <div class="dataTables_filter">
                                <label>Search:
                                    <input type="search"  id="basic-2_search" class="form-control form-control-sm" placeholder="Search" aria-controls="basic-2" data-bs-original-title="" title="">
                                </label>
                                
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

    <div class="modal fade" id="vendorModal" tabindex="-1" aria-hidden="true">
        <div id="vendor_ajax_html"></div>
    </div>

    <audio id="myAudio" controls class="d-none">
        <source src="{{ asset('audio/Beep.wav') }}" type="audio/wav">
    </audio>
@endsection
@section('script')
    <script>
        function openAddVendorModal(type, id) {
            var url = "{{ url('admin/master_vendor/add_modal') }}/" + type + "/" + id;
            $('#vendor_ajax_html').html('<div class="modal-dialog"><div class="modal-content"><div class="modal-body text-center p-4"><i class="fa fa-spinner fa-spin fa-2x"></i></div></div></div>');
            $('#vendorModal').modal('show');
            $.get(url, function(data) {
                $('#vendor_ajax_html').html(data);
            });
        }

        function openVendorListModal(type, id) {
            var url = "{{ url('admin/master_vendor/list_modal') }}/" + type + "/" + id;
            $('#vendor_ajax_html').html('<div class="modal-dialog modal-lg"><div class="modal-content"><div class="modal-body text-center p-4"><i class="fa fa-spinner fa-spin fa-2x"></i></div></div></div>');
            $('#vendorModal').modal('show');
            $.get(url, function(data) {
                $('#vendor_ajax_html').html(data);
            });
        }

        $(document).ready(function(){
            get_datatable();
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
                $container.html('<div class="loader-box"><div class="loader-37"></div></div>');
                var value = $('#basic-2_value').val();
                var search = $('#basic-2_search').val();
                var page = page ?? 1;
                $.ajax({
                    url: '{{ route("loop.datatable") }}',
                    data: { page: page, value: value, search: search, _token: "{{csrf_token() }}" },
                    type: 'GET',
                    success: function(data){
                        $container.html(data);
                        $('#basic-test').DataTable({ dom: '{{ auth()->user()->role_as == "Admin" ? "Brt" : "rt" }}', "pageLength": -1 , responsive: true, ordering: false});
                    }
                });
            }
        }

        $('#basic-2_search').on('keyup search', debounce(function() { get_datatable(); }, 500));
        $('#basic-2_value').on('change', function() { get_datatable(); });

        function edit_modal(id,key_value){
            var url = "{{route('loop.edit_modal',":id")}}";
            url = url.replace(':id',id);
            $('#ajax_html').html('<div class="loader-box"><div class="loader-37"></div></div>');
            $.get(url,{key_value:key_value}, function(data){
                $('#ajax_html').html(data);
                $('.js-example-basic-single').select2();
            });
        }

        $(document).on('submit','form',function(event){
            event.preventDefault();
            var form = event.target;
            var form_data = new FormData(form);
            var $submitBtn = $('form button[type="submit"]');
            $submitBtn.addClass('disabled');
            $.ajax({
                url: $(event.target).attr('action'),
                type: 'POST',
                data: form_data,
                processData: false,
                contentType: false,
                success: function(data){
                    if(data.result == 1){
                        $.notify({ title:'Success', message:data.message }, { type:'success', });
                        var page = Number($(".pages").find('span[aria-current="page"] span').text());
                        $('#name, #company_name, #phone_no, #order_qty, #alert_stock').val('');
                        $submitBtn.html('+').removeClass('disabled');
                        get_datatable(page);
                        $('#edit_modal').modal('hide');
                        $("#name").focus();
                    }else{
                        $.notify({ title:'Error', message:data.message }, { type:'danger', });
                        $submitBtn.html('+').removeClass('disabled');
                    }
                },
                error: function() { $submitBtn.html('+').removeClass('disabled'); }
            });
        });

        function delete_loop(id){
            swal({
                title: "Are you sure?",
                text: "You won't be able to revert this!",
                icon: "warning",
                buttons: true,
                dangerMode: true,
            })
            .then((willDelete) => {
                if (willDelete) {
                    var url = "{{route('loop.delete',":id")}}";
                    url = url.replace(':id',id);
                    $.get(url, function(data){
                        if(data.result == 1){
                            var page = Number($(".pages").find('span[aria-current="page"] span').text());
                            get_datatable(page);
                            $.notify({ title:'Deleted', message:data.message}, { type:'danger', });
                        }
                    })
                }
            })
        }
        function change_status(id){
            var url = "{{route('loop.change_status',":id")}}";
            url = url.replace(':id',id);
            $.get(url, function(data){
                if(data.result == 1){
                    $.notify({ title:'Status!', message:data.message}, { type:'info', });
                }
            })
        }
    </script>
@endsection
