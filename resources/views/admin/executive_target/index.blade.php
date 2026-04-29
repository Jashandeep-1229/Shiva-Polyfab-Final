@extends('layouts.admin.app')

@section('title', 'Executive Target')

@section('css')
<style>
    .target-card {
        border: none;
        border-radius: 12px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.05);
        transition: transform 0.3s ease;
    }
    .target-card:hover {
        transform: translateY(-5px);
    }
    .stat-icon {
        width: 48px;
        height: 48px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        margin-bottom: 15px;
    }
</style>
@endsection

@section('breadcrumb-items')
    <li class="breadcrumb-item">Sales Management</li>
    <li class="breadcrumb-item active">Executive Target</li>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <form id="filterForm" class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Executive</label>
                            <select id="executive_id" class="form-control form-control-sm">
                                <option value="">All Executives</option>
                                @foreach($executives as $ex)
                                    <option value="{{ $ex->id }}">{{ $ex->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">From Date</label>
                            <input type="date" id="from_date" value="{{ date('Y-m-01') }}" class="form-control form-control-sm">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">To Date</label>
                            <input type="date" id="to_date" value="{{ date('Y-m-d') }}" class="form-control form-control-sm">
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <button type="button" onclick="get_datatable()" class="btn btn-primary btn-sm w-100">Filter Results</button>
                        </div>
                    </form>
                </div>
            </div>

            <div id="target_summary">
                <!-- Summary cards will be loaded here -->
            </div>

            <div class="card">
                <div class="card-body">
                    <div class="dt-ext table-responsive" id="get_datatable">
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

    function get_datatable(){
        var $container = $('#get_datatable');
        $container.html('<div class="loader-box"><div class="loader-37"></div></div>');
        
        var executive_id = $('#executive_id').val();
        var from_date = $('#from_date').val();
        var to_date = $('#to_date').val();

        $.ajax({
            url: '{{ route("executive_target.datatable") }}',
            data: { executive_id: executive_id, from_date: from_date, to_date: to_date },
            type: 'GET',
            success: function(data) {
                $container.html(data);
            }
        });
    }
</script>
@endsection
