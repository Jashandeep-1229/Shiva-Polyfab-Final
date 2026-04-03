<div class="dt-ext table-responsive">
    <table class="display table-striped table-hover" id="basic-test">
        <thead>
            <tr>
                <th>#</th>
                <th>Order Date</th>
                <th>Dispatch Date</th>
                <th>Name of Job</th>
                <th>Image</th>
                <th>Bopp</th>
                <th>Fabric</th>
                <th>Loop Color</th>
                <th>Pieces</th>
                <th>Send For</th>
                <th>Notes</th>
                @if(auth()->user()->role_as == 'Admin')
                <th>Action</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @foreach ($old_data as $key => $item)
            <tr>
                <td>{{ $old_data->firstItem() + $key }}</td>
                <td>{{ $item->order_date ? \Carbon\Carbon::parse($item->order_date)->format('d-m-Y') : 'N/A' }}</td>
                <td>{{ $item->dispatch_date ? \Carbon\Carbon::parse($item->dispatch_date)->format('d-m-Y') : 'N/A' }}</td>
                <td>{{ $item->name_of_job ?? 'N/A' }}</td>
                <td>
                    @if($item->image)
                    <a href="{{ asset($item->image) }}" target="_blank">
                        <img src="{{ asset($item->image) }}" alt="" style="height: 30px; border-radius: 4px;">
                    </a>
                    @else
                    N/A
                    @endif
                </td>
                <td>{{ $item->bopp->name ?? 'N/A' }}</td>
                <td>{{ $item->fabric->name ?? 'N/A' }}</td>
                <td>{{ $item->loop->name ?? 'N/A' }}</td>
                <td>{{ $item->pieces ?? 'N/A' }}</td>
                <td>{{ $item->send_for ?? 'N/A' }}</td>
                <td>{{ $item->remarks ?? 'N/A' }}</td>
                @if(auth()->user()->role_as == 'Admin')
                <td>
                    <button onclick="delete_old_data({{$item->id}})" class="btn btn-danger btn-sm p-1 px-2" title="Delete">
                        <i class="fa fa-trash-o"></i>
                    </button>
                </td>
                @endif
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
<div class="mt-2 pages">
    {{$old_data->onEachSide(1)->links()}}
</div>
