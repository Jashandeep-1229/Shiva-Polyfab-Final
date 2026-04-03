<div class="d-flex justify-content-between align-items-center mb-2">
    <h6 class="mb-0 text-primary fw-bold f-14">Packing Slip Summary: {{ $slip->packing_slip_no }}</h6>
    <a href="{{ route('packing_slip.pdf', $slip->id) }}" target="_blank" class="btn btn-xs btn-outline-danger py-0 px-2 f-12">
        <i class="fa fa-file-pdf-o"></i> PDF
    </a>
</div>
<div class="row g-2 mb-2">
    <div class="col-md-4">
        <div class="p-1 border rounded bg-light text-center">
            <small class="text-muted d-block f-12">Dispatch Bags</small>
            <span class="fw-bold text-black f-14">{{ $slip->dispatch_bags }} / {{ $slip->total_bags }}</span>
        </div>
    </div>
    <div class="col-md-4">
        <div class="p-1 border rounded bg-light text-center">
            <small class="text-muted d-block f-12">Dispatch Weight</small>
            <span class="fw-bold text-black f-14">{{ number_format($slip->dispatch_weight, 3) }} kg</span>
        </div>
    </div>
    <div class="col-md-4">
        <div class="p-1 border rounded bg-light text-center">
            <small class="text-muted d-block f-12">Status</small>
            <span class="badge f-12 {{ $slip->status == 2 ? 'bg-success' : 'bg-warning text-dark' }}">
                {{ $slip->status == 2 ? 'Completed' : 'Partial' }}
            </span>
        </div>
    </div>
</div>
<div class="table-responsive">
    <table class="table table-bordered table-sm f-12 mb-0">
        <thead class="bg-light">
            <tr class="text-black">
                <th>S.No</th>
                <th>Weight</th>
                <th>Complete Date</th>
                <th>Completed By</th>
            </tr>
        </thead>
        <tbody class="text-black">
            @foreach($slip->packing_details as $detail)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ number_format($detail->weight, 3) }} kg</td>
                    <td>{{ $detail->complete_date ? date('d M, Y', strtotime($detail->complete_date)) : '-' }}</td>
                    <td>{{ $detail->user->name ?? 'N/A' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
