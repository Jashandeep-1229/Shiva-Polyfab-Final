@extends('layouts.admin.app')

@section('title', 'Common Order Matrix')

@section('css')
<style>
    /* Professional Layout & Scaling */
    .card {
        border: none;
        box-shadow: 0 0 15px rgba(0,0,0,0.05);
        border-radius: 4px;
    }
    .card-header {
        background-color: #fff;
        border-bottom: 1px solid #eef1f4;
        padding: 15px 20px;
    }
    
    /* Advanced Matrix Engine */
    .matrix-wrapper {
        position: relative;
        border: 1px solid #d1d5db;
        background: #fff;
        height: 65vh;
        overflow: auto;
    }
    .matrix-table {
        border-collapse: separate;
        border-spacing: 0;
        width: 100%;
    }
    
    /* Header & Column Management */
    .matrix-table th {
        background: #242934;
        color: #fff;
        padding: 10px;
        font-weight: 500;
        text-align: center;
        border-bottom: 1px solid #343a40;
        border-right: 1px solid #343a40;
        font-size: 11px;
        min-width: 140px;
        position: sticky;
        top: 0;
        z-index: 30;
    }
    
    /* Sticky Corner (0,0) */
    .matrix-table th:first-child {
        left: 0;
        z-index: 40;
        background: #1a1e26;
        width: 180px;
        min-width: 180px;
        border-right: 2px solid #343a40;
    }
    
    /* Row Styling */
    .matrix-table td {
        padding: 8px 10px;
        border-bottom: 1px solid #edf2f7;
        border-right: 1px solid #edf2f7;
        text-align: center;
        transition: background 0.1s;
    }
    
    /* Sticky First Column (Colors) */
    .matrix-table td:first-child {
        background: #f8fafc;
        font-weight: 600;
        color: #334155;
        text-align: left;
        padding-left: 15px;
        position: sticky;
        left: 0;
        z-index: 20;
        width: 180px;
        min-width: 180px;
        border-right: 2px solid #cbd5e1;
        font-size: 12px;
    }
    
    .matrix-table tr:hover td {
        background-color: #f1f5f9;
    }
    .matrix-table tr:hover td:first-child {
        background-color: #e2e8f0;
    }
    
    /* Compact Action Button */
    .cell-btn {
        width: 28px;
        height: 28px;
        border-radius: 4px;
        border: 1px solid #d1d5db;
        background: #fff;
        color: #64748b;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        font-size: 10px;
        transition: all 0.2s;
    }
    .cell-btn:hover {
        background: #242934;
        color: #fff;
        border-color: #242934;
        transform: scale(1.1);
    }
    
    /* Meta Labels */
    .size-name {
        display: block;
        font-size: 12px;
        font-weight: 600;
        margin-bottom: 2px;
    }
    .meta-tag {
        display: inline-block;
        font-size: 9px;
        background: rgba(255,255,255,0.15);
        padding: 1px 4px;
        border-radius: 2px;
        text-transform: uppercase;
        color: #cbd5e1;
    }
    
    /* Filter Bar Sophistication */
    .filter-card {
        background-color: #fff;
        margin-bottom: 15px;
        padding: 15px;
        border: 1px solid #e2e8f0;
    }

    /* Scrollbar Optimization */
    .matrix-wrapper::-webkit-scrollbar {
        width: 10px;
        height: 10px;
    }
    .matrix-wrapper::-webkit-scrollbar-track {
        background: #f1f5f9;
    }
    .matrix-wrapper::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border: 2px solid #f1f5f9;
        border-radius: 5px;
    }
    .matrix-wrapper::-webkit-scrollbar-thumb:hover {
        background: #94a3b8;
    }

    /* Quick Add Section Styling */
    .quick-add-form label {
        font-size: 10px;
        font-weight: 700;
        color: #64748b;
        text-transform: uppercase;
    }
    .quick-add-form .form-control, .quick-add-form .form-select {
        border-color: #e2e8f0;
    }
    .quick-add-form .form-control:focus, .quick-add-form .form-select:focus {
        border-color: #3b82f6;
        box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.1);
    }
    #quickAddCollapse .bg-light {
        background-color: #f8fafc !important;
    }
    
    /* Loader */
    .matrix-loader {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(255,255,255,0.7);
        z-index: 100;
        display: none;
        align-items: center;
        justify-content: center;
    }
</style>
@endsection

@section('breadcrumb-items')
    <li class="breadcrumb-item">Operations</li>
    <li class="breadcrumb-item active">Common Order Matrix</li>
@endsection

