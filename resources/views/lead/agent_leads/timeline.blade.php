<div class="timeline-wrapper">
    @php 
        $groupedFollowups = $lead->followups->groupBy('status_at_time_id');
        $allStatusIds = array_unique(array_merge($lead->followups->pluck('status_at_time_id')->filter()->toArray(), [$lead->status_id]));
        $statusIdsOrdered = collect($allStatusIds)->sortBy(function($sid) use ($groupedFollowups) {
            $first = $groupedFollowups->get($sid)?->sortBy('id')->first();
            return $first ? $first->id : 99999999;
        });
    @endphp

    @foreach($statusIdsOrdered as $sid)
        @php 
            $status = \App\Models\LeadStatus::find($sid);
            if(!$status) continue;
            $followups = $groupedFollowups->get($sid) ?? collect();
        @endphp
        <div class="timeline-item {{ ($status->slug == 'won') ? 'anim-won' : (($status->slug == 'lost') ? 'anim-lost' : '') }}">
            <div class="timeline-dot" style="background-color: {{ $status->color }}"></div>
            <div class="timeline-content">
                <div class="timeline-header d-flex justify-content-between align-items-center">
                    <div class="timeline-title">
                        {{ $status->name }}
                        @if($status->slug == 'won') 🥳 @elseif($status->slug == 'lost') 😔 @endif
                    </div>
                </div>

                @foreach($followups->whereNotNull('complete_date')->sortBy('id') as $f)
                    <div class="followup-entry mb-2" style="border-left: 3px solid #7366ff; background: #f9f9f9; padding: 10px; border-radius: 4px;">
                        <div class="f-12 d-flex justify-content-between">
                            <strong>{{ \Carbon\Carbon::parse($f->complete_date ?? $f->followup_date)->format('d M, Y h:i a') }}</strong>
                            <span class="text-muted">By: {{ $f->adder->name ?? 'System' }}</span>
                        </div>
                        <div class="f-13 mt-1">
                            {!! $f->remarks !!}
                        </div>
                        <div class="f-11 text-success mt-1">Recorded: {{ \Carbon\Carbon::parse($f->complete_date)->format('d M, Y') }}</div>
                    </div>
                @endforeach
            </div>
        </div>
    @endforeach
</div>
