@extends('layouts.admin.app')

@section('title', 'Remaining Stock List')

@section('breadcrumb-title')
    <h3>Remaining Stock List</h3>
@endsection
@section('css')
<style>
    .select2-container .select2-selection--single { height: 35px !important; padding: 5px !important; border-radius: 8px !important; }
    .select2-container--default .select2-selection--single .select2-selection__arrow { height: 20px !important; }
</style>
@endsection


@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-sm-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom py-3">
                    <div class="row g-3 align-items-center">
                        <div class="col-md-3">
                            <label class="form-label f-12 fw-bold text-muted uppercase">Color</label>
                            <select id="color_id" class="form-select form-select-sm js-example-basic-single" onchange="get_datatable()">
                                <option value="">All Colors</option>
                                @foreach($colors as $color)
                                    <option value="{{ $color->id }}">{{ $color->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label f-12 fw-bold text-muted uppercase">Size</label>
                            <select id="size_id" class="form-select form-select-sm js-example-basic-single" onchange="get_datatable()">
                                <option value="">All Sizes</option>
                                @foreach($sizes as $size)
                                    <option value="{{ $size->id }}">{{ $size->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label f-12 fw-bold text-muted uppercase">Stock Status</label>
                            <select id="balance_type" class="form-select form-select-sm" onchange="get_datatable()">
                                <option value="active" selected>Show Non-Zero Only</option>
                                <option value="all">Show All Records</option>
                            </select>
                        </div>
                        <div class="col-md-3 text-end d-flex gap-2 justify-content-end">
                            @if(auth()->user()->role_as == 'Admin')
                            <button type="button" onclick="exportExcel()" class="btn btn-outline-success btn-sm px-3">
                                <i class="fa fa-file-excel-o me-1"></i> Excel
                            </button>
                            @endif
                            <button type="button" onclick="printReport()" class="btn btn-outline-danger btn-sm px-3">
                                <i class="fa fa-file-pdf-o me-1"></i> PDF
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row mb-3 align-items-center">
                        <div class="col-sm-12 col-md-6">
                            <div class="dataTables_length d-flex align-items-center gap-2">
                                <label class="mb-0">Show</label>
                                <select id="basic-2_value" class="form-select form-select-sm w-auto" onchange="get_datatable()">
                                    <option value="50" selected>50</option>
                                    <option value="250">250</option>
                                    <option value="500">500</option>
                                </select>
                                <label class="mb-0">entries</label>
                            </div>
                        </div>
                        <div class="col-sm-12 col-md-6 text-md-end">
                            <div class="dataTables_filter d-inline-block">
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0"><i class="fa fa-search text-muted f-12"></i></span>
                                    <input type="search" id="basic-2_search" class="form-control form-control-sm border-start-0" placeholder="Search by color or size...">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div id="get_datatable">
                        <div class="loader-box text-center py-5">
                            <div class="loader-37"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- History Modal -->
<div class="modal fade" id="historyModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" id="history_modal_content"></div>
    </div>
</div>
@endsection

@section('script')
<script>
    $(document).ready(function(){
        $('.js-example-basic-single').select2();
        get_datatable();
    });

    $(document).on('click', '.pages a', function(e){
        e.preventDefault();
        var page = $(this).attr('href').split('page=')[1];
        get_datatable(page);
    });

    function get_datatable(page = 1) {
        var $container = $('#get_datatable');
        $container.html('<div class="loader-box text-center py-5"><div class="loader-37"></div></div>');

        var value = $('#basic-2_value').val();
        var search = $('#basic-2_search').val();
        var color_id = $('#color_id').val();
        var size_id = $('#size_id').val();
        var balance_type = $('#balance_type').val();

        $.ajax({
            url: '{{ route("common_stock.remaining_list_datatable") }}',
            data: { 
                page: page, 
                value: value, 
                search: search, 
                color_id: color_id, 
                size_id: size_id,
                balance_type: balance_type
            },
            type: 'GET',
            success: function(data) {
                $container.html(data);
                feather.replace();
            }
        });
    }

    function debounce(func, wait) {
        let timeout;
        return function(...args) {
            clearTimeout(timeout);
            timeout = setTimeout(() => func.apply(this, args), wait);
        };
    }

    $('#basic-2_search').on('keyup search', debounce(function() { get_datatable(); }, 500));

    function printReport() {
        var color_id = $('#color_id').val();
        var size_id = $('#size_id').val();
        var balance_type = $('#balance_type').val();
        var search = $('#basic-2_search').val();
        
        var url = '{{ route("common_stock.report") }}?type=RemainingList';
        if(color_id) url += '&color_id=' + color_id;
        if(size_id) url += '&size_id=' + size_id;
        if(balance_type) url += '&balance_type=' + balance_type;
        if(search) url += '&search=' + encodeURIComponent(search);
        
        window.open(url, '_blank');
    }
    @if(auth()->user()->role_as == 'Admin')
    function exportExcel() {
        var table = document.getElementById("remaining_stock_table");
        if(!table) return;
        
        // Clone table to remove actions column before exporting
        var clone = table.cloneNode(true);
        var cells = clone.querySelectorAll('th:last-child, td:last-child');
        cells.forEach(c => c.remove());

        var html = clone.outerHTML;
        var context = `<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">
            <head><meta charset="UTF-8"><!--[if gte mso 9]><xml><x` + `:ExcelWorkbook><x` + `:ExcelWorksheets><x` + `:ExcelWorksheet><x` + `:Name>Remaining Stock</x` + `:Name><x` + `:WorksheetOptions><x` + `:DisplayGridlines/></x` + `:WorksheetOptions></x` + `:ExcelWorksheet></x` + `:ExcelWorksheets></x` + `:ExcelWorkbook></xml><![endif]--></head>
            <body>${html}</body></html>`;

        var link = document.createElement("a");
        link.href = 'data:application/vnd.ms-excel;base64,' + window.btoa(unescape(encodeURIComponent(context)));
        link.download = "Remaining_Stock_List.xls";
        link.click();
    }
    @endif

    function viewHistory(color_id, size_id) {
        $('#history_modal_content').html('<div class="loader-box"><div class="loader-37"></div></div>');
        $('#historyModal').modal('show');
        $.get('{{ route("common_stock.history") }}', { color_id: color_id, size_id: size_id }, function(data) {
            $('#history_modal_content').html(data);
            feather.replace();
        });
    }
</script>
@endsection
