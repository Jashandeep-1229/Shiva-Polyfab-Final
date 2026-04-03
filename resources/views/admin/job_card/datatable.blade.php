@php
    use App\Helpers\PermissionHelper;
@endphp
<style>
    .custom-scrollbar {
        border-radius: 8px;
        border: 1px solid #e2e8f0;
        background: #fff;
        overflow-x: auto !important;
        position: relative;
        max-height: 70vh; /* Optional: adds a vertical scroll if table is very long */
    }
    #basic-test {
        margin: 0 !important;
        border-collapse: separate !important;
        border-spacing: 0;
        width: 100%;
        table-layout: auto !important; /* Let content determine width for better display */
    }
    #basic-test thead th {
        background-color: #f8fafc !important;
        color: #1e293b;
        font-weight: 700;
        text-transform: uppercase;
        font-size: 11px;
        letter-spacing: 0.5px;
        border-bottom: 2px solid #e2e8f0;
        padding: 12px 10px;
        white-space: nowrap;
        position: sticky;
        top: 0;
        z-index: 100;
    }
    #basic-test tbody td {
        vertical-align: middle;
        font-size: 13px;
        color: #334155;
        padding: 10px 10px;
        border-bottom: 1px solid #f1f5f9;
        white-space: nowrap;
        background: #fff;
    }
    
    /* Sticky Right Column - Action */
    #basic-test thead th:last-child,
    #basic-test tbody td:last-child {
        position: sticky;
        right: 0;
        z-index: 110;
        width: 140px !important;
        min-width: 140px !important;
        max-width: 140px !important;
        background-color: #fcfdfe;
        border-left: 1px solid #e2e8f0;
        text-align: center !important;
    }
    #basic-test thead th:last-child {
        background-color: #f1f5f9 !important;
        z-index: 120;
    }
    #basic-test tbody td:last-child {
        box-shadow: -4px 0 8px -2px rgba(0,0,0,0.05);
    }
    
    /* Hover highlight for sticky rows */
    #basic-test tr:hover td {
        background-color: #f8fafc !important;
    }

    /* ======= ON HOLD ROW HIGHLIGHT ======= */
    #basic-test tr.row-on-hold td {
        background-color: #fff5f5 !important;
        border-bottom: 1px solid #fecaca !important;
    }
    #basic-test tr.row-on-hold td:first-child {
        border-left: 4px solid #ef4444 !important;
    }
    #basic-test tr.row-on-hold:hover td {
        background-color: #fee2e2 !important;
    }
    #basic-test tr.row-on-hold td:last-child {
        background-color: #fff5f5 !important;
    }
    /* Pulsing lock icon for held orders */
    .btn-hold-locked {
        animation: holdPulse 1.4s ease-in-out infinite;
    }
    @keyframes holdPulse {
        0%, 100% { opacity: 1; transform: scale(1); }
        50%       { opacity: 0.7; transform: scale(1.15); }
    }
    /* Pulsing green for unhold */
    .btn-unhold-pulse {
        animation: unholdPulse 1.2s ease-in-out infinite;
        box-shadow: 0 0 0 0 rgba(34,197,94,0.6);
    }
    @keyframes unholdPulse {
        0%   { box-shadow: 0 0 0 0 rgba(34,197,94,0.55); }
        70%  { box-shadow: 0 0 0 7px rgba(34,197,94,0); }
        100% { box-shadow: 0 0 0 0 rgba(34,197,94,0); }
    }
