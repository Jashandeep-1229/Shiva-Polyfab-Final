<div class="dt-ext table-responsive">
    <table class="display table-striped table-hover" id="basic-test">
        <thead>
            <tr>
                <th class="all">#</th>
                <th class="all">Name</th>
                <th class="all">Phone No</th>
                <th class="all">Status</th>
                <th class="all">Action</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($sale_executive as $key => $item)
            <tr>
                <td>{{ $sale_executive->firstItem() + $key }}</td>
                <td>{{ $item->name ?? 'N/A' }}</td>
                <td>{{ $item->phone_no ?? 'N/A' }}</td>
                <td>
                    <div class="media-body text-start ">
                        <label class="switch">
                          <input type="checkbox" {{$item->status == 1 ? 'checked':''}} onchange="change_status({{$item->id}})"><span class="switch-state"></span>
                        </label>
                      </div>
                </td>
                <td>
                    <a onclick="edit_modal({{$item->id}},{{$key+1}})"  class="btn btn-warning btn-sm  pointer p-1 f-14" data-bs-toggle="modal" data-bs-target="#edit_modal"  data-toggle="tooltip" title="Edit">
                        <i class="fa fa-edit"></i>
                    </a>
                    @if (auth()->user()->role_as == 'Admin')
                        <a onclick="delete_sale_executive({{$item->id}})" class="btn btn-danger btn-sm  pointer p-1 f-14" data-toggle="tooltip" title="Delete">
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
    {{$sale_executive->onEachSide(1)->links()}}
</div>
