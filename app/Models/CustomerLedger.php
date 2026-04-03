<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CustomerLedger extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'customer_id',
        'job_card_id',
        'packing_slip_id',
        'bill_id',
        'payment_method_id',
        'transaction_date',
        'amount',
        'gst',
        'total_amount',
        'extra_charge_amount',
        'extra_charge_gst',
        'extra_total_amount',
        'grand_total_amount',
        'dr_cr',
        'is_bad_debt',
        'remarks',
        'software_remarks',
        'user_id'
    ];

    public function customer()
    {
        return $this->belongsTo(AgentCustomer::class, 'customer_id');
    }

    public function job_card()
    {
        return $this->belongsTo(JobCard::class, 'job_card_id');
    }

    public function packing_slip()
    {
        return $this->belongsTo(PackingSlip::class, 'packing_slip_id');
    }

    public function payment_method()
    {
        return $this->belongsTo(PaymentMethod::class, 'payment_method_id');
    }

    public function bill()
    {
        return $this->belongsTo(Bill::class, 'bill_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
