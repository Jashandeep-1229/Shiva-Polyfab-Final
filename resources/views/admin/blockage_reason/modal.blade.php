<form action="{{ route('blockage_reason.store') }}" method="post" id="updateForm" class="modal-content" enctype="multipart/form-data">
    @csrf
    <input type="hidden" name="blockage_reason_id"  value="{{$blockage_reason->id ?? 0}}">
    <input type="hidden" name="type"  value="{{$blockage_reason->type ?? 0}}">
    <div class="modal-header">
        <h4 class="modal-title" id="mySmallModalLabel">{{($blockage_reason->id ?? 0) ? 'Edit' : 'Add'}} Blockage Reason Master</h4>
        <button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Close" data-bs-original-title="" title=""></button>
    </div>
    <div class="modal-body dark-modal">
        <div class="row">
            <div class="col-md-12 form-group mb-3">
                <h6>Name</h6>
                <input type="text" name="name" value="{{$blockage_reason->name ?? ''}}" oninput="this.value = this.value.toUpperCase()" class="form-control form-control-sm" required>
            </div>
        </div>

    </div>
    <div class="modal-footer text-end">
        <button type="submit" id="update" class="btn btn-primary">{{($blockage_reason->id ?? 0) ? 'Update' : 'Add'}}</button>
    </div>
</form>
