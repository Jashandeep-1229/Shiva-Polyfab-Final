@extends('layouts.admin.app')
@section('title', 'Bill Management')
@section('breadcrumb-items')
    <li class="breadcrumb-item">Bill Management</li>
@endsection

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header pb-0 d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">Bill Management</h4>
                        @if(App\Helpers\PermissionHelper::check('bill_management', 'add'))
                            <a href="{{ route('bill.create') }}" class="btn btn-primary btn-sm"><i class="fa fa-plus"></i> Add Manual Bill</a>
                        @endif
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-3">
                                <label>Search</label>
                                <input type="text" id="search" class="form-control form-control-sm" placeholder="Search Bill No, Customer...">
                            </div>
                            <div class="col-md-3">
                                <label>From Date</label>
                                <input type="date" id="from_date" class="form-control form-control-sm">
                            </div>
                            <div class="col-md-3">
                                <label>To Date</label>
                                <input type="date" id="to_date" class="form-control form-control-sm">
                            </div>
                            <div class="col-md-3">
                                <label>Customer</label>
                                <select id="customer_id" class="form-select form-control-sm select2 w-100">
                                    <option value="">All Customers</option>
                                    @foreach($customers as $customer)
                                        <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label>Due Status</label>
                                <select id="due_status" class="form-select form-control-sm select2 w-100">
                                    <option value="">All Bills</option>
                                    <option value="overdue">Overdue</option>
                                    <option value="due_7">Due (7 Days)</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="table-responsive" id="table_data">
                            <!-- Datatable will load here -->
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
        get_data();
        
        $('#search, #from_date, #to_date').on('keyup change', function() {
            get_data();
        });
        
        $('#customer_id, #due_status').change(function() {
            get_data();
        });
    });

    function get_data(page = 1) {
        var search = $('#search').val();
        var from_date = $('#from_date').val();
        var to_date = $('#to_date').val();
        var customer_id = $('#customer_id').val();
        var due_status = $('#due_status').val();
        
        $.ajax({
            url: "{{ route('bill.datatable') }}?page=" + page,
            type: "GET",
            data: {
                search: search,
                from_date: from_date,
                to_date: to_date,
                customer_id: customer_id,
                due_status: due_status
            },
            success: function(response) {
                $('#table_data').html(response);
            }
        });
    }

    $(document).on('click', '.pagination a', function(e) {
        e.preventDefault();
        var page = $(this).attr('href').split('page=')[1];
        get_data(page);
    });

    function deleteCard(id) {
        swal({
            title: "Are you sure?",
            text: "This will permanently delete the Bill and its items!",
            icon: "warning",
            buttons: true,
            dangerMode: true,
        }).then((willDelete) => {
            if (willDelete) {
                var url = "{{ route('bill.delete', ':id') }}";
                url = url.replace(':id', id);
                $.ajax({
                    url: url,
                    type: "GET",
                    success: function(response) {
                        if(response.result == 1) {
                            $.notify({ title:'Success', message:response.message }, { type:'success', });
                            get_data();
                        } else {
                            $.notify({ title:'Error', message:response.message }, { type:'danger', });
                        }
                    }
                });
            }
        });
    }
</script>
@endsection
