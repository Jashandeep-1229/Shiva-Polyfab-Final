<style>
    .history-timeline-wrapper { position: relative; padding-left: 45px; margin-top: 15px; margin-bottom: 15px; }
    .history-timeline-wrapper::before { content: ''; position: absolute; left: 22px; top: 0; bottom: 0; width: 2px; background: #e6e9ed; }
    .history-timeline-item { position: relative; margin-bottom: 25px; }
    .history-timeline-dot { position: absolute; left: -31px; top: 5px; width: 16px; height: 16px; border-radius: 50%; border: 3px solid #fff; box-shadow: 0 0 0 2px #e6e9ed; background: #7366ff; z-index: 1; }
    .history-timeline-content { background: #fff; border-radius: 8px; padding: 15px; border: 1px solid #eef1f5; box-shadow: 0 2px 4px rgba(0,0,0,0.02); }
    .history-timeline-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px; border-bottom: 1px solid #f8f9fa; padding-bottom: 8px; }
    .history-timeline-title { font-size: 15px; font-weight: 700; color: #313131; text-transform: capitalize; }
    .history-timeline-date { font-size: 12px; color: #777; font-weight: 500; }
    .history-timeline-body { font-size: 13px; color: #555; line-height: 1.5; background: #f9fafb; border-radius: 6px; padding: 10px; border-left: 3px solid #7366ff; }
    .history-timeline-user { font-size: 11px; color: #999; margin-top: 8px; font-weight: 600; }
</style>

<div class="modal-content">
    <div class="modal-header">
        <h5 class="modal-title">Lead Activity History: {{ $lead->lead_no }}</h5>
        <button class="btn-close" type="button" data-bs-dismiss="modal"></button>
    </div>
    <div class="modal-body" style="background-color: #fbfcfe;">
        <div class="history-timeline-wrapper">
            @foreach($lead->histories->sortByDesc('created_at') as $history)
            <div class="history-timeline-item">
                <div class="history-timeline-dot" style="background-color: {{ $history->type == 'created' ? '#51bb25' : ($history->type == 'transferred' ? '#f8d62b' : '#7366ff') }}"></div>
                <div class="history-timeline-content">
                    <div class="history-timeline-header">
                        <div class="history-timeline-title">
                            <i class="fa {{ $history->type == 'created' ? 'fa-plus-circle' : ($history->type == 'transferred' ? 'fa-exchange' : 'fa-edit') }} me-1"></i>
                            {{ str_replace('_', ' ', $history->type) }}
                        </div>
                        <div class="history-timeline-date">
                            {{ $history->created_at->format('d M, Y h:i A') }}
                        </div>
                    </div>
                    <div class="history-timeline-body">
                        {!! $history->description !!}
                    </div>
                    <div class="history-timeline-user">
                        <i class="fa fa-user-circle-o me-1"></i> Performed by: <span class="text-primary">{{ $history->user->name ?? 'System' }}</span>
                    </div>
                </div>
            </div>
            @endforeach
            
            @if($lead->histories->isEmpty())
                <div class="text-center p-5">
                    <div class="mb-3"><i class="fa fa-history fa-3x text-light"></i></div>
                    <h6 class="text-muted">No activity records found yet.</h6>
                </div>
            @endif
        </div>
    </div>
    <div class="modal-footer">
        <button class="btn btn-light" type="button" data-bs-dismiss="modal">Dismiss</button>
    </div>
</div>
