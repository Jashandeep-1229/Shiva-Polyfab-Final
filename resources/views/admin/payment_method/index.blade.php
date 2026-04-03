@extends('layouts.admin.app')

@section('title', 'Payment Method Master')

@section('css')
@endsection

@section('breadcrumb-items')
    <li class="breadcrumb-item">Payment Method Master</li>
@endsection

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                @if(\App\Helpers\PermissionHelper::check('manage_master', 'add'))
                <div class="card" id="add_type">
                    <form action="{{route('payment_method.store')}}" method="POST" id="" class="modal-content" enctype="multipart/form-data">
                        @csrf
                        <div class="card-body row align-items-end">
                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-bold small text-uppercase mb-1">Method Name</label>
                                <input type="text" name="name" id="name" placeholder="E.g. Cash, Bank, UPI" oninput="this.value = this.value.toUpperCase()" class="form-control form-control-sm" required>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label fw-bold small text-uppercase mb-1">Status</label>
                                <select name="status" id="status" class="form-select form-select-sm">
                                    <option value="1">Active</option>
                                    <option value="0">Inactive</option>
                                </select>
                            </div>
                            <div class="col-md-1 mb-3">
                               <button type="submit" id="add_data" class="btn btn-primary btn-sm w-100" >+</button>
                            </div>
                        </div>
                    </form>
                </div>
                @endif
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <div id="basic-2_wrapper" class="dataTables_wrapper px-2">
                            <div class="dataTables_length">
                                <label>Show 
                                    <select name="basic-2_value" id="basic-2_value" class="form-control form-control-sm">
                                        <option value="50">50</option>
                                        <option value="250" selected>250</option>
                                        <option value="500">500</option>
                                    </select>
                                </label>
                            </div>
                            <div class="dataTables_filter">
                                <label>Search:
                                    <input type="search" id="basic-2_search" class="form-control form-control-sm" placeholder="Search methods...">
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
    </div>

    <div class="modal fade" id="edit_modal" aria-hidden="true">
        <div class="modal-dialog" id="ajax_html"></div>
    </div>
@endsection

@section('script')
    <script>
        $(document).ready(function(){
            get_datatable();
            $("#name").focus();
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
                    url: '{{ route("payment_method.datatable") }}',
                    data: { page: page, value: value, search: search },
                    type: 'GET',
                    success: function(data){
                        $container.html(data);
                        $('#basic-test').DataTable({ dom: 'Brt', "pageLength": -1 , responsive: true, ordering: false});
                    }
                });
            }
        }

        $(document).on('click','.pages a',function(n){
            n.preventDefault();
            var page = $(this).attr('href').split("page=")[1];
            get_datatable(page);
        });

        $('#basic-2_search').on('keyup search', debounce(function() { get_datatable(); }, 500));
        $('#basic-2_value').on('change', function() { get_datatable(); });

        function editMethod(id){
            var url = "{{route('payment_method.edit_modal',":id")}}";
            url = url.replace(':id',id);
            $('#ajax_html').html('<div class="loader-box"><div class="loader-37"></div></div>');
            $('#edit_modal').modal('show');
            $.get(url, function(data){
                $('#ajax_html').html(data);
            });
        }

        $(document).on('submit','form',function(event){
            event.preventDefault();
            var form = event.target;
            var form_data = new FormData(form);
            var $submitBtn = $(form).find('button[type="submit"]');
            var originalText = $submitBtn.html();
            $submitBtn.addClass('disabled').html('<i class="fa fa-spinner fa-spin"></i>');
            
            $.ajax({
                url: $(form).attr('action'),
                type: 'POST',
                data: form_data,
                processData: false,
                contentType: false,
                success: function(data){
                    if(data.result == 1){
                        $.notify({ title:'Success', message:data.message }, { type:'success' });
                        $('#name').val('');
                        $submitBtn.html(originalText).removeClass('disabled');
                        get_datatable();
                        $('#edit_modal').modal('hide');
                        $("#name").focus();
                    }else{
                        $.notify({ title:'Error', message:data.message }, { type:'danger' });
                        $submitBtn.html(originalText).removeClass('disabled');
                    }
                },
                error: function() { $submitBtn.html(originalText).removeClass('disabled'); }
            });
        });

        function deleteMethod(id){
            swal({
                title: "Are you sure?",
                text: "Deleting this may affect historical records!",
                icon: "warning",
                buttons: true,
                dangerMode: true,
            })
            .then((willDelete) => {
                if (willDelete) {
                    var url = "{{route('payment_method.delete',":id")}}";
                    url = url.replace(':id',id);
                    $.get(url, function(data){
                        if(data.result == 1){
                            get_datatable();
                            $.notify({ title:'Deleted', message:data.message}, { type:'danger' });
                        }
                    })
                }
            })
        }
    </script>
@endsection
