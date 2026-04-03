<div class="modal-dialog modal-lg">
    <div class="modal-content">
        <div class="modal-header bg-dark text-white">
            <h5 class="modal-title">Vendors for {{ $type }} - <span class="text-warning">{{ $master->name ?? '' }}</span></h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body p-0">
            <div class="table-responsive">
                <table class="table table-sm table-hover mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-3">Vendor Name</th>
                            <th>Phone No</th>
                            <th>Added By</th>
                            <th>Date</th>
                            <th class="text-end pe-3">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($vendors as $vendor)
                            <tr>
                                <td class="ps-3"><strong>{{ $vendor->name }}</strong></td>
                                <td>{{ $vendor->phone_no }}</td>
                                <td>{{ $vendor->user->name ?? 'System' }}</td>
                                <td>{{ $vendor->created_at->format('d M Y, h:i A') }}</td>
                                <td class="text-end pe-3">
                                    <button class="btn btn-danger btn-xs" onclick="deleteMasterVendor('{{ $vendor->id }}')">
                                        <i class="fa fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center p-4">
                                    <h6 class="text-muted mb-0">No vendors found for this item.</h6>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Close</button>
        </div>
    </div>
</div>

<script>
    function deleteMasterVendor(id) {
        if (confirm('Are you sure you want to delete this vendor?')) {
            $.ajax({
                url: "{{ url('admin/master_vendor/delete') }}/" + id,
                method: 'GET',
                success: function(data) {
                    if (data.result == 1) {
                        $.notify({ title: 'Success', message: data.message }, { type: 'success' });
                        // Reload the vendor list modal
                        setTimeout(function() {
                            openVendorListModal('{{ $type }}', '{{ $id }}');
                        }, 500);
                    } else {
                        $.notify({ title: 'Error', message: data.message }, { type: 'danger' });
                    }
                }
            });
        }
    }
</script>
