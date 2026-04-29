@extends('layouts.admin.app')

@section('title')
Order Process - {{$process }}
@endsection

@section('css')
<style>
    .select2-container .select2-selection--single{
        height:30px !important;
        padding:5px !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow{
        height:12px !important;
    }
    .select2 {
        width:91% !important;
    } 
    body.modal-open .select2-container--open {
        width:91% !important;
    }
    /* Professional Table Styles */
    .dataTables_scroll {
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        overflow: hidden;
    }
    .dataTables_scrollHead {
        background: #f8fafc !important;
    }
    .dataTables_scrollBody {
        border-top: 1px solid #e2e8f0;
    }
    /* Custom Scrollbar for Table */
    .dataTables_scrollBody::-webkit-scrollbar {
        height: 8px;
        width: 8px;
    }
    .dataTables_scrollBody::-webkit-scrollbar-track {
        background: #f1f5f9;
    }
    .dataTables_scrollBody::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 10px;
    }
    .dataTables_scrollBody::-webkit-scrollbar-thumb:hover {
        background: #94a3b8;
    }
    .dt-buttons {
        margin-bottom: 15px;
    }
    .dt-button {
        padding: 5px 15px !important;
        font-size: 12px !important;
        border-radius: 4px !important;
    }
</style>
@endsection

@section('breadcrumb-items')
    <li class="breadcrumb-item">Order Process</li>
    <li class="breadcrumb-item active">{{$process}}</li>
@endsection

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header pb-0">
                        <h5>{{$process}} Orders</h5>
                    </div>
                    <div class="card-body">
<style>
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
</style>
                        <div class="dt-controls-wrap px-2">
                            <div class="dt-controls-item">
                                <label>Show</label>
                                <select id="basic-2_value" class="form-control form-control-sm" style="width: auto;">
                                    <option value="50">50</option>
                                    <option value="250" selected>250</option>
                                    <option value="500">500</option>
                                    <option value="1000">1000</option>
                                </select>
                            </div>

                            @if($process == 'Schedule For Box / Cutting' || $process == 'Ready Bags List')
                            <div class="dt-controls-item">
                                <label>Type</label>
                                <ul class="nav nav-pills nav-primary" id="pills-tab" role="tablist">
                                    <li class="nav-item"><a class="nav-link active py-1 px-3 f-12" id="pills-all-tab" data-bs-toggle="pill" href="#pills-all" role="tab" onclick="set_sub_type('all')">All Orders</a></li>
                                    <li class="nav-item"><a class="nav-link py-1 px-3 f-12" id="pills-box-tab" data-bs-toggle="pill" href="#pills-box" role="tab" onclick="set_sub_type('Box')">For Box</a></li>
                                    <li class="nav-item"><a class="nav-link py-1 px-3 f-12" id="pills-cutting-tab" data-bs-toggle="pill" href="#pills-cutting" role="tab" onclick="set_sub_type('Cutting')">For Cutting</a></li>
                                </ul>
                                <input type="hidden" id="sub_type_filter" value="all">
                            </div>
                            @endif

                            <div class="dt-controls-item">
                                <label>Category</label>
                                <select id="order_category" class="form-select form-select-sm" onchange="get_datatable()" style="min-width: 150px;">
                                    <option value="all" {{ $category == 'all' ? 'selected' : '' }}>All Orders</option>
                                    <option value="roto" {{ $category == 'roto' ? 'selected' : '' }}>Roto Order</option>
                                    <option value="common" {{ $category == 'common' ? 'selected' : '' }}>Common Order</option>
                                </select>
                            </div>

                            <div class="dt-controls-item">
                                <label>Search</label>
                                <input type="search" id="basic-2_search" class="form-control form-control-sm" placeholder="Search...">
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

    <!-- Modals -->
    <div class="modal fade" id="edit_modal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" id="ajax_html"></div>
    </div>
    <div class="modal fade" id="job_card_modal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-xl" id="ajax_html2"></div>
    </div>

    <audio id="myAudio" controls class="d-none">
        <source src="{{ asset('audio/Beep.wav') }}" type="audio/wav">
    </audio>

