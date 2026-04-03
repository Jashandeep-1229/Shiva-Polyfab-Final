<div class="accordion accordion-flush" id="timelineAccordion">
    @php 
        $followupStatusIds = $lead->followups->pluck('status_at_time_id')->unique()->toArray();
        $allStatusIds = array_unique(array_merge($followupStatusIds, [$lead->status_id]));
        $groupedFollowups = $lead->followups->groupBy('status_at_time_id');
        $statusHistoryIds = collect($allStatusIds)->filter()->sortBy(function($sid) use ($groupedFollowups) {
            $first = $groupedFollowups->get($sid)?->sortBy('id')->first();
            return $first ? $first->id : 999999999;
        });
    @endphp

    @foreach($statusHistoryIds as $sid)
        @php 
            $status = \App\Models\LeadStatus::find($sid);
            if(!$status) continue;
            $followups = $groupedFollowups->get($sid) ?? collect();
            $isCurrent = ($lead->status_id == $sid && !in_array($status->slug, ['won', 'lost']));
        @endphp
        <div class="accordion-item mb-3 border rounded shadow-sm">
            <h2 class="accordion-header" id="heading{{ $sid }}">
                <button class="accordion-button collapsed p-3" type="button" data-bs-toggle="collapse" data-bs-target="#collapse{{ $sid }}" aria-expanded="false" aria-controls="collapse{{ $sid }}">
                    <div class="d-flex justify-content-between align-items-center w-100 me-3">
                        <div class="d-flex align-items-center">
                            <div class="rounded-circle me-3" style="width: 12px; height: 12px; background-color: {{ $status->color ?? '#7366ff' }}"></div>
                            <span class="fw-bold">{{ $status->name }}</span>
                            @if($isCurrent)
                                <span class="badge badge-primary ms-3 pulse-current">Active Stage</span>
                            @endif
                        </div>
                        <div class="d-flex align-items-center">
                            <small class="text-muted"><i class="fa fa-history me-1"></i> {{ $followups->count() }} Actions</small>
                        </div>
                    </div>
                </button>
            </h2>
            <div id="collapse{{ $sid }}" class="accordion-collapse collapse" aria-labelledby="heading{{ $sid }}" data-bs-parent="#timelineAccordion">
                <div class="accordion-body bg-white py-3">
                    {{-- Followup History for this Status --}}
                    <div class="followup-list mb-1">
                        @if($status->slug == 'lost')
                            <div class="followup-entry mb-2 border-start border-3 border-danger p-2 rounded bg-light shadow-sm" style="border-left-color: #dc3545 !important;">
                                <div class="followup-meta d-flex justify-content-between f-12 text-danger">
                                    <span class="fw-700"><i class="fa fa-times-circle me-1"></i> WHY IS THIS LEAD LOST?</span>
                                </div>
                                <div class="followup-remarks f-14 mt-2 ps-3 text-dark fw-bold italic">
                                    "{{ $stepData['lost_reason'] ?? 'Reason not provided.' }}"
                                </div>
                            </div>
                        @endif

                        @foreach($followups->sortBy('id') as $f)
                            @if(in_array($status->slug, ['won', 'lost']) && !$f->complete_date) @continue @endif
                            <div class="followup-entry mb-2 {{ !$f->complete_date ? 'pending-followup' : '' }} border-start border-3 p-2 rounded bg-light">
                                <div class="followup-meta d-flex justify-content-between f-12">
                                    <span class="fw-600">
                                        <i class="fa {{ !$f->complete_date ? 'fa-clock-o text-warning' : 'fa-phone-square text-primary' }} me-1"></i> 
                                        {{ $f->type ?? 'Call' }} — {{ \Carbon\Carbon::parse($f->followup_date)->format('d M, Y h:i a') }}
                                    </span>
                                    @if($f->complete_date)
                                        <span class="text-muted">Done: {{ \Carbon\Carbon::parse($f->complete_date)->format('d M, Y') }}</span>
                                    @endif
                                </div>
                                <div class="followup-remarks f-13 mt-1 ps-3">
                                    {!! $f->remarks ?? 'No remarks provided.' !!}
                                </div>
                            </div>
                        @endforeach
                        @if($followups->isEmpty() && $status->slug != 'lost')
                            <p class="text-muted f-12 text-center my-3 italic">No communication logged in this stage yet.</p>
                        @endif
                    </div>

                    {{-- Local Action Button --}}
                    @if($isCurrent)
                        <div class="border-top pt-3">
                            <button class="btn btn-primary btn-sm w-100" onclick="openFollowupModal({{ $lead->id }})">
                                <i class="fa fa-plus-circle me-1"></i> Update Followup or Next Stage
                            </button>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endforeach
