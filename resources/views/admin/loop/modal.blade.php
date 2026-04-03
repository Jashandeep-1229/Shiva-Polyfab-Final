<form action="{{ route('loop.store') }}" method="post" id="updateForm" class="modal-content" enctype="multipart/form-data">
    @csrf
    <input type="hidden" name="loop_id"  value="{{$loop_color->id ?? 0}}">
    <div class="modal-header">
        <h4 class="modal-title" id="mySmallModalLabel">{{($loop_color->id ?? 0) ? 'Edit' : 'Add'}} Loop Master</h4>
        <button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Close" data-bs-original-title="" title=""></button>
    </div>
    <div class="modal-body dark-modal">
        <div class="row">
            <div class="col-md-12 form-group mb-3">
                <h6>Name</h6>
                <input type="text" name="name" value="{{$loop_color->name ?? ''}}" oninput="this.value = this.value.toUpperCase()" class="form-control form-control-sm" required>
            </div>
             <div class="col-md-12 form-group mb-3">
                <h6>Alert Min Stock</h6>
                <input type="number" step="any" name="alert_min_stock" value="{{$loop_color->alert_min_stock ?? ''}}" class="form-control form-control-sm">
            </div>
            <div class="col-md-12 form-group mb-3">
                <h6>Alert Max Stock</h6>
                <input type="number" step="any" name="alert_max_stock" value="{{$loop_color->alert_max_stock ?? ''}}" class="form-control form-control-sm">
            </div>
            <div class="col-md-12 form-group mb-3">
                 <h6>Order Qty</h6>
                <input type="number" step="any" name="order_qty" value="{{$loop_color->order_qty ?? ''}}" class="form-control form-control-sm">
            </div>
        </div>

    </div>
    <div class="modal-footer text-end">
        <button type="submit" id="update" class="btn btn-primary">{{($loop_color->id ?? 0) ? 'Update' : 'Add'}}</button>
    </div>
</form>
