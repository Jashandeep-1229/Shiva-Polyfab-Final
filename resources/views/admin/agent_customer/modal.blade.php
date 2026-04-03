<form action="{{ route('agent_customer.store') }}" method="post" id="updateForm" class="modal-content" enctype="multipart/form-data">
    @csrf
    <input type="hidden" name="agent_customer_id"  value="{{$agent_customer->id ?? 0}}">

    <div class="modal-header">
        <h4 class="modal-title" id="mySmallModalLabel">{{($agent_customer->id ?? 0) ? 'Edit' : 'Add'}} Agent / Customer Master</h4>
        <button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Close" data-bs-original-title="" title=""></button>
    </div>
    <div class="modal-body dark-modal">
        <div class="row">
            <div class="col-md-12 form-group mb-3">
                <h6>Name <span class="text-danger">*</span></h6>
                <input type="text" name="name" id="name_input" value="{{$agent_customer->name ?? ''}}" oninput="this.value = this.value.toUpperCase()" class="form-control form-control-sm" required>
            </div>
            <div class="col-md-12 form-group mb-3">
                <h6>Phone No <span class="text-danger">*</span></h6>
                <input type="tel" id="phone_no_input" name="phone_no" value="{{$agent_customer->phone_no ?? ''}}" class="form-control form-control-sm" maxlength="10" oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 10); if(this.value.length == 10) { $(this).removeClass('is-invalid'); } else { $(this).addClass('is-invalid'); }" required>
            </div>
            <div class="col-md-12 form-group mb-3">
                <h6>Role</h6>
                <select name="role" id="role_select" class="form-control form-control-sm" required>
                    <option value="" disabled>Select Role</option>
                    <option value="Customer" {{ ($agent_customer->role ?? request()->role) == 'Customer' ? 'selected' : '' }}>Customer</option>
                    <option value="Agent" {{ ($agent_customer->role ?? request()->role) == 'Agent' ? 'selected' : '' }}>Agent</option>
                </select>
            </div>
            <div class="col-md-12 form-group mb-3">
                <h6>Type</h6>
                <select name="type" class="form-control form-control-sm" required>
                    <option value="A" {{ ($agent_customer->type ?? 'A') == 'A' ? 'selected' : '' }}>Type A</option>
                    <option value="B" {{ ($agent_customer->type ?? '') == 'B' ? 'selected' : '' }}>Type B</option>
                </select>
            </div>
            <div class="col-md-6 form-group mb-3">
                <h6>Sale Executive <span class="text-danger">*</span></h6>
                <select name="sale_executive_id" class="form-control form-control-sm select2-modal" required>
                    <option value="">Select Sale Executive</option>
                    @foreach($sales_executives as $se)
                        <option value="{{$se->id}}" {{($agent_customer->sale_executive_id ?? 0) == $se->id ? 'selected' : ''}}>{{$se->name}} ({{$se->role_as}})</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6 form-group mb-3">
                <h6>GST <span class="text-danger">*</span></h6>
                <input type="text" name="gst" value="{{$agent_customer->gst ?? 'NA'}}" class="form-control form-control-sm" oninput="this.value = this.value.toUpperCase()" required>
            </div>
            <div class="col-md-12 form-group mb-3">
                <h6>Address</h6>
                <textarea name="address" id="address_input" class="form-control form-control-sm" rows="2">{{$agent_customer->address ?? ''}}</textarea>
            </div>
            <div class="col-md-6 form-group mb-3">
                <h6>Pincode</h6>
                <input type="text" name="pincode" id="pincode_input" value="{{$agent_customer->pincode ?? ''}}" class="form-control form-control-sm" maxlength="6" oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 6); fetchLocation(this.value)">
                <small id="pincode_status" class="text-danger" style="display:none; font-size: 10px;">Invalid Pincode</small>
            </div>
            <div class="col-md-3 form-group mb-3">
                <h6>State</h6>
                <input type="text" name="state" id="state_input" value="{{$agent_customer->state ?? ''}}" class="form-control form-control-sm" readonly>
            </div>
            <div class="col-md-3 form-group mb-3">
                <h6>City</h6>
                <input type="text" name="city" id="city_input" value="{{$agent_customer->city ?? ''}}" class="form-control form-control-sm" readonly>
            </div>
            <div class="col-md-12 form-group mb-3">
                <h6>Remarks</h6>
                <textarea name="remarks" id="remarks_input" class="form-control form-control-sm" rows="2">{{$agent_customer->remarks ?? ''}}</textarea>
            </div>
        </div>

    </div>
    <div class="modal-footer text-end">
        <button type="submit" id="update" class="btn btn-primary">{{($agent_customer->id ?? 0) ? 'Update' : 'Add'}}</button>
    </div>
</form>

<script>
    function fetchLocation(pincode) {
        if(pincode.length === 6) {
            $.ajax({
                url: 'https://api.postalpincode.in/pincode/' + pincode,
                type: 'GET',
                success: function(response) {
                    if(response[0].Status === 'Success') {
                        $('#pincode_status').hide();
                        var postOffices = response[0].PostOffice;
                        var city = postOffices[0].District;
                        
                    
                        for(var i=0; i<postOffices.length; i++) {
                            if(postOffices[i].Name === postOffices[i].Block) {
                                city = postOffices[i].Name;
                                break;
                            }
                        }
                    

                        $('#state_input').val(postOffices[0].State);
                        $('#city_input').val(city);
                    } else {
                        $('#pincode_status').show();
                        $('#state_input').val('');
                        $('#city_input').val('');
                    }
                },
                error: function() {
                    $('#pincode_status').text('Error fetching location').show();
                    $('#state_input').val('');
                    $('#city_input').val('');
                }
            });
        } else {
            $('#pincode_status').hide();
            $('#state_input').val('');
            $('#city_input').val('');
        }
    }
</script>
