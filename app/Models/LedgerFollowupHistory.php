<?php

namespace App\Models;

use App\Traits\AutoLogsActivity;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LedgerFollowupHistory extends Model
{
    use HasFactory, AutoLogsActivity;

    protected $fillable = [
        'followup_id',
        'user_id',
        'remarks',
        'followup_date_time',
        'status',
        'complete_date_time',
        'complete_by',
        'total_no_of_days'
    ];

    public function followup()
    {
        return $this->belongsTo(LedgerFollowup::class, 'followup_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function completedBy()
    {
        return $this->belongsTo(User::class, 'complete_by');
    }
}
