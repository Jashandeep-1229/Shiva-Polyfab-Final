<div class="table-responsive">
    <table class="display dataTable no-footer" id="basic-test" role="grid">
        <thead>
            <tr role="row">
                <th style="width: 50px;">SR #</th>
                <th>SLIP #</th>
                <th>CUSTOMER</th>
                <th>DATE</th>
                <th class="text-center">TOTAL BAGS</th>
                <th class="text-center">TOTAL WEIGHT (KG)</th>
                <th class="text-center">ACTION</th>
            </tr>
        </thead>
        <tbody>
            @foreach($packing_slips as $key => $slip)
            @php
                $firstDetail = $slip->packing_details->first();
                $customer = $firstDetail->job_card->customer_agent->name ?? 'N/A';
            @endphp
            <tr>
                <td class="text-center">{{ $packing_slips->firstItem() + $key }}</td>
                <td><span class="badge bg-light text-dark">{{ $slip->packing_slip_no }}</span></td>
                <td><strong>{{ $customer }}</strong></td>
                <td>{{ date('d M, Y', strtotime($slip->packing_date)) }}</td>
                <td class="text-center"><span class="badge bg-primary">{{ $slip->total_bags }}</span></td>
                <td class="text-center"><strong>{{ number_format($slip->total_weight, 3) }} KG</strong></td>
                <td class="text-center">
                    <div class="d-flex justify-content-center gap-1">
                        <!-- Print Button -->
                        @if(auth()->user()->role_as == 'Admin')
                        <a href="{{ route('packing_slip_common.pdf', $slip->id) }}" target="_blank" class="btn btn-info btn-sm p-1 f-14 pointer" data-toggle="tooltip" title="Print PDF">
                            <i class="fa fa-file-pdf-o"></i>
                        </a>
                        @endif
                        
                        <!-- Edit Button -->
                        @if(\App\Helpers\PermissionHelper::check('packing_slip_common', 'edit'))
                        <a href="{{ route('packing_slip_common.edit', $slip->id) }}" class="btn btn-warning btn-sm p-1 f-14 pointer text-white" data-toggle="tooltip" title="Edit Slip">
                            <i class="fa fa-pencil"></i>
                        </a>
                        @endif
                        
                        <!-- Delete Button -->
                        @if(auth()->user()->role_as == 'Admin')
                        <button type="button" onclick="deleteSlip({{ $slip->id }})" class="btn btn-danger btn-sm p-1 f-14 pointer" data-toggle="tooltip" title="Delete Slip">
                            <i class="fa fa-trash-o"></i>
                        </button>
                        @endif
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

<div class="row pages mt-3">
    <div class="col-12 mt-3">
        {{ $packing_slips->appends(request()->input())->links() }}
    </div>
</div>

<script>
    function deleteSlip(id) {
        swal({
            title: "Are you sure?",
            text: "This will permanently delete this packing slip and automatically revert all linked stock deductions!",
            icon: "warning",
            buttons: true,
            dangerMode: true,
        })
        .then((willDelete) => {
            if (willDelete) {
                $.ajax({
                    url: '{{ url("admin/packing_slip_common/delete") }}/' + id,
                    type: 'GET',
                    success: function(data) {
                        if (data.result == 1) {
                            swal("Deleted!", data.message, "success")
                            .then(() => {
                                location.reload();
                            });
                        } else {
                            swal("Error", data.message, "error");
                        }
                    },
                    error: function() {
                        swal("Oops!", "Something went wrong on the server.", "error");
                    }
                });
            }
        });
    }
    
    $(document).ready(function() {
        $('[data-toggle="tooltip"]').tooltip();
    });
</script>
