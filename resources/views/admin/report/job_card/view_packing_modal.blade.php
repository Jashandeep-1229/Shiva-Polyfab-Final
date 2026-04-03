<div class="modal-content">
    <div class="modal-header bg-info text-white">
        <h5 class="modal-title"><i class="fa fa-truck me-2"></i>Packing Slips: {{ $job_card->name_of_job }}</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
    </div>
    <div class="modal-body p-0">
        <div class="p-4">
            @php $slips = $job_card?->packing_slips; @endphp
            @if($slips && $slips->count() > 0)
                @if($slips->count() > 1)
                    <div class="row">
                        <div class="col-md-3 border-end">
                            <div class="nav flex-column nav-pills" id="v-pills-tab-packing" role="tablist" aria-orientation="vertical">
                                @foreach($slips as $index => $slip)
                                    <button class="nav-link text-start mb-2 py-2 px-3 f-12 fw-bold {{ $index == 0 ? 'active' : '' }}" id="v-pills-slip-{{ $slip->id }}-tab" data-bs-toggle="pill" data-bs-target="#v-pills-slip-{{ $slip->id }}" type="button" role="tab">
                                        <i class="fa fa-file-text-o me-2"></i>Slip #{{ $slip->packing_slip_no }}
                                    </button>
                                @endforeach
                            </div>
                        </div>
                        <div class="col-md-9">
                            <div class="tab-content" id="v-pills-tabContent-packing">
                                @foreach($slips as $index => $slip)
                                    <div class="tab-pane fade {{ $index == 0 ? 'show active' : '' }}" id="v-pills-slip-{{ $slip->id }}" role="tabpanel">
                                        @include('admin.report.job_card.partial_packing_slip_details', ['slip' => $slip])
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @else
                    @php $slip = $slips->first(); @endphp
                    @include('admin.report.job_card.partial_packing_slip_details', ['slip' => $slip])
                @endif
            @else
                <div class="text-center py-5 text-muted">
                    <div class="mb-3">
                        <i class="fa fa-truck f-50 opacity-25"></i>
                    </div>
                    <h5 class="f-16 fw-bold">No Packing Slips Found</h5>
                    <p class="f-13">Dispatch records haven't been generated for this order yet.</p>
                </div>
            @endif
        </div>
    </div>
    <div class="modal-footer bg-light p-2">
        <button type="button" class="btn btn-secondary btn-sm px-4" data-bs-dismiss="modal">Close</button>
    </div>
</div>
