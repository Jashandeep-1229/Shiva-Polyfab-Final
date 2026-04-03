@php
if (!function_exists('formatIndianCurrency')) {
    function formatIndianCurrency($number) {
        $parts = explode('.', number_format($number, 2, '.', ''));
        $last_three = substr($parts[0], -3);
        $rest = substr($parts[0], 0, -3);
        if(strlen($rest) > 0) {
            $rest = preg_replace('/\B(?=(\d{2})+(?!\d))/', ',', $rest) . ',';
        }
        return $rest . $last_three . '.' . $parts[1];
    }
}
@endphp
<div class="table-responsive">
    <table class="table table-hover table-striped" id="basic-test">
        <thead>
            <tr class="bg-primary text-white">
                <th style="width: 50px;">#</th>
                <th style="width: 100px;">Date</th>
                <th>Customer</th>
                <th>Sale Executive</th>
                <th>Particulars</th>
                <th class="text-end" style="width: 120px;">Debit (Dr)</th>
                <th class="text-end" style="width: 120px;">Credit (Cr)</th>
                <th class="text-end" style="width: 80px;">Action</th>
            </tr>
        </thead>
        <tbody>
            @forelse($ledger as $item)
                <tr>
                    <td><small class="text-muted">#{{ $item->id }}</small></td>
                    <td class="fw-bold">{{ date('d-m-Y', strtotime($item->transaction_date)) }}</td>
                    <td>
                        <div class="d-flex flex-column">
                            <span class="fw-bold text-dark">
                                {{ $item->customer->name ?? 'N/A' }}
                                @if($item->customer && $item->customer->is_bad_debt)
                                    <span class="badge bg-danger text-white ms-1" style="font-size: 9px; vertical-align: middle;">BAD DEBT CUSTOMER</span>
                                @endif
                            </span>
                            <span class="text-muted extra-small text-uppercase fw-bold">{{ $item->customer->code ?? '' }} ({{ $item->customer->role ?? '' }})</span>
                        </div>
                    </td>
                    <td>
                        <span class="badge rounded-pill bg-light text-dark border">{{ $item->customer->sale_executive->name ?? 'N/A' }}</span>
                    </td>
                    <td>
                        @if($item->dr_cr == 'Dr')
                            <small class="text-uppercase fw-bold d-block text-danger">To {{ $item->remarks }}</small>
                        @elseif($item->dr_cr == 'Cr')
                            <small class="text-uppercase fw-bold d-block text-success">By {{ $item->payment_method->name ?? 'Manual/Direct' }} {{ $item->remarks }}</small>
                        @endif
                    </td>
                    <td class="text-end">
                        @if($item->dr_cr == 'Dr')
                            <span class="text-danger fw-bold">{{ formatIndianCurrency($item->grand_total_amount) }}</span>
                        @else
                            -
                        @endif
                    </td>
                    <td class="text-end">
                        @if($item->dr_cr == 'Cr')
                            <div class="d-flex flex-column align-items-end">
                                <span class="{{ $item->is_bad_debt ? 'text-indigo' : 'text-success' }} fw-bold">{{ formatIndianCurrency($item->grand_total_amount) }}</span>
                                @if($item->is_bad_debt)
                                    <span class="badge bg-warning text-dark px-2 mt-1 shadow-sm" style="font-size: 10px; letter-spacing: 0.5px;">BAD DEBT WRITE-OFF</span>
                                @endif
                            </div>
                        @else
                            -
                        @endif
                    </td>
                    <td class="text-end">
                        <div class="d-flex justify-content-end gap-1">
                            @if($item->bill_id)
                                <button type="button" onclick="window.open('{{ route('bill.pdf', $item->bill_id) }}', '_blank')" class="btn btn-dark btn-xs px-2 shadow-sm" title="View Bill PDF">
                                    <i class="fa fa-file-pdf-o"></i>
                                </button>
                            @elseif($item->job_card && $item->job_card->bill)
                                <button type="button" onclick="window.open('{{ route('bill.pdf', $item->job_card->bill->id) }}', '_blank')" class="btn btn-dark btn-xs px-2 shadow-sm" title="View Bill PDF">
                                    <i class="fa fa-file-pdf-o"></i>
                                </button>
                            @endif

                            @if($item->packing_slip)
                                <button type="button" onclick="window.open('{{ route('packing_slip.pdf', $item->packing_slip_id) }}', '_blank')" class="btn btn-dark btn-xs px-2 shadow-sm" title="View Packing Slip PDF">
                                    <i class="fa fa-file-pdf-o"></i>
                                </button>
                            @endif
                            
                            @php
                                $can_edit = auth()->user()->role_as == 'Admin' || \App\Helpers\PermissionHelper::check('vouchers', 'edit');
                                // Determine if this is a system-generated entry that should NOT be edited/deleted
                                $is_fixed = $item->job_card_id || $item->packing_slip_id || $item->bill_id;
                            @endphp
                            
                            @if($can_edit && !$is_fixed)
                                <button type="button" class="btn btn-info btn-xs px-2 text-white" onclick="editEntry({{ $item->id }})" title="Edit entry">
                                    <i class="fa fa-edit"></i>
                                </button>
                            @endif
                            
                            @if(auth()->user()->role_as == 'Admin' && !$is_fixed)
                                <button type="button" class="btn btn-danger btn-xs px-2" onclick="deleteEntry({{ $item->id }})" title="Delete entry">
                                    <i class="fa fa-trash text-white"></i>
                                </button>
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center py-5">
                        <img src="{{ asset('assets/images/no-data.png') }}" class="img-fluid mb-2" style="max-height: 80px;" alt="No Data">
                        <p class="text-muted">No transactions found for the selected filters.</p>
                    </td>
                </tr>
            @endforelse
        </tbody>
        @if($ledger->isNotEmpty())
        <tfoot>
            <tr class="bg-light fw-bold">
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td class="text-end">Period Totals:</td>
                <td class="text-end text-danger">{{ formatIndianCurrency($ledger->sum(fn($i) => $i->dr_cr == 'Dr' ? $i->grand_total_amount : 0)) }}</td>
                <td class="text-end text-success">{{ formatIndianCurrency($ledger->sum(fn($i) => $i->dr_cr == 'Cr' ? $i->grand_total_amount : 0)) }}</td>
                <td></td>
            </tr>
        </tfoot>
        @endif
    </table>
</div>

<div class="mt-2">
    {{ $ledger->links() }}
</div>
