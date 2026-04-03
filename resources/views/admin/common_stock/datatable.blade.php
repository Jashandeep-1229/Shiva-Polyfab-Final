<div class="table-responsive">
    <table class="table table-sm table-hover mb-0 f-13" id="basic-test">
        <thead>
            <tr class="bg-dark text-white">
                <th class="py-2 px-3">Date</th>
                <th class="py-2 px-3">Color</th>
                <th class="py-2 px-3">Size</th>
                <th class="py-2 px-3 text-center">Quantity</th>
                <th class="py-2 px-3">Remarks</th>
                <th class="py-2 px-3 text-center">By</th>
                <th class="py-2 px-3 text-center">Action</th>
            </tr>
        </thead>
        <tbody>
            @forelse($manage_stocks as $item)
                <tr>
                    <td class="px-3">{{ date('d M, Y', strtotime($item->date)) }}</td>
                    <td class="px-3 fw-bold text-dark">{{ $item->color->name ?? 'N/A' }}</td>
                    <td class="px-3 text-muted f-11">{{ $item->size->name ?? 'N/A' }}</td>
                    <td class="text-center fw-bold {{ $in_out == 'In' ? 'text-success' : 'text-danger' }}">
                        {{ $in_out == 'In' ? '+' : '-' }} {{ number_format($item->quantity, 3) }}
                    </td>
                    <td class="px-3">
                        <span class="text-muted italic f-11">
                            @if($item->from == 'Packing Slip' && $item->from_id)
                                <a href="{{ route('packing_slip_common.pdf', $item->from_id) }}" target="_blank" class="text-primary text-decoration-underline" title="View Packing Slip PDF">
                                    {{ $item->remarks ?: '-' }}
                                </a>
                            @else
                                {{ $item->remarks ?: '-' }}
                            @endif
                        </span>
                    </td>
                    <td class="text-center text-muted f-11 px-3">
                        {{ $item->user->name ?? 'Admin' }}
                    </td>
                    <td class="text-center px-3">
                        <div class="d-flex justify-content-center gap-2">
                            @if(\App\Helpers\PermissionHelper::check('common_product_stock', 'edit'))
                            <button onclick="edit_modal({{ $item->id }})" class="btn btn-warning btn-xs p-1" title="Edit">
                                <i class="fa fa-pencil" style="color: #fff !important;"></i>
                            </button>
                            @endif

                            @if(auth()->user()->role_as == 'Admin')
                            <button onclick="delete_stock({{ $item->id }})" class="btn btn-danger btn-xs p-1" title="Delete">
                                <i class="fa fa-trash-o" style="color: #fff !important;"></i>
                            </button>
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center py-5 text-muted">No stock records found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="d-flex justify-content-between align-items-center p-3 border-top bg-light">
    <div class="text-muted small">
        Showing {{ $manage_stocks->firstItem() ?? 0 }} to {{ $manage_stocks->lastItem() ?? 0 }} of {{ $manage_stocks->total() }} entries
    </div>
    <div class="pagination-wrapper">
        {{ $manage_stocks->appends(request()->all())->links() }}
    </div>
</div>
