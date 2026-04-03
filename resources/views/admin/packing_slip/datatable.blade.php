<div class="dt-ext table-responsive">
    <table class="display table-striped table-hover" id="basic-test">
        <thead>
            <tr>
                <th class="all">#</th>
                <th class="all">Date</th>
                <th class="all">Name Of Job</th>
                <th class="all">Agent/Customer</th>
                <th class="all">Bags Desctiption</th>
                <th class="all">Weight Desctiption</th>
                <th class="all">Action</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($packing_slips as $key => $item)
            <tr>
                <td>{{ $packing_slips->firstItem() + $key }}</td>
                <td>{{ date('d M, Y', strtotime($item->packing_date)) }}</td>
                <td>{{ $item->job_card->name_of_job ?? 'N/A' }}</td>
                <td>{{ $item->job_card->customer_agent->name ?? 'N/A' }}</td>
                <td>
                    Total Bags : {{ $item->total_bags }} <br>
                    Pending Bags : {{ $item->pending_bags }}
                </td>
                <td>
                    Total Weight : {{ $item->total_weight }} <br>
                    Pending Weight : {{ $item->pending_weight }}
                </td>
                <td>
                    <a onclick="view_modal({{$item->id}},{{$key+1}})"  class="btn btn-info btn-sm  pointer p-1 f-14" data-bs-toggle="modal" data-bs-target="#view_modal"  data-toggle="tooltip" title="View">
                        <i class="fa fa-eye"></i>
                    </a>
                    
                    @if (auth()->user()->role_as == 'Admin')
                        @if($item->status == 2)
                        <a href="{{ route('packing_slip.pdf', $item->id) }}" target="_blank" class="btn btn-primary btn-sm pointer p-1 f-14" data-toggle="tooltip" title="PDF">
                            <i class="fa fa-file-pdf-o"></i>
                        </a>
                        @endif
                    @endif

                    @if (auth()->user()->role_as == 'Admin')
                        <!-- <a onclick="edit_packing_slip({{$item->id}})" class="btn btn-warning btn-sm pointer p-1 f-14" data-toggle="tooltip" title="Edit Slip">
                            <i class="fa fa-pencil"></i>
                        </a> -->
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
<div class="mt-2">
    {{$packing_slips->onEachSide(1)->links()}}
</div>
