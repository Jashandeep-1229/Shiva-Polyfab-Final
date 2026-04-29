<?php

namespace App\Models;

use App\Traits\AutoLogsActivity;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LeadAgentCustomer extends Model
{
    use HasFactory, SoftDeletes, AutoLogsActivity;

    protected $table = 'lead_agent_customers';

    protected $fillable = [
        'code',
        'name',
        'phone_no',
        'role',
        'type',
        'sale_executive_id',
        'user_id',
        'gst',
        'address',
        'pincode',
        'state',
        'city',
        'remarks',
        'status',
        'lead_id',
        'agent_lead_id',
    ];

    public function lead()
    {
        return $this->belongsTo(Lead::class, 'lead_id');
    }

    public function agent_lead()
    {
        return $this->belongsTo(AgentLead::class, 'agent_lead_id');
    }

    public function sale_executive()
    {
        return $this->belongsTo(User::class, 'sale_executive_id');
    }
}