@section('content')
<div class="container-fluid">
    <!-- Simplified Filter Bar -->
    <div class="card filter-card">
        <div class="row gx-2 gy-2">
            <div class="col-md-3">
                <label class="form-label f-10 fw-bold text-uppercase text-muted">Search BOPP</label>
                <input type="text" id="size_search" class="form-control form-control-sm" placeholder="Search BOPP..." autocomplete="off">
            </div>
            <div class="col-md-3">
                <label class="form-label f-10 fw-bold text-uppercase text-muted">Search Color</label>
                <input type="text" id="color_search" class="form-control form-control-sm" placeholder="Search Color..." autocomplete="off">
            </div>
            <div class="col-md-3">
                    <label class="form-label f-10 fw-bold text-uppercase text-muted">&nbsp;</label>
                <button type="button" onclick="resetFilters()" class="btn btn-outline-secondary btn-sm w-100">Clear All Filters</button>
            </div>
            
            <div class="col-md-3 text-end d-flex align-items-end justify-content-end gap-2">
                 <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="collapse" data-bs-target="#quickAddCollapse"><i class="fa fa-plus-circle me-1"></i> Add Options</button>
            </div>
        </div>

        <div class="collapse mt-3" id="quickAddCollapse">
            <div class="row g-3 p-3 bg-light border rounded">
                <div class="col-md-4 border-end">
                    <h6 class="f-12 fw-bold text-primary mb-3"><i class="fa fa-paint-brush me-1"></i> Quick Add Color</h6>
                    <form action="{{ route('color_master.store') }}" method="POST" data-type="color" class="quick-add-form">
                        @csrf
                        <input type="hidden" name="color_id" value="0">
                        <div class="input-group input-group-sm">
                            <input type="text" name="name" class="form-control" placeholder="New Color Name" oninput="this.value = this.value.toUpperCase()" required>
                            <button type="submit" class="btn btn-primary">Add Color</button>
                        </div>
                    </form>
                </div>
                <div class="col-md-8">
                    <h6 class="f-12 fw-bold text-primary mb-3"><i class="fa fa-arrows-alt me-1"></i> Quick Add Size</h6>
                    <form action="{{ route('size_master.store') }}" method="POST" data-type="size" class="row gx-2 quick-add-form">
                        @csrf
                        <input type="hidden" name="size_id" value="0">
                        <div class="col-md-4">
                            <input type="text" name="name" class="form-control form-control-sm" placeholder="Size Name" oninput="this.value = this.value.toUpperCase()" required>
                        </div>
                        <div class="col-md-2">
                            <select name="fabric_id" class="form-select form-select-sm" required>
                                <option value="">Fabric</option>
                                @foreach($fabrics as $f) <option value="{{$f->id}}">{{$f->name}}</option> @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <select name="bopp_id" class="form-select form-select-sm" required>
                                <option value="">BOPP</option>
                                @foreach($all_bopps as $b) <option value="{{$b->id}}">{{$b->name}}</option> @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <select name="order_send_for" class="form-select form-select-sm" required>
                                <option value="Cutting">Cutting</option>
                                <option value="Box">Box</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary btn-sm w-100">Add Size</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Matrix Display Card -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h6 class="mb-0">Job Configuration Matrix | <span id="stats_display"><span class="text-primary">{{ $colors->count() }} Colors</span> x <span class="text-primary">{{ $bopps->count() }} BOPP Columns</span></span></h6>
        </div>
        <div class="card-body p-0 position-relative">
            <div class="matrix-loader">
                <div class="spinner-border text-primary" role="status"></div>
            </div>
            <div id="matrix_container">
                @include('admin.common_order.matrix_partial')
            </div>
        </div>
    </div>
</div>

