@php
    use App\Helpers\PermissionHelper;
@endphp
<div class="sidebar-wrapper" sidebar-layout="stroke-svg">
  <div>
    <div class="logo-wrapper" style="height: auto; width:200px;"><a href="{{ auth()->user()->role_as == 'Admin' ? route('admin.dashboard.overall') : url('/') }}"><img class="img-fluid for-light" src="{{ asset(env('APP_LOGO_DARK')) }}" alt=""><img class="img-fluid for-dark" src="{{ asset(env('APP_LOGO_LIGHT')) }}" alt=""></a>
      <div class="back-btn"><i class="fa fa-angle-left"></i></div>
      <div class="toggle-sidebar"><i class="status_toggle middle sidebar-toggle" data-feather="grid"> </i></div>
    </div>
    <div class="logo-icon-wrapper"><a href="{{ auth()->user()->role_as == 'Admin' ? route('admin.dashboard.overall') : url('/') }}"><img class="img-fluid" width="50px" src="{{ asset(env('APP_FAVICON')) }}" alt=""></a></div>
    <nav class="sidebar-main">
      <div class="left-arrow" id="left-arrow"><i data-feather="arrow-left"></i></div>
      <div id="sidebar-menu">
        <ul class="sidebar-links" id="simple-bar">
          <li class="back-btn"><a href="{{ auth()->user()->role_as == 'Admin' ? route('admin.dashboard.overall') : url('/') }}"><img class="img-fluid" src="{{ asset(env('APP_FAVICON')) }}" alt=""></a>
            <div class="mobile-back text-end"><span>Back</span><i class="fa fa-angle-right ps-2" aria-hidden="true"></i></div>
          </li>
          <li class="sidebar-main-title cat-general">
            <div>
              <h6>General</h6>
            </div>
          </li>
          @if(PermissionHelper::check('dashboard'))
          <li class="sidebar-list cat-general">
            <a class="sidebar-link sidebar-title link-nav {{ request()->routeIs('dashboard') || request()->routeIs('admin.dashboard.overall') ? 'active' : '' }}" href="{{ auth()->user()->role_as == 'Admin' ? route('admin.dashboard.overall') : route('dashboard') }}">
              <i data-feather="home"></i><span>Dashboard</span>
            </a>
          </li>
          @endif

          @if(auth()->user()->role_as == 'Admin')
          <li class="sidebar-list cat-general">
            <a class="sidebar-link sidebar-title link-nav {{ request()->routeIs('chat.index') ? 'active' : '' }}" href="{{ route('chat.index') }}">
              <i data-feather="message-circle" style="stroke: #25D366;"></i><span>WhatsApp Chat</span>
            </a>
          </li>
          @endif
          @if(auth()->user()->role_as == 'Admin')
          <li class="sidebar-list cat-general">
            <a class="sidebar-link sidebar-title link-nav {{ request()->routeIs('admin.dashboard.overall') ? 'active' : '' }}" href="{{ route('admin.dashboard.overall') }}">
              <i data-feather="monitor"></i><span>Overall Dashboard</span>
            </a>
          </li>
          @endif
          
          <!-- <li class="sidebar-list cat-general">
            <a class="sidebar-link sidebar-title link-nav {{ request()->routeIs('ai_studio.*') ? 'active' : '' }}" href="{{ route('ai_studio.index') }}">
              <i data-feather="cpu" style="stroke: #6366F1;"></i><span>AI Intelligence Studio</span>
            </a>
          </li> -->
          @if(PermissionHelper::check('old_data'))
          <li class="sidebar-list cat-general">
            <a class="sidebar-link sidebar-title link-nav {{ request()->routeIs('old_data.index') ? 'active' : '' }}" href="{{ route('old_data.index') }}">
              <i data-feather="database"></i><span>Old Data</span>
            </a>
          </li>
          @endif
          @if(PermissionHelper::check('roto_orders_heading'))
          <li class="sidebar-main-title cat-roto">
            <div>
              <h6>New/Repeat Roto Order</h6>
            </div>
          </li>
          @endif
           @if(PermissionHelper::check('roto_orders'))
           <li class="sidebar-list cat-roto">
            <a class="sidebar-link sidebar-title">
              <i data-feather="package"></i><span>Manage Roto Order</span>
            </a>
            <ul class="sidebar-submenu">
              <li><a href="{{ route('job_card.index', ['type' => 'all']) }}">All Orders</a></li>
              <li><a href="{{ route('job_card.index', ['type' => 'pending']) }}">Pending Orders</a></li>
              <li><a href="{{ route('job_card.index', ['type' => 'new']) }}">New Order</a></li>
              <li><a href="{{ route('job_card.index', ['type' => 'repeat']) }}">Repeat Order</a></li>
              <li><a href="{{ route('job_card.index', ['type' => 'Completed']) }}">Completed Orders</a></li>
            </ul>
          </li>

          @endif
            
       
          @if(PermissionHelper::check('order_process_heading'))
          <li class="sidebar-main-title cat-process">
            <div>
              <h6>All Order Process </h6>
            </div>
          </li>
          @endif
          @if(PermissionHelper::check('order_process'))
          <li class="sidebar-list cat-process">
            <a class="sidebar-link sidebar-title">
              <i data-feather="package"></i><span>Order Process</span>
            </a>
            <ul class="sidebar-submenu">
              @if(PermissionHelper::check('process_cylinder_come'))
              <li><a href="{{ route('job_card.process', ['type' => 'Cylinder Come']) }}">Cylinder Come</a></li>
              @endif
              @if(PermissionHelper::check('process_order_list'))
              <li><a href="{{ route('job_card.process', ['type' => 'Order List']) }}">Order List</a></li>
              @endif
              @if(PermissionHelper::check('process_printing'))
              <li><a href="{{ route('job_card.process', ['type' => 'Schedule For Printing']) }}">Schedule For Printing</a></li>
              @endif
              @if(PermissionHelper::check('process_bopp_list'))
              <li><a href="{{ route('job_card.process', ['type' => 'Printed Bopp List']) }}">Printed Bopp List</a></li>
              @endif
              @if(PermissionHelper::check('process_lamination'))
              <li><a href="{{ route('job_card.process', ['type' => 'Schedule For Lamination']) }}">Schedule For Lamination</a></li>
              @endif
              @if(PermissionHelper::check('process_laminated_rolls'))
              <li><a href="{{ route('job_card.process', ['type' => 'Laminated Rolls']) }}">Laminated Rolls</a></li>
              @endif
              @if(PermissionHelper::check('process_box_cutting'))
              <li><a href="{{ route('job_card.process', ['type' => 'Schedule For Box / Cutting']) }}">Schedule For Box / Cutting</a></li>
              @endif
              @if(PermissionHelper::check('process_ready_bags'))
              <li><a href="{{ route('job_card.process', ['type' => 'Ready Bags List']) }}">Ready Bags List</a></li>
              @endif
              @if(PermissionHelper::check('process_packing_slip'))
              <li><a href="{{ route('job_card.process', ['type' => 'Packing Slip']) }}">Packing Slip</a></li>
              @endif
              @if(PermissionHelper::check('process_dispatch'))
              <li><a href="{{ route('job_card.process', ['type' => 'Dispatch Material']) }}">Dispatch Material</a></li>
              @endif
            </ul>
          </li>
          @endif
           
          @if(PermissionHelper::check('packing_slip_heading'))
          <li class="sidebar-main-title cat-packing">
            <div>
              <h6>Packing Slip </h6>
            </div>
          </li>
          @endif
          @if(PermissionHelper::check('packing_slip'))
          <li class="sidebar-list cat-packing">
            <a class="sidebar-link sidebar-title">
              <i data-feather="package"></i><span>Roto Packing Slip</span>
            </a>
            <ul class="sidebar-submenu">
              <li><a href="{{ route('packing_slip.index', ['type' => 'pending']) }}">Order Pending Slip</a></li>
              <li><a href="{{ route('packing_slip.index', ['type' => 'complete']) }}">Complete Packing Slip</a></li>
            </ul>
          </li>
          
          @endif
           @if(PermissionHelper::check('packing_slip_common'))
          <li class="sidebar-list cat-packing">
            <a class="sidebar-link sidebar-title {{ request()->routeIs('packing_slip_common.*') ? 'active' : '' }}" href="#">
              <i data-feather="file-text"></i><span>Packing Slip - Common</span>
            </a>
            <ul class="sidebar-submenu" style="{{ request()->routeIs('packing_slip_common.*') ? 'display: block;' : '' }}">
              <li><a href="{{ route('packing_slip_common.create') }}" class="{{ request()->routeIs('packing_slip_common.create') ? 'active' : '' }}">Add New Slip</a></li>
              <li><a href="{{ route('packing_slip_common.index') }}" class="{{ request()->routeIs('packing_slip_common.index') || request()->routeIs('packing_slip_common.edit') ? 'active' : '' }}">All Packing Slip</a></li>
            </ul>
          </li>
          @endif
           @if(PermissionHelper::check('account_pending_heading'))
          <li class="sidebar-main-title cat-accounts">
            <div>
              <h6>Account Department</h6>
            </div>
          </li>
          @endif
           @if(PermissionHelper::check('account_pending'))
           <li class="sidebar-list cat-accounts">
            <a class="sidebar-link sidebar-title link-nav {{ request()->routeIs('job_card.process', ['type' => 'Account Pending']) ? 'active' : '' }}" href="{{ route('job_card.process', ['type' => 'Account Pending']) }}">
              <i data-feather="users"></i><span>Account Pending</span>
            </a>
          </li> 
          @endif
          @if(PermissionHelper::check('bill_management'))
           <li class="sidebar-list cat-accounts">
            <a class="sidebar-link sidebar-title {{ request()->routeIs('bill.*') ? 'active' : '' }}" href="#">
              <i data-feather="file-text"></i><span>Bill Management</span>
            </a>
            <ul class="sidebar-submenu" style="{{ request()->routeIs('bill.*') ? 'display: block;' : '' }}">
              <li><a href="{{ route('bill.create') }}" class="{{ request()->routeIs('bill.create') ? 'active' : '' }}">Add Bill</a></li>
              <li><a href="{{ route('bill.index') }}" class="{{ request()->routeIs('bill.index') ? 'active' : '' }}">All Bills</a></li>
            </ul>
          </li> 
          @endif
          @if(PermissionHelper::check('customer_ledger'))
            <li class="sidebar-list cat-accounts">
             <a class="sidebar-link sidebar-title link-nav {{ request()->routeIs('customer_ledger.*') && !request()->routeIs('customer_ledger.transactions*') && !request()->routeIs('customer_ledger.report*') && !request()->routeIs('customer_ledger.logs*') ? 'active' : '' }}" href="{{ route('customer_ledger.index') }}">
               <i data-feather="book"></i><span>Customer Ledger</span>
             </a>
           </li> 
           @endif

           @if(PermissionHelper::check('vouchers'))
           <li class="sidebar-list cat-accounts">
             <a class="sidebar-link sidebar-title link-nav {{ request()->routeIs('customer_ledger.transactions') ? 'active' : '' }}" href="{{ route('customer_ledger.transactions') }}">
               <i data-feather="repeat"></i><span>Vouchers</span>
             </a>
           </li> 
           @endif

           @if(PermissionHelper::check('customer_ledger_report'))
           <li class="sidebar-list cat-accounts">
             <a class="sidebar-link sidebar-title link-nav {{ request()->routeIs('customer_ledger.report') ? 'active' : '' }}" href="{{ route('customer_ledger.report') }}">
               <i data-feather="file-text"></i><span>Customer Ledger Report</span>
             </a>
           </li> 
           @endif

           @if(auth()->user()->role_as == 'Admin' || PermissionHelper::check('ledger_log_record'))
           <li class="sidebar-list cat-accounts">
             <a class="sidebar-link sidebar-title link-nav {{ request()->routeIs('customer_ledger.logs') ? 'active' : '' }}" href="{{ route('customer_ledger.logs') }}">
               <i data-feather="clock"></i><span>Ledger Log Record</span>
             </a>
           </li> 
           @endif

       

           @if(PermissionHelper::check('ledger_followups'))
           <li class="sidebar-list cat-accounts">
             <a class="sidebar-link sidebar-title {{ request()->routeIs('ledger_followup.*') ? 'active' : '' }}">
               <i data-feather="calendar"></i><span>Ledger Followups</span>
             </a>
             <ul class="sidebar-submenu" style="{{ request()->routeIs('ledger_followup.*') ? 'display: block;' : '' }}">
               <li><a href="{{ route('ledger_followup.index') }}" class="{{ request()->routeIs('ledger_followup.index') && !request()->has('filter') ? 'active' : '' }}">All Ledger Followup</a></li>
               <li><a href="{{ route('ledger_followup.pending_today') }}" class="{{ request()->routeIs('ledger_followup.pending_today') ? 'active' : '' }}">Pending & Today Followup</a></li>
               <li><a href="{{ route('ledger_followup.history_all') }}" class="{{ request()->routeIs('ledger_followup.history_all') ? 'active' : '' }}">History Followup</a></li>
               <li><a href="{{ route('ledger_followup.report') }}" class="{{ request()->routeIs('ledger_followup.report') ? 'active' : '' }}">Employee Followup Report</a></li>
               <li><a href="{{ route('ledger_followup.customer_report') }}" class="{{ request()->routeIs('ledger_followup.customer_report') ? 'active' : '' }}">Customer Followup Report</a></li>
             </ul>
           </li> 
           @endif
          @if(PermissionHelper::check('stock_management_heading'))
          <li class="sidebar-main-title cat-stock">
            <div>
              <h6>Stock Management</h6>
            </div>
          </li>
          @endif
          @if(PermissionHelper::check('stock_management'))
          <li class="sidebar-list cat-stock">
            <a class="sidebar-link sidebar-title" href="#">
              <i data-feather="package"></i><span>Stock In </span>
            </a>
            <ul class="sidebar-submenu">
              <li><a href="{{ route('manage_stock.index',['stock_name'=>'fabric','in_out'=>'in']) }}">Fabric Stock In</a></li>
              <li><a href="{{ route('manage_stock.index',['stock_name'=>'bopp','in_out'=>'in']) }}">BOPP Stock In</a></li>
              <li><a href="{{ route('manage_stock.index',['stock_name'=>'ink','in_out'=>'in']) }}">Ink Stock In</a></li>
              <li><a href="{{ route('manage_stock.index',['stock_name'=>'dana','in_out'=>'in']) }}">Dana Stock In</a></li>
              <li><a href="{{ route('manage_stock.index',['stock_name'=>'loop','in_out'=>'in']) }}">Loop Color Stock In</a></li>
            
            </ul>
          </li>
          @endif
           @if(PermissionHelper::check('stock_management'))
          <li class="sidebar-list cat-stock">
            <a class="sidebar-link sidebar-title" href="#">
              <i data-feather="package"></i><span>Stock Out </span>
            </a>
            <ul class="sidebar-submenu">
              <li><a href="{{ route('manage_stock.index',['stock_name'=>'fabric','in_out'=>'out']) }}">Fabric Stock Out</a></li>
              <li><a href="{{ route('manage_stock.index',['stock_name'=>'bopp','in_out'=>'out']) }}">BOPP Stock Out</a></li>
              <li><a href="{{ route('manage_stock.index',['stock_name'=>'ink','in_out'=>'out']) }}">Ink Stock Out</a></li>
              <li><a href="{{ route('manage_stock.index',['stock_name'=>'dana','in_out'=>'out']) }}">Dana Stock Out</a></li>
              <li><a href="{{ route('manage_stock.index',['stock_name'=>'loop','in_out'=>'out']) }}">Loop Color Stock Out</a></li>
            
            </ul>
          </li>
          @endif
           @if(PermissionHelper::check('stock_management'))
          <li class="sidebar-list cat-stock">
            <a class="sidebar-link sidebar-title" href="#">
              <i data-feather="package"></i><span>Average Stock </span>
            </a>
            <ul class="sidebar-submenu">
              <li><a href="{{ route('manage_stock.average_index',['stock_name'=>'fabric']) }}">Fabric Stock Average</a></li>
              <li><a href="{{ route('manage_stock.average_index',['stock_name'=>'bopp']) }}">BOPP Stock Average</a></li>
              <li><a href="{{ route('manage_stock.average_index',['stock_name'=>'ink']) }}">Ink Stock Average</a></li>
              <li><a href="{{ route('manage_stock.average_index',['stock_name'=>'dana']) }}">Dana Stock Average</a></li>
              <li><a href="{{ route('manage_stock.average_index',['stock_name'=>'loop']) }}">Loop Color Stock Average</a></li>
            
            </ul>
          </li>
          @endif
           @if(PermissionHelper::check('common_product_stock'))
           <li class="sidebar-list cat-stock">
            <a class="sidebar-link sidebar-title" href="#">
              <i data-feather="package"></i><span>Common Product Stock</span>
            </a>
            <ul class="sidebar-submenu">
              <li><a href="{{ route('common_stock.index', ['in_out' => 'In']) }}">Common Stock In</a></li>
              <li><a href="{{ route('common_stock.index', ['in_out' => 'Out']) }}">Common Stock Out</a></li>
              <li><a href="{{ route('common_stock.remaining') }}">Remaining Stock</a></li>
              <li><a href="{{ route('common_stock.remaining_list') }}">Remaining Stock - List</a></li>
            </ul>
          </li>
          @endif
           @if(PermissionHelper::check('cylinder_management_heading'))
          <li class="sidebar-main-title cat-cylinder">
            <div>
              <h6>Cylinder Management</h6>
            </div>
          </li>
          @endif
           @if(PermissionHelper::check('cylinder_management'))
           <li class="sidebar-list cat-cylinder">
            <a class="sidebar-link sidebar-title" href="#">
              <i data-feather="package"></i><span>Manage Cylinder</span>
            </a>
            <ul class="sidebar-submenu">
              <li><a href="{{ route('cylinder_job.index',['type'=>'pending']) }}">Cylinder Out</a></li>
              <li><a href="{{ route('cylinder_job.index',['type'=>'report']) }}">Cylinder Report</a></li>
            </ul>
          </li>
          @endif
            @if(PermissionHelper::check('common_orders_heading'))
          <li class="sidebar-main-title cat-common">
            <div>
              <h6>Common Order</h6>
            </div>
          </li>
          @endif
           @if(PermissionHelper::check('common_orders'))
           <li class="sidebar-list cat-common">
            <a class="sidebar-link sidebar-title">
              <i data-feather="package"></i><span>Manage Common Order</span>
            </a>
            <ul class="sidebar-submenu">
              <li><a href="{{ route('job_card.index', ['type' => 'Common']) }}">All Common Orders</a></li>
              @if(auth()->user()->role_as == 'Admin')
              <li><a href="{{ route('common_order.create') }}">Backend Common</a></li>
              @endif
            </ul>
          </li>


          @endif
          @if(PermissionHelper::check('roto_order_report_heading'))
          <li class="sidebar-main-title cat-reports">
            <div>
              <h6>MIS Reports</h6>
            </div>
          </li>
          @endif
          @if(PermissionHelper::check('roto_order_report'))
          <li class="sidebar-list cat-reports">
            <a class="sidebar-link sidebar-title link-nav {{ request()->routeIs('job_card.report') ? 'active' : '' }}" href="{{ route('job_card.report') }}">
              <i data-feather="file-text"></i><span>Roto Order Report</span>
            </a>
          </li>
          @endif
          @if(PermissionHelper::check('cylinder_agent_report'))
          <li class="sidebar-list cat-reports">
            <a class="sidebar-link sidebar-title link-nav {{ request()->routeIs('cylinder_job.agent_report') ? 'active' : '' }}" href="{{ route('cylinder_job.agent_report') }}">
              <i data-feather="users"></i><span>Cylinder Agent Report</span>
            </a>
          </li>
          @endif
          @if(PermissionHelper::check('machine_wise_report'))
          <li class="sidebar-list cat-reports">
            <a class="sidebar-link sidebar-title link-nav {{ request()->routeIs('report.machine_wise') ? 'active' : '' }}" href="{{ route('report.machine_wise') }}">
              <i data-feather="settings"></i><span>Machine Wise Report</span>
            </a>
          </li>
          @endif

          {{-- Lead Management - Integrated from Leads App --}}
          @if(PermissionHelper::check('lead_master_heading'))
          <li class="sidebar-main-title cat-lead">
            <div>
              <h6>Lead Masters</h6>
            </div>
          </li>
          @endif
          
          @if(PermissionHelper::check('lead_master'))
          <li class="sidebar-list cat-lead">
            <a class="sidebar-link sidebar-title" href="#">
              <i data-feather="settings"></i><span>Lead Master</span>
            </a>
            <ul class="sidebar-submenu">
              <li><a href="{{ route('lead.source.index') }}">Lead Source</a></li>
              <li><a href="{{ route('lead.tag.index') }}">Lead Tags</a></li>
              <li><a href="{{ route('lead.status.index') }}">Lead Steps</a></li>
            </ul>
          </li>
          @endif

          @if(PermissionHelper::check('location_master'))
          <li class="sidebar-list cat-lead">
            <a class="sidebar-link sidebar-title" href="#">
              <i data-feather="map-pin"></i><span>Location Master</span>
            </a>
            <ul class="sidebar-submenu">
              <li><a href="{{ route('lead.locations.states') }}">States</a></li>
              <li><a href="{{ route('lead.locations.cities') }}">Cities</a></li>
            </ul>
          </li>
          @endif

          @if(PermissionHelper::check('agent_master_lead'))
          <li class="sidebar-list cat-lead">
            <a class="sidebar-link sidebar-title" href="#">
              <i data-feather="briefcase"></i><span>Agent Master (Leads)</span>
            </a>
            <ul class="sidebar-submenu">
              <li><a href="{{ route('lead.agent.deals_in.index') }}">Deals In</a></li>
              <li><a href="{{ route('lead.agent.agent.index') }}">Agents</a></li>
            </ul>
          </li>
          @endif

          @if(PermissionHelper::check('customer_lead_heading'))
          <li class="sidebar-main-title cat-lead">
            <div>
              <h6>Customer Leads</h6>
            </div>
          </li>
          @endif

          @if(PermissionHelper::check('customer_lead'))
          <li class="sidebar-list cat-lead">
            <a class="sidebar-link sidebar-title" href="#">
              <i data-feather="users"></i><span>Customer Lead</span>
            </a>
            <ul class="sidebar-submenu">
              <li><a href="{{ route('lead.create') }}">Add New Lead</a></li>
              <li><a href="{{ route('lead.index') }}">All Lead</a></li>
              <li><a href="{{ route('lead.pending') }}">Pending Lead</a></li>
              <li><a href="{{ route('lead.won') }}">Won Lead</a></li>
              <li><a href="{{ route('lead.lost') }}">Lost Lead</a></li>
            </ul>
          </li>
          @endif

          @if(PermissionHelper::check('repeat_suggestion'))
          <li class="sidebar-list cat-lead">
            <a class="sidebar-link sidebar-title link-nav {{ request()->routeIs('lead.repeat_suggestions') ? 'active' : '' }}" href="{{ route('lead.repeat_suggestions') }}">
              <i data-feather="repeat"></i><span>Repeat Suggestion</span> @if(isset($repeatSuggestionCount) && $repeatSuggestionCount > 0) <span class="badge badge-light-danger text-dark ms-1">{{ $repeatSuggestionCount }}</span> @endif
            </a>
          </li>
          @endif

          @if(PermissionHelper::check('agent_lead_heading'))
          <li class="sidebar-main-title cat-lead">
            <div>
              <h6>Agent Leads</h6>
            </div>
          </li>
          @endif

          @if(PermissionHelper::check('agent_lead'))
          <li class="sidebar-list cat-lead">
            <a class="sidebar-link sidebar-title" href="#">
              <i data-feather="truck"></i><span>Agent Lead</span>
            </a>
            <ul class="sidebar-submenu">
              <li><a href="{{ route('lead.agent_leads.create') }}">Add Agent Lead</a></li>
              <li><a href="{{ route('lead.agent_leads.index') }}">All Agent Lead</a></li>
              <li><a href="{{ route('lead.agent_leads.pending') }}">Pending Agent Lead</a></li>
              <li><a href="{{ route('lead.agent_leads.won') }}">Won Agent Lead</a></li>
              <li><a href="{{ route('lead.agent_leads.lost') }}">Lost Agent Lead</a></li>
            </ul>
          </li>
          @endif

          @if(PermissionHelper::check('agent_repeat_suggestion'))
          <li class="sidebar-list cat-lead">
            <a class="sidebar-link sidebar-title link-nav {{ request()->routeIs('lead.agent_leads.repeat_suggestions') ? 'active' : '' }}" href="{{ route('lead.agent_leads.repeat_suggestions') }}">
              <i data-feather="repeat" style="stroke: #ff9f43;"></i><span>Agent Repeat Sug.</span> @if(isset($agentRepeatSuggestionCount) && $agentRepeatSuggestionCount > 0) <span class="badge badge-light-warning text-dark ms-1">{{ $agentRepeatSuggestionCount }}</span> @endif
            </a>
          </li>
          @endif

          @if(PermissionHelper::check('lead_followup_heading'))
          <li class="sidebar-main-title cat-lead">
            <div>
              <h6>Lead Followups</h6>
            </div>
          </li>
          @endif

          @if(PermissionHelper::check('lead_followup'))
          <li class="sidebar-list cat-lead">
            <a class="sidebar-link sidebar-title link-nav {{ request()->routeIs('lead.followup.index') ? 'active' : '' }}" href="{{ route('lead.followup.index') }}">
              <i data-feather="calendar"></i><span>Lead Follow-ups</span>
            </a>
          </li>
          @endif

          @if(PermissionHelper::check('lead_report_heading') || PermissionHelper::check('lead_report') || PermissionHelper::check('agent_report_lead'))
          <li class="sidebar-main-title cat-lead">
            <div>
              <h6>Lead Reports</h6>
            </div>
          </li>
          @endif
          
          @if(PermissionHelper::check('lead_dashboard'))
          <li class="sidebar-list cat-lead">
            <a class="sidebar-link sidebar-title link-nav {{ request()->routeIs('lead.dashboard') ? 'active' : '' }}" href="{{ route('lead.dashboard') }}">
              <i data-feather="monitor"></i><span>Sales Lead Dashboard</span>
            </a>
          </li>
          @endif

          @if(PermissionHelper::check('lead_report'))
          <li class="sidebar-list cat-lead">
            <a class="sidebar-link sidebar-title link-nav {{ request()->routeIs('lead.report') ? 'active' : '' }}" href="{{ route('lead.report') }}">
              <i data-feather="pie-chart"></i><span>Lead Report</span>
            </a>
          </li>
          @endif

          @if(PermissionHelper::check('agent_report_lead'))
          <li class="sidebar-list cat-lead">
            <a class="sidebar-link sidebar-title link-nav {{ request()->routeIs('lead.agent_leads.report') ? 'active' : '' }}" href="{{ route('lead.agent_leads.report') }}">
              <i data-feather="trending-up" style="stroke: #ff9f43;"></i><span>Agent Report (Leads)</span>
            </a>
          </li>
          @endif

         
         
          
          
        
         
          
          @if(auth()->user()->role_as == 'Admin' || PermissionHelper::check('master_management'))
           <li class="sidebar-main-title cat-master">
            <div>
              <h6>Master Management</h6>
            </div>
          </li>
           @if(PermissionHelper::check('manage_master'))
           <li class="sidebar-list cat-master">
            <a class="sidebar-link sidebar-title" href="#">
              <i data-feather="package"></i><span>Manage Master</span>
            </a>
            <ul class="sidebar-submenu">
              <li><a href="{{ route('fabric.index') }}">Fabric</a></li>
              <li><a href="{{ route('bopp.index') }}">BOPP</a></li>
              <li><a href="{{ route('ink.index') }}">Ink</a></li>
              <li><a href="{{ route('dana.index') }}">Dana</a></li>
               <li><a href="{{ route('loop.index') }}">Loop Color</a></li>
               <li><a href="{{ route('size_color.index') }}">Size & Color</a></li>
               <li><a href="{{ route('fabric_size.index') }}">Fabric Size Calculation</a></li>
               <li><a href="{{ route('payment_method.index') }}">Payment Method</a></li>
            
            </ul>
          </li>
          @endif
           @if(PermissionHelper::check('agent_customer'))
           <li class="sidebar-list cat-master">
            <a class="sidebar-link sidebar-title link-nav {{ request()->routeIs('agent_customer.index') ? 'active' : '' }}" href="{{ route('agent_customer.index') }}">
              <i data-feather="users"></i><span>Agent / Customer</span>
            </a>
          </li> 
          @endif
          @if(PermissionHelper::check('agent_customer'))
           <li class="sidebar-list cat-master">
            <a class="sidebar-link sidebar-title link-nav {{ request()->routeIs('lead_agent_customer.index') ? 'active' : '' }}" href="{{ route('lead_agent_customer.index') }}">
              <i data-feather="user-plus" style="stroke: #ff9f43;"></i><span>Lead Agent / Customer</span>
            </a>
          </li> 
          @endif
          @if(PermissionHelper::check('cylinder_agent'))
          <li class="sidebar-list cat-master">
            <a class="sidebar-link sidebar-title link-nav {{ request()->routeIs('cylinder_agent.index') ? 'active' : '' }}" href="{{ route('cylinder_agent.index') }}">
              <i data-feather="users"></i><span>Cylinder Agent</span>
            </a>
          </li>
          @endif

           @if(PermissionHelper::check('machine_master'))
           <li class="sidebar-list cat-master">
            <a class="sidebar-link sidebar-title" href="#">
              <i data-feather="package"></i><span>Machine Master</span>
            </a>
            <ul class="sidebar-submenu">
              <li><a href="{{ route('machine.index',['type'=>'printing']) }}">Printing Machine</a></li>
              <li><a href="{{ route('machine.index',['type'=>'lamination']) }}">Lamination Machine</a></li>
              <li><a href="{{ route('machine.index',['type'=>'box']) }}">Box Machine</a></li>
              <li><a href="{{ route('machine.index',['type'=>'cutting']) }}">Cutting Machine</a></li>
            
            </ul>
          </li>
          @endif
           @if(PermissionHelper::check('blockage_reason'))
           <li class="sidebar-list cat-master">
            <a class="sidebar-link sidebar-title" href="#">
              <i data-feather="package"></i><span>Blockage Reason</span>
            </a>
            <ul class="sidebar-submenu">
              <li><a href="{{ route('blockage_reason.index',['type'=>'printing']) }}">Printing Blockage</a></li>
              <li><a href="{{ route('blockage_reason.index',['type'=>'lamination']) }}">Lamination Blockage</a></li>
              <li><a href="{{ route('blockage_reason.index',['type'=>'box']) }}">Box Blockage</a></li>
              <li><a href="{{ route('blockage_reason.index',['type'=>'cutting']) }}">Cutting Blockage</a></li>
              <li><a href="{{ route('blockage_reason.index',['type'=>'hold']) }}" style="color:#f39c12;font-weight:600;"><i class="fa fa-pause-circle me-1"></i>Hold Reason Master</a></li>
            
            </ul>
          </li>
          @endif
          @if(PermissionHelper::check('manage_master'))
          <li class="sidebar-list cat-master">
            <a class="sidebar-link sidebar-title link-nav {{ request()->routeIs('gsm_calculator.index') ? 'active' : '' }}" href="{{ route('gsm_calculator.index') }}">
              <i data-feather="plus-square" style="stroke: #3b82f6;"></i><span>GSM Calculator</span>
            </a>
          </li>
          <li class="sidebar-list cat-master">
            <a class="sidebar-link sidebar-title link-nav {{ request()->routeIs('executive_target.index') ? 'active' : '' }}" href="{{ route('executive_target.index') }}">
              <i data-feather="trending-up" style="stroke: #10b981;"></i><span>Executive Target</span>
            </a>
          </li>
          @endif
          @endif
         
        
          @if(PermissionHelper::check('team_management'))
          <li class="sidebar-main-title cat-team">
            <div>
              <h6>Team</h6>
            </div>
          </li>
         
          <li class="sidebar-list cat-team">
            <a class="sidebar-link sidebar-title link-nav {{ request()->routeIs('team.index') ? 'active' : '' }}" href="{{ route('team.index') }}">
              <i data-feather="users"></i><span>Team Management</span>
            </a>
          </li>
          @if(auth()->user()->role_as == 'Admin')
          <li class="sidebar-list cat-team">
            <a class="sidebar-link sidebar-title link-nav {{ request()->routeIs('admin.device_verifications') ? 'active' : '' }}" href="{{ route('admin.device_verifications') }}">
              <i data-feather="shield"></i><span>Login Verifications</span>
            </a>
          </li>
          @endif
          @endif
              @if(auth()->user()->role_as == 'Admin')
           <li class="sidebar-list cat-accounts">
             <a class="sidebar-link sidebar-title link-nav {{ request()->routeIs('employee_log.index') ? 'active' : '' }}" href="{{ route('employee_log.index') }}">
               <i data-feather="activity"></i><span>Employee Log</span>
             </a>
           </li> 
           <li class="sidebar-list cat-accounts">
             <a class="sidebar-link sidebar-title link-nav {{ request()->routeIs('employee_log.performance') ? 'active' : '' }}" href="{{ route('employee_log.performance') }}">
               <i data-feather="award"></i><span>Employee Performance</span>
             </a>
           </li> 
           @endif

  @if(PermissionHelper::check('website_setting'))        
          <li class="sidebar-main-title cat-team">
            <div>
              <h6>Settings</h6>
            </div>
          </li>
         

          <li class="sidebar-list cat-team">
            <a class="sidebar-link sidebar-title link-nav {{ request()->routeIs('website.setting') ? 'active' : '' }}" href="{{ route('website.setting') }}">
              <i data-feather="home"></i><span>Website Setting</span>
            </a>
          </li>
@endif
        </ul>
      </div>
      <div class="right-arrow" id="right-arrow"><i data-feather="arrow-right"></i></div>
    </nav>
  </div>
</div>