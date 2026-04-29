<?php

namespace App\Models;

use App\Traits\AutoLogsActivity;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CommonRollOut extends Model
{
    use HasFactory, SoftDeletes, AutoLogsActivity;

    protected $fillable = [
        'job_card_id',
        'user_id',
        'rolls_out',
        'action_type',
        'date',
        'remarks'
    ];

    protected $casts = [
        'date' => 'date',
        'rolls_out' => 'decimal:2'
    ];

    public function job_card()
    {
        return $this->belongsTo(JobCard::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
