@extends('layouts.admin.app')

@php
    $isEdit = isset($lead);
    $title = $isEdit ? 'Edit Lead: ' . $lead->lead_no : 'Add New Lead';
    $action = $isEdit ? route('lead.update', $lead->id) : route('lead.store');
@endphp

@section('title', $title)

@section('css')
<link rel="stylesheet" type="text/css" href="{{ asset('assets/css/vendors/select2.css') }}">
@endsection

@section('breadcrumb-items')
    <li class="breadcrumb-item"><a href="{{ route('lead.index') }}">Leads</a></li>
    <li class="breadcrumb-item active">{{ $isEdit ? 'Edit' : 'Add' }}</li>
@endsection

@section('content')
<div class="container-fluid">
    <div class="card">
        <form action="{{ $action }}" method="POST" id="lead-form">
            @csrf
            @if($isEdit) @method('PUT') @endif
            
            <div class="card-body">
                {{-- Row 1: Basic Info --}}
                <div class="row">
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label">Lead Number</label>
                            <input type="text" class="form-control" value="{{ $isEdit ? $lead->lead_no : $leadNo }}" readonly style="background-color: #f8f9fa; font-weight: 600;">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label">Client Details <span>*</span></label>
                            <input type="text" name="name" class="form-control" value="{{ old('name', $lead->name ?? '') }}" placeholder="Enter Client/Company Name" required>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label">Phone Number (10 Digits) <span>*</span></label>
                            <input type="number" name="phone" id="phone_input" class="form-control" value="{{ old('phone', $lead->phone ?? '') }}" placeholder="Enter 10 digit number" 
                                   onKeyPress="if(this.value.length==10) return false;" 
                                   onkeydown="return event.keyCode !== 69" required>
                            <div id="phone_validation_msg" class="mt-1" style="font-size: 13px; display: none;"></div>
                        </div>
                    </div>
                </div>

                {{-- Row 2: Location --}}
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">State <span>*</span></label>
                            <select name="state" id="state_select" class="form-select select2" required>
                                <option value="">Select State</option>
                                @foreach($states as $state)
                                <option value="{{ $state }}" {{ old('state', $lead->state ?? '') == $state ? 'selected' : '' }}>{{ $state }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">City Name</label>
                            <div class="input-group">
                                <select name="city" id="city_select" class="form-select select2">
                                    <option value="">Select State First</option>
                                    @if(old('city', $lead->city ?? ''))
                                    <option value="{{ old('city', $lead->city ?? '') }}" selected>{{ old('city', $lead->city ?? '') }}</option>
                                    @endif
                                </select>
                                <button class="btn btn-outline-primary quick-add-city-btn" type="button" 
                                        data-state-selector="#state_select" 
                                        data-city-selector="#city_select" title="Quick Add City">
                                    <i class="fa fa-plus"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Row 3: Source, Staff, Step --}}
                <div class="row">
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label">Lead Source</label>
                            <select name="source_id" class="form-select select2">
                                <option value="">Select Source</option>
                                @foreach($sources as $source)
                                <option value="{{ $source->id }}" {{ old('source_id', $lead->source_id ?? '') == $source->id ? 'selected' : '' }}>{{ $source->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    @if(auth()->user()->role_as == 'Admin')
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label">Assigned To staff</label>
                            <select name="assigned_user_id" class="form-select select2">
                                <option value="">Select Staff</option>
                                @foreach($users as $user)
                                <option value="{{ $user->id }}" {{ old('assigned_user_id', $lead->assigned_user_id ?? (Auth::id())) == $user->id ? 'selected' : '' }}>
                                    {{ $user->name }} ({{ $user->role }})
                                </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    @else
                        <input type="hidden" name="assigned_user_id" value="{{ $lead->assigned_user_id ?? Auth::id() }}">
                    @endif
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label">Regarding/Requirement</label>
                            <input type="text" name="regarding" class="form-control" value="{{ old('regarding', $lead->regarding ?? '') }}" placeholder="What is the requirement?">
                        </div>
                    </div>
                </div>

                {{-- Row 4: Tags & Remarks --}}
                <div class="row">
                    <div class="col-md-12">
                        <div class="mb-3">
                            <label class="form-label">Lead Tags (Select Multiple)</label>
                            @php 
                                $selectedTags = $isEdit ? $lead->tags->pluck('id')->toArray() : [];
                            @endphp
                            <select name="tags[]" class="form-select select2" multiple="multiple">
                                @foreach($tags as $tag)
                                <option value="{{ $tag->id }}" {{ in_array($tag->id, old('tags', $selectedTags)) ? 'selected' : '' }}>{{ $tag->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="col-md-12">
                        <div class="mb-3">
                            <label class="form-label">General Lead Remarks</label>
                            <textarea name="lead_remarks" class="form-control" rows="3" placeholder="Additional details about this lead...">{{ old('lead_remarks', $lead->lead_remarks ?? '') }}</textarea>
                        </div>
                    </div>
                </div>

                {{-- Bottom Section: Only for New Leads --}}
                @if(!$isEdit)
                <div class="mt-4 pt-3 border-top">
                    <h6 class="mb-4">Initial Followup & Remarks</h6>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Followup after (Days) <span>*</span></label>
                                <input type="number" name="next_followup" class="form-control" value="1" min="0" placeholder="e.g. 4 for 4 days later" required>
                                <small class="text-muted">Will be scheduled at 12:00 PM</small>
                            </div>
                        </div>

                        <div class="col-md-8">
                            <div class="mb-3">
                                <label class="form-label">Initial Communication Notes (Remarks)</label>
                                <textarea name="remarks" class="form-control" rows="5" placeholder="Detail about the first communication..."></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                @endif
            </div>
            
            <div class="card-footer text-end">
                <button type="reset" class="btn btn-light me-2">Reset</button>
                <button type="submit" id="submit_btn" class="btn btn-primary">{{ $isEdit ? 'Update Lead Details' : 'Create Lead Entry' }}</button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('script')
<script src="{{ asset('assets/js/select2/select2.full.min.js') }}"></script>
<script>
    $(document).ready(function() {
        $('.select2').select2({
            placeholder: "Select options"
        });

        $('#phone_input').on('keyup', function() {
            var phone = $(this).val();
            var exclude_id = '{{ $isEdit ? $lead->id : "" }}';
            var msgDiv = $('#phone_validation_msg');
            var btn = $('#submit_btn');
            var nameInput = $('input[name="name"]');
            var stateSelect = $('#state_select');
            var citySelect = $('#city_select');

            if (phone.length === 10) {
                $.ajax({
                    url: '{{ route("lead.check-phone") }}',
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        phone: phone,
                        exclude_id: exclude_id
                    },
                    success: function(response) {
                        // Prefill data if exists (either from Lead or Customer Master)
                        if (response.name) {
                            nameInput.val(response.name).prop('readonly', true);
                            if (response.state) {
                                stateSelect.val(response.state).trigger('change.select2').prop('disabled', true);
                                populateCities(response.state, response.city);
                                citySelect.prop('disabled', true);
                            }
                            
                            // Add hidden inputs to ensure data is submitted since disabled fields aren't
                            if (!$('#hidden_state').length) {
                                $('#lead-form').append('<input type="hidden" name="state" id="hidden_state" value="'+(response.state || '')+'">');
                                $('#lead-form').append('<input type="hidden" name="city" id="hidden_city" value="'+(response.city || '')+'">');
                                $('#lead-form').append('<input type="hidden" name="prefilled_name" id="hidden_name" value="'+(response.name || '')+'">');
                            } else {
                                $('#hidden_state').val(response.state || '');
                                $('#hidden_city').val(response.city || '');
                                $('#hidden_name').val(response.name || '');
                            }

                            if (response.is_master && response.status === 'clear') {
                                msgDiv.html('<i class="fa fa-info-circle"></i> Found in Customer Master — details prefilled.').removeClass('text-danger text-warning text-info').addClass('text-success fw-bold').show();
                            }
                        }

                        if (response.status === 'exists') {
                            var leadLinkText = response.is_own 
                                ? `<a href="${response.link}" target="_blank" class="fw-bold text-danger text-decoration-underline">${response.lead_no}</a>` 
                                : `<span class="fw-bold">${response.lead_no}</span>`;
                                
                            msgDiv.html(`<i class="fa fa-warning"></i> Active lead already exists: ${leadLinkText} managed by <strong>${response.managed_by}</strong>. Close that pipeline first.`).removeClass('text-success text-info text-warning').addClass('text-danger').show();
                            btn.prop('disabled', true);
                        } else if (response.status === 'repeat') {
                            msgDiv.html(`<i class="fa fa-refresh"></i> Repeat Lead (Previous Customer)`).removeClass('text-danger text-success text-warning').addClass('text-info fw-bold').show();
                            btn.prop('disabled', false);
                        } else if (response.status === 'recover') {
                            msgDiv.html(`<i class="fa fa-undo"></i> Recover Lead (Previously Lost)`).removeClass('text-danger text-success text-info').addClass('text-warning text-dark fw-bold').show();
                            btn.prop('disabled', false);
                        } else if (response.status === 'clear' && !response.is_master) {
                            msgDiv.hide();
                            btn.prop('disabled', false);
                        } else if (response.status === 'clear' && response.is_master) {
                             btn.prop('disabled', false);
                        }
                    }
                });
            } else {
                // If not 10 digits, we still want to keep messages hidden unless it's a validation error
                msgDiv.hide();
                btn.prop('disabled', false);
                
                // Unlock fields and clear hidden inputs
                nameInput.prop('readonly', false);
                stateSelect.prop('disabled', false).trigger('change.select2');
                citySelect.prop('disabled', false).trigger('change.select2');
                $('#hidden_state, #hidden_city, #hidden_name').remove();
            }
        });

        $('#lead-form').on('submit', function(e) {
            const phone = $('#phone_input').val();
            if (phone.length !== 10) {
                alert('Please enter a valid 10-digit phone number.');
                e.preventDefault();
                return false;
            }
            if ($('#submit_btn').is(':disabled')) {
                e.preventDefault();
                return false;
            }
        });
        // ── State → City Cascade ──
        function populateCities(stateVal, selectedCity) {
            var $city = $('#city_select');
            $city.empty().append('<option value="">Loading...</option>');
            
            if (!stateVal) {
                $city.empty().append('<option value="">Select State First</option>');
                return;
            }

            $.get("{{ url('lead/locations/get-cities') }}/" + encodeURIComponent(stateVal), function(data) {
                $city.empty().append('<option value="">Select City</option>');
                var found = false;
                $.each(data, function(i, city) {
                    var sel = (city.name === selectedCity) ? ' selected' : '';
                    if (city.name === selectedCity) found = true;
                    $city.append('<option value="' + city.name + '"' + sel + '>' + city.name + '</option>');
                });
                
                // If saved city not in list, add it as custom
                if (selectedCity && !found) {
                    $city.append('<option value="' + selectedCity + '" selected>' + selectedCity + ' (Custom)</option>');
                }
                try { $city.trigger('change.select2'); } catch(e){}
            });
        }

        var savedCity  = '{{ old("city", $lead->city ?? "") }}';
        var savedState = '{{ old("state", $lead->state ?? "") }}';

        // Init on load if editing
        if (savedState) populateCities(savedState, savedCity);

        // On state change
        $('#state_select').on('change', function() {
            populateCities($(this).val(), '');
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
    });
</script>
@endsection
