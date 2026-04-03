<div class="table-responsive custom-scrollbar">
    <table class="table table-bordered table-sm align-middle" id="remaining_stock_table">
        <thead class="bg-dark f-12 fw-bold text-uppercase text-white">
            <tr>
                <th width="150" class="text-center bg-dark">Size (Spec.)</th>
                <th class="bg-dark">Color (Shade)</th>
                <th width="180" class="text-center bg-dark">Remaining Stock</th>
                <th width="150" class="text-center bg-dark">Statement</th>
            </tr>
        </thead>
        <tbody>
            @php $total_page_stock = 0; @endphp
            @forelse($grouped_stocks as $sizeName => $stocks)
                @php $size_total = $stocks->sum('balance'); @endphp
                @foreach($stocks as $index => $stock)
                    @php $total_page_stock += $stock->balance; @endphp
                    <tr>
                        @if($index === 0)
                            <td rowspan="{{ count($stocks) }}" class="text-center bg-light border-end" style="vertical-align: middle;">
                                <div class="size-container py-2">
                                    <div class="f-14 fw-bold text-primary">{{ $sizeName }}</div>
                                    <div class="f-11 text-muted mt-1 fw-bold text-uppercase">Total Stock:</div>
                                    <div class="f-16 fw-900 border-top mt-1 pt-1 {{ $size_total < 0 ? 'text-danger' : 'text-dark' }}">
                                        {{ number_format($size_total, 3) }}
                                    </div>
                                    <div class="f-10 text-muted">KGS</div>
                                </div>
                            </td>
                        @endif
                        <td class="ps-3 color-cell">
                            <div class="d-flex align-items-center">
                                <div class="color-indicator me-2" style="background-color: var(--theme-deafult); width: 8px; height: 8px; border-radius: 50%;"></div>
                                <span class="fw-bold f-13">{{ $stock->color->name ?? 'N/A' }}</span>
                            </div>
                        </td>
                        <td class="text-center">
                            <div class="stock-display py-1">
                                <span class="f-15 fw-bold {{ $stock->balance < 0 ? 'text-danger' : ($stock->balance > 0 ? 'text-primary' : 'text-muted') }}">
                                    {{ number_format($stock->balance, 3) }}
                                </span>
                                <span class="f-10 text-muted ps-1">KGS</span>
                            </div>
                        </td>
                        <td class="text-center">
                            <button type="button" class="btn btn-primary btn-xs px-3 shadow-none rounded-1" onclick="viewHistory({{ $stock->color_id }}, {{ $stock->size_id }})">
                                <i class="fa fa-history me-1"></i> VIEW
                            </button>
                        </td>
                    </tr>
                @endforeach
                <!-- Spacer Row -->
                <tr class="spacer-row">
                    <td colspan="4" style="height: 12px; background: #f8f9fa; border: none;"></td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="text-center py-5 text-muted f-14 bg-white">
                        <i class="fa fa-info-circle me-2"></i> No stock data found matching your selection.
                    </td>
                </tr>
            @endforelse
        </tbody>
        @if($grouped_stocks->isNotEmpty())
            <tfoot class="bg-dark text-white fw-bold">
                <tr>
                    <td colspan="2" class="text-end text-uppercase py-3">Grand Total (Overall Filtered):</td>
                    <td class="text-center f-18 py-3 bg-primary" id="grand_total_cell">
                        {{ number_format($total_page_stock, 3) }} <span class="f-11 text-white-50">KGS</span>
                    </td>
                    <td class="bg-dark"></td>
                </tr>
            </tfoot>
        @endif
    </table>
</div>

<style>
    #remaining_stock_table {
        border-collapse: separate;
        border-spacing: 0;
    }
    #remaining_stock_table thead th {
        border: 1px solid rgba(255,255,255,0.1) !important;
        position: sticky;
        top: 0;
        z-index: 10;
    }
    .fw-900 { font-weight: 900 !important; }
    .f-15 { font-size: 15px; }
    .f-13 { font-size: 13px; }
    .f-11 { font-size: 11px; }
    .f-10 { font-size: 10px; }
    
    #remaining_stock_table td[rowspan] {
        position: -webkit-sticky;
        position: sticky;
        top: 60px; /* Offset for the header height */
        z-index: 5;
        background-color: #f8f9fa !important;
        vertical-align: top;
        padding-top: 20px;
    }

    .size-container {
        display: block;
        width: 100%;
    }
    
    .color-cell {
        background-color: #fff !important;
        transition: background-color 0.2s;
    }
    .color-cell:hover {
        background-color: #f1f5f9 !important;
    }
    
    .btn-xs { padding: 4px 10px; font-size: 10px; text-transform: uppercase; font-weight: 700; }
    
    /* Clean up borders for the merged columns */
    .border-end {
        border-right: 2px solid #dee2e6 !important;
    }
    
    #grand_total_cell {
        box-shadow: inset 0 0 10px rgba(0,0,0,0.2);
    }

    #remaining_stock_table tr td {
        vertical-align: middle;
    }
</style>
