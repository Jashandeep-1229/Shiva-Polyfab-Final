<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SizeMaster extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = [
        'user_id', 
        'name', 
        'fabric_id', 
        'bopp_id', 
        'order_send_for', 
        'is_temporary', 
        'status'
    ];

    public function fabric()
    {
        return $this->belongsTo(Fabric::class, 'fabric_id');
    }

    public function bopp()
    {
        return $this->belongsTo(Bopp::class, 'bopp_id');
    }
}
