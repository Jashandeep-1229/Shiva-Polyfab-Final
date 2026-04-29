<?php

namespace App\Models;

use App\Traits\AutoLogsActivity;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\SoftDeletes;

class Lead extends Model
{
    use HasFactory, SoftDeletes, AutoLogsActivity;

    protected $fillable = [
        'lead_no', 'order_no', 'name', 'email', 'phone', 'address', 'state', 'city', 
        'source_id', 'regarding', 'architect_builder', 'sales_coordinator', 
        'sales_person', 'product', 'amount', 'site_stage',
        'assigned_user_id', 'status_id', 'added_by', 'remarks', 'lead_remarks'
    ];

    public function source()
    {
        return $this->belongsTo(LeadSource::class, 'source_id');
    }

    public function assignedUser()
    {
        return $this->belongsTo(User::class, 'assigned_user_id');
    }

    public function status()
    {
        return $this->belongsTo(LeadStatus::class, 'status_id');
    }

    public function addedBy()
    {
        return $this->belongsTo(User::class, 'added_by');
    }

    public function tags()
    {
        return $this->belongsToMany(LeadTag::class, 'lead_tag_pivot', 'lead_id', 'tag_id');
    }

    public function followups()
    {
        return $this->hasMany(LeadFollowup::class);
    }

    public function latestFollowup()
    {
        return $this->hasOne(LeadFollowup::class)->latestOfMany();
    }

    public function histories()
    {
        return $this->hasMany(LeadHistory::class);
    }

    public function stepDetails()
    {
        return $this->hasMany(LeadStepDetail::class);
    }

    public function agentCustomer()
    {
        return $this->hasOne(AgentCustomer::class, 'phone_no', 'phone');
    }

    public function leadAgentCustomer()
    {
        return $this->hasOne(LeadAgentCustomer::class, 'phone_no', 'phone');
    }
}
