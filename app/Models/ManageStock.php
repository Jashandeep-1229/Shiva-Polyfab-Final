<?php

namespace App\Models;

use App\Traits\AutoLogsActivity;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ManageStock extends Model
{
    use HasFactory, SoftDeletes, AutoLogsActivity;

    protected $fillable = [
        'user_id',
        'from',
        'from_id',
        'stock_name',
        'stock_id',
        'date',
        'unit',
        'quantity',
        'average',
        'in_out',
        'remarks',
        'status',
    ];

    protected $casts = [
        'stock_id' => 'integer',
        'from_id' => 'integer',
        'quantity' => 'decimal:2',
        'average' => 'decimal:2',
    ];

    public function master()
    {
        // Use the current instance's stock_name, or fallback to request or Fabric to prevent crashes during inspection
        $name = $this->stock_name ?: (request()->stock_name ?: 'fabric');
        $modelClass = "App\\Models\\" . ucfirst($name);
        
        // Final safety check to ensure class exists
        if (!class_exists($modelClass)) {
            $modelClass = "App\\Models\\Fabric";
        }

        return $this->belongsTo($modelClass, 'stock_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
