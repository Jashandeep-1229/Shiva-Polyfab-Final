<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bill extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function items()
    {
        return $this->hasMany(BillItem::class);
    }

    public function customer()
    {
        return $this->belongsTo(AgentCustomer::class, 'customer_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function job_card()
    {
        return $this->belongsTo(JobCard::class, 'job_card_id');
    }
}
