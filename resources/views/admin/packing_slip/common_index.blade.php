@extends('layouts.admin.app')

@section('title')
    Common Packing Slips
@endsection

@section('css')
@endsection

@section('breadcrumb-items')
    <li class="breadcrumb-item">Common Packing Slips</li>
@endsection

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">All Common Packing Slips</h5>
                        @if(\App\Helpers\PermissionHelper::check('packing_slip_common', 'add'))
                        <a href="{{ route('packing_slip_common.create') }}" class="btn btn-primary btn-sm"><i class="fa fa-plus me-1"></i> Add New Slip</a>
                        @endif
                    </div>
                    <div class="card-body">
                        <div id="basic-2_wrapper" class="dataTables_wrapper px-2" onchange="get_datatable()">
                            <div class="dataTables_length">
                                <label>Show 
                                    <select name="basic-2_value" id="basic-2_value" aria-controls="basic-2" class="form-control form-control-sm">
                                        <option value="50">50</option>
                                        <option value="250" selected>250</option>
                                        <option value="500">500</option>
                                        <option value="1000">1000</option>
                                    </select>
                                </label>
                            </div>
                            <div class="dataTables_filter">
                                <label>Search:
                                    <input type="search" id="basic-2_search" class="form-control form-control-sm" placeholder="Search Slip # or Customer" aria-controls="basic-2">
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

    <div class="modal fade" id="view_modal" data-bs-backdrop="static" data-bs-keyboard="false" aria-labelledby="mySmallModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" id="ajax_html">
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

        $('#basic-2_search').on('keyup', function() {
            get_datatable();
        });

        function get_datatable(page){
            var $container = $('#get_datatable');
            if ($container.length) {
                $container.html('<div class="loader-box"><div class="loader-37"></div></div>');
                var value = $('#basic-2_value').val();
                var search = $('#basic-2_search').val();
                var page = page ?? 1;
                $.ajax({
                    url: '{{ route("packing_slip_common.datatable") }}',
                    data: { page: page, value: value, search: search, _token: "{{csrf_token() }}" },
                    type: 'GET',
                    success: function(data){
                        $container.html(data);
                        $('#basic-test').DataTable({ 
                            dom: '{{ auth()->user()->role_as == "Admin" ? "Brt" : "rt" }}', 
                            "pageLength": -1 , 
                            responsive: true, 
                            ordering: false
                        });
                    }
                });
            }
        }
    </script>
@endsection
