<div class="page-header">
  <div class="header-wrapper row m-0">
    <form class="form-inline search-full col" action="#" method="get">
      <div class="form-group w-100">
        <div class="Typeahead Typeahead--twitterUsers">
          <div class="u-posRelative">
            <input class="demo-input Typeahead-input form-control-plaintext w-100" type="text" placeholder="Search Leads .." name="q" title="" autofocus>
            <div class="spinner-border Typeahead-spinner" role="status"><span class="sr-only">Loading...</span></div><i class="close-search" data-feather="x"></i>
          </div>
          <div class="Typeahead-menu"></div>
        </div>
      </div>
    </form>
    <div class="header-logo-wrapper col-auto p-0">
      <div class="logo-wrapper"><a href="{{ route('lead.dashboard') }}"><img class="img-fluid" src="{{ asset(env('APP_LOGO_DARK')) }}" alt=""></a></div>
      <div class="toggle-sidebar"><i class="status_toggle middle sidebar-toggle" data-feather="align-center"></i></div>
    </div>
    <div class="left-header col-xxl-5 col-xl-6 col-lg-5 col-md-4 col-sm-3 p-0">
      <div class="notification-slider">
        <div class="d-flex h-100">
            <span class="badge badge-primary-light">LEAD MANAGEMENT SYSTEM</span>
        </div>
      </div>
    </div>
    <div class="nav-right col-xxl-7 col-xl-6 col-md-7 col-8 pull-right right-header p-0 ms-auto">
      <ul class="nav-menus">
        <li><span class="header-search"><i data-feather="search"></i></span></li>
        @php
            $userIds = auth()->user()->getViewableUserIds();
            $customerCount = \App\Models\LeadFollowup::whereHas('lead', function($q) use ($userIds) {
                $q->whereIn('assigned_user_id', $userIds);
            })->whereNull('complete_date')->where('followup_date', '<=', now())->count();
            
            $agentCount = \App\Models\AgentOverallFollowup::where('status', 0)
                ->where('followup_date', '<=', now())->count();
            
            $pendingCount = $customerCount + $agentCount;
        @endphp
        <li class="onhover-dropdown">
          <div class="notification-box">
            <i data-feather="bell"></i><span class="badge rounded-pill badge-danger">{{ $pendingCount }}</span>
          </div>
          <div class="onhover-show-div notification-dropdown">
            <h6 class="f-18 mb-0 dropdown-title">Pending Follow-ups</h6>
            <ul class="p-3">
              @if($pendingCount > 0)
              <li class="b-l-danger border-4 mb-3">
                <p class="mb-0">You have <span class="font-danger fw-bold">{{ $pendingCount }}</span> follow-ups overdue or due today.</p>
              </li>
              <li class="text-center"><a class="f-w-700 btn btn-xs btn-outline-primary" href="{{ route('lead.followup.pending') }}">CHECK ALL</a></li>
              @else
              <li>
                <p class="mb-0 text-muted italic">No pending follow-ups. Great job!</p>
              </li>
              @endif
            </ul>
          </div>
        </li>
        <li>
          <div class="mode">
            <i class="fa fa-moon-o"></i>
          </div>
        </li>
        <li class="profile-nav onhover-dropdown pe-0 py-0">
          <div class="media profile-media"><img class="b-r-10" src="{{ asset('assets/images/dashboard/profile.png') }}" alt="">
            <div class="media-body"><span>{{ auth()->user()->name }}</span>
              <p class="mb-0 font-roboto">{{ auth()->user()->role }} <i class="middle fa fa-angle-down"></i></p>
            </div>
          </div>
          <ul class="profile-dropdown onhover-show-div">
            <li><a href="#"><i data-feather="user"></i><span>Profile </span></a></li>
            <li><a href="#"><i data-feather="settings"></i><span>Settings</span></a></li>
            <li><a href="{{ route('lead.logout') }}" onclick="event.preventDefault(); document.getElementById('lead-logout-form').submit();"><i data-feather="log-out"> </i><span>Log Out</span></a></li>
          </ul>
          <form id="lead-logout-form" action="{{ route('lead.logout') }}" method="POST" class="d-none">
            @csrf
        </form>
        </li>
      </ul>
    </div>
  </div>
</div>
