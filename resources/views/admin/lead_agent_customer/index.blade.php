@extends('layouts.admin.app')

@section('title', 'Lead Agent / Customer Master')

@section('breadcrumb-items')
    <li class="breadcrumb-item">Lead Master</li>
    <li class="breadcrumb-item active">Lead Agent / Customer</li>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header pb-0">
                    <h5>Lead Agent / Customer Master</h5>
                </div>
                <div class="card-body">
                    <div id="basic-2_wrapper" class="dataTables_wrapper px-2">
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
                        <div class="dataTables_filter d-flex align-items-center gap-2">
                            <label class="mb-0">Role:
                                <select id="role_filter" class="form-control form-control-sm" onchange="get_datatable()">
                                    <option value="">All</option>
                                    <option value="Customer">Customer</option>
                                    <option value="Agent">Agent</option>
                                </select>
                            </label>

                            <label class="mb-0">Executive:
                                <select id="sale_executive_filter" class="form-control form-control-sm" onchange="get_datatable()">
                                    <option value="">All</option>
                                    @foreach($sales_executives as $se)
                                        <option value="{{$se->id}}">{{$se->name}}</option>
                                    @endforeach
                                </select>
                            </label>
                            <label class="mb-0">Search:
                                <input type="search" id="basic-2_search" class="form-control form-control-sm" placeholder="Search Master..." onkeyup="get_datatable()">
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

<div class="modal fade" id="edit_modal" aria-labelledby="mySmallModalLabel" aria-hidden="true">
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
        var sale_executive_id = $('#sale_executive_filter').val();
        var role_filter = $('#role_filter').val();
        var page = page ?? 1;

        $.ajax({
            url: '{{ route("lead_agent_customer.datatable") }}',
            data: { 
                page: page, 
                value: value, 
                search: search, 
                sale_executive_id: sale_executive_id, 
                role_filter: role_filter 
            },
            type: 'GET',
            success: function(data){
                $container.html(data);
                $('#basic-test').DataTable({ 
                    dom: '{{ auth()->user()->role_as == "Admin" ? "Brt" : "rt" }}', 
                    buttons: ['copy', 'excel', 'csv', 'pdf', 'print'],
                    "pageLength": -1, 
                    responsive: true, 
                    ordering: false
                });
            }
        });
    }

    $('#basic-2_value').on('change', function() { get_datatable(); });

    function delete_lac(id){
        swal({
            title: "Delete record?",
            text: "This action cannot be undone.",
            icon: "warning",
            buttons: true,
            dangerMode: true,
        })
        .then((willDelete) => {
            if (willDelete) {
                $.ajax({
                    url: '{{ route("lead_agent_customer.delete", "") }}/' + id,
                    type: 'GET',
                    success: function(data){
                        if(data.result == 1){
                            $.notify({ message: data.message }, { type: 'success' });
                            get_datatable();
                        }
                    }
                });
            }
        });
    }

    function change_status(id){
        $.ajax({
            url: '{{ route("lead_agent_customer.change_status", "") }}/' + id,
            type: 'GET',
            success: function(data){
                if(data.result == 1){
                    $.notify({ message: data.message }, { type: 'info' });
                }
            }
        });
    }
</script>
@endsection
