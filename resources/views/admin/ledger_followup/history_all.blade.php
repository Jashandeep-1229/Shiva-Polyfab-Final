@extends('layouts.admin.app')
@section('title', 'History Followup Logs')
@section('css')
<style>
    .followup-card { border-radius: 15px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); border: none; }
    .dt-controls-wrap { display: flex; flex-wrap: wrap; gap: 20px; align-items: flex-end; padding: 15px 0; border-bottom: 1px solid #f1f5f9; margin-bottom: 15px; }
    .dt-controls-item { display: flex; flex-direction: column; gap: 5px; }
    .dt-controls-item label { font-weight: 700; font-size: 12px; color: #64748b; text-uppercase: uppercase; margin-bottom: 0; }
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
    <li class="breadcrumb-item">Account Department</li>
    <li class="breadcrumb-item active">History Followup</li>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card followup-card mb-4">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-4">
                        <div>
                            <h4 class="mb-1 fw-bold text-primary">History Followup Logs</h4>
                            <p class="mb-0 text-muted small text-uppercase fw-bold">Complete interaction history and performance tracking</p>
                        </div>
                    </div>

                    <div class="dt-controls-wrap px-2">
                        <div class="dt-controls-item" style="flex: 1; min-width: 200px;">
                            <label>Customer Selection</label>
                            <select id="customer_filter" class="form-control form-control-sm js-example-basic-single" onchange="get_datatable()">
                                <option value="">All Customers</option>
                                @foreach($customers as $customer)
                                    <option value="{{ $customer->id }}">{{ $customer->name }} ({{ $customer->code }})</option>
                                @endforeach
                            </select>
                        </div>

                        @if(count($executives) > 0)
                        <div class="dt-controls-item" style="flex: 1; min-width: 200px;">
                            <label>Executive Selection</label>
                            <select id="executive_filter" class="form-control form-control-sm" onchange="get_datatable()">
                                <option value="">All Executives</option>
                                @foreach($executives as $ex)
                                    <option value="{{ $ex->id }}">{{ $ex->name }} ({{ $ex->role_as }})</option>
                                @endforeach
                            </select>
                        </div>
                        @endif

                        <div class="dt-controls-item">
                            <label>From Date</label>
                            <input type="date" id="from_date_filter" class="form-control form-control-sm" onchange="get_datatable()">
                        </div>
                        <div class="dt-controls-item">
                            <label>To Date</label>
                            <input type="date" id="to_date_filter" class="form-control form-control-sm" onchange="get_datatable()">
                        </div>

                        <div class="dt-controls-item" style="flex: 1; min-width: 200px;">
                            <label>Quick Search</label>
                            <input type="search" id="basic-2_search" class="form-control form-control-sm" placeholder="Search Remarks, Subject...">
                        </div>
                    </div>

                    <div id="get_datatable">
                        <div class="loader-box"><div class="loader-37"></div></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="dynamic_modal_container"></div>
@endsection

@section('script')
<script>
    $(document).ready(function(){
        $('.js-example-basic-single').select2({ width: '100%' });
        get_datatable();

        var searchTimeout;
        $('#basic-2_search').on('keyup', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(function() {
                get_datatable();
            }, 500);
        });
    });

    function get_datatable(page){
        var search = $('#basic-2_search').val();
        var customer_id = $('#customer_filter').val();
        var executive_id = $('#executive_filter').length ? $('#executive_filter').val() : '';
        var from_date = $('#from_date_filter').val();
        var to_date = $('#to_date_filter').val();
        var page = page ?? 1;

        $('#get_datatable').html('<div class="loader-box"><div class="loader-37"></div></div>');
        
        $.ajax({
            url: '{{ route("ledger_followup.history_all_datatable") }}',
            data: { 
                page: page, 
                search: search, 
                customer_id: customer_id, 
                executive_id: executive_id, 
                from_date: from_date, 
                to_date: to_date
            },
            type: 'GET',
            success: function(data){
                $('#get_datatable').html(data);
            }
        });
    }

    $(document).on('click', '.pages a', function(e){
        e.preventDefault();
        get_datatable($(this).attr('href').split('page=')[1]);
    });
</script>
@endsection
