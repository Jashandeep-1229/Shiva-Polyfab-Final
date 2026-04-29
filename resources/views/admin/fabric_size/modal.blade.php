<div class="modal-content">
    <div class="modal-header">
        <h5 class="modal-title">Edit Fabric Size Calculation</h5>
        <button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Close"></button>
    </div>
    <form action="{{ route('fabric_size.store') }}" method="POST">
        @csrf
        <input type="hidden" name="fabric_size_id" value="{{ $fabric_size->id }}">
        <div class="modal-body row">
            <div class="col-md-12 mb-3">
                <label class="form-label">Size (Number Only)</label>
                <input type="number" step="any" name="name" class="form-control" value="{{ $fabric_size->name }}" required>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary btn-sm" type="button" data-bs-dismiss="modal">Close</button>
            <button class="btn btn-primary btn-sm" type="submit">Update</button>
        </div>
    </form>
</div>
