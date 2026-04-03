@extends('layouts.admin.app')

@section('title', 'Agent Performance Report')

@section('breadcrumb-items')
    <li class="breadcrumb-item">Agent Lead</li>
    <li class="breadcrumb-item active">Performance Report</li>
@endsection

@section('css')
<style>
    .report-card { border-radius: 12px; transition: transform 0.3s; border: none; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
    .report-card:hover { transform: translateY(-5px); }
    .metric-value { font-size: 24px; font-weight: 700; }
    .metric-label { font-size: 12px; color: #666; text-transform: uppercase; letter-spacing: 0.5px; }
    .filter-card { background: #f8f9fd; border: 1px solid #e0e6ed; }
</style>
@endsection

@section('content')
<div class="container-fluid">
    <!-- Filters Area -->
    <div class="card filter-card mb-4">
        <div class="card-body p-3">
            <form id="report-filter-form">
                <div class="row g-3">
                    <div class="col-md-2">
                        <label class="form-label f-12">From Date</label>
                        <input type="date" name="from_date" class="form-control form-control-sm trigger-filter">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label f-12">To Date</label>
                        <input type="date" name="to_date" class="form-control form-control-sm trigger-filter">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label f-12">Lead Step</label>
                        <select name="status_id" class="form-select form-select-sm trigger-filter">
                            <option value="">All Steps</option>
                            @foreach($statuses as $st)
                                <option value="{{ $st->id }}">{{ $st->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label f-12">Select Agent</label>
                        <select name="agent_id" class="form-select form-select-sm trigger-filter">
                            <option value="">All Agents</option>
                            @foreach($agents as $ag)
                                <option value="{{ $ag->id }}">{{ $ag->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label f-12">Assigned To</label>
                        <select name="assigned_user_id" class="form-select form-select-sm trigger-filter">
                            <option value="">All Users</option>
                            @foreach($users as $u)
                                <option value="{{ $u->id }}">{{ $u->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label f-12">Added By</label>
                        <select name="added_by" class="form-select form-select-sm trigger-filter">
                            <option value="">All Staff</option>
                            @foreach($users as $u)
                                <option value="{{ $u->id }}">{{ $u->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label f-12">State</label>
                        <select name="state" id="rpt_state" class="form-select form-select-sm trigger-filter">
                            <option value="">All States</option>
                            @foreach($states as $st)
                            <option value="{{ $st }}">{{ $st }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label f-12">City</label>
                        <select name="city" id="rpt_city" class="form-select form-select-sm trigger-filter">
                            <option value="">All Cities</option>
                        </select>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="button" onclick="loadReport()" class="btn btn-primary btn-sm w-100"><i class="fa fa-refresh"></i> Refresh</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- AJAX Container -->
    <div id="report-container">
        <div class="text-center py-5">
            <div class="loader-box">
                <div class="loader-3"></div>
            </div>
            <p class="mt-3 text-muted">Analyzing agent lead data, please wait...</p>
        </div>
    </div>
</div>
@endsection

@section('script')
<script src="{{ asset('assets/js/chart/apex-chart/apex-chart.js') }}"></script>
<script>
    function loadReport(page = 1) {
        $('#report-container').html(`
            <div class="text-center py-5">
                <div class="spinner-border text-primary" role="status"></div>
                <p class="mt-3 text-muted f-w-600">Processing Agent Leads... Please wait.</p>
            </div>
        `);
        
        let formData = $('#report-filter-form').serialize();
        $.ajax({
            url: "{{ route('lead.agent_leads.report.data') }}?page=" + page,
            type: "GET",
            data: formData,
            success: function(res) {
                $('#report-container').html(res);
            },
            error: function() {
                $('#report-container').html('<div class="alert alert-danger">Error loading report data. Please try again.</div>');
            }
        });
    }

    $(document).ready(function() {
        loadReport();

        var reportStateCities = {
            'Andhra Pradesh': ['Visakhapatnam','Vijayawada','Guntur','Nellore','Kurnool','Tirupati','Kakinada','Rajahmundry','Kadapa','Eluru'],
            'Arunachal Pradesh': ['Itanagar','Naharlagun','Pasighat','Tawang','Ziro'],
            'Assam': ['Guwahati','Silchar','Dibrugarh','Jorhat','Nagaon','Tinsukia','Tezpur','Bongaigaon'],
            'Bihar': ['Patna','Gaya','Bhagalpur','Muzaffarpur','Purnia','Darbhanga','Arrah','Begusarai','Katihar','Munger'],
            'Chhattisgarh': ['Raipur','Bhilai','Bilaspur','Korba','Durg','Rajnandgaon','Jagdalpur'],
            'Goa': ['Panaji','Margao','Vasco da Gama','Mapusa','Ponda'],
            'Gujarat': ['Ahmedabad','Surat','Vadodara','Rajkot','Bhavnagar','Jamnagar','Junagadh','Gandhinagar','Anand','Navsari','Mehsana','Morbi','Bharuch','Surendranagar'],
            'Haryana': ['Gurgaon','Faridabad','Panipat','Ambala','Yamunanagar','Rohtak','Hisar','Karnal','Sonipat','Panchkula','Rewari'],
            'Himachal Pradesh': ['Shimla','Manali','Dharamshala','Solan','Mandi','Hamirpur','Kullu'],
            'Jharkhand': ['Ranchi','Jamshedpur','Dhanbad','Bokaro','Deoghar','Hazaribagh','Giridih'],
            'Karnataka': ['Bengaluru','Mysuru','Hubballi','Mangaluru','Belagavi','Kalaburagi','Ballari','Vijayapura','Shivamogga','Tumakuru','Udupi','Dharwad','Davangere'],
            'Kerala': ['Thiruvananthapuram','Kochi','Kozhikode','Kollam','Thrissur','Palakkad','Malappuram','Alappuzha','Kottayam','Kannur'],
            'Madhya Pradesh': ['Bhopal','Indore','Jabalpur','Gwalior','Ujjain','Sagar','Ratlam','Satna','Murwara','Rewa','Dewas','Chhindwara'],
            'Maharashtra': ['Mumbai','Pune','Nagpur','Nashik','Thane','Aurangabad','Solapur','Kolhapur','Amravati','Nanded','Akola','Jalgaon','Latur','Dhule','Ahmednagar','Chandrapur'],
            'Manipur': ['Imphal','Thoubal','Bishnupur','Churachandpur'],
            'Meghalaya': ['Shillong','Tura','Jowai'],
            'Mizoram': ['Aizawl','Lunglei','Saiha'],
            'Nagaland': ['Kohima','Dimapur','Mokokchung'],
            'Odisha': ['Bhubaneswar','Cuttack','Rourkela','Berhampur','Sambalpur','Puri','Balasore','Bhadrak','Baripada'],
            'Punjab': ['Ludhiana','Amritsar','Jalandhar','Patiala','Bathinda','Mohali','Hoshiarpur','Gurdaspur','Firozpur'],
            'Rajasthan': ['Jaipur','Jodhpur','Udaipur','Kota','Bikaner','Ajmer','Bhilwara','Alwar','Bharatpur','Sikar','Pali','Sri Ganganagar'],
            'Sikkim': ['Gangtok','Namchi','Mangan','Gyalshing'],
            'Tamil Nadu': ['Chennai','Coimbatore','Madurai','Tiruchirappalli','Salem','Tirunelveli','Vellore','Erode','Thoothukudi','Dindigul','Karur','Cuddalore','Kancheepuram','Tiruppur'],
            'Telangana': ['Hyderabad','Warangal','Nizamabad','Karimnagar','Ramagundam','Khammam','Mahbubnagar','Nalgonda','Adilabad'],
            'Tripura': ['Agartala','Udaipur','Dharmanagar','Kailasahar'],
            'Uttar Pradesh': ['Lucknow','Kanpur','Ghaziabad','Agra','Meerut','Varanasi','Allahabad','Prayagraj','Bareilly','Aligarh','Moradabad','Saharanpur','Gorakhpur','Noida','Mathura','Muzaffarnagar','Firozabad','Jhansi','Ayodhya'],
            'Uttarakhand': ['Dehradun','Haridwar','Roorkee','Haldwani','Nainital','Rishikesh','Mussoorie'],
            'West Bengal': ['Kolkata','Asansol','Siliguri','Durgapur','Bardhaman','Malda','Barasat','Krishnanagar','Howrah','Hooghly'],
            'Delhi': ['New Delhi','Dwarka','Saket','Rohini','Janakpuri','Lajpat Nagar','Karol Bagh','Connaught Place','Pitampura','Shahdara'],
            'Chandigarh': ['Chandigarh'],
            'Jammu and Kashmir': ['Srinagar','Jammu','Anantnag','Sopore','Baramulla','Kathua'],
            'Ladakh': ['Leh','Kargil'],
        };

        $('#rpt_state').on('change', function() {
            var stateVal = $(this).val();
            var $city = $('#rpt_city');
            $city.prop('disabled', true).empty().append('<option value="">Loading...</option>');
            setTimeout(function() {
                $city.empty().append('<option value="">All Cities</option>');
                if (stateVal && reportStateCities[stateVal]) {
                    $.each(reportStateCities[stateVal], function(i, c) {
                        $city.append('<option value="' + c + '">' + c + '</option>');
                    });
                }
                $city.prop('disabled', false);
                loadReport();
            }, 300);
        });

        $('.trigger-filter').on('change', function() {
            loadReport();
        });

        $(document).on('click', '.pagination a', function(e) {
            e.preventDefault();
            let page = $(this).attr('href').split('page=')[1];
            loadReport(page);
            $('html, body').animate({ scrollTop: $('#leads-table-container').offset().top - 100 }, 500);
        });
    });
</script>
@endsection
