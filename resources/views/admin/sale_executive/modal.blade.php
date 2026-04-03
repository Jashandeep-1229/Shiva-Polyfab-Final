<form action="{{ route('sale_executive.store') }}" method="post" id="updateForm" class="modal-content" enctype="multipart/form-data">
    @csrf
    <input type="hidden" name="sale_executive_id"  value="{{$sale_executive->id ?? 0}}">
    <div class="modal-header">
        <h4 class="modal-title" id="mySmallModalLabel">{{($sale_executive->id ?? 0) ? 'Edit' : 'Add'}} Sale Executive</h4>
        <button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Close" data-bs-original-title="" title=""></button>
    </div>
    <div class="modal-body dark-modal">
        <div class="row">
            <div class="col-md-12 form-group mb-3">
                <h6>Name</h6>
                <input type="text" name="name" value="{{$sale_executive->name ?? ''}}" oninput="this.value = this.value.toUpperCase()" class="form-control form-control-sm" required>
            </div>
            <div class="col-md-12 form-group mb-3">
                <h6>Phone No</h6>
                <input type="tel" name="phone_no" value="{{$sale_executive->phone_no ?? ''}}" class="form-control form-control-sm" required>
            </div>
        </div>

    </div>
    <div class="modal-footer text-end">
        <button type="submit" id="update" class="btn btn-primary">{{($sale_executive->id ?? 0) ? 'Update' : 'Add'}}</button>
    </div>
</form>
