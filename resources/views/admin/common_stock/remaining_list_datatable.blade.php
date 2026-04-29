<div class="table-responsive custom-scrollbar">
    <table class="table table-sm align-middle mb-0" id="remaining_stock_table">
        <thead class="f-11 text-uppercase text-muted" style="background-color: #f8f9fa;">
            <tr>
                <th width="60" class="text-center py-3" style="border-bottom: 2px solid #dee2e6; font-weight: 700;">S.No.</th>
                <th class="text-start ps-3 py-3" style="border-bottom: 2px solid #dee2e6; font-weight: 700;">COLOR (SHADE)</th>
                <th width="180" class="text-end pe-4 py-3" style="border-bottom: 2px solid #dee2e6; font-weight: 700;">REMAINING STOCK</th>
                <th width="120" class="text-center py-3" style="border-bottom: 2px solid #dee2e6; font-weight: 700;">STATEMENT</th>
            </tr>
        </thead>
        <tbody class="border-top-0">
            @php 
                $total_page_stock = 0; 
                $overall_serial = 1;
            @endphp
            @forelse($grouped_stocks as $sizeName => $stocks)
                @php $size_total = $stocks->sum('balance'); @endphp
                <!-- Size Group Header -->
                <tr class="size-group-header">
                    <td colspan="4" class="ps-3 py-2 fw-bold text-dark bg-light" style="border-bottom: 1px solid #dee2e6;">
                        <i class="fa fa-tag me-2 text-primary"></i> SIZE: {{ $sizeName }}
                    </td>
                </tr>
                
                @foreach($stocks as $index => $stock)
                    @php $total_page_stock += $stock->balance; @endphp
                    <tr class="stock-row">
                        <td class="text-center text-muted f-12 py-2" style="border-bottom: 1px solid #f1f3f5;">
                            {{ $overall_serial++ }}
                        </td>
                        <td class="ps-3 py-2" style="border-bottom: 1px solid #f1f3f5;">
                            <span class="f-13 fw-medium text-dark">{{ $stock->color->name ?? 'N/A' }}</span>
                        </td>
                        <td class="text-end pe-4 py-2" style="border-bottom: 1px solid #f1f3f5;">
                            <div class="stock-display">
                                <span class="f-14 fw-bold {{ $stock->balance < 0 ? 'text-danger' : 'text-dark' }}">
                                    {{ number_format($stock->balance, 3) }}
                                </span>
                                <span class="f-11 text-muted ms-1">KGS</span>
                            </div>
                        </td>
                        <td class="text-center py-2" style="border-bottom: 1px solid #f1f3f5;">
                            <button type="button" class="btn btn-outline-primary btn-xs px-3 rounded-pill shadow-sm" onclick="viewHistory({{ $stock->color_id }}, {{ $stock->size_id }})">
                                HISTORY
                            </button>
                        </td>
                    </tr>
                @endforeach
                
                <!-- Size Group Subtotal -->
                <tr class="subtotal-row bg-white">
                    <td colspan="2" class="text-end py-2 pe-3 text-muted f-12 fw-bold italic">Subtotal ({{ $sizeName }}):</td>
                    <td class="text-end pe-4 py-2 fw-bold text-dark" style="border-bottom: 2px solid #f8f9fa;">
                        {{ number_format($size_total, 3) }} <span class="f-11 text-muted ms-1">KGS</span>
                    </td>
                    <td></td>
                </tr>
                
                <!-- Spacer Row -->
                <tr class="spacer-row">
                    <td colspan="4" style="height: 10px; background: transparent; border: none;"></td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="text-center py-5 text-muted f-14 bg-white border-0">
                        <div class="py-4">
                            <i class="fa fa-info-circle fa-2x mb-3 text-light"></i>
                            <p>No stock data found matching your selection.</p>
                        </div>
                    </td>
                </tr>
            @endforelse
        </tbody>
        @if($grouped_stocks->isNotEmpty())
            <tfoot style="background-color: #2b3035;">
                <tr>
                    <td colspan="2" class="text-end text-uppercase py-3 pe-3 f-12 text-light fw-bold">Grand Total (Overall Filtered):</td>
                    <td class="text-end pe-4 py-3 f-18 fw-bold text-white">
                        {{ number_format($total_page_stock, 3) }} <span class="f-11 text-light opacity-50 ms-1">KGS</span>
                    </td>
                    <td></td>
                </tr>
            </tfoot>
        @endif
    </table>
</div>

<style>
    .size-group-header {
        border-top: 1px solid #dee2e6;
    }
    .stock-row:hover td {
        background-color: #f8fafc;
    }
    .subtotal-row td {
        font-style: italic;
    }
    .btn-xs { 
        padding: 2px 8px; 
        font-size: 10px; 
        font-weight: 600;
        letter-spacing: 0.3px;
    }
    .f-18 { font-size: 18px; }
    .f-14 { font-size: 14px; }
    .f-13 { font-size: 13px; }
    .f-12 { font-size: 12px; }
    .f-11 { font-size: 11px; }
    
    #remaining_stock_table {
        border-collapse: separate;
        border-spacing: 0;
    }
</style>
