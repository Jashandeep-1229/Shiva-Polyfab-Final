<div class="modal-content">
    <div class="modal-header bg-dark text-white">
        <h5 class="modal-title"><i class="fa fa-file-text-o me-2"></i>Order Details: {{ $job_card->name_of_job }}</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
    </div>
    <div class="modal-body p-0">
        <div class="card mb-0 shadow-none border-0">
            <div class="card-header p-0">
                @if(!request()->hide_tabs)
                <ul class="nav nav-tabs nav-primary border-bottom-0" id="detailTab" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active py-3 px-4 fw-bold f-13" id="specs-tab" data-bs-toggle="tab" href="#specs" role="tab">
                            <i class="fa fa-list-alt me-2"></i>Order Specifications
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link py-3 px-4 fw-bold f-13" id="account-tab" data-bs-toggle="tab" href="#account" role="tab">
                            <i class="fa fa-calculator me-2"></i>Account & Billing
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link py-3 px-4 fw-bold f-13" id="packing-tab" data-bs-toggle="tab" href="#packing" role="tab">
                            <i class="fa fa-truck me-2"></i>Packing Slips ({{ $job_card->packing_slips->count() }})
                        </a>
                    </li>
                </ul>
                @else
                <div class="bg-primary p-3 text-white">
                    <h6 class="mb-0 fw-bold"><i class="fa fa-calculator me-2"></i>Account & Billing Details</h6>
                </div>
                @endif
            </div>
            <div class="card-body p-4">
                <div class="tab-content" id="detailTabContent">
                    @if(!request()->hide_tabs)
                    <!-- Order Specs Tab -->
                    <div class="tab-pane fade show active" id="specs" role="tabpanel">
                        <div class="table-responsive">
                            <table class="table table-bordered table-sm f-13 mb-0" id="order_spec_table">
                                <tbody>
                                    <tr class="bg-light">
                                        <th width="150" class="text-muted fw-bold p-2">Job Name</th>
                                        <td class="fw-bold p-2">{{$job_card->name_of_job}}</td>
                                        <th width="150" class="text-muted fw-bold p-2">Executive</th>
                                        <td class="p-2">{{$job_card->sale_executive->name ?? 'N/A'}}</td>
                                    </tr>
                                    <tr>
                                        <th class="text-muted fw-bold p-2">Job Type</th>
                                        <td class="p-2"><span class="badge bg-soft-primary text-primary">{{$job_card->job_type}}</span></td>
                                        <th class="text-muted fw-bold p-2">Customer</th>
                                        <td class="p-2">{{$job_card->customer_agent->name ?? 'N/A'}}</td>
                                    </tr>
                                    <tr class="bg-light">
                                        <th class="text-muted fw-bold p-2">Fabric</th>
                                        <td class="p-2">{{$job_card->fabric->name ?? 'N/A'}}</td>
                                        <th class="text-muted fw-bold p-2">BOPP</th>
                                        <td class="p-2">{{$job_card->bopp->name ?? 'N/A'}}</td>
                                    </tr>
                                    <tr>
                                        <th class="text-muted fw-bold p-2">Pieces</th>
                                        <td class="p-2 fw-bold text-dark">{{number_format($job_card->no_of_pieces)}}</td>
                                        <th class="text-muted fw-bold p-2">Loop Color</th>
                                        <td class="p-2">{{$job_card->loop_color ?? 'N/A'}}</td>
                                    </tr>
                                    <tr class="bg-light">
                                        <th class="text-muted fw-bold p-2">Created Date</th>
                                        <td class="p-2">{{date('d M Y', strtotime($job_card->job_card_date))}}</td>
                                        <th class="text-muted fw-bold p-2">Exp. Dispatch</th>
                                        <td class="p-2 text-primary fw-bold">{{$job_card->dispatch_date ? date('d M Y', strtotime($job_card->dispatch_date)) : '-'}}</td>
                                    </tr>
                                    <tr>
                                        <th class="text-muted fw-bold p-2">Send For</th>
                                        <td class="p-2">{{$job_card->order_send_for}}</td>
                                        <th class="text-muted fw-bold p-2">Current Process</th>
                                        <td class="p-2"><span class="text-info fw-600">{{$job_card->job_card_process}}</span></td>
                                    </tr>
                                    @if($job_card->remarks)
                                    <tr class="bg-light">
                                        <th class="text-muted fw-bold p-2">Additional Note</th>
                                        <td colspan="3" class="p-2 italic">{{$job_card->remarks}}</td>
                                    </tr>
                                    @endif
                                    @if($job_card->file_upload)
                                    <tr>
                                        <th class="text-muted fw-bold p-2">Reference File</th>
                                        <td colspan="3" class="p-2 text-center">
                                            <a href="{{asset('uploads/job_card/'.$job_card->file_upload)}}" target="_blank" class="d-inline-block border p-1 rounded bg-light">
                                                <img width="200px" src="{{asset('uploads/job_card/'.$job_card->file_upload)}}" class="rounded shadow-sm">
                                            </a>
                                        </td>
                                    </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @endif

                    <!-- Account Info Tab -->
                    <div class="tab-pane fade {{ request()->hide_tabs ? 'show active' : '' }}" id="account" role="tabpanel">
                        @if($job_card->billing_date)
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <div class="p-3 border rounded-3 bg-light text-center">
                                        <small class="text-muted fw-bold text-uppercase d-block mb-1 f-10">Billing Date</small>
                                        <h6 class="mb-0 text-dark fw-bold">{{ date('d M Y', strtotime($job_card->billing_date)) }}</h6>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="p-3 border rounded-3 bg-danger-light text-center border-danger border-opacity-25">
                                        <small class="text-danger fw-bold text-uppercase d-block mb-1 f-10">Bill Number</small>
                                        <h6 class="mb-0 text-danger fw-bold">{{ $job_card->billing_invoice_no ?? 'N/A' }}</h6>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="p-3 border rounded-3 bg-light text-center">
                                        <small class="text-muted fw-bold text-uppercase d-block mb-1 f-10">Billed Weight</small>
                                        <h6 class="mb-0 text-dark fw-bold">{{ number_format($job_card->billing_weight, 3) }} kg</h6>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="p-3 border rounded-3 bg-primary-light text-center border-primary border-opacity-25">
                                        <small class="text-primary fw-bold text-uppercase d-block mb-1 f-10">Total Amount</small>
                                        <h6 class="mb-0 text-primary fw-bold">₹ {{ number_format($job_card->billing_total_price + $job_card->cylinder_billing_total, 2) }}</h6>
                                    </div>
                                </div>
                                
                                <div class="col-12 mt-4">
                                    <h6 class="f-14 fw-bold mb-3 border-bottom pb-2">Billing Breakdown</h6>
                                    <div class="table-responsive">
                                        <table class="table table-sm table-bordered f-13">
                                            <thead class="bg-dark text-white">
                                                <tr>
                                                    <th>Description</th>
                                                    <th class="text-end">Weight / Qty</th>
                                                    <th class="text-end">Rate</th>
                                                    <th class="text-end">Amount</th>
                                                    <th class="text-end">GST %</th>
                                                    <th class="text-end">Total</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <!-- Main Billing Row -->
                                                <tr>
                                                    <td class="p-2 fw-bold text-dark">Main Order Billing</td>
                                                    <td class="p-2 text-end">{{ number_format($job_card->billing_weight, 3) }}</td>
                                                    <td class="p-2 text-end">₹ {{ number_format($job_card->billing_rate, 2) }}</td>
                                                    <td class="p-2 text-end">₹ {{ number_format($job_card->billing_weight * $job_card->billing_rate, 2) }}</td>
                                                    <td class="p-2 text-end">{{ $job_card->billing_gst_percent }}%</td>
                                                    <td class="p-2 text-end fw-bold">₹ {{ number_format($job_card->billing_total_price, 2) }}</td>
                                                </tr>
                                                
                                                <!-- Cylinder Billing Row -->
                                                @if($job_card->cylinder_billing_total > 0)
                                                <tr class="bg-light">
                                                    <td class="p-2 fw-bold text-muted italic">Cylinder Charges</td>
                                                    <td class="p-2 text-end">{{ number_format($job_card->cylinder_billing_weight, 3) }}</td>
                                                    <td class="p-2 text-end">₹ {{ number_format($job_card->cylinder_billing_rate, 2) }}</td>
                                                    <td class="p-2 text-end">₹ {{ number_format($job_card->cylinder_billing_weight * $job_card->cylinder_billing_rate, 2) }}</td>
                                                    <td class="p-2 text-end">{{ $job_card->cylinder_billing_gst_percent }}%</td>
                                                    <td class="p-2 text-end fw-bold">₹ {{ number_format($job_card->cylinder_billing_total, 2) }}</td>
                                                </tr>
                                                @endif
                                            </tbody>
                                            <tfoot class="bg-primary-light">
                                                <tr class="fw-bold">
                                                    <td colspan="5" class="text-end p-2 text-primary">Grand Total Settlement</td>
                                                    <td class="text-end p-2 text-primary" style="font-size: 1.1rem;">₹ {{ number_format($job_card->billing_total_price + $job_card->cylinder_billing_total, 2) }}</td>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="text-center py-5 text-muted">
                                <div class="mb-3">
                                    <i class="fa fa-calculator f-50 opacity-25"></i>
                                </div>
                                <h5 class="f-16 fw-bold">No Billing Data Available</h5>
                                <p class="f-13">Financial settlement for this order is still pending.</p>
                            </div>
                        @endif
                    </div>

                    @if(!request()->hide_tabs)
                    <!-- Packing Slips Tab -->
                    <div class="tab-pane fade" id="packing" role="tabpanel">
                        <div class="p-2">
                            @php $slips = $job_card->packing_slips; @endphp
                            @if($slips && $slips->count() > 0)
                                @if($slips->count() > 1)
                                    <div class="row">
                                        <div class="col-md-3 border-end">
                                            <div class="nav flex-column nav-pills" role="tablist">
                                                @foreach($slips as $index => $slip)
                                                    <button class="nav-link text-start mb-2 py-1 px-2 f-11 fw-bold {{ $index == 0 ? 'active' : '' }}" data-bs-toggle="pill" data-bs-target="#modal-ps-{{ $slip->id }}" type="button" role="tab">
                                                        Slip #{{ $slip->packing_slip_no }}
                                                    </button>
                                                @endforeach
                                            </div>
                                        </div>
                                        <div class="col-md-9">
                                            <div class="tab-content">
                                                @foreach($slips as $index => $slip)
                                                    <div class="tab-pane fade {{ $index == 0 ? 'show active' : '' }}" id="modal-ps-{{ $slip->id }}" role="tabpanel">
                                                        @include('admin.report.job_card.partial_packing_slip_details', ['slip' => $slip])
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                @else
                                    @php $slip = $slips->first(); @endphp
                                    @include('admin.report.job_card.partial_packing_slip_details', ['slip' => $slip])
                                @endif
                            @else
                                <div class="text-center py-4 text-muted f-12">
                                    <i class="fa fa-truck f-30 mb-2 opacity-25"></i>
                                    <p>No packing slips found for this order.</p>
                                </div>
                            @endif
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    <div class="modal-footer bg-light p-2">
        <button type="button" class="btn btn-secondary btn-sm px-4" data-bs-dismiss="modal">Close</button>
    </div>
</div>
