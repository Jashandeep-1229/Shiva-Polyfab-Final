<div class="bg-light p-2 rounded" style="border-left: 3px solid #2ecc71;">
    @if($overallFollowup)
        <div class="d-flex justify-content-between align-items-center">
            <small class="text-muted fw-bold">Next Action:</small>
            <span class="badge badge-success shadow-sm" style="font-size: 10px;">{{ \Carbon\Carbon::parse($overallFollowup->followup_date)->format('d M, Y') }}</span>
        </div>
        @if($overallFollowup->remarks)
            <div class="mt-2 p-1 bg-white rounded border">
                <small class="text-dark d-block" style="font-size: 11px; line-height: 1.2;">{{ Str::limit($overallFollowup->remarks, 80) }}</small>
            </div>
        @endif
    @else
        <div class="text-center py-2">
            <small class="text-muted"><i class="fa fa-info-circle me-1"></i> No pending followup</small>
        </div>
    @endif
    <div class="mt-2 border-top pt-1 text-center">
        <a href="javascript:void(0)" class="f-12 text-primary fw-600" onclick="viewOverallHistory()"><i class="fa fa-history me-1"></i> View Agent History</a>
    </div>
</div>
