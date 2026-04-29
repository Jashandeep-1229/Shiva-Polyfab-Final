<div class="p-4 bg-white">
    @php $ctx = $log->properties['context'] ?? []; @endphp
    <!-- Activity Timeline History -->
    @if(!empty($history) && count($history) > 1 && !in_array($log->log_name, ['LedgerFollowup', 'LedgerFollowupHistory', 'ManageStock']))
        <div class="mb-5">
            <div class="text-muted small fw-bold text-uppercase mb-3" style="letter-spacing: 1px;"><i class="fa fa-history me-2"></i>Activity Timeline History</div>
            <ul class="log-timeline">
                @foreach($history as $h)
                    <li class="log-timeline-item {{ $h->id == $log->id ? 'active' : '' }}">
                        <div class="log-timeline-badge {{ $h->event == 'created' ? 'badge-created-tl' : ($h->event == 'deleted' ? 'badge-deleted-tl' : 'badge-updated-tl') }}">
                            <i class="fa {{ $h->event == 'created' ? 'fa-plus' : ($h->event == 'deleted' ? 'fa-trash' : 'fa-edit') }}"></i>
                        </div>
                        <div class="log-timeline-panel {{ $h->id == $log->id ? 'active-panel' : '' }}">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <h6 class="mb-0 fw-bold text-dark" style="font-size: 11px;">
                                    @php $hType = $h->properties['context']['event_type'] ?? ($h->properties['context']['type'] ?? null); @endphp
                                    @if($h->log_name == 'LedgerFollowup' && $h->event == 'created')
                                        NEW FOLLOWUP STARTED
                                    @elseif($h->log_name == 'LedgerFollowupHistory' && $h->event == 'created')
                                        FOLLOWUP INTERACTION
                                    @elseif($h->log_name == 'LedgerFollowup' && $h->event == 'updated' && ($h->properties['attributes']['status'] ?? '') == 'Closed')
                                        FOLLOWUP CLOSED
                                    @elseif($h->event == 'created' || $hType == 'common_created')
                                        RECORD CREATED
                                    @elseif($h->event == 'deleted')
                                        RECORD DELETED
                                    @else
                                        RECORD UPDATED
                                    @endif
                                </h6>
                                <span class="small text-muted" style="font-size: 10px;">{{ $h->created_at->format('d M, h:i A') }}</span>
                            </div>
                            <div class="text-muted mb-2" style="font-size: 10px;">
                                By <span class="fw-bold text-dark">{{ $h->causer->name ?? 'Admin' }}</span>
                                @if($h->id == $log->id)
                                    <span class="badge ms-2" style="background-color: #6366f1; color: white; font-size: 8px; text-transform: uppercase;">YOU ARE VIEWING THIS</span>
                                @endif
                                @if($h->log_name == 'Bill')
                                    <a href="{{ route('bill.pdf', $h->subject_id) }}" target="_blank" class="ms-2 text-primary fw-bold" style="font-size: 8px; text-decoration: none;">
                                        <i class="fa fa-file-pdf-o"></i> VIEW PDF
                                    </a>
                                @endif
                            </div>
                            
                            @php 
                                $oldV = $h->properties['old'] ?? [];
                                $newV = $h->properties['attributes'] ?? [];
                                $diff = array_intersect_key($newV, $oldV);
                            @endphp
                            @if(!empty($diff))
                                <div class="mt-2 border-top pt-2" style="font-size: 9px;">
                                    @foreach($diff as $k => $v)
                                        @if($k == 'grand_total' && $h->log_name == 'Bill') @continue @endif
                                        @if($k == 'items')
                                            @php $oldI = $oldV['items'] ?? []; $newI = $v; @endphp
                                            @foreach($newI as $nIdx => $ni)
                                                @php $oi = $oldI[$nIdx] ?? null; @endphp
                                                @if($oi && (($oi['weight'] ?? 0) != ($ni['weight'] ?? 0)))
                                                    <div class="text-muted mb-1">
                                                        <span class="fw-bold text-dark text-uppercase">{{ $ni['size'] ?? 'N/A' }} / {{ $ni['color'] ?? 'N/A' }}:</span>
                                                        <span class="text-danger fw-bold">{{ number_format(($oi['weight'] ?? 0), 3) }}</span> <i class="fa fa-caret-right mx-1"></i> <span class="text-success fw-bold">{{ number_format(($ni['weight'] ?? 0), 3) }}</span>
                                                    </div>
                                                @elseif(!$oi)
                                                     <div class="text-muted mb-1">
                                                        <span class="fw-bold text-dark text-uppercase">{{ $ni['size'] ?? 'N/A' }} / {{ $ni['color'] ?? 'N/A' }}:</span>
                                                        <span class="badge bg-success" style="font-size: 8px;">[ADDED]</span> <span class="fw-bold text-success">{{ number_format(($ni['weight'] ?? 0), 3) }}</span>
                                                    </div>
                                                @endif
                                            @endforeach
                                        @elseif(($oldV[$k] ?? '') != $v)
                                            <div class="text-muted">
                                                <span class="fw-bold text-dark text-uppercase" style="font-size: 8px;">{{ str_replace('_', ' ', $k) }}:</span>
                                                <span class="text-danger fw-bold">{{ $oldV[$k] ?? 'N/A' }}</span> <i class="fa fa-caret-right mx-1"></i> <span class="text-success fw-bold">{{ $v }}</span>
                                            </div>
                                        @endif
                                    @endforeach
                                </div>
                            @endif

                            @if($h->log_name == 'Bill' && !empty($h->properties['old']['items']))
                                <div class="mt-2 p-2 border-start border-3 border-warning bg-white rounded shadow-sm" style="font-size: 9px; border-bottom: 1px solid #eee;">
                                    <div class="fw-bold text-dark mb-1" style="font-size: 10px;"><i class="fa fa-pencil me-1"></i>ITEMIZED CHANGES:</div>
                                    @php 
                                        $hOld = $h->properties['old']['items'] ?? [];
                                        $hNew = $h->properties['attributes']['items'] ?? ($h->properties['context']['items'] ?? []);
                                        $hMax = max(count($hOld), count($hNew));
                                    @endphp
                                    @for($i = 0; $i < $hMax; $i++)
                                        @php 
                                            $oI = $hOld[$i] ?? null;
                                            $nI = $hNew[$i] ?? null;
                                            $diffs = [];
                                            if($oI && $nI) {
                                                if(($oI['description'] ?? '') != ($nI['description'] ?? '')) $diffs[] = "Desc: <span class='text-muted'>{$oI['description']}</span> → <span class='text-dark fw-bold'>{$nI['description']}</span>";
                                                if(($oI['qty'] ?? 0) != ($nI['qty'] ?? 0)) $diffs[] = "Qty: <span class='text-muted'>{$oI['qty']}</span> → <span class='text-dark fw-bold'>{$nI['qty']}</span>";
                                                if(($oI['rate'] ?? 0) != ($nI['rate'] ?? 0)) $diffs[] = "Rate: <span class='text-muted'>₹" . ($oI['rate'] ?? 0) . "</span> → <span class='text-dark fw-bold'>₹" . ($nI['rate'] ?? 0) . "</span>";
                                                if(($oI['gst_percent'] ?? 0) != ($nI['gst_percent'] ?? 0)) $diffs[] = "GST: <span class='text-muted'>" . ($oI['gst_percent'] ?? 0) . "%</span> → <span class='text-dark fw-bold'>" . ($nI['gst_percent'] ?? 0) . "%</span>";
                                            } elseif(!$oI && $nI) {
                                                $diffs[] = "<span class='text-success fw-bold'>+ Added Item:</span> <span class='text-dark fw-bold'>" . ($nI['description'] ?? 'Item') . "</span>";
                                            } elseif($oI && !$nI) {
                                                $diffs[] = "<span class='text-danger fw-bold'>- Removed Item:</span> <span class='text-muted'>" . ($oI['description'] ?? 'Item') . "</span>";
                                            }
                                        @endphp
                                        @foreach($diffs as $d)
                                            <div class="mb-1 ms-2 text-dark" style="color: #1e293b !important;">• {!! $d !!}</div>
                                        @endforeach
                                    @endfor

                                    @if(($h->properties['old']['grand_total'] ?? 0) != ($h->properties['attributes']['grand_total'] ?? 0))
                                        <div class="mt-1 pt-1 border-top fw-bold text-primary" style="font-size: 10px;">
                                            GRAND TOTAL: <span class="text-danger" style="text-decoration: line-through; opacity: 0.7;">₹{{ number_format($h->properties['old']['grand_total'] ?? 0, 2) }}</span> <i class="fa fa-long-arrow-right mx-1"></i> <span class="text-success">₹{{ number_format($h->properties['attributes']['grand_total'] ?? 0, 2) }}</span>
                                        </div>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </li>
                @endforeach
            </ul>
        </div>
        <div style="clear: both;"></div>
        <hr class="my-4" style="border-top: 2px dashed #e2e8f0;">
    @endif

    <!-- Manage Stock Batch Block -->
    @if(in_array($log->log_name, ['ManageStock', 'CommonManageStock']))
        @php
            $attrs = $log->properties['attributes'] ?? [];
            $inOut = strtoupper($attrs['in_out'] ?? 'IN');
            $stockName = ($log->log_name == 'CommonManageStock') ? 'Common' : ucfirst($attrs['stock_name'] ?? 'Stock');
            $batchEntries = !empty($history) ? $history : [$log];
            $totalCount = count($batchEntries);
        @endphp
        <div class="mb-4">
            <div class="p-4 border rounded shadow-sm" style="background: white; border-left: 5px solid {{ $inOut == 'IN' ? '#10b981' : '#ef4444' }} !important;">
                <div class="d-flex justify-content-between align-items-center p-2 rounded mb-3 text-white fw-bold shadow-sm" style="background-color: {{ $inOut == 'IN' ? '#10b981' : '#ef4444' }} !important;">
                    <div class="ms-1" style="font-size: 11px; text-transform: uppercase;">
                        <i class="fa {{ $inOut == 'IN' ? 'fa-arrow-down' : 'fa-arrow-up' }} me-2"></i>{{ $stockName }} Stock {{ $inOut == 'IN' ? 'In' : 'Out' }}
                    </div>
                    <span class="badge bg-white text-dark fw-bold px-3 py-1" style="font-size: 10px;">{{ $totalCount }} ENTRIES</span>
                </div>

                <div class="table-responsive border rounded bg-white mt-1">
                    <table class="table table-sm table-hover mb-0" style="font-size: 11px;">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-3 py-2 text-muted uppercase" style="font-size: 9px;">Item Name</th>
                                <th class="text-center py-2 text-muted uppercase" style="font-size: 9px;">Qty</th>
                                <th class="text-center py-2 text-muted uppercase" style="font-size: 9px;">Remarks</th>
                                <th class="text-end pe-3 py-2 text-muted uppercase" style="font-size: 9px;">Time</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($batchEntries as $entry)
                                @php
                                    $eAttrs = $entry->properties['attributes'] ?? [];
                                    if($entry->log_name == 'CommonManageStock') {
                                        $itemName = ($entry->subject->color->name ?? 'N/A') . ' / ' . ($entry->subject->size->name ?? 'N/A');
                                    } else {
                                        $itemName = $entry->subject->master->name ?? 'N/A';
                                    }
                                @endphp
                                <tr>
                                    <td class="ps-3 py-2 text-dark">
                                        <div class="fw-bold">{{ $itemName }}</div>
                                    </td>
                                    <td class="text-center py-2 fw-bold text-dark">{{ $eAttrs['quantity'] ?? 0 }} {{ $eAttrs['unit_name'] ?? ($entry->log_name == 'CommonManageStock' ? 'Pcs' : 'Units') }}</td>
                                    <td class="text-center py-2 text-muted italic">{{ $eAttrs['remarks'] ?? '-' }}</td>
                                    <td class="text-end pe-3 text-muted">{{ $entry->created_at->format('h:i A') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                <div class="mt-4 pt-2 border-top d-flex justify-content-between align-items-center small text-muted" style="font-size: 10px;">
                    <span><i class="fa fa-user me-1"></i>RECORDED BY: <strong>{{ $log->causer->name ?? 'Admin' }}</strong></span>
                    <span><i class="fa fa-calendar me-1"></i>{{ $log->created_at->format('d M, Y') }}</span>
                </div>
            </div>
        </div>
    @endif

    <!-- Packing Slip Custom Block -->
    @if($log->log_name == 'PackingSlip')
        @php 
            $oldP = $log->properties['old'] ?? [];
            $subject = $log->subject;
            $jc = $subject->job_card ?? null;
            
            $totalW = $ctx['total_weight'] ?? ($subject->total_weight ?? ($oldP['total_weight'] ?? '0'));
            $totalB = $ctx['total_bags'] ?? ($subject->total_bags ?? ($oldP['total_bags'] ?? '0'));
            $psNo = $ctx['ps_no'] ?? ($subject->packing_slip_no ?? ($oldP['packing_slip_no'] ?? ($ctx['job_card_no'] ?? 'N/A')));
            $eventType = $ctx['event_type'] ?? ($ctx['type'] ?? null);
            
            // DEEP ITEM RECOVERY: If current log has no items, search history
            $items = $ctx['items'] ?? ($oldP['items'] ?? []);
            if(empty($items) && !empty($history)) {
                foreach($history as $h) {
                    $hCtx = $h->properties['context'] ?? [];
                    if(!empty($hCtx['items'])) {
                        $items = $hCtx['items'];
                        // Also recover Job Name if missing
                        if(($ctx['job_name'] ?? ($oldP['job_name'] ?? 'N/A')) == 'N/A') {
                            $oldP['job_name'] = $hCtx['job_name'] ?? 'N/A';
                        }
                        break;
                    }
                }
            }
        @endphp
        <div class="mb-4" style="clear: both;">
            <div class="p-3 border rounded shadow-sm" style="background: white; border-left: 5px solid {{ $log->event == 'deleted' || $eventType == 'common_deleted' ? '#ef4444' : '#10b981' }} !important;">
                @php 
                    $headerColor = ($log->event == 'deleted' || $eventType == 'common_deleted') ? '#ef4444' : '#f59e0b';
                    $headerIcon = ($log->event == 'deleted' || $eventType == 'common_deleted') ? 'fa-trash' : ($log->event == 'created' ? 'fa-plus-circle' : 'fa-edit');
                    $headerText = ($log->event == 'deleted' || $eventType == 'common_deleted') ? 'Common Packing Slip Deleted' : ($log->event == 'created' ? 'Common Packing Slip Created' : 'Common Packing Slip Updated');
                @endphp
                <div class="d-flex justify-content-between align-items-center p-2 rounded mb-3 text-white fw-bold shadow-sm" style="background-color: {{ $headerColor }} !important;">
                    <div class="ms-1" style="font-size: 11px; text-transform: uppercase;">
                        <i class="fa {{ $headerIcon }} me-2"></i>{{ $headerText }}
                    </div>
                    <div class="d-flex gap-2">
                        @if($log->event == 'deleted' || $eventType == 'common_deleted')
                            <span class="badge bg-white text-dark border px-2 py-1" style="font-size: 9px; min-width: 80px;"><i class="fa fa-undo me-1 text-danger"></i>STOCK REVERTED</span>
                            <span class="badge bg-white text-dark border px-2 py-1" style="font-size: 9px; min-width: 100px;"><i class="fa fa-trash-o me-1 text-muted"></i>RECORD REMOVED</span>
                        @else
                            <span class="badge bg-white text-dark border px-2 py-1" style="font-size: 9px; min-width: 80px;"><i class="fa fa-database me-1 text-success"></i>STOCK OUT</span>
                            <span class="badge bg-white text-dark border px-2 py-1" style="font-size: 9px; min-width: 100px;"><i class="fa fa-file-text-o me-1 text-primary"></i>ACCOUNT PENDING</span>
                        @endif
                    </div>
                </div>

                <div class="row align-items-center mb-3">
                    <div class="col-md-7 border-end">
                        <div class="text-muted small fw-bold mb-1" style="font-size: 9px; letter-spacing: 0.5px; color: #64748b;">PACKING SLIP DETAILS</div>
                        <h6 class="text-dark fw-bold mb-1" style="font-size: 14px;">NO: <span class="text-primary">{{ $psNo }}</span></h6>
                        <div class="text-muted small">Job: <span class="fw-bold text-dark">{{ $ctx['job_name'] ?? ($subject->job_card->name_of_job ?? ($oldP['job_name'] ?? ($oldP['remarks'] ?? 'N/A'))) }}</span></div>
                    </div>
                    <div class="col-md-5 text-end">
                        <div class="d-inline-block text-center px-2">
                            <div class="text-muted small fw-bold" style="font-size: 9px; color: #64748b;">TOTAL QTY</div>
                            <div class="fw-bold" style="font-size: 14px; color: #059669;">{{ number_format((float)$totalW, 3) }} <span class="small" style="font-size: 9px;">Kg</span></div>
                        </div>
                        <div class="d-inline-block text-center px-2 border-start">
                            <div class="text-muted small fw-bold" style="font-size: 9px; color: #64748b;">TOTAL BAGS</div>
                            <div class="text-dark fw-bold" style="font-size: 14px;">{{ $totalB }}</div>
                        </div>
                    </div>
                </div>

                @if(!empty($items))
                    <div class="table-responsive border rounded bg-white mt-1">
                        <table class="table table-sm table-hover mb-0" style="font-size: 11px;">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-3 py-2 text-muted uppercase" style="font-size: 9px;">Size / Color</th>
                                    <th class="text-center py-2 text-muted uppercase" style="font-size: 9px;">Bags</th>
                                    <th class="text-end pe-3 py-2 text-muted uppercase" style="font-size: 9px;">Total Weight (Kgs)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($items as $item)
                                    <tr>
                                        <td class="ps-3 py-2 text-dark">
                                            <span class="fw-bold">{{ $item['size'] ?? 'N/A' }}</span> / {{ $item['color'] ?? 'N/A' }}
                                        </td>
                                        <td class="text-center py-2 fw-bold text-dark">{{ $item['bags'] ?? 1 }}</td>
                                        <td class="text-end pe-3 fw-bold text-dark py-2">{{ number_format((float)($item['weight'] ?? 0), 3) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="bg-light fw-bold">
                                <tr style="border-top: 2px solid #e2e8f0;">
                                    <td class="ps-3 py-2 text-dark">CONSOLIDATED SUMMARY</td>
                                    <td class="text-center py-2 text-dark">{{ $totalB }} Bags</td>
                                    <td class="text-end pe-3 py-2 text-primary" style="font-size: 13px;">{{ number_format((float)$totalW, 3) }} Kg</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                @endif
                
                <div class="mt-3 d-flex justify-content-between align-items-center small text-muted" style="font-size: 10px;">
                    <span><i class="fa fa-user me-1"></i>ACTION BY: <strong>{{ $log->causer->name ?? 'Admin' }}</strong></span>
                    <span><i class="fa fa-calendar me-1"></i>{{ $log->created_at->format('d M, Y') }}</span>
                </div>
            </div>
        </div>
    @endif
    <!-- Job Card Completed Block -->
    @if($log->log_name == 'JobCard' && ($ctx['type'] ?? '') == 'job_completed')
        @php 
            $billNo = trim($ctx['bill_no'] ?? '');
            $jobId = $log->subject_id ?? ($ctx['job_id'] ?? null);
            $billId = null;

            if($billNo) {
                $billId = \DB::table('bills')->where('bill_no', $billNo)->value('id');
                if(!$billId) {
                    $billId = \DB::table('bills')->where('bill_no', 'LIKE', '%'.$billNo.'%')->value('id');
                }
            }
            
            if(!$billId && $jobId) {
                $billId = \DB::table('bills')->where('job_card_id', $jobId)->latest('id')->value('id');
            }
            
            $dueDate = $ctx['due_date'] ?? null;
            if($billId && !$dueDate) {
                $dueDate = \DB::table('bills')->where('id', $billId)->value('due_date');
                if($dueDate) {
                    try {
                        $dueDate = \Carbon\Carbon::parse($dueDate)->format('d M, Y');
                    } catch(\Exception $e) {}
                }
            }
        @endphp
        <div class="mb-4">
            <div class="p-4 border rounded shadow-sm" style="background: white; border-left: 5px solid #6366f1 !important;">
                <div class="d-flex justify-content-between align-items-center bg-primary p-2 rounded mb-3 text-white fw-bold shadow-sm" style="background-color: #6366f1 !important;">
                    <div class="ms-1" style="font-size: 11px; text-transform: uppercase;">
                        <i class="fa fa-check-circle me-2"></i>Job Card Completed
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-md-7">
                        <div class="text-muted small fw-bold mb-1" style="font-size: 9px; letter-spacing: 0.5px; color: #64748b;">COMPLETED JOB DETAILS</div>
                        <h6 class="text-dark fw-bold mb-1" style="font-size: 14px;">JOB NO: <span class="text-primary">{{ $ctx['job_no'] ?? 'N/A' }}</span></h6>
                        <div class="text-muted small">Name: <span class="fw-bold text-dark">{{ $ctx['job_name'] ?? 'N/A' }}</span></div>
                    </div>
                    <div class="col-md-5 text-end">
                        <div class="text-muted small fw-bold mb-1" style="font-size: 9px; color: #64748b;">
                            BILL NO: <span class="text-dark">{{ $ctx['bill_no'] ?? 'N/A' }}</span>
                            @if(!empty($billId))
                                <a href="{{ route('bill.pdf', $billId) }}" target="_blank" class="ms-2 badge bg-primary text-white p-2" style="font-size: 9px; text-decoration: none;">
                                    <i class="fa fa-file-pdf-o me-1"></i> VIEW PDF
                                </a>
                            @endif
                        </div>
                        <div class="text-muted small">Date: <span class="fw-bold text-dark">{{ $ctx['bill_date'] ?? 'N/A' }}</span></div>
                        @if(!empty($dueDate))
                            <div class="text-muted small mt-1">Due Date: <span class="fw-bold text-danger">{{ $dueDate }}</span></div>
                        @endif
                    </div>
                </div>

                @if(!empty($ctx['bill_items']))
                    <div class="table-responsive border rounded bg-white mb-3">
                        <table class="table table-sm mb-0" style="font-size: 10px;">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-3 py-2 text-muted" style="font-size: 8px;">DESCRIPTION</th>
                                    <th class="text-center py-2 text-muted" style="font-size: 8px;">QTY</th>
                                    <th class="text-center py-2 text-muted" style="font-size: 8px;">RATE</th>
                                    <th class="text-center py-2 text-muted" style="font-size: 8px;">GST (%)</th>
                                    <th class="text-end pe-3 py-2 text-muted" style="font-size: 8px;">TOTAL</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($ctx['bill_items'] as $bi)
                                    @php 
                                        $qty = (float)($bi['qty'] ?? 0);
                                        $rate = (float)($bi['rate'] ?? 0);
                                        $total = (float)($bi['total_amount'] ?? ($bi['total'] ?? 0));
                                        $gstP = (float)($bi['gst_percent'] ?? ($bi['gst_perc'] ?? 0));
                                        
                                        // Smart fallback for old logs: calculate GST if missing but total includes it
                                        if($gstP == 0 && ($qty * $rate) > 0 && abs($total - ($qty * $rate)) > 0.01) {
                                            $gstP = round((($total / ($qty * $rate)) - 1) * 100);
                                        }
                                    @endphp
                                    <tr>
                                        <td class="ps-3 py-2 fw-bold text-dark">{{ $bi['desc'] ?? ($bi['description'] ?? 'N/A') }}</td>
                                        <td class="text-center py-2">{{ $bi['qty'] }} {{ $bi['unit'] }}</td>
                                        <td class="text-center py-2">₹{{ number_format($rate, 2) }}</td>
                                        <td class="text-center py-2">{{ $gstP }}%</td>
                                        <td class="text-end pe-3 fw-bold text-dark py-2">₹{{ number_format($total, 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="bg-light fw-bold" style="font-size: 11px;">
                                <tr>
                                    <td colspan="4" class="ps-3 py-2 text-end">GRAND TOTAL</td>
                                    <td class="text-end pe-3 py-2 text-primary">₹{{ number_format($ctx['grand_total'] ?? 0, 2) }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                @endif

                @if(!empty($ctx['ledger']))
                    <div class="p-2 border rounded bg-light d-flex justify-content-between align-items-center" style="font-size: 10px;">
                        <div>
                            <span class="badge bg-success me-2" style="font-size: 8px;">LEDGER CREATED</span>
                            <span class="text-muted">Customer:</span> <strong class="text-dark">{{ $log->subject->customer_agent->name ?? 'N/A' }}</strong>
                            <span class="text-muted ms-2">/ Date:</span> <span class="fw-bold text-dark">{{ $ctx['ledger']['transaction_date'] ?? 'N/A' }}</span>
                        </div>
                        <div class="text-end d-flex align-items-center">
                            <div class="me-3">
                                <span class="text-muted">Amount:</span> <span class="fw-bold text-primary" style="font-size: 12px;">₹{{ number_format($ctx['ledger']['total_amount'] ?? 0, 2) }}</span>
                            </div>
                            <div>
                                <span class="text-muted">Entry Type:</span> <span class="fw-bold text-danger">{{ $ctx['ledger']['dr_cr'] ?? 'Dr' }}</span>
                            </div>
                        </div>
                    </div>
                @endif
                
                <div class="mt-4 pt-2 border-top d-flex justify-content-between align-items-center small text-muted" style="font-size: 10px;">
                    <span><i class="fa fa-user me-1"></i>COMPLETED BY: <strong>{{ $log->causer->name ?? 'Admin' }}</strong></span>
                    <span><i class="fa fa-clock-o me-1"></i>{{ $log->created_at->format('d M, Y h:i A') }}</span>
                </div>
            </div>
        </div>
    @endif
    

                        <!-- Standard Bill Log Block (Created/Updated/Deleted) -->
                        @if($log->log_name == 'Bill')
        @php 
            $billId = $log->subject_id;
            $bill = \App\Models\Bill::withTrashed()->with(['items', 'customer'])->find($billId);
            $billData = $log->properties['attributes'] ?? ($log->properties['old'] ?? []);
            
            $billNo = $bill->bill_no ?? ($billData['bill_no'] ?? 'N/A');
            $grandTotal = $bill->grand_total ?? ($billData['grand_total'] ?? 0);
            $billDate = $bill->bill_date ?? ($billData['bill_date'] ?? null);
            $dueDate = $bill->due_date ?? ($billData['due_date'] ?? null);
            $customerName = $bill->customer->name ?? 'N/A';
            
            if($billDate) {
                try { $billDate = \Carbon\Carbon::parse($billDate)->format('d M, Y'); } catch(\Exception $e) {}
            }
            if($dueDate) {
                try { $dueDate = \Carbon\Carbon::parse($dueDate)->format('d M, Y'); } catch(\Exception $e) {}
            }
        @endphp

        <div class="activity-job-card shadow-sm border rounded p-0 mb-4 bg-white overflow-hidden">
            <div class="bg-primary p-3 text-white d-flex justify-content-between align-items-center" style="background-color: #4f46e5 !important;">
                <h6 class="mb-0 fw-bold" style="font-size: 13px;">
                    <i class="fa fa-file-text-o me-2"></i>BILL {{ strtoupper($log->description) }}
                </h6>
                @if($bill && !$bill->deleted_at)
                    <a href="{{ route('bill.pdf', $bill->id) }}" target="_blank" class="btn btn-sm fw-bold px-3 py-1" style="font-size: 11px; background-color: #ffffff; color: #1e1b4b; border: 1px solid #ffffff; box-shadow: 0 2px 4px rgba(0,0,0,0.1); transition: all 0.2s;">
                        <i class="fa fa-file-pdf-o me-1"></i> VIEW PDF
                    </a>
                @endif
            </div>

            <div class="p-4">
                <div class="row mb-4">
                    <div class="col-md-7">
                        <div class="text-muted small fw-bold mb-1" style="font-size: 9px; color: #64748b;">BILL DETAILS @if($bill && $bill->deleted_at)<span class="badge bg-danger ms-2">DELETED</span>@endif</div>
                        <h6 class="text-dark fw-bold mb-1" style="font-size: 16px;">NO: <span class="text-primary">{{ $billNo }}</span></h6>
                        <div class="text-muted small">Customer: <span class="fw-bold text-dark">{{ $customerName }}</span></div>
                    </div>
                    <div class="col-md-5 text-end">
                        <div class="text-muted small">Date: <span class="fw-bold text-dark">{{ $billDate ?? 'N/A' }}</span></div>
                        <div class="text-muted small mt-1">Due Date: <span class="fw-bold text-danger">{{ $dueDate ?? 'N/A' }}</span></div>
                    </div>
                </div>

                @php 
                    $displayItems = ($bill && $bill->items->count() > 0) ? $bill->items : ($log->properties['context']['items'] ?? []);
                @endphp

                @if(!empty($displayItems))
                <div class="table-responsive">
                    <table class="table table-sm table-bordered mb-2" style="font-size: 11px;">
                        <thead class="bg-light">
                            <tr>
                                <th class="text-muted py-2" style="font-size: 9px;">DESCRIPTION</th>
                                <th class="text-center text-muted py-2" style="font-size: 9px;">QTY</th>
                                <th class="text-center text-muted py-2" style="font-size: 9px;">RATE</th>
                                <th class="text-center text-muted py-2" style="font-size: 9px;">GST</th>
                                <th class="text-end text-muted py-2" style="font-size: 9px;">TOTAL</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($displayItems as $item)
                                @php 
                                    $iDesc = is_array($item) ? ($item['description'] ?? 'N/A') : $item->description;
                                    $iQty = is_array($item) ? ($item['qty'] ?? 0) : $item->qty;
                                    $iUnit = is_array($item) ? ($item['unit'] ?? 'Kgs') : $item->unit;
                                    $iRate = is_array($item) ? ($item['rate'] ?? 0) : $item->rate;
                                    $iGst = is_array($item) ? ($item['gst_percent'] ?? 0) : ($item->gst_percent ?? 0);
                                    $iTotal = is_array($item) ? ($item['total_amount'] ?? 0) : $item->total_amount;
                                @endphp
                                <tr>
                                    <td class="fw-bold text-dark py-2">{{ $iDesc }}</td>
                                    <td class="text-center py-2">{{ $iQty }} {{ $iUnit }}</td>
                                    <td class="text-center py-2">₹{{ number_format((float)$iRate, 2) }}</td>
                                    <td class="text-center py-2">{{ $iGst }}%</td>
                                    <td class="text-end fw-bold text-dark py-2">₹{{ number_format((float)$iTotal, 2) }}</td>
                                </tr>
                            @endforeach
                            <tr class="bg-light">
                                <td colspan="4" class="text-end fw-bold py-2" style="font-size: 10px;">GRAND TOTAL</td>
                                <td class="text-end fw-bold text-primary py-2" style="font-size: 12px;">₹{{ number_format((float)$grandTotal, 2) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                @else
                    <div class="alert alert-light border text-center py-3">
                        <span class="text-muted small">Summary: Bill Total ₹{{ number_format((float)$grandTotal, 2) }}</span>
                    </div>
                @endif

                <div class="mt-4 pt-2 border-top d-flex justify-content-between align-items-center small text-muted" style="font-size: 10px;">
                    <span><i class="fa fa-user me-1"></i>ACTION BY: <strong>{{ $log->causer->name ?? 'Admin' }}</strong></span>
                    <span><i class="fa fa-clock-o me-1"></i>{{ $log->created_at->format('d M, Y h:i A') }}</span>
                </div>
            </div>
        </div>
    @endif

    <!-- Voucher Log Block (Multi-Payment) -->
    @if($log->log_name == 'Voucher')
        @php 
            $entries = $ctx['entries'] ?? [];
            $totalAmount = $log->properties['attributes']['total_amount'] ?? 0;
            $voucherType = $log->properties['attributes']['type'] ?? 'Cr';
        @endphp

        <div class="activity-job-card shadow-sm border rounded p-0 mb-4 bg-white overflow-hidden">
            <div class="p-3 text-white d-flex justify-content-between align-items-center" style="background-color: #0d9488 !important;">
                <h6 class="mb-0 fw-bold" style="font-size: 13px;">
                    <i class="fa fa-money me-2"></i>MULTI-PAYMENT VOUCHER ({{ $voucherType == 'Cr' ? 'CREDIT' : 'DEBIT' }})
                </h6>
                <span class="badge bg-white text-dark fw-bold px-3 py-1" style="font-size: 10px;">{{ count($entries) }} ENTRIES</span>
            </div>

            <div class="p-4">
                <div class="text-muted small fw-bold mb-3" style="font-size: 9px; color: #64748b;">TRANSACTION DETAILS</div>
                
                @if(!empty($entries))
                <div class="table-responsive">
                    <table class="table table-sm table-bordered mb-2" style="font-size: 11px;">
                        <thead class="bg-light">
                            <tr>
                                <th class="text-muted py-2" style="font-size: 9px;">CUSTOMER</th>
                                <th class="text-center text-muted py-2" style="font-size: 9px;">DATE</th>
                                <th class="text-muted py-2" style="font-size: 9px;">REMARKS</th>
                                <th class="text-end text-muted py-2" style="font-size: 9px;">AMOUNT</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($entries as $ent)
                                <tr>
                                    <td class="fw-bold text-dark py-2">{{ $ent['customer'] ?? 'N/A' }}</td>
                                    <td class="text-center py-2">{{ \Carbon\Carbon::parse($ent['date'])->format('d M, Y') }}</td>
                                    <td class="text-muted py-2 small">{{ $ent['remarks'] ?? 'N/A' }}</td>
                                    <td class="text-end fw-bold text-dark py-2">₹{{ number_format((float)($ent['amount'] ?? 0), 2) }}</td>
                                </tr>
                            @endforeach
                            <tr class="bg-light">
                                <td colspan="3" class="text-end fw-bold py-2" style="font-size: 10px;">TOTAL VOUCHER AMOUNT</td>
                                <td class="text-end fw-bold text-teal py-2" style="font-size: 12px; color: #0d9488;">₹{{ number_format((float)$totalAmount, 2) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                @endif

            </div>
        </div>
    @endif

    <!-- Ledger Followup Log Block -->
    @if($log->log_name == 'LedgerFollowup' || $log->log_name == 'LedgerFollowupHistory')
        @php 
            $subj = $log->subject;
            $followup_m = $log->log_name == 'LedgerFollowup' ? $subj : ($subj->followup ?? null);
            
            // Ensure we use the specific history item from the log, or the latest one if closed
            $history_item = ($log->log_name == 'LedgerFollowupHistory') ? $subj : 
                (($followup_m && $followup_m->status == 'Closed') ? $followup_m->histories()->latest('id')->first() : ($followup_m->activeHistory ?? null));
            $custName = $followup_m->customer->name ?? 'N/A';
            
            $isNew = ($log->log_name == 'LedgerFollowup' && $log->event == 'created');
            $isClosed = ($log->log_name == 'LedgerFollowup' && ($newV['status'] ?? ($followup_m->status ?? '')) == 'Closed');
            
            $headerColor = $isNew ? '#f59e0b' : ($isClosed ? '#10b981' : '#3b82f6');
            $headerText = $isNew ? 'NEW FOLLOWUP CREATED' : ($isClosed ? 'FOLLOWUP CLOSED & SETTLED' : 'FOLLOWUP INTERACTION');
        @endphp

        <div class="activity-job-card shadow-sm border rounded p-0 mb-4 bg-white overflow-hidden">
            <div class="p-3 text-white d-flex justify-content-between align-items-center" style="background-color: {{ $headerColor }} !important;">
                <h6 class="mb-0 fw-bold" style="font-size: 13px;">
                    <i class="fa {{ $isNew ? 'fa-plus-circle' : ($isClosed ? 'fa-check-circle' : 'fa-phone') }} me-2"></i>{{ $headerText }}
                </h6>
                <span class="badge bg-white text-dark fw-bold px-3 py-1" style="font-size: 10px;">{{ $isNew ? 'INITIAL' : ($isClosed ? 'FINAL' : 'IN-PROGRESS') }}</span>
            </div>

            <div class="p-4">
                <div class="row mb-4">
                    <div class="col-md-12">
                        <h5 class="fw-bold text-dark mb-1 text-uppercase">PAYMENT FOLLOWUP ({{ $custName }})</h5>
                        <div class="fw-bold text-success mb-2">STATUS: {{ ($newV['status'] ?? ($followup_m->status ?? '')) == 'Closed' ? 'Closed' : ($followup_m->status ?? 'Pending') }}</div>
                        <div class="text-muted fw-bold">
                            {!! nl2br(e($history_item->remarks ?? '')) !!}
                        </div>
                    </div>
                </div>

                @if(!$isClosed)
                    <div class="p-4 rounded-3 border bg-light shadow-inner mb-3">
                        <div class="text-muted small fw-bold mb-3 text-uppercase" style="font-size: 9px; letter-spacing: 1px;">Interaction Remarks & Next Steps</div>
                        
                        @if($history_item && $history_item->remarks)
                            <div class="text-dark fw-bold mb-4" style="font-size: 15px; line-height: 1.5; color: #1e293b !important;">
                                <i class="fa fa-quote-left me-2 text-muted opacity-50"></i>{!! nl2br(e($history_item->remarks)) !!}
                            </div>
                        @else
                            <div class="text-muted italic mb-4">No specific remarks recorded for this step.</div>
                        @endif
                        
                        <div class="row g-4 border-top pt-3">
                            <div class="col-md-6 text-center border-end">
                                <div class="text-muted extra-small text-uppercase mb-1" style="font-size: 8px;">Scheduled Date</div>
                                <div class="fw-bold text-primary" style="font-size: 14px;">
                                    <i class="fa fa-calendar-o me-1"></i> {{ date('d M, Y', strtotime($history_item->followup_date_time ?? ($followup_m->start_date ?? now()))) }}
                                    <div class="text-muted" style="font-size: 10px;">{{ date('h:i A', strtotime($history_item->followup_date_time ?? ($followup_m->start_date ?? now()))) }}</div>
                                </div>
                            </div>
                            @if($history_item && $history_item->complete_date_time)
                                <div class="col-md-6 text-center">
                                    <div class="text-muted extra-small text-uppercase mb-1" style="font-size: 8px;">Action Completed On</div>
                                    <div class="fw-bold text-success" style="font-size: 14px;">
                                        <i class="fa fa-check-circle-o me-1"></i> {{ date('d M, Y', strtotime($history_item->complete_date_time)) }}
                                        <div class="text-muted" style="font-size: 10px;">{{ date('h:i A', strtotime($history_item->complete_date_time)) }}</div>
                                    </div>
                                </div>
                            @else
                                 <div class="col-md-6 text-center">
                                    <div class="text-muted extra-small text-uppercase mb-1" style="font-size: 8px;">Current Status</div>
                                    <div class="fw-bold text-info" style="font-size: 14px;">
                                        <i class="fa fa-spinner fa-spin me-1"></i> WAITING
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif

                <div class="mt-4 pt-2 border-top d-flex justify-content-between align-items-center small text-muted" style="font-size: 10px;">
                    <span><i class="fa fa-user me-1"></i>RECORDED BY: <strong>{{ $log->causer->name ?? 'Admin' }}</strong></span>
                    <span><i class="fa fa-calendar me-1"></i>{{ $log->created_at->format('d M, Y h:i A') }}</span>
                </div>
            </div>
        </div>
    @endif
</div>

<style>
    .log-timeline { list-style: none; padding: 0; margin: 0; position: relative; }
    .log-timeline:before { top: 0; bottom: 0; position: absolute; content: " "; width: 2px; background-color: #e2e8f0; left: 20px; }
    .log-timeline-item { margin-bottom: 25px; position: relative; list-style: none; }
    .log-timeline-item:after { clear: both; content: ""; display: block; }
    .log-timeline-badge { color: #fff; width: 30px; height: 30px; line-height: 30px; font-size: 14px; text-align: center; position: absolute; top: 0; left: 6px; border-radius: 50%; z-index: 100; border: 2px solid #fff; }
    .log-timeline-panel { width: calc(100% - 60px); float: right; border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px; position: relative; background: #fff; box-sizing: border-box; }
    .badge-created-tl { background-color: #f59e0b !important; }
    .badge-updated-tl { background-color: #f59e0b !important; }
    .badge-deleted-tl { background-color: #ef4444 !important; }
    .active-panel { border-left: 4px solid #3b82f6 !important; }
</style>
