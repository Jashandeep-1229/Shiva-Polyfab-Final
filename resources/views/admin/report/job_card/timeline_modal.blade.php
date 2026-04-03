<div class="modal-content">
    <div class="modal-header bg-primary text-white">
        <h5 class="modal-title f-w-600"><i class="fa fa-history me-2"></i>Order Journey: {{ $job_card->name_of_job }} ({{ $processes->count() }} Stages)</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
    </div>
    <div class="modal-body p-0">
        <!-- Job Header Summary -->
        <div class="p-3 border-bottom bg-light">
            <div class="row g-3">
                <div class="col-md-3">
                    <div class="d-flex align-items-center">
                        <div class="bg-primary-light p-2 rounded-circle me-3">
                            <i class="fa fa-user text-primary f-18"></i>
                        </div>
                        <div>
                            <small class="text-muted d-block f-10 fw-bold text-uppercase">Customer</small>
                            <span class="f-14 fw-bold text-dark">{{ $job_card->customer_agent->name ?? 'N/A' }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="d-flex align-items-center">
                        <div class="bg-warning-light p-2 rounded-circle me-3">
                            <i class="fa fa-calendar text-warning f-18"></i>
                        </div>
                        <div>
                            <small class="text-muted d-block f-10 fw-bold text-uppercase">Created Date</small>
                            <span class="f-14 fw-bold text-dark">{{ date('d M Y', strtotime($job_card->job_card_date)) }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="d-flex align-items-center">
                        <div class="bg-info-light p-2 rounded-circle me-3">
                            <i class="fa fa-truck text-info f-18"></i>
                        </div>
                        <div>
                            <small class="text-muted d-block f-10 fw-bold text-uppercase">Exp. Dispatch</small>
                            <span class="f-14 fw-bold text-dark">{{ $job_card->dispatch_date ? date('d M Y', strtotime($job_card->dispatch_date)) : '-' }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="d-flex align-items-center">
                        <div class="bg-success-light p-2 rounded-circle me-3">
                            <i class="fa fa-spinner text-success f-18 {{ $job_card->status == 'Completed' ? '' : 'fa-spin' }}"></i>
                        </div>
                        <div>
                            <small class="text-muted d-block f-10 fw-bold text-uppercase">Current Status</small>
                            <span class="badge {{ $job_card->status == 'Completed' ? 'bg-success' : 'bg-primary' }} f-12">{{ $job_card->status }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    <div class="timeline-scroll-container">
        <div class="timeline-wrapper p-3 pt-4">
            <div class="vertical-timeline">
                @php $prev_process_end = null; @endphp
                @forelse($processes as $index => $p)
                    @php
                        $icon = 'fa-circle-o';
                        $color = 'primary';
                        if(str_contains($p->process_name, 'Cylinder')) { $icon = 'fa-life-ring'; $color = 'info'; }
                        elseif(str_contains($p->process_name, 'Order')) { $icon = 'fa-list-alt'; $color = 'warning'; }
                        elseif(str_contains($p->process_name, 'Print')) { $icon = 'fa-print'; $color = 'secondary'; }
                        elseif(str_contains($p->process_name, 'Lamination')) { $icon = 'fa-clone'; $color = 'info'; }
                        elseif(str_contains($p->process_name, 'Cutting')) { $icon = 'fa-scissors'; $color = 'danger'; }
                        elseif(str_contains($p->process_name, 'Dispatch')) { $icon = 'fa-truck'; $color = 'success'; }
                        elseif(str_contains($p->process_name, 'Completed')) { $icon = 'fa-check-circle'; $color = 'success'; }

                        $diff_text = null;
                        if($prev_process_end && $p->created_at) {
                            $start = \Carbon\Carbon::parse($prev_process_end);
                            $end = \Carbon\Carbon::parse($p->created_at);
                            $diff_min = $end->diffInMinutes($start);
                            
                            if($diff_min > 0) {
                                $days = floor($diff_min / 1440);
                                $hours = floor(($diff_min % 1440) / 60);
                                $mins = $diff_min % 60;
                                
                                if($days > 0) $diff_text = $days.'d '.($hours > 0 ? $hours.'h' : '');
                                elseif($hours > 0) $diff_text = $hours.'h '.($mins > 0 ? $mins.'m' : '');
                                else $diff_text = $mins.'m';
                            }
                        }
                        $prev_process_end = $p->created_at;
                    @endphp
                    
                    <div class="timeline-block mb-2">
                        @if($diff_text)
                            <div class="time-diff-marker">
                                <span class="badge rounded-pill bg-white text-muted border shadow-sm">+ {{ $diff_text }}</span>
                            </div>
                        @endif

                        <div class="timeline-marker bg-{{ $color }} shadow-sm">
                            <i class="fa {{ $icon }} text-white"></i>
                        </div>
                        <div class="timeline-content border p-2 px-3 rounded bg-white shadow-xs position-relative">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <h6 class="text-{{ $color }} fw-bold mb-0 f-12">{{ $p->process_name }}</h6>
                                <div class="text-end">
                                    <small class="text-dark fw-bold f-10">{{ date('h:i A', strtotime($p->created_at)) }}</small>
                                    <small class="text-muted f-8 d-block">{{ date('d M, Y', strtotime($p->created_at)) }}</small>
                                </div>
                            </div>
                            
                            <div class="f-10 d-flex justify-content-between align-items-center opacity-75 mb-1">
                                <span><span class="text-muted">Managed by:</span> <span class="fw-bold text-dark">{{ $p->user->name ?? 'System' }}</span></span>
                                @if($p->total_time)
                                    <span class="text-info fw-bold"><i class="fa fa-clock-o me-1"></i>{{ $p->total_time }}h spent</span>
                                @endif
                            </div>

                            @if($p->result_remarks)
                            <div class="mt-1 pt-1 border-top border-light">
                                <div class="text-success f-10 fw-600 bg-success-light p-1 px-2 rounded-1">
                                    <i class="fa fa-check-circle me-1 text-success"></i>{{ $p->result_remarks }}
                                </div>
                            </div>
                            @endif

                            @if($p->actual_order || $p->wastage || $p->blockage_time)
                                <div class="mt-2 stats-bar d-flex justify-content-between f-9 text-uppercase tracking-tighter">
                                    <div class="stat-item"><span class="text-muted">Actual</span> <span class="fw-800">{{ number_format((float)$p->actual_order, 2) }}</span></div>
                                    <div class="stat-item"><span class="text-muted">Wastage</span> <span class="fw-800 text-danger">{{ number_format((float)$p->wastage, 2) }}</span></div>
                                    <div class="stat-item"><span class="text-muted">Prod</span> <span class="fw-800 text-primary">{{ number_format((float)$p->estimate_production, 2) }}</span></div>
                                    @if($p->blockage_time > 0)
                                    <div class="stat-item"><span class="text-muted">Blockage</span> <span class="fw-800 text-warning">{{ number_format((float)$p->blockage_time, 2) }}h</span></div>
                                    @endif
                                </div>
                            @endif

                            @if($p->file)
                                <div class="mt-2">
                                    <a href="{{ asset('uploads/job_card/' . $p->file) }}" target="_blank" class="d-inline-block border rounded-1 overflow-hidden shadow-xs">
                                        <img src="{{ asset('uploads/job_card/' . $p->file) }}" style="max-height: 40px; width: auto;" onerror="this.onerror=null; this.src='{{ asset('assets/images/dashboard/default.jpg') }}'">
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="text-center p-5 text-muted">
                        <i class="fa fa-history f-40 mb-2 opacity-25"></i>
                        <p class="f-12">No data recorded for this journey yet.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
    <div class="modal-footer bg-light border-top p-2 px-3">
        <button type="button" class="btn btn-secondary btn-sm px-4" data-bs-dismiss="modal">Close Window</button>
    </div>
</div>

<style>
    .timeline-scroll-container {
        max-height: 500px;
        overflow-y: auto;
        scrollbar-width: thin;
        scrollbar-color: #dee2e6 transparent;
    }
    .timeline-scroll-container::-webkit-scrollbar { width: 4px; }
    .timeline-scroll-container::-webkit-scrollbar-thumb { background: #dee2e6; border-radius: 10px; }

    .vertical-timeline { position: relative; padding-left: 45px; }
    .vertical-timeline::before {
        content: '';
        position: absolute;
        left: 0;
        top: 0;
        bottom: 0;
        width: 2px;
        background: #f1f3f5;
    }
    .timeline-block { position: relative; }
    .timeline-marker {
        position: absolute;
        left: -58px;
        top: 8px;
        width: 28px;
        height: 28px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 2;
        border: 3px solid #fff;
    }
    .timeline-marker i { font-size: 11px; }
    
    .time-diff-marker {
        position: absolute;
        left: -70px;
        top: -18px;
        width: 52px;
        display: flex;
        justify-content: center;
        z-index: 5;
    }
    .time-diff-marker .badge {
        font-size: 8px !important;
        padding: 2px 6px !important;
        color: #6c757d !important;
        font-weight: 700;
        letter-spacing: -0.2px;
    }

    .status-indicator {
        width: 6px;
        height: 6px;
        background: #dee2e6;
        border-radius: 50%;
    }

    .stats-bar {
        background: #fbfcfd;
        padding: 4px 8px;
        border-radius: 4px;
        border: 1px solid #f1f3f5;
    }
    .stat-item { display: flex; flex-direction: column; line-height: 1.2; }
    .stat-item span:first-child { font-size: 7px; color: #adb5bd; }

    .shadow-xs { box-shadow: 0 1px 2px rgba(0,0,0,0.03); }
    .f-8 { font-size: 8px !important; }
    .f-10 { font-size: 10px !important; }
    .f-11 { font-size: 11px !important; }
    .f-12 { font-size: 12px !important; }
    .fw-600 { font-weight: 600; }
    .fw-800 { font-weight: 800; }
    .bg-success-light { background-color: rgba(40, 167, 69, 0.05); }
    .tracking-tighter { letter-spacing: -0.02em; }
</style>

