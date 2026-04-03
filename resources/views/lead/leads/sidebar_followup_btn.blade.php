@if($lead->status && !in_array($lead->status->slug, ['won', 'lost']))
    <button class="btn btn-primary btn-sm w-100" style="background-color: #7366ff;" onclick="openFollowupModal({{ $lead->id }})">
        <i class="fa fa-exchange me-1"></i> Continue Follow Up
    </button>
@endif
