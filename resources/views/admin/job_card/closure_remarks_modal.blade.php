<form action="{{ route('job_card.update_process', $job_card->id) }}" method="post" id="remarksForm" class="modal-content border-0 shadow-lg">
    @csrf
    <input type="hidden" name="job_card_id" value="{{$job_card->id}}">
    <input type="hidden" name="job_card_process" value="Account Pending">
    <input type="hidden" name="next_process" value="Completed">

    <style>
        .remarks-modal .modal-header {
            background: #4b5563; /* Gray-600 */
            color: white;
            border-bottom: none;
        }
        .remarks-modal .btn-close { filter: brightness(0) invert(1); }
        .remarks-area {
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 15px;
            font-size: 0.95rem;
            width: 100%;
            min-height: 120px;
            transition: all 0.2s;
        }
        .remarks-area:focus {
            border: 1px solid #6366f1 !important;
            box-shadow: 0 0 0 0.2rem rgba(99, 102, 241, 0.1) !important;
            outline: none !important;
        }
        .info-pill {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 10px 15px;
            margin-bottom: 20px;
        }
    </style>

    <div class="remarks-modal">
        <div class="modal-header">
            <h5 class="modal-title d-flex align-items-center gap-2">
                <i class="fa fa-check-circle"></i>
                <span>Complete Without Billing - {{ $job_card->job_card_no ?? '' }}</span>
            </h5>
            <button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <div class="modal-body bg-white p-4">
            <div class="info-pill mb-4">
                <div class="small text-muted text-uppercase fw-bold mb-1">Job Name</div>
                <div class="fw-bold text-dark">{{ $job_card->name_of_job ?? 'N/A' }}</div>
                <div class="mt-2 small text-muted">Closing this job without a bill will mark it as Completed and move it to history without creating any financial ledger entries.</div>
            </div>

            <div class="form-group">
                <label class="form-label fw-bold mb-2">Closure Remarks <span class="text-danger">*</span></label>
                <textarea name="remarks" class="remarks-area" placeholder="Enter reason for closing without bill (e.g. FOC, Internal Use, Sample etc.)" required></textarea>
            </div>
        </div>

        <div class="modal-footer border-top-0 d-flex justify-content-between px-4 pb-4">
            <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-dark px-5" style="box-shadow: 0 4px 10px rgba(0,0,0,0.15); border-radius: 8px;">
                Confirm Completion
            </button>
        </div>
    </div>
</form>
