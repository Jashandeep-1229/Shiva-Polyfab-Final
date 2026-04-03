<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LeadFollowup extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'lead_id', 'type', 'status_at_time_id', 'followup_date', 'complete_date', 'delay_days', 
        'remarks', 'next_followup_date', 'added_by', 'completed_by'
    ];

    public function status()
    {
        return $this->belongsTo(LeadStatus::class, 'status_at_time_id');
    }

    public function lead()
    {
        return $this->belongsTo(Lead::class);
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
