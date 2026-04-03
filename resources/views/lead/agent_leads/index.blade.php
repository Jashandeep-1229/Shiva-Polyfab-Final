@extends('layouts.admin.app')

@section('title', 'Agent Leads')

@section('breadcrumb-items')
    <li class="breadcrumb-item">Leads</li>
    <li class="breadcrumb-item active">Agent Leads</li>
@endsection

@section('css')
<style>
    #basic-test th { 
        padding: 10px 15px !important;
        white-space: nowrap;
    }
    #basic-test td {
        vertical-align: middle;
    }
</style>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header pb-2">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5>{{ $title ?? 'Agent Lead Repository' }}</h5>
                        <a href="{{ route('lead.agent_leads.create') }}" class="btn btn-primary btn-sm">Add Agent Lead</a>
                    </div>
                    
                    <div class="row g-2 align-items-center">
                        <div class="col-md-2">
                            <div class="input-group input-group-sm">
                                <span class="input-group-text"><i class="fa fa-search"></i></span>
                                <input type="text" id="agent_search" class="form-control" placeholder="Search...">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <input type="date" id="from_date" class="form-control form-control-sm" placeholder="From Date">
                        </div>
                        <div class="col-md-2">
                            <input type="date" id="to_date" class="form-control form-control-sm" placeholder="To Date">
                        </div>
                        <div class="col-md-2">
                            <select id="filter_status" class="form-select form-select-sm">
                                <option value="">- All Steps -</option>
                                @foreach($statuses as $st)
                                    <option value="{{ $st->id }}">{{ $st->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select id="filter_agent" class="form-select form-select-sm">
                                <option value="">- All Agents -</option>
                                @foreach($agents as $agent)
                                    <option value="{{ $agent->id }}">{{ $agent->name }} ({{ $agent->firm_name }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <select id="filter_user" class="form-select form-select-sm">
                                <option value="">- All Users -</option>
                                @foreach($users as $u)
                                    <option value="{{ $u->id }}">{{ $u->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-1">
                            <select id="agent_value" class="form-select form-select-sm">
                                <option value="10">10</option>
                                <option value="50" selected>50</option>
                                <option value="100">100</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="card-body p-0">
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

{{-- Common Modal for AJAX content --}}
<div class="modal fade" id="lead_ajax_modal" tabindex="-1" role="dialog" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-lg" id="ajax_modal_dialog" role="document">
    </div>
</div>
@endsection

@section('script')
<script>
    $(document).ready(function() {
        let currentPage = 1;
        get_datatable();

        function debounce(func, wait) {
            let timeout;
            return function(...args) {
                const context = this;
                clearTimeout(timeout);
                timeout = setTimeout(() => func.apply(context, args), wait);
            };
        }

        function get_datatable(page = currentPage) {
            currentPage = page;
            var $container = $('#get_datatable');
            var scrollPos = $(window).scrollTop();
            
            if($container.children().length > 1) {
                $container.css('opacity', '0.5');
            } else {
                $container.html('<div class="loader-box"><div class="loader-37"></div></div>');
            }
            
                $.ajax({
                    url: '{{ route("lead.agent_leads.datatable") }}',
                    data: { 
                        page: page, 
                        value: $('#agent_value').val(), 
                        search: $('#agent_search').val(),
                        status_id: $('#filter_status').val(),
                        agent_id: $('#filter_agent').val(),
                        assigned_user_id: $('#filter_user').val(),
                        from_date: $('#from_date').val(),
                        to_date: $('#to_date').val(),
                        date: "{{ request('date') }}",
                        type: "{{ $type ?? '' }}"
                    },
                    type: 'GET',
                success: function(data) {
                    $container.html(data).css('opacity', '1');
                    $(window).scrollTop(scrollPos);

                    // Initialize tooltips if needed
                    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
                    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                        return new bootstrap.Tooltip(tooltipTriggerEl)
                    });
                    
                    // Style the table as a DataTable
                    var table = $container.find('table').DataTable({
                        dom: 'rt', // Only table, no built-in search/pagination as we handle it
                        paging: false,
                        info: false,
                        responsive: true,
                        scrollX: true,
                        destroy: true
                    });
                    
                    setTimeout(function() {
                        if (table && table.columns) {
                            table.columns.adjust().responsive.recalc();
                        }
                    }, 300);
                }
            });
        }

        $('#agent_search').on('keyup search', debounce(function() { get_datatable(); }, 500));
        $('#agent_value, #filter_status, #filter_agent, #filter_user, #from_date, #to_date').on('change', function() { get_datatable(); });
        $(document).on('click', '.pages a', function(e) { e.preventDefault(); get_datatable($(this).attr('href').split("page=")[1]); });
    });

    function history_modal(agentId) {
        var url = "{{ route('lead.agent_leads.overall_followup.history', ':id') }}";
        url = url.replace(':id', agentId);
        $('#ajax_modal_dialog').html('<div class="modal-content"><div class="modal-body text-center p-4"><div class="loader-box"><div class="loader-37"></div></div></div></div>');
        $('#lead_ajax_modal').modal('show');
        $.get(url, function(data) {
            var content = `<div class="modal-content"><div class="modal-header"><h5>Agent Activity History</h5><button class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body">${data}</div></div>`;
            $('#ajax_modal_dialog').html(content);
        });
    }

    function openFollowupModal(id) {
        var url = "{{ route('lead.agent_leads.followup_modal', ':id') }}";
        url = url.replace(':id', id);
        $('#ajax_modal_dialog').html('<div class="modal-content"><div class="modal-body text-center p-4"><div class="loader-box"><div class="loader-37"></div></div></div></div>');
        $('#lead_ajax_modal').modal('show');
        $.get(url, function(data) {
            $('#ajax_modal_dialog').html(data);
        });
    }

    let lastAjaxAgentId = null;
    let ajaxModalContentEdited = false;

    function openOverallFollowupModal(agentId) {
        var url = "{{ route('lead.agent_leads.overall_followup_modal', ':id') }}";
        url = url.replace(':id', agentId);
        
        // If it's the same agent and we haven't successfully submitted (modal closed manually), 
        // and we have content in the modal, just show it without reloading
        if (lastAjaxAgentId == agentId && $('#ajax_modal_dialog').html().trim() !== '') {
            $('#lead_ajax_modal').modal('show');
            return;
        }

        lastAjaxAgentId = agentId;
        $('#ajax_modal_dialog').html('<div class="modal-content"><div class="modal-body text-center p-4"><div class="loader-box"><div class="loader-37"></div></div></div></div>');
        $('#lead_ajax_modal').modal('show');
        $.get(url, function(data) {
            $('#ajax_modal_dialog').html(data);
        });
    }

    // Reset tracker when modal is successfully submitted (successfully submitted means it should reload next time)
    $(document).on('ajax_modal_success', function() {
        lastAjaxAgentId = null;
    });
</script>
@endsection
