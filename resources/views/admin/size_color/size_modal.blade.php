<form action="{{route('size_master.store')}}" method="POST" class="modal-content">
    @csrf
    <div class="modal-header">
        <h5 class="modal-title">Edit Size</h5>
        <button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Close"></button>
    </div>
    <div class="modal-body">
        <input type="hidden" name="size_id" value="{{$size->id}}">
        <div class="mb-3">
            <label class="form-label">Size Name</label>
            <input type="text" name="name" value="{{$size->name}}" class="form-control form-control-sm" oninput="this.value = this.value.toUpperCase()" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Fabric</label>
            <select name="fabric_id" class="form-control js-example-basic-single" required>
                <option value="">Select Fabric</option>
                @foreach($fabrics as $f)
                    <option value="{{$f->id}}" {{ $size->fabric_id == $f->id ? 'selected' : '' }}>{{$f->name}}</option>
                @endforeach
            </select>
        </div>
        <div class="mb-3">
            <label class="form-label">BOPP</label>
            <select name="bopp_id" class="form-control js-example-basic-single" required>
                <option value="">Select BOPP</option>
                @foreach($bopps as $b)
                    <option value="{{$b->id}}" {{ $size->bopp_id == $b->id ? 'selected' : '' }}>{{$b->name}}</option>
                @endforeach
            </select>
        </div>
        <div class="mb-3">
            <label class="form-label">Send For</label>
            <select name="order_send_for" class="form-control form-control-sm" required>
                <option value="">Select Option</option>
                <option value="Cutting" {{ $size->order_send_for == 'Cutting' ? 'selected' : '' }}>Cutting</option>
                <option value="Box" {{ $size->order_send_for == 'Box' ? 'selected' : '' }}>Box</option>
            </select>
        </div>
    </div>
    <div class="modal-footer">
        <button class="btn btn-secondary" type="button" data-bs-dismiss="modal">Close</button>
        <button class="btn btn-primary" type="submit">Update</button>
    </div>
</form>
