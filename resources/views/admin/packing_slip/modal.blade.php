<div class="modal-content">
    @csrf
    <input type="hidden" name="packing_slip_id"  value="{{$packing_slip->id ?? 0}}">
    <div class="modal-header">
        <h4 class="modal-title" id="mySmallModalLabel">Dispatch - {{ $packing_slip->job_card->name_of_job ?? '' }} ({{ $packing_slip->job_card->customer_agent->name ?? '' }})</h4>
        <button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Close" data-bs-original-title="" title=""></button>
    </div>
    <div class="modal-body dark-modal">
        <div class="row mb-3">
            <div class="col-md-3">
                <div class="card b-primary mb-0">
                    <div class="card-body p-2 text-center">
                        <h6 class="mb-1 f-14">Pending Bags</h6>
                        <h5 class="mb-0" id="stat_pending_bags">{{ $packing_slip->pending_bags }}</h5>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card b-info mb-0">
                    <div class="card-body p-2 text-center">
                        <h6 class="mb-1 f-14">Pending Weight</h6>
                        <h5 class="mb-0"><span id="stat_pending_weight">{{ $packing_slip->pending_weight }}</span> kg</h5>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card b-success mb-0">
                    <div class="card-body p-2 text-center">
                        <h6 class="mb-1 f-14">Dispatched Bags</h6>
                        <h5 class="mb-0" id="stat_dispatch_bags">{{ $packing_slip->dispatch_bags }}</h5>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card b-warning mb-0">
                    <div class="card-body p-2 text-center">
                        <h6 class="mb-1 f-14">Dispatched Weight</h6>
                        <h5 class="mb-0"><span id="stat_dispatch_weight">{{ $packing_slip->dispatch_weight }}</span> kg</h5>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            
            <div class="col-md-12">
                <fieldset class="border px-2 row mb-2" id="packing_table">
                    <legend class="float-none w-auto">Packing Slip Details - {{ date('d M, Y', strtotime($packing_slip->packing_date)) }}</legend>
                    <table class="table table-responsive table-bordered mb-2">
                        <thead>
                            <tr>
                                <th style="width: 70px;">Bags</th>
                                <th>Weight</th>
                                <th style="width: 150px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="packing_slip_body">
                            @foreach ($packing_slip->packing_details as $key => $packing_slip_detail)
                                <tr id="row_{{ $packing_slip_detail->id }}" class="{{ $packing_slip_detail->status == 2 ? 'bg-light-success' : '' }}">
                                    <td class="bag-number text-center">{{ $key + 1 }}</td>
                                    <td>
                                        <input type="hidden" name="packing_slip[{{ $key }}][id]" value="{{ $packing_slip_detail->id }}">
                                        {{ $packing_slip_detail->weight }} kg
                                    </td>
                                    <td class="text-center action-cell">
                                        @if(App\Helpers\PermissionHelper::check('packing_slip', 'edit'))
                                            @if($packing_slip_detail->status == 1)
                                                <a href="javascript:void(0)" onclick="complete_status({{ $packing_slip_detail->id }}, 'complete')" class="btn btn-success p-1 btn-sm"><i class="fa fa-check"></i> Complete</a>
                                            @elseif($packing_slip_detail->status == 2 && $packing_slip_detail->is_undo == 1)
                                                <a href="javascript:void(0)" onclick="complete_status({{ $packing_slip_detail->id }}, 'undo')" class="btn btn-danger p-1 btn-sm"><i class="fa fa-undo"></i> Undo</a>
                                            @else
                                                <span class="btn btn-sm btn-success p-2">Completed <br> <small>{{ date('d M, Y', strtotime($packing_slip_detail->complete_date)) }}</small></span>
                                            @endif
                                        @else
                                            @if($packing_slip_detail->status == 2)
                                                <span class="badge bg-success">Completed</span>
                                            @else
                                                <span class="badge bg-warning">Pending</span>
                                            @endif
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <th style="text-align: right;">Total Summary</th>
                                <td>
                                    <strong>{{ $packing_slip->total_weight }} kg</strong> ({{ $packing_slip->total_bags }} Bags)
                                </td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </fieldset>
            </div>
            
        </div>

    </div>
    <div class="modal-footer text-end">
        <span id="pdf_button_container" {!! $packing_slip->status != 2 ? 'style="display:none;"' : '' !!}>
            <a href="{{ route('packing_slip.pdf', $packing_slip->id) }}" target="_blank" class="btn btn-primary"><i class="fa fa-file-pdf-o"></i> Print PDF</a>
        </span>
        <button class="btn btn-secondary" type="button" data-bs-dismiss="modal">Close</button>
    </div>
</div>

<script>
    function complete_status(id, action) {
        var url = action === 'complete' 
            ? "{{ route('packing_slip.complete_detail', ':id') }}" 
            : "{{ route('packing_slip.undo_detail', ':id') }}";
        
        url = url.replace(':id', id);

        $.ajax({
            url: url,
            type: 'GET',
            success: function(data) {
                if (data.result == 1) {
                    $.notify({ title: 'Success', message: data.message }, { type: 'success' });
                    
                    // Update Row appearance and buttons
                    var row = $('#row_' + id);
                    var actionCell = row.find('.action-cell');
                    
                    if (action === 'complete') {
                        row.addClass('bg-light-success');
                        actionCell.html('<a href="javascript:void(0)" onclick="complete_status(' + id + ', \'undo\')" class="btn btn-danger p-1 btn-sm"><i class="fa fa-undo"></i> Undo</a><br><small class="text-success">' + data.formatted_complete_date + '</small>');
                    } else {
                        row.removeClass('bg-light-success');
                        actionCell.html('<a href="javascript:void(0)" onclick="complete_status(' + id + ', \'complete\')" class="btn btn-success p-1 btn-sm"><i class="fa fa-check"></i> Complete</a>');
                    }

                    // Update Stats and Button visibility
                    updateStats(data.slip);
                } else {
                    $.notify({ title: 'Error', message: data.message }, { type: 'danger' });
                }
            },
            error: function() {
                $.notify({ title: 'Error', message: 'Something went wrong' }, { type: 'danger' });
            }
        });
    }

    function updateStats(slip) {
        $('#stat_pending_bags').text(slip.pending_bags);
        $('#stat_pending_weight').text(parseFloat(slip.pending_weight).toFixed(3));
        $('#stat_dispatch_bags').text(slip.dispatch_bags);
        $('#stat_dispatch_weight').text(parseFloat(slip.dispatch_weight).toFixed(3));

        // Show PDF button if everything is dispatched
        if (slip.status == 2) {
            $('#pdf_button_container').fadeIn();
        } else {
            $('#pdf_button_container').fadeOut();
        }
    }
</script>
