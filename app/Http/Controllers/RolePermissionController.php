<?php

namespace App\Http\Controllers;

use App\Models\MenuPermission;
use App\Models\User;
use Illuminate\Http\Request;

class RolePermissionController extends Controller
{
    protected $menus = [
        'General' => [
            'dashboard' => 'Dashboard Access',
            'old_data' => 'Menu: Old Data',
            'sales_dashboard_full_access' => 'Full Sales Dashboard Access (Manager/EA)',
        ],
        'Roto Orders' => [
            'roto_orders_heading' => 'Section Heading: New/Repeat Roto Order',
            'roto_orders' => 'Manage Roto Order (All Lists)',
        ],
        'All Order Process' => [
            'order_process_heading' => 'Section Heading: All Order Process',
            'order_process' => 'Order Process Dropdown',
            'process_cylinder_come' => 'Step: Cylinder Come',
            'process_order_list' => 'Step: Order List',
            'process_printing' => 'Step: Schedule For Printing',
            'process_bopp_list' => 'Step: Printed Bopp List',
            'process_lamination' => 'Step: Schedule For Lamination',
            'process_laminated_rolls' => 'Step: Laminated Rolls',
            'process_box_cutting' => 'Step: Schedule For Box / Cutting',
            'process_ready_bags' => 'Step: Ready Bags List',
            'process_packing_slip' => 'Step: Packing Slip',
            'process_dispatch' => 'Step: Dispatch Material',
        ],
        'Packing Slips' => [
            'packing_slip_heading' => 'Section Heading: Packing Slip',
            'packing_slip' => 'Roto Packing Slip (Index)',
            'packing_slip_common' => 'Packing Slip - Common (Custom Group)',
        ],
        'Account Department' => [
            'account_pending_heading' => 'Section Heading: Account Department',
            'account_pending' => 'Menu: Account Pending',
            'bill_management' => 'Menu: Bill Management',
            'customer_ledger' => 'Menu: Customer Ledger',
            'vouchers' => 'Menu: Vouchers (Transactions List)',
            'customer_ledger_report' => 'Menu: Customer Ledger Report',
            'ledger_followups' => 'Menu: Ledger Followups',
            'ledger_log_record' => 'Menu: Ledger Log Record (Logs)',
        ],
        'Stock & Inventory' => [
            'stock_management_heading' => 'Section Heading: Stock Management',
            'stock_management' => 'Manage Inventory (In/Out/Average)',
            'common_product_stock' => 'Menu: Common Product Stock',
        ],
        'Cylinder Management' => [
            'cylinder_management_heading' => 'Section Heading: Cylinder Management',
            'cylinder_management' => 'Manage Cylinder (Out/Report)',
        ],
        'Common Order' => [
            'common_orders_heading' => 'Section Heading: Common Order',
            'common_orders' => 'Manage Common Order (Index)',
        ],
        'MIS Reports' => [
            'roto_order_report_heading' => 'Section Heading: MIS Reports',
            'roto_order_report' => 'Roto Order Report',
            'cylinder_agent_report' => 'Cylinder Agent Report',
            'machine_wise_report' => 'Machine Wise Report',
        ],
        'Master Management' => [
            'master_management' => 'Section Heading: Master Management',
            'manage_master' => 'Sub-Menu: Manage Master (Fabric, BOPP, etc.)',
            'agent_customer' => 'Menu: Agent / Customer',
            'cylinder_agent' => 'Menu: Cylinder Agent',
            'machine_master' => 'Menu: Machine Master',
            'blockage_reason' => 'Menu: Blockage Reason',
        ],
        'Lead Management' => [
            'lead_master_heading' => 'Section Heading: Lead Masters',
            'lead_master' => 'Master: Lead (Source, Tags, Steps)',
            'location_master' => 'Master: Location (States, Cities)',
            'agent_master_lead' => 'Master: Agent (Deals In, Agent List)',
            'customer_lead_heading' => 'Section Heading: Leads - Customer',
            'customer_lead' => 'Menu: Customer Lead (Add/Lists)',
            'repeat_suggestion' => 'Menu: Repeat Suggestion',
            'agent_lead_heading' => 'Section Heading: Leads - Agent',
            'agent_lead' => 'Menu: Agent Lead (Add/Lists)',
            'agent_repeat_suggestion' => 'Menu: Agent Repeat Sug.',
            'lead_followup_heading' => 'Section Heading: Followup Management',
            'lead_followup' => 'Menu: Follow-ups (All)',
            'lead_dashboard' => 'Report: Sales Lead Dashboard (Weekly Trends/Leaderboard)',
            'lead_report_heading' => 'Section Heading: Lead Reports',
            'lead_report' => 'Report: Lead Report',
            'agent_report_lead' => 'Report: Agent Report',
        ],
    ];

    public function index()
    {
        if (!\App\Helpers\PermissionHelper::check('role_permission')) {
            abort(403, 'Unauthorized access to Role Permissions.');
        }
        $roles = User::where('role_as', '!=', 'Admin')->distinct()->pluck('role_as')->toArray();
        return view('admin.role_permission.index', [
            'roles' => $roles,
            'menus' => $this->menus
        ]);
    }

    public function get_permissions(Request $request)
    {
        $role = $request->role;
        $user_id = $request->user_id;

        if ($user_id) {
            $permissions = MenuPermission::where('user_id', $user_id)->get()->keyBy('menu_key');
            $user = User::find($user_id);
            $role = $user->role_as;
            
            // If no user-specific permissions found, load role defaults
            if ($permissions->isEmpty()) {
                $permissions = MenuPermission::where('role_name', $role)->get()->keyBy('menu_key');
            }
        } else {
            $permissions = MenuPermission::where('role_name', $role)->get()->keyBy('menu_key');
        }
        
        return view('admin.role_permission.permission_table', [
            'role' => $role,
            'user_id' => $user_id,
            'menus' => $this->menus,
            'permissions' => $permissions
        ]);
    }

    public function store(Request $request)
    {
        foreach ($request->permissions as $menu_key => $values) {
            $match = [
                'menu_key' => $menu_key
            ];
            
            if ($request->user_id) {
                $match['user_id'] = $request->user_id;
            } else {
                $match['role_name'] = $request->role_name;
            }

            MenuPermission::updateOrCreate(
                $match,
                [
                    'role_name' => $request->role_name,
                    'can_view' => isset($values['view']) ? 1 : 0,
                    'can_add' => isset($values['add']) ? 1 : 0,
                    'can_edit' => isset($values['edit']) ? 1 : 0,
                    'can_next_process' => isset($values['next_process']) ? '1' : '0',
                    'data_access' => $values['data_access'] ?? 'owned',
                ]
            );
        }

        return [
            'result' => 1,
            'message' => 'Permissions Updated Successfully'
        ];
    }
}
