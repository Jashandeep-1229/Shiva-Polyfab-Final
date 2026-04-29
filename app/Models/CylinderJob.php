<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use App\Traits\AutoLogsActivity;

class CylinderJob extends Model
{
    use HasFactory, SoftDeletes, AutoLogsActivity;

    public function tapActivity(\Spatie\Activitylog\Models\Activity $activity, string $eventName)
    {
        $agentName = $this->cylinder_agent ? $this->cylinder_agent->name : 'N/A';
        $activity->description = "Agent: {$agentName} | Job: {$this->name_of_job}";
    }
    protected $fillable = [
        'job_card_id',
        'cylinder_agent_id',
        'name_of_job',
        'check_in_by',
        'check_in_date',
        'check_out_by',
        'check_out_date',
        'total_no_of_days',
        'remarks',
    ];
    public function job_card(){
        return $this->belongsTo(JobCard::class,'job_card_id');
    }
    public function cylinder_agent(){
        return $this->belongsTo(CylinderAgent::class,'cylinder_agent_id');
    }
}
