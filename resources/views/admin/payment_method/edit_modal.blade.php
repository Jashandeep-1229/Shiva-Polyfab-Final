<form action="{{ route('payment_method.store') }}" method="POST">
    @csrf
    <input type="hidden" name="id" value="{{ $method->id }}">
    <div class="modal-content border-0 shadow-lg">
        <div class="modal-header bg-primary text-white">
            <h5 class="modal-title font-weight-bold">Edit Payment Method</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body p-4">
            <div class="mb-4">
                <label class="form-label font-weight-bold small text-uppercase mb-1">Method Name</label>
                <input type="text" name="name" value="{{ $method->name }}" oninput="this.value = this.value.toUpperCase()" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label font-weight-bold small text-uppercase mb-1">Status</label>
                <select name="status" class="form-select">
                    <option value="1" {{ $method->status == 1 ? 'selected' : '' }}>Active</option>
                    <option value="0" {{ $method->status == 0 ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>
        </div>
        <div class="modal-footer bg-light border-0">
            <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Close</button>
            <button type="submit" class="btn btn-primary px-4 shadow-sm">Save Changes</button>
        </div>
    </div>
</form>
