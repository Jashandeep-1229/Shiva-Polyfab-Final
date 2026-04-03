<form action="{{ route('job_card.update_process',$job_card->id) }}" method="post" id="updateForm" class="modal-content" enctype="multipart/form-data">
    @csrf
    <input type="hidden" name="job_card_id"  value="{{$job_card->id ?? 0}}">
    <input type="hidden" name="next_process"  value="{{$next_process ?? ''}}">
    <input type="hidden" name="job_card_process"  value="{{$job_card->job_card_process ?? ''}}">
    <div class="modal-header">
        <h4 class="modal-title" id="mySmallModalLabel">{{$next_process ?? ''}} Process - {{ $job_card->name_of_job ?? '' }} ({{ $job_card->job_card_no ?? '' }})</h4>
        <button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Close" data-bs-original-title="" title=""></button>
    </div>
    <div class="modal-body dark-modal">
        <div class="row">
            @if($job_card->job_card_process != 'Order List')
            <div class="col-md-12 form-group mb-3">
                <h6>File</h6>
                <input type="file" name="file"  class="form-control form-control-sm">
            </div>
            @endif
            <div class="col-md-12 form-group mb-3">
                 <h6>Remarks</h6>
                <textarea rows="3" name="remarks" value="" class="form-control form-control-sm" required></textarea>
            </div>
        </div>

    </div>
    <div class="modal-footer text-end">
        <button type="submit" id="update" class="btn btn-primary">Update</button>
    </div>
</form>
