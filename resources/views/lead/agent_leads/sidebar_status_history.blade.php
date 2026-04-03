<div class="sidebar-timeline" style="position: relative; padding-left: 20px;">
    <div style="position: absolute; left: 4px; top: 0; bottom: 0; width: 2px; background: #eee;"></div>
    @php
        $statusChanges = $history->sortBy('id')->values();
    @endphp
    @foreach($statusChanges as $index => $hist)
        @php
            $isWon = strpos(strtolower($hist->description), 'won') !== false;
            $isLost = strpos(strtolower($hist->description), 'lost') !== false;
        @endphp
        <div class="sidebar-timeline-item mb-4" style="position: relative;">
            <div class="sidebar-timeline-dot" style="position: absolute; left: -20px; top: 4px; width: 10px; height: 10px; border-radius: 50%; background: #7366ff; border: 2px solid #fff; box-shadow: 0 0 0 1px #7366ff;"></div>
            <div class="sidebar-timeline-content">
                <div class="f-11 text-muted">{{ \Carbon\Carbon::parse($hist->created_at)->format('d M, Y h:i A') }}</div>
                <div class="f-13" style="{{ $isWon ? 'color: #2ecc71; font-weight: bold;' : ($isLost ? 'color: #e74c3c; font-weight: bold;' : '') }}">
                    {{ $hist->description }}
                </div>
                <div class="f-10 text-muted mt-1">Processed by: {{ $hist->user->name ?? 'System' }}</div>
            </div>
        </div>
    @endforeach
    @if($statusChanges->isEmpty())
        <p class="text-muted f-11">Initial setup</p>
    @endif
</div>
