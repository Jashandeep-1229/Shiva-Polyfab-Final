<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AgentLeadFollowup extends Model
{
    use HasFactory;

    protected $fillable = [
        'agent_lead_id', 'status_at_time_id', 'type', 'followup_date', 
        'complete_date', 'remarks', 'delay_days', 'added_by', 'completed_by'
    ];

    public function agentLead()
    {
        return $this->belongsTo(AgentLead::class);
    }

    public function statusAtTime()
    {
        return $this->belongsTo(LeadStatus::class, 'status_at_time_id');
    }

    public function adder()
    {
        return $this->belongsTo(User::class, 'added_by');
    }

    public function completer()
    {
        return $this->belongsTo(User::class, 'completed_by');
    }
}
