<div class="sidebar-wrapper" sidebar-layout="stroke-svg">
  <div>
    <div class="logo-wrapper" style="height: auto; width:200px;">
      <a href="{{ route('lead.dashboard') }}">
        <img class="img-fluid for-light" src="{{ asset(env('APP_LOGO_DARK')) }}" alt="">
        <img class="img-fluid for-dark" src="{{ asset(env('APP_LOGO_LIGHT')) }}" alt="">
      </a>
      <div class="back-btn"><i class="fa fa-angle-left"></i></div>
      <div class="toggle-sidebar"><i class="status_toggle middle sidebar-toggle" data-feather="grid"> </i></div>
    </div>
    <div class="logo-icon-wrapper"><a href="{{ route('lead.dashboard') }}"><img class="img-fluid" width="50px" src="{{ asset(env('APP_FAVICON')) }}" alt=""></a></div>
    <nav class="sidebar-main">
      <div class="left-arrow" id="left-arrow"><i data-feather="arrow-left"></i></div>
      <div id="sidebar-menu">
        <ul class="sidebar-links" id="simple-bar">
          <li class="back-btn"><a href="{{ route('lead.dashboard') }}"><img class="img-fluid" src="{{ asset(env('APP_FAVICON')) }}" alt=""></a>
            <div class="mobile-back text-end"><span>Back</span><i class="fa fa-angle-right ps-2" aria-hidden="true"></i></div>
          </li>
          
          <li class="sidebar-list">
            <a class="sidebar-link sidebar-title link-nav {{ request()->routeIs('lead.dashboard') ? 'active' : '' }}" href="{{ route('lead.dashboard') }}">
              <i data-feather="monitor"></i><span>Dashboard</span>
            </a>
          </li>

          @if(auth()->user()->role_as == 'Admin')
          <li class="sidebar-main-title">
            <div>
              <h6>Masters</h6>
            </div>
          </li>

          <li class="sidebar-list">
            <a class="sidebar-link sidebar-title">
              <i data-feather="settings"></i><span>Lead Master</span>
            </a>
            <ul class="sidebar-submenu">
              <li><a href="{{ route('lead.source.index') }}">Lead Source</a></li>
              <li><a href="{{ route('lead.tag.index') }}">Lead Tags</a></li>
              <li><a href="{{ route('lead.status.index') }}">Lead Steps</a></li>
            </ul>
          </li>

          <li class="sidebar-list">
            <a class="sidebar-link sidebar-title">
              <i data-feather="map-pin"></i><span>Location Master</span>
            </a>
            <ul class="sidebar-submenu">
              <li><a href="{{ route('lead.locations.states') }}">States</a></li>
              <li><a href="{{ route('lead.locations.cities') }}">Cities</a></li>
            </ul>
          </li>

          <li class="sidebar-list">
            <a class="sidebar-link sidebar-title">
              <i data-feather="briefcase"></i><span>Agent Master</span>
            </a>
            <ul class="sidebar-submenu">
              <li><a href="{{ route('lead.agent.deals_in.index') }}">Deals In</a></li>
              <li><a href="{{ route('lead.agent.agent.index') }}">Agents</a></li>
            </ul>
          </li>
          @endif

          <li class="sidebar-main-title">
            <div>
              <h6>Leads Management - Customer</h6>
            </div>
          </li>

          @php
            $customerLeadRoutes = ['lead.index', 'lead.create', 'lead.edit', 'lead.show', 'lead.leads.show', 'lead.pending', 'lead.won', 'lead.lost', 'lead.datatable'];
            $isCustomerLeadActive = in_array(Route::currentRouteName(), $customerLeadRoutes);
          @endphp
          <li class="sidebar-list {{ $isCustomerLeadActive ? 'sidebar-open' : '' }}">
            <a class="sidebar-link sidebar-title {{ $isCustomerLeadActive ? 'active' : '' }}">
              <i data-feather="users"></i><span>Customer Lead</span>
            </a>
            <ul class="sidebar-submenu" {{ $isCustomerLeadActive ? 'data-server-open="1"' : '' }} style="{{ $isCustomerLeadActive ? 'display: block;' : 'display: none;' }}">
              <li><a href="{{ route('lead.create') }}">Add New Lead</a></li>
              <li><a href="{{ route('lead.index') }}">All Lead</a></li>
              <li><a href="{{ route('lead.pending') }}">Pending Lead</a></li>
              <li><a href="{{ route('lead.won') }}">Won Lead</a></li>
              <li><a href="{{ route('lead.lost') }}">Lost Lead</a></li>
            </ul>
          </li>
            <li class="sidebar-list">
            <a class="sidebar-link sidebar-title link-nav {{ request()->routeIs('lead.repeat_suggestions') ? 'active' : '' }}" href="{{ route('lead.repeat_suggestions') }}">
              <i data-feather="repeat"></i><span>Repeat Suggestion</span> @if(isset($repeatSuggestionCount) && $repeatSuggestionCount > 0) <span class="badge badge-light-danger text-dark ms-1">{{ $repeatSuggestionCount }}</span> @endif
            </a>
          </li>
             <!-- <li class="sidebar-list">
            <a class="sidebar-link sidebar-title link-nav {{ request()->routeIs('lead.job_card_status') ? 'active' : '' }}" href="{{ route('lead.job_card_status') }}">
              <i data-feather="cpu"></i><span>Order Process</span>
            </a>
          </li> -->

           <li class="sidebar-main-title">
            <div>
              <h6>Leads Management - Agent</h6>
            </div>
          </li>


          @php
            $agentLeadRoutes = ['lead.agent_leads.index', 'lead.agent_leads.create', 'lead.agent_leads.edit', 'lead.agent_leads.show', 'lead.agent_leads.pending', 'lead.agent_leads.won', 'lead.agent_leads.lost', 'lead.agent_leads.datatable'];
            $isAgentLeadActive = in_array(Route::currentRouteName(), $agentLeadRoutes);
          @endphp
          <li class="sidebar-list {{ $isAgentLeadActive ? 'sidebar-open' : '' }}">
            <a class="sidebar-link sidebar-title {{ $isAgentLeadActive ? 'active' : '' }}">
              <i data-feather="truck"></i><span>Agent Lead</span>
            </a>
            <ul class="sidebar-submenu" {{ $isAgentLeadActive ? 'data-server-open="1"' : '' }} style="{{ $isAgentLeadActive ? 'display: block;' : 'display: none;' }}">
              <li><a href="{{ route('lead.agent_leads.create') }}">Add Agent Lead</a></li>
              <li><a href="{{ route('lead.agent_leads.index') }}">All Agent Lead</a></li>
              <li><a href="{{ route('lead.agent_leads.pending') }}">Pending Agent Lead</a></li>
              <li><a href="{{ route('lead.agent_leads.won') }}">Won Agent Lead</a></li>
              <li><a href="{{ route('lead.agent_leads.lost') }}">Lost Agent Lead</a></li>
            </ul>
          </li>
              <li class="sidebar-list">
            <a class="sidebar-link sidebar-title link-nav {{ request()->routeIs('lead.agent_leads.repeat_suggestions') ? 'active' : '' }}" href="{{ route('lead.agent_leads.repeat_suggestions') }}">
              <i data-feather="repeat" style="stroke: #ff9f43;"></i><span>Agent Repeat Sug.</span> @if(isset($agentRepeatSuggestionCount) && $agentRepeatSuggestionCount > 0) <span class="badge badge-light-warning text-dark ms-1">{{ $agentRepeatSuggestionCount }}</span> @endif
            </a>
          </li>

          <!-- <li class="sidebar-list">
            <a class="sidebar-link sidebar-title link-nav {{ request()->routeIs('lead.agent_leads.order_process') ? 'active' : '' }}" href="{{ route('lead.agent_leads.order_process') }}">
              <i data-feather="cpu" style="stroke: #ff9f43;"></i><span>Agent Order Process</span>
            </a>
          </li> -->
             <li class="sidebar-main-title">
            <div>
              <h6>Followup Management</h6>
            </div>
          </li>
          <li class="sidebar-list">
            <a class="sidebar-link sidebar-title link-nav {{ request()->routeIs('lead.followup.index') ? 'active' : '' }}" href="{{ route('lead.followup.index') }}">
              <i data-feather="calendar"></i><span>Follow-ups</span>
            </a>
          </li>

          
        
       
          
          @if(auth()->user()->role_as == 'Admin')
          <li class="sidebar-main-title">
            <div>
              <h6>Report</h6>
            </div>
          </li>

          <li class="sidebar-list">
            <a class="sidebar-link sidebar-title link-nav {{ request()->routeIs('lead.report') ? 'active' : '' }}" href="{{ route('lead.report') }}">
              <i data-feather="pie-chart"></i><span>Lead Report</span>
            </a>
          </li>

          <li class="sidebar-list">
            <a class="sidebar-link sidebar-title link-nav {{ request()->routeIs('lead.agent_leads.report') ? 'active' : '' }}" href="{{ route('lead.agent_leads.report') }}">
              <i data-feather="trending-up" style="stroke: #ff9f43;"></i><span>Agent Report</span>
            </a>
          </li>
          @endif

        


        </ul>
      </div>
      <div class="right-arrow" id="right-arrow"><i data-feather="arrow-right"></i></div>
    </nav>
  </div>
</div>
