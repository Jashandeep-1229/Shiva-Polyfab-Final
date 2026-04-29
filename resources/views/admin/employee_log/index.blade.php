@extends('layouts.admin.app')

@section('title', 'Employee Activity Log')

@section('css')
<style>
    .log-card { border-radius: 15px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); border: none; }
    .dt-controls-wrap {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 15px;
        margin-bottom: 20px;
    }
    .dt-controls-item {
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .dt-controls-item label {
        margin-bottom: 0;
        font-weight: 700;
        font-size: 10px;
        text-transform: uppercase;
        color: #64748b;
        white-space: nowrap;
    }
    .badge-event { padding: 4px 8px; border-radius: 4px; font-weight: 600; font-size: 10px; text-transform: uppercase; }
    .badge-created { background: #dcfce7; color: #166534; }
    .badge-updated { background: #fef9c3; color: #854d0e; }
    .badge-deleted { background: #fee2e2; color: #991b1b; }
    .badge-login { background: #e0e7ff; color: #3730a3; }
</style>
@endsection

@section('breadcrumb-items')
    <li class="breadcrumb-item">Employee Activity Log</li>
@endsection

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card log-card mb-4 border-0 shadow-sm overflow-hidden">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-4">
                            <div>
                                <h4 class="mb-1 fw-bold text-primary">Employee Activity Log</h4>
                                <p class="mb-0 text-muted small text-uppercase fw-bold">Track every action across all modules</p>
                            </div>
                        </div>

                        <div class="dt-controls-wrap px-2 mt-2">
                            <div class="dt-controls-item">
                                <label>Show</label>
                                <select id="limit_filter" class="form-control form-control-sm" style="width: auto;" onchange="get_datatable()">
                                    <option value="50" selected>50</option>
                                    <option value="100">100</option>
                                    <option value="250">250</option>
                                    <option value="500">500</option>
                                </select>
                            </div>

                            <div class="dt-controls-item">
                                <label>From Date</label>
                                <input type="date" id="from_date" value="{{ request()->from_date ?? date('Y-m-d') }}" class="form-control form-control-sm" onchange="get_datatable()">
                                <label>To Date</label>
                                <input type="date" id="to_date" value="{{ request()->to_date ?? date('Y-m-d') }}" class="form-control form-control-sm" onchange="get_datatable()">
                            </div>

                            <div class="dt-controls-item">
                                <label>Employee</label>
                                <select id="user_filter" class="form-control form-control-sm" onchange="get_datatable()" style="min-width: 150px;">
                                    <option value="">All Employees</option>
                                    @foreach($users as $user)
                                        <option value="{{ $user->id }}" {{ request()->user_id == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="dt-controls-item">
                                <label>Search</label>
                                <input type="search" id="basic-2_search" class="form-control form-control-sm" placeholder="Search module, action, record..." style="min-width: 250px;">
                            </div>

                            <div class="dt-controls-item">
                                <button type="button" class="btn btn-outline-danger btn-sm fw-bold shadow-sm" onclick="deleteAllLogs()" style="border-radius: 6px; padding: 0.25rem 0.75rem;">
                                    <i class="fa fa-trash-o me-1"></i> CLEAR LOGS
                                </button>
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

    <!-- Activity Details Modal -->
    <div class="modal fade" id="activityModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-primary text-white py-3">
                    <h5 class="modal-title fw-bold"><i class="fa fa-info-circle me-2"></i> Activity Details</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-0" id="activity_details_content">
                    <!-- Loaded via AJAX -->
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
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

        function debounce(func, wait) {
            let timeout;
            return function(...args) {
                const context = this;
                clearTimeout(timeout);
                timeout = setTimeout(() => func.apply(context, args), wait);
            };
        }

        var currentSearchRequest = null;
        function get_datatable(page){
            var $container = $('#get_datatable');
            if ($container.length) {
                if (currentSearchRequest) {
                    currentSearchRequest.abort();
                }
                $container.html('<div class="loader-box"><div class="loader-37"></div><p class="text-center text-muted small mt-2">Loading logs...</p></div>');
                
                var search = $('#basic-2_search').val();
                var limit = $('#limit_filter').val();
                var from_date = $('#from_date').val();
                var to_date = $('#to_date').val();
                var user_id = $('#user_filter').val();
                var pageNum = page ?? 1;
                
                currentSearchRequest = $.ajax({
                    url: '{{ route("employee_log.datatable") }}',
                    data: { 
                        page: pageNum, 
                        search: search, 
                        limit: limit, 
                        from_date: from_date, 
                        to_date: to_date, 
                        user_id: user_id,
                        log_name: '{{ request()->log_name }}',
                        event: '{{ request()->event }}'
                    },
                    type: 'GET',
                    success: function(data){
                        $container.html(data);
                        currentSearchRequest = null;
                    },
                    error: function(xhr, status, error){
                        if(status !== 'abort') {
                            $container.html('<div class="alert alert-danger">Error loading data. Please try again.</div>');
                            currentSearchRequest = null;
                        }
                    }
                });
            }
        }

        $(document).on('click','.pages a',function(n){
            n.preventDefault();
            var page = $(this).attr('href').split("page=")[1];
            get_datatable(page);
        });

        $('#basic-2_search').on('input search', debounce(function() { 
            get_datatable(); 
        }, 400));

        function showLogDetails(id) {
            var url = "{{ route('employee_log.details', ':id') }}";
            url = url.replace(':id', id);
            
            $('#activity_details_content').html('<div class="text-center py-5"><div class="spinner-border text-primary" role="status"></div><div class="mt-2 text-muted">Fetching record details...</div></div>');
            $('#activityModal').modal('show');

            $.get(url, function(data) {
                if (data) {
                    $('#activity_details_content').html(data);
                } else {
                    $('#activity_details_content').html('<div class="alert alert-warning m-4">No details found for this action.</div>');
                }
            }).fail(function() {
                $('#activity_details_content').html('<div class="alert alert-danger m-4">Error: Could not load data. Please refresh and try again.</div>');
            });
        }

        function deleteAllLogs() {
            var search = $('#basic-2_search').val();
            var from_date = $('#from_date').val();
            var to_date = $('#to_date').val();
            var user_id = $('#user_filter').val();

            var confirmTitle = "Are you sure?";
            var confirmText = "Do you want to clear these logs based on your current filters?";
            if(!user_id && !from_date && !to_date && !search) {
                confirmText = "No filters selected. This will delete ALL logs in the system!";
            }

            swal({
                title: confirmTitle,
                text: confirmText,
                icon: 'warning',
                buttons: true,
                dangerMode: true,
            }).then((willDelete) => {
                if (willDelete) {
                    $.ajax({
                        url: '{{ route("employee_log.delete_all") }}',
                        data: { 
                            _token: '{{ csrf_token() }}',
                            search: search, 
                            from_date: from_date, 
                            to_date: to_date, 
                            user_id: user_id 
                        },
                        type: 'POST',
                        success: function(data){
                            if(data.result == 1) {
                                swal('Success', data.message, 'success');
                                get_datatable();
                            }
                        },
                        error: function(xhr, status, error){
                            swal('Error', 'Could not delete logs. Please try again.', 'error');
                        }
                    });
                }
            });
        }
    </script>
@endsection
