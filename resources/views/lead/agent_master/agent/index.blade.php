@extends('layouts.admin.app')

@section('title', 'Agent Master')

@section('breadcrumb-items')
    <li class="breadcrumb-item">Agent Master</li>
    <li class="breadcrumb-item active">Agents</li>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-4">
            <div class="card">
                <div class="card-header pb-0">
                    <h5 id="form-title">Add Agent</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('lead.agent.agent.store') }}" method="POST" id="agent-form">
                        @csrf
                        <input type="hidden" name="id" id="agent-id">
                        <input type="hidden" name="agent_customer_id" id="agent-customer-id" value="0">
                        <div class="mb-3">
                            <label class="form-label">Agent Name</label>
                            <input type="text" name="name" id="agent-name" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Firm Name (Optional)</label>
                            <input type="text" name="firm_name" id="agent-firm" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Mobile No (10 Digits) <span>*</span></label>
                            <input type="number" name="phone" id="agent-phone" class="form-control" placeholder="10 digit number" 
                                   onKeyPress="if(this.value.length==10) return false;" 
                                   onkeydown="return event.keyCode !== 69" required>
                            <div id="phone-validation-msg" class="mt-1" style="font-size: 12px; display: none;"></div>
                        </div>
                        <div class="row">
                            <div class="col-6 mb-3">
                                <label class="form-label">State <span>*</span></label>
                                <select name="state" id="agent-state" class="form-select select2" required>
                                    <option value="">Select State</option>
                                    @foreach($states as $state)
                                    <option value="{{ $state }}">{{ $state }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-6 mb-3">
                                <label class="form-label">City <span>*</span></label>
                                <div class="input-group">
                                    <select name="city" id="agent-city" class="form-select select2 city_select" required>
                                        <option value="">Select State First</option>
                                    </select>
                                    <button class="btn btn-outline-primary btn-sm quick-add-city-btn" type="button" 
                                            data-state-selector="#agent-state" 
                                            data-city-selector="#agent-city" title="Quick Add City">
                                        <i class="fa fa-plus"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Deals In</label>
                            <div class="input-group">
                                <select name="deals_in_id" id="agent-deals-in" class="form-control" required>
                                    <option value="">Select Category</option>
                                    @foreach($deals as $deal)
                                        <option value="{{ $deal->id }}">{{ $deal->name }}</option>
                                    @endforeach
                                </select>
                                <a href="{{ route('lead.agent.deals_in.index') }}" class="btn btn-outline-primary"><i class="fa fa-plus"></i></a>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <select name="status" id="agent-status" class="form-control">
                                <option value="1">Active</option>
                                <option value="0">Inactive</option>
                            </select>
                        </div>
                        <div class="d-flex justify-content-between">
                            <button type="submit" class="btn btn-primary">Save Agent</button>
                            <button type="button" class="btn btn-light d-none" id="cancel-edit">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-md-8">
            <div class="card">
                <div class="card-header pb-0">
                    <h5>Agent List</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Agent/Firm</th>
                                    <th>Contact</th>
                                    <th>Location</th>
                                    <th>Deals In</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($agents as $agent)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>
                                        <strong>{{ $agent->name }}</strong><br>
                                        <small class="text-muted">{{ $agent->firm_name }}</small>
                                    </td>
                                    <td>{{ $agent->phone }}</td>
                                    <td>{{ $agent->city }}, {{ $agent->state }}</td>
                                    <td>{{ $agent->dealsIn->name ?? 'N/A' }}</td>
                                    <td>
                                        <span class="badge badge-{{ $agent->status ? 'success' : 'danger' }}">
                                            {{ $agent->status ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                    <td>
                                        <button class="btn btn-info btn-xs edit-agent" 
                                            data-id="{{ $agent->id }}" 
                                            data-name="{{ $agent->name }}"
                                            data-firm="{{ $agent->firm_name }}"
                                            data-phone="{{ $agent->phone }}"
                                            data-state="{{ $agent->state }}"
                                            data-city="{{ $agent->city }}"
                                            data-deals="{{ $agent->deals_in_id }}"
                                            data-status="{{ $agent->status }}">Edit</button>
                                        <a href="{{ route('lead.agent.agent.delete', $agent->id) }}" class="btn btn-danger btn-xs" onclick="return confirm('Delete this?')">Delete</a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
    $(document).ready(function() {
        if ($.fn.select2) {
            $('.select2').select2({ placeholder: "Choose option" });
        }

        function populateCities(stateVal, selectedCity) {
            var $city = $('#agent-city');
            $city.prop('disabled', true).empty().append('<option value="">Loading Cities...</option>');
            if ($.fn.select2) $city.trigger('change.select2');

            if (!stateVal) {
                $city.empty().append('<option value="">Select State First</option>');
                $city.prop('disabled', false);
                if ($.fn.select2) $city.trigger('change.select2');
                return;
            }

            $.get(`{{ url('lead/locations/get-cities') }}/${encodeURIComponent(stateVal)}`, function(data) {
                $city.empty().append('<option value="">Select City</option>');
                $.each(data, function(i, city) {
                    var sel = (city.name === selectedCity) ? ' selected' : '';
                    $city.append('<option value="' + city.name + '"' + sel + '>' + city.name + '</option>');
                });
                $city.prop('disabled', false);
                if ($.fn.select2) $city.trigger('change.select2');
            });
        }

        // Trigger Quick Add City Modal
        $('.quick-add-city-btn').on('click', function() {
            var state = $($(this).data('state-selector')).val();
            if (!state) {
                $.notify({ title: 'Error', message: 'Please select a state first' }, { type: 'danger' });
                return;
            }
            $('#modal_state_name_hidden').val(state);
            $('#modal_target_select_hidden').val($(this).data('city-selector'));
            $('#modal_city_name').val('');
            $('#quickAddCityModal').modal('show');
            setTimeout(function() { $('#modal_city_name').focus(); }, 500);
        });
    
        $('#agent-state').on('change', function() {
            populateCities($(this).val(), '');
        });

        $('.edit-agent').click(function() {
            let id = $(this).data('id');
            $('#agent-id').val(id);
            $('#agent-name').val($(this).data('name'));
            $('#agent-firm').val($(this).data('firm'));
            $('#agent-phone').val($(this).data('phone'));
            
            let state = $(this).data('state');
            let city = $(this).data('city');
            
            $('#agent-state').val(state).trigger('change');
            populateCities(state, city);

            $('#agent-deals-in').val($(this).data('deals')).trigger('change');
            $('#agent-status').val($(this).data('status'));
            
            $('#form-title').text('Edit Agent');
            $('#cancel-edit').removeClass('d-none');
        });

        $('#cancel-edit').click(function() {
            $('#agent-id').val('');
            $('#agent-form')[0].reset();
            $('.select2').val('').trigger('change');
            $('#form-title').text('Add Agent');
            $(this).addClass('d-none');
        });

        $('#agent-form').on('submit', function(e) {
            const phone = $('#agent-phone').val();
            if (phone.length !== 10) {
                alert('Please enter a valid 10-digit phone number.');
                e.preventDefault();
                return false;
            }
            if ($('#phone-validation-msg').find('.text-danger').length > 0) {
                alert('This phone number belongs to a Customer. Please change it.');
                e.preventDefault();
                return false;
            }
        });

        // ── Auto-check Phone in Agent Master ──
        $('#agent-phone').on('input keyup', function () {
            var phone = $(this).val();
            var $result = $('#phone-validation-msg');
            
            if (phone.length === 10) {
                $result.show().html('<span class="text-muted"><i class="fa fa-spinner fa-spin"></i> Checking...</span>');
                
                $.get('{{ route("lead.agent.check_phone") }}', { phone: phone }, function (res) {
                    if (res.found) {
                        $result.html('<span class="text-success"><i class="fa fa-check-circle"></i> Found in Customer Master — details prefilled.</span>');
                        $('#agent-customer-id').val(res.agent_customer_id);
                        $('#agent-name').val(res.name).prop('readonly', true);
                        $('#agent-firm').val(res.firm_name).prop('readonly', true);
                        $('#agent-form').find('button[type="submit"]').prop('disabled', false);

                        // Set state and city
                        if (res.state) {
                            $('#agent-state').val(res.state).trigger('change');
                            setTimeout(function () {
                                if (res.city) {
                                    $('#agent-city').val(res.city).trigger('change');
                                }
                            }, 800);
                        }
                    } else {
                        if (res.is_customer) {
                            $result.html('<span class="text-danger"><i class="fa fa-times-circle"></i> ' + res.message + '</span>');
                            $('#agent-form').find('button[type="submit"]').prop('disabled', true);
                        } else {
                            $result.html('<span class="text-warning"><i class="fa fa-info-circle"></i> Not found in Customer Master — fill manually.</span>');
                            $('#agent-form').find('button[type="submit"]').prop('disabled', false);
                        }
                        $('#agent-customer-id').val(0);
                        $('#agent-name').val('').prop('readonly', false);
                        $('#agent-firm').val('').prop('readonly', false);
                    }
                });
            } else {
                $result.hide().html('');
                $('#agent-customer-id').val(0);
                $('#agent-name').prop('readonly', false);
                $('#agent-firm').prop('readonly', false);
                $('#agent-form').find('button[type="submit"]').prop('disabled', false);
            }
        });
    });
</script>
@endsection
