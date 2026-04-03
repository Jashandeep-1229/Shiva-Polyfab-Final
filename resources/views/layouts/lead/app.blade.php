<!DOCTYPE html>
<html lang="en">
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Lead Management System">
    <meta name="author" content="shivapolyfab">
    <link rel="icon" href="{{ asset(config('app.favicon')) }}" type="image/x-icon">
    <link rel="shortcut icon" href="{{ asset(config('app.favicon')) }}" type="image/x-icon">
    <title>@yield('title') - Lead Management</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <!-- Google font-->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preload" href="https://fonts.googleapis.com/css?family=Rubik:400,400i,500,500i,700,700i&display=swap" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript><link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Rubik:400,400i,500,500i,700,700i&display=swap"></noscript>
    <link rel="preload" href="https://fonts.googleapis.com/css?family=Roboto:300,300i,400,400i,500,500i,700,700i,900&display=swap" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript><link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Roboto:300,300i,400,400i,500,500i,700,700i,900&display=swap"></noscript>
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/font-awesome.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/vendors/feather-icon.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/vendors/scrollbar.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/vendors/datatables.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/vendors/datatable-extension.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/vendors/sweetalert2.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/vendors/select2.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/vendors/bootstrap.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/style.css') }}">
    <link id="color" rel="stylesheet" href="{{ asset('assets/css/color-1.css') }}" media="screen">
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/responsive.css') }}">
    <script src="{{ asset('assets/js/jquery-3.5.1.min.js') }}"></script>

    <style>
      .page-wrapper.compact-wrapper .page-body-wrapper div.sidebar-wrapper .logo-wrapper{ padding: 0px 30px; }
      .page-wrapper.compact-wrapper .page-body-wrapper div.sidebar-wrapper .logo-icon-wrapper{ padding: 20px 30px; }
      .lead-badge { background-color: #7366ff; color: white; padding: 2px 8px; border-radius: 4px; font-size: 10px; margin-left: 5px; text-transform: uppercase; }
      
      @keyframes pulse-won {
          0% { transform: scale(1); box-shadow: 0 0 0 0 rgba(81, 187, 37, 0.7); }
          70% { transform: scale(1.05); box-shadow: 0 0 0 10px rgba(81, 187, 37, 0); }
          100% { transform: scale(1); box-shadow: 0 0 0 0 rgba(81, 187, 37, 0); }
      }
      @keyframes pulse-lost {
          0% { transform: scale(1); box-shadow: 0 0 0 0 rgba(220, 53, 69, 0.7); }
          70% { transform: scale(1.05); box-shadow: 0 0 0 10px rgba(220, 53, 69, 0); }
          100% { transform: scale(1); box-shadow: 0 0 0 0 rgba(220, 53, 69, 0); }
      }
      .anim-won { animation: pulse-won 2s infinite; }
      .anim-lost { animation: pulse-lost 2s infinite; }

      /* Visual Focus for accessibility - Highlight button/link focal points */
      button:focus, a:focus, input:focus, select:focus, textarea:focus, .sidebar-link:focus, .btn:focus {
          outline: 2px solid #7366ff !important;
          outline-offset: 2px;
          box-shadow: 0 0 10px rgba(115, 102, 255, 0.5) !important;
      }
      /* Fix for Select2 in Input Group */
      .input-group > .select2-container--default {
          width: auto !important;
          flex: 1 1 auto !important;
      }
      .input-group > .select2-container--default .select2-selection--single {
          height: 100% !important;
          line-height: normal !important;
          border-top-right-radius: 0 !important;
          border-bottom-right-radius: 0 !important;
      }
    </style>
    @yield('css')
  <style>
    /* Auto-uppercase all text fields in Lead Module */
    input[type="text"],
    input[type="search"],
    input[type="email"],
    input[type="tel"],
    input:not([type]),
    textarea {
      text-transform: uppercase;
    }
    input[type="text"]::placeholder,
    textarea::placeholder {
      text-transform: none;
    }
  </style>
  <script>
    // Ensure the actual submitted value is also uppercase (not just visual)
    document.addEventListener('DOMContentLoaded', function () {
      document.addEventListener('input', function (e) {
        var el = e.target;
        var tag = el.tagName;
        var type = (el.getAttribute('type') || 'text').toLowerCase();
        var textTypes = ['text', 'search', 'email', 'tel'];
        if ((tag === 'INPUT' && textTypes.includes(type)) || tag === 'TEXTAREA') {
          var pos = el.selectionStart;
          el.value = el.value.toUpperCase();
          try { el.setSelectionRange(pos, pos); } catch(ex) {}
        }
      });
    });
  </script>
  </head>
  <body>
    <div class="tap-top"><i data-feather="chevrons-up"></i></div>
    <div class="page-wrapper compact-wrapper" id="pageWrapper">
      @include('layouts.lead.header')
      <div class="page-body-wrapper">
        @include('layouts.lead.sidebar')
        <div class="page-body">
          <div class="container-fluid">        
            <div class="page-title">
              <div class="row">
                <div class="col-6">
                  <h3>@yield('title') <span class="lead-badge">Lead System</span></h3>
                </div>
                <div class="col-6">
                  <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('lead.dashboard') }}"><i data-feather="home"></i></a></li>
                    @yield('breadcrumb-items')
                    <li class="breadcrumb-item active">@yield('title')</li>
                  </ol>
                </div>
              </div>
            </div>
          </div>
         @yield('content')
      
        </div>
           @include('layouts.lead.footer')
      </div>
    </div>
    
    <script src="{{ asset('assets/js/bootstrap/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('assets/js/notify/bootstrap-notify.min.js') }}"></script>
    <script src="{{ asset('assets/js/notify/notify-script.js') }}"></script>
    <script src="{{ asset('assets/js/icons/feather-icon/feather.min.js') }}"></script>
    <script src="{{ asset('assets/js/icons/feather-icon/feather-icon.js') }}"></script>
    <script src="{{ asset('assets/js/scrollbar/simplebar.js') }}"></script>
    <script src="{{ asset('assets/js/scrollbar/custom.js') }}"></script>
    <script src="{{ asset('assets/js/config.js') }}"></script>
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

    <script src="{{ asset('assets/js/script.js') }}"></script>
    <script>
    // Fix: theme sidebar-menu.js opens parent submenus based on partial URL match.
    // When on a standalone top-level page (link-nav), force-close the Customer Lead submenu.
    (function() {
        var topLevelLinks = document.querySelectorAll('.sidebar-link.link-nav');
        var currentHref = window.location.href;
        var isTopLevel = false;
        topLevelLinks.forEach(function(link) {
            if (link.href && currentHref === link.href) {
                isTopLevel = true;
            }
        });
        if (isTopLevel) {
            // Close all submenus that are not explicitly marked open by server
            document.querySelectorAll('.sidebar-submenu:not([data-server-open])').forEach(function(ul) {
                ul.style.setProperty('display', 'none', 'important');
            });
            // Remove active from parent sidebar-title links
            document.querySelectorAll('.sidebar-title.active').forEach(function(a) {
                if (!a.closest('li').querySelector('.link-nav.active')) {
                    a.classList.remove('active');
                }
            });
        }
    })();
    </script>
    
    @if (session('success'))
    <script>$.notify({ title:'Success', message:'{{ session('success') }}' }, { type:'success', });</script>
    @endif
    
    @if (session('danger'))
    <script>$.notify({ title:'Notice', message:'{{ session('danger') }}' }, { type:'secondary', });</script>
    @endif

    @if ($errors->any())
      @foreach ($errors->all() as $error)
      <script>$.notify({ title:'Error', message:'{{ $error }}' }, { type:'danger', });</script>
      @endforeach
    @endif
    
    <!-- Quick Add City Modal -->
    <div class="modal fade" id="quickAddCityModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content shadow-lg">
                <div class="modal-header bg-primary">
                    <h5 class="modal-title text-white">Quick Add City</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Enter City Name (IN CAPS)</label>
                        <input type="text" id="modal_city_name" class="form-control text-uppercase" placeholder="ENTER CITY NAME" autofocus required>
                    </div>
                </div>
                <div class="modal-footer pt-0 border-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="saveCityBtn">Save City</button>
                </div>
            </div>
        </div>
    </div>

    @yield('script')

    <script>
    $(document).on('click', '#saveCityBtn', function() {
        var state = $('#modal_state_name_hidden').val();
        var cityName = $('#modal_city_name').val().trim();
        var targetSelect = $('#modal_target_select_hidden').val();
        
        if (!cityName) {
            $.notify({ title: 'Error', message: 'Please enter city name' }, { type: 'danger' });
            return;
        }
        
        var $btn = $(this);
        $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Saving...');
        
        $.ajax({
            url: '{{ route("lead.locations.cities.quick_store") }}',
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                state_name: state,
                city_name: cityName
            },
            success: function(response) {
                if (response.success) {
                    $('#quickAddCityModal').modal('hide');
                    
                    // Unified City Select Update
                    var $select = $(targetSelect);
                    if ($select.length) {
                        // Check if it's Select2
                        if ($select.hasClass('select2-hidden-accessible')) {
                            var newOpt = new Option(response.city, response.city, true, true);
                            $select.append(newOpt).trigger('change');
                        } else {
                            $select.append('<option value="' + response.city + '" selected>' + response.city + '</option>');
                        }
                    }
                    
                    // Trigger populate if exists (legacy pages)
                    if (typeof populateCities === 'function' && targetSelect === '#city_select') {
                        populateCities(state, response.city);
                    } else if (typeof populateAgentCities === 'function') {
                        populateAgentCities(state, response.city);
                    }

                    $.notify({ title: 'Success', message: response.message }, { type: 'success' });
                } else {
                    $.notify({ title: 'Error', message: response.message }, { type: 'danger' });
                }
            },
            error: function() {
                $.notify({ title: 'Error', message: 'Something went wrong.' }, { type: 'danger' });
            },
            complete: function() {
                $btn.prop('disabled', false).text('Save City');
            }
        });
    });
    </script>
    <input type="hidden" id="modal_state_name_hidden">
    <input type="hidden" id="modal_target_select_hidden">
  </body>
</html>
