@extends('layouts.admin.app')

@section('title', $stock_name_capital . ' Stock Average')

@section('css')
@endsection

@section('breadcrumb-items')
    <li class="breadcrumb-item">Manage {{ $stock_name_capital }} Stock</li>
    <li class="breadcrumb-item active">Average Stock</li>
@endsection

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card" id="add_type" onchange="get_datatable()">
                     <div class="card-body row">
                        <div class="col-md-4">
                            <label>Filter By</label>
                            <select class="form-control form-control-sm" id="filter_by">
                               <option value="all">All</option>
                               <option value="zero_stock">Zero Stock</option>
                               <option value="low_stock">Low Stock</option>
                               <option value="over_stock">Over Stock</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label>Select {{ $stock_name_capital }}</label>
                            <select class="form-control form-control-sm" id="stock_id">
                                <option value="">Select {{ $stock_name_capital }}</option>
                               @foreach ($stock_data as $stock)
                                   <option value="{{ $stock->id }}">{{ $stock->name }}</option>
                               @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label>Search</label>
                            <input type="text" class="form-control form-control-sm" id="search" placeholder="Search">
                        </div>
                     </div>
                </div>
            </div>
                <div class="card">
                    <div class="card-body">
                        <div id="basic-2_wrapper" class="dataTables_wrapper px-2">
                            <div class="dataTables_length">
                                <label>Show 
                                    <select name="basic-2_value" id="basic-2_value" onchange="get_datatable()" class="form-control form-control-sm">
                                        <option value="10">10</option>
                                        <option value="50" selected>50</option>
                                        <option value="250">250</option>
                                        <option value="500">500</option>
                                        <option value="1000">1000</option>
                                    </select>
                                </label>
                            </div>
                            <div class="dataTables_filter">
                                <label>Search:
                                    <input type="search" id="basic-2_search" onkeyup="get_datatable()" class="form-control form-control-sm" placeholder="Search">
                                </label>
                            </div>
                        </div>
                        <div class="dt-ext" id="get_datatable">
                            <div class="loader-box">
                                <div class="loader-37"></div>
                            </div>
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
            get_datatable();
        });

        $(document).on('click','.pages a',function(n){
            n.preventDefault();
            var page = $(this).attr('href').split("page=")[1];
            get_datatable(page);
        });

        function get_datatable(page) {
            var $container = $('#get_datatable');
            var filter_by = $('#filter_by').val();
            var stock_id = $('#stock_id').val();
            var search = $('#basic-2_search').val();
            var value = $('#basic-2_value').val();
            var page = page ?? 1;

            if ($container.length) {
                $container.html('<div class="loader-box"><div class="loader-37"></div></div>');
                $.ajax({
                    url: '{{ route('manage_stock.average_datatable') }}',
                    data: {
                        _token: "{{ csrf_token() }}",
                        stock_name: "{{ $stock_name }}",
                        filter_by: filter_by,
                        search: search,
                        stock_id: stock_id,
                        value: value,
                        page: page
                    },
                    type: 'GET',
                    success: function(data) {
                        $container.html(data);
                        $('#basic-test').DataTable({
                            dom: '{{ auth()->user()->role_as == "Admin" ? "Birtp" : "irtp" }}',
                            responsive: true,
                            ordering: true,
                            order: [],
                            pageLength: -1
                        });
                    }
                });
            }
        }
    </script>
@endsection
