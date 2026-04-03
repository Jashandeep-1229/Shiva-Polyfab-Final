<form action="{{ route('job_card.store') }}" method="post" id="updateForm" class="modal-content" enctype="multipart/form-data">
    @csrf
    <input type="hidden" name="id"  value="{{$job_card->id ?? 0}}">
    <input type="hidden" name="job_type"  value="{{$job_card->job_type ?? 0}}">
    <div class="modal-header">
        <h4 class="modal-title" id="mySmallModalLabel">{{($job_card->id ?? 0) ? 'Edit' : 'Add'}} Job Card</h4>
        <button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Close" data-bs-original-title="" title=""></button>
    </div>
    <div class="modal-body dark-modal bg-light-{{($job_card->job_type ?? 'new') == 'new' ? 'success' : 'warning'}} text-dark">
        <div class="row">
            <div class="col-md-12 form-group mb-3">
                <h6>Name</h6>
                <input type="text" name="name_of_job" value="{{$job_card->name_of_job ?? ''}}" oninput="this.value = this.value.toUpperCase()" class="form-control form-control-sm" required>
            </div>
            <div class="col-md-3 form-group mb-3">
                <h6>Bopp Used (mm)</h6>
                <div class="input-group input-group-sm">
                    <select name="bopp_id" id="bopp_id" class="form-select form-control-sm" required>
                        <option value="" disabled>Select Bopp Used</option>
                        @foreach($bopps as $bopp)
                            <option value="{{ $bopp->id }}" {{ ($job_card->bopp_id ?? request()->bopp_id) == $bopp->id ? 'selected' : '' }}>{{ $bopp->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-md-3 form-group mb-3">
                <h6>Fabric (mm)</h6>
                <div class="input-group input-group-sm">
                    <select name="fabric_id" id="fabric_id" class="form-select form-control-sm" required>
                        <option value="" disabled>Select Fabric</option>
                        @foreach($fabrics as $fabric)
                            <option value="{{ $fabric->id }}" {{ ($job_card->fabric_id ?? request()->fabric_id) == $fabric->id ? 'selected' : '' }}>{{ $fabric->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
             <div class="col-md-3 form-group mb-3">
                <h6>No Of Pieces</h6>
                  <div class="input-group input-group-sm">
                    <select name="no_of_pieces" id="no_of_pieces" class="form-select  form-control-sm" aria-label="Example select with button addon" required>
                        <option {{ ($job_card->no_of_pieces ?? request()->no_of_pieces) == '5000' ? 'selected' : '' }} value="5000">5000</option>
                        <option {{ ($job_card->no_of_pieces ?? request()->no_of_pieces) == '7000' ? 'selected' : '' }} value="7000">7000</option>
                        <option {{ ($job_card->no_of_pieces ?? request()->no_of_pieces) == '10000' ? 'selected' : '' }} value="10000">10000</option>
                        <option {{ ($job_card->no_of_pieces ?? request()->no_of_pieces) == '12000' ? 'selected' : '' }} value="12000">12000</option>
                        <option {{ ($job_card->no_of_pieces ?? request()->no_of_pieces) == '15000' ? 'selected' : '' }} value="15000">15000</option>
                        <option {{ ($job_card->no_of_pieces ?? request()->no_of_pieces) == '20000' ? 'selected' : '' }} value="20000">20000</option>
                        <option {{ ($job_card->no_of_pieces ?? request()->no_of_pieces) == '25000' ? 'selected' : '' }} value="25000">25000</option>
                        <option {{ ($job_card->no_of_pieces ?? request()->no_of_pieces) == '30000' ? 'selected' : '' }} value="30000">30000</option>
                        <option {{ ($job_card->no_of_pieces ?? request()->no_of_pieces) == '50000' ? 'selected' : '' }} value="50000">50000</option>
                        
                    </select>
                </div>
            </div>
            
            <div class="col-md-3 form-group mb-3">
                <h6>Loop Color</h6>
                <div class="input-group input-group-sm">
                    <select name="loop_color" id="loop_color" class="form-select form-control-sm" required>
                        <option value="" disabled>Select Loop Color</option>
                        @foreach($loops as $loop)
                            <option value="{{ $loop->name }}" {{ ($job_card->loop_color ?? request()->loop_color) == $loop->name ? 'selected' : '' }}>{{ $loop->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-md-3 form-group mb-3">
                <h6>Send For</h6>
                 <div class="input-group input-group-sm">
                    <select name="order_send_for" id="order_send_for" class="form-select form-control form-control-sm" aria-label="Example select with button addon" required>
                        <option value="Cutting" {{ ($job_card->order_send_for ?? request()->order_send_for) == 'Cutting' ? 'selected' : '' }}>Cutting</option>
                        <option value="Box" {{ ($job_card->order_send_for ?? request()->order_send_for) == 'Box' ? 'selected' : '' }}>Box</option>
                    </select>
                    
                </div>  
            </div>
            <div class="col-md-3 form-group mb-3">
                <h6>Dispatch Date</h6>
                <input type="date" name="dispatch_date" value="{{$job_card->dispatch_date ?? ''}}" class="form-control form-control-sm">
            </div>
            @if($job_card->job_type == 'new')
            <div class="col-md-3 form-group mb-3">
                <h6>Cylinder Given To</h6>
                 <div class="input-group input-group-sm">
                <select name="cylinder_given_id" id="cylinder_given_id" class="form-select form-control form-control-sm" required>
                    <option value="" disabled>Select Cylinder Given To</option>
                    @foreach($cylinder_agent as $cylinder_given)
                        <option value="{{ $cylinder_given->id }}" {{ ($job_card->cylinder_given_id ?? request()->cylinder_given_id) == $cylinder_given->id ? 'selected' : '' }}>{{ $cylinder_given->name }}</option>
                    @endforeach
                </select>
                 </div>
            </div>
            @endif
             <div class="col-md-3 form-group mb-3">
                <h6>Sale Executive</h6>
                 <div class="input-group input-group-sm">
                <select name="sale_executive_id" id="sale_executive_id" class="form-select form-control form-control-sm" required>
                    <option value="" disabled>Select Sale Executive</option>
                    @foreach($sale_executive as $sl)
                        <option value="{{ $sl->id }}" {{ ($job_card->sale_executive_id ?? request()->sale_executive_id) == $sl->id ? 'selected' : '' }}>{{ $sl->name }}</option>
                    @endforeach
                </select>
                 </div>
            </div>
            <div class="col-md-3 form-group mb-3">
                <h6>Role</h6>
                 <div class="input-group input-group-sm">
                <select name="select_role" onchange="get_agent_customer_list(this.value)" id="select_role" class="form-select form-control form-control-sm" required>
                    <option value="" disabled>Select Role</option>
                    <option value="Customer" {{ ($job_card->customer_agent->role ?? request()->select_role) == 'Customer' ? 'selected' : '' }}>Customer</option>
                    <option value="Agent" {{ ($job_card->customer_agent->role ?? request()->select_role) == 'Agent' ? 'selected' : '' }}>Agent</option>
                </select>
                 </div>
            </div>
             <div class="col-md-3 mb-2">
                 <h6>Select Customer/Agent</h6>
                <div class="input-group input-group-sm">
                    <select name="customer_agent_id" id="modal_customer_agent_id" class="js-example-basic-single" aria-label="Example select with button addon" required>
                        <option selected value="">Select Customer Agent</option>
                        @foreach($customer_agent as $ca)
                            <option value="{{ $ca->id }}" data-sale-executive-id="{{ $ca->sale_executive_id }}" {{ ($job_card->customer_agent_id ?? request()->customer_agent_id) == $ca->id ? 'selected' : '' }}>{{ $ca->name }} ({{ $ca->phone_no }})</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-md-6 mb-2">
                 <h6>File Upload</h6>
                 <div class="input-group input-group-sm">
                <input type="file" name="file_upload" class="form-control form-control-sm">
                <span class="text-danger"><a class="btn btn-primary btn-sm" target="_blank" href="{{ asset('uploads/job_card/' . $job_card->file_upload) }}">View</a></span>
                </div>
            </div>
            <div class="col-md-12">
                <h6>Aditional Note</h6>
                <textarea name="remarks" id="remarks" rows="4" class="form-control form-control-sm">{{$job_card->remarks ?? ''}}</textarea>
            </div>
             
        </div>

    </div>
    <div class="modal-footer text-end">
        <button type="submit" id="update" class="btn btn-primary">{{($job_card->id ?? 0) ? 'Update' : 'Add'}}</button>
    </div>
</form>
