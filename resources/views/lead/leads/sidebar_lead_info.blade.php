<ul class="lead-info-list">
    <li><label>Client Details</label><span>{{ $lead->name }}</span></li>
    <li><label>Phone</label><span>{{ $lead->phone }}</span></li>
    <li><label>City/State</label><span>{{ $lead->city ?? '-' }}, {{ $lead->state }}</span></li>
    <li><label>Lead Source</label><span>{{ $lead->source->name ?? 'N/A' }}</span></li>
    <li><label>Assigned To</label><span>{{ $lead->assignedUser->name ?? 'Unassigned' }}</span></li>
    <li><label>Requirement</label><span>{{ $lead->regarding ?? 'N/A' }}</span></li>
    <li><label>Created By</label><span>{{ $lead->addedBy->name ?? 'Admin' }}</span></li>
    @if($lead->status && $lead->status->slug == 'won')
        @php
            $wonHistory = $lead->histories->where('type', 'step_changed')->filter(function ($h) {
                return strpos($h->description, 'Won') !== false;
            })->first();
            $conversionDays = $wonHistory ? $lead->created_at->diffInDays($wonHistory->created_at) : 0;
        @endphp
        <hr style="margin: 8px 0; border-top: 1px dashed #ccc;">
        <li>
            <label class="text-success"><i class="fa fa-trophy"></i> Conversion Time</label>
            <span class="text-success"><strong>{{ $conversionDays }} Days</strong></span>
        </li>
        <li>
            <label class="text-success"><i class="fa fa-clipboard"></i> Order No</label>
            <span>
                @if($lead->order_no)
                    <span class="badge badge-success">{{ $lead->order_no }}</span>
                    @if(strtolower(Auth::user()->role) == 'admin')
                        <a href="javascript:void(0)" class="ms-1 px-1 py-0 btn btn-xs btn-outline-info" onclick="setJobCardField('{{ $lead->order_no }}')" title="Admin Edit"><i class="fa fa-pencil"></i></a>
                     @endif
                 @else
                     <span class="badge badge-warning text-dark">Pending</span>
                     @if(strtolower(Auth::user()->role) == 'admin')
                        <a href="javascript:void(0)" class="ms-2 f-12 btn btn-primary btn-xs p-1" onclick="setJobCardField('')">Link Now</a>
                     @endif
                 @endif
            </span>
        </li>
    @endif
    
    @if($lead->status && $lead->status->slug == 'lost')
        <hr style="margin: 8px 0; border-top: 1px dashed #ccc;">
        <li>
            <label class="text-danger"><i class="fa fa-times-circle"></i> Reason for Lost</label>
            <span class="text-danger"><strong>{{ $stepData['lost_reason'] ?? 'Not Specified' }}</strong></span>
        </li>
    @endif
</ul>
