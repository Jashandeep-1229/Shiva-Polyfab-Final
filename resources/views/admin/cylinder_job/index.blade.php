@extends('layouts.admin.app')

@section('title', 'Cylinder Job')

@section('css')

@endsection

@section('breadcrumb-items')
    <li class="breadcrumb-item">Cylinder Job</li>
@endsection

@section('content')
    <div class="container-fluid">
        <!-- All Client Table Start -->
        <div class="row">
            <div class="col-12">
               
                <div class="card">
                    <div class="card-body">
                        <div  id="basic-2_wrapper"  class="dataTables_wrapper row px-2 justify-content-between" onchange="get_datatable()">
                            <div class="col-md-3 col-lg-auto">
                                <div class="">
                                    <label class="f-12">Show Entries
                                        <select name="basic-2_value"  id="basic-2_value" aria-controls="basic-2" class="form-control form-control-sm">
                                            <option value="50">50</option>
                                            <option value="250" selected>250</option>
                                            <option value="500">500</option>
                                            <option value="1000">1000</option>
                                        </select>
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-3 col-lg-auto">
                                <label class="f-12">Filter By
                                        <select name="filter_by"  id="filter_by" aria-controls="basic-2" class="form-control form-control-sm">
                                            <option value="check_in_date" selected>Check In Date</option>
                                            <option value="check_out_date" >Check Out Date</option>
                                        </select>
                                    </label>
                            </div>
                             <div class="col-md-3 col-lg-auto">
                                <label class="f-12">From Date
                                        <input type="date" name="from_date" id="from_date" class="form-control form-control-sm">
                                    </label>
                            </div>
                            <div class="col-md-3 col-lg-auto">
                                <label class="f-12">To Date
                                        <input type="date" name="to_date" id="to_date" class="form-control form-control-sm">
                                    </label>
                            </div>
                            <div class="col-md-3 col-lg-auto">
                                <label class="f-12">Select Cylinder Agent
                                        <select name="cylinder_agent" id="cylinder_agent" class="form-control form-control-sm">
                                            <option value="">Select Cylinder Agent</option>
                                            @foreach ($cylinder_agents as $cylinder_agent)
                                                <option value="{{ $cylinder_agent->id }}">{{ $cylinder_agent->name }}</option>
                                            @endforeach
                                        </select>
                                    </label>
                            </div>
                            @if(request()->type == 'report')
                            <div class="col-md-3 col-lg-auto">
                                <label class="f-12">Status
                                        <select name="status_filter" id="status_filter" class="form-control form-control-sm">
                                            <option value="">All</option>
                                            <option value="early">Early Time (< 7 Days)</option>
                                            <option value="ontime">On Time (7-10 Days)</option>
                                            <option value="late">Late (> 10 Days)</option>
                                        </select>
                                    </label>
                            </div>
                            @endif
                            <div class="col-md-3 col-lg-auto ">
                                <div class="">
                                      <label class="f-12">Search
                                        <input type="search"  id="basic-2_search" class="form-control form-control-sm" placeholder="Search" aria-controls="basic-2" data-bs-original-title="" title="">
                                    </label>
                                </div>
                            </div>
                            @if(auth()->user()->role_as == 'Admin')
                            <div class="col-md-3 col-lg-auto">
                                <label class="f-12">Action
                                    <button class="btn btn-primary btn-sm d-block mt-1 f-12 p-1" data-bs-toggle="modal" data-bs-target="#importModal"><i class="fa fa-upload"></i> Import History</button>
                                </label>
                            </div>
                            @endif
                        </div>

                        <!-- Import Modal -->
                        <div class="modal fade" id="importModal" tabindex="-1" aria-labelledby="importModalLabel" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="importModalLabel">Import Cylinder History</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <form id="importForm" enctype="multipart/form-data">
                                        @csrf
                                        <div class="modal-body">
                                            <div class="mb-3">
                                                <label for="file" class="form-label text-dark">Select CSV File</label>
                                                <input type="file" name="file" id="file" class="form-control" required>
                                                <small class="text-muted">Required Columns: Name of Job, Cylinder Given To, Check Out Date, Check In Date</small>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                            <button type="submit" class="btn btn-primary" id="importBtn">Import</button>
                                        </div>
                                    </form>
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
        <!-- All Client Table End -->
    </div>


    <audio id="myAudio" controls class="d-none">
        <source src="{{ asset('audio/Beep.wav') }}" type="audio/wav">
    </audio>
@endsection
@section('script')
    <script>
        $(document).ready(function(){
            get_datatable();

            $('#importForm').on('submit', function(e) {
                e.preventDefault();
                var formData = new FormData(this);
                $('#importBtn').prop('disabled', true).text('Importing...');

                $.ajax({
                    url: '{{ route("cylinder_job.import") }}',
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        if (response.result == 1) {
                            $.notify({ title: 'Success', message: response.message }, { type: 'success', delay: 2000 });
                            $('#importModal').modal('hide');
                            get_datatable();
                        } else {
                            $.notify({ title: 'Error', message: response.message }, { type: 'danger', delay: 5000 });
                        }
                        $('#importBtn').prop('disabled', false).text('Import');
                    },
                    error: function() {
                        $.notify({ title: 'Error', message: 'Something went wrong!' }, { type: 'danger', delay: 5000 });
                        $('#importBtn').prop('disabled', false).text('Import');
                    }
                });
            });
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
                var filter_by = $('#filter_by').val();
                var from_date = $('#from_date').val();
                var to_date = $('#to_date').val();
                var cylinder_agent = $('#cylinder_agent').val();
                var status_filter = $('#status_filter').val();
                $.ajax({
                    url: '{{ route("cylinder_job.datatable") }}',
                    data: { 
                        page: page, 
                        value: value, 
                        search: search, 
                        _token: "{{csrf_token() }}",
                        type: "{{request('type')}}",
                        filter_by: filter_by, 
                        from_date: from_date, 
                        to_date: to_date, 
                        cylinder_agent: cylinder_agent,
                        status_filter: status_filter 
                    },
                    type: 'GET',
                    success: function(data){
                        $container.html(data);
                        $('#basic-test').DataTable({ dom: '{{ auth()->user()->role_as == "Admin" ? "Brt" : "rt" }}', "pageLength": -1 , responsive: true, ordering: false});
                    }
                });
            }
        }

        // $('#basic-2_search').on('keyup search', debounce(function() { get_datatable(); }, 500));
        // $('#basic-2_value').on('change', function() { get_datatable(); });

       
    </script>
@endsection
