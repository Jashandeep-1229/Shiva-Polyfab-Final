@extends('layouts.admin.app')

@section('title', $item_name . ' - Stock History')

@section('css')
@endsection

@section('breadcrumb-items')
    <li class="breadcrumb-item">Manage {{ $stock_name_capital }} Stock</li>
    <li class="breadcrumb-item"><a href="{{ route('manage_stock.average_index', ['stock_name' => $stock_name]) }}">Average Stock</a></li>
    <li class="breadcrumb-item active">History</li>
@endsection

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card" onchange="get_datatable()">
                    <div class="card-body row align-items-end">
                        <div class="col-md-3">
                            <label class="form-label">From Date</label>
                            <input type="date" class="form-control form-control-sm" id="from_date">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">To Date</label>
                            <input type="date" class="form-control form-control-sm" id="to_date">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Entries per page</label>
                            <select id="basic-2_value" class="form-control form-control-sm">
                                <option value="50">50</option>
                                <option value="100">100</option>
                                <option value="250">250</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <button class="btn btn-primary btn-sm w-100" onclick="get_datatable()">
                                <i class="fa fa-filter"></i> Filter
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header pb-0 d-flex justify-content-between align-items-center">
                        <h5>Stock History for: <span class="text-primary">{{ $item_name }}</span></h5>
                    </div>
                    <div class="card-body">
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

        $(document).on('click', '.pages a', function(e) {
            e.preventDefault();
            var page = $(this).attr('href').split("page=")[1];
            get_datatable(page);
        });

        function get_datatable(page) {
            var $container = $('#get_datatable');
            if ($container.length) {
                $container.html('<div class="loader-box"><div class="loader-37"></div></div>');
                
                var page = page ?? 1;
                var from_date = $('#from_date').val();
                var to_date = $('#to_date').val();
                var value = $('#basic-2_value').val();

                $.ajax({
                    url: '{{ route('manage_stock.history_datatable') }}',
                    data: {
                        _token: "{{ csrf_token() }}",
                        stock_name: "{{ $stock_name }}",
                        stock_id: "{{ $stock_id }}",
                        page: page,
                        from_date: from_date,
                        to_date: to_date,
                        value: value
                    },
                    type: 'GET',
                    success: function(data) {
                        $container.html(data);
                        $('#basic-test').DataTable({ dom: '{{ auth()->user()->role_as == "Admin" ? "Brt" : "rt" }}', "pageLength": -1 , responsive: true, ordering: false});
                    }
                });
            }
        }
    </script>
@endsection