</div>

<script>
    function toggleLocalFollowup(leadId, sid) {
        const container = $(`#local-followup-form-${leadId}-${sid}`);
        if (container.is(':visible')) {
            container.slideUp();
        } else {
            container.slideDown();
            // Load official form if empty
            if (container.find('form').length === 0) {
                $.get(`{{ url('lead/leads/followup-modal') }}/${leadId}`, function(html) {
                    // Inject raw HTML which includes the script
                    container.html(html);
                    
                    // Cleanup modal-specific classes so it looks clean inline
                    const $form = container.find('form');
                    container.find('.modal-content').removeClass('modal-content');
                    container.find('.modal-header').hide();
                    container.find('.modal-footer').removeClass('modal-footer').addClass('mt-3 text-end').find('.btn-secondary').hide();
                });
            }
        }
    }
</script>

<style>
    .pending-followup {
        background-color: #fff9f0;
        border-left: 3px solid #ffaa00 !important;
        opacity: 0.9;
    }
    .badge-light-warning {
        background-color: #fff4e5;
        font-size: 10px;
        padding: 2px 6px;
    }
    .badge-light-danger {
        background-color: #ffeef0;
        font-size: 10px;
        padding: 2px 6px;
    }

    /* Additional Timeline Flair */
    .timeline-item {
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .timeline-item:hover {
        transform: translateX(5px);
    }
    .timeline-item.anim-won:hover {
        transform: scale(1.02) translateX(5px);
        box-shadow: 0 5px 15px rgba(46, 204, 113, 0.2);
    }

    @keyframes emoji-bounce {
        0%, 100% { transform: translateY(0) rotate(0); }
        50% { transform: translateY(-5px) rotate(5deg); }
    }
    .emoji-bounce {
        animation: emoji-bounce 2s infinite ease-in-out;
    }

    @keyframes emoji-shake {
        0%, 100% { transform: translateX(0); }
        25% { transform: translateX(-1px) rotate(-2deg); }
        75% { transform: translateX(1px) rotate(2deg); }
    }
    .emoji-shake {
        animation: emoji-shake 1.5s infinite ease-in-out;
    }

    @keyframes slow-glow-won-timeline {
        0% { border-color: #2ecc71; box-shadow: 0 0 5px rgba(46, 204, 113, 0.1); }
        50% { border-color: #27ae60; box-shadow: 0 0 12px rgba(46, 204, 113, 0.3); }
        100% { border-color: #2ecc71; box-shadow: 0 0 5px rgba(46, 204, 113, 0.1); }
    }

    .anim-won {
        border-color: #2ecc71 !important;
        animation: slow-glow-won-timeline 4s infinite ease-in-out !important;
    }

    @keyframes pulse-current {
        0% { box-shadow: 0 0 0 0 rgba(115, 102, 255, 0.4); }
        70% { box-shadow: 0 0 0 10px rgba(115, 102, 255, 0); }
        100% { box-shadow: 0 0 0 0 rgba(115, 102, 255, 0); }
    }
    .pulse-current {
        animation: pulse-current 2s infinite;
    }
</style>