</style>
<div class="custom-scrollbar">
    <table class="display nowrap table table-hover" id="basic-test" style="width:100%">
        <thead>
            <tr>
                <th class="all ps-3" style="width: 40px;">#</th>
                <th class="all" style="width: 100px;">Order No</th>
                <th class="all" style="width: 250px;">Name Of Job</th>
                @if(request()->process != 'Laminated Rolls')
                <th class="all text-center" style="width: 80px;">Est. Pieces</th>
                @endif
                @if(request()->process == 'Laminated Rolls' || request()->process == 'Schedule For Box / Cutting')
                <th class="all" style="min-width: 120px;">
                    @if(strcasecmp(request()->type ?? '', 'Common') === 0 || strcasecmp(request()->category ?? '', 'common') === 0 || isset($is_common_view))
                        @if(request()->process == 'Schedule For Box / Cutting')
                            No. Of Rolls
                        @else
                            Remaining Rolls
                        @endif
                    @else
                        Est. Rolls
                    @endif
                </th>
                @endif
                @if(request()->type != 'Common' && request()->category != 'common')
                <th class="all" style="min-width: 150px;">Customer/Agent</th>
                @endif
                @if(request()->process == 'Cylinder Come' || request()->type == 'new')
                <th class="all" style="min-width: 150px;">Cylinder Agent</th>
                @endif
              
                @if(request()->process != 'Ready Bags List')
                <th class="all" style="min-width: 120px;">Bopp</th>
                <th class="all" style="min-width: 120px;">Fabric</th>
                @endif
                  @if(request()->process == 'Schedule For Box / Cutting')
                <th class="all" style="min-width: 120px;">Loop Color</th>
                @endif
                <th class="all" style="min-width: 100px;">Job Type</th>
                <th class="all" style="min-width: 120px;">Process Status</th>
                <th class="all" style="min-width: 120px;">Dispatch Date</th>
                @if(request()->process == 'Ready Bags List')
                <th class="all" style="min-width: 120px;">Order Date</th>
                <th class="all" style="min-width: 150px;">Remaining Days</th>
                @endif
              
              
                <th class="all" style="min-width: 150px;">Action</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($job_card as $key => $item)
            <tr id="tr-{{ $item->id }}" class="{{ $item->is_hold == 1 ? 'row-on-hold' : '' }}">
                <td>{{ $job_card->firstItem() + $key }}</td>
                <td><span class="badge badge-light-primary">{{ $item->job_card_no ?? 'N/A' }}</span></td>
                <!-- <td>{{ date('d M,y', strtotime($item->job_card_date)) ?? 'N/A' }}</td> -->
                <td>{{ $item->name_of_job ?? 'N/A' }}</td>
                @if(request()->process != 'Laminated Rolls')
                <td class="fw-bold">
                    @if($item->job_type == 'Common')
                        @php
                            $has_suffix = str_contains($item->job_card_no, '-R');
                            $rollouts = $item->roll_outs()->get();
                            $has_rollouts = $rollouts->isNotEmpty();
                            
                            // Base production/pieces
                            $pd = $item->processes()->where('actual_order', '>', 0)->orderBy('id', 'desc')->first();
                            $base_pieces = $pd ? $pd->actual_order : ($item->actual_pieces ?: $item->no_of_pieces);
                            
                            $current_proc = $item->job_card_process;
                            // Stages where we actually expect to show partial/remaining counts
                            $is_partial_stage = in_array($current_proc, ['Laminated Rolls', 'Schedule For Box / Cutting', 'Ready Bags List', 'Packing Slip']);

                            if ($has_suffix) {
                                // Sub-job: Show its specific pieces
                                $display_output = '<strong>' . number_format($item->actual_pieces, 0) . ' PCS</strong>';
                            } elseif (!$has_rollouts || !$is_partial_stage) {
                                // Fresh job OR early stage (Printing/Bopp): Show full count
                                $display_output = '<strong>' . number_format($base_pieces, 0) . ' PCS</strong>';
                            } else {
                                // Partial main job in late stage: Calculate remaining
                                $lam_proc = $item->processes()->where('process_name', 'Schedule For Lamination')->orderBy('id', 'desc')->first();
                                $total_rolls = $lam_proc ? $lam_proc->estimate_production : 0;
                                $out_rolls = $rollouts->sum('rolls_out');
                                $remaining_rolls = max(0, $total_rolls - $out_rolls);
                                
                                if ($total_rolls > 0) {
                                    $ratio = $remaining_rolls / $total_rolls;
                                    $remaining_pieces = round($base_pieces * $ratio);
                                    $display_output = '<span class="text-muted" style="font-size: 0.85em;">' . number_format($remaining_pieces, 0) . ' / ' . number_format($base_pieces, 0) . '</span>';
                                } else {
                                    // Fallback if lamination record is missing but rollouts exist
                                    $display_output = '<strong>' . number_format($base_pieces, 0) . ' PCS</strong>';
                                }
                            }
                        @endphp
                        {!! $display_output !!}
                    @else
                    @if($item->actual_pieces != $item->no_of_pieces)
                        {{ number_format($item->actual_pieces, 0) }} / {{ number_format($item->no_of_pieces, 0) }} PCS
                    @else
                        {{ number_format($item->actual_pieces, 0) }} PCS
                    @endif
                @endif
                </td>
                @endif
                @if(request()->process == 'Laminated Rolls' || request()->process == 'Schedule For Box / Cutting')
                <td>
                    @if($item->job_type == 'Common')
                        @php
                            $current_proc = $process ?? request()->process;
                            if (strpos($current_proc, 'Box') !== false || strpos($current_proc, 'Cutting') !== false || strpos($current_proc, 'Bags') !== false) {
                                // For sub-jobs, just find the production record that brought it here
                                $rolls_to_show = $item->processes()->where('estimate_production', '>', 0)->orderBy('id', 'desc')->first()?->estimate_production ?? 0;
                            } else {
                                // For main job in lamination stage, calculate remaining
                                $lam_proc = $item->processes()->where('process_name', 'Schedule For Lamination')->orderBy('id', 'desc')->first();
                                $total_prod = $lam_proc ? $lam_proc->estimate_production : $item->no_of_pieces;
                                $taken_out = $item->roll_outs()->sum('rolls_out');
                                $rolls_to_show = max(0, $total_prod - $taken_out);
                            }
                        @endphp
                        <span class="badge badge-light-{{ (strpos($current_proc, 'Box') !== false || strpos($current_proc, 'Cutting') !== false || strpos($current_proc, 'Bags') !== false) ? 'success' : 'danger' }} fw-bold shadow-sm border border-{{ (strpos($current_proc, 'Box') !== false || strpos($current_proc, 'Cutting') !== false || strpos($current_proc, 'Bags') !== false) ? 'success' : 'danger' }} p-2" style="font-size: 14px;">{{ number_format($rolls_to_show, 2) }}</span>
                    @else
                        {{ $item->processes()->where('from', 'Lamination')->first()->estimate_production ?? '-' }}
                    @endif
                </td>
                @endif
                @if(request()->type != 'Common' && request()->category != 'common')
                <td>{{ $item->customer_agent->name ?? '-' }}</td>
                @endif
                @if(request()->process == 'Cylinder Come' || request()->type == 'new')
                <td>{{ $item->cylinder_agent->name ?? '-' }}</td>
                @endif
               
                @if(request()->process != 'Ready Bags List')
                <td>{{ $item->bopp->name ?? 'N/A' }}</td>
                <td>{{ $item->fabric->name ?? 'N/A' }}</td>
                @endif
                 @if(request()->process == 'Schedule For Box / Cutting')
                <td>{{ $item->loop_color ?? '-' }}</td>
                @endif
                <td style="text-transform: capitalize;">{{ $item->job_type ?? 'N/A' }}</td>
                <td>
                    <span class="badge badge-light-{{ $item->job_card_process == 'Completed' ? 'success' : 'primary' }} fw-bold" style="font-size: 11px;">
                        {{ $item->job_card_process ?? 'Pending' }}
                    </span>
                    @if($item->is_hold == 1)
                        <br>
                        <span class="badge badge-danger fw-bold mt-1 d-inline-flex align-items-center gap-1" style="font-size:10px;background:#dc3545;">
                            <i class="fa fa-pause-circle"></i> ON HOLD
                        </span>
                    @endif
                </td>
                
                <td>{{ $item->dispatch_date ? date('d M, Y', strtotime($item->dispatch_date)) : 'N/A' }}</td>
                @if(request()->process == 'Ready Bags List')
                <td>{{ $item->job_card_date ? date('d M, Y', strtotime($item->job_card_date)) : 'N/A' }}</td>
                <td>
                    @if($item->dispatch_date)
                        @php
                            $dispatchDate = \Carbon\Carbon::parse($item->dispatch_date);
                            $today = \Carbon\Carbon::today();
                            $days = $today->diffInDays($dispatchDate, false);
                        @endphp
                        <span class="badge badge-{{ $days < 0 ? 'danger' : ($days <= 2 ? 'warning' : 'success') }}">
                            {{ $days }} Days {{ $days < 0 ? 'Late' : 'Remaining' }}
                        </span>
                    @else
                        N/A
                    @endif
                </td>
                @endif
              
                <td>
            @php
                $permission_map = [
                    'Cylinder Come' => 'process_cylinder_come',
                    'Order List' => 'process_order_list',
                    'Schedule For Printing' => 'process_printing',
                    'Printed Bopp List' => 'process_bopp_list',
                    'Schedule For Lamination' => 'process_lamination',
                    'Laminated Rolls' => 'process_laminated_rolls',
                    'Schedule For Box / Cutting' => 'process_box_cutting',
                    'Ready Bags List' => 'process_ready_bags',
                    'Packing Slip' => 'process_packing_slip',
                    'Dispatch Material' => 'process_dispatch',
                    'Account Pending' => 'account_pending'
                ];

                $menu_key = ($item->job_type == 'Common') ? 'common_orders' : 'roto_orders';
                if(request()->process) $menu_key = (request()->process == 'Account Pending') ? 'account_pending' : 'order_process';

                // Check general permission first
                $can_next = PermissionHelper::check($menu_key, 'next_process');
                
                // If on a specific process page, also check the stage-wise permission
                if (!$can_next && request()->process && isset($permission_map[request()->process])) {
                    $can_next = PermissionHelper::check($permission_map[request()->process], 'next_process');
                }
            @endphp

            @if($can_next)
                @if($item->is_hold == 1)
                    {{-- ORDER IS ON HOLD: Show locked warning icon instead of Next Process --}}
                    <a onclick="showHoldAlert('{{ addslashes($item->job_card_no) }}', {{ $item->hold_reason_id ?? 'null' }})"
                       class="btn btn-danger btn-sm pointer p-1 f-14 btn-hold-locked"
                       data-toggle="tooltip" title="ORDER ON HOLD — Cannot proceed">
                        <i class="fa fa-lock"></i>
                    </a>
                @elseif($item->job_card_process == 'Moved For Dispatch' || $item->job_card_process == 'Dispatch Material')
                    <a onclick="packing_material({{$item->id}},'{{$item->job_card_process}}')" class="btn btn-dark btn-sm  pointer p-1 f-14" data-toggle="tooltip" title="Next Process">
                        <i class="fa fa-file"></i>
                    </a>
                @elseif($item->job_card_process == 'Laminated Rolls' && $item->job_type == 'Common')
                    <a onclick="manage_rolls({{$item->id}}, 'Manual Out')" class="btn btn-primary btn-sm pointer p-1 f-14" data-toggle="tooltip" title="Manual Out Rolls">
                        <i class="fa fa-cubes"></i>
                    </a>
                    <a onclick="manage_rolls({{$item->id}}, 'Next Process')" class="btn btn-success btn-sm pointer p-1 f-14" data-toggle="tooltip" title="Move to Next Process">
                        <i class="fa fa-arrow-right"></i>
                    </a>
                @elseif($item->job_card_process != 'Completed')
                    <a onclick="next_process({{$item->id}},'{{$item->job_card_process}}')" class="btn btn-success btn-sm pointer p-1 f-14" data-toggle="tooltip" title="Next Process">
                        <i class="fa fa-arrow-right"></i>
                    </a>
                @endif
            @endif
                    
                    @if(PermissionHelper::check($menu_key, 'edit'))
                        <!-- <a onclick="edit_modal({{$item->id}},{{$key+1}})"  class="btn btn-warning btn-sm  pointer p-1 f-14" data-bs-toggle="modal" data-bs-target="#job_card_modal"  data-toggle="tooltip" title="Edit">
                            <i class="fa fa-edit"></i>
                        </a> -->
                    @endif
                   


                    <a onclick="view_modal({{$item->id}})"  class="btn btn-info btn-sm  pointer p-1 f-14" data-bs-toggle="modal" data-bs-target="#job_card_modal"  data-toggle="tooltip" title="View">
                        <i class="fa fa-eye"></i>
                    </a>
                    <!-- <a onclick="edit_modal({{$item->id}},{{$key+1}})"  class="btn btn-warning btn-sm  pointer p-1 f-14" data-bs-toggle="modal" data-bs-target="#job_card_modal"  data-toggle="tooltip" title="Edit">
                        <i class="fa fa-edit"></i>
                    </a> -->
                    
                    @if (auth()->user()->role_as == 'Admin')
                        <a onclick="delete_job_card({{$item->id}})" class="btn btn-danger btn-sm  pointer p-1 f-14" data-toggle="tooltip" title="Delete">
                            <i class="fa fa-trash-o"></i>
                        </a>
                    @endif

                    {{-- HOLD / UNHOLD Buttons (same permission as Next Process) --}}
                    @if($can_next)
                        @if($item->is_hold == 0 && $item->job_card_process != 'Completed')
                            <a onclick="openHoldModal({{ $item->id }})"
                               class="btn btn-warning btn-sm pointer p-1 f-14"
                               data-toggle="tooltip" title="Place on HOLD">
                                <i class="fa fa-pause"></i>
                            </a>
                        @elseif($item->is_hold == 1)
                            <a onclick="unholdOrder({{ $item->id }})"
                               class="btn btn-success btn-sm pointer p-1 f-14 btn-unhold-pulse"
                               data-toggle="tooltip" title="Click to Release HOLD">
                                <i class="fa fa-unlock"></i>
                            </a>
                        @endif
                    @endif
                </td>
            </tr>
            @endforeach

        </tbody>
    </table>
</div>
<div class="mt-2 text-center pages">
    {{$job_card->onEachSide(1)->links()}}
</div>
