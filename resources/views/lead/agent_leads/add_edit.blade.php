@extends('layouts.admin.app')

@php
    $isEdit = isset($lead);
    $title = $isEdit ? 'Edit Agent Lead: ' . $lead->lead_no : 'Add New Agent Lead';
    $action = $isEdit ? route('lead.agent_leads.update', $lead->id) : route('lead.agent_leads.store');
@endphp

@section('title', $title)

@section('css')
<link rel="stylesheet" type="text/css" href="{{ asset('assets/css/vendors/select2.css') }}">
<style>
    .form-label { color: #333 !important; font-weight: 500; }
    .bg-light .form-label { color: #2c323f !important; }
</style>
@endsection

@section('breadcrumb-items')
    <li class="breadcrumb-item"><a href="{{ route('lead.agent_leads.index') }}">Agent Leads</a></li>
    <li class="breadcrumb-item active">{{ $isEdit ? 'Edit' : 'Add' }}</li>
@endsection

@section('content')
<div class="container-fluid">
    <div class="card">
        <form action="{{ $action }}" method="POST" id="agent-lead-form">
            @csrf
            @if($isEdit) @method('PUT') @endif
            
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label class="form-label">Lead Number</label>
                            <input type="text" class="form-control" value="{{ $isEdit ? $lead->lead_no : $leadNo }}" readonly style="background-color: #f8f9fa;">
                        </div>
                    </div>
                    <div class="col-md-5">
                        <div class="mb-3">
                            <label class="form-label">Select Agent <span>*</span></label>
                            <div class="d-flex gap-2">
                                <div class="flex-grow-1">
                                    <select name="agent_id" id="agent_id" class="form-select select2" required>
                                        <option value="">Select Agent</option>
                                        @foreach($agents as $agent)
                                        <option value="{{ $agent->id }}" {{ old('agent_id', $lead->agent_id ?? '') == $agent->id ? 'selected' : '' }}>
                                            {{ $agent->name }} ({{ $agent->firm_name }})
                                        </option>
                                        @endforeach
                                    </select>
                                </div>
                                <button type="button" class="btn btn-primary px-3" data-bs-toggle="modal" data-bs-target="#quickAddAgentModal" title="Quick Add Agent"><i class="fa fa-plus"></i></button>
                            </div>
                            <div id="agent_validation_msg" class="mt-1" style="font-size: 13px; display: none;"></div>
                        </div>
                    </div>
                    @if(auth()->user()->role_as == 'Admin')
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label">Assigned To staff</label>
                            <select name="assigned_user_id" id="assigned_user_id" class="form-select select2">
                                <option value="">Select Staff</option>
                                @foreach($users as $user)
                                <option value="{{ $user->id }}" {{ old('assigned_user_id', $lead->assigned_user_id ?? (Auth::id())) == $user->id ? 'selected' : '' }}>
                                    {{ $user->name }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    @else
                        <input type="hidden" name="assigned_user_id" value="{{ $lead->assigned_user_id ?? Auth::id() }}">
                    @endif
                </div>

                <hr>

                @if(!$isEdit)
                <div id="lead-repeater">
                    <div class="repeater-item border p-3 rounded mb-3 bg-light">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Name of Job <span>*</span></label>
                                    <input type="text" name="leads[0][name_of_job]" class="form-control text-uppercase" placeholder="ENTER JOB NAME" required style="text-transform: uppercase;">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Process (Status) <span>*</span></label>
                                    <select name="leads[0][status_id]" class="form-select" required>
                                        @foreach($statuses as $status)
                                        <option value="{{ $status->id }}">{{ $status->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="mb-0">
                                    <label class="form-label">Remarks</label>
                                    <textarea name="leads[0][remarks]" class="form-control" rows="2" placeholder="Initial communication remarks..."></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <button type="button" class="btn btn-outline-primary btn-sm mb-3" id="add-row"><i class="fa fa-plus"></i> Add Another Job</button>
                @else
                <div class="row">
                    <div class="col-md-12">
                        <div class="mb-3">
                            <label class="form-label">Name Of Job <span>*</span></label>
                            <input type="text" name="name_of_job" class="form-control text-uppercase" value="{{ $lead->name_of_job }}" required style="text-transform: uppercase;">
                        </div>
                    </div>
                </div>
                @endif
            </div>
            
            <div class="card-footer text-end">
                <button type="submit" class="btn btn-primary">{{ $isEdit ? 'Update Lead' : 'Create Agent Leads' }}</button>
            </div>
        </form>
    </div>
</div>

{{-- Quick Add Agent Modal --}}
<div class="modal fade" id="quickAddAgentModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <form id="quick-agent-form" class="modal-content">
            @csrf
            <input type="hidden" name="agent_customer_id" id="quick-agent-customer-id" value="0">
            <div class="modal-header">
                <h5 class="modal-title">Quick Add Agent</h5>
                <button class="btn-close" type="button" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">

                {{-- STEP 1: Phone First --}}
                <div class="mb-3">
                    <label class="form-label fw-bold">Mobile No (10 Digits) <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <input type="number" name="phone" id="quick-agent-phone" class="form-control"
                               placeholder="Enter phone number first..."
                               onKeyPress="if(this.value.length==10) return false;"
                               onkeydown="return event.keyCode !== 69" required>
                        <button type="button" class="btn btn-outline-secondary" id="check-phone-btn">
                            <i class="fa fa-search"></i> Check
                        </button>
                    </div>
                    <div id="phone-check-result" class="mt-1 small"></div>
                </div>

                {{-- Agent details (shown after phone check) --}}
                <div id="agent-detail-fields">
                    <div class="mb-3">
                        <label class="form-label">Agent Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="quick-agent-name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Firm Name</label>
                        <input type="text" name="firm_name" id="quick-agent-firm" class="form-control">
                    </div>
                    <div class="row">
                        <div class="col-6 mb-3">
                            <label class="form-label">State <span class="text-danger">*</span></label>
                            <select name="state" id="quick-agent-state" class="form-select select2" required>
                                <option value="">Select State</option>
                                @foreach($states as $state)
                                <option value="{{ $state }}">{{ $state }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-6 mb-3">
                            <label class="form-label">City <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <select name="city" id="quick-agent-city" class="form-select select2" required>
                                    <option value="">Select State First</option>
                                </select>
                                <button class="btn btn-outline-primary btn-sm quick-add-city-btn" type="button" 
                                        data-state-selector="#quick-agent-state" 
                                        data-city-selector="#quick-agent-city" title="Quick Add City">
                                    <i class="fa fa-plus"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Deals In</label>
                        <select name="deals_in_id" class="form-control" required>
                            @foreach($deals as $deal)
                            <option value="{{ $deal->id }}">{{ $deal->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-primary" type="submit">Save &amp; Select</button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('script')
<script src="{{ asset('assets/js/select2/select2.full.min.js') }}"></script>
<script>
    $(document).ready(function() {
        $('.select2').each(function() {
            $(this).select2({ 
                placeholder: "Select options",
                dropdownParent: $(this).parent()
            });
        });

        // $('#agent_id').on('change', function() {
        //     var agentId = $(this).val();
        //     var exclude_id = '{{ $isEdit ? $lead->id : "" }}';
        //     var msgDiv = $('#agent_validation_msg');
        //     var btn = $('#agent-lead-form').find('button[type="submit"]');

        //     if (agentId) {
        //         $.ajax({
        //             url: '{{ route("lead.agent_leads.check-agent") }}',
        //             type: 'POST',
        //             data: {
        //                 _token: '{{ csrf_token() }}',
        //                 agent_id: agentId,
        //                 exclude_id: exclude_id
        //             },
        //             success: function(response) {
        //                 if (response.status === 'exists') {
        //                     var leadLinkText = response.is_own 
        //                         ? `<a href="${response.link}" target="_blank" class="fw-bold text-danger text-decoration-underline">${response.lead_no}</a>` 
        //                         : `<span class="fw-bold">${response.lead_no}</span>`;
                                
        //                     msgDiv.html(`<i class="fa fa-warning"></i> Active lead already exists: ${leadLinkText} managed by <strong>${response.managed_by}</strong>. Close that pipeline first.`).removeClass('text-success text-info text-warning').addClass('text-danger').show();
        //                     btn.prop('disabled', true);
        //                 } else if (response.status === 'repeat') {
        //                     msgDiv.html(`<i class="fa fa-refresh"></i> Repeat Agent`).removeClass('text-danger text-success text-warning').addClass('text-info fw-bold').show();
        //                     btn.prop('disabled', false);
        //                 } else if (response.status === 'recover') {
        //                     msgDiv.html(`<i class="fa fa-undo"></i> Recover Agent`).removeClass('text-danger text-success text-info').addClass('text-warning text-dark fw-bold').show();
        //                     btn.prop('disabled', false);
        //                 } else {
        //                     msgDiv.hide();
        //                     btn.prop('disabled', false);
        //                 }
        //             }
        //         });
        //     } else {
        //         msgDiv.hide();
        //         btn.prop('disabled', false);
        //     }
        // });

        function populateCities(selector, stateVal) {
            var $city = $(selector);
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
                    $city.append('<option value="' + city.name + '">' + city.name + '</option>');
                });
                $city.prop('disabled', false);
                if ($.fn.select2) $city.trigger('change.select2');
            });
        }

        $('#quick-agent-state').on('change', function() {
            populateCities('#quick-agent-city', $(this).val());
        });

        // Phone check button
        $('#check-phone-btn').on('click', function () {
            var phone = $('#quick-agent-phone').val();
            var $result = $('#phone-check-result');

            if (phone.length < 10) {
                $result.html('<span class="text-danger">Please enter a valid 10-digit number first.</span>');
                return;
            }

            $result.html('<span class="text-muted"><i class="fa fa-spinner fa-spin"></i> Checking...</span>');

            $.get('{{ route("lead.agent.check_phone") }}', { phone: phone }, function (res) {
                if (res.found) {
                    $result.html('<span class="text-success"><i class="fa fa-check-circle"></i> Found in Customer Master — details prefilled.</span>');
                    $('#quick-agent-customer-id').val(res.agent_customer_id);
                    $('#quick-agent-name').val(res.name).prop('readonly', true);
                    $('#quick-agent-firm').val(res.firm_name).prop('readonly', true);
                    $('#quick-agent-form').find('button[type="submit"]').prop('disabled', false);

                    // Set state dropdown and trigger city load
                    var stateOpt = $('#quick-agent-state option').filter(function() {
                        return $(this).val().toUpperCase() === res.state.toUpperCase();
                    });
                    if (stateOpt.length) {
                        $('#quick-agent-state').val(stateOpt.val()).trigger('change');
                        // After cities load, set city
                        setTimeout(function () {
                            var cityOpt = $('#quick-agent-city option').filter(function() {
                                return $(this).val().toUpperCase() === res.city.toUpperCase();
                            });
                            if (cityOpt.length) {
                                $('#quick-agent-city').val(cityOpt.val()).trigger('change.select2');
                            }
                        }, 800);
                    }
                } else {
                    if (res.is_customer) {
                        $result.html('<span class="text-danger"><i class="fa fa-times-circle"></i> '+res.message+'</span>');
                        $('#quick-agent-form').find('button[type="submit"]').prop('disabled', true);
                    } else {
                        $result.html('<span class="text-warning"><i class="fa fa-info-circle"></i> Not found in Customer Master — please fill details manually.</span>');
                        $('#quick-agent-form').find('button[type="submit"]').prop('disabled', false);
                    }
                    $('#quick-agent-customer-id').val(0);
                    $('#quick-agent-name').val('').prop('readonly', false);
                    $('#quick-agent-firm').val('').prop('readonly', false);
                }
            });
        });

        // Auto-check when phone reaches 10 digits; clear when fewer
        $('#quick-agent-phone').on('input keyup', function () {
            var len = $(this).val().length;
            if (len === 10) {
                $('#check-phone-btn').trigger('click');
            } else {
                // Clear prefilled fields and unlock them
                $('#quick-agent-customer-id').val(0);
                $('#quick-agent-name').val('').prop('readonly', false);
                $('#quick-agent-firm').val('').prop('readonly', false);
                $('#phone-check-result').html('');
                $('#quick-agent-form').find('button[type="submit"]').prop('disabled', false);
            }
        });

        // Reset modal on close
        $('#quickAddAgentModal').on('hidden.bs.modal', function () {
            $('#quick-agent-form')[0].reset();
            $('#quick-agent-customer-id').val(0);
            $('#quick-agent-name, #quick-agent-firm').prop('readonly', false);
            $('#phone-check-result').html('');
            if ($.fn.select2) {
                $('#quick-agent-state, #quick-agent-city').val('').trigger('change.select2');
            }
        });

        $('#quick-agent-form').on('submit', function(e) {
            e.preventDefault();
            const phone = $('#quick-agent-phone').val();
            if (phone.length !== 10) {
                alert('Please enter a valid 10-digit phone number.');
                return false;
            }
            if ($('#phone-check-result').find('.text-danger').length > 0) {
                alert('This phone number belongs to a Customer. Please change it.');
                return false;
            }

            $.ajax({
                url: '{{ route("lead.agent.agent.store") }}',
                type: 'POST',
                data: $(this).serialize(),
                success: function(res) {
                    $('#quickAddAgentModal').modal('hide');
                    // Refresh agents list
                    $.get('{{ route("lead.agent.agents_json") }}', function(agents) {
                        let html = '<option value="">Select Agent</option>';
                        agents.forEach(a => {
                            html += `<option value="${a.id}">${a.name} (${a.firm_name || ''})</option>`;
                        });
                        $('#agent_id').html(html).val(res.agent_id).trigger('change');
                    });
                    $.notify({title:'Success', message:'Agent added successfully'}, {type:'success'});
                },
                error: function(xhr) {
                    var errors = xhr.responseJSON && xhr.responseJSON.errors;
                    if (errors) {
                        var msg = Object.values(errors).flat().join('\n');
                        alert(msg);
                    } else {
                        alert('Something went wrong. Please try again.');
                    }
                }
            });
        });

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

        // Repeater Logic
        let rowIdx = 1;
        $('#add-row').on('click', function() {
            const html = `
                <div class="repeater-item border p-3 rounded mb-3 bg-light position-relative">
                    <button type="button" class="btn-close position-absolute top-0 end-0 m-2 remove-row" style="font-size: 0.7rem;"></button>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Name of Job <span>*</span></label>
                                <input type="text" name="leads[${rowIdx}][name_of_job]" class="form-control text-uppercase" placeholder="ENTER JOB NAME" required style="text-transform: uppercase;">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Process (Status) <span>*</span></label>
                                <select name="leads[${rowIdx}][status_id]" class="form-select" required>
                                    @foreach($statuses as $status)
                                    <option value="{{ $status->id }}">{{ $status->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="mb-0">
                                <label class="form-label">Remarks</label>
                                <textarea name="leads[${rowIdx}][remarks]" class="form-control" rows="2" placeholder="Initial communication remarks..."></textarea>
                            </div>
                        </div>
                    </div>
                </div>`;
            $('#lead-repeater').append(html);
            rowIdx++;
        });

        $(document).on('click', '.remove-row', function() {
            $(this).closest('.repeater-item').fadeOut(300, function() {
                $(this).remove();
            });
        });
    });
</script>
@endsection
