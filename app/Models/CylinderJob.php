<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CylinderJob extends Model
{
    use HasFactory, SoftDeletes;
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
