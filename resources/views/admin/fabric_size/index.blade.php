@extends('layouts.admin.app')

@section('title', 'Fabric Size Calculation')

@section('breadcrumb-items')
    <li class="breadcrumb-item">Fabric Size Calculation</li>
@endsection

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                @if(\App\Helpers\PermissionHelper::check('manage_master', 'add'))
                <div class="card">
                    <form action="{{route('fabric_size.store')}}" method="POST" class="modal-content">
                        @csrf
                        <div class="card-body row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Size (Number Only)</label>
                                <input type="number" step="any" name="name" id="name" placeholder="Enter Size" class="form-control form-control-sm" required autofocus>
                            </div>
                            <div class="col-md-2 mt-4">
                               <button type="submit" class="btn btn-primary btn-sm">+</button>
                            </div>
                        </div>
                    </form>
                </div>
                @endif
                <div class="card">
                    <div class="card-body">
                        <div class="dataTables_wrapper px-2">
                            <div class="dataTables_length">
                                <label>Show 
                                    <select id="basic-2_value" class="form-control form-control-sm">
                                        <option value="50">50</option>
                                        <option value="250" selected>250</option>
                                        <option value="500">500</option>
                                    </select>
                                </label>
                            </div>
                            <div class="dataTables_filter">
                                <label>Search:
                                    <input type="search" id="basic-2_search" class="form-control form-control-sm" placeholder="Search">
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
        });

        function get_datatable(page){
            var $container = $('#get_datatable');
            $container.html('<div class="loader-box"><div class="loader-37"></div></div>');
            
            var value = $('#basic-2_value').val();
            var search = $('#basic-2_search').val();
            var page = page ?? 1;

            $.ajax({
                url: '{{ route("fabric_size.datatable") }}',
                data: { page: page, value: value, search: search },
                type: 'GET',
                success: function(data) {
                    $container.html(data);
                }
            });
        }

        $('#basic-2_search').on('keyup', function() { get_datatable(); });
        $('#basic-2_value').on('change', function() { get_datatable(); });

        function edit_modal(id){
            var url = "{{route('fabric_size.edit_modal',":id")}}";
            url = url.replace(':id',id);
            $('#ajax_html').html('<div class="loader-box"><div class="loader-37"></div></div>');
            $.get(url, function(data){
                $('#ajax_html').html(data);
            });
        }

        $(document).on('submit','form',function(event){
            event.preventDefault();
            var form = event.target;
            var form_data = new FormData(form);
            var $submitBtn = $(form).find('button[type="submit"]');

            if ($submitBtn.hasClass('disabled')) return;
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
                        get_datatable();
                        $('#edit_modal').modal('hide');
                        $('#name').val('');
                        $('#name').focus();
                    }else{
                        $.notify({ title:'Error', message:data.message }, { type:'danger' });
                    }
                    $submitBtn.removeClass('disabled').html('+');
                    if($(form).find('input[name="fabric_size_id"]').length > 0) $submitBtn.html('Update');
                },
                error: function() {
                    $submitBtn.removeClass('disabled').html('+');
                }
            });
        });

        function delete_record(id){
            swal({ title: "Are you sure?", icon: "warning", buttons: true, dangerMode: true })
            .then((willDelete) => {
                if (willDelete) {
                    var url = "{{route('fabric_size.delete',":id")}}";
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

        function change_status(id){
            var url = "{{route('fabric_size.change_status',":id")}}";
            url = url.replace(':id',id);
            $.get(url, function(data){
                if(data.result == 1){
                    $.notify({ title:'Status!', message:data.message}, { type:'info' });
                }
            })
        }
    </script>
@endsection
