<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use App\Traits\AutoLogsActivity;

class JobCardProcess extends Model
{
    use HasFactory, SoftDeletes, AutoLogsActivity;

    public function tapActivity(\Spatie\Activitylog\Models\Activity $activity, string $eventName)
    {
        $activity->description = "Process: {$this->process_name}";
    }
    protected $fillable = [
        'from_where',
        'job_card_id',
        'process_name',
        'process_start_date',
        'process_end_date',
        'total_time',
        'result_remarks',
        'process_remarks',
        'user_id',
        'status',
        'date',
        'estimate_production',
        'actual_order',
        'wastage',
        'working_hours',
        'machine_id',
        'shift_time',
        'blockage_reason_id',
        'blockage_time',
        'other_reason',
        'file',
    ];
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function job_card()
    {
        return $this->belongsTo(JobCard::class, 'job_card_id');
    }

    public function machine()
    {
        return $this->belongsTo(Machine::class, 'machine_id');
    }

    public function blockage_reason()
    {
        return $this->belongsTo(BlockageReason::class, 'blockage_reason_id');
    }
}
