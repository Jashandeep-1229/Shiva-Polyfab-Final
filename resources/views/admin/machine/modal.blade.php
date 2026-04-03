<form action="{{ route('machine.store') }}" method="post" id="updateForm" class="modal-content" enctype="multipart/form-data">
    @csrf
    <input type="hidden" name="machine_id"  value="{{$machine->id ?? 0}}">
    <input type="hidden" name="type"  value="{{$machine->type ?? 0}}">
    <div class="modal-header">
        <h4 class="modal-title" id="mySmallModalLabel">{{($machine->id ?? 0) ? 'Edit' : 'Add'}} Machine Master</h4>
        <button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Close" data-bs-original-title="" title=""></button>
    </div>
    <div class="modal-body dark-modal">
        <div class="row">
            <div class="col-md-12 form-group mb-3">
                <h6>Name</h6>
                <input type="text" name="name" value="{{$machine->name ?? ''}}" oninput="this.value = this.value.toUpperCase()" class="form-control form-control-sm" required>
            </div>
            <div class="col-md-12 form-group mb-3">
                <h6>Avg Per Day Production (Pcs)</h6>
                <input type="number" name="avg_per_day_production" value="{{$machine->avg_per_day_production ?? 0}}" class="form-control form-control-sm">
            </div>
        </div>

    </div>
    <div class="modal-footer text-end">
        <button type="submit" id="update" class="btn btn-primary">{{($machine->id ?? 0) ? 'Update' : 'Add'}}</button>
    </div>
</form>
