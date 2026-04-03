<?php

namespace App\Http\Controllers;

use App\Models\LeadAgentCustomer;
use App\Helpers\PermissionHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LeadAgentCustomerController extends Controller
{
    public function index()
    {
       if (!PermissionHelper::check('agent_customer')) {
           abort(403, 'Unauthorized access to Lead Agent / Customer Master.');
       }
       $sales_executives = \App\Models\User::whereIn('role_as', ['Sale Executive', 'Senior Sale Executive'])->where('status', 1)->get();
       return view('admin.lead_agent_customer.index', compact('sales_executives'));
    }

    public function datatable(Request $request){
        $number = $request->value ?? 50;
        $query = LeadAgentCustomer::with('sale_executive');
        
        if($request->search){
            $query->where(function($q) use ($request){
                $q->where('name','like','%'.$request->search.'%')
                ->orWhere('phone_no','like','%'.$request->search.'%')
                ->orWhere('code','like','%'.$request->search.'%')
                ->orWhere('role','like','%'.$request->search.'%');
            });
        }
        if($request->sale_executive_id){
            $query->where('sale_executive_id', $request->sale_executive_id);
        }
        if($request->role_filter){
            $query->where('role', $request->role_filter);
        }
        
        $lead_customers = $query->orderBy('name', 'asc')->paginate($number);
        return view('admin.lead_agent_customer.datatable', compact('lead_customers'));
    }

    public function delete($id)
    {
        if (!PermissionHelper::check('agent_customer', 'delete')) {
            return ['result' => -1, 'message' => 'Access Denied!'];
        }
        $lac = LeadAgentCustomer::find($id);
        if($lac) {
            $lac->delete();
            return ['result' => 1, 'message' => 'Deleted Successfully'];
        }
        return ['result' => -1, 'message' => 'Record not found'];
    }
}
