<div class="dt-ext table-responsive">
    <table class="display table-striped table-hover" id="basic-test">
        <thead>
            <tr>
                <th class="all">#</th>
                <th class="all">Date</th>
                <th class="all">Item Name</th>
                <th class="all">Quantity</th>
                <th class="all">Total Average</th>
                <th class="all">Remarks</th>
                <th class="all">Action</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($manage_stocks as $key => $item)
            <tr>
                <td>{{ $manage_stocks->firstItem() + $key }}</td>
                <td>{{ date('d M, Y', strtotime($item->date)) }}</td>
                <td>{{ $item->master->name ?? 'N/A' }}</td>
                <td>{{ $item->quantity }} {{ $item->unit }}</td>
                <td>{{ number_format($item->average, 2) }}</td>
                <td>{{ $item->remarks ?? '-' }}</td>
                <td>
                    @if(\App\Helpers\PermissionHelper::check('stock_management', 'edit'))
                    <a onclick="edit_modal({{$item->id}},{{$key+1}})"  class="btn btn-warning btn-sm  pointer p-1 f-14" data-bs-toggle="modal" data-bs-target="#edit_modal"  data-toggle="tooltip" title="Edit">
                        <i class="fa fa-edit"></i>
                    </a>
                    @endif
                    @if (auth()->user()->role_as == 'Admin')
                        <a onclick="delete_stock({{$item->id}})" class="btn btn-danger btn-sm pointer p-1 f-14" data-toggle="tooltip" title="Delete">
                            <i class="fa fa-trash-o"></i>
                        </a>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
<div class="mt-2">
    {{$manage_stocks->onEachSide(1)->links()}}
</div>
