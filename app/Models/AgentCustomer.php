<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use App\Traits\AutoLogsActivity;

class AgentCustomer extends Model
{
    use HasFactory, SoftDeletes, AutoLogsActivity;

    protected $table = 'agent_customers';

    protected $fillable = [
        'user_id',
        'code',
        'name',
        'phone_no',
        'role',
        'type',
        'sale_executive_id',
        'gst',
        'address',
        'pincode',
        'state',
        'city',
        'remarks',
        'status',
        'is_bad_debt',
        'last_message_at',
        'unseen_count',
    ];

    public function sale_executive()
    {
        return $this->belongsTo(User::class, 'sale_executive_id');
    }

    public function ledgerEntries()
    {
        return $this->hasMany(CustomerLedger::class, 'customer_id');
    }

    public function chatMessages()
    {
        return $this->hasMany(ChatMessage::class, 'customer_id');
    }
}
