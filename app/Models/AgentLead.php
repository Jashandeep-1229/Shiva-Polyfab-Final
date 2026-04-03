<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AgentLead extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'lead_no', 'name_of_job', 'agent_id', 'requirement', 'remarks', 
        'status_id', 'assigned_user_id', 'added_by', 'order_no'
    ];

    public function agent()
    {
        return $this->belongsTo(LeadAgent::class, 'agent_id');
    }

    public function status()
    {
        return $this->belongsTo(LeadStatus::class, 'status_id');
    }

    public function assignedUser()
    {
        return $this->belongsTo(User::class, 'assigned_user_id');
    }

    public function addedBy()
    {
        return $this->belongsTo(User::class, 'added_by');
    }

    public function followups()
    {
        return $this->hasMany(AgentLeadFollowup::class);
    }

    public function histories()
    {
        return $this->hasMany(AgentLeadHistory::class);
    }
}
