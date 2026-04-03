@extends('layouts.admin.app')

@section('title', 'Customer Ledger Summary')

@section('css')
<style>
    .ledger-card { border-radius: 15px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); border: none; }
    .badge-dr { background: #fee2e2; color: #b91c1c; border-radius: 6px; padding: 4px 10px; font-weight: 700; }
    .badge-cr { background: #dcfce7; color: #166534; border-radius: 6px; padding: 4px 10px; font-weight: 700; }
    .balance-neutral { background: #f1f5f9; color: #475569; border-radius: 6px; padding: 4px 10px; font-weight: 700; }
    
    .dt-controls-wrap {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 15px;
        margin-bottom: 20px;
    }
    .dt-controls-item {
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .dt-controls-item label {
        margin-bottom: 0;
        font-weight: 700;
        font-size: 10px;
        text-transform: uppercase;
        color: #64748b;
        white-space: nowrap;
    }
    .select2-container .select2-selection--single{
        height:30px !important;
        padding:5px !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow{
        height:12px !important;
    }
</style>
@endsection

@section('breadcrumb-items')
    <li class="breadcrumb-item">Customer Ledger</li>
@endsection

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card ledger-card mb-4 border-0 shadow-sm overflow-hidden">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-4">
                            <div>
                                <h4 class="mb-1 fw-bold text-primary">Customer Ledger Summary</h4>
                                <p class="mb-0 text-muted small text-uppercase fw-bold">Overview of all outstanding balances</p>
                            </div>
                            <div class="d-flex gap-2">
                                <button type="button" onclick="exportData('excel')" class="btn btn-success-gradien shadow-sm fw-bold">
                                    <i class="fa fa-file-excel-o me-2"></i> EXCEL
                                </button>
                                <button type="button" onclick="exportData('pdf')" class="btn btn-danger-gradien shadow-sm fw-bold">
                                    <i class="fa fa-file-pdf-o me-2"></i> PDF
                                </button>
                            </div>
                        </div>

                        <div class="dt-controls-wrap px-2 mt-2">
                            <div class="dt-controls-item">
                                <label>Show</label>
                                <select id="basic-2_value" class="form-control form-control-sm" style="width: auto;">
                                    <option value="50" selected>50</option>
                                    <option value="250" >250</option>
                                    <option value="500">500</option>
                                </select>
                            </div>

                            <div class="dt-controls-item">
                                <label>Outstanding From</label>
                                <input type="date" id="from_date" value="" class="form-control form-control-sm" onchange="get_datatable()">
                                <label>To</label>
                                <input type="date" id="to_date" value="" class="form-control form-control-sm" onchange="get_datatable()">
                            </div>

                            <div class="dt-controls-item">
                                <label>Type</label>
                                <select id="role_filter" class="form-control form-control-sm" onchange="get_datatable()">
                                    <option value="">All Types</option>
                                    @foreach($roles as $role)
                                        <option value="{{ $role }}">{{ $role }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="dt-controls-item">
                                <label>Sale Executive</label>
                                <select id="sale_executive_filter" class="form-control form-control-sm" onchange="get_datatable()" style="min-width: 150px;">
                                    <option value="">All Executives</option>
                                    @foreach($sale_executives as $executive)
                                        <option value="{{ $executive->id }}">{{ $executive->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="dt-controls-item">
                                <label>Balance</label>
                                <select id="balance_filter" class="form-control form-control-sm" onchange="get_datatable()">
                                    <option value="active" selected>Active Only (Non-Zero)</option>
                                    <option value="all">Show All (Including Zero)</option>
                                </select>
                            </div>

                            <div class="dt-controls-item">
                                <label>Bad Debt</label>
                                <select id="is_bad_debt_filter" class="form-control form-control-sm" onchange="get_datatable()">
                                    <option value="all" selected>All Customers</option>
                                    <option value="1">Bad Debt Only</option>
                                    <option value="0">Regular Only</option>
                                </select>
                            </div>

                            <div class="dt-controls-item">
                                <label>Search</label>
                                <input type="search" id="basic-2_search" class="form-control form-control-sm" placeholder="Search name, phone, code..." style="min-width: 200px;">
                            </div>
                        </div>
                        
                        <div class="dt-ext" id="get_datatable">
                            <div class="loader-box"><div class="loader-37"></div></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <!-- Quick Followup Modal -->
    <div class="modal fade" id="quickFollowupModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form id="quick_followup_form" class="modal-content border-0 shadow-lg">
                @csrf
                <input type="hidden" name="customer_id" id="followup_customer_id">
                <div class="modal-header bg-warning text-dark py-3">
                    <h5 class="modal-title fw-bold"><i class="fa fa-calendar-plus-o me-2"></i> Add Followup for <span id="followup_customer_name"></span></h5>
                    <button type="button" class="btn-close btn-close-dark" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold small">Date & Time</label>
                        <input type="datetime-local" name="followup_date_time" class="form-control" value="{{ date('Y-m-d\TH:i') }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small">Subject</label>
                        <input type="text" name="subject" class="form-control" placeholder="E.g. Payment Reminder..." required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small">Remarks / Response</label>
                        <textarea name="remarks" class="form-control" rows="3" placeholder="What did the customer say?"></textarea>
                    </div>
                    <input type="hidden" name="status" value="Continue">
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-warning px-4 fw-bold">SAVE FOLLOWUP</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('script')
    <script>
        $(document).ready(function(){
            $('.js-example-basic-single').select2({
                width: '100%'
            });
            get_datatable();
        });


        function debounce(func, wait) {
            let timeout;
            return function(...args) {
                const context = this;
                clearTimeout(timeout);
                timeout = setTimeout(() => func.apply(context, args), wait);
            };
        }

        var currentSearchRequest = null;
        function get_datatable(page){
            var $container = $('#get_datatable');
            if ($container.length) {
                if (currentSearchRequest) {
                    currentSearchRequest.abort();
                }
                $container.html('<div class="loader-box"><div class="loader-37"></div><p class="text-center text-muted small mt-2">Updating data...</p></div>');
                
                var search = $('#basic-2_search').val();
                var value = $('#basic-2_value').val();
                var from_date = $('#from_date').val();
                var to_date = $('#to_date').val();
                var role = $('#role_filter').val();
                var sale_executive_id = $('#sale_executive_filter').val();
                var balance_type = $('#balance_filter').val();
                var is_bad_debt = $('#is_bad_debt_filter').val();
                var page = page ?? 1;
                
                currentSearchRequest = $.ajax({
                    url: '{{ route("customer_ledger.datatable") }}',
                    data: { page: page, search: search, value: value, from_date: from_date, to_date: to_date, role: role, sale_executive_id: sale_executive_id, balance_type: balance_type, is_bad_debt: is_bad_debt },
                    type: 'GET',
                    success: function(data){
                        $container.html(data);
                        $('#basic-test').DataTable({ dom: 'rt', "pageLength": -1 , responsive: true, ordering: false});
                        currentSearchRequest = null;
                    },
                    error: function(xhr, status, error){
                        if(status !== 'abort') {
                            console.error('Data fetch error');
                            currentSearchRequest = null;
                        }
                    }
                });
            }
        }

        function openFollowupModal(id, name) {
            $('#followup_customer_id').val(id);
            $('#followup_customer_name').text(name);
            $('#quickFollowupModal').modal('show');
        }

        $('#quick_followup_form').submit(function(e){
            e.preventDefault();
            var $btn = $(this).find('button[type="submit"]');
            $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin me-1"></i> SAVING...');
            
            $.ajax({
                url: '{{ route("ledger_followup.store") }}',
                type: 'POST',
                data: $(this).serialize(),
                success: function(res){
                    if(res.result == 1){
                        $.notify({title:'Success', message:res.message}, {type:'success'});
                        $('#quickFollowupModal').modal('hide');
                        $('#quick_followup_form')[0].reset();
                    }
                    $btn.prop('disabled', false).html('SAVE FOLLOWUP');
                },
                error: function(){
                    $.notify({title:'Error', message:'Something went wrong'}, {type:'danger'});
                    $btn.prop('disabled', false).html('SAVE FOLLOWUP');
                }
            });
        });

        $(document).on('click','.pages a',function(n){
            n.preventDefault();
            var page = $(this).attr('href').split("page=")[1];
            get_datatable(page);
        });

        $('#basic-2_search').on('input search', debounce(function() { 
            get_datatable(); 
        }, 400));
        $('#basic-2_value').on('change', function() { get_datatable(); });

        function exportData(type) {
            var search = $('#basic-2_search').val();
            var from_date = $('#from_date').val();
            var to_date = $('#to_date').val();
            var role = $('#role_filter').val();
            var sale_executive_id = $('#sale_executive_filter').val();
            var balance_type = $('#balance_filter').val();
            var is_bad_debt = $('#is_bad_debt_filter').val();
            
            var url = (type === 'excel') ? '{{ route("customer_ledger.export_excel") }}' : '{{ route("customer_ledger.export_pdf") }}';
            var queryParams = $.param({
                search: search,
                from_date: from_date,
                to_date: to_date,
                role: role,
                sale_executive_id: sale_executive_id,
                balance_type: balance_type,
                is_bad_debt: is_bad_debt
            });
            
            window.open(url + '?' + queryParams, '_blank');
        }

    </script>
@endsection
