<table class="table table-bordered table-striped" id="data_table">
    <thead>
        <tr>
            <th>Sr No</th>
            <th>Bill No</th>
            <th>Bill Date</th>
            <th>Due Date</th>
            <th>Customer</th>
            <th>Items Count</th>
            <th>Total Amount</th>
            <th>GST</th>
            <th>Grand Total</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        @foreach($bills as $key => $bill)
        <tr>
            <td>{{ ($bills->currentPage() - 1) * $bills->perPage() + $key + 1 }}</td>
            <td>{{ $bill->bill_no }}</td>
            <td>{{ date('d-m-Y', strtotime($bill->bill_date)) }}</td>
            <td>
                @if($bill->due_date)
                    @php
                        $isOverdue = strtotime($bill->due_date) < strtotime(date('Y-m-d'));
                    @endphp
                    <span class="{{ $isOverdue ? 'text-danger fw-bold' : '' }}">
                        {{ date('d-m-Y', strtotime($bill->due_date)) }}
                    </span>
                @else
                    -
                @endif
            </td>
            <td>{{ $bill->customer->name ?? 'N/A' }}</td>
            <td>{{ $bill->items->count() }}</td>
            <td>₹ {{ number_format($bill->total_amount, 2) }}</td>
            <td>₹ {{ number_format($bill->igst_amount, 2) }}</td>
            <td class="fw-bold">₹ {{ number_format($bill->grand_total, 2) }}</td>
            <td>
                @if(empty($bill->job_card_id))
                    <a href="{{ route('bill.edit', $bill->id) }}" class="btn btn-warning btn-sm" title="Edit Bill"><i class="fa fa-edit"></i></a>
                @endif
                <a href="{{ route('bill.pdf', $bill->id) }}" target="_blank" class="btn btn-primary btn-sm" title="Print Bill"><i class="fa fa-print"></i></a>
                @if(auth()->user()->role_as == 'Admin')
                <button type="button" class="btn btn-danger btn-sm" onclick="deleteCard({{ $bill->id }})" title="Delete"><i class="fa fa-trash"></i></button>
                @endif
            </td>
        </tr>
        @endforeach
    </tbody>
</table>
<div class="mt-3">
    {{ $bills->links('pagination::bootstrap-4') }}
</div>
