<?php

namespace App\Models;

use App\Traits\AutoLogsActivity;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerLedgerLog extends Model
{
    use HasFactory, AutoLogsActivity;

    protected $fillable = [
        'customer_ledger_id', 'customer_id', 'action', 'old_data', 'new_data', 'user_id'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    public function customer()
    {
        return $this->belongsTo(AgentCustomer::class, 'customer_id');
    }
}
