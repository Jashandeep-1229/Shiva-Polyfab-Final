<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AgentCustomer extends Model
{
    use HasFactory, SoftDeletes;

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
    ];

    public function sale_executive()
    {
        return $this->belongsTo(User::class, 'sale_executive_id');
    }

    public function ledgerEntries()
    {
        return $this->hasMany(CustomerLedger::class, 'customer_id');
    }
}
