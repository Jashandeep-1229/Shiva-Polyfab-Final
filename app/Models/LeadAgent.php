<?php

namespace App\Models;

use App\Traits\AutoLogsActivity;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeadAgent extends Model
{
    use HasFactory, AutoLogsActivity;

    protected $fillable = ['agent_customer_id', 'lead_agent_customer_id', 'name', 'firm_name', 'phone', 'state', 'city', 'deals_in_id', 'status'];

    public function agentCustomer()
    {
        return $this->belongsTo(AgentCustomer::class, 'agent_customer_id');
    }

    public function leadAgentCustomer()
    {
        return $this->belongsTo(LeadAgentCustomer::class, 'lead_agent_customer_id');
    }

    public function dealsIn()
    {
        return $this->belongsTo(AgentDealsIn::class, 'deals_in_id');
    }

    public function overallFollowups()
    {
        return $this->hasMany(AgentOverallFollowup::class, 'agent_id');
    }

    public function latestPendingOverallFollowup()
    {
        return $this->hasOne(AgentOverallFollowup::class, 'agent_id')->where('status', 0)->latest('followup_date');
    }

    public function leads()
    {
        return $this->hasMany(AgentLead::class, 'agent_id');
    }

    public function latestLead()
    {
        return $this->hasOne(AgentLead::class, 'agent_id')->latest('id');
    }
}
