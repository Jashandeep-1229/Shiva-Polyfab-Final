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
            <div class="col-md-3 form-group mb-3">
                <h6>Date</h6>
                <input type="date" value="{{date('Y-m-d')}}" name="date"  class="form-control form-control-sm">
            </div>
            <div class="col-md-3 form-group mb-3">
                <h6>Estimate Production</h6>
                <input type="number" step="any" name="estimate_production" id="estimate_production"  class="form-control form-control-sm" value="" required>
            </div>
            <div class="col-md-3 form-group mb-3">
                <h6>Actual Printed Production</h6>
                <input type="number" step="any" name="actual_order" id="actual_order"  class="form-control form-control-sm" value="{{ $job_card->actual_pieces ?? '' }}" required readonly>
            </div>
            <div class="col-md-3 form-group mb-3">
                <h6>Wastage</h6>
                <input type="text" name="wastage" id="wastage"  class="form-control form-control-sm" value="" required>
            </div>
            <div class="col-md-2 form-group mb-3">
                <h6>Working Hours</h6>
                <input type="number" step="any" name="working_hours" id="working_hours"  class="form-control form-control-sm" value="" required>
            </div>
             <div class="col-md-2 form-group mb-3">
                <h6>Day/Night</h6>
                <div class="input-group input-group-sm">
                    <select name="shift_time" id="shift_time" class="form-select form-control-sm" required>
                        <option value="" disabled selected>Select Day/Night</option>
                        <option value="Day">Day</option>
                        <option value="Night">Night</option>
                    </select>
                </div>
            </div>
            <div class="col-md-3 form-group mb-3">
                <h6>Machine</h6>
                <div class="input-group input-group-sm">
                    <select name="machine_id" id="machine_id" class="form-select form-control-sm" required>
                        <option value="" disabled selected>Select Machine</option>
                        @foreach($machines as $machine)
                            <option value="{{ $machine->id }}">{{ $machine->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
           
            <div class="col-md-3 form-group mb-3">
                <h6>Blockage Reason</h6>
                <div class="input-group input-group-sm">
                    <select name="blockage_reason_id" id="blockage_reason_id" class="form-select form-control-sm" required>
                        <option value="" selected disabled>Select Blockage Reason</option>

                        @foreach($block_reasons as $blockage_reason)
                            <option value="{{ $blockage_reason->id }}">{{ $blockage_reason->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-md-2 form-group mb-3">
                <h6>Blockage Time</h6>
                <input type="number" step="any" name="blockage_time" id="blockage_time" class="form-control form-control-sm" value="0" required>
            </div>
            <div class="col-md-12 form-group mb-3">
                 <h6>Remarks</h6>
                    <textarea rows="3" name="remarks" value="" class="form-control form-control-sm"></textarea>
            </div>
        </div>

    </div>
    <div class="modal-footer text-end">
        <button type="submit" id="update" class="btn btn-primary">Update</button>
    </div>
</form>
