<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // 1. Process Leads
        $leads = \DB::table('leads')->get();
        foreach ($leads as $lead) {
            if (!$lead->phone) continue;

            $existing = \DB::table('agent_customers')->where('phone_no', $lead->phone)->first();
            if ($existing) {
                if (!$existing->lead_id) {
                    \DB::table('agent_customers')->where('id', $existing->id)->update(['lead_id' => $lead->id]);
                }
                continue;
            }

            // Create new record
            $id = \DB::table('agent_customers')->insertGetId([
                'name' => $lead->name,
                'phone_no' => $lead->phone,
                'role' => 'Customer',
                'type' => 'A',
                'address' => $lead->address,
                'state' => $lead->state,
                'city' => $lead->city,
                'sale_executive_id' => $lead->assigned_user_id ?: ($lead->added_by ?: 1),
                'user_id' => $lead->added_by ?: 1,
                'status' => 1,
                'is_lead' => 1,
                'lead_id' => $lead->id,
                'remarks' => $lead->remarks,
                'gst' => 'NA',
                'created_at' => $lead->created_at,
                'updated_at' => $lead->updated_at,
            ]);

            // Generate code
            $code = 'SPFC' . $id . rand(1000, 9999);
            \DB::table('agent_customers')->where('id', $id)->update(['code' => $code]);
        }

        // 2. Process Agent Leads
        $agentLeads = \DB::table('agent_leads')
            ->join('lead_agents', 'agent_leads.agent_id', '=', 'lead_agents.id')
            ->select('agent_leads.*', 'lead_agents.name as agent_name', 'lead_agents.phone as agent_phone', 'lead_agents.state as agent_state', 'lead_agents.city as agent_city')
            ->get();

        foreach ($agentLeads as $al) {
            if (!$al->agent_phone) continue;

            $existing = \DB::table('agent_customers')->where('phone_no', $al->agent_phone)->first();
            if ($existing) {
                if (!$existing->agent_lead_id) {
                    \DB::table('agent_customers')->where('id', $existing->id)->update(['agent_lead_id' => $al->id]);
                }
                continue;
            }

            // Create new record
            $id = \DB::table('agent_customers')->insertGetId([
                'name' => $al->agent_name,
                'phone_no' => $al->agent_phone,
                'role' => 'Agent',
                'type' => 'A',
                'address' => '', // No address in lead_agents
                'state' => $al->agent_state,
                'city' => $al->agent_city,
                'sale_executive_id' => $al->assigned_user_id ?: ($al->added_by ?: 1),
                'user_id' => $al->added_by ?: 1,
                'status' => 1,
                'is_lead' => 1,
                'agent_lead_id' => $al->id,
                'remarks' => $al->remarks,
                'gst' => 'NA',
                'created_at' => $al->created_at,
                'updated_at' => $al->updated_at,
            ]);

            // Generate code
            $code = 'SPFA' . $id . rand(1000, 9999);
            \DB::table('agent_customers')->where('id', $id)->update(['code' => $code]);
            
            // Link LeadAgent back
            \DB::table('lead_agents')->where('id', $al->agent_id)->update(['agent_customer_id' => $id]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // No easy rollback for data migration without potentially deleting manual entries
    }
};
