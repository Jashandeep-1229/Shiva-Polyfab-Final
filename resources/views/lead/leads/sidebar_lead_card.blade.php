<div class="text-center mb-4">
    <div class="avatar-wrapper mb-3">
        <img src="{{ asset('assets/images/user/7.jpg') }}" alt="" class="rounded-circle" width="80">
    </div>
    <h5 class="mb-1">{{ $lead->lead_no }}</h5>
    <div class="status-badge-pipeline {{ ($lead->status && $lead->status->slug == 'won') ? 'anim-won' : (($lead->status && $lead->status->slug == 'lost') ? 'anim-lost' : '') }}">
        <i class="fa fa-circle"></i> 
        <span id="sidebar-status-name">
            @if($lead->status && $lead->status->slug == 'won') 🥳 @elseif($lead->status && $lead->status->slug == 'lost') 😔 @endif
            {{ $lead->status->name }}
        </span> 
    </div>
    @if($lead->status && !in_array($lead->status->slug, ['won', 'lost']))
    <div class="mt-3">
        <button class="btn btn-primary btn-sm w-100" style="background-color: #7366ff;" onclick="openFollowupModal({{ $lead->id }})">
            <i class="fa fa-exchange me-1"></i> Continue Follow Up
        </button>
    </div>
    @endif
</div>

<ul class="lead-info-list" id="lead_info_list">
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
                return strpos(strtolower($h->description), 'won') !== false;
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
                    @if(strtolower(Auth::user()->role ?? '') == 'admin')
                        <a href="javascript:void(0)" class="ms-1 px-1 py-0 btn btn-xs btn-outline-info" onclick="setJobCardField('{{ $lead->order_no }}')" title="Admin Edit"><i class="fa fa-pencil"></i></a>
                    @endif
                @else
                    <span class="badge badge-warning text-dark">Pending</span>
                    @if(strtolower(Auth::user()->role ?? '') == 'admin')
                        <a href="javascript:void(0)" class="ms-2 f-12 btn btn-primary btn-xs p-1" onclick="setJobCardField('')">Link Now</a>
                    @endif
                @endif
            </span>
        </li>
    @endif
</ul>
