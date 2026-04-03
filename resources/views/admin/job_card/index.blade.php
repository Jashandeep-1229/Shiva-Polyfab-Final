@extends('layouts.admin.app')

@section('title')
Job Card {{request()->type ?? 'new' }}
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
    .typeahead-results {
        position: absolute;
        z-index: 1000;
        width: 100%;
        background: white;
        border: 1px solid #ddd;
        border-radius: 4px;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        display: none;
        max-height: 200px;
        overflow-y: auto;
    }
    .typeahead-item {
        padding: 8px 12px;
        cursor: pointer;
        border-bottom: 1px solid #eee;
        color: #333 !important; /* Force visible text color */
        font-size: 13px;
    }
    .typeahead-item:hover {
        background-color: #f8f9fa;
        color: var(--theme-deafult) !important;
    }
</style>
@endsection

@section('breadcrumb-items')
    <li class="breadcrumb-item">Job Card {{request()->type ?? 'new' }}</li>
@endsection

@section('content')
    <div class="container-fluid">
        <!-- All Client Table Start -->
        <div class="row">
            <div class="col-12">
                @php
                    $menu_key = ($type ?? 'all') == 'Common' ? 'common_orders' : 'roto_orders';
                @endphp
                @if(request()->type == 'Common' && PermissionHelper::check('common_orders', 'add'))
                    <div class="mb-3">
                        <a href="{{ route('common_order.create') }}" class="btn btn-primary btn-sm"><i class="fa fa-plus-circle me-1"></i> Create Common Order (Matrix)</a>
                    </div>
                @endif
                @if((request()->type ?? 'all') != 'all' && (request()->type ?? 'all') != 'pending' && (request()->type ?? 'all') != 'Common' && (request()->type ?? 'all') != 'Completed' && PermissionHelper::check($menu_key, 'add'))
                <div class="card bg-light-{{(request()->type ?? 'new') == 'new' ? 'success' : 'warning'}}"  id="add_type">
                    <form action="{{route('job_card.store')}}" method="POST" id="" class="modal-content" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="job_type" value="{{request()->type ?? 'new'}}">
                        <input type="hidden" name="id" value="0">
                        <input type="hidden" name="old_data_img_path" id="old_data_img_path">
                        <div class="card-body row p-3">
                            <div class="col-md-3 mb-2 ">
                                <div class="input-group input-group-sm position-relative">
                                    <input type="text" name="name_of_job" id="name_of_job"   placeholder="Name Of Job" oninput="this.value = this.value.toUpperCase()" class="form-control form-control-sm" autocomplete="off" required>
                                    <span class="input-group-text pointer" id="search_old_data" title="Search Old Data"><i class="fa fa-search"></i></span>
                                    <div id="typeahead_results" class="typeahead-results" style="top: 32px;"></div>
                                </div>
                            </div>
                            <div class="col-md-2 col-6 mb-2">
                                  <div class="input-group input-group-sm">
                                    <select name="bopp_id" id="bopp_id" class="form-select form-control-sm" aria-label="Example select with button addon" required>
                                        <option selected="">Select Bopp Used (mm)</option>
                                    </select>
                                    @if(auth()->user()->role_as == 'Admin')
                                    <span class="input-group-text" onclick="add_bopp()"><i class="fa fa-plus"></i></span>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-2 col-6 mb-2">
                                  <div class="input-group input-group-sm">
                                    <select name="fabric_id" id="fabric_id" class="form-select form-control-sm" aria-label="Example select with button addon" required>
                                        <option selected="">Select Fabric Used (inch/GSM)</option>
                                    </select>
                                    @if(auth()->user()->role_as == 'Admin')
                                    <span class="input-group-text" onclick="add_fabric()"><i class="fa fa-plus"></i></span>
                                    @endif
                                </div>
                            </div>
                             <div class="col-md-2 col-6 mb-2">
                                  <div class="input-group input-group-sm">
                                    <select name="no_of_pieces" id="no_of_pieces" class="form-select form-control-sm" aria-label="Example select with button addon" required>
                                        <option selected="">No Of Pieces</option>
                                        <option value="5000">5000</option>
                                        <option value="7000">7000</option>
                                        <option value="10000">10000</option>
                                        <option value="12000">12000</option>
                                        <option value="15000">15000</option>
                                        <option value="20000">20000</option>
                                        <option value="25000">25000</option>
                                        <option value="30000">30000</option>
                                        <option value="50000">50000</option>
                                        
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3 col-6 mb-2">
                                <div class="input-group input-group-sm">
                                    <select name="loop_color" id="loop_color" class="form-select form-control-sm" required>
                                        <option value="">Select Loop Color</option>
                                    </select>
                                    @if(auth()->user()->role_as == 'Admin')
                                    <span class="input-group-text" onclick="add_loop_color()"><i class="fa fa-plus"></i></span>
                                    @endif
                                </div>
                            </div>
                             <div class="col-md-3 mb-2">
                                <div class="input-group input-group-sm">
                                <span class="input-group-text">Send For</span>
                                <select name="order_send_for" id="order_send_for" class="form-select form-control form-control-sm" aria-label="Example select with button addon" required>
                                    <option value="Cutting">Cutting</option>
                                    <option value="Box">Box</option>
                                </select>
                               
                                </div>  
                            </div>
                             <div class="col-md-3 mb-2">
                                <div class="input-group input-group-sm">
                                <span class="input-group-text">Dispatch Date</span>
                                <input type="date" name="dispatch_date" id="dispatch_date"   placeholder="Dispatch Date"  class="form-control form-control-sm" required>
                                </div>  
                            </div>
                            @if((request()->type ?? 'new') == 'new')
                            <div class="col-md-3 mb-2">
                                  <div class="input-group input-group-sm">
                                    <select name="cylinder_given_id" id="cylinder_given_id" class="form-select form-control-sm" aria-label="Example select with button addon" required>
                                        <option selected="">Cylinder Given To </option>
                                    </select>
                                    @if(auth()->user()->role_as == 'Admin')
                                    <span class="input-group-text" onclick="add_cylinder_given_to()"><i class="fa fa-plus"></i></span>
                                    @endif
                                </div>
                            </div>
                            @endif
                             <div class="col-md-3 mb-2">
                                <div class="input-group input-group-sm">
                                    <input type="file" name="file_upload" id="file_upload" class="form-control form-control-sm">
                                    <span class="input-group-text">Upload File</span>
                                </div>
                            </div>
                            <div class="col-md-3 mb-2">
                                <div class="input-group input-group-sm">
                                    <select name="select_role" id="select_role" onchange="get_customer_agent(1)" class="form-select form-control-sm" aria-label="Example select with button addon" required>
                                        <option selected value="Customer">Customer</option>
                                        <option value="Agent">Agent</option>
                                    </select>
                                    <span class="input-group-text">Role</span>
                                </div>
                            </div>
                            <div class="col-md-3 mb-2">
                                <div class="input-group input-group-sm">
                                    <select name="customer_agent_id" id="customer_agent_id" class="js-example-basic-single" aria-label="Example select with button addon" required>
                                        <option selected value="">Select Customer Agent</option>
                                    </select>
                                    <span class="input-group-text" onclick="add_customer_agent()"><i class="fa fa-plus"></i></span>
                                </div>
                            </div>
                             <div class="col-md-3 mb-2">
                                <div class="input-group input-group-sm">
                                    <select name="sale_executive_id" id="sale_executive_id" class="js-example-basic-single" aria-label="Example select with button addon" required>
                                        <option selected value="">Select Sale Executive</option>
                                    </select>
                                </div>
                            </div>
                             <div class="col-md-10 mb-2">
                                <textarea name="remarks" rows="3" id="remarks" class="form-control form-control-sm" placeholder="Additional Note"></textarea>
                            </div>
                            <div id="img_preview_col" class="col-md-2 mb-2 d-none text-center">
                                <a href="#" target="_blank" id="old_data_img_link">
                                    <img id="old_data_img_preview" src="" alt="Job Image" style="height: 80px; width: 100%; object-fit: contain; border-radius: 4px; border: 2px solid #7366ff; background: #fff; box-shadow: 0 4px 8px rgba(0,0,0,0.1);">
                                </a>
                                <small class="text-muted d-block mt-1">Old Design</small>
                            </div>
                           
                    
                            <div class="col-md-12 text-center">
                               <button type="submit" id="add_data" class="btn btn-primary btn-sm w-25" >Add +</button>
                            </div>
                           
                        </div>
                    </form>
                </div>
                @endif
                <div class="card">
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
                                font-weight: 800;
                                font-size: 13px;
                                text-transform: uppercase;
                                color: #475569;
                                white-space: nowrap;
                            }
                            .filter-btn {
                                transition: all 0.2s;
                            }
                            .filter-btn.active {
                                background-color: #7366ff;
                                color: white;
                                shadow: 0 4px 12px rgba(115, 102, 255, 0.3);
                            }
                        </style>

                        <div class="dt-controls-wrap px-2">
                            <div class="dt-controls-item">
                                <label>Show</label>
                                <select name="basic-2_value" id="basic-2_value" class="form-control form-control-sm" style="width: auto;">
                                    <option value="50">50</option>
                                    <option value="250" selected>250</option>
                                    <option value="500">500</option>
                                    <option value="1000">1000</option>
                                </select>
                            </div>

                            <div class="dt-controls-item">
                                <label>Process Status</label>
                                <select id="process_status_filter" class="form-control form-control-sm" style="min-width: 180px;" onchange="get_datatable()">
                                    <option value="">All Statuses</option>
                                    <option value="Order List">Order List</option>
                                    <option value="Cylinder Come">Cylinder Come</option>
                                    <option value="Schedule For Printing">Schedule For Printing</option>
                                    <option value="Printed Bopp List">Printed Bopp List</option>
                                    <option value="Schedule For Lamination">Schedule For Lamination</option>
                                    <option value="Laminated Rolls">Laminated Rolls</option>
                                    <option value="Schedule For Box / Cutting">Schedule For Box / Cutting</option>
                                    <option value="Ready Bags List">Ready Bags List</option>
                                    <option value="Completed">Completed Job Card</option>
                                </select>
                            </div>

                            @if(auth()->user()->role_as == 'Admin')
                            <div class="dt-controls-item">
                                <label>Executive</label>
                                <select id="executive_filter" class="form-control form-control-sm" style="min-width: 150px;" onchange="get_datatable()">
                                    <option value="">All Executives</option>
                                    @foreach($executives as $ex)
                                        <option value="{{ $ex->id }}">{{ $ex->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            @else
                            <input type="hidden" id="executive_filter" value="{{ request('executive_id') }}">
                            @endif

                            <div class="dt-controls-item">
                                <label>From</label>
                                <input type="date" id="from_date_filter" class="form-control form-control-sm" onchange="get_datatable()">
                            </div>

                            <div class="dt-controls-item">
                                <label>To</label>
                                <input type="date" id="to_date_filter" class="form-control form-control-sm" onchange="get_datatable()">
                            </div>

                            <div class="dt-controls-item">
                                <label>Search</label>
                                <input type="search" id="basic-2_search" class="form-control form-control-sm" placeholder="Search orders...">
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


    <div class="modal fade" id="edit_modal"  aria-labelledby="mySmallModalLabel" aria-hidden="true">
        <div class="modal-dialog" id="ajax_html">
            
        </div>
    </div>
     <div class="modal fade" id="job_card_modal"  aria-labelledby="mySmallModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl" id="ajax_html2">
            
        </div>
    </div>

    <audio id="myAudio" controls class="d-none">
        <source src="{{ asset('audio/Beep.wav') }}" type="audio/wav">
    </audio>

@endsection
@section('script')
    @if((request()->type ?? 'all') == 'new')
    <script>
        $(document).ready(function(){
            get_cylinder_given_to(1);
        });
        function get_cylinder_given_to(value){
            $.ajax({
                url: "{{ route('cylinder_agent.list') }}",
                type: "GET",
                success: function(response) {
                    $("#cylinder_given_id").html(
                        '<option value="">Cylinder Given To</option>' + response
                    );
                    if(value == 1){
                        $("#cylinder_given_id option").prop("selected", false);
                    }
                }
            });
        }
        function add_cylinder_given_to(){
            var id=0;
            var url = "{{route('cylinder_agent.edit_modal',":id")}}";
                url = url.replace(':id',id);
                $('#ajax_html').html('<div class="loader-box"><div class="loader-37"></div></div>');
                $.get(url,{}, function(data){
                    $('#edit_modal').modal('show');
                    $('#ajax_html').html(data);
                });
        }
        </script>
 @endif
 
    <script>
         $(document).ready(function(){
            // Pre-fill filters from URL
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.get('executive_id')) $('#executive_filter').val(urlParams.get('executive_id'));
            if (urlParams.get('from_date')) $('#from_date_filter').val(urlParams.get('from_date'));
            if (urlParams.get('to_date')) $('#to_date_filter').val(urlParams.get('to_date'));

            get_datatable();
            get_bopp(1);
            get_fabric(1);
            get_loop_color(1);
            get_customer_agent(1);
            get_sale_executive(1);
            $("#name_of_job").focus();
        });
        function get_bopp(value){
            $.ajax({
                url: "{{ route('bopp.list') }}",
                type: "GET",
                success: function(response) {
                     $("#bopp_id").html(
                        '<option value="">Select Bopp Used (mm)</option>' + response
                    );
                    if(value == 1){
                        $("#bopp_id option").prop("selected", false);
                    }
                }
            });
        }
        function get_loop_color(value){
            $.ajax({
                url: "{{ route('loop.list') }}",
                type: "GET",
                success: function(response) {
                     $("#loop_color").html(
                        '<option value="">Select Loop Color</option>' + response
                    );
                    if(value == 1){
                        $("#loop_color option").prop("selected", false);
                    }
                }
            });
        }
        function add_loop_color(){
            var id=0;
            var url = "{{route('loop.edit_modal',":id")}}";
                url = url.replace(':id',id);
                $('#ajax_html').html('<div class="loader-box"><div class="loader-37"></div></div>');
                $.get(url,{}, function(data){
                    $('#edit_modal').modal('show');
                    $('#ajax_html').html(data);
                });
        }
        function add_customer_agent(){
            var id = 0;
            var url = "{{route('agent_customer.edit_modal',":id")}}";
            var role = $("#select_role").val();
            url = url.replace(':id',id);
            $('#ajax_html').html('<div class="loader-box"><div class="loader-37"></div></div>');
            $.get(url,{role:role}, function(data){
                $('#edit_modal').modal('show');
                $('#ajax_html').html(data);
            });
        } 
        function add_sale_executive(){
            var id = 0;
            var url = "{{route('sale_executive.edit_modal',":id")}}";
            var role = $("#select_role").val();
            url = url.replace(':id',id);
            $('#ajax_html').html('<div class="loader-box"><div class="loader-37"></div></div>');
            $.get(url,{role:role}, function(data){
                $('#edit_modal').modal('show');
                $('#ajax_html').html(data);
            });
        }
        function get_sale_executive(value){
            $.ajax({
                url: "{{ route('sale_executive.list') }}",
                type: "GET",
                data: { role: ['Sale Executive', 'Senior Sale Executive'] },
                success: function(response) {
                     $("#sale_executive_id").html(
                        '<option value="">Select Sale Executive</option>' + response
                    ).select2();
                    if(value == 1){
                        $("#sale_executive_id option").prop("selected", false);
                    }
                }
            });
        }
        function get_customer_agent(value){
            var role = $("#select_role").val();
            $.ajax({
                url: "{{ route('agent_customer.list') }}",
                type: "GET",
                data: { role: role },
                success: function(response) {
                     $("#customer_agent_id").html(
                        '<option value="">Select Customer Agent</option>' + response
                    ).select2();
                    if(value == 1){
                        $("#customer_agent_id option").prop("selected", false);
                    }
                }
            });
        }
        function get_agent_customer_list(value){
            $.ajax({
                url: "{{ route('agent_customer.list') }}",
                type: "GET",
                data: { role: value },
                success: function(response) {
                     $("#modal_customer_agent_id").html(
                        '<option value="">Select Customer Agent</option>' + response
                    ).select2();
                    $("#modal_customer_agent_id option").prop("selected", false);
                }
            });
        }

        $(document).on('change', '#customer_agent_id', function() {
            var selectedOption = $(this).find(':selected');
            var saleExecutiveId = selectedOption.data('sale-executive-id');
            if (saleExecutiveId) {
                $('#sale_executive_id').val(saleExecutiveId).trigger('change');
            }
        });

        $(document).on('change', '#modal_customer_agent_id', function() {
            var selectedOption = $(this).find(':selected');
            var saleExecutiveId = selectedOption.data('sale-executive-id');
            if (saleExecutiveId) {
                $('#sale_executive_id').val(saleExecutiveId).trigger('change');
            }
        });
            
        function add_bopp(){
            var id = 0;
            var url = "{{route('bopp.edit_modal',":id")}}";
                url = url.replace(':id',id);
                $('#ajax_html').html('<div class="loader-box"><div class="loader-37"></div></div>');
                $.get(url,{}, function(data){
                    $('#edit_modal').modal('show');
                    $('#ajax_html').html(data);
                });
        }
        function get_fabric(value){
            $.ajax({
                url: "{{ route('fabric.list') }}",
                type: "GET",
                success: function(response) {
                     $("#fabric_id").html(
                        '<option value="">Select Fabric Used (inch/GSM)</option>' + response
                    );
                    if(value == 1){
                        $("#fabric_id option").prop("selected", false);
                    }
                }
            });
        }
        function add_fabric(){
            var id = 0;
            var url = "{{route('fabric.edit_modal',":id")}}";
                url = url.replace(':id',id);
                $('#ajax_html').html('<div class="loader-box"><div class="loader-37"></div></div>');
                $.get(url,{}, function(data){
                    $('#edit_modal').modal('show');
                    $('#ajax_html').html(data);
                });
        }

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
                var category = $('.filter-btn.active').text().toLowerCase();
                var process_status = $('#process_status_filter').val() || '';
                var executive_id = $('#executive_filter').val() || '';
                var from_date = $('#from_date_filter').val() || '';
                var to_date = $('#to_date_filter').val() || '';

                $.ajax({
                    url: '{{ route("job_card.datatable") }}',
                    data: { 
                        page: page, 
                        value: value, 
                        search: search, 
                        _token: "{{csrf_token() }}", 
                        type: "{{request()->type}}", 
                        category: category,
                        process_status: process_status,
                        executive_id: executive_id,
                        from_date: from_date,
                        to_date: to_date
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
                            "scrollX": false, // Use CSS scrolling as done in process views
                            "autoWidth": false
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
            var category = $('.filter-btn.active').text().toLowerCase();
            var process_status = $('#process_status_filter').val() || '';
            var executive_id = $('#executive_filter').val() || '';
            var from_date = $('#from_date_filter').val() || '';
            var to_date = $('#to_date_filter').val() || '';

            $.ajax({
                url: '{{ route("job_card.datatable") }}',
                data: { 
                    id: id,
                    value: value, 
                    search: search, 
                    _token: "{{csrf_token() }}", 
                    type: "{{request()->type}}", 
                    category: category,
                    process_status: process_status,
                    executive_id: executive_id,
                    from_date: from_date,
                    to_date: to_date
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

        $(document).on('change', '#process_status_filter', function() {
            get_datatable();
        });

        $('#basic-2_search').on('keyup search', debounce(function() { get_datatable(); }, 500));
        $('#basic-2_value').on('change', function() { get_datatable(); });

        function edit_modal(id,key_value){
            var url = "{{route('job_card.edit_modal',":id")}}";
            url = url.replace(':id',id);
            $('#ajax_html2').html('<div class="loader-box"><div class="loader-37"></div></div>');
            $.get(url,{key_value:key_value}, function(data){
                $('#ajax_html2').html(data);
                $('.js-example-basic-single').select2();
            });
        }

        $(document).on('submit','form',function(event){
            event.preventDefault();
            var form = event.target;
            var form_data = new FormData(form);
            var $submitBtn = $('form button[type="submit"]');
            $submitBtn.addClass('disabled');
            $.ajax({
                url: $(event.target).attr('action'),
                type: 'POST',
                data: form_data,
                processData: false,
                contentType: false,
                success: function(data){
                    if(data.result == 1){
                        $.notify({ title:'Success', message:data.message }, { type:'success', });

                        // Master list refreshes
                        if(data.from == 'Cylinder Agent') get_cylinder_given_to(-1);
                        if(data.from == 'Bopp Master') get_bopp(-1);
                        if(data.from == 'Loop Master') get_loop_color(-1);
                        if(data.from == 'Fabric Master') get_fabric(-1);
                        if(data.from == 'Agent / Customer Master') get_customer_agent(-1);
                        if(data.from == 'Sale Executive Master') get_sale_executive(-1);

                        // Job Card specific logic
                        if(data.from == 'Job Card'){
                            var formId = $(form).find('input[name="id"]').val();
                            if (formId) {
                                // It's an update, just update the single row
                                update_single_row(formId);
                            } else {
                                // It's a creation, reload table
                                var page = Number($(".pages").find('span[aria-current="page"] span').text());
                                $('#name_of_job').val('');
                                $('#sale_executive_id').prop('selectedIndex', 0).trigger('change');
                                $('#customer_agent_id').prop('selectedIndex', 0).trigger('change');
                                $('#fabric_id').prop('selectedIndex', 0).trigger('change');
                                $('#bopp_id').prop('selectedIndex', 0).trigger('change');
                                $('#no_of_pieces').prop('selectedIndex', 0).trigger('change');
                                $('#cylinder_given_id').prop('selectedIndex', 0).trigger('change');
                                $('#loop_color').prop('selectedIndex', 0).trigger('change');
                                $('#dispatch_date').val('');
                                $('#remarks').val('');
                                $('#file_upload').val('');
                                get_datatable(page);
                                $("#name_of_job").focus();
                            }
                        }

                        // Clean up modals and buttons
                        $submitBtn.html('Save').removeClass('disabled');
                        $('#edit_modal').modal('hide');
                        $('#job_card_modal').modal('hide');
                    } else {
                        // Handle errors or validation messages (result != 1)
                        $.notify({ title:'Error', message:data.message }, { type:'danger', });
                        $submitBtn.html('Save').removeClass('disabled');
                    }
                },
                error: function() { $submitBtn.html('Save').removeClass('disabled'); }
            });
        });

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
                        } else {
                            swal("Error", data.message, "error");
                        }
                    })
                }
            })
        }
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

        function toggle_hold(id) {
            var url = "{{route('job_card.change_hold_status', ':id')}}";
            url = url.replace(':id', id);
            $.get(url, function(data) {
                if(data.result == 1) {
                    $.notify({ title:'Status Updated', message:data.message }, { type:'info', delay: 1000 });
                    update_single_row(id);
                }
            });
        }
        function next_process(id,process){
            process = process.trim();
            var currentPage = Number($(".pages").find('span[aria-current="page"] span').text()) || 1;
            
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
                        $.get(url, {process: process}, function(data){
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
            $.get(url, {process: process}, function(data){
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
        
        function updateCategory(cat) {
            $('.filter-btn').removeClass('active');
            $(`.filter-btn:contains(${cat.charAt(0).toUpperCase() + cat.slice(1)})`).addClass('active');
            get_datatable();
        }

        // ===== HOLD / UNHOLD FUNCTIONS =====
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
                            $.notify({ title: 'Success', message: data.message }, { type: 'success' });
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
        // ===================================
    </script>


    <script>
        $(document).ready(function() {
            var $input = $('#name_of_job');
            var $results = $('#typeahead_results');
            var isRepeat = "{{ (request()->type ?? $type ?? '') }}" == 'repeat';

            function performSearch() {
                var search = $input.val();
                if (search.length < 2) { // Minimum 2 characters for speed
                    $results.hide();
                    return;
                }

                $.get("{{ route('old_data.search') }}", { search: search }, function(data) {
                    console.log('Search results:', data);
                    if (data.length > 0) {
                        var html = '';
                        data.forEach(function(item) {
                            html += '<div class="typeahead-item" data-id="' + item.id + '">' + item.name_of_job + '</div>';
                        });
                        $results.html(html).show();
                    } else {
                        $results.html('<div class="typeahead-item">No old data found</div>').show();
                        setTimeout(function() { $results.hide(); }, 2000);
                    }
                });
            }

            if (isRepeat) {
                // Auto-search on keyup
                $input.on('keyup', debounce(function() {
                    performSearch();
                }, 500));
                
                // Manual search button
                $('#search_old_data').on('click', function() {
                    performSearch();
                });
            } else {
                // Hide search icon if not repeat order
                $('#search_old_data').hide();
            }

            $(document).on('click', '.typeahead-item', function() {
                var id = $(this).data('id');
                if (!id) return;
                
                var name = $(this).text();
                $input.val(name);
                $results.hide();

                $.notify({ title: 'Loading', message: 'Fetching historical details...' }, { type: 'info', delay: 1000 });

                // Fetch full details to pre-fill
                $.get("{{ url('admin/old_data/get_details') }}/" + id, function(response) {
                    if (response.result == 1) {
                        var d = response.data;
                        console.log('Filling data:', d);
                        
                        // Prefill everything
                        if (d.bopp_id) $('#bopp_id').val(d.bopp_id).trigger('change');
                        if (d.fabric_id) $('#fabric_id').val(d.fabric_id).trigger('change');
                        // Prefill Loop Color
                        if (d.loop && d.loop.name) {
                            $('#loop_color').val(d.loop.name).trigger('change');
                        }
                        
                        if (d.pieces) {
                            if ($("#no_of_pieces option[value='" + d.pieces + "']").length == 0) {
                                $('#no_of_pieces').append('<option value="' + d.pieces + '">' + d.pieces + '</option>');
                            }
                            $('#no_of_pieces').val(d.pieces).trigger('change');
                        }

                        if (d.send_for) {
                            var sf = d.send_for.toLowerCase();
                            if (sf.includes('box')) $('#order_send_for').val('Box').trigger('change');
                            else if (sf.includes('cutting')) $('#order_send_for').val('Cutting').trigger('change');
                        }

                        if (d.remarks) $('#remarks').val(d.remarks);

                        // Handle image preview
                        if (d.image) {
                            var imgPath = "{{ asset('') }}" + d.image;
                            $('#old_data_img_preview').attr('src', imgPath);
                            $('#old_data_img_link').attr('href', imgPath);
                            $('#old_data_img_path').val(d.image);
                            $('#img_preview_col').removeClass('d-none');
                        } else {
                            $('#img_preview_col').addClass('d-none');
                            $('#old_data_img_path').val('');
                        }

                        $.notify({ title: 'Success', message: 'Details pre-filled successfully' }, { type: 'success', delay: 2000 });
                    }
                });
            });

            // Close results on click outside
            $(document).on('click', function(e) {
                if (!$(e.target).closest('.input-group').length) {
                    $results.hide();
                }
            });
        });
    </script>
@endsection
