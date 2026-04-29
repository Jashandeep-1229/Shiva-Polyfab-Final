<div class="row mb-4">
    <div class="col-md-6">
        <div class="card target-card bg-primary-light">
            <div class="card-body p-4">
                <div class="stat-icon bg-primary text-white">
                    <i class="fa fa-balance-scale"></i>
                </div>
                <h6 class="text-muted mb-1">Total Weight Target</h6>
                <h3 class="mb-0">{{ number_format($total_weight, 2) }} KG</h3>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card target-card bg-success-light">
            <div class="card-body p-4">
                <div class="stat-icon bg-success text-white">
                    <i class="fa fa-cubes"></i>
                </div>
                <h6 class="text-muted mb-1">Total Pieces Selected</h6>
                <h3 class="mb-0">{{ number_format($total_pcs) }} PCS</h3>
            </div>
        </div>
    </div>
</div>

<table class="table table-striped table-hover mt-3" id="target-table">
    <thead>
        <tr>
            <th>Order #</th>
            <th>Executive</th>
            <th>Date</th>
            <th>Bag Type</th>
            <th>Dimensions (W x H x G)</th>
            <th>GSM</th>
            <th>Per Pc (g)</th>
            <th>Total Pcs</th>
            <th>Total Weight (KG)</th>
        </tr>
    </thead>
    <tbody>
        @forelse($records as $record)
        <tr>
            <td>
                <span class="badge badge-outline-primary">{{ $record->job_card->job_card_no ?? 'N/A' }}</span><br>
                <small class="text-muted">{{ $record->job_card->name_of_job ?? '' }}</small>
            </td>
            <td>{{ $record->executive->name ?? 'N/A' }}</td>
            <td>{{ date('d-M-Y', strtotime($record->date)) }}</td>
            <td><span class="badge badge-info">{{ $record->bag_type }}</span></td>
            <td>{{ $record->width }} x {{ $record->length }} x {{ $record->guzzete ?? 0 }}</td>
            <td>{{ $record->gsm }}</td>
            <td>{{ number_format($record->per_pcs_weight, 2) }}g</td>
            <td>{{ number_format($record->total_pcs) }}</td>
            <td class="fw-bold text-primary">{{ number_format($record->total_weight, 2) }} KG</td>
        </tr>
        @empty
        <tr>
            <td colspan="9" class="text-center p-5">
                <img src="{{ asset('assets/images/no-data.png') }}" alt="" style="max-width: 150px; opacity: 0.5;">
                <p class="mt-3 text-muted">No target records found for the selected criteria.</p>
            </td>
        </tr>
        @endforelse
    </tbody>
</table>

<script>
    if (!$.fn.DataTable.isDataTable('#target-table')) {
        $('#target-table').DataTable({
            dom: 'Bfrtip',
            buttons: ['copy', 'csv', 'excel', 'pdf', 'print'],
            pageLength: 50,
            order: [[2, 'desc']]
        });
    }
</script>
