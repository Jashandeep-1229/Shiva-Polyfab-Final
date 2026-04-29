<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use App\Traits\AutoLogsActivity;

class PackingSlip extends Model
{
    use HasFactory, SoftDeletes, AutoLogsActivity;
    
    public $log_context = [];

    public function tapActivity(\Spatie\Activitylog\Models\Activity $activity, string $eventName)
    {
        $activity->description = "Packing Slip Ref: {$this->packing_slip_no}";
        if(!empty($this->log_context)) {
            $properties = $activity->properties->toArray();
            $properties['context'] = $this->log_context;
            $activity->properties = collect($properties);
        }
    }

    protected $fillable = [
        'user_id',
        'packing_slip_no',
        'job_card_id',
        'total_weight',
        'pending_weight',
        'dispatch_weight',
        'total_bags',
        'pending_bags',
        'dispatch_bags',
        'packing_date',
        'dispatch_date',
        'complete_date',
        'remarks',
        'status',
        'dispatch_by',
    ];

    public function job_card()
    {
        return $this->belongsTo(JobCard::class);
    }
    public function packing_details()
    {
        return $this->hasMany(PackingDetail::class);
    }
}