@endsection

@section('script')
    <script>
        $(document).ready(function(){
            get_datatable();
        });

        $(document).on('click','.pages a',function(n){
            n.preventDefault();
            var page = $(this).attr('href').split("page=")[1];
            get_datatable(page);
        });

        // Debounce function
        function debounce(func, wait) {
            let timeout;
            return function(...args) {
                const context = this;
                clearTimeout(timeout);
                timeout = setTimeout(() => func.apply(context, args), wait);
            };
        }

        function set_sub_type(type) {
            $('#sub_type_filter').val(type);
            get_datatable();
        }

        function get_datatable(page){
            var $container = $('#get_datatable');
            if ($container.length) {
                // Aggressive scroll capturing to beat Bootstrap modal reset
                var wScroll = $(window).scrollTop();
                var pbScroll = $('.page-body').scrollTop();
                var pScroll = $('.page-wrapper').scrollTop();

                // Freeze the container height to prevent scroll collapsing
                var currentHeight = $container.height();
                $container.css('min-height', currentHeight + 'px');
                
                // If page not explicitly provided, try to grab the current active page
                if(!page) {
                    page = Number($(".pages").find('span[aria-current="page"] span').text()) || 1;
                }
                
                $container.html('<div class="loader-box"><div class="loader-37"></div></div>');
                var value = $('#basic-2_value').val();
                var search = $('#basic-2_search').val();
                var sub_type = $('#sub_type_filter').val();
                
                $.ajax({
                    url: '{{ route("job_card.process_datatable") }}',
                    data: { 
                        page: page, 
                        value: value, 
                        search: search, 
                        _token: "{{csrf_token() }}", 
                        process: "{{$process}}",
                        category: $('#order_category').val() || "{{$category}}",
                        sub_type: sub_type
                    },
                    type: 'GET',
                    success: function(data){
                        $container.html(data);
                        // Unfreeze container height
                        $container.css('min-height', '');
                        
                        $('#basic-test').DataTable({ 
                            dom: '{{ auth()->user()->role_as == "Admin" ? "Brt" : "rt" }}', 
                            "pageLength": -1, 
                            "ordering": true,
                            "info": false,
                            "searching": false,
                            "paging": false,
                            "scrollX": false,
                            "scrollCollapse": false,
                            "autoWidth": false,
                            "columnDefs": [
                                { "width": "200px", "targets": -1 } // Action column width
                            ],
                            "drawCallback": function(settings) {
                                var api = this.api();
                                setTimeout(function() {
                                    api.columns.adjust().fixedHeader.adjust();
                                }, 300);
                            }
                        });


                        $(window).on('resize', function() {
                            if ($.fn.DataTable.isDataTable('#basic-test')) {
                                $('#basic-test').DataTable().columns.adjust();
                            }
                        });

                        // Forcefully restore scroll state after everything (DataTable init, Modal closing) is done
                        setTimeout(function() {
                            if (wScroll > 0) $(window).scrollTop(wScroll);
                            if (pbScroll > 0) $('.page-body').scrollTop(pbScroll);
                            if (pScroll > 0) $('.page-wrapper').scrollTop(pScroll);
                        }, 300);
                    }
                });
            }
        }

        function update_single_row(id) {
            var value = $('#basic-2_value').val();
            var search = $('#basic-2_search').val();
            var sub_type = $('#sub_type_filter').val();
            
            $.ajax({
                url: '{{ route("job_card.process_datatable") }}',
                data: { 
                    id: id,
                    value: value, 
                    search: search, 
                    _token: "{{csrf_token() }}", 
                    process: "{{$process}}",
                    category: $('#order_category').val() || "{{$category}}",
                    sub_type: sub_type
                },
                type: 'GET',
                success: function(data){
                    var newRowHtml = $(data).find('#tr-' + id).prop('outerHTML');
                    if (newRowHtml) {
                        var $oldRow = $('#tr-' + id);
                        var oldIndex = $oldRow.find('td:first').text();
                        
                        var $newRow = $(newRowHtml);
                        $newRow.find('td:first').text(oldIndex);
                        
                        $oldRow.replaceWith($newRow);
                    } else {
                        $('#tr-' + id).fadeOut(300, function(){ $(this).remove(); });
                    }
                }
            });
        }

        $('#basic-2_search').on('keyup search', debounce(function() { get_datatable(); }, 500));
        $('#basic-2_value').on('change', function() { get_datatable(); });

        function view_modal(id){
            var url = "{{route('job_card.show',":id")}}";
            url = url.replace(':id',id);
            $('#ajax_html2').html('<div class="loader-box"><div class="loader-37"></div></div>');
            $.get(url,{}, function(data){
                $('#job_card_modal').modal('show');
                $('#ajax_html2').html(data);
            });
        }

        function manage_rolls(id, mode){
            var url = "{{route('job_card.common_roll_out_modal',":id")}}";
            url = url.replace(':id',id);
            $('#ajax_html2').html('<div class="loader-box"><div class="loader-37"></div></div>');
            $.get(url,{mode: mode}, function(data){
                $('#job_card_modal').modal('show');
                $('#ajax_html2').html(data);
            });
        }

        function edit_modal(id){
            var url = "{{route('job_card.edit_modal',":id")}}";
            url = url.replace(':id',id);
            $('#ajax_html2').html('<div class="loader-box"><div class="loader-37"></div></div>');
            $.get(url,{}, function(data){
                $('#job_card_modal').modal('show');
                $('#ajax_html2').html(data);
            });
        }

        function next_process(id, process, mode = ''){
            process = process.trim();
            if(process == 'Order List' || process == 'Cylinder Come'){
                swal({
                    title: "Move to Next Process?",
                    text: "Are you sure you want to move this order to the next stage?",
                    icon: "info",
                    buttons: true,
                })
                .then((willMove) => {
                    if (willMove) {
                        var url = "{{route('job_card.next_process',":id")}}";
                        url = url.replace(':id',id);
                        $.get(url, {process: process, mode: mode}, function(data){
                            if(data.result == 1){
                                 update_single_row(id);
                                 $.notify({ title: 'Success', message: data.message }, { type: 'success', });
                            }
                        });
                    }
                });
                return;
            }

            var url = "{{route('job_card.next_process',":id")}}";
            url = url.replace(':id',id);
            $.get(url, {process: process, mode: mode}, function(data){
                if(data.result == 1){
                     update_single_row(id);
                     $.notify({ title: 'Success', message: data.message }, { type: 'success', });
                }else{
                    if(process == 'Schedule For Printing' || process == 'Schedule For Lamination' || process == 'Schedule For Box / Cutting' || process == 'Packing Slip' || process == 'Account Pending'){
                         $('#job_card_modal').modal('show');
                          $('#ajax_html2').html(data);
                    }else{
                         $('#edit_modal').modal('show');
                          $('#ajax_html').html(data);
                    }
                }
            });
        }

        $(document).on('submit','form',function(event){
            event.preventDefault();
            var form = event.target;
            
            // Prevent multiple submissions
            if ($(form).data('submitting')) {
                return false;
            }
            
            var $submitBtn = $(form).find('button[type="submit"]');
            var form_data = new FormData(form);
            
            $(form).data('submitting', true);
            $submitBtn.prop('disabled', true).addClass('disabled');
            var originalBtnHtml = $submitBtn.html();
            $submitBtn.html('<i class="fa fa-spinner fa-spin me-2"></i> SAVING...');
            
            $.ajax({
                url: $(form).attr('action'),
                type: 'POST',
                data: form_data,
                processData: false,
                contentType: false,
                success: function(data){
                    if(data.result == 1){
                        $.notify({ title:'Success', message:data.message }, { type:'success' });
                        var formId = $(form).find('input[name="job_card_id"]').val();
                        if (!formId) { formId = $(form).find('input[name="id"]').val(); }
                        if (formId) update_single_row(formId);
                        else get_datatable();
                        $('#edit_modal').modal('hide');
                        $('#job_card_modal').modal('hide');
                    } else {
                        $.notify({ title:'Error', message:data.message }, { type:'danger' });
                        $submitBtn.prop('disabled', false).removeClass('disabled').html(originalBtnHtml);
                        $(form).data('submitting', false);
                    }
                },
                error: function() { 
                    $submitBtn.prop('disabled', false).removeClass('disabled').html(originalBtnHtml);
                    $(form).data('submitting', false);
                }
            });
        });
        function packing_material(id,process){
            process = process.trim();
            var url = "{{route('job_card.next_process',":id")}}";
            url = url.replace(':id',id);
            $('#ajax_html').html('<div class="loader-box"><div class="loader-37"></div></div>');
            $.get(url, {process: process}, function(data){
                $('#job_card_modal').modal('show');
                $('#ajax_html2').html(data);
            });
        }

        // $('#job_card_modal').on('hidden.bs.modal', function () {
        //     get_datatable();
        // });

        function delete_job_card(id){
            swal({
                title: "Are you sure?",
                text: "You won't be able to revert this!",
                icon: "warning",
                buttons: true,
                dangerMode: true,
            })
            .then((willDelete) => {
                if (willDelete) {
                    var url = "{{route('job_card.delete',":id")}}";
                    url = url.replace(':id',id);
                    $.get(url, function(data){
                        if(data.result == 1){
                            $('#tr-' + id).fadeOut(300, function(){ $(this).remove(); });
                            $.notify({ title:'Deleted', message:data.message}, { type:'danger', });
                        }
                    })
                }
            })
        }

        // ===================== HOLD / UNHOLD FUNCTIONS =====================
        function openHoldModal(jobCardId) {
            var url = "{{ route('job_card.hold_modal', ':id') }}".replace(':id', jobCardId);
            $('#ajax_html2').html('<div class="loader-box"><div class="loader-37"></div></div>');
            $('#job_card_modal').modal('show');
            $.get(url, function(data) {
                $('#ajax_html2').html(data);
            }).fail(function() {
                $('#ajax_html2').html('<div class="alert alert-danger m-3">Failed to load Hold modal. Please try again.</div>');
            });
        }

        function unholdOrder(jobCardId) {
            swal({
                title: "Release HOLD?",
                text: "Are you sure you want to release this order from HOLD? It will be available for next process again.",
                icon: "warning",
                buttons: { cancel: "Cancel", confirm: { text: "Yes, Release!", value: true, className: "btn-success" } },
                dangerMode: false,
            }).then((willUnhold) => {
                if (willUnhold) {
                    var url = "{{ route('job_card.unhold', ':id') }}".replace(':id', jobCardId);
                    $.get(url, function(data) {
                        if(data.result == 1) {
                            $.notify({ title: 'Released!', message: data.message }, { type: 'success', placement: { from: 'top', align: 'right' } });
                            update_single_row(jobCardId);
                        } else {
                            $.notify({ title: 'Error', message: data.message }, { type: 'danger' });
                        }
                    }).fail(function() {
                        $.notify({ title: 'Error', message: 'Request failed. Please try again.' }, { type: 'danger' });
                    });
                }
            });
        }

        function showHoldAlert(jobCardNo, reasonId) {
            swal({
                title: "⛔ Order On HOLD",
                text: "Order #" + jobCardNo + " is currently ON HOLD.\n\nNext process is blocked until this order is released.\n\nUse the green 🔓 button to unhold first.",
                icon: "error",
                button: { text: "Got it", className: "btn btn-danger" },
                dangerMode: true,
            });
        }
        // ==================================================================
    </script>
@endsection
