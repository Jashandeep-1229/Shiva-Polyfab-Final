<?php

namespace App\Models;

use App\Traits\AutoLogsActivity;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CommonManageStock extends Model
{
    use HasFactory, SoftDeletes, AutoLogsActivity;

    protected $fillable = [
        'date',
        'from',
        'from_id',
        'user_id',
        'color_id',
        'size_id',
        'quantity',
        'in_out',
        'remarks'
    ];

    protected $casts = [
        'date' => 'date',
        'quantity' => 'float',
        'user_id' => 'integer',
        'color_id' => 'integer',
        'size_id' => 'integer'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function color()
    {
        return $this->belongsTo(ColorMaster::class, 'color_id');
    }

    public function size()
    {
        return $this->belongsTo(SizeMaster::class, 'size_id');
    }
}
