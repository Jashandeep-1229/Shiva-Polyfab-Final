<form action="{{route('color_master.store')}}" method="POST" class="modal-content">
    @csrf
    <div class="modal-header">
        <h5 class="modal-title">Edit Color</h5>
        <button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Close"></button>
    </div>
    <div class="modal-body">
        <input type="hidden" name="color_id" value="{{$color->id}}">
        <div class="mb-3">
            <label class="form-label">Color Name</label>
            <input type="text" name="name" value="{{$color->name}}" class="form-control form-control-sm" oninput="this.value = this.value.toUpperCase()" required>
        </div>
    </div>
    <div class="modal-footer">
        <button class="btn btn-secondary" type="button" data-bs-dismiss="modal">Close</button>
        <button class="btn btn-primary" type="submit">Update</button>
    </div>
</form>
