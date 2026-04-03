<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Ink extends Model
{
    use HasFactory, SoftDeletes;

    protected $casts = [
        "alert_min_stock" => "decimal:2",
        "alert_max_stock" => "decimal:2",
        "order_qty" => "decimal:2",
    ];

    protected $fillable = [
        'user_id',
        'name',
        'company_name',
        'phone_no',
        'alert_min_stock', 'alert_max_stock',
        'order_qty',
        'status',
    ];
}
