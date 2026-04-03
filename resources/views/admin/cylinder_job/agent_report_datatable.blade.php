<div class="row">
    <div class="col-md-4">
        <div class="card bg-light-primary stats-card text-dark">
            <div class="card-body p-3 text-center">
                <h6 class="text-primary">Total Jobs</h6>
                <h3 class="mb-0">{{ $total_jobs }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card bg-light-warning stats-card text-dark">
            <div class="card-body p-3 text-center">
                <h6 class="text-warning">Average Days</h6>
                <h3 class="mb-0">{{ $average_days }} Days</h3>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card bg-light-{{ $average_days <= 10 ? 'success' : 'danger' }} stats-card text-dark">
            <div class="card-body p-3 text-center">
                <h6 class="text-{{ $average_days <= 10 ? 'success' : 'danger' }}">Performance Status</h6>
                <h5 class="mb-1">
                    {{ $average_days <= 7 ? 'Excellent' : ($average_days <= 10 ? 'Good (OK)' : 'Needs Improvement (Late)') }}
                </h5>
                <small class="text-muted">{{ $average_days <= 10 ? 'Working efficiently within deadlines.' : 'Agent is consistently taking more than 10 days.' }}</small>
            </div>
        </div>
    </div>
</div>

<div class="dt-ext table-responsive mt-4">
    <table class="display table-striped table-hover" id="basic-test">
        <thead>
            <tr>
                <th class="all">#</th>
                <th class="all">Name Of Job</th>
                <th class="all">Check In</th>
                <th class="all">Check Out</th>
                <th class="all">Duration</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($cylinder_jobs as $key => $item)
            @php
                $days = (int) filter_var($item->total_no_of_days, FILTER_SANITIZE_NUMBER_INT);
                $rowStyle = '';
                if($days >= 7 && $days <= 10) $rowStyle = 'background-color: rgba(255, 255, 0, 0.1) !important;';
                elseif($days > 10) $rowStyle = 'background-color: rgba(255, 0, 0, 0.05) !important;';
            @endphp
            <tr style="{{ $rowStyle }}">
                <td>{{ $cylinder_jobs->firstItem() + $key }}</td>
                <td>{{ $item->name_of_job ?? 'N/A' }}</td>
                <td>{{ date('d M,Y', strtotime($item->check_in_date)) }}</td>
                <td>{{ date('d M,Y', strtotime($item->check_out_date)) }}</td>
                <td>
                    <span class="badge badge-{{ $days <= 7 ? 'success' : ($days <= 10 ? 'warning' : 'danger') }}">
                        {{ $item->total_no_of_days }}
                    </span>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

<div class="mt-3 pages">
    {{ $cylinder_jobs->onEachSide(1)->links() }}
</div>
