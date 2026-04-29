<?php

namespace App\Models;

use App\Traits\AutoLogsActivity;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BillItem extends Model
{
    use HasFactory, AutoLogsActivity;

    protected $guarded = [];

    public function bill()
    {
        return $this->belongsTo(Bill::class);
    }
}
