@extends('layouts.admin.app')

@section('title', 'Cylinder Agent Report')

@section('css')
<style>
    .stats-card {
        transition: all 0.3s ease;
        border: none;
        box-shadow: 0 4px 15px rgba(0,0,0,0.05);
    }
    .stats-card:hover {
        transform: translateY(-5px);
    }
    .status-badge {
        font-weight: 600;
        padding: 5px 15px;
        border-radius: 20px;
    }
</style>
@endsection

@section('breadcrumb-items')
    <li class="breadcrumb-item">MIS Reports</li>
    <li class="breadcrumb-item active">Cylinder Agent Report</li>
@endsection

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header pb-0">
                        <h5>Cylinder Agent Performance</h5>
                    </div>
                    <div class="card-body">
                        <div class="row mb-4 justify-content-between">
                            <div class="col-md-3">
                                <label class="f-12">Select Cylinder Agent</label>
                                <select name="cylinder_agent" id="cylinder_agent" class="form-control form-control-sm" onchange="get_datatable()">
                                    <option value="">Select Cylinder Agent</option>
                                    @foreach ($cylinder_agents as $agent)
                                        <option value="{{ $agent->id }}">{{ $agent->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="f-12">From Date</label>
                                <input type="date" id="from_date" class="form-control form-control-sm" onchange="get_datatable()">
                            </div>
                            <div class="col-md-3">
                                <label class="f-12">To Date</label>
                                <input type="date" id="to_date" class="form-control form-control-sm" onchange="get_datatable()">
                            </div>
                            <div class="col-md-2">
                                <label class="f-12">Show Entries</label>
                                <select id="basic_value" class="form-control form-control-sm" onchange="get_datatable()">
                                    <option value="50">50</option>
                                    <option value="250" selected>250</option>
                                    <option value="500">500</option>
                                </select>
                            </div>
                        </div>

                        <div id="report_content">
                            <div class="text-center p-5">
                                <i class="fa fa-info-circle fa-3x text-info"></i>
                                <h5 class="mt-3">Please select a Cylinder Agent to view report</h5>
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
        function get_datatable(page) {
            var agent = $('#cylinder_agent').val();
            if (!agent) return;

            var $container = $('#report_content');
            $container.html('<div class="loader-box"><div class="loader-37"></div></div>');
            
            var value = $('#basic_value').val();
            var from_date = $('#from_date').val();
            var to_date = $('#to_date').val();
            var page = page ?? 1;

            $.ajax({
                url: '{{ route("cylinder_job.agent_report_datatable") }}',
                data: { 
                    page: page, 
                    value: value, 
                    cylinder_agent: agent, 
                    from_date: from_date, 
                    to_date: to_date,
                    _token: "{{csrf_token() }}"
                },
                type: 'GET',
                success: function(data) {
                    $container.html(data);
                    $('#basic-test').DataTable({ 
                        dom: '{{ auth()->user()->role_as == "Admin" ? "Brt" : "rt" }}', 
                        "pageLength": -1, 
                        responsive: true, 
                                                                        ordering: false 
                    });
                }
            });
        }

        $(document).on('click', '.pages a', function(e) {
            e.preventDefault();
            var page = $(this).attr('href').split("page=")[1];
            get_datatable(page);
        });
    </script>
@endsection
