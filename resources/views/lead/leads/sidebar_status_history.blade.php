<div class="sidebar-timeline">
    @php
        $statusChanges = $lead->histories->where('type', 'step_changed')->sortBy('created_at')->values();
    @endphp
    @foreach($statusChanges as $index => $hist)
        @php
            $daysDiff = 0;
            if ($index > 0) {
                $prevDate = \Carbon\Carbon::parse($statusChanges[$index - 1]->created_at);
                $currDate = \Carbon\Carbon::parse($hist->created_at);
                $daysDiff = $prevDate->diffInDays($currDate);
            }
        @endphp
        @php
            $isWon = strpos(strtolower($hist->description), 'won') !== false;
            $isLost = strpos(strtolower($hist->description), 'lost') !== false;
        @endphp
        <div class="sidebar-timeline-item {{ $isWon ? 'anim-won px-1 rounded' : ($isLost ? 'anim-lost px-1 rounded' : '') }}">
            <div class="sidebar-timeline-dot {{ $isWon ? 'bg-success' : ($isLost ? 'bg-danger' : '') }}"></div>
            <div class="sidebar-timeline-content">
                <div class="sidebar-timeline-meta">
                    <strong>{{ \Carbon\Carbon::parse($hist->created_at)->format('d M, Y h:i A') }}</strong>
                </div>
                <p>
                    @if($isWon) <span class="d-inline-block emoji-bounce">🥳</span> @elseif($isLost) <span class="d-inline-block emoji-shake">😔</span> @endif
                    {!! $hist->description !!}
                </p>
                <div class="d-flex justify-content-between align-items-center mt-2 mb-1">
                    <span class="f-10 text-muted border px-2 py-1 rounded bg-light"><i class="fa fa-user me-1"></i> {{ $hist->user->name ?? 'System' }}</span>
                    @if($index > 0)
                        <span class="badge {{ $daysDiff > 3 ? 'badge-light-danger text-danger' : 'badge-light-warning text-warning' }} f-10">{{ $daysDiff }} {{ \Str::plural('Day', $daysDiff) }} Later</span>
                    @else
                        <span class="badge badge-light-success text-success f-10">Initial Stage</span>
                    @endif
                </div>
            </div>
        </div>
    @endforeach
    @if($statusChanges->isEmpty())
        <p class="text-muted f-12 mt-3">No status changes recorded yet.</p>
    @endif
</div>
