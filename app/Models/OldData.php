<?php

namespace App\Models;

use App\Traits\AutoLogsActivity;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OldData extends Model
{
    use HasFactory, SoftDeletes, AutoLogsActivity;

    protected $table = 'old_data';

    protected $fillable = [
        'order_date',
        'dispatch_date',
        'name_of_job',
        'bopp_id',
        'fabric_id',
        'loop_color_id',
        'remarks',
        'pieces',
        'send_for',
        'image',
    ];

    public function bopp()
    {
        return $this->belongsTo(Bopp::class);
    }

    public function fabric()
    {
        return $this->belongsTo(Fabric::class);
    }

    public function loop()
    {
        return $this->belongsTo(Loop::class, 'loop_color_id');
    }
}
