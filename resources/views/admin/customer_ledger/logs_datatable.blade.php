<div class="table-responsive">
    <table class="table table-hover table-striped" id="basic-test">
        <thead>
            <tr class="bg-primary text-white">
                <th style="width: 50px;">Log #</th>
                <th style="width: 150px;">Date & Time</th>
                <th>Author</th>
                <th>Customer</th>
                <th style="width: 100px;">Action</th>
                <th>Changes / Info</th>
            </tr>
        </thead>
        <tbody>
            @forelse($logs as $log)
                <tr>
                    <td><small class="text-muted">#{{ $log->id }}</small></td>
                    <td class="fw-bold">{{ date('d-m-Y h:i A', strtotime($log->created_at)) }}</td>
                    <td>
                        <span class="badge bg-light text-dark border">{{ $log->user->name ?? 'System' }}</span>
                    </td>
                    <td>
                        <span class="fw-bold">{{ $log->customer->name ?? 'N/A' }}</span>
                        @if($log->customer)
                            <br><small class="text-muted">{{ $log->customer->code }}</small>
                        @endif
                    </td>
                    <td>
                        @if($log->action == 'Edit')
                            <span class="badge bg-info text-white">Edited</span>
                        @elseif($log->action == 'Delete')
                            <span class="badge bg-danger text-white">Deleted</span>
                        @else
                            <span class="badge bg-secondary">{{ $log->action }}</span>
                        @endif
                        <br><small class="text-muted mt-1 d-block">Ledger: #{{ $log->customer_ledger_id }}</small>
                    </td>
                    <td>
                        @php
                            $old = $log->old_data ? json_decode($log->old_data, true) : null;
                            $new = $log->new_data ? json_decode($log->new_data, true) : null;
                        @endphp
                        
                        @if($log->action == 'Edit' && $old && $new)
                            <div class="d-flex flex-column gap-1" style="font-size: 13px;">
                                @if(isset($old['transaction_date']) && $old['transaction_date'] != $new['transaction_date'])
                                    <div><strong>Date:</strong> <del class="text-danger">{{ date('d-m-Y', strtotime($old['transaction_date'])) }}</del> <i class="fa fa-arrow-right mx-1 text-muted"></i> <span class="text-success">{{ date('d-m-Y', strtotime($new['transaction_date'])) }}</span></div>
                                @endif
                                
                                @if(isset($old['grand_total_amount']) && $old['grand_total_amount'] != $new['grand_total_amount'])
                                    <div><strong>Amount:</strong> <del class="text-danger">{{ number_format($old['grand_total_amount'], 2) }}</del> <i class="fa fa-arrow-right mx-1 text-muted"></i> <span class="text-success">{{ number_format($new['grand_total_amount'], 2) }}</span></div>
                                @endif
                                
                                @if(isset($old['dr_cr']) && $old['dr_cr'] != $new['dr_cr'])
                                    <div><strong>Type:</strong> <del class="text-danger">{{ $old['dr_cr'] }}</del> <i class="fa fa-arrow-right mx-1 text-muted"></i> <span class="text-success">{{ $new['dr_cr'] }}</span></div>
                                @endif
                                
                                @if(isset($old['remarks']) && $old['remarks'] != $new['remarks'])
                                    <div><strong>Remarks:</strong> <del class="text-danger">{{ $old['remarks'] ?: 'None' }}</del> <i class="fa fa-arrow-right mx-1 text-muted"></i> <span class="text-success">{{ $new['remarks'] ?: 'None' }}</span></div>
                                @endif
                            </div>
                        @elseif($log->action == 'Delete' && $old)
                            <div class="text-muted" style="font-size: 13px;">
                                <strong>Date:</strong> {{ date('d-m-Y', strtotime($old['transaction_date'] ?? '')) }}<br>
                                <strong>Type:</strong> {{ $old['dr_cr'] ?? '-' }}<br>
                                <strong>Amount:</strong> {{ number_format($old['grand_total_amount'] ?? 0, 2) }}<br>
                                <strong>Remarks:</strong> {{ $old['remarks'] ?? 'None' }}
                            </div>
                        @else
                            <small class="text-muted">No details available</small>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center py-5">
                        <img src="{{ asset('assets/images/no-data.png') }}" class="img-fluid mb-2" style="max-height: 80px;" alt="No Data">
                        <p class="text-muted">No modification logs found for the selected filters.</p>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-2">
    {{ $logs->links() }}
</div>
