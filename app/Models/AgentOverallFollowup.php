<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AgentOverallFollowup extends Model
{
    use HasFactory;

    protected $fillable = [
        'agent_id', 'followup_date', 'complete_date', 'remarks', 
        'complete_remarks', 'delay_days', 'added_by', 'completed_by', 'status'
    ];

    public function agent()
    {
        return $this->belongsTo(LeadAgent::class, 'agent_id');
    }

    public function addedBy()
    {
        return $this->belongsTo(User::class, 'added_by');
    }

    public function completedBy()
    {
        return $this->belongsTo(User::class, 'completed_by');
    }
}
