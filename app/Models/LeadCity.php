<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeadCity extends Model
{
    use HasFactory;

    protected $fillable = ['state_id', 'name', 'status'];

    public function state()
    {
        return $this->belongsTo(LeadState::class, 'state_id');
    }
}
