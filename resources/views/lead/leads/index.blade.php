@extends('layouts.admin.app')

@section('title', 'Customer Leads')

@section('breadcrumb-items')
    <li class="breadcrumb-item">Leads</li>
    <li class="breadcrumb-item active">All Leads</li>
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
                        <h5>{{ $title ?? 'Lead Repository' }}</h5>
                        <a href="{{ route('lead.create') }}" class="btn btn-primary btn-sm">Add New Lead</a>
                    </div>
                    
                    <div class="row g-2 align-items-center">
                        <div class="col-md-2">
                            <div class="input-group input-group-sm">
                                <span class="input-group-text"><i class="fa fa-search"></i></span>
                                <input type="text" id="basic-2_search" class="form-control" placeholder="Search...">
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
                        <div class="col-md-2">
                            <select id="filter_source" class="form-select form-select-sm">
                                <option value="">- All Sources -</option>
                                @foreach($sources as $sc)
                                    <option value="{{ $sc->id }}">{{ $sc->name }}</option>
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
                            <select id="filter_tag" class="form-select form-select-sm">
                                <option value="">- All Tags -</option>
                                @foreach($tags as $t)
                                    <option value="{{ $t->id }}">{{ $t->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-1">
                            <select id="basic-2_value" class="form-select form-select-sm">
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
@endsection

@section('script')
<script>
    let currentPage = 1;

    // Debounce function
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
        
        // Save scroll position
        var scrollPos = $(window).scrollTop();
        
        // Show subtle loading indicator if not initial load
        if($container.children().length > 1) {
            $container.css('opacity', '0.5');
        } else {
            $container.html('<div class="loader-box"><div class="loader-37"></div></div>');
        }
        
        var value = $('#basic-2_value').val();
        var search = $('#basic-2_search').val();
        var status_id = $('#filter_status').val();
        var source_id = $('#filter_source').val();
        var assigned_user_id = $('#filter_user').val();
        var tag_id = $('#filter_tag').val();
        var from_date = $('#from_date').val();
        var to_date = $('#to_date').val();
        var date = "{{ request('date') }}"; // pass through dashboard URL parameter
        var type = "{{ $type ?? '' }}";
        
        $.ajax({
            url: '{{ route("lead.datatable") }}',
            data: { 
                page: page, 
                value: value, 
                search: search,
                status_id: status_id,
                source_id: source_id,
                assigned_user_id: assigned_user_id,
                tag_id: tag_id,
                from_date: from_date,
                to_date: to_date,
                date: date,
                type: type
            },
            type: 'GET',
            success: function(data) {
                $container.html(data).css('opacity', '1');
                
                // Restore scroll position
                $(window).scrollTop(scrollPos);
                
                // Initialize tooltips if needed
                var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
                var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                    return new bootstrap.Tooltip(tooltipTriggerEl)
                });
                
                // Style the table as a DataTable for features like fixed header or column adjustment
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

    $(document).ready(function() {
        // Initial load
        get_datatable();

        // Search and filter triggers
        $('#basic-2_search').on('keyup search', debounce(function() { 
            get_datatable(); 
        }, 500));

        $('#basic-2_value, #filter_status, #filter_source, #filter_user, #filter_tag, #from_date, #to_date').on('change', function() { 
            get_datatable(); 
        });

        // Pagination links
        $(document).on('click', '.pages a', function(e) {
            e.preventDefault();
            var page = $(this).attr('href').split("page=")[1];
            get_datatable(page);
        });
    });

    function history_modal(id) {
        var url = "{{ route('lead.history_modal', ':id') }}";
        url = url.replace(':id', id);
        $('#ajax_modal_dialog').html('<div class="modal-content"><div class="modal-body text-center p-4"><div class="loader-box"><div class="loader-37"></div></div></div></div>');
        $('#lead_ajax_modal').modal('show');
        $.get(url, function(data) {
            $('#ajax_modal_dialog').html(data);
        });
    }

    function followup_modal(id) {
        var url = "{{ route('lead.followup_modal', ':id') }}";
        url = url.replace(':id', id);
        $('#ajax_modal_dialog').html('<div class="modal-content"><div class="modal-body text-center p-4"><div class="loader-box"><div class="loader-37"></div></div></div></div>');
        $('#lead_ajax_modal').modal('show');
        $.get(url, function(data) {
            $('#ajax_modal_dialog').html(data);
        });
    }

    function delete_lead(id) {
        swal({
            title: "Manage Deletion",
            text: "Do you want to delete ONLY this last enquiry or EVERYTHING for this client (all enquiries)?",
            icon: "warning",
            buttons: {
                cancel: "Cancel",
                single: {
                  text: "Delete Single",
                  value: "single",
                  className: "btn-warning"
                },
                all: {
                  text: "Delete Everything",
                  value: "all",
                  className: "btn-danger"
                }
            },
            dangerMode: true,
        })
        .then((choice) => {
            if (!choice) return;
            
            var url = choice === 'all' 
                ? "{{ url('lead/leads/destroy-all-for-client') }}/" + id
                : "{{ url('lead/leads') }}/" + id;

            $.ajax({
                url: url,
                type: 'DELETE',
                data: { _token: '{{ csrf_token() }}' },
                success: function(res) {
                    if (res.success) {
                        $.notify({ title: 'Deleted', message: res.message }, { type: 'danger' });
                        get_datatable(); // Refresh the datatable in real-time
                    } else {
                        $.notify({ title: 'Error', message: 'Something went wrong.' }, { type: 'danger' });
                    }
                },
                error: function() {
                    $.notify({ title: 'Error', message: 'Unauthorized or server error.' }, { type: 'danger' });
                }
            });
        });
    }

    // Handle transfer form via AJAX
    $(document).on('submit', '.transfer-lead-form', function(e) {
        e.preventDefault();
        var form = $(this);
        var url = form.attr('action');
        var formData = form.serialize();
        var modalId = form.closest('.modal').attr('id');

        $.ajax({
            url: url,
            type: 'POST',
            data: formData,
            success: function(res) {
                $('#' + modalId).modal('hide');
                $('body').removeClass('modal-open');
                $('.modal-backdrop').remove();
                
                if(typeof get_datatable === 'function') {
                    get_datatable(); // Refresh without losing page/scroll
                }
                $.notify({ title: 'Success', message: 'Lead transferred successfully' }, { type: 'success' });
            },
            error: function() {
                $.notify({ title: 'Error', message: 'Something went wrong during transfer.' }, { type: 'danger' });
            }
        });
    });

    function updateJobCardNo(id, name, existingNo = '') {
        $('#update_jc_lead_name').text(name);
        $('#update_jc_id_hidden').val(id);
        var url = "{{ route('lead.leads.update-job-card-no', ':id') }}";
        url = url.replace(':id', id);
        $('#update_jc_form').attr('action', url);
        $('#update_jc_input').val(existingNo).trigger('keyup');
        $('#jc_validation_msg').hide();
        
        // If admin and has existing No, allow them to clear it or save it
        @if(Auth::user()->role == 'admin')
            $('#jc_submit_btn').text(existingNo ? 'Update / Clear' : 'Verify & Link').prop('disabled', false);
        @else
            $('#jc_submit_btn').text('Verify & Link').prop('disabled', true);
        @endif
        
        $('#update_jc_modal').modal('show');
    }

    $(document).on('keyup', '#update_jc_input', function() {
        var val = $(this).val();
        var leadId = $('#update_jc_id_hidden').val();
        var msgDiv = $('#jc_validation_msg');
        var btn = $('#jc_submit_btn');
        var isAdmin = {{ Auth::user()->role == 'admin' ? 'true' : 'false' }};

        if (val.length >= 5) {
            $.post("{{ route('lead.check-job-card-no') }}", {
                _token: '{{ csrf_token() }}',
                job_card_no: val,
                lead_id: leadId
            }, function(res) {
                if (res.status === 'success') {
                    msgDiv.html(`<i class="fa fa-check-circle"></i> ${res.message}`).removeClass('text-danger').addClass('text-success').show();
                    btn.prop('disabled', false);
                } else {
                    msgDiv.html(`<i class="fa fa-times-circle"></i> ${res.message}`).removeClass('text-success').addClass('text-danger').show();
                    btn.prop('disabled', true);
                }
            });
        } else {
            msgDiv.hide();
            // Admins can empty the field (length 0)
            if (isAdmin && val.length === 0) {
                btn.prop('disabled', false);
            } else {
                btn.prop('disabled', true);
            }
        }
    });
</script>

{{-- Common Modal for AJAX content --}}
<div class="modal fade" id="lead_ajax_modal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" id="ajax_modal_dialog" role="document">
        {{-- Content loaded via AJAX (now includes modal-content) --}}
    </div>
</div>

{{-- Modal for Order No Update --}}
<div class="modal fade" id="update_jc_modal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <form id="update_jc_form" method="POST" class="modal-content">
            @csrf
            <input type="hidden" id="update_jc_id_hidden">
            <div class="modal-header">
                <h5 class="modal-title">Link Order No to Lead</h5>
                <button class="btn-close" type="button" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="f-13">Linking Order No for: <strong id="update_jc_lead_name"></strong></p>
                <div class="mb-3">
                    <label class="form-label">Enter valid Order No (e.g. JC-24-25-01)</label>
                    <input type="text" name="order_no" id="update_jc_input" class="form-control" placeholder="Verification required..." required autocomplete="off">
                    <div id="jc_validation_msg" class="mt-2 f-12" style="display: none;"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-primary" type="submit" id="jc_submit_btn" disabled>Verify & Link</button>
            </div>
        </form>
    </div>
</div>
@endsection
