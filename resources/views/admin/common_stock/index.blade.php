@extends('layouts.admin.app')

@section('title', 'Common Stock ' . $in_out)

@section('css')
<style>
    .select2-container .select2-selection--single { height: 35px !important; padding: 5px !important; border-radius: 8px !important; }
    .select2-container--default .select2-selection--single .select2-selection__arrow { height: 20px !important; }
    .bg-light-success { background: rgba(22, 163, 74, 0.05) !important; border: 1px solid rgba(22, 163, 74, 0.2) !important; }
    .bg-light-danger { background: rgba(220, 38, 38, 0.05) !important; border: 1px solid rgba(220, 38, 38, 0.2) !important; }
    .badge-soft-success { background: rgba(22, 163, 74, 0.1); color: #16a34a; }
    .badge-soft-danger { background: rgba(220, 38, 38, 0.1); color: #dc2626; }
    .f-10 { font-size: 10px; }
    .f-11 { font-size: 11px; }
    .f-12 { font-size: 12px; }
    .fw-600 { font-weight: 600; }
    
    /* DataTable Buttons Alignment */
    .dt-buttons { float: left; margin-right: 15px; margin-bottom: 0 !important; display: flex; gap: 5px; }
    .dt-buttons .btn { padding: 5px 15px !important; font-size: 11px !important; border-radius: 6px !important; border: none !important; box-shadow: 0 2px 4px rgba(0,0,0,0.05); }
    .header-filter { min-width: 150px; }
    .dataTables_wrapper .dt-buttons { margin-top: -3px; }
    
    /* Form Alignment Fix */
    .stock-stat-box { 
        background: #fff; border: 1px solid #dee2e6; border-radius: 6px; 
        padding: 4px 10px; display: flex; flex-direction: column; align-items: center; min-width: 80px;
    }
    .stock-stat-label { font-size: 9px; text-transform: uppercase; color: #6c757d; font-weight: 700; line-height: 1; margin-bottom: 3px; }
    .stock-stat-value { font-size: 13px; font-weight: 700; color: #212529; line-height: 1; }
</style>
@endsection

@section('breadcrumb-title')
    <h3>Common Stock {{ $in_out }}</h3>
@endsection

@section('breadcrumb-items')
    <li class="breadcrumb-item">Common Product Stock</li>
    <li class="breadcrumb-item active">Stock {{ $in_out }}</li>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            @if(\App\Helpers\PermissionHelper::check('common_product_stock', 'add'))
            <div class="card mb-3 {{ $in_out == 'In' ? 'bg-light-success' : 'bg-light-danger' }}">
                <div class="card-body p-3">
                    <form action="{{ route('common_stock.store') }}" method="POST" id="stock_form" class="row g-3 align-items-center">
                        @csrf
                        <input type="hidden" name="in_out" value="{{ $in_out }}">
                        <input type="hidden" name="from" value="Manually">
                        
                        <div class="col-md-2">
                            <label class="form-label f-10 fw-bold text-uppercase text-muted mb-1">Date</label>
                            <input type="date" name="date" class="form-control form-control-sm" value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label f-10 fw-bold text-uppercase text-muted mb-1">Select Color</label>
                            <select name="color_id" id="color_id" class="form-select form-select-sm select2" onchange="get_current_stock()" required>
                                <option value="">Select Color</option>
                                @foreach($colors as $color)
                                    <option value="{{ $color->id }}">{{ $color->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label f-10 fw-bold text-uppercase text-muted mb-1">Select Size</label>
                            <select name="size_id" id="size_id" class="form-select form-select-sm select2" onchange="get_current_stock()" required>
                                <option value="">Select Size</option>
                                @foreach($sizes as $size)
                                    <option value="{{ $size->id }}">{{ $size->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div class="col-auto">
                            <div class="stock-stat-box mt-4">
                                <!-- <label class="form-label f-10 fw-bold text-uppercase text-muted mb-1">&nbsp</label> -->
                                <span class="stock-stat-label">Current</span>
                                <span class="stock-stat-value text-primary" id="display_current_stock">0.000</span>
                            </div>
                        </div>

                        <div class="col-md-2">
                            <label class="form-label f-10 fw-bold text-uppercase text-muted mb-1">Quantity (Kgs)</label>
                            <input type="number" step="0.001" name="quantity" id="quantity_input" class="form-control form-control-sm" placeholder="0.000" onkeyup="calculate_new_stock()" required>
                        </div>

                        <div class="col-auto">
                            <div class="stock-stat-box mt-4">
                                <span class="stock-stat-label">Result</span>
                                <span class="stock-stat-value" id="display_new_stock">0.000</span>
                            </div>
                        </div>

                        <div class="col-md-2">
                            <label class="form-label d-none d-md-block">&nbsp;</label>
                            <button type="submit" id="submit_btn" class="btn btn-{{ $in_out == 'In' ? 'success' : 'danger' }} btn-sm w-100 fw-bold">
                                <i class="fa fa-{{ $in_out == 'In' ? 'plus' : 'minus' }} me-1"></i> RECORD {{ strtoupper($in_out) }}
                            </button>
                        </div>

                        <div class="col-12 pt-0">
                            <input type="text" name="remarks" class="form-control form-control-sm" placeholder="Add remarks here (optional)...">
                        </div>
                    </form>
                </div>
            </div>
            @endif

            <!-- List Card -->
            <div class="card shadow-sm border-0">
                <div class="card-header p-3 border-bottom d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center gap-3">
                        <h6 class="mb-0 fw-bold">{{ $in_out }} Stock Records</h6>
                        <div id="btn_target"></div> <!-- Target for DT buttons -->
                    </div>
                    <div class="d-flex gap-2 align-items-center">
                        <select id="filter_color_id" class="form-select form-select-sm w-auto select2 header-filter" onchange="get_datatable()">
                            <option value="">All Colors</option>
                            @foreach($colors as $color)
                                <option value="{{ $color->id }}">{{ $color->name }}</option>
                            @endforeach
                        </select>
                        <select id="filter_size_id" class="form-select form-select-sm w-auto select2 header-filter" onchange="get_datatable()">
                            <option value="">All Sizes</option>
                            @foreach($sizes as $size)
                                <option value="{{ $size->id }}">{{ $size->name }}</option>
                            @endforeach
                        </select>
                        <input type="text" id="basic_search" class="form-control form-control-sm" placeholder="Search..." onkeyup="get_datatable()">
                        <select id="basic_limit" class="form-select form-select-sm w-auto" onchange="get_datatable()">
                            <option value="50">50</option>
                            <option value="100">100</option>
                            <option value="500">500</option>
                        </select>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div id="datatable_container">
                        <div class="loader-box"><div class="loader-37"></div></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="edit_modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" id="edit_modal_content"></div>
    </div>
</div>
@endsection

@section('script')
<script>
    $(document).ready(function() {
        get_datatable();
        $('.select2').select2();
    });

    function get_datatable(page = 1) {
        var $container = $('#datatable_container');
        $container.html('<div class="loader-box"><div class="loader-37"></div></div>');
        
        var search = $('#basic_search').val();
        var limit = $('#basic_limit').val();
        var color_id = $('#filter_color_id').val();
        var size_id = $('#filter_size_id').val();
        
        $.ajax({
            url: '{{ route("common_stock.datatable") }}',
            data: { 
                page: page, 
                value: limit, 
                search: search, 
                in_out: '{{ $in_out }}',
                color_id: color_id,
                size_id: size_id
            },
            type: 'GET',
            success: function(data) {
                $container.html(data);
                var table = $('#basic-test').DataTable({ 
                    dom: '{{ auth()->user()->role_as == "Admin" ? "Brt" : "rt" }}', 
                    "pageLength": -1, 
                    responsive: true, 
                    ordering: false,
                    @if(auth()->user()->role_as == 'Admin')
                    buttons: [
                        { extend: 'copy', className: 'btn-primary' },
                        { extend: 'csv', className: 'btn-info' },
                        { extend: 'excel', className: 'btn-success' },
                        { extend: 'pdf', className: 'btn-danger' },
                        { extend: 'print', className: 'btn-warning' }
                    ]
                    @endif
                });
                
                // Move buttons to custom location
                @if(auth()->user()->role_as == 'Admin')
                $('#btn_target').empty();
                table.buttons().container().appendTo('#btn_target');
                @endif
                
                feather.replace();
            }
        });
    }

    function printReport() {
        var search = $('#basic_search').val();
        var url = '{{ route("common_stock.report") }}?type={{ $in_out }}&search=' + encodeURIComponent(search);
        window.open(url, '_blank');
    }

    $(document).on('click', '.pagination a', function(e) {
        e.preventDefault();
        var page = $(this).attr('href').split('page=')[1];
        get_datatable(page);
    });

    $(document).on('submit', '#stock_form', function(e) {
        e.preventDefault();
        var $btn = $('#submit_btn');
        var originalHtml = $btn.html();
        $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Saving...');
        
        $.ajax({
            url: $(this).attr('action'),
            method: 'POST',
            data: $(this).serialize(),
            success: function(res) {
                if(res.result == 1) {
                    $.notify({ title: 'Success', message: res.message }, { type: 'success' });
                    $('#stock_form')[0].reset();
                    $('#color_id, #size_id').val('').trigger('change');
                    $('#display_current_stock, #display_new_stock').text('0.000');
                    get_datatable();
                } else {
                    $.notify({ title: 'Error', message: res.message }, { type: 'danger' });
                }
                $btn.prop('disabled', false).html(originalHtml);
            }
        });
    });

    function get_current_stock() {
        var colorId = $('#color_id').val();
        var sizeId = $('#size_id').val();
        if(!colorId || !sizeId) return;

        $.get('{{ route("common_stock.get_current_stock") }}', { color_id: colorId, size_id: sizeId }, function(res) {
            $('#display_current_stock').text(res.current_stock.toFixed(3));
            calculate_new_stock();
        });
    }

    function calculate_new_stock() {
        var current = parseFloat($('#display_current_stock').text()) || 0;
        var input = parseFloat($('#quantity_input').val()) || 0;
        var in_out = '{{ $in_out }}';
        
        var result = (in_out == 'In') ? (current + input) : (current - input);
        $('#display_new_stock').text(result.toFixed(3));
        
        if(in_out == 'Out' && result < 0) {
            $('#display_new_stock').addClass('text-danger').removeClass('text-dark');
        } else {
            $('#display_new_stock').addClass('text-dark').removeClass('text-danger');
        }
    }

    function edit_modal(id) {
        $('#edit_modal_content').html('<div class="loader-box"><div class="loader-37"></div></div>');
        $('#edit_modal').modal('show');
        $.get('{{ route("common_stock.edit_modal", ":id") }}'.replace(':id', id), function(data) {
            $('#edit_modal_content').html(data);
            $('.select2-modal').select2({ dropdownParent: $('#edit_modal') });
        });
    }

    function delete_stock(id) {
        swal({
            title: "Are you sure?",
            text: "You won't be able to revert this!",
            icon: "warning",
            buttons: true,
            dangerMode: true,
        })
        .then((willDelete) => {
            if (willDelete) {
                $.get('{{ route("common_stock.delete", ":id") }}'.replace(':id', id), function(res) {
                    if(res.result == 1) {
                        $.notify({ title: 'Deleted', message: res.message }, { type: 'success' });
                        get_datatable();
                    } else {
                        $.notify({ title: 'Error', message: res.message }, { type: 'danger' });
                    }
                });
            }
        });
    }
</script>
@endsection
