@extends('layouts.admin.app')

@section('title', 'Remaining Common Stock')

@section('css')
<style>
    /* Professional Matrix Layout */
    .matrix-wrapper {
        position: relative;
        border: 1px solid #d1d5db;
        background: #fff;
        height: 70vh;
        overflow: auto;
        border-radius: 8px;
    }
    .matrix-table { border-collapse: separate; border-spacing: 0; width: 100%; }
    
    .matrix-table th {
        background: #242934;
        color: #fff;
        padding: 12px;
        font-weight: 600;
        text-align: center;
        border-bottom: 1px solid #343a40;
        border-right: 1px solid #343a40;
        font-size: 11px;
        min-width: 150px;
        position: sticky;
        top: 0;
        z-index: 30;
    }
    
    /* Sticky First Column (Colors) */
    .matrix-table th:first-child, .matrix-table td:first-child {
        left: 0;
        z-index: 40;
        background: #1a1e26;
        width: 200px;
        min-width: 200px;
        position: sticky;
        border-right: 2px solid #343a40;
    }
    .matrix-table td:first-child {
        background: #f8fafc;
        color: #334155;
        font-weight: 700;
        text-align: left;
        padding-left: 20px;
        border-right: 2px solid #cbd5e1;
        z-index: 20;
    }
    
    .matrix-table td {
        padding: 15px 12px;
        border-bottom: 1px solid #edf2f7;
        border-right: 1px solid #edf2f7;
        text-align: center;
        vertical-align: middle;
    }
    
    .matrix-table tr:hover td { background-color: #f1f5f9; }
    .matrix-table tr:hover td:first-child { background-color: #e2e8f0; }

    /* Stock Badge Styles */
    .stock-val { font-size: 14px; font-weight: 800; display: block; }
    .stock-unit { font-size: 10px; color: #64748b; font-weight: 500; }
    .stock-btn { 
        padding: 4px 8px; 
        border: 1px solid #e2e8f0; 
        border-radius: 6px; 
        background: #fff; 
        color: #334155;
        cursor: pointer;
        transition: all 0.2s;
        width: 100%;
        display: block;
    }
    .stock-btn:hover { background: #242934; color: #fff; border-color: #242934; transform: translateY(-2px); box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
    .stock-btn:hover .stock-val, .stock-btn:hover .stock-unit { color: #fff !important; }
    
    .low-stock { background-color: #fef2f2 !important; }
    .low-stock .stock-val { color: #dc2626 !important; }
    
    /* Scrollbar */
    .matrix-wrapper::-webkit-scrollbar { width: 8px; height: 8px; }
    .matrix-wrapper::-webkit-scrollbar-track { background: #f1f5f9; }
    .matrix-wrapper::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
    
    .filter-card { background: #fff; padding: 15px; border-radius: 8px; border: 1px solid #e2e8f0; margin-bottom: 15px; }
</style>
@endsection

@section('breadcrumb-title')
    <h3>Remaining Stock Matrix</h3>
@endsection

@section('breadcrumb-items')
    <li class="breadcrumb-item">Common Product Stock</li>
    <li class="breadcrumb-item active">Remaining Stock</li>
@endsection

@section('content')
<div class="container-fluid">
    <div class="filter-card shadow-sm">
        <div class="row g-3 align-items-end">
            <div class="col-md-4">
                <label class="form-label f-10 fw-bold text-uppercase text-muted">Search Color</label>
                <input type="text" id="color_search" class="form-control form-control-sm" placeholder="Shade name..." autocomplete="off">
            </div>
            <div class="col-md-4">
                <label class="form-label f-10 fw-bold text-uppercase text-muted">Search Size</label>
                <input type="text" id="size_search" class="form-control form-control-sm" placeholder="Spec name..." autocomplete="off">
            </div>
            <div class="col-md-4">
                <button type="button" onclick="resetFilters()" class="btn btn-outline-secondary btn-sm w-100">Reset Filters</button>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm overflow-hidden">
        <div class="card-header bg-white p-3 border-bottom d-flex justify-content-between align-items-center">
            <h6 class="mb-0 fw-bold">Live Inventory Matrix | <span id="stats_display">{{ $colors->count() }} Colors x {{ $sizes->count() }} Sizes</span></h6>
            <div class="d-flex gap-2">
                <button type="button" onclick="printMatrix()" class="btn btn-primary btn-sm px-3">
                    <i class="fa fa-file-pdf-o me-1"></i> Export Matrix PDF
                </button>
                <span class="badge bg-soft-success text-success p-1 px-2 f-10"><i class="fa fa-circle me-1"></i> In Stock</span>
                <span class="badge bg-soft-danger text-danger p-1 px-2 f-10"><i class="fa fa-circle me-1"></i> Low/Out</span>
            </div>
        </div>
        <div class="card-body p-0 position-relative">
            <div id="matrix_container">
                @include('admin.common_stock.remaining_matrix_partial')
            </div>
        </div>
    </div>
</div>

<!-- Statement Modal -->
<div class="modal fade" id="historyModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" id="history_modal_content"></div>
    </div>
</div>
@endsection

@section('script')
<script>
    let searchTimeout;

    function get_matrix() {
        var $container = $('#matrix_container');
        var color_search = $('#color_search').val();
        var size_search = $('#size_search').val();

        $.ajax({
            url: '{{ route("common_stock.remaining") }}',
            data: { color_search: color_search, size_search: size_search },
            type: 'GET',
            success: function(data) {
                $container.html(data);
                // Update stats
                let c = $('.matrix-table tbody tr').length;
                let s = $('.matrix-table thead th').length - 1;
                $('#stats_display').text(c + ' Colors x ' + s + ' Sizes');
            }
        });
    }

    $('#color_search, #size_search').on('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(get_matrix, 400);
    });

    function printMatrix() {
        window.open('{{ route("common_stock.report") }}?type=Remaining', '_blank');
    }

    function resetFilters() {
        $('#color_search, #size_search').val('');
        get_matrix();
    }

    function viewHistory(color_id, size_id) {
        // Reuse the history modal logic from before
        $('#history_modal_content').html('<div class="loader-box"><div class="loader-37"></div></div>');
        $('#historyModal').modal('show');
        $.get('{{ route("common_stock.history") }}', { color_id: color_id, size_id: size_id }, function(data) {
            $('#history_modal_content').html(data);
            feather.replace();
        });
    }
</script>
@endsection
