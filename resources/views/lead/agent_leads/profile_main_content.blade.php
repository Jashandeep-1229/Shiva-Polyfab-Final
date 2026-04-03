<div class="card mb-4 border-0 shadow-sm">
    <div class="card-header pb-0 border-bottom bg-white d-flex justify-content-between align-items-center">
        <h5 class="fw-bold mb-0">All Jobs For This Agent</h5>
        <div>
            @php $totalJobs = isset($allLeads) ? $allLeads->count() : 1; @endphp
            <span class="badge badge-light-primary me-2">{{ $totalJobs }} Total</span>
            <button class="btn btn-sm btn-primary" style="border-radius: 20px;" data-bs-toggle="modal" data-bs-target="#newJobModal">
                <i class="fa fa-plus me-1"></i> New Job
            </button>
        </div>
    </div>
    <div class="card-body">
        <div class="row g-3">
            @foreach($allLeads as $item)
            <div class="col-md-6 col-lg-4">
                @if($item->id == $lead->id)
                    {{-- Highlighted Current Card --}}
                    <div class="card shadow-none mb-0 p-3 h-100 position-relative {{ ($item->status && $item->status->slug == 'won') ? 'anim-won' : (($item->status && $item->status->slug == 'lost') ? 'anim-lost' : 'border-primary') }}" 
                         style="background-color: rgba(115, 102, 255, 0.03); border-width: 2px !important; border-radius: 15px; cursor: default;">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="badge badge-primary" style="font-size: 13px;">{{ $item->lead_no }}</span>
                            <span class="text-primary fw-bold f-12"><i class="fa fa-dot-circle-o"></i> CURRENT</span>
                        </div>
                        <h6 class="mb-1 fw-bold text-dark" style="font-size: 17px;">{{ $item->name_of_job ?? 'New Job' }}</h6>
                        <p class="text-muted mb-2" style="font-size: 14px;"><i class="fa fa-calendar-o me-1"></i> {{ $item->created_at->format('d M Y') }}</p>
                        <div class="d-flex justify-content-between align-items-center mt-auto pt-2">
                            <span class="badge" style="background-color: {{ $item->status->color ?? '#7366ff' }}; color: #fff; font-size: 12px;">{{ $item->status->name }}</span>
                            @if($item->status && !in_array($item->status->slug, ['won', 'lost']))
                                <button class="btn btn-xs btn-primary p-0 px-2 fw-600" style="font-size: 12px; border-radius: 20px; background-color: #7366ff;" onclick="event.stopPropagation(); openFollowupModal({{ $item->id }})">Quick Followup</button>
                            @endif
                        </div>
                    </div>
                @else
                    {{-- Switchable Card --}}
                    <div class="card shadow-none border mb-0 h-100 enquiry-card-hover {{ ($item->status && $item->status->slug == 'won') ? 'anim-won' : (($item->status && $item->status->slug == 'lost') ? 'anim-lost' : '') }}" 
                         onclick="loadLeadProfileContent({{ $item->id }})"
                         style="border-radius: 15px; background: #f8f9fa; border: 1px solid #e0e0e0 !important; cursor: pointer; transition: all 0.3s ease; padding: 1rem !important;">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="badge badge-light-primary" style="color: #7366ff !important; background: #eeebff !important; font-size: 12px;">{{ $item->lead_no }}</span>
                            <span class="text-muted fw-600" style="font-size: 12px;"><i class="fa fa-mouse-pointer me-1"></i> Switch</span>
                        </div>
                        <h6 class="mb-1 fw-bold text-secondary" style="font-size: 16px;">{{ $item->name_of_job ?? 'Job' }}</h6>
                        <p class="text-muted mb-2" style="font-size: 14px;"><i class="fa fa-calendar-o me-1"></i> {{ $item->created_at->format('d M Y') }}</p>
                        <div class="d-flex justify-content-between align-items-center mt-auto pt-2">
                            <span class="badge" style="background-color: {{ $item->status->color ?? '#999' }}; color: #fff; font-size: 12px;">{{ $item->status->name }}</span>
                            @if($item->status && !in_array($item->status->slug, ['won', 'lost']))
                                <button class="btn btn-xs btn-outline-success p-0 px-2 fw-600" style="font-size: 12px; border-radius: 20px;" onclick="event.stopPropagation(); openFollowupModal({{ $item->id }})">Quick Followup</button>
                            @endif
                        </div>
                    </div>
                @endif
            </div>
            @endforeach
        </div>
    </div>
</div>

<style>
    .enquiry-card-hover:hover {
        border-color: #7366ff !important;
        transform: translateY(-3px);
        box-shadow: 0 5px 15px rgba(115, 102, 255, 0.1) !important;
    }
</style>

<div class="card border-0 shadow-sm">
    <div class="card-header pb-0 border-bottom bg-white d-flex justify-content-between align-items-center">
        <h5 class="fw-bold">Agent Lead Activity Timeline <small class="text-muted fw-normal ms-2">({{ $lead->lead_no }})</small></h5>
        @php 
            $currentSortOrder = $lead->status->sort_order ?? 0;
            $previousStatuses = $statuses->where('sort_order', '<', $currentSortOrder)->whereNotIn('slug', ['won', 'lost']);
        @endphp
        @if($previousStatuses->count() > 0 && !in_array($lead->status->slug ?? '', ['won', 'lost']))
            <button class="btn btn-xs btn-outline-warning" style="font-size: 11px; border-radius: 20px;" data-bs-toggle="modal" data-bs-target="#rollbackModal">
                <i class="fa fa-undo me-1"></i> Back Step
            </button>
        @endif
    </div>
    <div class="card-body" id="timeline-container">
        @include('lead.agent_leads.timeline', ['lead' => $lead])
    </div>
</div>

<!-- Rollback Modal (Dynamic) -->
<div class="modal fade" id="rollbackModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content border-warning">
            <div class="modal-header bg-warning">
                <h5 class="modal-title text-white">Reverse Lead Stage (Rollback)</h5>
                <button class="btn-close" type="button" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted f-13 px-1 mb-3">Moving this lead back will reset its current progress stage to a previous one. No activity history or remarks will be deleted.</p>
                <div class="mb-3">
                    <label class="form-label fw-bold">Select Stage to Rollback To:</label>
                    <select id="rollback_status_id" class="form-select">
                        <option value="">Choose a previous stage...</option>
                        @foreach($previousStatuses ?? collect() as $ps)
                            <option value="{{ $ps->id }}">{{ $ps->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-light" type="button" data-bs-dismiss="modal">Cancel</button>
                <button class="btn btn-warning text-white" type="button" onclick="submitRollback()">Confirm & Rollback</button>
            </div>
        </div>
    </div>
</div>
