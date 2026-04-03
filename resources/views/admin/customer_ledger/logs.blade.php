@extends('layouts.admin.app')
@section('title', 'Ledger Logs')

@section('css')
<style>
    .select2-container .select2-selection--single{
        height:35px !important;
        padding:5px !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow{
        height:15px !important;
    }
</style>
@endsection

@section('content')
  

    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12">
                <div class="card shadow-sm border-0">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-4">
                            <div>
                                <h4 class="mb-1 fw-bold text-primary">Ledger Action Logs</h4>
                                <p class="mb-0 text-muted small text-uppercase fw-bold">History of Editable & Deleted Records</p>
                            </div>
                        </div>

                        <div class="dt-controls-wrap mb-4 bg-light p-3 rounded border">
                            <div class="row g-3 align-items-end">
                                <div class="col-md-auto">
                                    <label class="form-label small fw-bold text-dark">Show</label>
                                    <select id="basic-2_value" class="form-select form-select-sm" style="min-width: 70px;">
                                        <option value="50">50</option>
                                        <option value="100">100</option>
                                        <option value="250">250</option>
                                        <option value="500">500</option>
                                    </select>
                                </div>

                                <div class="col-md-auto">
                                    <label class="form-label small fw-bold text-dark">From Date</label>
                                    <input type="date" id="from_date" value="{{ date('Y-m-01') }}" class="form-control form-control-sm" onchange="get_datatable()">
                                </div>
                                
                                <div class="col-md-auto">
                                    <label class="form-label small fw-bold text-dark">To Date</label>
                                    <input type="date" id="to_date" value="{{ date('Y-m-t') }}" class="form-control form-control-sm" onchange="get_datatable()">
                                </div>

                                <div class="col-md-auto">
                                    <label class="form-label small fw-bold text-dark">Action</label>
                                    <select id="action_filter" class="form-select form-select-sm" onchange="get_datatable()" style="min-width: 120px;">
                                        <option value="">All Actions</option>
                                        <option value="Edit">Edited</option>
                                        <option value="Delete">Deleted</option>
                                    </select>
                                </div>

                                <div class="col-md">
                                    <label class="form-label small fw-bold text-dark">Customer</label>
                                    <select id="customer_filter" class="form-select form-select-sm js-example-basic-single" onchange="get_datatable()">
                                        <option value="">All Customers</option>
                                        @foreach($customers as $customer)
                                            <option value="{{ $customer->id }}">{{ $customer->name }} ({{ $customer->code }})</option>
                                        @endforeach
                                    </select>
                                </div>
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
            $('.js-example-basic-single').select2({
                width: '100%'
            });
            get_datatable();
        });

        function get_datatable(page){
            var $container = $('#get_datatable');
            if ($container.length) {
                $container.html('<div class="loader-box"><div class="loader-37"></div></div>');
                var value = $('#basic-2_value').val();
                var from_date = $('#from_date').val();
                var to_date = $('#to_date').val();
                var action = $('#action_filter').val();
                var customer_id = $('#customer_filter').val();
                var page = page ?? 1;
                $.ajax({
                    url: '{{ route("customer_ledger.logs_datatable") }}',
                    data: { 
                        page: page, 
                        value: value, 
                        from_date: from_date, 
                        to_date: to_date, 
                        action: action, 
                        customer_id: customer_id
                    },
                    type: 'GET',
                    success: function(data){
                        $container.html(data);
                    }
                });
            }
        }

        $(document).on('click','.pages a',function(n){
            n.preventDefault();
            var page = $(this).attr('href').split("page=")[1];
            get_datatable(page);
        });

        $('#basic-2_value').on('change', function() { get_datatable(); });
    </script>
@endsection