<!-- Order Modal -->
<div class="modal fade" id="orderModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form id="common_order_form" class="modal-content border-0" action="{{ route('common_order.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="modal-header bg-dark text-white rounded-0">
                <h6 class="modal-title" id="order_modal_title">New Order Initiation</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="row mb-4 bg-light p-3 rounded mx-0 border">
                    <div class="col-6 border-end">
                        <small class="text-secondary text-uppercase f-10 d-block mb-1 fw-bold">Color Shade</small>
                        <h6 id="display_color" class="mb-0 text-dark fw-bold">-</h6>
                    </div>
                    <div class="col-6">
                        <small class="text-secondary text-uppercase f-10 d-block mb-1 fw-bold">BOPP Spec</small>
                        <h6 id="display_size" class="mb-0 text-dark fw-bold">-</h6>
                    </div>
                </div>

                <input type="hidden" name="job_card_id" id="modal_job_id">
                <input type="hidden" name="color_id" id="modal_color_id">
                <input type="hidden" name="size_id" id="modal_size_id">
                
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label fw-bold f-11 text-uppercase text-dark">Total Quantity (Pieces)</label>
                        <input type="number" name="qty" id="modal_qty" class="form-control form-control-sm" placeholder="Enter qty" min="1" required>
                    </div>

                    <div class="col-12">
                        <label class="form-label fw-bold f-11 text-uppercase text-dark">Operational Remarks</label>
                        <textarea name="software_remarks" id="modal_remarks" class="form-control form-control-sm" rows="3" placeholder="Additional notes for production..."></textarea>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0 p-4 pt-0">
                <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-dark btn-sm px-4" id="submit_btn">Process Order</button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('script')
<script>
    // Real-time Search Logic
    let searchTimeout;
    
    function performSearch() {
        // Save current scroll positions
        var winScrollPos = $(window).scrollTop();
        var wrapperScrollTop = $('.matrix-wrapper').scrollTop();
        var wrapperScrollLeft = $('.matrix-wrapper').scrollLeft();
        
        $('.matrix-loader').css('display', 'flex');
        
        let colorSearch = $('#color_search').val();
        let sizeSearch = $('#size_search').val();
        
        $.ajax({
            url: "{{ route('common_order.create') }}",
            type: "GET",
            data: {
                color_search: colorSearch,
                size_search: sizeSearch
            },
            success: function(response) {
                $('#matrix_container').html(response);
                
                // Restore scroll positions
                $(window).scrollTop(winScrollPos);
                if (wrapperScrollTop || wrapperScrollLeft) {
                    $('.matrix-wrapper').scrollTop(wrapperScrollTop);
                    $('.matrix-wrapper').scrollLeft(wrapperScrollLeft);
                }
                
                // Update Stats
                let colorCount = $('.matrix-table tbody tr').length;
                let boppCount = $('.matrix-table thead th').length - 1;
                if (boppCount < 0) boppCount = 0;
                $('#stats_display').html(`<span class="text-primary">${colorCount} Colors</span> x <span class="text-primary">${boppCount} BOPP Columns</span>`);
                
                $('.matrix-loader').hide();
            },
            error: function() {
                $('.matrix-loader').hide();
                $.notify({ title: 'Search Error', message: 'Failed to filter matrix.' }, { type: 'danger' });
            }
        });
    }

    $('#color_search, #size_search').on('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(performSearch, 400); // 400ms debounce
    });

    function resetFilters() {
        $('#color_search, #size_search').val('');
        performSearch();
    }

    function openOrderModal(colorId, colorName, boppId, boppName, sizeId, jobId = null, qty = '', remarks = '') {
        $('#modal_job_id').val(jobId);
        $('#modal_color_id').val(colorId);
        $('#modal_size_id').val(sizeId); 
        
        $('#display_color').text(colorName);
        $('#display_size').text(boppName);
        
        $('#modal_qty').val(qty);
        $('#modal_remarks').val(remarks);
        
        if (jobId) {
            $('#order_modal_title').text('Resume & Update Job Card');
            $('#submit_btn').text('Resume & Continue').removeClass('btn-dark').addClass('btn-warning');
        } else {
            $('#order_modal_title').text('New Order Initiation');
            $('#submit_btn').text('Generate Job Card').removeClass('btn-warning').addClass('btn-dark');
        }
        
        $('#orderModal').modal('show');
    }

    $('#common_order_form').on('submit', function(e) {
        e.preventDefault();
        
        var $btn = $('#submit_btn');
        var originalHtml = $btn.html();
        $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin me-1"></i> Processing');

        var formData = new FormData(this);

        $.ajax({
            url: $(this).attr('action'),
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(data) {
                if (data.result == 1) {
                    $.notify({ title: 'Success', message: data.message }, { type: 'success', placement: { from: 'top', align: 'right' } });
                    $('#orderModal').modal('hide');
                    performSearch(); 
                } else {
                    $.notify({ title: 'Error', message: data.message }, { type: 'danger' });
                }
                $btn.prop('disabled', false).html(originalHtml);
            },
            error: function() {
                $.notify({ title: 'Error', message: 'The request failed to transmit.' }, { type: 'danger' });
                $btn.prop('disabled', false).html(originalHtml);
            }
        });
    });

    // Quick Add Handling
    $(document).on('submit', '.quick-add-form', function(e) {
        e.preventDefault();
        var form = $(this);
        var btn = form.find('button[type="submit"]');
        btn.prop('disabled', true);

        $.ajax({
            url: form.attr('action'),
            method: 'POST',
            data: new FormData(this),
            processData: false,
            contentType: false,
            success: function(data) {
                if(data.result == 1) {
                    $.notify({ title: 'Success', message: data.message }, { type: 'success' });
                    form[0].reset();
                    performSearch(); 
                } else {
                    $.notify({ title: 'Error', message: data.message }, { type: 'danger' });
                }
                btn.prop('disabled', false);
            }
        });
    });

    function toggleHold(id) {
        var url = "{{ route('job_card.change_hold_status', ':id') }}";
        url = url.replace(':id', id);
        
        $.get(url, function(data) {
            if(data.result == 1) {
                $.notify({ title: 'Success', message: data.message }, { type: 'info' });
                performSearch(); 
            } else {
                $.notify({ title: 'Error', message: data.message }, { type: 'danger' });
            }
        });
    }
</script>
@endsection
