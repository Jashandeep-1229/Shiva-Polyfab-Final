<?php

namespace App\Models;

use App\Traits\AutoLogsActivity;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LedgerFollowup extends Model
{
    use HasFactory, SoftDeletes, AutoLogsActivity;

    protected $fillable = [
        'user_id',
        'customer_id',
        'subject',
        'start_date',
        'complete_date',
        'completed_by',
        'total_no_of_days',
        'status'
    ];

    public function customer()
    {
        return $this->belongsTo(AgentCustomer::class, 'customer_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function completedBy()
    {
        return $this->belongsTo(User::class, 'completed_by');
    }

    public function histories()
    {
        return $this->hasMany(LedgerFollowupHistory::class, 'followup_id');
    }

    public function activeHistory()
    {
        return $this->hasOne(LedgerFollowupHistory::class, 'followup_id')->where('status', 1)->latest();
    }
}
