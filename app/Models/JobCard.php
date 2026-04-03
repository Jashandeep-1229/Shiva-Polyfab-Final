<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class JobCard extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $fillable = [
        'user_id',
        'job_type',
        'size_id',
        'color_id',
        'name_of_job',
        'lead_id',
        'agent_lead_id',
        'job_card_no',

        'bopp_id',
        'fabric_id',
        'no_of_pieces',
        'actual_pieces',
        'loop_color',
        'order_send_for',
        'dispatch_date',
        'job_card_date',
        'job_card_type',
        'cylinder_given_id',
        'customer_agent_id',
        'sale_executive_id',
        'file_upload',
        'remarks',
        'complete_date',
        'complete_by_id',
        'cancel_date',
        'cancel_by_id',
        'cancel_reason',
        'late_reasons',
        'software_remarks',
        'job_card_process',
        'is_editable',
        'is_hold',
        'status',
        'billing_date',
        'billing_invoice_no',
        'billing_weight',
        'billing_rate',
        'billing_extra',
        'billing_gst_percent',
        'billing_total_price',
        'cylinder_billing_weight',
        'cylinder_billing_rate',
        'cylinder_billing_gst_percent',
        'cylinder_billing_total',
        // Hold fields
        'hold_reason_id',
        'hold_notes',
        'held_at',
        'held_by_id',
    ];

    protected $casts = [
        'user_id' => 'integer',
        'bopp_id' => 'integer',
        'fabric_id' => 'integer',
        'cylinder_given_id' => 'integer',
        'customer_agent_id' => 'integer',
        'sale_executive_id' => 'integer',
        'complete_by_id' => 'integer',
        'cancel_by_id' => 'integer',
        'no_of_pieces' => 'integer',
        'job_card_date' => 'datetime',
        'billing_date' => 'date',
        'billing_weight' => 'float',
        'billing_rate' => 'float',
        'billing_extra' => 'float',
        'billing_gst_percent' => 'float',
        'billing_total_price' => 'float',
        'cylinder_billing_weight' => 'float',
        'cylinder_billing_rate' => 'float',
        'cylinder_billing_gst_percent' => 'float',
        'cylinder_billing_total' => 'float',
        'held_at'                => 'datetime',
        'held_by_id'             => 'integer',
        'hold_reason_id'         => 'integer',
    ];
    public function cylinder_agent(){
        return $this->belongsTo(CylinderAgent::class,'cylinder_given_id');
    }
    public function fabric(){
        return $this->belongsTo(Fabric::class,'fabric_id');
    }
    public function bopp(){
        return $this->belongsTo(Bopp::class,'bopp_id');
    }
    public function sale_executive(){
        return $this->belongsTo(User::class,'sale_executive_id');
    }
    public function complete_by(){
        return $this->belongsTo(User::class,'complete_by_id');
    }
    public function cancel_by(){
        return $this->belongsTo(User::class,'cancel_by_id');
    }
    public function user(){
        return $this->belongsTo(User::class,'user_id');
    }
    public function customer_agent(){
        return $this->belongsTo(AgentCustomer::class,'customer_agent_id');
    }
    public function color(){
        return $this->belongsTo(ColorMaster::class,'color_id');
    }
    public function size(){
        return $this->belongsTo(SizeMaster::class,'size_id');
    }

    public function cylinder_job(){
        return $this->hasMany(CylinderJob::class,'job_card_id');
    }
    public function processes(){
        return $this->hasMany(JobCardProcess::class,'job_card_id');
    }
    public function packing_slips(){
        return $this->hasMany(PackingSlip::class,'job_card_id');
    }

    public function lead()
    {
        return $this->belongsTo('App\Models\Lead', 'lead_id');
    }

    public function agentLead()
    {
        return $this->belongsTo('App\Models\AgentLead', 'agent_lead_id');
    }
    public function roll_outs()
    {
        return $this->hasMany(CommonRollOut::class, 'job_card_id');
    }

    public function hold_reason()
    {
        return $this->belongsTo(BlockageReason::class, 'hold_reason_id');
    }

    public function heldByUser()
    {
        return $this->belongsTo(User::class, 'held_by_id');
    }

    public function bill()
    {
        return $this->hasOne(Bill::class, 'job_card_id');
    }
}
