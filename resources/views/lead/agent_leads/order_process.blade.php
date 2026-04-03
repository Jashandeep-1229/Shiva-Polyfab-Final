@extends('layouts.admin.app')

@section('title', 'Agent Order Process Status')

@section('breadcrumb-items')
    <li class="breadcrumb-item">Agent Lead</li>
    <li class="breadcrumb-item active">Order Process</li>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header pb-0 d-flex justify-content-between align-items-center">
                    <div>
                        <h5>Agent Order Process Monitoring</h5>
                        <span>Track the real-time stage of production for agent-linked leads.</span>
                    </div>
                    <div class="col-md-3">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text"><i class="fa fa-search"></i></span>
                            <input type="text" id="realtime_search" class="form-control" placeholder="Search Agent, Job #..." value="{{ request('search') }}">
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div id="table_container">
                        @include('lead.agent_leads.order_process_table')
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
        let currentPage = 1;

        function debounce(func, wait) {
            let timeout;
            return function(...args) {
                const context = this;
                clearTimeout(timeout);
                timeout = setTimeout(() => func.apply(context, args), wait);
            };
        }

        function refreshTable(page = currentPage) {
            currentPage = page;
            var $container = $('#table_container');
            $container.css('opacity', '0.5');
            
            $.ajax({
                url: '{{ route("lead.agent_leads.order_process") }}',
                data: { 
                    page: page, 
                    search: $('#realtime_search').val()
                },
                type: 'GET',
                success: function(data) {
                    $container.html(data).css('opacity', '1');
                }
            });
        }

        $('#realtime_search').on('keyup search', debounce(function() { refreshTable(1); }, 500));
        $(document).on('click', '.pages a', function(e) { 
            e.preventDefault(); 
            let url = $(this).attr('href');
            if(url) {
                let page = url.split("page=")[1];
                refreshTable(page);
            }
        });
    });
</script>
@endsection
