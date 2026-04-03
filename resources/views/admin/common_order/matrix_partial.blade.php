@if($bopps->isEmpty() || $colors->isEmpty())
    <div class="text-center p-5">
        <i class="fa fa-info-circle f-30 text-muted mb-2"></i>
        <h6 class="text-muted">No data found matching your current filters.</h6>
    </div>
@else
    <div class="matrix-wrapper">
        <table class="matrix-table">
            <thead>
                <tr>
                    <th class="corner-header">Color \ BOPP</th>
                    @foreach($bopps as $bopp)
                        <th>
                            <span class="size-name">{{ $bopp->name }}</span>
                        </th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach($colors as $color)
                    <tr>
                        <td class="sticky-col">{{ $color->name }}</td>
                        @foreach($bopps as $bopp)
                            <td class="p-2 border text-center">
                                @php
                                    $activeJobs = [];
                                    $firstSizeId = $bopp->sizes->first()->id ?? '';
                                    
                                    foreach($bopp->sizes as $s) {
                                        if(isset($disabled_cells[$color->id][$s->id])) {
                                            $job = $disabled_cells[$color->id][$s->id];
                                            $job['size_name'] = $s->name;
                                            $job['actual_size_id'] = $s->id;
                                            $activeJobs[] = $job;
                                        }
                                    }
                                @endphp

                                <div class="d-flex flex-column align-items-center gap-1">
                                    @php
                                        $hasActive = false;
                                        foreach($activeJobs as $j) { if($j['is_hold'] == 0) $hasActive = true; }
                                    @endphp

                                    @if(!$hasActive)
                                        {{-- New Order (Shown if no active job exists) --}}
                                        <button type="button" 
                                                class="cell-btn mb-1" 
                                                onclick="openOrderModal('{{ $color->id }}', '{{ e($color->name) }}', '{{ $bopp->id }}', '{{ e($bopp->name) }}', '{{ $firstSizeId }}')"
                                                title="New Order">
                                            <i class="fa fa-plus"></i>
                                        </button>
                                    @endif

                                    @foreach($activeJobs as $job)
                                        @if($job['is_hold'] == 0)
                                            <button type="button" 
                                                    class="btn btn-danger btn-xs fw-bold py-1 px-2" 
                                                    style="font-size: 9px; min-width: 65px;"
                                                    onclick="toggleHold('{{ $job['id'] }}')"
                                                    title="Active Job. Click to HOLD.">
                                                HOLD #{{ $job['id'] }}
                                            </button>
                                        @else
                                            <button type="button" 
                                                    class="btn btn-warning btn-xs fw-bold py-1 px-2 text-dark" 
                                                    style="font-size: 9px; min-width: 65px;"
                                                    onclick="openOrderModal('{{ $color->id }}', '{{ e($color->name) }}', '{{ $bopp->id }}', '{{ e($bopp->name) }}', '{{ $job['size_id'] }}', '{{ $job['id'] }}', '{{ $job['no_of_pieces'] }}', '{{ e($job['remarks']) }}')"
                                                    title="Paused. Click to RESUME.">
                                                HOLDING #{{ $job['id'] }}
                                            </button>
                                        @endif
                                    @endforeach
                                </div>
                            </td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif

<style>
    .matrix-table {
        border-collapse: separate;
        border-spacing: 0;
        width: 100%;
    }
    .matrix-table th {
        background: #242934;
        color: #fff !important;
        padding: 12px 10px;
        font-weight: 700;
        text-align: center;
        border: 1px solid #343a40 !important;
        font-size: 11px;
        text-transform: uppercase;
        min-width: 160px;
    }
    .corner-header {
        position: sticky;
        left: 0;
        top: 0;
        z-index: 50 !important;
        background: #1a1e26 !important;
        width: 180px;
        min-width: 180px !important;
    }
    .sticky-col {
        position: sticky;
        left: 0;
        background-color: #fff !important;
        z-index: 10;
        border: 1px solid #dee2e6 !important;
        border-right: 2px solid #cbd5e1 !important;
        font-weight: 700;
        font-size: 11px;
        color: #2b3344;
        padding-left: 15px !important;
        text-align: left;
    }
    .matrix-table td {
        border: 1px solid #dee2e6 !important;
        transition: background 0.2s;
    }
    .matrix-table tr:hover td {
        background-color: #f8fbff;
    }
    .cell-btn {
        width: 32px;
        height: 32px;
        border-radius: 6px;
        border: 1px solid #cbd5e1;
        background: #fff;
        color: #2b3344;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.2s;
    }
    .cell-btn:hover {
        background: #0d6efd;
        color: #fff;
        border-color: #0d6efd;
        transform: scale(1.1);
    }
    .size-name {
        display: block;
        font-size: 11px;
        letter-spacing: 0.5px;
    }
</style>
