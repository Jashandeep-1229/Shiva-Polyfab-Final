<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PackingDetail extends Model
{
    use HasFactory,SoftDeletes;
    protected $fillable = [
        'packing_slip_id',
        'job_card_id',
        'weight',
        'start_date',
        'complete_date',
        'complete_by',
        'status',
        'remarks',
        'is_undo',
    ];
    public function packing_slip()
    {
        return $this->belongsTo(PackingSlip::class);
    }
    public function job_card()
    {
        return $this->belongsTo(JobCard::class);
    }
    public function user()
    {
        return $this->belongsTo(User::class, 'complete_by');
    }
}
