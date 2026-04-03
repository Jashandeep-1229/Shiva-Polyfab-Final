<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PackingSlip extends Model
{
    use HasFactory,SoftDeletes;
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
