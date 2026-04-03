@if($lead->status && !in_array($lead->status->slug, ['won', 'lost']))
    <div class="d-flex gap-2">
        <button class="btn btn-primary btn-sm w-100" onclick="openFollowupModal({{ $lead->id }})">
            <i class="fa fa-comments-o me-1"></i> Log Activity
        </button>
    </div>
@endif
