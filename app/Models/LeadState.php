<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeadState extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'status'];

    public function cities()
    {
        return $this->hasMany(LeadCity::class, 'state_id');
    }
}
