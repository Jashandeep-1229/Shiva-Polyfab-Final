<div class="dt-ext table-responsive">
    <table class="display table-striped table-hover" id="basic-test">
        <thead>
            <tr>
                <th class="all">#</th>
                <th class="all">Name Of Job</th>
                <th class="all">Cylinder Given To</th>
                <th class="all">Check In Date</th>
                @if(request()->type == 'pending')
                <th class="all">No Of Days Pending</th>
                @else
                <th class="all">Check Out Date</th>
                <th class="all">Total No of Days</th>
                @endif
              
            </tr>
        </thead>
        <tbody>
            @foreach ($cylinder_job as $key => $item)
            @php
                $days = 0;
                $rowStyle = '';
                if(request()->type != 'pending' && $item->total_no_of_days) {
                    $days = (int) filter_var($item->total_no_of_days, FILTER_SANITIZE_NUMBER_INT);
                    if($days >= 7 && $days <= 10) {
                        $rowStyle = 'background-color: rgba(255, 255, 0, 0.2) !important;'; // Light Yellow
                    } elseif($days > 10) {
                        $rowStyle = 'background-color: rgba(255, 0, 0, 0.1) !important;'; // Very Light Red
                    }
                }
            @endphp
            <tr style="{{ $rowStyle }}">
                <td>{{ $cylinder_job->firstItem() + $key }}</td>
                <td>{{ $item->name_of_job ?? 'N/A' }}</td>
                <td>{{ $item->cylinder_agent->name ?? 'N/A' }}</td>
                <td>{{ date('d M,Y', strtotime($item->check_in_date)) ?? 'N/A' }}</td>
                @if(request()->type == 'pending')
                <td>
                    @php
                        $checkIn = \Carbon\Carbon::parse($item->check_in_date)->startOfDay();
                        $today = \Carbon\Carbon::now()->startOfDay();
                        $diff = $today->diffInDays($checkIn);
                    @endphp
                    {{ $diff == 0 ? '0 days' : $diff . ' Days Ago' }}
                </td>
                @else
                <td>{{ date('d M,Y', strtotime($item->check_out_date)) ?? 'N/A' }}</td>
                <td>{{ $item->total_no_of_days ?? 'N/A' }}</td>
                @endif
            </tr>
            @endforeach

        </tbody>
    </table>
</div>
<div class="mt-2">
    {{$cylinder_job->onEachSide(1)->links()}}
</div>
