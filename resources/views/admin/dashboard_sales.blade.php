@extends('layouts.admin.app')

@section('title', 'Sales Performance Dashboard')

@section('css')
<style>
    .sales-widget-card {
        transition: all 0.3s ease;
        border: none;
        border-radius: 15px;
        overflow: hidden;
    }
    .sales-widget-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.1);
    }
    .widget-icon-bg {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
    }
    .bg-light-primary { background: rgba(115, 102, 255, 0.1); color: #7366ff; }
    .bg-light-success { background: rgba(81, 187, 37, 0.1); color: #51bb25; }
    .bg-light-warning { background: rgba(248, 214, 43, 0.1); color: #f8d62b; }
    .bg-light-danger { background: rgba(220, 53, 69, 0.1); color: #dc3545; }
    .bg-light-info { background: rgba(0, 150, 136, 0.1); color: #009688; }
    
    .dev-badge {
        font-size: 0.65rem;
        padding: 2px 8px;
        border-radius: 50px;
        background: #eee;
        color: #999;
        text-transform: uppercase;
        font-weight: 700;
        letter-spacing: 0.5px;
    }
    
    .progress-round {
        width: 60px;
        height: 60px;
    }
</style>
@endsection

@section('breadcrumb-items')
    <li class="breadcrumb-item">Sales Dashboard</li>
@endsection

@section('content')
    

    <div class="row">
        <div class="col-12 mb-4">
            <div class="card shadow-sm border-0" style="border-radius: 12px; background: #fff;">
                <div class="card-body p-3">
                    <form id="dashboardFilterForm" class="row g-3 align-items-end">
                        @if($isAdmin)
                        <div class="col-md-4">
                            <label class="form-label small fw-bold text-muted mb-1">Sales Executive</label>
                            <select name="executive_id" id="executive_id" class="form-select border shadow-none" style="background-color: #f8f9fa; color: #333; height: 45px; border-radius: 8px;">
                                <option value="">Choose Executive...</option>
                                @foreach($executives as $ex)
                                    <option value="{{ $ex->id }}" {{ ($target_user_id ?? '') == $ex->id ? 'selected' : '' }}>
                                        {{ $ex->name }} ({{ $ex->role_as }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        @else
                        <div class="col-md-4 d-none">
                             <input type="hidden" name="executive_id" value="">
                        </div>
                        @endif
                        <div class="col-md-3">
                            <label class="form-label small fw-bold text-muted mb-1">From Date</label>
                            <input type="date" name="from_date" id="from_date" value="{{ $from_date ? $from_date->format('Y-m-d') : '' }}" class="form-control border shadow-none" style="background-color: #f8f9fa; color: #333; height: 45px; border-radius: 8px;">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-bold text-muted mb-1">To Date</label>
                            <input type="date" name="to_date" id="to_date" value="{{ $to_date ? $to_date->format('Y-m-d') : '' }}" class="form-control border shadow-none" style="background-color: #f8f9fa; color: #333; height: 45px; border-radius: 8px;">
                        </div>
                        <div class="col-md-2">
                             <button type="button" onclick="updateDashboardRealtime()" class="btn btn-primary w-100 fw-bold border-0" style="height: 45px; border-radius: 8px; background: linear-gradient(135deg, #6c5ce7 0%, #a29bfe 100%); box-shadow: 0 4px 15px rgba(108, 92, 231, 0.4);">
                                Generate <i class="fa fa-arrow-right ms-1 f-12"></i>
                             </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @if (!$is_under_development)
    <div id="sales-widgets-container" class="{{ ($isAdmin && empty($target_user_id)) ? 'd-none' : '' }}">
    <div class="row">
        <!-- 1. Today Pending Followup -->
        <div class="col-xl-3 col-md-6 mb-4">
            <a id="pending_link" href="{{ route('lead.followup.pending', ['from_date' => $from_date?->format('Y-m-d'), 'to_date' => $to_date?->format('Y-m-d'), 'executive_id' => $target_user_id]) }}" class="text-decoration-none">
                <div class="card sales-widget-card shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <p class="mb-1 text-muted fw-bold">Pending Followups</p>
                                <h3 class="mb-0" id="stat_total_pending">{{ $total_pending_followup }}</h3>
                                <small class="text-primary fw-600">Sales Leads</small>
                            </div>
                            <div class="widget-icon-bg bg-light-primary">
                                <i data-feather="calendar"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        <!-- 2. Today Payment Followup -->
        <div class="col-xl-3 col-md-6 mb-4">
            <a id="payment_link" href="{{ route('ledger_followup.pending_today', ['from_date' => $from_date?->format('Y-m-d'), 'to_date' => $to_date?->format('Y-m-d'), 'executive_id' => $target_user_id]) }}" class="text-decoration-none">
                <div class="card sales-widget-card shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <p class="mb-1 text-muted fw-bold">Payment Followups</p>
                                <h3 class="mb-0" id="stat_payment_followup">{{ $today_payment_followup }}</h3>
                                <small class="text-warning" id="payment_range_label">{{ $from_date ? 'Selected Range' : 'Due Today' }}</small>
                            </div>
                            <div class="widget-icon-bg bg-light-warning">
                                <i data-feather="dollar-sign"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        <!-- 3. Total Job Card Counter -->
        <div class="col-xl-3 col-md-6 mb-4">
            <a id="job_card_link" href="{{ route('job_card.list', ['type' => 'pending', 'executive_id' => $target_user_id ?: auth()->id()]) }}" class="text-decoration-none">
                <div class="card sales-widget-card shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <p class="mb-1 text-muted fw-bold">Order In Process</p>
                                <h3 class="mb-0" id="stat_job_cards">{{ $se_job_cards_count }}</h3>
                                <small class="text-muted">Total Pipeline</small>
                            </div>
                            <div class="widget-icon-bg bg-light-info">
                                <i data-feather="file-text"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        <!-- 4. Incoming Payment -->
        <div class="col-xl-3 col-md-6 mb-4">
            <a href="{{ route('customer_ledger.transactions', [
                'type' => 'Cr', 
                'sale_executive_id' => $target_user_id,
                'from_date' => $from_date ? $from_date->format('Y-m-d') : date('Y-m-d', strtotime('-7 days')),
                'to_date' => $to_date ? $to_date->format('Y-m-d') : date('Y-m-d')
            ]) }}" id="payment_stat_link" class="text-decoration-none">
                <div class="card sales-widget-card shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <p class="mb-1 text-muted fw-bold">Payments</p>
                                <h3 class="mb-0 text-dark" id="stat_incoming_payment">{{ $incoming_payment_formatted }}</h3>
                                <small class="text-success" id="payment_period_label">{{ $from_date ? 'Selected Range' : 'Last 7 Days' }}</small>
                            </div>
                            <div class="widget-icon-bg bg-light-success">
                                <i data-feather="trending-up"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>
    </div>

    <div class="row">
        <!-- 6. Total New Lead -->
        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card sales-widget-card shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h6 class="text-muted fw-bold mb-1">Leads Generated</h6>
                            <h4 class="mb-0" id="stat_new_leads">{{ $new_leads_count }}</h4>
                        </div>
                        <div class="progress-round">
                            <div class="text-end">
                                <span class="badge bg-light-primary text-primary" id="lead_period_label">{{ $from_date ? 'Range' : date('M') }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 11. Conversion Rate -->
        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card sales-widget-card shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h6 class="text-muted fw-bold mb-1">Conversion Ratio</h6>
                            <div class="d-flex align-items-center gap-2">
                                <h4 class="mb-0" id="stat_conversion_rate">{{ $conversion_rate }}%</h4>
                                <span class="text-success small"><i data-feather="arrow-up-right"></i></span>
                            </div>
                        </div>
                        <div class="widget-icon-bg bg-light-success">
                            <i data-feather="user-check"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 9. Orders (Customer Vs Agents) -->
        <div class="col-xl-4 col-md-12 mb-4">
            <div class="card sales-widget-card shadow-sm">
                <div class="card-body">
                    <h6 class="text-muted fw-bold mb-3">Order Distribution</h6>
                    <div class="row text-center">
                        <div class="col-6 border-end">
                            <div class="text-primary h5 mb-0" id="stat_direct_orders">{{ $direct_orders }}</div>
                            <small class="text-muted">Direct</small>
                        </div>
                        <div class="col-6">
                            <div class="text-warning h5 mb-0" id="stat_customer_orders">{{ $customer_orders }}</div>
                            <small class="text-muted">Agents</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- 5. 30 Days Reminder (Under Dev) -->
        <div class="col-md-4 mb-4">
            <div class="card sales-widget-card shadow-sm border-dashed">
                <div class="card-body text-center py-4">
                    <span class="dev-badge mb-2 d-inline-block">Under Development</span>
                    <h6 class="text-muted fw-bold">30 Days Payment Reminder</h6>
                    <div class="mt-2 text-muted small">Parties pending payment for more than 30 days.</div>
                </div>
            </div>
        </div>

        <!-- 10. Target (Under Dev) -->
        <div class="col-md-4 mb-4">
            <div class="card sales-widget-card shadow-sm border-dashed">
                <div class="card-body text-center py-4">
                    <span class="dev-badge mb-2 d-inline-block">Under Development</span>
                    <h6 class="text-muted fw-bold">Monthly Sales Target</h6>
                    <div class="mt-2 text-muted small">Tracking against your assigned monthly revenue.</div>
                </div>
            </div>
        </div>

         <!-- 12. Advance Payment (Under Dev) -->
         <div class="col-md-4 mb-4">
            <div class="card sales-widget-card shadow-sm border-dashed">
                <div class="card-body text-center py-4">
                    <span class="dev-badge mb-2 d-inline-block">Under Development</span>
                    <h6 class="text-muted fw-bold">Advance Payment Tracking</h6>
                    <div class="mt-2 text-muted small">Monitor advance bookings and initial deposits.</div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
         <!-- 7. Repeat Followup -->
         <div class="col-md-6 mb-4">
            <div class="card sales-widget-card shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted fw-bold mb-1">Repeat Customer Action</h6>
                            <h4 class="mb-0">Analytics Coming Soon</h4>
                        </div>
                        <div class="widget-icon-bg bg-light-info">
                            <i data-feather="repeat"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 8. Followup On Time Vs Late -->
        <div class="col-md-6 mb-4">
            <div class="card sales-widget-card shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted fw-bold mb-1">Followup Efficiency</h6>
                            <div class="d-flex gap-3 mt-2">
                                <span class="badge bg-success">On Time: --</span>
                                <span class="badge bg-danger">Late: --</span>
                            </div>
                        </div>
                        <div class="widget-icon-bg bg-light-danger">
                            <i data-feather="clock"></i>
                        </div>
                    </div>
                </div>
        </div>
    </div>
    </div>
    @else
    <div class="row mt-4">
        <div class="col-12 text-center">
            <div class="card shadow-sm border-0 py-5" style="background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);">
                <div class="card-body">
                    <div class="mb-4">
                        <i class="fa fa-tools fa-4x text-primary opacity-50 mb-3 animate__animated animate__pulse animate__infinite"></i>
                    </div>
                    <h2 class="fw-bold text-dark mb-2">Dashboard Under Development</h2>
                    <p class="text-muted fs-5">This module is currently being configured for your role.</p>
                    <div class="mt-4">
                        <span class="badge bg-primary px-3 py-2 rounded-pill">COMING SOON</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection

@section('script')
<script>
    function updateDashboardRealtime() {
        const isAdmin = {{ $isAdmin ? 'true' : 'false' }};
        const executiveId = $('#executive_id').val();
        
        if (isAdmin && !executiveId) {
            $('#sales-widgets-container').addClass('d-none');
            
            // Still update the URL to clear the parameter
            const params = { from_date: $('#from_date').val(), to_date: $('#to_date').val() };
            const currentUrl = new URL(window.location);
            currentUrl.searchParams.delete('executive_id');
            Object.keys(params).forEach(key => {
                if (params[key]) currentUrl.searchParams.set(key, params[key]);
                else currentUrl.searchParams.delete(key);
            });
            window.history.pushState({}, '', currentUrl);
            
            return; // Stop the AJAX request since no user is selected
        }
        
        $('#sales-widgets-container').removeClass('d-none');

        const fromDate = $('#from_date').val();
        const toDate = $('#to_date').val();

        // Update Links
        const pendingUrl = new URL("{{ route('lead.followup.pending') }}", window.location.origin);
        const paymentUrl = new URL("{{ route('ledger_followup.pending_today') }}", window.location.origin);
        const jobCardUrl = new URL("{{ route('job_card.list') }}", window.location.origin);
        const paymentStatUrl = new URL("{{ route('customer_ledger.transactions') }}", window.location.origin);
        
        const params = { from_date: fromDate, to_date: toDate, executive_id: executiveId };
        Object.keys(params).forEach(key => {
            if (params[key]) {
                pendingUrl.searchParams.set(key, params[key]);
                paymentUrl.searchParams.set(key, params[key]);
                paymentStatUrl.searchParams.set(key === 'executive_id' ? 'sale_executive_id' : key, params[key]);
            }
        });
        
        // Custom override for job card which ignores dates
        const fallbackExec = executiveId ? executiveId : "{{ auth()->id() }}";
        if (fallbackExec) jobCardUrl.searchParams.set('executive_id', fallbackExec);
        jobCardUrl.searchParams.set('type', 'pending');
        paymentStatUrl.searchParams.set('type', 'Cr');

        $('#pending_link').attr('href', pendingUrl.toString());
        $('#payment_link').attr('href', paymentUrl.toString());
        $('#job_card_link').attr('href', jobCardUrl.toString());
        $('#payment_stat_link').attr('href', paymentStatUrl.toString());

        // Perform AJAX request to get updated stats
        $.ajax({
            url: "{{ route('dashboard') }}",
            type: 'GET',
            data: params,
            beforeSend: function() {
                // Optional: add opacity or loader
                $('.sales-widget-card').css('opacity', '0.6');
            },
            success: function(response) {
                $('.sales-widget-card').css('opacity', '1');
                
                // Update Numeric Stats
                $('#stat_total_pending').text(response.total_pending_followup);
                $('#stat_payment_followup').text(response.today_payment_followup);
                $('#stat_job_cards').text(response.se_job_cards_count);
                $('#stat_incoming_payment').text(response.incoming_payment_formatted);
                $('#stat_new_leads').text(response.new_leads_count);
                $('#stat_conversion_rate').text(response.conversion_rate + '%');
                $('#stat_direct_orders').text(response.direct_orders);
                $('#stat_customer_orders').text(response.customer_orders);

                // Update Labels
        const isRange = response.is_range;
        $('#payment_range_label').text(isRange ? 'Selected Range' : 'Due Today');
        $('#payment_period_label').text(isRange ? 'Selected Range' : 'Last 7 Days');
        $('#lead_period_label').text(isRange ? 'Range' : "{{ date('M') }}");

                // Update Browser URL without reload
                const currentUrl = new URL(window.location);
                Object.keys(params).forEach(key => {
                    if (params[key]) currentUrl.searchParams.set(key, params[key]);
                    else currentUrl.searchParams.delete(key);
                });
                window.history.pushState({}, '', currentUrl);
            },
            error: function() {
                $('.sales-widget-card').css('opacity', '1');
            }
        });
    }

    $(document).ready(function() {
        if(typeof feather !== 'undefined') {
            feather.replace();
        }

        $('#executive_id, #from_date, #to_date').on('change', function() {
            updateDashboardRealtime();
        });
    });
</script>
@endsection
