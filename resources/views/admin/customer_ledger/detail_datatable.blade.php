<div class="table-responsive">
    <table class="display" id="basic-test">
        <thead>
            <tr>
                <th class="small text-uppercase">#</th>
                <th class="small text-uppercase">Date</th>
                <th class="small text-uppercase">Particulars</th>
                <th class="small text-uppercase text-danger">Debit (Dr)</th>
                <th class="small text-uppercase text-success">Credit (Cr)</th>
                <th class="small text-uppercase">Balance</th>
                <th class="small text-uppercase text-end">Action</th>
            </tr>
        </thead>
        <tbody>
            @php $balance = $opening_balance; @endphp
            @if($ledger->currentPage() == 1 || $opening_balance != 0)
            <tr class="bg-light">
                <td colspan="5" class="text-end fw-bold small text-uppercase" style="color: #4a5568 !important;">Opening Balance / C/F</td>
                <td class="fw-bold px-3 py-2 rounded">
                    <span class="{{ $balance > 0 ? 'text-danger' : ($balance < 0 ? 'text-success' : 'text-muted') }}">
                        {{ number_format(abs($balance), 2) }} {{ $balance > 0 ? 'Dr' : ($balance < 0 ? 'Cr' : '') }}
                    </span>
                </td>
                <td></td>
            </tr>
            @endif
            @foreach($ledger as $index => $row)
                @php
                    if ($row->dr_cr == 'Dr') $balance += $row->grand_total_amount;
                    else $balance -= $row->grand_total_amount;
                @endphp
                <tr class="{{ $row->is_bad_debt ? 'bg-indigo-subtle' : '' }}" style="{{ $row->is_bad_debt ? 'background-color: #f5f3ff !important;' : '' }}">
                    <td>{{ $index + 1 }}</td>
                    <td class="text-nowrap">{{ date('d-m-Y', strtotime($row->transaction_date)) }}</td>
                    <td style="max-width: 350px;">
                        @php
                            $prefix = ($row->dr_cr == 'Dr') ? 'TO' : 'BY';
                            $prefix_class = ($row->dr_cr == 'Dr') ? 'text-danger' : 'text-success';
                        @endphp

                        <div class="d-flex flex-wrap align-items-center gap-1">
                            <span class="{{ $prefix_class }} fw-bold small me-1">{{ $prefix }}</span>
                            
                            @if ($row->job_card_id)
                                <a href="javascript:void(0)" onclick="viewJobDetails({{ $row->job_card_id }})" class="text-primary fw-bold f-12 text-decoration-none hover-underline">
                                    <i class="fa fa-file-text-o me-1"></i> {{ $row->job_card->name_of_job ?? 'JOB' }} (#{{ $row->job_card_id }})
                                </a>
                            @elseif ($row->packing_slip_id)
                                <span class="text-info fw-bold small me-1"><i class="fa fa-truck me-1 text-info"></i> SLIP #{{ $row->packing_slip_id }}</span>
                                <span class="small text-muted">{{ $row->remarks }}</span>
                            @else
                                @if($row->is_bad_debt)
                                    <span class="badge px-2 py-1 text-white me-1 f-8" style="background-color: #4338ca !important;">BAD DEBT WRITE-OFF</span>
                                @endif
                                <span class="small fw-bold {{ $row->is_bad_debt ? 'text-indigo' : '' }}">
                                    {{ $row->remarks ?: ($row->payment_method ? $row->payment_method->name : '') }}
                                    @if($row->dr_cr == 'Cr' && $row->remarks && $row->payment_method)
                                        <span class="text-muted f-10">[{{ $row->payment_method->name }}]</span>
                                    @endif
                                </span>
                            @endif
                        </div>
                    </td>
                    <td>
                        @if ($row->dr_cr == 'Dr')
                            <span class="text-danger fw-bold">{{ number_format($row->grand_total_amount, 2) }}</span>
                        @else
                            <span class="text-muted small">-</span>
                        @endif
                    </td>
                    <td>
                        @if ($row->dr_cr == 'Cr')
                            <span class="{{ $row->is_bad_debt ? 'text-indigo' : 'text-success' }} fw-bold">{{ number_format($row->grand_total_amount, 2) }}</span>
                        @else
                            <span class="text-muted small">-</span>
                        @endif
                    </td>
                    <td>
                        <span class="fw-bold px-3 py-1 rounded small {{ $balance > 0 ? 'text-danger bg-danger-subtle' : ($balance < 0 ? 'text-success bg-success-subtle' : 'text-muted bg-light border') }}">
                            {{ number_format(abs($balance), 2) }} {{ $balance > 0 ? 'Dr' : ($balance < 0 ? 'Cr' : '') }}
                        </span>
                    </td>
                    <td class="text-end">
                        <div class="d-flex justify-content-end gap-1">
                            @if($row->bill_id || ($row->job_card && $row->job_card->bill))
                                @php
                                    $bill_id = $row->bill_id ?: $row->job_card->bill->id;
                                    $lastSentBill = \Illuminate\Support\Facades\Cache::get('bill_whatsapp_sent_' . $bill_id);
                                    $isSentBill = $lastSentBill ? true : false;
                                @endphp
                                <button type="button" onclick="window.open('{{ route('bill.pdf', $bill_id) }}', '_blank')" class="btn btn-dark btn-xs px-2 shadow-sm" title="View Bill PDF">
                                    <i class="fa fa-file-pdf-o"></i>
                                </button>
                                <button type="button" onclick="sendWhatsAppBill(this, {{ $bill_id }})" 
                                    class="btn {{ $isSentBill ? 'btn-success' : 'btn-outline-success border-success' }} btn-xs px-2 shadow-sm position-relative" 
                                    title="{{ $isSentBill ? 'Sent: '.\Carbon\Carbon::parse($lastSentBill)->diffForHumans() : 'Send WhatsApp Bill' }}">
                                    <i class="fa fa-whatsapp {{ $isSentBill ? 'text-white' : 'text-success' }}" style="{{ $isSentBill ? 'color: white !important;' : '' }}"></i>
                                    @if($isSentBill)
                                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-circle bg-white border border-success p-0" style="width: 14px; height: 14px; margin-top: 2px; margin-left: -2px; display: flex; align-items: center; justify-content: center; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                                            <i class="fa fa-check text-success" style="font-size: 8px; color: #28a745 !important;"></i>
                                        </span>
                                    @endif
                                </button>
                            @endif

                            @if($row->packing_slip_id)
                                <button type="button" onclick="window.open('{{ route('packing_slip.pdf', $row->packing_slip_id) }}', '_blank')" class="btn btn-dark btn-xs px-2 shadow-sm" title="View Packing Slip PDF">
                                    <i class="fa fa-file-pdf-o"></i>
                                </button>
                            @endif

                            @php
                                $is_fixed = $row->job_card_id || $row->packing_slip_id || $row->bill_id;
                                $can_edit = auth()->user()->role_as == 'Admin' || \App\Helpers\PermissionHelper::check('customer_ledger', 'edit');
                            @endphp

                            @if(!$is_fixed && $can_edit)
                                <button type="button" class="btn btn-info btn-xs px-2" onclick="editEntry({{ $row->id }})" title="Edit"><i class="fa fa-edit text-white"></i></button>
                            @endif

                            @if(!$is_fixed && auth()->user()->role_as == 'Admin')
                                <button type="button" class="btn btn-danger btn-xs px-2" onclick="deleteEntry({{ $row->id }})" title="Delete"><i class="fa fa-trash text-white"></i></button>
                            @endif
                        </div>
                    </td>
                </tr>
            @endforeach
        </tbody>
        <tfoot class="bg-light border-top">
            <tr>
                <th colspan="3" class="text-end py-3 small fw-bold text-uppercase" style="color: #4a5568 !important;">Total Summary</th>
                <th class="py-3 text-dark-danger fw-bold fs-6" style="color: #c53030;">
                    @php
                        $total_dr = $ledger->where('dr_cr', 'Dr')->sum('grand_total_amount');
                        if($opening_balance > 0) $total_dr += $opening_balance;
                    @endphp
                    {{ number_format($total_dr, 2) }}
                </th>
                <th class="py-3 text-dark-success fw-bold fs-6" style="color: #2f855a;">
                    @php
                        $total_cr = $ledger->where('dr_cr', 'Cr')->sum('grand_total_amount');
                        if($opening_balance < 0) $total_cr += abs($opening_balance);
                    @endphp
                    {{ number_format($total_cr, 2) }}
                </th>
                <th class="py-3"></th>
                <th class="py-3"></th>
            </tr>
        </tfoot>
    </table>
</div>

<div class="mt-3 pages">
    {{ $ledger->links() }}
</div>
