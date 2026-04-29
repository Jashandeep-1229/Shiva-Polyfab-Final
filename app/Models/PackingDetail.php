<?php

namespace App\Models;

use App\Traits\AutoLogsActivity;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PackingDetail extends Model
{
    use HasFactory,SoftDeletes, AutoLogsActivity;
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
    public function size()
    {
        return $this->belongsTo(SizeMaster::class, 'size_id');
    }
    public function color()
    {
        return $this->belongsTo(ColorMaster::class, 'color_id');
    }
    public function user()
    {
        return $this->belongsTo(User::class, 'complete_by');
    }
}
