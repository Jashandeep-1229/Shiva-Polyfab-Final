@extends('layouts.admin.app')

@section('title', 'Old Data')

@section('css')
@endsection

@section('breadcrumb-items')
    <li class="breadcrumb-item">Old Data</li>
@endsection

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                @if(App\Helpers\PermissionHelper::check('old_data', 'add'))
                <div class="card">
                    <form action="{{route('old_data.import')}}" method="POST" id="import_form" class="modal-content" enctype="multipart/form-data">
                        @csrf
                        <div class="card-body">
                            <div class="row align-items-end">
                                <div class="col-md-4">
                                    <label class="form-label">Upload Old Data (Excel/CSV)</label>
                                    <input type="file" name="file" class="form-control form-control-sm" required>
                                </div>
                                <div class="col-md-2">
                                    <button type="submit" id="add_data" class="btn btn-primary btn-sm w-100">Upload Data</button>
                                </div>
                                <div class="col-md-6 text-end">
                                    <small class="text-muted">Columns: order_date, dispatch_date, name, bopp, fabric, loop_color, notes, pieces, send_for</small>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                @endif

                <div class="card">
                    <div class="card-body">
                        <div id="basic-2_wrapper" class="dataTables_wrapper px-2">
                            <div class="dataTables_length">
                                <label>Show 
                                    <select name="basic-2_value" id="basic-2_value" class="form-control form-control-sm">
                                        <option value="50">50</option>
                                        <option value="250" selected>250</option>
                                        <option value="500">500</option>
                                        <option value="1000">1000</option>
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
@endsection

@section('script')
    <script>
        $(document).ready(function(){
            get_datatable();
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
                    url: '{{ route("old_data.datatable") }}',
                    data: { page: page, value: value, search: search, _token: "{{csrf_token() }}" },
                    type: 'GET',
                    success: function(data){
                        $container.html(data);
                        $('#basic-test').DataTable({ 
                            dom: '{{ auth()->user()->role_as == "Admin" ? "Brt" : "rt" }}', 
                            "pageLength": -1, 
                            responsive: true, 
                                                                                    ordering: false 
                        });
                    }
                });
            }
        }

        $('#basic-2_search').on('keyup search', debounce(function() { get_datatable(); }, 500));
        $('#basic-2_value').on('change', function() { get_datatable(); });

        $('#import_form').on('submit', function(event){
            event.preventDefault();
            var form_data = new FormData(this);
            var $submitBtn = $('#add_data');
            $submitBtn.addClass('disabled').html('<i class="fa fa-spinner fa-spin"></i> Uploading...');
            
            $.ajax({
                url: $(this).attr('action'),
                type: 'POST',
                data: form_data,
                processData: false,
                contentType: false,
                success: function(data){
                    if(data.result == 1){
                        $.notify({ title:'Success', message:data.message }, { type:'success', });
                        get_datatable();
                        $('#import_form')[0].reset();
                    } else {
                        $.notify({ title:'Error', message:data.message }, { type:'danger', });
                    }
                    $submitBtn.removeClass('disabled').html('Upload Data');
                },
                error: function() { 
                    $.notify({ title:'Error', message:'Something went wrong' }, { type:'danger', });
                    $submitBtn.removeClass('disabled').html('Upload Data');
                }
            });
        });

        function delete_old_data(id){
            if(confirm('Are you sure you want to delete this record?')){
                $.ajax({
                    url: '{{ url("admin/old_data/delete") }}/' + id,
                    type: 'GET',
                    success: function(data){
                        if(data.result == 1){
                            $.notify({ title:'Deleted', message:data.message}, { type:'danger', });
                            get_datatable();
                        }
                    }
                });
            }
        }
    </script>
@endsection
