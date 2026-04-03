<?php

namespace App\Http\Controllers\Lead;

use App\Http\Controllers\Controller;
use App\Models\AgentCustomer;
use App\Models\AgentDealsIn;
use App\Models\LeadAgent;
use Illuminate\Http\Request;

class AgentMasterController extends Controller
{
    // Deals In Methods
    public function dealsInIndex()
    {
        $deals = AgentDealsIn::all();
        return view('lead.agent_master.deals_in.index', compact('deals'));
    }

    public function dealsInStore(Request $request)
    {
        $request->validate(['name' => 'required']);
        AgentDealsIn::updateOrCreate(['id' => $request->id], $request->all());
        return back()->with('success', 'Deals In saved successfully');
    }

    public function dealsInDelete($id)
    {
        AgentDealsIn::find($id)->delete();
        return back()->with('danger', 'Deals In deleted');
    }

    // Check if phone exists in agent_customers OR lead_agent_customers
    public function checkAgentPhone(Request $request)
    {
        $phone = $request->phone;
        if (!$phone || strlen($phone) < 10) {
            return response()->json(['found' => false]);
        }

        $customer = AgentCustomer::where('phone_no', $phone)->first();
        $leadCustomer = \App\Models\LeadAgentCustomer::where('phone_no', $phone)->first();

        if ($customer) {
            if (strtolower($customer->role) === 'customer') {
                return response()->json([
                    'found'   => false,
                    'is_customer' => true,
                    'message' => 'This phone number belongs to a Customer. You cannot add a Customer as an Agent.'
                ]);
            }

            return response()->json([
                'found'               => true,
                'agent_customer_id'   => $customer->id,
                'name'                => strtoupper($customer->name ?? ''),
                'firm_name'           => strtoupper($customer->name ?? ''),
                'state'               => strtoupper($customer->state ?? ''),
                'city'                => strtoupper($customer->city ?? ''),
            ]);
        }

        if ($leadCustomer) {
            return response()->json([
                'found'               => true,
                'lead_agent_customer_id' => $leadCustomer->id,
                'name'                => strtoupper($leadCustomer->name ?? ''),
                'firm_name'           => strtoupper($leadCustomer->name ?? ''),
                'state'               => strtoupper($leadCustomer->state ?? ''),
                'city'                => strtoupper($leadCustomer->city ?? ''),
            ]);
        }

        return response()->json(['found' => false]);
    }

    // Agent Methods
    public function agentIndex()
    {
        $agents = LeadAgent::with('dealsIn', 'agentCustomer', 'leadAgentCustomer')->get();
        $deals = AgentDealsIn::where('status', 1)->get();
        // ... (states array same)
        $states = [
            "Andhra Pradesh", "Arunachal Pradesh", "Assam", "Bihar", "Chhattisgarh", "Goa", "Gujarat", 
            "Haryana", "Himachal Pradesh", "Jharkhand", "Karnataka", "Kerala", "Madhya Pradesh", 
            "Maharashtra", "Manipur", "Meghalaya", "Mizoram", "Nagaland", "Odisha", "Punjab", 
            "Rajasthan", "Sikkim", "Tamil Nadu", "Telangana", "Tripura", "Uttar Pradesh", 
            "Uttarakhand", "West Bengal", "Delhi", "Chandigarh", "Jammu & Kashmir", "Ladakh"
        ];
        return view('lead.agent_master.agent.index', compact('agents', 'deals', 'states'));
    }

    public function agentStore(Request $request)
    {
        $request->validate([
            'name'  => 'required',
            'phone' => 'required|digits:10',
            'state' => 'required',
            'city'  => 'required',
        ]);

        // Final check to prevent adding customer as agent
        $existing = \App\Models\AgentCustomer::where('phone_no', $request->phone)->first();
        if ($existing && strtolower($existing->role) === 'customer') {
            $msg = 'This phone number belongs to a Customer and cannot be added as an Agent.';
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $msg], 422);
            }
            return back()->with('error', $msg)->withInput();
        }

        $data = $request->only(['name', 'firm_name', 'phone', 'state', 'city', 'deals_in_id', 'status']);
        $data['agent_customer_id'] = $request->agent_customer_id ?? null;
        $data['lead_agent_customer_id'] = $request->lead_agent_customer_id ?? null;

        // Sync to LeadAgentCustomer Master if not already in AgentCustomer
        if (!$data['agent_customer_id']) {
            $lac = \App\Models\LeadAgentCustomer::where('phone_no', $request->phone)->first();
            if (!$lac) {
                $lac = \App\Models\LeadAgentCustomer::create([
                    'name' => $request->name,
                    'phone_no' => $request->phone,
                    'role' => 'Agent',
                    'type' => 'A',
                    'state' => $request->state,
                    'city' => $request->city,
                    'status' => 1,
                    'user_id' => auth()->id()
                ]);
                $lac->code = 'SPFA' . $lac->id . rand(10000, 99999);
                $lac->save();
            } else {
                $lac->update(['role' => 'Agent']);
            }
            $data['lead_agent_customer_id'] = $lac->id;
        }

        $agent = LeadAgent::updateOrCreate(['id' => $request->id], $data);

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'agent_id' => $agent->id, 'message' => 'Agent saved successfully']);
        }
        return back()->with('success', 'Agent saved successfully');
    }

    public function agentDelete($id)
    {
        LeadAgent::find($id)->delete();
        return back()->with('danger', 'Agent deleted');
    }
    
    public function getAgentsJson()
    {
        $agents = LeadAgent::where('status', 1)->get();
        return response()->json($agents);
    }
}
