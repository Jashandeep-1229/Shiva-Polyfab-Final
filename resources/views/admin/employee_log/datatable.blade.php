<div class="table-responsive border rounded">
    <table class="table table-hover table-striped align-middle mb-0" id="basic-test">
        <thead class="bg-light">
            <tr>
                <th class="py-3 px-4">Time</th>
                <th class="py-3 px-4">Employee</th>
                <th class="py-3 px-4">Module</th>
                <th class="py-3 px-4">Action</th>
                <th class="py-3 px-4">Description</th>
                <th class="py-3 px-4">Changes</th>
            </tr>
        </thead>
            @php 
                $lastActionForSlip = []; 
                $lastFollowupId = [];
                
                // Pre-process logs to group ManageStock entries
                $consolidated = [];
                $skipIds = [];
                foreach($logs as $index => $log) {
                    if (in_array($log->id, $skipIds)) continue;
                    
                    if ($log->log_name == 'ManageStock' && $log->event == 'created') {
                        $batchIds = [$log->id];
                        $inOut = $log->properties['attributes']['in_out'] ?? 'in';
                        
                        // Look ahead for similar entries (same user, same time, same in_out)
                        for ($i = $index + 1; $i < count($logs); $i++) {
                            $check = $logs[$i];
                            if ($check->log_name == 'ManageStock' && 
                                $check->event == 'created' &&
                                $check->causer_id == $log->causer_id &&
                                ($check->properties['attributes']['in_out'] ?? '') == $inOut &&
                                $check->created_at->diffInSeconds($log->created_at) < 5) {
                                
                                $batchIds[] = $check->id;
                                $skipIds[] = $check->id;
                            }
                        }
                        
                        if (count($batchIds) > 1) {
                            $log->batch_count = count($batchIds);
                            $log->is_batch = true;
                            $log->batch_ids = $batchIds;
                        }
                    }

                    if ($log->log_name == 'CommonManageStock' && $log->event == 'created') {
                        $batchIds = [$log->id];
                        $inOut = $log->properties['attributes']['in_out'] ?? 'in';
                        
                        // Look ahead for similar entries (same user, same time, same in_out)
                        for ($i = $index + 1; $i < count($logs); $i++) {
                            $check = $logs[$i];
                            if ($check->log_name == 'CommonManageStock' && 
                                $check->event == 'created' &&
                                $check->causer_id == $log->causer_id &&
                                ($check->properties['attributes']['in_out'] ?? '') == $inOut &&
                                $check->created_at->diffInSeconds($log->created_at) < 5) {
                                
                                $batchIds[] = $check->id;
                                $skipIds[] = $check->id;
                            }
                        }
                        
                        if (count($batchIds) > 1) {
                            $log->batch_count = count($batchIds);
                            $log->is_batch = true;
                            $log->batch_ids = $batchIds;
                        }
                    }
                    $consolidated[] = $log;
                }
            @endphp
            @forelse($consolidated as $log)
                @php
                    // Skip noise models that are now consolidated into parent logs
                    if (in_array($log->log_name, ['BillItem', 'CustomerLedger', 'CustomerLedgerLog'])) {
                        continue;
                    }

                    // Skip the parent LedgerFollowup creation log as it's redundant with the first history record (which contains remarks)
                    if ($log->log_name == 'LedgerFollowup' && $log->event == 'created') {
                        continue;
                    }

                    // Skip LedgerFollowupHistory update logs (they just show technical status changes like 1 -> 0)
                    if ($log->log_name == 'LedgerFollowupHistory' && $log->event == 'updated') {
                        continue;
                    }

                    // Collapse consecutive same actions for Packing Slips
                    if ($log->log_name == 'PackingSlip' && $log->subject_id) {
                        $ctx = $log->properties['context'] ?? [];
                        $eventType = $ctx['event_type'] ?? 'unknown';
                        $slipId = $log->subject_id;
                        
                        // If we already showed a LATER action for this slip, and it's the SAME type, skip this older identical one
                        if (isset($lastActionForSlip[$slipId]) && $lastActionForSlip[$slipId] == $eventType) {
                            continue;
                        }
                        $lastActionForSlip[$slipId] = $eventType;
                    }


                    $oldVals = $log->properties['old'] ?? [];
                    $newVals = $log->properties['attributes'] ?? [];
                @endphp
                <tr>
                    <td class="px-4">
                        <div class="fw-bold text-dark">{{ $log->created_at->format('d M, Y') }}</div>
                        <div class="small text-muted">{{ $log->created_at->format('h:i A') }}</div>
                    </td>
                    <td class="px-4">
                        <div class="d-flex align-items-center">
                            <div class="avatar-sm me-2 bg-light rounded-circle d-flex align-items-center justify-content-center text-primary fw-bold" style="width: 32px; height: 32px;">
                                {{ substr($log->causer->name ?? 'S', 0, 1) }}
                            </div>
                            <span class="fw-bold">{{ $log->causer->name ?? 'System' }}</span>
                        </div>
                    </td>
                    <td class="px-4">
                        <span class="badge bg-light text-primary border border-primary px-2 py-1 small fw-bold">
                            {{ $log->log_name ?? 'General' }}
                        </span>
                    </td>
                    <td class="px-4">
                        @php
                            $ctx = $log->properties['context'] ?? [];
                            $eventType = $ctx['event_type'] ?? null;
                            
                            $eventClass = [
                                'created' => 'badge-created',
                                'updated' => 'badge-updated',
                                'deleted' => 'badge-deleted',
                            ][$log->event] ?? 'bg-info';
                            
                            $actionText = $log->event ?? 'Added';
                            
                            if ($eventType == 'bag_dispatched') {
                                $eventClass = 'bg-success';
                                $actionText = 'DISPATCHED';
                            } elseif ($eventType == 'bag_undo') {
                                $eventClass = 'bg-warning text-dark';
                                $actionText = 'REVERTED';
                            }

                            if(($ctx['type'] ?? '') == 'job_completed') {
                                $actionText = 'COMPLETED';
                                $eventClass = 'bg-primary';
                            }
                            
                            $badgeStyle = '';
                            if(in_array($log->log_name, ['ManageStock', 'CommonManageStock'])) {
                                $attrs = $log->properties['attributes'] ?? [];
                                $inOutType = strtoupper($attrs['in_out'] ?? 'IN');
                                $actionText = 'STOCK ' . $inOutType;
                                $badgeStyle = ($inOutType == 'IN') 
                                    ? 'background-color: #dcfce7 !important; color: #15803d !important; border: 1px solid #bbf7d0 !important;' 
                                    : 'background-color: #fee2e2 !important; color: #b91c1c !important; border: 1px solid #fecaca !important;';
                            }
                        @endphp
                        <span class="badge {{ empty($badgeStyle) ? $eventClass : '' }} fw-bold" style="font-size: 9px; letter-spacing: 0.5px; padding: 5px 10px; white-space: nowrap; {!! $badgeStyle !!}">
                            {{ strtoupper($actionText) }}
                        </span>
                    </td>
                    <td class="px-4">
                        @php
                            $props = $log->properties;
                            $oldP = $props['old']['job_card_process'] ?? null;
                            $newP = $props['attributes']['job_card_process'] ?? null;
                        @endphp
                        
                        @if($oldP && $newP)
                            <div class="small fw-bold text-dark mb-1">
                                <span class="text-secondary opacity-75">{{ $oldP }}</span> 
                                <i class="fa fa-long-arrow-right mx-1 text-primary"></i> 
                                <span class="text-primary">{{ $newP }}</span>
                            </div>
                            @if(($ctx['type'] ?? '') == 'job_completed')
                                <div class="small fw-bold text-dark mb-0">
                                    {{ $ctx['job_name'] ?? 'Job' }} (COMPLETED)
                                </div>
                                <div class="text-muted small" style="font-size: 10px;">
                                    {{ $log->subject->customer_agent->name ?? ($ctx['ledger']['remarks'] ?? 'N/A') }} 
                                    <span class="ms-1 px-1 rounded bg-light border text-success fw-bold">₹{{ number_format($ctx['grand_total'] ?? 0, 2) }}</span>
                                </div>
                            @endif
                            <div class="small text-muted" style="font-size: 10px;">{{ $log->description }}</div>
                        @else
                            @if($log->log_name == 'JobCard')
                                <div class="small fw-bold text-dark mb-0">
                                    {{ $ctx['job_name'] ?? ($ctx['job_no'] ?? 'Job') }} (COMPLETED)
                                </div>
                                <div class="text-muted small" style="font-size: 10px;">
                                    {{ $log->subject->customer_agent->name ?? ($ctx['ledger']['remarks'] ?? 'N/A') }} 
                                    <span class="ms-1 px-1 rounded bg-light border text-success fw-bold">₹{{ number_format($ctx['grand_total'] ?? 0, 2) }}</span>
                                </div>
                            @elseif($log->log_name == 'Bill')
                                @php 
                                    $billData = $log->properties['attributes'] ?? ($log->properties['old'] ?? []);
                                    // Try to find customer name
                                    $custName = 'N/A';
                                    if(isset($billData['customer_id'])) {
                                        $custName = \DB::table('agent_customers')->where('id', $billData['customer_id'])->value('name') ?? 'N/A';
                                    }
                                    $oldTotal = $ctx['old_total'] ?? null;
                                @endphp
                                <div class="small fw-bold text-dark mb-0">
                                    BILL {{ strtoupper($log->description) }}: {{ $billData['bill_no'] ?? 'N/A' }}
                                </div>
                                <div class="text-muted small" style="font-size: 10px;">
                                    {{ $custName }} 
                                    @if($oldTotal && $log->description == 'updated')
                                        <span class="ms-1 px-1 rounded bg-light border text-danger opacity-75 fw-normal" style="text-decoration: line-through;">₹{{ number_format($oldTotal, 2) }}</span>
                                        <i class="fa fa-long-arrow-right mx-1 text-primary"></i> 
                                    @endif
                                    <span class="ms-1 px-1 rounded bg-light border text-success fw-bold">₹{{ number_format($billData['grand_total'] ?? 0, 2) }}</span>
                                </div>
                            @elseif($log->log_name == 'Voucher')
                                @php
                                    $vType = $newVals['type'] ?? 'Cr';
                                    $vColor = $vType == 'Dr' ? 'text-danger' : 'text-success';
                                    $vLabel = $vType == 'Dr' ? 'DEBIT' : 'CREDIT';
                                @endphp
                                <div class="small fw-bold text-dark mb-0">
                                    VOUCHER ({{ $vLabel }})
                                </div>
                                <div class="text-muted small" style="font-size: 10px;">
                                    {{ $newVals['count'] ?? 0 }} Entries | <span class="{{ $vColor }} fw-bold">Total: ₹{{ number_format($newVals['total_amount'] ?? 0, 2) }}</span>
                                </div>
                            @elseif($log->log_name == 'LedgerFollowup' || $log->log_name == 'LedgerFollowupHistory')
                                @php
                                    $subj = $log->subject;
                                    $custName = 'N/A';
                                    $statusLabel = 'NEW';
                                    $remarks = '';
                                    
                                    if($log->log_name == 'LedgerFollowup') {
                                        $custName = $subj->customer->name ?? 'N/A';
                                        $statusLabel = strtoupper($newVals['status'] ?? ($subj->status ?? 'UPDATED'));
                                        // Fetch latest remark for the parent log display
                                        $remarks = \DB::table('ledger_followup_histories')
                                            ->where('followup_id', $subj->id)
                                            ->orderBy('id', 'desc')
                                            ->value('remarks') ?? '';
                                    } else {
                                        $parent = $subj->followup ?? null;
                                        $custName = $parent->customer->name ?? 'N/A';
                                        $remarks = $subj->remarks ?? '';
                                        
                                        if($log->event == 'created') {
                                            $isFirst = \DB::table('ledger_followup_histories')
                                                ->where('followup_id', $subj->followup_id)
                                                ->orderBy('id', 'asc')
                                                ->value('id') == $subj->id;
                                            $statusLabel = $isFirst ? 'NEW' : 'CONTINUE';
                                        } else {
                                             if(($newVals['status'] ?? 1) == 0) {
                                                 $statusLabel = 'COMPLETED';
                                             } else {
                                                 $statusLabel = 'UPDATED';
                                             }
                                        }
                                    }

                                    // Force "Closed" if the status label is CLOSED or result of parent status
                                    $finalStatus = ($statusLabel == 'CLOSED' || $statusLabel == 'Closed') ? 'Closed' : $statusLabel;
                                @endphp
                                <div class="fw-bold text-dark mb-0" style="font-size: 13px;">
                                    PAYMENT FOLLOWUP ({{ $custName }})
                                </div>
                                <div class="text-muted small">
                                    <div class="fw-bold">STATUS: {{ $finalStatus }}</div>
                                    @if($remarks)
                                        <div class="mt-0 text-dark" style="font-size: 12px; line-height: 1.2;">
                                            {{ $remarks }}
                                        </div>
                                    @endif
                                </div>
                            @elseif(in_array($log->log_name, ['ManageStock', 'CommonManageStock']))
                                @php
                                    $attrs = $log->properties['attributes'] ?? [];
                                    $stockName = ($log->log_name == 'CommonManageStock') ? 'Common' : ucfirst($attrs['stock_name'] ?? 'Stock');
                                    $inOutType = strtoupper($attrs['in_out'] ?? 'IN');
                                    $count = $log->batch_count ?? 1;
                                @endphp
                                <div class="small fw-bold text-dark mb-0">
                                    {{ $stockName }} Stock {{ $inOutType == 'IN' ? 'In' : 'Out' }}
                                    @if($count > 1)
                                        <span class="text-primary">({{ $count }} entries)</span>
                                    @endif
                                </div>
                            @else
                                <div class="small text-dark fw-bold mb-1">{{ $log->description }}</div>
                            @endif
                            @if(!in_array($log->log_name, ['Bill', 'JobCard', 'Voucher', 'LedgerFollowup', 'LedgerFollowupHistory', 'ManageStock', 'CommonManageStock']))
                                <div class="small text-muted" style="font-size: 10px;">{{ $log->description }}</div>
                            @endif
                            @php 
                                $changes = array_intersect_key($newVals, $oldVals);
                            @endphp
                            @foreach($changes as $key => $newVal)
                                @if(in_array($log->log_name, ['Bill', 'PackingSlip', 'JobCard', 'LedgerFollowup', 'LedgerFollowupHistory'])) @break @endif
                                @if($key != 'job_card_process' && $key != 'items' && $oldVals[$key] != $newVal)
                                    <div class="f-10 text-muted">
                                        <span class="text-uppercase fw-bold" style="font-size: 8px;">{{ str_replace('_', ' ', $key) }}:</span>
                                        <span class="text-danger text-decoration-line-through">{{ is_array($oldVals[$key]) ? json_encode($oldVals[$key]) : $oldVals[$key] }}</span>
                                        <i class="fa fa-caret-right mx-1"></i>
                                        <span class="text-success fw-bold">{{ is_array($newVal) ? json_encode($newVal) : $newVal }}</span>
                                    </div>
                                @endif
                            @endforeach
                        @endif
                        
                    </td>
                    <td class="px-4">
                        <button type="button" class="btn btn-xs btn-outline-primary shadow-sm px-3" 
                                onclick="showLogDetails({{ $log->id }})">
                            <i class="fa fa-eye me-1"></i> View Logs
                        </button>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center py-5">
                        <div class="mb-2"><i class="fa fa-info-circle fa-2x text-light"></i></div>
                        <p class="text-muted fw-bold">No activity logs found for the selected filters.</p>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="d-flex justify-content-between align-items-center mt-4 px-2">
    <div class="text-muted small fw-bold">
        Showing {{ $logs->firstItem() ?? 0 }} to {{ $logs->lastItem() ?? 0 }} of {{ $logs->total() }} records
    </div>
    <div class="pages">
        {!! $logs->links('pagination::bootstrap-4') !!}
    </div>
</div>
