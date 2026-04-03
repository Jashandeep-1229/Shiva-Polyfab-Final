<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        $marchCutoff = '2026-03-25 00:00:00';

        // 1. Identify all leads remaining in agent_customers
        $leads = DB::table('agent_customers')
            ->where('is_lead', 1)
            ->get();

        foreach ($leads as $ac) {
            // Check if this lead was ALREADY moved (deduplication)
            $alreadyMoved = DB::table('lead_agent_customers')
                ->where('name', $ac->name)
                ->where('phone_no', $ac->phone_no)
                ->exists();

            $newId = null;
            if (!$alreadyMoved) {
                // ADD to lead_agent_customers (Treating all 71 as leads in the new module)
                $newId = DB::table('lead_agent_customers')->insertGetId([
                    'code' => $ac->code,
                    'name' => $ac->name,
                    'phone_no' => $ac->phone_no,
                    'role' => $ac->role,
                    'type' => $ac->type,
                    'sale_executive_id' => $ac->sale_executive_id,
                    'user_id' => $ac->user_id,
                    'gst' => $ac->gst,
                    'address' => $ac->address,
                    'pincode' => $ac->pincode,
                    'state' => $ac->state,
                    'city' => $ac->city,
                    'remarks' => $ac->remarks,
                    'status' => $ac->status,
                    'lead_id' => $ac->lead_id,
                    'agent_lead_id' => $ac->agent_lead_id,
                    'created_at' => $ac->created_at,
                    'updated_at' => $ac->updated_at,
                ]);
            } else {
                // If already moved, get the existing ID for linking
                $newId = DB::table('lead_agent_customers')
                    ->where('name', $ac->name)
                    ->where('phone_no', $ac->phone_no)
                    ->value('id');
            }

            // 2. Update references in lead_agents (Firms/Persons) for EVERY lead
            DB::table('lead_agents')
                ->where('agent_customer_id', $ac->id)
                ->update([
                    'lead_agent_customer_id' => $newId,
                    'agent_customer_id' => null
                ]);

            // 3. Purge criteria: ONLY delete if post-cutoff AND has NO ledger entries
            $hasLedger = DB::table('customer_ledgers')->where('customer_id', $ac->id)->exists();
            $isAfterCutoff = $ac->created_at > $marchCutoff;

            if ($isAfterCutoff && !$hasLedger) {
                // Delete from agent_customers (Safe purge)
                DB::table('agent_customers')->where('id', $ac->id)->delete();
            } else {
                // Keep it, but treat as full customer for accounting
                DB::table('agent_customers')->where('id', $ac->id)->update(['is_lead' => 0]);
            }
        }

        // 4. Cleanup agent_customers table schema
        Schema::table('agent_customers', function (Blueprint $table) {
            $table->dropColumn(['is_lead', 'lead_id', 'agent_lead_id']);
        });
    }

    public function down()
    {
        // No easy reversal as data was moved across tables and columns dropped
    }
};
