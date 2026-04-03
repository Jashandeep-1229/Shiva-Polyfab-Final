<!DOCTYPE html>
<html lang="en">
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="ShivaPolyfab">
    <meta name="keywords" content="Best Carry Bag Manufacturer in Khanna, Best Carry Bag Manufacturer in Punjab, Best Carry Bag Manufacturer in India">
    <meta name="author" content="shivapolyfab">
    <link rel="icon" href="{{ asset(config('app.favicon')) }}" type="image/x-icon">
    <link rel="shortcut icon" href="{{ asset(config('app.favicon')) }}" type="image/x-icon">
    <title>@yield('title') - {{ config('app.name') }}</title>
    <!-- Google font-->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preload" href="https://fonts.googleapis.com/css?family=Rubik:400,400i,500,500i,700,700i&display=swap" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript><link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Rubik:400,400i,500,500i,700,700i&display=swap"></noscript>
    <link rel="preload" href="https://fonts.googleapis.com/css?family=Roboto:300,300i,400,400i,500,500i,700,700i,900&display=swap" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript><link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Roboto:300,300i,400,400i,500,500i,700,700i,900&display=swap"></noscript>
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/font-awesome.css') }}">
    <!-- Feather icon-->
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/vendors/feather-icon.css') }}">
    <!-- Plugins css start-->
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/vendors/scrollbar.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/vendors/datatables.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/vendors/datatable-extension.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/vendors/sweetalert2.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/vendors/select2.css') }}">
    <!-- Plugins css Ends-->
    <!-- Bootstrap css-->
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/vendors/bootstrap.css') }}">
    <!-- App css-->
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/style.css') }}">
    <link id="color" rel="stylesheet" href="{{ asset('assets/css/color-1.css') }}" media="screen">
    <!-- Responsive css-->
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/responsive.css') }}">
    <!-- latest jquery-->
    <script src="{{ asset('assets/js/jquery-3.5.1.min.js') }}"></script>

    <style>
      /* For Logo Setting  */
      .page-wrapper.compact-wrapper .page-body-wrapper div.sidebar-wrapper .logo-wrapper{
        padding: 0px 30px;
      }
      .page-wrapper.compact-wrapper .page-body-wrapper div.sidebar-wrapper .logo-icon-wrapper{
        padding: 20px 30px;
      }
      /* For Logo Setting  */
      .pointer{
        cursor: pointer;
      }
      .sidebar-title.active{
        color: var(--theme-deafult) !important;
      }
      .select2-container--default .select2-selection--multiple .select2-selection__choice{
        margin-top: 5px !important; 
      }
      .form-group span{
        color: #FF0000;
      }
      .btn:focus {
        outline: 2px solid #242934 !important;
        outline-offset: 2px;
        box-shadow: 0 0 8px rgba(36, 41, 52, 0.4);
      }
      /* Sidebar Professional Styling */
      .sidebar-main-title {
        padding: 20px 0 10px 0 !important;
        background: rgba(248, 250, 252, 0.05); /* Very subtle tint */
        border-bottom: 1px solid rgba(0,0,0,0.05);
        margin-bottom: 10px !important;
      }
      .sidebar-main-title h6 {
        color: #1e3a8a !important; /* Deep Royal Blue */
        font-weight: 800 !important;
        text-transform: uppercase !important;
        letter-spacing: 1.2px !important;
        font-size: 11px !important;
        padding-left: 25px;
        position: relative;
      }
      .sidebar-main-title h6::before {
        content: '';
        position: absolute;
        left: 15px;
        top: 50%;
        transform: translateY(-50%);
        width: 3px;
        height: 12px;
        background: #3b82f6; /* Accent light blue */
        border-radius: 2px;
      }
      .sidebar-list i {
        color: #4f46e5 !important; /* Sharp Indigo for Icons */
        stroke-width: 2.5px;
      }
      .sidebar-link.active i, .sidebar-link:hover i {
        color: #ffffff !important;
      }
      .sidebar-link span {
        font-weight: 500;
      }
      /* Colorful Sidebar Categories */
      /* General */
      .cat-general.sidebar-main-title h6 { color: #2563eb !important; }
      .cat-general.sidebar-main-title h6::before { background: #2563eb !important; }
      .cat-general.sidebar-list i { color: #2563eb !important; }
      
      /* Roto Orders */
      .cat-roto.sidebar-main-title h6 { color: #ea580c !important; }
      .cat-roto.sidebar-main-title h6::before { background: #ea580c !important; }
      .cat-roto.sidebar-list i { color: #ea580c !important; }

      /* Process */
      .cat-process.sidebar-main-title h6 { color: #059669 !important; }
      .cat-process.sidebar-main-title h6::before { background: #059669 !important; }
      .cat-process.sidebar-list i { color: #059669 !important; }

      /* Packing */
      .cat-packing.sidebar-main-title h6 { color: #7c3aed !important; }
      .cat-packing.sidebar-main-title h6::before { background: #7c3aed !important; }
      .cat-packing.sidebar-list i { color: #7c3aed !important; }

      /* Accounts */
      .cat-accounts.sidebar-main-title h6 { color: #e11d48 !important; }
      .cat-accounts.sidebar-main-title h6::before { background: #e11d48 !important; }
      .cat-accounts.sidebar-list i { color: #e11d48 !important; }

      /* Stock */
      .cat-stock.sidebar-main-title h6 { color: #d97706 !important; }
      .cat-stock.sidebar-main-title h6::before { background: #d97706 !important; }
      .cat-stock.sidebar-list i { color: #d97706 !important; }

      /* Cylinder */
      .cat-cylinder.sidebar-main-title h6 { color: #0891b2 !important; }
      .cat-cylinder.sidebar-main-title h6::before { background: #0891b2 !important; }
      .cat-cylinder.sidebar-list i { color: #0891b2 !important; }

      /* Common */
      .cat-common.sidebar-main-title h6 { color: #4f46e5 !important; }
      .cat-common.sidebar-main-title h6::before { background: #4f46e5 !important; }
      .cat-common.sidebar-list i { color: #4f46e5 !important; }

      /* Reports */
      .cat-reports.sidebar-main-title h6 { color: #db2777 !important; }
      .cat-reports.sidebar-main-title h6::before { background: #db2777 !important; }
      .cat-reports.sidebar-list i { color: #db2777 !important; }

      /* Master */
      .cat-master.sidebar-main-title h6 { color: #0d9488 !important; }
      .cat-master.sidebar-main-title h6::before { background: #0d9488 !important; }
      .cat-master.sidebar-list i { color: #0d9488 !important; }

      /* Team */
      .cat-team.sidebar-main-title h6 { color: #475569 !important; }
      .cat-team.sidebar-main-title h6::before { background: #475569 !important; }
      .cat-team.sidebar-list i { color: #475569 !important; }

      .sidebar-link.active i, .sidebar-link:hover i {
        color: #ffffff !important;
      }
      .sidebar-list i {
        stroke-width: 2.5px;
      }
    </style>
    @yield('css')
  </head>
  <body>
    <!-- loader starts-->
    {{-- <div class="loader-wrapper">
      <div class="loader-index"><span></span></div>
      <svg>
        <defs></defs>
        <filter id="goo">
          <fegaussianblur in="SourceGraphic" stddeviation="11" result="blur"></fegaussianblur>
          <fecolormatrix in="blur" values="1 0 0 0 0  0 1 0 0 0  0 0 1 0 0  0 0 0 19 -9" result="goo"> </fecolormatrix>
        </filter>
      </svg>
    </div> --}}
    <!-- loader ends-->
    <!-- tap on top starts-->
    <div class="tap-top"><i data-feather="chevrons-up"></i></div>
    <!-- tap on tap ends-->
    <!-- page-wrapper Start-->
    <div class="page-wrapper compact-wrapper" id="pageWrapper">
      <!-- Page Header Start-->
      @include('layouts.admin.header')
      <!-- Page Header Ends                              -->
      <!-- Page Body Start-->
      <div class="page-body-wrapper">
        <!-- Page Sidebar Start-->
        @include('layouts.admin.sidebar')
        <!-- Page Sidebar Ends-->
        <div class="page-body">
          <div class="container-fluid">        
            <div class="page-title">
              <div class="row">
                <div class="col-6">
                  <h3>@yield('title')</h3>
                </div>
                <div class="col-6">
                  <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">                                       
                      <i data-feather="home"></i></a></li>
                    @yield('breadcrumb-items')
                    <li class="breadcrumb-item active">@yield('title')</li>
                  </ol>
                </div>
              </div>
            </div>
          </div>
          <!-- Container-fluid starts-->
         @yield('content')
          <!-- Container-fluid Ends-->
        </div>
        <!-- footer start-->
        @include('layouts.admin.footer')
      </div>
    </div>
    
    <!-- Bootstrap js-->
    <script src="{{ asset('assets/js/bootstrap/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('assets/js/notify/bootstrap-notify.min.js') }}"></script>
    <script src="{{ asset('assets/js/notify/notify-script.js') }}"></script>
    <!-- feather icon js-->
    <script src="{{ asset('assets/js/icons/feather-icon/feather.min.js') }}"></script>
    <script src="{{ asset('assets/js/icons/feather-icon/feather-icon.js') }}"></script>
    <!-- scrollbar js-->
    <script src="{{ asset('assets/js/scrollbar/simplebar.js') }}"></script>
    <script src="{{ asset('assets/js/scrollbar/custom.js') }}"></script>
    <!-- Sidebar jquery-->
    <script src="{{ asset('assets/js/config.js') }}"></script>
    <!-- Plugins JS start-->
    <script src="{{ asset('assets/js/sidebar-menu.js') }}"></script>

    <script src="{{ asset('assets/js/sweet-alert/sweetalert.min.js') }}"></script>
    <script src="{{ asset('assets/js/select2/select2.full.min.js') }}"></script>
    <script src="{{ asset('assets/js/select2/select2-custom.js') }}"></script>
    <script src="{{ asset('assets/js/datatable/datatables/jquery.dataTables.min.js') }}" defer></script>
    <script src="{{ asset('assets/js/datatable/datatable-extension/dataTables.buttons.min.js') }}" defer></script>
    <script src="{{ asset('assets/js/datatable/datatable-extension/jszip.min.js') }}" defer></script>
    <script src="{{ asset('assets/js/datatable/datatable-extension/buttons.colVis.min.js') }}" defer></script>
    <script src="{{ asset('assets/js/datatable/datatable-extension/pdfmake.min.js') }}" defer></script>
    <script src="{{ asset('assets/js/datatable/datatable-extension/vfs_fonts.js') }}" defer></script>
    <script src="{{ asset('assets/js/datatable/datatable-extension/dataTables.autoFill.min.js') }}" defer></script>
    <script src="{{ asset('assets/js/datatable/datatable-extension/dataTables.select.min.js') }}" defer></script>
    <script src="{{ asset('assets/js/datatable/datatable-extension/buttons.bootstrap4.min.js') }}" defer></script>
    <script src="{{ asset('assets/js/datatable/datatable-extension/buttons.html5.min.js') }}" defer></script>
    <script src="{{ asset('assets/js/datatable/datatable-extension/buttons.print.min.js') }}" defer></script>
    <script src="{{ asset('assets/js/datatable/datatable-extension/dataTables.bootstrap4.min.js') }}" defer></script>
    <script src="{{ asset('assets/js/datatable/datatable-extension/dataTables.responsive.min.js') }}" defer></script>
    <script src="{{ asset('assets/js/datatable/datatable-extension/responsive.bootstrap4.min.js') }}" defer></script>
    <script src="{{ asset('assets/js/datatable/datatable-extension/dataTables.keyTable.min.js') }}" defer></script>
    <script src="{{ asset('assets/js/datatable/datatable-extension/dataTables.colReorder.min.js') }}" defer></script>
    <script src="{{ asset('assets/js/datatable/datatable-extension/dataTables.fixedHeader.min.js') }}" defer></script>
    <script src="{{ asset('assets/js/datatable/datatable-extension/dataTables.rowReorder.min.js') }}" defer></script>
    <script src="{{ asset('assets/js/datatable/datatable-extension/dataTables.scroller.min.js') }}" defer></script>  
    <!-- Plugins JS Ends-->
    <!-- Theme js-->
    <script src="{{ asset('assets/js/script.js') }}"></script>
    <!-- login js-->
    <!-- Plugin used-->
    @if (session('success'))
    <script>
      $.notify({ title:'Success', message:'{{ session('success') }}' }, { type:'success', });
    </script>
    @endif
    @if (session('danger'))
    <script>
      $.notify({ title:'Deleted', message:'{{ session('danger') }}' }, { type:'secondary', });
    </script>
    @endif
    @if ($errors->any())
      @foreach ($errors->all() as $error)
      <script>
        $.notify({ title:'Error', message:'{{ $error }}' }, { type:'danger', });
      </script>
      @endforeach
    @endif

    <script>
      $(document).ready(function() {
          // Global Shortcut for Search (Alt + S)
          $(window).keydown(function(e) {
              // Alt + S OR Ctrl + Shift + F
              if ((e.altKey && e.keyCode === 83) || (e.ctrlKey && e.shiftKey && e.keyCode === 70)) {
                  e.preventDefault();
                  // Try to find common search inputs
                  var $searchInput = $('#basic-2_search, .dataTables_filter input, input[type="search"]');
                  if ($searchInput.length > 0) {
                      $searchInput.first().focus().select();
                      // Optional: Show a quick notification or visual cue
                      $.notify({ message: 'Search Focused' }, { type: 'primary', delay: 1000, placement: { from: 'bottom', align: 'right' } });
                  }
              }
          });

          // Auto scroll to active sidebar item
          var activeItem = $('.sidebar-main .active');
          if (activeItem.length > 0) {
              var sidebarContainer = $('#sidebar-menu');
              // SimpleBar specific handling if needed, but usually .scroll() works on the wrapper
              var container = $('#simple-bar');
              
              if (container.length > 0) {
                  var scrollTop = activeItem.offset().top - container.offset().top + container.scrollTop() - (container.height() / 2);
                  
                  // Use SimpleBar scroll if it's initialized
                  var simpleBarElement = document.getElementById('simple-bar');
                  if (simpleBarElement && SimpleBar.instances.get(simpleBarElement)) {
                      SimpleBar.instances.get(simpleBarElement).getScrollElement().scrollTo({
                          top: scrollTop,
                          behavior: 'smooth'
                      });
                  } else {
                      container.animate({
                          scrollTop: scrollTop
                      }, 500);
                  }
              }
          }
      });
    </script>

   @yield('script')

   @if(Auth::check() && Auth::user()->role_as != 'Admin' && (!isset(Auth::user()->is_device_verified) || !Auth::user()->is_device_verified))
   <!-- Device Verification Modal -->
   <div class="modal fade show" id="otpVerificationModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="otpModalLabel" aria-hidden="true" style="z-index: 999999; background: rgba(0,0,0,0.85); display: block;">
       <div class="modal-dialog modal-dialog-centered">
           <div class="modal-content border-0 shadow-lg" style="border-radius: 15px; overflow: hidden;">
               <div class="modal-header bg-primary text-white border-0 py-3">
                   <h5 class="modal-title fw-bold" id="otpModalLabel"><i class="fa fa-shield me-2"></i> Security Verification</h5>
               </div>
               <div class="modal-body text-center p-5">
                   <div class="mb-4">
                       <div class="bg-light-primary d-inline-block p-4 rounded-circle mb-3">
                           <i class="fa fa-mobile fs-1 text-primary"></i>
                       </div>
                       <h4 class="fw-bold">New Device Detected</h4>
                       <p class="text-muted">For your security, please enter the 4-digit verification code provided by your administrator.</p>
                   </div>
                   
                   <div class="d-flex justify-content-center gap-3 mb-4" id="otp-inputs">
                       <input type="text" maxlength="1" id="otp_1" class="form-control otp-box text-center fw-bold fs-1" style="width: 70px; height: 75px; border-radius: 12px; border: 2px solid #e2e8f0; background: #f8fafc;" autocomplete="off">
                       <input type="text" maxlength="1" id="otp_2" class="form-control otp-box text-center fw-bold fs-1" style="width: 70px; height: 75px; border-radius: 12px; border: 2px solid #e2e8f0; background: #f8fafc;" autocomplete="off">
                       <input type="text" maxlength="1" id="otp_3" class="form-control otp-box text-center fw-bold fs-1" style="width: 70px; height: 75px; border-radius: 12px; border: 2px solid #e2e8f0; background: #f8fafc;" autocomplete="off">
                       <input type="text" maxlength="1" id="otp_4" class="form-control otp-box text-center fw-bold fs-1" style="width: 70px; height: 75px; border-radius: 12px; border: 2px solid #e2e8f0; background: #f8fafc;" autocomplete="off">
                   </div>

                   <div id="otp-error" class="alert alert-danger mb-3 d-none animate__animated animate__shakeX">Incorrect code. Please check with your Admin.</div>
                   
                   <div class="d-grid mt-4">
                       <button type="button" id="verify-otp-btn" class="btn btn-primary btn-lg py-3 fw-bold shadow-sm disabled" style="border-radius: 12px; letter-spacing: 1px;">WAITING FOR INPUT</button>
                   </div>
               </div>
           </div>
       </div>
   </div>

   <style>
       .otp-box:focus {
           border-color: #4f46e5 !important;
           background: #fff !important;
           box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.1) !important;
           transform: translateY(-2px);
           transition: all 0.2s;
       }
       #otpVerificationModal {
           backdrop-filter: blur(8px);
       }
       body.modal-open {
           overflow: hidden !important;
       }
       .bg-light-primary {
           background-color: rgba(79, 70, 229, 0.1);
       }
   </style>

   <script>
       $(document).ready(function() {
           // Ensure modal stays open
           if($('#otpVerificationModal').length > 0) {
              $('body').addClass('modal-open');
              setTimeout(function() { $('#otp_1').focus(); }, 1000);

              $('.otp-box').on('input', function() {
                  this.value = this.value.replace(/[^0-9]/g, '');
                  if (this.value.length === 1) {
                      $(this).next('.otp-box').focus();
                  }
                  checkOtpCompletion();
              });

              $('.otp-box').on('keydown', function(e) {
                  if (e.key === 'Backspace' && this.value.length === 0) {
                      $(this).prev('.otp-box').focus();
                  }
              });

              function checkOtpCompletion() {
                  let otp = '';
                  let filled = true;
                  $('.otp-box').each(function() {
                      if (this.value === '') filled = false;
                      otp += this.value;
                  });

                  if (filled && otp.length === 4) {
                      $('#verify-otp-btn').removeClass('disabled').text('VERIFYING...').addClass('btn-info');
                      submitOtp(otp);
                  } else {
                      $('#verify-otp-btn').addClass('disabled').text('WAITING FOR INPUT').removeClass('btn-info');
                  }
              }

              function submitOtp(otp) {
                  $('#otp-error').addClass('d-none');
                  $.ajax({
                      url: "{{ route('otp.verify') }}",
                      type: "POST",
                      data: {
                          _token: "{{ csrf_token() }}",
                          otp: otp
                      },
                      success: function(response) {
                          if (response.result == 1) {
                              $('#verify-otp-btn').text('SUCCESS! REDIRECTING...').removeClass('btn-info').addClass('btn-success');
                              setTimeout(function() { window.location.reload(); }, 1000);
                          } else {
                              $('#otp-error').removeClass('d-none');
                              $('.otp-box').val('').first().focus();
                              $('#verify-otp-btn').addClass('disabled').text('INCORRECT CODE').removeClass('btn-info');
                          }
                      },
                      error: function() {
                          $('#verify-otp-btn').addClass('disabled').text('DASHBOARD ERROR').removeClass('btn-info');
                      }
                  });
              }
           }
       });
   </script>
   @endif
   @include('admin.ai_studio.chat_widget')
  </body>
</html>