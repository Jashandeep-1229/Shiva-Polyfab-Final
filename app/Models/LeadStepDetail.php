<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LeadStepDetail extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['lead_id', 'status_id', 'field_key', 'field_value'];

    public function lead()
    {
        return $this->belongsTo(Lead::class);
    }

    public function status()
    {
        return $this->belongsTo(LeadStatus::class, 'status_id');
    }
}
